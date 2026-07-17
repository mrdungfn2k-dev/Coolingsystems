BEGIN IMMEDIATE;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-5-detail-safe-20260717.webp', sort_order=0, is_main=1 WHERE id=40 AND product_id=8;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-2-2-detail-safe-20260717.webp', sort_order=1, is_main=0 WHERE id=41 AND product_id=8;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-3-2-detail-safe-20260717.webp', sort_order=2, is_main=0 WHERE id=42 AND product_id=8;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-4-2-detail-safe-20260717.webp', sort_order=3, is_main=0 WHERE id=43 AND product_id=8;
UPDATE products SET updated_at=datetime('now','localtime') WHERE id=8;
COMMIT;
