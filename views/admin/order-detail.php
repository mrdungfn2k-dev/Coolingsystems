<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
$delMap = [
    'pending'    => ['Đang chờ','#f3f4f6','#4b5563'],
    'received'   => ['Tiếp nhận','#eff6ff','#1d4ed8'],
    'delivering' => ['Đang giao','#fef3c7','#d97706'],
    'delivered'  => ['Đã giao','#ecfdf5','#059669'],
    'cancelled'  => ['Đã hủy','#fef2f2','#dc2626'],
    'completed'  => ['Đã hoàn thành','#d1fae5','#047857'],
    'returned'   => ['Đã trả hàng','#f3e8ff','#7e22ce']
];
$psLabel = [
    'unpaid'       => ['Chưa thanh toán','#f3f4f6','#4b5563'],
    
    'paid'         => ['Đã thanh toán','#ecfdf5','#059669'],
    'refunded'     => ['Đã hoàn tiền','#f3e8ff','#7e22ce']
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
        <span class="ohi-val"><?= e($order['full_name']) ?></span>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Hình thức TT</span>
        <span class="ohi-val blue" style="font-size:12px"><?= e($ptMap[$payType] ?? 'COD') ?></span>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Trạng thái TT</span>
        <form method="post" action="/admin/orders/<?= $order['id'] ?>/payment-status" style="display:inline">
          <?= csrfField() ?>
            <select name="status" class="del-select" style="background-color:<?= $psCfg[1] ?>;color:<?= $psCfg[2] ?>;border-color:<?= $psCfg[2] ?>" onchange="this.form.submit()">
                <option value="unpaid"  <?= $payStatus==='unpaid'  ?'selected':'' ?>>Chưa thanh toán</option>
                <option value="paid"    <?= $payStatus==='paid'    ?'selected':'' ?>>Đã thanh toán</option>
            </select>
        </form>
    </div>
    <div class="ohi-col">
        <span class="ohi-label">Giao hàng</span>
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
        <button class="btn btn-outline-navy btn-sm" onclick="openEditInvoiceModal()">Sửa</button>
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
                    <?php if(!empty($item['snapshot_oem'])): ?><div style="font-size:11px;color:#888">OEM: <?= e($item['snapshot_oem']) ?></div><?php endif; ?>
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
                <td style="padding:10px 12px;font-size:12px"><?= $pay['payment_method'] ?? ($order['payment_type']==='bank_transfer'?'Chuyển khoản':'COD') ?></td>
                <td style="padding:10px 12px;font-size:12px;text-align:right;font-weight:700;color:#059669"><?= vnd($pay['amount'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php elseif(($order['payment_status'] ?? '') === 'paid'): ?>
            <tr>
                <td style="padding:10px 12px;font-size:12px"><?= $order['code'] ?? '' ?></td>
                <td style="padding:10px 12px;font-size:12px"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td style="padding:10px 12px;font-size:12px">Thanh toán đơn hàng</td>
                <td style="padding:10px 12px;font-size:12px"><?= $order['payment_type']==='bank_transfer'?'Chuyển khoản ngân hàng':($order['payment_type']==='momo'?'MoMo':'COD - Thanh toán khi nhận hàng') ?></td>
                <td style="padding:10px 12px;font-size:12px;text-align:right;font-weight:700;color:#059669"><?= vnd($order['grand_total'] ?? 0) ?></td>
            </tr>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; padding:20px; color:#888">Chưa có giao dịch nào</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="display:flex;justify-content:space-between;padding:14px 16px;background:#f8f9fa;border-radius:8px;margin-top:12px">
        <div>
            <div style="font-size:12px;font-weight:700;color:<?= ($order['payment_status']??'')=='paid'?'#059669':'#4b5563' ?>">
                ● <?= ($order['payment_status']??'')==='paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' ?>
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
                    <button type="submit" name="action" value="approve" class="btn btn-navy" style="background:#059669;color:#fff;border-color:#059669" onclick="return confirm('Duyệt trả hàng và trừ doanh thu?');">Chấp nhận trả hàng</button>
                    <button type="submit" name="action" value="reject" class="btn btn-outline-navy" style="color:#dc2626;border-color:#dc2626" onclick="return confirm('Từ chối yêu cầu này?');">Từ chối</button>
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
        Địa chỉ: <?= e($order['shipping_detail'] ?? $order['ship_address'] ?? '') ?><?php if(!empty($order['shipping_district'])): ?>, <?= e($order['shipping_district']) ?><?php endif; ?><?php if(!empty($order['shipping_province'])): ?>, <?= e($order['shipping_province']) ?><?php endif; ?>
    </div>
    
    <!-- Ghi chú khách hàng -->
    <?php if(!empty($order['customer_note'])): ?>
    <div style="font-size:14px; font-weight:700; margin-bottom:12px"> Ghi chú của khách hàng</div>
    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px; font-size:13px; line-height:1.6; margin-bottom:24px; color:#92400e">
        <?= nl2br(e($order['customer_note'])) ?>
    </div>
    <?php endif; ?>

    <!-- Ghi chú nhân viên -->
    <?php if(!empty($order['staff_note'])): ?>
    <div style="font-size:14px; font-weight:700; margin-bottom:12px"> Ghi chú nhân viên</div>
    <div style="background:#f0f4ff; border:1px solid #d0d8f0; border-radius:8px; padding:14px; font-size:13px; line-height:1.6; margin-bottom:24px; color:#1e40af">
        <?= nl2br(e($order['staff_note'])) ?>
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
  <div style="max-width:800px;margin:0 auto;padding:40px;font-family:Arial,sans-serif">
    <div style="text-align:center;margin-bottom:30px">
      <h1 style="font-size:24px;color:#1a3258;margin:0">HÓA ĐƠN BÁN HÀNG</h1>
      <p style="color:#888;font-size:13px;margin:4px 0">Cooling Parts & Service</p>
      <p style="color:#888;font-size:12px">Ngày: <?= date('d/m/Y', strtotime($order['created_at'])) ?> | Mã đơn: <?= e($order['code'] ?? $order['order_code'] ?? '') ?></p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;border:1px solid #ddd;padding:16px;border-radius:6px">
      <div>
        <h3 style="font-size:14px;color:#1a3258;margin:0 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px">Thông tin người mua</h3>
        <?php if (!empty($invoice)): ?>
          <?php if (($invoice['invoice_type']??'') === 'business'): ?>
          <p style="margin:2px 0;font-size:13px"><b>Công ty:</b> <?= e($invoice['company_name']??'') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Người đại diện:</b> <?= e($invoice['legal_representative']??'') ?></p>
          <?php else: ?>
          <p style="margin:2px 0;font-size:13px"><b>Tên:</b> <?= e($invoice['buyer_name']??'') ?></p>
          <?php endif; ?>
          <p style="margin:2px 0;font-size:13px"><b>MST:</b> <?= e($invoice['tax_code']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Địa chỉ:</b> <?= e(($invoice['address']??'').' '.($invoice['province']??'').' '.($invoice['ward']??'')) ?></p>
          <p style="margin:2px 0;font-size:13px"><b>CCCD:</b> <?= e($invoice['id_number']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Email:</b> <?= e($invoice['email']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>SĐT:</b> <?= e($invoice['phone']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Ngân hàng:</b> <?= e($invoice['bank_name']??'') ?> — STK: <?= e($invoice['bank_account']??'—') ?></p>
        <?php else: ?>
          <p style="margin:2px 0;font-size:13px"><b>Tên:</b> <?= e($order['full_name']??$order['shipping_full_name']??'') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Email:</b> <?= e($order['email']??'') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>SĐT:</b> <?= e($order['phone']??'') ?></p>
          <p style="color:#e74c3c;font-size:12px;margin-top:6px">⚠ Khách hàng chưa điền thông tin xuất hóa đơn</p>
        <?php endif; ?>
      </div>
      <div>
        <h3 style="font-size:14px;color:#1a3258;margin:0 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px">Thông tin đơn hàng</h3>
        <p style="margin:2px 0;font-size:13px"><b>Mã đơn:</b> <?= e($order['code'] ?? $order['order_code'] ?? '') ?></p>
        <p style="margin:2px 0;font-size:13px"><b>Ngày đặt:</b> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        <p style="margin:2px 0;font-size:13px"><b>Thanh toán:</b> <?= ($order['payment_method']??'')==='bank_transfer'?'Chuyển khoản ngân hàng':'COD (Thanh toán khi nhận)' ?></p>
        <p style="margin:2px 0;font-size:13px"><b>Giao tới:</b> <?= e($order['shipping_detail']??'') ?></p>
      </div>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-bottom:20px;font-size:13px">
      <thead><tr style="background:#1a3258;color:#fff">
        <th style="padding:8px;text-align:left">STT</th>
        <th style="padding:8px;text-align:left">Sản phẩm</th>
        <th style="padding:8px;text-align:center">SL</th>
        <th style="padding:8px;text-align:right">Đơn giá</th>
        <th style="padding:8px;text-align:right">Thành tiền</th>
      </tr></thead>
      <tbody>
      <?php $stt=1; $sub=0; foreach($items as $it): $lt=($it['unit_price']??0)*($it['quantity']??1); $sub+=$lt; ?>
      <tr style="border-bottom:1px solid #eee">
        <td style="padding:6px 8px"><?= $stt++ ?></td>
        <td style="padding:6px 8px"><?= e($it['product_name']??'') ?></td>
        <td style="padding:6px 8px;text-align:center"><?= $it['quantity']??1 ?></td>
        <td style="padding:6px 8px;text-align:right"><?= number_format($it['unit_price']??0) ?> ₫</td>
        <td style="padding:6px 8px;text-align:right"><?= number_format($lt) ?> ₫</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="4" style="text-align:right;padding:6px 8px;font-weight:600">Tiền hàng:</td><td style="text-align:right;padding:6px 8px"><?= number_format($sub) ?> ₫</td></tr>
        <tr><td colspan="4" style="text-align:right;padding:6px 8px;font-weight:600">Phí vận chuyển:</td><td style="text-align:right;padding:6px 8px"><?= number_format($order['shipping_total']??0) ?> ₫</td></tr>
        <?php if(($order['discount_total']??0)>0): ?>
        <tr><td colspan="4" style="text-align:right;padding:6px 8px;font-weight:600;color:#27ae60">Giảm giá:</td><td style="text-align:right;padding:6px 8px;color:#27ae60">-<?= number_format($order['discount_total']) ?> ₫</td></tr>
        <?php endif; ?>
        <tr style="background:#f8f9fa"><td colspan="4" style="text-align:right;padding:10px 8px;font-weight:900;font-size:16px;color:#1a3258">TỔNG CỘNG:</td><td style="text-align:right;padding:10px 8px;font-weight:900;font-size:16px;color:#c8962b"><?= number_format($order['grand_total']??0) ?> ₫</td></tr>
      </tfoot>
    </table>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:30px">
      <div style="text-align:center"><p style="font-size:13px;font-weight:700;margin:0">Người mua hàng</p><p style="font-size:11px;color:#888;margin:2px 0">(Ký, ghi rõ họ tên)</p></div>
      <div style="text-align:center"><p style="font-size:13px;font-weight:700;margin:0">Người bán hàng</p><p style="font-size:11px;color:#888;margin:2px 0">(Ký, ghi rõ họ tên)</p></div>
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
          <select name="invoice_type" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
            <option value="personal" <?= ($invoice['invoice_type']??'personal')==='personal'?'selected':'' ?>>Cá nhân</option>
            <option value="business" <?= ($invoice['invoice_type']??'')==='business'?'selected':'' ?>>Tổ chức</option>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Tên người mua / Công ty</label>
          <input type="text" name="buyer_name" value="<?= e($invoice['buyer_name']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Họ tên / Tên CT">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Mã số thuế</label>
          <input type="text" name="tax_code" value="<?= e($invoice['tax_code']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Nhập MST">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">CCCD/CMND</label>
          <input type="text" name="id_number" value="<?= e($invoice['id_number']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Số CCCD" maxlength="12" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
      </div>
      <div class="form-group" style="margin-top:10px">
        <label style="font-weight:600;font-size:13px">Địa chỉ</label>
        <input type="text" name="inv_address" value="<?= e($invoice['address']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="Địa chỉ xuất hóa đơn">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Tỉnh/Thành phố</label>
          <input type="text" name="province" value="<?= e($invoice['province']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Phường/Xã</label>
          <input type="text" name="ward" value="<?= e($invoice['ward']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Email</label>
          <input type="email" name="inv_email" value="<?= e($invoice['email']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="email@gmail.com">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Số điện thoại</label>
          <input type="tel" name="inv_phone" value="<?= e($invoice['phone']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" placeholder="0912345678" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Ngân hàng</label>
          <select name="bank_name" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px">
            <option value="">Chọn ngân hàng</option>
            <?php foreach(['Vietcombank','Techcombank','BIDV','VietinBank','MB Bank','ACB','Sacombank','VPBank','TPBank','HDBank','SHB','Agribank'] as $bk): ?>
            <option value="<?= $bk ?>" <?= ($invoice['bank_name']??'')===$bk?'selected':'' ?>><?= $bk ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:13px">Số TK ngân hàng</label>
          <input type="text" name="bank_account" value="<?= e($invoice['bank_account']??'') ?>" style="width:100%;padding:8px;border:1px solid #d0d5e0;border-radius:6px" maxlength="15" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
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
  var data = new FormData(form);
  var userId = <?= json_encode($order['user_id']) ?>;
  fetch('/admin/users/' + userId + '/invoice-info', {method:'POST', body:data})
    .then(r => r.json())
    .then(d => {
      if (d.ok) {
        document.getElementById('invoiceEditStatus').innerHTML = '<span style="color:green">✅ Đã lưu thành công!</span>';
        setTimeout(function(){ location.reload(); }, 1000);
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
    <form method="post" action="/admin/orders/<?= $order['id'] ?>/cancel" onsubmit="return confirm('Xác nhận hủy đơn hàng này? Khách hàng sẽ nhận thông báo.')">
      <?= csrfField() ?>
      <input type="text" name="cancel_reason" placeholder="Lý do hủy đơn..." required style="width:100%;padding:8px 12px;border:1px solid #fca5a5;border-radius:6px;margin-bottom:10px;font-size:13px;box-sizing:border-box">
      <button type="submit" style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px">Hủy đơn & Thông báo KH</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>


