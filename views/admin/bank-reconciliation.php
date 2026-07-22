<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
  <div>
    <h1>Đối soát ngân hàng/QR</h1>
    <p style="margin:5px 0 0;color:#667085;font-size:13px">Ghi nhận sao kê và đối chiếu với bút toán thu chi của quỹ ngân hàng.</p>
  </div>
  <a class="btn btn-outline-navy" href="/admin/cashbook">Về sổ quỹ</a>
</div>

<?php foreach(getFlash() as $message): ?>
<div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
<?php endforeach; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;margin-bottom:16px">
  <div class="panel" style="padding:15px"><div class="fs-11 text-muted">Chưa đối soát</div><strong style="font-size:24px;color:#a15c00"><?= (int)$summary['unmatched'] ?></strong></div>
  <div class="panel" style="padding:15px"><div class="fs-11 text-muted">Đã đối soát</div><strong style="font-size:24px;color:#18794e"><?= (int)$summary['matched'] ?></strong></div>
  <div class="panel" style="padding:15px"><div class="fs-11 text-muted">Chênh lệch chưa khớp</div><strong style="font-size:24px;color:#b42318"><?= number_format((int)$summary['unmatched_amount'],0,',','.') ?> đ</strong></div>
</div>

<?php if($canManage): ?>
<div class="panel" style="padding:16px;margin-bottom:16px">
  <h2 style="font-size:16px;margin:0 0 14px;color:var(--navy)">Ghi nhận giao dịch từ sao kê/QR</h2>
  <form method="post" action="/admin/bank-reconciliation" id="bankReconciliationForm" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Tài khoản ngân hàng *</label>
      <select name="account_id" required>
        <?php foreach($bankAccounts as $account): ?>
        <option value="<?= (int)$account['id'] ?>"><?= e($account['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Loại giao dịch *</label>
      <select name="direction">
        <option value="in">Tiền vào</option>
        <option value="out">Tiền ra</option>
      </select>
    </div>
    <div class="form-group">
      <label>Ngày giao dịch *</label>
      <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="form-group">
      <label>Số tiền *</label>
      <input name="amount" inputmode="numeric" maxlength="12" required placeholder="Ví dụ: 500000">
    </div>
    <div class="form-group">
      <label>Mã giao dịch ngân hàng/QR</label>
      <input name="bank_reference" maxlength="120" placeholder="Mã từ sao kê hoặc QR">
    </div>
    <div class="form-group">
      <label>Bút toán quỹ để khớp</label>
      <select name="ledger_entry_id">
        <option value="">Để đối soát sau</option>
        <?php foreach($bankLedgerEntries as $entry): ?>
        <option value="<?= (int)$entry['id'] ?>"><?= e($entry['entry_date']) ?> - <?= $entry['direction']==='in'?'Thu':'Chi' ?> <?= number_format((int)$entry['amount'],0,',','.') ?> đ<?= $entry['reference_code']?' - '.e($entry['reference_code']):'' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="grid-column:span 3">
      <label>Diễn giải <span id="bankDescriptionCount" style="font-weight:400;color:#667085">(0/200 ký tự)</span></label>
      <textarea name="description" id="bankDescription" rows="3" maxlength="200" placeholder="Nội dung từ sao kê (tối đa 200 ký tự)"></textarea>
    </div>
    <div><button class="btn btn-navy">Ghi nhận &amp; đối soát</button></div>
  </form>
</div>
<?php endif; ?>

<div class="panel">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700">Danh sách giao dịch sao kê</div>
  <div style="overflow:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Mã</th>
          <th>Ngày</th>
          <th>Tài khoản</th>
          <th>Loại</th>
          <th>Số tiền</th>
          <th>Mã NH/QR</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($transactions as $item): ?>
        <tr>
          <td><strong><?= e($item['code']) ?></strong><div class="fs-11 text-muted"><?= e($item['description'] ?: '-') ?></div></td>
          <td><?= e($item['transaction_date']) ?></td>
          <td><?= e($item['account_name']) ?></td>
          <td style="font-weight:700;color:<?= $item['direction']==='in'?'#18794e':'#b42318' ?>"><?= $item['direction']==='in'?'Tiền vào':'Tiền ra' ?></td>
          <td style="font-weight:700"><?= number_format((int)$item['amount'],0,',','.') ?> đ</td>
          <td><?= e($item['bank_reference'] ?: '-') ?></td>
          <td>
            <span style="font-weight:700;color:<?= $item['status']==='matched'?'#18794e':'#a15c00' ?>"><?= $item['status']==='matched'?'Đã khớp':'Chưa khớp' ?></span>
            <?php if($item['ledger_reference']): ?><div class="fs-11 text-muted"><?= e($item['ledger_reference']) ?></div><?php endif; ?>
          </td>
          <td>
            <?php if($canManage&&$item['status']==='unmatched'): ?>
            <form method="post" action="/admin/bank-reconciliation/<?= (int)$item['id'] ?>/match" style="display:flex;gap:5px;align-items:center;min-width:255px">
              <?= csrfField() ?>
              <select name="ledger_entry_id" required>
                <option value="">Chọn bút toán</option>
                <?php foreach($bankLedgerEntries as $entry): ?>
                <option value="<?= (int)$entry['id'] ?>"><?= e($entry['entry_date']) ?> - <?= $entry['direction']==='in'?'Thu':'Chi' ?> <?= number_format((int)$entry['amount'],0,',','.') ?> đ</option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-navy">Khớp</button>
            </form>
            <?php else: ?>
            <span class="fs-11 text-muted">-</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$transactions): ?>
        <tr><td colspan="8" style="padding:28px;text-align:center;color:#667085">Chưa có giao dịch sao kê.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  var desc = document.getElementById('bankDescription'), counter = document.getElementById('bankDescriptionCount');
  if(!desc) return;
  function update() {
    var len = desc.value.length;
    if(counter) {
      counter.textContent = '(' + len + '/200 ký tự)';
      counter.style.color = len >= 200 ? '#e11d48' : '#667085';
    }
  }
  desc.addEventListener('input', update);
  update();
})();
</script>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
