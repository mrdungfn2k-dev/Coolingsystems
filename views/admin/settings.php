<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>⚙️ Cài đặt hệ thống</h1>
</div>

<?php /* flash is handled in head */ ?>

<style>
.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media(max-width: 768px) { .settings-grid { grid-template-columns: 1fr; } }
.settings-card { background: #fff; border: 1px solid #eaeaea; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.settings-card h3 { font-size: 16px; font-weight: 800; color: var(--navy); margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0; display: flex; align-items: center; gap: 8px; }
.settings-card .form-group { margin-bottom: 16px; }
.settings-card label { display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px; }
.settings-card label small { font-weight: 400; color: #888; }
.settings-card input[type=text], .settings-card input[type=url] { width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 8px; padding: 0 12px; font-size: 14px; transition: border-color 0.2s; box-sizing: border-box; }
.settings-card input:focus { border-color: var(--navy); outline: none; box-shadow: 0 0 0 3px rgba(26,50,88,0.1); }
.qr-preview { width: 180px; height: 180px; border: 2px dashed #ddd; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 12px 0; overflow: hidden; background: #f9f9f9; cursor: pointer; transition: border-color 0.2s; }
.qr-preview:hover { border-color: var(--navy); }
.qr-preview img { width: 100%; height: 100%; object-fit: contain; }
.qr-preview .placeholder { text-align: center; color: #aaa; font-size: 13px; }
.btn-save { background: var(--navy); color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.btn-save:hover { background: #0b1f40; transform: translateY(-1px); }
</style>

<div class="settings-grid">
  <!-- General Site Settings -->
  <div class="settings-card" style="grid-column: 1 / -1;">
    <h3>🛠 Cài đặt thông tin chung</h3>
    <form method="post" action="/admin/settings/general" enctype="multipart/form-data" style="display:flex; gap:24px; flex-wrap:wrap;">
      <?= csrfField() ?>
      <div style="flex:1; min-width:300px;">
        <div class="form-group">
          <label>Hotline tư vấn</label>
          <input type="text" name="site_phone" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='site_phone'")['value'] ?? '08 6585 6585') ?>" placeholder="08 6585 6585">
        </div>
        <button type="submit" class="btn-save" style="margin-top:10px;">💾 Lưu cài đặt chung</button>
      </div>
      <div style="flex:1; min-width:300px;">
        <div class="form-group">
          <label>Logo Website <small>(Định dạng PNG/SVG/JPG)</small></label>
          <?php $siteLogo = dbGet("SELECT value FROM system_config WHERE key='site_logo'")['value'] ?? ''; ?>
          <input type="file" name="site_logo" accept="image/*" style="margin-bottom:12px; display:block;">
          <?php if($siteLogo): ?>
          <div style="background:#1a3258; padding:12px; border-radius:8px; display:inline-block; margin-top:8px;">
             <img src="/uploads/<?= htmlspecialchars($siteLogo) ?>" style="max-height:50px; display:block; object-fit:contain;">
          </div>
          <?php else: ?>
          <div style="font-size:12px; color:#888;">Chưa có logo tùy chỉnh. Đang dùng logo mặc định.</div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
  <!-- Social Media Links -->
  <div class="settings-card">
    <h3>🌐 Liên kết mạng xã hội</h3>
    <p style="font-size:13px;color:#666;margin-bottom:16px">Cập nhật link liên kết cho các nút nổi trên trang khách hàng</p>
    <form method="post" action="/admin/settings/social">
      <?= csrfField() ?>
      <div class="form-group">
        <label>
          <svg style="vertical-align:middle;margin-right:4px" width="16" height="16" fill="#25D366" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp Link
        </label>
        <input type="text" name="social_whatsapp" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_whatsapp'")['value'] ?? '') ?>" placeholder="https://wa.me/84xxxxxxxxx">
      </div>
      <div class="form-group">
        <label>
          <svg style="vertical-align:middle;margin-right:4px" width="16" height="16" fill="#000" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1.04-.1z"/></svg>
          TikTok Link
        </label>
        <input type="text" name="social_tiktok" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_tiktok'")['value'] ?? '') ?>" placeholder="https://www.tiktok.com/@...">
      </div>
      <div class="form-group">
        <label>
          <svg style="vertical-align:middle;margin-right:4px" width="16" height="16" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          Facebook Link
        </label>
        <input type="text" name="social_facebook" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_facebook'")['value'] ?? '') ?>" placeholder="https://www.facebook.com/...">
      </div>
      <button type="submit" class="btn-save">💾 Lưu liên kết mạng xã hội</button>
    </form>
  </div>

  <!-- QR Payment Settings -->
  <div class="settings-card">
    <h3>💳 Cài đặt thanh toán QR</h3>
    <p style="font-size:13px;color:#666;margin-bottom:16px">Quản lý mã QR và thông tin tài khoản ngân hàng cho thanh toán trước</p>
    <form method="post" action="/admin/settings/payment" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div class="form-group">
        <label>Tên ngân hàng</label>
        <input type="text" name="payment_bank_name" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_bank_name'")['value'] ?? '') ?>" placeholder="Vietcombank, MB Bank...">
      </div>
      <div class="form-group">
        <label>Tên chủ tài khoản</label>
        <input type="text" name="payment_account_name" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_account_name'")['value'] ?? '') ?>" placeholder="NGUYEN VAN A">
      </div>
      <div class="form-group">
        <label>Số tài khoản</label>
        <input type="text" name="payment_account_number" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_account_number'")['value'] ?? '') ?>" placeholder="0123456789">
      </div>
      <div class="form-group">
        <label>Ảnh mã QR thanh toán <small>(Click để thay đổi)</small></label>
        <?php $qrImg = dbGet("SELECT value FROM system_config WHERE key='payment_qr_image'")['value'] ?? ''; ?>
        <label class="qr-preview" for="qrUpload">
          <?php if($qrImg): ?>
            <img src="/uploads/qr/<?= htmlspecialchars($qrImg) ?>" alt="QR Code" id="qrPreviewImg">
          <?php else: ?>
            <div class="placeholder" id="qrPlaceholder">
              <div style="font-size:40px;margin-bottom:8px">📷</div>
              <div>Click để upload mã QR</div>
            </div>
          <?php endif; ?>
        </label>
        <input type="file" id="qrUpload" name="qr_image" accept="image/*" style="display:none" onchange="previewQr(this)">
      </div>
      <button type="submit" class="btn-save">💾 Lưu cài đặt thanh toán</button>
    </form>
  </div>
</div>

<script>
function previewQr(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var img = document.getElementById('qrPreviewImg');
      var ph = document.getElementById('qrPlaceholder');
      if (!img) {
        var label = document.querySelector('.qr-preview');
        label.innerHTML = '<img id="qrPreviewImg" src="' + e.target.result + '" style="width:100%;height:100%;object-fit:contain">';
      } else {
        img.src = e.target.result;
        if(ph) ph.style.display = 'none';
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>


  
  <!-- Contact Info Settings -->
  <div class="settings-card" style="grid-column: 1 / -1;">
    <h3>📞 Thông tin liên hệ</h3>
    <p style="color:#666;font-size:12px;margin-bottom:16px">Cập nhật thông tin liên hệ hiển thị trên trang Liên hệ. Hotline sẽ tự động đồng bộ với trường "Hotline tư vấn" ở trên.</p>
    <form method="post" action="/admin/settings/contact-info" style="display:flex;gap:20px;flex-wrap:wrap;">
      <?= csrfField() ?>
      <div style="flex:1;min-width:250px;">
        <div class="form-group">
          <label>Hotline hỗ trợ</label>
          <input type="text" name="contact_hotline" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_hotline'")['value'] ?? '') ?>" placeholder="08 6585 6585" readonly style="background:#f0f0f0;cursor:not-allowed">
          <small style="color:#888;font-size:11px">Tự động đồng bộ từ Hotline tư vấn ở trên</small>
        </div>
        <div class="form-group">
          <label>Email liên hệ</label>
          <input type="email" name="contact_email" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_email'")['value'] ?? '') ?>" placeholder="cskh@cooling.vn">
        </div>
      </div>
      <div style="flex:1;min-width:250px;">
        <div class="form-group">
          <label>Địa chỉ</label>
          <input type="text" name="contact_address" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_address'")['value'] ?? '') ?>" placeholder="123 Trường Chinh...">
        </div>
        <div class="form-group">
          <label>Giờ làm việc</label>
          <textarea name="contact_hours" rows="2" style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px 12px;font-size:14px;box-sizing:border-box"><?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_hours'")['value'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="width:100%">
        <button type="submit" class="btn-save">💾 Lưu thông tin liên hệ</button>
      </div>
    </form>
  </div>

  <!-- Newsletter / Promotion Settings -->
  <div class="settings-card">
    <h3>📧 Cài đặt ưu đãi đăng ký</h3>
    <p style="color:#666;font-size:13px;margin-bottom:16px">Chỉnh sửa tiêu đề, nội dung, mã voucher và giá trị ưu đãi hiển thị ở phần đăng ký nhận ưu đãi (footer).</p>
    <a href="/admin/settings/newsletter" class="btn-save" style="display:inline-block;text-decoration:none;background:var(--navy);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:700;">⚙️ Quản lý ưu đãi đăng ký</a>
  </div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>