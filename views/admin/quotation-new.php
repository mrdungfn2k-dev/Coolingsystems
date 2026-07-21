<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="/admin/quotations" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
    <div>
      <h1>Tạo báo giá độc lập mới</h1>
      <p style="color:#667085;margin:0">Tạo bảng báo giá linh hoạt, khách hàng có thể kiểm tra trước khi lên đơn hàng.</p>
    </div>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.flex-container{display:flex;gap:20px;align-items:flex-start}
.main-card{flex:1;background:#fff;border:1px solid #e6ebf1;border-radius:8px;padding:20px}
.sidebar-card{width:320px;background:#fff;border:1px solid #e6ebf1;border-radius:8px;padding:20px;position:sticky;top:20px}
.item-table{width:100%;border-collapse:collapse;margin-top:14px}
.item-table th{background:#f8fafc;padding:10px 8px;font-size:11px;text-transform:uppercase;color:#64748b;text-align:left;border-bottom:1px solid #e6ebf1}
.item-table td{padding:10px 8px;border-top:1px solid #edf1f5}
.item-table input{width:100%;height:34px;border:1px solid #d8e0ea;border-radius:4px;padding:0 8px;font-size:13px}
.remove-btn{background:none;border:none;color:#dc2626;cursor:pointer;font-weight:700}
.search-results{position:absolute;background:#fff;border:1px solid #d8e0ea;border-radius:4px;width:100%;max-height:220px;overflow-y:auto;z-index:999;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:none}
.search-item{padding:8px 12px;cursor:pointer;border-bottom:1px solid #f3f4f6;font-size:13px}
.search-item:hover{background:#f0f7ff}
</style>

<form method="post" action="/admin/quotations">
  <?= csrfField() ?>
  <div class="flex-container">
    <div class="main-card">
      <h3 style="margin:0 0 14px;color:#1a3258">Danh sách sản phẩm báo giá</h3>
      
      <!-- Tìm sản phẩm -->
      <div style="position:relative" class="form-group">
        <label>Tìm sản phẩm thêm vào báo giá (Nhập tên, SKU hoặc OEM)</label>
        <input type="text" id="product-search" placeholder="Nhập ít nhất 2 ký tự..." style="height:40px">
        <div id="search-results" class="search-results"></div>
      </div>

      <table class="item-table" id="items-table">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th style="width:100px">Số lượng</th>
            <th style="width:150px">Đơn giá bán (đ)</th>
            <th style="width:150px;text-align:right">Thành tiền</th>
            <th style="width:40px"></th>
          </tr>
        </thead>
        <tbody id="items-tbody">
          <!-- Các dòng sản phẩm được thêm vào qua JS -->
        </tbody>
      </table>
      <div id="empty-msg" style="padding:40px;text-align:center;color:#9ca3af">Chưa chọn sản phẩm nào cho báo giá.</div>
    </div>

    <!-- Sidebar cấu hình chung -->
    <div class="sidebar-card">
      <h3 style="margin:0 0 14px;color:#1a3258">Thông tin khách hàng</h3>
      
      <div class="form-group">
        <label>Chọn khách hàng <span style="color:#dc2626">*</span></label>
        <select name="user_id" required style="height:38px">
          <option value="">-- Chọn khách hàng --</option>
          <?php foreach($customers as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e($c['full_name']) ?> (<?= e($c['phone'] ?: 'Không SĐT') ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Ngày hết hạn báo giá <span style="color:#dc2626">*</span></label>
        <input type="date" name="expires_at" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>" style="height:38px">
        <small style="color:#6b7280;display:block;margin-top:4px">Mặc định có hiệu lực trong 7 ngày.</small>
      </div>

      <div class="form-group">
        <label>Ghi chú báo giá</label>
        <textarea name="note" rows="3" placeholder="Điều khoản thanh toán, vận chuyển..."></textarea>
      </div>

      <div style="border-top:1px solid #edf2f7;margin-top:16px;padding-top:16px">
        <div style="display:flex;justify-content:between;margin-bottom:8px;font-size:14px">
          <span>Tổng giá trị:</span>
          <strong id="summary-total" style="font-size:18px;color:#1e3a8a">0 đ</strong>
        </div>
      </div>

      <button type="submit" class="btn btn-navy" style="width:100%;margin-top:12px;height:40px">Lưu & Gửi báo giá</button>
    </div>
  </div>
</form>

<script>
const searchInput = document.getElementById('product-search');
const searchResults = document.getElementById('search-results');
const tbody = document.getElementById('items-tbody');
const emptyMsg = document.getElementById('empty-msg');
const summaryTotal = document.getElementById('summary-total');

let debounceTimer;
searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    if(q.length < 2) {
        searchResults.style.display = 'none';
        return;
    }
    debounceTimer = setTimeout(() => {
        fetch(`/admin/inventory/search-product?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                searchResults.innerHTML = '';
                if(data.length === 0) {
                    searchResults.innerHTML = '<div class="search-item" style="color:#9ca3af">Không tìm thấy sản phẩm.</div>';
                } else {
                    data.forEach(p => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.innerHTML = `<strong>${p.name}</strong><br><small style="color:#6b7280">SKU: ${p.sku} | Tồn kho: ${p.stock}</small>`;
                        div.addEventListener('click', () => {
                            addProductRow(p);
                            searchResults.style.display = 'none';
                            searchInput.value = '';
                        });
                        searchResults.appendChild(div);
                    });
                }
                searchResults.style.display = 'block';
            });
    }, 200);
});

// Click ra ngoài ẩn kết quả tìm kiếm
document.addEventListener('click', function(e) {
    if (e.target !== searchInput && e.target !== searchResults) {
        searchResults.style.display = 'none';
    }
});

function addProductRow(p) {
    // Check trùng
    if(document.getElementById(`row-p-${p.id}`)) {
        const qtyInput = document.querySelector(`#row-p-${p.id} .qty-input`);
        qtyInput.value = parseInt(qtyInput.value) + 1;
        updateRowTotal(p.id);
        return;
    }

    emptyMsg.style.display = 'none';

    // Query đơn giá bán (giả định mặc định hoặc lấy từ API)
    // Tạm thời lấy giá bán của sản phẩm hoặc mặc định là 0
    const defaultPrice = p.price || 0;

    const tr = document.createElement('tr');
    tr.id = `row-p-${p.id}`;
    tr.innerHTML = `
        <td>
            <strong>${p.name}</strong>
            <input type="hidden" name="items[${p.id}][product_id]" value="${p.id}">
            <div style="font-size:11px;color:#6b7280">SKU: ${p.sku}</div>
        </td>
        <td>
            <input type="number" class="qty-input" name="items[${p.id}][quantity]" value="1" min="1" style="text-align:center" oninput="updateRowTotal(${p.id})">
        </td>
        <td>
            <input type="number" class="price-input" name="items[${p.id}][price]" value="${defaultPrice}" min="0" oninput="updateRowTotal(${p.id})">
        </td>
        <td style="text-align:right;font-weight:600;color:#1e3a8a" class="row-total">
            ${formatVND(defaultPrice)} đ
        </td>
        <td style="text-align:center">
            <button type="button" class="remove-btn" onclick="removeProductRow(${p.id})">×</button>
        </td>
    `;
    tbody.appendChild(tr);
    updateGrandTotal();
}

function removeProductRow(id) {
    const tr = document.getElementById(`row-p-${id}`);
    if(tr) tr.remove();
    if(tbody.children.length === 0) {
        emptyMsg.style.display = 'block';
    }
    updateGrandTotal();
}

function updateRowTotal(id) {
    const tr = document.getElementById(`row-p-${id}`);
    const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
    const price = parseInt(tr.querySelector('.price-input').value) || 0;
    const total = qty * price;
    tr.querySelector('.row-total').textContent = formatVND(total) + ' đ';
    updateGrandTotal();
}

function updateGrandTotal() {
    let grand = 0;
    document.querySelectorAll('#items-tbody tr').forEach(tr => {
        const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
        const price = parseInt(tr.querySelector('.price-input').value) || 0;
        grand += qty * price;
    });
    summaryTotal.textContent = formatVND(grand) + ' đ';
}

function formatVND(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
