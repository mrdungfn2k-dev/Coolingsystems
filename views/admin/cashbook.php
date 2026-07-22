<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
  <div>
    <h1>Sổ quỹ</h1>
    <p style="margin:5px 0 0;color:#667085;font-size:13px">Theo dõi số dư tiền mặt và ngân hàng từ các phiếu thu, chi đã ghi nhận.</p>
  </div>
</div>

<?php foreach(getFlash() as $message): ?>
<div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
<?php endforeach; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:16px">
  <?php foreach($accounts as $account): ?>
  <div class="panel" style="padding:16px">
    <div style="display:flex;justify-content:space-between;gap:8px">
      <strong style="color:var(--navy)"><?= e($account['name']) ?></strong>
      <span class="fs-11 text-muted"><?= $account['type']==='bank'?'Ngân hàng':'Tiền mặt' ?></span>
    </div>
    <div style="font-size:25px;font-weight:800;color:<?= (int)$account['balance']<0?'#b42318':'#18794e' ?>;margin-top:12px"><?= number_format((int)$account['balance'],0,',','.') ?> đ</div>
    <div class="fs-11 text-muted" style="margin-top:5px">Mã quỹ: <?= e($account['code']) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="panel" style="padding:14px 16px;margin-bottom:16px">
  <form method="get" action="/admin/cashbook" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
    <div class="form-group" style="min-width:190px;margin:0">
      <label>Quỹ</label>
      <select name="account">
        <option value="">Tất cả quỹ</option>
        <?php foreach($accounts as $account): ?>
        <option value="<?= (int)$account['id'] ?>" <?= (int)$selectedAccount===(int)$account['id']?'selected':'' ?>><?= e($account['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0"><label>Từ ngày</label><input type="date" name="from" value="<?= e($fromDate) ?>"></div>
    <div class="form-group" style="margin:0"><label>Đến ngày</label><input type="date" name="to" value="<?= e($toDate) ?>"></div>
    <button class="btn btn-navy">Lọc</button>
    <a href="/admin/cashbook" class="btn btn-outline-navy">Xóa lọc</a>
  </form>
</div>

<?php if($canCreateReceipt): ?>
<div class="panel" style="padding:16px;margin-bottom:16px">
  <h2 style="font-size:16px;margin:0 0 14px;color:var(--navy)">Lập phiếu thu</h2>
  <form method="post" action="/admin/cashbook/receipts" id="cashReceiptForm" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Quỹ nhận tiền *</label>
      <select name="account_id" required>
        <?php foreach($accounts as $account): ?>
        <option value="<?= (int)$account['id'] ?>"><?= e($account['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Số tiền thu *</label><input name="amount" inputmode="numeric" required maxlength="12" placeholder="Ví dụ: 500000"></div>
    <div class="form-group"><label>Ngày ghi nhận *</label><input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required></div>
    
    <div class="form-group">
      <label>Người nộp tiền * <span id="payerCharCnt" style="font-weight:400;color:#667085">(0/50 ký tự)</span></label>
      <input name="payer_name" id="receiptPayerName" required maxlength="50" placeholder="Họ và tên người nộp tiền (tối đa 50 ký tự)...">
    </div>
    <div class="form-group">
      <label>Số điện thoại *</label>
      <input name="payer_phone" required inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" placeholder="Ví dụ: 0947123456">
      <small id="receiptPhoneHint" style="display:block;min-height:18px;margin-top:4px;color:#667085">Gồm 10 số, bắt đầu: 03, 05, 07, 08 hoặc 09.</small>
    </div>
    <div class="form-group"><label>Email *</label><input name="payer_email" type="email" required maxlength="254" placeholder="email@nguoinop.com"></div>
    <div class="form-group"><label>Mã tham chiếu</label><input name="reference_code" maxlength="64" placeholder="Hóa đơn, biên nhận..."></div>
    <div class="form-group" style="grid-column:span 2">
      <label>Diễn giải <span id="receiptCharCount" style="font-weight:400;color:#667085">0/200 ký tự</span></label>
      <textarea name="description" id="receiptDescription" rows="2" maxlength="200" placeholder="Nội dung khoản thu (tối đa 200 ký tự)"></textarea>
    </div>
    <div style="grid-column:span 3"><button class="btn btn-navy">Tạo phiếu thu</button></div>
  </form>
</div>
<?php endif; ?>

<?php if($canCreateDisbursement): ?>
<div class="panel" style="padding:16px;margin-bottom:16px">
  <h2 style="font-size:16px;margin:0 0 14px;color:var(--navy)">Lập phiếu chi</h2>
  <form method="post" action="/admin/cashbook/disbursements" id="cashDisbursementForm" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Quỹ chi *</label>
      <select name="account_id" required>
        <?php foreach($accounts as $account): ?>
        <option value="<?= (int)$account['id'] ?>"><?= e($account['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Số tiền chi *</label><input name="amount" inputmode="numeric" required maxlength="12" placeholder="Ví dụ: 500000"></div>
    <div class="form-group"><label>Ngày dự chi *</label><input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required></div>
    
    <div class="form-group">
      <label>Người nhận tiền * <span id="payeeCharCnt" style="font-weight:400;color:#667085">(0/50 ký tự)</span></label>
      <input name="payee_name" id="disbursementPayeeName" required maxlength="50" placeholder="Họ và tên người nhận tiền (tối đa 50 ký tự)...">
    </div>
    <div class="form-group">
      <label>Số điện thoại *</label>
      <input name="payee_phone" required inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" placeholder="Ví dụ: 0947123456">
      <small id="disbursementPhoneHint" style="display:block;min-height:18px;margin-top:4px;color:#667085">Gồm 10 số, bắt đầu: 03, 05, 07, 08 hoặc 09.</small>
    </div>
    <div class="form-group"><label>Email *</label><input name="payee_email" type="email" required maxlength="254" placeholder="email@nguoinhan.com"></div>
    <div class="form-group"><label>Mã tham chiếu</label><input name="reference_code" maxlength="64" placeholder="Hóa đơn, biên nhận..."></div>
    <div class="form-group" style="grid-column:span 2">
      <label>Diễn giải <span id="disbursementCharCount" style="font-weight:400;color:#667085">0/200 ký tự</span></label>
      <textarea name="description" id="disbursementDescription" rows="2" maxlength="200" placeholder="Nội dung khoản chi (tối đa 200 ký tự)"></textarea>
    </div>
    <div style="grid-column:span 3"><button class="btn btn-navy">Tạo phiếu chi</button></div>
  </form>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;margin-bottom:16px">
  <div class="panel" style="padding:14px"><div class="fs-11 text-muted">Tổng thu trong kỳ</div><strong style="font-size:22px;color:#18794e"><?= number_format((int)$totals['income'],0,',','.') ?> đ</strong></div>
  <div class="panel" style="padding:14px"><div class="fs-11 text-muted">Tổng chi trong kỳ</div><strong style="font-size:22px;color:#b42318"><?= number_format((int)$totals['expense'],0,',','.') ?> đ</strong></div>
  <div class="panel" style="padding:14px"><div class="fs-11 text-muted">Chênh lệch trong kỳ</div><strong style="font-size:22px;color:var(--navy)"><?= number_format((int)$totals['net'],0,',','.') ?> đ</strong></div>
</div>

<div class="panel">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700">Giao dịch sổ quỹ</div>
  <div style="overflow:auto">
    <table class="tbl">
      <thead>
        <tr><th>Ngày ghi nhận</th><th>Quỹ</th><th>Loại</th><th>Số tiền</th><th>Tham chiếu</th><th>Diễn giải</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
        <?php foreach($entries as $entry): ?>
        <tr>
          <td><?= e($entry['entry_date']) ?></td>
          <td><?= e($entry['account_name']) ?></td>
          <td><span style="color:<?= $entry['direction']==='in'?'#18794e':'#b42318' ?>;font-weight:700"><?= $entry['direction']==='in'?'Thu':'Chi' ?></span></td>
          <td style="font-weight:700;color:<?= $entry['direction']==='in'?'#18794e':'#b42318' ?>"><?= $entry['direction']==='in'?'+':'-' ?><?= number_format((int)$entry['amount'],0,',','.') ?> đ</td>
          <td><?= e($entry['reference_code'] ?: '-') ?></td>
          <td><?= e($entry['description'] ?: '-') ?></td>
          <td>
            <?php if($canVoidEntry): ?>
            <?php if(!$entry['void_request_id']): ?>
            <form method="post" action="/admin/cashbook/entries/<?= (int)$entry['id'] ?>/void-requests" style="display:flex;gap:5px;align-items:center;min-width:260px">
              <?= csrfField() ?>
              <input name="reason" maxlength="300" required placeholder="Lý do hủy (5-300 ký tự)">
              <button class="btn btn-sm btn-outline-navy">Yêu cầu hủy</button>
            </form>
            <?php elseif($entry['void_request_status']==='pending'&&(int)$entry['void_request_created_by']!==(int)$currentUserId): ?>
            <form method="post" action="/admin/cashbook/void-requests/<?= (int)$entry['void_request_id'] ?>/approve" style="display:flex;gap:5px;align-items:center;min-width:320px">
              <?= csrfField() ?>
              <input name="rejection_reason" maxlength="300" placeholder="Lý do nếu từ chối">
              <button class="btn btn-sm btn-navy">Duyệt hủy</button>
              <button class="btn btn-sm btn-outline-navy" formaction="/admin/cashbook/void-requests/<?= (int)$entry['void_request_id'] ?>/reject">Từ chối</button>
            </form>
            <?php elseif($entry['void_request_status']==='pending'): ?>
            <span class="fs-11 text-muted">Chờ người khác duyệt</span>
            <?php else: ?>
            <span class="fs-11 text-muted"><?= $entry['void_request_status']==='rejected'?'Yêu cầu bị từ chối':'Đã xử lý' ?></span>
            <?php endif; ?>
            <?php else: ?>
            <span class="fs-11 text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$entries): ?>
        <tr><td colspan="7" style="padding:28px;text-align:center;color:#667085">Chưa có giao dịch trong sổ quỹ.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  function setupCharCounter(inputEl, maxChars, counterEl) {
    if(!inputEl) return;
    function update() {
      var len = inputEl.value.length;
      if(counterEl) {
        counterEl.textContent = '(' + len + '/' + maxChars + ' ký tự)';
        counterEl.style.color = len >= maxChars ? '#e11d48' : '#667085';
      }
    }
    inputEl.addEventListener('input', update);
    update();
  }

  // Phiếu thu
  var rForm = document.getElementById('cashReceiptForm');
  if(rForm) {
    var rPhone = rForm.querySelector('[name=payer_phone]'), rHint = document.getElementById('receiptPhoneHint');
    var rPayer = document.getElementById('receiptPayerName'), rPayerCnt = document.getElementById('payerCharCnt');
    var rDesc = document.getElementById('receiptDescription'), rDescCnt = document.getElementById('receiptCharCount');

    setupCharCounter(rPayer, 50, rPayerCnt);
    setupCharCounter(rDesc, 200, rDescCnt);

    function validPhone(phone, hint){
      var value = phone.value.replace(/\D/g,'').slice(0,10);
      phone.value = value;
      var valid = /^0[35789]\d{8}$/.test(value);
      if(!valid){
        phone.setCustomValidity('Số điện thoại phải có 10 số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.');
        if(hint) hint.style.color='#b42318';
      } else {
        phone.setCustomValidity('');
        if(hint) hint.style.color='#667085';
      }
      return valid;
    }
    rPhone.addEventListener('input', function(){ validPhone(rPhone, rHint); });
    rPhone.addEventListener('blur', function(){ validPhone(rPhone, rHint); });
    rForm.addEventListener('submit', function(e){
      if(!validPhone(rPhone, rHint)){ e.preventDefault(); rPhone.reportValidity(); }
    });
  }

  // Phiếu chi
  var dForm = document.getElementById('cashDisbursementForm');
  if(dForm) {
    var dPhone = dForm.querySelector('[name=payee_phone]'), dHint = document.getElementById('disbursementPhoneHint');
    var dPayee = document.getElementById('disbursementPayeeName'), dPayeeCnt = document.getElementById('payeeCharCnt');
    var dDesc = document.getElementById('disbursementDescription'), dDescCnt = document.getElementById('disbursementCharCount');

    setupCharCounter(dPayee, 50, dPayeeCnt);
    setupCharCounter(dDesc, 200, dDescCnt);

    dPhone.addEventListener('input', function(){ validPhone(dPhone, dHint); });
    dPhone.addEventListener('blur', function(){ validPhone(dPhone, dHint); });
    dForm.addEventListener('submit', function(e){
      if(!validPhone(dPhone, dHint)){ e.preventDefault(); dPhone.reportValidity(); }
    });
  }
})();
</script>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
