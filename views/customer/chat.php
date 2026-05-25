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
/* Chat responsive fix */
.chat-form-wrap {
  display: flex;
  gap: 8px;
  padding: 12px;
  background: #fff;
  border-top: 1px solid #eee;
  border-radius: 0 0 12px 12px;
  box-sizing: border-box;
  max-width: 100%;
}
.chat-form-wrap input[type="text"] {
  flex: 1;
  min-width: 0;
  padding: 10px 14px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  box-sizing: border-box;
}
.chat-form-wrap .chat-img-btn {
  background: none;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 8px 10px;
  cursor: pointer;
  color: #888;
  flex-shrink: 0;
}
.chat-form-wrap .chat-send-btn {
  background: linear-gradient(135deg, #c8a84e, #b8942e);
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  flex-shrink: 0;
  white-space: nowrap;
}
@media (max-width: 768px) {
  .chat-form-wrap {
    gap: 6px;
    padding: 10px;
  }
  .chat-form-wrap input[type="text"] {
    padding: 8px 10px;
    font-size: 13px;
  }
  .chat-form-wrap .chat-send-btn {
    padding: 8px 12px;
    font-size: 13px;
  }
  .chat-form-wrap .chat-img-btn {
    padding: 8px;
  }
}
@media (max-width: 480px) {
  .chat-form-wrap {
    gap: 4px;
    padding: 8px;
  }
  .chat-form-wrap .chat-send-btn {
    padding: 8px 10px;
    font-size: 12px;
  }
  .chat-form-wrap .chat-img-btn {
    padding: 6px;
    font-size: 12px;
  }
}
/* Make the chat card and its parent not overflow */
.sec-card {
  overflow: hidden;
  box-sizing: border-box;
  max-width: 100%;
}
#chatBox {
  box-sizing: border-box;
}
#chatBox img {
  max-width: 100% !important;
}
</style>
<section class="block"><div class="wrap" style="max-width:800px;box-sizing:border-box;overflow:hidden">
  <div class="sec-card">
    <div class="sec-head"><div class="title"><span class="bar"></span><h2>Tin nhắn với Admin</h2></div></div>
    <div id="chatBox" style="height:400px;overflow-y:auto;padding:16px;background:#f8f9fa;border-radius:0 0 12px 12px">
      <?php if (empty($messages)): ?>
        <div style="text-align:center;padding:40px;color:#888">
          <svg width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          <p style="margin-top:12px">Chưa có tin nhắn. Hãy gửi tin nhắn đầu tiên!</p>
        </div>
      <?php else: ?>
        <?php foreach ($messages as $m):
          $isMe = ($m['sender_role'] === 'customer');
        ?>
        <div style="display:flex;justify-content:<?= $isMe ? 'flex-end' : 'flex-start' ?>;margin-bottom:10px">
          <div style="max-width:70%;padding:10px 14px;border-radius:<?= $isMe ? '14px 14px 4px 14px' : '14px 14px 14px 4px' ?>;background:<?= $isMe ? 'linear-gradient(135deg,#1a3258,#2c4a73)' : '#fff' ?>;color:<?= $isMe ? '#fff' : '#333' ?>;box-shadow:0 1px 3px rgba(0,0,0,0.1);font-size:14px;line-height:1.5">
            <?php if (!empty($m['content'])): ?><?= nl2br(e($m['content'])) ?><?php endif; ?>
            <?php if (!empty($m['image_path'])): ?><img src="/uploads/chat/<?= e($m['image_path']) ?>" style="max-width:200px;border-radius:8px;margin-top:6px" onerror="this.style.display='none'"><?php endif; ?>
            <div style="font-size:10px;opacity:0.6;margin-top:4px;text-align:right"><?= date('H:i d/m', strtotime($m['created_at'])) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <!-- Image preview -->
    <div id="custChatPreview" style="display:none;padding:8px 12px;background:#f8f9fa;border-top:1px solid #eee">
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:12px;color:#666;font-weight:600">📷 1 ảnh</span>
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
  if(imgBtn) imgBtn.style.borderColor = '#c8a84e';
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
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>