import requests
import pandas as pd
import re
import os
import unicodedata
from concurrent.futures import ThreadPoolExecutor, as_completed
import time

def slugify(text):
    if not text or pd.isna(text):
        return "san-pham"
    text = str(text)
    text = unicodedata.normalize('NFD', text)
    text = ''.join(c for c in text if unicodedata.category(c) != 'Mn')
    text = text.replace('đ', 'd').replace('Đ', 'd')
    text = re.sub(r'[^a-zA-Z0-9\s-]', '', text).strip().lower()
    text = re.sub(r'[\s-]+', '-', text)
    return text or "san-pham"

excel_path = r"c:\xampp2\htdocs\coolingsystems\Palmair_Products_Final_20260724.xlsx"
df = pd.read_excel(excel_path, header=1)

save_dir = r"c:\xampp2\htdocs\coolingsystems\public\uploads\products\palmair"
os.makedirs(save_dir, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
}

def process_row(index, row):
    stt = row['STT']
    name = row['name']
    orig_url = str(row['image_urls']) if pd.notna(row['image_urls']) else ""

    if not orig_url or orig_url == "nan":
        return index, None, "No URL"

    clean_slug = slugify(name)
    filename = f"{clean_slug}-stt{stt}.jpg"
    filepath = os.path.join(save_dir, filename)
    rel_path = f"palmair/{filename}"

    # Try high-res URLs first
    highres_url = orig_url.replace("-tm_home_default.jpg", "-tm_thickbox_default.jpg")
    thickbox_url = orig_url.replace("-tm_home_default.jpg", "-thickbox_default.jpg")
    plain_url = orig_url.replace("-tm_home_default.jpg", ".jpg")

    target_urls = [highres_url, thickbox_url, plain_url, orig_url]

    downloaded = False
    file_size = 0

    for url in target_urls:
        try:
            r = requests.get(url, headers=headers, timeout=12)
            if r.status_code == 200 and len(r.content) > 1000:
                with open(filepath, 'wb') as f:
                    f.write(r.content)
                file_size = len(r.content)
                downloaded = True
                break
        except Exception:
            continue

    if downloaded:
        return index, rel_path, f"OK ({file_size} bytes)"
    else:
        return index, None, "FAILED"

print(f"Starting download of {len(df)} product images from Palmair.com...")
start_time = time.time()

results = {}
success_count = 0
fail_count = 0

with ThreadPoolExecutor(max_workers=16) as executor:
    futures = [executor.submit(process_row, idx, row) for idx, row in df.iterrows()]
    for future in as_completed(futures):
        idx, rel_path, status = future.result()
        if rel_path:
            results[idx] = rel_path
            success_count += 1
        else:
            fail_count += 1

elapsed = time.time() - start_time
print(f"\n🎉 Download Complete in {elapsed:.1f}s!")
print(f"✅ Success: {success_count} / {len(df)}")
print(f"❌ Failed: {fail_count} / {len(df)}")

# Update DataFrame with local relative image paths / web URLs
df['local_image_path'] = [results.get(i, '') for i in range(len(df))]

output_excel = r"c:\xampp2\htdocs\coolingsystems\Palmair_Products_Final_With_Images_20260725.xlsx"
df.to_excel(output_excel, index=False)
print(f"📁 Updated Excel saved to: {output_excel}")
