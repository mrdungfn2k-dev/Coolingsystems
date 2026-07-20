<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
$inventoryPermissions = $inventoryPermissions ?? [];
$canViewCost = !empty($inventoryPermissions['view_cost']);
$canEditCost = !empty($inventoryPermissions['edit_cost']);
$canEditPrice = !empty($inventoryPermissions['edit_price']);
$canEditStock = !empty($inventoryPermissions['edit_stock']);
$canEditThresholds = !empty($inventoryPermissions['edit_thresholds']);
$canEditWarranty = !empty($inventoryPermissions['edit_warranty']);
$canSaveInventory = $canEditCost || $canEditPrice || $canEditStock || $canEditThresholds || $canEditWarranty;
?>
<div class="dash-head">
  <div><h1>Quản lý kho</h1><p style="margin:5px 0 0;color:#718096;font-size:13px">Cập nhật giá bán và tồn kho sản phẩm.</p></div>
  <a class="btn btn-outline" href="/admin/products">Quản lý sản phẩm</a>
</div>
<style>
.inventory-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}.inventory-kpi{border:1px solid #e3e9f1;background:#fff;padding:16px;border-radius:8px}.inventory-kpi b{display:block;font-size:23px;color:#17325c}.inventory-kpi span{font-size:12px;color:#718096}.inventory-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.inventory-filter input,.inventory-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff}.inventory-table{width:100%;border-collapse:collapse;background:#fff}.inventory-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 8px;text-align:left;white-space:nowrap}.inventory-table td{padding:10px 8px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:12px}.inventory-table .num{width:106px}.inventory-table input{width:100%;box-sizing:border-box;height:34px;border:1px solid #d8e0ea;border-radius:5px;padding:0 7px;font-size:12px}.inventory-table input:focus{border-color:#1a3258;outline:none;box-shadow:0 0 0 2px rgba(26,50,88,.09)}.inventory-table .save{border:0;background:#1a3258;color:#fff;border-radius:5px;font-weight:700;padding:8px 10px;white-space:nowrap;cursor:pointer}.inventory-table .low td{background:#fffaf0}.stock-flag{display:inline-block;padding:3px 6px;border-radius:999px;font-size:10px;font-weight:800;background:#dcfce7;color:#166534;margin-top:4px}.stock-flag.low{background:#fee2e2;color:#b91c1c}.inventory-product{display:flex;gap:9px;align-items:center;min-width:240px}.inventory-product img{width:42px;height:42px;object-fit:contain;background:#f8fafc;border:1px solid #edf1f5;border-radius:4px}.inventory-product strong{display:block;color:#1f365b;font-size:12px;line-height:1.35}.inventory-product small{display:block;color:#718096;margin-top:3px}.table-wrap{overflow:auto;border:1px solid #e6ebf1;border-radius:8px}@media(max-width:900px){.inventory-kpis{grid-template-columns:1fr}.inventory-table{min-width:1180px}}
</style>
<div class="inventory-kpis">
  <div class="inventory-kpi"><b><?= (int)$summary['total'] ?></b><span>Tổng sản phẩm</span></div>
  <div class="inventory-kpi"><b style="color:#c2410c"><?= (int)$summary['low'] ?></b><span>Chạm mức tồn tối thiểu</span></div>
  <div class="inventory-kpi"><b style="color:#b91c1c"><?= (int)$summary['out'] ?></b><span>Đã hết hàng</span></div>
</div>
<form class="inventory-filter" method="get" action="/admin/inventory">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên, SKU hoặc OEM" style="min-width:250px">
  <select name="status"><option value="all">Tất cả tồn kho</option><option value="low" <?= $stockStatus==='low'?'selected':'' ?>>Sắp hết hàng</option><option value="out" <?= $stockStatus==='out'?'selected':'' ?>>Hết hàng</option></select>
  <select name="category"><option value="0">Tất cả danh mục</option><?php foreach($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= $categoryId===(int)$category['id']?'selected':'' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
  <button class="btn btn-navy" type="submit">Lọc</button>
</form>
<div class="table-wrap"><table class="inventory-table"><thead><tr><th>Sản phẩm</th><th>Danh mục</th><?php if($canViewCost): ?><th>Giá nhập</th><?php endif; ?><th>Giá bán</th><th>Giá gốc</th><th>Tồn hiện tại</th><th>Trạng thái</th><th>Tồn tối thiểu</th><th>Tồn tối đa</th><th>Bảo hành</th><th></th></tr></thead><tbody>
<?php foreach($products as $product): $isLow=(int)$product['min_stock']>0 && (int)$product['stock']<=(int)$product['min_stock']; $formId='inventory-'.$product['id']; ?>
  <tr class="<?= $isLow?'low':'' ?>">
    <td><form id="<?= $formId ?>" method="post" action="/admin/inventory/<?= (int)$product['id'] ?>/update"><?= csrfField() ?><div class="inventory-product"><?php if(!empty($product['image'])): ?><img src="/uploads/products/<?= e($product['image']) ?>" alt=""><?php endif; ?><div><strong><?= e($product['name']) ?></strong><small>SKU: <?= e($product['sku']) ?><?= $product['oem_code']?' · OEM: '.e($product['oem_code']):'' ?></small></div></div></form></td>
    <td><?= e($product['category_name'] ?: '—') ?></td>
<?php if($canViewCost): ?>    <td class="num"><input form="<?= $formId ?>" name="cost_price" inputmode="numeric" pattern="[0-9]*" <?= $canEditCost ? '' : 'readonly' ?> value="<?= (int)$product['cost_price'] ?>"></td><?php else: ?>    <td class="num"><input form="<?= $formId ?>" type="hidden" name="cost_price" value="<?= (int)$product['cost_price'] ?>"><span>--</span></td><?php endif; ?>
    <td class="num"><input form="<?= $formId ?>" name="price" inputmode="numeric" pattern="[0-9]*" <?= $canEditPrice ? '' : 'readonly' ?> value="<?= (int)$product['price'] ?>"></td>
    <td class="num"><input form="<?= $formId ?>" name="original_price" inputmode="numeric" pattern="[0-9]*" <?= $canEditPrice ? '' : 'readonly' ?> value="<?= (int)$product['original_price'] ?>"></td>
    <td class="num"><input form="<?= $formId ?>" name="stock" inputmode="numeric" pattern="[0-9]*" min="0" max="1000" <?= $canEditStock ? '' : 'readonly' ?> value="<?= (int)$product['stock'] ?>"></td>
    <td><?php if($isLow): ?><span class="stock-flag low">Cảnh báo</span><?php else: ?><span class="stock-flag">Bình thường</span><?php endif; ?></td>
    <td class="num"><input form="<?= $formId ?>" name="min_stock" inputmode="numeric" pattern="[0-9]*" min="0" max="1000" <?= $canEditThresholds ? '' : 'readonly' ?> value="<?= (int)$product['min_stock'] ?>"></td>
    <td class="num"><input form="<?= $formId ?>" name="max_stock" inputmode="numeric" pattern="[0-9]*" min="0" max="1000" <?= $canEditThresholds ? '' : 'readonly' ?> value="<?= (int)$product['max_stock'] ?>"></td>
    <td class="num"><input form="<?= $formId ?>" name="warranty_months" inputmode="numeric" pattern="[0-9]*" min="0" max="999" <?= $canEditWarranty ? '' : 'readonly' ?> value="<?= (int)$product['warranty_months'] ?>"></td>
<?php if($canSaveInventory): ?>    <td><button form="<?= $formId ?>" class="save" type="submit">Lưu</button></td><?php endif; ?>    <td><a href="/admin/inventory/<?= (int)$product['id'] ?>/ledger" style="font-size:11px;color:#1a3258;text-decoration:none;white-space:nowrap" title="Xem thẻ kho">📋 Thẻ kho</a></td>
  </tr>
<?php endforeach; if(!$products): ?><tr><td colspan="<?= $canViewCost ? 12 : 11 ?>" style="text-align:center;padding:30px;color:#718096">Không tìm thấy sản phẩm phù hợp.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($totalPages>1): $base=['q'=>$q,'status'=>$stockStatus==='all'?null:$stockStatus,'category'=>$categoryId?:null]; ?><div class="pagination" style="margin-top:18px"><?php if($page>1): ?><a href="/admin/inventory?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?><span style="padding:0 10px;font-size:12px">Trang <?= $page ?> / <?= $totalPages ?></span><?php if($page<$totalPages): ?><a href="/admin/inventory?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?></div><?php endif; ?>

<!-- Lịch sử điều chỉnh thủ công gần đây -->
<div class="panel" style="margin-top:24px">
  <div style="padding:14px 16px;border-bottom:1px solid #edf2f7">
    <h2 style="font-size:15px;font-weight:700;margin:0;color:#1e3a8a">Lịch sử điều chỉnh tồn kho thủ công gần đây</h2>
  </div>
  <div style="overflow:auto">
    <table class="inventory-table">
      <thead>
        <tr>
          <th>Thời gian</th>
          <th>Sản phẩm</th>
          <th>Chiều biến động</th>
          <th style="text-align:right">SL chênh lệch</th>
          <th>Ghi chú / Lý do</th>
          <th>Người thực hiện</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $h): ?>
        <tr>
          <td style="color:#667085;font-size:12px;white-space:nowrap;padding:10px 8px"><?= e(substr($h['created_at'],0,16)) ?></td>
          <td style="padding:10px 8px">
            <strong><?= e($h['product_name']) ?></strong>
            <div style="font-size:11px;color:#667085;margin-top:2px">SKU: <?= e($h['sku']) ?></div>
          </td>
          <td style="padding:10px 8px">
            <?php if($h['direction']==='in'): ?>
              <span class="stock-flag">↑ Tăng tồn</span>
            <?php else: ?>
              <span class="stock-flag low">↓ Giảm tồn</span>
            <?php endif; ?>
          </td>
          <td style="text-align:right;font-weight:700;color:<?= $h['direction']==='in'?'#059669':'#dc2626' ?>;padding:10px 8px">
            <?= $h['direction']==='in'?'+':'-' ?><?= number_format((int)$h['quantity']) ?>
          </td>
          <td style="font-size:12px;color:#4a5568;padding:10px 8px"><?= e($h['note'] ?? '—') ?></td>
          <td style="font-size:12px;color:#667085;padding:10px 8px"><?= e($h['creator_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$history): ?>
        <tr><td colspan="6" style="padding:25px;text-align:center;color:#667085">Chưa có lịch sử điều chỉnh tồn kho thủ công nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>