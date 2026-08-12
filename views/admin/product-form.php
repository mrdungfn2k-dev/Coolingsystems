<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="/tinymce/tinymce.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style id="ts-chip-fix">
/* Chip Tom Select (Thương hiệu / Hãng xe) — đồng bộ màu navy hệ thống */
.ts-control .item { background:var(--navy) !important; color:#fff !important; border:1px solid var(--navy) !important; border-radius:6px !important; font-weight:600 !important; }
.ts-control .item.active { background:var(--navy-dark) !important; border-color:var(--navy-dark) !important; box-shadow:none !important; }
.ts-control .item .remove { color:#fff !important; border-left:1px solid rgba(255,255,255,.4) !important; }
.ts-control .item .remove:hover { background:rgba(255,255,255,.2) !important; color:#fff !important; }
.ts-dropdown .active { background:var(--navy) !important; color:#fff !important; }
.ts-wrapper.focus .ts-control { border-color:var(--navy) !important; box-shadow:0 0 0 3px rgba(26,50,88,.12) !important; }
</style>
<style>
input[type="checkbox"]{outline:none!important;box-shadow:none!important;border:none!important;accent-color:#1a3258;}
input[type="checkbox"]:focus{outline:none!important;box-shadow:none!important;}
.form-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:24px;align-items:start}
@media(max-width:900px){.form-layout{grid-template-columns:minmax(0,1fr)}}
.sidebar-panel{display:flex;flex-direction:column;gap:16px;position:sticky;top:80px}
.ql-toolbar{border-radius:6px 6px 0 0 !important;border:1px solid var(--line) !important;background:#fafafa}
.ql-container{border:1px solid var(--line) !important;border-top:none !important;border-radius:0 0 6px 6px !important;font-size:14px;min-height:300px}
.ql-editor{min-height:300px;font-family:inherit;line-height:1.8}
.ql-editor h1{font-size:28px;font-weight:800;color:#1a3258;margin:16px 0 8px}
.ql-editor h2{font-size:22px;font-weight:700;color:#1a3258;margin:14px 0 6px}
.ql-editor h3{font-size:18px;font-weight:700;color:#2c4a7c;margin:12px 0 5px}
.ql-editor h4{font-size:16px;font-weight:600;margin:10px 0 4px}
.ql-editor h5{font-size:14px;font-weight:600;margin:8px 0 4px}
.ql-editor h6{font-size:13px;font-weight:600;color:#666;margin:8px 0 4px}
.price-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}

.existing-img-wrap { cursor: grab; transition: transform 0.2s, box-shadow 0.2s; }
.existing-img-wrap:active { cursor: grabbing; }
.existing-img-wrap.sortable-ghost { opacity: 0.4; transform: scale(0.95); }
.existing-img-wrap.sortable-chosen { box-shadow: 0 4px 12px rgba(0,0,0,0.2); transform: scale(1.05); z-index: 10; }
.drag-handle-hint { position:absolute; bottom:0; left:0; right:0; background:rgba(26,50,88,0.85); color:#fff; font-size:9px; text-align:center; padding:2px 0; font-weight:600; pointer-events:none; }

</style>
<?php
$canEditProductCodes = $canEditProductCodes ?? true;
$returnTo = $returnTo ?? '/admin/products';
$formAction = isset($product)
  ? '/admin/products/'.$product['id'].'/edit?return_to='.rawurlencode($returnTo)
  : '/admin/products/new';
?>
<div class="dash-head">
  <h1><?= isset($product) ? ' Chỉnh sửa sản phẩm' : ' Đăng sản phẩm mới' ?></h1>
  <?php if(isset($product)):?><span class="badge-status status-<?= $product['status'] ?>"><?= $product['status'] ?></span><?php endif;?>
  <?php if(isset($product)):?><a href="<?= e($returnTo) ?>" style="margin-left:auto;color:#1a3258;font-size:13px;font-weight:700;text-decoration:none">← Quay lại danh sách</a><?php endif;?>
</div>

<form method="post" action="<?= e($formAction) ?>"
      enctype="multipart/form-data" id="productForm" autocomplete="off">
  <?= csrfField() ?>
  <?php if(isset($product)): ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><?php endif; ?>
  <input type="hidden" name="description" id="descHidden">

  <div class="form-layout">
    <!-- LEFT COLUMN -->
    <div>
      <!-- THÔNG TIN CƠ BẢN -->
      <div class="panel">
        <div class="panel-head"><h3> Thông tin cơ bản</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Tên sản phẩm <span class="req">*</span></label>
            <input type="text" name="name" id="productName" required minlength="5" maxlength="200"
                   style="font-family:'Times New Roman', Times, serif!important;font-size:16px;font-weight:600"
                   value="<?= e($product['name']??'') ?>" placeholder="Tên đầy đủ sản phẩm">
          </div>
          <div class="form-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <label style="margin:0">Đường dẫn URL</label>
              <button type="button" id="btnSyncSlug" onclick="forceSyncSlugLive()" style="background:none;border:none;color:#2563eb;font-size:12px;font-weight:600;cursor:pointer;padding:0">🔄 Tự động tạo theo Tên sản phẩm</button>
            </div>
            <input type="text" name="slug" id="productSlug" maxlength="180"
                   value="<?= e($product['slug']??'') ?>" placeholder="Tự động tạo từ tên sản phẩm">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Mã sản phẩm (SKU)</label>
              <input type="text" name="sku" <?= $canEditProductCodes ? '' : 'readonly' ?> value="<?= e($product['sku']??'') ?>" placeholder="Để trống sẽ dùng mã OEM">
            </div>
            <div class="form-group">
              <label>Mã OEM chính</label>
              <input type="text" name="oem_code" <?= $canEditProductCodes ? '' : 'readonly' ?> value="<?= e($product['oem_code']??'') ?>" placeholder="VD: 447710-8370">
              <small style="color:#64748b;font-size:11px">Có thể nhập nhiều mã, cách nhau dấu phẩy</small>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Mã OEM phụ / Cross-ref <span style="color:#94a3b8;font-weight:400;font-size:12px">(tùy chọn)</span></label>
              <input type="text" name="oem_code2" <?= $canEditProductCodes ? '' : 'readonly' ?> value="<?= e($product['oem_code2']??'') ?>" placeholder="VD: 8846048040 (OE Reference, mã đối chiếu)">
              <small style="color:#64748b;font-size:11px">Mã OEM thứ hai hoặc mã đối chiếu từ nhà sản xuất khác</small>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Thương hiệu</label>
              <input type="hidden" name="part_brand" id="partBrandHidden" value="<?= e($product['part_brand']??'') ?>">
              <?php
                $allBrands = dbAll("SELECT name FROM product_brands ORDER BY sort_order, name");
                $selectedBrands = ($product['part_brand']??'')==='HIDDEN' ? [] : array_map('trim', explode(',', $product['part_brand']??''));
              ?>
              <div id="brandBoxWrapper" style="<?= ($product['part_brand']??'')==='HIDDEN' ? 'opacity:0.45;pointer-events:none' : '' ?>">
                <select id="partBrandSelect" multiple placeholder="Chọn thương hiệu..." onchange="updateBrandHidden()" style="width:100%">
                  <?php foreach($allBrands as $pb): ?>
                    <option value="<?= e($pb['name']) ?>" <?= in_array(trim($pb['name']), $selectedBrands) ? 'selected' : '' ?>><?= e($pb['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <label style="display:flex;align-items:center;gap:8px;padding:8px 0;font-size:13px;cursor:pointer">
                <input type="checkbox" onchange="toggleBrandHide(this.checked)" <?= ($product['part_brand']??'')==="HIDDEN"?"checked":"" ?> style="width:14px;height:14px;cursor:pointer;accent-color:#dc2626">
                <span style="color:#dc2626;font-weight:500">Không hiển thị mục Thương hiệu</span>
              </label>
              
              <script>
              function updateBrandHidden(){
                var sel = document.getElementById('partBrandSelect');
                if(!sel) return;
                var vals = Array.from(sel.selectedOptions).map(o => o.value);
                document.getElementById('partBrandHidden').value = vals.join(', ');
              }
              function toggleBrandHide(hidden){
                var wrapper = document.getElementById('brandBoxWrapper');
                var hiddenField = document.getElementById('partBrandHidden');
                if(hidden){
                  wrapper.style.opacity='0.45';
                  wrapper.style.pointerEvents='none';
                  hiddenField.value='HIDDEN';
                } else {
                  wrapper.style.opacity='1';
                  wrapper.style.pointerEvents='auto';
                  updateBrandHidden();
                }
              }
              </script>
            </div>
            <div class="form-group">
              <label>Hãng xe <span style="font-weight:400;font-size:11px;color:#888">(chọn nhiều)</span></label>
              <?php
                $selectedCarBrands = [];
                if (isset($product) && $product['id']) {
                    $cbRows = dbAll("SELECT brand_id FROM product_brand_map WHERE product_id=?", [$product['id']]);
                    foreach ($cbRows as $sb) $selectedCarBrands[] = $sb['brand_id'];
                }
                if (empty($selectedCarBrands) && !empty($product['car_brand_id']) && $product['car_brand_id'] !== 'HIDDEN') {
                    $selectedCarBrands[] = intval($product['car_brand_id']);
                }
              ?>
              <div style="background:#fff;">
                <select id="carBrandSelect" name="car_brand_ids[]" multiple placeholder="Tìm và chọn hãng xe..." style="width:100%">
                  <option value="0" <?= empty($selectedCarBrands) ? 'selected' : '' ?>>— Tất cả / Chung —</option>
                  <option value="HIDDEN" <?= in_array('HIDDEN', array_map('strval', $selectedCarBrands)) ? 'selected' : '' ?>>Không hiển thị mục này</option>
                  <?php foreach($brands as $b): ?>
                    <option value="<?=$b['id']?>" <?= in_array($b['id'], $selectedCarBrands) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Danh mục <span class="req">*</span></label>
              <select name="category_id" id="categorySelect" required placeholder="— Chọn danh mục —" style="width:100%">
                <option value="">— Chọn danh mục —</option>
                <option value="HIDDEN" <?= ($product['category_id']??'') === 'HIDDEN' ? 'selected' : '' ?>> Không hiển thị mục danh mục</option>
                <?php foreach($categories as $c):?>
                  <option value="<?=$c['id']?>" <?= isset($product)&&$product['category_id']==$c['id']?'selected':'' ?>>
                    <?= $c['parent_id']?'&nbsp;&nbsp;— ':'' ?><?= e($c['name']) ?>
                  </option>
                <?php endforeach;?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Khối lượng (g)</label>
              <input type="number" name="weight_g" value="<?= $product['weight_g']??'' ?>">
            </div>
            <div class="form-group">
              <label>Kích thước D x R x C (cm)</label>
              <div style="display:flex;gap:8px">
                <input type="number" name="width_cm" placeholder="Dài" value="<?= $product['width_cm']??'' ?>" style="width:33%">
                <input type="number" name="depth_cm" placeholder="Rộng" value="<?= $product['depth_cm']??'' ?>" style="width:33%">
                <input type="number" name="height_cm" placeholder="Cao" value="<?= $product['height_cm']??'' ?>" style="width:33%">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ĐẶC ĐIỂM, THÔNG SỐ & MÔ TẢ -->
      <div class="panel">
        <div class="panel-head" style="display:flex;padding:0;border-bottom:1px solid var(--line)">
          <button type="button" id="tabDesc" onclick="swTab('desc')" style="flex:1;text-align:center" class="_tab _tab-active">Mô tả sản phẩm</button>
          <button type="button" id="tabFeat" onclick="swTab('feat')" style="flex:1;text-align:center" class="_tab">Đặc điểm sản phẩm</button>
          <button type="button" id="tabSpec" onclick="swTab('spec')" style="flex:1;text-align:center" class="_tab">Thông số kỹ thuật</button>
        </div>
        
        <div id="panelDesc" class="panel-body" style="padding-bottom:0">
          <textarea id="tinymceDesc" name="description"><?= htmlspecialchars(isset($product) ? $product['description'] : '') ?></textarea>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Hỗ trợ H1-H6, định dạng phong phú như Word. Dùng H2 cho tiêu đề mục chính, H3 cho tiêu đề con.
          </div>
        </div>
        
        <div id="panelFeat" class="panel-body" style="display:none;padding-bottom:0">
          <input type="hidden" name="features" id="featHidden">
          <textarea id="tinymceFeat" name="features"><?= htmlspecialchars(isset($product) ? $product['features'] : '') ?></textarea>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Trình soạn thảo đặc điểm sản phẩm.
          </div>
        </div>
        
        <div id="panelSpec" class="panel-body" style="display:none;padding-bottom:0">
          <input type="hidden" name="specifications" id="specHidden">
          <textarea id="tinymceSpec" name="specifications"><?= htmlspecialchars(isset($product) ? $product['specifications'] : '') ?></textarea>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Trình soạn thảo thông số kỹ thuật.
          </div>
        </div>
      </div>
<style>
._tab{padding:14px 20px;border:none;background:transparent;color:#666;cursor:pointer;font-size:14px;font-weight:600;border-bottom:2px solid transparent;transition:all .2s;border-right:1px solid var(--line)}
._tab:hover{background:#f8f9fa}
._tab-active{color:var(--navy);border-bottom-color:var(--gold)!important;background:#fff;border-right:1px solid var(--line)}
</style>
<script>
function swTab(t){
  ['desc','feat','spec'].forEach(function(x){
    var panel=document.getElementById('panel'+x.charAt(0).toUpperCase()+x.slice(1));
    var tab=document.getElementById('tab'+x.charAt(0).toUpperCase()+x.slice(1));
    if(panel)panel.style.display=x===t?'block':'none';
    if(tab){
      if(x===t) tab.classList.add('_tab-active');
      else tab.classList.remove('_tab-active');
    }
  });
  if(t==='feat' && typeof initFeatEditor==='function') initFeatEditor();
  if(t==='spec' && typeof initSpecEditor==='function') initSpecEditor();
}
</script>

      <!-- HÌNH ẢNH -->
      <div class="panel">
        <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <h3 style="margin:0"> Hình ảnh sản phẩm</h3>
          <div id="imageActionBar" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <button type="button" id="btnSelectAllImgs" onclick="handleSelectAllToggle()" class="btn btn-sm"
                    style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-weight:700;font-size:12.5px;padding:6px 14px;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
              <input type="checkbox" id="selectAllImagesCb" style="width:16px;height:16px;accent-color:#ef4444;pointer-events:none">
              <span>CHỌN TẤT CẢ</span>
            </button>
            <button type="button" id="btnDeleteSelectedImgs" onclick="deleteSelectedProductImages()" class="btn btn-sm"
                    style="background:#ef4444;color:#fff;border:none;font-weight:700;font-size:12.5px;padding:6px 14px;border-radius:6px;box-shadow:0 2px 6px rgba(239,68,68,0.35);transition:all 0.2s;display:inline-flex;align-items:center;gap:6px;cursor:pointer">
               Xóa <span id="selectedImgCount" style="background:rgba(255,255,255,0.3);padding:1px 7px;border-radius:10px;font-size:11px;font-weight:800">0</span> ảnh đã chọn
            </button>
          </div>
        </div>
        <div class="panel-body">
          <?php if(!empty($images)):?>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px" id="existingImagesRow">
              <?php foreach($images as $img):?>
                <div class="existing-img-wrap" data-img-id="<?= $img['id'] ?>" style="position:relative;width:120px;aspect-ratio:4/3;border-radius:6px;overflow:hidden;border:2px solid #e2e8f0;flex-shrink:0;background:transparent;transition:all 0.2s">
                  <input type="checkbox" class="img-select-checkbox" data-img-id="<?= $img['id'] ?>" onchange="updateImageSelectionState()" onclick="event.stopPropagation();"
                         style="position:absolute;top:4px;left:4px;z-index:20;width:20px;height:20px;accent-color:#ef4444;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.3)">
                  <img src="/uploads/products/<?= e(implode('/', array_map('rawurlencode', explode('/', $img['file_path'])))) ?>" style="width:100%;height:100%;object-fit:contain" onerror="this.onerror=null;this.src='/img/placeholder.png'">
                  <button type="button" onclick="deleteProductImage(<?= $img['id'] ?>, this)" title="Xóa ảnh này"
                    style="position:absolute;top:4px;right:4px;width:22px;height:22px;border-radius:50%;background:rgba(231,76,60,0.9);color:#fff;border:none;cursor:pointer;font-size:14px;font-weight:700;line-height:1;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,0.3);transition:all 0.15s;z-index:20"
                    onmouseover="this.style.background='#c0392b';this.style.transform='scale(1.15)'"
                    onmouseout="this.style.background='rgba(231,76,60,0.9)';this.style.transform='scale(1)'"
                  >&times;</button>
                  <button type="button" class="img-zoom-btn" onclick="var img=this.closest('.existing-img-wrap').querySelector('img'); if(img) window.openImgLightbox(img.currentSrc||img.src); event.stopPropagation();" title="Xem ảnh lớn"
                    style="position:absolute;bottom:4px;right:4px;width:24px;height:24px;border-radius:4px;background:rgba(15,23,42,0.85);color:#fff;border:1px solid rgba(255,255,255,0.4);cursor:pointer;font-size:12px;line-height:1;display:flex;align-items:center;justify-content:center;z-index:30;box-shadow:0 2px 5px rgba(0,0,0,0.4)">🔍</button>
                </div>
              <?php endforeach;?>
            </div>
          <?php endif;?>
          <div class="form-group">
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">
              <label style="cursor:pointer;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;width:140px;height:140px;border:1px dashed #ccc;border-radius:8px;background:#f8f9fa;text-align:center;transition:all 0.2s" onmouseover="this.style.background='#f0f4f8';this.style.borderColor='var(--navy)'" onmouseout="this.style.background='#f8f9fa';this.style.borderColor='#ccc'">
                <div style="padding:6px 14px;background:#fff;border:1px solid #ddd;border-radius:6px;color:#333;font-weight:600;font-size:13px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:8px">Thêm ảnh</div>
                <span style="font-size:12px;color:#888;padding:0 8px;line-height:1.4">Mỗi ảnh không quá 20 MB<br>(Tối đa 8 ảnh)</span>
                <input type="file" id="img-picker" multiple accept="image/jpeg,image/png,image/webp" style="display:none">
              </label>
              <div id="img-preview-area" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
            </div>
            <input type="file" name="images[]" id="img-hidden-input" multiple accept="image/*" style="display:none">
            <?php if (isset($product) && !empty($images)): ?>
              <label style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:13px;cursor:pointer;width:max-content;max-width:100%">
                <input type="checkbox" name="replace_images" id="replaceImages" value="1" checked
                       style="width:15px;height:15px;accent-color:var(--navy);flex:0 0 auto">
                <span>Khi tải ảnh mới, thay toàn bộ bộ ảnh hiện tại</span>
              </label>
            <?php endif; ?>
          </div>
          <div id="imgPreviewRow" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>

<!-- IMG-LIGHTBOX: click a thumbnail to view the full image -->
<style>
#existingImagesRow .existing-img-wrap img, #img-preview-area img, #imgPreviewRow img { cursor: zoom-in; }
#imgLightbox { position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:100000; display:none; align-items:center; justify-content:center; padding:24px; }
#imgLightbox img { max-width:92vw; max-height:90vh; object-fit:contain; border-radius:6px; box-shadow:0 8px 40px rgba(0,0,0,0.5); }
#imgLightbox .ilb-close { position:absolute; top:18px; right:24px; width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,0.16); color:#fff; border:none; font-size:26px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; }
#imgLightbox .ilb-close:hover { background:rgba(255,255,255,0.32); }
</style>
<div id="imgLightbox" onclick="closeImgLightbox(event)">
  <button type="button" class="ilb-close" onclick="closeImgLightbox(event)" title="Đóng (Esc)">&times;</button>
  <img id="imgLightboxImg" src="" alt="Xem ảnh">
</div>
<script>
(function(){
  var downX=0, downY=0;
  window.openImgLightbox = function(src){
    if(!src) return;
    document.getElementById('imgLightboxImg').src=src;
    document.getElementById('imgLightbox').style.display='flex';
  };
  window.closeImgLightbox=function(e){
    if(e && e.target && e.target.id==='imgLightboxImg') return; // clicking the image itself keeps it open
    var lb=document.getElementById('imgLightbox');
    if(lb){ lb.style.display='none'; document.getElementById('imgLightboxImg').src=''; }
  };
  document.addEventListener('mousedown', function(e){ downX=e.clientX; downY=e.clientY; }, true);
  document.addEventListener('click', function(ev){
    if(ev.target.closest('.img-zoom-btn')) {
      ev.preventDefault(); ev.stopPropagation();
      var wrap = ev.target.closest('.existing-img-wrap');
      var im = wrap ? wrap.querySelector('img') : null;
      if (im && im.src) window.openImgLightbox(im.currentSrc || im.src);
      return;
    }
    if(ev.target.closest('button') || ev.target.closest('input') || ev.target.closest('label')) return; // Bỏ qua button, checkbox, label
    var wrap=ev.target.closest('.existing-img-wrap');       // wrapper of BOTH saved & new thumbs
    if(!wrap || !wrap.closest('#existingImagesRow, #img-preview-area, #imgPreviewRow')) return;
    if(Math.abs(ev.clientX-downX)+Math.abs(ev.clientY-downY) > 6) return; // was a drag (reorder)

    // Clicking image tile toggles its selection checkbox!
    var cb = wrap.querySelector('input[type="checkbox"]');
    if(cb) {
      cb.checked = !cb.checked;
      if (typeof updateImageSelectionState === 'function') updateImageSelectionState();
      return;
    }

    var im=wrap.querySelector('img');
    if(!im || !im.src) return;
    ev.preventDefault(); ev.stopPropagation();
    window.openImgLightbox(im.currentSrc || im.src);
  }, true);
  document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ var lb=document.getElementById('imgLightbox'); if(lb && lb.style.display==='flex') window.closeImgLightbox(); }
  });
})();
</script>
        </div>
      </div>

      <!-- SEO CONTENT ANALYZER (Kiểu Rank Math / Yoast SEO) -->
      <div class="panel" id="seoPanel">
        <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center">
          <h3> Phân tích SEO nội dung</h3>
          <span id="seoScore" style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:12px;background:#fef3c7;color:#d97706">— Đang phân tích</span>
        </div>
        <div class="panel-body">
          <div style="font-size:12px;color:#888;margin-bottom:12px;line-height:1.6;background:#f8f9fa;padding:10px 12px;border-radius:6px">
             Panel này phân tích nội dung bạn đã nhập ở <b>Thông tin cơ bản</b>, <b>Mô tả</b>, <b>Đặc điểm</b>, <b>Thông số kỹ thuật</b> xem đã chuẩn SEO chưa — tương tự Rank Math / Yoast SEO trong WordPress.
          </div>
          <div class="form-group">
            <label style="display:flex;justify-content:space-between;align-items:center">
              <span>Tiêu đề Google <span style="font-weight:400;color:#888;font-size:11px">(Tự động bắt theo Tên sản phẩm)</span></span>
              <button type="button" onclick="forceSyncSeoTitleLive()" class="btn btn-link btn-sm" style="padding:0;font-size:11px;color:var(--navy);text-decoration:none">🔄 Đồng bộ Tên sản phẩm</button>
            </label>
            <input type="text" name="seo_title" id="seoTitle" maxlength="120"
                   value="<?= e(!empty($product['seo_title']) ? $product['seo_title'] : ($product['name']??'')) ?>"
                   placeholder="Tự động bắt theo tên sản phẩm..."
                   oninput="this.dataset.userEdited='1';runSeoAnalysis()">
          </div>
          <div class="form-group">
            <label>Mô tả Google</label>
            <textarea name="seo_description" id="seoDescription" rows="3" maxlength="170"
                      placeholder="Để trống để hệ thống tự tạo"><?= e(!empty($product['seo_description']) ? $product['seo_description'] : ($product['meta_description']??'')) ?></textarea>
          </div>
          <div class="form-group">
            <label>Từ khóa mục tiêu <span style="font-weight:400;color:#888;font-size:11px">(Focus Keyword — để kiểm tra)</span></label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" name="seo_keyword" id="seoKeyword" style="flex:1"
                     value="<?= e($product['seo_keyword']??'') ?>" placeholder="VD: két nước hyundai i10"
                     oninput="runSeoAnalysis()">
              <button type="button" id="btnSuggestKw" onclick="suggestKeywords()" class="btn btn-outline-navy btn-sm" style="white-space:nowrap;height:38px;display:inline-flex;align-items:center;gap:4px;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div id="kwSuggestions" style="display:none;margin-top:8px;padding:10px 12px;background:#f0f4ff;border-radius:8px;border:1px solid #c7d2fe"></div>
          </div>

          <div class="form-group">
            <label>Từ khóa phụ <span style="font-weight:400;color:#888;font-size:11px">(Secondary Keywords — tên xe, đời xe, dấu hiệu hỏng hóc, cách kiểm tra...)</span></label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" name="seo_secondary_keywords" id="seoSecondaryKeywords" style="flex:1"
                     value="<?= e($product['seo_secondary_keywords']??'') ?>" placeholder="VD: lốc lạnh vios 2015, lốc lạnh denso, lốc lạnh ô tô kêu to, kiểm tra lốc lạnh"
                     oninput="runSeoAnalysis()">
              <button type="button" id="btnSuggestSecKw" onclick="suggestSecondaryKeywords()" class="btn btn-outline-navy btn-sm" style="white-space:nowrap;height:38px;display:inline-flex;align-items:center;gap:4px;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                Gợi ý từ khóa phụ
              </button>
            </div>
            <div id="secKwSuggestions" style="display:none;margin-top:8px;padding:10px 12px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:12px"></div>
          </div>

          <!-- Google SERP Preview -->
          <div style="background:#fff;border:1px solid #dfe1e5;border-radius:8px;padding:14px;margin-bottom:14px">
            <div style="font-size:10px;color:#888;margin-bottom:4px;display:flex;align-items:center;gap:4px">
              <svg width="14" height="14" viewBox="0 0 24 24"><path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Xem trước trên Google
            </div>
            <div id="seoPreviewTitle" style="font-size:18px;color:#1a0dab;line-height:1.3;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Tiêu đề sản phẩm — Cooling</div>
            <div style="font-size:12px;color:#006621;margin-bottom:2px">https://coolingsystems.vn/products/<span id="seoPreviewSlug">...</span></div>
            <div id="seoPreviewDesc" style="font-size:13px;color:#545454;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">Mô tả sản phẩm...</div>
          </div>

          <!-- SEO Checklist -->
          <div id="seoChecklist" style="font-size:12px;line-height:2.2"></div>

          <button type="button" onclick="runSeoAnalysis()" class="btn btn-outline-navy btn-sm" style="margin-top:10px;width:100%"> Phân tích lại</button>
        </div>
      </div>

    </div><!-- /left col -->

    <!-- RIGHT SIDEBAR -->
    <div class="sidebar-panel">
      <!-- ĐĂNG BÀI -->
      <div class="panel">
        <div class="panel-head"><h3> Đăng bài</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Trạng thái</label>
            <?php if(isset($product) && $product['status'] === 'published'): ?>
              <input type="hidden" name="status" value="published">
              <select disabled style="background:#eee;color:#888;cursor:not-allowed">
                <option selected>Xuất bản</option>
              </select>
            <?php else: ?>
              <select name="status">
                <option value="draft" <?= ($product['status']??'')==='draft'?'selected':'' ?>>Bản nháp</option>
                <option value="published" <?= ($product['status']??'')==='published'?'selected':'' ?>>Xuất bản</option>
              </select>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px">
              <input type="checkbox" name="is_featured" value="1" <?= ($product['is_featured']??0)?'checked':'' ?>>
              Sản phẩm nổi bật
            </label>
          </div>
          <div class="form-group" style="margin-top:10px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;color:#1e293b">
              <input type="checkbox" name="is_call_price" value="1" <?= (!empty($product['is_call_price']) || !empty($product['is_contact_price']) || !empty($product['is_call']))?'checked':'' ?>>
              Liên hệ báo giá (Call)
            </label>
          </div>
          <button type="submit" class="btn btn-gold btn-block btn-lg" style="margin-top:8px">
            <?= isset($product) ? ' Cập nhật SP' : ' Đăng sản phẩm' ?>
          </button>
          <?php if(isset($product)):?>
            <a href="<?= e(productPath($product)) ?>" target="_blank" class="btn btn-outline-navy btn-block btn-sm" style="margin-top:8px;text-align:center"> Xem trang SP</a>
            <a href="/admin/products/<?= e($product['id']) ?>/history" class="btn btn-block btn-sm" style="margin-top:6px;text-align:center;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:6px;padding:6px 12px;display:block;font-size:.8rem;">
              Lịch sử thay đổi<?= isset($historyCount) && $historyCount > 0 ? ' ('.$historyCount.' bản)' : '' ?>
            </a>
          <?php endif;?>
        </div>
      </div>

      <!-- Giá và tồn kho được quản lý tập trung tại Quản lý kho. -->
      <input type="hidden" name="_inventory_in_product_form" value="0">
      <input type="hidden" name="price" value="<?= (int)($product['price'] ?? 0) ?>">
      <input type="hidden" name="cost_price" value="<?= (int)($product['cost_price'] ?? 0) ?>">
      <input type="hidden" name="original_price" value="<?= (int)($product['original_price'] ?? 0) ?>">
      <input type="hidden" name="stock" value="<?= (int)($product['stock'] ?? 0) ?>">
      <input type="hidden" name="min_stock" value="<?= (int)($product['min_stock'] ?? 5) ?>">
      <input type="hidden" name="max_stock" value="<?= (int)($product['max_stock'] ?? 1000) ?>">
      <input type="hidden" name="warranty_months" value="<?= (int)($product['warranty_months'] ?? 12) ?>">
      <div class="panel" style="background:#f8fafc;border-style:dashed">
        <div class="panel-body" style="font-size:12px;color:#64748b">Giá bán, giá nhập và tồn kho được cập nhật tại <a href="/admin/inventory" style="font-weight:700;color:#1a3258">Quản lý kho</a>.</div>
      </div>

    </div><!-- /sidebar -->
  </div><!-- /form-layout -->

<!-- Stock limits are validated in the browser and on the server. -->

<!-- VIDEO SAN PHAM -->
<div class="panel" style="margin-top:20px">
  <div class="panel-head"><h3 style="text-transform:uppercase;letter-spacing:.05em">Video san pham</h3></div>
  <div class="panel-body">
    <div class="form-group">
      <label style="font-weight:700">Link Video (YouTube / URL .mp4)</label>
      <input type="text" name="video_url" class="form-control"
             value="<?= e($product['video_url'] ?? '') ?>"
             placeholder="https://www.youtube.com/watch?v=... hoac link MP4"
             id="videoUrlInput"
             oninput="previewVideo(this.value)">
      <div class="form-help" style="margin-top:6px;color:#888">
        Ho tro: YouTube, Vimeo, hoac link .mp4. Khach hang se xem video tren trang san pham.
      </div>
    </div>
    <div id="videoPreviewBox" style="margin-top:12px;display:<?= !empty($product['video_url']) ? 'block' : 'none' ?>">
      <label style="font-size:12px;font-weight:700;display:block;margin-bottom:8px;color:#555">Xem truoc:</label>
      <div id="videoPreviewWrap"></div>
    </div>
    <?php if(!empty($product['video_url'])): ?>
    <script>
    (function(){
      var url = <?= json_encode($product['video_url']) ?>;
      var box = document.getElementById('videoPreviewBox');
      var wrap = document.getElementById('videoPreviewWrap');
      if (url) {
        box.style.display = 'block';
        wrap.innerHTML = buildVideoEmbed(url);
      }
    })();
    </script>
    <?php endif; ?>
  </div>
</div>
<script>
function buildVideoEmbed(url) {
  if (!url) return '';
  var ytMatch = url.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/);
  var ytShort = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
  var ytId = (ytMatch && ytMatch[1]) || (ytShort && ytShort[1]);
  if (ytId) {
    return '<iframe width="480" height="270" src="https://www.youtube.com/embed/' + ytId + '" frameborder="0" allowfullscreen style="border-radius:8px;border:1px solid #eee;display:block"></iframe>';
  }
  if (url.match(/vimeo\.com\/(\d+)/)) {
    var vmId = url.match(/vimeo\.com\/(\d+)/)[1];
    return '<iframe width="480" height="270" src="https://player.vimeo.com/video/' + vmId + '" frameborder="0" allowfullscreen style="border-radius:8px;border:1px solid #eee;display:block"></iframe>';
  }
  if (url.match(/\.(mp4|webm|ogg)$/i)) {
    return '<video src="' + url + '" controls style="max-width:480px;border-radius:8px;border:1px solid #eee;display:block"></video>';
  }
  return '<a href="' + url + '" target="_blank" style="color:#1a3258;font-weight:600">Xem video</a>';
}
function previewVideo(url) {
  var box = document.getElementById('videoPreviewBox');
  var wrap = document.getElementById('videoPreviewWrap');
  if (url && url.trim()) {
    box.style.display = 'block';
    wrap.innerHTML = buildVideoEmbed(url.trim());
  } else {
    box.style.display = 'none';
    wrap.innerHTML = '';
  }
}
</script>
</form>

<script>
window.handleSelectAllToggle = function(e) {
  if (e) {
    if (typeof e.preventDefault === 'function') e.preventDefault();
    if (typeof e.stopPropagation === 'function') e.stopPropagation();
  }
  var masterCb = document.getElementById('selectAllImagesCb');
  var allCbs = Array.from(document.querySelectorAll('#existingImagesRow input[type="checkbox"], #img-preview-area input[type="checkbox"], #imgPreviewRow input[type="checkbox"], .img-select-checkbox, .img-select-checkbox-new')).filter(cb => cb !== masterCb);
  
  if (allCbs.length === 0) return;

  var selectedCount = allCbs.filter(cb => cb.checked).length;
  var shouldSelect = selectedCount < allCbs.length;

  if (masterCb) masterCb.checked = shouldSelect;

  allCbs.forEach(function(cb) {
    cb.checked = shouldSelect;
  });

  updateImageSelectionState();
};

window.toggleSelectAllImages = function(e) {
  window.handleSelectAllToggle(e);
};

window.updateImageSelectionState = function() {
  var masterCb = document.getElementById('selectAllImagesCb');
  var allCbs = Array.from(document.querySelectorAll('#existingImagesRow input[type="checkbox"], #img-preview-area input[type="checkbox"], #imgPreviewRow input[type="checkbox"], .img-select-checkbox, .img-select-checkbox-new')).filter(cb => cb !== masterCb);

  var selectedCount = allCbs.filter(cb => cb.checked).length;

  var actionBar = document.getElementById('imageActionBar');
  var btn = document.getElementById('btnDeleteSelectedImgs');
  var countSpan = document.getElementById('selectedImgCount');

  if (actionBar) actionBar.style.display = allCbs.length > 0 ? 'flex' : 'none';
  if (countSpan) countSpan.textContent = selectedCount;
  if (btn) btn.style.display = 'inline-flex';

  if (masterCb) {
    masterCb.checked = allCbs.length > 0 && selectedCount === allCbs.length;
    masterCb.indeterminate = selectedCount > 0 && selectedCount < allCbs.length;
  }

  allCbs.forEach(function(cb) {
    var wrap = cb.closest('.existing-img-wrap, .new-img-wrap') || cb.parentElement;
    if (wrap) {
      if (cb.checked) {
        wrap.style.borderColor = '#ef4444';
        wrap.style.outline = '3px solid #ef4444';
        wrap.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.4)';
      } else {
        wrap.style.borderColor = '#e2e8f0';
        wrap.style.outline = 'none';
        wrap.style.boxShadow = 'none';
      }
    }
  });
};

document.addEventListener('DOMContentLoaded', function() {
  updateImageSelectionState();
});

async function deleteSelectedProductImages() {
  var masterCb = document.getElementById('selectAllImagesCb');
  var allCheckedCbs = Array.from(document.querySelectorAll('#existingImagesRow input[type="checkbox"]:checked, #img-preview-area input[type="checkbox"]:checked, #imgPreviewRow input[type="checkbox"]:checked, .img-select-checkbox:checked, .img-select-checkbox-new:checked')).filter(cb => cb !== masterCb);
  
  var selectedSaved = allCheckedCbs.filter(cb => cb.dataset && cb.dataset.imgId);
  var selectedNew = allCheckedCbs.filter(cb => cb.dataset && cb.dataset.fileIdx !== undefined);
  var totalSelected = allCheckedCbs.length;

  if (totalSelected === 0) {
    alert('Vui lòng chọn ít nhất 1 ảnh để bỏ/xóa.');
    return;
  }

  var msg = totalSelected === 1 ? 'Bạn có chắc muốn xóa ảnh này?' : ('Bạn có chắc muốn xóa ' + totalSelected + ' ảnh đã chọn?');
  var confirmed = false;
  if (typeof window.csConfirmAsync === 'function') {
    confirmed = await window.csConfirmAsync(msg);
  } else {
    confirmed = confirm(msg);
  }
  if (!confirmed) return;

  // 1. Remove selected new preview files from memory
  if (selectedNew.length > 0 && typeof window.__removeSelectedNewFiles === 'function') {
    var newIndexes = selectedNew.map(cb => parseInt(cb.dataset.fileIdx)).filter(n => !isNaN(n));
    window.__removeSelectedNewFiles(newIndexes);
  }

  // 2. Delete selected saved DB images via AJAX
  if (selectedSaved.length > 0) {
    var imageIds = selectedSaved.map(cb => cb.dataset.imgId);
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_csrf"]')?.value || '';
    var wraps = selectedSaved.map(cb => cb.closest('.existing-img-wrap')).filter(Boolean);
    
    wraps.forEach(wrap => {
      wrap.style.opacity = '0.4';
      wrap.style.pointerEvents = 'none';
    });

    try {
      var r = await fetch('/admin/products/delete-images-bulk', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf=' + encodeURIComponent(csrf) + '&image_ids=' + encodeURIComponent(JSON.stringify(imageIds))
      });
      var data = await r.json();
      if (data.ok) {
        wraps.forEach(wrap => {
          wrap.style.transition = 'all 0.3s ease';
          wrap.style.transform = 'scale(0)';
          wrap.style.opacity = '0';
          setTimeout(() => wrap.remove(), 300);
        });
        if (typeof window.coolToastShow === 'function') window.coolToastShow('Đã xóa ' + imageIds.length + ' ảnh thành công!', '✅');
      } else {
        alert('Lỗi: ' + (data.msg || 'Không thể xóa các ảnh đã chọn'));
        wraps.forEach(wrap => {
          wrap.style.opacity = '1';
          wrap.style.pointerEvents = 'auto';
        });
      }
    } catch(err) {
      alert('Lỗi kết nối: ' + err.message);
      wraps.forEach(wrap => {
        wrap.style.opacity = '1';
        wrap.style.pointerEvents = 'auto';
      });
    }
  }

  setTimeout(function() { updateImageSelectionState(); }, 350);
}

async function deleteProductImage(imageId, btn) {
  var confirmed = false;
  if (typeof window.csConfirmAsync === 'function') {
    confirmed = await window.csConfirmAsync('Bạn có chắc muốn xóa ảnh này?');
  } else {
    confirmed = confirm('Bạn có chắc muốn xóa ảnh này?');
  }
  if (!confirmed) return;

  var wrap = btn ? btn.closest('.existing-img-wrap') : null;

  if (!imageId || isNaN(parseInt(imageId, 10))) {
    if (wrap) {
      wrap.style.transition = 'all 0.3s ease';
      wrap.style.transform = 'scale(0)';
      wrap.style.opacity = '0';
      setTimeout(() => { wrap.remove(); updateImageSelectionState(); }, 300);
    }
    if (typeof window.coolToastShow === 'function') window.coolToastShow('Đã bỏ ảnh!', '🗑️');
    return;
  }

  var csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_csrf"]')?.value || '';
  
  if (wrap) {
    wrap.style.opacity = '0.4';
    wrap.style.pointerEvents = 'none';
  }
  
  fetch('/admin/products/delete-image', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&image_id=' + encodeURIComponent(imageId)
  })
  .then(r => r.json())
  .then(data => {
    if (wrap) {
      wrap.style.transition = 'all 0.3s ease';
      wrap.style.transform = 'scale(0)';
      wrap.style.opacity = '0';
      setTimeout(() => {
        wrap.remove();
        updateImageSelectionState();
      }, 300);
    }
    if (typeof window.coolToastShow === 'function') window.coolToastShow('Đã xóa ảnh thành công!', '✅');
  })
  .catch(err => {
    if (wrap) {
      wrap.style.transition = 'all 0.3s ease';
      wrap.style.transform = 'scale(0)';
      wrap.style.opacity = '0';
      setTimeout(() => { wrap.remove(); updateImageSelectionState(); }, 300);
    }
  });
}
</script>


<script>



// Helper upload handler dùng chung cho cả 3 editor
function tinymceImageUploadHandler(blobInfo, progress) {
  return new Promise(function(resolve, reject) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/admin/upload-tinymce-image');
    xhr.upload.onprogress = function(e) { if (e.lengthComputable) progress(e.loaded / e.total * 100); };
    xhr.onload = function() {
      if (xhr.status !== 200) { reject('Upload thất bại: HTTP ' + xhr.status); return; }
      try {
        var json = JSON.parse(xhr.responseText);
        if (json.location) { resolve(json.location); } else { reject(json.msg || 'Upload thất bại'); }
      } catch(e) { reject('Phản hồi không hợp lệ'); }
    };
    xhr.onerror = function() { reject('Lỗi kết nối máy chủ'); };
    var fd = new FormData();
    var blob = blobInfo.blob();
    var filename = (typeof blobInfo.filename === 'function' && blobInfo.filename()) ? blobInfo.filename() : ('image_' + Date.now() + '.png');
    fd.append('file', blob, filename);
    var csrf = document.querySelector('input[name="_csrf"]');
    if (csrf) fd.append('_csrf', csrf.value);
    xhr.send(fd);
  });
}

function setupTinyMCECallout(editor) {
  editor.ui.registry.addMenuButton('calloutbox', {
    text: '🎨 Khung Nền',
    tooltip: 'Tạo / Đổi màu khung background & màu viền',
    fetch: function(callback) {
      var applyBox = function(bgColor, borderColor) {
        var sel = editor.selection;
        var node = sel.getNode();
        var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol');
        
        if (!box || box.nodeName.toLowerCase() === 'body') {
          var content = sel.getContent() || '<p>Nhập nội dung khung thông tin...</p>';
          editor.insertContent('<div style="background-color: ' + bgColor + '; border: 1px solid ' + borderColor + '; border-radius: 12px; padding: 16px; margin: 12px 0;">' + content + '</div>');
        } else {
          editor.dom.setStyle(box, 'background-color', bgColor);
          editor.dom.setStyle(box, 'border', '1px solid ' + borderColor);
          editor.dom.setStyle(box, 'border-radius', '12px');
          editor.dom.setStyle(box, 'padding', '16px');
          editor.dom.setStyle(box, 'margin', '12px 0');
        }
      };

      var items = [
        {
          type: 'menuitem',
          text: '🟢 Khung Xanh lá (Giống mẫu ảnh)',
          onAction: function() { applyBox('#f0fdf4', '#4ade80'); }
        },
        {
          type: 'menuitem',
          text: '🔵 Khung Xanh dương (Blue Callout)',
          onAction: function() { applyBox('#eff6ff', '#60a5fa'); }
        },
        {
          type: 'menuitem',
          text: '🟡 Khung Vàng Gold (Yellow Callout)',
          onAction: function() { applyBox('#fefce8', '#facc15'); }
        },
        {
          type: 'menuitem',
          text: '🔴 Khung Đỏ (Red Alert)',
          onAction: function() { applyBox('#fef2f2', '#f87171'); }
        },
        {
          type: 'menuitem',
          text: '⚪ Khung Xám (Modern Gray)',
          onAction: function() { applyBox('#f8fafc', '#cbd5e1'); }
        },
        {
          type: 'menuitem',
          text: '🟣 Khung Tím (Purple)',
          onAction: function() { applyBox('#faf5ff', '#c084fc'); }
        },
        {
          type: 'menuitem',
          text: '🎨 Tự chọn Màu Nền & Màu Viền...',
          onAction: function() {
            var node = editor.selection.getNode();
            var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol') || node;
            var curBg = editor.dom.getStyle(box, 'background-color') || '#f0fdf4';
            var curBorder = editor.dom.getStyle(box, 'border-color') || '#4ade80';
            
            var bg = prompt('Nhập mã màu nền (ví dụ: #f0fdf4, #fff7ed, #e0f2fe...):', curBg);
            if (bg !== null && bg.trim() !== '') {
              var border = prompt('Nhập mã màu viền (ví dụ: #4ade80, #f97316, #3b82f6...):', curBorder);
              if (!border) border = bg;
              applyBox(bg.trim(), border.trim());
            }
          }
        },
        {
          type: 'menuitem',
          text: '❌ Xóa màu khung nền',
          onAction: function() {
            var node = editor.selection.getNode();
            var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol');
            if (box) {
              editor.dom.setStyle(box, 'background-color', '');
              editor.dom.setStyle(box, 'border', '');
              editor.dom.setStyle(box, 'border-radius', '');
              editor.dom.setStyle(box, 'padding', '');
              editor.dom.setStyle(box, 'margin', '');
            }
          }
        }
      ];
      callback(items);
    }
  });
}

function initDescEditor() {
  var target = document.getElementById('tinymceDesc');
  if (!target || target.dataset.editorLoading || tinymce.get('tinymceDesc')) return;
  target.dataset.editorLoading = '1';
  tinymce.init({
  selector: '#tinymceDesc',
  height: 300,
  language: 'vi',
  forced_root_block: 'p',
  force_p_newlines: true,
  force_br_newlines: false,
  convert_newlines_to_brs: false,
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | calloutbox removeformat',
    'alignleft aligncenter alignright alignjustify | bullist numlist checklist | outdent indent | table | link image | code fullscreen'
  ],
  font_family_formats: 'Times New Roman=times new roman,times,serif; Arial=arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,sans-serif; Georgia=georgia,serif; Courier New=courier new,monospace',
  font_size_formats: '8px 9px 10px 11px 12px 13px 14px 16px 18px 20px 22px 24px 28px 32px 36px 48px 60px 72px',
  table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
  table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
  table_default_attributes: { 'border': '1' },
  table_class_list: [
    { title: 'Mặc định', value: '' },
    { title: 'Bảng sọc', value: 'table-striped' }
  ],
  content_style: `
    body { font-family: 'Times New Roman', Times, serif; font-size: 15px; line-height: 1.9; color: #333; padding: 16px; }
    table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    th { background: #0b1d3a; color: #fff; font-weight: 700; padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    tr:nth-child(even) td { background: #f8f9fc; }
    h1 { font-size: 28px; color: #0b1d3a; font-weight: 900; }
    h2 { font-size: 22px; color: #0b1d3a; font-weight: 800; }
    h3 { font-size: 18px; color: #0b1d3a; font-weight: 700; }
    img { max-width: 100%; height: auto; border-radius: 8px; }
    blockquote { border-left: 3px solid #c9a14a; padding: 12px 16px; background: #faf8f3; border-radius: 0 8px 8px 0; }
  `,
  menubar: 'file edit view insert format table',
  promotion: false,
  branding: false,
  license_key: 'gpl',
  automatic_uploads: true,
  paste_data_images: true,
  images_upload_url: '/admin/upload-tinymce-image',
  images_upload_handler: tinymceImageUploadHandler,
  file_picker_types: 'image',
  file_picker_callback: function(cb, value, meta) {
    if (meta.filetype === 'image') {
      var input = document.createElement('input');
      input.type = 'file'; input.accept = 'image/*';
      input.onchange = function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        var csrf = document.querySelector('input[name="_csrf"]');
        if (csrf) fd.append('_csrf', csrf.value);
        fetch('/admin/upload-tinymce-image', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.location) {
            cb(data.location, { title: file.name });
          } else {
            alert('Lỗi upload: ' + (data.msg || 'Không thể tải ảnh'));
          }
        })
        .catch(err => alert('Lỗi kết nối: ' + err.message));
      };
      input.click();
    }
  },
  paste_preprocess: function(editor, args) {
    // Tự động xóa font-family inline khi paste từ Word / web / Google Docs
    args.content = args.content.replace(/font-family\s*:\s*[^;'"<>]+;?/gi, '');
    args.content = args.content.replace(/\s*face="[^"]*"/gi, '');
  },
  setup: function(editor) {
    setupTinyMCECallout(editor);
    editor.on('BeforeExecCommand', function(e) {
      if (['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull'].indexOf(e.command) !== -1) {
        var node = editor.selection.getNode();
        if (node && (node.nodeName === 'P' || node.nodeName === 'DIV')) {
          if (node.innerHTML.search(/<br\s*\/?>/i) !== -1) {
            var parts = node.innerHTML.split(/<br\s*\/?>/i).filter(function(l){ return l.trim() !== ''; });
            if (parts.length > 1) {
              var replacement = parts.map(function(l){ return '<p>' + l + '</p>'; }).join('');
              node.outerHTML = replacement;
            }
          }
        }
      }
    });

    editor.on('SetContent', function(e) {
      var body = editor.getBody();
      if (!body) return;
      var blocks = body.querySelectorAll('p, div');
      blocks.forEach(function(b) {
        if (b.querySelectorAll('table, ul, ol, img').length === 0 && b.innerHTML.search(/<br\s*\/?>/i) !== -1) {
          var parts = b.innerHTML.split(/<br\s*\/?>/i).filter(function(p){ return p.trim() !== ''; });
          if (parts.length > 1) {
            b.outerHTML = parts.map(function(p){ return '<p>' + p + '</p>'; }).join('');
          }
        }
      });
    });

    editor.on('change keyup setcontent paste execCommand nodechange input', function() {
      editor.save();
      if (typeof runSeoAnalysis === 'function') {
        clearTimeout(window._seoT);
        window._seoT = setTimeout(runSeoAnalysis, 300);
      }
    });
    // Xóa font-family còn sót sau khi paste (fallback về Times New Roman theo body)
    editor.on('PastePostProcess', function(e) {
      e.node.querySelectorAll('[style]').forEach(function(el) {
        el.style.fontFamily = '';
      });
      e.node.querySelectorAll('font[face]').forEach(function(el) {
        el.removeAttribute('face');
      });
    });
  }
  });
}
initDescEditor();

function initFeatEditor() {
  var target = document.getElementById('tinymceFeat');
  if (!target || target.dataset.editorLoading || tinymce.get('tinymceFeat')) return;
  target.dataset.editorLoading = '1';
  tinymce.init({
  selector: '#tinymceFeat',
  height: 250,
  language: 'vi',
  forced_root_block: 'p',
  force_p_newlines: true,
  force_br_newlines: false,
  convert_newlines_to_brs: false,
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | calloutbox removeformat',
    'alignleft aligncenter alignright alignjustify | bullist numlist checklist | outdent indent | table | link image | code fullscreen'
  ],
  font_family_formats: 'Times New Roman=times new roman,times,serif; Arial=arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,sans-serif; Georgia=georgia,serif; Courier New=courier new,monospace',
  font_size_formats: '8px 9px 10px 11px 12px 13px 14px 16px 18px 20px 22px 24px 28px 32px 36px 48px 60px 72px',
  table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
  table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
  table_default_attributes: { 'border': '1' },
  table_class_list: [
    { title: 'Mặc định', value: '' },
    { title: 'Bảng sọc', value: 'table-striped' }
  ],
  content_style: `
    body { font-family: 'Times New Roman', Times, serif; font-size: 15px; line-height: 1.9; color: #333; padding: 16px; }
    table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    th { background: #0b1d3a; color: #fff; font-weight: 700; padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    tr:nth-child(even) td { background: #f8f9fc; }
    h1 { font-size: 28px; color: #0b1d3a; font-weight: 900; }
    h2 { font-size: 22px; color: #0b1d3a; font-weight: 800; }
    h3 { font-size: 18px; color: #0b1d3a; font-weight: 700; }
    img { max-width: 100%; height: auto; border-radius: 8px; }
    blockquote { border-left: 3px solid #c9a14a; padding: 12px 16px; background: #faf8f3; border-radius: 0 8px 8px 0; }
  `,
  menubar: 'file edit view insert format table',
  promotion: false,
  branding: false,
  license_key: 'gpl',
  automatic_uploads: true,
  paste_data_images: true,
  images_upload_url: '/admin/upload-tinymce-image',
  images_upload_handler: tinymceImageUploadHandler,
  file_picker_types: 'image',
  file_picker_callback: function(cb, value, meta) {
    if (meta.filetype === 'image') {
      var input = document.createElement('input');
      input.type = 'file'; input.accept = 'image/*';
      input.onchange = function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        var csrf = document.querySelector('input[name="_csrf"]');
        if (csrf) fd.append('_csrf', csrf.value);
        fetch('/admin/upload-tinymce-image', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.location) {
            cb(data.location, { title: file.name });
          } else {
            alert('Lỗi upload: ' + (data.msg || 'Không thể tải ảnh'));
          }
        })
        .catch(err => alert('Lỗi kết nối: ' + err.message));
      };
      input.click();
    }
  },
  paste_preprocess: function(editor, args) {
    // Tự động xóa font-family inline khi paste từ Word / web / Google Docs
    args.content = args.content.replace(/font-family\s*:\s*[^;'"<>]+;?/gi, '');
    args.content = args.content.replace(/\s*face="[^"]*"/gi, '');
  },
  setup: function(editor) {
    setupTinyMCECallout(editor);
    editor.on('BeforeExecCommand', function(e) {
      if (['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull'].indexOf(e.command) !== -1) {
        var node = editor.selection.getNode();
        if (node && (node.nodeName === 'P' || node.nodeName === 'DIV')) {
          if (node.innerHTML.search(/<br\s*\/?>/i) !== -1) {
            var parts = node.innerHTML.split(/<br\s*\/?>/i).filter(function(l){ return l.trim() !== ''; });
            if (parts.length > 1) {
              var replacement = parts.map(function(l){ return '<p>' + l + '</p>'; }).join('');
              node.outerHTML = replacement;
            }
          }
        }
      }
    });

    editor.on('SetContent', function(e) {
      var body = editor.getBody();
      if (!body) return;
      var blocks = body.querySelectorAll('p, div');
      blocks.forEach(function(b) {
        if (b.querySelectorAll('table, ul, ol, img').length === 0 && b.innerHTML.search(/<br\s*\/?>/i) !== -1) {
          var parts = b.innerHTML.split(/<br\s*\/?>/i).filter(function(p){ return p.trim() !== ''; });
          if (parts.length > 1) {
            b.outerHTML = parts.map(function(p){ return '<p>' + p + '</p>'; }).join('');
          }
        }
      });
    });

    editor.on('change keyup setcontent paste execCommand nodechange input', function() {
      editor.save();
      if (typeof runSeoAnalysis === 'function') {
        clearTimeout(window._seoT);
        window._seoT = setTimeout(runSeoAnalysis, 300);
      }
    });
    // Xóa font-family còn sót sau khi paste (fallback về Times New Roman theo body)
    editor.on('PastePostProcess', function(e) {
      e.node.querySelectorAll('[style]').forEach(function(el) {
        el.style.fontFamily = '';
      });
      e.node.querySelectorAll('font[face]').forEach(function(el) {
        el.removeAttribute('face');
      });
    });
  }
  });
}

function initSpecEditor() {
  var target = document.getElementById('tinymceSpec');
  if (!target || target.dataset.editorLoading || tinymce.get('tinymceSpec')) return;
  target.dataset.editorLoading = '1';
  tinymce.init({
  selector: '#tinymceSpec',
  height: 250,
  language: 'vi',
  forced_root_block: 'p',
  force_p_newlines: true,
  force_br_newlines: false,
  convert_newlines_to_brs: false,
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | calloutbox removeformat',
    'alignleft aligncenter alignright alignjustify | bullist numlist checklist | outdent indent | table | link image | code fullscreen'
  ],
  font_family_formats: 'Times New Roman=times new roman,times,serif; Arial=arial,helvetica,sans-serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,sans-serif; Georgia=georgia,serif; Courier New=courier new,monospace',
  font_size_formats: '8px 9px 10px 11px 12px 13px 14px 16px 18px 20px 22px 24px 28px 32px 36px 48px 60px 72px',
  table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
  table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
  table_default_attributes: { 'border': '1' },
  table_class_list: [
    { title: 'Mặc định', value: '' },
    { title: 'Bảng sọc', value: 'table-striped' }
  ],
  content_style: `
    body { font-family: 'Times New Roman', Times, serif; font-size: 15px; line-height: 1.9; color: #333; padding: 16px; }
    table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    th { background: #0b1d3a; color: #fff; font-weight: 700; padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    tr:nth-child(even) td { background: #f8f9fc; }
    h1 { font-size: 28px; color: #0b1d3a; font-weight: 900; }
    h2 { font-size: 22px; color: #0b1d3a; font-weight: 800; }
    h3 { font-size: 18px; color: #0b1d3a; font-weight: 700; }
    img { max-width: 100%; height: auto; border-radius: 8px; }
    blockquote { border-left: 3px solid #c9a14a; padding: 12px 16px; background: #faf8f3; border-radius: 0 8px 8px 0; }
  `,
  menubar: 'file edit view insert format table',
  promotion: false,
  branding: false,
  license_key: 'gpl',
  automatic_uploads: true,
  paste_data_images: true,
  images_upload_url: '/admin/upload-tinymce-image',
  images_upload_handler: tinymceImageUploadHandler,
  file_picker_types: 'image',
  file_picker_callback: function(cb, value, meta) {
    if (meta.filetype === 'image') {
      var input = document.createElement('input');
      input.type = 'file'; input.accept = 'image/*';
      input.onchange = function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        var csrf = document.querySelector('input[name="_csrf"]');
        if (csrf) fd.append('_csrf', csrf.value);
        fetch('/admin/upload-tinymce-image', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.location) {
            cb(data.location, { title: file.name });
          } else {
            alert('Lỗi upload: ' + (data.msg || 'Không thể tải ảnh'));
          }
        })
        .catch(err => alert('Lỗi kết nối: ' + err.message));
      };
      input.click();
    }
  },
  paste_preprocess: function(editor, args) {
    // Tự động xóa font-family inline khi paste từ Word / web / Google Docs
    args.content = args.content.replace(/font-family\s*:\s*[^;'"<>]+;?/gi, '');
    args.content = args.content.replace(/\s*face="[^"]*"/gi, '');
  },
  setup: function(editor) {
    setupTinyMCECallout(editor);
    editor.on('BeforeExecCommand', function(e) {
      if (['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull'].indexOf(e.command) !== -1) {
        var node = editor.selection.getNode();
        if (node && (node.nodeName === 'P' || node.nodeName === 'DIV')) {
          if (node.innerHTML.search(/<br\s*\/?>/i) !== -1) {
            var parts = node.innerHTML.split(/<br\s*\/?>/i).filter(function(l){ return l.trim() !== ''; });
            if (parts.length > 1) {
              var replacement = parts.map(function(l){ return '<p>' + l + '</p>'; }).join('');
              node.outerHTML = replacement;
            }
          }
        }
      }
    });

    editor.on('SetContent', function(e) {
      var body = editor.getBody();
      if (!body) return;
      var blocks = body.querySelectorAll('p, div');
      blocks.forEach(function(b) {
        if (b.querySelectorAll('table, ul, ol, img').length === 0 && b.innerHTML.search(/<br\s*\/?>/i) !== -1) {
          var parts = b.innerHTML.split(/<br\s*\/?>/i).filter(function(p){ return p.trim() !== ''; });
          if (parts.length > 1) {
            b.outerHTML = parts.map(function(p){ return '<p>' + p + '</p>'; }).join('');
          }
        }
      });
    });

    editor.on('change keyup setcontent paste execCommand nodechange input', function() {
      editor.save();
      if (typeof runSeoAnalysis === 'function') {
        clearTimeout(window._seoT);
        window._seoT = setTimeout(runSeoAnalysis, 300);
      }
    });
    // Xóa font-family còn sót sau khi paste (fallback về Times New Roman theo body)
    editor.on('PastePostProcess', function(e) {
      e.node.querySelectorAll('[style]').forEach(function(el) {
        el.style.fontFamily = '';
      });
      e.node.querySelectorAll('font[face]').forEach(function(el) {
        el.removeAttribute('face');
      });
    });
  }
  });
}





document.getElementById('productForm').addEventListener('submit', function(e) {
  if (window.tinymce) tinymce.triggerSave();
  // === VALIDATE CÁC TRƯỜNG BẮT BUỘC ===
  var name  = (document.querySelector('input[name="name"]')?.value  || '').trim();
  var skuInput = document.querySelector('input[name="sku"]');
  var oemInput = document.querySelector('input[name="oem_code"]');
  var sku = (skuInput?.value || '').trim();
  var oem = (oemInput?.value || '').trim();
  if (!sku && oem && skuInput) {
    skuInput.value = oem;
    sku = oem;
  }
  var price = (document.querySelector('input[name="price"]')?.value || '').trim();
  var stockEl = document.querySelector('input[name="stock"]');
  var stock = stockEl !== null ? stockEl.value : null;
  var catSel = document.querySelector('select[name="category_id"]');
  var catVal = catSel ? catSel.value : '';
  var costPrice = (document.querySelector('input[name="cost_price"]')?.value || '').trim();
  var origPrice = (document.querySelector('input[name="original_price"]')?.value || '').trim();

  var errors = [];
  if (!name)  errors.push('• Tên sản phẩm không được để trống');
  if (!sku)   errors.push('• Vui lòng nhập mã sản phẩm (SKU) hoặc mã OEM');
  var inventoryManagedSeparately = document.querySelector('input[name="_inventory_in_product_form"]')?.value === '0';
  var isContactPrice = !!(document.querySelector('input[name="is_contact_price"]')?.checked || document.querySelector('input[name="is_call"]')?.checked);
  if (!inventoryManagedSeparately) {
    if (!isContactPrice && (!price || parseInt(price) <= 0)) errors.push('• Giá bán sau VAT phải lớn hơn 0');
    if (stock === '' || stock === null) errors.push('• Tồn kho hiện tại không được để trống');
    else if (!/^\d+$/.test(stock) || parseInt(stock, 10) > 1000) errors.push('• Tồn kho hiện tại chỉ được từ 0 đến 1000');
    var maxStock = (document.querySelector('input[name="max_stock"]')?.value || '').trim();
    if (maxStock !== '' && (!/^\d+$/.test(maxStock) || parseInt(maxStock, 10) > 1000)) errors.push('• Tồn kho tối đa chỉ được từ 0 đến 1000');
  }
  if (!catVal || catVal === '') errors.push('• Vui lòng chọn danh mục sản phẩm');

  // Validate giá nhập (tùy chọn - nhưng nếu có thì phải là số nguyên >= 0)
  if (costPrice !== '') {
    if (!/^\d+$/.test(costPrice)) {
      errors.push('• Giá nhập phải là số nguyên không âm (hoặc để trống)');
    }
  }
  if (origPrice !== '') {
    if (!/^\d+$/.test(origPrice)) {
      errors.push('• Giá gốc phải là số nguyên không âm (hoặc để trống)');
    }
  }

  if (errors.length > 0) {
    e.preventDefault();
    alert('⚠️ Vui lòng điền đầy đủ thông tin bắt buộc:\n\n' + errors.join('\n'));
    return false;
  }

  // === VALIDATE SEO SCORE >= 80 ===
  var scoreEl = document.getElementById('seoScore');
  if (scoreEl) {
    var scoreText = scoreEl.textContent || '';
    var match = scoreText.match(/(\d+)\/100/);
    var pct = match ? parseInt(match[1]) : 0;
    if (pct < 80) {
      e.preventDefault();
      alert('❌ Điểm SEO chưa đủ!\n\nĐiểm hiện tại: ' + pct + '/100 (cần đạt tối thiểu 80/100)\n\nVui lòng cải thiện các tiêu chí SEO được đánh dấu ❌ bên dưới.');
      var panel = document.getElementById('seoPanel');
      if (panel) panel.scrollIntoView({behavior:'smooth', block:'center'});
      return false;
    }
  }
  // TinyMCE tự lưu vào textarea
});
function calcPrice() {
  var vatEl=document.getElementById('vatRate');
  var vatRate=vatEl?parseInt(vatEl.value):10;
  var before = parseInt(document.getElementById('priceBefore')?.value)||0;
  var tax = Math.round(before * vatRate / 100);
  var taxField=document.getElementById('taxAmount'); if(taxField)taxField.value=tax;
  if (before > 0) { document.getElementById('priceAfterVat').value = before + tax; }
}

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
}




// ── GỢI Ý TỪ KHÓA SEO ──
function suggestKeywords() {
  var btn = document.getElementById('btnSuggestKw');
  var box = document.getElementById('kwSuggestions');
  btn.disabled = true; btn.innerHTML = '⏳ Đang phân tích...';

  // Thu thập nội dung từ tất cả các nguồn
  var name = (document.querySelector('input[name="name"]')?.value || '').trim();
  var sku  = (document.querySelector('input[name="sku"]')?.value  || '').trim();
  var oem  = (document.querySelector('input[name="oem_code"]')?.value || '').trim();
  var catSel = document.querySelector('select[name="category_id"]');
  var catText = catSel ? catSel.options[catSel.selectedIndex]?.text || '' : '';

  function getEditorText(edId, taName) {
    if (typeof tinymce !== 'undefined') {
      var ed = tinymce.get(edId);
      if (!ed && tinymce.editors) {
        for (var i = 0; i < tinymce.editors.length; i++) {
          if (tinymce.editors[i].id === edId) { ed = tinymce.editors[i]; break; }
        }
      }
      if (ed) try { return (ed.getContent({format:'text'}) || '').trim(); } catch(e) {}
    }
    var ta = document.querySelector('textarea[name="' + taName + '"]');
    return ta ? (ta.value || '').replace(/<[^>]+>/g,' ').trim() : '';
  }

  var descText = getEditorText('tinymceDesc', 'description');
  var featText = getEditorText('tinymceFeat', 'features');
  var specText = getEditorText('tinymceSpec', 'specifications');

  var allText = (name + ' ' + catText + ' ' + descText + ' ' + featText + ' ' + specText).toLowerCase();

  // Từ dừng tiếng Việt + tiếng Anh phổ biến
  var stopWords = 'và của cho với các được có không trong này là một những được theo từ đến khi của bạn để như về sản phẩm tính năng thông số hàng chính hãng cao cấp chất lượng tốt nhất giá rẻ mua bán the and for with from this that are was were has have had been being will would could should may might shall can does did not but or if then else when how what which who whom whose where why all any both each few more most other some such than too very also just only own same her his its our their your a an in on at to by is it of as do no so up we he she me my we us i am be'.split(' ');

  // Trích xuất từ/cụm từ quan trọng
  var words = allText.replace(/[.,;:!?()[\]{}""''\"]/g, ' ').split(/\s+/).filter(function(w) {
    return w.length >= 2 && stopWords.indexOf(w) === -1 && !/^\d+$/.test(w);
  });

  // Đếm tần suất từ đơn
  var freq = {};
  words.forEach(function(w) { freq[w] = (freq[w] || 0) + 1; });

  // Tạo bigrams (cụm 2 từ)
  var bigrams = {};
  for (var i = 0; i < words.length - 1; i++) {
    var bg = words[i] + ' ' + words[i+1];
    bigrams[bg] = (bigrams[bg] || 0) + 1;
  }

  // Tạo trigrams (cụm 3 từ)
  var trigrams = {};
  for (var i = 0; i < words.length - 2; i++) {
    var tg = words[i] + ' ' + words[i+1] + ' ' + words[i+2];
    trigrams[tg] = (trigrams[tg] || 0) + 1;
  }

  // Ưu tiên: từ trong tiêu đề được boost x3
  var nameWords = name.toLowerCase().replace(/[.,;:!?()[\]{}""''\"]/g, ' ').split(/\s+/).filter(function(w) {
    return w.length >= 2 && stopWords.indexOf(w) === -1;
  });
  nameWords.forEach(function(w) { freq[w] = (freq[w] || 0) + 5; });

  // Gộp tất cả ngrams + sắp xếp theo tần suất
  var candidates = [];
  Object.keys(trigrams).forEach(function(k) { if (trigrams[k] >= 2) candidates.push({text: k, score: trigrams[k] * 4}); });
  Object.keys(bigrams).forEach(function(k) { if (bigrams[k] >= 2) candidates.push({text: k, score: bigrams[k] * 2.5}); });
  Object.keys(freq).forEach(function(k) { if (freq[k] >= 2 && k.length >= 3) candidates.push({text: k, score: freq[k]}); });

  // Thêm cụm từ đặc biệt từ tiêu đề (ưu tiên cao)
  if (name.length > 5) {
    // Lấy 2-4 từ đầu tiên từ tên SP làm keyword chính
    var nameTokens = name.toLowerCase().split(/\s+/).filter(function(w) { return w.length >= 2; });
    for (var len = Math.min(4, nameTokens.length); len >= 2; len--) {
      var kw = nameTokens.slice(0, len).join(' ');
      candidates.push({text: kw, score: 20});
    }
    // Nếu có tên hãng/model trong tên SP
    if (sku) candidates.push({text: sku.toLowerCase(), score: 15});
    if (oem) candidates.push({text: oem.toLowerCase(), score: 15});
  }

  // Thêm danh mục
  if (catText && catText !== 'Chọn danh mục' && catText.length > 2) {
    candidates.push({text: catText.toLowerCase(), score: 10});
  }

  // Loại trùng, sắp xếp
  var seen = {};
  candidates = candidates.filter(function(c) {
    if (seen[c.text]) return false;
    seen[c.text] = true;
    return true;
  }).sort(function(a, b) { return b.score - a.score; }).slice(0, 12);

  // Render
  if (candidates.length === 0) {
    box.style.display = 'block';
    box.innerHTML = '<div style="color:#666;font-size:12px">⚠️ Chưa đủ nội dung để gợi ý. Hãy viết mô tả, đặc điểm, thông số kỹ thuật trước.</div>';
  } else {
    var html = '<div style="font-size:11px;font-weight:700;color:#4338ca;margin-bottom:6px">💡 Từ khóa gợi ý (click để chọn):</div><div style="display:flex;flex-wrap:wrap;gap:6px">';
    candidates.forEach(function(c) {
      html += '<span onclick="pickKeyword(this)" style="cursor:pointer;padding:4px 12px;background:#fff;border:1px solid #c7d2fe;border-radius:20px;font-size:12px;color:#4338ca;font-weight:600;transition:all .2s" onmouseover="this.style.background=\'#4338ca\';this.style.color=\'#fff\'" onmouseout="this.style.background=\'#fff\';this.style.color=\'#4338ca\'">' + c.text + '</span>';
    });
    html += '</div>';
    box.style.display = 'block';
    box.innerHTML = html;
  }

  btn.disabled = false;
  btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg> Gợi ý từ khóa';
}

function pickKeyword(el) {
  var kw = el.textContent.trim();
  var input = document.getElementById('seoKeyword');
  input.value = kw;
  input.dispatchEvent(new Event('input'));
  runSeoAnalysis();
  // Highlight chọn
  document.querySelectorAll('#kwSuggestions span').forEach(function(s) {
    s.style.background = '#fff'; s.style.color = '#4338ca'; s.style.borderColor = '#c7d2fe';
  });
  el.style.background = '#4338ca'; el.style.color = '#fff'; el.style.borderColor = '#4338ca';
}

function suggestSecondaryKeywords() {
  var box = document.getElementById('secKwSuggestions');
  var name = (document.querySelector('input[name="name"]')?.value || '').trim();
  var brand = (document.querySelector('input[name="part_brand"]')?.value || '').trim();
  var oem = (document.querySelector('input[name="oem_code"]')?.value || '').trim();
  var catText = (document.querySelector('select[name="category_id"] option:checked')?.textContent || '').trim();

  if (!name) {
    box.style.display = 'block';
    box.innerHTML = '<div style="color:#b91c1c">⚠️ Vui lòng nhập Tên sản phẩm trước để hệ thống gợi ý từ khóa phụ.</div>';
    return;
  }

  var candidates = [];
  var cleanName = name.replace(/\|.*/, '').trim();

  // 1. Tên sản phẩm + dòng xe
  candidates.push(cleanName.toLowerCase());
  if (brand) candidates.push((cleanName + ' ' + brand).toLowerCase());
  if (oem) candidates.push(('mã phụ tùng ' + oem).toLowerCase());

  // 2. Từ khóa chuẩn đoán & tìm thông tin sửa chữa
  if (catText.match(/lốc|máy nén/i)) {
    candidates.push('lốc lạnh ô tô kêu to', 'nguyên nhân điều hòa không mát', 'cách kiểm tra lốc lạnh', 'lốc lạnh đóng ngắt liên tục');
  } else if (catText.match(/dàn lạnh|giàn lạnh/i)) {
    candidates.push('dàn lạnh ô tô bị rò rỉ gas', 'điều hòa ô tô có mùi hôi', 'thay dàn lạnh ô tô chính hãng');
  } else if (catText.match(/dàn nóng|giàn nóng/i)) {
    candidates.push('dàn nóng ô tô bị bẩn', 'vệ sinh dàn nóng điều hòa ô tô', 'dàn nóng ô tô xì gas');
  } else if (catText.match(/quạt/i)) {
    candidates.push('quạt dàn nóng ô tô không quay', 'quạt dàn lạnh ô tô kêu to', 'thay mô tơ quạt điều hòa');
  } else {
    candidates.push('dấu hiệu hỏng điều hòa ô tô', 'cách kiểm tra phụ tùng ô tô chính hãng');
  }

  var html = '<div style="font-size:11px;font-weight:700;color:#15803d;margin-bottom:6px">💡 Từ khóa phụ gợi ý (click để thêm):</div><div style="display:flex;flex-wrap:wrap;gap:6px">';
  candidates.forEach(function(kw) {
    html += '<span onclick="pickSecondaryKeyword(this)" style="cursor:pointer;padding:4px 10px;background:#fff;border:1px solid #86efac;border-radius:16px;font-size:11px;color:#15803d;font-weight:600">' + kw + '</span>';
  });
  html += '</div>';

  box.style.display = 'block';
  box.innerHTML = html;
}

function pickSecondaryKeyword(el) {
  var kw = el.textContent.trim();
  var input = document.getElementById('seoSecondaryKeywords');
  var cur = input.value.trim();
  if (cur) {
    var parts = cur.split(',').map(function(s){return s.trim();});
    if (parts.indexOf(kw) === -1) {
      input.value = cur + ', ' + kw;
    }
  } else {
    input.value = kw;
  }
  input.dispatchEvent(new Event('input'));
  runSeoAnalysis();
  el.style.background = '#15803d'; el.style.color = '#fff';
}

// ── SEO CONTENT ANALYSIS (Weighted Scoring) ──
function runSeoAnalysis() {
  var name = (document.querySelector('input[name="name"]')?.value || '').trim();
  var keyword = (document.getElementById('seoKeyword')?.value || '').toLowerCase().trim();
  var slugInput = document.getElementById('productSlug');
  var slug = (slugInput?.value || '').trim();
  if (slugInput && slugInput.dataset.auto === '1' && name) {
    slug = name.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    slugInput.value = slug;
  }

  function decodeHtml(html) {
    if (!html) return '';
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
  }

  function readEditor(edId, taName) {
    if (typeof tinymce !== 'undefined') {
      var ed = tinymce.get(edId);
      if (!ed && tinymce.editors) {
        for (var i = 0; i < tinymce.editors.length; i++) {
          if (tinymce.editors[i].id === edId) { ed = tinymce.editors[i]; break; }
        }
      }
      if (ed) {
        try {
          return {html: ed.getContent() || '', text: decodeHtml(ed.getContent() || '')};
        } catch(ex) {}
      }
    }
    var ta = document.querySelector('textarea[name="' + taName + '"]');
    if (ta && ta.value) {
      return {html: ta.value, text: decodeHtml(ta.value)};
    }
    return {html: '', text: ''};
  }

  var _d = readEditor('tinymceDesc', 'description');
  var descHtml = _d.html, descText = _d.text;
  var _f = readEditor('tinymceFeat', 'features');
  var featHtml = _f.html, featText = _f.text;
  var _s = readEditor('tinymceSpec', 'specifications');
  var specHtml = _s.html, specText = _s.text;
  var secKwInput = (document.getElementById('seoSecondaryKeywords')?.value || '').toLowerCase().trim();
  var allText = (name + ' ' + descText + ' ' + featText + ' ' + specText).toLowerCase();

  // Preview Google SERP
  var customSeoTitle = (document.getElementById('seoTitle')?.value || '').trim();
  var customSeoDesc = (document.getElementById('seoDescription')?.value || '').trim();
  var previewTitle = customSeoTitle || name;
  document.getElementById('seoPreviewTitle').textContent = previewTitle || 'Tiêu đề sản phẩm...';
  document.getElementById('seoPreviewDesc').textContent = customSeoDesc || (descText ? descText.substring(0,155) : 'Mô tả sản phẩm...');
  document.getElementById('seoPreviewSlug').textContent = slug || '...';

  // ── WEIGHTED SCORING (100 điểm) ──
  // Meta/Tiêu đề: 10đ | Mô tả: 35đ | Cấu trúc: 20đ | Từ khóa: 25đ | Hình ảnh: 10đ
  var score = 0;
  var checks = [];
  function add(pass, msg, cat, pts) { if(pass) score += pts; checks.push({pass:pass,msg:msg,cat:cat,pts:pts}); }

  // ─ META / TIÊU ĐỀ (10đ)
  add(previewTitle.length >= 15 && previewTitle.length <= 90, 'Tiêu đề Google dài ' + previewTitle.length + ' ký tự (tốt: 15-90)', 'meta', 5);
  var catSel = document.querySelector('select[name="category_id"]');
  add(catSel && catSel.value && catSel.value !== '', 'Đã chọn danh mục sản phẩm', 'meta', 5);

  // ─ MÔ TẢ SẢN PHẨM (35đ)
  add(descText.length >= 150, 'Mô tả dài ' + descText.length + ' ký tự (tối thiểu 150)', 'desc', 8);
  add(descText.length >= 250, 'Mô tả chi tiết ≥250 ký tự (' + descText.length + ')', 'desc', 5);
  var h2c = (descHtml.match(/<h2\b[^>]*>/gi)||[]).length;
  var h3c = (descHtml.match(/<h3\b[^>]*>/gi)||[]).length;
  add(h2c + h3c >= 1 || /<p\b[^>]*>.*?<strong>/i.test(descHtml), 'Mô tả có thẻ Heading H2/H3 hoặc tiêu đề in đậm', 'desc', 7);
  add(h2c + h3c >= 2 || (descHtml.match(/<strong>/gi)||[]).length >= 3, 'Mô tả có cấu trúc tiêu đề con rõ ràng', 'desc', 5);
  add(/<(ul|ol|table)\b[^>]*>/i.test(descHtml) || descText.indexOf('Mã phụ tùng') !== -1, 'Mô tả có danh sách bullet/bảng thông số', 'desc', 5);
  add(/<img\b[^>]*>/i.test(descHtml) || descText.length >= 200, 'Nội dung trình bày đầy đủ hình ảnh/thông tin minh họa', 'desc', 5);

  // ─ CẤU TRÚC: ĐẶC ĐIỂM + THÔNG SỐ (20đ)
  add(featText.length >= 30 || descText.length >= 300, 'Đặc điểm & Mô tả chi tiết (≥50 ký tự)', 'struct', 5);
  add(/<(ul|ol|p|table)\b[^>]*>/i.test(featHtml) || descText.length >= 300, 'Đặc điểm trình bày cấu trúc rõ ràng', 'struct', 3);
  
  var fb = (featHtml.match(/<(?:strong|b)\b[^>]*>/gi) || []).length + (descHtml.match(/<(?:strong|b)\b[^>]*>/gi) || []).length;
  add(fb >= 2, 'Bài viết có ' + fb + ' cụm/từ in đậm làm nổi bật (≥2)', 'struct', 2);
  add(specText.length >= 20 || descText.indexOf('Mã phụ tùng') !== -1, 'Thông số kỹ thuật đầy đủ', 'struct', 5);
  add(/<table/i.test(specHtml) || specText.length >= 50 || descText.length >= 300, 'Thông số trình bày dạng bảng/khối chi tiết', 'struct', 5);

  // ─ TỪ KHÓA MỤC TIÊU & TỪ KHÓA PHỤ (25đ)
  if (keyword) {
    var kwParts = keyword.split(/\s+/).filter(function(w){ return w.length >= 2; });
    var kwMatchInTitle = kwParts.every(function(w){ return name.toLowerCase().indexOf(w) !== -1; }) || name.toLowerCase().indexOf(keyword) !== -1;
    var kwMatchInDesc = kwParts.every(function(w){ return descText.toLowerCase().indexOf(w) !== -1; }) || descText.toLowerCase().indexOf(keyword) !== -1;

    add(kwMatchInTitle, 'Từ khóa mục tiêu có trong tiêu đề', 'kw', 8);
    add(kwMatchInDesc, 'Từ khóa mục tiêu có trong mô tả sản phẩm', 'kw', 7);
    
    // Từ khóa phụ check
    var secParts = secKwInput ? secKwInput.split(',').map(function(s){return s.trim();}).filter(Boolean) : [];
    var secFound = 0;
    secParts.forEach(function(skw){
      if (allText.indexOf(skw) !== -1) secFound++;
    });
    add(secParts.length === 0 || secFound > 0, 'Từ khóa phụ (' + (secParts.length > 0 ? secFound + '/' + secParts.length + ' từ xuất hiện' : 'đã tối ưu từ khóa phụ') + ')', 'kw', 5);
    add(kwMatchInDesc || allText.length >= 200, 'Mật độ từ khóa phân bổ tự nhiên trong bài viết', 'kw', 5);
  } else {
    add(name.length >= 15, 'Tiêu đề chứa từ khóa tự nhiên chuẩn SEO', 'kw', 25);
  }

  // ─ HÌNH ẢNH (10đ)
  var imgCnt = document.querySelectorAll('#img-preview-area .existing-img-wrap').length
    + document.querySelectorAll('#existingImagesRow .existing-img-wrap').length;
  add(imgCnt > 0 || /<img\b/i.test(descHtml), 'Sản phẩm có hình ảnh minh họa', 'img', 7);
  add(imgCnt >= 1 || /<img\b/i.test(descHtml), 'Có hình ảnh thực tế rõ nét', 'img', 3);

  // Render
  var catLabels = {
    meta: '📋 Tiêu đề & Meta', desc: '📝 Mô tả sản phẩm',
    struct: '⭐ Đặc điểm & Thông số', kw: '🔑 Từ khóa SEO', img: '🖼 Hình ảnh'
  };
  var html = '';
  ['meta','desc','struct','kw','img'].forEach(function(cat) {
    var items = checks.filter(function(c){return c.cat===cat;});
    if (!items.length) return;
    html += '<div style="font-weight:700;color:var(--navy);margin:10px 0 4px;font-size:13px">'+catLabels[cat]+'</div>';
    items.forEach(function(c){
      html += '<div style="color:'+(c.pass?'#059669':'#dc2626')+'">'+(c.pass?'✅':'❌')+' '+c.msg+' <span style="font-size:10px;color:#999">('+c.pts+'đ)</span></div>';
    });
  });
  document.getElementById('seoChecklist').innerHTML = html;

  var pct = Math.min(score, 100);
  var badge = document.getElementById('seoScore');
  if (pct >= 80)      { badge.textContent='✅ Đạt chuẩn SEO ('+pct+'/100)'; badge.style.background='#ecfdf5'; badge.style.color='#059669'; }
  else if (pct >= 50) { badge.textContent='⚠️ Trung bình ('+pct+'/100)'; badge.style.background='#fef3c7'; badge.style.color='#d97706'; }
  else                { badge.textContent='❌ Cần cải thiện ('+pct+'/100)'; badge.style.background='#fef2f2'; badge.style.color='#dc2626'; }
}

// Auto-trigger SEO analysis khi TinyMCE thay đổi
setTimeout(function() {
  runSeoAnalysis();
  // Kết nối TinyMCE events
  ['tinymceDesc','tinymceFeat','tinymceSpec'].forEach(function(id){
    var ed = tinymce.get(id);
    if(ed) ed.on('Change KeyUp', function(){
      clearTimeout(window._seoT);
      window._seoT = setTimeout(runSeoAnalysis, 800);
    });
  });
  document.querySelector('input[name="name"]')?.addEventListener('input', function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,500);});
  document.querySelector('input[name="sku"]')?.addEventListener('input', function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,500);});
  document.getElementById('productSlug')?.addEventListener('input', function(){this.dataset.auto='0';clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,300);});
  document.getElementById('seoTitle')?.addEventListener('input', function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,300);});
  document.getElementById('seoDescription')?.addEventListener('input', function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,300);});
  document.getElementById('seoKeyword')?.addEventListener('input', function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,500);});
  document.querySelector('select[name="category_id"]')?.addEventListener('change', function(){runSeoAnalysis();});
}, 1500);

// --- TỰ ĐỘNG CẬP NHẬT ĐƯỜNG DẪN URL TỨC THÌ THEO TÊN SẢN PHẨM ---
(function() {
  function toVietnameseSlug(str) {
    if (!str) return '';
    return str.toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd')
      .replace(/Đ/g, 'd')
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }

  window.forceSyncSlugLive = function() {
    var nameInput = document.getElementById('productName') || document.querySelector('input[name="name"]');
    var slugInput = document.getElementById('productSlug');
    if (!nameInput || !slugInput) return;
    slugInput.dataset.userEdited = '0';
    slugInput.value = toVietnameseSlug(nameInput.value);
    if (typeof runSeoAnalysis === 'function') runSeoAnalysis();
  };

  window.forceSyncSeoTitleLive = function() {
    var nameInput = document.getElementById('productName') || document.querySelector('input[name="name"]');
    var seoTitleInput = document.getElementById('seoTitle');
    if (!nameInput || !seoTitleInput) return;
    seoTitleInput.dataset.userEdited = '0';
    seoTitleInput.value = nameInput.value.trim();
    if (typeof runSeoAnalysis === 'function') runSeoAnalysis();
  };

  document.addEventListener('DOMContentLoaded', function() {
    var nameInput = document.getElementById('productName') || document.querySelector('input[name="name"]');
    var slugInput = document.getElementById('productSlug');
    var seoTitleInput = document.getElementById('seoTitle');
    if (!nameInput) return;

    function syncTitleAndSlugNow() {
      if (slugInput && (slugInput.dataset.userEdited !== '1' || !slugInput.value.trim())) {
        slugInput.value = toVietnameseSlug(nameInput.value);
      }
      if (seoTitleInput && (seoTitleInput.dataset.userEdited !== '1' || !seoTitleInput.value.trim())) {
        seoTitleInput.value = nameInput.value.trim();
      }
      if (typeof runSeoAnalysis === 'function') runSeoAnalysis();
    }

    ['input', 'keyup', 'change', 'paste', 'cut'].forEach(function(evtName) {
      nameInput.addEventListener(evtName, syncTitleAndSlugNow);
    });

    if (slugInput) {
      slugInput.addEventListener('input', function() {
        var autoValue = toVietnameseSlug(nameInput.value);
        if (this.value.trim() === '' || this.value.trim() === autoValue) {
          this.dataset.userEdited = '0';
        } else {
          this.dataset.userEdited = '1';
        }
      });
    }
  });
})();
</script>

<script>
// Init drag-and-drop for existing images
document.addEventListener('DOMContentLoaded', function() {
  var existingRow = document.getElementById('existingImagesRow');
  if (existingRow && existingRow.children.length > 0) {
    // Add order labels
    updateImageLabels(existingRow);
    
    // Init Sortable
    new Sortable(existingRow, {
      animation: 200,
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      onEnd: function() {
        updateImageLabels(existingRow);
        saveImageOrder(existingRow);
      }
    });
    
    // Add hint text
    var hint = document.createElement('div');
    hint.style.cssText = 'font-size:11px;color:#888;margin-top:6px;';
    hint.innerHTML = ' <b>Kéo thả</b> để sắp xếp lại thứ tự ảnh. Ảnh đầu tiên sẽ là ảnh chính.';
    existingRow.parentNode.insertBefore(hint, existingRow.nextSibling);
  }
});

function updateImageLabels(container) {
  var items = container.querySelectorAll('.existing-img-wrap');
  items.forEach(function(item, idx) {
    // Remove existing label
    var old = item.querySelector('.drag-handle-hint');
    if (old) old.remove();
    
    var lbl = document.createElement('div');
    lbl.className = 'drag-handle-hint';
    lbl.textContent = idx === 0 ? '⭐ Ảnh chính' : ('Ảnh ' + (idx + 1));
    if (idx === 0) lbl.style.background = 'rgba(201,151,44,0.9)';
    item.appendChild(lbl);
  });
}

function saveImageOrder(container) {
  var ids = [];
  container.querySelectorAll('.existing-img-wrap').forEach(function(el) {
    ids.push(el.getAttribute('data-img-id'));
  });
  
  var csrf = document.querySelector('input[name="_csrf"]')?.value || '';
  fetch('/admin/products/reorder-images', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&ids=' + encodeURIComponent(JSON.stringify(ids))
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      // Brief green flash on container
      container.style.outline = '2px solid #10b981';
      setTimeout(function() { container.style.outline = 'none'; }, 800);
    }
  })
  .catch(function(){});
}
</script>




<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

<script>
(function() {
  var picker = document.getElementById('img-picker');
  var preview = document.getElementById('img-preview-area');
  var hiddenInput = document.getElementById('img-hidden-input');
  if (!picker || !preview || !hiddenInput) return;
  if (picker.dataset.hasListener === '1') return;
  picker.dataset.hasListener = '1';

  var selectedFiles = [];
  var sortableNew = null;

  function handleNewFiles(incomingFiles) {
    var replaceImages = document.getElementById('replaceImages');
    var existingCount = document.querySelectorAll('#existingImagesRow .existing-img-wrap').length;
    var maximumNewImages = (!replaceImages || replaceImages.checked) ? 8 : Math.max(0, 8 - existingCount);

    incomingFiles.forEach(function(file) {
      if (!file.type || !file.type.match(/^image\//i)) return;
      if (file.size > 20 * 1024 * 1024) {
        alert('Ảnh "' + file.name + '" quá 20 MB, bỏ qua.');
        return;
      }
      // Deduplicate check
      var isDuplicate = selectedFiles.some(function(f) {
        return f.name === file.name && f.size === file.size && f.lastModified === file.lastModified;
      });
      if (!isDuplicate) {
        if (selectedFiles.length >= maximumNewImages) {
          alert('Sản phẩm chỉ được chọn tối đa ' + maximumNewImages + ' ảnh mới.');
          return;
        }
        selectedFiles.push(file);
      }
    });

    renderPreviews();
    syncHiddenInput();
  }

  picker.addEventListener('change', function(e) {
    if (e.target.files && e.target.files.length) {
      handleNewFiles(Array.from(e.target.files));
      picker.value = '';
    }
  });

  // Hỗ trợ kéo thả
  var dropBox = picker.closest('label');
  if (dropBox) {
    ['dragenter', 'dragover'].forEach(function(evtName) {
      dropBox.addEventListener(evtName, function(e) {
        e.preventDefault(); e.stopPropagation();
        dropBox.style.borderColor = 'var(--navy)';
        dropBox.style.background = '#e2e8f0';
      });
    });
    ['dragleave', 'drop'].forEach(function(evtName) {
      dropBox.addEventListener(evtName, function(e) {
        e.preventDefault(); e.stopPropagation();
        dropBox.style.borderColor = '#ccc';
        dropBox.style.background = '#f8f9fa';
      });
    });
    dropBox.addEventListener('drop', function(e) {
      var dt = e.dataTransfer;
      if (dt && dt.files && dt.files.length) {
        handleNewFiles(Array.from(dt.files));
      }
    });
  }

  function renderPreviews() {
    preview.innerHTML = '';
    selectedFiles.forEach(function(file, idx) {
      var wrapper = document.createElement('div');
      wrapper.className = 'existing-img-wrap';
      wrapper.setAttribute('data-file-idx', idx);
      wrapper.style.cssText = 'position:relative;width:120px;aspect-ratio:4/3;border:1px solid #d0d5dd;border-radius:6px;overflow:hidden;background:transparent;cursor:grab;flex-shrink:0;transition:all 0.2s;';

      var cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.className = 'img-select-checkbox-new';
      cb.setAttribute('data-file-idx', idx);
      cb.style.cssText = 'position:absolute;top:4px;left:4px;z-index:20;width:20px;height:20px;accent-color:#ef4444;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.3);';
      cb.onclick = function(e) { e.stopPropagation(); };
      cb.onchange = function() { if (typeof updateImageSelectionState === 'function') updateImageSelectionState(); };

      var img = document.createElement('img');
      img.style.cssText = 'width:100%;height:100%;object-fit:contain;pointer-events:none;';
      var reader = new FileReader();
      reader.onload = function(e) { img.src = e.target.result; };
      reader.readAsDataURL(file);

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.innerHTML = '&times;';
      removeBtn.title = 'Bỏ ảnh này';
      removeBtn.style.cssText = 'position:absolute;top:4px;right:4px;width:22px;height:22px;background:rgba(220,38,38,0.9);color:#fff;border:none;border-radius:50%;font-size:16px;line-height:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.3);z-index:10;';
      removeBtn.setAttribute('data-idx', idx);
      removeBtn.addEventListener('click', async function(e) {
        e.stopPropagation();
        var i = parseInt(this.getAttribute('data-idx'), 10);
        if (typeof csConfirmAsync === 'function') {
          if (!(await csConfirmAsync('Bạn có chắc muốn bỏ ảnh này?'))) return;
        }
        selectedFiles.splice(i, 1);
        renderPreviews();
        syncHiddenInput();
      });

      var label = document.createElement('div');
      label.className = 'drag-handle-hint';
      if (idx === 0) {
        label.textContent = '⭐ Ảnh chính';
        label.style.background = 'rgba(201,151,44,0.9)';
      } else {
        label.textContent = 'Ảnh ' + (idx + 1);
      }

      var zoomBtn = document.createElement('button');
      zoomBtn.type = 'button';
      zoomBtn.innerHTML = '🔍';
      zoomBtn.title = 'Xem ảnh lớn';
      zoomBtn.style.cssText = 'position:absolute;bottom:4px;right:4px;width:24px;height:24px;border-radius:4px;background:rgba(15,23,42,0.85);color:#fff;border:1px solid rgba(255,255,255,0.4);cursor:pointer;font-size:12px;line-height:1;display:flex;align-items:center;justify-content:center;z-index:30;box-shadow:0 2px 5px rgba(0,0,0,0.4);';
      zoomBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        if (typeof window.openImgLightbox === 'function') window.openImgLightbox(img.src);
      });

      wrapper.appendChild(cb);
      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      wrapper.appendChild(zoomBtn);
      wrapper.appendChild(label);
      preview.appendChild(wrapper);
    });

    if (typeof updateImageSelectionState === 'function') updateImageSelectionState();

    if (sortableNew) sortableNew.destroy();
    if (selectedFiles.length > 1) {
      sortableNew = new Sortable(preview, {
        animation: 200,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function(evt) {
          if (evt.oldIndex !== undefined && evt.newIndex !== undefined && evt.oldIndex !== evt.newIndex) {
            var movedItem = selectedFiles.splice(evt.oldIndex, 1)[0];
            selectedFiles.splice(evt.newIndex, 0, movedItem);
            renderPreviews();
            syncHiddenInput();
          }
        }
      });
    }
  }

  window.__removeSelectedNewFiles = function(indexes) {
    // Remove in descending index order
    indexes.sort(function(a,b){ return b - a; });
    indexes.forEach(function(idx) {
      if (idx >= 0 && idx < selectedFiles.length) {
        selectedFiles.splice(idx, 1);
      }
    });
    renderPreviews();
    syncHiddenInput();
  };

  function syncHiddenInput() {
    var dt = new DataTransfer();
    selectedFiles.forEach(function(f) { dt.items.add(f); });
    hiddenInput.files = dt.files;
  }

  var form = hiddenInput.closest('form');
  if (form) {
    form.addEventListener('submit', function() { syncHiddenInput(); });
  }
})();

// Initialize Tom Select for dropdowns
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-numeric-max]').forEach(function(input) {
    function normalizeNumericValue() {
      var digits = input.value.replace(/[^0-9]/g, '').replace(/^0+(?=\d)/, '');
      var maximum = parseInt(input.getAttribute('data-numeric-max'), 10) || 0;
      if (maximum > 0 && digits !== '' && parseInt(digits, 10) > maximum) {
        digits = String(maximum);
      }
      if (input.value !== digits) input.value = digits;
    }
    input.addEventListener('input', normalizeNumericValue, {passive: true});
    input.addEventListener('change', normalizeNumericValue);
  });

  if (document.getElementById('partBrandSelect')) {
    new TomSelect('#partBrandSelect', {
      plugins: ['remove_button'],
      create: false,
      maxOptions: null,
      placeholder: 'Tìm và chọn thương hiệu...'
    });
  }
  if (document.getElementById('carBrandSelect')) {
    new TomSelect('#carBrandSelect', {
      plugins: ['remove_button'],
      create: false,
      maxOptions: null,
      placeholder: 'Tìm và chọn hãng xe...'
    });
  }
  if (document.getElementById('categorySelect')) {
    new TomSelect('#categorySelect', {
      create: false,
      maxOptions: null,
      placeholder: 'Tìm và chọn danh mục...'
    });
  }
  var prodForm = document.getElementById('productForm');
  if (prodForm) {
    prodForm.addEventListener('submit', function(e) {
      var priceInput = document.getElementById('priceAfterVat');
      var originalInput = document.querySelector('input[name="original_price"]');
      if (priceInput && originalInput && originalInput.value.trim() !== '') {
        var price = parseInt(priceInput.value) || 0;
        var original = parseInt(originalInput.value) || 0;
        if (original > 0 && original < price) {
          e.preventDefault();
          alert('Lỗi: Giá gốc không được nhỏ hơn Giá bán sau VAT!');
          originalInput.focus();
        }
      }
    });
  }
});
</script>

<script>
// --- TỰ ĐỘNG LƯU BẢN NHÁP (AUTOSAVE & RESTORE) ---
(function() {
  var form = document.getElementById('productForm');
  if (!form) return;

  var isEdit = <?= isset($product) ? 'true' : 'false' ?>;
  var productId = <?= isset($product) ? intval($product['id']) : 0 ?>;
  var draftKey = isEdit ? ('product_draft_edit_' + productId) : 'product_draft_new';

  // Lấy toàn bộ dữ liệu hiện tại của form
  function getFormData() {
    var data = {};
    
    // Lấy các input thông thường
    var inputs = form.querySelectorAll('input[type="text"], input[type="number"], select, textarea');
    inputs.forEach(function(input) {
      if (input.name && !input.name.includes('_csrf') && input.id !== 'tinymceDesc' && input.id !== 'tinymceFeat' && input.id !== 'tinymceSpec') {
        if (input.type === 'checkbox') {
          data[input.name] = input.checked;
        } else {
          data[input.name] = input.value;
        }
      }
    });

    // Lấy dữ liệu TomSelect (Thương hiệu, Hãng xe, Danh mục)
    ['partBrandSelect', 'carBrandSelect', 'categorySelect'].forEach(function(selectId) {
      var select = document.getElementById(selectId);
      if (select && select.tomselect) {
        data[selectId] = select.tomselect.getValue();
      }
    });

    // Lấy dữ liệu từ các trình soạn thảo TinyMCE
    if (typeof tinymce !== 'undefined') {
      ['tinymceDesc', 'tinymceFeat', 'tinymceSpec'].forEach(function(id) {
        var ed = tinymce.get(id);
        if (ed) {
          data[id] = ed.getContent();
        }
      });
    }

    return data;
  }

  // Tự động lưu nháp
  var saveTimeout;
  function autosave() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(function() {
      var data = getFormData();
      localStorage.setItem(draftKey, JSON.stringify(data));
      showAutosaveStatus('Đã tự động lưu nháp...');
    }, 1000);
  }

  function showAutosaveStatus(msg) {
    var statusEl = document.getElementById('autosave-status');
    if (!statusEl) {
      statusEl = document.createElement('div');
      statusEl.id = 'autosave-status';
      statusEl.style.cssText = 'position:fixed;bottom:24px;right:24px;background:rgba(26,50,88,0.9);color:#fff;padding:8px 16px;border-radius:20px;font-size:12px;font-weight:600;z-index:99999;transition:opacity 0.3s;pointer-events:none;opacity:0;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
      document.body.appendChild(statusEl);
    }
    statusEl.textContent = msg;
    statusEl.style.opacity = '1';
    setTimeout(function() {
      statusEl.style.opacity = '0';
    }, 2000);
  }

  // Gắn sự kiện thay đổi cho các trường thông thường
  form.addEventListener('input', autosave);
  form.addEventListener('change', autosave);

  // Gắn sự kiện thay đổi cho các trình soạn thảo TinyMCE
  setTimeout(function() {
    ['tinymceDesc', 'tinymceFeat', 'tinymceSpec'].forEach(function(id) {
      var ed = tinymce.get(id);
      if (ed) {
        ed.on('Change KeyUp NodeChange', autosave);
      }
    });
  }, 2500);

  // Xóa bản nháp sau khi người dùng bấm Lưu/Cập nhật thành công
  form.addEventListener('submit', function() {
    localStorage.removeItem(draftKey);
  });

  // --- LOGIC KHÔI PHỤC BẢN NHÁP ---
  window.addEventListener('load', function() {
    var saved = localStorage.getItem(draftKey);
    if (!saved) return;

    var data;
    try {
      data = JSON.parse(saved);
    } catch (e) {
      return;
    }

    // Chỉ gợi ý khôi phục nếu bản nháp thực sự có nội dung
    var hasContent = false;
    if (data.name && data.name.trim().length > 0) hasContent = true;
    if (data.tinymceDesc && data.tinymceDesc.trim().replace(/<[^>]+>/g, '').length > 0) hasContent = true;

    if (!hasContent) return;

    // Tạo Popup Modal khôi phục ở giữa màn hình (màu xanh dương)
    var overlay = document.createElement('div');
    overlay.id = 'draft-restore-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:999999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);animation: fadeIn 0.2s ease;';
    overlay.innerHTML = `
      <div style="background:#fff;border-radius:16px;width:90%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.15), 0 10px 10px -5px rgba(0,0,0,0.1);border-top: 6px solid #2563eb;padding:28px 24px;text-align:center;animation: scaleUp 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);font-family:system-ui,-apple-system,sans-serif;">
        <div style="width:56px;height:56px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#2563eb;">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:#1e3a8a;margin-bottom:8px;letter-spacing:-0.01em">Khôi phục bản nháp</h3>
        <p style="color:#4b5563;font-size:14px;line-height:1.6;margin-bottom:24px;">Hệ thống phát hiện dữ liệu chưa lưu từ phiên làm việc trước. Bạn có muốn khôi phục lại không?</p>
        <div style="display:flex;gap:12px;justify-content:center;">
          <button type="button" id="btn-restore-draft" style="background:#2563eb;color:#fff;border:none;padding:10px 24px;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;transition:background 0.2s;box-shadow:0 4px 6px -1px rgba(37,99,235,0.2);">Khôi phục</button>
          <button type="button" id="btn-discard-draft" style="background:#fff;color:#4b5563;border:1px solid #d1d5db;padding:9px 23px;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.2s;">Bỏ qua</button>
        </div>
      </div>
      <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes scaleUp { from { transform: scale(0.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        #btn-restore-draft:hover { background: #1d4ed8 !important; }
        #btn-discard-draft:hover { background: #f9fafb !important; border-color: #c4c5c7 !important; }
      </style>
    `;

    document.body.appendChild(overlay);

    // Khi người dùng bấm "Bỏ qua"
    document.getElementById('btn-discard-draft').addEventListener('click', function() {
      localStorage.removeItem(draftKey);
      overlay.remove();
    });

    // Khi người dùng bấm "Khôi phục"
    document.getElementById('btn-restore-draft').addEventListener('click', function() {
      // 1. Điền lại các ô input thông thường
      Object.keys(data).forEach(function(key) {
        var input = form.querySelector('[name="' + key + '"]');
        if (input && key !== 'description' && key !== 'features' && key !== 'specifications') {
          if (input.type === 'checkbox') {
            input.checked = !!data[key];
            input.dispatchEvent(new Event('change'));
          } else {
            input.value = data[key];
            input.dispatchEvent(new Event('input'));
          }
        }
      });

      // 2. Điền lại các trường TomSelect
      ['partBrandSelect', 'carBrandSelect', 'categorySelect'].forEach(function(selectId) {
        var select = document.getElementById(selectId);
        if (select && select.tomselect && data[selectId]) {
          select.tomselect.setValue(data[selectId]);
        }
      });

      // 3. Điền lại nội dung TinyMCE
      ['tinymceDesc', 'tinymceFeat', 'tinymceSpec'].forEach(function(id) {
        if (data[id]) {
          var ed = tinymce.get(id);
          if (ed) {
            ed.setContent(data[id]);
          } else {
            // Đợi TinyMCE load hẳn nếu chưa khởi động xong
            setTimeout(function() {
              var ed2 = tinymce.get(id);
              if (ed2) ed2.setContent(data[id]);
            }, 1000);
          }
        }
      });

      overlay.remove();
      if (typeof runSeoAnalysis === 'function') runSeoAnalysis();
      showAutosaveStatus('Đã khôi phục toàn bộ dữ liệu bản nháp!');
    });
  });
})();
</script>

