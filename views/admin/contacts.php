<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.cm-card{background:#fff;border:1px solid var(--line);border-radius:10px;margin-bottom:12px;overflow:hidden;transition:box-shadow .2s}
.cm-card:hover{box-shadow:0 4px 15px rgba(0,0,0,0.06)}
.cm-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;cursor:pointer;transition:background .2s}
.cm-head:hover{background:#f8f9fc}
.cm-badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
.cm-badge.new{background:#ffeeba;color:#856404}
.cm-badge.replied{background:#d4edda;color:#155724}
.cm-badge.read{background:#e2e3e5;color:#383d41}
.cm-body{padding:18px;display:none}
.cm-body.open{display:block}
.cm-info{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;font-size:13px}
.cm-info span{color:var(--ink-3)}
.cm-info strong{color:var(--navy)}
.cm-msg{background:#f8f9fc;border:1px solid var(--line);border-radius:6px;padding:14px;margin-bottom:14px;font-size:14px;line-height:1.6;color:var(--ink-1)}
.cm-reply-box{background:#edf7ed;border:1px solid #c3e6cb;border-radius:6px;padding:14px;margin-bottom:14px}
.cm-reply-box h4{margin:0 0 6px;color:#155724;font-size:13px}
.cm-reply-box p{margin:0;font-size:14px;color:#333}
.cm-form textarea{width:100%;padding:10px;border:1.5px solid var(--line);border-radius:6px;font-size:14px;resize:vertical;min-height:80px}
.cm-form textarea:focus{border-color:var(--navy);outline:none}
.cm-actions{display:flex;gap:8px;margin-top:10px}
.btn-reply{background:var(--navy);color:#fff;border:none;padding:8px 20px;border-radius:5px;font-weight:600;font-size:13px;cursor:pointer}
.btn-reply:hover{background:#0b1f40}
.btn-reply:disabled{opacity:0.6;cursor:not-allowed}
.btn-delete{background:#dc3545;color:#fff;border:none;padding:8px 16px;border-radius:5px;font-weight:600;font-size:13px;cursor:pointer}
.btn-delete:hover{background:#c82333}
.cm-empty{text-align:center;padding:60px;color:var(--ink-3);font-size:15px}
.cm-stats{display:flex;gap:16px;margin-bottom:20px}
.cm-stat{background:#fff;border:1px solid var(--line);border-radius:8px;padding:14px 20px;flex:1;text-align:center}
.cm-stat .num{font-size:28px;font-weight:800;color:var(--navy)}
.cm-stat .label{font-size:12px;color:var(--ink-3);margin-top:2px}
.cm-pager{display:flex;justify-content:center;gap:6px;margin-top:20px}
.cm-pager a,.cm-pager span{display:inline-block;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid var(--line)}
.cm-pager a{color:var(--navy);background:#fff}
.cm-pager a:hover{background:var(--navy);color:#fff}
.cm-pager span.current{background:var(--navy);color:#fff;border-color:var(--navy)}
</style>

<div class="dash-content">
  <div class="sec-head" style="margin-bottom:20px">
    <div class="title"><span class="bar"></span><h1 style="font-size:22px">Quản lý liên hệ</h1></div>
  </div>

  <?php
  // Stats (all messages)
  $allStats = dbAll("SELECT status, COUNT(*) AS cnt FROM contact_messages GROUP BY status");
  $totalAll = 0; $newCount = 0; $repliedCount = 0; $readCount = 0;
  foreach ($allStats as $st) {
      $totalAll += $st['cnt'];
      if ($st['status'] === 'new') $newCount = $st['cnt'];
      elseif ($st['status'] === 'replied') $repliedCount = $st['cnt'];
      else $readCount += $st['cnt'];
  }

  // Pagination
  $perPage = 5;
  $page = max(1, intval($_GET['page'] ?? 1));
  $totalPages = max(1, ceil($totalAll / $perPage));
  $offset = ($page - 1) * $perPage;
  $msgs = dbAll("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset]);
  ?>

  <div class="cm-stats">
    <div class="cm-stat"><div class="num"><?= $totalAll ?></div><div class="label">Tổng tin nhắn</div></div>
    <div class="cm-stat"><div class="num" style="color:#856404"><?= $newCount ?></div><div class="label">Chưa đọc</div></div>
    <div class="cm-stat"><div class="num" style="color:#155724"><?= $repliedCount ?></div><div class="label">Đã trả lời</div></div>
    <div class="cm-stat"><div class="num" style="color:#6c757d"><?= $readCount ?></div><div class="label">Đã đọc</div></div>
  </div>

  <div style="font-size:13px;color:var(--ink-3);margin-bottom:12px">
    Hiển thị <?= count($msgs) ?>/<?= $totalAll ?> tin nhắn (trang <?= $page ?>/<?= $totalPages ?>)
  </div>

  <?php if (empty($msgs)): ?>
    <div class="cm-empty">
      <svg width="48" height="48" fill="none" stroke="#ccc" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      <div style="margin-top:12px">Chưa có tin nhắn liên hệ nào</div>
    </div>
  <?php else: ?>
    <?php foreach ($msgs as $msg): ?>
      <?php $status = $msg['status'] ?? 'new'; ?>
      <div class="cm-card">
        <div class="cm-head" onclick="this.nextElementSibling.classList.toggle('open');if(this.dataset.id && '<?= $status ?>'==='new'){fetch('/admin/contacts/<?= $msg['id'] ?>/mark-read',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});this.querySelector('.cm-badge').className='cm-badge read';this.querySelector('.cm-badge').textContent='Đã đọc'}" data-id="<?= $msg['id'] ?>">
          <div>
            <strong style="color:var(--navy);font-size:15px"><?= e($msg['name']) ?></strong>
            <span style="color:var(--ink-3);font-size:12px;margin-left:8px"><?= e($msg['email']) ?> · <?= e($msg['phone']) ?></span>
            <span style="color:var(--ink-4);font-size:12px;margin-left:8px"><?= e($msg['subject'] ?? '') ?></span>
          </div>
          <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12px;color:var(--ink-3)"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
            <span class="cm-badge <?= $status ?>"><?= $status==='new'?'Mới':($status==='replied'?'Đã trả lời':'Đã đọc') ?></span>
          </div>
        </div>
        <div class="cm-body">
          <div class="cm-info">
            <div><span>Họ tên:</span> <strong><?= e($msg['name']) ?></strong></div>
            <div><span>Email:</span> <strong><?= e($msg['email']) ?></strong></div>
            <div><span>SĐT:</span> <strong><?= e($msg['phone']) ?></strong></div>
          </div>
          <div style="font-size:12px;color:var(--ink-3);margin-bottom:6px">Chủ đề: <strong><?= e($msg['subject'] ?? 'Không có') ?></strong></div>
          <div class="cm-msg"><?= nl2br(e($msg['message'])) ?></div>

          <?php if (!empty($msg['reply'])): ?>
            <div class="cm-reply-box">
              <h4>✅ Đã trả lời (<?= !empty($msg['replied_at']) ? date('d/m/Y H:i', strtotime($msg['replied_at'])) : '' ?>):</h4>
              <p><?= nl2br(e($msg['reply'])) ?></p>
            </div>
            <div class="cm-actions">
              <button type="button" class="btn-delete" onclick="if(confirm('Xóa tin nhắn này?'))location.href='/admin/contacts/<?= $msg['id'] ?>/delete'">🗑 Xóa tin nhắn</button>
            </div>
          <?php else: ?>
            <form method="POST" action="/admin/contacts/<?= $msg['id'] ?>/reply" class="cm-form">
              <?= csrfField() ?>
              <label style="font-weight:700;font-size:13px;color:var(--navy);display:block;margin-bottom:6px">Trả lời (sẽ gửi về email <?= e($msg['email']) ?>):</label>
              <textarea name="reply" required minlength="5" maxlength="100" placeholder="Nhập nội dung trả lời (tối đa 100 ký tự)..."></textarea>
              <div class="cm-actions">
                <button type="submit" class="btn-reply" onclick="this.disabled=true;this.textContent='Đang gửi...';this.form.submit();">📧 Gửi trả lời qua Email</button>
                <button type="button" class="btn-delete" onclick="if(confirm('Xóa tin nhắn này?'))location.href='/admin/contacts/<?= $msg['id'] ?>/delete'">🗑 Xóa</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="cm-pager">
      <?php if ($page > 1): ?>
        <a href="/admin/contacts?page=<?= $page - 1 ?>">← Trước</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="/admin/contacts?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="/admin/contacts?page=<?= $page + 1 ?>">Sau →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
