<?php $user = $user ?? (function_exists('currentUser') ? currentUser() : null); ?>
<style>
.all-cats-wrap:hover .cat-dropdown,.all-cats-wrap.open .cat-dropdown{display:block!important}
.cat-dropdown a:hover{background:#f5f7fb!important;color:#c8a951!important}
.all-cats-wrap .arrow{transition:transform 0.2s}
.all-cats-wrap:hover .arrow,.all-cats-wrap.open .arrow{transform:rotate(180deg)}

/* Modern header & search bar responsive layout */
header.main { min-height: 90px !important; height: auto !important; padding: 12px 0 !important; background: #fff !important; }
header.main .wrap { max-width: 1280px !important; margin: 0 auto !important; padding: 0 16px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 10px 12px !important; }
header.main .search { border: 1px solid var(--line) !important; border-radius: 8px !important; display: flex !important; flex: 1 1 300px !important; max-width: 520px !important; min-width: 180px !important; margin: 0 16px !important; }
header.main .search:focus-within { border-color: var(--navy) !important; box-shadow: 0 0 0 3px var(--navy-soft) !important; }
header.main .search .submit { flex-shrink:0; padding:0!important; min-width:44px!important; width:44px!important; background:transparent!important; color:#555!important; border-left:1px solid var(--line)!important; border-radius:0!important; display:flex; align-items:center; justify-content:center; }
header.main .search .submit::before { display:none!important; }
header.main .search .submit:hover { background:var(--bg-soft)!important; color:var(--navy)!important; }
header.main .search input { flex:1; min-width:120px!important; width:100%; padding:10px 12px; color:#1e293b; font-size:13.5px; }
header.main .hotline-slider-card {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%) !important;
  border: 1px solid #cbd5e1 !important;
  border-radius: 12px !important;
  padding: 5px 10px !important;
  min-width: 220px !important;
  max-width: 245px !important;
  height: 48px !important;
  box-sizing: border-box !important;
  position: relative !important;
  box-shadow: 0 2px 8px rgba(26,50,88,0.06) !important;
  user-select: none !important;
  flex-shrink: 0 !important;
}
header.main .hotline-slider-card .pulse-icon {
  width: 32px !important;
  height: 32px !important;
  background: #1a3258 !important;
  color: #fff !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  flex-shrink: 0 !important;
  box-shadow: 0 0 0 0 rgba(26,50,88,0.4) !important;
  animation: pulse-ring 2s infinite !important;
}
@keyframes pulse-ring {
  0% { box-shadow: 0 0 0 0 rgba(26,50,88,0.4); }
  70% { box-shadow: 0 0 0 8px rgba(26,50,88,0); }
  100% { box-shadow: 0 0 0 0 rgba(26,50,88,0); }
}
header.main .hotline-slider-card .slider-box {
  flex: 1 !important;
  position: relative !important;
  height: 36px !important;
  overflow: hidden !important;
}
header.main .hotline-slider-card .slide-unit {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: center !important;
  opacity: 0 !important;
  transform: translateX(-100%) !important;
  transition: transform 0.45s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.45s ease !important;
  pointer-events: none !important;
}
header.main .hotline-slider-card .slide-unit.active {
  opacity: 1 !important;
  transform: translateX(0) !important;
  pointer-events: auto !important;
}
header.main .hotline-slider-card .slide-unit.exit-right {
  opacity: 0 !important;
  transform: translateX(100%) !important;
  pointer-events: none !important;
}
header.main .hotline-slider-card .slide-unit .lbl {
  font-size: 10.5px !important;
  font-weight: 700 !important;
  color: #64748b !important;
  text-transform: uppercase !important;
  letter-spacing: 0.2px !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  line-height: 1.2 !important;
}
header.main .hotline-slider-card .slide-unit .num {
  font-size: 14px !important;
  font-weight: 800 !important;
  color: #1a3258 !important;
  text-decoration: none !important;
  line-height: 1.2 !important;
  transition: color 0.15s !important;
}
header.main .hotline-slider-card .slide-unit .num:hover {
  color: #c8a951 !important;
}
header.main .hotline-slider-card .slider-arrow {
  width: 20px !important;
  height: 20px !important;
  border-radius: 50% !important;
  background: #fff !important;
  border: 1px solid #cbd5e1 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: #475569 !important;
  cursor: pointer !important;
  flex-shrink: 0 !important;
  transition: all 0.15s !important;
}
header.main .hotline-slider-card .slider-arrow:hover {
  background: #1a3258 !important;
  color: #fff !important;
  border-color: #1a3258 !important;
}

@media(max-width: 768px) {
  header.main { min-height: auto !important; padding: 4px 0 !important; background: #fff !important; margin: 0 !important; border-bottom: 1px solid #e2e8f0 !important; }
  header.main .wrap { 
    display: flex !important; 
    flex-direction: row !important; 
    flex-wrap: nowrap !important; 
    align-items: center !important; 
    justify-content: space-between !important; 
    padding: 0 12px !important; 
    gap: 8px !important; 
    width: 100% !important; 
    height: 48px !important;
    min-height: 48px !important;
    max-height: 48px !important;
    box-sizing: border-box !important;
    grid-template-columns: none !important;
    margin: 0 !important;
  }
  
  /* Ultra-compact mobile logo centered inline with cart and menu icons */
  header.main .logo { 
    display: flex !important; 
    align-items: center !important; 
    justify-content: flex-start !important; 
    flex: 0 0 auto !important; 
    width: 180px !important; 
    height: 40px !important; 
    max-width: 180px !important; 
    max-height: 40px !important; 
    margin: 0 !important; 
    padding: 0 !important;
  }
  header.main .logo img, header.main .logo svg { 
    width: 180px !important; 
    height: 40px !important; 
    max-width: 180px !important; 
    max-height: 40px !important; 
    object-fit: contain !important; 
    object-position: left center !important; 
    margin: 0 !important;
  }

  header.main .hotline-slider-card { display: none !important; }
  header.main .search { display: none !important; }
  header.main .header-actions { display: none !important; }
  
  /* Ultra-compact search bar below header */
  .mobile-search-bar { display: flex !important; padding: 4px 12px 4px 12px !important; width: 100% !important; box-sizing: border-box !important; background: #fff !important; }
  .mobile-search-bar form { display: flex !important; border: 1.5px solid #1a3258 !important; border-radius: 8px !important; overflow: hidden !important; background: #fff !important; width: 100% !important; height: 36px !important; box-shadow: 0 2px 6px rgba(0,0,0,0.04) !important; }
  .mobile-search-bar input { flex: 1 !important; border: none !important; padding: 0 10px !important; font-size: 13px !important; outline: none !important; background: transparent !important; color: #1e293b !important; }
  .mobile-search-bar button { width: 40px !important; height: 36px !important; border: none !important; background: #1a3258 !important; color: #fff !important; cursor: pointer !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; }
  
  /* Right actions (Cart & Menu toggle) in static flex flow perfectly centered on same row */
  .mobile-right-actions { 
    position: static !important;
    top: auto !important;
    right: auto !important;
    transform: none !important;
    display: flex !important; 
    align-items: center !important; 
    align-self: center !important; 
    gap: 6px !important; 
    margin-left: auto !important; 
    flex-shrink: 0 !important; 
    height: 40px !important;
    padding: 0 !important;
  }
  .mobile-icon-btn, .mobile-cart-btn { 
    padding: 2px !important; 
    color: #1a3258 !important; 
    background: transparent !important; 
    border: none !important; 
    display: flex !important; 
    align-items: center !important; 
    justify-content: center !important; 
    border-radius: 6px !important; 
    position: relative !important; 
    text-decoration: none !important; 
    height: 34px !important;
    width: 34px !important;
  }
  .cart-badge-mobile { position: absolute !important; top: -2px !important; right: -2px !important; background: #c8962b !important; color: #fff !important; font-size: 10px !important; font-weight: 800 !important; border-radius: 10px !important; min-width: 16px !important; height: 16px !important; line-height: 16px !important; text-align: center !important; padding: 0 3px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important; z-index: 5 !important; }
  
  /* Transparent frameless hamburger toggle matching cart button color */
  .mobile-menu-toggle {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 34px !important;
    height: 34px !important;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    color: #1a3258 !important;
    margin-left: 2px !important;
  }
  .mobile-menu-toggle svg { width: 22px !important; height: 22px !important; stroke: #1a3258 !important; }
}
</style>
<?php 
$cart = cartInfo(); $fav = favCount();
$sysConfig = dbAll("SELECT key, value FROM system_config");
$configMap = [];
foreach($sysConfig as $cfg) {
    $configMap[$cfg['key']] = $cfg['value'];
}
$siteLogo = $configMap['site_logo'] ?? '';
$sitePhone = $configMap['site_phone'] ?? '<?= $sysHotline ?>';

$rawHotlineConfig = $configMap['hotline_list'] ?? '';
$hotlineItems = !empty($rawHotlineConfig) ? json_decode($rawHotlineConfig, true) : null;
if (empty($hotlineItems) || !is_array($hotlineItems)) {
    $hotlineItems = [
        ['label' => 'CSKH & Dịch vụ', 'phone' => '0705.0705.26'],
        ['label' => 'CSKH & Dịch vụ', 'phone' => '0705.0705.28'],
        ['label' => 'Kĩ thuật & Bảo Hành', 'phone' => '0704.0704.18'],
        ['label' => 'Bán Buôn', 'phone' => '0703.0703.21'],
        ['label' => 'Bán Buôn', 'phone' => '0703.0703.61'],
        ['label' => 'Bán lẻ', 'phone' => '0703.0703.15']
    ];
}
?>
<header class="main">
  <div class="wrap">
    <a href="/" class="logo" aria-label="Trang chủ Cooling - Phụ tùng ô tô chính hãng">
      <?php if ($siteLogo): ?>
        <img src="/uploads/<?= htmlspecialchars($siteLogo) ?>" alt="Cooling - Phụ tùng ô tô chính hãng" style="width:180px;height:76px;max-width:180px;max-height:76px;object-fit:contain;aspect-ratio:180/76" width="180" height="76">
      <?php else: ?>
        <svg class="logo-svg" width="180" height="76" style="width:180px;height:76px;aspect-ratio:180/76" viewBox="0 0 480 200" aria-label="Cooling logo" role="img"><use href="#cooling-logo"/></svg>
      <?php endif; ?>
    </a>
    <form class="search" method="get" action="/products" onsubmit="if(!this.q.value.trim()){coolToastShow('Vui lòng nhập từ khóa tìm kiếm (SKU, mã OEM, tên phụ tùng...)','🔍');return false;}">
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm theo SKU, mã OEM, tên phụ tùng..." aria-label="Tìm kiếm phụ tùng theo SKU, mã OEM, tên sản phẩm">
      <button class="submit" type="submit" aria-label="Tìm kiếm">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </form>
    
    <div class="header-actions">
      <?php
        $isGuestUser = $user && strpos($user['email'] ?? '', '@guest.local') !== false;
      ?>
      <?php if ($user && in_array($user['role'], ['customer','staff']) && !$isGuestUser): ?>
        <?php
          $unreadNotiCount = dbGet("SELECT COUNT(*) as n FROM user_notifications WHERE user_id=? AND is_read=0", [$user['id']])['n'] ?? 0;
          $userNotis = dbAll("SELECT * FROM user_notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 8", [$user['id']]);
        ?>
        <!-- Customer Notification Bell -->
        <div style="position:relative;display:inline-block">
          <button onclick="toggleUserNoti(event)" style="background:none;border:none;cursor:pointer;position:relative;padding:8px 10px;display:flex;align-items:center;justify-content:center;color:#1a3258">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <?php /* badge so thong bao da an theo yeu cau */ ?>
          </button>
          <div id="userNotiDropdown" style="display:none;position:absolute;right:0;top:42px;width:320px;background:#fff;border:1px solid #e0e4eb;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.12);z-index:200;max-height:380px;overflow-y:auto">
            <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center">
              <h4 style="margin:0;font-size:14px;color:#1a3258;font-weight:700"> Thông báo</h4>
              <button onclick="markAllUserNotiRead()" style="background:none;border:none;color:#d4a84b;font-size:12px;font-weight:600;cursor:pointer">Đọc tất cả</button>
            </div>
            <div id="userNotiList">
              <?php if(empty($userNotis)): ?>
                <div style="padding:24px;text-align:center;font-size:13px;color:#888">Chưa có thông báo nào</div>
              <?php else: ?>
              <?php foreach($userNotis as $n): ?>
              <div style="position:relative">
                <a href="<?= e($n['link'] ?? '/customer/orders') ?>" onclick="return goUserNoti(<?= $n['id'] ?>, this)" style="display:block;padding:12px 40px 12px 14px;border-bottom:1px solid #f5f5f5;text-decoration:none;background:<?= $n['is_read'] ? '#fff' : '#f0f4ff' ?>;transition:background .2s" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='<?= $n['is_read'] ? '#fff' : '#f0f4ff' ?>'">
                  <div style="font-size:13px;font-weight:<?= $n['is_read'] ? '400' : '700' ?>;color:#1a3258;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding-right:20px"><?= e($n['title']) ?></div>
                  <div style="font-size:12px;color:#555;margin-top:3px;line-height:1.4;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;padding-right:20px"><?= e($n['message']) ?></div>
                  <div style="font-size:10px;color:#999;margin-top:5px"><?= date('d/m H:i', strtotime($n['created_at'])) ?></div>
                </a>
                <button onclick="deleteUserNoti(<?= $n['id'] ?>, event, this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#bbb;padding:4px" title="Xóa">
                  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path></svg>
                </button>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Popup confirm delete notification modal -->
        <div id="userNotiConfirmModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999;background:rgba(10,25,47,0.5);align-items:center;justify-content:center;padding:16px;box-sizing:border-box">
          <div style="background:#fff;border-radius:14px;max-width:340px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,0.3);padding:22px 20px;text-align:center">
            <div style="font-size:16px;font-weight:700;color:#1a3258;margin-bottom:8px">Xác nhận xóa</div>
            <p style="font-size:13px;color:#555;margin:0 0 18px;line-height:1.5">Bạn có chắc chắn muốn xóa thông báo này không?</p>
            <div style="display:flex;gap:10px">
              <button type="button" onclick="closeUserNotiConfirm()" style="flex:1;padding:10px;background:#eef2f7;color:#1a3258;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px">Hủy</button>
              <button type="button" onclick="confirmDeleteUserNoti()" style="flex:1;padding:10px;background:#e74c3c;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:13px">Xóa</button>
            </div>
          </div>
        </div>

        <script>
        function toggleUserNoti(e) {
          e.stopPropagation();
          const d = document.getElementById('userNotiDropdown');
          d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }
        document.addEventListener('click', function(e) {
          const d = document.getElementById('userNotiDropdown');
          if(d && !d.contains(e.target)) d.style.display = 'none';
        });
        function markUserNotiRead(id) {
          fetch('/customer/notifications/'+id+'/read', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+(document.querySelector('input[name="_csrf"]')?.value||'')});
        }
        function markAllUserNotiRead() {
          fetch('/customer/notifications/read-all', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+(document.querySelector('input[name="_csrf"]')?.value||'')})
          .then(function(){
            var badge = document.querySelector('button[onclick*="toggleUserNoti"] span');
            if(badge) badge.remove();
            document.querySelectorAll('#userNotiList a').forEach(function(a){
              a.style.background='#fff';
              a.onmouseover=function(){this.style.background='#f8f9fa'};
              a.onmouseout=function(){this.style.background='#fff'};
              var title=a.querySelector('div');
              if(title) title.style.fontWeight='400';
            });
          });
        }
        function goUserNoti(id, link){ try{markUserNotiRead(id);}catch(e){} var d=document.getElementById('userNotiDropdown'); if(d)d.style.display='none'; var href=(link&&link.getAttribute('href'))||'/customer/orders'; if(window.csNav){csNav(href);}else{location.href=href;} return false; }
        
        var _pendingNotiDelId = null;
        var _pendingNotiBtn = null;

        function deleteUserNoti(id, e, btn) {
          e.preventDefault(); e.stopPropagation();
          _pendingNotiDelId = id;
          _pendingNotiBtn = btn;
          var modal = document.getElementById('userNotiConfirmModal');
          if(modal) modal.style.display = 'flex';
        }

        function closeUserNotiConfirm() {
          var modal = document.getElementById('userNotiConfirmModal');
          if(modal) modal.style.display = 'none';
          _pendingNotiDelId = null;
          _pendingNotiBtn = null;
        }

        function confirmDeleteUserNoti() {
          if(!_pendingNotiDelId) return;
          var id = _pendingNotiDelId;
          var btn = _pendingNotiBtn;
          closeUserNotiConfirm();
          fetch('/customer/notifications/'+id+'/delete', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'_csrf='+(document.querySelector('input[name="_csrf"]')?.value||'')
          })
          .then(function(){
            if(btn && btn.parentNode){ btn.parentNode.remove(); }
            var L = document.getElementById('userNotiList');
            if(L && !L.querySelector('a')){
              L.innerHTML='<div style="padding:24px;text-align:center;font-size:13px;color:#888">Chưa có thông báo nào</div>';
            }
          });
        }

        document.addEventListener('click', function(e) {
          var modal = document.getElementById('userNotiConfirmModal');
          if(modal && e.target === modal) closeUserNotiConfirm();
        });
        </script>

        <a href="/customer/orders" class="h-btn"><span class="label">Đơn hàng</span><span class="value"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg></span></a>
        <a href="/customer/chat" class="h-btn"><span class="label">Tin nhắn</span><span class="value"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></span></a>
        <a href="/customer/favorites" class="h-btn"><span class="label">Yêu thích</span><span class="value"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></span><?php if($fav > 0): ?><span class="count"><?= $fav ?></span><?php endif; ?></a>
        <a href="/customer/cart" class="h-btn"><span class="label">Giỏ hàng</span><span class="value"><?= vnd($cart['total']) ?></span>
          <?php if ($cart['cnt'] > 0): ?><span class="count"><?= $cart['cnt'] ?></span><?php endif; ?>
        </a>
        <a href="/customer/profile" class="h-btn" style="display:flex;align-items:center;gap:8px">
          <div style="width:32px;height:32px;border-radius:50%;background:#f0f0f0;overflow:hidden;border:1px solid #d0d5e0;flex-shrink:0">
            <?php if(!empty($user['avatar'])): ?>
              <img src="/uploads/avatars/<?= e($user['avatar']) ?>" alt="Ảnh đại diện <?= e($user['full_name'] ?? '') ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <img src="/assets/images/default-avatar.png" alt="Ảnh đại diện" style="width:100%;height:100%;object-fit:cover" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2NjYyI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00czEuNzktNCA0LTRtMCAxMGMtMi42NyAwLTggMS4zNC04IDR2MmgxNnYtMmMwLTIuNjYtNS4zMy00LTgtNHoiLz48L3N2Zz4='">
            <?php endif; ?>
          </div>
          <div>
            <span class="label" style="display:block;font-size:10px;text-transform:uppercase;color:#888;font-weight:700">Tài khoản</span>
            <span class="value" style="display:block;font-size:13px;font-weight:700;color:var(--navy)"><?= truncate($user['full_name'], 12) ?></span>
          </div>
        </a>
      <?php elseif ($user && $user['role'] === 'admin'): ?>
        <a href="/admin" class="h-btn"><span class="label">Admin</span><span class="value">Bảng điều khiển</span></a>
        <a href="/admin/logout" class="h-btn"><span class="label">Đăng xuất</span><span class="value"><?= truncate($user['full_name'], 12) ?></span></a>
      <?php else: ?>
        <?php if($isGuestUser ?? false): ?>
          <a href="/customer/cart" class="h-btn"><span class="label">Giỏ hàng</span><span class="value"><?= vnd($cart['total'] ?? 0) ?></span><?php if (($cart['cnt'] ?? 0) > 0): ?><span class="count"><?= $cart['cnt'] ?></span><?php endif; ?></a>
        <?php else: ?>
          <a href="/customer/cart" class="h-btn"><span class="label">Giỏ hàng</span><span class="value"><?= vnd($cart['total'] ?? 0) ?></span><?php if (($cart['cnt'] ?? 0) > 0): ?><span class="count"><?= $cart['cnt'] ?></span><?php endif; ?></a>
        <?php endif; ?>
        <a href="/auth/login" class="h-btn"><span class="label">Tài khoản</span><span class="value">Đăng nhập</span></a>
        <a href="/auth/register" class="h-btn"><span class="label">Mới?</span><span class="value">Đăng ký</span></a>
      <?php endif; ?>
    </div>
    <div class="mobile-right-actions" style="display:flex;align-items:center;gap:8px;margin-left:auto;">
      <?php if ($user && in_array($user['role'], ['customer','garage','staff'])): ?>
        <!-- Thông báo -->
        <a href="/customer/notifications" class="mobile-icon-btn" aria-label="Thông báo" style="position:relative;color:var(--navy);align-items:center;padding:6px;" title="Thông báo">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
          <?php if (!empty($notiCount) && $notiCount > 0): ?>
            
          <?php endif; ?>
        </a>
        <!-- Tin nhắn -->
        <a href="/customer/chat" class="mobile-icon-btn" aria-label="Tin nhắn" style="position:relative;color:var(--navy);align-items:center;padding:6px;" title="Tin nhắn">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </a>
        <!-- Yêu thích -->
        <a href="/customer/favorites" class="mobile-icon-btn" aria-label="Danh sách yêu thích" style="position:relative;color:var(--navy);align-items:center;padding:6px;" title="Yêu thích">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <?php if (!empty($fav) && $fav > 0): ?>
            <span class="fav-badge-mobile"><?= min($fav, 99) ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
      <!-- Giỏ hàng -->
      <a href="/customer/cart" class="mobile-cart-btn" aria-label="Giỏ hàng<?= $cart['cnt'] > 0 ? ' (' . $cart['cnt'] . ' sản phẩm)' : '' ?>" style="position:relative;color:var(--navy);display:flex;align-items:center;padding:6px;">
         <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
         <?php if ($cart['cnt'] > 0): ?><span class="cart-badge-mobile" aria-hidden="true"><?= $cart['cnt'] ?></span><?php endif; ?>
      </a>
      <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Mở menu" style="display:flex;align-items:center;justify-content:center;width:38px;height:38px;padding:0;background:#1a3258;border:none;border-radius:6px;cursor:pointer;color:#fff;flex-shrink:0;margin-left:4px">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
      </button>
    </div>
  </div>
  <div class="mobile-search-bar">
    <form method="get" action="/products" style="width:100%" onsubmit="if(!this.q.value.trim()){coolToastShow('Vui lòng nhập từ khóa tìm kiếm','🔍');return false;}">
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm phụ tùng, SKU, mã OEM...">
      <button type="submit" aria-label="Tìm kiếm" style="display:inline-flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
    </form>
  </div>
</header>
