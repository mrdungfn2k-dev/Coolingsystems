<?php require __DIR__.'/../partials/head.php'; ?>
<?php if(empty($user)) { $user = currentUser(); } if(empty($user)) { header('Location: /auth/login'); exit; } ?>
<?php
$thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
$messages = [];
if ($thread) {
    $messages = dbAll("SELECT * FROM chat_messages WHERE thread_id=? ORDER BY created_at ASC", [$thread['id']]);
    // Mark as read
    dbRun("UPDATE chat_messages SET status='read' WHERE thread_id=? AND sender_role!='customer' AND status!='read'", [$thread['id']]);
}
?>
<style>
.chat-card{overflow:hidden;box-sizing:border-box;max-width:100%;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 6px 24px rgba(15,35,66,.07)}
.chat-card .sec-head{padding-bottom:0;border:none}
.chat-topbar{display:flex;align-items:center;gap:12px;padding:14px 18px;background:linear-gradient(135deg,#1a3258,#2c4a73);color:#fff}
.chat-topbar .av{width:42px;height:42px;border-radius:50%;background:#fff;color:#1a3258;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;letter-spacing:.5px}
.chat-topbar .nm{font-weight:700;font-size:15px;line-height:1.2}
.chat-topbar .st{font-size:12px;opacity:.85;display:flex;align-items:center;gap:6px;margin-top:3px}
.chat-topbar .st .dot{width:8px;height:8px;border-radius:50%;background:#4ade80;display:inline-block;box-shadow:0 0 0 3px rgba(74,222,128,.25)}
.chat-body{height:520px;overflow-y:auto;padding:18px;background:#f4f7fb}
.chat-body img{max-width:100% !important}
.chat-empty{text-align:center;padding:46px 20px}
.chat-empty .ic{width:74px;height:74px;border-radius:50%;background:#e8eef7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.chat-empty .t{font-size:15px;font-weight:700;color:#1a3258;margin:0 0 5px}
.chat-empty .s{font-size:13px;color:#8b97a8;margin:0;line-height:1.5}
.chat-form-wrap{display:flex;gap:8px;padding:12px;background:#fff;border-top:1px solid #eef2f7;box-sizing:border-box;max-width:100%;align-items:center}
.chat-form-wrap input[type="text"]{flex:1;min-width:0;padding:11px 16px;border:1px solid #d6deea;border-radius:24px;font-size:14px;box-sizing:border-box;font-family:inherit}
.chat-form-wrap input[type="text"]:focus{border-color:#1a3258;outline:none;box-shadow:0 0 0 3px rgba(26,50,88,.1)}
.chat-form-wrap .chat-img-btn{background:#f4f7fb;border:1px solid #d6deea;border-radius:50%;width:42px;height:42px;cursor:pointer;color:#1a3258;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px;padding:0}
.chat-form-wrap .chat-send-btn{background:#1a3258;color:#fff;border:none;padding:0 22px;height:42px;border-radius:24px;font-weight:700;cursor:pointer;flex-shrink:0;white-space:nowrap;font-size:14px;transition:background .15s}
.chat-form-wrap .chat-send-btn:hover{background:#0f2342}
@media (max-width:480px){.chat-form-wrap{gap:6px;padding:10px}.chat-form-wrap .chat-send-btn{padding:0 16px}.chat-topbar{padding:12px 14px}}
</style>
<section class="block"><div class="wrap" style="max-width:1280px;box-sizing:border-box;overflow:hidden;margin:24px auto">
  <div class="sec-card chat-card">
    <div class="sec-head"><div class="title"><span class="bar"></span><h2>Tin nhắn với Admin</h2></div></div>
    <div class="chat-topbar">
      <div class="av">CS</div>
      <div><div class="nm">Hỗ trợ CoolingSystem</div><div class="st"><span class="dot"></span> Thường phản hồi trong vài phút</div></div>
    </div>
    <div id="chatBox" class="chat-body">
      <?php if (empty($messages)): ?>
        <div class="chat-empty">
          <div class="ic"><svg width="34" height="34" fill="none" stroke="#1a3258" stroke-width="1.6" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg></div>
          <p class="t">Chưa có tin nhắn</p>
          <p class="s">Hãy gửi tin nhắn đầu tiên, chúng tôi sẽ phản hồi sớm nhất!</p>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $m):
          $isMe = ($m['sender_role'] === 'customer');
        ?>
        <div style="display:flex;justify-content:<?= $isMe ? 'flex-end' : 'flex-start' ?>;margin-bottom:12px">
          <div style="max-width:620px;word-break:break-word;overflow-wrap:break-word;padding:10px 14px;border-radius:<?= $isMe ? '16px 16px 4px 16px' : '16px 16px 16px 4px' ?>;background:<?= $isMe ? '#1a3258' : '#fff' ?>;color:<?= $isMe ? '#fff' : '#1f2937' ?>;box-shadow:0 1px 4px rgba(15,35,66,.08);font-size:14px;line-height:1.5">
            <?php if (!empty($m['content'])): ?><?= nl2br(e($m['content'])) ?><?php endif; ?>
            <?php if (!empty($m['image_path'])): ?><img src="/uploads/chat/<?= e($m['image_path']) ?>" style="max-width:200px;border-radius:8px;margin-top:6px" onerror="this.style.display='none'"><?php endif; ?>
            <div style="font-size:10px;opacity:0.6;margin-top:4px;text-align:right"><?= date('H:i d/m', strtotime($m['created_at'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div id="custChatPreview" style="display:none;padding:8px 12px;background:#f4f7fb;border-top:1px solid #eef2f7">
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:12px;color:#666;font-weight:600">Ảnh đính kèm</span>
        <div style="position:relative;display:inline-block">
          <img id="custPreviewImg" src="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
          <button type="button" onclick="cancelCustPreview()" style="position:absolute;top:-6px;right:-6px;background:#fff;border:1px solid #ddd;border-radius:50%;width:20px;height:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:11px;color:#dc2626;font-weight:700;box-shadow:0 1px 3px rgba(0,0,0,0.15)" title="Xoá ảnh">&times;</button>
        </div>
        <span id="custPreviewName" style="font-size:11px;color:#999;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
      </div>
    </div>
    <form method="post" action="/customer/chat/send" enctype="multipart/form-data" id="custChatForm" class="chat-form-wrap">
      <?php if(function_exists("csrfField")) echo csrfField(); else echo "<input type=\"hidden\" name=\"_csrf\" value=\"".($_SESSION["_csrf"]??"")."\">" ?>
      <input type="text" name="message" id="custChatInput" placeholder="Nhập tin nhắn..." autocomplete="off">
      <input type="file" name="image" accept="image/*" style="display:none" id="chatImgInput" onchange="previewCustImage(this)">
      <button type="button" onclick="document.getElementById('chatImgInput').click()" class="chat-img-btn" id="custImgBtn" title="Gửi ảnh">📷</button>
      <button type="submit" class="chat-send-btn">Gửi</button>
    </form>
  </div>
</div></section>
<script>
var cb=document.getElementById('chatBox');
if(cb) cb.scrollTop=cb.scrollHeight;

// Image preview for customer chat
function previewCustImage(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  var preview = document.getElementById('custChatPreview');
  var img = document.getElementById('custPreviewImg');
  var nameEl = document.getElementById('custPreviewName');
  var imgBtn = document.getElementById('custImgBtn');
  if (!preview || !img) return;
  nameEl.textContent = file.name;
  if (file.type && file.type.startsWith('image/')) {
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
  if(imgBtn) imgBtn.style.borderColor = '#1a3258';
}

function cancelCustPreview() {
  var preview = document.getElementById('custChatPreview');
  if (preview) preview.style.display = 'none';
  document.getElementById('chatImgInput').value = '';
  var imgBtn = document.getElementById('custImgBtn');
  if(imgBtn) imgBtn.style.borderColor = '#ddd';
}

// Validate form: require at least message or image
var form = document.getElementById('custChatForm');
if(form) {
  form.addEventListener('submit', function(e) {
    var msg = (document.getElementById('custChatInput') || {}).value || '';
    var fileInput = document.getElementById('chatImgInput');
    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    if (!msg.trim() && !hasFile) {
      e.preventDefault();
      alert('Vui lòng nhập tin nhắn hoặc đính kèm ảnh.');
    }
  });
}

// ── Auto-poll for new admin messages (no reload) ──
var lastMsgId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
function escapeChatHtml(s){ var d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
function appendAdminMsg(m){
  var box=document.getElementById('chatBox'); if(!box) return;
  var empty=box.querySelector('.chat-empty'); if(empty) empty.remove();
  var wrap=document.createElement('div'); wrap.style.cssText='display:flex;justify-content:flex-start;margin-bottom:12px';
  var img=''; if(m.image_path){ img='<img src="/uploads/chat/'+m.image_path+'" style="max-width:200px;border-radius:8px;margin-top:6px" onerror="this.style.display=&#39;none&#39;">'; }
  var ca=(m.created_at||''); var t=ca.length>=16 ? (ca.substr(11,5)+' '+ca.substr(8,2)+'/'+ca.substr(5,2)) : '';
  wrap.innerHTML='<div style="max-width:620px;word-break:break-word;overflow-wrap:break-word;padding:10px 14px;border-radius:16px 16px 16px 4px;background:#fff;color:#1f2937;box-shadow:0 1px 4px rgba(15,35,66,.08);font-size:14px;line-height:1.5">'+(m.content?escapeChatHtml(m.content).replace(/\n/g,'<br>'):'')+img+'<div style="font-size:10px;opacity:.6;margin-top:4px;text-align:right">'+t+'</div></div>';
  box.appendChild(wrap); box.scrollTop=box.scrollHeight;
}
function pollCustChat(){
  fetch('/customer/chat/poll?after='+lastMsgId).then(function(r){return r.json();}).then(function(d){
    if(d&&d.messages&&d.messages.length){
      d.messages.forEach(function(m){
        var mid=parseInt(m.id)||0; if(mid>lastMsgId) lastMsgId=mid;
        if(m.sender_role!=='customer') appendAdminMsg(m);
      });
    }
  }).catch(function(){});
}
setInterval(pollCustChat, 3000);
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>