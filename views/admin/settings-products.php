<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>Cấu hình hiển thị sản phẩm mới</h1>
</div>

<?php if (!empty($_SESSION['flash'])): foreach ((array)$_SESSION['flash'] as $f): ?>
  <div class="alert alert-<?= $f['type'] ?? 'info' ?>" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;<?= ($f['type']??'')=='success' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca' ?>"><?= e($f['msg'] ?? '') ?></div>
<?php endforeach; unset($_SESSION['flash']); endif; ?>

  <div style="max-width:500px;background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.06)">
    <form method="post" action="/admin/settings/products">
      <?= csrfField() ?>
      <div style="margin-bottom:20px">
        <label style="display:block;font-weight:700;margin-bottom:8px;color:#1a3258">Số ngày hiển thị "Sản phẩm mới"</label>
        <input type="number" name="new_product_days" value="<?= intval($days) ?>" min="1" max="90"
               style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:15px">
        <p style="font-size:12px;color:#888;margin-top:6px">Sản phẩm được đăng trong vòng N ngày gần nhất sẽ hiển thị trong tab "Sản phẩm mới" trên trang chủ.</p>
      </div>
      <button type="submit" style="background:linear-gradient(135deg,#c8a84e,#b8942e);color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">Lưu cấu hình</button>
    </form>
  </div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
