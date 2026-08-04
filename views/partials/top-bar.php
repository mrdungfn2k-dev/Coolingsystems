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
    <div class="top-bar-left-status">
      <span class="badge-live"><span class="dot"></span> Đang phục vụ</span>
    </div>

    <!-- Hotline Stream (Center 1 row on Desktop, Exactly 2 rows on Mobile) -->
    <div class="top-bar-hotline-stream">
      <!-- Dòng 1: CSKH & DỊCH VỤ + KĨ THUẬT & BẢO HÀNH -->
      <div class="hotline-row hotline-row-1">
        <span class="hotline-pill">
          <span class="h-label">CSKH & DỊCH VỤ:</span>
          <a href="tel:0705070526" class="h-num">0705.0705.26</a> - <a href="tel:0705070528" class="h-num">0705.0705.28</a>
        </span>
        <span class="h-sep">•</span>
        <span class="hotline-pill">
          <span class="h-label">KĨ THUẬT & BẢO HÀNH:</span>
          <a href="tel:0704070418" class="h-num">0704.0704.18</a>
        </span>
      </div>
      
      <!-- Dòng 2: BÁN BUÔN + BÁN LẺ -->
      <div class="hotline-row hotline-row-2">
        <span class="hotline-pill">
          <span class="h-label">BÁN BUÔN:</span>
          <a href="tel:0703070321" class="h-num">0703.0703.21</a> - <a href="tel:0703070361" class="h-num">0703.0703.61</a>
        </span>
        <span class="h-sep">•</span>
        <span class="hotline-pill">
          <span class="h-label">BÁN LẺ:</span>
          <a href="tel:0703070315" class="h-num">0703.0703.15</a>
        </span>
      </div>
    </div>
    
    <div class="top-bar-right-nav">
      <a href="/agency/login" style="color:var(--gold-warm); font-weight:700;">Kênh Đại Lý</a>
      <span class="sep">|</span>
      <a href="/policies">Chính sách</a>
      <span class="sep">|</span>
      <a href="/stores">Cửa hàng</a>
      <span class="sep">|</span>
      <div id="google_translate_element" style="display:none"></div>
      <button id="langSwitchBtn" onclick="switchLang()">EN/VI</button>
      <?php if (!empty($user)): ?>
        <span class="sep">|</span>
        <a href="<?= ($user['role']??'')==='admin' ? '/admin/logout' : '/auth/logout' ?>" class="logout-link">Đăng xuất</a>
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
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
/* Hide Google Translate top bar & prevent layout shifting */
body { top: 0 !important; position: static !important; }
.goog-te-banner-frame, .goog-te-balloon-frame, #goog-gt-tt, .goog-te-spinner-pos { display: none !important; visibility: hidden !important; width: 0 !important; height: 0 !important; }
font { background-color: transparent !important; box-shadow: none !important; border: none !important; font-family: inherit !important; color: inherit !important; display: inline !important; vertical-align: baseline !important; }
.skiptranslate { font-size: 0 !important; }
iframe.skiptranslate { display: none !important; visibility: hidden !important; }
#goog-gt- { display: none !important; }

/* Expand Top-Bar Wrap 100% Full Width (Far Left Status, Far Right Nav, Center Hotline) */
.top-bar {
  width: 100% !important;
  box-sizing: border-box !important;
  background: var(--navy-dark, #0b1a30) !important;
}
.top-bar .wrap {
  max-width: 100% !important;
  width: 100% !important;
  padding: 4px 20px !important;
  box-sizing: border-box !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 12px !important;
}

.top-bar-left-status {
  flex: 0 0 auto !important;
  display: flex !important;
  align-items: center !important;
}

.top-bar-right-nav {
  flex: 0 0 auto !important;
  display: flex !important;
  align-items: center !important;
  gap: 6px !important;
}

.top-bar-right-nav a {
  color: rgba(255,255,255,0.92) !important;
  font-size: 11px !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  white-space: nowrap !important;
}
.top-bar-right-nav .sep {
  color: rgba(255,255,255,0.25) !important;
  font-size: 10px !important;
}
.top-bar-right-nav #langSwitchBtn {
  background: none !important;
  border: 1px solid rgba(255,255,255,0.35) !important;
  color: #fff !important;
  padding: 1px 5px !important;
  border-radius: 3px !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  cursor: pointer !important;
}

/* Static Hotline Stream Styles (Desktop: Center 1 row, Mobile: Exactly 2 rows) */
.top-bar-hotline-stream {
  flex: 1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 16px !important;
  overflow: hidden !important;
}
.top-bar-hotline-stream .hotline-row {
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  white-space: nowrap !important;
}
.top-bar-hotline-stream .hotline-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
  font-size: 11px !important;
  white-space: nowrap !important;
}
.top-bar-hotline-stream .h-label {
  color: #c8a951 !important;
  font-weight: 700 !important;
  font-size: 11px !important;
  text-transform: uppercase !important;
}
.top-bar-hotline-stream .h-num {
  color: #ffffff !important;
  font-weight: 800 !important;
  font-size: 12px !important;
  text-decoration: none !important;
}
.top-bar-hotline-stream .h-num:hover {
  color: #f59e0b !important;
  text-decoration: underline !important;
}
.top-bar-hotline-stream .h-sep {
  color: rgba(255,255,255,0.35) !important;
  margin: 0 2px !important;
  font-size: 10px !important;
}

@media (max-width: 992px) {
  .top-bar-hotline-stream .hotline-row { gap: 6px !important; }
  .top-bar-hotline-stream .h-label { font-size: 10px !important; }
  .top-bar-hotline-stream .h-num { font-size: 11px !important; }
}

@media (max-width: 768px) {
  .top-bar { min-height: auto !important; height: auto !important; padding: 4px 0 !important; overflow: hidden !important; width: 100% !important; box-sizing: border-box !important; }
  .top-bar .wrap { 
    display: flex !important; 
    flex-wrap: wrap !important; 
    align-items: center !important; 
    justify-content: space-between !important; 
    padding: 0 4px !important; 
    width: 100% !important; 
    box-sizing: border-box !important; 
    gap: 3px !important; 
  }
  
  .top-bar-left-status { order: 1 !important; flex: 0 0 auto !important; margin: 0 !important; }
  .top-bar-left-status .badge-live { font-size: 9.5px !important; padding: 1.5px 4px !important; line-height: 1.2 !important; white-space: nowrap !important; }

  .top-bar-right-nav { 
    order: 2 !important;
    display: flex !important; 
    align-items: center !important; 
    gap: 3px !important; 
    flex: 0 0 auto !important; 
    margin-left: auto !important; 
    justify-content: flex-end !important;
    padding-right: 2px !important;
  }
  .top-bar-right-nav a { color: rgba(255,255,255,0.92) !important; font-size: 9.5px !important; font-weight: 600 !important; text-decoration: none !important; white-space: nowrap !important; }
  .top-bar-right-nav .sep { color: rgba(255,255,255,0.25) !important; font-size: 9px !important; margin: 0 1px !important; }
  .top-bar-right-nav #langSwitchBtn { background: none !important; border: 1px solid rgba(255,255,255,0.35) !important; color: #fff !important; padding: 1px 4px !important; border-radius: 3px !important; font-size: 9px !important; font-weight: 700 !important; cursor: pointer !important; white-space: nowrap !important; line-height: 1.2 !important; }
  .top-bar-right-nav .logout-link { background: #e74c3c !important; color: #fff !important; padding: 1px 4px !important; border-radius: 3px !important; font-size: 9px !important; font-weight: 700 !important; text-decoration: none !important; white-space: nowrap !important; }

  .top-bar-hotline-stream { 
    order: 3 !important; 
    flex: 0 0 100% !important; 
    width: 100% !important; 
    display: flex !important; 
    flex-direction: column !important; 
    align-items: center !important; 
    justify-content: center !important; 
    margin: 2px 0 0 0 !important; 
    height: auto !important; 
    min-height: auto !important; 
    overflow: visible !important; 
    gap: 2px !important;
  }
  .top-bar-hotline-stream .hotline-row { 
    display: flex !important; 
    flex-direction: row !important; 
    align-items: center !important; 
    justify-content: center !important; 
    width: 100% !important; 
    white-space: nowrap !important; 
    gap: 2px !important; 
  }
  .top-bar-hotline-stream .hotline-pill { 
    display: inline-flex !important;
    align-items: center !important;
    gap: 2px !important;
    font-size: clamp(7px, 2.05vw, 8.5px) !important; 
    padding: 0 !important; 
    white-space: nowrap !important; 
    background: none !important;
    border: none !important;
  }
  .top-bar-hotline-stream .h-label { 
    font-size: clamp(6.8px, 1.95vw, 8.2px) !important; 
    font-weight: 700 !important;
    color: #c8a951 !important;
    letter-spacing: -0.3px !important;
  }
  .top-bar-hotline-stream .h-num { 
    font-size: clamp(7.8px, 2.2vw, 9.2px) !important; 
    font-weight: 800 !important;
    color: #ffffff !important;
    letter-spacing: -0.3px !important;
  }
  .top-bar-hotline-stream .h-sep { 
    display: inline !important; 
    color: rgba(255,255,255,0.3) !important;
    font-size: 7px !important;
    margin: 0 1px !important;
  }
}
</style>
