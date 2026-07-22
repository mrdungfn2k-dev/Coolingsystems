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
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Điện thoại liên hệ</label>
      <input type="tel" name="phone" inputmode="numeric" maxlength="11" pattern="0[35789][0-9]{8,9}" oninput="this.value=this.value.replace(/\D/g,'')" placeholder="VD: 0912345678" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Email liên hệ</label>
      <input type="email" name="email" maxlength="254" placeholder="VD: nhacungcap@gmail.com" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mã số thuế</label>
      <input type="text" name="tax_code" inputmode="numeric" maxlength="14" pattern="[0-9\-]{10,14}" oninput="this.value=this.value.replace(/[^0-9\-]/g,'')" placeholder="MST 10-13 số..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>
    <div class="form-group" style="grid-column:span 2">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Địa chỉ trụ sở / kho</label>
      <input name="address" maxlength="300" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
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
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $i):?>
      <tr style="border-top:1px solid #edf1f5">
        <td style="padding:11px 12px;font-family:monospace;font-weight:700"><?=e($i['code'])?></td>
        <td style="padding:11px 12px"><strong style="color:#1a3258"><?=e($i['name'])?></strong><div style="font-size:11px;color:#64748b;margin-top:2px"><?=e($i['address']?:'—')?></div></td>
        <td style="padding:11px 12px;font-size:13px"><?=e($i['phone']?:'—')?></td>
        <td style="padding:11px 12px;font-size:13px;color:#0284c7"><?=e($i['email']?:'—')?></td>
        <td style="padding:11px 12px;font-size:13px;font-family:monospace"><?=e($i['tax_code']?:'—')?></td>
      </tr>
      <?php endforeach;?>
      <?php if(!$items):?>
      <tr><td colspan="5" style="padding:30px;text-align:center;color:#9ca3af">Chưa có nhà cung cấp.</td></tr>
      <?php endif;?>
    </tbody>
  </table>
</div>
<?php require __DIR__.'/../partials/dashboard-foot.php';?>