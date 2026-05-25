<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Quản lý các bước cam kết</h1></div>
<div class="panel">
  <div style="padding:20px">
    <p style="color:#666;margin-bottom:20px">Quản lý nội dung các bước cam kết hiển thị trên trang chủ. Bạn có thể thêm, sửa, xóa và sắp xếp thứ tự.</p>
    
    <!-- Existing steps -->
    <?php $steps = dbAll("SELECT * FROM trust_steps ORDER BY sort_order ASC, id ASC"); ?>
    <?php foreach($steps as $s): ?>
      <div style="display:grid;grid-template-columns:50px 1fr 2fr 80px;gap:12px;align-items:center;padding:14px;margin-bottom:10px;background:#f8f9fa;border-radius:8px;border:1px solid #eee">
        <div style="font-size:24px;font-weight:900;color:var(--navy);text-align:center"><?= str_pad($s['sort_order'],2,'0',STR_PAD_LEFT) ?></div>
        <div>
          <div style="font-weight:700;font-size:14px"><?= htmlspecialchars($s['title']) ?></div>
          <div style="font-size:12px;color:#888"><?= htmlspecialchars($s['description'] ?? '') ?></div>
        </div>
        <form method="post" action="/admin/trust-steps/<?= $s['id'] ?>/edit" style="display:flex;gap:8px;align-items:end">
          <?= csrfField() ?>
          <div class="form-group" style="margin:0;flex:1"><label style="font-size:11px">Tiêu đề</label>
            <input type="text" name="title" value="<?= htmlspecialchars($s['title']) ?>" class="form-control" required></div>
          <div class="form-group" style="margin:0;flex:2"><label style="font-size:11px">Mô tả</label>
            <input type="text" name="description" value="<?= htmlspecialchars($s['description'] ?? '') ?>" class="form-control"></div>
          <input type="hidden" name="step_number" value="<?= $s['step_number'] ?>">
          <input type="hidden" name="icon" value="<?= htmlspecialchars($s['icon'] ?? '') ?>">
          <input type="hidden" name="sort_order" value="<?= $s['sort_order'] ?>">
          <input type="hidden" name="is_active" value="<?= $s['is_active'] ?>">
          <button type="submit" class="btn btn-navy" style="padding:6px 14px;font-size:12px">Lưu</button>
        </form>
        <form method="post" action="/admin/trust-steps/<?= $s['id'] ?>/delete" onsubmit="return confirm('Xóa bước này?')">
          <?= csrfField() ?>
          <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:6px 12px;border-radius:4px;font-size:12px;cursor:pointer">Xóa</button>
        </form>
      </div>
    <?php endforeach; ?>

    <!-- Add new step -->
    <div style="margin-top:20px;padding:16px;background:#e8f4f8;border-radius:8px;border:2px dashed #2196F3">
      <h4 style="margin-bottom:10px;color:#1565C0">➕ Thêm bước mới</h4>
      <form method="post" action="/admin/trust-steps/add" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
        <?= csrfField() ?>
        <div class="form-group" style="margin:0;flex:1;min-width:150px"><label style="font-size:11px">Tiêu đề</label>
          <input type="text" name="title" class="form-control" required placeholder="VD: Hỗ trợ 24/7"></div>
        <div class="form-group" style="margin:0;flex:2;min-width:200px"><label style="font-size:11px">Mô tả</label>
          <input type="text" name="description" class="form-control" placeholder="Mô tả ngắn gọn"></div>
        <div class="form-group" style="margin:0;width:60px"><label style="font-size:11px">Icon</label>
          <input type="text" name="icon" value="📦" class="form-control" style="text-align:center"></div>
        <input type="hidden" name="step_number" value="<?= count($steps) + 1 ?>">
        <input type="hidden" name="sort_order" value="<?= count($steps) + 1 ?>">
        <input type="hidden" name="is_active" value="1">
        <button type="submit" class="btn btn-navy" style="padding:8px 18px">Thêm</button>
      </form>
    </div>

    <!-- Sort order -->
    <div style="margin-top:24px;padding:20px;background:#fff;border-radius:10px;border:1px solid #eee">
      <h4 style="margin-bottom:6px">📋 Sắp xếp thứ tự hiển thị</h4>
      <p style="font-size:12px;color:#666;margin-bottom:14px">Dùng nút ↑ ↓ để thay đổi vị trí, sau đó bấm "Lưu thứ tự"</p>
      <form method="post" action="/admin/trust-steps/reorder">
        <?= csrfField() ?>
        <ul id="sortableSteps" style="list-style:none;padding:0;margin:0">
          <?php foreach($steps as $s): ?>
            <li style="padding:12px 16px;margin-bottom:6px;background:#f8f9fa;border:1px solid #ddd;border-radius:8px;display:flex;align-items:center;gap:12px">
              <input type="hidden" name="ids[]" value="<?= $s['id'] ?>">
              <span style="font-weight:800;color:#1a3258;font-size:18px;min-width:30px"><?= str_pad($s['sort_order'],2,'0',STR_PAD_LEFT) ?></span>
              <span style="flex:1;font-weight:600"><?= htmlspecialchars($s['title']) ?></span>
              <span style="font-size:12px;color:#888"><?= htmlspecialchars($s['description'] ?? '') ?></span>
              <button type="button" onclick="moveUp(this.closest('li'))" style="border:1px solid #ccc;background:#fff;border-radius:4px;padding:4px 10px;cursor:pointer">↑</button>
              <button type="button" onclick="moveDown(this.closest('li'))" style="border:1px solid #ccc;background:#fff;border-radius:4px;padding:4px 10px;cursor:pointer">↓</button>
            </li>
          <?php endforeach; ?>
        </ul>
        <button type="submit" class="btn btn-navy" style="margin-top:14px">💾 Lưu thứ tự</button>
      </form>
    </div>
  </div>
</div>
<script>
function moveUp(li){if(li.previousElementSibling)li.parentNode.insertBefore(li,li.previousElementSibling);}
function moveDown(li){if(li.nextElementSibling)li.parentNode.insertBefore(li.nextElementSibling,li);}
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>