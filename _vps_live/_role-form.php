<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1><?= empty($staffRole) ? 'Tạo vai trò mới' : 'Sửa vai trò: ' . e($staffRole['name']) ?></h1></div>
<?php
$legacyGroups = [
  'Vận hành' => ['orders'=>['Đơn hàng','Xem và cập nhật đơn hàng'],'create_order'=>['Tạo đơn hộ','Tạo đơn hàng thay khách'],'returns'=>['Trả hàng','Xem và xử lý trả hàng']],
  'Sản phẩm' => ['products'=>['Sản phẩm','Quản lý sản phẩm'],'brands'=>['Hãng xe','Quản lý hãng xe'],'brand_models'=>['Thương hiệu','Quản lý thương hiệu sản phẩm'],'categories'=>['Danh mục','Quản lý danh mục']],
  'Nội dung và hỗ trợ' => ['content'=>['Nội dung','Tin tức và trang tĩnh'],'reviews'=>['Đánh giá','Kiểm duyệt đánh giá'],'contacts'=>['Liên hệ khách','Phản hồi liên hệ'],'chat'=>['Tin nhắn','Trao đổi với khách']],
  'Khác' => ['vouchers'=>['Voucher','Quản lý voucher'],'promotions'=>['Khuyến mãi','Quản lý khuyến mãi'],'users'=>['Khách hàng','Xem danh sách khách'],'staff'=>['Nhân viên','Xem nhân viên'],'reports'=>['Báo cáo','Xem tổng quan']],
];
$currentPermissions = json_decode($staffRole['permissions'] ?? '[]', true) ?: [];
?>
<style>
.perm-group{margin:22px 0}.perm-group-title{font-size:12px;font-weight:800;text-transform:uppercase;color:#1a3258;margin-bottom:10px;padding-bottom:7px;border-bottom:1px solid #eef1f6}.perm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:10px}.perm-card{display:flex;gap:12px;padding:12px 14px;border:1px solid #e5e9f0;border-radius:8px;cursor:pointer;background:#fff}.perm-card:hover,.perm-card.on{border-color:#1a3258;background:#f4f7fc}.perm-card input{position:absolute;opacity:0}.perm-check{width:20px;height:20px;border:1px solid #cbd4e1;border-radius:5px;flex:none}.perm-card.on .perm-check{background:#1a3258;box-shadow:inset 0 0 0 4px #fff}.perm-t{font-size:13px;font-weight:700;color:#1a3258;display:block}.perm-d{font-size:12px;color:#718096;display:block;margin-top:2px;line-height:1.35}
</style>
<div class="panel"><form method="post" action="<?= empty($staffRole) ? '/admin/staff/roles/new' : '/admin/staff/roles/' . $staffRole['id'] . '/edit' ?>" style="padding:24px">
  <?= csrfField() ?>
  <div class="form-group"><label>Tên vai trò <span class="req">*</span></label><input name="name" value="<?= e($staffRole['name'] ?? '') ?>" required placeholder="Ví dụ: Nhân viên kinh doanh"></div>
  <div class="form-group"><label>Mô tả vai trò</label><input name="description" value="<?= e($staffRole['description'] ?? '') ?>" placeholder="Mô tả ngắn về vai trò"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px"><label style="margin:0">Quyền hạn được cấp</label><button type="button" id="permToggleAll" class="btn btn-outline-navy btn-sm">Chọn tất cả</button></div>
  <p style="font-size:12px;color:#718096;margin:6px 0 14px">Quyền chi tiết theo ma trận sẽ được kiểm tra tại máy chủ, không chỉ dựa vào menu hiển thị.</p>
  <?php foreach ($legacyGroups as $groupName => $permissions): ?><div class="perm-group"><div class="perm-group-title"><?= e($groupName) ?></div><div class="perm-grid"><?php foreach ($permissions as $key => $info): $checked = in_array($key, $currentPermissions, true); ?><label class="perm-card<?= $checked ? ' on' : '' ?>"><input type="checkbox" name="permissions[]" value="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>><span class="perm-check"></span><span><span class="perm-t"><?= e($info[0]) ?></span><span class="perm-d"><?= e($info[1]) ?></span></span></label><?php endforeach; ?></div></div><?php endforeach; ?>
  <div class="perm-group"><div class="perm-group-title">Quyền RBAC chi tiết theo ma trận</div><div class="perm-grid"><?php foreach (($rbacCapabilities ?? []) as $capability): $key = 'rbac:' . $capability['capability']; $checked = in_array($key, $currentPermissions, true); ?><label class="perm-card<?= $checked ? ' on' : '' ?>"><input type="checkbox" name="permissions[]" value="<?= e($key) ?>" <?= $checked ? 'checked' : '' ?>><span class="perm-check"></span><span><span class="perm-t"><?= e($capability['feature_name']) ?></span><span class="perm-d"><?= e($capability['action_name']) ?> · <?= e($capability['module_name']) ?></span></span></label><?php endforeach; ?></div></div>
  <div style="display:flex;gap:10px;margin-top:24px"><button type="submit" class="btn btn-navy"><?= empty($staffRole) ? 'Tạo vai trò' : 'Lưu thay đổi' ?></button><a href="/admin/staff" class="btn btn-outline-navy">Hủy</a></div>
</form></div>
<script>(function(){document.querySelectorAll('.perm-card input').forEach(function(box){box.addEventListener('change',function(){box.closest('.perm-card').classList.toggle('on',box.checked);});});var button=document.getElementById('permToggleAll');if(button)button.addEventListener('click',function(){var boxes=[].slice.call(document.querySelectorAll('.perm-card input'));var check=!boxes.every(function(box){return box.checked;});boxes.forEach(function(box){box.checked=check;box.closest('.perm-card').classList.toggle('on',check);});button.textContent=check?'Bỏ chọn tất cả':'Chọn tất cả';});})();</script>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
