<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js">
function swTab(t){
  ['desc','feat','spec'].forEach(function(x){
    var panel=document.getElementById('panel'+x.charAt(0).toUpperCase()+x.slice(1));
    var tab=document.getElementById('tab'+x.charAt(0).toUpperCase()+x.slice(1));
    if(panel)panel.style.display=x===t?'block':'none';
    if(tab){tab.classList.toggle('_tab-active',x===t);}
  });
}

</script>
<style>
.form-layout{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}
@media(max-width:900px){.form-layout{grid-template-columns:1fr}}
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
<div class="dash-head">
  <h1><?= isset($product) ? ' Chỉnh sửa sản phẩm' : ' Đăng sản phẩm mới' ?></h1>
  <?php if(isset($product)):?><span class="badge-status status-<?= $product['status'] ?>"><?= $product['status'] ?></span><?php endif;?>
</div>

<form method="post" action="<?= isset($product) ? '/admin/products/'.$product['id'].'/edit' : '/admin/products/new' ?>"
      enctype="multipart/form-data" id="productForm">
  <?= csrfField() ?>
  <input type="hidden" name="description" id="descHidden">
  <input type="hidden" name="seo_title_field" id="seoTitleHidden">
  <input type="hidden" name="seo_description_field" id="seoDescHidden">

  <div class="form-layout">
    <!-- LEFT COLUMN -->
    <div>
      <!-- THÔNG TIN CƠ BẢN -->
      <div class="panel">
        <div class="panel-head"><h3> Thông tin cơ bản</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Tên sản phẩm <span class="req">*</span></label>
            <input type="text" name="name" required minlength="5" maxlength="200"
                   value="<?= e($product['name']??'') ?>" placeholder="Tên đầy đủ sản phẩm">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Mã sản phẩm (SKU) <span class="req">*</span></label>
              <input type="text" name="sku" required value="<?= e($product['sku']??'') ?>" placeholder="VD: CS-001">
            </div>
            <div class="form-group">
              <label>Mã OEM</label>
              <input type="text" name="oem_code" value="<?= e($product['oem_code']??'') ?>" placeholder="VD: PFR6V">
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
              <div id="brandBoxWrapper" style="border:1px solid #d0d5dd;border-radius:8px;background:#fff;overflow:hidden;<?= ($product['part_brand']??'')==='HIDDEN' ? 'opacity:0.45;pointer-events:none' : '' ?>">
                <div style="padding:6px 10px;display:grid;grid-template-columns:1fr 1fr;gap:0;">
                  <?php foreach($allBrands as $pb): ?>
                  <label style="display:flex;align-items:center;gap:8px;padding:7px 8px;font-size:13px;font-weight:500;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#1a1a2e">
                    <input type="checkbox" class="brandCheck" value="<?= e($pb['name']) ?>"
                      <?= in_array(trim($pb['name']), $selectedBrands) ? 'checked' : '' ?>
                      onchange="updateBrandHidden()"
                      style="width:15px;height:15px;cursor:pointer;flex-shrink:0;accent-color:#1a1a2e">
                    <span><?= e($pb['name']) ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
                <div style="border-top:2px solid #f0f0f0;padding:8px 14px;background:#f8f9fc;">
                </div>
              </div>
              <label style="display:flex;align-items:center;gap:8px;padding:8px 0;font-size:13px;cursor:pointer">
                <input type="checkbox" onchange="toggleBrandHide(this.checked)" <?= ($product['part_brand']??'')==="HIDDEN"?"checked":"" ?> style="width:14px;height:14px;cursor:pointer;accent-color:#dc2626">
                <span style="color:#dc2626;font-weight:500">Không hiển thị mục Thương hiệu</span>
              </label>
              
<style>
/* Quill font size dropdown labels */
.ql-snow .ql-picker.ql-size .ql-picker-label::before { content: 'Cỡ chữ'; }
.ql-snow .ql-picker.ql-size .ql-picker-item::before { content: attr(data-value); }
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value]::before { content: attr(data-value); }

/* Quill font family dropdown labels */
.ql-snow .ql-picker.ql-font .ql-picker-label::before,
.ql-snow .ql-picker.ql-font .ql-picker-item::before { content: 'Sans Serif'; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="serif"]::before { content: 'Serif'; font-family: Georgia, serif; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="monospace"]::before { content: 'Monospace'; font-family: monospace; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="sans-serif"]::before { content: 'Sans Serif'; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="arial"]::before { content: 'Arial'; font-family: Arial; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="times"]::before { content: 'Times New Roman'; font-family: 'Times New Roman'; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="verdana"]::before { content: 'Verdana'; font-family: Verdana; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="tahoma"]::before { content: 'Tahoma'; font-family: Tahoma; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before { content: 'Georgia'; font-family: Georgia; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="impact"]::before { content: 'Impact'; font-family: Impact; }
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="courier"]::before { content: 'Courier New'; font-family: 'Courier New'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="serif"]::before { content: 'Serif'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="monospace"]::before { content: 'Monospace'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="sans-serif"]::before { content: 'Sans Serif'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="arial"]::before { content: 'Arial'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="times"]::before { content: 'Times New Roman'; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before { content: 'Georgia'; }
/* Apply actual fonts to items */
.ql-font-serif { font-family: Georgia, serif; }
.ql-font-monospace { font-family: 'Courier New', monospace; }
.ql-font-sans-serif { font-family: Arial, sans-serif; }
.ql-font-arial { font-family: Arial; }
.ql-font-times { font-family: 'Times New Roman', serif; }
.ql-font-verdana { font-family: Verdana; }
.ql-font-tahoma { font-family: Tahoma; }
.ql-font-georgia { font-family: Georgia; }
.ql-font-impact { font-family: Impact; }
.ql-font-courier { font-family: 'Courier New', monospace; }
</style>

<script>
              function updateBrandHidden(){
                var checks = document.querySelectorAll('.brandCheck:checked');
                var vals = [];
                checks.forEach(function(c){ vals.push(c.value); });
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
              <div style="border:1px solid #d0d5dd;border-radius:8px;background:#fff;overflow:hidden;max-height:250px;overflow-y:auto">
                <div style="padding:6px 10px;display:grid;grid-template-columns:1fr 1fr;gap:0;">
                  <label style="display:flex;align-items:center;gap:8px;padding:7px 8px;font-size:13px;font-weight:500;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#1a1a2e;grid-column:span 2">
                    <input type="checkbox" name="car_brand_ids[]" value="0" <?= empty($selectedCarBrands) ? 'checked' : '' ?> style="width:14px;height:14px;cursor:pointer"> — Tất cả / Chung —
                  </label>
                  <label style="display:flex;align-items:center;gap:8px;padding:7px 8px;font-size:13px;font-weight:500;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#1a1a2e;grid-column:span 2">
                    <input type="checkbox" name="car_brand_ids[]" value="HIDDEN" <?= in_array('HIDDEN', array_map('strval', $selectedCarBrands)) ? 'checked' : '' ?> style="width:14px;height:14px;cursor:pointer"> Không hiển thị mục này
                  </label>
                  <?php foreach($brands as $b): ?>
                  <label style="display:flex;align-items:center;gap:8px;padding:7px 8px;font-size:13px;font-weight:500;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#1a1a2e">
                    <input type="checkbox" name="car_brand_ids[]" value="<?=$b['id']?>" <?= in_array($b['id'], $selectedCarBrands) ? 'checked' : '' ?> style="width:14px;height:14px;cursor:pointer"> <?= htmlspecialchars($b['name']) ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Danh mục <span class="req">*</span></label>
              <select name="category_id" required>
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
          <div id="quillDesc"><?= isset($product) ? $product['description'] : '' ?></div>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Hỗ trợ H1-H6, định dạng phong phú như Word. Dùng H2 cho tiêu đề mục chính, H3 cho tiêu đề con.
          </div>
        </div>
        
        <div id="panelFeat" class="panel-body" style="display:none;padding-bottom:0">
          <input type="hidden" name="features" id="featHidden">
          <div id="quillFeat"><?= isset($product) ? $product['features'] : '' ?></div>
          <div style="padding:8px 12px;background:#f9f9f9;border:1px solid var(--line);border-top:none;border-radius:0 0 4px 4px;font-size:11px;color:#999">
             Trình soạn thảo đặc điểm sản phẩm.
          </div>
        </div>
        
        <div id="panelSpec" class="panel-body" style="display:none;padding-bottom:0">
          <input type="hidden" name="specifications" id="specHidden">
          <div id="quillSpec"><?= isset($product) ? $product['specifications'] : '' ?></div>
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
}
</script>

      <!-- HÌNH ẢNH -->
      <div class="panel">
        <div class="panel-head"><h3> Hình ảnh sản phẩm</h3></div>
        <div class="panel-body">
          <?php if(!empty($images)):?>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px" id="existingImagesRow">
              <?php foreach($images as $img):?>
                <div class="existing-img-wrap" data-img-id="<?= $img['id'] ?>" style="position:relative;width:90px;height:90px;border-radius:6px;overflow:hidden;border:2px solid #e0e0e0;flex-shrink:0">
                  <img src="/uploads/products/<?= e($img['file_path']) ?>" style="width:100%;height:100%;object-fit:cover">
                  <button type="button" onclick="deleteProductImage(<?= $img['id'] ?>, this)" title="Xóa ảnh này"
                    style="position:absolute;top:2px;right:2px;width:22px;height:22px;border-radius:50%;background:rgba(231,76,60,0.9);color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:700;line-height:1;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,0.3);transition:all 0.15s"
                    onmouseover="this.style.background='#c0392b';this.style.transform='scale(1.15)'"
                    onmouseout="this.style.background='rgba(231,76,60,0.9)';this.style.transform='scale(1)'"
                  ></button>
                </div>
              <?php endforeach;?>
            </div>
          <?php endif;?>
          <div class="form-group">
            <label>Thêm ảnh mới (tối đa 8 ảnh)</label>
            <div id="img-preview-area" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px"></div>
            <label class="btn btn-outline-navy btn-sm" style="cursor:pointer;display:inline-block">
               Chọn ảnh
              <input type="file" id="img-picker" multiple accept="image/jpeg,image/png,image/webp" style="display:none">
            </label>
            <input type="file" name="images[]" id="img-hidden-input" multiple accept="image/*" style="display:none">
            <div class="form-help" style="margin-top:6px">JPG/PNG/WEBP, mỗi ảnh ≤ 5MB, tối đa 8 ảnh. Click  để bỏ ảnh không muốn đăng.</div>
          </div>
          <div id="imgPreviewRow" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px"></div>
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
            <label>Từ khóa mục tiêu <span style="font-weight:400;color:#888;font-size:11px">(Focus Keyword — để kiểm tra)</span></label>
            <input type="text" name="seo_keyword" id="seoKeyword"
                   value="<?= e($product['seo_keyword']??'') ?>" placeholder="VD: két nước hyundai i10"
                   oninput="runSeoAnalysis()">
          </div>

          <!-- Google SERP Preview -->
          <div style="background:#fff;border:1px solid #dfe1e5;border-radius:8px;padding:14px;margin-bottom:14px">
            <div style="font-size:10px;color:#888;margin-bottom:4px;display:flex;align-items:center;gap:4px">
              <svg width="14" height="14" viewBox="0 0 24 24"><path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Xem trước trên Google
            </div>
            <div id="seoPreviewTitle" style="font-size:18px;color:#1a0dab;line-height:1.3;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Tiêu đề sản phẩm — Cooling</div>
            <div style="font-size:12px;color:#006621;margin-bottom:2px">https://coolingsystem.vn/products/...</div>
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
          <button type="submit" class="btn btn-gold btn-block btn-lg" style="margin-top:8px">
            <?= isset($product) ? ' Cập nhật SP' : ' Đăng sản phẩm' ?>
          </button>
          <?php if(isset($product)):?>
            <a href="/products/<?= $product['id'] ?>" target="_blank" class="btn btn-outline-navy btn-block btn-sm" style="margin-top:8px;text-align:center"> Xem trang SP</a>
          <?php endif;?>
        </div>
      </div>

      <!-- GIÁ & TỒN KHO -->
      <div class="panel">
        <div class="panel-head"><h3> Giá & Tồn kho</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Giá nhập <span style="color:#888;font-weight:normal;font-size:11px">(tùy chọn)</span></label>
            <input type="number" name="cost_price" id="costPrice" min="0"
                   value="<?= $product['cost_price']??'' ?>" placeholder="VD: 400000"
                   >
          </div>

          <div class="form-group">
            <label>Giá bán sau VAT <span class="req">*</span></label>
            <input type="number" name="price" id="priceAfterVat" required min="1000"
                   value="<?= $product['price']??'' ?>" placeholder="VD: 440000">
          </div>
          <div class="form-group">
            <label>Giá gốc (để gạch ngang)</label>
            <input type="number" name="original_price" value="<?= $product['original_price']??'' ?>">
          </div>
          
          <div class="form-group">
            <label>Tồn kho hiện tại <span class="req">*</span></label>
            <input type="number" name="stock" required min="0" max="1000" value="<?= $product['stock']??0 ?>">
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div class="form-group">
              <label>Tồn kho tối thiểu</label>
              <input type="number" name="min_stock" min="0" value="<?= $product['min_stock']??5 ?>">
            </div>
            <div class="form-group">
              <label>Tồn kho tối đa</label>
              <input type="number" name="max_stock" min="0" value="<?= $product['max_stock']??1000 ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Bảo hành (tháng)</label>
            <input type="number" name="warranty_months" value="<?= $product['warranty_months']??12 ?>" min="0">
          </div>
        </div>
      </div>
    </div><!-- /sidebar -->
  </div><!-- /form-layout -->

<script>
document.querySelector('input[name="stock"]')?.addEventListener('input', function() {
  if (parseInt(this.value) > 1000) {
    this.value = 1000;
    alert('Số lượng tồn kho tối đa là 1.000 sản phẩm!');
  }
});
</script>

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
function deleteProductImage(imageId, btn) {
  if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;
  var csrf = document.querySelector('input[name="_csrf"]')?.value || '';
  var wrap = btn.closest('.existing-img-wrap');
  
  // Visual feedback
  wrap.style.opacity = '0.4';
  wrap.style.pointerEvents = 'none';
  
  fetch('/admin/products/delete-image', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&image_id=' + imageId
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      wrap.style.transition = 'all 0.3s ease';
      wrap.style.transform = 'scale(0)';
      wrap.style.opacity = '0';
      setTimeout(() => wrap.remove(), 300);
    } else {
      alert('Lỗi: ' + (data.msg || 'Không thể xóa'));
      wrap.style.opacity = '1';
      wrap.style.pointerEvents = 'auto';
    }
  })
  .catch(err => {
    alert('Lỗi kết nối: ' + err.message);
    wrap.style.opacity = '1';
    wrap.style.pointerEvents = 'auto';
  });
}
</script>


<script>



var toolbarFull = [
  [{ 'font': ['', 'serif', 'monospace', 'sans-serif', 'arial', 'times', 'verdana', 'tahoma', 'georgia', 'impact', 'courier'] }],
  [{ 'size': ['8px','9px','10px','11px','12px','13px','14px','16px','18px','20px','22px','24px','26px','28px','32px','36px','48px','60px','72px'] }],
  [{ 'header': [1,2,3,4,5,6,false] }],
  ['bold','italic','underline','strike'],
  [{ 'color': [] }, { 'background': [] }],
  ['blockquote','code-block'],
  [{ 'script': 'sub' }, { 'script': 'super' }],
  [{ 'list':'ordered' }, { 'list':'bullet' }, { 'list':'check' }],
  [{ 'indent':'-1' }, { 'indent':'+1' }],
  [{ 'align': ['','center','right','justify'] }],
  ['link','image','video'],
  ['clean']
];

// Custom font size handler
var SizeStyle = Quill.import('attributors/style/size');
SizeStyle.whitelist = ['8px','9px','10px','11px','12px','13px','14px','16px','18px','20px','22px','24px','26px','28px','32px','36px','48px','60px','72px'];
Quill.register(SizeStyle, true);
var FontStyle = Quill.import('attributors/style/font');
FontStyle.whitelist = ['', 'serif', 'monospace', 'sans-serif', 'arial', 'times', 'verdana', 'tahoma', 'georgia', 'impact', 'courier'];
Quill.register(FontStyle, true);

var quillDesc = new Quill('#quillDesc', {
  theme: 'snow', placeholder: 'Viết mô tả chi tiết sản phẩm...',
  modules: { toolbar: { container: toolbarFull } }
});
var quillFeat = new Quill('#quillFeat', {
  theme: 'snow', placeholder: 'Viết đặc điểm nổi bật...',
  modules: { toolbar: { container: toolbarFull } }
});
var quillSpec = new Quill('#quillSpec', {
  theme: 'snow', placeholder: 'Viết thông số kỹ thuật...',
  modules: { toolbar: { container: toolbarFull } }
});


// === Word-like Table Grid Picker ===
function addTableHandler(quillInstance) {
  var toolbarEl = quillInstance.container.previousSibling;
  if (!toolbarEl || !toolbarEl.classList.contains('ql-toolbar')) return;

  var wrap = document.createElement('span');
  wrap.className = 'ql-formats';
  wrap.style.position = 'relative';

  var tableBtn = document.createElement('button');
  tableBtn.type = 'button';
  tableBtn.className = 'ql-table-insert';
  tableBtn.title = 'Chèn bảng';
  tableBtn.innerHTML = '<svg viewBox="0 0 18 18" width="18" height="18"><rect x="1" y="1" width="16" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="1" y1="7" x2="17" y2="7" stroke="currentColor" stroke-width="1"/><line x1="1" y1="12" x2="17" y2="12" stroke="currentColor" stroke-width="1"/><line x1="7" y1="1" x2="7" y2="17" stroke="currentColor" stroke-width="1"/><line x1="12" y1="1" x2="12" y2="17" stroke="currentColor" stroke-width="1"/></svg>';
  tableBtn.style.cssText = 'cursor:pointer;padding:3px 5px;';

  // Grid picker popup
  var popup = document.createElement('div');
  popup.className = 'table-grid-popup';
  popup.style.cssText = 'display:none;position:absolute;top:100%;left:0;z-index:9999;background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;box-shadow:0 8px 30px rgba(0,0,0,0.15);min-width:200px;';
  
  var label = document.createElement('div');
  label.style.cssText = 'text-align:center;font-size:12px;font-weight:700;color:#0b1d3a;margin-bottom:8px;';
  label.textContent = 'Chọn kích thước bảng';
  popup.appendChild(label);

  var ROWS = 8, COLS = 8;
  var grid = document.createElement('div');
  grid.style.cssText = 'display:grid;grid-template-columns:repeat(' + COLS + ',24px);gap:2px;justify-content:center;';
  
  var cells = [];
  var selR = 0, selC = 0;

  for (var r = 0; r < ROWS; r++) {
    for (var c = 0; c < COLS; c++) {
      var cell = document.createElement('div');
      cell.style.cssText = 'width:24px;height:24px;border:1px solid #ddd;border-radius:3px;cursor:pointer;transition:all 0.1s;';
      cell.dataset.r = r + 1;
      cell.dataset.c = c + 1;
      cells.push(cell);
      grid.appendChild(cell);
    }
  }
  popup.appendChild(grid);

  var info = document.createElement('div');
  info.style.cssText = 'text-align:center;font-size:11px;color:#888;margin-top:6px;';
  info.textContent = '0 × 0';
  popup.appendChild(info);

  // Hover highlight
  grid.addEventListener('mouseover', function(e) {
    var t = e.target;
    if (!t.dataset.r) return;
    selR = parseInt(t.dataset.r);
    selC = parseInt(t.dataset.c);
    info.textContent = selR + ' × ' + selC;
    cells.forEach(function(cl) {
      var cr = parseInt(cl.dataset.r), cc = parseInt(cl.dataset.c);
      if (cr <= selR && cc <= selC) {
        cl.style.background = '#0b1d3a';
        cl.style.borderColor = '#0b1d3a';
      } else {
        cl.style.background = '#f8f9fc';
        cl.style.borderColor = '#ddd';
      }
    });
  });

  // Click to insert
  grid.addEventListener('click', function(e) {
    var t = e.target;
    if (!t.dataset.r) return;
    var rows = parseInt(t.dataset.r), cols = parseInt(t.dataset.c);
    
    var html = '<table style="width:100%;border-collapse:collapse;margin:16px 0"><thead><tr>';
    for (var c = 0; c < cols; c++) {
      html += '<th style="border:1px solid #ddd;padding:10px 14px;background:#0b1d3a;color:#fff;font-weight:700;text-align:left">Cột ' + (c+1) + '</th>';
    }
    html += '</tr></thead><tbody>';
    for (var r = 1; r < rows; r++) {
      html += '<tr>';
      for (var c = 0; c < cols; c++) {
        html += '<td style="border:1px solid #ddd;padding:10px 14px;text-align:left">&nbsp;</td>';
      }
      html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    
    var range = quillInstance.getSelection(true);
    quillInstance.clipboard.dangerouslyPasteHTML(range ? range.index : quillInstance.getLength(), html);
    popup.style.display = 'none';
  });

  // Toggle popup
  tableBtn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
    // Reset grid
    cells.forEach(function(cl) { cl.style.background = '#f8f9fc'; cl.style.borderColor = '#ddd'; });
    info.textContent = '0 × 0';
  });

  // Close on outside click
  document.addEventListener('click', function(e) {
    if (!wrap.contains(e.target)) popup.style.display = 'none';
  });

  wrap.appendChild(tableBtn);
  wrap.appendChild(popup);
  toolbarEl.appendChild(wrap);
}

addTableHandler(quillDesc);
addTableHandler(quillFeat);
addTableHandler(quillSpec);





document.getElementById('productForm').addEventListener('submit', function(e) {
  // SEO validation - block if score < 50%
  var scoreEl = document.getElementById('seoScore');
  if(scoreEl) {
    var scoreText = scoreEl.textContent || '';
    var match = scoreText.match(/\((\d+)\/100\)/);
    var pct = match ? parseInt(match[1]) : 0;
    if(pct < 0) {
      e.preventDefault();
      alert(' Chưa đạt đầy đủ tiêu chuẩn SEO!\n\nĐiểm SEO hiện tại: ' + pct + '/100 (cần đạt 100/100)\n\nVui lòng hoàn thiện TẤT CẢ các tiêu chí:\n• Tên sản phẩm (20-100 ký tự)\n• Mã SKU và OEM\n• Danh mục sản phẩm\n• Mô tả sản phẩm (≥150 ký tự, có H2/H3, danh sách)\n• Đặc điểm sản phẩm (≥50 ký tự, có danh sách, in đậm)\n• Thông số kỹ thuật (≥30 ký tự)\n• Hình ảnh sản phẩm');
      // Scroll to SEO panel
      var panel = document.getElementById('seoPanel');
      if(panel) panel.scrollIntoView({behavior:'smooth', block:'center'});
      return false;
    }
  }
  // Proceed with normal submission
  document.getElementById('descHidden').value = quillDesc.root.innerHTML;
  document.getElementById('featHidden').value = quillFeat.root.innerHTML;
  document.getElementById('specHidden').value = quillSpec.root.innerHTML;
});

function calcPrice() {
  var vatEl=document.getElementById('vatRate');
  var vatRate=vatEl?parseInt(vatEl.value):10;
  var before = parseInt(document.getElementById('priceBefore').value)||0;
  var tax = Math.round(before * vatRate / 100);
  var taxField=document.getElementById('taxAmount'); if(taxField)taxField.value=tax;
  if (before > 0) {
    document.getElementById('priceAfterVat').value = before + tax;
  }
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


// ── SEO CONTENT ANALYSIS (Rank Math / Yoast style) ──
function runSeoAnalysis() {
  var name = (document.querySelector('input[name="name"]')?.value || '').trim();
  var sku = (document.querySelector('input[name="sku"]')?.value || '').trim();
  var oem = (document.querySelector('input[name="oem_code"]')?.value || '').trim();
  var keyword = (document.getElementById('seoKeyword')?.value || '').toLowerCase().trim();

  // Get content from Quill editors
  var descText='', featText='', specText='', descHtml='', featHtml='', specHtml='';
  try { descText = quillDesc.getText().trim(); descHtml = quillDesc.root.innerHTML; } catch(e){}
  try { featText = quillFeat.getText().trim(); featHtml = quillFeat.root.innerHTML; } catch(e){}
  try { specText = quillSpec.getText().trim(); specHtml = quillSpec.root.innerHTML; } catch(e){}
  var allText = (name + ' ' + descText + ' ' + featText + ' ' + specText).toLowerCase();
  var allHtml = descHtml + featHtml + specHtml;

  // Preview
  document.getElementById('seoPreviewTitle').textContent = name ? name + ' — Cooling' : 'Tiêu đề sản phẩm...';
  document.getElementById('seoPreviewDesc').textContent = descText ? descText.substring(0,155) : 'Mô tả sản phẩm...';

  var checks = [], score = 0, total = 0;
  function add(pass, msg, cat) {
    total++;
    if(pass) score++;
    checks.push({pass:pass, msg:msg, cat:cat});
  }

  // ── THÔNG TIN CƠ BẢN ──
  add(name.length >= 20 && name.length <= 100, 'Tên SP dài ' + name.length + ' ký tự (tốt: 20-100)', 'basic');
  add(!!sku, 'Đã có mã SKU' + (sku ? ': ' + sku : ''), 'basic');
  add(!!oem, 'Đã có mã OEM' + (oem ? ': ' + oem : ''), 'basic');
  var catSel = document.querySelector('select[name="category_id"]');
  add(catSel && catSel.value && catSel.value !== '' && catSel.value !== 'HIDDEN', 'Đã chọn danh mục sản phẩm', 'basic');

  // ── MÔ TẢ SẢN PHẨM ──
  add(descText.length >= 150, 'Mô tả dài ' + descText.length + ' ký tự (tối thiểu 150)', 'desc');
  add(descText.length >= 300, 'Mô tả đủ chi tiết ≥300 ký tự (' + descText.length + ')', 'desc');
  var h2Count = (descHtml.match(/<h2/gi)||[]).length;
  var h3Count = (descHtml.match(/<h3/gi)||[]).length;
  add(h2Count > 0, 'Mô tả có ' + h2Count + ' tiêu đề H2 (nên có ít nhất 1)', 'desc');
  add(h2Count + h3Count >= 2, 'Mô tả có tổng ' + (h2Count+h3Count) + ' heading H2/H3 (nên ≥2)', 'desc');
  var hasDescList = /<(ul|ol)/i.test(descHtml);
  add(hasDescList, 'Mô tả có danh sách (bullet/numbered) giúp dễ đọc', 'desc');
  var hasDescImg = /<img/i.test(descHtml);
  add(hasDescImg, 'Mô tả có chèn hình ảnh minh họa', 'desc');

  // ── ĐẶC ĐIỂM SẢN PHẨM ──
  add(featText.length >= 50, 'Đặc điểm dài ' + featText.length + ' ký tự (tối thiểu 50)', 'feat');
  var hasFeatList = /<(ul|ol)/i.test(featHtml);
  add(hasFeatList, 'Đặc điểm dùng danh sách để liệt kê', 'feat');
  var featBold = (featHtml.match(/<(strong|b)>/gi)||[]).length;
  add(featBold >= 2, 'Đặc điểm có ' + featBold + ' từ/cụm in đậm (nên ≥2)', 'feat');

  // ── THÔNG SỐ KỸ THUẬT ──
  add(specText.length >= 30, 'Thông số KT dài ' + specText.length + ' ký tự (tối thiểu 30)', 'spec');
  var hasTable = /<table/i.test(specHtml);
  add(hasTable || specText.length >= 80, 'Thông số có bảng hoặc nội dung chi tiết', 'spec');

  // ── TỪ KHÓA ──
  if(keyword) {
    add(name.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa "'+keyword+'" có trong tên sản phẩm', 'kw');
    add(descText.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa có trong mô tả sản phẩm', 'kw');
    add(featText.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa có trong đặc điểm sản phẩm', 'kw');
    // Density
    var kwCount = (allText.match(new RegExp(keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi'))||[]).length;
    var wordCount = allText.split(/\s+/).length;
    var density = wordCount > 0 ? (kwCount/wordCount*100).toFixed(1) : 0;
    add(kwCount >= 3 && density < 5, 'Mật độ từ khóa: '+density+'% ('+kwCount+' lần, tốt: 1-3%)', 'kw');
  }

  // ── HÌNH ẢNH ──
  var imgCount = document.querySelectorAll('#imgPreviewRow img').length + document.querySelectorAll('.panel-body img[src*="uploads"]').length;
  add(imgCount > 0, 'Sản phẩm có ' + imgCount + ' hình ảnh', 'img');

  // Build output grouped by category
  var catLabels = {basic:' Thông tin cơ bản', desc:' Mô tả sản phẩm', feat:'⭐ Đặc điểm sản phẩm', spec:' Thông số kỹ thuật', kw:' Từ khóa mục tiêu', img:' Hình ảnh'};
  var cats = ['basic','desc','feat','spec','kw','img'];
  var html = '';
  cats.forEach(function(cat) {
    var items = checks.filter(function(c){return c.cat===cat;});
    if(!items.length) return;
    html += '<div style="font-weight:700;color:var(--navy);margin:10px 0 4px;font-size:13px">'+catLabels[cat]+'</div>';
    items.forEach(function(c) {
      html += '<div style="color:'+(c.pass?'#059669':'#dc2626')+'">'+(c.pass?'':'')+' '+c.msg+'</div>';
    });
  });
  document.getElementById('seoChecklist').innerHTML = html;

  // Score
  var pct = total > 0 ? Math.round(score/total*100) : 0;
  var badge = document.getElementById('seoScore');
  if(pct >= 80) { badge.textContent = ' Đạt chuẩn SEO ('+pct+'/100)'; badge.style.background='#ecfdf5'; badge.style.color='#059669'; }
  else if(pct >= 50) { badge.textContent = ' Trung bình ('+pct+'/100)'; badge.style.background='#fef3c7'; badge.style.color='#d97706'; }
  else { badge.textContent = ' Cần cải thiện ('+pct+'/100)'; badge.style.background='#fef2f2'; badge.style.color='#dc2626'; }
}

// Auto-analyze when content changes
setTimeout(function() {
  runSeoAnalysis();
  try{quillDesc.on('text-change',function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,800);});}catch(e){}
  try{quillFeat.on('text-change',function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,800);});}catch(e){}
  try{quillSpec.on('text-change',function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,800);});}catch(e){}
  document.querySelector('input[name="name"]')?.addEventListener('input',function(){clearTimeout(window._seoT);window._seoT=setTimeout(runSeoAnalysis,500);});
}, 800);

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
  var selectedFiles = [];

  picker.addEventListener('change', function(e) {
    var newFiles = Array.from(e.target.files);
    if (selectedFiles.length + newFiles.length > 8) {
      alert('Tối đa 8 ảnh!');
      return;
    }
    newFiles.forEach(function(file) {
      if (file.size > 5 * 1024 * 1024) {
        alert('Ảnh "' + file.name + '" quá 5MB, bỏ qua.');
        return;
      }
      selectedFiles.push(file);
    });
    renderPreviews();
    syncHiddenInput();
    picker.value = '';
  });

  function renderPreviews() {
    preview.innerHTML = '';
    selectedFiles.forEach(function(file, idx) {
      var wrapper = document.createElement('div');
      wrapper.style.cssText = 'position:relative;width:100px;height:100px;border:1px solid #d0d5dd;border-radius:6px;overflow:hidden;background:#f9fafb;';

      var img = document.createElement('img');
      img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
      var reader = new FileReader();
      reader.onload = function(e) { img.src = e.target.result; };
      reader.readAsDataURL(file);

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.innerHTML = '&times;';
      removeBtn.title = 'Bỏ ảnh này';
      removeBtn.style.cssText = 'position:absolute;top:2px;right:2px;width:24px;height:24px;background:rgba(220,38,38,0.9);color:#fff;border:none;border-radius:50%;font-size:18px;line-height:22px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.3);z-index:2;';
      removeBtn.setAttribute('data-idx', idx);
      removeBtn.addEventListener('click', function() {
        var i = parseInt(this.getAttribute('data-idx'));
        selectedFiles.splice(i, 1);
        renderPreviews();
        syncHiddenInput();
      });

      var label = document.createElement('div');
      label.textContent = idx === 0 ? 'Ảnh chính' : (idx + 1);
      label.style.cssText = 'position:absolute;bottom:0;left:0;right:0;background:rgba(26,50,88,0.85);color:#fff;font-size:10px;text-align:center;padding:2px 0;font-weight:600;';

      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      wrapper.appendChild(label);
      preview.appendChild(wrapper);
    });
  }

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
</script>