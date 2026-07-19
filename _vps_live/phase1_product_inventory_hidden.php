      <!-- Giá và tồn kho được quản lý tập trung tại Quản lý kho. -->
      <input type="hidden" name="_inventory_in_product_form" value="0">
      <input type="hidden" name="price" value="<?= (int)($product['price'] ?? 0) ?>">
      <input type="hidden" name="cost_price" value="<?= (int)($product['cost_price'] ?? 0) ?>">
      <input type="hidden" name="original_price" value="<?= (int)($product['original_price'] ?? 0) ?>">
      <input type="hidden" name="stock" value="<?= (int)($product['stock'] ?? 0) ?>">
      <input type="hidden" name="min_stock" value="<?= (int)($product['min_stock'] ?? 5) ?>">
      <input type="hidden" name="max_stock" value="<?= (int)($product['max_stock'] ?? 1000) ?>">
      <input type="hidden" name="warranty_months" value="<?= (int)($product['warranty_months'] ?? 12) ?>">
      <div class="panel" style="background:#f8fafc;border-style:dashed">
        <div class="panel-body" style="font-size:12px;color:#64748b">Giá bán, giá nhập và tồn kho được cập nhật tại <a href="/admin/inventory" style="font-weight:700;color:#1a3258">Quản lý kho</a>.</div>
      </div>
