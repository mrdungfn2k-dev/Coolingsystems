<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.st-status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700}
.st-draft{background:#f3f4f6;color:#374151}
.st-counting{background:#fef3c7;color:#92400e}
.st-pending{background:#e0f2fe;color:#075985}
.st-approved{background:#d1fae5;color:#065f46}
.st-rejected{background:#fee2e2;color:#991b1b}
.diff-plus{color:#059669;font-weight:700}
.diff-minus{color:#dc2626;font-weight:700}
.diff-zero{color:#6b7280}
</style>

<div class="dash-head">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h1>Kiểm kho (Stocktake)</h1>
            <p style="color:#667085;margin:0">Đối chiếu tồn kho thực tế với số liệu hệ thống.</p>
        </div>
        <?php if ($canCreate): ?>
        <a href="/admin/stocktake/new" class="btn btn-navy">+ Tạo phiếu kiểm kho</a>
        <?php endif; ?>
    </div>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<!-- Danh sách phiếu kiểm kho -->
<div class="panel">
    <div style="overflow:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Tiêu đề</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right">Tổng dòng</th>
                    <th style="text-align:right">Chênh lệch</th>
                    <th>Người tạo</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stMap = ['draft'=>['Nháp','st-draft'],'counting'=>['Đang đếm','st-counting'],
                          'pending_approval'=>['Chờ duyệt','st-pending'],
                          'approved'=>['Đã duyệt','st-approved'],'rejected'=>['Từ chối','st-rejected']];
                foreach ($stocktakes as $st):
                    $badge = $stMap[$st['status']] ?? [$st['status'],'st-draft'];
                    $diffClass = (int)$st['total_diff'] > 0 ? 'diff-plus' : ((int)$st['total_diff'] < 0 ? 'diff-minus' : 'diff-zero');
                ?>
                <tr>
                    <td><strong><?= e($st['code']) ?></strong></td>
                    <td><?= e($st['title']) ?></td>
                    <td><span class="st-status-badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
                    <td style="text-align:right"><?= number_format((int)$st['item_count']) ?></td>
                    <td style="text-align:right">
                        <span class="<?= $diffClass ?>">
                            <?= (int)$st['total_diff'] >= 0 ? '+'.number_format((int)$st['total_diff']) : number_format((int)$st['total_diff']) ?>
                        </span>
                    </td>
                    <td style="font-size:12px"><?= e($st['creator_name'] ?? '—') ?></td>
                    <td style="font-size:12px;color:#667085"><?= e(substr($st['created_at'],0,16)) ?></td>
                    <td>
                        <a href="/admin/stocktake/<?= (int)$st['id'] ?>" class="btn btn-ghost" style="padding:4px 10px;font-size:12px">
                            <?= in_array($st['status'],['draft','counting']) ? 'Tiếp tục đếm' : 'Xem chi tiết' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$stocktakes): ?>
                <tr><td colspan="8" style="padding:30px;text-align:center;color:#667085">Chưa có phiếu kiểm kho nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
