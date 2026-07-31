<?php /** @var array $history @var array $product */ ?>
<?php include __DIR__ . '/../partials/dashboard-head.php'; ?>
<style>
.history-page { max-width: 1200px; margin: 0 auto; padding: 24px 16px; }
.history-page h2 { font-size: 1.4rem; font-weight: 700; margin-bottom: 4px; color: #1e293b; }
.history-page .subtitle { color: #64748b; font-size: .9rem; margin-bottom: 24px; }
.history-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.history-table th { background: #f1f5f9; color: #475569; font-weight: 600; padding: 10px 14px; text-align: left; border-bottom: 2px solid #e2e8f0; }
.history-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.history-table tr:hover td { background: #f8fafc; }
.action-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: .78rem; font-weight: 600; }
.action-create    { background: #dcfce7; color: #166534; }
.action-update    { background: #dbeafe; color: #1e40af; }
.action-inventory { background: #fef9c3; color: #854d0e; }
.action-status    { background: #f3e8ff; color: #6b21a8; }
.restore-btn { padding: 4px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; color: #1e40af; font-size: .8rem; cursor: pointer; transition: .15s; }
.restore-btn:hover { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #1e293b; font-size: .875rem; font-weight: 500; text-decoration: none; margin-bottom: 20px; transition: .15s; }
.btn-back:hover { background: #e2e8f0; }
</style>
<div class="history-page">
    <a class="btn-back" href="/admin/products/<?= e($product['id']) ?>/edit">← Quay lại sản phẩm</a>
    <h2>Lịch sử thay đổi: <?= e($product['name']) ?></h2>
    <div class="subtitle">SKU: <?= e($product['sku'] ?? '—') ?> &nbsp;·&nbsp; Tổng <?= count($history) ?> bản ghi</div>

    <?php if (empty($history)): ?>
        <div style="text-align:center;padding:48px;color:#94a3b8;">Chưa có lịch sử thay đổi nào cho sản phẩm này.</div>
    <?php else: ?>
    <table class="history-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Thời gian</th>
                <th>Loại</th>
                <th>Người thực hiện</th>
                <th>Thông tin tại thời điểm đó</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $idx => $h):
            $actionClass = 'action-' . ($h['action'] ?? 'update');
            $actionLabel = match($h['action'] ?? '') {
                'create'    => 'Tạo mới',
                'update'    => 'Cập nhật SP',
                'inventory' => 'Kho / Giá',
                'status'    => 'Trạng thái',
                default     => $h['action'] ?? 'update'
            };
            $fmtMoney = fn($v) => number_format((int)$v, 0, ',', '.') . 'đ';
        ?>
            <tr>
                <td><?= $idx + 1 ?></td>
                <td style="white-space:nowrap;font-size:.8rem"><?= e($h['changed_at']) ?></td>
                <td><span class="action-badge <?= $actionClass ?>"><?= $actionLabel ?></span></td>
                <td style="font-size:.8rem"><?= e($h['changer_name'] ?? 'Admin') ?></td>
                <td style="font-size:.8rem;color:#475569">
                    <?php if ($h['action'] === 'create'): ?>
                        Tạo ban đầu — Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · Trạng thái: <?= e($h['status'] ?? '') ?>
                    <?php elseif ($h['action'] === 'inventory'): ?>
                        Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · Giá gốc: <?= $fmtMoney($h['original_price'] ?? 0) ?>
                    <?php else: ?>
                        <?= e(mb_substr($h['name'] ?? '', 0, 50)) ?> · Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · <?= e($h['status'] ?? '') ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($h['action'] !== 'create'): ?>
                    <form method="POST" action="/admin/products/<?= e($product['id']) ?>/restore/<?= $h['id'] ?>" onsubmit="return confirm('Khôi phục sản phẩm về bản này?\nThao tác này sẽ ghi đè nội dung hiện tại!')">
                        <?= csrfField() ?>
                        <button type="submit" class="restore-btn">Khôi phục</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../partials/dashboard-foot.php'; ?>
