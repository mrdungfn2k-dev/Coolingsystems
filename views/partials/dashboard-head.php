<?php require __DIR__ . '/head-admin.php'; ?>
<?php
/* sidebar permission gating: admin sees all; staff sees only granted perms */
$__sbU = $user ?? (function_exists('currentUser') ? currentUser() : null);
$__isAdmin = $__sbU && (($__sbU['role'] ?? '') === 'admin');
$__isSuperadmin = $__sbU && !empty($__sbU['is_superadmin']);
$__perms = []; $__roleName = '';
if ($__sbU && (($__sbU['role'] ?? '') === 'staff')) {
    $__prs = dbAll("SELECT sr.name AS role_name, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=?", [$__sbU['id']]);
    $__names = [];
    foreach ($__prs as $__r) { $__a = json_decode($__r['permissions'] ?? '[]', true); if (is_array($__a)) $__perms = array_merge($__perms, $__a); if (!empty($__r['role_name'])) $__names[] = $__r['role_name']; }
    $__perms = array_values(array_unique($__perms));
    $__roleName = $__names ? implode(', ', $__names) : 'Nhân viên';
}
$sb = function($perm) use ($__isAdmin, $__perms, $__sbU) {
  if ($__isAdmin || in_array($perm, $__perms, true)) return true;
  return $__sbU && (($__sbU['role'] ?? '') === 'staff') && function_exists('rbacMenuCan') && rbacMenuCan((int)$__sbU['id'], $perm);
};
?>

<div class="dash">
  <aside class="dash-sidebar">
    <div class="who">
      <?php $__siteLogo = ((dbGet("SELECT value FROM system_config WHERE key='site_logo'") ?: [])['value'] ?? ''); ?>
      <?php if($__siteLogo): ?>
        <a href="/admin" style="display:block"><img src="/uploads/<?= e($__siteLogo) ?>" alt="CoolingSystem" style="max-width:170px;max-height:46px;object-fit:contain;display:block;margin-bottom:8px;border-radius:12px;background:#fff"></a>
      <?php else: ?>
        <div class="name"><?= e($user['full_name']) ?></div>
      <?php endif; ?>
      <div class="role"><?= $__isSuperadmin ? 'Super Admin' : ($__isAdmin ? 'Quản trị viên' : e($__roleName ?: 'Nhân viên')) ?></div>
    </div>
    <style>.dash-nav a{display:flex;align-items:center;gap:10px}.dash-nav a .sb-ic{width:18px;height:18px;flex-shrink:0;opacity:.62}.dash-nav a.active .sb-ic{opacity:.95}</style>
    <nav class="dash-nav">
      <?php if($sb('reports')): ?><a href="/admin" class="<?= isActive('/admin') ?>"><?= sbIcon('home') ?>Tổng quan</a><?php endif; ?>
      <?php if($sb('serials')||$sb('products')||$sb('inventory')||$sb('products_create')||$sb('categories')||$sb('brand_models')||$sb('brands')): ?>
      <div class="sb-section">SẢN PHẨM<span class="sb-sec-desc">Sản phẩm · Danh mục · Thương hiệu · Hãng xe</span></div>
      <?php if($sb('products')): ?><a href="/admin/products" class="<?= (startsWith(currentPath(),'/admin/products') && currentPath()!=='/admin/products/new')?'active':'' ?>"><?= sbIcon('box') ?>Quản lý sản phẩm</a>
      <a href="/admin/inventory" class="<?= startsWith(currentPath(),'/admin/inventory')?'active':'' ?>">
        <?= sbIcon('list') ?>Quản lý kho</a>
      <a href="/admin/stocktake" class="<?= startsWith(currentPath(),'/admin/stocktake')?'active':'' ?>">
        <?= sbIcon('list') ?>Kiểm kho</a>
      <a href="/admin/products/new" class="<?= currentPath()==='/admin/products/new'?'active':'' ?>"><?= sbIcon('plus') ?>+ Đăng SP mới</a><?php endif; ?>
      <?php if($sb('serials')): ?><a href="/admin/serials" class="<?= startsWith(currentPath(),'/admin/serials')?'active':'' ?>"><?= sbIcon('list') ?>Serial & Lô hàng</a><?php endif; ?>
      <?php if($sb('categories')): ?><a href="/admin/categories" class="<?= startsWith(currentPath(),'/admin/categories')?'active':'' ?>"><?= sbIcon('folder') ?>Danh mục</a><?php endif; ?>
      <?php if($sb('brand_models')): ?><a href="/admin/product-brands" class="<?= startsWith(currentPath(),'/admin/product-brands')?'active':'' ?>"><?= sbIcon('tag') ?>Thương hiệu</a><?php endif; ?>
      <?php if($sb('brands')): ?><a href="/admin/brands" class="<?= startsWith(currentPath(),'/admin/brands')?'active':'' ?>"><?= sbIcon('truck') ?>Hãng xe</a><?php endif; ?>
      <?php endif; ?>
      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'finance.cashbook.view'))): ?>
      <div class="sb-section">T&#192;I CH&#205;NH<span class="sb-sec-desc">Qu&#7929; &middot; Thu chi</span></div>
      <a href="/admin/cashbook" class="<?= startsWith(currentPath(),'/admin/cashbook')?'active':'' ?>"><?= sbIcon('list') ?>S&#7893; qu&#7929;</a>
      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'finance.bank_reconciliation.manage'))): ?>
      <a href="/admin/bank-reconciliation" class="<?= startsWith(currentPath(),'/admin/bank-reconciliation')?'active':'' ?>"><?= sbIcon('list') ?>&#272;&#7889;i so&#225;t NH/QR</a>
      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'finance.customer_debt.collect'))): ?>
      <a href="/admin/customer-debts" class="<?= startsWith(currentPath(),'/admin/customer-debts')?'active':'' ?>"><?= sbIcon('list') ?>C&#244;ng n&#7907; kh&#225;ch h&#224;ng</a>
      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.suppliers.view'))): ?><a href="/admin/suppliers" class="<?= startsWith(currentPath(),'/admin/suppliers')?'active':'' ?>"><?= sbIcon('list') ?>Nh&#224; cung c&#7845;p</a><?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.requests.create'))): ?><a href="/admin/purchase-requests" class="<?= startsWith(currentPath(),'/admin/purchase-requests')?'active':'' ?>"><?= sbIcon('list') ?>Y&#234;u c&#7847;u mua</a><?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.receipts.create'))): ?><a href="/admin/purchase-receipts" class="<?= startsWith(currentPath(),'/admin/purchase-receipts')?'active':'' ?>"><?= sbIcon('list') ?>Nh&#7853;n h&#224;ng PO</a><?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.quality.inspect'))): ?><a href="/admin/purchase-quality" class="<?= startsWith(currentPath(),'/admin/purchase-quality')?'active':'' ?>"><?= sbIcon('list') ?>Ki&#7875;m ch&#7845;t l&#432;&#7907;ng</a><?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.costs.allocate'))): ?><a href="/admin/purchase-costs" class="<?= startsWith(currentPath(),'/admin/purchase-costs')?'active':'' ?>"><?= sbIcon('list') ?>Chi ph&#237; mua</a><?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'purchasing.returns.create'))): ?><a href="/admin/supplier-returns" class="<?= startsWith(currentPath(),'/admin/supplier-returns')?'active':'' ?>"><?= sbIcon('list') ?>Tr&#7843; h&#224;ng NCC</a><?php endif; ?><?php endif; ?><?php endif; ?><?php endif; ?><?php endif; ?><?php endif; ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php if($sb('orders')||$sb('returns')||$sb('create_order')): ?>
      <div class="sb-section">VẬN HÀNH<span class="sb-sec-desc">Đơn hàng · Trả hàng</span></div>
      <?php if($sb('orders')): ?><a href="/admin/orders" class="<?= startsWith(currentPath(),'/admin/orders')?'active':'' ?>"><?= sbIcon('cart') ?>Đơn hàng</a><?php endif; ?>
      <?php if($sb('returns')): ?><a href="/admin/returns" class="<?= startsWith(currentPath(),'/admin/returns')?'active':'' ?>"><?= sbIcon('undo') ?>Trả hàng</a><?php endif; ?>
      <?php if($sb('warranties')): ?><a href="/admin/warranties" class="<?= startsWith(currentPath(),'/admin/warranties')?'active':'' ?>"><?= sbIcon('tool') ?>Bảo hành & Kỹ thuật</a><?php endif; ?>
      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'warranty.performance.view'))): ?><a href="/admin/warranties/performance" class="<?= currentPath()==='/admin/warranties/performance'?'active':'' ?>"><?= sbIcon('chart') ?>Hiệu suất kỹ thuật</a><?php endif; ?>
      <?php endif; ?>
      <?php if($__isAdmin||$sb('users')||$sb('staff')||$sb('content')): ?>
      <div class="sb-section">NHÂN SỰ<span class="sb-sec-desc">Phân quyền · Khách hàng · Nhân viên · Tin tức</span></div>
      <?php if($__isAdmin): ?><a href="/admin/staff" class="<?= (currentPath()==='/admin/staff' || startsWith(currentPath(),'/admin/staff/'))?'active':'' ?>"><?= sbIcon('shield') ?>Phân quyền NV</a><?php endif; ?>
      <?php if($sb('users')): ?><a href="/admin/users" class="<?= currentPath()==='/admin/users'?'active':'' ?>"><?= sbIcon('users') ?>Khách hàng</a>
      <?php if($sb('staff')): ?><a href="/admin/staff-accounts" class="<?= startsWith(currentPath(),'/admin/staff-accounts')?'active':'' ?>"><?= sbIcon('user') ?>Nhân viên</a><?php endif; ?>
      <?php if($__isSuperadmin): ?><a href="/admin/admin-accounts" class="<?= startsWith(currentPath(),'/admin/admin-accounts')?'active':'' ?>"><?= sbIcon('shield') ?>Quản trị viên</a><?php endif; ?><?php endif; ?>
      <?php if($sb('content')): ?><a href="/admin/news" class="<?= (startsWith(currentPath(),'/admin/news') && currentPath()!=='/admin/news/new')?'active':'' ?>"><?= sbIcon('filetext') ?>Tin tức</a>
      <a href="/admin/news/new" class="<?= currentPath()==='/admin/news/new'?'active':'' ?>"><?= sbIcon('plus') ?>+ Viết bài</a>
      <a href="/admin/content" class="<?= startsWith(currentPath(),'/admin/content')?'active':'' ?>"><?= sbIcon('file') ?>Quản lý trang tĩnh</a><?php endif; ?>
      <?php endif; ?>
      <?php if($sb('chat')||$sb('reviews')||$sb('contacts')): ?>
      <div class="sb-section">HỖ TRỢ<span class="sb-sec-desc">Tin nhắn · Đánh giá · Liên hệ</span></div>
      <?php if($sb('chat')): ?><a href="/admin/chat" class="sb-link"><?= sbIcon('message') ?><span>Tin nhắn</span></a><?php endif; ?>
      <?php if($sb('reviews')): ?><a href="/admin/reviews" class="<?= isActive('/admin/reviews') ?>"><?= sbIcon('star') ?>Kiểm duyệt đánh giá</a><?php endif; ?>
      <?php if($sb('contacts')): ?><a href="/admin/contacts" class="<?= startsWith(currentPath(),'/admin/contacts')!==false?'active':'' ?>"><?= sbIcon('mail') ?>Liên hệ khách</a><?php endif; ?>
      <?php endif; ?>
      <?php if($__isAdmin||$sb('stores')): ?>
      <div class="sb-section">CỬA HÀNG<span class="sb-sec-desc">Hệ thống · Loại chi nhánh</span></div>
      <?php if($sb('stores')): ?><a href="/admin/stores" class="<?= isActive('/admin/stores') ?>"><?= sbIcon('pin') ?>Hệ thống cửa hàng</a><?php endif; ?>
      <?php if($sb('stores')): ?><a href="/admin/branch-types" class="<?= isActive('/admin/branch-types') ?>"><?= sbIcon('list') ?>Loại chi nhánh cửa hàng</a><?php endif; ?>
      <?php endif; ?>
      <?php if($__isAdmin||$sb('tax_config')||$sb('promotions')||$sb('vouchers')): ?>
      <div class="sb-section">CẤU HÌNH<span class="sb-sec-desc">Khuyến mãi · Voucher · Vận chuyển · Cài đặt</span></div>
      <?php if($sb('promotions')): ?><a href="/admin/promotions" class="<?= isActive('/admin/promotions') ?>"><?= sbIcon('gift') ?>Khuyến mãi</a><?php endif; ?>
      <?php if($sb('vouchers')): ?><a href="/admin/vouchers" class="<?= startsWith(currentPath(),'/admin/vouchers')?'active':'' ?>"><?= sbIcon('ticket') ?>Voucher toàn sàn</a><?php endif; ?>
      <?php if($sb('tax_config')): ?><a href="/admin/settings/finance" class="<?= isActive('/admin/settings/finance') ?>"><?= sbIcon('truck') ?>Cấu hình Vận chuyển</a><?php endif; ?>
      <?php if($__isAdmin||$sb('settings')): ?><a href="/admin/settings" class="<?= isActive('/admin/settings') ?>"><?= sbIcon('gear') ?>Cài đặt hệ thống</a><?php endif; ?>
      <?php endif; ?>
      <a href="/admin/logout"><?= sbIcon('logout') ?>Đăng xuất</a>
    </nav>
  </aside>
  <div class="dash-main">
<?php
// Admin Breadcrumb
$_ap = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH);
$_abcMap = [
    '/admin' => 'Tổng quan',
    '/admin/products' => 'Quản lý sản phẩm',
    '/admin/products/new' => 'Đăng SP mới',
    '/admin/categories' => 'Danh mục',
    '/admin/product-brands' => 'Thương hiệu',
    '/admin/orders' => 'Đơn hàng',
    '/admin/orders/create' => 'Tạo đơn hàng',
    '/admin/returns' => 'Trả hàng',
    '/admin/staff' => 'Phân quyền NV',
    '/admin/users' => 'Người dùng',
    '/admin/brands' => 'Hãng xe',
    '/admin/promotions' => 'Khuyến mãi',
    '/admin/vouchers' => 'Voucher toàn sàn',
    '/admin/news' => 'Tin tức',
    '/admin/news/new' => 'Viết bài',
    '/admin/content' => 'Trang tĩnh',
    '/admin/chat' => 'Tin nhắn',
    '/admin/reviews' => 'Kiểm duyệt đánh giá',
    '/admin/contacts' => 'Liên hệ khách',
    '/admin/stores' => 'Hệ thống cửa hàng',
    '/admin/branch-types' => 'Loại chi nhánh cửa hàng',
    '/admin/settings/finance' => 'Cấu hình Vận chuyển',
    '/admin/settings' => 'Cài đặt hệ thống',
    '/admin/banners' => 'Banner 2',
];
$_abcLabel = $_abcMap[$_ap] ?? '';
$_abcParent = '';
if (empty($_abcLabel) && $_ap !== '/admin') {
    foreach ($_abcMap as $bk => $bv) {
        if (strpos($_ap, $bk.'/') === 0 && $bk !== '/admin') {
            $_abcLabel = $title ?? 'Chi tiết';
            $_abcParent = $bk;
            break;
        }
    }
    if (empty($_abcLabel)) $_abcLabel = $title ?? '';
}
if ($_ap !== '/admin' && !empty($_abcLabel)):
?>
<nav style="font-size:12px;color:var(--ink-3);margin-bottom:16px">
  <a href="/admin" style="color:var(--navy);text-decoration:none;font-weight:600">Tổng quan</a>
  <?php if (!empty($_abcParent) && isset($_abcMap[$_abcParent])): ?>
    <span style="margin:0 5px;color:#ccc">›</span>
    <a href="<?= $_abcParent ?>" style="color:var(--navy);text-decoration:none"><?= $_abcMap[$_abcParent] ?></a>
  <?php endif; ?>
  <span style="margin:0 5px;color:#ccc">›</span>
  <span style="color:var(--ink-2)"><?= htmlspecialchars($_abcLabel) ?></span>
</nav>
<?php endif; ?>
<?php
// === Admin BreadcrumbList JSON-LD Schema (for SEOQuake detection) ===
$_adminSchemaItems = [['name' => 'Tổng quan', 'url' => 'https://coolingsystems.vn/admin']];
if ($_ap !== '/admin' && !empty($_abcLabel)) {
    if (!empty($_abcParent) && isset($_abcMap[$_abcParent])) {
        $_adminSchemaItems[] = ['name' => $_abcMap[$_abcParent], 'url' => 'https://coolingsystems.vn' . $_abcParent];
    }
    $_adminSchemaItems[] = ['name' => strip_tags($_abcLabel), 'url' => $_canonicalUrl];
}
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    <?php foreach ($_adminSchemaItems as $si => $item): ?>
    {
      "@type": "ListItem",
      "position": <?= $si + 1 ?>,
      "name": "<?= addslashes($item['name']) ?>",
      "item": "<?= $item['url'] ?>"
    }<?= $si < count($_adminSchemaItems) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>

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
<div style="display:flex; justify-content:flex-end; padding:10px 20px; background:#fff; border-bottom:1px solid var(--line); margin:0 0 20px 0; align-items:center">
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
          <a href="<?= e($n['link']) ?>" class="noti-item <?= $n['is_read'] ? '' : 'unread' ?>" onclick="return goAdminNoti(<?= $n['id'] ?>, this)" style="padding-right: 36px;">
            <div class="noti-title"><?= e($n['title']) ?></div>
            <div class="noti-msg"><?= e($n['message']) ?></div>
            <span class="noti-time"><?= agoVN($n['created_at']) ?></span>
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
  .then(function(){ var b=document.getElementById('notiBadge'); if(b)b.remove(); document.querySelectorAll('#notiList .noti-item.unread').forEach(function(it){ it.classList.remove('unread'); }); });
}
function goAdminNoti(id, link){ try{markRead(id);}catch(e){} var d=document.getElementById('notiDropdown'); if(d)d.classList.remove('show'); var href=(link&&link.getAttribute('href'))||'/admin'; if(window.csNav){csNav(href);}else{location.href=href;} return false; }
function markRead(id) {
  let csrf = document.getElementById('globalCsrf').value;
  fetch('/admin/notifications/' + id + '/read', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: '_csrf=' + csrf });
}
async function deleteNoti(id, e) {
  e.stopPropagation(); var __b=e.currentTarget;
  let csrf = document.getElementById('globalCsrf').value;
  if(await csConfirmAsync('Bạn có chắc muốn xóa thông báo này?')) {
    fetch('/admin/notifications/' + id + '/delete', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: '_csrf=' + csrf })
    .then(function(){ var w=(__b&&__b.closest)?__b.closest('.noti-item-wrap'):null; if(w)w.remove(); var L=document.getElementById('notiList'); if(L&&!L.querySelector('.noti-item')){L.innerHTML='<div style="padding:24px;text-align:center;font-size:13px;color:#888">Chưa có thông báo nào</div>';} });
  }
}
</script>