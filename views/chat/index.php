<?php
$myRole = $user['role'] ?? 'customer';
require __DIR__.'/../partials/head.php';
?>
<style>
.chat-page { display:grid; grid-template-columns:300px 1fr; gap:0; height:calc(100vh - 230px); min-height:500px; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); border: 1px solid #eaeaea; }
@media(max-width:768px) {
  .chat-page { grid-template-columns:1fr; height:calc(100vh - 80px); border-radius:0; border:none; box-shadow:none; }
  .chat-list { display:<?= !empty($active)?'none':'flex' ?>; flex-direction:column; }
}
.chat-list { border-right:1px solid var(--line); overflow-y:auto; background:#f9fafc; max-height:calc(100vh - 230px); }
.chat-list-head { padding:20px; font-weight:800; font-size:18px; border-bottom:1px solid var(--line); color:var(--navy); background:#fff; position:sticky; top:0; z-index:10; }
.chat-list-item { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid var(--line); cursor:pointer; text-decoration:none; color:inherit; transition:all 0.2s; }
.chat-list-item:hover, .chat-list-item.active { background:#fff; border-left: 4px solid var(--gold); padding-left: 16px; }
.chat-avatar { width:36px !important; height:36px !important; min-width:36px; max-width:36px; border-radius:50% !important; background:linear-gradient(135deg, var(--navy), #2c4a7c); color:#fff; display:flex !important; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex:0 0 36px !important; box-shadow: 0 2px 8px rgba(26,50,88,0.2); }
.chat-info { flex:1; min-width:0; }
.chat-info .name { font-weight:700; font-size:15px; color:var(--navy); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chat-info .last { font-size:13px; color:var(--ink-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:4px; }
.unread-dot { background:#e74c3c; color:#fff; font-size:11px; font-weight:800; padding:2px 8px; border-radius:12px; flex-shrink:0; }
.chat-window { display:flex; flex-direction:column; background:#fff; height:100%; overflow:hidden; }
.chat-head { display:flex; align-items:center; gap:14px; padding:16px 24px; border-bottom:1px solid var(--line); background:#fff; position:relative; box-shadow: 0 2px 10px rgba(0,0,0,0.02); z-index:5; }
.chat-head-info .name { font-weight:800; font-size:16px; color:var(--navy); }
.chat-head-info .status { font-size:12px; color:#27ae60; font-weight:600; margin-top:2px; display:flex; align-items:center; gap:4px; }
.chat-head-info .status::before { content:''; display:block; width:8px; height:8px; background:#27ae60; border-radius:50%; }
.chat-msgs { flex:1; min-height:0; overflow-y:auto; padding:24px; display:flex; flex-direction:column; gap:12px; background:#f4f7f9; }
.msg-row { display:flex; flex-direction:row; gap:10px; max-width:80%; word-break:break-word; align-items:flex-start; }
.msg-row.in { align-self:flex-start; }
.msg-row.out { align-self:flex-end; flex-direction:row-reverse; }
.msg-content { display:flex; flex-direction:column; flex:1; min-width:60px; overflow:hidden; }
.msg-bubble { padding:12px 18px; border-radius:18px; font-size:14.5px; line-height:1.6; word-break:break-word; box-shadow:0 1px 2px rgba(0,0,0,0.05); overflow:hidden; max-width:100%; }
.msg-row.in .msg-bubble { background:#fff; border:1px solid #eaeaea; border-bottom-left-radius:4px; color:var(--ink); }
.msg-row.out .msg-bubble { background:linear-gradient(135deg, var(--navy), #2c4a7c); color:#fff; border-bottom-right-radius:4px; border:none; }
.msg-bubble img { max-width:100%; max-height:260px; border-radius:8px; display:block; margin-top:6px; cursor:pointer; border:2px solid rgba(255,255,255,0.15); object-fit:contain; }
.msg-time { font-size:11px; color:#aaa; margin-top:4px; text-align:right; font-weight:500; }
.msg-row.in .msg-time { text-align:left; }
.chat-input-area { border-top:1px solid var(--line); padding:16px 24px; background:#fff; flex-shrink:0; }
.chat-attach-preview { display:none; align-items:center; gap:12px; margin-bottom:12px; padding:12px; background:#f8f9fa; border-radius:8px; font-size:13px; font-weight:600; border:1px solid #eaeaea; }
.chat-attach-preview img { width:56px; height:56px; object-fit:cover; border-radius:6px; }
.chat-input-row { display:flex; align-items:center; gap:12px; }
.chat-input-row input[type=text] { flex:1; border:1px solid #ddd; border-radius:24px; padding:12px 20px; font-size:15px; outline:none; background:#f9fafc; transition:all 0.2s; }
.chat-input-row input[type=text]:focus { border-color:var(--navy); background:#fff; box-shadow:0 0 0 3px rgba(26,50,88,0.1); }
.btn-attach { background:#f1f3f5; border:none; cursor:pointer; color:#555; width:44px; height:44px; border-radius:50%; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
.btn-attach:hover { background:#e2e6ea; color:var(--navy); transform:scale(1.05); }
.btn-send { background:var(--gold); color:#fff; border:none; border-radius:50%; width:46px; height:46px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all 0.2s; box-shadow:0 2px 8px rgba(197,163,101,0.4); }
.btn-send:hover { transform:scale(1.05); filter:brightness(1.1); }
.btn-send svg { transform:translateX(-1px); }
.chat-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:var(--ink-3); text-align:center; padding:40px; background:#f4f7f9; }
.chat-empty .icon { font-size:64px; margin-bottom:20px; opacity:0.5; }
.chat-empty h3 { font-size:20px; color:var(--navy); margin-bottom:8px; }
.product-suggestion { display:flex; align-items:center; gap:12px; padding:12px; background:#fff; border-radius:8px; border:1px solid #eaeaea; margin-bottom:16px; cursor:pointer; transition:all 0.2s; }
.product-suggestion:hover { border-color:var(--gold); box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.product-suggestion img { width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #f0f0f0; }
.product-suggestion .info { flex:1; }
.product-suggestion .title { font-weight:700; font-size:14px; color:var(--navy); margin-bottom:4px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.product-suggestion .price { font-weight:700; color:#e74c3c; font-size:14px; }
.btn-send-suggestion { background:var(--navy); color:#fff; padding:6px 12px; border-radius:4px; font-size:12px; font-weight:600; border:none; cursor:pointer; white-space:nowrap; }

.char-counter { font-size:11px; color:#999; text-align:right; margin-top:4px; }
.char-counter.warn { color:#e74c3c; font-weight:600; }
</style>

<section class="block" style="padding-top:20px;padding-bottom:40px">
  <div class="wrap" style="max-width:1200px">
    <div class="chat-page" id="chatPage">
      <!-- Thread list -->
      <div class="chat-list">
        <div class="chat-list-head"> Trung tâm tin nhắn</div>
        <?php if(empty($threads)):?>
          <div style="padding:40px 20px;color:var(--ink-3);font-size:14px;text-align:center">
            <div style="font-size:40px;margin-bottom:12px"></div>
            Chưa có cuộc trò chuyện nào
          </div>
        <?php endif;?>
        <?php foreach($threads as $t):
          $unreadKey = ($myRole==='customer') ? 'customer_unread' : 'partner_unread';
          $unread = $t[$unreadKey] ?? 0;
        ?>
          <a href="/chat?thread=<?=$t['id']?>" class="chat-list-item <?= $active && $active['id']==$t['id']?'active':'' ?>">
            <div class="chat-avatar" style="width:36px;height:36px;min-width:36px;max-width:36px;min-height:36px;max-height:36px;border-radius:50%;flex:0 0 36px;font-size:14px"><?= mb_strtoupper(mb_substr($t['title']??'?',0,1)) ?></div>
            <div class="chat-info">
              <div class="name"><?= e($t['title']??'Không tên') ?></div>
              <div class="last"><?= e(mb_substr($t['last_message']??'Chưa có tin',0,40)) ?></div>
            </div>
            <?php if($unread>0):?><span class="unread-dot"><?=$unread?></span><?php endif;?>
          </a>
        <?php endforeach;?>
      </div>

      <!-- Chat window -->
      <div class="chat-window">
        <?php if($active):?>
          <!-- Header -->
          <div class="chat-head">
            <?php if(count($threads)>0): ?>
              <a href="/chat" style="display:none;margin-right:8px;color:var(--navy)" class="back-btn" id="chatBackBtn">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
              </a>
            <?php endif; ?>
            <div class="chat-avatar" style="width:36px;height:36px;min-width:36px;min-height:36px;max-width:36px;max-height:36px;border-radius:50%;flex-shrink:0;flex-grow:0;font-size:14px"><?= mb_strtoupper(mb_substr($active['title'],0,1)) ?></div>
            <div class="chat-head-info">
              <div class="name"><?= e($active['title']) ?></div>
              <div class="status">Đang hoạt động</div>
            </div>
          </div>

          <!-- Messages -->
          <div class="chat-msgs" id="chatMsgs">
            <?php if(isset($active['product_id'])): 
                $product = dbGet("SELECT * FROM products WHERE id=?", [$active['product_id']]);
                if($product):
                  $img = dbGet("SELECT file_path FROM product_images WHERE product_id=? ORDER BY is_main DESC, sort_order ASC LIMIT 1", [$product['id']]);
                  $image_url = $img ? $img['file_path'] : '';
            ?>
              <div class="product-suggestion" onclick="document.getElementById('chatInput').value='Tôi muốn tư vấn về sản phẩm: <?=e($product['name'])?>';document.getElementById('chatInput').focus();">
                <img src="/uploads/products/<?=e($image_url)?>" onerror="this.src='/img/placeholder.png'">
                <div class="info">
                  <div class="title"><?=e($product['name'])?></div>
                  <div class="price"><?=vnd($product['price'])?></div>
                </div>
                <button class="btn-send-suggestion">Hỏi ngay</button>
              </div>
            <?php endif; endif; ?>
            
            <?php foreach($messages as $m):
              $isMe = ($m['sender_user_id'] == $user['id']);
            ?>
              <div class="msg-row <?= $isMe?'out':'in' ?>" data-mid="<?=$m['id']?>">
                <?php if(!$isMe):?>
                  <div class="chat-avatar" style="width:36px;height:36px;min-width:36px;max-width:36px;min-height:36px;max-height:36px;border-radius:50%;flex:0 0 36px;font-size:14px"><?= mb_strtoupper(mb_substr($active['title'],0,1)) ?></div>
                <?php endif;?>
                <div class="msg-content">
                  <div class="msg-bubble">
                    <?php if($m['attachment_path']):?>
                      <?php $ext = strtolower(pathinfo($m['attachment_path'], PATHINFO_EXTENSION)); ?>
                      <?php if(in_array($ext,['jpg','jpeg','png','gif','webp'])):?>
                        <img src="/uploads/chat/<?= e($m['attachment_path']) ?>" loading="lazy" alt="Ảnh" onclick="window.open(this.src)">
                      <?php else:?>
                        <a href="/uploads/chat/<?= e($m['attachment_path']) ?>" target="_blank" style="color:inherit;text-decoration:underline;font-weight:600"> Xem tệp đính kèm</a>
                      <?php endif;?>
                    <?php endif;?>
                    <?php if($m['content'] && $m['content']!=='[Ảnh]'):?><?= nl2br(e($m['content'])) ?><?php endif;?>
                  </div>
                  <div class="msg-time"><?= relTime($m['created_at']) ?></div>
                </div>
              </div>
            <?php endforeach;?>
          </div>

          <!-- Input -->
          <div class="chat-input-area">
            <form method="post" action="/chat/send" enctype="multipart/form-data" id="chatForm">
              <?= csrfField() ?>
              <input type="hidden" name="thread_id" value="<?=$active['id']?>">

              <!-- Image preview - same design as admin (proven working) -->
              <div id="attachPreview" style="display:none;padding:10px 16px;background:#f8f9fa;border-radius:8px;margin-bottom:10px;border:1px solid #e0e0e0">
                <div style="font-size:12px;color:#666;font-weight:600;margin-bottom:6px">1 ảnh</div>
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="position:relative;display:inline-block">
                    <img id="attachImg" src="" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
                    <button type="button" onclick="cancelAttach()" style="position:absolute;top:-6px;right:-6px;background:#fff;border:1px solid #ddd;border-radius:50%;width:22px;height:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;color:#dc2626;font-weight:700;box-shadow:0 1px 3px rgba(0,0,0,0.15)" title="Xoá ảnh">&times;</button>
                  </div>
                  <span id="attachName" style="font-size:12px;color:#999;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
                </div>
              </div>

              <div class="chat-input-row">
                <!-- Image attach button -->
                <button type="button" class="btn-attach" title="Gửi ảnh" onclick="document.getElementById('attachFile').click()">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="2"/>
                    <polyline points="21 15 16 10 5 21"/>
                  </svg>
                </button>
                <input type="file" name="attachment" id="attachFile" accept="image/*,application/pdf,.pdf" style="display:none" onchange="previewAttach(this)">
                
                <input type="text" name="content" id="chatInput" placeholder="Nhập tin nhắn của bạn..." autocomplete="off">
                
                <button type="submit" class="btn-send" id="btnSend">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </button>
              </div>
          <div class="char-counter" id="charCounter" style="display:none">0/500</div>
            </form>
          </div>
        <?php else:?>
          <div class="chat-empty">
            <div class="icon"></div>
            <h3>Xin chào, <?=$user['full_name']?>!</h3>
            <p>Chọn một cuộc trò chuyện để tiếp tục hoặc bắt đầu hỏi đáp về sản phẩm bạn quan tâm.</p>
          </div>
        <?php endif;?>
      </div>
    </div>
  </div>
</section>

<style>
@media(max-width:768px){
  #chatBackBtn { display:block!important; }
}

.char-counter { font-size:11px; color:#999; text-align:right; margin-top:4px; }
.char-counter.warn { color:#e74c3c; font-weight:600; }
</style>

<script>
// === Customer Chat Script (Rebuilt) ===
var chatInput = document.getElementById('chatInput');
var chatForm = document.getElementById('chatForm');

// Enter key sends message  
if (chatInput && chatForm) {
  chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      var hasText = chatInput.value.trim().length > 0;
      var hasFile = document.getElementById('attachFile') && document.getElementById('attachFile').files.length > 0;
      if (hasText || hasFile) {
        chatForm.submit();
      }
    }
  });
}

// === Image Preview System (inline, same as admin) ===
function previewAttach(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  var preview = document.getElementById('attachPreview');
  var img = document.getElementById('attachImg');
  var nameEl = document.getElementById('attachName');
  if (!preview || !img) return;
  
  if (nameEl) nameEl.textContent = file.name;
  
  if (file.type && file.type.startsWith('image/')) {
    var reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    // Non-image file (PDF)
    img.src = '';
    img.style.display = 'none';
    preview.style.display = 'block';
  }
}

function cancelAttach() {
  var preview = document.getElementById('attachPreview');
  if (preview) preview.style.display = 'none';
  var img = document.getElementById('attachImg');
  if (img) { img.src = ''; img.style.display = ''; }
  var fileInput = document.getElementById('attachFile');
  if (fileInput) fileInput.value = '';
}

// Scroll chat to bottom
var chatMsgs = document.getElementById('chatMsgs');
if (chatMsgs) chatMsgs.scrollTop = chatMsgs.scrollHeight;

// Poll for new messages
<?php if (!empty($active)): ?>
var lastMsgId = <?= intval($lastMsgId ?? 0) ?>;
function pollMessages() {
  fetch('/chat/poll?thread=<?=$active['id']?>&after=' + lastMsgId)
    .then(function(r) { return r.json(); })
    .then(function(msgs) {
      if (!msgs || !msgs.length) return;
      var container = document.getElementById('chatMsgs');
      msgs.forEach(function(m) {
        var isMine = (m.sender_role === 'customer');
        var cls = isMine ? 'out' : 'in';
        var avatar = isMine ? '' : '<div class="chat-avatar" style="width:36px;height:36px;min-width:36px;max-width:36px;min-height:36px;max-height:36px;border-radius:50%;flex:0 0 36px;font-size:14px">' + (m.sender_name ? m.sender_name.charAt(0).toUpperCase() : 'H') + '</div>';
        var imgHtml = '';
        if (m.attachment_path) {
          imgHtml = '<img src="/uploads/chat/' + m.attachment_path + '" style="max-width:260px;border-radius:8px;margin-top:6px;cursor:pointer" onclick="window.open(this.src)">';
        }
        var textContent = m.content ? m.content.replace(/\n/g, '<br>') : '';
        var html = '<div class="msg-row ' + cls + '">' + avatar +
          '<div class="msg-content"><div class="msg-bubble">' + textContent + imgHtml + '</div>' +
          '<div class="msg-time">Vừa xong</div></div></div>';
        container.insertAdjacentHTML('beforeend', html);
        if (m.id > lastMsgId) lastMsgId = m.id;
      });
      container.scrollTop = container.scrollHeight;
    })
    .catch(function(e) { console.log('Poll error:', e); });
}
setInterval(pollMessages, 5000);
<?php endif; ?>

// Character counter
var chatInputEl = document.getElementById('chatInput');
var counterEl = document.getElementById('charCounter');
if (chatInputEl && counterEl) {
  counterEl.style.display = 'block';
  chatInputEl.addEventListener('input', function() {
    var len = this.value.length;
    counterEl.textContent = len + '/500';
    counterEl.className = 'char-counter' + (len > 450 ? ' warn' : '');
  });
}
</script>
<?php
require __DIR__.'/../partials/foot.php';
?>