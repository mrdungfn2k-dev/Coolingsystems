<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
    <h1>Trả hàng nhà cung cấp</h1>
    <p style="color:#667085">Tạo yêu cầu trả hàng từ phiếu nhận đã kiểm chất lượng.</p>
</div>
<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<div class="panel" style="padding:16px;margin-bottom:16px">
    <form method="get" action="/admin/supplier-returns">
        <div class="form-group" style="max-width:460px">
            <label>Phiếu nhận đã kiểm</label>
            <select name="receipt" onchange="this.form.submit()">
                <option value="">Chọn phiếu nhận</option>
                <?php foreach ($receipts as $r): ?>
                    <option value="<?= (int)$r['id'] ?>" <?= $selectedReceipt === (int)$r['id'] ? 'selected' : '' ?>>
                        <?= e($r['code']) ?> - <?= e($r['po_code']) ?> - <?= e($r['supplier_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($receipt): ?>
    <div class="panel" style="padding:16px;margin-bottom:16px">
        <h2 style="font-size:16px">Tạo phiếu trả từ <?= e($receipt['code']) ?></h2>
        <form method="post" action="/admin/supplier-returns">
            <?= csrfField() ?>
            <input type="hidden" name="receipt_id" value="<?= (int)$receipt['id'] ?>">
            <div style="overflow:auto">
                <table class="tbl">
                    <thead><tr><th>Sản phẩm</th><th>Đã nhận</th><th>Đạt</th><th>Lỗi</th><th>Đã yêu cầu trả</th><th>Trả lần này</th></tr></thead>
                    <tbody>
                        <?php foreach ($lines as $line): ?>
                            <?php $available = max(0, (int)$line['received_qty'] - (int)$line['returned_qty']); ?>
                            <tr>
                                <td><strong><?= e($line['product_name']) ?></strong><div class="fs-11 text-muted"><?= e($line['sku']) ?></div></td>
                                <td><?= (int)$line['received_qty'] ?></td>
                                <td><?= (int)$line['accepted_qty'] ?></td>
                                <td><?= (int)$line['rejected_qty'] ?></td>
                                <td><?= (int)$line['returned_qty'] ?></td>
                                <td><input type="number" name="qty[<?= (int)$line['id'] ?>]" min="0" max="<?= $available ?>" value="0" <?= $available === 0 ? 'disabled' : '' ?> style="width:120px"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-group" style="margin-top:12px">
                <label>Lý do trả *</label>
                <textarea name="reason" required minlength="5" maxlength="500" rows="3"></textarea>
            </div>
            <button class="btn btn-navy">Tạo phiếu chờ duyệt</button>
        </form>
    </div>
<?php endif; ?>

<div class="panel">
    <div style="overflow:auto">
        <table class="tbl">
            <thead><tr><th>Mã phiếu</th><th>Phiếu nhận</th><th>Nhà cung cấp</th><th>Số lượng</th><th>Lý do</th><th>Trạng thái</th><th>Ngày tạo</th></tr></thead>
            <tbody>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td><strong><?= e($i['code']) ?></strong></td><td><?= e($i['receipt_code']) ?></td><td><?= e($i['supplier_name']) ?></td>
                        <td><?= (int)$i['total_qty'] ?></td><td><?= e($i['reason']) ?></td>
                        <td><?= $i['status'] === 'approved' ? 'Đã duyệt' : ($i['status'] === 'rejected' ? 'Từ chối' : 'Chờ duyệt') ?></td>
                        <td><?= e($i['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?><tr><td colspan="7" style="padding:25px;text-align:center">Chưa có phiếu trả nhà cung cấp.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
