<?php
$currentUser = $user ?? currentUser();
$staffRoleAssignment = dbGet("SELECT sr.name AS role_name, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=?", [$currentUser['id']]);
$staffPerms = json_decode($staffRoleAssignment['permissions'] ?? '[]', true) ?: [];

// Notifications for staff
$staffNotifs = dbAll("SELECT * FROM user_notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 15", [$currentUser['id']]);
$unreadNotifCount = dbGet("SELECT COUNT(*) as n FROM user_notifications WHERE user_id=? AND is_read=0", [$currentUser['id']])['n'] ?? 0;

// Count unread messages across all threads for bell
$totalUnreadMsgs = 0;
foreach ($threads as &$t) {
    $uc = dbGet("SELECT COUNT(*) as c FROM chat_messages WHERE thread_id=? AND sender_role='customer' AND status='sent'", [$t['id']]);
    $t['unread_count'] = $uc['c'] ?? 0;
    $totalUnreadMsgs += $t['unread_count'];
}
unset($t);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Tin nhắn') ?> — Cooling System</title>
<link rel="stylesheet" href="/css/cooling.css">
<style>
*{box-sizing:border-box}
.staff-wrap{display:grid;grid-template-columns:220px 1fr;min-height:100vh}
.staff-side{background:var(--navy,#1a3258);color:#fff}
.staff-side .who{padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.1)}
.staff-side .who .name{font-weight:700;font-size:14px}
.staff-side .who .role{font-size:11px;color:var(--gold-warm,#d4a84b);margin-top:2px}
.staff-side nav a{display:block;padding:10px 16px;color:rgba(255,255,255,.7);text-decoration:none;font-size:13px;border-left:3px solid transparent;transition:.2s}
.staff-side nav a:hover,.staff-side nav a.active{color:#fff;background:rgba(255,255,255,.07);border-left-color:var(--gold-warm,#d4a84b)}
.staff-main{background:#f4f6fa;display:flex;flex-direction:column}
.staff-topbar{display:flex;justify-content:flex-end;align-items:center;padding:10px 20px;background:#fff;border-bottom:1px solid #e8e8e8;gap:12px}
.noti-btn{background:none;border:none;cursor:pointer;position:relative;padding:6px}
.noti-btn svg{width:22px;height:22px;fill:var(--navy,#1a3258)}
.noti-badge{position:absolute;top:0;right:0;background:#e74c3c;color:#fff;font-size:9px;font-weight:700;border-radius:8px;padding:1px 5px;min-width:16px;text-align:center}
.noti-drop{display:none;position:absolute;right:0;top:36px;width:300px;background:#fff;border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:200;max-height:360px;overflow-y:auto}
.noti-drop.show{display:block}
.noti-drop .ni{display:block;padding:10px 14px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#333;font-size:12px;transition:.15s}
.noti-drop .ni:hover{background:#f8f9fc}
.noti-drop .ni.unread{background:#fef9e7;font-weight:600}
.noti-drop .ni .nt{font-weight:700;font-size:12px;color:var(--navy,#1a3258)}
.noti-drop .ni .nm{color:#666;margin-top:2px}
.noti-drop .ni .nd{font-size:10px;color:#aaa;margin-top:3px}
.chat-box{display:grid;grid-template-columns:280px 1fr;flex:1;margin:16px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.tlist{border-right:1px solid #e8e8e8;overflow-y:auto;display:flex;flex-direction:column}
.tlist h3{padding:14px 16px;margin:0;font-size:14px;color:var(--navy,#1a3258);border-bottom:1px solid #e8e8e8;background:#fafbfc}
.titem{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:inherit;transition:.15s;position:relative}
.titem:hover{background:#f8f9fc}
.titem.active{background:#e8f0fe;border-left:3px solid var(--gold-warm,#d4a84b)}
.titem.pinned{background:#f0f7ff;border-bottom:2px solid #bee3f8}
.titem .av{width:36px;height:36px;border-radius:50%;background:var(--navy,#1a3258);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0}
.titem .av.admin-av{background:var(--gold-warm,#d4a84b);color:#1a3258}
.titem .tinfo{flex:1;min-width:0}
.titem .tname{font-weight:600;font-size:13px;color:#222;display:flex;align-items:center;gap:6px}
.titem .tprev{font-size:11px;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.titem .ttime{font-size:10px;color:#aaa;flex-shrink:0}
.ubadge{background:#e74c3c;color:#fff;font-size:9px;font-weight:700;border-radius:8px;padding:1px 5px;min-width:14px;text-align:center;display:inline-block}
.chat-area{display:flex;flex-direction:column}
.chat-hdr{padding:14px 18px;border-bottom:1px solid #e8e8e8;font-weight:700;font-size:14px;color:var(--navy,#1a3258);background:#fafbfc}
.msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:6px}
.msg{max-width:70%;padding:10px 14px;border-radius:12px;font-size:13px;line-height:1.5}
.msg.customer{align-self:flex-start;background:#f0f0f0;color:#333;border-bottom-left-radius:4px}
.msg.admin,.msg.staff{align-self:flex-end;background:var(--navy,#1a3258);color:#fff;border-bottom-right-radius:4px}
.msg .mt{font-size:9px;color:#aaa;margin-top:3px}
.msg.admin .mt,.msg.staff .mt{color:rgba(255,255,255,.5)}
.cinput{display:flex;padding:10px 14px;border-top:1px solid #e8e8e8;gap:8px;background:#fff}
.cinput input{flex:1;padding:9px 12px;border:1px solid #ddd;border-radius:8px;font-size:13px;outline:none}
.cinput input:focus{border-color:var(--gold-warm,#d4a84b)}
.cinput button{padding:9px 18px;background:var(--navy,#1a3258);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer}
.cinput button:hover{opacity:.9}
.empty-chat{display:flex;align-items:center;justify-content:center;flex:1;color:#aaa;font-size:14px}
</style>
</head>
<body>
<div class="staff-wrap">
  <aside class="staff-side">
    <div class="who">
      <div class="name"><?= e($currentUser['full_name']) ?></div>
      <div class="role"><?= e($staffRoleAssignment['role_name'] ?? 'Nhân viên') ?></div>
    </div>
    <nav class="staff-side-nav">
      <?php if(in_array('orders',$staffPerms) || in_array('create_order',$staffPerms)): ?>
        <a href="/staff/orders">Đơn hàng</a>
      <?php endif; ?>
      <?php if(in_array('create_order',$staffPerms)): ?>
        <a href="/staff/orders/create">Tạo đơn hộ</a>
      <?php endif; ?>
      <?php if(in_array('products',$staffPerms)): ?>
        <a href="/admin/products">Sản phẩm</a>
      <?php endif; ?>
      <?php if(in_array('reviews',$staffPerms)): ?>
        <a href="/admin/reviews">Đánh giá</a>
      <?php endif; ?>
      <?php if(in_array('users',$staffPerms)): ?>
        <a href="/admin/users">Người dùng</a>
      <?php endif; ?>
      <?php if(in_array('vouchers',$staffPerms)): ?>
        <a href="/admin/vouchers">Voucher</a>
      <?php endif; ?>
      <?php if(in_array('content',$staffPerms)): ?>
        <a href="/admin/news">Nội dung</a>
      <?php endif; ?>
      <?php if(in_array('reports',$staffPerms)): ?>
        <a href="/admin">Báo cáo</a>
      <?php endif; ?>
      <a href="/chat" class="active">Tin nhắn</a>
      <a href="/auth/logout" style="margin-top:20px;border-top:1px solid rgba(255,255,255,.1);padding-top:12px">Đăng xuất</a>
    </nav>
  </aside>
  <div class="staff-main">
    <!-- Top bar with notification bell -->
    <div class="staff-topbar">
      <div style="position:relative">
        <button class="noti-btn" onclick="toggleNoti()">
          <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
          <?php if($unreadNotifCount > 0): ?>
            <span class="noti-badge"><?= $unreadNotifCount ?></span>
          <?php endif; ?>
        </button>
        <div class="noti-drop" id="notiDrop">
          <?php if(empty($staffNotifs)): ?>
            <div style="padding:20px;text-align:center;color:#999;font-size:12px">Chưa có thông báo</div>
          <?php endif; ?>
          <?php foreach($staffNotifs as $n): ?>
            <a href="<?= e($n['link'] ?? '#') ?>" class="ni <?= $n['is_read']?'':'unread' ?>">
              <div class="nt"><?= e($n['title']) ?></div>
              <div class="nm"><?= e($n['message']) ?></div>
              <div class="nd"><?= date('H:i d/m', strtotime($n['created_at'])) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- Chat layout -->
    <div class="chat-box">
      <div class="tlist">
        <h3>Cuộc hội thoại</h3>
        <?php if(empty($threads)): ?>
          <div style="padding:20px;text-align:center;font-size:12px;color:#999">Chưa có cuộc hội thoại</div>
        <?php endif; ?>
        <?php foreach($threads as $t): ?>
          <a href="/chat?thread=<?= $t['id'] ?>" class="titem <?= ($active && $active['id']==$t['id'])?'active':'' ?>">
            <div class="av"><?= mb_strtoupper(mb_substr($t['title'] ?? $t['full_name'] ?? 'K', 0, 1)) ?></div>
            <div class="tinfo">
              <div class="tname">
                <?= e($t['title'] ?? $t['full_name'] ?? 'Khách hàng') ?>
                <?php if(($t['unread_count'] ?? 0) > 0): ?>
                  <span class="ubadge"><?= $t['unread_count'] ?></span>
                <?php endif; ?>
              </div>
              <div class="tprev"><?= e($t['last_message'] ?? '') ?></div>
            </div>
            <div class="ttime"><?= date('H:i d/m', strtotime($t['last_message_at'] ?? $t['created_at'] ?? 'now')) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="chat-area">
        <?php if($active): ?>
          <div class="chat-hdr">Chat với <?= e($active['title'] ?? $active['full_name'] ?? 'Khách hàng') ?></div>
          <div class="msgs" id="chatMsgs">
            <?php foreach($messages as $m): ?>
              <div class="msg <?= e($m['sender_role']) ?>">
                <?php if(($m['attachment_path'] ?? null) && ($m['content'] === '[Ảnh]' || strpos($m['content'],'[Ảnh]')!==false)): ?>
                  <img src="/uploads/chat/<?= e($m['attachment_path']) ?>" style="max-width:200px;border-radius:8px">
                <?php else: ?>
                  <?= e($m['content']) ?>
                <?php endif; ?>
                <div class="mt"><?= date('H:i d/m', strtotime($m['created_at'])) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <form class="cinput" onsubmit="sendMsg(event)" enctype="multipart/form-data">
            <?= csrfField() ?>
            <!-- Attachment preview -->
            <div id="attachPreview" style="display:none;align-items:center;gap:10px;margin-bottom:8px;padding:8px;background:#f8f9fa;border-radius:6px;font-size:12px;border:1px solid #eaeaea">
              <img id="attachImg" src="" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px" loading="lazy">
              <span id="attachName" style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"></span>
              <button type="button" onclick="cancelAttach()" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:18px;font-weight:700">&times;</button>
            </div>
            <div style="display:flex;align-items:center;gap:8px;width:100%">
              <label for="staffAttachFile" style="cursor:pointer;color:#888;flex-shrink:0" title="Gửi ảnh">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="2"/><polyline points="21 15 16 10 5 21"/></svg>
              </label>
              <input type="file" name="attachment" id="staffAttachFile" accept="image/*,.pdf" style="display:none" onchange="previewAttach(this)">
              <input type="text" id="msgIn" placeholder="Nhập tin nhắn..." autocomplete="off" style="flex:1">
            <button type="submit">Gửi</button>
            </div>
          </form>
        <?php else: ?>
          <div class="empty-chat">Chọn cuộc hội thoại để xem tin nhắn</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
function toggleNoti(){
  var d=document.getElementById('notiDrop');
  d.classList.toggle('show');
  if(d.classList.contains('show')){
    fetch('/staff/notifications/read',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'_csrf=<?= csrfToken() ?>'});
    var b=document.querySelector('.noti-badge');if(b)b.remove();
  }
}
document.addEventListener('click',function(e){if(!e.target.closest('.noti-btn')&&!e.target.closest('.noti-drop'))document.getElementById('notiDrop').classList.remove('show')});

<?php if($active): ?>
function sendMsg(e){
  e.preventDefault();
  var inp=document.getElementById('msgIn'),msg=inp.value.trim();
  if(!msg)return;
  var csrf=document.querySelector('input[name="_csrf"]').value;
  fetch('/chat/send',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'_csrf='+encodeURIComponent(csrf)+'&thread_id=<?=$active['id']?>&content='+encodeURIComponent(msg)
  }).then(r=>r.json()).then(d=>{
    if(d.ok||d.id){
      var box=document.getElementById('chatMsgs'),div=document.createElement('div');
      div.className='msg admin';
      div.innerHTML=msg+'<div class="mt">'+new Date().toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'})+' '+new Date().toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'})+'</div>';
      box.appendChild(div);box.scrollTop=box.scrollHeight;inp.value='';
    }
  }).catch(()=>{ window.location.href='/chat?thread=<?=$active['id']?>'; });
}
var chatBox=document.getElementById('chatMsgs');
if(chatBox)chatBox.scrollTop=chatBox.scrollHeight;
var lastId=<?= !empty($messages)?end($messages)['id']:0 ?>;
setInterval(()=>{
  fetch('/chat/poll?thread=<?=$active['id']?>&after='+lastId).then(r=>r.json()).then(msgs=>{
    if(msgs.length){
      var box=document.getElementById('chatMsgs');
      msgs.forEach(m=>{
        if(m.id>lastId)lastId=m.id;
        var div=document.createElement('div');
        div.className='msg '+m.sender_role;
        div.innerHTML=(m.attachment_path?'<img src="/uploads/chat/'+m.attachment_path+'" style="max-width:200px;border-radius:8px">':m.content)+'<div class="mt">'+new Date(m.created_at).toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'})+'</div>';
        box.appendChild(div);
      });
      box.scrollTop=box.scrollHeight;
    }
  }).catch(()=>{});
},4000);
<?php endif; ?>
</script>

<script>
function previewAttach(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  var preview = document.getElementById('attachPreview');
  var img = document.getElementById('attachImg');
  var nameEl = document.getElementById('attachName');
  if (!preview) return;
  nameEl.textContent = file.name;
  if (file.type && file.type.startsWith('image/')) {
    var reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; img.style.display='block'; preview.style.display='flex'; };
    reader.readAsDataURL(file);
  } else {
    img.style.display = 'none';
    preview.style.display = 'flex';
  }
}
function cancelAttach() {
  document.getElementById('attachPreview').style.display = 'none';
  document.getElementById('attachImg').src = '';
  var fileInput = document.getElementById('staffAttachFile');
  if (fileInput) fileInput.value = '';
}
</script>
</body>
</html>
