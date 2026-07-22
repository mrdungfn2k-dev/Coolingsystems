<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Tạo phiếu chuyển kho mới</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Lập phiếu điều chuyển phụ tùng giữa Kho xuất và Kho nhận.</p>
  </div>
  <a href="/admin/stock-transfers" class="btn btn-outline">‹ Trở lại danh sách</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.search-results{position:absolute;background:#fff;border:1px solid #d8e0ea;border-radius:6px;width:100%;max-height:260px;overflow-y:auto;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,0.12);display:none;margin-top:4px}
.search-item{padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;font-size:13px}
.search-item:hover{background:#f0f7ff}
</style>

<form method="post" action="/admin/stock-transfers" style="max-width:950px;background:#fff;padding:24px;border:1px solid #e6ebf1;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.03)">
  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
    <div>
      <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Kho xuất hàng <span style="color:#e11d48">*</span></label>
      <select name="from_warehouse" required style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
        <option value="Kho chính">Kho chính (Tổng kho)</option>
        <option value="Chi nhánh Hà Nội">Chi nhánh Hà Nội</option>
        <option value="Chi nhánh TP.HCM">Chi nhánh TP.HCM</option>
      </select>
    </div>

    <div>
      <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Kho nhận hàng <span style="color:#e11d48">*</span></label>
      <select name="to_warehouse" required style="width:100%;height:40px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:14px;background:#fff">
        <option value="Chi nhánh Hà Nội">Chi nhánh Hà Nội</option>
        <option value="Chi nhánh TP.HCM">Chi nhánh TP.HCM</option>
        <option value="Kho Bảo hành">Kho Bảo hành & Lắp đặt</option>
        <option value="Kho chính">Kho chính (Tổng kho)</option>
      </select>
    </div>
  </div>

  <div style="margin-bottom:24px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Danh sách phụ tùng điều chuyển <span style="color:#e11d48">*</span></label>
    
    <!-- Ô tìm kiếm gõ hiển thị gợi ý -->
    <div style="position:relative;margin-bottom:14px">
      <input type="text" id="productSearchInput" placeholder="🔍 Gõ tên phụ tùng, mã SKU hoặc OEM để tìm kiếm..." style="width:100%;height:42px;border:1px solid #0284c7;border-radius:6px;padding:0 14px;font-size:14px;background:#fff;outline:none">
      <div id="productSearchDropdown" class="search-results"></div>
    </div>

    <div style="border:1px solid #e6ebf1;border-radius:8px;padding:16px;background:#fafbfc">
      <table style="width:100%;border-collapse:collapse" id="transferItemsTable">
        <thead>
          <tr style="font-size:12px;color:#64748b;text-transform:uppercase;border-bottom:1px solid #cbd5e1">
            <th style="text-align:left;padding:8px">Phụ tùng sản phẩm</th>
            <th style="width:140px;text-align:center;padding:8px">Mã SKU / OEM</th>
            <th style="width:120px;text-align:center;padding:8px">Tồn kho hiện tại</th>
            <th style="width:130px;text-align:center;padding:8px">Số lượng chuyển</th>
            <th style="width:50px"></th>
          </tr>
        </thead>
        <tbody id="transferItemsBody">
        </tbody>
      </table>
      <div id="emptyItemsMsg" style="padding:30px;text-align:center;color:#94a3b8;font-size:13px">
        Chưa chọn phụ tùng nào. Gõ tên hoặc mã SKU vào ô phía trên để tìm và thêm sản phẩm.
      </div>
    </div>
  </div>

  <div style="margin-bottom:24px">
    <label style="display:block;font-weight:700;font-size:13px;color:#1a3258;margin-bottom:6px">Ghi chú vận chuyển / Mã vận đơn</label>
    <textarea name="note" rows="3" placeholder="Ghi chú người vận chuyển, số điện thoại tài xế, mã vận đơn..." style="width:100%;border:1px solid #d8e0ea;border-radius:6px;padding:10px;font-size:14px"></textarea>
  </div>

  <div style="display:flex;gap:12px;justify-content:flex-end">
    <a href="/admin/stock-transfers" class="btn btn-outline">Hủy bỏ</a>
    <button type="submit" class="btn btn-navy" style="padding:10px 24px">+ Tạo phiếu chuyển kho</button>
  </div>
</form>

<script>
const searchInput = document.getElementById('productSearchInput');
const searchDropdown = document.getElementById('productSearchDropdown');
const itemsBody = document.getElementById('transferItemsBody');
const emptyMsg = document.getElementById('emptyItemsMsg');
let debounceTimer;

searchInput.addEventListener('input', function() {
  clearTimeout(debounceTimer);
  const q = this.value.trim();
  if (q.length < 1) {
    searchDropdown.style.display = 'none';
    return;
  }
  debounceTimer = setTimeout(() => {
    fetch('/admin/api/products/search?q=' + encodeURIComponent(q))
      .then(res => res.json())
      .then(data => {
        searchDropdown.innerHTML = '';
        if (!data || data.length === 0) {
          searchDropdown.innerHTML = '<div class="search-item" style="color:#94a3b8">Không tìm thấy phụ tùng phù hợp.</div>';
        } else {
          data.forEach(p => {
            const div = document.createElement('div');
            div.className = 'search-item';
            div.innerHTML = `<strong>${escapeHtml(p.name)}</strong><br><small style="color:#64748b">SKU: ${escapeHtml(p.sku || '—')} | OEM: ${escapeHtml(p.oem_code || '—')} | Tồn kho: <strong style="color:#0284c7">${p.stock}</strong></small>`;
            div.addEventListener('click', () => {
              addProductToTransfer(p);
              searchDropdown.style.display = 'none';
              searchInput.value = '';
            });
            searchDropdown.appendChild(div);
          });
        }
        searchDropdown.style.display = 'block';
      });
  }, 150);
});

document.addEventListener('click', function(e) {
  if (e.target !== searchInput && e.target !== searchDropdown) {
    searchDropdown.style.display = 'none';
  }
});

function addProductToTransfer(p) {
  const existingRow = document.getElementById('row-product-' + p.id);
  if (existingRow) {
    const qtyInput = existingRow.querySelector('.qty-input');
    qtyInput.value = parseInt(qtyInput.value) + 1;
    return;
  }

  emptyMsg.style.display = 'none';
  const rowIdx = itemsBody.children.length;

  const tr = document.createElement('tr');
  tr.id = 'row-product-' + p.id;
  tr.style.borderTop = '1px solid #edf1f5';
  tr.innerHTML = `
    <td style="padding:10px 8px">
      <input type="hidden" name="items[${rowIdx}][product_id]" value="${p.id}">
      <strong style="color:#1a3258">${escapeHtml(p.name)}</strong>
    </td>
    <td style="text-align:center;padding:10px 8px;font-family:monospace;font-size:12px;color:#64748b">
      ${escapeHtml(p.sku || p.oem_code || '—')}
    </td>
    <td style="text-align:center;padding:10px 8px;font-weight:700;color:#0284c7">
      ${p.stock}
    </td>
    <td style="text-align:center;padding:10px 8px">
      <input type="number" name="items[${rowIdx}][quantity]" value="1" min="1" required class="qty-input" style="width:90px;height:36px;border:1px solid #cbd5e1;border-radius:6px;padding:0 8px;text-align:center;font-weight:700;font-size:14px">
    </td>
    <td style="text-align:center;padding:10px 8px">
      <button type="button" onclick="removeTransferRow(this)" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:18px;line-height:1">×</button>
    </td>
  `;
  itemsBody.appendChild(tr);
}

function removeTransferRow(btn) {
  btn.closest('tr').remove();
  if (itemsBody.children.length === 0) {
    emptyMsg.style.display = 'block';
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
