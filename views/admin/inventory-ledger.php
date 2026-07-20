<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.ledger-header{display:flex;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.ledger-info-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 22px;min-width:160px}
.ledger-info-card .lbl{font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em}
.ledger-info-card .val{font-size:22px;font-weight:800;color:#1a3258;margin-top:4px}
.ledger-info-card .val.low{color:#dc2626}
.ledger-info-card .val.ok{color:#059669}
.dir-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700}
.dir-in{background:#d1fae5;color:#059669}
.dir-out{background:#fee2e2;color:#dc2626}
.dir-adj{background:#e0f2fe;color:#0369a1}
.ref-link{color:#1a3258;text-decoration:none;font-weight:600}
.ref-link:hover{text-decoration:underline}
.ledger-filter{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center}
.ledger-filter select,.ledger-filter input{padding:6px 10px;border:1px solid #d1d5db;border-radius:7px;font-size:13px;height:34px}
</style>

<div class="dash-head">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <a href="/admin/inventory" class="btn btn-ghost" style="padding:6px 12px">← Quản lý kho</a>
        <div>
            <h1>Thẻ kho — <?= e($product['name']) ?></h1>
            <p style="color:#667085;margin:0">SKU: <?= e($product['sku']) ?><?= $product['oem_code']?' | OEM: '.e($product['oem_code']):'' ?></p>
        </div>
    </div>
</div>

<!-- Thông tin tổng quan -->
<div class="ledger-header">
    <div class="ledger-info-card">
        <div class="lbl">Tồn hiện tại</div>
        <div class="val <?= (int)$product['stock'] <= (int)$product['min_stock'] && (int)$product['min_stock']>0 ? 'low':'ok' ?>">
            <?= number_format((int)$product['stock']) ?>
        </div>
    </div>
    <div class="ledger-info-card">
        <div class="lbl">Tồn tối thiểu</div>
        <div class="val" style="font-size:18px;color:#374151"><?= number_format((int)$product['min_stock']) ?></div>
    </div>
    <div class="ledger-info-card">
        <div class="lbl">Tồn tối đa</div>
        <div class="val" style="font-size:18px;color:#374151"><?= number_format((int)$product['max_stock']) ?></div>
    </div>
    <div class="ledger-info-card">
        <div class="lbl">Tổng nhập (30 ngày)</div>
        <div class="val" style="font-size:18px;color:#059669">+<?= number_format((int)$stats['in_30d']) ?></div>
    </div>
    <div class="ledger-info-card">
        <div class="lbl">Tổng xuất (30 ngày)</div>
        <div class="val" style="font-size:18px;color:#dc2626">-<?= number_format((int)$stats['out_30d']) ?></div>
    </div>
    <div class="ledger-info-card">
        <div class="lbl">Giá vốn bình quân</div>
        <div class="val" style="font-size:18px;color:#1a3258"><?= number_format((int)$product['cost_price']) ?>đ</div>
    </div>
</div>

<!-- Filter -->
<div class="panel" style="padding:12px 16px;margin-bottom:14px">
    <form method="get" action="/admin/inventory/<?= (int)$product['id'] ?>/ledger">
        <div class="ledger-filter">
            <select name="dir" onchange="this.form.submit()">
                <option value="">Tất cả chiều</option>
                <option value="in" <?= ($dir??'')==='in'?'selected':'' ?>>Nhập kho ↑</option>
                <option value="out" <?= ($dir??'')==='out'?'selected':'' ?>>Xuất kho ↓</option>
            </select>
            <select name="ref_type" onchange="this.form.submit()">
                <option value="">Tất cả loại</option>
                <?php
                $refTypes=['goods_receipt'=>'Nhập từ NCC','order'=>'Đơn bán hàng','order_return'=>'Đổi trả đơn hàng',
                           'supplier_return'=>'Trả hàng NCC','warranty'=>'Bảo hành','manual_adjust'=>'Điều chỉnh thủ công',
                           'stocktake'=>'Kiểm kho'];
                foreach($refTypes as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($refType??'')===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="from" value="<?= e($fromDate??'') ?>" placeholder="Từ ngày">
            <input type="date" name="to" value="<?= e($toDate??'') ?>" placeholder="Đến ngày">
            <button type="submit" class="btn btn-navy" style="height:34px;padding:0 14px">Lọc</button>
            <a href="/admin/inventory/<?= (int)$product['id'] ?>/ledger" class="btn btn-ghost" style="height:34px;padding:0 12px;line-height:34px">Reset</a>
        </div>
    </form>
</div>

<!-- Lịch sử biến động -->
<div class="panel">
    <div style="padding:14px 16px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center">
        <h2 style="font-size:15px;margin:0;font-weight:700">Lịch sử biến động tồn kho</h2>
        <span style="font-size:12px;color:#667085"><?= number_format($totalMovements) ?> bản ghi</span>
    </div>
    <div style="overflow:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th style="width:160px">Thời gian</th>
                    <th style="width:90px">Chiều</th>
                    <th style="text-align:right;width:80px">Số lượng</th>
                    <th style="text-align:right;width:90px">Tồn sau</th>
                    <th>Loại chứng từ</th>
                    <th>Ghi chú</th>
                    <th style="width:120px">Người tạo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$movements): ?>
                <tr><td colspan="7" style="padding:30px;text-align:center;color:#667085">Chưa có biến động tồn kho nào được ghi lại.</td></tr>
                <?php endif; ?>
                <?php foreach ($movements as $m): ?>
                <?php
                    $dirClass = $m['direction']==='in' ? 'dir-in' : ($m['direction']==='out'?'dir-out':'dir-adj');
                    $dirLabel = $m['direction']==='in' ? '↑ Nhập' : ($m['direction']==='out'?'↓ Xuất':'↕ Điều chỉnh');
                    $refLabel = $refTypes[$m['reference_type']] ?? $m['reference_type'];
                    $refUrl = match($m['reference_type']) {
                        'goods_receipt' => '/admin/purchase-quality',
                        'supplier_return' => '/admin/supplier-returns/'.((int)$m['reference_id']),
                        'order', 'order_return' => '/admin/orders/'.((int)$m['reference_id']),
                        'warranty' => '/admin/warranties',
                        'manual_adjust' => '/admin/inventory',
                        'stocktake' => '/admin/stocktake/'.((int)$m['reference_id']),
                        default => null
                    };
                ?>
                <tr>
                    <td style="color:#667085;font-size:12px"><?= e(substr($m['created_at'],0,16)) ?></td>
                    <td><span class="dir-badge <?= $dirClass ?>"><?= $dirLabel ?></span></td>
                    <td style="text-align:right;font-weight:700;color:<?= $m['direction']==='in'?'#059669':'#dc2626' ?>">
                        <?= $m['direction']==='in'?'+':'-' ?><?= number_format((int)$m['quantity']) ?>
                    </td>
                    <td style="text-align:right;font-weight:700"><?= number_format((int)($m['running_total'] ?? 0)) ?></td>
                    <td>
                        <?php if ($refUrl && $m['reference_id']): ?>
                            <a href="<?= $refUrl ?>" class="ref-link"><?= $refLabel ?></a>
                            <?php if ($m['reference_id']): ?><span style="color:#667085;font-size:11px"> #<?= (int)$m['reference_id'] ?></span><?php endif; ?>
                        <?php else: ?>
                            <span><?= e($refLabel) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#374151;font-size:13px"><?= e($m['note'] ?? '—') ?></td>
                    <td style="color:#667085;font-size:12px"><?= e($m['creator_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin:14px 16px">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_filter(['dir'=>$dir??'','ref_type'=>$refType??'','from'=>$fromDate??'','to'=>$toDate??'','page'=>$page-1])) ?>">‹</a>
        <?php endif; ?>
        <span style="padding:0 12px;font-size:12px">Trang <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_filter(['dir'=>$dir??'','ref_type'=>$refType??'','from'=>$fromDate??'','to'=>$toDate??'','page'=>$page+1])) ?>">›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
