<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
    <h1>Trả hàng nhà cung cấp</h1>
    <p style="color:#667085">Tạo yêu cầu trả hàng từ phiếu nhận đã kiểm chất lượng.</p>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<!-- Filter trạng thái -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
    <?php
    $filterStatus = $_GET['status'] ?? 'all';
    $tabs = ['all'=>'Tất cả','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
    foreach ($tabs as $k => $v):
        $active = $filterStatus === $k;
        $cnt = $statusCounts[$k] ?? ($k==='all'?array_sum($statusCounts):0);
    ?>
    <a href="?status=<?= $k ?>" style="padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
        background:<?= $active?'#1a3258':'#f3f4f6' ?>;color:<?= $active?'#fff':'#374151' ?>;border:1px solid <?= $active?'#1a3258':'#e5e7eb' ?>">
        <?= $v ?><?= $cnt>0?" ($cnt)":'' ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Form tạo phiếu trả mới -->
<div class="panel" style="padding:16px;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none"
         onclick="var b=document.getElementById('create-form-body');b.style.display=b.style.display==='none'?'block':'none'">
        <strong style="font-size:14px">➕ Tạo phiếu trả hàng mới</strong>
        <span style="color:#667085;font-size:12px">(click để mở / đóng)</span>
    </div>
    <div id="create-form-body" style="display:<?= $selectedReceipt?'block':'none' ?>;margin-top:16px">
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

        <?php if ($receipt): ?>
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0">
            <h2 style="font-size:15px;margin:0 0 12px">Tạo phiếu trả từ <?= e($receipt['code']) ?></h2>
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
    </div>
</div>

<!-- Danh sách phiếu trả -->
<div class="panel">
    <div style="overflow:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Phiếu nhận</th>
                    <th>Nhà cung cấp</th>
                    <th style="text-align:right">Số lượng</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $statusMap = ['pending'=>['Chờ duyệt','#f59e0b','#fef3c7'],
                              'approved'=>['Đã duyệt','#059669','#d1fae5'],
                              'rejected'=>['Từ chối','#dc2626','#fee2e2']];
                foreach ($items as $i):
                    $st = $statusMap[$i['status']] ?? [$i['status'],'#6b7280','#f3f4f6'];
                ?>
                <tr>
                    <td><strong><?= e($i['code']) ?></strong></td>
                    <td><?= e($i['receipt_code']) ?></td>
                    <td><?= e($i['supplier_name']) ?></td>
                    <td style="text-align:right"><?= (int)$i['total_qty'] ?></td>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($i['reason']) ?>"><?= e(mb_strtoupper(mb_substr($i['reason'],0,50))) ?>...</td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:<?= $st[2] ?>;color:<?= $st[1] ?>"><?= $st[0] ?></span>
                    </td>
                    <td><?= e(substr($i['created_at'],0,16)) ?></td>
                    <td>
                        <a href="/admin/supplier-returns/<?= (int)$i['id'] ?>" class="btn btn-ghost" style="padding:4px 10px;font-size:12px">Xem chi tiết</a>
                        <?php if ($i['status']==='pending' && $canApprove): ?>
                            <a href="/admin/supplier-returns/<?= (int)$i['id'] ?>" class="btn btn-navy" style="padding:4px 10px;font-size:12px;background:#059669;border-color:#059669">Duyệt</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                <tr><td colspan="8" style="padding:25px;text-align:center;color:#667085">Chưa có phiếu trả nhà cung cấp nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
