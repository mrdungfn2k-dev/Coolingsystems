<?php require __DIR__.'/../partials/head.php'; ?>
<style>
.co-grid{display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start}
@media(max-width:900px){.co-grid{grid-template-columns:1fr}}
.co-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:22px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.03)}
.co-card h3{font-size:15px;font-weight:800;color:var(--navy);margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #f0f4f8}
.pay-opt{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.pay-btn{border:2px solid #e0e6f0;border-radius:10px;padding:16px 12px;cursor:pointer;text-align:center;font-size:14px;transition:all 0.2s;background:#fff;display:flex;flex-direction:column;align-items:center;gap:8px}
.pay-btn:hover{border-color:var(--navy);background:#f0f4f8}
.pay-btn.active{border-color:var(--navy);background:#f0f4f8;box-shadow:0 0 0 3px rgba(26,50,88,0.1)}
.pay-btn .pay-name{font-weight:700;color:var(--navy);font-size:14px}
.pay-btn .pay-desc{font-size:11px;color:#888;line-height:1.4}
.pay-btn input[type=radio]{display:none}
.order-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--line);font-size:13px}
.order-item:last-child{border:none}
.order-item img{width:56px;height:56px;object-fit:cover;border-radius:6px;flex-shrink:0;border:1px solid #f0f0f0}
/* QR Panel */
#qrPanel{display:none;margin-top:20px;padding:24px;background:#f9fafb;border-radius:12px;border:1px solid #e0e6f0;text-align:center}
.qr-img-box{width:200px;height:200px;margin:16px auto;border:2px solid #ddd;border-radius:10px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center}
.qr-img-box img{width:100%;height:100%;object-fit:contain}
.transfer-info{background:#fff;border:1px solid #e0e6f0;border-radius:10px;padding:16px;text-align:left;margin:16px 0;font-size:13px}
.transfer-info .row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f5f5f5}
.transfer-info .row:last-child{border:none;padding-bottom:0}
.transfer-info .key{color:#888;font-weight:600}
.transfer-info .val{font-weight:800;color:var(--navy)}
.transfer-note{background:#fffbf0;border:1px solid #f0d070;border-radius:10px;padding:16px;margin:16px 0;font-size:13px;text-align:left}
.transfer-note .note-title{font-weight:800;color:#8a6000;margin-bottom:8px;font-size:13px}
.content-box{background:#fff;border:2px solid var(--navy);border-radius:8px;padding:10px 14px;font-weight:800;color:var(--navy);font-size:15px;font-family:monospace;letter-spacing:0.5px;margin:8px 0;word-break:break-all}
.btn-show-qr{width:100%;background:var(--navy);color:#fff;border:none;border-radius:8px;padding:14px;font-size:15px;font-weight:700;cursor:pointer;margin-top:16px;transition:all 0.2s}
.btn-show-qr:hover{background:#0b1f40;transform:translateY(-1px)}
.cod-info{margin-top:16px;padding:16px;background:#f0f9f0;border-radius:10px;border:1px solid #b2dfdb;display:none}
/* Modal Overlay */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-box { background:#fff; border-radius:14px; padding:24px; width:400px; max-width:90%; box-shadow:0 10px 40px rgba(0,0,0,0.2); max-height:90vh; overflow-y:auto; position:relative; text-align:center; }
.btn-confirm-qr { width:100%; background:#27ae60; color:#fff; border:none; border-radius:8px; padding:14px; font-size:15px; font-weight:700; cursor:pointer; margin-top:16px; transition:all 0.2s; }
.btn-confirm-qr:hover { background:#219653; }
.btn-close-modal { position:absolute; top:12px; right:12px; background:none; border:none; font-size:24px; cursor:pointer; color:#888; }
</style>

<section class="block"><div class="wrap" style="max-width:960px">
  <nav class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">›</span><a href="/customer/cart">Giỏ hàng</a><span class="sep">›</span><span>Thanh toán</span></nav>
  <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Thanh toán đơn hàng</h1></div></div>

  <form method="post" action="/customer/checkout" id="coForm" onsubmit="return handleCheckoutSubmit(event)">
    <?= csrfField() ?>
    <div class="co-grid">
      <div>
        <!-- Shipping address -->
        <div class="co-card">
          <h3>Địa chỉ nhận hàng</h3>
          <div class="form-row">
            <div class="form-group"><label>Họ và tên <span class="req">*</span></label>
              <input type="text" name="shipping_full_name" required value="<?= e(str_replace('Khách vãng lai', '', $user['full_name'] ?? '')) ?>" placeholder="Nguyễn Văn A" pattern="^[\p{L}\s]+$" title="Chỉ nhập chữ cái và khoảng trắng" oninput="this.value=this.value.replace(/[0-9!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
            <div class="form-group"><label>Số điện thoại <span class="req">*</span></label>
              <input type="tel" name="shipping_phone" id="coPhoneField" required value="<?=e($user['phone']??'')?>" placeholder="0901234567" pattern="^0[0-9]{9}$" inputmode="numeric" maxlength="10"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Tỉnh/Thành phố <span class="req">*</span></label>
              <input type="text" name="shipping_province" required placeholder="VD: Hà Nội" oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
            <div class="form-group"><label>Quận/Huyện <span class="req">*</span></label>
              <input type="text" name="shipping_district" required placeholder="VD: Đống Đa" oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
          </div>
          <div class="form-group"><label>Phường/Xã <span class="req">*</span></label>
            <input type="text" name="shipping_ward" required placeholder="VD: Phương Liên" oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
          <div class="form-group"><label>Địa chỉ cụ thể <span class="req">*</span></label>
            <input type="text" name="shipping_detail" required placeholder="Số nhà, tên đường..." oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:.<>?~`]/g,'')" maxlength="100"></div>
          <div class="form-group"><label>Ghi chú đơn hàng</label>
            <textarea name="customer_note" id="coNoteField" rows="2" maxlength="100" placeholder="Hướng dẫn giao hàng, yêu cầu đặc biệt..." oninput="if(this.value.length>100){this.value=this.value.substring(0,100);}document.getElementById('noteCounter').textContent=this.value.length+'/100 ký tự';document.getElementById('noteCounter').style.color=this.value.length>=100?'#e74c3c':'#999'"></textarea>
              <div id="noteCounter" style="font-size:11px;color:#999;text-align:right;margin-top:4px">0/100 ký tự</div></div>
        </div>

        <!-- Payment methods -->
        <div class="co-card">
          <h3>Phương thức thanh toán</h3>
          <div class="pay-opt">
            <label class="pay-btn active" id="payBtnPre" onclick="selectPay(this,'prepay')">
              <input type="radio" name="payment_method" value="bank_transfer" checked id="payPre">
              <div class="pay-name">Chuyển khoản (QR)</div>
              <div class="pay-desc">Thanh toán toàn bộ qua chuyển khoản ngân hàng</div>
            </label>
            <label class="pay-btn" id="payBtnCod" onclick="selectPay(this,'cod')">
              <input type="radio" name="payment_method" value="cod" id="payCod">
              <div class="pay-name">Thanh toán khi nhận</div>
              <div class="pay-desc">Trả tiền mặt khi nhận hàng</div>
            </label>
          </div>

          

          <!-- COD info -->
          <div class="cod-info" id="codBlock">
            <div style="font-weight:700;color:#1b5e20;font-size:14px;margin-bottom:6px">Thanh toán khi nhận hàng</div>
            <div style="font-size:13px;color:#388e3c">Bạn sẽ thanh toán tiền mặt sau khi nhận được hàng. Đơn hàng sẽ được xử lý và giao đến địa chỉ của bạn.</div>
          </div>

          <?php
          $qrImg = dbGet("SELECT value FROM system_config WHERE key='payment_qr_image'")['value'] ?? '';
          $bankName = dbGet("SELECT value FROM system_config WHERE key='payment_bank_name'")['value'] ?? '';
          $accName = dbGet("SELECT value FROM system_config WHERE key='payment_account_name'")['value'] ?? '';
          $accNum = dbGet("SELECT value FROM system_config WHERE key='payment_account_number'")['value'] ?? '';
          $tmpCode = strtoupper(substr(base_convert(time(), 10, 36), -4) . substr(md5(uniqid()), 0, 6));
          ?>
        </div>
      </div>

      <!-- Order summary -->
      <div>
        <div class="co-card">
          <h3>Đơn hàng (<?=count($items)?> sản phẩm)</h3>
          <?php $sub=0; foreach($items as $it): $sub+=$it['price']*$it['quantity']; ?>
          <div class="order-item">
            <?php if($it['main_image']):?>
              <img src="/uploads/products/<?=e($it['main_image'])?>" loading="lazy" alt="">
            <?php else:?>
              <div style="width:56px;height:56px;background:var(--bg-soft);border-radius:6px;flex-shrink:0"></div>
            <?php endif;?>
            <div style="flex:1">
              <div class="fw-600" style="font-size:12px;color:var(--navy)"><?=e(mb_substr($it['name'],0,50))?></div>
              <div class="fs-12 text-muted" style="margin-top:3px">x<?=$it['quantity']?></div>
              <div class="fs-12" style="margin-top:2px"><strong><?=vnd($it['price']*$it['quantity'])?></strong></div>
            </div>
          </div>
          <?php endforeach;?>
          <?php 
          $discountTotal = 0;
          $newsletterDiscount = 0;
          $qtyDiscount = 0;
          // Quantity discount from system config
          $cfgD = dbAll("SELECT key, value FROM system_config WHERE key IN ('discount_quantity_threshold','discount_quantity_percent')");
          $cfgDMap = []; foreach($cfgD as $cr) $cfgDMap[$cr['key']] = $cr['value'];
          $dqThreshold = intval($cfgDMap['discount_quantity_threshold'] ?? 0);
          $dqPercent = floatval($cfgDMap['discount_quantity_percent'] ?? 0);
          $totalQtyItems = 0; foreach($items as $qi) $totalQtyItems += $qi['quantity'];
          if ($dqThreshold > 0 && $totalQtyItems >= $dqThreshold && $dqPercent > 0) {
              $qtyDiscount = (int)ceil($sub * ($dqPercent / 100));
              $discountTotal += $qtyDiscount;
          }
          if ($user) {
              $isSub = dbGet("SELECT 1 FROM newsletter_subscribers WHERE email=?", [$user['email']]);
              $oCount = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$user['id']])['c'];
              if ($isSub && $oCount == 0) {
                  $newsletterDiscount = 100000;
                  if ($newsletterDiscount > $sub) $newsletterDiscount = $sub;
                  $discountTotal += $newsletterDiscount;
              }
          }
          
          if (!empty($_SESSION['cart_voucher'])) {
              $v = $_SESSION['cart_voucher'];
              $vDiscount = ($v['discount_type'] === 'percent') ? (int)ceil($sub * (($v['discount_amount'] ?? $v['discount_value'] ?? 0) / 100)) : ($v['discount_amount'] ?? $v['discount_value'] ?? 0);
              if ($vDiscount > $sub) $vDiscount = $sub;
              $discountTotal += $vDiscount;
          }
          
          $afterDiscount = $sub - $discountTotal;
          $ship = $afterDiscount >= 2000000 ? 0 : 30000; 
          $grand = $afterDiscount + $ship; 
          ?>
          <div style="border-top:2px solid var(--line);padding-top:14px;margin-top:8px">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#666;margin-bottom:8px">
              <span>Tạm tính</span><span><?=vnd($sub)?></span></div>
            <?php if ($newsletterDiscount > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#dc2626;margin-bottom:8px">
              <span>Ưu đãi bản tin (Đơn đầu)</span><span>- <?=vnd($newsletterDiscount)?></span></div>
            <?php endif; ?>
            <?php if ($qtyDiscount > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#e65100;margin-bottom:8px">
              <span>Giảm SL (<?= $totalQtyItems ?> SP ≥ <?= $dqThreshold ?>, -<?= $dqPercent ?>%)</span><span>- <?=vnd($qtyDiscount)?></span></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['cart_voucher'])): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#dc2626;margin-bottom:8px">
              <span>Voucher (<?= e($_SESSION['cart_voucher']['code']) ?>)</span><span>- <?=vnd($discountTotal - $newsletterDiscount - $qtyDiscount)?></span></div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#666;margin-bottom:10px">
              <span>Vận chuyển</span><span><?=$ship?vnd($ship):'Miễn phí'?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:800;color:var(--navy);padding-top:10px;border-top:1px solid #f0f0f0">
              <span>Tổng cộng</span><span id="grandDisplay"><?=vnd($grand)?></span></div>
          </div>
          <button type="submit" class="btn btn-gold btn-block btn-lg" style="margin-top:18px;font-size:16px;height:52px" id="submitBtn">
            Đặt hàng ngay
          </button>
          <div style="font-size:11px;color:var(--ink-3);text-align:center;margin-top:10px">
            Bằng cách đặt hàng, bạn đồng ý với <a href="/policies">Điều khoản sử dụng</a>
          </div>
        </div>
      </div>
    </div>
  </form>
</div></section>

<!-- QR Modal -->
<div class="modal-overlay" id="qrModal">
  <div class="modal-box">
    <button class="btn-close-modal" onclick="closeQRModal()" type="button">&times;</button>
    <div style="font-size:16px;font-weight:800;color:var(--navy);margin-bottom:4px">Quét mã QR để chuyển khoản</div>
    <div style="font-size:12px;color:#888;margin-bottom:12px">Sử dụng app ngân hàng để quét mã bên dưới</div>

    <?php if($qrImg): ?>
      <div class="qr-img-box"><img src="/uploads/qr/<?=htmlspecialchars($qrImg)?>" alt="QR Code thanh toán"></div>
    <?php else: ?>
      <div class="qr-img-box" style="background:#f5f5f5;flex-direction:column;gap:8px;color:#aaa;font-size:13px;padding:20px">
        <div style="font-size:32px;opacity:0.4">QR</div>
        <div>Chưa có mã QR</div>
      </div>
    <?php endif; ?>

    <div class="transfer-info">
      <div class="row"><span class="key">Ngân hàng</span><span class="val"><?=htmlspecialchars($bankName)?></span></div>
      <div class="row"><span class="key">Số tài khoản</span><span class="val"><?=htmlspecialchars($accNum)?></span></div>
      <div class="row"><span class="key">Chủ tài khoản</span><span class="val"><?=htmlspecialchars($accName)?></span></div>
      <div class="row"><span class="key">Số tiền</span><span class="val" id="amtDisplay" style="color:#e74c3c">—</span></div>
    </div>

    <div class="transfer-note">
      <div class="note-title">Nội dung chuyển khoản bắt buộc</div>
      <div style="color:#666;margin-bottom:8px;font-size:12px">Ghi chính xác nội dung sau khi chuyển tiền để xác nhận đơn hàng:</div>
      <div class="content-box" id="transferContent"><?=e($user['full_name']??'Khách')?> - <?=$tmpCode?></div>
      <div style="font-size:11px;color:#999;margin-top:4px">Định dạng: Họ tên - Mã đơn hàng</div>
      <button type="button" onclick="copyContent(this)" style="background:var(--navy);color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:12px;font-weight:700;cursor:pointer;margin-top:10px;transition:all 0.2s">Sao chép nội dung</button>
    </div>
    
    <button type="button" class="btn-confirm-qr" onclick="confirmQR()">Tôi đã chuyển khoản & Đặt hàng</button>
  </div>
</div>

<script>
// ── Checkout Phone Validation ──
function coValidatePhone(el) {
  var val = el.value;
  var errId = 'coPhoneErr';
  var existing = document.getElementById(errId);
  if (!existing) {
    existing = document.createElement('div');
    existing.id = errId;
    existing.style.cssText = 'color:#e74c3c;font-size:12px;margin-top:4px;font-weight:600';
    el.parentNode.appendChild(existing);
  }
  if (val.length > 0 && val[0] !== '0') {
    existing.textContent = '⚠ Số điện thoại phải bắt đầu bằng số 0';
    existing.style.display = 'block';
    el.style.borderColor = '#e74c3c';
  } else if (val.length > 0 && val.length < 10) {
    existing.textContent = '⚠ Số điện thoại phải đủ 10 chữ số (' + val.length + '/10)';
    existing.style.display = 'block';
    el.style.borderColor = '#e74c3c';
  } else if (val.length === 10 && val[0] === '0') {
    existing.style.display = 'none';
    el.style.borderColor = '#28a745';
  } else {
    existing.style.display = 'none';
    el.style.borderColor = '';
  }
}
// Block non-numeric on checkout phone
document.addEventListener('DOMContentLoaded', function() {
  var phoneF = document.getElementById('coPhoneField') || document.querySelector('[name="shipping_phone"]');
  if (phoneF) {
    phoneF.addEventListener('keypress', function(e) {
      if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
    });
    phoneF.addEventListener('paste', function(e) {
      e.preventDefault();
      var pasted = (e.clipboardData || window.clipboardData).getData('text');
      this.value = pasted.replace(/[^0-9]/g, '').substring(0, 10);
      coValidatePhone(this);
    });
    // Run initial validation if value exists
    phoneF.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value.length > 10) this.value = this.value.substring(0, 10);
      coValidatePhone(this);
    });
    if (phoneF.value) coValidatePhone(phoneF);
  }
});


// Voucher discount
var voucherAmt = <?= isset($vDiscount) ? $vDiscount : 0 ?>;
var grandTotal = <?= $grand ?>;
var depositAmount = grandTotal;  // Full amount, no deposit split
var remainingAmount = 0;
var userName = <?= json_encode($user['full_name'] ?? '') ?>;
var tmpOrderCode = '<?= $tmpCode ?>';

function selectPay(el, val) {
  document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  
  // Hide QR panel when switching
  document.getElementById('qrPanel').style.display = 'none';
  
  if (val === 'prepay') {
    document.getElementById('payPre').checked = true;
    document.getElementById('codBlock').style.display = 'none';
    document.getElementById('submitBtn').textContent = 'Đặt hàng ngay';
  } else {
    document.getElementById('payCod').checked = true;
    document.getElementById('codBlock').style.display = 'block';
    document.getElementById('submitBtn').textContent = 'Đặt hàng ngay';
  }
}

function handleCheckoutSubmit(e) {
    // Validate phone: must start with 0 and have 10 digits
    var phoneEl = document.getElementById('coPhoneField') || document.querySelector('[name="shipping_phone"]');
    if (phoneEl) {
      var pv = phoneEl.value.replace(/[^0-9]/g, '');
      if (pv.length === 0) {
        e.preventDefault();
        alert('Vui lòng nhập số điện thoại.');
        phoneEl.focus();
        return false;
      }
      if (pv[0] !== '0') {
        e.preventDefault();
        alert('Số điện thoại phải bắt đầu bằng số 0.');
        phoneEl.focus();
        return false;
      }
      if (pv.length !== 10) {
        e.preventDefault();
        alert('Số điện thoại phải có đúng 10 chữ số (đang có ' + pv.length + ' số).');
        phoneEl.focus();
        return false;
      }
    }

    // Validate address - no special characters
    var addrDetail = document.querySelector('[name="shipping_detail"]');
    if (addrDetail && /[!@#$%^&*()_+=\[\]{}|;:.<>?~`]/.test(addrDetail.value)) {
      e.preventDefault();
      alert('Địa chỉ cụ thể không được chứa ký tự đặc biệt (@, #, $, %, ...)');
      addrDetail.focus();
      return false;
    }
    // Validate note - max 100 chars
    var noteField = document.getElementById('coNoteField');
    if (noteField && noteField.value.length > 100) {
      e.preventDefault();
      alert('Ghi chú đơn hàng tối đa 100 ký tự (đang có ' + noteField.value.length + ' ký tự).');
      noteField.focus();
      return false;
    }

  if (document.getElementById('payPre').checked && !window.qrConfirmed) {
    e.preventDefault();
    // Validate required fields natively
    if (!document.getElementById('coForm').reportValidity()) return false;
    updateTransferInfo();
    document.getElementById('qrModal').classList.add('show');
    return false;
  }
  return true;
}

function confirmQR() {
  window.qrConfirmed = true;
  document.getElementById('coForm').submit();
}

function closeQRModal() {
  document.getElementById('qrModal').classList.remove('show');
}

function updateTransferInfo() {
  var isPrepay = document.getElementById('payPre').checked;
  var displayAmt = isPrepay ? depositAmount : grandTotal;
  var amt = new Intl.NumberFormat('vi-VN').format(displayAmt) + ' đ';
  var el = document.getElementById('amtDisplay');
  if (el) {
    el.textContent = amt;
    // Show breakdown
    if (isPrepay) {
      var remaining = new Intl.NumberFormat('vi-VN').format(remainingAmount) + ' đ';
      el.innerHTML = amt + '<div style="font-size:11px;color:#059669;font-weight:500;margin-top:3px">Thanh toán toàn bộ qua chuyển khoản)</div>';
    }
  }
  
  var nameEl = document.querySelector('input[name="shipping_full_name"]');
  var name = nameEl ? nameEl.value.trim() : userName;
  if (!name) name = userName;
  var content = name + ' - ' + tmpOrderCode;
  var box = document.getElementById('transferContent');
  if (box) box.textContent = content;
}

function copyContent(btn) {
  var text = document.getElementById('transferContent').textContent;
  navigator.clipboard.writeText(text).then(function() {
    btn.textContent = 'Đã sao chép!';
    btn.style.background = '#27ae60';
    setTimeout(function() {
      btn.textContent = 'Sao chép nội dung';
      btn.style.background = 'var(--navy)';
    }, 2000);
  });
}
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>