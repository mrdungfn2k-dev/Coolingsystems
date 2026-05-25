<?php require __DIR__.'/../partials/head.php'; ?>
<?php $profileComplete = !empty($user['full_name']) && !empty($user['phone']) && !empty($user['address']); ?>
<?php $cinv = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$user['id']]); ?>
<section class="block"><div class="wrap">
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
              <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" style="font-size:13px;max-width:250px" onchange="document.getElementById('avatarPreview').src=window.URL.createObjectURL(this.files[0])">
            </div>
          </div>
          <div class="form-group"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" readonly style="background:#f8f9fa;color:#888;cursor:not-allowed"></div>
          <div class="form-group"><label>Họ và tên <span class="req">*</span></label><input type="text" name="full_name" id="pf_name" value="<?= e($user['full_name']) ?>" required maxlength="20" placeholder="Nguyễn Văn A"></div>
          <div class="form-group"><label>Số điện thoại <span class="req">*</span></label><input type="tel" name="phone" id="pf_phone" value="<?= e($user['phone']??'') ?>" maxlength="10" required pattern="0[1-9][0-9]{8}"></div>
          <div class="form-group"><label>Địa chỉ nhận hàng <span class="req">*</span></label><input type="text" name="address" id="pf_address" value="<?= e($user['address']??'') ?>" required maxlength="100"><small style="color:#888;font-size:11px">Địa chỉ mặc định khi đặt hàng</small></div>
          <button type="submit" class="btn btn-gold btn-lg">Cập nhật thông tin</button>
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
            <div class="form-group"><label>Ngân hàng <span class="req">*</span></label>
              <select name="bank_name" id="inv_bank" required style="width:100%"><option value="">Chọn ngân hàng</option>
                <?php foreach(['Vietcombank','Techcombank','BIDV','VietinBank','MB Bank','ACB','Sacombank','VPBank','TPBank','HDBank','SHB','SeABank','OCB','LienVietPostBank','MSB','Eximbank','VIB','ABBank','BacABank','NCB','PVcomBank','SCB','CIMB','UOB','BanVietBank','Agribank'] as $bk): ?>
                <option value="<?= $bk ?>" <?= ($cinv['bank_name']??'')===$bk?'selected':'' ?>><?= $bk ?></option>
                <?php endforeach; ?>
              </select></div>
            <div class="form-group"><label>Số tài khoản <span class="req">*</span></label><input type="text" name="bank_account" id="inv_bankno" value="<?= e($cinv['bank_account']??'') ?>" required></div>
          </div>
          <div id="invErrors" style="color:#e74c3c;font-size:12px;margin-bottom:10px"></div>
          <button type="submit" class="btn btn-gold btn-lg" id="custInvBtn">Lưu thông tin hóa đơn</button>
          <div id="custInvStatus" style="margin-top:8px;font-size:12px"></div>
        </form>
      </div>
    </div>
  </div>
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
  blockChars($('pf_address'),/[!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g);

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
  onlyDigits($('inv_bankno'),20);

  // Bank account length based on bank
  var bankSel=$('inv_bank'),bankNo=$('inv_bankno');
  if(bankSel&&bankNo){bankSel.addEventListener('change',function(){
    var r=BANK_RULES[this.value];
    if(r){bankNo.maxLength=r[1];bankNo.placeholder=r[0]===r[1]?r[0]+' số':r[0]+'-'+r[1]+' số';bankNo.value=bankNo.value.slice(0,r[1]);}
    else{bankNo.maxLength=20;bankNo.placeholder='Nhập số tài khoản';}
  });bankSel.dispatchEvent(new Event('change'));}

  
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
  if(!$('inv_bank').value)errs.push('Chọn ngân hàng');
  var bkno=$('inv_bankno').value.trim();
  if(!bkno)errs.push('Số tài khoản bắt buộc');
  var bk=$('inv_bank').value;
  if(bk&&BANK_RULES[bk]){var r=BANK_RULES[bk];if(bkno.length<r[0]||bkno.length>r[1])errs.push('STK '+bk+' phải '+r[0]+'-'+r[1]+' số');}
  if(errs.length){$('invErrors').innerHTML=errs.map(function(x){return '⚠ '+x;}).join('<br>');return false;}
  $('invErrors').innerHTML='';
  var form=$('custInvoiceForm');var fd=new FormData(form);var data=new URLSearchParams();
  fd.forEach(function(v,k){if(k==='inv_address')data.append('address',v);else if(k==='inv_email')data.append('email',v);else if(k==='inv_phone')data.append('phone',v);else data.append(k,v);});
  $('custInvBtn').disabled=true;$('custInvStatus').innerHTML="<span style='color:#888'>Đang lưu...</span>";
  fetch('/customer/invoice-info',{method:'POST',body:data}).then(function(r){return r.json();}).then(function(d){
    $('custInvBtn').disabled=false;
    if(d.ok)$('custInvStatus').innerHTML="<span style='color:#27ae60'>✅ Đã lưu!</span>";
    else $('custInvStatus').innerHTML="<span style='color:#e74c3c'>Lỗi: "+(d.error||'')+"</span>";
    setTimeout(function(){$('custInvStatus').innerHTML='';},3000);
  }).catch(function(){$('custInvBtn').disabled=false;$('custInvStatus').innerHTML="<span style='color:#e74c3c'>Lỗi kết nối</span>";});
  return false;
}
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>