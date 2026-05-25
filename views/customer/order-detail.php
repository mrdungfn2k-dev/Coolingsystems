<?php require __DIR__.'/../partials/head.php'; ?>
<style>
.od-card{background:#fff;border-radius:8px;border:1px solid var(--line);margin-bottom:20px;overflow:hidden}
.od-head{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;background:var(--bg-soft)}
.od-head .code{font-family:'Courier New',monospace;font-weight:700;font-size:15px;color:var(--navy)}
.od-body{padding:20px}
.od-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.od-grid .label{font-size:11px;color:#888;text-transform:uppercase;margin-bottom:2px}
.od-grid .value{font-size:14px;font-weight:500;color:#333}
.od-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f0f0f0;align-items:center}
.od-item:last-child{border-bottom:none}
.od-item img{width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #eee}
.od-item .name{font-size:14px;font-weight:600;color:var(--navy);flex:1}
.od-item .price{font-size:14px;font-weight:700;color:#c8962b}
.status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.status-badge.green{background:#e6f7ed;color:#27ae60}
.status-badge.orange{background:#fff3e0;color:#f57c00}
.status-badge.blue{background:#e3f2fd;color:#1565c0}
.status-badge.red{background:#fce4ec;color:#c62828}
.status-badge.gray{background:#f5f5f5;color:#666}
.invoice-print{background:linear-gradient(135deg,#c8a55a,#b8923e);color:#fff;padding:10px 28px;border-radius:6px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.5px;transition:all .2s}
.invoice-print:hover{background:linear-gradient(135deg,#b8923e,#a07e2e);box-shadow:0 2px 8px rgba(200,165,90,.3)}
@media print{.no-print{display:none!important} .od-card{border:none;box-shadow:none}}
</style>

<section class="block"><div class="wrap">
  <div style="margin-bottom:16px">
    <a href="/customer/orders" style="color:var(--navy);font-size:13px;text-decoration:none">← Quay lại đơn hàng</a>
  </div>

  <!-- Order header -->
  <div class="od-card">
    <div class="od-head">
      <div>
        <span class="code"># <?= e($order['code']) ?></span>
        <span style="margin-left:10px;font-size:12px;color:#888"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
      </div>
      <div>
        <?php
        $statusMap = [
          'pending'=>['Chờ xử lý','orange'],'confirmed'=>['Đã xác nhận','blue'],
          'shipping'=>['Đang giao','blue'],'delivered'=>['Đã giao','green'],
          'completed'=>['Hoàn thành','green'],'cancelled'=>['Đã hủy','red'],
          'returned'=>['Trả hàng','gray']
        ];
        $s = $statusMap[$order['status']] ?? ['N/A','gray'];
        ?>
        <span class="status-badge <?= $s[1] ?>"><?= $s[0] ?></span>
      </div>
    </div>
    <div class="od-body">
      <div class="od-grid" style="margin-bottom:16px">
        <div><div class="label">Khách hàng</div><div class="value"><?= e($order['shipping_full_name']??'') ?></div></div>
        <div><div class="label">Hình thức TT</div><div class="value"><?= $order['payment_method']==='bank_transfer'?'Chuyển khoản':'COD (Thanh toán khi nhận)' ?></div></div>
        <div><div class="label">Địa chỉ giao hàng</div><div class="value"><?= e($order['shipping_detail']??'—') ?></div></div>
        <div><div class="label">Ghi chú</div><div class="value"><?= e($order['notes']??'—') ?></div></div>
      </div>

      <h3 style="font-size:15px;color:var(--navy);margin:20px 0 10px;border-bottom:2px solid var(--navy);padding-bottom:6px">Chi tiết sản phẩm</h3>
      <?php $subtotal = 0; foreach ($items as $item): $lineTotal = ($item['unit_price']??0)*($item['quantity']??1); $subtotal += $lineTotal; ?>
      <div class="od-item">
        <?php if (!empty($item['image'])): ?>
        <img src="/uploads/products/<?= e($item['image']) ?>" onerror="this.src='/assets/images/no-image.png'">
        <?php else: ?>
        <div style="width:60px;height:60px;background:#f0f0f0;border-radius:6px"></div>
        <?php endif; ?>
        <div class="name">
          <?= e($item['product_name']??'Sản phẩm') ?>
          <div style="font-size:12px;color:#888;font-weight:400">x<?= $item['quantity']??1 ?></div>
        </div>
        <div class="price"><?= number_format($lineTotal) ?> ₫</div>
      </div>
      <?php endforeach; ?>

      <div style="margin-top:16px;padding-top:12px;border-top:2px solid #e0e0e0;text-align:right">
        <div style="font-size:13px;color:#888">Tiền hàng: <span style="color:#333;font-weight:600"><?= number_format($subtotal) ?> ₫</span></div>
        <?php $shipping = $order['shipping_total']??0; ?>
        <div style="font-size:13px;color:#888;margin-top:4px">Phí vận chuyển: <span style="color:#333;font-weight:600">+ <?= number_format($shipping) ?> ₫</span></div>
        <?php $discount = $order['discount_total']??0; if($discount>0): ?>
        <div style="font-size:13px;color:#888;margin-top:4px">Giảm giá: <span style="color:#27ae60;font-weight:600">- <?= number_format($discount) ?> ₫</span></div>
        <?php endif; ?>
        <?php $calculatedTotal = $subtotal + $shipping - $discount; ?>
        <div style="font-size:20px;font-weight:900;color:#c8962b;margin-top:8px">Tổng: <?= number_format($calculatedTotal) ?> ₫</div>
      </div>
    </div>
  </div>

  
  <!-- Cancel / Return Actions -->
  <div class="od-card no-print">
    <div class="od-body" style="display:flex;gap:12px;flex-wrap:wrap;padding:20px">
      <?php if (($order['delivery_status']??'pending') === 'pending'): ?>
        <form method="post" action="/customer/orders/<?= $order['id'] ?>/cancel" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')" style="margin:0">
          <?= csrfField() ?>
          <button type="submit" style="padding:10px 24px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">✕ Hủy đơn hàng</button>
        </form>
        <p style="font-size:12px;color:#888;margin:0;align-self:center">Bạn có thể hủy đơn khi admin chưa xác nhận</p>
      <?php endif; ?>

      <?php if (in_array($order['delivery_status']??'', ['delivered','completed'])): ?>
        <?php
          $existingReturn = dbGet("SELECT * FROM order_returns WHERE order_id=? AND user_id=?", [$order['id'], $user['id']]);
        ?>
        <?php if (!$existingReturn): ?>
          <button onclick="document.getElementById('returnForm').style.display='block';this.style.display='none'" style="padding:10px 24px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">↩ Yêu cầu trả hàng</button>
        <?php else: ?>
          <div style="padding:10px 20px;background:#f3f4f6;border-radius:8px;font-size:13px;color:#6b7280;font-weight:600">
            ✓ Đã gửi yêu cầu trả hàng (<?= ucfirst($existingReturn['status']??'pending') ?>)
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Return Form (hidden by default) -->
  <?php if (in_array($order['delivery_status']??'', ['delivered','completed']) && empty($existingReturn)): ?>
  <div id="returnForm" class="od-card no-print" style="display:<?= !empty($_GET['return']) ? 'block' : 'none' ?>">
    <div class="od-head"><h3 style="margin:0;font-size:16px;color:#1a3258">Yêu cầu trả hàng</h3></div>
    <div class="od-body">
      <form method="post" action="/customer/orders/<?= $order['id'] ?>/return" enctype="multipart/form-data" id="returnReqForm" onsubmit="return validateReturnForm()">
        <?= csrfField() ?>
        <div style="margin-bottom:14px">
          <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Lý do trả hàng <span style="color:red">*</span></label>
          <textarea name="reason" id="rt_reason" required rows="3" maxlength="500" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;font-size:14px;resize:vertical" placeholder="Mô tả chi tiết lý do trả hàng..."></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">SĐT liên hệ <span style="color:red">*</span></label>
            <input type="tel" name="contact_phone" id="rt_phone" required maxlength="10" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px" placeholder="09xxxxxxxx">
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Email liên hệ <span style="color:red">*</span></label>
            <input type="email" name="contact_email" id="rt_email" required style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px" placeholder="email@gmail.com">
          </div>
        </div>
        <div style="margin-bottom:14px">
          <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Địa chỉ nhận lại hàng <span style="color:red">*</span></label>
          <input type="text" name="contact_address" id="rt_addr" required maxlength="50" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px" placeholder="Số nhà, đường, phường, quận, TP">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Ngân hàng <span style="color:red">*</span></label>
            <select name="bank_name" id="rt_bank" required style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px">
              <option value="">-- Chọn ngân hàng --</option>
              <option value="Vietcombank">Vietcombank</option><option value="Techcombank">Techcombank</option><option value="BIDV">BIDV</option><option value="VietinBank">VietinBank</option><option value="MB Bank">MB Bank</option><option value="ACB">ACB</option><option value="Sacombank">Sacombank</option><option value="VPBank">VPBank</option><option value="TPBank">TPBank</option><option value="HDBank">HDBank</option><option value="SHB">SHB</option><option value="SeABank">SeABank</option><option value="OCB">OCB</option><option value="LienVietPostBank">LienVietPostBank</option><option value="MSB">MSB</option><option value="Eximbank">Eximbank</option><option value="VIB">VIB</option><option value="ABBank">ABBank</option><option value="BacABank">BacABank</option><option value="NCB">NCB</option><option value="PVcomBank">PVcomBank</option><option value="SCB">SCB</option><option value="CIMB">CIMB</option><option value="UOB">UOB</option><option value="BanVietBank">BanVietBank</option><option value="Agribank">Agribank</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">STK hoàn tiền <span style="color:red">*</span></label>
            <input type="text" name="bank_account" id="rt_bankno" required style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px" placeholder="Số tài khoản">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Tên chủ TK <span style="color:red">*</span></label>
            <input type="text" name="bank_holder" id="rt_holder" required maxlength="30" style="width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:8px;font-size:14px" placeholder="NGUYEN VAN A">
          </div>
          <div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Ảnh minh chứng</label>
            <input type="file" name="return_image" accept="image/*" style="font-size:13px">
          </div>
          <div>
            <label style="display:block;font-weight:600;margin-bottom:4px;font-size:13px">Video minh chứng</label>
            <input type="file" name="return_video" accept="video/*" style="font-size:13px">
          </div>
        </div>
        <div id="rtErrors" style="color:#e74c3c;font-size:12px;margin-bottom:10px"></div>
        <button type="submit" style="padding:12px 28px;background:linear-gradient(135deg,#c8a84e,#b8942e);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">Gửi yêu cầu trả hàng</button>
      </form>
    </div>
  </div>
  <?php endif; ?>


  <!-- Invoice export button (only for completed orders) -->
  <?php if ($order['delivery_status'] === 'completed' && $order['payment_status'] === 'paid'): ?>
  <?php $invoiceInfo = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$user['id']]); ?>
  <div class="od-card no-print">
    <div class="od-body" style="text-align:center;padding:20px">
      <?php if ($invoiceInfo && !empty($invoiceInfo['buyer_name']) && !empty($invoiceInfo['tax_code']) && !empty($invoiceInfo['id_number'])): ?>
        <button onclick="printInvoice()" class="invoice-print">XUẤT HÓA ĐƠN</button>
      <?php else: ?>
        <div style="padding:12px;background:#fef3c7;border:1px solid #fbbf24;border-radius:8px;font-size:13px;color:#92400e;margin-bottom:12px">
          ⚠️ Vui lòng cập nhật đầy đủ <strong>Thông tin xuất hóa đơn</strong> trong <a href="/customer/profile" style="color:#1a3258;font-weight:700">Hồ sơ cá nhân</a> trước khi xuất hóa đơn.
        </div>
        <button disabled style="padding:10px 24px;background:#d1d5db;color:#9ca3af;border:none;border-radius:8px;font-weight:700;cursor:not-allowed;font-size:14px">XUẤT HÓA ĐƠN</button>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Invoice preview (printable) -->
  <?php if ($order['delivery_status'] === 'completed' && $order['payment_status'] === 'paid'): ?>
  <div id="invoicePrintArea" style="display:none">
    <div style="max-width:800px;margin:0 auto;padding:40px;font-family:Arial,sans-serif">
      <div style="text-align:center;margin-bottom:30px">
        <h1 style="font-size:24px;color:#1a3258;margin:0">HÓA ĐƠN BÁN HÀNG</h1>
        <p style="color:#888;font-size:13px;margin:4px 0">Cooling Parts & Service</p>
        <p style="color:#888;font-size:12px">Ngày: <?= date('d/m/Y', strtotime($order['created_at'])) ?> | Mã: <?= e($order['code']) ?></p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;border:1px solid #ddd;padding:16px;border-radius:6px">
        <div>
          <h3 style="font-size:14px;color:#1a3258;margin:0 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px">Thông tin người mua</h3>
          <?php if ($invoice): ?>
          <?php if ($invoice['invoice_type']==='business'): ?>
          <p style="margin:2px 0;font-size:13px"><b>Công ty:</b> <?= e($invoice['company_name']??'') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Người đại diện:</b> <?= e($invoice['legal_representative']??'') ?></p>
          <?php else: ?>
          <p style="margin:2px 0;font-size:13px"><b>Tên:</b> <?= e($invoice['buyer_name']??'') ?></p>
          <?php endif; ?>
          <p style="margin:2px 0;font-size:13px"><b>MST:</b> <?= e($invoice['tax_code']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Địa chỉ:</b> <?= e($invoice['address']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>CCCD:</b> <?= e($invoice['id_number']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Email:</b> <?= e($invoice['email']??'—') ?></p>
          <p style="margin:2px 0;font-size:13px"><b>SĐT:</b> <?= e($invoice['phone']??'—') ?></p>
          <?php else: ?>
          <p style="color:#888;font-size:13px">Chưa có thông tin xuất hóa đơn</p>
          <?php endif; ?>
        </div>
        <div>
          <h3 style="font-size:14px;color:#1a3258;margin:0 0 8px;border-bottom:1px solid #ddd;padding-bottom:4px">Thông tin đơn hàng</h3>
          <p style="margin:2px 0;font-size:13px"><b>Mã đơn:</b> <?= e($order['code']) ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Ngày đặt:</b> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
          <p style="margin:2px 0;font-size:13px"><b>Thanh toán:</b> <?= $order['payment_method']==='bank_transfer'?'Chuyển khoản':'COD' ?></p>
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
        <?php $i=1; $sub=0; foreach($items as $it): $lt=($it['unit_price']??0)*($it['quantity']??1); $sub+=$lt; ?>
        <tr style="border-bottom:1px solid #eee">
          <td style="padding:6px 8px"><?= $i++ ?></td>
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
          <?php $invoiceTotal = $sub + ($order['shipping_total']??0) - ($order['discount_total']??0); ?>
          <tr style="background:#f8f9fa"><td colspan="4" style="text-align:right;padding:10px 8px;font-weight:900;font-size:16px;color:#1a3258">TỔNG CỘNG:</td><td style="text-align:right;padding:10px 8px;font-weight:900;font-size:16px;color:#c8962b"><?= number_format($invoiceTotal) ?> ₫</td></tr>
        </tfoot>
      </table>
      <div style="text-align:center;margin-top:30px;font-size:12px;color:#999">
        <p>Cảm ơn quý khách đã mua hàng tại Cooling Parts & Service!</p>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div></section>

<script>
function printInvoice() {
  var area = document.getElementById('invoicePrintArea');
  if(!area) return;
  var win = window.open('','_blank','width=900,height=700');
  win.document.write('<html><head><title>Hóa đơn #<?= e($order["code"]) ?></title><style>body{font-family:Arial,sans-serif;margin:0;padding:0}@page{margin:1cm}@media print{body{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}}</style></head><body>');
  win.document.write(area.innerHTML);
  win.document.write('</body></html>');
  win.document.close();
  setTimeout(function(){ win.print(); }, 500);
}
</script>


<script>
// Return form validation
(function(){
  var phone = document.getElementById('rt_phone');
  var email = document.getElementById('rt_email');
  if(phone) {
    phone.addEventListener('input', function(){
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
      if(this.value.length > 0 && this.value[0] !== '0') this.value = '';
      if(this.value.length > 1 && !/^0[1-9]/.test(this.value)) this.value = this.value[0];
    });
  }
  if(email) {
    email.type = 'email';
  }
  var form = document.getElementById('returnForm');
  if(form) {
    form.addEventListener('submit', function(e){
      var p = document.getElementById('rt_phone');
      if(p && (p.value.length !== 10 || !/^0[1-9][0-9]{8}$/.test(p.value))) {
        e.preventDefault();
        alert('Số điện thoại phải gồm đúng 10 số, bắt đầu bằng 0');
        return false;
      }
      var em = document.getElementById('rt_email');
      if(em && em.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value)) {
        e.preventDefault();
        alert('Email không hợp lệ');
        return false;
      }
    });
  }
})();
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>
