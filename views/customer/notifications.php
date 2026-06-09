<?php require __DIR__.'/../partials/head.php'; ?>
<div class="wrap" style="max-width:800px;margin:30px auto;padding:0 16px">
  <h1 style="font-size:22px;color:var(--navy);margin-bottom:20px">Thông báo</h1>
  <?php if(empty($notifications)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--ink-3)">
      <div style="font-size:48px;margin-bottom:12px">📭</div>
      <div style="font-size:15px">Chưa có thông báo nào</div>
    </div>
  <?php else: ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <span style="font-size:12px;color:var(--ink-4)">Trang <?= $page ?? 1 ?>/<?= $totalPages ?? 1 ?> (<?= $total ?? 0 ?> thông báo)</span>
      <form method="post" action="/customer/notifications/read-all" style="display:inline"><?= csrfField() ?>
        <button type="submit" style="background:none;border:1px solid var(--line);padding:6px 14px;border-radius:6px;font-size:12px;cursor:pointer;color:var(--ink-2)">✓ Đánh dấu tất cả đã đọc</button>
      </form>
    </div>
    <?php foreach($notifications as $n): ?>
      <div style="background:<?= $n['is_read'] ? '#fff' : '#f0f6ff' ?>;border:1px solid var(--line);border-radius:8px;padding:14px 18px;margin-bottom:10px;<?= !$n['is_read'] ? 'border-left:3px solid var(--navy)' : '' ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-weight:700;font-size:14px;color:var(--navy)"><?= e($n['title']) ?></div>
            <div style="font-size:13px;color:var(--ink-2);margin-top:4px"><?= e($n['message']) ?></div>
            <?php if(!empty($n['link'])): ?>
              <a href="<?= e($n['link']) ?>" style="font-size:12px;color:var(--gold-warm);font-weight:600;margin-top:6px;display:inline-block">Xem chi tiết →</a>
            <?php endif; ?>
          </div>
          <div style="font-size:11px;color:var(--ink-4);white-space:nowrap;margin-left:12px"><?= date('d/m H:i', strtotime($n['created_at'])) ?></div>
        </div>
        <div style="margin-top:8px;display:flex;gap:8px">
          <?php if(!$n['is_read']): ?>
            <form method="post" action="/customer/notifications/<?= $n['id'] ?>/read" style="display:inline"><?= csrfField() ?>
              <button type="submit" style="background:none;border:none;font-size:11px;color:var(--ink-3);cursor:pointer;text-decoration:underline">Đánh dấu đã đọc</button>
            </form>
          <?php endif; ?>
          <form method="post" action="/customer/notifications/<?= $n['id'] ?>/delete" style="display:inline"><?= csrfField() ?>
            <button type="submit" style="background:none;border:none;font-size:11px;color:#e74c3c;cursor:pointer" onclick="return confirmDeleteNotif(event, this.form)">Xóa</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <?php require_once __DIR__ . '/../partials/pagination.php'; renderPagination($page ?? 1, $totalPages ?? 1, '/customer/notifications'); ?>
  <?php endif; ?>
</div>
<div id="notifConfirmModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(10,25,47,0.5);align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:14px;max-width:360px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,0.3);padding:24px 22px;text-align:center">
    <div style="font-size:16px;font-weight:700;color:#0a192f;margin-bottom:8px">Xóa thông báo</div>
    <p style="font-size:13px;color:#666;margin:0 0 20px;line-height:1.5">Bạn có chắc muốn xóa thông báo này không?</p>
    <div style="display:flex;gap:10px">
      <button type="button" onclick="notifDelClose()" style="flex:1;padding:11px;background:#eef2f7;color:#0a192f;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">Hủy</button>
      <button type="button" onclick="notifDelConfirm()" style="flex:1;padding:11px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">Xóa</button>
    </div>
  </div>
</div>
<script>
var _notifDelForm=null;
function confirmDeleteNotif(e, form){ e.preventDefault(); _notifDelForm=form; document.getElementById("notifConfirmModal").style.display="flex"; return false; }
function notifDelClose(){ document.getElementById("notifConfirmModal").style.display="none"; _notifDelForm=null; }
function notifDelConfirm(){ if(_notifDelForm) _notifDelForm.submit(); }
document.addEventListener("click",function(e){ if(e.target&&e.target.id==="notifConfirmModal") notifDelClose(); });
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>
