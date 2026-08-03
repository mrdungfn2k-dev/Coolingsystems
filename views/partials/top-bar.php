<?php
$tbConfig = dbAll("SELECT key, value FROM system_config WHERE key IN ('hotline_list','site_phone')");
$tbMap = [];
if (!empty($tbConfig) && is_array($tbConfig)) {
    foreach($tbConfig as $c) { $tbMap[$c['key']] = $c['value']; }
}
$rawHotlineConfig = $tbMap['hotline_list'] ?? '';
$hotlineItems = !empty($rawHotlineConfig) ? json_decode($rawHotlineConfig, true) : null;
if (empty($hotlineItems) || !is_array($hotlineItems)) {
    $hotlineItems = [
        ['label' => 'CSKH & Dịch vụ', 'phone' => '0705.0705.26 - 0705.0705.28'],
        ['label' => 'Kĩ thuật & Bảo Hành', 'phone' => '0704.0704.18'],
        ['label' => 'Bán Buôn', 'phone' => '0703.0703.21 - 0703.0703.61'],
        ['label' => 'Bán lẻ', 'phone' => '0703.0703.15']
    ];
}
?>
<div class="top-bar">
  <div class="wrap">
    <div class="top-bar-top-row">
      <div class="left">
        <span class="badge-live"><span class="dot"></span> Đang phục vụ</span>
      </div>
      <div class="right" style="display:flex;align-items:center;gap:10px">
        <a href="/policies" style="color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;text-decoration:none;transition:0.2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">Chính sách</a>
        <span style="width:1px;height:12px;background:rgba(255,255,255,0.2)"></span>
        <a href="/stores" style="color:rgba(255,255,255,0.85);font-size:11px;font-weight:600;text-decoration:none;transition:0.2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.85)'">Cửa hàng</a>
        <span style="width:1px;height:12px;background:rgba(255,255,255,0.2)"></span>
        <div id="google_translate_element" style="display:none"></div>
        <button id="langSwitchBtn" onclick="switchLang()" style="background:none;border:1px solid rgba(255,255,255,0.3);color:rgba(255,255,255,0.8);padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;transition:0.2s" onmouseover="this.style.borderColor='#fff';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,0.3)';this.style.color='rgba(255,255,255,0.8)'">EN/VI</button>
        <?php if (!empty($user)): ?>
          <a href="<?= ($user['role']??'')==='admin' ? '/admin/logout' : '/auth/logout' ?>" style="background:#e74c3c;color:#fff;font-weight:700;padding:3px 10px;border-radius:4px;font-size:11px;text-decoration:none;text-transform:uppercase;box-shadow:0 2px 4px rgba(231,76,60,0.3)">Đăng xuất</a>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Static Hotline Stream (Full Visible Hotlines) -->
    <div class="top-bar-hotline-stream">
      <div class="hotline-list">
        <?php foreach ($hotlineItems as $hIdx => $hItem): ?>
          <span class="hotline-pill">
            <span class="h-label"><?= htmlspecialchars($hItem['label']) ?>:</span>
            <?php
              $rawPhone = $hItem['phone'] ?? '';
              $pParts = preg_split('/\s*[\-\/]\s*/', $rawPhone);
              $linkParts = [];
              foreach ($pParts as $pVal) {
                  $pClean = preg_replace('/[^0-9\+]/', '', $pVal);
                  $linkParts[] = '<a href="tel:'.htmlspecialchars($pClean).'" class="h-num">'.htmlspecialchars(trim($pVal)).'</a>';
              }
              echo implode(' - ', $linkParts);
            ?>
          </span>
          <?php if ($hIdx < count($hotlineItems) - 1): ?>
            <span class="h-sep">•</span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
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

(function() {
  var c = document.cookie.match(/googtrans=\/vi\/(\w+)/);
  if (c && c[1] === 'en') {
    _currentLang = 'en';
    var btn = document.getElementById('langSwitchBtn');
    if (btn) btn.textContent = 'VI/EN';
  }
})();

function switchLang() {
  var isEn = (_currentLang === 'vi');
  var host = location.hostname;
  var domainPart = host ? ("; domain=." + host) : "";
  
  if (isEn) {
    document.cookie = "googtrans=/vi/en; path=" + domainPart;
    document.cookie = "googtrans=/vi/en; path=/";
  } else {
    document.cookie = "googtrans=; path=/" + domainPart + "; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "googtrans=; path=/; domain=" + host + "; expires=Thu, 01 Jan 1970 00:00:00 GMT";
  }
  location.reload();
}

function loadGoogleTranslate() {
  if (window._gtLoaded) return;
  window._gtLoaded = true;
  var s = document.createElement('script');
  s.type = 'text/javascript';
  s.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
  document.body.appendChild(s);
}

// Load translate script immediately if translated to EN, or defer slightly on idle
if (_currentLang === 'en') {
  loadGoogleTranslate();
} else {
  window.addEventListener('load', function() {
    setTimeout(loadGoogleTranslate, 1000);
  });
}
</script>
<style>
/* Hide Google Translate top bar & prevent layout shifting */
body { top: 0 !important; position: static !important; }
.goog-te-banner-frame, .goog-te-balloon-frame, #goog-gt-tt, .goog-te-spinner-pos { display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important; }
font { background-color: transparent !important; box-shadow: none !important; border: none !important; font-family: inherit !important; color: inherit !important; display: inline !important; vertical-align: baseline !important; }
.skiptranslate { font-size: 0 !important; }
iframe.skiptranslate { display: none !important; visibility: hidden !important; }
#goog-gt- { display: none !important; }

/* Make Top-Bar Wrap Expand 100% Full Width across screen */
.top-bar {
  width: 100% !important;
  box-sizing: border-box !important;
}
.top-bar .wrap {
  max-width: 100% !important;
  width: 100% !important;
  padding: 4px 24px !important;
  box-sizing: border-box !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
}

/* Static Hotline List Styles (100% Fully Visible Across Space) */
.top-bar-hotline-stream {
  flex: 1 !important;
  margin: 0 16px !important;
  overflow-x: auto !important;
  overflow-y: hidden !important;
  position: relative !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  height: 28px !important;
  scrollbar-width: none !important; /* Firefox */
  -ms-overflow-style: none !important;  /* IE/Edge */
}
.top-bar-hotline-stream::-webkit-scrollbar {
  display: none !important; /* Chrome/Safari */
}
.top-bar-hotline-stream .hotline-list {
  display: flex !important;
  align-items: center !important;
  justify-content: space-evenly !important;
  width: 100% !important;
  white-space: nowrap !important;
  gap: 4px !important;
}
.top-bar-hotline-stream .hotline-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 3px !important;
  padding: 1px 3px !important;
  font-size: 11px !important;
  white-space: nowrap !important;
}
.top-bar-hotline-stream .h-label {
  color: #c8a951 !important;
  font-weight: 700 !important;
  font-size: clamp(9.5px, 0.7vw, 10.5px) !important;
  text-transform: uppercase !important;
  letter-spacing: 0.1px !important;
}
.top-bar-hotline-stream .h-num {
  color: #ffffff !important;
  font-weight: 800 !important;
  font-size: clamp(10.5px, 0.8vw, 11.5px) !important;
  text-decoration: none !important;
  transition: color 0.15s !important;
}
.top-bar-hotline-stream .h-num:hover {
  color: #f59e0b !important;
  text-decoration: underline !important;
}
.top-bar-hotline-stream .h-sep {
  color: rgba(255,255,255,0.3) !important;
  margin: 0 2px !important;
  font-size: 9px !important;
}

@media (max-width: 992px) {
  .top-bar-hotline-stream .hotline-list { justify-content: flex-start !important; gap: 6px !important; }
  .top-bar-hotline-stream .h-label { font-size: 9.5px !important; }
  .top-bar-hotline-stream .h-num { font-size: 10.5px !important; }
}

@media (max-width: 768px) {
  .top-bar { min-height: auto !important; height: auto !important; padding: 4px 0 !important; overflow: hidden !important; width: 100% !important; box-sizing: border-box !important; }
  .top-bar .wrap { display: flex !important; flex-direction: column !important; align-items: stretch !important; justify-content: center !important; padding: 0 8px !important; width: 100% !important; box-sizing: border-box !important; gap: 4px !important; }
  
  .top-bar-top-row { display: flex !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; flex-wrap: nowrap !important; }
  .top-bar .left { flex: 0 0 auto !important; display: flex !important; align-items: center !important; }
  .top-bar .left .badge-live { font-size: 9px !important; padding: 1px 4px !important; line-height: 1.2 !important; white-space: nowrap !important; }
  .top-bar .right { display: flex !important; align-items: center !important; gap: 3px !important; flex: 0 0 auto !important; margin-left: auto !important; }
  .top-bar .right a[href="/policies"],
  .top-bar .right a[href="/stores"] { display: inline-block !important; color: rgba(255,255,255,0.9) !important; font-size: 10px !important; font-weight: 600 !important; text-decoration: none !important; white-space: nowrap !important; padding: 0 1px !important; }
  .top-bar .right span { display: inline-block !important; width: 1px !important; height: 9px !important; background: rgba(255,255,255,0.25) !important; margin: 0 1px !important; }
  #langSwitchBtn { padding: 1px 4px !important; font-size: 9.5px !important; border-radius: 3px !important; line-height: 1.2 !important; white-space: nowrap !important; }
  .top-bar .right a[href*="logout"] { padding: 2px 5px !important; font-size: 9.5px !important; border-radius: 3px !important; white-space: nowrap !important; margin-right: 0 !important; display: inline-block !important; line-height: 1.2 !important; }

  .top-bar-hotline-stream { margin: 0 !important; height: auto !important; min-height: auto !important; overflow: visible !important; width: 100% !important; justify-content: center !important; }
  .top-bar-hotline-stream .hotline-list { display: flex !important; flex-wrap: wrap !important; justify-content: center !important; align-items: center !important; white-space: normal !important; gap: 2px 6px !important; width: 100% !important; }
  .top-bar-hotline-stream .hotline-pill { font-size: 10px !important; padding: 0 2px !important; }
  .top-bar-hotline-stream .h-label { font-size: 9.5px !important; }
  .top-bar-hotline-stream .h-num { font-size: 10.5px !important; }
}
</style>
