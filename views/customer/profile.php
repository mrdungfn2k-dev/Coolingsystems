<?php require __DIR__.'/../partials/head.php'; ?>
<?php $profileComplete = !empty($user['full_name']) && !empty($user['phone']) && !empty($user['address']); ?>
<?php $cinv = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$user['id']]); ?>
<section class="block profile-page"><div class="wrap">
<style id="profile-redesign">.profile-page .form-group input,.profile-page .form-group select,.profile-page .form-group textarea{border-radius:10px;border:1px solid #d6deea;padding:11px 14px}.profile-page .form-group input:focus,.profile-page .form-group select:focus,.profile-page .form-group textarea:focus{border-color:#1a3258;box-shadow:0 0 0 3px rgba(26,50,88,0.12)}.profile-page .form-group select{-webkit-appearance:none;-moz-appearance:none;appearance:none;padding-right:36px;cursor:pointer;background-color:#fff;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='3'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;background-size:11px}.profile-page input[type=radio]{accent-color:#1a3258;width:16px;height:16px;cursor:pointer}.profile-page input[type=radio]:focus{outline:none;box-shadow:none}.profile-page .btn{border-radius:8px}</style>
  <?php if (!$profileComplete): ?>
  <div style="background:#fff8e1;border-left:4px solid #f0c040;padding:12px 16px;font-size:13px;color:#7a5c00;margin-bottom:16px">
    Vui lòng cập nhật đầy đủ thông tin để có thể đặt hàng.
  </div>
  <?php endif; ?>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <div class="sec-card">
      <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:20px">Thông tin tài khoản</h1></div></div>
      <div class="panel-body">
        <form method="post" action="/customer/profile" enctype="multipart/form-data" id="profileForm">
          <?= csrfField() ?>
          <div class="form-group" style="display:flex;gap:16px;align-items:center;margin-bottom:20px">
            <div style="width:80px;height:80px;border-radius:50%;background:#f0f0f0;overflow:hidden;border:2px solid #ddd">
              <?php if (!empty($user['avatar'])): ?>
                <img src="/uploads/avatars/<?= e($user['avatar']) ?>" style="width:100%;height:100%;object-fit:cover" id="avatarPreview">
              <?php else: ?>
                <img src="/assets/images/default-avatar.png" style="width:100%;height:100%;object-fit:cover" id="avatarPreview" onerror="this.style.background='#e0e0e0'">
              <?php endif; ?>
            </div>
            <div>
              <label style="font-size:13px;font-weight:700;display:block;margin-bottom:4px">Ảnh đại diện</label>
              <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="js-filepick" data-file-label="Chọn ảnh" style="max-width:250px" onchange="document.getElementById('avatarPreview').src=window.URL.createObjectURL(this.files[0])">
<style>
.cs-file{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.cs-file-btn{display:inline-flex;align-items:center;gap:8px;padding:0 16px;height:38px;border:1px solid #1a3258;border-radius:8px;background:#1a3258;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s}
.cs-file-btn:hover{background:#0f2342}
.cs-file-name{font-size:13px;color:#566;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px}
.cs-file-name.empty{color:#9aa7bd;font-style:italic}
</style>
<script>
(function(){
  function enh(inp){
    if(inp.dataset.cfEnh)return; inp.dataset.cfEnh="1";
    var label=inp.getAttribute("data-file-label")||"Chọn ảnh";
    var wrap=document.createElement("div"); wrap.className="cs-file";
    var btn=document.createElement("button"); btn.type="button"; btn.className="cs-file-btn";
    btn.innerHTML='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="M21 15l-5-5L5 21"></path></svg><span>'+label+'</span>';
    var nm=document.createElement("span"); nm.className="cs-file-name empty"; nm.textContent="Chưa chọn ảnh";
    inp.parentNode.insertBefore(wrap,inp); inp.style.display="none";
    wrap.appendChild(btn); wrap.appendChild(nm); wrap.appendChild(inp);
    btn.addEventListener("click",function(){inp.click();});
    inp.addEventListener("change",function(){ if(inp.files&&inp.files.length){nm.textContent=inp.files[0].name;nm.classList.remove("empty");}else{nm.textContent="Chưa chọn ảnh";nm.classList.add("empty");} });
  }
  function r(){ document.querySelectorAll("input.js-filepick").forEach(enh); }
  if(document.readyState!=="loading") r(); else document.addEventListener("DOMContentLoaded",r);
})();
</script>
            </div>
          </div>
          <div class="form-group"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" readonly style="background:#f8f9fa;color:#888;cursor:not-allowed"></div>
          <div class="form-group"><label>Họ và tên <span class="req">*</span></label><input type="text" name="full_name" id="pf_name" value="<?= e($user['full_name']) ?>" required maxlength="20" placeholder="Nguyễn Văn A"></div>
          <div class="form-group"><label>Số điện thoại <span class="req">*</span></label><input type="tel" name="phone" id="pf_phone" value="<?= e($user['phone']??'') ?>" maxlength="10" required pattern="0[1-9][0-9]{8}"></div>
                    <?php
            $uAddr = $user['address'] ?? '';
            $addrParts = array_map('trim', explode(',', $uAddr));
            $pProv = ''; $pDist = ''; $pWard = ''; $pDetail = '';
            if (!empty($uAddr)) {
                if (count($addrParts) >= 4) {
                    $pProv = array_pop($addrParts);
                    $pDist = array_pop($addrParts);
                    $pWard = array_pop($addrParts);
                    $pDetail = implode(', ', $addrParts);
                } else {
                    $pDetail = $uAddr;
                }
            }
          ?>
          <div style="font-weight:700;font-size:14px;color:var(--navy);margin-bottom:12px;padding-top:12px;border-top:1px dashed #e0e6f0">Địa chỉ nhận hàng (Mặc định khi thanh toán)</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Tỉnh/Thành phố <span class="req">*</span></label>
              <input type="text" name="shipping_province" id="pf_prov" value="<?= e($pProv) ?>" required placeholder="VD: Hà Nội"></div>
            <div class="form-group"><label>Quận/Huyện <span class="req">*</span></label>
              <input type="text" name="shipping_district" id="pf_dist" value="<?= e($pDist) ?>" required placeholder="VD: Đống Đa"></div>
          </div>
          <div class="form-group"><label>Phường/Xã <span class="req">*</span></label>
            <input type="text" name="shipping_ward" id="pf_ward" value="<?= e($pWard) ?>" required placeholder="VD: Phương Liên"></div>
          <div class="form-group"><label>Địa chỉ cụ thể <span class="req">*</span></label>
            <input type="text" name="shipping_detail" id="pf_detail" value="<?= e($pDetail) ?>" required maxlength="100" placeholder="Số nhà, đường..."></div>
          <button type="submit" class="btn btn-navy btn-lg">Cập nhật thông tin</button>
        </form>
      </div>
    </div>
    <div class="sec-card" id="invoice-section">
      <div class="sec-head"><div class="title"><span class="bar"></span><h2 style="font-size:20px">Thông tin xuất hóa đơn</h2></div></div>
      <div class="panel-body">
        <form id="custInvoiceForm" onsubmit="return saveCustInvoice(event)">
          <?= csrfField() ?>
          <div class="form-group" style="margin-bottom:12px"><label style="font-weight:700;font-size:13px">Loại hình</label>
            <div style="display:flex;gap:16px;margin-top:4px">
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px"><input type="radio" name="invoice_type" value="personal" <?= ($cinv['invoice_type']??'personal')==='personal'?'checked':'' ?>> Cá nhân</label>
              <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px"><input type="radio" name="invoice_type" value="business" <?= ($cinv['invoice_type']??'')==='business'?'checked':'' ?>> Tổ chức/Hộ KD</label>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Tên người mua <span class="req">*</span></label><input type="text" name="buyer_name" id="inv_buyer" value="<?= e($cinv['buyer_name']??'') ?>" maxlength="50" required></div>
            <div class="form-group"><label>Mã số thuế / CCCD <span class="req">*</span></label><input type="text" name="tax_code" id="inv_tax" value="<?= e($cinv['tax_code']??'') ?>" maxlength="13" required placeholder="MST hoặc CCCD 12 số"></div>
          </div>
          <div class="form-group"><label>Địa chỉ <span class="req">*</span></label><input type="text" name="inv_address" id="inv_addr" value="<?= e($cinv['address']??'') ?>" maxlength="200" required></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Tỉnh/Thành phố <span class="req">*</span></label>
              <select name="province" id="inv_province" required style="width:100%"><option value="">-- Chọn --</option><option value="An Giang">An Giang</option><option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option><option value="Bạc Liêu">Bạc Liêu</option><option value="Bắc Giang">Bắc Giang</option><option value="Bắc Kạn">Bắc Kạn</option><option value="Bắc Ninh">Bắc Ninh</option><option value="Bến Tre">Bến Tre</option><option value="Bình Dương">Bình Dương</option><option value="Bình Định">Bình Định</option><option value="Bình Phước">Bình Phước</option><option value="Bình Thuận">Bình Thuận</option><option value="Cà Mau">Cà Mau</option><option value="Cao Bằng">Cao Bằng</option><option value="Cần Thơ">Cần Thơ</option><option value="Đà Nẵng">Đà Nẵng</option><option value="Đắk Lắk">Đắk Lắk</option><option value="Đắk Nông">Đắk Nông</option><option value="Điện Biên">Điện Biên</option><option value="Đồng Nai">Đồng Nai</option><option value="Đồng Tháp">Đồng Tháp</option><option value="Gia Lai">Gia Lai</option><option value="Hà Giang">Hà Giang</option><option value="Hà Nam">Hà Nam</option><option value="Hà Nội">Hà Nội</option><option value="Hà Tĩnh">Hà Tĩnh</option><option value="Hải Dương">Hải Dương</option><option value="Hải Phòng">Hải Phòng</option><option value="Hậu Giang">Hậu Giang</option><option value="Hòa Bình">Hòa Bình</option><option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option><option value="Hưng Yên">Hưng Yên</option><option value="Khánh Hòa">Khánh Hòa</option><option value="Kiên Giang">Kiên Giang</option><option value="Kon Tum">Kon Tum</option><option value="Lai Châu">Lai Châu</option><option value="Lâm Đồng">Lâm Đồng</option><option value="Lạng Sơn">Lạng Sơn</option><option value="Lào Cai">Lào Cai</option><option value="Long An">Long An</option><option value="Nam Định">Nam Định</option><option value="Nghệ An">Nghệ An</option><option value="Ninh Bình">Ninh Bình</option><option value="Ninh Thuận">Ninh Thuận</option><option value="Phú Thọ">Phú Thọ</option><option value="Phú Yên">Phú Yên</option><option value="Quảng Bình">Quảng Bình</option><option value="Quảng Nam">Quảng Nam</option><option value="Quảng Ngãi">Quảng Ngãi</option><option value="Quảng Ninh">Quảng Ninh</option><option value="Quảng Trị">Quảng Trị</option><option value="Sóc Trăng">Sóc Trăng</option><option value="Sơn La">Sơn La</option><option value="Tây Ninh">Tây Ninh</option><option value="Thái Bình">Thái Bình</option><option value="Thái Nguyên">Thái Nguyên</option><option value="Thanh Hóa">Thanh Hóa</option><option value="Thừa Thiên Huế">Thừa Thiên Huế</option><option value="Tiền Giang">Tiền Giang</option><option value="Trà Vinh">Trà Vinh</option><option value="Tuyên Quang">Tuyên Quang</option><option value="Vĩnh Long">Vĩnh Long</option><option value="Vĩnh Phúc">Vĩnh Phúc</option><option value="Yên Bái">Yên Bái</option>
              <?php if(!empty($cinv['province'])): ?><script>document.getElementById('inv_province').value="<?= e($cinv['province']) ?>";</script><?php endif; ?>
              </select></div>
            <div class="form-group"><label>Phường/Xã <span class="req">*</span></label><input type="text" name="ward" id="inv_ward" value="<?= e($cinv['ward']??'') ?>" required placeholder="Nhập phường/xã"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Số CCCD <span class="req">*</span></label><input type="text" name="id_number" id="inv_cccd" value="<?= e($cinv['id_number']??'') ?>" maxlength="12" required placeholder="12 số"></div>
            <div class="form-group"><label>Số hộ chiếu</label><input type="text" name="passport" id="inv_passport" value="<?= e($cinv['passport']??'') ?>" maxlength="8" placeholder="VD: A1234567"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="inv_email" id="inv_email" value="<?= e($cinv['email']??'') ?>" required placeholder="email@gmail.com" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Nhập đúng định dạng email"></div>
            <div class="form-group"><label>Số điện thoại <span class="req">*</span></label><input type="tel" name="inv_phone" id="inv_phone" value="<?= e($cinv['phone']??'') ?>" maxlength="10" required pattern="0[1-9][0-9]{8}" title="Nhập số điện thoại 10 số, bắt đầu bằng 0" placeholder="0912345678"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Ngân hàng</label>
              <select name="bank_name" id="inv_bank" style="width:100%"><option value="">Chọn ngân hàng</option>
                <?php foreach(['Vietcombank','Techcombank','BIDV','VietinBank','MB Bank','ACB','Sacombank','VPBank','TPBank','HDBank','SHB','SeABank','OCB','LienVietPostBank','MSB','Eximbank','VIB','ABBank','BacABank','NCB','PVcomBank','SCB','CIMB','UOB','BanVietBank','Agribank'] as $bk): ?>
                <option value="<?= $bk ?>" <?= ($cinv['bank_name']??'')===$bk?'selected':'' ?>><?= $bk ?></option>
                <?php endforeach; ?>
              </select></div>
            <div class="form-group"><label>Số tài khoản</label><input type="text" name="bank_account" id="inv_bankno" value="<?= e($cinv['bank_account']??'') ?>"></div>
          </div>
          <div id="invErrors" style="color:#e74c3c;font-size:12px;margin-bottom:10px"></div>
          <button type="submit" class="btn btn-navy btn-lg" id="custInvBtn">Lưu thông tin hóa đơn</button>
          <div id="custInvStatus" style="margin-top:8px;font-size:12px"></div>
        </form>
      </div>
    </div>
  </div>
  <!-- Khối Quản lý Thông tin Garage & Xe của tôi -->
  <div class="sec-card" style="margin-bottom:20px;">
    <div class="sec-head" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
      <div class="title"><span class="bar"></span><h2 style="font-size:18px; margin:0;">Thông tin Garage &amp; Danh sách Xe của tôi</h2></div>
      <button type="button" onclick="openAddCarModal()" class="btn btn-outline-navy" style="font-size:13px; padding:6px 14px;">+ Thêm xe mới</button>
    </div>
    <div class="panel-body">
      <?php if (!empty($user['is_verified_garage']) || !empty($user['garage_name'])): ?>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
          <div>
            <div style="font-weight:800; color:#1e3a8a; font-size:14.5px;">🏷️ TÀI KHOẢN ĐÃ XÁC THỰC GARA / ĐẠI LÝ</div>
            <div style="font-size:13px; color:#3b82f6; margin-top:2px;">Tên Gara: <strong><?= e($user['garage_name'] ?? 'Tài khoản Gara') ?></strong> — Áp dụng Bảng Giá Sỉ Gốc (Đơn 10 - 20 triệu VNĐ)</div>
          </div>
          <span style="background:#2563eb; color:#fff; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:20px; text-transform:uppercase;">ĐÃ DUYỆT GIÁ SỈ</span>
        </div>
      <?php else: ?>
        <div style="background:#fff8e1; border:1px solid #ffe082; border-radius:8px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
          <div>
            <div style="font-weight:700; color:#7a5c00; font-size:13.5px;">Bạn là Gara ô tô / Đại lý phụ tùng?</div>
            <div style="font-size:12.5px; color:#8d6e63;">Đăng ký thông tin Gara để nhận ngay Bảng giá chiết khấu sỉ gốc cho đơn từ 10 - 20 triệu.</div>
          </div>
          <button type="button" onclick="openGarageRegisterModal()" style="background:#c9a14a; color:#0b1d3a; font-weight:800; font-size:12.5px; padding:6px 14px; border-radius:6px; border:none; cursor:pointer;">Đăng ký Gara giá sỉ</button>
        </div>
      <?php endif; ?>

      <?php if (!empty($userGarages)): ?>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:13.5px; text-align:left;">
            <thead>
              <tr style="background:#0b1d3a; color:#fff;">
                <th style="padding:10px 12px; border-radius:6px 0 0 0;">Hãng xe</th>
                <th style="padding:10px 12px;">Dòng xe / Model</th>
                <th style="padding:10px 12px;">Năm SX</th>
                <th style="padding:10px 12px;">Động cơ / Ghi chú</th>
                <th style="padding:10px 12px; text-align:center;">Trạng thái</th>
                <th style="padding:10px 12px; border-radius:0 6px 0 0; text-align:center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($userGarages as $ug): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                  <td style="padding:10px 12px; font-weight:700; color:#0b1d3a;"><?= e($ug['brand_name'] ?? 'Hãng khác') ?></td>
                  <td style="padding:10px 12px; color:#1e293b;"><?= e($ug['model_name'] ?? 'Dòng khác') ?></td>
                  <td style="padding:10px 12px; color:#64748b;"><?= e($ug['year']) ?></td>
                  <td style="padding:10px 12px; color:#64748b;"><?= e($ug['label'] ?: ($ug['trim'] ?: '—')) ?></td>
                  <td style="padding:10px 12px; text-align:center;">
                    <?php if (!empty($ug['is_default'])): ?>
                      <span style="background:#dcfce7; color:#15803d; font-size:11px; font-weight:800; padding:2px 8px; border-radius:12px;">Mặc định</span>
                    <?php else: ?>
                      <span style="color:#94a3b8; font-size:12px;">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:10px 12px; text-align:center;">
                    <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                      <?php if (empty($ug['is_default'])): ?>
                        <button type="button" onclick="setDefaultCar(<?= $ug['id'] ?>)" style="background:#f1f5f9; color:#334155; border:1px solid #cbd5e1; border-radius:6px; padding:3px 8px; font-size:11.5px; cursor:pointer;" title="Đặt làm xe mặc định">⭐ Mặc định</button>
                      <?php endif; ?>
                      <button type="button" onclick="deleteCar(<?= $ug['id'] ?>)" style="background:#fef2f2; color:#ef4444; border:1px solid #fca5a5; border-radius:6px; padding:3px 8px; font-size:11.5px; font-weight:700; cursor:pointer;" title="Hủy / Xóa xe này">🗑️ Hủy / Xóa</button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:#64748b; font-size:13.5px; font-style:italic; margin:8px 0;">Chưa có xe nào trong danh sách. Hãy nhấn nút "+ Thêm xe mới" để thêm xe ô tô của bạn.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Thêm Xe Mới -->
  <div id="addCarModal" style="display:none; position:fixed; inset:0; background:rgba(11,29,58,0.75); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; max-width:460px; width:100%; padding:24px; box-shadow:0 20px 50px rgba(0,0,0,0.3); position:relative;">
      <button type="button" onclick="closeAddCarModal()" style="position:absolute; top:14px; right:14px; border:none; background:#f1f5f9; width:30px; height:30px; border-radius:50%; font-size:16px; font-weight:bold; cursor:pointer;">&times;</button>
      <h3 style="font-size:18px; font-weight:800; color:#0b1d3a; margin:0 0 16px 0;">Thêm xe mới vào hồ sơ</h3>
      
      <form id="addCarForm" onsubmit="submitAddCar(event)">
        <?= csrfField() ?>
        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">Hãng xe <span style="color:#ef4444">*</span></label>
          <select name="brand_id" required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:13.5px;">
            <option value="">-- Chọn Hãng xe --</option>
            <?php if (!empty($carBrands)): foreach ($carBrands as $cb): ?>
              <option value="<?= $cb['id'] ?>"><?= e($cb['name']) ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">Dòng xe <span style="color:#ef4444">*</span></label>
          <select name="model_id" required style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:13.5px;">
            <option value="">-- Chọn Dòng xe --</option>
            <?php if (!empty($carModels)): foreach ($carModels as $cm): ?>
              <option value="<?= $cm['id'] ?>"><?= e($cm['name']) ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">Năm sản xuất</label>
            <input type="number" name="year" value="<?= date('Y') ?>" min="1990" max="2027" style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:13.5px; box-sizing:border-box;">
          </div>
          <div>
            <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">Động cơ/Phiên bản</label>
            <input type="text" name="trim" placeholder="VD: 2.0 / 1.6 Turbo" style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:13.5px; box-sizing:border-box;">
          </div>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">Tên nhãn / Ghi chú xe</label>
          <input type="text" name="label" placeholder="VD: Xe khách 1 / Xe đưa đón..." style="width:100%; height:40px; border-radius:8px; border:1px solid #cbd5e1; padding:0 10px; font-size:13.5px; box-sizing:border-box;">
        </div>

        <div style="margin-bottom:16px;">
          <label style="font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
            <input type="checkbox" name="is_default" value="1" checked> Chọn làm xe mặc định
          </label>
        </div>

        <div id="addCarMsg" style="margin-bottom:12px; font-size:13px; display:none;"></div>

        <button type="submit" id="btnSubmitAddCar" style="width:100%; height:42px; background:#0b1d3a; color:#fff; border:none; border-radius:8px; font-weight:800; font-size:14px; cursor:pointer;">LƯU XE VÀO HỒ SƠ</button>
      </form>
    </div>
  </div>

  <script>
  function openAddCarModal(){ var m=document.getElementById('addCarModal'); if(m)m.style.display='flex'; }
  function closeAddCarModal(){ var m=document.getElementById('addCarModal'); if(m)m.style.display='none'; }
  function submitAddCar(e){
    e.preventDefault();
    var form=document.getElementById('addCarForm');
    var msg=document.getElementById('addCarMsg');
    var btn=document.getElementById('btnSubmitAddCar');
    var fd=new FormData(form);
    btn.disabled=true; btn.innerText='Đang lưu...';
    fetch('/customer/garage/add',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(res){
      btn.disabled=false; btn.innerText='LƯU XE VÀO HỒ SƠ';
      msg.style.display='block';
      if(res.ok){
        msg.style.color='#15803d'; msg.innerText='✅ Thêm xe thành công!';
        setTimeout(function(){ location.reload(); },1000);
      } else {
        msg.style.color='#b91c1c'; msg.innerText='⚠️ '+(res.error||'Có lỗi xảy ra');
      }
    })
    .catch(function(){ btn.disabled=false; btn.innerText='LƯU XE VÀO HỒ SƠ'; msg.style.display='block'; msg.style.color='#b91c1c'; msg.innerText='⚠️ Lỗi kết nối'; });
  }

  function deleteCar(id) {
    if(!confirm('Bạn có chắc chắn muốn xóa/hủy xe này khỏi hồ sơ không?')) return;
    var fd = new FormData();
    fd.append('_csrf', '<?= csrfToken() ?>');
    fd.append('id', id);
    fetch('/customer/garage/delete', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if(res.ok) {
        if(window.coolToastShow) coolToastShow('Đã xóa xe thành công!', '✅');
        setTimeout(function(){ location.reload(); }, 600);
      } else {
        alert(res.error || 'Không thể xóa xe');
      }
    })
    .catch(function(){ alert('Lỗi kết nối máy chủ'); });
  }

  function setDefaultCar(id) {
    var fd = new FormData();
    fd.append('_csrf', '<?= csrfToken() ?>');
    fd.append('id', id);
    fetch('/customer/garage/set-default', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if(res.ok) {
        if(window.coolToastShow) coolToastShow('Đã đặt làm xe mặc định!', '✅');
        setTimeout(function(){ location.reload(); }, 600);
      } else {
        alert(res.error || 'Không thể đặt mặc định');
      }
    })
    .catch(function(){ alert('Lỗi kết nối máy chủ'); });
  }
  </script>

  <div class="sec-card">
    <div class="sec-head"><div class="title"><span class="bar"></span><h2 style="font-size:18px">Đổi mật khẩu</h2></div></div>
    <div class="panel-body">
      <form method="post" action="/customer/change-password" style="max-width:500px" id="changePwdForm">
        <?= csrfField() ?>
        <div class="form-group"><label>Mật khẩu hiện tại <span class="req">*</span></label><input type="password" name="current_password" required></div>
        <div class="form-group"><label>Mật khẩu mới <span class="req">*</span></label><input type="password" name="new_password" id="newPwd" minlength="8" required>
          <div id="pwdBar" style="font-size:11px;margin-top:4px"></div></div>
        <div class="form-group"><label>Nhập lại mật khẩu mới <span class="req">*</span></label><input type="password" name="new_password2" id="newPwd2" required minlength="8"></div>
        <button type="submit" class="btn btn-outline-navy">Đổi mật khẩu</button>
      </form>
    </div>
  </div>
</div></section>
<style>@media(max-width:900px){div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr !important;}}</style>
<script>
var BANK_RULES={"Vietcombank":[9,13],"Techcombank":[14,14],"BIDV":[14,14],"VietinBank":[12,12],"MB Bank":[13,13],"ACB":[13,13],"Sacombank":[12,12],"VPBank":[10,10],"TPBank":[12,12],"HDBank":[12,12],"SHB":[13,13],"SeABank":[13,13],"OCB":[13,13],"LienVietPostBank":[13,13],"MSB":[14,14],"Eximbank":[14,14],"VIB":[15,15],"ABBank":[14,14],"BacABank":[14,14],"NCB":[13,13],"PVcomBank":[15,15],"SCB":[12,12],"CIMB":[14,14],"UOB":[10,10],"BanVietBank":[13,13],"Agribank":[13,13]};
(function(){
  // Helper
  function $(id){return document.getElementById(id);}
  function blockChars(el,regex){
    if(!el)return;
    var isComposing=false;
    el.addEventListener('compositionstart',function(){isComposing=true;});
    el.addEventListener('compositionend',function(){
      isComposing=false;
      // Defer to ensure the final value is settled
      var self=this;
      setTimeout(function(){self.value=self.value.replace(regex,'');},0);
    });
    el.addEventListener('input',function(e){
      // During IME composition, do NOT filter
      if(isComposing || e.isComposing) return;
      this.value=this.value.replace(regex,'');
    });
  }
  function onlyDigits(el,max){if(!el)return;el.addEventListener('input',function(){this.value=this.value.replace(/[^0-9]/g,'').slice(0,max);});}
  function phoneValidate(el){if(!el)return;el.addEventListener('input',function(){this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);if(this.value.length>0&&this.value[0]!=='0')this.value='';if(this.value.length>1&&!/^0[1-9]/.test(this.value))this.value=this.value[0];});}

  // Profile fields
  // Name validation: only on blur, not during typing (to support Vietnamese IME)
  (function(){
    var el=$('pf_name'); if(!el)return;
    el.addEventListener('blur',function(){
      this.value=this.value.replace(/[0-9!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g,'').trim();
    });
  })();
  phoneValidate($('pf_phone'));
  ['pf_prov','pf_dist','pf_ward','pf_detail'].forEach(function(id){blockChars($(id),/[!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g);});

  // Invoice fields
  // Buyer name: only validate on blur, not during typing
  (function(){
    var el=$('inv_buyer'); if(!el)return;
    el.addEventListener('blur',function(){
      this.value=this.value.replace(/[0-9!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g,'').trim();
    });
  })();
  onlyDigits($('inv_tax'),13);  // MST 10-13 digits or CCCD 12
  blockChars($('inv_addr'),/[!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g);
  onlyDigits($('inv_cccd'),12);
  // Passport: 1 uppercase + 7 digits
  var pp=$('inv_passport');
  if(pp)pp.addEventListener('input',function(){
    var v=this.value.toUpperCase();
    if(v.length>0&&!/^[A-Z]/.test(v)){v='';}
    if(v.length>1){v=v[0]+v.slice(1).replace(/[^0-9]/g,'');}
    this.value=v.slice(0,8);
  });
  phoneValidate($('inv_phone'));
  onlyDigits($('inv_bankno'),13);

  // Bank account length based on bank
  var bankNo=$('inv_bankno');
  if(bankNo){bankNo.maxLength=13;bankNo.placeholder='10-13 số';}

  
  // Email validation
  var emailEl=$('inv_email');
  if(emailEl) emailEl.addEventListener('input',function(){
    var v=this.value.trim();
    if(v && !/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(v)){
      this.style.borderColor='#e74c3c';
    } else {
      this.style.borderColor='';
    }
  });

  // Password strength
  var pwd=$('newPwd'),bar=$('pwdBar');
  if(pwd&&bar){pwd.addEventListener('input',function(){
    var v=this.value;if(!v){bar.innerHTML='';return;}
    var c=[/[A-Z]/.test(v),/[a-z]/.test(v),/[0-9]/.test(v),/[!@#$%^&*(),.?\":{}|<>]/.test(v),v.length>=8];
    var m=['Chữ hoa','Chữ thường','Số','Ký tự đặc biệt','≥8 ký tự'];
    var miss=m.filter(function(_,i){return !c[i];});
    bar.innerHTML=miss.length===0?"<span style='color:#27ae60'>✓ Mật khẩu mạnh</span>":"<span style='color:#e74c3c'>Còn thiếu: "+miss.join(', ')+"</span>";
  });}
  // Change pwd form validate
  var cpf=$('changePwdForm');
  if(cpf)cpf.addEventListener('submit',function(e){
    var v=pwd?pwd.value:'';
    if(v){var ok=[/[A-Z]/,/[a-z]/,/[0-9]/,/[!@#$%^&*(),.?\":{}|<>]/].every(function(r){return r.test(v);})&&v.length>=8;
    if(!ok){e.preventDefault();alert('Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, thường, số và ký tự đặc biệt!');return;}}
    if(pwd&&$('newPwd2')&&pwd.value!==$('newPwd2').value){e.preventDefault();alert('Mật khẩu mới không khớp!');}
  });
})();

function saveCustInvoice(e){
  e.preventDefault();
  var $=function(id){return document.getElementById(id);};
  var errs=[];
  var buyer=$('inv_buyer').value.trim();
  if(!buyer)errs.push('Tên người mua bắt buộc');
  if(buyer.length>50)errs.push('Tên tối đa 50 ký tự');
  if(/[0-9]/.test(buyer))errs.push('Tên không được chứa số');
  var tax=$('inv_tax').value.trim();
  if(!tax)errs.push('MST/CCCD bắt buộc');
  if(!/^\d{10,13}$/.test(tax))errs.push('MST phải 10-13 số');
  var addr=$('inv_addr').value.trim();
  if(!addr)errs.push('Địa chỉ bắt buộc');
  if(addr.length>200)errs.push('Địa chỉ tối đa 200 ký tự');
  if(!$('inv_province').value)errs.push('Chọn Tỉnh/TP');
  if(!$('inv_ward').value.trim())errs.push('Nhập Phường/Xã');
  var cccd=$('inv_cccd').value.trim();
  if(!cccd)errs.push('CCCD bắt buộc');
  if(!/^\d{12}$/.test(cccd))errs.push('CCCD phải đủ 12 số');
  var pp=$('inv_passport').value.trim();
  if(pp&&!/^[A-Z]\d{7}$/.test(pp))errs.push('Hộ chiếu: 1 chữ hoa + 7 số (VD: A1234567)');
  var email=$('inv_email').value.trim();
  if(!email)errs.push('Email bắt buộc');
  if(email&&!/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/i.test(email))errs.push('Email không đúng định dạng (ví dụ: ten@gmail.com)');
  var phone=$('inv_phone').value.trim();
  if(!phone)errs.push('SĐT bắt buộc');
  if(!/^0[1-9]\d{8}$/.test(phone))errs.push('SĐT 10 số, đầu số 01-09');
  var bkno=$('inv_bankno').value.trim();
  var bk=$('inv_bank').value;
  if(bkno && !/^\d{10,13}$/.test(bkno))errs.push('Số tài khoản phải 10-13 số');
  if(errs.length){$('invErrors').innerHTML=errs.map(function(x){return '⚠ '+x;}).join('<br>'); if(window.coolToastShow)coolToastShow(errs[0],'⚠️'); return false;}
  $('invErrors').innerHTML='';
  var form=$('custInvoiceForm');var fd=new FormData(form);var data=new URLSearchParams();
  fd.forEach(function(v,k){if(k==='inv_address')data.append('address',v);else if(k==='inv_email')data.append('email',v);else if(k==='inv_phone')data.append('phone',v);else data.append(k,v);});
  $('custInvBtn').disabled=true;$('custInvStatus').innerHTML="<span style='color:#888'>Đang lưu...</span>";
  fetch('/customer/invoice-info',{method:'POST',body:data}).then(function(r){return r.json();}).then(function(d){
    $('custInvBtn').disabled=false;
    if(d.ok){$('custInvStatus').innerHTML="<span style='color:#27ae60'>✅ Đã lưu!</span>"; if(window.coolToastShow)coolToastShow('Đã lưu thông tin hóa đơn!','✅');}
    else {$('custInvStatus').innerHTML="<span style='color:#e74c3c'>Lỗi: "+(d.error||'')+"</span>"; if(window.coolToastShow)coolToastShow(d.error||'Không thể lưu thông tin hóa đơn','⚠️');}
    setTimeout(function(){$('custInvStatus').innerHTML='';},3000);
  }).catch(function(){$('custInvBtn').disabled=false;$('custInvStatus').innerHTML="<span style='color:#e74c3c'>Lỗi kết nối</span>"; if(window.coolToastShow)coolToastShow('Lỗi kết nối, vui lòng thử lại','⚠️');});
  return false;
}
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>