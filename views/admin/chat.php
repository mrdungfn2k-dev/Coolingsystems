<?php require __DIR__.'/../partials/dashboard-head.php'; ?>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
  <h1>Tin nhắn</h1>
</div>

<?php
$adminUser = currentUser();
$threads = dbAll("SELECT t.*, u.full_name, u.avatar, u.email,
    (SELECT COUNT(*) FROM chat_messages WHERE thread_id=t.id AND sender_role='customer' AND status='sent') AS unread_count
    FROM chat_threads t
    LEFT JOIN users u ON u.id=t.customer_id
    ORDER BY t.last_message_at DESC");
$activeThread = null;
$messages = [];
if (!empty($_GET['thread'])) {
    $activeThread = dbGet("SELECT t.*, u.full_name, u.avatar, u.email FROM chat_threads t LEFT JOIN users u ON u.id=t.customer_id WHERE t.id=?", [intval($_GET['thread'])]);
    if ($activeThread) {
        $messages = dbAll("SELECT m.*, u.full_name AS sender_name, u.avatar AS sender_avatar FROM chat_messages m LEFT JOIN users u ON u.id=m.sender_user_id WHERE m.thread_id=? ORDER BY m.created_at ASC", [$activeThread['id']]);
        dbRun("UPDATE chat_messages SET status='read' WHERE thread_id=? AND sender_role='customer' AND status='sent'", [$activeThread['id']]);
        try { dbRun("UPDATE chat_threads SET partner_unread=0 WHERE id=?", [$activeThread['id']]); } catch (Throwable $e) {}
    }
}
?>
<style>
.chat-container { display:flex; height:calc(100vh - 120px); background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
.chat-sidebar { max-height:calc(100vh - 120px); overflow-y:auto;  width:320px; border-right:1px solid #eaeaea; display:flex; flex-direction:column;  overflow-y:auto; }
.chat-sidebar-header { padding:16px; border-bottom:1px solid #eaeaea; font-weight:700; font-size:15px; color:var(--navy); display:flex; align-items:center; gap:8px; }
.chat-search { padding:8px 12px; border-bottom:1px solid #eaeaea; }
.chat-search input { width:100%; border:1px solid #ddd; border-radius:6px; padding:8px 12px; font-size:13px; }
.chat-list { flex:1; overflow-y:auto; }
.chat-item { display:flex; align-items:center; gap:10px; padding:12px 16px; cursor:pointer; border-bottom:1px solid #f5f5f5; transition:background 0.15s; }
.chat-item:hover, .chat-item.active { background:#f0f4ff; }
.chat-item .avatar { width:40px; height:40px; border-radius:50%; background:#ddd; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; color:#fff; flex-shrink:0; overflow:hidden; }
.chat-item .avatar img { width:100%; height:100%; object-fit:cover; }
.chat-item .info { flex:1; min-width:0; }
.chat-item .info .name { font-weight:600; font-size:13px; color:#222; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chat-item .info .last-msg { font-size:12px; color:#888; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chat-item .meta { text-align:right; flex-shrink:0; }
.chat-item .meta .time { font-size:10px; color:#aaa; }
.chat-item .meta .badge { background:#dc2626; color:#fff; font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px; margin-top:4px; display:inline-block; }

.chat-main { flex:1; display:flex; flex-direction:column; }
.chat-main-header { padding:14px 20px; border-bottom:1px solid #eaeaea; display:flex; align-items:center; gap:12px; background:#fafbfc; }
.chat-main-header .name { font-weight:700; font-size:14px; color:#222; }
.chat-main-header .email { font-size:12px; color:#888; }
.chat-messages { flex:1; overflow-y:auto; max-height:calc(100vh - 220px); padding:20px; display:flex; flex-direction:column; gap:12px; background:#f8f9fc; }
.msg-row { display:flex; gap:8px; max-width:70%; flex-direction:column; }
.msg-row.mine { margin-left:auto; flex-direction:column; align-items:flex-end; }
.msg-bubble { padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; word-break:break-word; overflow:hidden; max-width:100%; }
.msg-row:not(.mine) .msg-bubble { background:#fff; border:1px solid #eee; color:#333; border-bottom-left-radius:4px; align-self:flex-start; }
.msg-row.mine .msg-bubble { background:var(--navy); color:#fff; border-bottom-right-radius:4px; align-self:flex-end; }
.msg-time { font-size:10px; color:#aaa; margin-top:4px; }
.msg-row.mine .msg-time { text-align:right; align-self:flex-end; }
.msg-img { max-width:200px; max-height:200px; width:100%; object-fit:cover; border-radius:8px; cursor:pointer; display:block; }

.chat-input-bar { padding:12px 16px; border-top:1px solid #eaeaea; display:flex; gap:8px; align-items:flex-end; background:#fff; }
.chat-input-bar textarea { flex:1; border:1px solid #ddd; border-radius:8px; padding:10px 12px; font-size:13px; resize:none; min-height:40px; max-height:100px; font-family:inherit; }
.chat-input-bar .btn-send { background:var(--navy); color:#fff; border:none; border-radius:8px; padding:10px 16px; cursor:pointer; font-weight:700; font-size:13px; }
.chat-input-bar .btn-send:hover { opacity:0.9; }
.chat-input-bar .btn-img { background:none; border:1px solid #ddd; border-radius:8px; padding:8px 10px; cursor:pointer; color:#666; }
.chat-empty { flex:1; display:flex; align-items:center; justify-content:center; color:#aaa; font-size:14px; flex-direction:column; gap:8px; }

/* Hidden conversations */
.chat-hidden { display:none; }
.chat-searching .chat-hidden { display:block !important; opacity:0.7; }
.chat-hidden .chat-item { opacity:0.5; }
.chat-hidden .chat-item::after { content:'(Đã ẩn)'; font-size:10px; color:#999; position:absolute; right:40px; bottom:8px; }
.show-hidden .chat-hidden { display:block !important; opacity:0.7; }
.toggle-hidden-link { display:block; padding:8px 14px; font-size:12px; color:var(--navy); cursor:pointer; text-align:center; border-top:1px solid #eee; font-weight:600; }
.toggle-hidden-link:hover { background:#f0f4ff; }
/* Chat context menu */
.chat-item-wrap { position:relative; }
.chat-item-wrap .chat-menu-btn { position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; color:#999; padding:4px 8px; border-radius:4px; z-index:5; display:none; line-height:1; }
.chat-item-wrap:hover .chat-menu-btn { display:block; }
.chat-item-wrap .chat-menu-btn:hover { background:#e5e7eb; color:#333; }
.chat-context-menu { display:none; position:absolute; right:8px; top:calc(50% + 14px); background:#fff; border:1px solid #e0e0e0; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,0.12); z-index:50; min-width:140px; overflow:hidden; }
.chat-context-menu.show { display:block; }
.chat-context-menu button { width:100%; padding:10px 16px; border:none; background:none; text-align:left; cursor:pointer; font-size:13px; font-weight:500; display:flex; align-items:center; gap:8px; }
.chat-context-menu button:hover { background:#f3f4f6; }
.chat-context-menu button.danger { color:#dc2626; }
.chat-context-menu button.danger:hover { background:#fef2f2; }

</style>

<div class="chat-container">
  <!-- Sidebar -->
  <div class="chat-sidebar">
    <div class="chat-sidebar-header"> Tin nhắn khách hàng</div>
    <div class="chat-search"><input type="text" placeholder="Tìm theo tên..." oninput="filterChats(this.value)"></div>
    <div class="chat-list" id="chatList">
      <?php foreach($threads as $t): ?>
      <div class="chat-item-wrap<?= (!empty($t['is_hidden']) && $t['is_hidden']) ? ' chat-hidden' : '' ?>" data-thread-id="<?=$t['id']?>" data-hidden="<?= (!empty($t['is_hidden']) && $t['is_hidden']) ? '1' : '0' ?>">
      <a href="/admin/chat?thread=<?=$t['id']?>" class="chat-item <?= ($activeThread && $activeThread['id']==$t['id'])?'active':'' ?>" data-name="<?=e(strtolower($t['full_name']??''))?>">
        <div class="avatar" style="background:hsl(<?= crc32($t['full_name']??'')%360 ?>,50%,45%)">
          <?php if(!empty($t['avatar'])): ?><img src="<?=e($t['avatar'])?>">
          <?php else: echo mb_substr($t['full_name']??'?',0,1); endif; ?>
        </div>
        <div class="info">
          <div class="name"><?=e($t['full_name']??'Khách')?></div>
          <div class="last-msg"><?=e(mb_substr($t['last_message']??'',0,40))?></div>
        </div>
        <div class="meta">
          <div class="time"><?= $t['last_message_at'] ? date('H:i', strtotime($t['last_message_at'])) : '' ?></div>
          <?php if(($t['unread_count']??0)>0): ?><div class="badge"><?=$t['unread_count']?></div><?php endif; ?>
        </div>
      </a>
      <button class="chat-menu-btn" onclick="event.preventDefault();event.stopPropagation();toggleChatMenu(<?=$t['id']?>)" title="Tùy chọn">⋯</button>
      <div class="chat-context-menu" id="chatMenu_<?=$t['id']?>">
        <?php if(!empty($t['is_hidden']) && $t['is_hidden']): ?><button onclick="unhideThread(<?=$t['id']?>)">👁 Bỏ ẩn</button><?php else: ?><button onclick="hideThread(<?=$t['id']?>)">👁‍🗨 Ẩn hội thoại</button><?php endif; ?>
        <button class="danger" onclick="deleteThread(<?=$t['id']?>)">🗑 Xóa hội thoại</button>
      </div>
      </div>
      <?php endforeach; ?>
      <?php
    $hiddenCount = 0;
    foreach($threads as $th) { if(!empty($th['is_hidden']) && $th['is_hidden']) $hiddenCount++; }
    if($hiddenCount > 0):
    ?>
    <div class="toggle-hidden-link" id="toggleHiddenBtn" onclick="toggleHiddenChats()">
      Hiện hội thoại đã ẩn (<?= $hiddenCount ?>)
    </div>
    <?php endif; ?>
    <?php if(empty($threads)): ?>
      <div style="text-align:center;padding:30px;color:#aaa;font-size:13px">Chưa có cuộc hội thoại nào</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Main Chat -->
  <div class="chat-main">
    <?php if($activeThread): ?>
    <div class="chat-main-header">
      <div class="avatar" style="width:36px;height:36px;border-radius:50%;background:hsl(<?= crc32($activeThread['full_name']??'')%360 ?>,50%,45%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;overflow:hidden">
        <?php if(!empty($activeThread['avatar'])): ?><img src="<?=e($activeThread['avatar'])?>" style="width:100%;height:100%;object-fit:cover">
        <?php else: echo mb_substr($activeThread['full_name']??'?',0,1); endif; ?>
      </div>
      <div>
        <div class="name"><?=e($activeThread['full_name']??'Khách')?></div>
        <div class="email"><?=e($activeThread['email']??'')?></div>
      </div>
    </div>
    <div class="chat-messages" id="chatMessages">
      <?php foreach($messages as $m): ?>
      <div class="msg-row <?= $m['sender_role']!=='customer'?'mine':'' ?>">
        <div>
          <div class="msg-bubble">
            <?php if(!empty($m['attachment_path']) || !empty($m['image_path'])): ?>
              <?php
              $imgFile = !empty($m['attachment_path']) ? $m['attachment_path'] : ($m['image_path'] ?? '');
              $imgSrc = (strpos($imgFile, '/') === 0 || strpos($imgFile, 'http') === 0)
                ? $imgFile
                : '/uploads/chat/' . $imgFile;
            ?>
              <img src="<?= e($imgSrc) ?>" class="msg-img" onclick="window.open(this.src)" onerror="this.style.display='none'">
            <?php endif; ?>
            <?php if(!empty($m['content'])): ?><?= nl2br(e($m['content'])) ?><?php endif; ?>
          </div>
          <div class="msg-time"><?= date('H:i d/m', strtotime($m['created_at'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    
      <!-- Image preview before sending -->
      <div id="adminChatPreview" style="display:none;padding:8px 16px;border-top:1px solid #eaeaea;background:#f8f9fa">
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:12px;color:#666;font-weight:600">1 ảnh</span>
          <div style="position:relative;display:inline-block">
            <img id="adminPreviewImg" src="" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
            <button type="button" onclick="cancelAdminPreview()" style="position:absolute;top:-6px;right:-6px;background:#fff;border:1px solid #ddd;border-radius:50%;width:22px;height:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;color:#dc2626;font-weight:700;box-shadow:0 1px 3px rgba(0,0,0,0.15)" title="Xoá ảnh">&times;</button>
          </div>
          <span id="adminPreviewName" style="font-size:12px;color:#999;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
        </div>
      </div>
<div class="chat-input-bar">
      <input type="file" id="chatImageInput" accept="image/*" style="display:none" onchange="previewChatImage(this)">
      <button type="button" class="btn-img" onclick="document.getElementById('chatImageInput').click()" title="Gửi ảnh"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="2"/><polyline points="21 15 16 10 5 21"/></svg></button>
      <textarea id="chatInput" placeholder="Nhập tin nhắn..." maxlength="3000" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg()}"></textarea>
      <button type="button" class="btn-send" onclick="sendMsg()">Gửi ▶</button>
    </div>
    <?php else: ?>
    <div class="chat-empty">
      <div style="font-size:48px;opacity:0.3"></div>
      <div>Chọn một cuộc hội thoại để xem tin nhắn</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if($activeThread): ?>
<script>
var threadId = <?= $activeThread['id'] ?>;
var lastMsgId = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;
var csrf = '<?= $_SESSION['csrf_token'] ?? '' ?>';

// Auto-scroll to bottom
var chatBox = document.getElementById('chatMessages');
chatBox.scrollTop = chatBox.scrollHeight;

function sendMsg() {
  var input = document.getElementById('chatInput');
  var content = input.value.trim();
  if (!content && !pendingChatFile) return;
  input.value = '';

  // Send text message if any
  if (content) {
    appendMsg(content, 'mine', new Date());
    fetch('/admin/chat/send', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf=' + encodeURIComponent(csrf) + '&thread_id=' + threadId + '&content=' + encodeURIComponent(content)
    }).then(r => r.json()).then(d => {
      if (d.id) lastMsgId = d.id;
    });
  }

  // Send pending image if any
  if (pendingChatFile) {
    uploadChatImage(null);
  }
}

function appendMsg(content, cls, date, imgPath) {
  var box = document.getElementById('chatMessages');
  var div = document.createElement('div');
  div.className = 'msg-row ' + cls;
  var timeStr = date instanceof Date ? date.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'}) : date;
  var imgHtml = '';
  if(imgPath){
    var fullPath = (imgPath.indexOf('/') === 0 || imgPath.indexOf('http') === 0) ? imgPath : '/uploads/chat/' + imgPath;
    imgHtml = '<img src="' + fullPath + '" class="msg-img" onclick="window.open(this.src)" onerror="this.style.display=\'none\'">';
  }
  div.innerHTML = '<div><div class="msg-bubble">' + imgHtml + (content ? content.replace(/\n/g,'<br>') : '') + '</div><div class="msg-time">' + timeStr + '</div></div>';
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
}

// Store pending file for preview
var pendingChatFile = null;

function previewChatImage(input) {
  if (!input.files[0]) return;
  pendingChatFile = input.files[0];
  var preview = document.getElementById('adminChatPreview');
  var img = document.getElementById('adminPreviewImg');
  var nameEl = document.getElementById('adminPreviewName');
  if (!preview || !img) return;
  
  nameEl.textContent = pendingChatFile.name;
  
  if (pendingChatFile.type && pendingChatFile.type.startsWith('image/')) {
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(pendingChatFile);
  } else {
    img.src = '';
    preview.style.display = 'block';
  }
}

function cancelAdminPreview() {
  pendingChatFile = null;
  var preview = document.getElementById('adminChatPreview');
  if (preview) preview.style.display = 'none';
  document.getElementById('chatImageInput').value = '';
}

function uploadChatImage(input) {
  // Called when user clicks send with pending file
  var file = pendingChatFile || (input && input.files && input.files[0]);
  if (!file) return;
  var fd = new FormData();
  fd.append('_csrf', csrf);
  fd.append('thread_id', threadId);
  fd.append('image', file);
  fetch('/admin/chat/send-image', {method:'POST', body:fd})
    .then(r => r.json())
    .then(d => {
      if (d.ok) { appendMsg('', 'mine', new Date(), d.path); lastMsgId = d.id || lastMsgId; }
      else { console.error('Image upload failed:', d); }
      cancelAdminPreview();
    })
    .catch(e => { console.error('Image upload error:', e); cancelAdminPreview(); });
}

// Poll for new messages every 3 seconds
setInterval(function() {
  fetch('/admin/chat/poll?thread_id=' + threadId + '&after_id=' + lastMsgId)
    .then(r => r.json())
    .then(msgs => {
      if (msgs && msgs.length) {
        msgs.forEach(function(m) {
          if (m.sender_role === 'customer') {
            appendMsg(m.content || '', '', m.time || '', m.attachment_path || '');
          }
          lastMsgId = Math.max(lastMsgId, m.id);
        });
      }
    }).catch(()=>{});
}, 3000);

</script>
<?php endif; ?>

<script>

function removeDiacritics(str) {
  if (!str) return '';
  return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase();
}

function filterChats(q) {
  q = removeDiacritics(q);
  var chatList = document.getElementById('chatList');
  if (q) {
    chatList.classList.add('chat-searching');
    document.querySelectorAll('.chat-item-wrap').forEach(function(wrap) {
      var nameEl = wrap.querySelector('.chat-item .info .name');
      var rawName = nameEl ? nameEl.textContent : '';
      var name = removeDiacritics(rawName);
      // Also check data-name attribute as fallback
      var dataName = wrap.querySelector('.chat-item') ? removeDiacritics(wrap.querySelector('.chat-item').dataset.name || '') : '';
      if (name.includes(q) || dataName.includes(q)) {
        wrap.setAttribute('style', 'display:block !important');
      } else {
        wrap.setAttribute('style', 'display:none !important');
      }
    });
  } else {
    chatList.classList.remove('chat-searching');
    document.querySelectorAll('.chat-item-wrap').forEach(function(wrap) {
      wrap.removeAttribute('style');
    });
  }
}

function toggleHiddenChats() {
  var chatList = document.getElementById('chatList');
  var btn = document.getElementById('toggleHiddenBtn');
  if (chatList.classList.contains('show-hidden')) {
    chatList.classList.remove('show-hidden');
    if(btn) btn.textContent = btn.textContent.replace('Ẩn bớt', 'Hiện hội thoại đã ẩn');
  } else {
    chatList.classList.add('show-hidden');
    if(btn) btn.textContent = btn.textContent.replace('Hiện hội thoại đã ẩn', 'Ẩn bớt');
  }
}

function unhideThread(threadId) {
  var csrf = document.querySelector('#globalCsrf') ? document.querySelector('#globalCsrf').value : '<?= $_SESSION["csrf_token"] ?? "" ?>';
  fetch('/admin/chat/' + threadId + '/unhide', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf)
  }).then(function(r){ return r.json(); }).then(function(d) {
    if (d.ok) {
      var wrap = document.querySelector('.chat-item-wrap[data-thread-id="' + threadId + '"]');
      if (wrap) {
        wrap.classList.remove('chat-hidden');
        wrap.dataset.hidden = '0';
        // Update menu button
        var menuEl = wrap.querySelector('.chat-context-menu');
        if (menuEl) {
          var hideBtn = menuEl.querySelector('button:first-child');
          if (hideBtn) {
            hideBtn.textContent = '👁‍🗨 Ẩn hội thoại';
            hideBtn.setAttribute('onclick', 'hideThread(' + threadId + ')');
          }
        }
      }
      // Close menu
      document.querySelectorAll('.chat-context-menu.show').forEach(function(m) { m.classList.remove('show'); });
    }
  });
}

// Chat context menu functions
var currentOpenMenu = null;

function toggleChatMenu(threadId) {
  var menu = document.getElementById('chatMenu_' + threadId);
  if (!menu) return;
  // Close any open menu first
  if (currentOpenMenu && currentOpenMenu !== menu) {
    currentOpenMenu.classList.remove('show');
  }
  menu.classList.toggle('show');
  currentOpenMenu = menu.classList.contains('show') ? menu : null;
}

// Close menu on outside click
document.addEventListener('click', function(e) {
  if (!e.target.closest('.chat-menu-btn') && !e.target.closest('.chat-context-menu')) {
    document.querySelectorAll('.chat-context-menu.show').forEach(function(m) { m.classList.remove('show'); });
    currentOpenMenu = null;
  }
});

function hideThread(threadId) {
  var csrf = document.querySelector('#globalCsrf') ? document.querySelector('#globalCsrf').value : '<?= $_SESSION["csrf_token"] ?? "" ?>';
  fetch('/admin/chat/' + threadId + '/hide', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf)
  }).then(function(r){ return r.json(); }).then(function(d) {
    if (d.ok) {
      // Hide the item from UI
      var wrap = document.querySelector('.chat-item-wrap[data-thread-id="' + threadId + '"]');
      if (wrap) {
        wrap.style.transition = 'opacity 0.3s, max-height 0.3s';
        wrap.style.opacity = '0';
        wrap.style.maxHeight = '0';
        wrap.style.overflow = 'hidden';
        setTimeout(function(){ wrap.remove(); }, 300);
      }
      // If this was the active thread, redirect
      if (window.location.search.includes('thread=' + threadId)) {
        window.location.href = '/admin/chat';
      }
    }
  });
}

function deleteThread(threadId) {
  if (!confirm('Bạn có chắc muốn xóa cuộc hội thoại này? Toàn bộ tin nhắn sẽ bị xóa vĩnh viễn.')) return;
  var csrf = document.querySelector('#globalCsrf') ? document.querySelector('#globalCsrf').value : '<?= $_SESSION["csrf_token"] ?? "" ?>';
  fetch('/admin/chat/' + threadId + '/delete', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf)
  }).then(function(r){ return r.json(); }).then(function(d) {
    if (d.ok) {
      var wrap = document.querySelector('.chat-item-wrap[data-thread-id="' + threadId + '"]');
      if (wrap) {
        wrap.style.transition = 'opacity 0.3s, max-height 0.3s';
        wrap.style.opacity = '0';
        wrap.style.maxHeight = '0';
        wrap.style.overflow = 'hidden';
        setTimeout(function(){ wrap.remove(); }, 300);
      }
      if (window.location.search.includes('thread=' + threadId)) {
        window.location.href = '/admin/chat';
      }
    }
  });
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
