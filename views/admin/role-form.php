<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1><?= empty($staffRole) ? 'Tạo vai trò mới' : 'Sửa vai trò: '.e($staffRole['name']) ?></h1>
</div>

<?php
$permGroups = [
  'Đơn hàng & Trả hàng' => [
    'orders'       => ['Đơn hàng', 'Xem & cập nhật đơn hàng'],
    'create_order' => ['Tạo đơn hộ', 'Tạo đơn hàng thay khách'],
    'returns'      => ['Trả hàng', 'Xem & duyệt yêu cầu trả hàng'],
  ],
  'Sản phẩm' => [
    'products'     => ['Sản phẩm', 'Xem & quản lý sản phẩm'],
    'brands'       => ['Hãng xe', 'Quản lý danh sách hãng xe'],
    'brand_models' => ['Thương hiệu SP', 'Quản lý thương hiệu sản phẩm'],
    'categories'   => ['Danh mục', 'Quản lý danh mục sản phẩm'],
  ],
  'Nội dung' => [
    'content'      => ['Nội dung', 'Quản lý bài viết tin tức'],
    'static_pages' => ['Trang tĩnh', 'Quản lý nội dung trang tĩnh'],
    'stores'       => ['Cửa hàng', 'Quản lý hệ thống cửa hàng'],
  ],
  'Vận hành & Hỗ trợ' => [
    'reviews'      => ['Đánh giá', 'Xem & kiểm duyệt đánh giá'],
    'contacts'     => ['Liên hệ khách', 'Phản hồi & quản lý tin nhắn liên hệ'],
    'chat'         => ['Tin nhắn', 'Xem & trả lời tin nhắn khách'],
  ],
  'Khuyến mãi & Cấu hình' => [
    'vouchers'     => ['Voucher', 'Xem & tạo mã giảm giá'],
    'promotions'   => ['Khuyến mãi', 'Quản lý khuyến mãi sản phẩm'],
    'tax_config'   => ['Cấu hình', 'Vận chuyển & giảm giá'],
  ],
  'Nhân sự & Báo cáo' => [
    'users'        => ['Người dùng', 'Xem danh sách khách hàng'],
    'staff'        => ['Nhân viên', 'Xem & quản lý nhân viên'],
    'reports'      => ['Báo cáo', 'Xem dashboard tổng quan'],
  ],
];
$currentPerms = json_decode($staffRole['permissions'] ?? '[]', true) ?: [];
?>

<style>
.role-form-grid .form-group { margin-bottom:18px; }
.perm-group { margin-bottom:18px; }
.perm-group:last-child { margin-bottom:2px; }
.perm-group-title { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#1a3258; margin-bottom:10px; padding-bottom:7px; border-bottom:1px solid #eef1f6; }
.perm-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:10px; }
.perm-card { display:flex; align-items:flex-start; gap:12px; padding:12px 14px; border:1.5px solid #e5e9f0; border-radius:10px; cursor:pointer; background:#fff; transition:border-color .15s, background .15s; }
.perm-card:hover { border-color:#b9c4d6; background:#fafbfd; }
.perm-card input { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
.perm-check { flex-shrink:0; width:20px; height:20px; border:1.5px solid #cbd4e1; border-radius:6px; display:flex; align-items:center; justify-content:center; background:#fff; transition:background .15s, border-color .15s; margin-top:1px; color:#fff; }
.perm-check svg { opacity:0; transition:opacity .12s; }
.perm-card.on { border-color:#1a3258; background:#f4f7fc; }
.perm-card.on .perm-check { background:#1a3258; border-color:#1a3258; }
.perm-card.on .perm-check svg { opacity:1; }
.perm-t { font-size:13.5px; font-weight:700; color:#1a3258; display:block; }
.perm-d { font-size:12px; color:#7a8699; line-height:1.4; display:block; margin-top:2px; }
</style>
<div class="panel" style="max-width:100%">
  <form method="post" action="<?= empty($staffRole) ? '/admin/staff/roles/new' : '/admin/staff/roles/'.$staffRole['id'].'/edit' ?>" style="padding:24px">
    <?= csrfField() ?>
    
    <div class="form-group">
      <label>Tên vai trò <span class="req">*</span></label>
      <input type="text" name="name" value="<?= e($staffRole['name'] ?? '') ?>" required placeholder="Ví dụ: Nhân viên kinh doanh, Nhân viên kho,...">
    </div>
    
    <div class="form-group">
      <label>Mô tả vai trò</label>
      <input type="text" name="description" value="<?= e($staffRole['description'] ?? '') ?>" placeholder="Mô tả ngắn về vai trò này">
    </div>
    
    <div class="form-group">
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
        <label style="margin:0">Quyền hạn được cấp <span class="req">*</span></label>
        <button type="button" id="permToggleAll" class="btn btn-outline-navy btn-sm" style="height:32px">Chọn tất cả</button>
      </div>
      <p style="font-size:12px;color:#888;margin:4px 0 14px">Nhân viên chỉ có thể vào các mục được tích bên dưới.</p>
      <?php foreach($permGroups as $gname => $perms): ?>
      <div class="perm-group">
        <div class="perm-group-title"><?= e($gname) ?></div>
        <div class="perm-grid">
          <?php foreach($perms as $key => $info): $on = in_array($key, $currentPerms); ?>
          <label class="perm-card<?= $on ? ' on' : '' ?>">
            <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= $on ? 'checked' : '' ?>>
            <span class="perm-check"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg></span>
            <span class="perm-text"><span class="perm-t"><?= e($info[0]) ?></span><span class="perm-d"><?= e($info[1]) ?></span></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="display:flex;gap:10px;margin-top:24px">
      <button type="submit" class="btn btn-navy"><?= empty($staffRole) ? 'Tạo vai trò' : 'Lưu thay đổi' ?></button>
      <a href="/admin/staff" class="btn btn-outline-navy">Hủy</a>
    </div>
  </form>
</div>

<script>
(function(){
  document.querySelectorAll('.perm-card input[type="checkbox"]').forEach(function(cb){
    cb.addEventListener('change', function(){ var c=cb.closest('.perm-card'); if(c) c.classList.toggle('on', cb.checked); });
  });
  var t=document.getElementById('permToggleAll');
  if(t){ t.addEventListener('click', function(){
    var boxes=document.querySelectorAll('.perm-card input[type="checkbox"]');
    var allOn=Array.prototype.every.call(boxes, function(b){return b.checked;});
    boxes.forEach(function(b){ b.checked=!allOn; var c=b.closest('.perm-card'); if(c) c.classList.toggle('on', !allOn); });
    t.textContent = allOn ? 'Chọn tất cả' : 'Bỏ chọn tất cả';
  }); }
})();
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
