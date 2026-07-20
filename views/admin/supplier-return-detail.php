<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="/admin/supplier-returns" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
        <div>
            <h1>Chi tiết phiếu trả NCC</h1>
            <p style="color:#667085;margin:0">Mã phiếu: <?= e($return['code']) ?> &mdash; <?= e($return['supplier_name']) ?></p>
        </div>
    </div>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<!-- Thông tin phiếu -->
<div class="panel" style="padding:20px;margin-bottom:16px">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Phiếu nhận hàng</div>
            <div style="font-weight:700;margin-top:4px"><?= e($return['receipt_code']) ?></div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Nhà cung cấp</div>
            <div style="font-weight:700;margin-top:4px"><?= e($return['supplier_name']) ?></div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Trạng thái</div>
            <div style="margin-top:4px">
                <?php
                $statusMap = ['pending'=>['label'=>'Chờ duyệt','color'=>'#f59e0b','bg'=>'#fef3c7'],
                              'approved'=>['label'=>'Đã duyệt','color'=>'#059669','bg'=>'#d1fae5'],
                              'rejected'=>['label'=>'Từ chối','color'=>'#dc2626','bg'=>'#fee2e2']];
                $st = $statusMap[$return['status']] ?? ['label'=>$return['status'],'color'=>'#6b7280','bg'=>'#f3f4f6'];
                ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>"><?= $st['label'] ?></span>
            </div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Ngày tạo</div>
            <div style="font-weight:600;margin-top:4px"><?= e(substr($return['created_at'],0,16)) ?></div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Người tạo</div>
            <div style="font-weight:600;margin-top:4px"><?= e($return['creator_name'] ?? '—') ?></div>
        </div>
        <?php if ($return['approved_by']): ?>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em"><?= $return['status']==='approved'?'Người duyệt':'Người từ chối' ?></div>
            <div style="font-weight:600;margin-top:4px"><?= e($return['approver_name'] ?? '—') ?></div>
        </div>
        <div>
            <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em">Thời gian duyệt</div>
            <div style="font-weight:600;margin-top:4px"><?= e(substr($return['approved_at'],0,16)) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0">
        <div style="font-size:11px;text-transform:uppercase;color:#667085;font-weight:600;letter-spacing:.05em;margin-bottom:6px">Lý do trả hàng</div>
        <div style="background:#f9fafb;border-radius:8px;padding:12px;line-height:1.6"><?= e($return['reason']) ?></div>
    </div>

    <?php if (!empty($return['rejection_reason'])): ?>
    <div style="margin-top:12px;background:#fff5f5;border:1px solid #fecaca;border-radius:8px;padding:12px">
        <strong style="color:#dc2626">Lý do từ chối:</strong> <?= e($return['rejection_reason']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Danh sách sản phẩm trả -->
<div class="panel" style="margin-bottom:16px">
    <div style="padding:16px;border-bottom:1px solid #f0f0f0">
        <h2 style="font-size:15px;margin:0;font-weight:700">Danh sách sản phẩm trả</h2>
    </div>
    <div style="overflow:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align:right">SL đạt (nhập)</th>
                    <th style="text-align:right">SL lỗi (nhập)</th>
                    <th style="text-align:right">SL trả lần này</th>
                    <th style="text-align:right">Trong đó hàng tốt</th>
                    <th style="text-align:right">Trong đó hàng lỗi</th>
                    <th style="text-align:right">Tồn kho hiện tại</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <strong><?= e($item['product_name']) ?></strong>
                        <div style="font-size:11px;color:#667085"><?= e($item['sku']) ?></div>
                    </td>
                    <td style="text-align:right"><?= (int)$item['accepted_qty'] ?></td>
                    <td style="text-align:right"><?= (int)$item['rejected_qty'] ?></td>
                    <td style="text-align:right"><strong><?= (int)$item['return_qty'] ?></strong></td>
                    <td style="text-align:right;color:#059669"><?= (int)$item['returned_accepted_qty'] ?></td>
                    <td style="text-align:right;color:#dc2626"><?= (int)$item['returned_rejected_qty'] ?></td>
                    <td style="text-align:right">
                        <?php $curStock = (int)($item['current_stock'] ?? 0); ?>
                        <?php if ($return['status']==='pending' && $curStock < (int)$item['returned_accepted_qty']): ?>
                            <span style="color:#dc2626;font-weight:700"><?= $curStock ?> ⚠️</span>
                        <?php else: ?>
                            <span><?= $curStock ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Nút duyệt / từ chối (chỉ khi pending) -->
<?php if ($return['status'] === 'pending' && $canApprove): ?>
<div class="panel" style="padding:20px">
    <h2 style="font-size:15px;font-weight:700;margin:0 0 16px">Xử lý phiếu trả</h2>

    <?php 
    // Kiểm tra xem có dòng nào tồn kho không đủ không
    $hasStockWarning = false;
    foreach ($items as $it) {
        if ((int)($it['current_stock']??0) < (int)$it['returned_accepted_qty']) {
            $hasStockWarning = true; break;
        }
    }
    if ($hasStockWarning): ?>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px;margin-bottom:16px">
        ⚠️ <strong>Cảnh báo:</strong> Một số sản phẩm có tồn kho hiện tại thấp hơn số lượng hàng tốt cần trả. 
        Khi duyệt, hệ thống vẫn sẽ xử lý nhưng tồn kho có thể âm. Hãy kiểm tra lại trước khi duyệt.
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <!-- Form Duyệt -->
        <form method="post" action="/admin/supplier-returns/<?= (int)$return['id'] ?>/approve"
              onsubmit="return confirm('Xác nhận DUYỆT phiếu trả này? Hệ thống sẽ tự động trừ tồn kho.')"
              style="display:inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-navy" style="background:#059669;border-color:#059669">
                ✅ Duyệt — Trừ tồn kho
            </button>
        </form>

        <!-- Form Từ chối -->
        <button type="button" class="btn btn-ghost" style="border-color:#dc2626;color:#dc2626"
                onclick="document.getElementById('reject-form').style.display='block';this.style.display='none'">
            ❌ Từ chối phiếu
        </button>
    </div>

    <div id="reject-form" style="display:none;margin-top:16px;background:#fff5f5;border:1px solid #fecaca;border-radius:10px;padding:16px">
        <form method="post" action="/admin/supplier-returns/<?= (int)$return['id'] ?>/reject">
            <?= csrfField() ?>
            <div class="form-group">
                <label><strong>Lý do từ chối *</strong></label>
                <textarea name="rejection_reason" required minlength="5" maxlength="500" rows="3"
                          placeholder="Nhập lý do từ chối phiếu trả này..."></textarea>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn" style="background:#dc2626;color:#fff;border-color:#dc2626">Xác nhận từ chối</button>
                <button type="button" class="btn btn-ghost" 
                        onclick="document.getElementById('reject-form').style.display='none';document.querySelector('[onclick*=reject-form]').style.display='inline-block'">Hủy</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
