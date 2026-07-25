import pandas as pd
import os
import re

print("==================================================")
print("AUDITING & UPDATING ALL PRODUCT DATA & IMAGES")
print("==================================================\n")

# ---------------------------------------------------------
# 1. AUDIT & UPDATE PALMAIR DATASET
# ---------------------------------------------------------
palmair_excel = r"c:\xampp2\htdocs\coolingsystems\Palmair_Products_Final_20260724.xlsx"
palmair_img_dir = r"C:\xampp2\htdocs\coolingsystems\File ảnh Palmair"

xl_palmair = pd.ExcelFile(palmair_excel)
writer_palmair = pd.ExcelWriter(r"c:\xampp2\htdocs\coolingsystems\Palmair_Products_Final_With_Images_20260725.xlsx", engine='openpyxl')

palmair_total_rows = 0
palmair_matched_imgs = 0

def clean_fn(text):
    if not text or pd.isna(text):
        return "San-pham"
    text = str(text).strip()
    text = re.sub(r'[\x00-\x1f\\/:*?"<>|]', ' ', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

print("--- 1. PALMAIR DATASET ---")
for sheet in xl_palmair.sheet_names:
    df = xl_palmair.parse(sheet, header=1)
    if 'name' not in df.columns or len(df) == 0:
        df.to_excel(writer_palmair, sheet_name=sheet, index=False)
        continue

    sheet_img_dir = os.path.join(palmair_img_dir, clean_fn(sheet))
    existing_files = set(os.listdir(sheet_img_dir)) if os.path.exists(sheet_img_dir) else set()

    local_paths = []
    name_counts = {}

    for idx, row in df.iterrows():
        raw_name = clean_fn(row.get('name', ''))
        stt = row.get('STT', idx+1)

        name_counts[raw_name] = name_counts.get(raw_name, 0) + 1
        fn = f"{raw_name}.jpg" if name_counts[raw_name] == 1 else f"{raw_name}_{name_counts[raw_name]}.jpg"

        if fn in existing_files:
            rel_p = f"File ảnh Palmair/{clean_fn(sheet)}/{fn}"
            local_paths.append(rel_p)
            palmair_matched_imgs += 1
        else:
            # Check any file matching prefix
            matched = [f for f in existing_files if f.startswith(raw_name[:15])]
            if matched:
                rel_p = f"File ảnh Palmair/{clean_fn(sheet)}/{matched[0]}"
                local_paths.append(rel_p)
                palmair_matched_imgs += 1
            else:
                local_paths.append("")

    df['local_image_path'] = local_paths
    df.to_excel(writer_palmair, sheet_name=sheet, index=False)
    palmair_total_rows += len(df)
    print(f" Sheet '{sheet}': {len(df)} rows | Matched Images: {sum(1 for p in local_paths if p)}")

writer_palmair.close()
print(f"✅ Palmair Updated Excel saved to Palmair_Products_Final_With_Images_20260725.xlsx")
print(f"📊 Total Palmair Products: {palmair_total_rows} | Images Matched: {palmair_matched_imgs}\n")


# ---------------------------------------------------------
# 2. AUDIT & UPDATE PHUTUNGOTOMIENBAC DATASET
# ---------------------------------------------------------
phutung_excel = r"c:\xampp2\htdocs\coolingsystems\PhuTungOToMienBac_Products_20260725.xlsx"
phutung_img_dir = r"C:\xampp2\htdocs\coolingsystems\File ảnh phutungotommienbac"

xl_phutung = pd.ExcelFile(phutung_excel)
writer_phutung = pd.ExcelWriter(r"c:\xampp2\htdocs\coolingsystems\PhuTungOToMienBac_Products_With_Images_20260725.xlsx", engine='openpyxl')

phutung_total_rows = 0
phutung_matched_imgs = 0

print("--- 2. PHUTUNGOTOMIENBAC DATASET ---")
for sheet in xl_phutung.sheet_names:
    df = xl_phutung.parse(sheet)
    header_idx = 0
    for i in range(min(5, len(df))):
        row_vals = [str(v).lower() for v in df.iloc[i].values]
        if 'name' in row_vals or 'stt' in row_vals or 'source_url' in row_vals or 'sku' in row_vals:
            header_idx = i
            break

    df = xl_phutung.parse(sheet, header=header_idx)
    if 'name' not in df.columns or len(df) == 0:
        df.to_excel(writer_phutung, sheet_name=sheet, index=False)
        continue

    sheet_img_dir = os.path.join(phutung_img_dir, clean_fn(sheet))
    existing_files = set(os.listdir(sheet_img_dir)) if os.path.exists(sheet_img_dir) else set()

    local_paths = []
    name_counts = {}

    for idx, row in df.iterrows():
        raw_name = clean_fn(row.get('name', ''))
        raw_name = re.sub(r'^\d+\.\s*', '', raw_name)
        raw_name = re.sub(r'\s*\(phutungotomienbac\)$', '', raw_name, flags=re.IGNORECASE)

        name_counts[raw_name] = name_counts.get(raw_name, 0) + 1
        base_fn = raw_name if name_counts[raw_name] == 1 else f"{raw_name}_{name_counts[raw_name]}"

        # Check existing file with any extension
        matched_fn = None
        for ext in ['.jpg', '.png', '.gif', '.webp', '.jpeg']:
            test_fn = f"{base_fn}{ext}"
            if test_fn in existing_files:
                matched_fn = test_fn
                break

        if not matched_fn:
            matched_list = [f for f in existing_files if f.startswith(base_fn[:15])]
            if matched_list:
                matched_fn = matched_list[0]

        if matched_fn:
            rel_p = f"File ảnh phutungotommienbac/{clean_fn(sheet)}/{matched_fn}"
            local_paths.append(rel_p)
            phutung_matched_imgs += 1
        else:
            local_paths.append("")

    df['local_image_path'] = local_paths
    df.to_excel(writer_phutung, sheet_name=sheet, index=False)
    phutung_total_rows += len(df)
    print(f" Sheet '{sheet}': {len(df)} rows | Matched Images: {sum(1 for p in local_paths if p)}")

writer_phutung.close()
print(f"✅ PhuTungOToMienBac Updated Excel saved to PhuTungOToMienBac_Products_With_Images_20260725.xlsx")
print(f"📊 Total PhuTungOToMienBac Products: {phutung_total_rows} | Images Matched: {phutung_matched_imgs}\n")

print("==================================================")
print("AUDIT & EXCEL UPDATE COMPLETED SUCCESSFULLY!")
print("==================================================")
