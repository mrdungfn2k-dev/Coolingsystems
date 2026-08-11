<?php require __DIR__.'/../partials/head.php'; ?>
<style>
#coForm .form-group{min-width:0}
#coForm .form-group input:not([type=file]):not([type=radio]):not([type=checkbox]):not([type=hidden]),#coForm .form-group select,#coForm .form-group textarea{border-radius:10px !important;border:1px solid #d6deea !important;padding:12px 14px !important;font-size:14px !important;background-color:#fff !important;color:#0a192f;max-width:100%;width:100%;-webkit-appearance:none;-moz-appearance:none;appearance:none;transition:border-color .2s,box-shadow .2s}
#coForm .form-group input:not([type=file]):not([type=radio]):not([type=checkbox]):not([type=hidden]):focus,#coForm .form-group select:focus,#coForm .form-group textarea:focus{border-color:#1a3258 !important;box-shadow:0 0 0 3px rgba(26,50,88,.12) !important;outline:none !important}
#coForm .form-group select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='none' stroke='%231a3258' stroke-width='1.8' d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:40px !important}
.prov-dd{position:relative}
.prov-dd-trg{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;border:1px solid #d6deea;border-radius:10px;padding:12px 14px;background:#fff;font-size:14px;color:#0a192f;cursor:pointer;text-align:left;font-family:inherit;line-height:1.3}
.prov-dd-trg:focus,.prov-dd.open .prov-dd-trg{border-color:#1a3258;box-shadow:0 0 0 3px rgba(26,50,88,.12);outline:none}
.prov-dd-trg #provLabel.ph{color:#9aa7bd}
.prov-dd-caret{transition:transform .2s;flex-shrink:0}
.prov-dd.open .prov-dd-caret{transform:rotate(180deg)}
.prov-dd-panel{display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:10000;background:#fff;border:1px solid #d6deea;border-radius:12px;box-shadow:0 12px 32px rgba(10,25,47,.18);overflow:hidden}
.prov-dd.open .prov-dd-panel{display:block}
.prov-dd-search{padding:8px;border-bottom:1px solid #eef2f7}
.prov-dd-list{max-height:240px;overflow-y:auto}
.prov-dd-opt{padding:11px 14px;font-size:14px;color:#0a192f;cursor:pointer}
.prov-dd-opt:hover{background:#f0f4fb}
.prov-dd-opt.sel{background:#1a3258;color:#fff;font-weight:600}
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
<style>
#qrPanel {
    position: fixed !important;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6) !important;
    z-index: 99999;
    display: none;
    align-items: center; justify-content: center;
    margin: 0 !important;
    border-radius: 0 !important;
    border: none !important;
    padding: 20px;
}
.qr-modal-inner {
    background: #fff;
    width: 100%;
    max-width: 600px;
    border-radius: 12px;
    padding: 24px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}
@media(max-width:500px) {
    .qr-modal-inner { padding: 16px; }
    .transfer-info .key, .transfer-info .val { font-size: 12px !important; }
    .qr-layout { flex-direction: column !important; align-items: center !important; }
    .qr-layout-left { margin-bottom: 16px !important; }
    .qr-layout-right { width: 100% !important; }
    .copy-layout { flex-direction: column !important; align-items: stretch !important; }
    .copy-layout button { width: 100%; margin-top: 8px !important; }
    .content-box { font-size: 14px !important; text-align: center; }
    .btn-gold.btn-lg, .btn-navy.btn-lg { white-space: normal !important; font-size: 13px !important; line-height: 1.4 !important; height: auto !important; padding: 10px !important; }
}
.qr-modal-close {
    position: absolute; top: 16px; right: 16px;
    background: none; border: none; font-size: 28px;
    cursor: pointer; color: #888; line-height: 1;
}
</style>


<section class="block"><div class="wrap" style="max-width:960px">
  <nav class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">›</span><a href="/customer/cart">Giỏ hàng</a><span class="sep">›</span><span>Thanh toán</span></nav>
  <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Thanh toán đơn hàng</h1></div></div>

  <form method="post" action="/customer/checkout" id="coForm" onsubmit="return handleCheckoutSubmit(event)" enctype="multipart/form-data">
    <?php
$qrImg = dbGet("SELECT value FROM system_config WHERE key='payment_qr_image'")['value'] ?? '';
$bankName = dbGet("SELECT value FROM system_config WHERE key='payment_bank_name'")['value'] ?? '';
$accName = dbGet("SELECT value FROM system_config WHERE key='payment_account_name'")['value'] ?? '';
$accNum = dbGet("SELECT value FROM system_config WHERE key='payment_account_number'")['value'] ?? '';
$qrPrefix = dbGet("SELECT value FROM system_config WHERE key='payment_transfer_prefix'")['value'] ?? '';
$tmpCode = strtoupper(substr(base_convert(time(), 10, 36), -4) . substr(md5(uniqid()), 0, 6));
$customerName = removeAccents($user['full_name'] ?? 'KHACH');
$customerName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $customerName));
$transferContent = trim($customerName . ' ' . $tmpCode);
?>
    <?= csrfField() ?>
    <div class="co-grid">
      <div>
        <!-- Shipping address -->
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
        <?php 
          $isPartnerUser = $user && (in_array($user['role'] ?? '', ['partner','agent']) || !empty($user['is_verified_garage']));
          if ($isPartnerUser):
            $agencyRate = (float)($user['custom_commission_rate'] ?? 5.0);
        ?>
        <!-- MODEL 3: AGENCY ORDER-ON-BEHALF PORTAL -->
        <div class="co-card" style="background:#f0f4ff; border:2px solid #0b1d3a; border-radius:12px; margin-bottom:20px; padding:20px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
            <h3 style="margin:0; font-size:16px; color:#0b1d3a; font-weight:800; display:flex; align-items:center; gap:8px;">
              <span>🏬</span> ĐẶT HÀNG HỘ CHO KHÁCH HÀNG CUỐI (MÔ HÌNH 3)
            </h3>
            <span style="background:#0b1d3a; color:#fff; font-size:11px; font-weight:800; padding:4px 12px; border-radius:12px;">MỨC CHIẾT KHẤU <?= $agencyRate ?>%</span>
          </div>

          <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; color:#1a3258; margin-bottom:14px; font-size:14px;">
            <input type="checkbox" name="is_agency_order" value="1" id="isAgencyOrderChk" onchange="toggleAgencyOrderBlock()" style="width:18px; height:18px; accent-color:#0b1d3a;">
            Bật chế độ "Đại lý Đặt hàng hộ cho Khách hàng cuối"
          </label>

          <div id="agencyOrderBlock" style="display:none; border-top:1px dashed #cbd5e1; padding-top:14px;">
            <p style="font-size:12.5px; color:#475569; margin-bottom:14px; line-height:1.5;">
              Đơn hàng sẽ lưu thông tin bảo hành cho <strong>Khách hàng cuối</strong> và tích lũy <strong><?= $agencyRate ?>% Hoa hồng</strong> vào Ví Đại lý của bạn.
            </p>

            <!-- Thông tin Khách hàng cuối -->
            <div style="background:#fff; padding:14px; border-radius:8px; border:1px solid #cbd5e1; margin-bottom:16px;">
              <div style="font-weight:800; color:#0b1d3a; font-size:13px; margin-bottom:10px; text-transform:uppercase;">👤 Thông tin Khách hàng cuối (Chủ xe / Nơi lắp đặt)</div>
              <div class="form-row">
                <div class="form-group">
                  <label style="font-size:12px; font-weight:700;">Họ tên Khách hàng cuối *</label>
                  <input type="text" name="end_customer_name" id="endCustName" placeholder="VD: Nguyễn Văn A (Khách lắp dàn lạnh)">
                </div>
                <div class="form-group">
                  <label style="font-size:12px; font-weight:700;">Số điện thoại Khách cuối *</label>
                  <input type="tel" name="end_customer_phone" id="endCustPhone" placeholder="0987654321" maxlength="10">
                </div>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:12px; font-weight:700;">Địa chỉ lắp đặt / Bảo hành Khách cuối</label>
                <input type="text" name="end_customer_address" id="endCustAddr" placeholder="VD: Gara Ô Tô Hải Hà, Số 10 Lê Văn Lương, Hà Nội">
              </div>
            </div>

            <!-- Hình thức Thu hộ & Thanh toán -->
            <div style="background:#fff; padding:14px; border-radius:8px; border:1px solid #cbd5e1;">
              <div style="font-weight:800; color:#0b1d3a; font-size:13px; margin-bottom:10px; text-transform:uppercase;">💵 Hình thức Thu hộ & Đối soát Hoa hồng</div>
              
              <div style="display:flex; flex-direction:column; gap:10px;">
                <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc;" id="lblCompCollect">
                  <input type="radio" name="agency_payment_mode" value="company_collect" checked onchange="updateAgencyCalc()" style="margin-top:3px;">
                  <div>
                    <div style="font-weight:700; color:#0b1d3a; font-size:13.5px;">1. Công ty Thu hộ 100% khi Giao hàng (COD / Chuyển khoản Công ty)</div>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;">Khách trả đủ 100%. Công ty tự động trích <strong><?= $agencyRate ?>% Hoa hồng</strong> cộng vào Ví tích lũy Đại lý.</div>
                  </div>
                </label>

                <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc;" id="lblAgentCollect">
                  <input type="radio" name="agency_payment_mode" value="agent_collected" onchange="updateAgencyCalc()" style="margin-top:3px;">
                  <div>
                    <div style="font-weight:700; color:#1b5e20; font-size:13.5px;">2. Đại lý tự thu tiền từ Khách (Khấu trừ ngay <?= $agencyRate ?>% Hoa hồng)</div>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;">Đại lý thu tiền mặt từ Khách. Đại lý chỉ thanh toán cho Công ty <strong>Tổng tiền - <?= $agencyRate ?>% Hoa hồng</strong>.</div>
                  </div>
                </label>
              </div>

              <!-- Bảng Tính Giá Chiết Khấu Realtime -->
              <div id="agencyCalcBox" style="margin-top:14px; background:#fffbe6; border:1px solid #ffe58f; padding:12px; border-radius:6px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                  <span>Tổng giá trị hàng hóa (trước VAT):</span>
                  <strong id="agencyOrderSubtotal"><?= vnd($cart['total']) ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:#16a34a;">
                  <span>Hoa hồng Đại lý (<?= $agencyRate ?>%):</span>
                  <strong id="agencyCalcCommission">-0đ</strong>
                </div>
                <hr style="border:0; border-top:1px dashed #ffd591; margin:6px 0;">
                <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:800; color:#0b1d3a;">
                  <span id="agencyPayableLabel">Số tiền Đại lý thực trả Công ty:</span>
                  <span id="agencyCalcPayable" style="color:#dc2626;"><?= vnd($cart['total']) ?></span>
                </div>
              </div>

              <!-- Nghiệp vụ Thuế VAT B2B -->
              <div style="margin-top:16px; border-top:1px dashed #cbd5e1; padding-top:12px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; color:#0b1d3a; font-size:13px;">
                  <input type="checkbox" name="requires_vat" value="1" id="reqVatChk" onchange="document.getElementById('vatBox').style.display=this.checked?'block':'none'">
                  🧾 Yêu cầu xuất Hóa đơn Tài chính (VAT B2B)
                </label>
                <div id="vatBox" style="display:none; margin-top:10px; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #cbd5e1;">
                  <div style="display:flex; gap:16px; margin-bottom:10px; font-size:12.5px;">
                    <label style="cursor:pointer;"><input type="radio" name="vat_invoice_type" value="agency" checked> Xuất cho Đại lý</label>
                    <label style="cursor:pointer;"><input type="radio" name="vat_invoice_type" value="end_customer"> Xuất cho Khách cuối</label>
                  </div>
                  <div class="form-row">
                    <div class="form-group" style="margin-bottom:8px;">
                      <label style="font-size:11.5px; font-weight:700;">Mã số thuế doanh nghiệp *</label>
                      <input type="text" name="vat_tax_code" placeholder="VD: 0101234567">
                    </div>
                    <div class="form-group" style="margin-bottom:8px;">
                      <label style="font-size:11.5px; font-weight:700;">Tên Công ty / Đơn vị mua hàng *</label>
                      <input type="text" name="vat_company_name" placeholder="VD: Công ty TNHH Phụ tùng Ô tô Hải Hà">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
        function toggleAgencyOrderBlock() {
          const chk = document.getElementById('isAgencyOrderChk');
          const blk = document.getElementById('agencyOrderBlock');
          if (chk && blk) {
            blk.style.display = chk.checked ? 'block' : 'none';
            updateAgencyCalc();
          }
        }

        function updateAgencyCalc() {
          const chk = document.getElementById('isAgencyOrderChk');
          if (!chk || !chk.checked) return;

          const total = <?= (float)$cart['total'] ?>;
          const rate = <?= (float)$agencyRate ?> / 100;
          const comm = Math.round(total * rate);
          const modeComp = document.querySelector('input[name="agency_payment_mode"]:checked')?.value === 'company_collect';

          document.getElementById('agencyCalcCommission').textContent = '+' + new Intl.NumberFormat('vi-VN').format(comm) + 'đ';
          
          if (modeComp) {
            document.getElementById('agencyPayableLabel').textContent = 'Số tiền Công ty thu hộ từ Khách hàng:';
            document.getElementById('agencyCalcPayable').textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
          } else {
            const payable = Math.max(0, total - comm);
            document.getElementById('agencyPayableLabel').textContent = 'Số tiền Đại lý thực trả Công ty (đã trừ ' + <?= (float)$agencyRate ?> + '%):';
            document.getElementById('agencyCalcPayable').textContent = new Intl.NumberFormat('vi-VN').format(payable) + 'đ';
          }
        }
        </script>
        <?php endif; ?>

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
              <div class="prov-dd" id="provDD">
                <input type="hidden" name="shipping_province" id="provValue" value="<?= e($pProv) ?>">
                <button type="button" class="prov-dd-trg" id="provTrg" onclick="provToggle()"><span id="provLabel"<?= $pProv?'':' class="ph"' ?>><?= $pProv ? e($pProv) : '-- Chọn tỉnh/thành --' ?></span><svg class="prov-dd-caret" width="12" height="8" viewBox="0 0 12 8"><path fill="none" stroke="#1a3258" stroke-width="1.8" d="M1 1.5l5 5 5-5"/></svg></button>
                <div class="prov-dd-panel" id="provPanel">
                  <div class="prov-dd-search"><input type="text" id="provSearch" placeholder="Tìm tỉnh/thành..." oninput="provFilter(this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault();return false;}" autocomplete="off"></div>
                  <div class="prov-dd-list" id="provList"><?php foreach(vnProvinceList() as $pp): ?><div class="prov-dd-opt<?= $pp===$pProv?' sel':'' ?>" data-v="<?= e($pp) ?>" onclick="provPick(this)"><?= e($pp) ?></div><?php endforeach; ?></div>
                </div>
              </div></div>
            <div class="form-group"><label>Quận/Huyện <span class="req">*</span></label>
              <input type="text" name="shipping_district" value="<?= e($pDist) ?>" required placeholder="VD: Đống Đa" oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
          </div>
          <div class="form-group"><label>Phường/Xã <span class="req">*</span></label>
            <input type="text" name="shipping_ward" value="<?= e($pWard) ?>" required placeholder="VD: Phương Liên" oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:,.<>?/~`]/g,'')"></div>
          <div class="form-group"><label>Địa chỉ cụ thể <span class="req">*</span></label>
            <input type="text" name="shipping_detail" value="<?= e($pDetail) ?>" required placeholder="Số nhà, tên đường..." oninput="this.value=this.value.replace(/[!@#$%^&*()_+=\[\]{}|;:.<>?~`]/g,'')" maxlength="100"></div>
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
            <?php if ($isPartnerUser): 
              $creditLimit = (float)($user['credit_limit'] ?? 100000000);
              $currentDebt = (float)($user['current_debt'] ?? 0);
              $availableCredit = max(0, $creditLimit - $currentDebt);
            ?>
            <label class="pay-btn" id="payBtnCredit" onclick="selectPay(this,'credit')">
              <input type="radio" name="payment_method" value="credit" id="payCredit">
              <div class="pay-name" style="color:#0b1d3a; font-weight:800;">Thanh toán Công nợ (Gối đầu B2B)</div>
              <div class="pay-desc">Hạn mức khả dụng: <?= vnd($availableCredit) ?> (Thời hạn 30 ngày)</div>
            </label>
            <?php endif; ?>
          </div>

          

          <!-- COD info -->
          <div class="cod-info" id="codBlock">
            <div style="font-weight:700;color:#1b5e20;font-size:14px;margin-bottom:6px">Thanh toán khi nhận hàng</div>
            <div style="font-size:13px;color:#388e3c">Bạn sẽ thanh toán tiền mặt sau khi nhận được hàng. Đơn hàng sẽ được xử lý và giao đến địa chỉ của bạn.</div>
          </div>
          
          <!-- Transfer Modal -->
          <div id="qrPanel" style="display:none;">
            <div class="qr-modal-inner">
              <button type="button" class="qr-modal-close" onclick="closeQrModal()">×</button>
              <h3 style="margin-top:0;font-size:18px;color:var(--navy);margin-bottom:20px;text-align:center">Quét mã QR Thanh toán</h3>
              <!-- QR & Account Info -->
              <div style="display:flex; gap:16px; align-items:flex-start;" class="qr-layout">
                <div style="flex:0 0 160px; margin:0;" class="qr-layout-left">
                  <?php if($qrImg): ?>
                    <div class="qr-img-box" style="margin:0; margin-bottom: 8px;"><img src="/uploads/qr/<?=htmlspecialchars($qrImg)?>" alt="QR" style="width:100%; height:auto; display:block;"></div>
                    <a href="/uploads/qr/<?=htmlspecialchars($qrImg)?>" download="ma-qr-thanh-toan.png" style="display:inline-block; font-size:13px; color:var(--navy); font-weight:700; text-decoration:underline;">📥 Tải mã QR</a>
                  <?php else: ?>
                    <div class="qr-img-box" style="margin:0;background:#f5f5f5;flex-direction:column;gap:8px;color:#aaa;font-size:13px;padding:20px"><div style="font-size:32px;opacity:0.4">QR</div><div>Chưa có mã QR</div></div>
                  <?php endif; ?>
                </div>
                
                <div style="flex:1; min-width:0;" class="qr-layout-right">
                  <div class="transfer-info" style="margin:0;">
                    <div class="row"><span class="key">Ngân hàng</span><span class="val"><?=htmlspecialchars($bankName)?></span></div>
                    <div class="row"><span class="key">Số tài khoản</span><span class="val"><?=htmlspecialchars($accNum)?></span></div>
                    <div class="row"><span class="key">Chủ tài khoản</span><span class="val"><?=htmlspecialchars($accName)?></span></div>
                    <div class="row"><span class="key">Số tiền</span><div style="text-align:right"><span class="val" id="amtDisplay" style="color:#e74c3c">—</span></div></div>
                  </div>
                </div>
              </div>
              
              <!-- Transfer Note & Upload (Full width below) -->
              <div style="margin-top: 20px;">

                  <div class="transfer-note" style="margin:0; background:#fffcf2; border:1px solid #f2e3b6;">
                    <div class="note-title" style="color:#b8860b; margin-bottom:8px;">Nội dung chuyển khoản bắt buộc</div>
                    <div style="display:flex; align-items:center; gap:12px;" class="copy-layout">
                      <div class="content-box" id="transferContent" style="flex:1; border-color:#1a3258; color:#1a3258; font-size: 16px; margin:0; padding:10px;"><?=e($transferContent)?></div>
                      <button type="button" onclick="copyContent(this)" style="background:var(--navy);color:#fff;border:none;border-radius:6px;padding:12px 18px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;margin:0;">Sao chép nội dung</button>
                    </div>
                  </div>
                  
                  <div style="margin-top:20px;">
                    <label style="font-weight:700;font-size:13px;color:var(--navy);display:block;margin-bottom:6px;">Tải lên biên lai chuyển khoản <span class="req" style="color:red">*</span></label>
                    <label style="cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;width:140px;height:140px;border:1px dashed #ccc;border-radius:8px;background:#f8f9fa;text-align:center;transition:all 0.2s" onmouseover="this.style.background='#f0f4f8';this.style.borderColor='var(--navy)'" onmouseout="this.style.background='#f8f9fa';this.style.borderColor='#ccc'">
                      <div style="padding:6px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;color:#333;font-weight:600;font-size:13px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:8px">Thêm ảnh</div>
                      <span style="font-size:12px;color:#888;padding:0 8px;line-height:1.4" id="receiptFileName">Mỗi ảnh không quá 5 MB</span>
                      <input type="file" name="payment_receipt" id="payment_receipt_input" accept="image/*" style="display:none" onchange="document.getElementById('receiptFileName').textContent = this.files[0] ? this.files[0].name : 'Mỗi ảnh không quá 5 MB'">
                    </label>
                  </div>
              </div>
              <div style="margin-top: 24px; text-align: center;">
                 <button type="submit" class="btn btn-navy btn-lg" style="width:100%">Xác nhận đặt hàng</button>
              </div>
            </div>
          </div>


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
          $shipCfg = dbAll("SELECT key,value FROM system_config WHERE key IN ('default_shipping_fee','free_shipping_threshold','shipping_origin_province','shipping_rates')");
          $shipCfgM=[]; foreach($shipCfg as $sc)$shipCfgM[$sc['key']]=$sc['value'];
          $freeThreshold = intval($shipCfgM['free_shipping_threshold'] ?? 2000000);
          $cartWeight = 0; foreach($items as $wi){ $cartWeight += intval($wi['weight_g']??0) * $wi['quantity']; }
          $shipFeeRaw = calcShippingFee($pProv, (int)$cartWeight, $shipCfgM);
          $ship = ($freeThreshold > 0 && $afterDiscount >= $freeThreshold) ? 0 : $shipFeeRaw; 
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
              <span>Vận chuyển</span><span id="shipDisplay"><?=$ship?vnd($ship):'Miễn phí'?></span></div>
            <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:800;color:var(--navy);padding-top:10px;border-top:1px solid #f0f0f0">
              <span>Tổng cộng</span><span id="grandDisplay"><?=vnd($grand)?></span></div>
          </div>
          <button type="submit" class="btn btn-navy btn-block btn-lg" style="margin-top:18px;font-size:16px;height:52px" id="submitBtn">
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
var SHIP_ORIGIN = <?= json_encode($shipCfgM['shipping_origin_province'] ?? '') ?>;
var SHIP_RATES = (function(){ var o={}; (<?= json_encode(json_decode($shipCfgM['shipping_rates'] ?? '[]', true) ?: []) ?>).forEach(function(r){o[r.zone]={bw:+r.base_weight,bp:+r.base_price,sw:+r.step_weight,sp:+r.step_price};}); return o; })();
var SHIP_REGION = <?= json_encode((function(){ $m=[]; foreach(vnProvinceList() as $pp){ $m[$pp]=vnProvinceRegion($pp); } return $m; })(), JSON_UNESCAPED_UNICODE) ?>;
var SHIP_CART_WEIGHT = <?= (int)($cartWeight ?? 0) ?>;
var SHIP_FREE = <?= (int)($freeThreshold ?? 2000000) ?>;
var SHIP_AFTER_DISCOUNT = <?= (int)$afterDiscount ?>;
var SHIP_DEFAULT = <?= (int)($shipCfgM['default_shipping_fee'] ?? 30000) ?>;
function shipVnd(n){ return Math.round(n).toLocaleString('vi-VN') + ' ₫'; }
function shipZone(dest){ if(dest && dest===SHIP_ORIGIN) return 'noi_tinh'; var o=SHIP_REGION[SHIP_ORIGIN]||'', d=SHIP_REGION[dest]||''; if(!o||!d) return ''; if(o===d) return 'noi_mien'; if(o==='trung'||d==='trung') return 'can_mien'; return 'lien_mien'; }
function shipFeeFor(dest){ var z=shipZone(dest); if(!z||!SHIP_RATES[z]) return SHIP_DEFAULT; var r=SHIP_RATES[z]; var w=SHIP_CART_WEIGHT>0?SHIP_CART_WEIGHT:r.bw; if(w<=r.bw) return r.bp; return r.bp + Math.ceil((w-r.bw)/r.sw)*r.sp; }
function recalcShip(){ var el=document.querySelector('[name="shipping_province"]'); if(!el) return; var fee=shipFeeFor(el.value); var ship=(SHIP_FREE>0 && SHIP_AFTER_DISCOUNT>=SHIP_FREE)?0:fee; var sd=document.getElementById('shipDisplay'); if(sd) sd.textContent=ship?shipVnd(ship):'Miễn phí'; grandTotal=SHIP_AFTER_DISCOUNT+ship; depositAmount=grandTotal; var gd=document.getElementById('grandDisplay'); if(gd) gd.textContent=shipVnd(grandTotal); }
document.addEventListener('DOMContentLoaded', function(){ var el=document.querySelector('[name="shipping_province"]'); if(el){ el.addEventListener('change', recalcShip); recalcShip(); } });
function provToggle(){ var dd=document.getElementById('provDD'); var willOpen=!dd.classList.contains('open'); dd.classList.toggle('open'); if(willOpen){ var s=document.getElementById('provSearch'); if(s){ s.value=''; provFilter(''); setTimeout(function(){s.focus();},50); } } }
function provPick(el){ var v=el.getAttribute('data-v'); document.getElementById('provValue').value=v; var lb=document.getElementById('provLabel'); lb.textContent=v; lb.classList.remove('ph'); document.querySelectorAll('#provList .prov-dd-opt.sel').forEach(function(x){x.classList.remove('sel');}); el.classList.add('sel'); document.getElementById('provDD').classList.remove('open'); if(typeof recalcShip==='function') recalcShip(); }
function provFilter(q){ q=(q||'').toLowerCase(); document.querySelectorAll('#provList .prov-dd-opt').forEach(function(o){ o.style.display=o.textContent.toLowerCase().indexOf(q)>=0?'':'none'; }); }
document.addEventListener('click', function(e){ var dd=document.getElementById('provDD'); if(dd && !dd.contains(e.target)) dd.classList.remove('open'); });
var depositAmount = grandTotal;  // Full amount, no deposit split
var remainingAmount = 0;
var userName = <?= json_encode($user['full_name'] ?? '') ?>;
var tmpOrderCode = '<?= $tmpCode ?>';

function closeQrModal() {
    document.getElementById('qrPanel').style.display = 'none';
    window.qrConfirmed = false;
    document.getElementById('submitBtn').textContent = 'Đặt hàng ngay';
}
function selectPay(el, val) {
  document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  
  // Hide QR panel when switching
  document.getElementById('qrPanel').style.display = 'none';
  window.qrConfirmed = false;
  
  var receiptInput = document.querySelector('input[name="payment_receipt"]');
  if (receiptInput) receiptInput.required = false;

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
    var _pv=document.getElementById('provValue'); if(_pv && !_pv.value){ e.preventDefault(); alert('Vui lòng chọn Tỉnh/Thành phố.'); var _pt=document.getElementById('provTrg'); if(_pt)_pt.focus(); return false; }
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
    if (!document.getElementById('coForm').reportValidity()) return false;
    updateTransferInfo();
    
    // SHOW QR PANEL INSTEAD OF SUBMITTING
    var qrPanel = document.getElementById('qrPanel');
    qrPanel.style.display = 'flex';
    
    // Change required for receipt
    var receiptInput = document.querySelector('input[name="payment_receipt"]');
    if (receiptInput) {
        receiptInput.required = true;
        // Make sure label shows it's required
        var lbl = receiptInput.previousElementSibling;
        if (lbl && !lbl.innerHTML.includes('*')) {
            lbl.innerHTML = 'Tải lên biên lai chuyển khoản <span class="req" style="color:red">*</span>';
        }
    }
    
    document.getElementById('submitBtn').textContent = 'Tôi đã chuyển khoản & Hoàn tất Đặt hàng';
    window.qrConfirmed = true;
    
    setTimeout(function() {
        qrPanel.scrollIntoView({behavior: 'smooth', block: 'center'});
    }, 100);
    
    return false;
  }
  return true;
}





function removeAccents(str) {
  return str.normalize("NFD").replace(/[̀-ͯ]/g, "").replace(/đ/g, "d").replace(/Đ/g, "D").replace(/\s+/g, "");
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
  var qrPrefix = "<?= e($qrPrefix) ?>";
  var formattedName = removeAccents(name).replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
  var content = formattedName + ' ' + tmpOrderCode;
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