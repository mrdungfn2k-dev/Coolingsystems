<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center">
  <h1>Tạo đơn hàng hộ khách</h1>
  <?php $backUrl = (isset($role) && $role === "staff") ? "/staff/orders" : "/admin/orders"; ?>
  <a href="<?= $backUrl ?>" class="btn btn-outline-navy">Quay lại đơn hàng</a>
</div>

<?php $formAction = (isset($role) && $role === "staff") ? "/staff/orders/create" : "/admin/orders/create"; ?>
<form method="post" action="<?= $formAction ?>" id="createOrderForm">
  <?= csrfField() ?>
  <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px">
    
    <!-- Left: Thông tin khách & sản phẩm -->
    <div>
      <!-- Thông tin khách hàng -->
      <div class="panel mb-4">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)"> Thông tin khách hàng</div>
        <div style="padding:16px">
          <div class="form-group">
            <label>Tìm khách hàng (theo tên hoặc SĐT)</label>
            <div style="display:flex;gap:8px">
              <input type="text" id="customerSearch" placeholder="Nhập tên hoặc SĐT khách..." style="flex:1" oninput="searchCustomer(this.value)">
            </div>
            <div id="customerResults" style="display:none;border:1px solid var(--line);border-radius:4px;margin-top:4px;max-height:200px;overflow-y:auto;background:#fff"></div>
          </div>
          
          <input type="hidden" name="user_id" id="selectedUserId">
          <div id="selectedCustomer" style="display:none;background:#f0f7ff;border:1px solid #bee3f8;border-radius:6px;padding:12px;margin-top:8px">
            <div style="font-weight:700" id="selectedCustomerName"></div>
            <div style="font-size:12px;color:#666" id="selectedCustomerEmail"></div>
            <div style="font-size:12px;color:#666" id="selectedCustomerPhone"></div>
          </div>
          
          <!-- Hoặc nhập thông tin giao hàng thủ công -->
          <hr style="margin:16px 0;border-color:var(--line)">
          <p style="font-size:12px;color:#888;margin-bottom:12px">Hoặc nhập thông tin giao hàng (khách mới, chưa có tài khoản)</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Họ tên <span class="req">*</span></label><input type="text" name="ship_name" id="shipName" placeholder="Nguyễn Văn A"></div>
            <div class="form-group"><label>Số điện thoại <span class="req">*</span></label><input type="tel" name="ship_phone" id="shipPhone" placeholder="0987..." pattern="^0[0-9]{9}$" maxlength="10" title="Số điện thoại phải 10 chữ số, bắt đầu bằng 0" oninput="this.value=this.value.replace(/[^0-9]/g,'')"></div>
          </div>
          <div class="form-group"><label>Địa chỉ giao hàng</label><input type="text" name="ship_address" placeholder="Số nhà, đường, phường/xã..."></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group"><label>Quận/Huyện</label><input type="text" name="ship_district" placeholder="Quận/Huyện"></div>
            <div class="form-group"><label>Tỉnh/Thành</label><input type="text" name="ship_province" placeholder="Hà Nội, TP.HCM..."></div>
          </div>
        </div>
      </div>

      <!-- Sản phẩm -->
      <div class="panel">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)"> Sản phẩm</div>
        <div style="padding:16px">
          <div style="display:flex;gap:8px;margin-bottom:12px">
            <input type="text" id="productSearch" placeholder="Tìm sản phẩm theo tên hoặc SKU..." style="flex:1" oninput="searchProduct(this.value)">
          </div>
          <div id="productResults" style="display:none;border:1px solid var(--line);border-radius:4px;margin-bottom:12px;max-height:250px;overflow-y:auto;background:#fff"></div>
          
          <table id="itemsTable" class="tbl" style="font-size:13px">
            <thead><tr><th>Sản phẩm</th><th style="width:70px">SL</th><th style="width:120px">Đơn giá</th><th style="width:120px">Thành tiền</th><th style="width:40px"></th></tr></thead>
            <tbody id="itemsBody">
              <tr id="emptyRow"><td colspan="5" class="text-center text-muted" style="padding:20px">Chưa có sản phẩm. Tìm và thêm sản phẩm bên trên.</td></tr>
            </tbody>
          </table>
          <input type="hidden" name="items" id="itemsJson" value="[]">
        </div>
      </div>
    </div>
    
    <!-- Right: Thanh toán & Giao hàng -->
    <div>
      <div class="panel mb-4">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)"> Hình thức thanh toán</div>
        <div style="padding:16px">
          <div class="form-group">
            <label>Phương thức thanh toán</label>
            <select name="payment_method" id="paymentMethod" onchange="updatePayment()">
              <option value="cod">COD — Thanh toán khi nhận hàng</option>
              <option value="bank_transfer">Chuyển khoản trước</option>
            </select>
          </div>
          
          <div id="paymentTypeBox" style="display:none">
            <div class="form-group">
              <label>Hình thức thanh toán trước</label>
              <select name="payment_type" id="paymentType" onchange="updatePayment()">
                <option value="full_prepay">Thanh toán 100% trước</option>
                              </select>
            </div>
          </div>

          <div id="paymentSummary" style="background:#f8f9fa;border-radius:6px;padding:12px;margin-top:8px;font-size:13px">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px"><span>Tổng tiền hàng:</span><span id="totalDisplay" style="font-weight:700">0₫</span></div>

          </div>
        </div>
      </div>
      
      <div class="panel mb-4">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)"> Giao hàng & Ghi chú</div>
        <div style="padding:16px">
          <div class="form-group">
            <label>Trạng thái giao hàng ban đầu</label>
            <select name="delivery_status">
              <option value="pending">Đang chờ</option>
              <option value="received">Tiếp nhận</option>
            </select>
          </div>
          <div class="form-group">
            <label>Ghi chú của nhân viên</label>
            <textarea name="staff_note" rows="3" placeholder="Ghi chú về đơn hàng, nguồn khách (Zalo, Facebook,...), yêu cầu đặc biệt..."></textarea>
          </div>
        </div>
      </div>
      
      <div class="panel">
        <div style="padding:16px">
          <div style="background:#fff3e0;border:1px solid #ffe0b2;border-radius:6px;padding:12px;margin-bottom:14px;font-size:13px">
            <strong> Nhân viên tạo hộ:</strong> <?= e($currentUser['full_name']) ?>
          </div>
          <button type="submit" class="btn btn-gold btn-block btn-lg" onclick="return validateOrder()">Tạo đơn hàng hộ khách</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
let items = [];
let totalAmount = 0;

function formatVND(n) {
  return new Intl.NumberFormat('vi-VN').format(n) + '₫';
}

function searchCustomer(q) {
  if (q.length < 2) { document.getElementById('customerResults').style.display='none'; return; }
  fetch(('<?= isset($role) && $role === "staff" ? "/staff" : "/admin" ?>' + '/api/search-customers?q=')+ encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {
      const box = document.getElementById('customerResults');
      if (!data.length) { box.style.display='none'; return; }
      box.innerHTML = data.map(u => `<div onclick="selectCustomer(${u.id},'${u.full_name}','${u.email}','${u.phone||''}')" style="padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:13px" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='#fff'"><strong>${u.full_name}</strong> — ${u.email} ${u.phone ? '| '+u.phone : ''}</div>`).join('');
      box.style.display = 'block';
    });
}

function selectCustomer(id, name, email, phone) {
  document.getElementById('selectedUserId').value = id;
  document.getElementById('selectedCustomerName').textContent = name;
  document.getElementById('selectedCustomerEmail').textContent = email;
  document.getElementById('selectedCustomerPhone').textContent = phone;
  document.getElementById('selectedCustomer').style.display = 'block';
  document.getElementById('customerResults').style.display = 'none';
  document.getElementById('customerSearch').value = name;
  document.getElementById('shipName').value = name;
  document.getElementById('shipPhone').value = phone;
}

function searchProduct(q) {
  if (q.length < 2) { document.getElementById('productResults').style.display='none'; return; }
  fetch(('<?= isset($role) && $role === "staff" ? "/staff" : "/admin" ?>' + '/api/search-products?q=')+ encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {
      const box = document.getElementById('productResults');
      if (!data.length) { box.style.display='none'; return; }
      box.innerHTML = data.map(p => `<div onclick="addItem(${p.id},'${p.name.replace(/'/g,"\\'")}',${p.price},'${p.sku||''}')" style="padding:10px 12px;cursor:pointer;border-bottom:1px solid #f0f0f0;font-size:13px;display:flex;justify-content:space-between" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='#fff'"><span><strong>${p.name}</strong>${p.sku?' <small style=color:#888>['+p.sku+']</small>':''}</span><span style="font-weight:700;color:var(--navy)">${formatVND(p.price)}</span></div>`).join('');
      box.style.display = 'block';
    });
}

function addItem(id, name, price, sku) {
  const existing = items.find(i => i.product_id == id);
  if (existing) { existing.qty++; } 
  else { items.push({product_id: id, name, price, sku, qty: 1}); }
  renderItems();
  document.getElementById('productResults').style.display = 'none';
  document.getElementById('productSearch').value = '';
}

function removeItem(idx) {
  items.splice(idx, 1);
  renderItems();
}

function updateQty(idx, qty) {
  items[idx].qty = parseInt(qty) || 1;
  renderItems();
}

function renderItems() {
  const tbody = document.getElementById('itemsBody');
  if (!items.length) {
    tbody.innerHTML = '<tr id="emptyRow"><td colspan="5" class="text-center text-muted" style="padding:20px">Chưa có sản phẩm.</td></tr>';
    totalAmount = 0; updateTotals(); return;
  }
  totalAmount = 0;
  tbody.innerHTML = items.map((item, i) => {
    const line = item.price * item.qty;
    totalAmount += line;
    return `<tr>
      <td><div style="font-size:12px;font-weight:600">${item.name}</div>${item.sku?'<div style="font-size:10px;color:#888">SKU: '+item.sku+'</div>':''}</td>
      <td><input type="number" min="1" value="${item.qty}" style="width:60px;text-align:center" onchange="updateQty(${i},this.value)"></td>
      <td style="font-size:12px;font-weight:600">${formatVND(item.price)}</td>
      <td style="font-size:12px;font-weight:700;color:var(--navy)">${formatVND(line)}</td>
      <td><button type="button" onclick="removeItem(${i})" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:16px">×</button></td>
    </tr>`;
  }).join('');
  updateTotals();
}

function updateTotals() {
  document.getElementById('totalDisplay').textContent = formatVND(totalAmount);
  document.getElementById('itemsJson').value = JSON.stringify(items);
  updatePayment();
}

function updatePayment() {
  const method = document.getElementById('paymentMethod').value;
  // No deposit logic - full payment only
}

function validateOrder() {
  if (!items.length) { alert('Vui lòng thêm ít nhất 1 sản phẩm!'); return false; }
  const shipName = document.getElementById('shipName').value.trim();
  const shipPhone = document.getElementById('shipPhone').value.trim();
  if (!shipName || !shipPhone) { alert('Vui lòng nhập họ tên và số điện thoại khách hàng!'); return false; }
      if (!/^0[0-9]{9}$/.test(shipPhone)) { alert('Số điện thoại phải đúng 10 chữ số và bắt đầu bằng 0!'); return false; }
  return true;
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
