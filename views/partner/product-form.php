<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<!-- Quill.js rich text editor -->
<link href="/css/quill.snow.css" rel="stylesheet">
<script src="/js/quill.min.js"></script>

<style>
.seo-panel{border:1px solid #e6c860;border-radius:6px;margin-bottom:0}
.seo-panel .panel-head{background:linear-gradient(90deg,#fffbe6,#fff8d0);border-color:#e6c860}
.seo-meter{height:6px;border-radius:3px;background:#eee;overflow:hidden;margin:6px 0}
.seo-meter-bar{height:100%;border-radius:3px;transition:width 0.4s,background 0.4s}
.seo-checklist{list-style:none;padding:0;margin:0;font-size:12px}
.seo-checklist li{padding:4px 0;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:8px}
.seo-checklist li:last-child{border:none}
.seo-check{width:14px;height:14px;border-radius:50%;flex-shrink:0}
.seo-check.ok{background:#27ae60}
.seo-check.warn{background:#e67e22}
.seo-check.err{background:#e74c3c}
.char-count{font-size:11px;float:right;color:#999}
.char-count.warn{color:#e67e22}
.char-count.err{color:#e74c3c}
/* Quill customizations */
.ql-toolbar{border-radius:6px 6px 0 0 !important;border:1px solid var(--line) !important;background:#fafafa}
.ql-container{border:1px solid var(--line) !important;border-top:none !important;border-radius:0 0 6px 6px !important;font-size:14px;min-height:260px}
.ql-editor{min-height:260px;font-family:inherit;line-height:1.8}
.ql-editor h1{font-size:28px;font-weight:700}
.ql-editor h2{font-size:22px;font-weight:700}
.ql-editor h3{font-size:18px;font-weight:600}
.ql-editor h4{font-size:16px;font-weight:600}
.ql-editor h5{font-size:14px;font-weight:600}
.ql-editor h6{font-size:13px;font-weight:600;color:#666}
.form-layout{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}
@media(max-width:900px){.form-layout{grid-template-columns:1fr}}
.sidebar-panel{display:flex;flex-direction:column;gap:16px;position:sticky;top:80px}

/* ── Word-like Table Grid Picker ── */
.ql-table-btn { position:relative; display:inline-block; }
.ql-table-btn button.ql-table-trigger {
  background:none; border:none; cursor:pointer; padding:6px 8px;
  font-size:13px; color:#444; font-weight:600; display:flex; align-items:center; gap:4px;
  border-radius:4px; transition:background .15s;
}
.ql-table-btn button.ql-table-trigger:hover { background:#e8e8e8; }
.ql-table-btn button.ql-table-trigger svg { width:18px; height:18px; fill:#444; }
.table-grid-popup {
  display:none; position:absolute; top:100%; left:0; z-index:9999;
  background:#fff; border:1px solid #ccc; border-radius:8px;
  box-shadow:0 8px 24px rgba(0,0,0,.18); padding:10px 12px 8px;
  min-width:200px;
}
.table-grid-popup.active { display:block; }
.table-grid-popup .grid-label {
  font-size:12px; font-weight:700; color:#1a3258; text-align:center;
  margin-bottom:6px; min-height:18px;
}
.table-grid-cells {
  display:grid; grid-template-columns:repeat(8,22px); gap:2px;
  justify-content:center;
}
.table-grid-cells .cell {
  width:22px; height:22px; border:1px solid #d0d0d0; border-radius:2px;
  cursor:pointer; transition:background .1s, border-color .1s;
  background:#fff;
}
.table-grid-cells .cell.active {
  background:#dbeafe; border-color:#3b82f6;
}
.table-grid-cells .cell:hover {
  background:#bfdbfe; border-color:#2563eb;
}
.table-grid-popup .grid-actions {
  margin-top:8px; padding-top:8px; border-top:1px solid #eee;
  display:flex; gap:6px; justify-content:center;
}
.table-grid-popup .grid-actions button {
  font-size:11px; padding:4px 12px; border:1px solid #d0d5dd;
  border-radius:4px; background:#f8f9fa; cursor:pointer; color:#333;
  font-weight:500; transition:all .15s;
}
.table-grid-popup .grid-actions button:hover {
  background:#1a3258; color:#fff; border-color:#1a3258;
}
/* Tables inside Quill editor */
.ql-editor table { border-collapse:collapse; width:100%; margin:12px 0; }
.ql-editor table th { background:#0b1d3a; color:#fff; font-weight:700; padding:10px 14px; border:1px solid #ddd; text-align:left; }
.ql-editor table td { padding:10px 14px; border:1px solid #ddd; text-align:left; }
.ql-editor table tr:nth-child(even) td { background:#f8f9fc; }

</style>

<div class="dash-head">
  <h1><?= isset($product) ? ' Chỉnh sửa sản phẩm' : ' Đăng sản phẩm mới' ?></h1>
  <?php if(isset($product)):?>
    <span class="badge-status status-<?= $product['status'] ?>"><?= $product['status'] ?></span>
  <?php endif;?>
</div>

<form method="post" action="<?= isset($product) ? '/partner/products/'.$product['id'].'/edit' : '/partner/products/new' ?>"
      enctype="multipart/form-data" id="productForm">
  <?= csrfField() ?>
  <!-- Hidden field for Quill HTML content -->
  <input type="hidden" name="description" id="descHidden">
  <input type="hidden" name="short_specs" id="specsHidden">

  <div class="form-layout">
    <!-- LEFT COLUMN: Main content -->
    <div>

      <!-- 1. TIÊU ĐỀ + SLUG -->
      <div class="panel">
        <div class="panel-head"><h3> Thông tin cơ bản</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Tên sản phẩm (H1) <span class="req">*</span>
              <span class="char-count" id="nameCount">0/150</span>
            </label>
            <input type="text" name="name" id="nameInput" required minlength="10" maxlength="150"
                   value="<?= e($product['name']??'') ?>"
                   placeholder="Tên đầy đủ, rõ ràng — sẽ là H1 trên trang sản phẩm"
                   oninput="updateCounts()">
          </div>

          <div class="form-group">
            <label>Slug URL (SEO friendly)</label>
            <div style="display:flex;gap:8px">
              <input type="text" name="slug" id="slugInput" value="<?= e($product['slug']??'') ?>"
                     placeholder="ten-san-pham-theo-url" style="flex:1">
              <button type="button" onclick="autoSlug()" class="btn btn-outline-navy btn-sm">Tạo slug</button>
            </div>
            <div class="fs-12 text-muted mt-1">coolingsystems.vn/products/<span id="slugPreview" style="color:var(--navy);font-weight:600"><?= e($product['slug']??'...') ?></span></div>
          </div>

          <div class="form-row">
            <div class="form-group"><label>Mã SP nội bộ (SKU) <span class="req">*</span></label>
              <input type="text" name="sku" required value="<?= e($product['sku']??'') ?>"></div>
            <div class="form-group"><label>Mã OEM</label>
              <input type="text" name="oem_code" value="<?= e($product['oem_code']??'') ?>" placeholder="VD: PFR6V"></div>
          </div>

          <div class="form-row">
            <div class="form-group"><label>Thương hiệu</label>
              <input type="text" name="part_brand" value="<?= e($product['part_brand']??'') ?>" placeholder="VD: Bosch, NGK, PMC"></div>
            <div class="form-group"><label>Danh mục <span class="req">*</span></label>
              <select name="category_id" required>
                <option value="">— Chọn danh mục —</option>
                <?php foreach($categories as $c):?>
                  <option value="<?=$c['id']?>" <?= isset($product)&&$product['category_id']==$c['id']?'selected':'' ?>>
                    <?= $c['parent_id']?'&nbsp;&nbsp;— ':'' ?><?= e($c['name']) ?>
                  </option>
                <?php endforeach;?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. MÔ TẢ CHI TIẾT — Quill Editor -->
      <div class="panel">
        <div class="panel-head">
          <h3> Mô tả sản phẩm <span class="req">*</span></h3>
          <div class="fs-12 text-muted">Hỗ trợ H1–H6, Bold, Italic, Danh sách, Bảng, Hình ảnh, Link</div>
        </div>
        <div class="panel-body" style="padding-bottom:0">
          <div id="quillDesc"><?= isset($product) ? $product['description'] : '' ?></div>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Sử dụng H2 cho tiêu đề mục, H3 cho tiêu đề con. H1 chỉ dùng trong tên sản phẩm phía trên.
          </div>
        </div>
      </div>

      <!-- 3. THÔNG SỐ KỸ THUẬT -->
      <div class="panel">
        <div class="panel-head"><h3> Thông số kỹ thuật</h3></div>
        <div class="panel-body" style="padding-bottom:0">
          <div id="quillSpecs"><?= isset($product) ? ($product['short_specs']??'') : '' ?></div>
        </div>
      </div>

      <!-- 4. HÌNH ẢNH -->
      <div class="panel">
        <div class="panel-head"><h3> Hình ảnh (tối đa 8, Alt text = tên SP)</h3></div>
        <div class="panel-body">
          <?php if(!empty($images)):?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
              <?php foreach($images as $img):?>
                <div style="position:relative">
                  <img src="/uploads/products/<?= e(implode('/', array_map('rawurlencode', explode('/', $img['file_path'])))) ?>" loading="lazy" style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--line)" onerror="this.onerror=null;this.src='/img/placeholder.png'">
                </div>
              <?php endforeach;?>
            </div>
          <?php endif;?>
          <div class="form-group">
            <label>Thêm ảnh mới</label>
            <input type="file" name="images[]" multiple accept="image/*" id="imgInput" onchange="previewImgs(this)">
          </div>
          <div id="imgPreviewRow" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>
        </div>
      </div>

    </div><!-- /left col -->

    <!-- RIGHT SIDEBAR -->
    <div class="sidebar-panel">

      <!-- PUBLISH SETTINGS -->
      <div class="panel">
        <div class="panel-head"><h3> Đăng bài</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
              <option value="draft" <?= ($product['status']??'')==='draft'?'selected':'' ?>>Bản nháp</option>
              <option value="pending" <?= ($product['status']??'')==='pending'?'selected':'' ?>>Chờ duyệt</option>
              <?php if(($user['role']??'')==='admin'):?>
                <option value="published" <?= ($product['status']??'')==='published'?'selected':'' ?>>Xuất bản</option>
              <?php endif;?>
            </select>
          </div>
          <button type="submit" class="btn btn-gold btn-block btn-lg" style="margin-top:8px">
            <?= isset($product) ? ' Cập nhật SP' : ' Gửi chờ duyệt' ?>
          </button>
        </div>
      </div>

      <!-- GIÁ & TỒN KHO -->
      <div class="panel">
        <div class="panel-head"><h3> Giá & Tồn kho</h3></div>
        <div class="panel-body">
          <div class="form-group"><label>Giá bán (VND) <span class="req">*</span></label>
            <input type="number" name="price" required min="1000" value="<?= $product['price']??'' ?>" placeholder="VD: 480000"></div>
          <div class="form-group"><label>Giá gốc (để gạch ngang)</label>
            <input type="number" name="original_price" value="<?= $product['original_price']??'' ?>"></div>
          <div class="form-group"><label>Tồn kho <span class="req">*</span></label>
            <input type="number" name="stock" required min="0" value="<?= $product['stock']??0 ?>"></div>
          <div class="form-group"><label>Bảo hành (tháng)</label>
            <input type="number" name="warranty_months" value="<?= $product['warranty_months']??12 ?>" min="0"></div>
        </div>
      </div>

      <!-- SEO PANEL -->
      <div class="panel seo-panel">
        <div class="panel-head"><h3> Cài đặt SEO</h3></div>
        <div class="panel-body">

          <!-- SEO Score -->
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600">
              <span>Điểm SEO</span>
              <span id="seoScoreLabel" style="color:#e67e22">0 / 100</span>
            </div>
            <div class="seo-meter"><div class="seo-meter-bar" id="seoBar" style="width:0%;background:#e67e22"></div></div>
          </div>

          <div class="form-group">
            <label> Từ khóa trọng tâm (Focus keyword)</label>
            <input type="text" name="focus_keyword" id="focusKw" value="<?= e($product['focus_keyword']??'') ?>"
                   placeholder="VD: bugi NGK Platinum" oninput="updateSEO()">
          </div>

          <div class="form-group">
            <label>Meta Title (tiêu đề Google)
              <span class="char-count" id="mtCount">0/60</span>
            </label>
            <input type="text" name="meta_title" id="metaTitle"
                   value="<?= e($product['meta_title']??'') ?>"
                   placeholder="Tiêu đề hiển thị trên Google (50–60 ký tự)"
                   maxlength="70" oninput="updateSEO()">
            <!-- Google preview -->
            <div style="margin-top:8px;padding:10px;background:#f8f9fa;border-radius:4px;font-size:12px">
              <div style="color:#1a0dab;font-size:14px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" id="gpTitle">Tiêu đề sẽ hiển thị tại đây...</div>
              <div style="color:#006621;font-size:11px">coolingsystems.vn › products › <span id="gpSlug">slug</span></div>
              <div style="color:#545454;line-height:1.5" id="gpDesc">Mô tả ngắn sẽ hiển thị tại đây...</div>
            </div>
          </div>

          <div class="form-group">
            <label>Meta Description (mô tả Google)
              <span class="char-count" id="mdCount">0/160</span>
            </label>
            <textarea name="meta_description" id="metaDesc" rows="3"
                      maxlength="170" oninput="updateSEO()"
                      placeholder="Mô tả ngắn hiển thị trên Google (120–160 ký tự)"><?= e($product['meta_description']??'') ?></textarea>
          </div>

          <div class="form-group">
            <label>Canonical URL (để trống = tự động)</label>
            <input type="text" name="canonical_url" value="<?= e($product['canonical_url']??'') ?>"
                   placeholder="https://coolingsystems.vn/products/...">
          </div>

          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px">
              <input type="checkbox" name="is_indexed" value="1" <?= ($product['is_indexed']??1)?'checked':'' ?>>
              Cho phép Google lập chỉ mục
            </label>
          </div>

          <!-- SEO Checklist -->
          <div style="border-top:1px solid #f0f0f0;padding-top:10px">
            <div class="fs-12 fw-700 text-muted mb-1"> Kiểm tra SEO</div>
            <ul class="seo-checklist" id="seoChecklist">
              <li><span class="seo-check err" id="ck1"></span> <span id="ck1t">Nhập từ khóa trọng tâm</span></li>
              <li><span class="seo-check err" id="ck2"></span> <span id="ck2t">Từ khóa trong tên SP</span></li>
              <li><span class="seo-check err" id="ck3"></span> <span id="ck3t">Meta title có từ khóa (50–60 ký tự)</span></li>
              <li><span class="seo-check err" id="ck4"></span> <span id="ck4t">Meta description đủ dài (120–160 ký tự)</span></li>
              <li><span class="seo-check err" id="ck5"></span> <span id="ck5t">Mô tả SP ≥ 300 ký tự</span></li>
              <li><span class="seo-check err" id="ck6"></span> <span id="ck6t">Có ảnh sản phẩm</span></li>
              <li><span class="seo-check err" id="ck7"></span> <span id="ck7t">Slug URL đã được đặt</span></li>
            </ul>
          </div>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div><!-- /form-layout -->
</form>

<script>
// ── Quill init for Description ────────────────────────────────────────────
var toolbarOpts = [
  [{'header': [1,2,3,4,5,6,false]}],
  ['bold','italic','underline','strike'],
  ['blockquote','code-block'],
  [{'list':'ordered'},{'list':'bullet'}],
  [{'color':[]},{'background':[]}],
  [{'align':[]}],
  ['link','image'],
  ['clean']
];

var quillDesc = new Quill('#quillDesc', {
  theme: 'snow',
  placeholder: 'Viết mô tả chi tiết sản phẩm... Dùng H2 cho tiêu đề mục, H3 cho tiêu đề con...',
  modules: { toolbar: { container: toolbarOpts } }
});

var quillSpecs = new Quill('#quillSpecs', {
  theme: 'snow',
  placeholder: 'Nhập thông số kỹ thuật, kích thước, trọng lượng...',
  modules: { toolbar: [
    [{'header':[2,3,false]}],
    ['bold','italic'],
    [{'list':'ordered'},{'list':'bullet'}],
    ['clean']
  ]}
});

// ── Form submit: extract Quill HTML into hidden fields ──────────────────
document.getElementById('productForm').addEventListener('submit', function(e) {
  document.getElementById('descHidden').value = quillDesc.root.innerHTML;
  document.getElementById('specsHidden').value = quillSpecs.root.innerHTML;

  // Auto-fill meta title if empty
  var mt = document.getElementById('metaTitle');
  if (!mt.value.trim()) {
    mt.value = document.getElementById('nameInput').value.trim().substring(0,60);
  }

  // Auto-fill meta description if empty
  var md = document.getElementById('metaDesc');
  if (!md.value.trim()) {
    var text = quillDesc.getText().trim().replace(/\s+/g,' ');
    md.value = text.substring(0,160);
  }

  // Auto-generate slug if empty
  if (!document.getElementById('slugInput').value.trim()) autoSlug();
});

// ── Slug generator ─────────────────────────────────────────────────────
function autoSlug() {
  var name = document.getElementById('nameInput').value;
  var slug = name.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
    .replace(/đ/g,'d').replace(/[^a-z0-9\s-]/g,'')
    .trim().replace(/\s+/g,'-').replace(/-+/g,'-').substring(0,80);
  document.getElementById('slugInput').value = slug;
  document.getElementById('slugPreview').textContent = slug || '...';
  updateSEO();
}

document.getElementById('slugInput').addEventListener('input', function() {
  document.getElementById('slugPreview').textContent = this.value || '...';
  updateSEO();
});

// ── Char count and Google preview ─────────────────────────────────────
function updateCounts() {
  var name = document.getElementById('nameInput').value;
  var nc = document.getElementById('nameCount');
  nc.textContent = name.length + '/150';
  nc.className = 'char-count' + (name.length > 150 ? ' err' : name.length > 120 ? ' warn' : '');
  // Auto-fill meta title from name
  if (!document.getElementById('metaTitle').value) {
    document.getElementById('gpTitle').textContent = name.substring(0,60) || 'Tiêu đề sẽ hiển thị...';
  }
  updateSEO();
}

function updateSEO() {
  var kw = document.getElementById('focusKw').value.trim().toLowerCase();
  var name = document.getElementById('nameInput').value.trim();
  var mt = document.getElementById('metaTitle').value.trim();
  var md = document.getElementById('metaDesc').value.trim();
  var slug = document.getElementById('slugInput').value.trim();
  var descText = quillDesc ? quillDesc.getText().trim() : '';
  var hasImg = document.getElementById('imgInput') && document.getElementById('imgInput').files.length > 0;
  var hasExistImg = <?= !empty($images) ? 'true' : 'false' ?>;

  // Update char counts
  var mtc = document.getElementById('mtCount');
  mtc.textContent = mt.length+'/60';
  mtc.className = 'char-count'+(mt.length>70?' err':mt.length>60?' warn':'');

  var mdc = document.getElementById('mdCount');
  mdc.textContent = md.length+'/160';
  mdc.className = 'char-count'+(md.length>170?' err':md.length>160?' warn':'');

  // Google preview
  document.getElementById('gpTitle').textContent = mt || name.substring(0,60) || 'Tiêu đề...';
  document.getElementById('gpSlug').textContent = slug || 'slug';
  document.getElementById('gpDesc').textContent = md.substring(0,160) || 'Mô tả ngắn sẽ xuất hiện tại đây...';

  // Checklist
  var checks = [
    kw.length > 0,
    kw.length > 0 && name.toLowerCase().includes(kw),
    mt.length >= 30 && mt.length <= 70 && (kw.length === 0 || mt.toLowerCase().includes(kw)),
    md.length >= 100 && md.length <= 170,
    descText.length >= 300,
    hasImg || hasExistImg,
    slug.length > 0
  ];
  var msgs = [
    'Từ khóa trọng tâm: ' + (kw||'chưa nhập'),
    kw?'Từ khóa trong tên SP: '+(checks[1]?'':'không tìm thấy'):'Nhập từ khóa trước',
    'Meta title: '+mt.length+' ký tự '+(mt.length>=30&&mt.length<=70?'':'(cần 30–60)'),
    'Meta description: '+md.length+' ký tự '+(checks[3]?'':'(cần 120–160)'),
    'Mô tả: '+descText.length+' ký tự '+(checks[4]?'':'(cần ≥300)'),
    checks[5]?'Có ảnh sản phẩm ':'Chưa có ảnh',
    slug?'Slug: '+slug.substring(0,30):'Chưa có slug URL'
  ];

  var score = 0;
  checks.forEach(function(ok, i) {
    var ck = document.getElementById('ck'+(i+1));
    var ckt = document.getElementById('ck'+(i+1)+'t');
    if (ck) { ck.className = 'seo-check '+(ok?'ok':'err'); }
    if (ckt) ckt.textContent = msgs[i];
    if (ok) score += [15,15,20,20,15,10,5][i];
  });

  var bar = document.getElementById('seoBar');
  var label = document.getElementById('seoScoreLabel');
  bar.style.width = score+'%';
  bar.style.background = score>=80?'#27ae60':score>=50?'#e67e22':'#e74c3c';
  label.textContent = score + ' / 100';
  label.style.color = score>=80?'#27ae60':score>=50?'#e67e22':'#e74c3c';
}

// ── Image preview ────────────────────────────────────────────────────
function previewImgs(input) {
  var row = document.getElementById('imgPreviewRow');
  row.innerHTML = '';
  Array.from(input.files).forEach(function(f) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var img = document.createElement('img');
      img.src = e.target.result;
      img.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:4px;border:1px solid var(--line)';
      row.appendChild(img);
    };
    reader.readAsDataURL(f);
  });
  updateSEO();
}

// Init
updateCounts();
updateSEO();
quillDesc.on('text-change', function(){ updateSEO(); });


// ── Word-like Table Grid Picker for Quill ──
(function() {
  function createTableGridPicker(quillInstance) {
    var toolbar = quillInstance.container.previousSibling;
    if (!toolbar || !toolbar.classList.contains('ql-toolbar')) return;
    
    // Remove the default empty table-insert button if exists
    var defaultBtn = toolbar.querySelector('.ql-table-insert');
    if (defaultBtn) defaultBtn.style.display = 'none';
    
    // Create table button with grid popup
    var wrapper = document.createElement('span');
    wrapper.className = 'ql-table-btn ql-formats';
    
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'ql-table-trigger';
    btn.title = 'Chèn bảng';
    btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M3 3h18v18H3V3zm2 2v4h5V5H5zm7 0v4h5V5h-5zm5 6h-5v4h5v-4zm0 6h-5v4h5v-4zM10 17H5v4h5v-4zm0-6H5v4h5v-4z"/></svg> Bảng';
    
    var popup = document.createElement('div');
    popup.className = 'table-grid-popup';
    
    var label = document.createElement('div');
    label.className = 'grid-label';
    label.textContent = 'Chọn kích thước bảng';
    
    var grid = document.createElement('div');
    grid.className = 'table-grid-cells';
    
    var ROWS = 8, COLS = 8;
    var cells = [];
    for (var r = 0; r < ROWS; r++) {
      for (var c = 0; c < COLS; c++) {
        var cell = document.createElement('div');
        cell.className = 'cell';
        cell.setAttribute('data-row', r + 1);
        cell.setAttribute('data-col', c + 1);
        cells.push(cell);
        grid.appendChild(cell);
      }
    }
    
    // Hover highlight
    grid.addEventListener('mouseover', function(e) {
      if (!e.target.classList.contains('cell')) return;
      var hRow = parseInt(e.target.getAttribute('data-row'));
      var hCol = parseInt(e.target.getAttribute('data-col'));
      label.textContent = hRow + ' x ' + hCol + ' Bảng';
      cells.forEach(function(c) {
        var cr = parseInt(c.getAttribute('data-row'));
        var cc = parseInt(c.getAttribute('data-col'));
        if (cr <= hRow && cc <= hCol) {
          c.classList.add('active');
        } else {
          c.classList.remove('active');
        }
      });
    });
    
    grid.addEventListener('mouseleave', function() {
      label.textContent = 'Chọn kích thước bảng';
      cells.forEach(function(c) { c.classList.remove('active'); });
    });
    
    // Click to insert table
    grid.addEventListener('click', function(e) {
      if (!e.target.classList.contains('cell')) return;
      var rows = parseInt(e.target.getAttribute('data-row'));
      var cols = parseInt(e.target.getAttribute('data-col'));
      insertTable(quillInstance, rows, cols);
      popup.classList.remove('active');
    });
    
    // Quick actions
    var actions = document.createElement('div');
    actions.className = 'grid-actions';
    var presets = [
      {label: '3x3', r:3, c:3},
      {label: '4x2', r:4, c:2},
      {label: '5x3', r:5, c:3}
    ];
    presets.forEach(function(p) {
      var b = document.createElement('button');
      b.type = 'button';
      b.textContent = p.label;
      b.addEventListener('click', function() {
        insertTable(quillInstance, p.r, p.c);
        popup.classList.remove('active');
      });
      actions.appendChild(b);
    });
    
    popup.appendChild(label);
    popup.appendChild(grid);
    popup.appendChild(actions);
    wrapper.appendChild(btn);
    wrapper.appendChild(popup);
    toolbar.appendChild(wrapper);
    
    // Toggle popup
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      document.querySelectorAll('.table-grid-popup.active').forEach(function(p) {
        if (p !== popup) p.classList.remove('active');
      });
      popup.classList.toggle('active');
    });
  }
  
  function insertTable(quill, rows, cols) {
    var range = quill.getSelection(true);
    if (!range) quill.focus();
    range = quill.getSelection(true);
    
    var html = '<table><thead><tr>';
    for (var c = 0; c < cols; c++) {
      html += '<th>Cột ' + (c + 1) + '</th>';
    }
    html += '</tr></thead><tbody>';
    for (var r = 0; r < rows - 1; r++) {
      html += '<tr>';
      for (var c2 = 0; c2 < cols; c2++) {
        html += '<td>&nbsp;</td>';
      }
      html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    
    var index = range ? range.index : quill.getLength();
    quill.clipboard.dangerouslyPasteHTML(index, html);
  }
  
  // Close popups on outside click
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.ql-table-btn')) {
      document.querySelectorAll('.table-grid-popup.active').forEach(function(p) {
        p.classList.remove('active');
      });
    }
  });
  
  // Init for editors
  setTimeout(function() {
    if (typeof quillDesc !== 'undefined') createTableGridPicker(quillDesc);
    if (typeof quillFeat !== 'undefined') createTableGridPicker(quillFeat);
    if (typeof quillSpec !== 'undefined') createTableGridPicker(quillSpec);
  }, 500);
})();

</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
