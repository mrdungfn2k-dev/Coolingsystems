<script>
// Global cart badge updater

// Global favorites badge updater
function updateFavBadge(n) {
  var count = parseInt(n) || 0;
  
  // 1. Desktop Update
  document.querySelectorAll('a[href="/customer/favorites"].h-btn').forEach(function(link) {
    var badge = link.querySelector('.count');
    if (!badge && count > 0) {
      badge = document.createElement('span');
      badge.className = 'count';
      link.appendChild(badge);
    }
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    }
  });

  // 2. Mobile Update
  var mFavBtn = document.querySelector('a[href="/customer/favorites"].mobile-icon-btn');
  if (mFavBtn) {
    var mBadge = mFavBtn.querySelector('.fav-badge-mobile');
    if (!mBadge && count > 0) {
      mBadge = document.createElement('span');
      mBadge.className = 'fav-badge-mobile';
      mBadge.style.cssText = '';
      mFavBtn.style.position = 'relative';
      mFavBtn.appendChild(mBadge);
    }
    if (mBadge) {
      mBadge.textContent = count > 99 ? '99+' : count;
      mBadge.style.display = count > 0 ? 'flex' : 'none';
    }
  }
}

function updateCartBadge(n, totalText) {
  var cc=parseInt(n)||0;
  document.querySelectorAll('.count,[data-cart-count]').forEach(function(b){
    b.textContent=cc>0?cc:'';b.style.display=cc>0?'inline-block':'none';
  });
  var mCartBtn=document.querySelector('.mobile-cart-btn');
  if(mCartBtn){
    var mb=mCartBtn.querySelector('.cart-badge-mobile');
    if(!mb && cc>0){
      mb=document.createElement('span');
      mb.className='cart-badge-mobile';
      mCartBtn.appendChild(mb);
    }
    if(mb){
      mb.textContent=cc;
      mb.style.display=cc>0?'inline-block':'none';
    }
  }
  document.querySelectorAll('a[href="/customer/cart"]').forEach(function(link){
    var badge=link.querySelector('.count');
    if(!badge&&cc>0){badge=document.createElement('span');badge.className='count';link.style.position='relative';link.appendChild(badge);}
    if(badge){badge.textContent=cc;badge.style.display=cc>0?'inline-block':'none';}
    if(totalText){var val=link.querySelector('.value');if(val)val.textContent=totalText;}
  });
}
</script>
<script>
function ajaxAddCart(pid, btn){
  var csrf='';
  if(typeof window._CSRF!=='undefined') csrf=window._CSRF;
  else{var ci=document.querySelector('input[name="_csrf"]');if(ci)csrf=ci.value;}
  if(!csrf) csrf = '';
  btn.style.pointerEvents='none';btn.style.opacity='0.5';
  fetch('/customer/cart/quick-add',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    credentials:'same-origin',
    body:'_csrf='+encodeURIComponent(csrf)+'&product_id='+pid+'&quantity=1'
  }).then(function(r){return r.json();}).then(function(d){
    btn.style.pointerEvents='';btn.style.opacity='';
    if(d.ok||d.success){
      coolToastShow('Đã thêm sản phẩm vào giỏ hàng!','🛒');
      // === Update ALL cart badges (desktop + mobile) ===
      var cc = parseInt(d.cart_count)||0;
      var ct = d.cart_total||'0 ₫';
      // Desktop: .h-btn links to /cart
      document.querySelectorAll('a[href="/customer/cart"], a[href*="/cart"].h-btn').forEach(function(link){
        var valEl=link.querySelector('.value');
        if(valEl) valEl.textContent=ct;
        // Find or create count badge
        var badge=link.querySelector('.count');
        if(!badge && cc>0){
          badge=document.createElement('span');
          badge.className='count';
          badge.style.cssText='background:var(--gold-warm);color:var(--navy-dark);font-size:10px;font-weight:800;border-radius:10px;min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 6px;position:absolute;top:4px;right:4px;';
          link.style.position='relative';
          link.appendChild(badge);
        }
        if(badge){
          badge.textContent=cc;
          badge.style.display=cc>0?'inline-block':'none';
        }
      });
      // Mobile cart button (.mobile-cart-btn)
      var mCartBtn=document.querySelector('.mobile-cart-btn');
      if(mCartBtn){
        var mBadge=mCartBtn.querySelector('.cart-badge-mobile');
        if(!mBadge && cc>0){
          mBadge=document.createElement('span');
          mBadge.className='cart-badge-mobile';
          mBadge.style.cssText='position:absolute;top:0px;right:0px;background:var(--gold-warm);color:var(--navy-dark);font-size:9px;font-weight:bold;border-radius:10px;min-width:15px;height:15px;line-height:15px;text-align:center;padding:0 3px;';
          mCartBtn.style.position='relative';
          mCartBtn.appendChild(mBadge);
        }
        if(mBadge){
          mBadge.textContent=cc;
          mBadge.style.display=cc>0?'inline-block':'none';
        }
      }
      // Mobile .mobile-cart-count text
      var mCartCount=document.querySelector('.mobile-cart-count');
      if(mCartCount) mCartCount.textContent=cc;
    }else{
      coolToastShow(d.msg||'Không thể thêm vào giỏ hàng','❌');
    }
  }).catch(function(){btn.style.pointerEvents='';btn.style.opacity='';coolToastShow('Lỗi kết nối','❌');});
}
</script>

<!-- Global Toast Popup System -->
<div id="coolToast" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;pointer-events:none">
  <div id="coolToastBox" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);background:#fff;border-radius:16px;padding:26px 28px;max-width:380px;width:90%;box-sizing:border-box;text-align:center;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,0.3);pointer-events:auto;opacity:0;transition:all 0.25s ease">
    <div id="coolToastIcon" style="margin-bottom:14px"></div>
    <div id="coolToastMsg" style="color:#1a3258;font-size:14px;line-height:1.6;margin-bottom:18px;word-break:break-word"></div>
    <button onclick="coolToastHide()" style="padding:10px 34px;background:#1a3258;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;min-width:104px">OK</button>
  </div>
  <div onclick="coolToastHide()" style="position:absolute;inset:0;background:rgba(0,0,0,0.4);z-index:-1;pointer-events:auto"></div>
</div>
<script>
function coolToastShow(msg,icon){
  var t=document.getElementById('coolToast'),b=document.getElementById('coolToastBox');
  document.getElementById('coolToastMsg').innerHTML=msg;
  (function(ic){ var s=String(icon||''); var err=/❌|⚠|✖|✗|✕/.test(s); var col=err?'#c0392b':'#1a3258'; var sym=err?'!':'✓'; ic.innerHTML='<span style="display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:50%;background:'+col+';color:#fff;font-size:28px;font-weight:800;line-height:1">'+sym+'</span>'; })(document.getElementById('coolToastIcon'));
  t.style.display='block';
  setTimeout(function(){b.style.opacity='1';b.style.transform='translate(-50%,-50%) scale(1)';},10);
  if(window._toastTimeout) clearTimeout(window._toastTimeout);
  window._toastTimeout = setTimeout(function(){ coolToastHide(); }, 5000);
}
function coolToastHide(){
  var t=document.getElementById('coolToast'),b=document.getElementById('coolToastBox');
  b.style.opacity='0';b.style.transform='translate(-50%,-50%) scale(0.9)';
  setTimeout(function(){t.style.display='none';},250);
}
// Override native alert
window._origAlert=window.alert;
window.alert=function(msg){coolToastShow(msg);};
</script>

<!-- Floating Social Bubbles -->
<style>
.floating-social { position: fixed; bottom: 100px; right: 24px; display: flex; flex-direction: column; gap: 10px; z-index: 9999; }
.floating-social a { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.22); transition: transform 0.25s ease, box-shadow 0.25s ease; color: #fff; text-decoration: none; position: relative; z-index: 10; }

/* Continuous Visible Ripple Wave Effect (Gợn sóng rõ nét, nổi bật) */
.floating-social a::before,
.floating-social a::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: inherit;
  z-index: -1;
  animation: rippleWave 2.4s infinite cubic-bezier(0.25, 1, 0.5, 1);
  pointer-events: none;
  box-shadow: 0 0 10px rgba(255,255,255,0.3);
}
.floating-social a::after {
  animation-delay: 1.2s;
}
@keyframes rippleWave {
  0% { transform: scale(1); opacity: 0.8; }
  50% { opacity: 0.5; }
  100% { transform: scale(1.25); opacity: 0; }
}

.floating-social a:hover { transform: translateY(-2px) scale(1.08); box-shadow: 0 6px 20px rgba(0,0,0,0.35); }
.floating-social a.fb { background: #1877F2; }
.floating-social a.tt { background: #000000; }
.floating-social a.zl { background: #0068FF; }
.floating-social a.wa { background: #25D366; }
.floating-social a.chat { background: #1a3258; }
.floating-social a svg { width: 22px; height: 22px; fill: currentColor; z-index: 2; }
.floating-social .tooltip { position: absolute; right: 58px; background: rgba(15,23,42,0.92); color: #fff; padding: 5px 12px; border-radius: 6px; font-size: 12px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s, transform 0.2s; font-weight: 600; transform: translateX(6px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.floating-social a:hover .tooltip { opacity: 1; transform: translateX(0); }

/* Ripple animation on Hotline "Gọi ngay" button in float-stack */
.float-stack { position: fixed; right: 20px; bottom: 24px; z-index: 9998; display: flex; flex-direction: column; gap: 8px; }
.float-stack .float-btn { position: relative; z-index: 10; }
.float-stack .float-btn::before {
  content: '';
  position: absolute;
  inset: -1px;
  border-radius: 999px;
  background: inherit;
  z-index: -1;
  animation: floatRipple 2.4s infinite cubic-bezier(0.25, 1, 0.5, 1);
  pointer-events: none;
}
@keyframes floatRipple {
  0% { transform: scale(1); opacity: 0.75; }
  50% { opacity: 0.45; }
  100% { transform: scale(1.18); opacity: 0; }
}

@media (max-width: 768px) {
  .floating-social,
  .float-stack { 
    display: none !important; 
    visibility: hidden !important; 
    pointer-events: none !important; 
    opacity: 0 !important; 
  }
}
</style>
<?php if (!str_starts_with(currentPath(), '/admin') && !str_starts_with(currentPath(), '/partner')): ?>
<div class="floating-social">
  <?php
$_zl = dbGet("SELECT value FROM system_config WHERE key='social_zalo'")['value'] ?? 'https://zalo.me/0705070526';
$_wa = dbGet("SELECT value FROM system_config WHERE key='social_whatsapp'")['value'] ?? '';
$_tt = dbGet("SELECT value FROM system_config WHERE key='social_tiktok'")['value'] ?? '';
$_fb = dbGet("SELECT value FROM system_config WHERE key='social_facebook'")['value'] ?? '';
if (!function_exists('socialAttrs')) { function socialAttrs($url){ return (!empty($url) && preg_match('#^https?://#i',$url)) ? 'href="'.htmlspecialchars($url).'" target="_blank" rel="noopener"' : 'style="cursor:default" onclick="return false;" aria-disabled="true"'; } }
?>
<a href="/customer/chat" class="chat" title="Chat với Admin (Hỗ trợ)">
    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
    <div class="tooltip">Chat với Admin</div>
  </a>
  <a <?= socialAttrs($_zl) ?> class="zl" title="Zalo Chat">
    <img src="/uploads/zalo_icon.png" alt="Zalo" style="width:30px;height:30px;object-fit:contain;border-radius:50%;z-index:2">
    <div class="tooltip">Zalo Chat</div>
  </a>
  <a <?= socialAttrs($_wa) ?> class="wa" title="WhatsApp">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    <div class="tooltip">WhatsApp</div>
  </a>
  <a <?= socialAttrs($_tt) ?> class="tt" title="Tiktok">
    <svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1.04-.1z"/></svg>
    <div class="tooltip">Tiktok</div>
  </a>
  <a <?= socialAttrs($_fb) ?> class="fb" title="Facebook">
    <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
    <div class="tooltip">Facebook</div>
  </a>
</div>
<?php endif; ?>
</main>

<?php if (!str_starts_with(currentPath(), '/admin') && !str_starts_with(currentPath(), '/partner')): ?>
<?php require __DIR__ . '/footer.php'; ?>
<?php endif; ?>
<script src="/js/app.js?v=20260608b"></script>
<script>
(function() {
  var toggle  = document.getElementById('mobileMenuToggle');
  var overlay = document.getElementById('mobileNavOverlay');
  var drawer  = document.getElementById('mobileNavDrawer');
  var closeBtn = document.getElementById('mobileNavClose');
  function openMenu() {
    if (toggle)  toggle.classList.add('open');
    if (overlay) overlay.classList.add('open');
    if (drawer)  drawer.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    if (toggle)  toggle.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    if (drawer)  drawer.classList.remove('open');
    document.body.style.overflow = '';
  }
  if (toggle)  toggle.addEventListener('click', openMenu);
  if (overlay) overlay.addEventListener('click', closeMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
})();
</script>

<!-- Mobile Bottom Navigation Removed Per User Request -->

<?php
/* staff-assign popup: notify a newly-assigned staff user once */
$__sapU = function_exists('currentUser') ? currentUser() : null;
if ($__sapU && ($__sapU['role'] ?? '') === 'staff') {
    $__sapN = dbGet("SELECT id, message FROM user_notifications WHERE user_id=? AND title='Phân quyền mới' AND is_read=0 ORDER BY id DESC LIMIT 1", [$__sapU['id']]);
    if ($__sapN) {
        dbRun("UPDATE user_notifications SET is_read=1 WHERE id=?", [$__sapN['id']]);
?>
<div id="staffAssignPopup" style="position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:14px;max-width:420px;width:100%;padding:28px 26px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <div style="width:56px;height:56px;border-radius:50%;background:#eef6ff;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:26px">&#127881;</div>
    <h3 style="margin:0 0 10px;font-size:18px;color:#1a3258">Tài khoản đã được phân quyền nhân viên</h3>
    <p style="margin:0 0 22px;font-size:14px;color:#555;line-height:1.6"><?= e($__sapN['message']) ?></p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <button onclick="document.getElementById('staffAssignPopup').remove()" style="padding:10px 18px;border:1px solid #d1d5db;background:#fff;color:#444;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">Để sau</button>
      <a href="/staff" style="padding:10px 22px;background:#1a3258;color:#fff;border-radius:8px;font-weight:700;text-decoration:none;font-size:14px">Vào khu vực nhân viên &#8594;</a>
    </div>
  </div>
</div>
<?php } } ?>

<?php require __DIR__ . '/confirm-modal.php'; ?>
<script>
/* ===== PJAX khách hàng: toàn bộ trang khách (storefront + tài khoản + giỏ hàng + tin nhắn) — không reload cả trang.
   Loại trừ: thanh toán (tiền), đăng nhập/đăng ký/đăng xuất, form upload file. ===== */
(function(){
  if(document.querySelector('.dash-main')) return;             /* trang admin -> bỏ qua (admin có pjax riêng) */
  if(!document.querySelector('main')) return;                  /* không có <main> -> bỏ qua */
  var EXCLUDE=/^\/(auth|logout)(\/|$)/;
  function pathOf(u){ try{ return (new URL(u, location.origin)).pathname.replace(/\/+$/,'')||'/'; }catch(e){ return ''; } }
  function pjaxable(u){
    var p=pathOf(u);
    if(p==='/admin'||p.indexOf('/admin/')===0||p==='/staff'||p.indexOf('/staff/')===0) return false;
    return !EXCLUDE.test(p);
  }
  function getMain(){ return document.querySelector('main'); }
  function reinit(root){ ['enhanceTables','enhanceSelects','enhanceDateInputs','enhanceFilePick','__initApp'].forEach(function(n){ if(typeof window[n]==='function'){ try{ window[n](root); }catch(e){} } }); }
  /* theo dõi & dọn timer của trang được swap (vd poll chat 3s) để không rò rỉ khi rời trang */
  var _setInterval=window.setInterval, _pageTimers=[];
  window.setInterval=function(){ var id=_setInterval.apply(window, arguments); _pageTimers.push(id); return id; };
  function clearPageTimers(){ for(var i=0;i<_pageTimers.length;i++){ try{ clearInterval(_pageTimers[i]); }catch(e){} } _pageTimers=[]; }
  function runScripts(root, done){
    var ss=Array.prototype.slice.call(root.querySelectorAll('script'));
    (function nx(i){
      if(i>=ss.length){ if(done) done(); return; }
      var o=ss[i];
      if(o.src){
        var s=document.createElement('script');
        for(var j=0;j<o.attributes.length;j++){ var a=o.attributes[j]; s.setAttribute(a.name,a.value); }
        s.onload=s.onerror=function(){ nx(i+1); }; o.parentNode.replaceChild(s,o);
      } else {
        /* chạy ở phạm vi TOÀN CỤC (indirect eval) -> function/var khai báo ở top vẫn là global,
           nên các handler inline onclick/onsubmit (vd saveCustInvoice) vẫn gọi được sau khi swap.
           Tạm override DOMContentLoaded để init chạy ngay. */
        var _a=document.addEventListener;
        document.addEventListener=function(t,f,op){ if(t==="DOMContentLoaded"){ try{f()}catch(e){if(window.console)console.error(e)} } else { return _a.call(document,t,f,op); } };
        try{ (0,eval)(o.textContent); }catch(e){ if(window.console)console.error(e); }
        document.addEventListener=_a;
        nx(i+1);
      }
    })(0);
  }
  function showFlash(doc){ var fs=doc.querySelector('.flash-stack'); if(!fs||typeof window.coolToastShow!=='function') return; var ms=fs.querySelectorAll('.flash'); for(var i=0;i<ms.length;i++){ var bad=/error|warning/.test(ms[i].className); window.coolToastShow(ms[i].textContent.trim(), bad?'⚠️':'✅'); } }
  var busy=false;
  function swap(html, url, mode, doc){
    doc=doc||new DOMParser().parseFromString(html,'text/html');
    var nm=doc.querySelector('main'), m=getMain();
    if(!nm||!m){ location.href=url; return; }
    clearPageTimers();                              /* dọn timer trang cũ (poll chat...) */
    m.innerHTML=nm.innerHTML;
    var t=doc.querySelector('title'); if(t) document.title=t.textContent;

    /* Synchronize primary header nav, mobile drawer, and bottom bar active states during PJAX swaps */
    var newNav=doc.querySelector('nav.primary'), curNav=document.querySelector('nav.primary');
    if(newNav && curNav){
      curNav.innerHTML=newNav.innerHTML;
      var wrap = document.getElementById('navScrollWrap');
      if(wrap){
        try {
          var savedPos = sessionStorage.getItem('navScrollPos');
          if (savedPos !== null) {
            wrap.scrollLeft = parseInt(savedPos, 10);
          }
        } catch(e){}
      }
    }
    var newMobNav=doc.querySelector('.mobile-nav-links'), curMobNav=document.querySelector('.mobile-nav-links');
    if(newMobNav && curMobNav) curMobNav.innerHTML=newMobNav.innerHTML;
    var newBottomBar=doc.querySelector('.mobile-bottom-bar'), curBottomBar=document.querySelector('.mobile-bottom-bar');
    if(newBottomBar && curBottomBar) curBottomBar.innerHTML=newBottomBar.innerHTML;

    runScripts(m, function(){ reinit(m); });
    if(mode==='push') history.pushState({cpjax:1},'',url); else history.replaceState({cpjax:1},'',url);
    m.style.opacity=''; m.style.pointerEvents=''; busy=false; window.scrollTo(0,0);
  }
  function load(url, mode){
    var m=getMain(); if(!m){ location.href=url; return; }
    busy=true; m.style.opacity='0.5'; m.style.pointerEvents='none';
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'})
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){ swap(html, url, mode||'push'); })
      .catch(function(){ location.href=url; });
  }
  var _natSubmit = HTMLFormElement.prototype.submit;
  function pjaxSubmit(f){
    if(!f || f.tagName!=='FORM') return false;
    /* multipart (upload ảnh) VẪN cho AJAX qua FormData — form có handler riêng (vd gửi chat) tự preventDefault ở phase target nên bị bỏ qua trước */
    if(f.querySelector('.tox, .ql-container, textarea[id^="tinymce"]')) return false;   /* chỉ bỏ qua form có trình soạn thảo */
    var action=f.getAttribute('action')||location.pathname; if(!pjaxable(action)) return false;
    var m=getMain(); if(!m||busy) return false;
    var method=(f.getAttribute('method')||'get').toLowerCase();
    if(method==='get'){
      var qs=new URLSearchParams(new FormData(f)).toString();
      var url=action+(qs?('?'+qs):'');
      if(!pjaxable(url)) return false;
      load(url,'push'); return true;
    }
    busy=true; m.style.opacity='0.5'; m.style.pointerEvents='none';
    fetch(action, {method:'POST', body:new FormData(f), headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin', redirect:'follow'})
      .then(function(r){ return r.text().then(function(h){ return {u:r.url||action, h:h}; }); })
      .then(function(res){ var doc=new DOMParser().parseFromString(res.h,'text/html'); if(!doc.querySelector('main')){ location.href=res.u; return; } swap(res.h, res.u, 'replace', doc); showFlash(doc); })  /* swap TRƯỚC rồi showFlash (vì #coolToast nằm trong <main>, swap sẽ xoá toast nếu show trước) */
      .catch(function(){ busy=false; _natSubmit.call(f); });
    return true;
  }
  HTMLFormElement.prototype.submit = function(){ try{ if(pjaxSubmit(this)) return; }catch(e){} return _natSubmit.call(this); };
  window.csNav = function(u){ if(pjaxable(u) && getMain()){ load(u,'push'); } else { location.href=u; } };
  document.addEventListener('click', function(e){
    if(e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return;
    var a=e.target.closest('a'); if(!a) return;
    var href=a.getAttribute('href'); if(!href||href.charAt(0)==='#'||href.lastIndexOf('javascript:',0)===0) return;
    if((a.target&&a.target!=='_self')||a.hasAttribute('download')||a.getAttribute('onclick')) return;
    if(a.origin&&a.origin!==location.origin) return;
    if(!pjaxable(href)||busy) return;
    e.preventDefault(); load(a.href, 'push');
  });
  document.addEventListener('submit', function(e){
    if(e.defaultPrevented) return;
    if(pjaxSubmit(e.target)) e.preventDefault();
  });
  window.addEventListener('popstate', function(){ if(pjaxable(location.href)) load(location.href,'replace'); });
})();
</script>

<!-- Global Favorite Login Prompt Modal -->
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
function showFavLoginPopup(url) {
  var m = document.getElementById("favLoginModal");
  if (!m) return;
  var go = document.getElementById("favLoginGo");
  if (go && url) go.href = url;
  m.style.display = "flex";
}

(function(){
  var m = document.getElementById("favLoginModal");
  if (m) {
    m.addEventListener("click", function(e) {
      if (e.target === m) m.style.display = "none";
    });
  }
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && m) m.style.display = "none";
  });
})();

window.toggleFav = function toggleFav(btn, productId) {
  var isFav = btn.getAttribute('data-fav') === '1';
  var newFav = !isFav;
  var svg = btn.querySelector('svg');
  if (svg) svg.setAttribute('fill', newFav ? '#e74c3c' : 'none');
  btn.setAttribute('data-fav', newFav ? '1' : '0');
  btn.title = newFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
  
  var csrf = (typeof _csrf !== 'undefined') ? _csrf : ((typeof window._CSRF !== 'undefined') ? window._CSRF : '');
  
  fetch('/customer/favorites/toggle', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    credentials: 'same-origin',
    body: '_csrf=' + encodeURIComponent(csrf) + '&product_id=' + encodeURIComponent(productId)
  }).then(function(r){ return r.json(); }).then(function(data){
    if (!data.ok) {
      if (svg) svg.setAttribute('fill', isFav ? '#e74c3c' : 'none');
      btn.setAttribute('data-fav', isFav ? '1' : '0');
      btn.title = isFav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
      if (data.redirect || data.login) {
        showFavLoginPopup(data.redirect || '/auth/login');
      }
    } else {
      if (svg) svg.setAttribute('fill', data.fav ? '#e74c3c' : 'none');
      btn.setAttribute('data-fav', data.fav ? '1' : '0');
      btn.title = data.fav ? 'Bỏ yêu thích' : 'Thêm vào yêu thích';
      if (typeof updateFavBadge === 'function') {
        updateFavBadge(data.count !== undefined ? data.count : (data.fav ? 1 : 0));
      }
    }
  }).catch(function(){
    showFavLoginPopup('/auth/login');
    if (svg) svg.setAttribute('fill', isFav ? '#e74c3c' : 'none');
    btn.setAttribute('data-fav', isFav ? '1' : '0');
  });
};
</script>
</body>
</html>
