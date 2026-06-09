<?php $title = 'Khuyến mại'; require __DIR__.'/../partials/head.php'; ?>

<style>
@media (max-width:768px){
  .promo-wide { padding-left:8px !important; padding-right:8px !important; max-width:100% !important; }
  .promo-wide .sec-card { padding-left:6px !important; padding-right:6px !important; }
  .promo-body { padding-left:6px !important; padding-right:6px !important; padding-top:12px !important; }
  .promo-wide .prod-grid { gap:10px !important; }
}
</style>
<section class="block"><div class="wrap promo-wide">
  <div class="sec-card"><div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Khuyến mại</h1></div></div>
    <div class="promo-body" style="padding:20px 24px">

        <?php if (empty($products)): ?>
        <div style="text-align:center;padding:80px 20px;color:#aaa">
            <div style="font-size:48px;margin-bottom:12px"></div>
            <div style="font-size:16px;font-weight:600">Hiện chưa có sản phẩm khuyến mãi</div>
            <div style="font-size:13px;margin-top:6px">Quay lại sau bạn nhé!</div>
            <a href="/products" style="display:inline-block;margin-top:20px;padding:10px 24px;background:var(--navy);color:#fff;border-radius:6px;text-decoration:none;font-weight:600">Xem tất cả sản phẩm</a>
        </div>
        <?php else: ?>
        <div style="font-size:13px;color:#666;margin-bottom:16px">Tìm thấy <strong><?= $total ?></strong> sản phẩm đang khuyến mãi</div>

        <div class="prod-grid cols-4">
            <?php foreach ($products as $p): require __DIR__ . '/partials/prod-card.php'; endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="margin-top:32px">
            <?php require_once __DIR__.'/../partials/pagination.php'; renderPagination($page, $totalPages, '/promotions', $_GET); ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        </div>
  </div>
</div></section>

<?php require __DIR__.'/../partials/footer.php'; ?>
<?php require __DIR__.'/../partials/foot.php'; ?>
