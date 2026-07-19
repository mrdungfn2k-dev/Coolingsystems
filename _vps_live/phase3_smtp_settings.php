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
