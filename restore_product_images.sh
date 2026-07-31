#!/bin/bash
# ============================================================
# restore_product_images.sh — Khôi phục ảnh của 1 sản phẩm
# Cách dùng: bash restore_product_images.sh <product_id> <backup_date>
# Ví dụ:     bash restore_product_images.sh 1234 20260801_0200
# ============================================================
PRODUCT_ID="${1}"
BACKUP_DATE="${2:-}"
BACKUP_ROOT="/var/lib/coolingsystems/backups/product_images"
UPLOAD_DIR="/var/lib/coolingsystems/uploads/products"

if [ -z "$PRODUCT_ID" ]; then
    echo "Cách dùng: bash restore_product_images.sh <product_id> [backup_date]"
    echo "Ví dụ:     bash restore_product_images.sh 1234 20260801_0200"
    echo ""
    echo "Các bản backup hiện có:"
    ls "$BACKUP_ROOT"
    exit 1
fi

# Tìm backup date gần nhất nếu không chỉ định
if [ -z "$BACKUP_DATE" ]; then
    BACKUP_DATE=$(ls -dt "$BACKUP_ROOT"/20*/ 2>/dev/null | head -1 | xargs basename)
    echo "Dùng bản backup mới nhất: $BACKUP_DATE"
fi

BACKUP_DIR="$BACKUP_ROOT/$BACKUP_DATE"
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Không tìm thấy backup: $BACKUP_DIR"
    exit 1
fi

# Tìm thư mục ảnh của sản phẩm
PROD_DIR=$(find "$BACKUP_DIR" -maxdepth 1 -type d -name "id${PRODUCT_ID}_*" | head -1)
if [ -z "$PROD_DIR" ]; then
    echo "Không tìm thấy ảnh cho sản phẩm #$PRODUCT_ID trong bản backup $BACKUP_DATE"
    echo "Các sản phẩm trong backup này:"
    ls "$BACKUP_DIR" | grep "^id${PRODUCT_ID}" | head -20
    exit 1
fi

echo "Khôi phục ảnh từ: $PROD_DIR"
COUNT=0
for img in "$PROD_DIR"/*; do
    [ -f "$img" ] || continue
    fname=$(basename "$img")
    cp "$img" "$UPLOAD_DIR/$fname"
    echo "  Restored: $fname"
    COUNT=$((COUNT+1))
done

echo ""
echo "Đã khôi phục $COUNT ảnh cho sản phẩm #$PRODUCT_ID"
