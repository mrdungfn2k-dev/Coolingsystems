<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center; }
.modal-box { background:#fff; border-radius:14px; padding:28px 30px; width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
.modal-box h3 { margin:0 0 20px; font-size:17px; font-weight:700; color:var(--navy); }
.form-group { margin-bottom:14px; }
.form-group label { display:block; font-size:11px; font-weight:700; color:#555; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.5px; }
.form-group input, .form-group select { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; transition:border-color 0.2s; }
.form-group input:focus, .form-group select:focus { border-color:var(--navy); outline:none; box-shadow:0 0 0 3px rgba(26,54,93,0.08); }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; padding-top:16px; border-top:1px solid #eee; }
.add-btn { padding:9px 16px; background:var(--navy); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; transition:opacity 0.2s; }
.add-btn:hover { opacity:0.9; }
.add-btn.secondary { background:#fff; color:var(--navy); border:1px solid var(--navy); }
.add-btn.secondary:hover { background:#f0f4f8; }
.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.badge-green { background:#dcfce7; color:#166534; }
.badge-yellow { background:#fef9c3; color:#854d0e; }
.badge-red { background:#fce7e7; color:#991b1b; }
.badge-blue { background:#dbeafe; color:#1e40af; }
.form-row { display:flex; gap:12px; }
.form-row > .form-group { flex:1; }
.form-hint { font-size:11px; color:#888; margin-top:3px; }
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<div class="dash-head">
    <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0">Voucher toàn sàn</h1>
    <button class="add-btn" onclick="openVoucherModal()">+ Thêm Voucher</button>
</div>

<div class="panel">
    <table class="tbl" style="width:100%">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên</th>
                <th>Loại</th>
                <th>Giảm</th>
                <th>Đã dùng</th>
                <th>Tổng</th>
                <th>Hiệu lực</th>
                <th>Trạng thái</th>
                <th style="text-align:right">Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($vouchers)): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:#888;">Chưa có voucher nào.</td></tr>
        <?php else: ?>
            <?php foreach($vouchers as $v):?>
            <tr>
                <td><strong><?= e($v['code']) ?></strong></td>
                <td><?= e($v['name'] ?? '') ?></td>
                <td>
                    <?php
                    $scopeMap = ['platform'=>'Toàn sàn','shop'=>'Shop','freeship'=>'Freeship','new_customer'=>'Khách mới'];
                    $scopeClass = ['platform'=>'badge-blue','shop'=>'badge-yellow','freeship'=>'badge-green','new_customer'=>'badge-red'];
                    $sc = $v['scope'] ?? 'platform';
                    ?>
                    <span class="badge <?= $scopeClass[$sc] ?? 'badge-blue' ?>"><?= $scopeMap[$sc] ?? $sc ?></span>
                </td>
                <td>
                    <?php if($v['discount_type'] === 'percent'): ?>
                        <?= $v['discount_value'] ?>%
                        <?php if(!empty($v['max_discount'])): ?>
                            <br><small style="color:#888">Tối đa <?= vnd($v['max_discount']) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= vnd($v['discount_value']) ?>
                    <?php endif; ?>
                </td>
                <td><?= $v['used_quantity'] ?? 0 ?></td>
                <td><?= $v['total_quantity'] ?? 0 ?></td>
                <td class="fs-12">
                    <?= date('d/m/Y', strtotime($v['valid_from'])) ?><br>
                    <small style="color:#888">→ <?= date('d/m/Y', strtotime($v['valid_to'])) ?></small>
                </td>
                <td>
                    <?php
                    $stMap = ['active'=>['Hoạt động','badge-green'],'paused'=>['Tạm dừng','badge-yellow'],'expired'=>['Hết hạn','badge-red']];
                    $st = $v['status'] ?? 'active';
                    $stInfo = $stMap[$st] ?? [$st,'badge-blue'];
                    ?>
                    <span class="badge <?= $stInfo[1] ?>"><?= $stInfo[0] ?></span>
                </td>
                <td style="text-align:right">
                    <form method="post" action="/admin/vouchers/<?= $v['id'] ?>/delete" style="margin:0;display:inline" onsubmit="return confirm('Xóa voucher này?')">
                        <?= csrfField() ?>
                        <button type="submit" style="border:none;background:none;color:#dc2626;cursor:pointer;font-size:13px;font-weight:600;">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach;?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Voucher Modal -->
<div id="voucherModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center">
    <div class="modal-box">
        <h3>➕ Thêm Voucher mới</h3>
        <form method="post" action="/admin/vouchers/add" id="voucherForm">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Mã Voucher *</label>
                    <input type="text" name="code" required style="text-transform:uppercase" placeholder="VD: GIAM10K" maxlength="30">
                </div>
                <div class="form-group">
                    <label>Tên Voucher *</label>
                    <input type="text" name="name" required placeholder="VD: Giảm 10% đơn hàng" maxlength="100">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phạm vi *</label>
                    <select name="scope" required>
                        <option value="platform">Toàn sàn</option>
                        <option value="shop">Shop</option>
                        <option value="freeship">Freeship</option>
                        <option value="new_customer">Khách mới</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="active">Hoạt động</option>
                        <option value="paused">Tạm dừng</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Loại giảm *</label>
                    <select name="discount_type" required id="discountType" onchange="toggleMaxDiscount()">
                        <option value="amount">Tiền mặt (VNĐ)</option>
                        <option value="percent">Phần trăm (%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mức giảm *</label>
                    <input type="number" name="discount_value" required min="1" placeholder="10000 hoặc 10">
                </div>
            </div>

            <div class="form-row" id="maxDiscountRow" style="display:none">
                <div class="form-group">
                    <label>Giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount" min="0" placeholder="VD: 200000">
                    <div class="form-hint">Áp dụng khi giảm theo %</div>
                </div>
                <div class="form-group">
                    <label>Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" min="0" value="0" placeholder="0">
                </div>
            </div>

            <div class="form-row" id="minOrderRow">
                <div class="form-group" id="minOrderFixed">
                    <label>Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount_fixed" min="0" value="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Số lượng phát hành *</label>
                    <input type="number" name="total_quantity" required min="1" value="100" placeholder="100">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lượt/người tối đa</label>
                    <input type="number" name="max_per_user" min="1" value="1">
                </div>
                <div class="form-group" style="flex:1"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ngày bắt đầu *</label>
                    <input type="date" name="valid_from" min="<?= date('Y-m-d') ?>" required id="inputValidFrom">
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc *</label>
                    <input type="date" name="valid_to" required id="inputValidTo">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="add-btn secondary" onclick="closeVoucherModal()">Hủy</button>
                <button type="submit" class="add-btn">💾 Lưu Voucher</button>
            </div>
        </form>
    </div>
</div>

<script>
function openVoucherModal() {
    // Set min dates to prevent past dates
    var today = new Date().toISOString().split('T')[0];
    var vf = document.getElementById('inputValidFrom');
    var vt = document.getElementById('inputValidTo');
    if(vf) vf.setAttribute('min', today);
    if(vt) vt.setAttribute('min', today);

    var m = document.getElementById('voucherModal');
    m.style.display = 'flex';
    m.style.alignItems = 'center';
    m.style.justifyContent = 'center';
    // Set default dates
    var today = new Date().toISOString().split('T')[0];
    var next30 = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0];
    var vf = document.querySelector('input[name="valid_from" min="<?= date('Y-m-d') ?>"]');
    var vt = document.querySelector('input[name="valid_to"]');
    if(vf && !vf.value) vf.value = today;
    if(vt && !vt.value) vt.value = next30;
}
function closeVoucherModal() {
    document.getElementById('voucherModal').style.display = 'none';
}
// Close on backdrop click
document.getElementById('voucherModal').addEventListener('click', function(e){
    if(e.target === this) closeVoucherModal();
});
// Close on ESC
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeVoucherModal(); });

function toggleMaxDiscount() {
    var dt = document.getElementById('discountType').value;
    var maxRow = document.getElementById('maxDiscountRow');
    var minRow = document.getElementById('minOrderRow');
    if(dt === 'percent') {
        maxRow.style.display = 'flex';
        minRow.style.display = 'none';
    } else {
        maxRow.style.display = 'none';
        minRow.style.display = 'flex';
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var startInput = document.querySelector('input[name="valid_from" min="<?= date('Y-m-d') ?>"]');
  if (startInput) {
    startInput.min = new Date().toISOString().split('T')[0];
    startInput.addEventListener('change', function() {
      if (this.value < new Date().toISOString().split('T')[0]) {
        alert('Ngày bắt đầu không được ở quá khứ');
        this.value = new Date().toISOString().split('T')[0];
      }
    });
  }
});
</script><?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var today = new Date().toISOString().split('T')[0];
  var sf = document.querySelector('input[name="valid_from"]');
  if(sf) {
    sf.setAttribute('min', today);
    sf.addEventListener('change', function() {
      if(this.value < today) {
        alert('Ngày bắt đầu không được chọn ngày đã qua!');
        this.value = today;
      }
    });
  }
  // Also validate on form submit
  var forms = document.querySelectorAll('form');
  forms.forEach(function(f) {
    f.addEventListener('submit', function(e) {
      var sd = f.querySelector('input[name="valid_from"]');
      if(sd && sd.value < today) {
        e.preventDefault();
        alert('Không thể tạo voucher với ngày bắt đầu đã qua!');
        return false;
      }
    });
  });
});
</script>