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
