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

    <!-- Hotline Stream (Center 1 row on Desktop, Responsive Wrapped Pills on Mobile) -->
    <div class="top-bar-hotline-stream">
      <div class="hotline-list">
        <?php foreach ($hotlineItems as $hIdx => $hItem): ?>
          <span class="hotline-pill">
            <span class="h-label"><?= htmlspecialchars(mb_strtoupper($hItem['label'], 'UTF-8')) ?>:</span>
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
    
    <div class="top-bar-right-nav">
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

/* Make Top-Bar Wrap Align with Main Container (1240px centered) */
.top-bar {
  width: 100% !important;
  box-sizing: border-box !important;
  background: var(--navy-dark, #0b1a30) !important;
}
.top-bar .wrap {
  max-width: 1240px !important;
  margin: 0 auto !important;
  width: 100% !important;
  padding: 4px 16px !important;
  box-sizing: border-box !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 16px !important;
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

/* Static Hotline Stream Styles (Desktop: Center 1 row, Mobile: Responsive Wrapped) */
.top-bar-hotline-stream {
  flex: 1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 12px !important;
  overflow: hidden !important;
}
.top-bar-hotline-stream .hotline-list {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  white-space: nowrap !important;
  gap: 10px !important;
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
  .top-bar-hotline-stream .hotline-list { gap: 6px !important; }
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
    padding: 0 6px !important; 
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
    align-items: center !important; 
    justify-content: center !important; 
    margin: 2px 0 0 0 !important; 
    height: auto !important; 
    min-height: auto !important; 
    overflow: visible !important; 
  }
  .top-bar-hotline-stream .hotline-list { 
    display: flex !important; 
    flex-wrap: wrap !important; 
    justify-content: center !important; 
    align-items: center !important; 
    white-space: normal !important; 
    gap: 3px 6px !important; 
    width: 100% !important; 
  }
  .top-bar-hotline-stream .hotline-pill { 
    display: inline-flex !important;
    align-items: center !important;
    gap: 3px !important;
    font-size: clamp(8.5px, 2.5vw, 10px) !important; 
    padding: 1.5px 5px !important; 
    white-space: nowrap !important; 
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
    border-radius: 4px !important;
  }
  .top-bar-hotline-stream .h-label { 
    font-size: clamp(8px, 2.3vw, 9.5px) !important; 
    font-weight: 700 !important;
    color: #c8a951 !important;
    letter-spacing: -0.1px !important;
  }
  .top-bar-hotline-stream .h-num { 
    font-size: clamp(9px, 2.6vw, 10.5px) !important; 
    font-weight: 800 !important;
    color: #ffffff !important;
  }
  .top-bar-hotline-stream .h-sep { 
    display: none !important; 
  }
}
</style>
