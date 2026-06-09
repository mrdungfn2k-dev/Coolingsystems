<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
$qtyThreshold = dbGet("SELECT value FROM system_config WHERE key='discount_quantity_threshold'")['value'] ?? 0;
$qtyPercent = dbGet("SELECT value FROM system_config WHERE key='discount_quantity_percent'")['value'] ?? 0;
?>
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
#voucherForm .cs-date { width:100%; }
#voucherForm .cs-date-trg { min-height:38px; border-color:#ddd; border-radius:6px; font-size:14px; }
#voucherForm .cs-date.open .cs-date-trg { border-color:var(--navy); box-shadow:0 0 0 3px rgba(26,54,93,0.08); }
.form-hint { font-size:11px; color:#888; margin-top:3px; }
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<div class="dash-head">
    <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0">Voucher toàn sàn</h1>
    <button class="add-btn" onclick="openVoucherModal()">+ Thêm Voucher</button>
</div>

<div class="panel" style="margin-bottom:18px">
    <div style="padding:18px 22px">
        <h3 style="font-size:16px;color:var(--navy);margin:0 0 4px;font-weight:700">Quy tắc Giảm giá số lượng</h3>
        <p style="font-size:12.5px;color:#888;margin:0 0 16px">Tự động giảm giá khi khách mua đạt số lượng sản phẩm trong cùng một đơn hàng.</p>
        <form method="post" action="/admin/vouchers/qty-discount">
            <?= csrfField() ?>
            <div class="form-row" style="align-items:flex-start">
                <div class="form-group" style="margin-bottom:0">
                    <label>Mua đạt số lượng SP (Cái)</label>
                    <input type="number" name="discount_quantity_threshold" value="<?= (int)$qtyThreshold ?>" min="0">
                    <div class="form-hint">Điền 0 nếu không áp dụng.</div>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Mức giảm giá (%)</label>
                    <input type="number" name="discount_quantity_percent" value="<?= e((string)$qtyPercent) ?>" min="0" max="100" step="0.1">
                </div>
                <div class="form-group" style="margin-bottom:0;flex:0 0 auto">
                    <label>&nbsp;</label>
                    <button type="submit" class="add-btn" style="white-space:nowrap">Lưu quy tắc</button>
                </div>
            </div>
        </form>
    </div>
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
                    <form method="post" action="/admin/vouchers/<?= $v['id'] ?>/delete" style="margin:0;display:inline" onsubmit="return csConfirmForm(this,'Xóa voucher này?')">
                        <?= csrfField() ?>
                        <button type="submit" class="adm-del">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach;?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (($totalPages ?? 1) > 1): ?>
<div style="margin-top:16px">
  <?php require_once __DIR__.'/../partials/pagination.php'; renderPagination($page, $totalPages, '/admin/vouchers', []); ?>
</div>
<?php endif; ?>

<!-- Add Voucher Modal -->
<div id="voucherModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;align-items:center;justify-content:center">
    <div class="modal-box">
        <h3>Thêm Voucher mới</h3>
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
                    <select name="scope" required id="scopeSelect" onchange="toggleScopeShop()">
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

            <div class="form-row" id="scopeShopFields" style="display:none">
                <div class="form-group" style="flex:1">
                    <label>Hãng xe áp dụng <small style="font-weight:400;color:#888">(tích chọn)</small></label>
                    <div style="max-height:120px;overflow:auto;border:1px solid #ddd;border-radius:6px;padding:8px;display:flex;flex-wrap:wrap;gap:6px 14px">
                        <?php foreach(($carBrands ?? []) as $cb): ?>
                          <label style="display:flex;align-items:center;gap:5px;font-weight:400;font-size:13px;white-space:nowrap"><input type="checkbox" name="scope_brands[]" value="<?= (int)$cb['id'] ?>" style="width:auto;margin:0"> <?= e($cb['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group" style="flex:1">
                    <label>Thương hiệu áp dụng <small style="font-weight:400;color:#888">(tích chọn)</small></label>
                    <div style="max-height:120px;overflow:auto;border:1px solid #ddd;border-radius:6px;padding:8px;display:flex;flex-wrap:wrap;gap:6px 14px">
                        <?php foreach(($productBrands ?? []) as $pb): ?>
                          <label style="display:flex;align-items:center;gap:5px;font-weight:400;font-size:13px;white-space:nowrap"><input type="checkbox" name="scope_product_brands[]" value="<?= (int)$pb['id'] ?>" style="width:auto;margin:0"> <?= e($pb['name']) ?></label>
                        <?php endforeach; ?>
                    </div>
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
                    <input type="number" name="discount_value" id="discountValue" required min="1" max="10000000" oninput="clampMax(this)" placeholder="10000 hoặc 10">
                </div>
            </div>

            <div class="form-row" id="maxDiscountRow" style="display:none">
                <div class="form-group">
                    <label>Giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount" min="0" max="10000000" oninput="clampMax(this)" placeholder="VD: 200000">
                    <div class="form-hint">Áp dụng khi giảm theo %</div>
                </div>
                <div class="form-group">
                    <label>Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" min="0" max="100000000" oninput="clampMax(this)" value="0" placeholder="0">
                </div>
            </div>

            <div class="form-row" id="minOrderRow">
                <div class="form-group" id="minOrderFixed">
                    <label>Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount_fixed" min="0" max="100000000" oninput="clampMax(this)" value="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label>Số lượng phát hành *</label>
                    <input type="number" name="total_quantity" required min="1" max="100000" oninput="clampMax(this)" value="100" placeholder="100">
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
                    <input type="date" class="js-date" name="valid_from" min="<?= date('Y-m-d') ?>" required id="inputValidFrom">
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc *</label>
                    <input type="date" class="js-date" name="valid_to" min="<?= date('Y-m-d') ?>" required id="inputValidTo">
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="add-btn secondary" onclick="closeVoucherModal()">Hủy</button>
                <button type="submit" class="add-btn">Lưu Voucher</button>
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
    var vf = document.querySelector('input[name="valid_from"]');
    var vt = document.querySelector('input[name="valid_to"]');
    if(vf && !vf.value) vf.value = today;
    if(vt && !vt.value) vt.value = next30;
    if(vf) vf.dispatchEvent(new Event('change'));
    if(vt) vt.dispatchEvent(new Event('change'));
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
    var dv = document.getElementById('discountValue'); if(dv) dv.max = (dt==='percent') ? 100 : 10000000;
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
  var startInput = document.querySelector('input[name="valid_from"]');
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
</script><script>
function clampMax(el){ var mx=parseFloat(el.max); if(isNaN(mx)) return; var d=(el.value||'').replace(/\D/g,''); if(d==='') return; if(parseInt(d,10)>mx) el.value=String(mx); }
function toggleScopeShop(){
  var s=document.getElementById('scopeSelect'); var box=document.getElementById('scopeShopFields');
  if(s&&box) box.style.display=(s.value==='shop')?'flex':'none';
}
document.addEventListener('DOMContentLoaded', function(){
  if(typeof toggleMaxDiscount==='function') toggleMaxDiscount();
  toggleScopeShop();
  var f=document.getElementById('voucherForm');
  if(f) f.addEventListener('submit', function(e){
    var s=document.getElementById('scopeSelect');
    if(s && s.value==='shop'){
      var n=f.querySelectorAll('input[name="scope_brands[]"]:checked, input[name="scope_product_brands[]"]:checked').length;
      if(n===0){ e.preventDefault(); alert('Phạm vi \"Shop\": vui lòng tích ít nhất 1 Hãng hoặc 1 Thương hiệu.'); return false; }
    }
  });
});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

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