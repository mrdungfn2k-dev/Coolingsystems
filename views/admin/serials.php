<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Quản lý Serial & Lô hàng</h1>
    <p style="margin:4px 0 0;color:#667085;font-size:13px">Quản lý số sê-ri định danh sản phẩm đơn lẻ, hạn bảo hành và truy xuất chứng từ.</p>
  </div>
</div>

<?php foreach(getFlash() as $message): ?>
<div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
<?php endforeach; ?>

<div class="panel" style="padding:16px;margin-bottom:16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;font-size:13px;color:#0369a1">
  💡 <strong>Quy định về Số Serial (Số sê-ri):</strong> Số serial là mã định danh duy nhất gồm các chữ và số được gán cho từng sản phẩm riêng lẻ. Quy định bao gồm tính duy nhất trên toàn hệ thống, bắt buộc ghi chính xác trên chứng từ xuất hàng và kích hoạt bảo hành.
</div>

<div class="panel" style="padding:20px;margin-bottom:20px;background:#fff;border:1px solid #e6ebf1;border-radius:10px">
  <h2 style="font-size:16px;margin:0 0 16px;color:#1a3258">Thêm Serial sản phẩm mới</h2>
  <form method="post" action="/admin/serials" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
    <?= csrfField() ?>
    
    <!-- Trường sản phẩm Live Autocomplete -->
    <div class="form-group" style="position:relative">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Chọn sản phẩm <span style="color:#e11d48">*</span></label>
      <input type="hidden" name="product_id" id="selected_product_id" required>
      <input type="text" id="product_search" placeholder="Nhập tên SP, SKU hoặc OEM để chọn..." autocomplete="off" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
      <div id="product_suggestions" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #cbd5e1;border-radius:6px;max-height:220px;overflow-y:auto;z-index:99;box-shadow:0 4px 12px rgba(0,0,0,0.15);margin-top:2px"></div>
    </div>

    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Số Serial (Số sê-ri) <span style="color:#e11d48">*</span></label>
      <input type="text" name="serial_no" required pattern="[A-Za-z0-9\-]{3,50}" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')" placeholder="VD: SN-DENSO-9982" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;font-family:monospace;font-weight:700">
    </div>

    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Ngày sản xuất</label>
      <input type="date" name="manufactured_at" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Hết bảo hành <span style="color:#e11d48">*</span></label>
      <input type="date" name="warranty_end_date" required style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <button class="btn btn-navy" style="padding:0 20px;height:38px">Thêm Serial</button>
  </form>
</div>

<div class="panel" style="background:#fff;border:1px solid #e6ebf1;border-radius:10px;overflow:hidden">
  <div style="padding:14px 16px;border-bottom:1px solid #e6ebf1;font-weight:700;color:#1a3258">Danh sách Serial & Lô hàng (<?= count($serials) ?>)</div>
  <div style="overflow:auto">
    <table class="tbl" style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="background:#f7f9fc;border-bottom:2px solid #e6ebf1">
          <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Số Serial</th>
          <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Sản phẩm</th>
          <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Ngày sản xuất</th>
          <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Hết bảo hành</th>
          <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($serials as $serial): ?>
        <tr style="border-top:1px solid #edf1f5">
          <td style="padding:11px 12px;font-family:monospace;font-weight:700;color:#0369a1"><?= e($serial['serial_no']) ?></td>
          <td style="padding:11px 12px"><strong style="color:#1a3258"><?= e($serial['product_name']) ?></strong><div style="font-size:11px;color:#64748b"><?= e($serial['sku']) ?></div></td>
          <td style="padding:11px 12px;font-size:13px"><?= e($serial['manufactured_at']?:'—') ?></td>
          <td style="padding:11px 12px;font-size:13px"><?= e($serial['warranty_end_date']) ?></td>
          <td style="padding:11px 12px"><?= $serial['warranty_end_date']>=date('Y-m-d')?'<span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:4px;font-weight:700;font-size:12px">Còn hạn</span>':'<span style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:4px;font-weight:700;font-size:12px">Hết hạn</span>' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$serials): ?>
        <tr><td colspan="5" style="padding:30px;text-align:center;color:#9ca3af">Chưa có serial nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  var products = <?= json_encode(array_map(function($p){ return ['id'=>(int)$p['id'], 'sku'=>$p['sku'], 'name'=>$p['name']]; }, $products), JSON_UNESCAPED_UNICODE) ?>;
  var input = document.getElementById('product_search');
  var hidden = document.getElementById('selected_product_id');
  var box = document.getElementById('product_suggestions');

  input.addEventListener('input', function(){
    var q = input.value.toLowerCase().trim();
    hidden.value = '';
    if(!q) { box.style.display = 'none'; return; }
    var matches = products.filter(function(p){
      return p.name.toLowerCase().indexOf(q) !== -1 || p.sku.toLowerCase().indexOf(q) !== -1;
    }).slice(0, 10);

    if(!matches.length) {
      box.innerHTML = '<div style="padding:10px;font-size:12px;color:#9ca3af;text-align:center">Không tìm thấy sản phẩm</div>';
    } else {
      box.innerHTML = matches.map(function(p){
        return '<div class="p-sug-item" data-id="'+p.id+'" data-label="'+p.sku+' - '+p.name+'" style="padding:8px 12px;font-size:13px;cursor:pointer;border-bottom:1px solid #f1f5f9"><strong>'+p.sku+'</strong> - '+p.name+'</div>';
      }).join('');
    }
    box.style.display = 'block';
  });

  box.addEventListener('click', function(e){
    var item = e.target.closest('.p-sug-item');
    if(item) {
      hidden.value = item.getAttribute('data-id');
      input.value = item.getAttribute('data-label');
      box.style.display = 'none';
    }
  });

  document.addEventListener('click', function(e){
    if(!input.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
  });
})();
</script>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
