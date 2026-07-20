<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.st-status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700}
.st-draft{background:#f3f4f6;color:#374151}.st-counting{background:#fef3c7;color:#92400e}
.st-pending{background:#e0f2fe;color:#075985}.st-approved{background:#d1fae5;color:#065f46}.st-rejected{background:#fee2e2;color:#991b1b}
.count-input{width:80px;border:1px solid #d1d5db;border-radius:6px;padding:5px 8px;text-align:right;font-weight:700}
.diff-plus{color:#059669;font-weight:700}.diff-minus{color:#dc2626;font-weight:700}.diff-zero{color:#6b7280}
.row-diff-pos td{background:#f0fdf4}.row-diff-neg td{background:#fff5f5}
</style>

<div class="dash-head">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <a href="/admin/stocktake" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
        <div>
            <h1>Kiểm kho — <?= e($stocktake['code']) ?></h1>
            <p style="color:#667085;margin:0"><?= e($stocktake['title']) ?></p>
        </div>
        <?php
        $stMap=['draft'=>['Nháp','st-draft'],'counting'=>['Đang đếm','st-counting'],
                'pending_approval'=>['Chờ duyệt','st-pending'],
                'approved'=>['Đã duyệt','st-approved'],'rejected'=>['Từ chối','st-rejected']];
        $badge=$stMap[$stocktake['status']]??[$stocktake['status'],'st-draft'];
        ?>
        <span class="st-status-badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
    </div>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<!-- Tóm tắt -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px">
    <?php
    $totalItems = count($items);
    $counted = array_filter($items, fn($i) => $i['actual_qty'] !== null);
    $withDiff = array_filter($items, fn($i) => $i['actual_qty'] !== null && (int)$i['actual_qty'] !== (int)$i['system_qty']);
    $totalDiff = array_sum(array_map(fn($i) => (int)($i['actual_qty']??0) - (int)$i['system_qty'], $counted));
    $cards = [
        ['Tổng sản phẩm', $totalItems, '#1a3258'],
        ['Đã đếm', count($counted), '#059669'],
        ['Còn lại', $totalItems - count($counted), '#f59e0b'],
        ['Có chênh lệch', count($withDiff), '#dc2626'],
        ['Tổng chênh lệch', ($totalDiff >= 0 ? '+' : '').$totalDiff, $totalDiff>=0?'#059669':'#dc2626'],
    ];
    foreach ($cards as [$lbl, $val, $clr]): ?>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px">
        <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em"><?= $lbl ?></div>
        <div style="font-size:22px;font-weight:800;color:<?= $clr ?>;margin-top:4px"><?= $val ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($stocktake['note'])): ?>
<div style="background:#f0f4ff;border:1px solid #c7d7f7;border-radius:8px;padding:12px 16px;margin-bottom:14px">
    <strong>Ghi chú:</strong> <?= e($stocktake['note']) ?>
</div>
<?php endif; ?>

<?php if (!empty($stocktake['rejection_reason'])): ?>
<div style="background:#fff5f5;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:14px">
    <strong style="color:#dc2626">Lý do từ chối:</strong> <?= e($stocktake['rejection_reason']) ?>
</div>
<?php endif; ?>

<!-- Bảng sản phẩm kiểm kho -->
<div class="panel">
    <?php if (in_array($stocktake['status'], ['counting','draft'])): ?>
    <div style="padding:12px 16px;border-bottom:1px solid #f0f0f0;background:#fffbeb">
        <strong>⏱ Đang đếm hàng.</strong> Nhập số lượng thực tế vào cột "Đếm thực tế" rồi bấm <strong>Lưu & tiếp tục</strong>.
        Sau khi đếm xong tất cả, bấm <strong>Gửi duyệt</strong>.
    </div>
    <form method="post" action="/admin/stocktake/<?= (int)$stocktake['id'] ?>/save-counts" id="count-form">
        <?= csrfField() ?>
    <?php endif; ?>

    <div style="overflow:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th style="text-align:right">Tồn hệ thống</th>
                    <th style="text-align:right">Đếm thực tế</th>
                    <th style="text-align:right">Chênh lệch</th>
                    <th>Ghi chú dòng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $idx => $item):
                    $diff = $item['actual_qty'] !== null ? ((int)$item['actual_qty'] - (int)$item['system_qty']) : null;
                    $rowClass = '';
                    if ($diff !== null) $rowClass = $diff > 0 ? 'row-diff-pos' : ($diff < 0 ? 'row-diff-neg' : '');
                ?>
                <tr class="<?= $rowClass ?>">
                    <td style="color:#667085"><?= $idx+1 ?></td>
                    <td>
                        <strong><?= e($item['product_name']) ?></strong>
                        <div style="font-size:11px;color:#667085"><?= e($item['sku']) ?></div>
                    </td>
                    <td style="text-align:right;font-weight:700"><?= number_format((int)$item['system_qty']) ?></td>
                    <td style="text-align:right">
                        <?php if (in_array($stocktake['status'],['counting','draft'])): ?>
                        <input type="number" class="count-input" name="actual[<?= (int)$item['id'] ?>]"
                               min="0" max="999999"
                               value="<?= $item['actual_qty'] !== null ? (int)$item['actual_qty'] : '' ?>"
                               placeholder="—"
                               onchange="updateDiff(this, <?= (int)$item['system_qty'] ?>)">
                        <?php else: ?>
                        <strong><?= $item['actual_qty'] !== null ? number_format((int)$item['actual_qty']) : '—' ?></strong>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right" id="diff-<?= (int)$item['id'] ?>">
                        <?php if ($diff !== null): ?>
                        <span class="<?= $diff>0?'diff-plus':($diff<0?'diff-minus':'diff-zero') ?>">
                            <?= $diff>0?'+'.number_format($diff):number_format($diff) ?>
                        </span>
                        <?php else: ?>
                        <span class="diff-zero">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (in_array($stocktake['status'],['counting','draft'])): ?>
                        <input type="text" name="note[<?= (int)$item['id'] ?>]"
                               value="<?= e($item['note'] ?? '') ?>"
                               style="width:160px;padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:12px"
                               placeholder="Ghi chú...">
                        <?php else: ?>
                        <span style="font-size:12px;color:#667085"><?= e($item['note'] ?? '—') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (in_array($stocktake['status'],['counting','draft'])): ?>
    <div style="padding:16px;border-top:1px solid #f0f0f0;display:flex;gap:10px;flex-wrap:wrap">
        <button type="submit" class="btn btn-ghost" name="action" value="save">💾 Lưu & tiếp tục đếm</button>
        <button type="submit" class="btn btn-navy" name="action" value="submit"
                onclick="return confirm('Gửi duyệt? Sau khi gửi sẽ không chỉnh sửa được nữa.')">
            ✅ Gửi duyệt
        </button>
    </div>
    </form>
    <?php endif; ?>

    <!-- Nút duyệt / từ chối -->
    <?php if ($stocktake['status'] === 'pending_approval' && $canApprove): ?>
    <div style="padding:16px;border-top:1px solid #f0f0f0;background:#f0fdf4">
        <div style="font-size:14px;font-weight:700;margin-bottom:10px">
            ⚠️ Kiểm duyệt phiếu — Duyệt sẽ cập nhật tồn kho cho <?= count($withDiff) ?> sản phẩm có chênh lệch.
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <form method="post" action="/admin/stocktake/<?= (int)$stocktake['id'] ?>/approve"
                  onsubmit="return confirm('Xác nhận DUYỆT? Hệ thống sẽ cập nhật tồn kho theo kết quả kiểm kho.')">
                <?= csrfField() ?>
                <button class="btn btn-navy" style="background:#059669;border-color:#059669">✅ Duyệt — Cập nhật tồn kho</button>
            </form>
            <button class="btn btn-ghost" style="border-color:#dc2626;color:#dc2626"
                    onclick="document.getElementById('reject-box').style.display='block';this.style.display='none'">❌ Từ chối</button>
        </div>
        <div id="reject-box" style="display:none;margin-top:12px">
            <form method="post" action="/admin/stocktake/<?= (int)$stocktake['id'] ?>/reject">
                <?= csrfField() ?>
                <textarea name="rejection_reason" required minlength="5" maxlength="500" rows="2"
                          placeholder="Lý do từ chối..." style="width:100%;margin-bottom:8px"></textarea>
                <button class="btn" style="background:#dc2626;color:#fff;border-color:#dc2626">Xác nhận từ chối</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateDiff(input, sysQty){
    var id=input.name.match(/\d+/)[0];
    var el=document.getElementById('diff-'+id);
    if(!el)return;
    var actual=parseInt(input.value);
    if(isNaN(actual)){el.innerHTML='<span class="diff-zero">—</span>';return;}
    var diff=actual-sysQty;
    var cls=diff>0?'diff-plus':diff<0?'diff-minus':'diff-zero';
    var sign=diff>0?'+':'';
    el.innerHTML='<span class="'+cls+'">'+sign+diff.toLocaleString()+'</span>';
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
