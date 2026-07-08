<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.bn-grid { display:grid; grid-template-columns:1fr; gap:14px; }
.bn-row { display:flex; gap:14px; align-items:center; background:#fff; border:1px solid #eef1f6; border-radius:12px; padding:12px; box-shadow:0 1px 4px rgba(20,40,80,.05); }
.bn-row img { width:200px; height:62px; object-fit:cover; border-radius:8px; flex-shrink:0; background:#f2f4f8; border:1px solid #e7ebf2; }
.bn-info { flex:1; min-width:0; }
.bn-info .t { font-weight:700; color:#16243f; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bn-info .l { font-size:12px; color:#7a869a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.bn-badge { display:inline-block; padding:2px 9px; border-radius:11px; font-size:11px; font-weight:700; margin-top:5px; }
.bn-badge.on { background:#dcfce7; color:#166534; }
.bn-badge.off { background:#eef2f7; color:#64748b; }
.bn-acts { display:flex; gap:6px; align-items:center; flex-shrink:0; flex-wrap:wrap; justify-content:flex-end; }
.bn-acts form { margin:0; }
.bn-mini { padding:7px 11px; border-radius:7px; font-size:12.5px; font-weight:600; cursor:pointer; border:1px solid #d7deea; background:#fff; color:#1a3258; }
.bn-mini:disabled { opacity:.4; cursor:default; }
.bn-mini.del { border-color:#f3c2c2; color:#c0392b; }
.bn-mini.del:hover { background:#fef2f2; }
.bn-mini.tg { border-color:#cbd6e6; }
.bn-add { background:#fff; border:1px solid #eef1f6; border-radius:12px; padding:18px 20px; box-shadow:0 1px 4px rgba(20,40,80,.05); margin-bottom:18px; }
.bn-add label { display:block; font-size:12px; font-weight:700; color:#555; margin:10px 0 5px; text-transform:uppercase; letter-spacing:.4px; }
.bn-add input[type=text], .bn-add input[type=file] { width:100%; max-width:560px; padding:9px 12px; border:1px solid #d7deea; border-radius:8px; font-size:14px; box-sizing:border-box; }
.bn-add .hint { font-size:12px; color:#9aa5b5; margin-top:4px; }
.bn-add button { margin-top:16px; padding:10px 20px; background:#1a3258; color:#fff; border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:14px; }
</style>
<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<div class="dash-head">
  <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0">Banner 2 <span style="font-size:13px;font-weight:500;color:#7a869a">— Banner trượt (carousel) trang chủ</span></h1>
</div>

<div class="bn-add">
  <div style="font-weight:700;color:var(--navy);font-size:15px;margin-bottom:4px">Thêm banner mới</div>
  <div style="font-size:12.5px;color:#7a869a">Banner sẽ hiển thị dạng trượt (carousel) ở trang chủ. Kích thước khuyến nghị <b>1600 × 500px</b> (tỷ lệ ~16:5), ảnh khác cỡ sẽ tự cắt cho vừa.</div>
  <form method="post" action="/admin/banners/add" enctype="multipart/form-data">
    <?= csrfField() ?>
    <label>Ảnh banner *</label>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
    <label>Link khi bấm vào (tùy chọn)</label>
    <input type="text" name="link" placeholder="VD: /promotions hoặc /products?cat=dan-lanh hoặc https://...">
    <div class="hint">Để trống nếu banner chỉ để xem (không bấm được).</div>
    <label>Tiêu đề / ghi chú (tùy chọn — chỉ để bạn quản lý)</label>
    <input type="text" name="btitle" maxlength="120" placeholder="VD: Khuyến mãi tháng 6">
    <button type="submit">+ Thêm banner</button>
  </form>
</div>

<div style="font-weight:700;color:var(--navy);font-size:15px;margin:0 0 12px">Danh sách banner (<?= count($banners) ?>)</div>
<?php if(empty($banners)): ?>
  <div style="background:#fff;border:1px dashed #d7deea;border-radius:12px;padding:30px;text-align:center;color:#9aa5b5;font-size:14px">Chưa có banner nào. Thêm banner ở trên để hiển thị trên trang chủ.</div>
<?php else: ?>
<div class="bn-grid">
  <?php foreach($banners as $i=>$b): ?>
  <div class="bn-row">
    <img src="/uploads/banners/<?= e($b['img']) ?>?v=<?= @filemtime(__DIR__.'/../../uploads/banners/'.$b['img']) ?>" alt="" onerror="this.style.opacity=.3">
    <div class="bn-info">
      <div class="t"><?= e(!empty($b['title']) ? $b['title'] : 'Banner #'.($i+1)) ?></div>
      <div class="l"><?= !empty($b['link']) ? '🔗 '.e($b['link']) : '<span style="color:#b3bccb">(không có link)</span>' ?></div>
      <span class="bn-badge <?= !empty($b['active'])?'on':'off' ?>"><?= !empty($b['active'])?'Đang hiển thị':'Đang ẩn' ?></span>
    </div>
    <div class="bn-acts">
      <form method="post" action="/admin/banners/move"><?= csrfField() ?><input type="hidden" name="idx" value="<?= $i ?>"><input type="hidden" name="dir" value="up"><button class="bn-mini" type="submit" title="Lên" <?= $i===0?'disabled':'' ?>>↑</button></form>
      <form method="post" action="/admin/banners/move"><?= csrfField() ?><input type="hidden" name="idx" value="<?= $i ?>"><input type="hidden" name="dir" value="down"><button class="bn-mini" type="submit" title="Xuống" <?= $i===count($banners)-1?'disabled':'' ?>>↓</button></form>
      <form method="post" action="/admin/banners/<?= $i ?>/toggle"><?= csrfField() ?><button class="bn-mini tg" type="submit"><?= !empty($b['active'])?'Ẩn':'Hiện' ?></button></form>
      <form method="post" action="/admin/banners/<?= $i ?>/delete" onsubmit="return csConfirmForm(this,'Xóa banner này?')"><?= csrfField() ?><button class="bn-mini del" type="submit">Xóa</button></form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
