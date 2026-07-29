<?php
$displayPrice = $p['price'] ?? 0;
$displayOriginalPrice = $p['original_price'] ?? null;
if (!empty($p['is_on_sale']) && !empty($p['sale_price']) && $p['sale_price'] < $p['price']) {
    $displayPrice = $p['sale_price'];
    $displayOriginalPrice = $p['price'];
}
$productPath = productPath($p);
?>
<div class="prod-card">
  <!-- Ảnh sản phẩm — bấm vào xem chi tiết -->
  <a href="<?= e($productPath) ?>" class="prod-img-wrap" style="display:block;background:#fff;aspect-ratio:4/3;border-radius:6px;overflow:hidden;position:relative;margin-bottom:10px<?= !empty($p['main_image']) ? ';--pcbg:url(\'/uploads/products/'.e($p['main_image']).'\')' : '' ?>">
    <?php if ($p['main_image']): ?>
      <img src="/uploads/products/<?= e($p['main_image']) ?>" alt="<?= e($p['name']) ?>" width="280" height="210" loading="lazy" decoding="async" style="position:relative;z-index:1;width:100%;height:100%;object-fit:cover!important;padding:0">
    <?php else: ?>
      <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#ccc">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        <div style="font-size:10px;margin-top:4px"><?= e($p['oem_code']??$p['sku']??'') ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($displayOriginalPrice) && $displayOriginalPrice > $displayPrice): ?>
      <span style="position:absolute;top:8px;left:8px;background:var(--navy);color:#fff;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:700">-<?= round(100-$displayPrice/$displayOriginalPrice*100) ?>%</span>
    <?php endif; ?>
    <?php 
      $is_new_badge = !empty($p['is_new']);
      if (!$is_new_badge && !empty($p['published_at'])) {
          if (strtotime($p['published_at']) > time() - 3600) $is_new_badge = true;
      } elseif (!$is_new_badge && !empty($p['created_at'])) {
          if (strtotime($p['created_at']) > time() - 3600) $is_new_badge = true;
      }
    ?>
    <?php if ($is_new_badge): ?>
      <span style="position:absolute;top:<?= !empty($displayOriginalPrice)&&$displayOriginalPrice>$displayPrice ? 30 : 8 ?>px;left:8px;background:var(--gold-warm);color:var(--navy-dark);padding:2px 7px;border-radius:3px;font-size:10px;font-weight:700">MỚI</span>
    <?php endif; ?>
  </a>

  <!-- Mã & tên -->
  <div style="font-size:10px;color:var(--ink-4);margin-bottom:3px">Cooling · <?= e($p['oem_code']??'') ?></div>
  <a href="<?= e($productPath) ?>" style="display:block;font-weight:600;font-size:13px;line-height:1.4;color:var(--navy-dark);text-decoration:none;margin-bottom:6px;min-height:36px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($p['name']) ?></a>

  <?php $displayRating = floatval($p['rating_avg'] ?? $p['avg_rating'] ?? 0); $displayCount = intval($p['rating_count'] ?? $p['review_count'] ?? $p['rating_count'] ?? 0); if ($displayRating > 0): ?>
    <div style="margin-bottom:4px"><span style="color:var(--gold-warm);font-size:12px"><?= stars(round($displayRating)) ?></span> <span style="color:var(--ink-4);font-size:11px">(<?= $p['review_count']??0 ?>)</span></div>
  <?php endif; ?>

  <!-- Giá + nút giỏ hàng cùng hàng -->
  <div class="prod-price-row" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;gap:4px">
    <div>
      <span style="font-size:15px;font-weight:800;color:var(--navy);white-space:nowrap"><?= vnd($displayPrice) ?></span>
      <?php if (!empty($displayOriginalPrice) && $displayOriginalPrice > $displayPrice): ?>
        <span style="text-decoration:line-through;color:var(--ink-4);font-size:11px;margin-left:4px"><?= vnd($displayOriginalPrice) ?></span>
      <?php endif; ?>
    </div>
    <!-- Nút giỏ hàng icon màu xám -->
    <?php if (($p['stock']??0) > 0): ?>
    <button type="button" onclick="ajaxAddCart(<?= $p['id'] ?>, this)" title="Thêm vào giỏ hàng" style="width:34px;height:34px;border-radius:50%;background:#f0f2f7;border:1.5px solid #d0d5e0;color:#6b7a99;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background 0.2s,color 0.2s" onmouseover="this.style.background='#1a3258';this.style.color='#fff'" onmouseout="this.style.background='#f0f2f7';this.style.color='#6b7a99'">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      </button>
    <?php endif; ?>
  </div>

  <!-- Nhãn Giá sỉ Gara -->
  <div style="margin-bottom:6px">
    <span class="gara-wholesale-badge" title="Đăng ký Gara/Đại lý để nhận bảng giá chiết khấu đơn từ 10 - 20 triệu - Hotline 0703 070 361" style="font-size:10px;font-weight:700;color:#1d4ed8;background:#eff6ff;border:1px solid #93c5fd;padding:2px 6px;border-radius:4px;display:inline-block">Giá sỉ Gara</span>
  </div>

  <!-- Footer: Còn hàng + Xem chi tiết -->
  <div style="display:flex;align-items:center;justify-content:space-between">
    <?php if (($p['stock']??0) > 0): ?>
      <span style="color:#27ae60;font-size:11px;font-weight:600">● Còn hàng</span>
    <?php else: ?>
      <span style="color:#e74c3c;font-size:11px;font-weight:600">● Hết hàng</span>
    <?php endif; ?>
    <div style="display:flex;align-items:center;gap:12px;">
      <?php
        global $user;
        $isFav = false;
        if ($user && isset($p['id'])) {
            $isFav = (bool)dbGet("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $p['id']]);
        }
      ?>
      <?php $favDataId = 'fav-'.$p['id'].'-'.uniqid(); ?>
      <button id="<?= $favDataId ?>"
        onclick="toggleFav(this, <?= $p['id'] ?>)"
        data-fav="<?= $isFav ? '1' : '0' ?>"
        title="<?= $isFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>"
        style="background:none;border:none;cursor:pointer;color:#e74c3c;padding:0;display:flex;align-items:center;transition:transform 0.15s"
        onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">
        <svg width="18" height="18"
          fill="<?= $isFav ? '#e74c3c' : 'none' ?>"
          stroke="#e74c3c" stroke-width="2" viewBox="0 0 24 24"
          style="transition:fill 0.2s">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
      </button>
      <a href="<?= e($productPath) ?>" style="font-size:11px;color:var(--navy);font-weight:700;text-decoration:none;display:flex;align-items:center;">Chi tiết <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:2px"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    </div>
  </div>
</div>
