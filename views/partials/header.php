<style>
.all-cats-wrap:hover .cat-dropdown,.all-cats-wrap.open .cat-dropdown{display:block!important}
.cat-dropdown a:hover{background:#f5f7fb!important;color:#c8a951!important}
.all-cats-wrap .arrow{transition:transform 0.2s}
.all-cats-wrap:hover .arrow,.all-cats-wrap.open .arrow{transform:rotate(180deg)}

/* Modern search bar styling */
header.main .search { border: 1px solid var(--line) !important; border-radius: 8px !important; }
header.main .search:focus-within { border-color: var(--navy) !important; box-shadow: 0 0 0 3px var(--navy-soft) !important; }
header.main .search .submit { flex-shrink:0; padding:0!important; min-width:48px!important; width:48px!important; background:transparent!important; color:#888!important; border-left:1px solid var(--line)!important; border-radius:0!important; display:flex; align-items:center; justify-content:center; }
header.main .search .submit::before { display:none!important; }
header.main .search .submit:hover { background:var(--bg-soft)!important; color:var(--navy)!important; }
@media(min-width: 901px) {
  header.main .wrap { max-width: 1280px !important; padding: 0 20px !important; gap: 20px !important; }
  header.main .search { display:flex; flex: 1 1 auto; max-width: 600px; min-width: 250px !important; margin: 0 auto !important; }
  header.main .search input { flex:1; min-width: 180px !important; width: 100%; padding-right: 12px; }
  header.main .hotline { margin-left: 0 !important; }
}
@media(max-width: 900px) {
  header.main .search { display:flex; flex: 1; min-width: 150px !important; margin: 0 8px !important; }
  header.main .search input { flex:1; min-width: 80px !important; width: 100%; }
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
?>
<style>@media(min-width:901px){ .mobile-cart-btn{display:none!important;} }</style>
<header class="main">
  <div class="wrap">
    <a href="/" class="logo" aria-label="Trang chủ Cooling - Phụ tùng ô tô chính hãng">
      <?php if ($siteLogo): ?>
        <img src="/uploads/<?= htmlspecialchars($siteLogo) ?>" alt="Cooling - Phụ tùng ô tô chính hãng" style="max-width:180px; max-height:76px; object-fit:contain" width="180" height="76">
      <?php else: ?>
        <svg class="logo-svg" width="180" height="76" viewBox="0 0 480 200" aria-label="Cooling logo" role="img"><use href="#cooling-logo"/></svg>
      <?php endif; ?>
    </a>
    <form class="search" method="get" action="/products" onsubmit="if(!this.q.value.trim()){coolToastShow('Vui lòng nhập từ khóa tìm kiếm (SKU, mã OEM, tên phụ tùng...)','🔍');return false;}">
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm theo SKU, mã OEM, tên phụ tùng...">
      <button class="submit" type="submit" aria-label="Tìm kiếm">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </form>
    <div class="hotline">
      <div class="pulse">T</div>
      <div class="info"><div class="lbl">Tư vấn miễn phí 24/7</div><div class="num"><?= htmlspecialchars($sitePhone) ?></div></div>
    </div>
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
          fetch('/customer/notifications/'+id+'/read', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+document.querySelector('input[name="_csrf"]')?.value||''});
        }
        function markAllUserNotiRead() {
          fetch('/customer/notifications/read-all', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+(document.querySelector('input[name="_csrf"]')?.value||'')})
          .then(function(){
            // Update UI without reload: remove badge, mark all as read visually
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
        function deleteUserNoti(id, e, btn) {
          e.preventDefault(); e.stopPropagation();
          fetch('/customer/notifications/'+id+'/delete', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_csrf='+(document.querySelector('input[name="_csrf"]')?.value||'')})
          .then(function(){ if(btn&&btn.parentNode){btn.parentNode.remove();} var L=document.getElementById('userNotiList'); if(L&&!L.querySelector('a')){L.innerHTML='<div style="padding:24px;text-align:center;font-size:13px;color:#888">Chưa có thông báo nào</div>';} });
        }
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
    </div>
    <div class="mobile-right-actions" style="display:flex;align-items:center;gap:8px;margin-left:auto;">
      <?php if ($user && in_array($user['role'], ['customer','staff'])): ?>
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
         <?php if ($cart['cnt'] > 0): ?><span style="position:absolute;top:0px;right:0px;background:#c8962b;color:#fff;font-size:9px;font-weight:bold;border-radius:10px;min-width:15px;height:15px;line-height:15px;text-align:center;padding:0 3px;" aria-hidden="true"><?= $cart['cnt'] ?></span><?php endif; ?>
      </a>
      <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Mở menu"><span></span><span></span><span></span></button>
    </div>
  </div>
  <div class="mobile-search-bar">
    <form method="get" action="/products" style="width:100%" onsubmit="if(!this.q.value.trim()){coolToastShow('Vui lòng nhập từ khóa tìm kiếm','🔍');return false;}">
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm phụ tùng, SKU, mã OEM...">
      <button type="submit" aria-label="Tìm kiếm" style="display:inline-flex;align-items:center;justify-content:center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
    </form>
  </div>
</header>
