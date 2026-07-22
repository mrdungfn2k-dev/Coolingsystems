<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Tạo phiếu chuyển kho mới</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Lập phiếu điều chuyển phụ tùng giữa Kho xuất và Kho nhận.</p>
  </div>
  <a href="/admin/stock-transfers" class="btn btn-outline">‹ Trở lại danh sách</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<form method="post" action="/admin/stock-transfers" style="max-width:900px;background:#fff;padding:24px;border:1px solid #e6ebf1;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <div>
      <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Kho xuất hàng <span style="color:#e11d48">*</span></label>
      <select name="from_warehouse" required style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
        <option value="Kho chính">Kho chính (Tổng kho)</option>
        <option value="Chi nhánh Hà Nội">Chi nhánh Hà Nội</option>
        <option value="Chi nhánh TP.HCM">Chi nhánh TP.HCM</option>
      </select>
    </div>

    <div>
      <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Kho nhận hàng <span style="color:#e11d48">*</span></label>
      <select name="to_warehouse" required style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
        <option value="Chi nhánh Hà Nội">Chi nhánh Hà Nội</option>
        <option value="Chi nhánh TP.HCM">Chi nhánh TP.HCM</option>
        <option value="Kho Bảo hành">Kho Bảo hành & Lắp đặt</option>
        <option value="Kho chính">Kho chính (Tổng kho)</option>
      </select>
    </div>
  </div>

  <div style="margin-bottom:20px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Danh sách sản phẩm điều chuyển <span style="color:#e11d48">*</span></label>
    <div style="border:1px solid #e6ebf1;border-radius:8px;padding:16px;background:#fafbfc">
      <table style="width:100%;border-collapse:collapse" id="transferItemsTable">
        <thead>
          <tr style="font-size:12px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #cbd5e1">
            <th style="text-align:left;padding:8px">Sản phẩm phụ tùng</th>
            <th style="width:120px;text-align:center;padding:8px">Số lượng</th>
            <th style="width:40px"></th>
          </tr>
        </thead>
        <tbody id="transferItemsBody">
          <tr>
            <td style="padding:8px">
              <select name="items[0][product_id]" required style="width:100%;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;font-size:13px;background:#fff">
                <option value="">-- Chọn phụ tùng --</option>
                <?php foreach($products as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?> (SKU: <?= e($p['sku']?:'—') ?> | Tồn: <?= (int)$p['stock'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="padding:8px">
              <input type="number" name="items[0][quantity]" value="1" min="1" required style="width:100%;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;text-align:center;font-weight:700;font-size:14px">
            </td>
            <td style="padding:8px;text-align:center">
              <button type="button" onclick="removeRow(this)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:18px;line-height:1">×</button>
            </td>
          </tr>
        </tbody>
      </table>
      <button type="button" onclick="addRow()" class="btn btn-outline" style="margin-top:12px;font-size:13px">+ Thêm sản phẩm</button>
    </div>
  </div>

  <div style="margin-bottom:24px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Ghi chú vận chuyển / Mã vận đơn</label>
    <textarea name="note" rows="3" placeholder="Ghi chú người vận chuyển, số điện thoại tài xế, mã vận đơn..." style="width:100%;border:1px solid #d8e0ea;border-radius:6px;padding:10px;font-size:14px"></textarea>
  </div>

  <div style="display:flex;gap:12px;justify-content:flex-end">
    <a href="/admin/stock-transfers" class="btn btn-outline">Hủy bỏ</a>
    <button type="submit" class="btn btn-navy" style="padding:10px 24px">+ Tạo phiếu chuyển kho</button>
  </div>
</form>

<script>
var rowIdx = 1;
var productsHtml = `<?php foreach($products as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e(addslashes($p['name'])) ?> (SKU: <?= e(addslashes($p['sku']?:'—')) ?> | Tồn: <?= (int)$p['stock'] ?>)</option><?php endforeach; ?>`;

function addRow() {
  var tbody = document.getElementById('transferItemsBody');
  var tr = document.createElement('tr');
  tr.innerHTML = `
    <td style="padding:8px">
      <select name="items[` + rowIdx + `][product_id]" required style="width:100%;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;font-size:13px;background:#fff">
        <option value="">-- Chọn phụ tùng --</option>
        ` + productsHtml + `
      </select>
    </td>
    <td style="padding:8px">
      <input type="number" name="items[` + rowIdx + `][quantity]" value="1" min="1" required style="width:100%;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;text-align:center;font-weight:700;font-size:14px">
    </td>
    <td style="padding:8px;text-align:center">
      <button type="button" onclick="removeRow(this)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:18px;line-height:1">×</button>
    </td>
  `;
  tbody.appendChild(tr);
  rowIdx++;
}

function removeRow(btn) {
  var tbody = document.getElementById('transferItemsBody');
  if (tbody.children.length > 1) {
    btn.closest('tr').remove();
  } else {
    alert('Phiếu chuyển kho phải có ít nhất một sản phẩm.');
  }
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
