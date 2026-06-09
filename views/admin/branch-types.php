<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.bt-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center; }
.bt-modal-box { background:#fff; border-radius:14px; padding:28px 30px; width:440px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
.bt-modal-box h3 { margin:0 0 20px; font-size:17px; font-weight:700; color:var(--navy); }
.bt-fg { margin-bottom:14px; }
.bt-fg label.lbl { display:block; font-size:11px; font-weight:700; color:#555; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.5px; }
.bt-fg input[type=text], .bt-fg input[type=number] { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
.bt-fg input:focus { border-color:var(--navy); outline:none; box-shadow:0 0 0 3px rgba(26,54,93,0.08); }
.bt-modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; padding-top:16px; border-top:1px solid #eee; }
.bt-btn { padding:9px 16px; background:var(--navy); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; }
.bt-btn.sec { background:#fff; color:var(--navy); border:1px solid var(--navy); }
.bt-btn.del { background:#ef4444; }
.bt-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.bt-badge.on { background:#dcfce7; color:#166534; }
.bt-badge.off { background:#eef2f7; color:#64748b; }
.bt-check { display:flex; align-items:center; gap:8px; font-size:13px; color:#333; font-weight:600 }
.bt-check input { width:auto }
</style>
<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<div class="dash-head">
    <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0">Loại chi nhánh cửa hàng</h1>
    <button class="bt-btn" onclick="openBtModal()">+ Thêm loại</button>
</div>
<div class="panel">
    <table class="tbl" style="width:100%">
        <thead><tr>
            <th style="width:60px">ID</th>
            <th>Tên loại</th>
            <th style="width:90px">Thứ tự</th>
            <th style="width:120px">Trạng thái</th>
            <th style="width:170px">Thao tác</th>
        </tr></thead>
        <tbody>
        <?php if(empty($types)): ?>
            <tr><td colspan="5" style="text-align:center;color:#888;padding:24px">Chưa có loại chi nhánh nào.</td></tr>
        <?php endif; ?>
        <?php foreach($types as $t): ?>
            <tr>
                <td><?= (int)$t['id'] ?></td>
                <td style="font-weight:700"><?= e($t['name']) ?></td>
                <td><?= (int)$t['sort_order'] ?></td>
                <td><?php if($t['is_active']): ?><span class="bt-badge on">Đang dùng</span><?php else: ?><span class="bt-badge off">Ẩn</span><?php endif; ?></td>
                <td>
                    <button class="adm-edit" onclick="openBtEdit(<?= (int)$t['id'] ?>, '<?= e($t['name']) ?>', <?= (int)$t['sort_order'] ?>, <?= $t['is_active']?1:0 ?>)">Sửa</button>
                    <form method="post" action="/admin/branch-types/<?= (int)$t['id'] ?>/delete" style="display:inline" onsubmit="return csConfirmForm(this, 'Xóa loại chi nhánh này?')">
                        <?= csrfField() ?>
                        <button type="submit" class="adm-del">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="bt-modal-overlay" id="btModal">
    <div class="bt-modal-box">
        <h3 id="btModalTitle">Thêm loại chi nhánh</h3>
        <form method="post" id="btForm" action="/admin/branch-types/add">
            <?= csrfField() ?>
            <div class="bt-fg">
                <label class="lbl">Tên loại *</label>
                <input type="text" name="name" id="btName" required maxlength="50" placeholder="VD: Chi nhánh chính">
            </div>
            <div class="bt-fg">
                <label class="lbl">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" id="btSort" value="0" min="0">
            </div>
            <div class="bt-fg">
                <label class="bt-check"><input type="checkbox" name="is_active" id="btActive" checked> Đang sử dụng</label>
            </div>
            <div class="bt-modal-actions">
                <button type="button" class="bt-btn sec" onclick="closeBtModal()">Hủy</button>
                <button type="submit" class="bt-btn">Lưu</button>
            </div>
        </form>
    </div>
</div>
<script>
function openBtModal(){ document.getElementById('btModalTitle').textContent='Thêm loại chi nhánh'; document.getElementById('btForm').action='/admin/branch-types/add'; document.getElementById('btName').value=''; document.getElementById('btSort').value='0'; document.getElementById('btActive').checked=true; document.getElementById('btModal').style.display='flex'; }
function openBtEdit(id, name, sort, active){ document.getElementById('btModalTitle').textContent='Sửa loại chi nhánh'; document.getElementById('btForm').action='/admin/branch-types/'+id+'/edit'; document.getElementById('btName').value=name; document.getElementById('btSort').value=sort; document.getElementById('btActive').checked=!!active; document.getElementById('btModal').style.display='flex'; }
function closeBtModal(){ document.getElementById('btModal').style.display='none'; }
document.getElementById('btModal').addEventListener('click', function(e){ if(e.target===this) closeBtModal(); });
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
