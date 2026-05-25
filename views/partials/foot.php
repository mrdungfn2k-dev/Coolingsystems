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

function updateCartBadge(n) {
  var cc=parseInt(n)||0;
  document.querySelectorAll('.count,[data-cart-count]').forEach(function(b){
    b.textContent=cc>0?cc:'';b.style.display=cc>0?'inline-block':'none';
  });
  var mb=document.querySelector('.cart-badge-mobile');
  if(!mb&&cc>0){var mc=document.querySelector('.mobile-cart-btn');if(mc){mb=document.createElement('span');mb.className='cart-badge-mobile';mb.style.cssText='';mc.appendChild(mb);}}
  if(mb){mb.textContent=cc;mb.style.display=cc>0?'flex':'none';}
}
</script>
<script>
function ajaxAddCart(pid, btn){
  var csrf='';
  if(typeof window._CSRF!=='undefined') csrf=window._CSRF;
  else{var ci=document.querySelector('input[name="_csrf"]');if(ci)csrf=ci.value;}
  if(!csrf){coolToastShow('Vui lòng đăng nhập để thêm giỏ hàng','🛒');return;}
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
  <div id="coolToastBox" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(0.9);background:#fff;border-radius:16px;padding:28px 32px;max-width:400px;width:90%;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,0.3);pointer-events:auto;opacity:0;transition:all 0.25s ease">
    <div id="coolToastIcon" style="font-size:40px;margin-bottom:10px">ℹ️</div>
    <div id="coolToastMsg" style="color:#1a3258;font-size:14px;line-height:1.6;margin-bottom:18px;word-break:break-word"></div>
    <button onclick="coolToastHide()" style="padding:10px 32px;background:linear-gradient(135deg,#c8a951,#b8860b);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;min-width:100px">OK</button>
  </div>
  <div onclick="coolToastHide()" style="position:absolute;inset:0;background:rgba(0,0,0,0.4);z-index:-1;pointer-events:auto"></div>
</div>
<script>
function coolToastShow(msg,icon){
  var t=document.getElementById('coolToast'),b=document.getElementById('coolToastBox');
  document.getElementById('coolToastMsg').innerHTML=msg;
  document.getElementById('coolToastIcon').textContent=icon||'ℹ️';
  t.style.display='block';
  setTimeout(function(){b.style.opacity='1';b.style.transform='translate(-50%,-50%) scale(1)';},10);
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
.floating-social { position: fixed; bottom: 110px; right: 30px; display: flex; flex-direction: column; gap: 12px; z-index: 9999; }
.floating-social a { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s; color: #fff; text-decoration: none; position: relative; }
.floating-social a:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 6px 16px rgba(0,0,0,0.2); }
.floating-social a.fb { background: #1877F2; }
.floating-social a.tt { background: #000000; }
.floating-social a.wa { background: #25D366; }
.floating-social a svg { width: 24px; height: 24px; fill: currentColor; }
.floating-social .tooltip { position: absolute; right: 60px; background: rgba(0,0,0,0.8); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 12px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s; font-weight: 600; }
.floating-social a:hover .tooltip { opacity: 1; }
@media (max-width: 768px) {
  .floating-social { bottom: 140px; right: 20px; }
  .floating-social a { width: 50px; height: 50px; }
  .floating-social a svg { width: 24px; height: 24px; }
  .floating-social .tooltip { display: none; }
}
</style>
<?php if (!str_starts_with(currentPath(), '/admin') && !str_starts_with(currentPath(), '/partner')): ?>
<div class="floating-social">
  <?php
$_wa = dbGet("SELECT value FROM system_config WHERE key='social_whatsapp'")['value'] ?? '#';
$_tt = dbGet("SELECT value FROM system_config WHERE key='social_tiktok'")['value'] ?? '#';
$_fb = dbGet("SELECT value FROM system_config WHERE key='social_facebook'")['value'] ?? '#';
?>
<a href="<?= htmlspecialchars($_wa) ?>" class="wa" title="WhatsApp" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    <div class="tooltip">WhatsApp</div>
  </a>
  <a href="<?= htmlspecialchars($_tt) ?>" class="tt" title="Tiktok" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1.04-.1z"/></svg>
    <div class="tooltip">Tiktok</div>
  </a>
  <a href="<?= htmlspecialchars($_fb) ?>" class="fb" title="Facebook" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
    <div class="tooltip">Facebook</div>
  </a>
</div>
<?php endif; ?>
</main>

<?php if (!str_starts_with(currentPath(), '/admin') && !str_starts_with(currentPath(), '/partner')): ?>
<?php require __DIR__ . '/footer.php'; ?>
<?php endif; ?>
<script src="/js/app.js"></script>
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

<!-- Mobile Bottom Navigation -->
<?php $user = $user ?? currentUser(); ?>
<?php $__uri=$_SERVER["REQUEST_URI"]??"";if(strpos($__uri,"/admin")!==0&&strpos($__uri,"/staff")!==0): ?>
<div class="mobile-bottom-bar">
  <a href="/" class="mb-item"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg><span>Trang chủ</span></a>
  <?php if ($user): ?>
  <a href="/customer/orders" class="mb-item"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span>Đơn hàng</span></a>
  <a href="/customer/chat" class="mb-item"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg><span>Tin nhắn</span></a>
  <a href="/customer/favorites" class="mb-item pos-rel"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg><span>Yêu thích</span></a>
  <a href="/cart" class="mb-item pos-rel"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg><span>Giỏ hàng</span></a>
  <?php else: ?>
  <a href="/auth/login" class="mb-item"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Đăng nhập</span></a>
  <?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>