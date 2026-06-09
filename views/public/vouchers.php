<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.promo-page { background:#fff; border:1px solid #eef0f4; border-radius:14px; padding:6px 22px 26px; }
.promo-sub { margin-top:24px; }
.promo-subhead { font-size:16px; font-weight:700; color:var(--navy); margin:0 0 14px; padding-left:12px; border-left:3px solid var(--gold-warm); line-height:1.3; }
.promo-empty { text-align:center; padding:36px 20px; background:#f9fafb; border-radius:10px; color:#888; font-size:14px; border:1px dashed #e2e6ee; }
.voucher-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
.voucher-card { background:#fff; border:2px dashed #d0d5e0; border-radius:12px; padding:20px; text-align:center; position:relative; overflow:hidden; }
.voucher-card:hover { border-color:var(--navy); box-shadow:0 4px 12px rgba(0,0,0,0.05); }
.voucher-card::before, .voucher-card::after { content:''; position:absolute; top:50%; width:24px; height:24px; background:#fff; border-radius:50%; transform:translateY(-50%); }
.voucher-card::before { left:-12px; border-right:2px dashed #d0d5e0; }
.voucher-card::after { right:-12px; border-left:2px dashed #d0d5e0; }
.v-code { display:inline-block; background:var(--navy); color:#fff; font-family:monospace; font-size:18px; font-weight:800; padding:8px 16px; border-radius:8px; margin:12px 0; letter-spacing:1px; }
.v-title { font-size:16px; font-weight:700; color:var(--navy); margin-bottom:8px; }
.v-desc { font-size:13px; color:#666; line-height:1.5; margin-bottom:16px; }
.v-btn { display:inline-block; background:#e0e6f0; color:var(--navy); border:none; padding:8px 24px; border-radius:6px; font-weight:700; cursor:pointer; transition:all 0.2s; text-decoration:none; }
.v-btn:hover { background:var(--navy); color:#fff; }
.voucher-pager { display:flex; justify-content:center; gap:8px; margin-top:22px; flex-wrap:wrap; }
.voucher-pager a { min-width:38px; height:38px; padding:0 12px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #e2e6ee; border-radius:8px; color:var(--navy); text-decoration:none; font-weight:700; font-size:14px; background:#fff; transition:all .15s; }
.voucher-pager a:hover { border-color:var(--navy); }
.voucher-pager a.active { background:var(--navy); color:#fff; border-color:var(--navy); }
@media (max-width:768px) {
  .promo-page { padding:6px 12px 20px; border-radius:10px; }
  .promo-subhead { font-size:15px; }
  .voucher-grid { grid-template-columns: repeat(2, 1fr); gap:10px; }
  .voucher-card { padding:16px 10px; }
  .voucher-card::before, .voucher-card::after { width:18px; height:18px; }
  .voucher-card::before { left:-9px; } .voucher-card::after { right:-9px; }
  .v-code { font-size:15px; padding:7px 10px; letter-spacing:0.5px; word-break:break-all; }
  .v-title { font-size:13px; }
  .v-desc { font-size:11.5px; line-height:1.45; margin-bottom:12px; }
  .v-btn { padding:7px 14px; font-size:12px; }
}
@media (max-width:380px) {
  .voucher-card { padding:14px 7px; }
  .v-code { font-size:13px; padding:6px 8px; }
}
</style>

<section class="block"><div class="wrap" style="max-width:1280px">
  <nav class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">&#8250;</span><span>Khuyến mãi</span></nav>

  <div class="promo-page">
    <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:24px">Mã Giảm Giá & Khuyến Mãi</h1></div></div>

    <!-- Mục con 1: Sản phẩm đang khuyến mãi -->
    <div class="promo-sub">
      <h2 class="promo-subhead">Sản phẩm đang khuyến mãi</h2>
      <?php if (empty($saleProducts)): ?>
        <div class="promo-empty">Hiện chưa có sản phẩm nào đang khuyến mãi.</div>
      <?php else: ?>
        <div class="prod-grid cols-4"><?php foreach ($saleProducts as $p): require __DIR__ . '/partials/prod-card.php'; endforeach; ?></div>
        <div style="text-align:center;margin-top:20px"><a href="/promotions" class="v-btn" style="background:var(--navy);color:#fff;padding:10px 26px">Xem tất cả sản phẩm khuyến mãi &#8594;</a></div>
      <?php endif; ?>
    </div>

    <!-- Mục con 2: Mã giảm giá -->
    <div class="promo-sub" id="vouchers">
      <h2 class="promo-subhead">Mã giảm giá đang có</h2>
      <?php if (empty($vouchers)): ?>
        <div class="promo-empty">Hiện tại chưa có mã giảm giá nào.</div>
      <?php else: ?>
        <div class="voucher-grid">
          <?php foreach($vouchers as $v): ?>
          <div class="voucher-card">
            <div class="v-title"><?= e($v['name'] ?? 'Mã Ưu Đãi') ?></div>
            <div class="v-code"><?= e($v['code']) ?></div>
            <div class="v-desc">
              <?php if($v['discount_type'] === 'percent'): ?>
                Giảm <?= $v['discount_value'] ?>%
                <?php if($v['max_discount'] > 0): ?> (Tối đa <?= vnd($v['max_discount']) ?>)<?php endif; ?>
              <?php else: ?>
                Giảm <?= vnd($v['discount_value'] ?? 0) ?>
              <?php endif; ?>
              <br>
              Đơn tối thiểu: <?= vnd($v['min_order_amount'] ?? 0) ?>
            </div>
            <button class="v-btn" onclick="copyVoucher('<?= e($v['code']) ?>', this)">Sao chép mã</button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if (($vTotalPages ?? 1) > 1): ?>
        <div class="voucher-pager">
          <?php for($i=1; $i<=$vTotalPages; $i++): ?>
            <a href="?vpage=<?= $i ?>#vouchers" class="<?= $i==($vPage??1)?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div></section>

<script>
function copyVoucher(code, btn) {
  navigator.clipboard.writeText(code).then(function() {
    var oldText = btn.textContent;
    btn.textContent = 'Đã chép!';
    btn.style.background = '#27ae60';
    btn.style.color = '#fff';
    setTimeout(function() { btn.textContent = oldText; btn.style.background = ''; btn.style.color = ''; }, 2000);
  });
}
</script>
<?php require __DIR__ . '/../partials/foot.php'; ?>
