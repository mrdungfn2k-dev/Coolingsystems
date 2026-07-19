#!/usr/bin/env python3
"""Backup and import public HVAC KiotVietWeb products as CoolingSystem drafts.

The crawler is deliberately sitemap-driven and polite. The importer only reads the
backup created by this script, records the source mapping, and never publishes a
product.
"""

from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import html
import json
import os
import re
import shutil
import sqlite3
import ssl
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET
from collections import Counter
from html.parser import HTMLParser
from pathlib import Path
from typing import Any


SOURCE_ROOT = "https://hvaccorporation.kiotvietweb.vn"
PRODUCT_SITEMAP = SOURCE_ROOT + "/sitemaps/500908911/product1/product.xml"
USER_AGENT = "CoolingSystem migration bot/1.0 (+https://coolingsystem.vn)"
IMAGE_MAX_BYTES = 18 * 1024 * 1024

CATEGORY_MAP = {
    "van-tiet-luu": 23,
    "van-duoi-loc": 24,
    "loc-may-nen": 22,
    "dan-lanh": 19,
    "dan-nong": 20,
    "motor-quat-dan-lanh": 26,
    "motor-quat-dan-nong": 25,
    "phin-loc": 27,
    "bon-dien": 28,
    "phot-loc": 28,
    "bo-dau-loc": 28,
    "mat-hit": 28,
    "san-pham-khac": 28,
}

# Product text is only mapped to a vehicle make when the make is explicitly named.
# The first detected make becomes car_brand_id; every detected make is also saved
# in product_brand_map so multi-make fitment is not lost.
VEHICLE_BRANDS = {
    "Audi": ("audi",),
    "BMW": ("bmw",),
    "Chevrolet": ("chevrolet", "chevy"),
    "Daewoo": ("daewoo",),
    "Ford": ("ford",),
    "Honda": ("honda",),
    "Hyundai": ("hyundai",),
    "Isuzu": ("isuzu",),
    "KIA": ("kia",),
    "Land Rover": ("land rover", "range rover"),
    "Lexus": ("lexus",),
    "MG": ("mg",),
    "Mazda": ("mazda",),
    "Mercedes-Benz": ("mercedes-benz", "mercedes", "benz"),
    "Mitsubishi": ("mitsubishi",),
    "Nissan": ("nissan",),
    "Peugeot": ("peugeot",),
    "Porsche": ("porsche",),
    "Subaru": ("subaru",),
    "Suzuki": ("suzuki",),
    "Toyota": ("toyota",),
    "VinFast": ("vinfast", "vin fast"),
    "Volkswagen": ("volkswagen", "vw"),
    "Volvo": ("volvo",),
}

ALLOWED_TAGS = {
    "p", "br", "h1", "h2", "h3", "h4", "h5", "h6", "ul", "ol", "li",
    "strong", "b", "em", "i", "u", "blockquote", "table", "thead", "tbody",
    "tr", "th", "td", "span", "div", "a",
}


def now_iso() -> str:
    return dt.datetime.now(dt.timezone.utc).replace(microsecond=0).isoformat()


def fetch(url: str, timeout: int = 45, retries: int = 3) -> tuple[bytes, str]:
    request = urllib.request.Request(url, headers={"User-Agent": USER_AGENT, "Accept": "text/html,application/xml,image/*;q=0.9,*/*;q=0.7"})
    last_error: Exception | None = None
    for attempt in range(retries):
        try:
            with urllib.request.urlopen(request, timeout=timeout, context=ssl.create_default_context()) as response:
                content_type = response.headers.get_content_type() or "application/octet-stream"
                body = response.read(IMAGE_MAX_BYTES + 1)
                if len(body) > IMAGE_MAX_BYTES:
                    raise ValueError("response exceeds the 18 MB safety limit")
                return body, content_type
        except (urllib.error.HTTPError, urllib.error.URLError, TimeoutError, ValueError) as error:
            last_error = error
            if attempt + 1 < retries:
                time.sleep(1.0 + attempt)
    raise RuntimeError(f"fetch failed for {url}: {last_error}")


def product_urls() -> list[str]:
    data, _ = fetch(PRODUCT_SITEMAP)
    root = ET.fromstring(data)
    namespace = "{http://www.sitemaps.org/schemas/sitemap/0.9}"
    urls = [node.text.strip() for node in root.findall(f"{namespace}url/{namespace}loc") if node.text]
    if not urls:
        raise RuntimeError("product sitemap contains no URLs")
    return list(dict.fromkeys(urls))


def text_value(value: Any) -> str:
    return value.strip() if isinstance(value, str) else ""


def int_value(value: Any, default: int = 0) -> int:
    try:
        return max(0, int(float(value)))
    except (TypeError, ValueError):
        return default


def source_category_slug(url: str) -> str:
    match = re.search(r"/c/([^/]+)/p/", urllib.parse.urlparse(url).path)
    return match.group(1) if match else "san-pham-khac"


def product_detail_from_html(document: str) -> dict[str, Any]:
    match = re.search(r'<script id="__NEXT_DATA__" type="application/json">(.*?)</script>', document, flags=re.DOTALL)
    if not match:
        raise ValueError("__NEXT_DATA__ is not present")
    payload = json.loads(html.unescape(match.group(1)))
    page_props = payload.get("props", {}).get("pageProps", {})
    detail = page_props.get("initialState", {}).get("products", {}).get("productDetail", {})
    if not isinstance(detail, dict) or not detail.get("id"):
        tools = page_props.get("toolsData", [])
        for tool in tools if isinstance(tools, list) else []:
            candidate = tool.get("data") if isinstance(tool, dict) else None
            if isinstance(candidate, dict) and candidate.get("id"):
                detail = candidate
                break
    if not isinstance(detail, dict) or not detail.get("id"):
        raise ValueError("product detail is missing from page data")
    return detail


def extension_for(url: str, content_type: str) -> str:
    suffix = Path(urllib.parse.urlparse(url).path).suffix.lower()
    if suffix in {".jpg", ".jpeg", ".png", ".webp", ".gif"}:
        return ".jpg" if suffix == ".jpeg" else suffix
    return {
        "image/jpeg": ".jpg", "image/png": ".png", "image/webp": ".webp", "image/gif": ".gif",
    }.get(content_type.lower(), ".img")


def download_image(url: str, image_dir: Path, delay: float) -> dict[str, Any]:
    data, content_type = fetch(url, timeout=60)
    digest = hashlib.sha256(data).hexdigest()
    filename = digest + extension_for(url, content_type)
    destination = image_dir / filename
    if not destination.exists():
        destination.write_bytes(data)
    if delay:
        time.sleep(delay)
    return {"source_url": url, "file": filename, "sha256": digest, "bytes": len(data), "content_type": content_type}


def crawl(output: Path, delay: float, image_delay: float) -> None:
    output.mkdir(parents=True, exist_ok=True)
    pages_dir = output / "pages"
    image_dir = output / "images"
    pages_dir.mkdir(exist_ok=True)
    image_dir.mkdir(exist_ok=True)
    urls = product_urls()
    records_path = output / "products.jsonl"
    errors_path = output / "errors.jsonl"
    completed: set[str] = set()
    if records_path.exists():
        for line in records_path.read_text(encoding="utf-8").splitlines():
            try:
                completed.add(str(json.loads(line)["source_id"]))
            except (json.JSONDecodeError, KeyError):
                continue

    with records_path.open("a", encoding="utf-8") as records, errors_path.open("a", encoding="utf-8") as errors:
        for index, url in enumerate(urls, start=1):
            try:
                body, _ = fetch(url)
                document = body.decode("utf-8", "replace")
                detail = product_detail_from_html(document)
                source_id = str(detail["id"])
                page_path = pages_dir / f"{source_id}.html"
                if not page_path.exists():
                    page_path.write_text(document, encoding="utf-8")
                if source_id in completed:
                    continue
                images: list[dict[str, Any]] = []
                image_errors: list[dict[str, str]] = []
                seen_urls: set[str] = set()
                for attachment in detail.get("ref_attachments", []) if isinstance(detail.get("ref_attachments"), list) else []:
                    image_url = text_value(attachment.get("url") if isinstance(attachment, dict) else "")
                    if not image_url or image_url in seen_urls:
                        continue
                    seen_urls.add(image_url)
                    try:
                        images.append(download_image(image_url, image_dir, image_delay))
                    except Exception as error:  # Continue backing up the product even when one CDN file is unavailable.
                        image_errors.append({"source_url": image_url, "error": str(error)})
                record = {
                    "source_id": source_id,
                    "source_ref_id": text_value(detail.get("ref_id")),
                    "source_url": url,
                    "source_category_slug": source_category_slug(url),
                    "source_category_name": text_value(detail.get("category_name")),
                    "scraped_at": now_iso(),
                    "source_html_sha256": hashlib.sha256(body).hexdigest(),
                    "product": detail,
                    "images": images,
                    "image_errors": image_errors,
                }
                records.write(json.dumps(record, ensure_ascii=False, separators=(",", ":")) + "\n")
                records.flush()
                completed.add(source_id)
                print(f"crawl {index}/{len(urls)} source={source_id} images={len(images)} image_errors={len(image_errors)}", flush=True)
            except Exception as error:
                errors.write(json.dumps({"url": url, "error": str(error), "at": now_iso()}, ensure_ascii=False) + "\n")
                errors.flush()
                print(f"crawl {index}/{len(urls)} ERROR {url}: {error}", file=sys.stderr, flush=True)
            if delay:
                time.sleep(delay)
    summarize(output, urls)


class SafeHtml(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.parts: list[str] = []
        self.skip_depth = 0

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        tag = tag.lower()
        if tag in {"script", "style", "iframe", "object", "embed"}:
            self.skip_depth += 1
            return
        if self.skip_depth or tag not in ALLOWED_TAGS:
            return
        allowed: list[str] = []
        for key, value in attrs:
            key = key.lower()
            value = value or ""
            if key in {"title", "alt"}:
                allowed.append(f' {key}="{html.escape(value, quote=True)}"')
            elif tag == "a" and key == "href" and re.match(r"^https?://", value, flags=re.I):
                allowed.append(f' href="{html.escape(value, quote=True)}" rel="nofollow noopener"')
        self.parts.append("<" + tag + "".join(allowed) + ">")

    def handle_startendtag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        self.handle_starttag(tag, attrs)

    def handle_endtag(self, tag: str) -> None:
        tag = tag.lower()
        if tag in {"script", "style", "iframe", "object", "embed"}:
            self.skip_depth = max(0, self.skip_depth - 1)
            return
        if not self.skip_depth and tag in ALLOWED_TAGS and tag != "br":
            self.parts.append(f"</{tag}>")

    def handle_data(self, data: str) -> None:
        if not self.skip_depth:
            self.parts.append(html.escape(data))


def sanitize_html(value: Any) -> str:
    raw = text_value(value)
    if not raw:
        return ""
    parser = SafeHtml()
    parser.feed(raw)
    parser.close()
    return "".join(parser.parts).strip()


def plain_text(value: str) -> str:
    return re.sub(r"\s+", " ", re.sub(r"<[^>]+>", " ", html.unescape(value))).strip()


def slugify(value: str) -> str:
    normalized = unicodedata.normalize("NFD", value)
    normalized = "".join(char for char in normalized if unicodedata.category(char) != "Mn")
    normalized = normalized.replace("đ", "d").replace("Đ", "D").lower()
    normalized = re.sub(r"[^a-z0-9]+", "-", normalized).strip("-")
    return normalized[:170] or "san-pham-hvac"


def vehicle_brand_ids(cursor: sqlite3.Cursor, text: str) -> list[int]:
    rows = cursor.execute("SELECT id,name FROM brands").fetchall()
    available = {str(name).casefold(): int(brand_id) for brand_id, name in rows}
    normalized = unicodedata.normalize("NFKD", text).casefold()
    found: list[int] = []
    for target_name, aliases in VEHICLE_BRANDS.items():
        brand_id = available.get(target_name.casefold())
        if not brand_id:
            continue
        for alias in aliases:
            if re.search(r"(?<![\w])" + re.escape(alias.casefold()) + r"(?![\w])", normalized):
                found.append(brand_id)
                break
    return list(dict.fromkeys(found))


def source_attributes(product: dict[str, Any]) -> tuple[str, str]:
    attributes = product.get("ref_attributes")
    if not isinstance(attributes, list):
        return "", ""
    rows: list[tuple[str, str]] = []
    for attribute in attributes:
        if not isinstance(attribute, dict):
            continue
        label = text_value(attribute.get("name") or attribute.get("attribute_name") or attribute.get("label"))
        value = text_value(attribute.get("value") or attribute.get("attribute_value"))
        if label or value:
            rows.append((label, value))
    if not rows:
        return "", ""
    short_specs = "; ".join(filter(None, [f"{label}: {value}" if label else value for label, value in rows]))
    table = "<table><tbody>" + "".join(
        f"<tr><th>{html.escape(label)}</th><td>{html.escape(value)}</td></tr>" for label, value in rows
    ) + "</tbody></table>"
    return short_specs, table


def normalized_text(value: Any) -> str:
    value = unicodedata.normalize("NFD", text_value(value))
    value = "".join(character for character in value if unicodedata.category(character) != "Mn")
    return re.sub(r"\s+", " ", value.replace("đ", "d").replace("Đ", "D").casefold()).strip()


def excel_category_id(value: Any) -> int:
    category = normalized_text(value)
    if "motor, quat dan lanh" in category:
        return 26
    if "motor, quat dan nong" in category:
        return 25
    if "van tiet luu" in category:
        return 23
    if "van duoi loc" in category:
        return 24
    if "loc, may nen" in category:
        return 22
    if "dan lanh" in category:
        return 19
    if "dan nong" in category:
        return 20
    if "phin loc" in category:
        return 27
    return 28


def load_records(backup: Path) -> list[dict[str, Any]]:
    records_path = backup / "products.jsonl"
    if not records_path.exists():
        raise RuntimeError(f"backup file is missing: {records_path}")
    records = [json.loads(line) for line in records_path.read_text(encoding="utf-8").splitlines() if line.strip()]
    if not records:
        raise RuntimeError("backup contains no completed product records")
    return records


def load_excel_records(backup: Path, excel_json: Path) -> list[dict[str, Any]]:
    rows = json.loads(excel_json.read_text(encoding="utf-8"))
    if not isinstance(rows, list) or not rows:
        raise RuntimeError("Excel authority file contains no product rows")
    source_by_code: dict[str, dict[str, Any]] = {}
    for record in load_records(backup):
        source_code = text_value((record.get("product") or {}).get("code"))
        if source_code:
            source_by_code[source_code] = record
    merged: list[dict[str, Any]] = []
    for row in rows:
        if not isinstance(row, dict):
            continue
        code = text_value(row.get("code"))
        name = text_value(row.get("name"))
        if not code or not name:
            raise RuntimeError("Excel authority file contains a blank code or name")
        source = source_by_code.get(code, {})
        source_product = source.get("product") if isinstance(source.get("product"), dict) else {}
        product = dict(source_product)
        product.update({
            "name": name,
            "long_name": name,
            "code": code,
            "price": int_value(row.get("price")),
            "remain": int_value(row.get("stock")),
            "on_hand": int_value(row.get("stock")),
            "category_name": text_value(row.get("category")),
            "trade_mark_name": text_value(row.get("part_brand")) or text_value(source_product.get("trade_mark_name")),
            "description": text_value(row.get("description")) or text_value(source_product.get("description")),
        })
        source_id = "hvac:" + code
        merged.append({
            "source_id": source_id,
            "source_ref_id": text_value(source.get("source_id")) or text_value(source_product.get("ref_id")),
            "source_url": text_value(source.get("source_url")),
            "source_category_slug": "excel",
            "source_category_name": text_value(row.get("category")),
            "source_html_sha256": text_value(source.get("source_html_sha256")) or hashlib.sha256(
                json.dumps(row, ensure_ascii=False, sort_keys=True).encode("utf-8")
            ).hexdigest(),
            "product": product,
            "images": source.get("images") if isinstance(source.get("images"), list) else [],
            "image_errors": source.get("image_errors") if isinstance(source.get("image_errors"), list) else [],
            "excel_image_urls": row.get("image_urls") if isinstance(row.get("image_urls"), list) else [],
            "matched_source_page": bool(source),
        })
    codes = [text_value(row.get("code")) for row in rows if isinstance(row, dict)]
    if len(codes) != len(set(codes)):
        raise RuntimeError("Excel authority file contains duplicate product codes")
    return merged


def make_sku(cursor: sqlite3.Cursor, source_code: str, source_id: str) -> str:
    base = source_code.strip() or f"HVAC-{source_id}"
    exists = cursor.execute("SELECT 1 FROM products WHERE partner_id=1 AND sku=?", (base,)).fetchone()
    return base if not exists else f"HVAC-{base}-{source_id[-6:]}"[:190]


def ensure_source_map(cursor: sqlite3.Cursor) -> None:
    cursor.execute(
        """CREATE TABLE IF NOT EXISTS hvac_product_import_map (
            source_id TEXT PRIMARY KEY,
            source_ref_id TEXT,
            source_url TEXT NOT NULL,
            product_id INTEGER NOT NULL UNIQUE REFERENCES products(id) ON DELETE CASCADE,
            imported_at TEXT NOT NULL,
            raw_sha256 TEXT NOT NULL
        )"""
    )


def import_records(backup: Path, database: Path, uploads: Path, apply: bool, excel_json: Path | None = None) -> dict[str, Any]:
    records = load_excel_records(backup, excel_json) if excel_json else load_records(backup)
    connection = sqlite3.connect(database)
    connection.execute("PRAGMA foreign_keys=ON")
    cursor = connection.cursor()
    category_ids = {int(row[0]) for row in cursor.execute("SELECT id FROM categories")}
    report: Counter[str] = Counter()
    unknown_categories: Counter[str] = Counter()
    missing_images: list[dict[str, str]] = []
    imported_product_ids: list[int] = []
    timestamp = now_iso()

    try:
        cursor.execute("BEGIN IMMEDIATE")
        ensure_source_map(cursor)
        for record in records:
            source_id = str(record["source_id"])
            if cursor.execute("SELECT product_id FROM hvac_product_import_map WHERE source_id=?", (source_id,)).fetchone():
                report["already_imported"] += 1
                continue
            product = record.get("product") or {}
            original_name = text_value(product.get("name") or product.get("long_name"))
            if not original_name:
                report["missing_name"] += 1
                continue
            source_category = text_value(record.get("source_category_slug"))
            category_id = excel_category_id(record.get("source_category_name")) if excel_json else CATEGORY_MAP.get(source_category, 28)
            if not excel_json and source_category not in CATEGORY_MAP:
                unknown_categories[source_category] += 1
            if category_id not in category_ids:
                raise RuntimeError(f"target category {category_id} does not exist")
            description = sanitize_html(product.get("description"))
            short_specs, specifications = source_attributes(product)
            source_code = text_value(product.get("code"))
            sku = make_sku(cursor, source_code, source_id)
            source_seo = product.get("seo_information") if isinstance(product.get("seo_information"), dict) else {}
            seo_title = text_value(source_seo.get("title")) or original_name
            seo_description = text_value(source_seo.get("description")) or plain_text(description)[:160]
            vehicle_ids = vehicle_brand_ids(cursor, original_name + " " + plain_text(description))
            name = "1 " + original_name
            slug = f"hvac-{source_id[-10:]}-{slugify(original_name)}"[:190]
            price = int_value(product.get("price"))
            stock = int_value(product.get("remain"), int_value(product.get("on_hand")))
            cursor.execute(
                """INSERT INTO products (
                    partner_id,sku,oem_code,part_brand,category_id,name,slug,description,short_specs,
                    price,stock,status,is_admin_created,meta_title,meta_description,focus_keyword,
                    is_indexed,features,specifications,car_brand_id,seo_title,seo_description,seo_keyword,
                    created_at,updated_at
                ) VALUES (1,?,?,?,?,?,?,?,?,?,?, 'draft',1,?,?,?,0,?,?,?,?,?,?,?,?)""",
                (
                    sku, source_code or None, text_value(product.get("trade_mark_name")) or None, category_id,
                    name, slug, description, short_specs or None, price, stock,
                    seo_title, seo_description or None, source_code or None,
                    "", specifications or "", vehicle_ids[0] if vehicle_ids else None,
                    seo_title, seo_description or "", source_code or "", timestamp, timestamp,
                ),
            )
            product_id = int(cursor.lastrowid)
            imported_product_ids.append(product_id)
            cursor.execute(
                "INSERT INTO hvac_product_import_map (source_id,source_ref_id,source_url,product_id,imported_at,raw_sha256) VALUES (?,?,?,?,?,?)",
                (source_id, text_value(record.get("source_ref_id")) or None, record["source_url"], product_id, timestamp, record["source_html_sha256"]),
            )
            for brand_id in vehicle_ids:
                cursor.execute("INSERT OR IGNORE INTO product_brand_map (product_id,brand_id) VALUES (?,?)", (product_id, brand_id))
            for order, image in enumerate(record.get("images") or []):
                image_file = text_value(image.get("file") if isinstance(image, dict) else "")
                source_file = backup / "images" / image_file
                if not image_file or not source_file.is_file():
                    missing_images.append({"source_id": source_id, "file": image_file})
                    continue
                extension = source_file.suffix.lower() if source_file.suffix.lower() in {".jpg", ".png", ".webp", ".gif"} else ".jpg"
                target_name = f"hvac-{source_id[:12]}-{order + 1}-{image_file[:12]}{extension}"
                target_path = uploads / target_name
                if apply and not target_path.exists():
                    shutil.copy2(source_file, target_path)
                cursor.execute(
                    "INSERT INTO product_images (product_id,file_path,sort_order,is_main,alt_text) VALUES (?,?,?,?,?)",
                    # Product views add the public /uploads/products/ prefix themselves.
                    (product_id, target_name, order, 1 if order == 0 else 0, original_name),
                )
            report["imported"] += 1
            report["images"] += len(record.get("images") or [])
            report["with_vehicle_brand"] += 1 if vehicle_ids else 0
            report["matched_source_page"] += 1 if record.get("matched_source_page") else 0
        if apply:
            connection.commit()
        else:
            connection.rollback()
            report["dry_run"] = report["imported"]
            report["imported"] = 0
    except Exception:
        connection.rollback()
        raise
    finally:
        connection.close()

    result = {
        "ok": True,
        "applied": apply,
        "backup": str(backup),
        "records": len(records),
        "excel_authority": str(excel_json) if excel_json else None,
        "report": dict(report),
        "unknown_source_categories": dict(unknown_categories),
        "missing_local_images": missing_images,
        "imported_product_ids": imported_product_ids if apply else [],
    }
    (backup / ("import-result.json" if apply else "import-dry-run.json")).write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
    return result


def summarize(output: Path, expected_urls: list[str] | None = None) -> dict[str, Any]:
    records = load_records(output)
    categories = Counter(text_value(item.get("source_category_slug")) for item in records)
    images = sum(len(item.get("images") or []) for item in records)
    source_errors = 0
    if (output / "errors.jsonl").exists():
        source_errors = sum(1 for line in (output / "errors.jsonl").read_text(encoding="utf-8").splitlines() if line.strip())
    result = {
        "ok": len(records) == (len(expected_urls) if expected_urls is not None else len(records)),
        "expected_products": len(expected_urls) if expected_urls is not None else None,
        "backed_up_products": len(records),
        "downloaded_images": images,
        "product_page_errors": source_errors,
        "categories": dict(categories),
    }
    (output / "backup-summary.json").write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
    return result


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("command", choices=("crawl", "summary", "import"))
    parser.add_argument("--backup", required=True, type=Path)
    parser.add_argument("--delay", type=float, default=0.25)
    parser.add_argument("--image-delay", type=float, default=0.10)
    parser.add_argument("--db", type=Path, default=Path("/var/lib/cooling/cooling.db"))
    parser.add_argument("--uploads", type=Path, default=Path("/var/lib/cooling/uploads/products"))
    parser.add_argument("--excel-json", type=Path)
    parser.add_argument("--apply", action="store_true")
    args = parser.parse_args()
    if args.command == "crawl":
        crawl(args.backup, max(0.0, args.delay), max(0.0, args.image_delay))
        return
    if args.command == "summary":
        print(json.dumps(summarize(args.backup), ensure_ascii=False, indent=2))
        return
    print(json.dumps(import_records(args.backup, args.db, args.uploads, args.apply, args.excel_json), ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
