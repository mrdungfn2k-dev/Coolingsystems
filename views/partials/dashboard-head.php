<?php require __DIR__ . '/head-admin.php'; ?>

<div class="dash">
  <aside class="dash-sidebar">
    <div class="who">
      <div class="name"><?= e($user['full_name']) ?></div>
      <div class="role">Quản trị viên</div>
    </div>
    <nav class="dash-nav">
      <a href="/admin" class="<?= isActive('/admin') ?>">Tổng quan</a>
     <div class="sb-section">SẢN PHẨM</div>
     <a href="/admin/products" class="<?= startsWith(currentPath(),'/admin/products')?'active':'' ?>">Quản lý sản phẩm</a>
     <a href="/admin/products/new">+ Đăng SP mới</a>
     <div class="sb-section">VẬN HÀNH</div>
     <a href="/admin/orders" class="<?= startsWith(currentPath(),'/admin/orders')?'active':'' ?>">Đơn hàng</a>
     <a href="/admin/returns" class="<?= startsWith(currentPath(),'/admin/returns')?'active':'' ?>">Trả hàng</a>
     <div class="sb-section">DANH MỤC</div>
     <a href="/admin/categories" class="<?= startsWith(currentPath(),'/admin/categories')?'active':'' ?>">Danh mục</a>
     <a href="/admin/brands" class="<?= startsWith(currentPath(),'/admin/brands')?'active':'' ?>">Hãng xe</a>
     <a href="/admin/promotions" class="<?= isActive('/admin/promotions') ?>">Khuyến mãi</a>
     <a href="/admin/vouchers" class="<?= startsWith(currentPath(),'/admin/vouchers')?'active':'' ?>">Voucher toàn sàn</a>
     <div class="sb-section">NỘI DUNG</div>
     <a href="/admin/news">Tin tức</a>
     <a href="/admin/news/new" style="padding-left:28px">+ Viết bài</a>
     <a href="/admin/content" class="<?= startsWith(currentPath(),'/admin/content')?'active':'' ?>">Quản lý trang tĩnh</a>
     <div class="sb-section">HỖ TRỢ</div>
     <a href="/admin/chat" class="sb-link"><span>Tin nhắn</span></a>
     <a href="/admin/reviews" class="<?= isActive('/admin/reviews') ?>">Kiểm duyệt đánh giá</a>
     <a href="/admin/contacts" class="<?= startsWith(currentPath(),'/admin/contacts')!==false?'active':'' ?>">Liên hệ khách</a>
     <div class="sb-section">NHÂN SỰ</div>
     <a href="/admin/staff" class="<?= startsWith(currentPath(),'/admin/staff')?'active':'' ?>">Phân quyền NV</a>
     <a href="/admin/users" class="<?= isActive('/admin/users') ?>">Người dùng</a>
     <div class="sb-section">CẤU HÌNH</div>
     <a href="/admin/stores" class="sb-link"><span>Hệ thống cửa hàng</span></a>
     <a href="/admin/settings/finance" class="<?= isActive('/admin/settings/finance') ?>">Cấu hình Thuế &amp; Giảm giá</a>
     <a href="/admin/settings" class="<?= isActive('/admin/settings') ?>">Cài đặt hệ thống</a>
     <a href="/admin/logout">Đăng xuất</a>
    </nav>
  </aside>
  <div class="dash-main">
<?php
// Generate low stock alerts
$lowStockProducts = dbAll("SELECT id, name, stock, min_stock FROM products WHERE stock <= min_stock AND min_stock > 0");
foreach($lowStockProducts as $p) {
    // Check if noti already exists recently
    $exists = dbGet("SELECT 1 FROM admin_notifications WHERE type='stock' AND link LIKE ? AND created_at >= datetime('now', '-1 day')", ['%/admin/products/'.$p['id'].'%']);
    if (!$exists) {
        dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('stock', 'Cảnh báo tồn kho', ?, ?)", [
            "Sản phẩm '{$p['name']}' sắp hết hàng (còn {$p['stock']}).",
            "/admin/products/{$p['id']}/edit"
        ]);
    }
}
$notis = dbAll("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 10");
$unreadNotisCount = dbGet("SELECT COUNT(*) as n FROM admin_notifications WHERE is_read=0")['n'] ?? 0;
?>
<style>
.noti-btn { background:none; border:none; cursor:pointer; position:relative; padding:8px; display:flex; align-items:center; justify-content:center; }
.noti-badge { position:absolute; top:2px; right:2px; background:#e74c3c; color:#fff; font-size:10px; font-weight:bold; border-radius:10px; padding:2px 5px; }
.noti-dropdown { display:none; position:absolute; right:0; top:40px; width:320px; background:#fff; border:1px solid var(--line); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:100; max-height:400px; overflow-y:auto; }
.noti-dropdown.show { display:block; }
.noti-item { padding:12px; border-bottom:1px solid #f0f0f0; display:block; text-decoration:none; color:#333; transition:background .2s; }
.noti-item:hover { background:#f8f9fa; }
.noti-item.unread { background:#f0f4ff; }
.noti-title { font-weight:700; font-size:13px; margin-bottom:4px; color:var(--navy); }
.noti-msg { font-size:12px; color:#555; line-height:1.4; }
.noti-time { font-size:10px; color:#999; margin-top:6px; display:block; }
</style>
<div style="display:flex; justify-content:flex-end; padding:10px 20px; background:#fff; border-bottom:1px solid var(--line); margin:-20px -20px 20px -20px; align-items:center">
  <input type="hidden" id="globalCsrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <div style="position:relative">
    <button class="noti-btn" onclick="toggleNoti(event)">
      <svg width="22" height="22" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
      <?php if($unreadNotisCount > 0): ?><span class="noti-badge" id="notiBadge"><?= $unreadNotisCount ?></span><?php endif; ?>
    </button>
    <div class="noti-dropdown" id="notiDropdown">
      <div style="padding:12px; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center">
        <h4 style="margin:0; font-size:14px; color:var(--navy)">Thông báo</h4>
        <button onclick="markAllRead()" style="background:none; border:none; color:var(--gold); font-size:12px; font-weight:600; cursor:pointer">Đánh dấu đã đọc</button>
      </div>
      <div id="notiList">
        <?php foreach($notis as $n): ?>
        <div style="position:relative" class="noti-item-wrap">
          <a href="<?= e($n['link']) ?>" class="noti-item <?= $n['is_read'] ? '' : 'unread' ?>" onclick="markRead(<?= $n['id'] ?>)" style="padding-right: 36px;">
            <div class="noti-title"><?= e($n['title']) ?></div>
            <div class="noti-msg"><?= e($n['message']) ?></div>
            <span class="noti-time"><?= date('d/m H:i', strtotime($n['created_at'])) ?></span>
          </a>
          <button onclick="deleteNoti(<?= $n['id'] ?>, event)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#999; padding:4px;" title="Xóa thông báo">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
          </button>
        </div>
        <?php endforeach; ?>
        <?php if(!$notis): ?>
          <div style="padding:20px; text-align:center; font-size:13px; color:#888">Không có thông báo nào</div>
        <?php endif; ?>
        
      </div>
    </div>
  </div>
</div>
<script>
function toggleNoti(e) {
  e.stopPropagation();
  document.getElementById('notiDropdown').classList.toggle('show');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('#notiDropdown') && !e.target.closest('.noti-btn')) {
    document.getElementById('notiDropdown').classList.remove('show');
  }
});
function markAllRead() {
  let csrf = document.getElementById('globalCsrf').value;
  fetch('/admin/notifications/read-all', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: '_csrf=' + csrf })
  .then(()=>location.reload());
}
function markRead(id) {
  let csrf = document.getElementById('globalCsrf').value;
  fetch('/admin/notifications/' + id + '/read', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: '_csrf=' + csrf });
}
function deleteNoti(id, e) {
  e.stopPropagation();
  let csrf = document.getElementById('globalCsrf').value;
  if(confirm('Bạn có chắc muốn xóa thông báo này?')) {
    fetch('/admin/notifications/' + id + '/delete', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: '_csrf=' + csrf })
    .then(()=>location.reload());
  }
}
</script>