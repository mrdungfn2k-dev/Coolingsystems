<?php require __DIR__.'/../partials/dashboard-head.php';?>
<div class="dash-head">
  <div>
    <h1>Nhà cung cấp</h1>
    <p style="color:#667085;margin:4px 0 0;font-size:13px">Quản lý thông tin nhà cung cấp phục vụ đơn mua và công nợ.</p>
  </div>
</div>

<?php foreach(getFlash() as $x):?>
<div class="alert alert-<?=e($x['type'])?>"><?=e($x['message'])?></div>
<?php endforeach;?>

<?php if($canManage):?>
<div class="panel" style="padding:20px;margin-bottom:20px;background:#fff;border:1px solid #e6ebf1;border-radius:10px">
  <h2 style="font-size:16px;margin:0 0 16px;color:#1a3258">Thêm nhà cung cấp mới</h2>
  <form method="post" action="/admin/suppliers" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px">
    <?=csrfField()?>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tên nhà cung cấp <span style="color:#e11d48">*</span></label>
      <input name="name" required maxlength="160" placeholder="Công ty TNHH Phụ tùng..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Điện thoại liên hệ (10 số) <span style="color:#e11d48">*</span></label>
      <input type="tel" name="phone" required inputmode="numeric" maxlength="10" minlength="10" pattern="0[35789][0-9]{8}" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="VD: 0912345678" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Email liên hệ <span style="color:#e11d48">*</span></label>
      <input type="email" name="email" required maxlength="254" placeholder="VD: nhacungcap@gmail.com" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mã số thuế <span style="color:#e11d48">*</span></label>
      <input type="text" name="tax_code" required inputmode="numeric" minlength="10" maxlength="14" pattern="[0-9\-]{10,14}" oninput="this.value=this.value.replace(/[^0-9\-]/g,'')" placeholder="MST 10-13 số..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group" style="grid-column:span 2">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Địa chỉ trụ sở / kho <span style="color:#e11d48">*</span></label>
      <input name="address" required minlength="5" maxlength="300" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div style="grid-column: span 3; display:flex; justify-content:flex-end; margin-top:4px">
      <button class="btn btn-navy" style="padding:8px 20px">Tạo nhà cung cấp</button>
    </div>
  </form>
</div>
<?php endif;?>

<div class="panel" style="background:#fff;border:1px solid #e6ebf1;border-radius:10px;overflow:hidden">
  <table class="tbl" style="width:100%;border-collapse:collapse">
    <thead>
      <tr style="background:#f7f9fc;border-bottom:2px solid #e6ebf1">
        <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Mã</th>
        <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Nhà cung cấp</th>
        <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Điện thoại</th>
        <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Email</th>
        <th style="padding:11px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Mã số thuế</th>
        <th style="padding:11px 12px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $i):?>
      <tr style="border-top:1px solid #edf1f5">
        <td style="padding:11px 12px;font-family:monospace;font-weight:700"><?=e($i['code'])?></td>
        <td style="padding:11px 12px"><strong style="color:#1a3258"><?=e($i['name'])?></strong><div style="font-size:11px;color:#64748b;margin-top:2px"><?=e($i['address']?:'—')?></div></td>
        <td style="padding:11px 12px;font-size:13px;font-weight:600"><?=e($i['phone']?:'—')?></td>
        <td style="padding:11px 12px;font-size:13px;color:#0284c7"><?=e($i['email']?:'—')?></td>
        <td style="padding:11px 12px;font-size:13px;font-family:monospace"><?=e($i['tax_code']?:'—')?></td>
        <td style="padding:11px 12px;text-align:center">
          <?php if($canManage): ?>
          <button type="button" onclick="openEditSupplierModal(<?= (int)$i['id'] ?>, <?= e(json_encode($i['name'])) ?>, <?= e(json_encode($i['phone'])) ?>, <?= e(json_encode($i['email'])) ?>, <?= e(json_encode($i['tax_code'])) ?>, <?= e(json_encode($i['address'])) ?>)" class="btn btn-outline" style="padding:3px 8px;font-size:11px;margin-right:4px">✏ Sửa</button>
          <button type="button" onclick="openDeleteSupplierModal(<?= (int)$i['id'] ?>, <?= e(json_encode($i['name'])) ?>)" class="btn btn-outline" style="padding:3px 8px;font-size:11px;color:#dc2626">🗑 Xóa</button>
          <?php else: ?>
          —
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach;?>
      <?php if(!$items):?>
      <tr><td colspan="6" style="padding:30px;text-align:center;color:#9ca3af">Chưa có nhà cung cấp.</td></tr>
      <?php endif;?>
    </tbody>
  </table>
</div>

<!-- Modal Sửa Nhà cung cấp -->
<div id="editSupplierModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form id="editSupplierForm" method="post" action="" style="background:#fff;padding:24px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <?= csrfField() ?>
    <h3 style="margin:0 0 16px;color:#1a3258">Cập nhật thông tin Nhà cung cấp</h3>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tên nhà cung cấp <span style="color:#e11d48">*</span></label>
      <input type="text" id="edit_name" name="name" required maxlength="160" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Điện thoại liên hệ (10 số) <span style="color:#e11d48">*</span></label>
      <input type="tel" id="edit_phone" name="phone" required inputmode="numeric" maxlength="10" minlength="10" pattern="0[35789][0-9]{8}" oninput="this.value=this.value.replace(/\D/g,'')" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Email liên hệ <span style="color:#e11d48">*</span></label>
      <input type="email" id="edit_email" name="email" required maxlength="254" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mã số thuế <span style="color:#e11d48">*</span></label>
      <input type="text" id="edit_tax_code" name="tax_code" required inputmode="numeric" minlength="10" maxlength="14" pattern="[0-9\-]{10,14}" oninput="this.value=this.value.replace(/[^0-9\-]/g,'')" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Địa chỉ trụ sở / kho <span style="color:#e11d48">*</span></label>
      <input type="text" id="edit_address" name="address" required minlength="5" maxlength="300" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('editSupplierModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu thay đổi</button>
    </div>
  </form>
</div>

<!-- Modal Xóa Nhà cung cấp Yêu cầu Mật khẩu Admin -->
<div id="deleteSupplierModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form id="deleteSupplierForm" method="post" action="" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <?= csrfField() ?>
    <h3 style="margin:0 0 10px;color:#dc2626">⚠️ Xác nhận Xóa Nhà Cung Cấp</h3>
    <p style="font-size:13px;color:#4b5563;margin-bottom:14px">Bạn có chắc chắn muốn xóa nhà cung cấp <strong id="delete_supplier_name" style="color:#1a3258"></strong>?</p>
    
    <div style="margin-bottom:18px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#1a3258">Vui lòng nhập Mật khẩu tài khoản của bạn để xác thực <span style="color:#e11d48">*</span></label>
      <input type="password" name="admin_password" required placeholder="Nhập mật khẩu Admin..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('deleteSupplierModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy" style="background:#dc2626;border-color:#dc2626">Xác nhận Xóa</button>
    </div>
  </form>
</div>

<script>
function openEditSupplierModal(id, name, phone, email, tax_code, address) {
  document.getElementById('editSupplierForm').action = '/admin/suppliers/' + id + '/update';
  document.getElementById('edit_name').value = name || '';
  document.getElementById('edit_phone').value = phone || '';
  document.getElementById('edit_email').value = email || '';
  document.getElementById('edit_tax_code').value = tax_code || '';
  document.getElementById('edit_address').value = address || '';
  document.getElementById('editSupplierModal').style.display = 'flex';
}

function openDeleteSupplierModal(id, name) {
  document.getElementById('deleteSupplierForm').action = '/admin/suppliers/' + id + '/delete';
  document.getElementById('delete_supplier_name').innerText = name;
  document.getElementById('deleteSupplierModal').style.display = 'flex';
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php';?>