<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Sơ đồ & Vị trí kho (Kệ / Tầng / Khay)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Định nghĩa vị trí lưu trữ phụ tùng giúp nhân viên kho định vị và lấy hàng nhanh chóng.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importLocModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    <a href="/admin/locations/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newLocationModal').style.display='flex'" class="btn btn-navy">+ Thêm vị trí mới</button>
  </div>
</div>

<div id="importLocModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/locations/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập Vị trí kho từ CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>Location_Code, Area_Name, Shelf_Name, Bin_Name, Note</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importLocModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên & Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.loc-table{width:100%;border-collapse:collapse;background:#fff}
.loc-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.loc-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.loc-badge{background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-family:monospace;font-weight:700}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="loc-table">
  <thead>
    <tr>
      <th>Mã vị trí</th>
      <th>Khu vực / Kệ</th>
      <th>Tầng</th>
      <th>Khay / Ô</th>
      <th>Số phụ tùng đang xếp</th>
      <th>Ghi chú</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($locations as $loc): ?>
    <tr>
      <td><span class="loc-badge"><?= e($loc['code'] ?? '') ?></span></td>
      <td style="font-weight:700;color:#1a3258"><?= e($loc['area_name'] ?? '') ?></td>
      <td><?= e($loc['shelf_name'] ?: '—') ?></td>
      <td><?= e($loc['bin_name'] ?: '—') ?></td>
      <td>
        <button type="button" onclick="openLocProductsModal('<?= e($loc['code']) ?>')" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;padding:4px 10px;border-radius:6px;font-weight:700;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px" title="Bấm để xem danh sách chi tiết các mã phụ tùng đang lưu trữ tại vị trí này">
          📦 <?= number_format((int)($loc['product_count'] ?? 0)) ?> mã SP (Xem danh sách)
        </button>
      </td>
      <td style="font-size:12px;color:#64748b"><?= e($loc['note'] ?: '—') ?></td>
      <td>
        <form method="post" action="/admin/locations/<?= (int)$loc['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa vị trí này?')">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <button type="submit" class="btn btn-outline" style="padding:3px 8px;font-size:11px;color:#dc2626">Xóa</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$locations): ?>
    <tr><td colspan="7" style="padding:30px;text-align:center;color:#9ca3af">Chưa có vị trí kho nào. Bấm nút "+ Thêm vị trí mới" để tạo.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/locations?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/locations?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newLocationModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form id="locForm" method="post" action="/admin/locations" style="background:#fff;padding:24px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Thêm vị trí kho mới</h3>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Mã vị trí (VD: KE-A-T1-K05) <span style="color:#e11d48">*</span></label>
      <input type="text" name="code" required pattern="[A-Z0-9\-]{3,30}" oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9\-]/g,'')" placeholder="Mã viết liền không dấu (VD: KE-A-T1-K05)" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;font-family:monospace;font-weight:700">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Khu vực / Kệ <span style="color:#e11d48">*</span> <span id="areaCharCnt" style="font-weight:400;color:#64748b">(0/50 ký tự)</span></label>
      <input type="text" id="loc_area" name="area_name" required maxlength="50" placeholder="Ví dụ: Kệ A, Kệ B, Khu Vực 1 (tối đa 50 ký tự)..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
      <div>
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tầng <span id="shelfCharCnt" style="font-weight:400;color:#64748b">(0/50 ký tự)</span></label>
        <input type="text" id="loc_shelf" name="shelf_name" maxlength="50" placeholder="Tầng 1, Tầng 2..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
      </div>
      <div>
        <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Khay / Ô <span id="binCharCnt" style="font-weight:400;color:#64748b">(0/50 ký tự)</span></label>
        <input type="text" id="loc_bin" name="bin_name" maxlength="50" placeholder="Khay 05, Ô 12..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
      </div>
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Ghi chú <span id="noteWordCnt" style="font-weight:400;color:#64748b">(0/200 từ)</span></label>
      <textarea id="loc_note" name="note" rows="3" placeholder="Mô tả loại phụ tùng lưu ở đây (tối đa 200 từ)..." style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;font-size:13px"></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newLocationModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu vị trí</button>
    </div>
  </form>
</div>

<!-- Modal xem & gán danh sách phụ tùng tại vị trí kho -->
<div id="locProductsModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;padding:24px;border-radius:10px;max-width:780px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
      <h3 style="margin:0;color:#1a3258;font-size:16px">Danh sách phụ tùng lưu tại vị trí: <span id="modalLocCode" style="color:#0284c7;font-family:monospace;font-weight:700"></span></h3>
      <button type="button" onclick="closeLocProductsModal()" style="background:none;border:none;font-size:22px;color:#64748b;cursor:pointer">&times;</button>
    </div>

    <!-- Khung gán thêm phụ tùng vào vị trí kho -->
    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px 16px;margin-bottom:16px">
      <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:8px">➕ Gán sản phẩm phụ tùng vào vị trí kho này</div>
      <div style="display:flex;gap:8px;position:relative">
        <input type="hidden" id="assign_product_id">
        <input type="text" id="assign_product_search" placeholder="Nhập tên sản phẩm, SKU hoặc OEM để tìm và gán..." autocomplete="off" style="flex:1;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
        <button type="button" onclick="submitAssignProduct()" class="btn btn-navy" style="height:38px;padding:0 16px;white-space:nowrap">+ Gán vị trí</button>
        <div id="assign_product_suggestions" style="display:none;position:absolute;top:100%;left:0;right:110px;background:#fff;border:1px solid #cbd5e1;border-radius:6px;max-height:220px;overflow-y:auto;z-index:99999;box-shadow:0 4px 14px rgba(0,0,0,0.18);margin-top:2px"></div>
      </div>
      <div id="assign_msg" style="margin-top:6px;font-size:12px;font-weight:600"></div>
    </div>

    <div id="locProductsContent">Đang tải danh sách phụ tùng...</div>

    <div style="display:flex;justify-content:flex-end;margin-top:16px">
      <button type="button" onclick="closeLocProductsModal()" class="btn btn-navy">Đóng & Cập nhật</button>
    </div>
  </div>
</div>

<script>
var currentLocCode = '';

(function(){
  function setupCharCounter(inputEl, maxChars, counterEl) {
    if(!inputEl) return;
    function update() {
      var len = inputEl.value.length;
      if(counterEl) {
        counterEl.textContent = '(' + len + '/' + maxChars + ' ký tự)';
        counterEl.style.color = len >= maxChars ? '#e11d48' : '#64748b';
      }
    }
    ['input', 'keyup', 'change', 'paste'].forEach(function(evt) {
      inputEl.addEventListener(evt, update);
    });
    update();
  }

  function enforceLiveWordLimit(inputEl, maxWords, counterEl) {
    if(!inputEl) return;
    function update() {
      var text = inputEl.value;
      var words = text.trim() ? text.trim().split(/\s+/) : [];
      if(words.length > maxWords) {
        var regex = new RegExp('^(?:\\s*\\S+){' + maxWords + '}');
        var match = text.match(regex);
        if(match) {
          inputEl.value = match[0];
          words = inputEl.value.trim().split(/\s+/);
        }
      }
      if(counterEl) {
        counterEl.textContent = '(' + words.length + '/' + maxWords + ' từ)';
        counterEl.style.color = words.length >= maxWords ? '#e11d48' : '#64748b';
      }
    }
    ['input', 'keyup', 'change', 'paste'].forEach(function(evt) {
      inputEl.addEventListener(evt, update);
    });
    update();
  }

  setupCharCounter(document.getElementById('loc_area'), 50, document.getElementById('areaCharCnt'));
  setupCharCounter(document.getElementById('loc_shelf'), 50, document.getElementById('shelfCharCnt'));
  setupCharCounter(document.getElementById('loc_bin'), 50, document.getElementById('binCharCnt'));
  enforceLiveWordLimit(document.getElementById('loc_note'), 200, document.getElementById('noteWordCnt'));

  // Auto-complete gán sản phẩm vào vị trí kho
  var searchInput = document.getElementById('assign_product_search');
  var hiddenInput = document.getElementById('assign_product_id');
  var sugBox = document.getElementById('assign_product_suggestions');

  if(searchInput && sugBox) {
    searchInput.addEventListener('input', function(){
      var q = searchInput.value.trim();
      hiddenInput.value = '';
      if(q.length < 2) { sugBox.style.display = 'none'; return; }
      fetch('/admin/locations/api/search-products?q=' + encodeURIComponent(q))
        .then(function(r){ return r.json(); })
        .then(function(items){
          if(!items || !items.length) {
            sugBox.innerHTML = '<div style="padding:10px;font-size:12px;color:#94a3b8;text-align:center">Không tìm thấy sản phẩm</div>';
          } else {
            sugBox.innerHTML = items.map(function(item){
              var locText = item.location_code ? ' [Đang ở: ' + item.location_code + ']' : '';
              return '<div class="as-item" data-id="' + item.id + '" data-label="' + escapeHtml(item.sku + ' - ' + item.name) + '" style="padding:8px 12px;font-size:12.5px;cursor:pointer;border-bottom:1px solid #f1f5f9"><strong>' + escapeHtml(item.sku) + '</strong> - ' + escapeHtml(item.name) + '<span style="color:#0284c7;font-size:11px;font-weight:600">' + escapeHtml(locText) + '</span></div>';
            }).join('');
          }
          sugBox.style.display = 'block';
        });
    });

    sugBox.addEventListener('click', function(e){
      var item = e.target.closest('.as-item');
      if(item) {
        hiddenInput.value = item.getAttribute('data-id');
        searchInput.value = item.getAttribute('data-label');
        sugBox.style.display = 'none';
      }
    });

    document.addEventListener('click', function(e){
      if(!searchInput.contains(e.target) && !sugBox.contains(e.target)) sugBox.style.display = 'none';
    });
  }
})();

function openLocProductsModal(code) {
  currentLocCode = code;
  var modal = document.getElementById('locProductsModal');
  var codeSpan = document.getElementById('modalLocCode');
  codeSpan.textContent = code;
  document.getElementById('assign_product_id').value = '';
  document.getElementById('assign_product_search').value = '';
  document.getElementById('assign_msg').textContent = '';
  modal.style.display = 'flex';
  loadLocProducts(code);
}

function closeLocProductsModal() {
  document.getElementById('locProductsModal').style.display = 'none';
  window.location.reload();
}

function loadLocProducts(code) {
  var contentDiv = document.getElementById('locProductsContent');
  contentDiv.innerHTML = '<div style="padding:20px;text-align:center;color:#64748b">⏳ Đang tải dữ liệu phụ tùng...</div>';

  fetch('/admin/locations/api/products?code=' + encodeURIComponent(code))
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if(!data.success || !data.products || data.products.length === 0) {
        contentDiv.innerHTML = '<div style="padding:25px;text-align:center;color:#9ca3af;font-size:14px">Chưa có mã phụ tùng nào được xếp lưu trữ tại vị trí <strong>' + escapeHtml(code) + '</strong>. Hãy chọn sản phẩm từ ô ở trên để gán vào vị trí này.</div>';
        return;
      }
      var html = '<table style="width:100%;border-collapse:collapse;margin-top:10px">';
      html += '<thead><tr style="background:#f8fafc;font-size:12px;color:#475569;text-align:left;border-bottom:2px solid #e2e8f0"><th style="padding:10px 8px">Tên sản phẩm phụ tùng</th><th style="padding:10px 8px">Mã SKU</th><th style="padding:10px 8px">Mã OEM</th><th style="padding:10px 8px;text-align:center">Tồn kho hiện tại</th><th style="padding:10px 8px;text-align:center">Thao tác</th></tr></thead><tbody>';
      data.products.forEach(function(p) {
        html += '<tr style="border-bottom:1px solid #f1f5f9;font-size:13px">';
        html += '<td style="padding:10px 8px;font-weight:600;color:#1e293b">' + escapeHtml(p.name) + '</td>';
        html += '<td style="padding:10px 8px;font-family:monospace;color:#64748b">' + escapeHtml(p.sku || '—') + '</td>';
        html += '<td style="padding:10px 8px;font-family:monospace;color:#0284c7">' + escapeHtml(p.oem_code || '—') + '</td>';
        html += '<td style="padding:10px 8px;text-align:center;font-weight:700;color:#059669">' + (p.stock || 0) + '</td>';
        html += '<td style="padding:10px 8px;text-align:center"><button type="button" onclick="unassignProduct(' + p.id + ')" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;cursor:pointer">❌ Bỏ gán</button></td>';
        html += '</tr>';
      });
      html += '</tbody></table>';
      contentDiv.innerHTML = html;
    })
    .catch(function(err) {
      contentDiv.innerHTML = '<div style="padding:20px;text-align:center;color:#dc2626">Lỗi tải dữ liệu. Vui lòng thử lại.</div>';
    });
}

function submitAssignProduct() {
  var pid = document.getElementById('assign_product_id').value;
  var msg = document.getElementById('assign_msg');
  if(!pid) {
    msg.style.color = '#dc2626';
    msg.textContent = 'Vui lòng gõ tìm và chọn một sản phẩm từ danh sách gợi ý.';
    return;
  }
  msg.style.color = '#0284c7';
  msg.textContent = '⏳ Đang lưu gán vị trí kho...';

  var formData = new FormData();
  formData.append('_csrf', '<?= csrfToken() ?>');
  formData.append('code', currentLocCode);
  formData.append('product_id', pid);

  fetch('/admin/locations/api/assign-product', { method: 'POST', body: formData })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if(res.success) {
        msg.style.color = '#15803d';
        msg.textContent = res.message;
        document.getElementById('assign_product_id').value = '';
        document.getElementById('assign_product_search').value = '';
        loadLocProducts(currentLocCode);
      } else {
        msg.style.color = '#dc2626';
        msg.textContent = res.message || 'Gán vị trí thất bại.';
      }
    })
    .catch(function(){
      msg.style.color = '#dc2626';
      msg.textContent = 'Lỗi kết nối máy chủ.';
    });
}

function unassignProduct(pid) {
  if(!confirm('Bạn có chắc muốn gỡ sản phẩm này khỏi vị trí kho ' + currentLocCode + '?')) return;

  var formData = new FormData();
  formData.append('_csrf', '<?= csrfToken() ?>');
  formData.append('product_id', pid);

  fetch('/admin/locations/api/unassign-product', { method: 'POST', body: formData })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if(res.success) {
        loadLocProducts(currentLocCode);
      } else {
        alert(res.message || 'Gỡ vị trí thất bại.');
      }
    });
}

function escapeHtml(str) {
  if(!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
