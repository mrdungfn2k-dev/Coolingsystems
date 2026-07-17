BEGIN IMMEDIATE;
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-detail-safe2-20260717.webp' WHERE id=44 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-20260717092538-9ce5764b.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-2-detail-safe2-20260717.webp' WHERE id=45 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-2-20260717092542-42a9de3a.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-3-detail-safe2-20260717.webp' WHERE id=46 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-3-20260717092546-77266565.webp';
UPDATE product_images SET file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-4-detail-safe2-20260717.webp' WHERE id=47 AND product_id=8 AND file_path='dan-lanh-dieu-hoa-o-to-hyundai-2002-2005-kia-2003-2006-hanon-97109-3d000-97109-3d000-4-20260717092550-b3b4901b.webp';
UPDATE products SET updated_at=datetime('now','localtime') WHERE id=8;
COMMIT;
