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
$_hl = $sysHotline ?? '0947796471';

$_tLogo = dbGet("SELECT value FROM settings WHERE key='footer_logo_text'")['value'] ?? ''; $_footerLogo = !empty($_tLogo) ? $_tLogo : 'COOLING';
$_tDesc = dbGet("SELECT value FROM settings WHERE key='footer_desc'")['value'] ?? ''; $_footerDesc = !empty($_tDesc) ? $_tDesc : 'Sàn TMĐT phụ tùng ô tô chính hãng — chuyên sâu hệ thống làm mát. Cung cấp phụ tùng uy tín cho hàng triệu khách hàng trên toàn quốc.';
?>
<?php if(function_exists("csrfField")) echo csrfField(); ?>
<footer>
  <div class="newsletter-band">
    <div class="wrap">
      <div class="nl-text"><h3><?= htmlspecialchars($_nlTitle) ?></h3><p><?= htmlspecialchars($_nlSub) ?></p></div>
      <div class="nl-form" id="nlFormWrap">
        <input type="email" id="nlEmailInput" placeholder="Nhập địa chỉ email @gmail.com..." autocomplete="email">
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
      <div class="foot-col"><h4>Sản phẩm</h4><ul><li><a href="/products">Tất cả phụ tùng</a></li><li><a href="/brands">Theo hãng xe</a></li><li><a href="/promotions">Khuyến mại</a></li></ul></div>
      <div class="foot-col"><h4>Về chúng tôi</h4><ul><li><a href="/about">Câu chuyện Cooling</a></li><li><a href="/stores">Hệ thống cửa hàng</a></li><li><a href="/news">Tin tức</a></li><li><a href="/contact">Liên hệ</a></li></ul></div>
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
<div class="float-stack"><a href="tel:<?= $_hl ?>" class="float-btn gold">Gọi ngay</a></div>

<script>
function nlSubmit() {
  var input = document.getElementById('nlEmailInput');
  var val = input.value.trim();
  if (!val || !/^[^@]+@gmail\.com$/i.test(val)) { alert('Vui lòng nhập email @gmail.com hợp lệ'); return; }
  var csrf = document.querySelector('input[name="_csrf"]');
  csrf = csrf ? csrf.value : '';
  fetch('/newsletter/subscribe', {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&email=' + encodeURIComponent(val)
  }).then(function(r){return r.json()}).then(function(data){
    if (data.ok) alert('Đăng ký thành công! Mã giảm giá: <?= $_nlCode ?>');
    else if (data.already) alert('Email đã đăng ký!');
    else alert(data.msg || 'Có lỗi xảy ra.');
  }).catch(function(){alert('Lỗi kết nối.');});
}
</script>
