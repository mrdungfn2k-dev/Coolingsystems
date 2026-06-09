<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>

<div class="dash-head">
  <h1><?= isset($editUser['id']) ? 'Sửa tài khoản' : 'Thêm tài khoản' ?></h1>
  <a href="/admin/users" class="btn btn-outline-navy btn-sm">Quay lại</a>
</div>

<?php $inv = isset($editUser['id']) ? dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$editUser['id']]) : []; ?>
<?php $ctxRole = isset($editUser['id']) ? ($editUser['role'] ?? 'customer') : (in_array($_GET['role'] ?? '', ['customer','staff','admin']) ? $_GET['role'] : 'customer'); $isCustomerForm = ($ctxRole === 'customer'); ?>

<!-- 2 panels 50/50 -->
<div style="display:grid;grid-template-columns:<?= $isCustomerForm ? '1fr 1fr' : '1fr' ?>;gap:20px;align-items:start;">

  <!-- LEFT: Thông tin tài khoản -->
  <div class="panel">
    <div style="padding:14px 20px;border-bottom:1px solid var(--line);font-weight:700;font-size:15px;color:#1a3258">Thông tin tài khoản</div>
    <div class="panel-body">
      <form method="post" action="<?= isset($editUser['id']) ? '/admin/users/'.$editUser['id'].'/edit' : '/admin/users/new' ?>" id="userAccountForm">
        <?= csrfField() ?>
        <?php if(!$isCustomerForm): ?><div style="display:grid;grid-template-columns:1fr 1fr;gap:0 18px"><?php endif; ?>
        <div class="form-group">
          <label>Họ và tên <span class="req">*</span></label>
          <input type="text" name="full_name" id="ueNameF" value="<?= e($editUser['full_name']??'') ?>" required>
        </div>
        <div class="form-group">
          <label>Email <span class="req">*</span></label>
          <input type="email" name="email" id="ueEmailF" value="<?= e($editUser['email']??'') ?>" required <?= isset($editUser['id']) ? 'readonly style="background:#f8f9fa;color:#888;cursor:not-allowed"' : '' ?>>
        </div>
        <div class="form-group">
          <label>Số điện thoại</label>
          <input type="tel" name="phone" id="uePhoneF" pattern="0[0-9]{9}" minlength="10" title="SĐT bắt đầu từ 0, 10 số" oninput="this.value=this.value.replace(/[^0-9]/g,'')" value="<?= e($editUser['phone']??'') ?>" maxlength="10">
        </div>
        <div class="form-group">
          <label>Địa chỉ</label>
          <input type="text" name="address" id="ueAddrF" value="<?= e($editUser['address']??'') ?>">
        </div>
        <input type="hidden" name="role" value="<?= e($ctxRole) ?>">
        <?php if (!isset($editUser['id'])): ?>
        <div class="form-group">
          <label>Mật khẩu <span class="req">*</span></label>
          <input type="password" name="password" id="uePwdF" minlength="8" required placeholder="Tối thiểu 8 ký tự">
          <div id="pwdStrength" style="margin-top:6px;font-size:11px;color:#888"></div>
        </div>
        <?php else: ?>
        <div class="form-group">
          <label>Mật khẩu mới (để trống = giữ nguyên)</label>
          <input type="password" name="password" id="uePwdF" minlength="8" placeholder="Nhập để đổi mật khẩu">
          <div id="pwdStrength" style="margin-top:6px;font-size:11px;color:#888"></div>
        </div>
        <?php endif; ?>
        <div class="form-group">
          <label>Ghi chú nội bộ</label>
          <input type="text" name="notes" value="<?= e($editUser['notes']??'') ?>" placeholder="Ghi chú về tài khoản này">
        </div>
        <?php if(!$isCustomerForm): ?></div><?php endif; ?>
        <button type="submit" class="btn btn-gold" id="ueSubmitBtn">Lưu tài khoản</button>
      </form>

      <?php if (isset($editUser['id']) && $editUser['role'] === 'staff'): ?>
      <hr style="margin:24px 0">
      <h3 style="font-size:16px;color:#1a3258">Phân quyền nhân viên</h3>
      <p style="font-size:12px;color:#888;margin:4px 0 0">Tích một nhóm = trao toàn bộ quyền của nhóm đó (vd: <b>Quản lý sản phẩm</b> = Sản phẩm + Hãng xe + Thương hiệu + Danh mục). Nhân viên sẽ hiển thị ở trang <b>Phân quyền NV</b> theo các nhóm đã tích.</p>
      <?php $asgN = array_column(dbAll("SELECT sr.name FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=?", [$editUser['id']]), 'name'); $deptMap=['can_products'=>'Quản lý sản phẩm','can_orders'=>'Quản lý đơn hàng','can_content'=>'Quản lý nội dung','can_users'=>'Quản lý người dùng','can_staff'=>'Quản lý nhân viên','can_vouchers'=>'Quản lý voucher','can_reviews'=>'Kiểm duyệt đánh giá']; $perms=[]; foreach($deptMap as $dk=>$dn){ $perms[$dk]=in_array($dn,$asgN)?1:0; } ?>
      <form method="post" action="/admin/users/<?= $editUser['id'] ?>/permissions">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
          <?php $plist=['can_products'=>'Quản lý sản phẩm','can_orders'=>'Quản lý đơn hàng','can_content'=>'Quản lý nội dung','can_users'=>'Quản lý người dùng','can_staff'=>'Quản lý nhân viên','can_vouchers'=>'Quản lý voucher','can_reviews'=>'Kiểm duyệt đánh giá']; ?>
          <?php foreach ($plist as $key=>$label): ?>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px;border:1px solid #e0e6f0;border-radius:6px;font-size:13px;background:<?= !empty($perms[$key])?'#f0f4ff':'#fff' ?>">
            <input type="checkbox" name="<?= $key ?>" value="1" <?= !empty($perms[$key])?'checked':'' ?>>
            <?= $label ?>
          </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-navy btn-sm" style="margin-top:12px">Lưu phân quyền</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

<?php if ($isCustomerForm): ?>
  <!-- RIGHT: Thông tin xuất hóa đơn -->
  <div class="panel" id="invoice-section">
    <div style="padding:14px 20px;border-bottom:1px solid var(--line);font-weight:700;font-size:15px;color:#1a3258">Thông tin xuất hóa đơn</div>
    <div class="panel-body">
      <?php $formId = 'invoiceForm'; require __DIR__ . '/../partials/invoice-form.php'; ?>
      <?php if (isset($editUser['id'])): ?>
      <button type="button" class="btn btn-gold" id="invSaveBtn" onclick="saveInvoiceAdmin()" style="margin-top:10px">Lưu thông tin hóa đơn</button>
      <div id="invStatus" style="margin-top:8px;font-size:12px"></div>
      <?php else: ?>
      <p style="font-size:12px;color:#888;margin-top:8px">💡 Thông tin hóa đơn sẽ được lưu tự động sau khi tạo tài khoản.</p>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<script>
// Block special chars in name and address
document.getElementById("ueNameF").addEventListener("input",function(){
  this.value=this.value.replace(/[!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g,"");
});
document.getElementById("ueAddrF").addEventListener("input",function(){
  this.value=this.value.replace(/[!@#$%^&*()+={}\[\];:'"<>?\/\\|]/g,"");
});
// Phone: digits only, start 0, second digit 1-9
document.getElementById("uePhoneF").addEventListener("input",function(){
  this.value=this.value.replace(/[^0-9]/g,"").slice(0,10);
  if(this.value.length>0&&this.value[0]!=="0")this.value="";
  if(this.value.length>1&&!/^0[1-9]/.test(this.value))this.value=this.value[0];
});
// Password strength
var pwdEl=document.getElementById("uePwdF");
if(pwdEl){
  pwdEl.addEventListener("input",function(){
    var v=this.value;
    var checks=[/[A-Z]/.test(v),/[a-z]/.test(v),/[0-9]/.test(v),/[!@#$%^&*(),.?":{}|<>]/.test(v),v.length>=8];
    var ok=checks.filter(Boolean).length;
    var el=document.getElementById("pwdStrength");
    var msgs=["Chữ hoa","Chữ thường","Số","Ký tự đặc biệt","≥8 ký tự"];
    var missing=msgs.filter(function(_,i){return !checks[i];});
    if(v.length===0){el.textContent="";return;}
    if(ok===5){el.innerHTML="<span style='color:#27ae60'> Mật khẩu mạnh</span>";}
    else{el.innerHTML="<span style='color:#e74c3c'>Còn thiếu: "+missing.join(", ")+"</span>";}
  });
  pwdEl.closest("form").addEventListener("submit",function(e){
    var v=pwdEl.value;
    if(!v&&"<?= isset($editUser['id'])?'edit':'new' ?>"==="edit")return;
    var ok=[/[A-Z]/,/[a-z]/,/[0-9]/,/[!@#$%^&*(),.?":{}|<>]/].every(function(r){return r.test(v);})&&v.length>=8;
    if(!ok){e.preventDefault();alert("Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt!");}
  });
}

<?php if ($isCustomerForm): ?>
function saveInvoice(e) {
  e.preventDefault();
  var form = document.getElementById("invoiceForm");
  var fd = new FormData(form);
  var data = new URLSearchParams();
  data.append("_csrf", fd.get("_csrf"));
  data.append("invoice_type", fd.get("invoice_type")||"personal");
  data.append("buyer_name", fd.get("buyer_name")||"");
  data.append("tax_code", fd.get("tax_code")||"");
  data.append("address", fd.get("inv_address")||"");
  data.append("province", fd.get("province")||"");
  data.append("ward", fd.get("ward")||"");
  data.append("id_number", fd.get("id_number")||"");
  data.append("passport", fd.get("passport")||"");
  data.append("email", fd.get("inv_email")||"");
  data.append("phone", fd.get("inv_phone")||"");
  data.append("bank_name", fd.get("bank_name")||"");
  data.append("bank_account", fd.get("bank_account")||"");
  var uid = "<?= $editUser['id'] ?? 0 ?>";
  document.getElementById("invSaveBtn").disabled = true;
  document.getElementById("invStatus").innerHTML = "<span style='color:#888'>Đang lưu...</span>";
  fetch("/admin/users/"+uid+"/invoice-info", {method:"POST", body:data})
    .then(function(r){return r.json();}).then(function(d){
      document.getElementById("invSaveBtn").disabled = false;
      document.getElementById("invStatus").innerHTML = d.ok ? "<span style='color:#27ae60'>✅ Đã lưu!</span>" : "<span style='color:#e74c3c'>Lỗi</span>";
      setTimeout(function(){ document.getElementById("invStatus").innerHTML=""; }, 3000);
    }).catch(function(){
      document.getElementById("invSaveBtn").disabled = false;
      document.getElementById("invStatus").innerHTML = "<span style='color:#e74c3c'>Lỗi kết nối</span>";
    });
  return false;
}

// For NEW user: intercept account form, save invoice after account created
<?php if (!isset($editUser['id'])): ?>
document.getElementById("userAccountForm").addEventListener("submit", function(e){
  // Copy invoice fields into account form as hidden inputs
  var invForm = document.getElementById("invoiceForm");
  var fd = new FormData(invForm);
  var fields = {"inv_address":"inv_address","inv_email":"inv_email","inv_phone":"inv_phone",
    "invoice_type":"invoice_type","buyer_name":"buyer_name","tax_code":"tax_code",
    "province":"province","ward":"ward","id_number":"id_number","passport":"passport",
    "bank_name":"bank_name","bank_account":"bank_account"};
  for(var key in fields){
    var val = fd.get(key) || "";
    var existing = this.querySelector("input[name='"+fields[key]+"']");
    if(!existing){
      var inp = document.createElement("input");
      inp.type = "hidden"; inp.name = fields[key]; inp.value = val;
      this.appendChild(inp);
    }
  }
  // Store invoice data in sessionStorage to save after redirect
  var fd = new FormData(document.getElementById("invoiceForm"));
  var invData = {};
  fd.forEach(function(v,k){ invData[k]=v; });
  sessionStorage.setItem("pendingInvoice", JSON.stringify(invData));
});
<?php endif; ?>
<?php endif; ?>
</script>

<?php if ($isCustomerForm): ?>
<script src="/js/invoice-validation.js"></script>
<script>
setupInvoiceForm('invoiceForm');

function saveInvoiceAdmin() {
  if (!validateInvoiceForm('invoiceForm')) return;
  var form = document.getElementById('invoiceForm');
  var fd = new FormData(form);
  var data = new URLSearchParams();
  fd.forEach(function(v,k){
    if(k==='inv_address') data.append('address',v);
    else if(k==='inv_email') data.append('email',v);
    else if(k==='inv_phone') data.append('phone',v);
    else if(k==='tax_code_biz') { if(!data.has('tax_code')) data.append('tax_code',v); }
    else data.append(k,v);
  });
  // If business type, use tax_code_biz
  var invType = form.querySelector('input[name="invoice_type"]:checked');
  if(invType && invType.value==='business') {
    data.set('tax_code', fd.get('tax_code_biz')||'');
  }
  var uid = '<?= $editUser["id"] ?? 0 ?>';
  document.getElementById('invSaveBtn').disabled = true;
  document.getElementById('invStatus').innerHTML = '<span style="color:#888">Đang lưu...</span>';
  fetch('/admin/users/'+uid+'/invoice-info', {method:'POST', body:data})
    .then(function(r){return r.json();}).then(function(d){
      document.getElementById('invSaveBtn').disabled = false;
      document.getElementById('invStatus').innerHTML = d.ok ? '<span style="color:#27ae60">✅ Đã lưu!</span>' : '<span style="color:#e74c3c">Lỗi</span>';
      setTimeout(function(){ document.getElementById('invStatus').innerHTML=''; }, 3000);
    }).catch(function(){
      document.getElementById('invSaveBtn').disabled = false;
      document.getElementById('invStatus').innerHTML = '<span style="color:#e74c3c">Lỗi kết nối</span>';
    });
}
</script>
<?php endif; ?>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
