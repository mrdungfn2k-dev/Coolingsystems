<?php
$_nlCfg = [];
foreach(['newsletter_title','newsletter_subtitle','newsletter_voucher_amount','newsletter_voucher_code','newsletter_btn_text'] as $_nk) {
    $_nr = dbGet("SELECT value FROM system_config WHERE key=?", [$_nk]);
    $_nlCfg[$_nk] = $_nr['value'] ?? '';
}
$_nlTitle = $_nlCfg['newsletter_title'] ?: 'Đăng ký nhận ưu đãi';
$_nlSub = $_nlCfg['newsletter_subtitle'] ?: 'Voucher 100K cho đơn đầu tiên';
$_nlBtn = $_nlCfg['newsletter_btn_text'] ?: 'Đăng ký nhận tin';
$_nlCode = $_nlCfg['newsletter_voucher_code'] ?: 'UUDAI100K';
$_nlAmount = intval($_nlCfg['newsletter_voucher_amount'] ?: 100000);
$_footArticles = dbAll("SELECT title, slug FROM articles ORDER BY created_at DESC LIMIT 3");
$_hl = $sysHotline ?? '0705070526';

$_tLogo = dbGet("SELECT value FROM settings WHERE key='footer_logo_text'")['value'] ?? ''; $_footerLogo = !empty($_tLogo) ? $_tLogo : 'COOLING';
$_tDesc = dbGet("SELECT value FROM settings WHERE key='footer_desc'")['value'] ?? ''; $_footerDesc = !empty($_tDesc) ? $_tDesc : 'Sàn TMĐT phụ tùng ô tô chính hãng — chuyên sâu hệ thống làm mát. Cung cấp phụ tùng uy tín cho hàng triệu khách hàng trên toàn quốc.';
?>
<?php if(function_exists("csrfField")) echo csrfField(); ?>
<footer>
  <div class="newsletter-band">
    <div class="wrap">
      <div class="nl-text"><h3><?= htmlspecialchars($_nlTitle) ?></h3><p><?= htmlspecialchars($_nlSub) ?></p></div>
      <div class="nl-form" id="nlFormWrap">
        <input type="email" id="nlEmailInput" placeholder="Nhập địa chỉ email @gmail.com..." autocomplete="email" aria-label="Địa chỉ email nhận ưu đãi">
        <button type="button" class="btn btn-navy" onclick="nlSubmit()"><?= htmlspecialchars($_nlBtn) ?></button>
      </div>
    </div>
  </div>
  <div class="main-foot">
    <div class="wrap">
      <div class="foot-brand">
        <?php $_fLogoImg = dbGet("SELECT value FROM settings WHERE key='footer_logo_image'")['value'] ?? ''; ?>
        <?php if($_fLogoImg && file_exists('/var/lib/coolingsystems/uploads/'.$_fLogoImg)): ?>
          <div class="logo footer-logo-img-wrap"><img src="/uploads/<?= e($_fLogoImg) ?>" alt="<?= e($_footerLogo) ?>" class="footer-logo-img" width="180" height="60" loading="lazy"></div>
        <?php else: ?>
          <div class="logo" style="font-size:24px;font-weight:900;color:#fff;letter-spacing:1px"><?= e($_footerLogo) ?></div>
        <?php endif; ?>
        <p class="desc" style="color:rgba(255,255,255,0.7);font-size:13px;margin-top:8px"><?= e($_footerDesc) ?></p>
      </div>
      <?php
        $_visRows = dbAll("SELECT key, value FROM settings WHERE key LIKE 'page_visible_%'");
        $_pVisFoot = [];
        foreach ($_visRows as $_vr) {
            $_pVisFoot[str_replace('page_visible_', '', $_vr['key'])] = $_vr['value'];
        }
      ?>
      <div class="foot-col"><h4>Sản phẩm</h4><ul><li><a href="/products">Tất cả phụ tùng</a></li><li><a href="/brands">Theo hãng xe</a></li><li><a href="/promotions">Khuyến mại</a></li></ul></div>
      <div class="foot-col"><h4>Về chúng tôi</h4><ul><?php if(($_pVisFoot['gioi-thieu'] ?? '1') !== '0'): ?><li><a href="/about">Câu chuyện Cooling</a></li><?php endif; ?><?php if(($_pVisFoot['he-thong-cua-hang'] ?? '1') !== '0'): ?><li><a href="/stores">Hệ thống cửa hàng</a></li><?php endif; ?><?php if(($_pVisFoot['tin-tuc-tong-hop'] ?? '1') !== '0'): ?><li><a href="/news">Tin tức</a></li><?php endif; ?><li><a href="/contact">Liên hệ</a></li></ul></div>
      <div class="foot-col"><h4>Hỗ trợ</h4><ul><li><a href="/policies/huong-dan-mua-hang">Hướng dẫn mua hàng</a></li><li><a href="/policies/chinh-sach-doi-tra">Chính sách đổi trả</a></li><li><a href="/policies/chinh-sach-bao-hanh">Chính sách bảo hành</a></li><li><a href="/policies/dieu-khoan-bao-mat">Điều khoản bảo mật</a></li></ul></div>
      <div class="foot-col"><h4>Bài viết mới</h4><ul>
        <?php if(!empty($_footArticles)): foreach($_footArticles as $_fa): ?>
        <li><a href="/news/<?= e($_fa['slug']) ?>"><?= e(mb_substr($_fa['title'],0,40)) ?></a></li>
        <?php endforeach; else: ?>
        <li><a href="/news">Xem tin tức</a></li>
        <?php endif; ?>
      </ul></div>
    </div>
  </div>
  <?php
  $_footCopy = dbGet("SELECT value FROM settings WHERE key='footer_copyright'");
  $_copyText = !empty($_footCopy['value']) ? $_footCopy['value'] : ('&copy; ' . date('Y') . ' Cooling. Bảo lưu mọi quyền.');
  ?>
  <div class="foot-bottom"><div class="wrap"><span><?= $_copyText ?></span></div></div>
</footer>
<div class="float-stack"><a href="tel:<?= $_hl ?>" class="float-btn gold" aria-label="Gọi ngay hotline tư vấn miễn phí 24/7 <?= $_hl ?>">Gọi ngay</a></div>

<!-- Mobile nav drawer (Bottom of DOM tree) -->
<style>
.mobile-nav-drawer {
  background: #0b1329 !important;
  color: #f8fafc !important;
}
.mobile-nav-drawer .mobile-nav-links a {
  color: #f1f5f9 !important;
  display: block !important;
  padding: 10px 12px !important;
  font-size: 13.5px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  border-bottom: 1px solid rgba(255,255,255,0.06) !important;
  transition: all 0.2s ease !important;
}
.mobile-nav-drawer .mobile-nav-links a:hover,
.mobile-nav-drawer .mobile-nav-links a:active {
  background: rgba(200, 169, 81, 0.15) !important;
  color: #c8a951 !important;
  padding-left: 16px !important;
}
.mobile-nav-drawer .nav-section-label {
  font-size: 11px !important;
  color: #c8a951 !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
  font-weight: 800 !important;
  display: block !important;
  margin: 16px 0 6px 0 !important;
  padding-left: 4px !important;
}
</style>
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
<div class="mobile-nav-drawer" id="mobileNavDrawer">
  <?php $mobileUser = $user ?? (function_exists('currentUser') ? currentUser() : null); ?>
  <div style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:space-between;background:#0b1329">
    <div style="font-weight:800;font-size:15px;color:#fff;display:flex;align-items:center;gap:8px">
      <span style="color:#c8a951">⚡</span> MENU COOLING
    </div>
    <button class="mobile-nav-close" id="mobileNavClose" aria-label="Đóng menu" style="background:rgba(255,255,255,0.12);border:none;color:#fff;width:30px;height:30px;border-radius:50%;font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center">✕</button>
  </div>

  <?php if ($mobileUser): ?>
    <div style="padding:14px 18px 0 18px">
      <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between">
        <div>
          <div style="font-size:10px;color:#c8a951;font-weight:800;text-transform:uppercase;letter-spacing:0.5px">TÀI KHOẢN CỦA BẠN</div>
          <div style="font-size:14px;font-weight:800;color:#fff;margin-top:2px"><?= e($mobileUser['full_name'] ?: ($mobileUser['garage_name'] ?? 'Khách hàng')) ?></div>
        </div>
        <a href="/customer/profile" style="background:#c9a14a;color:#0b1d3a;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:800;text-decoration:none">Hồ sơ ›</a>
      </div>
    </div>
  <?php endif; ?>

  <div class="mobile-nav-links" style="padding:12px 18px">
    <!-- Section 1: Danh mục sản phẩm (Category Accordion) -->
    <div style="margin-bottom:14px;background:rgba(255,255,255,0.03);border-radius:8px;padding:8px 12px">
      <div onclick="var a=document.getElementById('mobileCatAccordion'); if(a){a.style.display=(a.style.display==='none'?'block':'none');}" style="font-size:11px;color:#c8a951;letter-spacing:0.06em;text-transform:uppercase;font-weight:800;display:flex;align-items:center;justify-content:space-between;cursor:pointer;padding:4px 0">
        <span>📁 Danh mục sản phẩm</span>
        <span style="font-size:11px;color:#94a3b8">▼</span>
      </div>
      <div id="mobileCatAccordion" style="margin-top:6px">
        <?php
          $mCats = dbAll("SELECT * FROM categories WHERE parent_id IS NULL AND (is_active=1 OR is_active IS NULL) ORDER BY is_featured DESC, sort_order, id");
          foreach($mCats as $mc):
            $mIcon = trim($mc['icon'] ?? '');
            $mIsImg = !empty($mIcon) && (preg_match('/\.(png|jpg|jpeg|webp|gif|svg)$/i', $mIcon) || str_starts_with($mIcon, 'cat_'));
        ?>
        <a href="/products?cat=<?= e($mc['slug']) ?>" style="display:flex;align-items:center;padding:9px 10px;color:#f1f5f9!important;text-decoration:none;font-size:13.5px;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.06)">
          <?php if(!empty($mIcon)): ?>
            <?php if($mIsImg): ?>
              <?php $mImgSrc = str_starts_with($mIcon, '/') ? $mIcon : (str_starts_with($mIcon, 'uploads/') ? '/' . $mIcon : '/uploads/categories/' . $mIcon); ?>
              <img src="<?= e($mImgSrc) ?>" alt="" style="width:18px;height:18px;object-fit:contain;margin-right:8px;flex-shrink:0" onerror="this.style.display='none'">
            <?php else: ?>
              <span style="margin-right:8px;font-size:14px"><?= e($mIcon) ?></span>
            <?php endif; ?>
          <?php endif; ?>
          <span><?= e($mc['name']) ?></span>
          <span style="margin-left:auto;color:#64748b;font-size:11px">›</span>
        </a>
        <?php endforeach; ?>
        <a href="/products" style="display:block;padding:9px 10px;color:#c8a951!important;font-weight:700;font-size:13px;text-decoration:none">Xem tất cả sản phẩm →</a>
      </div>
    </div>

    <!-- Section 2: Khám phá -->
    <span class="nav-section-label">Khám phá</span>
    <a href="/">Trang chủ</a>
    <a href="/about">Giới thiệu</a>
    <a href="/products">Sản phẩm</a>
    <a href="/brands">Phụ tùng theo hãng ô tô</a>
    <a href="/product-brands">Thương hiệu phụ tùng</a>
    <a href="/vouchers">Khuyến mại</a>
    <a href="/news">Tin tức & Kinh nghiệm</a>

    <!-- Section 3: Dịch vụ & Tra cứu -->
    <span class="nav-section-label">Dịch vụ & Tra cứu</span>
    <a href="/stores">Hệ thống cửa hàng</a>
    <a href="/policies">Chính sách & Quy định</a>

    <!-- Section 4: Tài khoản của tôi -->
    <?php if ($mobileUser && in_array($mobileUser['role'], ['customer','garage','staff'])): ?>
      <span class="nav-section-label">Tài khoản của tôi</span>
      <a href="/customer/profile">Hồ sơ cá nhân</a>
      <a href="/customer/orders">Đơn hàng của tôi</a>
      <a href="/customer/favorites">Sản phẩm yêu thích</a>
      <a href="/customer/chat">Tin nhắn tư vấn</a>
      <a href="/auth/logout" style="color:#ef4444!important">Đăng xuất</a>
    <?php elseif ($mobileUser && $mobileUser['role'] === 'partner'): ?>
      <span class="nav-section-label">Quản lý đối tác</span>
      <a href="/partner/dashboard">Bảng điều khiển Partner</a>
      <a href="/customer/profile">Hồ sơ cá nhân</a>
      <a href="/partner/logout" style="color:#ef4444!important">Đăng xuất</a>
    <?php elseif ($mobileUser && $mobileUser['role'] === 'admin'): ?>
      <span class="nav-section-label">Quản trị viên</span>
      <a href="/admin">Quản trị Admin Panel</a>
      <a href="/customer/profile">Hồ sơ cá nhân</a>
      <a href="/admin/logout" style="color:#ef4444!important">Đăng xuất</a>
    <?php else: ?>
      <div style="display:flex;gap:10px;margin-top:16px">
        <a href="/auth/login" style="flex:1;padding:10px;background:rgba(255,255,255,0.1);color:#fff!important;text-align:center;border-radius:8px;font-weight:700;text-decoration:none;font-size:13px">Đăng nhập</a>
        <a href="/auth/register" style="flex:1;padding:10px;background:#c8a951;color:#1a3258!important;text-align:center;border-radius:8px;font-weight:700;text-decoration:none;font-size:13px">Đăng ký</a>
      </div>
    <?php endif; ?>
  </div>

  <div style="padding:14px 20px;border-top:1px solid rgba(255,255,255,0.08);margin-top:8px;">
    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;letter-spacing:0.06em;text-transform:uppercase;font-weight:700;">Tư vấn kỹ thuật 24/7</div>
    <a href="tel:<?= preg_replace('/[^0-9]/', '', $_hl) ?>" style="font-size:20px;font-weight:800;color:var(--gold-light);text-decoration:none;display:block;"><?= htmlspecialchars($_hl) ?></a>
  </div>
</div>

<script>
function nlSubmit() {
  var input = document.getElementById('nlEmailInput');
  var val = input ? input.value.trim() : '';
  if (!val || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
    alert('Vui lòng nhập địa chỉ email hợp lệ (VD: trantunglam2025@gmail.com)');
    return;
  }
  var csrf = document.querySelector('input[name="_csrf"]');
  csrf = csrf ? csrf.value : '';
  
  var btn = document.querySelector('.newsletter-band button');
  if (btn) btn.disabled = true;

  fetch('/newsletter/subscribe', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&email=' + encodeURIComponent(val)
  }).then(function(r){
    return r.json();
  }).then(function(data){
    if (btn) btn.disabled = false;
    if (data.ok) {
      alert(data.msg || 'Đăng ký nhận ưu đãi thành công! Mã giảm giá: UUDAI100K');
      if (input) input.value = '';
    } else {
      alert(data.msg || 'Có lỗi xảy ra.');
    }
  }).catch(function(err){
    if (btn) btn.disabled = false;
    alert('Đăng ký nhận ưu đãi thành công! Mã giảm giá: UUDAI100K');
  });
}
</script>
