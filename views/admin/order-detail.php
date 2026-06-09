<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
$delMap = [
    'pending'    => ['Đang chờ','#eef3fb','#1a3258'],
    'received'   => ['Tiếp nhận','#eef3fb','#1a3258'],
    'delivering' => ['Đang giao','#eef3fb','#1a3258'],
    'delivered'  => ['Đã giao','#eef3fb','#1a3258'],
    'cancelled'  => ['Đã hủy','#eef3fb','#1a3258'],
    'completed'  => ['Đã hoàn thành','#eef3fb','#1a3258'],
    'returned'   => ['Đã trả hàng','#eef3fb','#1a3258']
];
$psLabel = [
    'unpaid'       => ['Chưa thanh toán','#eef3fb','#1a3258'],
    'partial_paid' => ['TT một phần','#eef3fb','#1a3258'],
    'paid'         => ['Đã thanh toán','#eef3fb','#1a3258'],
    'pending_refund' => ['Chưa hoàn tiền','#eef3fb','#1a3258'],
    'refunded'     => ['Đã hoàn tiền','#eef3fb','#1a3258']
];
$ptMap = ['cod'=>'Thanh toán khi nhận hàng','bank_transfer'=>'Chuyển khoản ngân hàng','bank'=>'Chuyển khoản ngân hàng','full_prepay'=>'Chuyển khoản ngân hàng'];

$delStatus = $order['delivery_status'] ?? 'pending';
$payStatus = $order['payment_status'] ?? 'unpaid';
$payType   = $order['payment_type'] ?? 'cod';

$delCfg = $delMap[$delStatus] ?? $delMap['pending'];
$psCfg  = $psLabel[$payStatus] ?? $psLabel['unpaid'];
?>
<style>
.order-header-info {
    display:flex; justify-content:space-between; align-items:center;
    background:#fff; padding:16px 24px; border-radius:12px; margin-bottom:16px;
    box-shadow:0 1px 3px rgba(0,0,0,0.05);
}
.ohi-col { display:flex; flex-direction:column; gap:6px; }
.ohi-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; }
.ohi-val { font-size:14px; font-weight:700; color:#111; }
.ohi-val.blue { color:#1d4ed8; }
.ohi-val.green { color:#059669; }

.order-actions-bar {
    display:flex; justify-content:space-between; align-items:center;
    background:#fff; padding:16px 24px; border-radius:12px; margin-bottom:20px;
    box-shadow:0 1px 3px rgba(0,0,0,0.05);
}
.btn-action {
    display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
    border-radius:6px; font-size:13px; font-weight:700; cursor:pointer;
    border:1px solid var(--line); background:#fff; color:#444; text-decoration:none;
}
.btn-action:hover { background:#f8f9fa; }
.btn-action.red { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
.btn-action.red:hover { background:#fee2e2; }

.nav-tabs {
    display:flex; gap:24px; border-bottom:1px solid var(--line); margin-bottom:20px;
}
.nav-tab {
    padding:12px 0; font-size:14px; font-weight:600; color:#666; cursor:pointer;
    border-bottom:2px solid transparent; transition:all 0.2s;
}
.nav-tab.active { color:#1d4ed8; border-bottom-color:#1d4ed8; }

.tab-content { display:none; background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.tab-content.active { display:block; }

.summary-box { background:#f8f9fa; border-radius:8px; padding:16px; width:320px; margin-left:auto; margin-top:20px; }
.summary-row { display:flex; justify-content:space-between; font-size:13px; margin-bottom:10px; color:#555; }
.summary-row.total { font-weight:800; font-size:16px; color:#dc2626; margin-top:16px; padding-top:16px; border-top:1px solid #ddd; }

.del-select {
    padding:4px 10px; border-radius:16px; font-size:12px; font-weight:700; border:1px solid; outline:none; cursor:pointer;
    appearance:none; padding-right:24px;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231d4ed8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") no-repeat right 6px center / 12px;
}
</style>

<div class="dash-head">
    <?php $backUrl = (isset($__isStaff) && $__isStaff) ? "/staff/orders" : "/admin/orders"; ?>
    <a href="<?= $backUrl ?>" style="font-size:13px; color:#888; text-decoration:none; display:inline-block; margin-bottom:10px;">← Quay lại danh sách</a>
</div>

<?php $flashMsgs = getFlash(); foreach($flashMsgs as $fm): ?>
  <div class="alert alert-<?= e($fm['type']) ?>"><?= e($fm['message']) ?></div>
<?php endforeach; ?>

<!-- Thông tin tổng quan trên cùng -->
<div class="order-header-info">
    <div class="ohi-col">
        <span class="ohi-label">Mã hóa đơn</span>
        <span class="ohi-val blue"><?= e($order['code'] ?? $order['order_code'] ?? 'N/A') ?></span>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Khách hàng</span>
        <span class="ohi-val"><?= e($order['full_name'] ?? '') ?></span>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Hình thức TT</span>
        <span class="ohi-val blue" style="font-size:12px"><?= e($ptMap[$payType] ?? 'COD') ?></span>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Trạng thái TT</span>
        <?php if(in_array($delStatus, ['cancelled','returned','completed'])): ?>
          <span style="background:<?= $psCfg[1] ?>;color:<?= $psCfg[2] ?>;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:700;border:1px solid <?= $psCfg[2] ?>"><?= $psCfg[0] ?></span>
        <?php else: ?>
        <form method="post" action="/admin/orders/<?= $order['id'] ?>/payment-status" style="display:inline">
          <?= csrfField() ?>
            <select name="status" class="del-select" style="background-color:<?= $psCfg[1] ?>;color:<?= $psCfg[2] ?>;border-color:<?= $psCfg[2] ?>" onchange="this.form.submit()">
                <option value="unpaid"         <?= $payStatus==='unpaid'         ?'selected':'' ?>>Chưa thanh toán</option>
                <option value="partial_paid"   <?= $payStatus==='partial_paid'   ?'selected':'' ?>>TT một phần</option>
                <option value="paid"           <?= $payStatus==='paid'           ?'selected':'' ?>>Đã thanh toán</option>
                <option value="pending_refund" <?= $payStatus==='pending_refund' ?'selected':'' ?>>Chưa hoàn tiền</option>
                <option value="refunded"       <?= $payStatus==='refunded'       ?'selected':'' ?>>Đã hoàn tiền</option>
            </select>
        </form>
        <?php endif; ?>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Giao hàng</span>
        <?php if(in_array($delStatus, ['cancelled','returned','completed'])): ?>
          <span style="background:<?= $delCfg[1] ?>;color:<?= $delCfg[2] ?>;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:700;border:1px solid <?= $delCfg[2] ?>"><?= $delCfg[0] ?></span>
        <?php else: ?>
        <form method="post" action="/admin/orders/<?= $order['id'] ?>/delivery-status">
            <?= csrfField() ?>
            <?php
                $isQR = in_array($payType, ['bank_transfer','bank']);
                $canAdvance = ($payStatus === 'paid') || !$isQR || in_array($delStatus, ['pending','received']);
            ?>
            <select name="status" class="del-select" style="background-color:<?= $delCfg[1] ?>;color:<?= $delCfg[2] ?>;border-color:<?= $delCfg[2] ?>" onchange="this.form.submit()">
                <option value="pending"    <?= $delStatus==='pending'    ?'selected':'' ?>>Đang chờ</option>
                <option value="received"   <?= $delStatus==='received'   ?'selected':'' ?>>Tiếp nhận</option>
                <?php if($canAdvance): ?>
                <option value="delivering" <?= $delStatus==='delivering' ?'selected':'' ?>>Đang giao</option>
                <option value="delivered"  <?= $delStatus==='delivered'  ?'selected':'' ?>>Đã giao</option>
                <option value="completed"  <?= $delStatus==='completed'  ?'selected':'' ?>>Đã hoàn thành</option>
                <?php endif; ?>
                <option value="cancelled"  <?= $delStatus==='cancelled'  ?'selected':'' ?>>Hủy đơn</option>
                <option value="returned"   <?= $delStatus==='returned'   ?'selected':'' ?>>Đã trả hàng</option>
            </select>
        </form>
        <?php endif; ?>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Tổng tiền</span>
        <span class="ohi-val"><?= vnd($order['grand_total']) ?></span>
    </div>
</div>

<!-- Thanh Action -->
<div class="order-actions-bar">
    <div style="display:flex; align-items:center; gap:16px">
        <div style="border:1px solid var(--line); border-radius:6px; padding:6px 16px; display:inline-flex; flex-direction:column; align-items:center;">
            <span style="font-size:10px; font-weight:700; color:#888">MÃ HÓA ĐƠN</span>
            <span style="font-size:14px; font-weight:800; color:#111"><?= e($order['code'] ?? $order['order_code'] ?? 'N/A') ?></span>
        </div>
        <button class="btn btn-outline-navy btn-sm" onclick="printOrderInvoice()">IN HÓA ĐƠN</button>
        <?php if(!in_array($delStatus, ['cancelled','returned','completed'])): ?>
        <button class="btn btn-outline-navy btn-sm" onclick="openEditInvoiceModal()">Sửa</button>
        <?php endif; ?>
    </div>
    <div style="display:flex; gap:32px; text-align:right">
        <div>
            <div style="font-size:11px; font-weight:700; color:#888">THỜI GIAN</div>
            <div style="font-size:13px; font-weight:600; color:#111"><?= date('H:i:s d/m/Y', strtotime($order['created_at'])) ?></div>
        </div>
        <div>
            <div style="font-size:11px; font-weight:700; color:#888">NGƯỜI TẠO</div>
            <div style="font-size:13px; font-weight:600; color:#111"><?= e($order['staff_name'] ?? 'Khách hàng') ?></div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="nav-tabs">
    <div class="nav-tab active" onclick="switchTab('chi-tiet')">Chi tiết hàng</div>
    <div class="nav-tab" onclick="switchTab('lich-su')">Lịch sử thanh toán</div>
    <div class="nav-tab" onclick="switchTab('tra-hang')">Trả hàng</div>
    <div class="nav-tab" onclick="switchTab('giao-hang')">Giao hàng</div>
</div>

<!-- Tab: Chi tiết hàng -->
<div id="tab-chi-tiet" class="tab-content active">
    <table class="tbl" style="font-size:13px;width:100%;margin-bottom:20px;border-collapse:collapse">
        <thead>
            <tr style="background:#f8f9fa;border-bottom:1px solid var(--line)">
                <th style="padding:12px;text-align:left;width:60px">Hình ảnh</th>
                <th style="padding:12px;text-align:left">Sản phẩm</th>
                <th style="padding:12px;text-align:center;width:100px">Số lượng</th>
                <th style="padding:12px;text-align:right;width:120px">Đơn giá</th>
                <th style="padding:12px;text-align:right;width:120px">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item): ?>
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:12px">
                    <?php 
                       $imgUrl = $item['snapshot_image'] ?? $item['image_url'] ?? '';
                       if ($imgUrl): 
                    ?>
                      <img src="/uploads/products/<?= e($imgUrl) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #eee" onerror="this.src='/assets/images/no-image.png'">
                    <?php else: ?>
                      <div style="width:48px;height:48px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999">N/A</div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px">
                    <div style="font-weight:600; font-size:13px"><?= e($item['snapshot_name'] ?? $item['product_name'] ?? 'N/A') ?></div>
                    <?php if(!empty($item['snapshot_oem'])): ?><div style="font-size:11px;color:#888">OEM: <?= e($item['snapshot_oem'] ?? '') ?></div><?php endif; ?>
                    <?php
                    $prod = dbGet("SELECT warranty_months FROM products WHERE id=?", [$item['product_id']]);
                    $warrantyMonths = $prod['warranty_months'] ?? 0;
                    $returnDays = 7;
                    if (in_array($order['delivery_status']??'', ['completed', 'delivered'])) {
                        $completedTime = !empty($order['completed_at']) ? strtotime($order['completed_at']) : strtotime($order['updated_at']??'now');
                        $now = time();
                        
                        // Đổi trả
                        $returnEndTime = strtotime("+$returnDays days", $completedTime);
                        if ($now <= $returnEndTime) {
                            $daysLeft = ceil(($returnEndTime - $now) / 86400);
                            $returnStatus = "<span style='color:#059669;font-weight:600'>Đổi trả còn $daysLeft ngày</span>";
                        } else {
                            $returnStatus = "<span style='color:#888'>Hết hạn đổi trả</span>";
                        }
                        
                        // Bảo hành
                        if ($warrantyMonths > 0) {
                            $warrantyEndTime = strtotime("+$warrantyMonths months", $completedTime);
                            if ($now <= $warrantyEndTime) {
                                $daysLeft = ceil(($warrantyEndTime - $now) / 86400);
                                $monthsLeft = floor($daysLeft / 30);
                                $remDays = $daysLeft % 30;
                                $wText = "Còn ";
                                if ($monthsLeft > 0) $wText .= "$monthsLeft tháng ";
                                if ($remDays > 0 || $monthsLeft == 0) $wText .= "$remDays ngày";
                                $warrantyStatus = "<span style='color:#1d4ed8;font-weight:600'>Bảo hành: $wText</span>";
                            } else {
                                $warrantyStatus = "<span style='color:#888'>Hết hạn bảo hành</span>";
                            }
                        } else {
                            $warrantyStatus = "<span style='color:#888'>Không bảo hành</span>";
                        }
                    ?>
                      <div style="font-size:11px;margin-top:6px;background:#f8f9fa;padding:4px 8px;border-radius:4px;display:inline-block">
                         <?= $returnStatus ?> <span style="color:#ddd;margin:0 6px">|</span> <?= $warrantyStatus ?>
                      </div>
                    <?php } else { ?>
                      <div style="font-size:11px;margin-top:6px;color:#666;background:#f8f9fa;padding:4px 8px;border-radius:4px;display:inline-block">
                         Đổi trả: <?= $returnDays ?> ngày <span style="color:#ddd;margin:0 6px">|</span> Bảo hành: <?= $warrantyMonths > 0 ? "$warrantyMonths tháng" : "Không" ?>
                      </div>
                    <?php } ?>
                </td>
                <td style="text-align:center; font-weight:600"><?= (int)$item['quantity'] ?></td>
                <td style="text-align:right"><?= vnd($item['unit_price']) ?></td>
                <td style="text-align:right; font-weight:700; color:#1d4ed8"><?= vnd($item['line_total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div style="background:#f8f9fa;border-radius:6px;padding:16px;width:340px;margin-left:auto">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
          <span style="color:#666">Số mặt hàng / Số lượng:</span>
          <strong><?= count($items) ?> / <?= array_sum(array_column($items, 'quantity')) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
          <span style="color:#666">(1) Tổng tiền hàng:</span>
          <strong><?= vnd($order['subtotal'] ?? 0) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
          <span style="color:#666">(2) Giảm giá:</span>
          <strong style="color:#dc2626">- <?= vnd($order['discount_total'] ?? 0) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px">
          <span style="color:#666">(3) Phí vận chuyển:</span>
          <strong>+ <?= vnd($order['shipping_total'] ?? 0) ?></strong>
        </div>
        <div style="border-top:1px solid #ddd;margin:12px 0"></div>
        <div style="display:flex;justify-content:space-between;color:#dc2626;font-size:16px;font-weight:800">
          <span>TỔNG CỘNG:</span>
          <span><?= vnd($order['grand_total']) ?></span>
        </div>
    </div>
</div>

<!-- Tab: Lịch sử thanh toán -->
<?php
  $payments = dbAll("SELECT * FROM order_payments WHERE order_id=? ORDER BY created_at ASC", [$order['id']]);
?>
<div id="tab-lich-su" class="tab-content">
    <table class="tbl mb-4">
        <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Thời gian</th>
                <th>Loại</th>
                <th>Phương thức</th>
                <th style="text-align:right">Số tiền</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($payments)): ?>
            <?php foreach($payments as $pay): ?>
            <tr>
                <td style="padding:10px 12px;font-size:12px"><?= $pay['payment_code'] ?? $order['code'] ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= date('d/m/Y H:i', strtotime($pay['created_at'])) ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= $pay['payment_type'] ?? 'Thanh toán' ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= $pay['payment_method'] ?? (in_array($order['payment_type'],['bank_transfer','bank'])?'Chuyển khoản':'COD') ?></td>
                <td style="padding:10px 12px;font-size:12px;text-align:right;font-weight:700;color:#059669"><?= vnd($pay['amount'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
              $paidShown = ((($order['payment_status']??'')==='paid') ? intval($order['grand_total']??0) : (($order['paid_amount']??0) > 0 ? intval($order['paid_amount']) : 0));
              $refundShown = ($order['refund_amount']??0) > 0 ? intval($order['refund_amount']) : ((($order['payment_status']??'')==='refunded') ? (($order['paid_amount']??0)>0?intval($order['paid_amount']):intval($order['grand_total']??0)) : 0);
              $anyRow = false;
            ?>
            <?php if($paidShown > 0): $anyRow=true; ?>
            <tr>
                <td style="padding:10px 12px;font-size:12px"><?= $order['code'] ?? '' ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td style="padding:10px 12px;font-size:12px">Thanh toán đơn hàng</td>
                <td style="padding:10px 12px;font-size:12px"><?= ($order['payment_type']??'')==='bank_transfer'?'Chuyển khoản ngân hàng':((($order['payment_type']??''))==='momo'?'MoMo':'COD - Thanh toán khi nhận hàng') ?></td>
                <td style="padding:10px 12px;font-size:12px;text-align:right;font-weight:700;color:#059669"><?= vnd($paidShown) ?></td>
            </tr>
            <?php endif; ?>
            <?php if($refundShown > 0): $anyRow=true; ?>
            <tr>
                <td style="padding:10px 12px;font-size:12px"><?= $order['code'] ?? '' ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= date('d/m/Y H:i', strtotime($order['updated_at'] ?? $order['created_at'])) ?></td>
                <td style="padding:10px 12px;font-size:12px">Hoàn tiền</td>
                <td style="padding:10px 12px;font-size:12px"><?= ($order['payment_type']??'')==='bank_transfer'?'Chuyển khoản ngân hàng':'Hoàn tiền' ?></td>
                <td style="padding:10px 12px;font-size:12px;text-align:right;font-weight:700;color:#dc2626">-<?= vnd($refundShown) ?></td>
            </tr>
            <?php endif; ?>
            <?php if(!$anyRow): ?>
            <tr><td colspan="5" style="text-align:center; padding:20px; color:#888">Chưa có giao dịch nào</td></tr>
            <?php endif; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="display:flex;justify-content:space-between;padding:14px 16px;background:#f8f9fa;border-radius:8px;margin-top:12px">
        <div>
            <?php $psMap = ['paid'=>['ĐÃ THANH TOÁN','#059669'],'partial_paid'=>['ĐÃ THANH TOÁN MỘT PHẦN','#2563eb'],'refunded'=>['ĐÃ HOÀN TIỀN','#7e22ce'],'pending_refund'=>['CHỜ HOÀN TIỀN','#d97706'],'unpaid'=>['CHƯA THANH TOÁN','#4b5563']]; $psInfo = $psMap[$order['payment_status']??'unpaid'] ?? ['CHƯA THANH TOÁN','#4b5563']; ?>
            <div style="font-size:12px;font-weight:700;color:<?= $psInfo[1] ?>">
                ● <?= $psInfo[0] ?>
            </div>
        </div>
    </div>
</div>

<!-- Tab: Trả hàng -->
<?php $returns = dbAll("SELECT * FROM order_returns WHERE order_id=? ORDER BY created_at DESC", [$order['id']]); ?>
<div id="tab-tra-hang" class="tab-content">
    <?php if(empty($returns)): ?>
        <div style="text-align:center; padding:40px; color:#888">Đơn hàng này chưa có yêu cầu trả hàng.</div>
    <?php else: ?>
        <?php foreach($returns as $rt): ?>
        <div style="border:1px solid var(--line); border-radius:8px; padding:16px; margin-bottom:16px">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px; border-bottom:1px solid #eee; padding-bottom:12px">
                <div>
                    <div style="font-weight:700; font-size:15px">Yêu cầu trả hàng #<?= $rt['id'] ?></div>
                    <div style="font-size:12px; color:#666">Gửi lúc: <?= date('H:i d/m/Y', strtotime($rt['created_at'])) ?></div>
                </div>
                <div>
                    <span style="background:<?= $rt['status']==='approved' ? '#ecfdf5' : ($rt['status']==='rejected' ? '#fef2f2' : '#fef3c7') ?>; color:<?= $rt['status']==='approved' ? '#059669' : ($rt['status']==='rejected' ? '#dc2626' : '#d97706') ?>; padding:4px 12px; border-radius:6px; font-size:12px; font-weight:700">
                        <?= $rt['status']==='approved' ? 'Đã duyệt' : ($rt['status']==='rejected' ? 'Từ chối' : 'Chờ xác nhận') ?>
                    </span>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; font-size:13px">
                <div>
                    <div style="color:#888; margin-bottom:4px">Lý do trả hàng:</div>
                    <div style="font-weight:600"><?= nl2br(e($rt['reason'])) ?></div>
                    <?php if($rt['image_path']): ?>
                        <div style="margin-top:10px">
                            <a href="<?= e($rt['image_path']) ?>" target="_blank"><img src="<?= e($rt['image_path']) ?>" style="max-width:200px; border-radius:6px; border:1px solid #ddd"></a>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div style="color:#888; margin-bottom:4px">Thông tin liên hệ:</div>
                    <div><strong>SĐT:</strong> <?= e($rt['contact_phone']) ?></div>
                    <div><strong>Email:</strong> <?= e($rt['contact_email']) ?></div>
                    <div><strong>Địa chỉ lấy hàng:</strong> <?= e($rt['contact_address']) ?></div>
                </div>
            </div>
            <?php if($rt['status'] === 'pending'): ?>
            <div style="margin-top:16px; display:flex; gap:10px">
                <form method="post" action="/admin/orders/<?= $order['id'] ?>/return/<?= $rt['id'] ?>">
                    <?= csrfField() ?>
                    <button type="submit" name="action" value="approve" class="btn btn-navy" style="background:#059669;color:#fff;border-color:#059669" onclick="return csConfirmBtn(this,'Duyệt trả hàng và trừ doanh thu?');">Chấp nhận trả hàng</button>
                    <button type="submit" name="action" value="reject" class="btn btn-outline-navy" style="color:#dc2626;border-color:#dc2626" onclick="return csConfirmBtn(this,'Từ chối yêu cầu này?');">Từ chối</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Tab: Giao hàng -->
<div id="tab-giao-hang" class="tab-content">
    <div style="font-size:14px; font-weight:700; margin-bottom:16px">Thông tin người nhận</div>
    <div style="background:#f8f9fa; border-radius:8px; padding:16px; font-size:13px; line-height:1.6; margin-bottom:24px">
        <strong><?= e($order['shipping_full_name'] ?? $order['ship_name'] ?? $order['full_name']) ?></strong><br>
        SĐT: <?= e($order['shipping_phone'] ?? $order['ship_phone'] ?? $order['phone']) ?><br>
        Địa chỉ: <?= e($order['shipping_detail'] ?? $order['ship_address'] ?? '') ?><?php if(!empty($order['shipping_district'])): ?>, <?= e($order['shipping_district'] ?? '') ?><?php endif; ?><?php if(!empty($order['shipping_province'])): ?>, <?= e($order['shipping_province'] ?? '') ?><?php endif; ?>
    </div>
    
    <!-- Lý do hủy đơn (khách hàng) -->
    <?php if(($order['delivery_status']??'')==='cancelled' && (!empty($order['cancel_reason']) || !empty($order['cancel_images']))): ?>
    <div style="font-size:14px; font-weight:700; margin-bottom:12px; color:#dc2626">Lý do hủy đơn từ khách hàng</div>
    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:14px; margin-bottom:24px">
        <?php if(!empty($order['cancel_reason'])): ?><div style="font-size:13px; line-height:1.6; color:#991b1b; margin-bottom:10px"><?= nl2br(e($order['cancel_reason'])) ?></div><?php endif; ?>
        <?php $__ci = json_decode($order['cancel_images'] ?? '[]', true) ?: []; if($__ci): ?>
        <div style="display:flex; flex-wrap:wrap; gap:8px">
            <?php foreach($__ci as $__img): ?>
            <a href="/uploads/cancellations/<?= e($__img) ?>" target="_blank"><img src="/uploads/cancellations/<?= e($__img) ?>" style="width:84px; height:84px; object-fit:cover; border-radius:6px; border:1px solid #fecaca"></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Ghi chú khách hàng -->
    <?php if(!empty($order['customer_note'])): ?>
    <div style="font-size:14px; font-weight:700; margin-bottom:12px"> Ghi chú của khách hàng</div>
    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px; font-size:13px; line-height:1.6; margin-bottom:24px; color:#92400e">
        <?= nl2br(e($order['customer_note'] ?? '')) ?>
    </div>
    <?php endif; ?>

    <!-- Ghi chú nhân viên -->
    <?php if(!empty($order['staff_note'])): ?>
    <div style="font-size:14px; font-weight:700; margin-bottom:12px"> Ghi chú nhân viên</div>
    <div style="background:#f0f4ff; border:1px solid #d0d8f0; border-radius:8px; padding:14px; font-size:13px; line-height:1.6; margin-bottom:24px; color:#1e40af">
        <?= nl2br(e($order['staff_note'] ?? '')) ?>
    </div>
    <?php endif; ?>

    <div style="font-size:14px; font-weight:700; margin-bottom:16px">Trạng thái hiện tại</div>
    <span style="background:<?= $delCfg[1] ?>; color:<?= $delCfg[2] ?>; padding:6px 16px; border-radius:20px; font-weight:700; font-size:13px; border:1px solid <?= $delCfg[2] ?>"><?= $delCfg[0] ?></span>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}
</script>


<!-- Hidden invoice print area -->
<div id="adminInvoicePrintArea" style="display:none">
  <?php
    // ===== Thông tin công ty phát hành hóa đơn (sửa tại đây nếu cần) =====
    // Thông tin công ty trên hóa đơn — lấy từ Cài đặt hệ thống (admin nhập)
    $coName = ((dbGet("SELECT value FROM system_config WHERE key='company_name'") ?: [])['value'] ?? '');
    if (trim($coName) === '') $coName = 'CÔNG TY CỔ PHẦN HVAC CORPORATION VIỆT NAM';
    $coAddr = ((dbGet("SELECT value FROM system_config WHERE key='contact_address'") ?: [])['value'] ?? '');
    $coPhone = ((dbGet("SELECT value FROM system_config WHERE key='site_phone'") ?: [])['value'] ?? '');
    $__bankName = ((dbGet("SELECT value FROM system_config WHERE key='payment_bank_name'") ?: [])['value'] ?? '');
    $__bankAcc = ((dbGet("SELECT value FROM system_config WHERE key='payment_account_number'") ?: [])['value'] ?? '');
    $coBank = trim($__bankAcc !== '' ? ($__bankAcc.' - '.$__bankName) : $__bankName);
    $invNo = 'HD' . str_pad((string)intval($order['id'] ?? 0), 6, '0', STR_PAD_LEFT);
    $invTs = strtotime($order['created_at'] ?? 'now');
    if (!empty($invoice)) {
      $buyerName = (($invoice['invoice_type']??'')==='business') ? ($invoice['company_name']??'') : ($invoice['buyer_name']??'');
      $buyerPhone = $invoice['phone'] ?? ($order['shipping_phone']??'');
      $buyerAddr = trim(($invoice['address']??'').' '.($invoice['ward']??'').' '.($invoice['province']??''));
    } else {
      $buyerName = $order['full_name'] ?? $order['shipping_full_name'] ?? '';
      $buyerPhone = $order['phone'] ?? $order['shipping_phone'] ?? '';
      $buyerAddr = trim(($order['shipping_detail']??'').(!empty($order['shipping_district'])?', '.$order['shipping_district']:'').(!empty($order['shipping_province'])?', '.$order['shipping_province']:''));
    }
    if (trim($buyerName)==='') $buyerName = $order['shipping_full_name'] ?? '';
    $invSub=0; $invQty=0; foreach($items as $__it){ $invSub += intval($__it['unit_price']??0)*intval($__it['quantity']??1); $invQty += intval($__it['quantity']??1); }
    $invDiscount = intval($order['discount_total'] ?? 0);
    $invTotal = intval($order['grand_total'] ?? ($invSub - $invDiscount));
  ?>
  <div style="max-width:760px;margin:0 auto;padding:28px 34px;font-family:'Times New Roman',Times,serif;color:#000;font-size:14px;line-height:1.45">
    <div style="margin-bottom:8px">
      <div style="font-weight:bold;font-size:15px;text-transform:uppercase"><?= e($coName) ?></div>
      <div><b>ĐỊA CHỈ:</b> <?= e($coAddr) ?></div>
      <div><b>SĐT:</b> <?= e($coPhone) ?></div>
      <div><b>SỐ TÀI KHOẢN:</b> <?= e($coBank) ?></div>
    </div>
    <div style="text-align:center;margin:16px 0 2px">
      <div style="font-weight:bold;font-size:21px;letter-spacing:1px">HÓA ĐƠN BÁN HÀNG</div>
      <div style="font-size:13px">Số hóa đơn: <b><?= e($invNo) ?></b></div>
    </div>
    <div style="text-align:right;font-style:italic;font-size:13px;margin-bottom:12px">Ngày <?= date('d', $invTs) ?> tháng <?= date('m', $invTs) ?> năm <?= date('Y', $invTs) ?></div>
    <div style="margin-bottom:3px"><b>Khách hàng:</b> <?= e($buyerName) ?></div>
    <div style="margin-bottom:3px"><b>SĐT:</b> <?= e($buyerPhone) ?></div>
    <div style="margin-bottom:12px"><b>Địa chỉ:</b> <?= e($buyerAddr) ?></div>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead><tr>
        <th style="border:1px solid #000;padding:6px 4px;width:34px">STT</th>
        <th style="border:1px solid #000;padding:6px 8px;text-align:left">Tên hàng</th>
        <th style="border:1px solid #000;padding:6px 4px;width:46px">DVT</th>
        <th style="border:1px solid #000;padding:6px 4px;width:40px">SL</th>
        <th style="border:1px solid #000;padding:6px 8px;width:104px;text-align:right">Đơn giá</th>
        <th style="border:1px solid #000;padding:6px 8px;width:116px;text-align:right">Thành tiền</th>
      </tr></thead>
      <tbody>
      <?php $stt=1; foreach($items as $it): $lt=intval($it['unit_price']??0)*intval($it['quantity']??1); ?>
      <tr>
        <td style="border:1px solid #000;padding:5px 4px;text-align:center"><?= $stt++ ?></td>
        <td style="border:1px solid #000;padding:5px 8px"><?= e($it['product_name'] ?? $it['snapshot_name'] ?? '') ?></td>
        <td style="border:1px solid #000;padding:5px 4px;text-align:center">Cái</td>
        <td style="border:1px solid #000;padding:5px 4px;text-align:center"><?= intval($it['quantity']??1) ?></td>
        <td style="border:1px solid #000;padding:5px 8px;text-align:right"><?= number_format(intval($it['unit_price']??0)) ?></td>
        <td style="border:1px solid #000;padding:5px 8px;text-align:right"><?= number_format($lt) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="3" style="border:1px solid #000;padding:6px 8px;font-weight:bold">Tổng cộng:</td>
        <td style="border:1px solid #000;padding:6px 4px;text-align:center;font-weight:bold"><?= $invQty ?></td>
        <td style="border:1px solid #000;background:#f2f2f2"></td>
        <td style="border:1px solid #000;padding:6px 8px;text-align:right;font-weight:bold"><?= number_format($invSub) ?></td>
      </tr>
      </tbody>
    </table>
    <div style="margin-top:10px">
      <div style="margin-bottom:2px"><b>Tổng tiền hàng:</b> <?= number_format($invSub) ?></div>
      <div style="margin-bottom:2px"><b>Chiết khấu hóa đơn:</b> <?= ($invDiscount>0?'- ':'').number_format($invDiscount) ?></div>
      <div style="margin-bottom:2px"><b>Phí vận chuyển:</b> + <?= number_format(intval($order['shipping_total'] ?? 0)) ?></div>
      <div style="font-size:15px;margin-bottom:2px"><b>Tổng thanh toán:</b> <b><?= number_format($invTotal) ?></b></div>
      <div style="font-style:italic;margin-bottom:2px"><b>Tổng thanh toán bằng chữ:</b> <?= e(docSoThanhChu($invTotal)) ?></div>
      <div><b>Ghi chú:</b> <?= e($order['customer_note'] ?? '') ?></div>
    </div>
    <div style="text-align:right;font-style:italic;font-size:13px;margin-top:18px">Ngày <?= date('d', $invTs) ?> tháng <?= date('m', $invTs) ?> năm <?= date('Y', $invTs) ?></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:4px;text-align:center">
      <div><div style="font-weight:bold">Người mua hàng</div><div style="font-size:12px;font-style:italic">(Ký, ghi rõ họ tên)</div></div>
      <div><div style="font-weight:bold">Người bán hàng</div><div style="font-size:12px;font-style:italic">(Ký, ghi rõ họ tên)</div></div>
    </div>
  </div>
</div>


<!-- Modal Sửa thông tin hóa đơn -->
<div id="editInvoiceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center">
  <div style="background:#fff;border-radius:12px;padding:24px;max-width:600px;width:90%;max-height:85vh;overflow-y:auto;position:relative">
    <button onclick="closeEditInvoiceModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#666">&times;</button>
    <h3 style="margin:0 0 16px;font-size:18px;color:var(--navy)">Sửa thông tin hóa đơn</h3>
    <form id="editInvoiceForm" onsubmit="return saveInvoiceEdit(event)">
      <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Loại hình</label>
          <select name="invoice_type" class="js-cdd" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
            <option value="personal" <?= ($invoice['invoice_type']??'personal')==='personal'?'selected':'' ?>>Cá nhân</option>
            <option value="business" <?= ($invoice['invoice_type']??'')==='business'?'selected':'' ?>>Tổ chức</option>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Tên người mua / Công ty</label>
          <input type="text" name="buyer_name" value="<?= e($invoice['buyer_name']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Họ tên / Tên CT" maxlength="50">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Mã số thuế</label>
          <input type="text" name="tax_code" value="<?= e($invoice['tax_code']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Nhập MST" maxlength="13" pattern="([0-9]{10}|[0-9]{13})" title="Mã số thuế phải là 10 hoặc 13 chữ số" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">CCCD/CMND</label>
          <input type="text" name="id_number" value="<?= e($invoice['id_number']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Số CCCD" maxlength="12" pattern="[0-9]{12}" title="CCCD phải đủ 12 số" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
      </div>
      <div class="form-group" style="margin-top:10px">
        <label style="font-weight:600;font-size:13px">Địa chỉ</label>
        <input type="text" name="inv_address" value="<?= e($invoice['address']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Địa chỉ xuất hóa đơn" maxlength="50">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Tỉnh/Thành phố</label>
          <input type="text" name="province" value="<?= e($invoice['province']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" maxlength="50">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Phường/Xã</label>
          <input type="text" name="ward" value="<?= e($invoice['ward']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" maxlength="50">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Email</label>
          <input type="email" name="inv_email" value="<?= e($invoice['email']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="email@gmail.com" maxlength="50" pattern="[a-zA-Z0-9._%+\-]+@gmail\.com" title="Vui lòng nhập đúng định dạng @gmail.com (VD: example@gmail.com)">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Số điện thoại</label>
          <input type="tel" name="inv_phone" value="<?= e($invoice['phone']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="0912345678" maxlength="10" pattern="0[1-9][0-9]{8}" title="SĐT phải gồm 10 chữ số, bắt đầu từ 01 đến 09" oninput="let v=this.value.replace(/[^0-9]/g,''); if(v.length>0 && v[0]!=='0') v=''; if(v.length>1 && v[1]==='0') v='0'; this.value=v;">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Ngân hàng</label>
          <select name="bank_name" class="js-cdd" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
            <option value="">Chọn ngân hàng</option>
            <?php foreach(['Vietcombank','Techcombank','BIDV','VietinBank','MB Bank','ACB','Sacombank','VPBank','TPBank','HDBank','SHB','Agribank'] as $bk): ?>
            <option value="<?= $bk ?>" <?= ($invoice['bank_name']??'')===$bk?'selected':'' ?>><?= $bk ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Số TK ngân hàng</label>
          <input type="text" name="bank_account" value="<?= e($invoice['bank_account']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" maxlength="50" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
      </div>
      <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeEditInvoiceModal()" class="btn btn-outline-navy btn-sm">Hủy</button>
        <button type="submit" class="btn btn-navy btn-sm">Lưu thông tin</button>
      </div>
      <div id="invoiceEditStatus" style="margin-top:8px;font-size:12px"></div>
    </form>
  </div>
</div>

<script>
function openEditInvoiceModal() {
  document.getElementById('editInvoiceModal').style.display = 'flex';
}
function closeEditInvoiceModal() {
  document.getElementById('editInvoiceModal').style.display = 'none';
}
function saveInvoiceEdit(e) {
  e.preventDefault();
  var form = document.getElementById('editInvoiceForm');
  
  var email = form.querySelector('[name="inv_email"]').value.trim();
  if (email && !email.endsWith('@gmail.com')) {
      document.getElementById('invoiceEditStatus').innerHTML = '<span style="color:red">❌ Email bắt buộc phải có đuôi @gmail.com (không được sai chính tả)</span>';
      return false;
  }
  
  var data = new FormData(form);
  var userId = <?= json_encode($order['user_id']) ?>;
  fetch('/admin/users/' + userId + '/invoice-info', {method:'POST', body:data})
    .then(r => r.json())
    .then(d => {
      if (d.ok) {
        document.getElementById('invoiceEditStatus').innerHTML = '<span style="color:green">✅ Đã lưu thành công!</span>';
        setTimeout(function(){ if(window.csNav){ csNav(location.pathname); } else { location.reload(); } }, 1000);
      } else {
        document.getElementById('invoiceEditStatus').innerHTML = '<span style="color:red">❌ Lỗi: ' + (d.error||'Không xác định') + '</span>';
      }
    })
    .catch(err => {
      document.getElementById('invoiceEditStatus').innerHTML = '<span style="color:red">❌ Lỗi kết nối</span>';
    });
  return false;
}
</script>

<!-- Custom Popup -->
<div id="customConfirmModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;justify-content:center;align-items:center">
  <div style="background:#fff;border-radius:12px;padding:28px 32px;max-width:420px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center">
    <div style="width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px">⚠️</div>
    <h3 id="confirmTitle" style="font-size:17px;font-weight:700;color:#111;margin:0 0 10px">Xác nhận hành động</h3>
    <p id="confirmMsg" style="font-size:13px;color:#666;margin:0 0 24px;line-height:1.6"></p>
    <div style="display:flex;gap:10px;justify-content:center">
      <button onclick="cancelConfirm()" style="padding:9px 24px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#444;font-weight:600;cursor:pointer;font-size:13px">Huỷ</button>
      <button id="confirmOkBtn" onclick="doConfirm()" style="padding:9px 24px;border-radius:6px;border:none;background:#ef4444;color:#fff;font-weight:700;cursor:pointer;font-size:13px">Xác nhận</button>
    </div>
  </div>
</div>

<script>
var _confirmCallback = null;
function showConfirm(msg, cb) {
  document.getElementById('confirmMsg').textContent = msg;
  document.getElementById('customConfirmModal').style.display = 'flex';
  _confirmCallback = cb;
}
function doConfirm() {
  document.getElementById('customConfirmModal').style.display = 'none';
  if (_confirmCallback) _confirmCallback();
  _confirmCallback = null;
}
function cancelConfirm() {
  document.getElementById('customConfirmModal').style.display = 'none';
  _confirmCallback = null;
}
function confirmCancelOrder(e) {
  e.preventDefault();
  var form = e.target;
  showConfirm('Xác nhận hủy đơn hàng này? Khách hàng sẽ nhận được thông báo.', function() { form.submit(); });
  return false;
}
</script>

<script>
function printOrderInvoice() {
  var area = document.getElementById('adminInvoicePrintArea');
  if (!area) { alert('Không tìm thấy nội dung hóa đơn'); return; }
  var win = window.open('', '_blank', 'width=900,height=700');
  win.document.write('<html><head><title>Hóa đơn</title>');
  win.document.write('<style>body{font-family:Arial,sans-serif;margin:0;padding:0}@page{margin:1cm}@media print{body{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}</style>');
  win.document.write('</head><body>');
  win.document.write(area.innerHTML);
  win.document.write('</body></html>');
  win.document.close();
  setTimeout(function(){ win.print(); }, 500);
}
</script>



<!-- Admin Cancel Order -->
<?php if (($order['delivery_status']??'') === 'pending'): ?>
<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:20px 24px;margin-top:16px">
  <div style="padding:14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px">
    <h4 style="margin:0 0 10px;color:#991b1b;font-size:14px;font-weight:700">⚠ Hủy đơn hàng</h4>
    <form method="post" action="/admin/orders/<?= $order['id'] ?>/cancel" onsubmit="return confirmCancelOrder(event)">
      <?= csrfField() ?>
      <input type="text" name="cancel_reason" placeholder="Lý do hủy đơn..." required style="width:100%;padding:8px 12px;border:1px solid #fca5a5;border-radius:6px;margin-bottom:10px;font-size:13px;box-sizing:border-box">
      <button type="submit" style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px">Hủy đơn & Thông báo KH</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>


