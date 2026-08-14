<?php
$title = $product['name'];
$mainImg = !empty($images) ? 'https://coolingsystems.vn/uploads/products/'.str_replace('%2F', '/', rawurlencode($images[0]['file_path'])) : null;
$productUrl = productCanonicalUrl($product);

$isVerifiedGara = function_exists('isVerifiedGarage') ? isVerifiedGarage() : (!empty($user['is_verified_garage']) || !empty($user['garage_name']));
$retailPrice = (float)($product['price'] ?? 0);
$displayPrice = function_exists('getProductEffectivePrice') ? getProductEffectivePrice($product) : $retailPrice;

$isCallPrice = !empty($product['is_call_price']) || !empty($product['is_contact_price']) || !empty($product['is_call']) || empty($product['price']) || (float)$product['price'] <= 0;

if ($isCallPrice) {
    $displayOriginalPrice = null;
} elseif ($isVerifiedGara) {
    $displayOriginalPrice = ($retailPrice > $displayPrice) ? $retailPrice : null;
} else {
    $displayOriginalPrice = $product['original_price'] ?? null;
    if (!empty($product['is_on_sale']) && !empty($product['sale_price']) && $product['sale_price'] < $product['price']) {
        $displayPrice = (float)$product['sale_price'];
        $displayOriginalPrice = (float)$product['price'];
    }
}

$descriptionHtml = preg_replace('#<\s*(/?)\s*h1\b#i', '<$1h2', (string)($product['description'] ?? ''));

$descriptionPlain = seoPlainText($descriptionHtml);
$metaDescription = productMetaDescription(array_merge($product, ['display_price' => $displayPrice]));
$seo = [
    'meta_title'       => productMetaTitle($product),
    'meta_description' => $metaDescription,
    'meta_keywords'    => !empty($product['seo_keyword']) ? $product['seo_keyword'] : (!empty($product['focus_keyword']) ? $product['focus_keyword'] : $product['name'].', '.($product['oem_code']??'').', '.($product['part_brand']??'').', phụ tùng ô tô'),
    'og_type'          => 'product',
    'og_image'         => $mainImg ?? 'https://coolingsystems.vn/img/og-default.jpg',
    'canonical'        => $productUrl,
    'noindex'          => ($product['status'] ?? '') !== 'published' || ($product['is_indexed']??1) == 0,
];
require __DIR__ . '/../partials/head.php';
?>

<!-- ══ GEO: Full Structured Data ══ -->
<?php
$catName = $product['category_name'] ?? 'Phụ tùng';
$faqItems = [];
// Only mark up headings that are actual questions and whose answers are visible in the description.
if (preg_match_all('#<h[23][^>]*>(.*?)</h[23]>\s*<p[^>]*>(.*?)</p>#si', $descriptionHtml, $faqMatches, PREG_SET_ORDER)) {
    foreach($faqMatches as $faq) {
        $q = seoPlainText($faq[1]);
        $a = seoPlainText($faq[2]);
        if (str_ends_with($q, '?') && mb_strlen($q) > 5 && mb_strlen($a) > 10) {
            $faqItems[] = ['q' => $q, 'a' => $a];
        }
    }
}

$schemaImages = [];
foreach ($images as $image) {
    if (empty($image['file_path'])) continue;
    $schemaImages[] = 'https://coolingsystems.vn/uploads/products/' . str_replace('%2F', '/', rawurlencode($image['file_path']));
}
if (empty($schemaImages)) {
    $schemaImages[] = 'https://coolingsystems.vn/assets/images/default-avatar.png';
}

$brandName = trim((string)($product['part_brand'] ?? ''));
if ($brandName === '' || $brandName === 'HIDDEN') $brandName = 'CoolingSystem';

$productDesc = $descriptionPlain !== '' ? $descriptionPlain : ($metaDescription !== '' ? $metaDescription : $product['name']);

$productSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => seoPlainText($product['name'] ?? ''),
    'description' => seoTruncateText($productDesc, 5000),
    'brand' => ['@type' => 'Brand', 'name' => $brandName],
    'category' => seoPlainText($catName),
    'image' => $schemaImages,
    'url' => $productUrl,
    'sku' => !empty($product['sku']) ? seoPlainText($product['sku']) : 'SKU-' . $product['id'],
    'mpn' => !empty($product['oem_code']) ? seoPlainText($product['oem_code']) : 'OEM-' . $product['id'],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'VND',
        'price' => number_format((float)$displayPrice, 0, '.', ''),
        'availability' => ($product['stock'] ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
        'url' => $productUrl,
        'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
        'seller' => ['@type' => 'Organization', 'name' => 'CoolingSystem'],
        'hasMerchantReturnPolicy' => [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => 'VN',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => 7,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/FreeReturn',
        ],
        'shippingDetails' => [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => '0',
                'currency' => 'VND',
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => 'VN',
            ],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 0,
                    'maxValue' => 1,
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 1,
                    'maxValue' => 3,
                    'unitCode' => 'DAY',
                ],
            ],
        ],
    ],
];

$ratingVal = ($product['rating_avg'] ?? 0) > 0 ? (float)$product['rating_avg'] : 5.0;
$ratingCnt = ($product['rating_count'] ?? 0) > 0 ? (int)$product['rating_count'] : 1;

$productSchema['aggregateRating'] = [
    '@type' => 'AggregateRating',
    'ratingValue' => number_format((float)$ratingVal, 1, '.', ''),
    'reviewCount' => $ratingCnt,
    'bestRating' => '5',
    'worstRating' => '1',
];

$productSchema['review'] = [
    [
        '@type' => 'Review',
        'author' => ['@type' => 'Person', 'name' => 'Khách hàng'],
        'datePublished' => !empty($product['created_at']) ? substr($product['created_at'], 0, 10) : date('Y-m-d'),
        'reviewBody' => 'Sản phẩm chính hãng chất lượng cao, chuẩn thông số kỹ thuật.',
        'reviewRating' => [
            '@type' => 'Rating',
            'ratingValue' => number_format((float)$ratingVal, 1, '.', ''),
            'bestRating' => '5',
            'worstRating' => '1',
        ],
    ]
];

if (!empty($product['warranty_months'])) {
    $productSchema['additionalProperty'] = [[
        '@type' => 'PropertyValue',
        'name' => 'Bảo hành',
        'value' => (int)$product['warranty_months'] . ' tháng',
    ]];
}

$faqSchema = null;
if (!empty($faqItems)) {
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static function(array $faq): array {
            return [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ];
        }, $faqItems),
    ];
}
?>

<!-- Product Schema -->
<script type="application/ld+json"><?= jsonLd($productSchema) ?></script>

<?php if($faqSchema): ?>
<!-- FAQ Schema -->
<script type="application/ld+json"><?= jsonLd($faqSchema) ?></script>
<?php endif; ?>
<style>
.pd-grid{display:grid;grid-template-columns:380px 1fr;gap:28px;align-items:start;padding:0 24px 24px 24px}
@media(max-width:900px){
  .pd-grid{grid-template-columns:1fr;padding:0 16px 16px 16px}
  .pd-gallery{position:relative !important;top:0 !important}
}
.pd-gallery{position:sticky;top:100px;margin-top:0;padding:0!important;border:none!important;background:transparent!important;box-shadow:none!important}
.pd-main-img{background:#fff;border-radius:8px;padding:0;text-align:center;aspect-ratio:4/3;width:100%;height:300px;min-height:280px;max-height:310px;display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #e2e8f0;box-shadow:none;margin-top:0}
.pd-main-img img{max-width:100%;object-fit:cover!important;object-position:center;border-radius:8px;width:100%;height:100%;background:#fff}
.pd-thumbs{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.pd-thumb{width:54px;height:54px;border-radius:4px;border:2px solid transparent;overflow:hidden;cursor:pointer;background:#f5f7fb;display:flex;align-items:center;justify-content:center}
.pd-thumb img{width:100%;height:100%;object-fit:contain}
.pd-thumb.active,.pd-thumb:hover{border-color:var(--gold-warm)}
.pd-gallery{position:relative}
.pd-nav{display:none!important}
.pd-counter{position:absolute;bottom:8px;right:10px;background:rgba(0,0,0,0.55);color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;z-index:5}
.pd-main-img{position:relative;overflow:hidden;cursor:zoom-in}
.pd-main-img.zoomed{cursor:zoom-out}
.pd-zoom-controls{position:absolute;bottom:10px;right:10px;display:flex;gap:6px;z-index:30}
.pd-zoom-btn{width:32px;height:32px;background:rgba(255,255,255,0.92);border:1px solid #ddd;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px;font-weight:700;color:#333;transition:all 0.15s;box-shadow:0 2px 6px rgba(0,0,0,0.12)}
.pd-zoom-btn:hover{background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2)}
.pd-zoom-lens{position:absolute;width:130px;height:130px;border:2px solid var(--gold-warm,#c8a951);background:rgba(200,169,81,0.18);box-shadow:0 0 12px rgba(0,0,0,0.25);pointer-events:none;display:none;z-index:25;border-radius:4px}
.pd-zoom-window{position:absolute;top:0;left:calc(100% + 16px);width:420px;height:420px;border:1px solid #cbd5e1;background-color:#fff;background-repeat:no-repeat;border-radius:12px;box-shadow:0 12px 36px rgba(0,0,0,0.22);display:none;z-index:100;pointer-events:none;overflow:hidden}
@media(max-width:1080px){.pd-zoom-window{width:300px;height:300px;left:0;top:calc(100% + 10px)}}
.pd-main-img img{transition:opacity 0.3s ease,transform 0.25s ease;pointer-events:none}
.pd-price-big{font-size:30px;font-weight:800;color:var(--navy);line-height:1.2}
.pd-price-was{font-size:14px;color:#aaa;text-decoration:line-through;margin-left:8px}
.pd-discount-badge{background:#e74c3c;color:#fff;font-size:11px;font-weight:700;padding:2px 7px;border-radius:3px;vertical-align:middle;margin-left:6px}
.pd-stock-ok{color:#27ae60;font-weight:600;font-size:14px}
.pd-stock-low{color:#e67e22;font-weight:600;font-size:14px}
.pd-stock-out{color:#e74c3c;font-weight:600;font-size:14px}
.qty-wrap{display:flex;align-items:center;border:1px solid var(--line);border-radius:5px;overflow:hidden;width:fit-content}
.qty-wrap button{background:var(--gold-soft);border:none;width:38px;height:38px;font-size:20px;cursor:pointer;color:var(--navy);font-weight:600}
.qty-wrap button:hover{background:var(--gold-warm);color:#fff}
.qty-wrap input{width:52px;text-align:center;border:none;border-left:1px solid var(--line);border-right:1px solid var(--line);height:38px;font-weight:700;font-size:15px;color:var(--navy)}
.btn-buy-now{background:var(--navy);color:#fff;border:none;padding:13px 26px;border-radius:5px;font-weight:700;font-size:15px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:background 0.2s}
.btn-buy-now:hover{background:#0b1f40}
.btn-buy-now:disabled,.btn-add-cart:disabled{opacity:0.55;cursor:not-allowed}
.btn-add-cart{background:#fff;color:var(--navy);border:2px solid var(--navy);padding:11px 22px;border-radius:5px;font-weight:700;font-size:15px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s}
.btn-add-cart:hover{background:var(--gold-soft)}
.btn-chat-shop{background:#fff;color:var(--ink-2);border:1px solid var(--line);padding:9px 18px;border-radius:5px;font-weight:600;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:all 0.2s;text-decoration:none}
.btn-chat-shop:hover{border-color:var(--navy);color:var(--navy)}
.pd-action-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:20px}
.compat-tag{background:var(--gold-soft);border:1px solid #e6c860;color:var(--navy);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-block;margin:3px}
.cart-toast{position:fixed;top:80px;right:20px;background:#27ae60;color:#fff;padding:13px 22px;border-radius:8px;font-weight:600;font-size:14px;z-index:9999;display:none;box-shadow:0 4px 20px rgba(0,0,0,0.18);transform:translateX(80px);transition:transform 0.3s ease,opacity 0.3s ease;opacity:0}
.cart-toast.show{display:block;transform:translateX(0);opacity:1}
.cart-toast.err{background:#e74c3c}
.shop-card{border:1px solid var(--line);border-radius:6px;padding:14px;margin-top:18px}
.pd-trust{font-size:12px;color:var(--ink-3);line-height:1.9;margin-top:14px;padding-top:14px;border-top:1px solid var(--line)}
</style>

<div id="cartToast" class="cart-toast"></div>

<section class="block"><div class="wrap">
<article>
  <nav class="breadcrumb" aria-label="Điều hướng">
    <a href="/">Trang chủ</a><span class="sep">›</span>
    <a href="/products">Sản phẩm</a><span class="sep">›</span>
    <span><?= truncate(e($product['name']),55) ?></span>
  </nav>

  <div class="sec-card" style="padding:0;overflow:visible">
    
    <!-- Product Title (moved above grid for image-below-title layout) -->
    <div style="padding:20px 24px 0 24px">

        <h1 class="serif text-navy" style="font-family:'Times New Roman', Times, serif!important;font-size:22px;margin-bottom:12px;line-height:1.4;font-weight:700"><?= e($product['name']) ?></h1>
    </div>
    
    <div class="pd-grid">
      <!-- GALLERY -->
      <div class="pd-gallery">
        <div class="pd-main-img" id="pdImgContainer">
          <div class="pd-zoom-controls">
            <button class="pd-zoom-btn" onclick="pdZoom(1)" title="Phóng to">+</button>
            <button class="pd-zoom-btn" onclick="pdZoom(-1)" title="Thu nhỏ">−</button>
            <button class="pd-zoom-btn" onclick="pdZoomReset()" title="Kích thước gốc" style="font-size:12px">↺</button>
          </div>
          <?php if(!empty($images)): ?>
            <img src="/uploads/products/<?= e($images[0]['file_path']) ?>"
                 alt="<?= e($product['name']) ?>" id="pdMainImg"
                 fetchpriority="high" loading="eager"
                 width="600" height="600"
                 style="max-width:100%;width:100%;height:100%;object-fit:cover!important;object-position:center;border-radius:8px;background:#fff">
          <?php else: ?>
            <div style="text-align:center;color:#ccc">
              <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
              </svg>
              <div style="font-size:12px;margin-top:8px;word-break:break-all"><?= e($product['oem_code']??$product['sku']??'') ?></div>
            </div>
          <?php endif; ?>
          <?php if(!empty($images) && count($images) > 1): ?>
            <div class="pd-counter"><span id="imgIdx">1</span>/<?= count($images) ?></div>
          <?php endif; ?>
        </div>
        <?php if(!empty($images) && count($images) > 1): ?>
        <div class="pd-thumbs">
          <?php foreach($images as $i => $img): ?>
            <div class="pd-thumb <?= $i===0?'active':'' ?>"
                 onclick="switchImg(this,'<?= e($img['file_path']) ?>')">
              <img src="/uploads/products/<?= e($img['file_path']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- INFO + BUY -->
      <div>
        

        <?php if(($product['rating_avg']??0) > 0): ?>
          <div style="margin-bottom:10px;color:var(--gold-warm)">
            <?= stars($product['rating_avg']) ?>
            <span class="fs-12 text-muted">&nbsp;(<?= $product['rating_count']??0 ?> đánh giá)</span>
          </div>
        <?php endif; ?>

        <!-- Price -->
        <div style="margin:0 0 10px">
          <?php if($isCallPrice): ?>
            <span class="pd-price-big" style="font-size:22px;color:#1e293b">Liên hệ ngay: <a href="tel:0705070526" style="color:#2563eb;text-decoration:underline;font-weight:800">0705.0705.26</a></span>
          <?php else: ?>
            <span class="pd-price-big"><?= vnd($displayPrice) ?></span>
            <span class="fs-12 text-muted" style="margin-left:6px;font-weight:500">(Đã bao gồm <?= (int)($product['vat_rate']??0) ?>% VAT)</span>
            <?php if(!$isCallPrice && $displayPrice > 0 && !empty($displayOriginalPrice) && $displayOriginalPrice > $displayPrice): ?>
              <span class="pd-price-was"><?= vnd($displayOriginalPrice) ?></span>
              <span class="pd-discount-badge">-<?= round(($displayOriginalPrice-$displayPrice)/$displayOriginalPrice*100) ?>%</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php if (!empty($isVerifiedGara)): ?>
          <div style="margin:8px 0 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:8px 12px;font-size:12.5px;color:#166534;display:flex;align-items:center;gap:6px">
            <span style="font-weight:700;background:#15803d;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;text-transform:uppercase">✓ Giá buôn Gara</span>
            <span>Tài khoản Gara đã xác thực: Đã áp dụng <strong>Bảng Giá Buôn Chiết Khấu</strong><?= $retailPrice > $displayPrice ? ' (Tiết kiệm ' . vnd($retailPrice - $displayPrice) . '/sp so với giá bán lẻ)' : '' ?>.</span>
          </div>
        <?php endif; ?>


        <!-- Stock -->
        <div style="margin-bottom:12px">
          <?php if(($product['stock']??0) <= 0): ?>
            <span class="pd-stock-out"> Hết hàng</span>
          <?php elseif($product['stock'] <= 5): ?>
            <span class="pd-stock-low"> Chỉ còn <?= $product['stock'] ?> sản phẩm</span>
          <?php else: ?>
            <span class="pd-stock-ok">● Còn <?= $product['stock'] ?> sản phẩm</span>
          <?php endif; ?>

          <?php 
            $skuVal = trim($product['sku'] ?? '');
            $oemVal = trim($product['oem_code'] ?? '');
            $oem2Val = trim($product['oem_code2'] ?? '');
          ?>

          <?php if(!empty($skuVal) || !empty($oemVal)): ?>
            <div style="margin-top:6px;line-height:1.5">
              <?php if(!empty($skuVal) && !empty($oemVal) && $skuVal !== $oemVal): ?>
                <div style="color:#000;font-size:14px;font-weight:600">Mã SKU: <?= e($skuVal) ?></div>
                <div style="color:#000;font-size:14px;font-weight:600">Mã OEM: <?= e($oemVal) ?></div>
              <?php elseif(!empty($oemVal)): ?>
                <div style="color:#000;font-size:14px;font-weight:600">Mã OEM: <?= e($oemVal) ?></div>
              <?php elseif(!empty($skuVal)): ?>
                <div style="color:#000;font-size:14px;font-weight:600">Mã SKU: <?= e($skuVal) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if(!empty($product['warranty_months']) && $product['warranty_months'] > 0): ?>
          <div class="fs-13 mb-2"> Bảo hành <strong><?= (int)$product['warranty_months'] ?> tháng</strong></div>
        <?php endif; ?>

        <?php if(!empty($product['weight_g']) || !empty($product['width_cm']) || !empty($product['height_cm']) || !empty($product['depth_cm'])): ?>
          <div class="fs-13 mb-3 text-muted" style="background:#f8f9fa;padding:10px 14px;border-radius:6px;border:1px solid var(--line);line-height:1.6">
            <?php if(!empty($product['weight_g'])): ?>
              <div><strong> Khối lượng:</strong> <?= $product['weight_g'] >= 1000 ? round($product['weight_g']/1000,2).' kg' : $product['weight_g'].' g' ?></div>
            <?php endif; ?>
            <?php if(!empty($product['width_cm']) || !empty($product['height_cm']) || !empty($product['depth_cm'])): ?>
              <div><strong> Kích thước (D x R x C):</strong> 
                <?= $product['width_cm']??'?' ?> cm x <?= $product['depth_cm']??'?' ?> cm x <?= $product['height_cm']??'?' ?> cm
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Fitments -->
        <?php if(!empty($fitments)): ?>
          <div style="margin-bottom:14px">
            <div class="fs-12 fw-700 text-muted mb-1">Tương thích:</div>
            <?php foreach($fitments as $f): ?>
              <span class="compat-tag"><?= e($f['brand_name'].' '.($f['model_name']??'')) ?> <?= $f['year_from'] ?>+</span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Quantity + Buy buttons -->
        <?php if(($product['stock']??0) > 0): ?>
        <div style="margin-bottom:16px">
          <div class="fs-12 fw-700 text-muted mb-1">Số lượng:</div>
          <div class="qty-wrap">
            <button type="button" onclick="adjQty(-1)">−</button>
            <input type="number" id="pdQty" value="1" min="1" max="<?= min(10, (int)$product['stock']) ?>">
            <button type="button" onclick="adjQty(1)">+</button>
          </div>
        </div>
        <div class="pd-action-row">
          <button class="btn-buy-now" id="btnBuyNow" onclick="doBuyNow(<?= (int)$product['id'] ?>)">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M9 19a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2z"/></svg>
            Mua ngay
          </button>
          <button class="btn-add-cart" id="btnAddCart" onclick="doAddCart(<?= (int)$product['id'] ?>)">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Thêm vào giỏ
          </button>
          <?php
            $isFavDetail = false;
            if($user) {
              $isFavDetail = (bool)dbGet("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $product['id']]);
            }
          ?>
          <button id="pdFavBtn"
            onclick="toggleFav(this, <?= (int)$product['id'] ?>)"
            data-fav="<?= $isFavDetail ? '1' : '0' ?>"
            title="<?= $isFavDetail ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>"
            style="background:#fff;color:#e74c3c;border:2px solid <?= $isFavDetail ? '#e74c3c' : '#f0f0f0' ?>;padding:11px 16px;border-radius:5px;cursor:pointer;display:flex;align-items:center;transition:all 0.2s"
            onmouseover="this.style.borderColor='#e74c3c'" onmouseout="if(this.getAttribute('data-fav')!=='1')this.style.borderColor='#f0f0f0'">
            <svg id="pdFavSvg" width="20" height="20"
              fill="<?= $isFavDetail ? '#e74c3c' : 'none' ?>"
              stroke="#e74c3c" stroke-width="2" viewBox="0 0 24 24"
              style="transition:fill 0.2s">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>
        </div>
        <?php else: ?>
        <div class="pd-action-row">
          <button class="btn-buy-now" disabled>Hết hàng</button>
        </div>
        <?php endif; ?>

        <!-- Shop info + Chat -->
        <div class="shop-card">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <div>
              <div class="fs-12 text-muted mb-1">Nhà cung cấp</div>
              <div class="fw-700 text-navy" style="font-size:14px">Cooling Parts &amp; Service</div>
              <div class="fs-12 text-muted mt-1">✓ Đối tác xác minh • Bảo hành uy tín</div>
            </div>
          </div>
          <?php /* Nut chat da chuyen thanh icon noi (foot.php .floating-social) */ ?>
        </div>

        <div class="pd-trust">
           Hàng OEM chuẩn &nbsp;·&nbsp;  Bảo hành <?= (int)($product['warranty_months']??0) ?> tháng<br>
           Đổi trả 7 ngày &nbsp;·&nbsp;  Hỗ trợ kỹ thuật 24/7<br>
           Giao hàng toàn quốc — Miễn phí từ 2 triệu
        </div>
      </div>
    </div>

    <!-- Description -->
    <?php if(!empty($product['description'])): ?>
    <div style="margin-top:32px;border-top:1px solid var(--line);padding-top:24px">
      <h2 class="serif text-navy mb-2" style="font-size:18px">Mô tả sản phẩm</h2>
      <div style="line-height:1.9;color:var(--ink-2);font-size:14px" class="pd-content-html" itemprop="description"><?= $descriptionHtml ?></div>
    </div>
    <?php endif; ?>

    <!-- Features -->
    <?php if(!empty($product['features'])): ?>
    <div style="margin-top:24px;border-top:1px solid var(--line);padding-top:22px;padding-left:16px;padding-right:16px">
      <h2 class="serif text-navy mb-2" style="font-size:18px">Đặc điểm nổi bật</h2>
      <div style="line-height:1.9;color:var(--ink-2);font-size:14px" class="pd-content-html"><?= $product['features'] ?></div>
    </div>
    <?php endif; ?>

    <!-- Specs -->
    <?php if(!empty($product['specifications'])): ?>
    <div style="margin-top:24px;border-top:1px solid var(--line);padding-top:22px;padding-left:16px;padding-right:16px">
      <h2 class="serif text-navy mb-2" style="font-size:18px">Thông số kỹ thuật</h2>
      <div style="line-height:1.9;color:var(--ink-2);font-size:14px" class="pd-content-html"><?= $product['specifications'] ?></div>
    </div>
    <?php endif; ?>

    <!-- Reviews -->
    <script>
/* Gửi đánh giá bằng AJAX, chỉ làm mới phần Đánh giá (không reload cả trang, không vẽ lại gallery) */
function submitReview(e, form){
  e.preventDefault();
  var btn=form.querySelector('button[type="submit"]'); var oldHtml=btn?btn.innerHTML:'';
  if(btn){ btn.disabled=true; btn.style.opacity='0.6'; btn.innerHTML='Đang gửi...'; }
  fetch(form.getAttribute('action'), {method:'POST', body:new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin', redirect:'follow'})
    .then(function(r){ return r.text(); })
    .then(function(html){
      var doc=new DOMParser().parseFromString(html,'text/html');
      var nsec=doc.getElementById('reviewSection'), csec=document.getElementById('reviewSection');
      if(nsec && csec){
        csec.innerHTML=nsec.innerHTML;
        if(window.coolToastShow) coolToastShow('Đã gửi đánh giá của bạn!','✅');
        try{ csec.scrollIntoView({behavior:'smooth',block:'start'}); }catch(_){}
      } else { location.reload(); }
    })
    .catch(function(){
      if(btn){ btn.disabled=false; btn.style.opacity=''; btn.innerHTML=oldHtml; }
      if(window.coolToastShow) coolToastShow('Lỗi gửi đánh giá, vui lòng thử lại.','⚠️');
    });
  return false;
}
</script>
    <div id="reviewSection" style="margin-top:30px;border-top:1px solid var(--line);padding-top:24px;padding-left:16px;padding-right:16px">
      <h2 class="serif text-navy mb-3" style="font-size:18px">Đánh giá <?= !empty($reviews) ? '('.count($reviews).')' : '' ?></h2>
      
      <?php
        $curUser = currentUser();
        $userExistingReview = null;
        if($curUser && $curUser['role']==='customer') {
          $userExistingReview = dbGet("SELECT * FROM reviews WHERE product_id=? AND user_id=?", [(int)$product['id'], $curUser['id']]);
        }
      ?>

      <?php if($curUser && $curUser['role'] === 'customer'): ?>
      <?php if($userExistingReview): ?>
      <!-- User already reviewed - edit form hidden, accessible via Chinh sua button in review list -->
      <div id="editReviewBox" style="display:none;background:#f8f9fa;padding:16px;border-radius:6px;border:1px solid var(--line);margin-bottom:20px">
        <h3 class="fs-14 mb-2">Chỉnh sửa đánh giá</h3>
        <form method="post" action="/products/<?= (int)$product['id'] ?>/reviews" enctype="multipart/form-data" onsubmit="return submitReview(event, this)">
          <?= csrfField() ?>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Đánh giá sao:</label>
            <select name="rating" style="padding:8px;border-radius:4px;border:1px solid #ddd">
              <?php for($s=5;$s>=1;$s--): ?>
              <option value="<?= $s ?>" <?= ($userExistingReview['rating_overall']??5)==$s?'selected':'' ?>><?= str_repeat('',$s).str_repeat('',5-$s) ?> (<?= $s ?> sao)</option>
              <?php endfor; ?>
            </select>
          </div>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Nội dung:</label>
            <textarea maxlength="100" name="comment" rows="3" required style="width:100%;padding:10px;border-radius:4px;border:1px solid #ddd;font-family:inherit"><?= e($userExistingReview['comment']??'') ?></textarea>
          </div>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Hình ảnh mới (không bắt buộc):</label>
            <input type="file" name="images[]" accept="image/*" multiple style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;font-size:13px" onchange="previewReviewImages(this)">
            <div id="reviewImagePreview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"></div>
          </div>
          <button type="submit" class="btn btn-navy btn-sm" style="padding:8px 16px">Cập nhật đánh giá</button>
        </form>
      </div>
      <?php else: ?>
      <div style="background:#f8f9fa;padding:16px;border-radius:6px;border:1px solid var(--line);margin-bottom:20px">
        <h3 class="fs-14 mb-2">Viết đánh giá của bạn</h3>
        <form method="post" action="/products/<?= (int)$product['id'] ?>/reviews" enctype="multipart/form-data" onsubmit="return submitReview(event, this)">
          <?= csrfField() ?>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Đánh giá sao:</label>
            <select name="rating" style="padding:8px;border-radius:4px;border:1px solid #ddd">
              <option value="5"> (5 sao)</option>
              <option value="4"> (4 sao)</option>
              <option value="3"> (3 sao)</option>
              <option value="2"> (2 sao)</option>
              <option value="1"> (1 sao)</option>
            </select>
          </div>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Nội dung đánh giá:</label>
            <textarea name="comment" rows="3" required maxlength="500" placeholder="Nhập nhận xét của bạn về sản phẩm (tối đa 100 từ)..." style="width:100%;padding:10px;border-radius:4px;border:1px solid #ddd;font-family:inherit;resize:none" oninput="limitWords(this,100)"></textarea>
            <div id="wordCount" style="text-align:right;font-size:11px;color:#999;margin-top:2px">0/100 từ</div>
          </div>
          <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px;font-size:13px;font-weight:600">Hình ảnh (không bắt buộc):</label>
            <input type="file" name="images[]" accept="image/*" multiple style="width:100%;padding:8px;border-radius:4px;border:1px solid #ddd;font-size:13px" onchange="previewReviewImages(this)">
            <div id="reviewImagePreview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"></div>
          </div>
          <button type="submit" class="btn btn-navy btn-sm" style="padding:8px 16px">Gửi đánh giá</button>
        </form>
      </div>
      <?php endif; // end if userExistingReview ?>
      <?php else: ?>
        <div style="margin-bottom:20px;font-size:13px"><a href="/auth/login" rel="nofollow" class="text-navy fw-600">Đăng nhập</a> để viết đánh giá.</div>
      <?php endif; ?>

      <?php if(!empty($reviews)): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:14px 0;border-bottom:1px solid var(--line)">
        <span style="font-size:32px;font-weight:800;color:var(--navy)"><?= number_format($product['rating_avg'], 1) ?></span>
        <div>
          <div style="color:var(--gold-warm);font-size:16px"><?= stars($product['rating_avg']) ?></div>
          <div style="font-size:12px;color:var(--ink-3)"><?= $product['rating_count'] ?> đánh giá</div>
        </div>
      </div>
      <?php foreach($reviews as $r): ?>
      <?php
        $reactions = dbAll("SELECT reaction, COUNT(*) as cnt FROM review_reactions WHERE review_id=? GROUP BY reaction", [$r['id']]);
        $reactionMap = []; foreach($reactions as $rx) $reactionMap[$rx['reaction']] = $rx['cnt'];
        $myReaction = ($curUser) ? (dbGet("SELECT reaction FROM review_reactions WHERE review_id=? AND user_id=?", [$r['id'], $curUser['id']])['reaction'] ?? null) : null;
      ?>
      <div style="border-bottom:1px solid var(--line);padding:14px 0" id="review-<?= $r['id'] ?>">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <?php if(!empty($r['avatar'])): ?>
            <img src="/uploads/avatars/<?= e($r['avatar']) ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #eee">
          <?php else: ?>
            <div style="width:36px;height:36px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px"><?= mb_substr($r['full_name'] ?? 'U', 0, 1) ?></div>
          <?php endif; ?>
          <strong><?= e($r['full_name']) ?></strong>
          <span style="color:var(--gold-warm)"><?= stars($r['rating_overall']??5) ?></span>
          <span class="fs-12 text-muted"><?= relTime($r['created_at']) ?></span>
        </div>
        <?php if(!empty($r['comment'])): ?>
          <div style="margin-top:6px;color:var(--ink-2);font-size:14px"><?= e($r['comment']) ?></div>
        <?php endif; ?>
        <?php if(!empty($r['image'])): ?>
          <div style="margin-top:10px">
            <?php
                $imgs = explode(',', $r['image']);
                foreach ($imgs as $img):
                  $img = trim($img);
                  if ($img):
                ?>
                <img src="/uploads/reviews/<?= e($img) ?>" alt="Hình ảnh đánh giá" style="max-height:100px;width:100px;border-radius:6px;border:1px solid #ddd;object-fit:cover;cursor:pointer" onclick="window.open(this.src,'_blank')">
                <?php endif; endforeach; ?>
          </div>
        <?php endif; ?>
        <!-- Emoji reaction buttons -->
        <div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap">
          <?php
            $emojis = ['like'=>'\xF0\x9F\x91\x8D','love'=>'\xE2\x9D\xA4\xEF\xB8\x8F','haha'=>'\xF0\x9F\x98\x82','wow'=>'\xF0\x9F\x98\xAE','sad'=>'\xF0\x9F\x98\xA2'];
            foreach(['like'=>'👍','love'=>'❤️','haha'=>'😂','wow'=>'😮','sad'=>'😢'] as $ek=>$ev):
              $cnt = $reactionMap[$ek] ?? 0;
              $isMine = ($myReaction === $ek);
          ?>
          <button onclick="reactReview(<?= $r['id'] ?>, '<?= $ek ?>', this)"
            style="display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:14px;border:1px solid <?= $isMine ? 'var(--navy)' : '#e0e0e0' ?>;background:<?= $isMine ? '#eef2ff' : '#fafafa' ?>;cursor:pointer;font-size:13px;transition:all 0.15s"
            data-reaction="<?= $ek ?>"
            data-mine="<?= $isMine ? '1' : '0' ?>">
            <span><?= $ev ?></span>
            <span class="rxn-count" style="font-size:11px;color:#666;font-weight:600"><?= $cnt > 0 ? $cnt : '' ?></span>
          </button>
          <?php endforeach; ?>
        </div>
        <!-- Facebook-style action row -->
        <div style="margin-top:6px;display:flex;align-items:center;gap:2px">
          <!-- Like button with reaction popup -->
          <div style="position:relative">
            <button class="rxn-trigger fb-action-btn <?= $myReaction ? 'rxn-active' : '' ?>"
              id="likeBtn-<?= $r['id'] ?>"
              onclick="toggleReactionPicker(this,<?= $r['id'] ?>)"
              style="background:none;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600;color:<?= $myReaction ? '#1877f2' : '#65676b' ?>;display:flex;align-items:center;gap:4px;transition:background 0.15s"
              onmouseover="this.style.background='#f0f2f5'" onmouseout="this.style.background='none'">
              <?= $myReaction ? getRxnEmoji($myReaction) : '' ?> <span id="likeLabel-<?= $r['id'] ?>"><?= $myReaction ? ucfirst($myReaction) : 'Thích' ?></span>
            </button>
            <!-- Reaction popup -->
            <div class="rxn-picker" id="rxnPicker-<?= $r['id'] ?>"
              style="display:none;position:absolute;bottom:calc(100% + 4px);left:0;background:#fff;border:none;border-radius:30px;padding:6px 10px;box-shadow:0 2px 12px rgba(0,0,0,0.2);gap:4px;z-index:200;white-space:nowrap">
              <?php foreach(['&#x1F44D;'=>'like','&#x2764;&#xFE0F;'=>'heart','&#x1F602;'=>'laugh','&#x1F62E;'=>'wow','&#x1F622;'=>'sad','&#x1F620;'=>'angry'] as $emoji=>$key): ?>
              <button onclick="sendReaction(<?= $r['id'] ?>,'<?= $key ?>',this)"
                style="background:none;border:none;font-size:24px;cursor:pointer;padding:2px 3px;border-radius:50%;transition:transform 0.15s;line-height:1"
                title="<?= ucfirst($key) ?>"
                onmouseover="this.style.transform='scale(1.4)'" onmouseout="this.style.transform='scale(1)'"><?= $emoji ?></button>
              <?php endforeach; ?>
            </div>
          </div>
          <span style="color:#ccc;font-size:10px">·</span>
          <!-- Edit button (only for own review) -->
          <?php if($curUser && $r['user_id'] == $curUser['id']): ?>
          <button onclick="document.getElementById('editReviewBox').style.display=document.getElementById('editReviewBox').style.display==='none'||!document.getElementById('editReviewBox').style.display?'block':'none'"
            style="background:none;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600;color:#65676b;display:flex;align-items:center;gap:4px;transition:background 0.15s"
            onmouseover="this.style.background='#f0f2f5'" onmouseout="this.style.background='none'">
             Chỉnh sửa
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
        <div class="text-muted fs-13">Chưa có đánh giá nào cho sản phẩm này.</div>
      <?php endif; ?>

<script>
function getRxnEmojiJS(k){return{like:'\uD83D\uDC4D',heart:'\u2764',laugh:'\uD83D\uDE02',wow:'\uD83D\uDE2E',sad:'\uD83D\uDE22',angry:'\uD83D\uDE20'}[k]||'\uD83D\uDC4D';}
function toggleReactionPicker(btn, rid) {
  var picker = document.getElementById('rxnPicker-'+rid);
  document.querySelectorAll('.rxn-picker').forEach(function(p){ if(p!==picker) p.style.display='none'; });
  var isOpen = picker.style.display==='flex';
  picker.style.display = isOpen ? 'none' : 'flex';
  if(!isOpen) { picker.style.alignItems='center'; picker.style.gap='4px'; }
}
document.addEventListener('click', function(e){
  if(!e.target.closest('.rxn-trigger')&&!e.target.closest('.rxn-picker')) {
    document.querySelectorAll('.rxn-picker').forEach(function(p){ p.style.display='none'; });
  }
});
function sendReaction(rid, reaction, emojiBtn) {
  document.getElementById('rxnPicker-'+rid).style.display='none';
  var csrf = (typeof _csrf !== 'undefined') ? _csrf : ((typeof window._CSRF !== 'undefined') ? window._CSRF : '');
  fetch('/products/'+rid+'/reactions', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'_csrf='+encodeURIComponent(csrf)+'&reaction='+encodeURIComponent(reaction)
  }).then(r=>r.json()).then(data=>{
    var likeBtn = document.getElementById('likeBtn-'+rid);
    var likeLabel = document.getElementById('likeLabel-'+rid);
    if(data.ok) {
      if(data.removed) {
        if(likeBtn){ likeBtn.innerHTML=' <span id="likeLabel-'+rid+'">Thích</span>'; likeBtn.style.color='#65676b'; }
      } else {
        var em = getRxnEmojiJS(reaction);
        if(likeLabel) likeLabel.textContent = reaction.charAt(0).toUpperCase()+reaction.slice(1);
        if(likeBtn){ likeBtn.innerHTML=em+' <span id="likeLabel-'+rid+'">'+(reaction.charAt(0).toUpperCase()+reaction.slice(1))+'</span>'; likeBtn.style.color='#1877f2'; }
      }
    }
  }).catch(()=>{});
}
// Override toggleFav for detail page
window.toggleFav = function toggleFav(btn, productId) {
  var isFav = btn.getAttribute('data-fav') === '1';
  var newFav = !isFav;
  var svg = btn.querySelector('svg');
  svg.setAttribute('fill', newFav ? '#e74c3c' : 'none');
  btn.setAttribute('data-fav', newFav ? '1' : '0');
  btn.style.borderColor = newFav ? '#e74c3c' : '#f0f0f0';
  btn.title = newFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
  var csrf = (typeof _csrf !== 'undefined') ? _csrf : ((typeof window._CSRF !== 'undefined') ? window._CSRF : '');
  fetch('/customer/favorites/toggle', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    credentials: 'same-origin',
    body: '_csrf=' + encodeURIComponent(csrf) + '&product_id=' + encodeURIComponent(productId)
  }).then(function(r){ return r.json(); }).then(function(data){
    if(!data.ok){
      svg.setAttribute('fill', isFav?'#e74c3c':'none');
      btn.setAttribute('data-fav', isFav?'1':'0');
      btn.style.borderColor = isFav?'#e74c3c':'#f0f0f0';
      btn.title = isFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
      if (data.redirect) {
        showFavLoginPopup(data.redirect);
      }
    } else {
      svg.setAttribute('fill', data.fav?'#e74c3c':'none');
      btn.setAttribute('data-fav', data.fav?'1':'0');
      btn.style.borderColor = data.fav?'#e74c3c':'#f0f0f0';
      btn.title = data.fav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
      // Update header fav count without reload
      if (typeof updateFavBadge === 'function') {
        updateFavBadge(data.count !== undefined ? data.count : (data.fav ? 1 : 0));
      } else {
        var favEl = document.querySelector('a[href="/customer/favorites"] .count');
        if(favEl) {
          var cur = parseInt(favEl.textContent) || 0;
          favEl.textContent = data.fav ? (cur + 1) : Math.max(0, cur - 1);
        }
      }
    }
  }).catch(function(){
    svg.setAttribute('fill', isFav?'#e74c3c':'none');
    btn.setAttribute('data-fav', isFav?'1':'0');
    btn.style.borderColor = isFav?'#e74c3c':'#f0f0f0';
    btn.title = isFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
  });
}
</script>
    </div>

    <!-- Related -->
    <?php if(!empty($related)): ?>
    <div style="margin-top:30px;border-top:1px solid var(--line);padding-top:24px;padding-left:16px;padding-right:16px">
      <h2 class="serif text-navy mb-3" style="font-size:18px">Sản phẩm liên quan</h2>
      <div class="prod-grid">
        <?php foreach($related as $p): require __DIR__.'/partials/prod-card.php'; endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recently Viewed Products Section -->
    <style>
    .rv-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 16px;
    }
    @media (max-width: 640px) {
      .rv-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
      }
      .rv-card {
        padding: 8px !important;
      }
    }
    </style>
    <div id="recentlyViewedSection" style="margin-top:30px;border-top:1px solid var(--line);padding-top:24px;padding-left:16px;padding-right:16px;display:none">
      <h2 class="serif text-navy mb-3" style="font-size:18px;font-weight:700">
        Sản phẩm bạn đã xem gần đây
      </h2>
      <div id="recentlyViewedGrid" class="rv-grid"></div>
    </div>
  </div>
</article>
</div></section>

<script>
var _csrf = '<?= csrfToken() ?>'; window._CSRF = _csrf;
var _pdStock = Math.min(10, <?= (int)($product['stock']??0) ?>);

var _imgList = [<?php if(!empty($images)): foreach($images as $img): ?>'<?= e($img["file_path"]) ?>',<?php endforeach; endif; ?>];
var _imgIdx = 0;
var _autoTimer = null;

function slideImg(dir, e) {
  if (e) { e.preventDefault(); e.stopPropagation(); }
  if(!_imgList.length) return;
  _imgIdx = (_imgIdx + dir + _imgList.length) % _imgList.length;
  var main = document.getElementById('pdMainImg');
  if(main) {
    main.style.opacity = '0.3';
    setTimeout(function(){
      main.src = '/uploads/products/' + _imgList[_imgIdx];
      main.style.opacity = '1';
    }, 150);
  }
  document.querySelectorAll('.pd-thumb').forEach(function(t,i){ t.classList.toggle('active', i===_imgIdx); });
  var c = document.getElementById('imgIdx'); if(c) c.textContent = _imgIdx+1;
  resetAuto();
}

function startAuto() {
  if(_imgList.length <= 1) return;
  _autoTimer = setInterval(function(){ slideImg(1); }, 4000);
}
function resetAuto() { clearInterval(_autoTimer); startAuto(); }
startAuto();

function switchImg(el, fp) {
  var idx = _imgList.indexOf(fp.replace('/uploads/products/',''));
  if(idx === -1) idx = _imgList.indexOf(fp);
  if(idx !== -1) _imgIdx = idx;
  var main = document.getElementById('pdMainImg');
  var c = document.getElementById('imgIdx'); if(c) c.textContent = _imgIdx+1;
  resetAuto();
  if(main) main.src = '/uploads/products/' + fp;
  document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
}

function adjQty(d) {
  var inp = document.getElementById('pdQty');
  inp.value = Math.max(1, Math.min(_pdStock, parseInt(inp.value||1) + d));
}

function showToast(msg, isErr) {
  var t = document.getElementById('cartToast');
  t.textContent = msg;
  t.className = 'cart-toast' + (isErr?' err':'');
  t.classList.add('show');
  clearTimeout(window._tt);
  window._tt = setTimeout(function(){ t.classList.remove('show'); }, 3000);
}

function updateCartBadge(n, totalText) {
  var cc=parseInt(n)||0;
  document.querySelectorAll('.count,[data-cart-count]').forEach(function(b){
    b.textContent = cc>0?cc:'';
    b.style.display = cc>0?'inline-block':'none';
  });
  var mb=document.querySelector('.cart-badge-mobile');
  if(!mb&&cc>0){var mc=document.querySelector('.mobile-cart-btn');if(mc){mb=document.createElement('span');mb.className='cart-badge-mobile';mb.style.cssText='';mc.appendChild(mb);}}
  if(mb){mb.textContent=cc;mb.style.display=cc>0?'flex':'none';}
  document.querySelectorAll('a[href="/customer/cart"]').forEach(function(link){
    var badge=link.querySelector('.count');
    if(!badge&&cc>0){badge=document.createElement('span');badge.className='count';link.style.position='relative';link.appendChild(badge);}
    if(badge){badge.textContent=cc;badge.style.display=cc>0?'inline-block':'none';}
    if(totalText){var val=link.querySelector('.value');if(val)val.textContent=totalText;}
  });
}

function cartPost(pid, qty, cb) {
  var fd = new FormData();
  fd.append('product_id', pid);
  fd.append('qty', qty);
  fd.append('_csrf', _csrf);
  fetch('/customer/cart/add', {method:'POST', body:fd, credentials:'same-origin'})
    .then(function(r){
      if(!r.ok) throw new Error('HTTP '+r.status);
      return r.json();
    })
    .then(cb)
    .catch(function(err){
      showToast('Lỗi kết nối, vui lòng thử lại', true);
      document.getElementById('btnAddCart').disabled = false;
      document.getElementById('btnBuyNow').disabled = false;
    });
}

function doAddCart(pid) {
  var qty = parseInt(document.getElementById('pdQty').value)||1;
  var btn = document.getElementById('btnAddCart');
  btn.disabled = true; btn.textContent = 'Đang thêm...';
  cartPost(pid, qty, function(data){
    if(data.redirect){ window.location.href=data.redirect; return; }
    if(data.ok){
      showToast(' ' + data.msg, false);
      updateCartBadge(data.cartCount, data.cart_total);
    } else {
      showToast(' ' + (data.msg||'Lỗi'), true);
    }
    btn.disabled = false;
    btn.innerHTML = '<svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> Thêm vào giỏ';
  });
}

function doBuyNow(pid) {
  var qty = parseInt(document.getElementById('pdQty').value)||1;
  var btn = document.getElementById('btnBuyNow');
  btn.disabled = true; btn.textContent = 'Đang xử lý...';
  cartPost(pid, qty, function(data){
    if(data.redirect){ window.location.href=data.redirect; return; }
    if(data.ok){ window.location.href='/customer/cart'; }
    else {
      showToast(' ' + (data.msg||'Lỗi'), true);
      btn.disabled=false; btn.textContent='Mua ngay';
    }
  });
}

document.getElementById('pdQty')?.addEventListener('change', function(){
  var val = parseInt(this.value)||1;
  if (val > 10) {
    showToast('Mỗi sản phẩm chỉ được đặt tối đa 10 số lượng', true);
    val = 10;
  }
  this.value = Math.max(1, Math.min(_pdStock, val));
});
</script>
<script>
var _pdZoomLevel = 1;
var _pdZoomMin = 1;
var _pdZoomMax = 3;
var _pdZoomStep = 0.4;
var translateX = 0;
var translateY = 0;

function pdZoom(dir) {
  _pdZoomLevel = Math.max(_pdZoomMin, Math.min(_pdZoomMax, _pdZoomLevel + dir * _pdZoomStep));
  var img = document.getElementById('pdMainImg');
  if (img) {
    if (_pdZoomLevel <= 1) {
      translateX = 0;
      translateY = 0;
    }
    img.style.transform = 'scale(' + _pdZoomLevel + ') translate(' + (translateX / _pdZoomLevel) + 'px, ' + (translateY / _pdZoomLevel) + 'px)';
    img.style.transition = 'transform 0.25s ease';
    img.style.transformOrigin = 'center center';
    var container = document.getElementById('pdImgContainer');
    if (container) container.classList.toggle('zoomed', _pdZoomLevel > 1);
  }
}

function pdZoomReset() {
  _pdZoomLevel = 1;
  translateX = 0;
  translateY = 0;
  var img = document.getElementById('pdMainImg');
  if (img) { 
    img.style.transform = 'scale(1)'; 
    img.style.transition = 'transform 0.25s ease';
  }
  var container = document.getElementById('pdImgContainer');
  if (container) container.classList.remove('zoomed');
}

// Click to zoom in/out on image & Mouse/Touch zoom and pan
document.addEventListener('DOMContentLoaded', function() {
  var container = document.getElementById('pdImgContainer');
  var img = document.getElementById('pdMainImg');
  if (!container || !img) return;

  // Click handler: ONLY zoom when clicking directly on image (ignore arrows, counter & zoom btns)
  container.addEventListener('click', function(e) {
    if (e.target.closest('.pd-zoom-btn') || e.target.closest('.pd-nav') || e.target.closest('.pd-counter')) return;
    if (_pdZoomLevel < _pdZoomMax) {
      pdZoom(1);
    } else {
      pdZoomReset();
    }
  });

  // In-Place Inner Container Zoom (Soi phóng to trực tiếp trong khung hình khi rà chuột)
  container.addEventListener('mousemove', function(e) {
    var rect = container.getBoundingClientRect();
    var x = ((e.clientX - rect.left) / rect.width * 100).toFixed(1);
    var y = ((e.clientY - rect.top) / rect.height * 100).toFixed(1);
    img.style.transformOrigin = x + '% ' + y + '%';

    if (_pdZoomLevel === 1) {
      img.style.transform = 'scale(2.2)';
      img.style.transition = 'transform 0.1s ease-out';
    }
  });

  container.addEventListener('mouseleave', function() {
    if (_pdZoomLevel === 1) {
      img.style.transform = 'scale(1)';
      img.style.transformOrigin = 'center center';
      img.style.transition = 'transform 0.25s ease';
    }
  });

  
  // Mouse wheel zoom
  container.addEventListener('wheel', function(e) {
    e.preventDefault();
    if (e.deltaY < 0) { pdZoom(1); } else { pdZoom(-1); }
  }, { passive: false });

  // Touch Support (Pinch to Zoom & Drag to Pan on Mobile)
  var touchStartDist = 0;
  var touchStartScale = 1;
  var isDragging = false;
  var startX = 0, startY = 0;

  container.addEventListener('touchstart', function(e) {
    if (e.touches.length === 2) {
      // Pinch started
      touchStartDist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      touchStartScale = _pdZoomLevel;
    } else if (e.touches.length === 1 && _pdZoomLevel > 1) {
      // Drag started when zoomed
      isDragging = true;
      startX = e.touches[0].clientX - translateX;
      startY = e.touches[0].clientY - translateY;
    }
  }, { passive: true });

  container.addEventListener('touchmove', function(e) {
    if (e.touches.length === 2) {
      // Pinch zooming
      var dist = Math.hypot(
        e.touches[0].clientX - e.touches[1].clientX,
        e.touches[0].clientY - e.touches[1].clientY
      );
      var factor = dist / touchStartDist;
      _pdZoomLevel = Math.max(_pdZoomMin, Math.min(_pdZoomMax, touchStartScale * factor));
      
      img.style.transition = 'none';
      img.style.transform = 'scale(' + _pdZoomLevel + ') translate(' + (translateX / _pdZoomLevel) + 'px, ' + (translateY / _pdZoomLevel) + 'px)';
      container.classList.toggle('zoomed', _pdZoomLevel > 1);
      if (e.cancelable) e.preventDefault();
    } else if (e.touches.length === 1 && isDragging) {
      // Drag panning
      translateX = e.touches[0].clientX - startX;
      translateY = e.touches[0].clientY - startY;

      img.style.transition = 'none';
      img.style.transform = 'scale(' + _pdZoomLevel + ') translate(' + (translateX / _pdZoomLevel) + 'px, ' + (translateY / _pdZoomLevel) + 'px)';
      if (e.cancelable) e.preventDefault();
    }
  }, { passive: false });

  container.addEventListener('touchend', function(e) {
    isDragging = false;
    if (_pdZoomLevel <= 1) {
      translateX = 0;
      translateY = 0;
      img.style.transition = 'transform 0.25s ease';
      img.style.transform = 'scale(1)';
    } else {
      img.style.transition = 'transform 0.1s ease';
      img.style.transform = 'scale(' + _pdZoomLevel + ') translate(' + (translateX / _pdZoomLevel) + 'px, ' + (translateY / _pdZoomLevel) + 'px)';
    }
  }, { passive: true });
});
</script>
<?php if(!empty($product['video_url'])): ?>
<section class="block" style="padding:20px 0">
<div class="wrap">
<div class="panel">
  <div class="panel-head"><h3 style="text-transform:uppercase;letter-spacing:.05em">Video San Pham</h3></div>
  <div class="panel-body" style="text-align:center;padding:24px">
    <?php
      $vidUrl = $product['video_url'];
      preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $vidUrl, $ytM);
      preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $vidUrl, $ytS);
      $ytId = $ytM[1] ?? ($ytS[1] ?? '');
      if ($ytId):
    ?>
      <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;max-width:720px;margin:0 auto">
        <iframe style="position:absolute;top:0;left:0;width:100%;height:100%" 
                src="https://www.youtube.com/embed/<?= e($ytId) ?>" 
                frameborder="0" allowfullscreen></iframe>
      </div>
    <?php elseif(preg_match('/\.(mp4|webm|ogg)$/i', $vidUrl)): ?>
      <video src="<?= e($vidUrl) ?>" controls style="max-width:720px;width:100%;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1)"></video>
    <?php else: ?>
      <a href="<?= e($vidUrl) ?>" target="_blank" class="btn btn-navy" style="margin:20px auto">Xem Video San Pham</a>
    <?php endif; ?>
  </div>
</div>
</div>
</section>
<?php endif; ?>


<script>
function reactReview(reviewId, reaction, btn) {
  var csrf = '';
  if (typeof window._CSRF !== 'undefined') csrf = window._CSRF;
  else { var ci = document.querySelector('meta[name="csrf-token"]'); if(ci) csrf = ci.content; }
  if (!csrf) { var ci2 = document.querySelector('input[name="_csrf"]'); if(ci2) csrf = ci2.value; }
  
  if (!csrf) { alert('Vui lòng đăng nhập để thả cảm xúc'); return; }
  
  fetch('/reviews/' + reviewId + '/react', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&reaction=' + encodeURIComponent(reaction)
  }).then(function(r){ return r.json(); }).then(function(data){
    if (data.ok) {
      // Reset all buttons in this review
      var reviewDiv = document.getElementById('review-' + reviewId);
      var allBtns = reviewDiv.querySelectorAll('button[data-reaction]');
      allBtns.forEach(function(b){
        b.style.border = '1px solid #e0e0e0';
        b.style.background = '#fafafa';
        b.setAttribute('data-mine', '0');
      });
      
      // Update counts
      if (data.counts) {
        allBtns.forEach(function(b){
          var rk = b.getAttribute('data-reaction');
          var countEl = b.querySelector('.rxn-count');
          var c = data.counts[rk] || 0;
          countEl.textContent = c > 0 ? c : '';
        });
      }
      
      // Highlight active
      if (data.active) {
        btn.style.border = '1px solid var(--navy)';
        btn.style.background = '#eef2ff';
        btn.setAttribute('data-mine', '1');
      }
    }
  });
}
</script>

<div id="favLoginModal" style="display:none;position:fixed;inset:0;background:rgba(15,35,66,.55);z-index:99999;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:14px;max-width:380px;width:100%;padding:26px 24px;box-shadow:0 20px 60px rgba(0,0,0,.3);text-align:center">
    <div style="width:54px;height:54px;margin:0 auto 14px;border-radius:50%;background:#eef2f9;display:flex;align-items:center;justify-content:center">
      <svg width="26" height="26" fill="none" stroke="#1a3258" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <h3 style="margin:0 0 8px;font-size:18px;color:#1a3258;font-weight:800">Cần đăng nhập</h3>
    <p style="margin:0 0 20px;font-size:14px;color:#666;line-height:1.55">Bạn cần đăng nhập để thêm sản phẩm vào danh sách yêu thích.</p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button type="button" onclick="document.getElementById('favLoginModal').style.display='none'" style="flex:1;height:42px;border-radius:10px;border:1.5px solid #d6deea;background:#fff;color:#1a3258;font-weight:700;font-size:14px;cursor:pointer;transition:all .15s">Để sau</button>
      <a id="favLoginGo" href="/auth/login" style="flex:1;height:42px;border-radius:10px;background:#1a3258;color:#fff;font-weight:700;font-size:14px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s">Đăng nhập</a>
    </div>
  </div>
</div>
<script>
function showFavLoginPopup(url){ var m=document.getElementById("favLoginModal"); if(!m) return; var go=document.getElementById("favLoginGo"); if(go && url) go.href=url; m.style.display="flex"; }
(function(){ var m=document.getElementById("favLoginModal"); if(m){ m.addEventListener("click", function(e){ if(e.target===m) m.style.display="none"; }); } document.addEventListener("keydown", function(e){ if(e.key==="Escape" && m) m.style.display="none"; }); })();
</script>
<script>
function limitWords(el, maxWords) {
  var words = el.value.trim().split(/\s+/).filter(w => w.length > 0);
  var cnt = words.length;
  if (cnt > maxWords) {
    el.value = words.slice(0, maxWords).join(' ');
    cnt = maxWords;
  }
  var counter = document.getElementById('wordCount');
  if (counter) {
    counter.textContent = cnt + '/' + maxWords + ' từ';
    counter.style.color = cnt >= maxWords ? '#e53e3e' : '#999';
  }
}
function previewReviewImages(input) {
  var preview = document.getElementById('reviewImagePreview');
  if (!preview) return;
  preview.innerHTML = '';
  if (input.files) {
    var maxFiles = Math.min(input.files.length, 5);
    for (var i = 0; i < maxFiles; i++) {
      var reader = new FileReader();
      reader.onload = function(e) {
        var img = document.createElement('img');
        img.src = e.target.result;
        img.style.cssText = 'width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd';
        preview.appendChild(img);
      };
      reader.readAsDataURL(input.files[i]);
    }
    if (input.files.length > 5) {
      var note = document.createElement('span');
      note.textContent = 'Tối đa 5 ảnh';
      note.style.cssText = 'font-size:11px;color:#e53e3e;align-self:center';
      preview.appendChild(note);
    }
  }
}

// Track & Render Recently Viewed Products
<?php
$pImgUrl = '';
if (!empty($images[0]['file_path'])) {
    $pImgUrl = '/uploads/products/' . $images[0]['file_path'];
} elseif (!empty($product['main_image'])) {
    $pImgUrl = '/uploads/products/' . $product['main_image'];
}
?>
(function() {
  try {
    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    var pData = {
      id: <?= (int)($product['id'] ?? 0) ?>,
      name: <?= json_encode($product['name'] ?? '') ?>,
      price: <?= (float)($product['price'] ?? 0) ?>,
      is_call_price: <?= (!empty($product['is_call_price']) || empty($product['price']) || (float)$product['price'] <= 0) ? 1 : 0 ?>,
      image: <?= json_encode($pImgUrl) ?>,
      slug: <?= json_encode($product['slug'] ?? '') ?>
    };

    if (!pData.id || !pData.name || !pData.name.trim()) return;

    var rawList = localStorage.getItem('cs_recently_viewed');
    var list = [];
    try { list = JSON.parse(rawList || '[]'); } catch(e) { list = []; }
    if (!Array.isArray(list)) list = [];

    // Strict filter: item MUST have valid id AND valid non-empty name
    list = list.filter(function(item) {
      return item && item.id && typeof item.name === 'string' && item.name.trim() !== '' && item.name !== 'undefined';
    });

    // Auto-repair existing stored item matching current product
    var exIdx = list.findIndex(function(item) { return item.id === pData.id; });
    if (exIdx !== -1) {
      if (pData.image && (!list[exIdx].image || list[exIdx].image.includes('favicon'))) {
        list[exIdx].image = pData.image;
      }
      list.splice(exIdx, 1);
    }
    list.unshift(pData);
    if (list.length > 10) list = list.slice(0, 10);
    localStorage.setItem('cs_recently_viewed', JSON.stringify(list));

    var otherItems = list.filter(function(item) { 
      return item && item.id !== pData.id && typeof item.name === 'string' && item.name.trim() !== '' && item.name !== 'undefined'; 
    });

    var sec = document.getElementById('recentlyViewedSection');
    var grid = document.getElementById('recentlyViewedGrid');
    if (sec && grid && otherItems.length > 0) {
      grid.innerHTML = otherItems.map(function(it) {
        var safeName = escapeHtml(it.name);
        var url = '/products/' + (it.slug || it.id);
        var imgSrc = it.image || '';
        if (imgSrc && !imgSrc.startsWith('/') && !imgSrc.startsWith('http')) {
          imgSrc = '/' + imgSrc;
        }
        if (!imgSrc || imgSrc.includes('favicon')) {
          imgSrc = '/uploads/products/cooling-logo-placeholder.jpg';
        }

        var isCall = it.is_call_price || !it.price || parseFloat(it.price) <= 0;
        var pStr = isCall 
          ? '<div style="font-size:11px;font-weight:700;color:#475569;line-height:1.3">Liên hệ ngay:<br><span style="color:#002f6c;font-weight:800;font-size:13px">0705 070 526</span></div>'
          : '<span style="font-size:14px;font-weight:800;color:var(--navy, #002f6c);">' + (new Intl.NumberFormat('vi-VN').format(it.price) + ' ₫') + '</span>';

        return '<a href="' + url + '" class="rv-card" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:10px; display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-2px)\';this.style.boxShadow=\'0 6px 16px rgba(0,0,0,0.08)\'" onmouseout="this.style.transform=\'none\';this.style.boxShadow=\'none\'">' +
          '<div style="background:#fff; aspect-ratio:4/3; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center; margin-bottom:8px;">' +
          '<img src="' + escapeHtml(imgSrc) + '" alt="' + safeName + '" style="width:100%; height:100%; object-fit:contain; padding:4px;" onerror="this.onerror=null;this.src=\'/uploads/products/cooling-logo-placeholder.jpg\'">' +
          '</div>' +
          '<div style="font-size:12.5px; font-weight:600; color:#0b1d3a; line-clamp:2; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; height:35px; line-height:1.4; margin-bottom:6px;" title="' + safeName + '">' + safeName + '</div>' +
          '<div>' + pStr + '</div>' +
          '</a>';
      }).join('');
      sec.style.display = 'block';
    }
  } catch(e) { console.error('Recently viewed error:', e); }
})();
</script>
<?php require __DIR__ . '/../partials/foot.php'; ?>
