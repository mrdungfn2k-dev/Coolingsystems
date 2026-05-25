<?php
// Usage: $inv = invoice data array, $formId = form ID string
$inv = $inv ?? [];
$formId = $formId ?? 'invoiceForm';
?>
<form id="<?= $formId ?>" onsubmit="return false">
  <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <div class="form-group">
    <label style="font-weight:700;font-size:13px;color:#555">Loại hình</label>
    <div style="display:flex;gap:16px;margin-top:4px;flex-wrap:wrap">
      <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;padding:8px 14px;background:#f8f9fc;border-radius:8px;border:1px solid #ddd;white-space:nowrap">
        <input type="radio" name="invoice_type" value="personal" <?= ($inv['invoice_type']??'personal')==='personal'?'checked':'' ?> style="width:18px;height:18px;margin:0;flex-shrink:0"> Cá nhân
      </label>
      <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;padding:8px 14px;background:#f8f9fc;border-radius:8px;border:1px solid #ddd;white-space:nowrap">
        <input type="radio" name="invoice_type" value="business" <?= ($inv['invoice_type']??'')==='business'?'checked':'' ?> style="width:18px;height:18px;margin:0;flex-shrink:0"> Tổ chức/Hộ KD
      </label>
    </div>
  </div>

  <!-- Personal fields -->
  <div class="inv-personal-fields" style="<?= ($inv['invoice_type']??'personal')==='business'?'display:none':'' ?>">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div class="form-group"><label>Tên người mua</label><input type="text" name="buyer_name" value="<?= e($inv['buyer_name']??'') ?>" placeholder="Tên người mua"></div>
      <div class="form-group"><label>Mã số thuế</label><input type="text" name="tax_code" value="<?= e($inv['tax_code']??'') ?>" placeholder="Nhập mã số thuế"></div>
    </div>
  </div>

  <!-- Business fields -->
  <div class="inv-business-fields" style="<?= ($inv['invoice_type']??'personal')!=='business'?'display:none':'' ?>">
    <div class="form-group"><label>Tên công ty</label><input type="text" name="company_name" value="<?= e($inv['company_name']??'') ?>" placeholder="Nhập tên công ty"></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div class="form-group"><label>Người đại diện pháp luật</label><input type="text" name="legal_representative" value="<?= e($inv['legal_representative']??'') ?>" placeholder="Người đại diện pháp luật"></div>
      <div class="form-group"><label>Mã số thuế</label><input type="text" name="tax_code_biz" value="<?= e($inv['tax_code']??'') ?>" placeholder="Nhập mã số thuế"></div>
    </div>
  </div>

  <div class="form-group"><label>Địa chỉ</label><input type="text" name="inv_address" value="<?= e($inv['address']??'') ?>" placeholder="Nhập địa chỉ"></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
    <div class="form-group"><label>Tỉnh/Thành phố</label><input type="text" name="province" value="<?= e($inv['province']??'') ?>" placeholder="Tìm Tỉnh/Thành phố"></div>
    <div class="form-group"><label>Phường/Xã</label><input type="text" name="ward" value="<?= e($inv['ward']??'') ?>" placeholder="Tìm Phường/Xã"></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
    <div class="form-group"><label>Số CCCD/CMND</label><input type="text" name="id_number" value="<?= e($inv['id_number']??'') ?>" placeholder="Nhập số CCCD/CMND (12 số)" maxlength="12"></div>
    <div class="form-group"><label>Số hộ chiếu</label><input type="text" name="passport" value="<?= e($inv['passport']??'') ?>" placeholder="VD: B1234567" maxlength="8"></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
    <div class="form-group"><label>Email</label><input type="email" name="inv_email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Email đúng định dạng" value="<?= e($inv['email']??'') ?>" placeholder="email@gmail.com"></div>
    <div class="form-group"><label>Số điện thoại</label><input type="tel" name="inv_phone" value="<?= e($inv['phone']??'') ?>" placeholder="0912345678" maxlength="10"></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
    <div class="form-group"><label>Ngân hàng</label>
      <select name="bank_name" style="width:100%"><option value="">Chọn ngân hàng</option>
      <?php foreach(['Vietcombank','Techcombank','BIDV','VietinBank','MB Bank','ACB','Sacombank','VPBank','TPBank','HDBank','SHB','SeABank','OCB','LienVietPostBank','MSB','Eximbank','VIB','ABBank','BacABank','NCB','PVcomBank','SCB','CIMB','UOB','BanVietBank','Agribank'] as $bk): ?>
      <option value="<?= $bk ?>" <?= ($inv['bank_name']??'')===$bk?'selected':'' ?>><?= $bk ?></option>
      <?php endforeach; ?></select>
    </div>
    <div class="form-group"><label>Số TK ngân hàng</label><input type="text" name="bank_account" value="<?= e($inv['bank_account']??'') ?>" placeholder="Nhập số tài khoản" maxlength="15"></div>
  </div>
</form>
