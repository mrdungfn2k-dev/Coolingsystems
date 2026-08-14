import sqlite3
import os
import sys

# Re-configure stdout encoding for Windows
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

def classify(name):
    n = name.lower()
    
    # 1. Bộ đầu lốc & Pulley / Puly / Bôn từ / Mặt từ / Cuộn từ / Côn từ / Mặt hít
    if any(k in n for k in ['bộ đầu lốc', 'đầu lốc', 'pulley', 'puly', 'puli', 'bôn từ', 'mặt từ', 'cuộn từ', 'côn từ', 'bộ côn từ', 'mặt hít']):
        return 49, 'Bộ đầu lốc điều hòa'
        
    # 2. Van đuôi lốc / Van đít lốc / Van điều khiển lốc
    if any(k in n for k in ['van đuôi lốc', 'van đít lốc', 'van đuôi', 'van đít', 'van điều khiển lốc', 'van điều khiển']):
        return 24, 'Van Đuôi Lốc'

    # 3. Van tiết lưu / Van vuông / Van áp suất / Van nạp ga
    if 'van áp suất' in n:
        return 44, 'Cảm biến áp suất gas'
    if any(k in n for k in ['van tiết lưu', 'van vuông', 'van vuôn', 'van nạp ga', 'van nạp', 'van màng']) or n.startswith('van 14/') or n.startswith('van 16/'):
        return 23, 'Van Tiết Lưu'
        
    # 4. Lốc điều hòa / Bơm nén
    if any(k in n for k in ['lốc', 'bơm nén', 'chân lốc', 'bánh răng lốc', 'đầu nén']):
        return 22, 'Lốc Điều Hòa'
        
    # 5. Motor, Quạt Dàn Nóng
    if ('quạt' in n or 'mô tơ quạt' in n or 'motor quạt' in n) and ('dàn nóng' in n or 'giàn nóng' in n or 'ngoài' in n or 'nước' in n):
        return 25, 'Motor, Quạt Dàn Nóng'

    # 6. Motor, Quạt Dàn Lạnh (including quạt giàn lạnh, mô tơ quạt gió, bơm quạt, hộp quạt, mô tơ chia gió, mô tơ trộn gió, motor dẫn hướng gió)
    if any(k in n for k in ['quạt', 'mô tơ quạt', 'motor quạt', 'bơm quạt', 'hộp quạt', 'cánh quạt', 'mô tơ gió', 'chia gió', 'trộn gió', 'dẫn hướng gió']):
        return 26, 'Motor, Quạt Dàn Lạnh'
        
    # 7. Dàn sưởi / Giàn sưởi / Két sưởi
    if any(k in n for k in ['dàn sưởi', 'giàn sưởi', 'két sưởi', 'sưởi']):
        return 21, 'Dàn Sưởi Điều Hòa'
        
    # 8. Dàn nóng / Giàn nóng
    if any(k in n for k in ['dàn nóng', 'giàn nóng', 'dàn ( giàn ) nóng', 'dàn (giàn) nóng', 'giàn ( dàn ) nóng', 'giàn (dàn) nóng', 'két nóng']):
        return 43, 'Dàn nóng điều hòa'
        
    # 9. Lọc gió điều hòa
    if any(k in n for k in ['lọc gió', 'lọc cabin', 'lọc điều hòa']):
        return 47, 'Lọc Gió Điều Hòa'
        
    # 10. Dàn lạnh / Giàn lạnh / Dàn lanh
    if any(k in n for k in ['dàn lạnh', 'giàn lạnh', 'dàn lanh', 'giàn lạnh', 'dàn ( giàn ) lạnh', 'dàn (giàn) lạnh', 'giàn ( dàn ) lạnh', 'giàn (dàn) lạnh', 'dàn mát', 'giàn mát', 'két lạnh']):
        return 46, 'Dàn lạnh điều hòa'
        
    # 11. Phin lọc ga
    if any(k in n for k in ['phin lọc', 'phin ga', 'phin', 'lọc ga']):
        return 27, 'Phin Lọc Ga'
        
    # 12. Cảm biến áp suất / Cảm biến lạnh
    if any(k in n for k in ['cảm biến', 'sensor', 'rơ le nhiệt', 'ngắt lạnh', 'cảm biến lạnh']):
        return 44, 'Cảm biến áp suất gas'
        
    # 13. Ống dẫn gas / Tuy ô
    if any(k in n for k in ['ống', 'tuy ố', 'tuy-ô', 'tuyô', 'hose']):
        return 41, 'Ống dẫn gas điều hòa'
        
    # 14. Két nước
    if any(k in n for k in ['két nước', 'két làm mát']):
        return 11, 'Két Nước'
        
    # 15. ECU / Hộp điều khiển / Điều hòa điện / Phụ kiện
    return 48, 'Điều Hòa Điện & Phụ kiện'

def process_db(db_path):
    if not os.path.exists(db_path):
        return
        
    print(f"\n=========================================")
    print(f"Processing Database: {db_path}")
    print(f"=========================================")
    
    conn = sqlite3.connect(db_path)
    c = conn.cursor()
    
    # Check if products table exists
    tbl_check = c.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='products'").fetchone()
    if not tbl_check:
        print("  Table 'products' does not exist. Skipping.")
        conn.close()
        return

    # Update category settings
    # ID 11 (Két Nước) -> is_active = 1
    c.execute("UPDATE categories SET is_active = 1 WHERE id = 11")
    # ID 42 & 45 -> set is_active = 0 to avoid duplicates
    c.execute("UPDATE categories SET is_active = 0 WHERE id IN (42, 45)")

    prods = c.execute("SELECT id, name, category_id FROM products").fetchall()
    print(f"  Total products to check: {len(prods)}")
    
    updated_count = 0
    for pid, pname, old_cid in prods:
        new_cid, cname = classify(pname or '')
        if new_cid != old_cid:
            c.execute("UPDATE products SET category_id = ? WHERE id = ?", (new_cid, pid))
            updated_count += 1
            
    print(f"  Reassigned category_id for {updated_count} products.")
    
    # Recalculate product_count in categories table
    cat_ids = c.execute("SELECT id FROM categories").fetchall()
    for (cid,) in cat_ids:
        cnt = c.execute("SELECT COUNT(*) FROM products WHERE category_id = ?", (cid,)).fetchone()[0]
        c.execute("UPDATE categories SET product_count = ? WHERE id = ?", (cnt, cid))
        
    conn.commit()
    
    # Show active category summary
    cats = c.execute("SELECT id, name, slug, product_count, is_active FROM categories WHERE is_active = 1 ORDER BY sort_order, id").fetchall()
    print("\n  Summary of Active Categories:")
    for cid, cname, cslug, count, active in cats:
        print(f"    - ID {cid:2d} | {cname:30s} (slug: {cslug:25s}): {count} products")
        
    conn.close()

if __name__ == '__main__':
    possible_paths = [
        'database/cooling.sqlite',
        '/opt/coolingsystems/cooling.db',
        '/opt/coolingsystems/database/cooling.sqlite',
        '/opt/cooling-php/cooling.db',
        '/var/lib/coolingsystems/cooling.db'
    ]
    for path in possible_paths:
        process_db(path)
