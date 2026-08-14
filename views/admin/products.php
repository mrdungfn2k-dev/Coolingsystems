<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
/* Quản lý SP: bảng vừa khung + ẩn thanh cuộn ngang; cụm nút THAO TÁC nằm NGANG 1 hàng */
.tbl-scroll { overflow-x: auto !important; }
/* các cột chữ: xuống dòng để bảng gọn, KHÔNG áp cho cột thao tác */
/* các cột chữ: chỉ ngắt ở khoảng trắng/gạch nối — KHÔNG bẻ giữa số/từ (vd '999','KHO') */
.tbl-scroll > .tbl td:nth-child(3) { white-space: normal !important; min-width: 260px !important; max-width: 380px !important; word-break: normal !important; overflow-wrap: break-word !important; }
/* GIÁ (cột 7): giá tiền giữ 1 dòng (chỉ ô dữ liệu, để tiêu đề dài vẫn xuống dòng) */
.tbl-scroll > .tbl td:nth-child(7) { white-space: nowrap !important; }
/* KHO (cột 8): số giữ 1 dòng, rộng rãi, căn giữa cho đều */
.tbl-scroll > .tbl th:nth-child(8), .tbl-scroll > .tbl td:nth-child(8) { white-space: nowrap !important; min-width: 46px !important; text-align: center !important; }
/* cột THAO TÁC: các nút trên 1 HÀNG NGANG, không bọc xuống dòng */
.tbl-scroll > .tbl td:last-child { max-width: none !important; white-space: nowrap !important; }
.tbl td:last-child > div { flex-wrap: nowrap !important; gap: 4px !important; justify-content: center !important; }
.tbl td:last-child a:not([href*="/history"]), .tbl td:last-child button[type="submit"] { padding: 0 9px !important; font-size: 12px !important; }
.tbl td:last-child a[href*="/history"] { width: 34px !important; height: 30px !important; box-sizing: border-box !important; flex: 0 0 auto !important; }
</style>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
  <h1 style="margin:0">Quản lý sản phẩm</h1>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <div style="position:relative;display:inline-block" id="exportDropdown">
      <button type="button" onclick="toggleExportMenu()" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;border-radius:8px;font-weight:600;padding:6px 14px">
        Xuất CSV ▾
      </button>
      <div id="exportMenu" style="display:none;position:absolute;top:100%;right:0;background:#fff;border:1.5px solid #cbd5e1;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:100;min-width:220px;margin-top:4px;overflow:hidden">
        <a href="#" onclick="document.getElementById('exportMenu').style.display='none';csColPick({section:'products',url:'/admin/products/export-csv',title:'Tất cả sản phẩm'});return false" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:13px" onmouseover="this.style.background='#f5f7fa'" onmouseout="this.style.background='#fff'">Xuất tất cả SP (CSV)</a>
        <a href="#" onclick="exportSelected();return false" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:13px" onmouseover="this.style.background='#f5f7fa'" onmouseout="this.style.background='#fff'">Xuất SP đã chọn (CSV)</a>
        <hr style="margin:4px 0;border:0;border-top:1px solid #eee">
        <a href="#" onclick="triggerImageExport('all');return false;" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:13px" onmouseover="this.style.background='#f5f7fa'" onmouseout="this.style.background='#fff'">Xuất ảnh tất cả SP (ZIP)</a>
        <a href="#" onclick="triggerImageExport('selected');return false;" style="display:block;padding:10px 16px;color:#333;text-decoration:none;font-size:13px" onmouseover="this.style.background='#f5f7fa'" onmouseout="this.style.background='#fff'">Xuất ảnh SP đã chọn (ZIP)</a>
      </div>
    </div>
    <button type="button" onclick="document.getElementById('csvImportModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;border-radius:8px;font-weight:600;padding:6px 14px">
      Nhập CSV
    </button>
    <form method="post" action="/admin/products/batch-set-call-price" style="display:inline-block;margin:0" onsubmit="return csConfirmForm(this, 'Xác nhận TÍCH CHỌN tất cả sản phẩm thành Liên hệ báo giá?')">
      <?= csrfField() ?>
      <input type="hidden" name="call_price_status" value="1">
      <button type="submit" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;font-weight:600;padding:6px 14px;border:1.5px solid #cbd5e1" title="Tích chọn Liên hệ báo giá cho tất cả SP">
        Tích tất cả Báo giá
      </button>
    </form>
    <form method="post" action="/admin/products/batch-set-call-price" style="display:inline-block;margin:0" onsubmit="return csConfirmForm(this, 'Xác nhận BỎ TÍCH tất cả sản phẩm để hiển thị lại giá bán bình thường?')">
      <?= csrfField() ?>
      <input type="hidden" name="call_price_status" value="0">
      <button type="submit" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;font-weight:600;padding:6px 14px;border:1.5px solid #cbd5e1" title="Bỏ tích Liên hệ báo giá để hiện lại giá bán cho tất cả SP">
        Bỏ tích hiện lại giá
      </button>
    </form>
    <a href="/admin/products/new" class="btn btn-gold btn-sm" style="border-radius:8px;font-weight:600;padding:6px 14px">+ Đăng SP mới</a>
  </div>
</div>
<!-- Import CSV Modal -->
<div id="csvImportModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:600px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="margin:0;color:var(--navy)">📥 Nhập sản phẩm từ file dữ liệu</h3>
      <button onclick="document.getElementById('csvImportModal').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#999">&times;</button>
    </div>
    <div style="background:#f0f4ff;border:1px solid #d0d8f0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:12px;line-height:1.6;color:#555">
      <b>📋 Định dạng CSV:</b> Hàng đầu tiên là tiêu đề. Mã hóa UTF-8.<br>
      <a href="/admin/products/export-csv?template=1" style="color:var(--navy);font-weight:600">⬇ Tải file mẫu CSV</a>
    </div>
    <form method="post" action="/admin/products/import-csv" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Xử lý trùng mã SKU?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="dup_sku" value="skip" checked> Báo lỗi và dừng import
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="dup_sku" value="update"> Cập nhật SP cũ bằng dữ liệu mới
          </label>
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Trạng thái mặc định?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="default_status" value="draft" checked> Bản nháp
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="default_status" value="published"> Xuất bản
          </label>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Cập nhật tồn kho?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="update_stock" value="no" checked> Không
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="update_stock" value="yes"> Có
          </label>
        </div>
        <div>
          <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:8px">Cập nhật giá?</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;cursor:pointer">
            <input type="radio" name="update_price" value="no" checked> Không
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="radio" name="update_price" value="yes"> Có
          </label>
        </div>
      </div>

      <div style="margin-bottom:16px">
        <label style="display:block;font-size:12px;font-weight:700;color:#555;margin-bottom:6px">Chọn file CSV</label>
        <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn btn-gold" style="flex:1">📤 Nhập sản phẩm</button>
        <button type="button" onclick="document.getElementById('csvImportModal').style.display='none'" class="btn btn-outline-navy" style="flex:1">Hủy</button>
      </div>
    </form>
  </div>
</div>
<?php
// Build filter options for Hãng xe and Thương hiệu
$carBrands = dbAll("SELECT DISTINCT b.id, b.name FROM brands b INNER JOIN products p ON p.car_brand_id=b.id ORDER BY b.name");
$partBrands = dbAll("SELECT name AS part_brand FROM product_brands ORDER BY sort_order, name");
?>
<?php if(!function_exists('afCdd')){ function afCdd($name,$current,$opts){
  $current=(string)$current; $sel=$opts[0]['text']??'';
  foreach($opts as $o){ if((string)$o['val']===$current){ $sel=$o['text']; break; } }
  echo '<input type="hidden" name="'.e($name).'" id="af-'.e($name).'" value="'.e($current).'">';
  echo '<div class="cdd" data-target="af-'.e($name).'">';
  echo '<button type="button" class="cdd-trigger" onclick="afCddToggle(this)"><span class="cdd-label">'.e($sel).'</span><svg class="cdd-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg></button>';
  echo '<div class="cdd-panel">';
  foreach($opts as $o){ $k=((string)$o['val']===$current)?' sel':''; echo '<div class="cdd-opt'.$k.'" data-val="'.e((string)$o['val']).'" onclick="afCddPick(this)">'.e($o['text']).'</div>'; }
  echo '</div></div>';
} } ?>
<style>
.af-filter .cdd { position:relative; }
.af-filter .cdd-trigger { width:100%; height:38px; padding:0 12px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1px solid #d6deea; border-radius:6px; background:#fff; color:#1a3258; font-size:13.5px; font-weight:500; cursor:pointer; font-family:inherit; transition:border-color .15s,box-shadow .15s; }
.af-filter .cdd-trigger:hover { border-color:#b9c4d6; }
.af-filter .cdd.open .cdd-trigger { border-color:#1a3258; box-shadow:0 0 0 3px rgba(26,50,88,.12); }
.af-filter .cdd-label { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left; }
.af-filter .cdd-arrow { flex-shrink:0; color:#1a3258; transition:transform .2s; }
.af-filter .cdd.open .cdd-arrow { transform:rotate(180deg); }
.af-filter .cdd-panel { display:none; position:absolute; top:calc(100% + 6px); left:0; right:0; min-width:170px; background:#fff; border:1px solid #d6deea; border-radius:8px; box-shadow:0 14px 38px rgba(15,35,66,.18); overflow-y:auto; max-height:300px; z-index:100; padding:6px; }
.af-filter .cdd.open .cdd-panel { display:block; }
.af-filter .cdd-opt { padding:9px 12px; border-radius:6px; font-size:13.5px; color:#1a3258; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; transition:background .12s; }
.af-filter .cdd-opt:hover { background:#f1f5fb; }
.af-filter .cdd-opt.sel { background:#1a3258; color:#fff; font-weight:600; }
/* === Đồng đều ô lọc: cùng chiều cao 42px + cùng cỡ chữ 14px === */
.af-filter .frm-input { height:42px !important; font-size:14px !important; }
.af-filter .cdd-trigger { height:42px !important; font-size:14px !important; }
.af-filter .cdd-label, .af-filter .cdd-opt { font-size:14px !important; }
.af-filter .btn { height:42px !important; }
/* === Tab Tất cả / Bản nháp / Đã xuất bản: khung navy nổi bật === */
.sec-tabs { gap:6px; }
.sec-tabs a { padding:9px 16px !important; border-radius:8px !important; border-bottom:none !important; margin-bottom:0 !important; transition:all .15s; }
.sec-tabs a.active { background:var(--navy) !important; color:#fff !important; }
.sec-tabs a:not(.active):hover { background:var(--navy-soft) !important; color:var(--navy) !important; }
/* === Trạng thái 'Xuất bản' -> tông navy cho đồng bộ === */
.badge-status.published { background:#eef2f9 !important; color:#1a3258 !important; }
/* bỏ thanh vàng accent phía trên dải tab */
.panel .sec-head::after { display:none !important; }
/* căn giữa dải tab (đệm trên/dưới) + nút Cột hiển thị không sát viền phải */
.panel .sec-head { padding-top:12px !important; padding-bottom:12px !important; gap:12px !important; }
.tbl-coltools { padding-right:20px !important; padding-top:6px !important; }
/* nút Cột hiển thị nằm CÙNG HÀNG với tabs (canh phải) */
.sec-head .tbl-coltools { margin:0 !important; padding:0 !important; }
.panel .sec-head .sec-tabs { margin-right:auto !important; }
/* đồng nhất bo góc khung bảng = 12px; bo luôn góc phần tử con để viền hiện rõ, hết 'nhọn' */
.dash-main .panel { border:1px solid var(--line) !important; border-radius:12px !important; overflow:visible !important; }
.dash-main .panel > .sec-head:first-child { border-top-left-radius:12px !important; border-top-right-radius:12px !important; }
.dash-main .panel > .tbl-scroll:last-child { border-bottom-left-radius:12px !important; border-bottom-right-radius:12px !important; overflow-x:auto !important; }
</style>
<form method="get" action="/admin/products" class="af-filter" style="background:#fff;padding:16px 20px;border-radius:12px;border:1px solid #eaeaea;box-shadow:0 2px 8px rgba(0,0,0,0.02);margin-bottom:20px;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end">
  <div style="flex:1;min-width:200px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">TÌM KIẾM</label>
    <input type="text" name="q" class="frm-input" value="<?= e($_GET['q']??'') ?>" placeholder="Tên SP, SKU..." style="width:100%;height:38px;border-radius:6px">
  </div>
  <div style="min-width:190px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">MÃ OEM</label>
    <div style="display:flex;gap:4px">
      <input type="text" name="oem" class="frm-input" value="<?= e($_GET['oem']??'') ?>" placeholder="Nhập mã OEM..." style="flex:1;height:42px;border-radius:6px">
      <button type="submit" class="btn btn-navy" style="height:42px;border-radius:6px;padding:0 12px;font-size:13px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:4px">
        TÌM OEM
      </button>
    </div>
  </div>
  <div style="min-width:160px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">DANH MỤC</label>
    <?php $afo=[['val'=>'','text'=>'Tất cả danh mục']]; foreach($categories as $cat){ $afo[]=['val'=>$cat['id'],'text'=>$cat['name']]; } afCdd('cat', $_GET['cat']??'', $afo); ?>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">TRẠNG THÁI</label>
    <?php afCdd('tab', ($_GET['tab']??'') ?: 'all', [['val'=>'all','text'=>'Tất cả'],['val'=>'published','text'=>'Đã xuất bản'],['val'=>'draft','text'=>'Bản nháp']]); ?>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">HÃNG XE</label>
    <?php $afo=[['val'=>'','text'=>'Tất cả hãng xe']]; foreach($carBrands as $br){ $afo[]=['val'=>$br['id'],'text'=>$br['name']]; } afCdd('brand', $_GET['brand']??'', $afo); ?>
  </div>
  <div style="min-width:140px">
    <label style="font-size:12px;font-weight:700;color:#555;display:block;margin-bottom:6px">THƯƠNG HIỆU SP</label>
    <?php $afo=[['val'=>'','text'=>'Tất cả thương hiệu']]; foreach($partBrands as $pb){ $afo[]=['val'=>$pb['part_brand'],'text'=>$pb['part_brand']]; } afCdd('pbrand', $_GET['pbrand']??'', $afo); ?>
  </div>
  <div style="display:flex;gap:8px">
    <button type="submit" class="btn btn-navy" style="height:38px;border-radius:6px;padding:0 20px;display:flex;align-items:center;gap:6px">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      Lọc
    </button>
    <a href="/admin/products" class="btn btn-outline-navy" style="height:38px;border-radius:6px;padding:0 16px;display:flex;align-items:center">Đặt lại</a>
  </div>
  <div style="font-size:13px;color:#666;align-self:center;margin-left:auto;font-weight:500">Tìm thấy: <strong class="text-navy"><?= $total ?? 0 ?></strong> sản phẩm</div>
</form>
<script>
function afCddToggle(b){ var d=b.closest('.cdd'); var o=d.classList.contains('open'); document.querySelectorAll('.af-filter .cdd.open').forEach(function(x){x.classList.remove('open');}); if(!o) d.classList.add('open'); }
function afCddPick(opt){ var d=opt.closest('.cdd'); var t=document.getElementById(d.getAttribute('data-target')); if(t) t.value=opt.getAttribute('data-val')||''; var l=d.querySelector('.cdd-label'); if(l) l.textContent=opt.textContent; d.querySelectorAll('.cdd-opt').forEach(function(o){o.classList.remove('sel');}); opt.classList.add('sel'); d.classList.remove('open'); }
document.addEventListener('click', function(e){ if(!e.target.closest('.af-filter .cdd')) document.querySelectorAll('.af-filter .cdd.open').forEach(function(x){x.classList.remove('open');}); });
/* đưa nút 'Cột hiển thị' lên cùng hàng với dải tab */
document.addEventListener('DOMContentLoaded', function(){ setTimeout(function(){ var ct=document.querySelector('.panel .tbl-coltools'), sh=document.querySelector('.panel .sec-head'); if(ct && sh && ct.parentNode!==sh) sh.appendChild(ct); }, 0); });
</script>
<div class="panel">
  <div class="sec-head" style="border:none;display:flex;justify-content:space-between;align-items:center"><div class="sec-tabs">
  <?php foreach(['all'=>'Tất cả','draft'=>'Bản nháp','published'=>'Đã xuất bản'] as $k=>$v):?>
  <a href="?tab=<?=$k?>" class="<?=$tab===$k?'active':''?>"><?=$v?></a><?php endforeach;?></div>
  <button type="button" id="bulkDeleteBtn" onclick="bulkDelete()" style="display:none;height:34px;line-height:34px;padding:0 16px;border-radius:6px;font-size:13px;font-weight:700;background:#1a3258;color:#fff;border:none;cursor:pointer;white-space:nowrap">XÓA ĐÃ CHỌN (<span id="bulkCount">0</span>)</button>
  </div>
<table class="tbl"><thead><tr>
  <th style="width:36px"><input type="checkbox" id="checkAll" onchange="toggleAllCheckboxes(this)"></th>
  <th>SKU</th>
  <th>Tên SP</th>
  <th>Danh mục</th>
  <th>Hãng xe</th>
  <th>Thương hiệu</th>
  <th>Trạng thái</th>
  <th style="text-align:center;min-width:220px">Thao tác</th>
</tr></thead><tbody>
<?php foreach($products as $p):?><tr>
  <td><input type="checkbox" class="row-check" value="<?=$p['id']?>"></td>
  <td class="fs-12"><?=e($p['sku']??'—')?></td>
  <td><a href="/admin/products/<?=$p['id']?>/edit?return_to=<?= rawurlencode($listReturnUrl ?? '/admin/products') ?>" class="text-navy" style="font-family:'Times New Roman', Times, serif!important;font-size:15px;font-weight:600"><?=truncate(e($p['name']),40)?></a></td>
  <td><?php if(!empty($p['cat_name'])): ?><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#eef2f9;color:#1a3258;border:1px solid #d3deec;white-space:nowrap"><?=e($p['cat_name'])?></span><?php else: ?><span style="color:#ccc">—</span><?php endif; ?></td>
  <td><?php if(!empty($p['brand_name'])): ?><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#eef2f9;color:#1a3258;border:1px solid #d3deec;white-space:nowrap"><?=e($p['brand_name'])?></span><?php else: ?><span style="color:#ccc">—</span><?php endif; ?></td>
  <td><?php if(!empty($p['part_brand'])): ?><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:#eef2f9;color:#1a3258;border:1px solid #d3deec;white-space:nowrap"><?=e($p['part_brand'])?></span><?php else: ?><span style="color:#ccc">—</span><?php endif; ?></td>
  <td><span class="badge-status <?=e($p['status'])?>"><?= $p['status']==='published' ? 'Xuất bản' : ($p['status']==='draft' ? 'Bản nháp' : e($p['status'])) ?></span></td>
  <td>
    <div style="display:flex;gap:6px;justify-content:center;align-items:center">
      <!-- Sửa -->
      <a href="/admin/products/<?=$p['id']?>/edit?return_to=<?= rawurlencode($listReturnUrl ?? '/admin/products') ?>"
         style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#1a3258;color:#fff;text-decoration:none;border:none;cursor:pointer;white-space:nowrap">
        Sửa
      </a>
      <!-- Lịch sử & lượt truy cập -->
      <a href="/admin/products/<?=$p['id']?>/history" title="Lịch sử đăng bài & lượt truy cập"
         style="display:inline-flex;align-items:center;justify-content:center;height:30px;width:34px;border-radius:6px;background:#eef2f7;color:#1a3258;text-decoration:none;border:1px solid #d6deea">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
      </a>
      <!-- Ngừng / Cho phép KD -->
      <?php if($p['status'] === 'published'): ?>
        <form method="post" action="/admin/products/<?=$p['id']?>/toggle-status" style="margin:0">
          <?= csrfField() ?>
          <input type="hidden" name="status" value="hidden">
          <button type="submit"
            style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#fff;color:#1a3258;border:1.5px solid #1a3258;cursor:pointer;white-space:nowrap">
            Ngừng KD
          </button>
        </form>
      <?php else: ?>
        <form method="post" action="/admin/products/<?=$p['id']?>/toggle-status" style="margin:0">
          <?= csrfField() ?>
          <input type="hidden" name="status" value="published">
          <button type="submit"
            style="display:inline-flex;align-items:center;height:30px;padding:0 14px;border-radius:6px;font-size:12px;font-weight:700;background:#fff;color:#1a3258;border:1.5px solid #1a3258;cursor:pointer;white-space:nowrap">
            Cho phép KD
          </button>
        </form>
      <?php endif; ?>
      <!-- Xóa -->
      <form method="post" action="/admin/products/<?=$p['id']?>/delete" style="margin:0" id="deleteForm-<?=$p['id']?>">
        <?= csrfField() ?>
        <button type="button" title="Xóa sản phẩm"
          onclick="showDeleteModal(<?=$p['id']?>)"
          style="display:inline-flex;align-items:center;justify-content:center;height:30px;width:30px;border-radius:6px;background:#fdf2f2;color:#ef4444;border:1px solid #fca5a5;cursor:pointer;opacity:1;transition:all 0.2s"
          onmouseover="this.style.color='#dc2626'; this.style.background='#fee2e2';"
          onmouseout="this.style.color='#ef4444'; this.style.background='#fdf2f2';">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"></path></svg>
        </button>
      </form>
    </div>
  </td>
</tr><?php endforeach;?></tbody></table>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:20px;padding:16px 0;border-top:1px solid #f0f0f0;">
    <?php
    require_once __DIR__.'/../partials/pagination.php';
    renderPagination($page, $totalPages, '/admin/products', $_GET);
    ?>
</div>
<?php endif; ?>
</div>



<!-- Modal Tiến trình Nén & Tải tệp ZIP -->
<div id="zipProgressModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:99999;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:14px;padding:28px;width:440px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center">
    <div style="width:56px;height:56px;margin:0 auto 16px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center">
      <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    </div>
    <h3 style="margin:0 0 8px;font-size:17px;font-weight:700;color:var(--navy)" id="zipModalTitle">Đang nén & xuất ảnh sản phẩm...</h3>
    <p style="font-size:13px;color:#666;margin:0 0 20px;line-height:1.5" id="zipModalDesc">Hệ thống đang tự động đóng gói hình ảnh theo Danh mục & Mã OEM. Vui lòng đợi trong giây lát...</p>
    
    <div style="background:#f1f5f9;border-radius:10px;height:10px;overflow:hidden;margin-bottom:16px;position:relative">
      <div id="zipProgressBar" style="background:linear-gradient(90deg, #2563eb, #3b82f6);height:100%;width:100%;animation:zipPulse 1.5s infinite ease-in-out;border-radius:10px"></div>
    </div>
    
    <div style="font-size:12px;color:#888;display:flex;justify-content:space-between;align-items:center">
      <span id="zipStatusText">⏳ Đang xử lý trên máy chủ VPS...</span>
      <button type="button" onclick="closeZipModal()" class="btn btn-outline-navy btn-sm" style="padding:4px 12px;font-size:12px">Đóng</button>
    </div>
  </div>
</div>

<style>
@keyframes zipPulse {
  0% { opacity: 0.4; transform: scaleX(0.2); transform-origin: left; }
  50% { opacity: 1; transform: scaleX(0.85); transform-origin: left; }
  100% { opacity: 0.4; transform: scaleX(1); transform-origin: left; }
}
</style>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>

<script>
function toggleExportMenu() {
  var menu = document.getElementById('exportMenu');
  menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}
function exportSelected() {
  var checked = document.querySelectorAll('.row-check:checked');
  if (checked.length === 0) { alert('Vui lòng chọn ít nhất 1 sản phẩm để xuất'); return; }
  var ids = Array.from(checked).map(function(c){ return c.value; }).join(',');
  document.getElementById('exportMenu').style.display='none'; csColPick({section:'products',url:'/admin/products/export-csv',title:'SP đã chọn ('+checked.length+')',extra:{ids:ids}});
}

function triggerImageExport(mode) {
  document.getElementById('exportMenu').style.display = 'none';
  
  var url = '/admin/products/export-images';
  var descText = 'Hệ thống đang mở luồng tải trực tiếp gói hình ảnh đầy đủ (~840 MB). Vui lòng đợi trình duyệt tải xong 100% trước khi mở giải nén!';
  
  if (mode === 'selected') {
    var checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
      alert('Vui lòng chọn ít nhất 1 sản phẩm để xuất ảnh.');
      return;
    }
    var ids = Array.from(checked).map(function(c) { return c.value; }).join(',');
    url += '?ids=' + ids;
    descText = 'Hệ thống đang đóng gói hình ảnh của ' + checked.length + ' sản phẩm đã chọn...';
  }
  
  document.getElementById('zipModalDesc').textContent = descText;
  document.getElementById('zipStatusText').textContent = '⏳ Đang khởi tạo luồng tải tệp ZIP từ máy chủ VPS...';
  document.getElementById('zipProgressModal').style.display = 'flex';
  
  if (mode === 'all') {
    window.location.href = url;
  } else {
    var iframe = document.getElementById('hiddenDownloadIframe');
    if (!iframe) {
      iframe = document.createElement('iframe');
      iframe.id = 'hiddenDownloadIframe';
      iframe.style.display = 'none';
      document.body.appendChild(iframe);
    }
    iframe.src = url;
  }
  
  setTimeout(function() {
    document.getElementById('zipStatusText').textContent = '✅ Đang tải tệp ZIP về máy! Vui lòng kiểm tra thanh tải về của trình duyệt và đợi hoàn tất 100% trước khi giải nén.';
    setTimeout(function() {
      closeZipModal();
    }, 4500);
  }, 2000);
}

function closeZipModal() {
  document.getElementById('zipProgressModal').style.display = 'none';
}
document.addEventListener('click', function(e) {
  var dd = document.getElementById('exportDropdown');
  if (dd && !dd.contains(e.target)) {
    document.getElementById('exportMenu').style.display = 'none';
  }
});

function toggleAllCheckboxes(master) {
  document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = master.checked; });
  updateBulkBtn();
}

function updateBulkBtn() {
  var checked = document.querySelectorAll('.row-check:checked');
  var btn = document.getElementById('bulkDeleteBtn');
  var count = document.getElementById('bulkCount');
  count.textContent = checked.length;
  btn.style.display = checked.length > 0 ? 'inline-flex' : 'none';
}

document.addEventListener('change', function(e) {
  if (e.target.classList.contains('row-check') || e.target.id === 'checkAll') {
    updateBulkBtn();
  }
});

function bulkDelete() {
  var checked = document.querySelectorAll('.row-check:checked');
  if (checked.length === 0) return;
  showBulkDeleteModal(checked.length);
}
function doBulkDelete() {
  var checked = document.querySelectorAll('.row-check:checked');
  if (checked.length === 0) return;
  
  var ids = Array.from(checked).map(function(c){ return c.value; });
  var csrfEl = document.querySelector('input[name="_csrf"]');
  var csrf = csrfEl ? csrfEl.value : '<?= $_SESSION["csrf_token"] ?? "" ?>';
  
  fetch('/admin/products/bulk-delete', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf=' + encodeURIComponent(csrf) + '&ids=' + encodeURIComponent(ids.join(','))
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      alert('Đã xóa thành công ' + data.deleted + ' sản phẩm.');
      var targetUrl = location.pathname + location.search;
      var currentScroll = window.scrollY || window.pageYOffset || 0;
      if (window.csNav) {
        csNav(targetUrl);
        setTimeout(function() {
          window.scrollTo(0, currentScroll);
        }, 150);
      } else {
        window.location.href = targetUrl;
      }
    } else {
      alert('Lỗi: ' + (data.msg || 'Không thể xóa'));
    }
  })
  .catch(function(err) {
    alert('Lỗi kết nối: ' + err.message);
  });
}
</script>

<!-- Custom Delete Modal -->
<div id="customDeleteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;padding:24px;width:400px;max-width:90%;box-shadow:0 4px 12px rgba(0,0,0,0.15)">
    <h3 style="margin:0 0 16px 0;font-size:18px;color:#dc2626;display:flex;align-items:center;gap:8px">
      <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      Yêu cầu bảo mật
    </h3>
    <p id="deleteModalDesc" style="margin:0 0 16px 0;font-size:14px;color:#4b5563">Hành động xóa sản phẩm không thể hoàn tác. Vui lòng nhập mật khẩu xác nhận để tiếp tục:</p>
    <input type="password" id="deleteModalPassword" placeholder="Nhập mật khẩu..." style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;margin-bottom:8px;font-size:14px;outline:none" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
    <p id="deleteModalError" style="margin:0 0 16px 0;color:#dc2626;font-size:13px;display:none">Mật khẩu không chính xác!</p>
    <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px">
      <button type="button" onclick="closeDeleteModal()" style="padding:8px 16px;border:none;background:#f3f4f6;color:#374151;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px">Hủy</button>
      <button type="button" onclick="submitDeleteModal()" style="padding:8px 16px;border:none;background:#dc2626;color:#fff;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px">Xóa sản phẩm</button>
    </div>
  </div>
</div>
<script>
let currentDeleteId = null;
let deleteMode = 'single';
function showDeleteModal(id) {
  deleteMode = 'single';
  currentDeleteId = id;
  document.getElementById('deleteModalDesc').textContent = 'Hành động xóa sản phẩm không thể hoàn tác. Vui lòng nhập mật khẩu xác nhận để tiếp tục:';
  openDeleteModal();
}
function showBulkDeleteModal(count) {
  deleteMode = 'bulk';
  currentDeleteId = null;
  document.getElementById('deleteModalDesc').textContent = 'Hành động xóa ' + count + ' sản phẩm đã chọn không thể hoàn tác. Vui lòng nhập mật khẩu xác nhận để tiếp tục:';
  openDeleteModal();
}
function openDeleteModal() {
  document.getElementById('deleteModalPassword').value = '';
  document.getElementById('deleteModalError').style.display = 'none';
  const modal = document.getElementById('customDeleteModal');
  modal.style.display = 'flex';
  setTimeout(() => document.getElementById('deleteModalPassword').focus(), 100);
}
function closeDeleteModal() {
  document.getElementById('customDeleteModal').style.display = 'none';
  currentDeleteId = null;
}
function submitDeleteModal() {
  const pass = document.getElementById('deleteModalPassword').value;
  if (pass === 'miken') {
    if (deleteMode === 'bulk') {
      closeDeleteModal();
      doBulkDelete();
    } else if (currentDeleteId) {
      const targetId = currentDeleteId;
      closeDeleteModal();
      const form = document.getElementById('deleteForm-' + targetId);
      if (form) form.submit();
    }
  } else {
    document.getElementById('deleteModalError').style.display = 'block';
  }
}
document.getElementById('deleteModalPassword').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') submitDeleteModal();
});
</script>
