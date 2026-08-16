import sqlite3
import unicodedata
import shutil
import os
import re
import sys
import time

sys.stdout.reconfigure(encoding='utf-8')

LOCAL_BASE = r'c:\xampp2\htdocs\coolingsystems'
LOCAL_UPLOADS = os.path.join(LOCAL_BASE, 'public', 'uploads', 'products')
TEMP_EXTRACT_DIR = os.path.join(LOCAL_BASE, 'scratch', 'vps_extracted_raw')
DB_PATH = os.path.join(LOCAL_BASE, 'database', 'cooling.sqlite')
DB_BAK = os.path.join(LOCAL_BASE, 'database', 'cooling.sqlite.bak_sync')

t0 = time.time()

# 1. Backup SQLite Database
if not os.path.exists(DB_BAK):
    print(f"Creating database backup: {DB_BAK}...")
    shutil.copyfile(DB_PATH, DB_BAK)

def vietnamese_slugify(text):
    if not text:
        return "san-pham"
    text = text.replace("Đ", "D").replace("đ", "d")
    text = unicodedata.normalize('NFD', text)
    text = re.sub(r'[\u0300-\u036f]', '', text)
    text = text.lower()
    text = re.sub(r'[^a-z0-9]+', '-', text)
    text = text.strip('-')
    return text[:50] if len(text) > 50 else text

def clean_oem(oem_str):
    if not oem_str:
        return ""
    primary = re.split(r'[/,]', oem_str)[0].strip()
    cleaned = re.sub(r'[^a-zA-Z0-9_-]', '', primary)
    return cleaned.upper()

# 2. Query Database
conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()

cursor.execute("SELECT id, name, slug FROM categories")
cat_map = {cid: (cslug if cslug else vietnamese_slugify(cname)) for cid, cname, cslug in cursor.fetchall()}

cursor.execute("SELECT id, name, oem_code, category_id FROM products")
products = {}
for pid, pname, oem, cid in cursor.fetchall():
    cslug = cat_map.get(cid, 'uncategorized')
    products[pid] = {
        'id': pid,
        'name': pname,
        'oem': clean_oem(oem),
        'cat_slug': cslug
    }

cursor.execute("SELECT id, product_id, file_path, is_main, sort_order FROM product_images ORDER BY product_id, is_main DESC, sort_order ASC, id ASC")
pi_rows = cursor.fetchall()
print(f"Total product_images in DB: {len(pi_rows)}")

# 3. Index local extracted image repository
print(f"\nIndexing local extracted image repository from {TEMP_EXTRACT_DIR}...")
local_extracted_map = {} # rel_path / basename -> full_local_path
for root, dirs, files in os.walk(TEMP_EXTRACT_DIR):
    for f in files:
        if f.lower().endswith(('.jpg', '.jpeg', '.png', '.webp')):
            full_p = os.path.join(root, f)
            rel_p = os.path.relpath(full_p, TEMP_EXTRACT_DIR).replace('\\', '/')
            if rel_p not in local_extracted_map:
                local_extracted_map[rel_p] = full_p
            if f not in local_extracted_map:
                local_extracted_map[f] = full_p

print(f"Total extracted image repository indexed: {len(local_extracted_map)} local files.")

# 4. Build Re-organization & DB Update Plan
product_images_group = {}
for row in pi_rows:
    pi_id, pid, fp, is_main, sort_order = row
    if pid not in product_images_group:
        product_images_group[pid] = []
    product_images_group[pid].append({
        'id': pi_id,
        'file_path': fp,
        'is_main': is_main,
        'sort_order': sort_order
    })

copy_plan = []
db_updates = []
pi_to_delete = []

for pid, imgs in product_images_group.items():
    p = products.get(pid)
    if not p:
        continue
    
    cat_slug = p['cat_slug']
    oem = p['oem']
    name_slug = vietnamese_slugify(p['name'])
    
    base_title = f"{oem}_{name_slug}" if oem else name_slug
    base_title = re.sub(r'_+', '_', base_title).strip('_')
    
    seq = 1
    for img in imgs:
        pi_id = img['id']
        fp = img['file_path']
        if not fp:
            pi_to_delete.append(pi_id)
            continue
            
        fp_norm = fp.replace('\\', '/')
        basename = os.path.basename(fp_norm)
        
        src_local_path = local_extracted_map.get(fp_norm) or local_extracted_map.get(basename)
        if not src_local_path or not os.path.exists(src_local_path):
            pi_to_delete.append(pi_id)
            continue
            
        ext = os.path.splitext(src_local_path)[1].lower()
        if not ext:
            ext = '.jpg'
            
        if seq == 1:
            new_filename = f"{base_title}{ext}"
        else:
            new_filename = f"{base_title}_{seq}{ext}"
        seq += 1
        
        new_rel_path = f"{cat_slug}/{new_filename}"
        local_target_dir = os.path.join(LOCAL_UPLOADS, cat_slug)
        local_target_file = os.path.join(local_target_dir, new_filename)
        
        copy_plan.append({
            'pi_id': pi_id,
            'src_path': src_local_path,
            'target_dir': local_target_dir,
            'target_file': local_target_file,
            'new_rel_path': new_rel_path
        })
        db_updates.append((new_rel_path, pi_id))

print(f"\nProcessing & Re-organizing Images...")
print(f" - Valid images to copy into category folders: {len(copy_plan)}")
print(f" - Orphaned DB records to delete: {len(pi_to_delete)}")

# 5. Perform file copying & structure creation
created_dirs = set()
copied_count = 0

for item in copy_plan:
    target_dir = item['target_dir']
    if target_dir not in created_dirs:
        os.makedirs(target_dir, exist_ok=True)
        created_dirs.add(target_dir)
        
    shutil.copyfile(item['src_path'], item['target_file'])
    copied_count += 1
    if copied_count % 2000 == 0 or copied_count == len(copy_plan):
        print(f"  Copied {copied_count}/{len(copy_plan)} files into category folders...")

print(f"Successfully created and populated category folders with {copied_count} standardized images.")

# 6. Update Database
print("\nUpdating SQLite database...")
cursor.executemany("UPDATE product_images SET file_path=? WHERE id=?", db_updates)
print(f"Updated {cursor.rowcount} rows in product_images table.")

if pi_to_delete:
    cursor.executemany("DELETE FROM product_images WHERE id=?", [(pi_id,) for pi_id in pi_to_delete])
    print(f"Deleted {cursor.rowcount} orphaned rows from product_images table.")

conn.commit()
conn.close()

# 7. Clean up temp files & unreferenced old files in public/uploads/products
print("\nCleaning up temporary and old junk image files...")
valid_target_files = {item['target_file'] for item in copy_plan}

deleted_old_count = 0
for root, dirs, files in os.walk(LOCAL_UPLOADS):
    for f in files:
        full_p = os.path.join(root, f)
        if full_p not in valid_target_files:
            try:
                os.remove(full_p)
                deleted_old_count += 1
            except Exception as e:
                pass

# Clean up raw extracted temp folder
shutil.rmtree(TEMP_EXTRACT_DIR, ignore_errors=True)

t1 = time.time()
print(f"Cleaned up {deleted_old_count} old/orphan files.")
print(f"\n=== SUCCESS! Image Organization, Renaming & DB Sync Completed in {t1 - t0:.2f} seconds ===")
