<?php $title = 'Khuyến mại'; require __DIR__.'/../partials/head.php'; ?>

<section class="block"><div class="wrap">
  <div class="sec-card"><div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:22px">Khuyến mại</h1></div></div>
    <div style="padding:20px 24px">

        <?php if (empty($products)): ?>
        <div style="text-align:center;padding:80px 20px;color:#aaa">
            <div style="font-size:48px;margin-bottom:12px"></div>
            <div style="font-size:16px;font-weight:600">Hiện chưa có sản phẩm khuyến mãi</div>
            <div style="font-size:13px;margin-top:6px">Quay lại sau bạn nhé!</div>
            <a href="/products" style="display:inline-block;margin-top:20px;padding:10px 24px;background:var(--navy);color:#fff;border-radius:6px;text-decoration:none;font-weight:600">Xem tất cả sản phẩm</a>
        </div>
        <?php else: ?>
        <div style="font-size:13px;color:#666;margin-bottom:16px">Tìm thấy <strong><?= $total ?></strong> sản phẩm đang khuyến mãi</div>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:24px; padding:4px 0;">
            <?php foreach ($products as $p): ?>
            <?php
                $discountPct = $p['sale_price'] > 0 ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
            ?>
            <a href="/products/<?= $p['id'] ?>" style="text-decoration:none;display:block;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.07);transition:transform .2s,box-shadow .2s" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 20px rgba(0,0,0,.12)'" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(0,0,0,.07)'">
                <div style="position:relative;overflow:hidden;height:180px;background:#f9f9f9">
                    <?php if ($p['main_image']): ?>
                    <img src="/uploads/products/<?= e($p['main_image']) ?>" alt="<?= e($p['name']) ?>" style="width:100%;height:100%;object-fit:cover" onerror="this.src='/assets/images/no-image.png'">
                    <?php else: ?>
                    <div style="height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:32px"></div>
                    <?php endif; ?>
                    <?php if ($discountPct > 0): ?>
                    <div style="position:absolute;top:10px;left:10px;background:var(--navy);color:#fff;font-size:12px;font-weight:800;padding:4px 10px;border-radius:20px">-<?= $discountPct ?>%</div>
                    <?php endif; ?>
                </div>
                <div style="padding:14px">
                    <div style="font-size:12px;color:#888;margin-bottom:4px"><?= e($p['shop_name']) ?></div>
                    <div style="font-size:14px;font-weight:700;color:#333;line-height:1.3;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($p['name']) ?></div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:16px;font-weight:800;color:var(--navy)"><?= vnd($p['sale_price']) ?></span>
                        <span style="font-size:12px;color:#aaa;text-decoration:line-through"><?= vnd($p['price']) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:32px">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" style="padding:8px 14px;border:1px solid <?= $i==$page ? 'var(--navy)' : '#ddd' ?>;border-radius:6px;font-size:13px;text-decoration:none;background:<?= $i==$page ? 'var(--navy)' : '#fff' ?>;color:<?= $i==$page ? '#fff' : '#333' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        </div>
  </div>
</div></section>

<?php require __DIR__.'/../partials/footer.php'; ?>
<?php require __DIR__.'/../partials/foot.php'; ?>
