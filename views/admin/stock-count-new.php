<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Tạo phiên kiểm kho mới</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Khởi tạo đợt kiểm đếm số lượng phụ tùng tồn kho thực tế.</p>
  </div>
  <a href="/admin/stock-counts" class="btn btn-outline">‹ Trở lại danh sách</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<form method="post" action="/admin/stock-counts" style="max-width:800px;background:#fff;padding:24px;border:1px solid #e6ebf1;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">

  <div style="margin-bottom:18px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Kho cần kiểm kê <span style="color:#e11d48">*</span></label>
    <select name="warehouse_name" required style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
      <option value="Kho chính">Kho chính (Tổng kho)</option>
      <option value="Chi nhánh Hà Nội">Chi nhánh Hà Nội</option>
      <option value="Chi nhánh TP.HCM">Chi nhánh TP.HCM</option>
      <option value="Kho Bảo hành">Kho Bảo hành & Lắp đặt</option>
    </select>
  </div>

  <div style="margin-bottom:18px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Phạm vi kiểm kê</label>
    <select name="category_id" style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
      <option value="0">Tất cả danh mục sản phẩm (Toàn bộ kho)</option>
      <?php foreach($categories as $c): ?>
      <option value="<?= (int)$c['id'] ?>">Danh mục: <?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <p style="margin:4px 0 0;font-size:12px;color:#718096">Chọn một danh mục cụ thể nếu bạn chỉ muốn kiểm đếm một nhóm phụ tùng nhất định.</p>
  </div>

  <div style="margin-bottom:24px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Ghi chú đợt kiểm kho</label>
    <textarea name="note" rows="3" placeholder="Ví dụ: Kiểm kê định kỳ tháng 7/2026, kiểm kê đợt nhập hàng mới..." style="width:100%;border:1px solid #d8e0ea;border-radius:6px;padding:10px;font-size:14px"></textarea>
  </div>

  <div style="display:flex;gap:12px;justify-content:flex-end">
    <a href="/admin/stock-counts" class="btn btn-outline">Hủy bỏ</a>
    <button type="submit" class="btn btn-navy" style="padding:10px 24px">+ Bắt đầu phiên kiểm kho</button>
  </div>
</form>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
