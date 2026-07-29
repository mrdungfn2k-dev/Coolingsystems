<?php if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin')): ?>
  <div style="padding:40px; text-align:center; background:#fff; border-radius:12px; border:1px solid #cbd5e1; margin:20px 0;">
    <h2 style="font-size:20px; font-weight:800; color:#b91c1c; margin:0 0 10px 0;">Đã Xảy Ra Lỗi Hệ Thống (500)</h2>
    <p style="font-size:13.5px; color:#475569; margin:0 0 16px 0;">Vui lòng làm mới trang hoặc liên hệ quản trị viên.</p>
    <a href="/admin" style="background:#0b1d3a; color:#fff; font-weight:700; padding:8px 16px; border-radius:6px; text-decoration:none; display:inline-block;">Về Trang Tổng Quan Admin</a>
  </div>
<?php else: ?>
  <?php require __DIR__ . '/../partials/head.php'; ?>
  <section class="block">
    <div class="wrap" style="text-align:center;padding:80px 20px">
      <h1 class="serif" style="font-size:72px;color:var(--red);margin-bottom:12px">500</h1>
      <h2 class="serif text-navy" style="margin-bottom:12px">Lỗi hệ thống</h2>
      <p class="text-muted" style="margin-bottom:24px">Đã xảy ra lỗi. Vui lòng thử lại sau.</p>
      <a href="/" class="btn btn-navy">Về trang chủ</a>
    </div>
  </section>
  <?php require __DIR__ . '/../partials/foot.php'; ?>
<?php endif; ?>
