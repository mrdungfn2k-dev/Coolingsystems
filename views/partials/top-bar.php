<?php ?>
<div class="top-bar">
  <div class="wrap">
    <div class="left">
      <span class="badge-live"><span class="dot"></span> Đang phục vụ</span>
    </div>
    <div class="right" style="display:flex;align-items:center;gap:12px">
      <a href="/policies" style="color:rgba(255,255,255,0.75);font-size:11px;font-weight:600;text-decoration:none;transition:0.2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.75)'">Chính sách</a>
      <span style="width:1px;height:12px;background:rgba(255,255,255,0.15)"></span>
      <a href="/stores" style="color:rgba(255,255,255,0.75);font-size:11px;font-weight:600;text-decoration:none;transition:0.2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.75)'">Cửa hàng</a>
      <span style="width:1px;height:12px;background:rgba(255,255,255,0.15)"></span>
      <div id="google_translate_element" style="display:none"></div>
      <button id="langSwitchBtn" onclick="switchLang()" style="background:none;border:1px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.8);padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;transition:0.2s" onmouseover="this.style.borderColor='#fff';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,0.3)';this.style.color='rgba(255,255,255,0.8)'">EN/VI</button>
      <?php if ($user): ?>
        <a href="<?= $user['role']==='admin' ? '/admin/logout' : '/auth/logout' ?>" style="background:#e74c3c;color:#fff;font-weight:700;padding:4px 12px;border-radius:4px;font-size:12px;text-decoration:none;text-transform:uppercase;box-shadow:0 2px 4px rgba(231,76,60,0.3)">Đăng xuất</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Google Translate Integration -->
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'vi',
    includedLanguages: 'en,vi',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
    autoDisplay: false
  }, 'google_translate_element');
}

var _currentLang = 'vi';

function switchLang() {
  var frame = document.querySelector('.goog-te-menu-frame');
  if (!frame) {
    // Google Translate not loaded yet, try cookie approach
    if (_currentLang === 'vi') {
      document.cookie = "googtrans=/vi/en; path=/; domain=." + location.hostname;
      document.cookie = "googtrans=/vi/en; path=/";
      _currentLang = 'en';
    } else {
      document.cookie = "googtrans=; path=/; domain=." + location.hostname + "; expires=Thu, 01 Jan 1970 00:00:00 GMT";
      document.cookie = "googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
      _currentLang = 'vi';
    }
    location.reload();
    return;
  }
  // If frame exists, trigger via the select
  var sel = document.querySelector('.goog-te-combo');
  if (sel) {
    if (_currentLang === 'vi') {
      sel.value = 'en';
      _currentLang = 'en';
    } else {
      sel.value = 'vi';
      _currentLang = 'vi';
    }
    sel.dispatchEvent(new Event('change'));
    document.getElementById('langSwitchBtn').textContent = _currentLang === 'en' ? 'VI/EN' : 'EN/VI';
  }
}

// Check if already translated
(function() {
  var c = document.cookie.match(/googtrans=\/vi\/(\w+)/);
  if (c && c[1] === 'en') {
    _currentLang = 'en';
    var btn = document.getElementById('langSwitchBtn');
    if (btn) btn.textContent = 'VI/EN';
  }
})();
window.addEventListener('load', function() {
  var s = document.createElement('script');
  s.type = 'text/javascript';
  s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
  document.body.appendChild(s);
});
</script>
<style>
/* Hide Google Translate bar */
.goog-te-banner-frame { display: none !important; }
body { top: 0 !important; }
.goog-te-gadget { font-size: 0 !important; }
.skiptranslate { display: none !important; }
</style>

