<?php require __DIR__.'/../partials/dashboard-head.php'; ?>

<div class="dash-head">
  <h1>Dashboard Nhân viên</h1>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px">
  <?php if(in_array('orders',$__staffPerms)): ?>
  <a href="/staff/orders" class="panel" style="padding:24px;text-decoration:none;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">📦</div>
    <div style="font-weight:700;color:var(--navy)">Đơn hàng</div>
  </a>
  <?php endif; ?>
  <?php if(in_array('create_order',$__staffPerms)): ?>
  <a href="/staff/orders/create" class="panel" style="padding:24px;text-decoration:none;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">✏️</div>
    <div style="font-weight:700;color:var(--navy)">Tạo đơn hộ</div>
  </a>
  <?php endif; ?>
  <?php if(in_array('products',$__staffPerms)): ?>
  <a href="/admin/products" class="panel" style="padding:24px;text-decoration:none;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">🛍️</div>
    <div style="font-weight:700;color:var(--navy)">Sản phẩm</div>
  </a>
  <?php endif; ?>
  <?php if(in_array('users',$__staffPerms)): ?>
  <a href="/admin/users" class="panel" style="padding:24px;text-decoration:none;text-align:center">
    <div style="font-size:28px;margin-bottom:8px">👤</div>
    <div style="font-weight:700;color:var(--navy)">Người dùng</div>
  </a>
  <?php endif; ?>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
