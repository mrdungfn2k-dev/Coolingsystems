<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1><?= empty($staffRole) ? 'Tạo vai trò mới' : 'Sửa vai trò: '.e($staffRole['name']) ?></h1>
</div>

<?php
$permList = [
  // ĐƠN HÀNG
  'orders'        => 'Đơn hàng — Xem & cập nhật đơn hàng',
  'create_order'  => 'Tạo đơn hộ — Tạo đơn hàng thay khách',
  'returns'       => 'Trả hàng — Xem & duyệt yêu cầu trả hàng',
  // SẢN PHẨM
  'products'      => 'Sản phẩm — Xem & quản lý sản phẩm',
  'brands'        => 'Hãng xe — Quản lý danh sách hãng xe',
  'brand_models'  => 'Thương hiệu SP — Quản lý thương hiệu sản phẩm',
  'categories'    => 'Danh mục — Quản lý danh mục sản phẩm',
  // NỘI DUNG
  'content'       => 'Nội dung — Quản lý bài viết tin tức',
  'static_pages'  => 'Trang tĩnh — Quản lý nội dung trang tĩnh',
  'stores'        => 'Cửa hàng — Quản lý hệ thống cửa hàng',
  // VẬN HÀNH
  'reviews'       => 'Đánh giá — Xem & kiểm duyệt đánh giá',
  'contacts'      => 'Quản lý liên hệ — Xem, phản hồi & quản lý tin nhắn liên hệ từ khách',
  'chat'          => 'Tin nhắn — Xem & trả lời tin nhắn khách',
  // CẤU HÌNH
  'vouchers'      => 'Voucher — Xem & tạo mã giảm giá',
  'promotions'    => 'Khuyến mãi — Quản lý khuyến mãi sản phẩm',
  'tax_config'    => 'Cấu hình — Vận chuyển & Giảm giá',
  // NHÂN SỰ
  'users'         => 'Người dùng — Xem danh sách khách hàng',
  'reports'       => 'Báo cáo — Xem dashboard tổng quan',
];
$currentPerms = json_decode($staffRole['permissions'] ?? '[]', true) ?: [];
?>

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
      <label>Quyền hạn được cấp <span class="req">*</span></label>
      <p style="font-size:12px;color:#888;margin:4px 0 12px">Nhân viên chỉ có thể vào các mục được tích bên dưới.</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <?php foreach($permList as $key => $label): ?>
        <label style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid var(--line);border-radius:6px;cursor:pointer;background:#fff;transition:background .2s" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='#fff'">
          <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key, $currentPerms) ? 'checked' : '' ?> style="margin-top:2px">
          <span style="font-size:13px"><?= $label ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:24px">
      <button type="submit" class="btn btn-navy"><?= empty($staffRole) ? 'Tạo vai trò' : 'Lưu thay đổi' ?></button>
      <a href="/admin/staff" class="btn btn-outline-navy">Hủy</a>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
