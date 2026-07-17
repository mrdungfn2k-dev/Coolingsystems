BEGIN IMMEDIATE;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-edge-safe-20260717.webp' WHERE id=48 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-20260717094651-f81a0424.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-2-edge-safe-20260717.webp' WHERE id=49 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-2-20260717094655-c848cafa.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-3-edge-safe-20260717.webp' WHERE id=50 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-3-20260717094700-a323d04e.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-4-edge-safe-20260717.webp' WHERE id=51 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-4-20260717094703-f350e995.webp';
UPDATE products SET updated_at=datetime('now','localtime') WHERE id=8;
COMMIT;
