<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>Cài đặt hệ thống</h1><div id="settingsCardTools" style="display:flex;align-items:center"></div>
</div>

<?php /* flash is handled in head */ ?>

<style>
.settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:22px; align-items:start; }
@media(max-width:768px){ .settings-grid { grid-template-columns:1fr; } }
.settings-card { background:#fff; border:1px solid #e8edf3; border-radius:14px; padding:24px 26px; box-shadow:0 2px 12px rgba(15,35,66,0.05); }
.settings-card h3 { font-size:15px; font-weight:800; color:var(--navy); margin:0 0 20px; padding:0 0 14px 0; border-bottom:1px solid #eef1f6; display:flex; align-items:center; gap:9px; }
.settings-card .form-group { margin-bottom:16px; }
.settings-card label { display:block; font-size:13px; font-weight:700; color:#3a4658; margin-bottom:6px; }
.settings-card label small { font-weight:400; color:#8a93a3; }
.settings-card input[type=text], .settings-card input[type=url], .settings-card input[type=email], .settings-card input[type=password], .settings-card input[type=tel], .settings-card textarea, .settings-card select { width:100%; min-height:42px; border:1px solid #dde3ec; border-radius:9px; padding:0 13px; font-size:14px; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; background:#fff; }
.settings-card textarea { padding:10px 13px; line-height:1.5; }
.settings-card input:focus, .settings-card textarea:focus, .settings-card select:focus { border-color:var(--navy); outline:none; box-shadow:0 0 0 3px rgba(26,50,88,0.10); }
.settings-card .field-hint { font-size:12px; color:#8a93a3; margin-top:6px; }
.settings-logo-prev { display:inline-flex; align-items:center; justify-content:center; border-radius:10px; padding:12px 16px; margin-top:10px; min-height:60px; }
.qr-preview { width:180px; height:180px; border:2px dashed #dde3ec; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:12px 0; overflow:hidden; background:#f8fafc; cursor:pointer; transition:border-color .2s; }
.qr-preview:hover { border-color:var(--navy); }
.qr-preview img { width:100%; height:100%; object-fit:contain; }
.qr-preview .placeholder { text-align:center; color:#aab4c4; font-size:13px; }
.btn-save { background:var(--navy); color:#fff; border:none; border-radius:9px; padding:11px 26px; font-size:14px; font-weight:700; cursor:pointer; transition:all .15s; }
.btn-save:hover { background:#0b1f40; transform:translateY(-1px); box-shadow:0 6px 16px rgba(15,35,66,.2); }
.general-form { display:grid; grid-template-columns:1fr 1fr 1fr; gap:28px; align-items:stretch; }
.general-form > div { display:flex; flex-direction:column; }
.general-form .btn-save, .general-form .cs-file { margin-top:auto; }
@media(max-width:900px){ .general-form { grid-template-columns:1fr; } }
</style>

<div class="settings-grid">
  <!-- General Site Settings -->
  <div class="settings-card" style="grid-column: 1 / -1;">
    <h3>🛠 Cài đặt thông tin chung</h3>
    <form method="post" action="/admin/settings/general" enctype="multipart/form-data" class="general-form">
      <?= csrfField() ?>
      <div>
        <div class="form-group">
          <label>Tên công ty <small>(hiện trên hóa đơn bán hàng)</small></label>
          <input type="text" name="company_name" value="<?= htmlspecialchars(((dbGet("SELECT value FROM system_config WHERE key='company_name'") ?: [])['value'] ?? '') ?: 'CÔNG TY CỔ PHẦN HVAC CORPORATION VIỆT NAM') ?>" placeholder="Tên công ty xuất hóa đơn">
        </div>
        <div class="form-group">
          <label>Hotline tư vấn</label>
          <input type="text" name="site_phone" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='site_phone'")['value'] ?? '') ?>" placeholder="0987654321" pattern="0[1-9][0-9]{8}" title="Hotline phải là 10 chữ số, bắt đầu bằng số 0, tiếp theo là số từ 1-9" required oninput="let v = this.value.replace(/[^0-9]/g, ''); if(v.length > 0 && v[0] !== '0') v = ''; if(v.length > 1 && v[1] === '0') v = '0'; this.value = v;" maxlength="10">
        </div>
        <div class="form-group">
          <label>Tiêu đề Website Trang Chủ <small>(Thẻ trình duyệt Browser Tab)</small></label>
          <input type="text" name="site_meta_title" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='site_meta_title'")['value'] ?? 'Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng | Hệ Thống Làm Mát Ô Tô') ?>" placeholder="VD: Cooling — Phụ Tùng & Dịch Vụ Ô Tô Chính Hãng">
        </div>
        <button type="submit" class="btn-save">Lưu cài đặt chung</button>
      </div>
      <div>
        <div class="form-group">
          <label>Logo Website <small>(Định dạng PNG/SVG/JPG)</small></label>
          <?php $siteLogo = dbGet("SELECT value FROM system_config WHERE key='site_logo'")['value'] ?? ''; ?>
          <?php if($siteLogo): ?>
          <div style="background:#1a3258; padding:12px; border-radius:8px; display:inline-block; margin-top:8px;">
             <img src="/uploads/<?= htmlspecialchars($siteLogo) ?>" style="max-height:50px; display:block; object-fit:contain;">
          </div>
          <?php else: ?>
          <div style="font-size:12px; color:#888;">Chưa có logo tùy chỉnh. Đang dùng logo mặc định.</div>
          <?php endif; ?>
        </div>
        <input type="file" name="site_logo" accept="image/*" class="js-filepick" data-file-label="Chọn ảnh logo" style="display:block;">
      </div>
      <div>
        <div class="form-group">
          <label>Logo Footer <small>(Định dạng PNG)</small></label>
          <?php $footerLogoImg = dbGet("SELECT value FROM settings WHERE key='footer_logo_image'")['value'] ?? ''; ?>
          <?php if($footerLogoImg): ?>
          <div style="background:#fff; padding:12px; border-radius:8px; display:inline-block; margin-top:8px; border:1px solid #e2e8f0;">
             <img src="/uploads/<?= htmlspecialchars($footerLogoImg) ?>" style="max-height:60px; max-width:100%; display:block; object-fit:contain;">
          </div>
          <label style="display:flex; align-items:center; gap:6px; margin-top:10px; font-size:12px; color:#888; cursor:pointer;">
            <input type="checkbox" name="remove_footer_logo" value="1"> Xóa ảnh, hiển thị lại chữ ở chân trang
          </label>
          <?php else: ?>
          <div style="font-size:12px; color:#888;">Chưa có logo footer. Đang hiển thị chữ ở chân trang.</div>
          <?php endif; ?>
        </div>
        <input type="file" name="footer_logo" accept="image/*" class="js-filepick" data-file-label="Chọn ảnh logo" style="display:block;">
      </div>
    </form>
  </div>

  <!-- Hotline & Service Phone Numbers Management -->
  <?php
    $rawHotlines = dbGet("SELECT value FROM system_config WHERE key='hotline_list'")['value'] ?? '';
    $hotlineItems = !empty($rawHotlines) ? json_decode($rawHotlines, true) : null;
    if (empty($hotlineItems) || !is_array($hotlineItems)) {
        $hotlineItems = [
            ['label' => 'CSKH & Dịch vụ', 'phone' => '0705.0705.26'],
            ['label' => 'CSKH & Dịch vụ', 'phone' => '0705.0705.28'],
            ['label' => 'Kĩ thuật & Bảo Hành', 'phone' => '0704.0704.18'],
            ['label' => 'Bán Buôn', 'phone' => '0703.0703.21'],
            ['label' => 'Bán Buôn', 'phone' => '0703.0703.61'],
            ['label' => 'Bán lẻ', 'phone' => '0703.0703.15']
        ];
    }
  ?>
  <div class="settings-card" style="grid-column: 1 / -1;">
    <h3>📞 Quản lý Danh sách Hotline & Bộ phận hỗ trợ</h3>
    <p style="font-size:13.5px;color:#666;margin-bottom:18px">Thêm, sửa, xóa các số điện thoại hotline theo từng bộ phận (CSKH & Dịch vụ, Kĩ thuật & Bảo Hành, Bán Buôn, Bán lẻ...). Danh sách số điện thoại này sẽ tự động chạy trượt tự động sang phải mượt mà trên thanh Header trang chủ.</p>
    
    <form method="post" action="/admin/settings/hotlines" id="hotlineForm">
      <?= csrfField() ?>
      <div id="hotlineListContainer" style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px">
        <?php foreach ($hotlineItems as $idx => $item): ?>
        <div class="hotline-row" style="display:flex;gap:12px;align-items:center;background:#f8fafc;padding:12px 16px;border-radius:10px;border:1px solid #e2e8f0">
          <span style="color:#a0aec0;font-size:16px;font-weight:bold" title="STT"><?= $idx + 1 ?>.</span>
          <div style="flex:1">
            <label style="font-size:12px;color:#4a5568;margin-bottom:4px;font-weight:700">Tên bộ phận / Nhóm hỗ trợ</label>
            <input type="text" name="hotline_labels[]" value="<?= htmlspecialchars($item['label'] ?? '') ?>" placeholder="VD: CSKH & Dịch vụ" required style="width:100%">
          </div>
          <div style="flex:1">
            <label style="font-size:12px;color:#4a5568;margin-bottom:4px;font-weight:700">Số điện thoại hotline</label>
            <input type="text" name="hotline_phones[]" value="<?= htmlspecialchars($item['phone'] ?? '') ?>" placeholder="VD: 0705.0705.26" required style="width:100%">
          </div>
          <button type="button" onclick="this.closest('.hotline-row').remove()" style="background:#fff0f0;color:#e53e3e;border:1px solid #fed7d7;padding:9px 14px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;margin-top:20px">✕ Xóa</button>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="display:flex;gap:12px;align-items:center;justify-content:space-between">
        <button type="button" onclick="addHotlineRow()" style="background:#edf2f7;color:#2d3748;border:1px solid #cbd5e0;padding:10px 18px;border-radius:8px;cursor:pointer;font-size:13.5px;font-weight:700">+ Thêm số hotline mới</button>
        <button type="submit" class="btn-save">Lưu danh sách Hotline</button>
      </div>
    </form>
  </div>

  <script>
  function addHotlineRow() {
    const container = document.getElementById('hotlineListContainer');
    const count = container.querySelectorAll('.hotline-row').length + 1;
    const div = document.createElement('div');
    div.className = 'hotline-row';
    div.style = 'display:flex;gap:12px;align-items:center;background:#f8fafc;padding:12px 16px;border-radius:10px;border:1px solid #e2e8f0';
    div.innerHTML = `
      <span style="color:#a0aec0;font-size:16px;font-weight:bold">${count}.</span>
      <div style="flex:1">
        <label style="font-size:12px;color:#4a5568;margin-bottom:4px;font-weight:700">Tên bộ phận / Nhóm hỗ trợ</label>
        <input type="text" name="hotline_labels[]" placeholder="VD: CSKH & Dịch vụ" required style="width:100%">
      </div>
      <div style="flex:1">
        <label style="font-size:12px;color:#4a5568;margin-bottom:4px;font-weight:700">Số điện thoại hotline</label>
        <input type="text" name="hotline_phones[]" placeholder="VD: 0705.0705.26" required style="width:100%">
      </div>
      <button type="button" onclick="this.closest('.hotline-row').remove()" style="background:#fff0f0;color:#e53e3e;border:1px solid #fed7d7;padding:9px 14px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;margin-top:20px">✕ Xóa</button>
    `;
    container.appendChild(div);
  }
  </script>

  <!-- Admin Account -->
  <div class="settings-card" style="grid-column:1 / -1">
    <h3>🔑 Tài khoản quản trị</h3>
    <p style="font-size:13px;color:#666;margin-bottom:16px">Đổi tên hiển thị, email đăng nhập và mật khẩu của tài khoản quản trị. Phải nhập đúng mật khẩu hiện tại để xác nhận thay đổi.</p>
    <?php $__me = (function_exists('currentUser') ? currentUser() : null) ?: []; ?>
    <form method="post" action="/admin/settings/account" style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-end">
      <?= csrfField() ?>
      <div class="form-group" style="margin:0;flex:1;min-width:220px"><label>Tên hiển thị</label><input type="text" name="full_name" value="<?= htmlspecialchars($__me['full_name'] ?? '') ?>" required></div>
      <div class="form-group" style="margin:0;flex:1;min-width:220px"><label>Email đăng nhập</label><input type="email" name="login_email" value="<?= htmlspecialchars($__me['email'] ?? '') ?>" required></div>
      <div class="form-group" style="margin:0;flex:1;min-width:220px"><label>Mật khẩu mới <small>(để trống nếu không đổi)</small></label><input type="text" name="new_password" placeholder="Tối thiểu 6 ký tự"></div>
      <div class="form-group" style="margin:0;flex:1;min-width:220px"><label>Mật khẩu hiện tại <span style="color:#e74c3c">*</span></label><input type="password" name="current_password" required placeholder="Xác nhận để lưu"></div>
      <div style="width:100%"><button type="submit" class="btn-save">Lưu tài khoản quản trị</button></div>
    </form>
  </div>
  <?php $smtp = function_exists('smtpConfig') ? smtpConfig() : ['enabled'=>false,'host'=>'','port'=>587,'encryption'=>'tls','username'=>'','from_email'=>'','from_name'=>'']; ?>
  <div class="settings-card" style="grid-column:1 / -1">
    <h3>✉ Cấu hình SMTP gửi email</h3>
    <p style="font-size:13px;color:#666;margin:-5px 0 16px">Dùng SMTP của email doanh nghiệp, Gmail hoặc nhà cung cấp mail. Với Gmail, hãy dùng Mật khẩu ứng dụng 16 ký tự.</p>
    <form method="post" action="/admin/settings/smtp" class="general-form">
      <?= csrfField() ?>
      <div>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" name="smtp_enabled" value="1" <?= !empty($smtp['enabled'])?'checked':'' ?>> Bật gửi mail qua SMTP</label>
        <div class="form-group" style="margin-top:14px"><label>Máy chủ SMTP</label><input type="text" name="smtp_host" value="<?= htmlspecialchars($smtp['host']) ?>" placeholder="smtp.gmail.com"></div>
        <div class="form-group"><label>Cổng SMTP</label><input type="text" name="smtp_port" value="<?= (int)$smtp['port'] ?>" inputmode="numeric" placeholder="587"></div>
      </div>
      <div>
        <div class="form-group"><label>Bảo mật kết nối</label><select name="smtp_encryption"><option value="tls" <?= $smtp['encryption']==='tls'?'selected':'' ?>>STARTTLS (khuyến nghị, cổng 587)</option><option value="ssl" <?= $smtp['encryption']==='ssl'?'selected':'' ?>>SSL/TLS (cổng 465)</option><option value="none" <?= $smtp['encryption']==='none'?'selected':'' ?>>Không mã hóa</option></select></div>
        <div class="form-group"><label>Tài khoản SMTP</label><input type="email" name="smtp_username" value="<?= htmlspecialchars($smtp['username']) ?>" placeholder="email@domain.com"></div>
        <div class="form-group"><label>Mật khẩu ứng dụng <small>(để trống để giữ mật khẩu đã lưu)</small></label><input type="password" name="smtp_password" value="" autocomplete="new-password" placeholder="Mật khẩu ứng dụng SMTP"><?php if(!empty($smtp['password'])): ?><div class="field-hint" style="color:#15803d">Đã lưu mật khẩu ứng dụng. Ô này luôn để trống để bảo mật.</div><?php endif; ?></div>
      </div>
      <div>
        <div class="form-group"><label>Email người gửi</label><input type="email" name="smtp_from_email" value="<?= htmlspecialchars($smtp['from_email']) ?>" placeholder="email@domain.com"></div>
        <div class="form-group"><label>Tên người gửi</label><input type="text" name="smtp_from_name" value="<?= htmlspecialchars($smtp['from_name']) ?>" placeholder="COOLING PARTS & SERVICE"></div>
        <button type="submit" class="btn-save">Lưu cấu hình SMTP</button>
      </div>
    </form>
  </div>

  <?php $inventoryAlertEmail = dbGet("SELECT value FROM settings WHERE key='inventory_alert_email'")['value'] ?? ''; $inventoryAlertEnabled = (dbGet("SELECT value FROM settings WHERE key='inventory_alert_enabled'")['value'] ?? '0') === '1'; ?>
  <div class="settings-card" style="grid-column:1 / -1">
    <h3>🔔 Cảnh báo tồn kho qua email</h3>
    <p style="font-size:13px;color:#666;margin:-5px 0 16px">Khi tồn kho chạm hoặc thấp hơn mức tối thiểu, hệ thống gửi một email. Cảnh báo chỉ gửi lại sau khi tồn đã tăng cao hơn mức tối thiểu rồi giảm xuống lần nữa.</p>
    <form method="post" action="/admin/settings/inventory-alert" style="display:flex;gap:18px;flex-wrap:wrap;align-items:flex-end">
      <?= csrfField() ?>
      <label style="display:flex;align-items:center;gap:8px;margin:0 8px 11px 0;cursor:pointer"><input type="checkbox" name="inventory_alert_enabled" value="1" <?= $inventoryAlertEnabled?'checked':'' ?>> Bật gửi cảnh báo tồn kho</label>
      <div class="form-group" style="margin:0;flex:1;min-width:280px"><label>Email nhận cảnh báo</label><input type="email" name="inventory_alert_email" value="<?= htmlspecialchars($inventoryAlertEmail) ?>" placeholder="admin@example.com"></div>
      <button type="submit" class="btn-save">Lưu cấu hình cảnh báo</button>
    </form>
    <form method="post" action="/admin/settings/inventory-alert/test" style="margin-top:12px">
      <?= csrfField() ?>
      <button type="submit" class="btn-save" style="background:#fff;color:#1a3258;border:1px solid #1a3258">Gửi email kiểm tra</button>
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
        <input type="text" name="social_whatsapp" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_whatsapp'")['value'] ?? '') ?>" placeholder="https://wa.me/84xxxxxxxxx" pattern="https?://(wa\.me|(www\.)?whatsapp\.com)/.*" title="Phải là link WhatsApp (wa.me / whatsapp.com)">
      </div>
      <div class="form-group">
        <label>
          <img src="/uploads/zalo_icon.png" alt="Zalo" style="width:16px;height:16px;vertical-align:middle;margin-right:4px">
          Zalo Link <small>(VD: https://zalo.me/0705070526)</small>
        </label>
        <input type="text" name="social_zalo" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_zalo'")['value'] ?? 'https://zalo.me/0705070526') ?>" placeholder="https://zalo.me/0705070526" pattern="https?://.*" title="Link Zalo hợp lệ (VD: https://zalo.me/0705070526)">
      </div>
      <div class="form-group">
        <label>
          <svg style="vertical-align:middle;margin-right:4px" width="16" height="16" fill="#000" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1.04-.1z"/></svg>
          TikTok Link
        </label>
        <input type="text" name="social_tiktok" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_tiktok'")['value'] ?? '') ?>" placeholder="https://www.tiktok.com/@..." pattern="https?://((www|vt|vm|m)\.)?tiktok\.com/.*" title="Phải là link TikTok (tiktok.com)">
      </div>
      <div class="form-group">
        <label>
          <svg style="vertical-align:middle;margin-right:4px" width="16" height="16" fill="#1877F2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          Facebook Link
        </label>
        <input type="text" name="social_facebook" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='social_facebook'")['value'] ?? '') ?>" placeholder="https://www.facebook.com/..." pattern="https?://((www|m|web|business)\.)?(facebook\.com|fb\.com|fb\.me)/.*" title="Phải là link Facebook (facebook.com)">
      </div>
      <button type="submit" class="btn-save">Lưu liên kết mạng xã hội</button>
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
        <select name="payment_bank_name" class="js-cdd" style="width: 100%; height: 40px; border: 1px solid #ddd; border-radius: 8px; padding: 0 12px; font-size: 14px; background: #fff;">
          <?php $currentBank = dbGet("SELECT value FROM system_config WHERE key='payment_bank_name'")['value'] ?? ''; ?>
          <option value="">-- Chọn ngân hàng --</option>
          <option value="Vietcombank" <?= $currentBank == 'Vietcombank' ? 'selected' : '' ?>>Vietcombank (Ngân hàng TMCP Ngoại thương VN)</option>
          <option value="VietinBank" <?= $currentBank == 'VietinBank' ? 'selected' : '' ?>>VietinBank (Ngân hàng TMCP Công Thương VN)</option>
          <option value="BIDV" <?= $currentBank == 'BIDV' ? 'selected' : '' ?>>BIDV (Ngân hàng TMCP Đầu tư và Phát triển VN)</option>
          <option value="Agribank" <?= $currentBank == 'Agribank' ? 'selected' : '' ?>>Agribank (Ngân hàng NN&PTNT VN)</option>
          <option value="Techcombank" <?= $currentBank == 'Techcombank' ? 'selected' : '' ?>>Techcombank (Ngân hàng TMCP Kỹ Thương VN)</option>
          <option value="MB Bank" <?= $currentBank == 'MB Bank' ? 'selected' : '' ?>>MB Bank (Ngân hàng TMCP Quân Đội)</option>
          <option value="ACB" <?= $currentBank == 'ACB' ? 'selected' : '' ?>>ACB (Ngân hàng TMCP Á Châu)</option>
          <option value="VPBank" <?= $currentBank == 'VPBank' ? 'selected' : '' ?>>VPBank (Ngân hàng TMCP Việt Nam Thịnh Vượng)</option>
          <option value="Sacombank" <?= $currentBank == 'Sacombank' ? 'selected' : '' ?>>Sacombank (Ngân hàng TMCP Sài Gòn Thương Tín)</option>
          <option value="VIB" <?= $currentBank == 'VIB' ? 'selected' : '' ?>>VIB (Ngân hàng TMCP Quốc Tế VN)</option>
          <option value="TPBank" <?= $currentBank == 'TPBank' ? 'selected' : '' ?>>TPBank (Ngân hàng TMCP Tiên Phong)</option>
          <option value="SHB" <?= $currentBank == 'SHB' ? 'selected' : '' ?>>SHB (Ngân hàng TMCP Sài Gòn - Hà Nội)</option>
          <option value="SeABank" <?= $currentBank == 'SeABank' ? 'selected' : '' ?>>SeABank (Ngân hàng TMCP Đông Nam Á)</option>
          <option value="MSB" <?= $currentBank == 'MSB' ? 'selected' : '' ?>>MSB (Ngân hàng TMCP Hàng Hải VN)</option>
          <option value="LienVietPostBank" <?= $currentBank == 'LienVietPostBank' ? 'selected' : '' ?>>LienVietPostBank (Ngân hàng TMCP Bưu Điện Liên Việt)</option>
          <option value="HDBank" <?= $currentBank == 'HDBank' ? 'selected' : '' ?>>HDBank (Ngân hàng TMCP Phát triển TPHCM)</option>
          <option value="Nam A Bank" <?= $currentBank == 'Nam A Bank' ? 'selected' : '' ?>>Nam A Bank (Ngân hàng TMCP Nam Á)</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tên chủ tài khoản</label>
        <input type="text" name="payment_account_name" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_account_name'")['value'] ?? '') ?>" placeholder="NGUYEN VAN A">
      </div>
            <div class="form-group">
        <label>Mã tiền tố Nội dung chuyển khoản <small>(Ví dụ: CK)</small></label>
        <input type="text" name="payment_transfer_prefix" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_transfer_prefix'")['value'] ?? '') ?>" placeholder="Để trống nếu không cần">
      </div>
      <div class="form-group">
        <label>Số tài khoản</label>
        <input type="text" name="payment_account_number" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='payment_account_number'")['value'] ?? '') ?>" placeholder="0123456789">
      </div>
      <div class="form-group">
        <label>Ảnh mã QR thanh toán <small>(Click để thay đổi)</small></label>
        <?php $qrImg = dbGet("SELECT value FROM system_config WHERE key='payment_qr_image'")['value'] ?? ''; ?>
        <div style="position:relative; width: 180px;">
          <label class="qr-preview" for="qrUpload" style="margin: 12px 0 0 0;">
            <?php if($qrImg): ?>
              <img src="/uploads/qr/<?= htmlspecialchars($qrImg) ?>" alt="QR Code" id="qrPreviewImg">
            <?php else: ?>
              <div class="placeholder" id="qrPlaceholder">
                <div style="font-size:40px;margin-bottom:8px">📷</div>
                <div>Click để upload mã QR</div>
              </div>
            <?php endif; ?>
          </label>
          <button type="button" id="removeQrBtn" style="position:absolute; top:4px; right:4px; background:rgba(255,0,0,0.7); color:white; border:none; border-radius:50%; width:24px; height:24px; font-size:12px; cursor:pointer; display:<?= $qrImg ? 'block' : 'none' ?>;" onclick="removeQr()" title="Xóa ảnh mã QR">✕</button>
        </div>
        <input type="file" id="qrUpload" name="qr_image" accept="image/*" style="display:none" onchange="previewQr(this)">
        <input type="hidden" id="removeQrInput" name="remove_qr" value="0">
      </div>
      <button type="submit" class="btn-save" style="margin-top: 12px;">Lưu cài đặt thanh toán</button>
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
      document.getElementById('removeQrBtn').style.display = 'block';
      document.getElementById('removeQrInput').value = '0';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function removeQr() {
  var label = document.querySelector('.qr-preview');
  label.innerHTML = '<div class="placeholder" id="qrPlaceholder"><div style="font-size:40px;margin-bottom:8px">📷</div><div>Click để upload mã QR</div></div>';
  document.getElementById('qrUpload').value = '';
  document.getElementById('removeQrInput').value = '1';
  document.getElementById('removeQrBtn').style.display = 'none';
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
          <input type="text" name="contact_address" maxlength="100" value="<?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_address'")['value'] ?? '') ?>" placeholder="Số 11, ngõ 171, phố Sài Đồng, Phường Phúc Lợi, Thành phố Hà Nội, Việt Nam">
        </div>
        <div class="form-group">
          <label>Giờ làm việc</label>
          <textarea name="contact_hours" rows="2" maxlength="100" style="width:100%;border:1px solid #ddd;border-radius:8px;padding:10px 12px;font-size:14px;box-sizing:border-box;resize:vertical;min-height:40px;max-height:80px;"><?= htmlspecialchars(dbGet("SELECT value FROM system_config WHERE key='contact_hours'")['value'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="width:100%">
        <button type="submit" class="btn-save">Lưu thông tin liên hệ</button>
      </div>
    </form>
  </div>

    <!-- Footer Owner Information Settings -->
  <div class="settings-card" style="grid-column: 1 / -1; margin-top: 10px;">
    <h3>🏢 Quản lý thông tin đơn vị chủ quản (Footer)</h3>
    <p style="color:#666;font-size:13px;margin-bottom:16px">Các thông tin này sẽ hiển thị trực tiếp dưới Logo & Mô tả ở chân trang (Footer) và trên trang Thông tin chủ quản.</p>
    <form method="post" action="/admin/settings/footer-owner" style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
      <?= csrfField() ?>
      <div class="form-group" style="grid-column: 1 / -1;">
        <label>Tên đơn vị / Công ty chủ quản</label>
        <input type="text" name="footer_company_name" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_company_name'")['value'] ?? (dbGet("SELECT value FROM system_config WHERE key='company_name'")['value'] ?? 'CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM')) ?>" placeholder="CÔNG TY TNHH ĐẦU TƯ VÀ CÔNG NGHỆ AUTOPARTS VIỆT NAM" required>
      </div>
      <div class="form-group">
        <label>Mã số thuế / Giấy phép ĐKKD</label>
        <input type="text" name="footer_company_tax" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_company_tax'")['value'] ?? '0110325421') ?>" placeholder="0110325421">
      </div>
      <div class="form-group">
        <label>Hotline & Zalo liên hệ</label>
        <input type="text" name="footer_company_phone" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_company_phone'")['value'] ?? '0705.0705.26') ?>" placeholder="0705.0705.26">
      </div>
      <div class="form-group">
        <label>Email hỗ trợ</label>
        <input type="email" name="footer_company_email" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_company_email'")['value'] ?? 'cskh@cooling.vn') ?>" placeholder="cskh@cooling.vn">
      </div>
      <div class="form-group">
        <label>Mô tả ngắn ở Chân trang <small>(dưới Logo)</small></label>
        <input type="text" name="footer_desc" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_desc'")['value'] ?? 'Sàn TMĐT phụ tùng ô tô chính hãng — chuyên sâu hệ thống làm mát. Cung cấp phụ tùng uy tín cho hàng triệu khách hàng trên toàn quốc.') ?>" placeholder="Mô tả ngắn...">
      </div>
      <div class="form-group" style="grid-column: 1 / -1;">
        <label>Địa chỉ trụ sở chủ quản</label>
        <input type="text" name="footer_company_address" value="<?= htmlspecialchars(dbGet("SELECT value FROM settings WHERE key='footer_company_address'")['value'] ?? (dbGet("SELECT value FROM system_config WHERE key='contact_address'")['value'] ?? 'Số 11, ngõ 171, phố Sài Đồng, Phường Phúc Lợi, Thành phố Hà Nội, Việt Nam')) ?>" placeholder="Số 11, ngõ 171, phố Sài Đồng, Phường Phúc Lợi, Thành phố Hà Nội, Việt Nam" required>
      </div>
      <div style="grid-column: 1 / -1;">
        <button type="submit" class="btn-save">Lưu thông tin đơn vị chủ quản</button>
      </div>
    </form>
  </div>

  <!-- Newsletter / Promotion Settings -->
  <div class="settings-card" style="grid-column: 1 / -1;">
    <h3>📧 Cài đặt ưu đãi đăng ký</h3>
    <p style="color:#666;font-size:13px;margin-bottom:16px">Chỉnh sửa tiêu đề, nội dung, mã voucher và giá trị ưu đãi hiển thị ở phần đăng ký nhận ưu đãi (footer).</p>
    <a href="/admin/settings/newsletter" class="btn-save" style="display:inline-block;text-decoration:none;background:var(--navy);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:700;">Quản lý ưu đãi đăng ký</a>
  </div>

<script>
(function(){
  var cards = Array.prototype.slice.call(document.querySelectorAll(".settings-card"));
  var tools = document.getElementById("settingsCardTools");
  if(!cards.length || !tools) return;
  var key="settings-cards-hidden";
  var hidden=[]; try{ hidden=JSON.parse(localStorage.getItem(key)||"[]"); }catch(e){}
  var bar=document.createElement("div"); bar.className="tbl-coltools"; bar.style.margin="0";
  var btn=document.createElement("button"); btn.type="button"; btn.className="tbl-colbtn";
  btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9h18M3 15h18M10 3v18"/></svg> Hiển thị mục';
  var menu=document.createElement("div"); menu.className="tbl-colmenu";
  menu.addEventListener("click", function(ev){ ev.stopPropagation(); });
  var hd=document.createElement("div"); hd.className="tbl-colmenu-hd"; hd.innerHTML="<span>Hiển thị mục</span>";
  var x=document.createElement("button"); x.type="button"; x.className="tbl-colmenu-x"; x.innerHTML="&times;"; x.addEventListener("click", function(){ menu.classList.remove("open"); });
  hd.appendChild(x); menu.appendChild(hd);
  cards.forEach(function(card, i){
    var h=card.querySelector("h3"); var name=h?(h.textContent||"").trim():("Mục "+(i+1));
    var lbl=document.createElement("label");
    var cb=document.createElement("input"); cb.type="checkbox"; cb.checked=hidden.indexOf(i)===-1;
    cb.addEventListener("change", function(){
      card.style.display = cb.checked ? "" : "none";
      var h2=[]; try{ h2=JSON.parse(localStorage.getItem(key)||"[]"); }catch(e){}
      if(cb.checked){ h2=h2.filter(function(z){return z!==i}); } else if(h2.indexOf(i)===-1){ h2.push(i); }
      localStorage.setItem(key, JSON.stringify(h2));
    });
    lbl.appendChild(cb); lbl.appendChild(document.createTextNode(" "+name));
    menu.appendChild(lbl);
    if(hidden.indexOf(i)!==-1) card.style.display="none";
  });
  btn.addEventListener("click", function(e){ e.stopPropagation(); var o=menu.classList.contains("open"); document.querySelectorAll(".tbl-colmenu.open").forEach(function(m){m.classList.remove("open");}); if(!o) menu.classList.add("open"); });
  bar.appendChild(btn); bar.appendChild(menu); tools.appendChild(bar);
})();
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>