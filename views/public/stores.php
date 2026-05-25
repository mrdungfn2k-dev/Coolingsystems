<?php require __DIR__.'/../partials/head.php'; ?>
<?php
$stores = dbAll("SELECT * FROM stores WHERE is_active=1 ORDER BY sort_order, id");
$typeMap = ['chi_nhanh'=>'CHI NHÁNH CHÍNH','dai_ly'=>'ĐẠI LÝ ỦY QUYỀN','bao_hanh'=>'TRẠM BẢO HÀNH'];
$typeColors = ['chi_nhanh'=>'#1a3258','dai_ly'=>'#c8a55a','bao_hanh'=>'#d97706'];
$activeStore = $stores[0] ?? null;
?>
<style>
.store-hero{background:linear-gradient(135deg,#0f1724,#1a3258);color:#fff;padding:40px 0;text-align:center}
.store-hero h1{font-size:28px;font-weight:900;margin:0 0 8px 0}
.store-hero p{opacity:0.7;font-size:14px;margin:0}
.store-tabs{display:flex;gap:8px;padding:16px 0;flex-wrap:wrap}
.store-tab{padding:8px 16px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #ddd;background:#fff;color:#555;transition:all 0.2s}
.store-tab.active,.store-tab:hover{background:var(--navy);color:#fff;border-color:var(--navy)}
.store-layout{display:grid;grid-template-columns:320px 1fr;gap:20px;min-height:500px}
.store-list{display:flex;flex-direction:column;gap:10px;max-height:600px;overflow-y:auto;padding-right:8px}
.store-card{border:2px solid #eee;border-radius:12px;padding:14px;cursor:pointer;transition:all 0.2s;background:#fff}
.store-card:hover,.store-card.active{border-color:var(--navy);box-shadow:0 4px 16px rgba(26,50,88,0.12)}
.store-card .type-badge{font-size:9px;font-weight:800;letter-spacing:1px;padding:2px 8px;border-radius:4px;display:inline-block;margin-bottom:6px}
.store-card .name{font-weight:700;font-size:14px;color:#222;margin-bottom:4px}
.store-card .addr{font-size:12px;color:#666;margin-bottom:6px}
.store-card .meta{display:flex;justify-content:space-between;font-size:11px;color:#888}
.store-card .meta .phone{color:var(--navy);font-weight:600}
.store-map-panel{border-radius:12px;overflow:hidden;display:flex;flex-direction:column;background:#fff;border:1px solid #eee}
.store-map{flex:1;min-height:350px;background:#f0f4ff;display:flex;align-items:center;justify-content:center;position:relative}
.store-map iframe{width:100%;height:100%;border:0}
.store-map .no-map{text-align:center;color:#aaa;padding:40px}
.store-map .no-map svg{width:64px;height:64px;opacity:0.3;margin-bottom:12px}
.store-info-bar{padding:16px 20px;border-top:1px solid #eee;display:flex;align-items:center;justify-content:space-between;gap:12px}
.store-info-bar .info .sname{font-weight:700;font-size:15px;color:#222}
.store-info-bar .info .saddr{font-size:12px;color:#888}
.store-info-bar .actions{display:flex;gap:8px}
.btn-hotline{background:#dc2626;color:#fff;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.btn-direction{background:var(--navy);color:#fff;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:700;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
@media (max-width: 768px) {
  .store-layout {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
    min-height: auto !important;
  }
  .store-list {
    max-height: 250px;
    order: 1;
  }
  .store-map-panel {
    order: 2;
    min-height: 300px;
  }
  .store-map {
    min-height: 280px !important;
  }
  .store-info-panel {
    flex-direction: column !important;
    order: 3;
  }
  .store-info-panel .info-btns { 
    display: flex !important; 
    flex-direction: row !important; 
    gap: 6px !important;
    width: 100% !important;
    margin-top: 10px;
    flex-wrap: nowrap !important;
  }
  .btn-hotline, .btn-direction {
    flex: 1 1 0 !important;
    min-width: 0 !important;
    text-align: center !important;
    justify-content: center !important;
    white-space: nowrap !important;
    font-size: 11px !important;
    padding: 8px 6px !important;
    box-sizing: border-box !important;
  }
  .store-hero h1 { font-size: 20px !important; }
  .store-tabs { justify-content: center; }
}
</style>

<div class="store-hero">
  <h1>Hệ Thống Đại Lý & Chi Nhánh</h1>
  <p>Tìm kiếm trung tâm dịch vụ và đại lý ủy quyền CoolingSystem gần bạn nhất.</p>
</div>

<div class="container" style="max-width:1200px;margin:0 auto;padding:0 20px">
  <div class="store-tabs">
    <div class="store-tab active" onclick="filterStores('all',this)">TẤT CẢ</div>
    <div class="store-tab" onclick="filterStores('chi_nhanh',this)">CHI NHÁNH CHÍNH</div>
    <div class="store-tab" onclick="filterStores('dai_ly',this)">ĐẠI LÝ ỦY QUYỀN</div>
    <div class="store-tab" onclick="filterStores('bao_hanh',this)">TRẠM BẢO HÀNH</div>
  </div>

  <div class="store-layout">
    <div class="store-list" id="storeList">
      <?php foreach($stores as $i => $s): ?>
      <div class="store-card <?= $i===0?'active':'' ?>" data-type="<?= e($s['type']) ?>"
           data-lat="<?= $s['lat'] ?>" data-lng="<?= $s['lng'] ?>"
           data-name="<?= e($s['name']) ?>" data-addr="<?= e($s['address']) ?>"
           data-phone="<?= e($s['phone']) ?>"
           onclick="selectStore(this)">
        <div class="type-badge" style="background:<?= $typeColors[$s['type']]??'#888' ?>20;color:<?= $typeColors[$s['type']]??'#888' ?>"><?= $typeMap[$s['type']]??'KHÁC' ?></div>
        <div class="name"><?= e($s['name']) ?></div>
        <div class="addr"> <?= e($s['address']) ?></div>
        <div class="meta">
          <span class="phone"> <?= e($s['phone']) ?></span>
          <span> <?= e($s['hours']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($stores)): ?>
      <div style="text-align:center;padding:40px;color:#aaa">Chưa có cửa hàng nào</div>
      <?php endif; ?>
    </div>

    <div class="store-map-panel">
      <div class="store-map" id="storeMap">
        <?php if($activeStore && $activeStore['lat']): ?>
        <iframe src="https://maps.google.com/maps?q=<?= $activeStore['lat'] ?>,<?= $activeStore['lng'] ?>&z=15&output=embed" allowfullscreen></iframe>
        <?php else: ?>
        <div class="no-map">
          <div style="font-size:48px;opacity:0.3;margin-bottom:8px"></div>
          <div>Chưa có thông tin bản đồ cho chi nhánh này.</div>
        </div>
        <?php endif; ?>
      </div>
      <div class="store-info-bar" id="storeInfoBar">
        <div class="info">
          <div class="sname" id="infoName"><?= e($activeStore['name']??'') ?></div>
          <div class="saddr" id="infoAddr"><?= e($activeStore['address']??'') ?></div>
        </div>
        <div class="actions">
          <a href="tel:<?= e($activeStore['phone']??'') ?>" class="btn-hotline" id="infoPhone">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.18 2 2 0 015.06 6h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L9.91 13.09a16 16 0 006 6l.41-.41a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 20.9z"/></svg>
            <span id="infoPhoneNum"><?= e($activeStore['phone']??'') ?></span>
          </a>
          <a href="https://www.google.com/maps?q=<?= ($activeStore['lat']??0) ?>,<?= ($activeStore['lng']??0) ?>" target="_blank" class="btn-direction" id="infoDir"> CHỈ ĐƯỜNG</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function selectStore(el) {
  document.querySelectorAll('.store-card').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  var lat = el.dataset.lat, lng = el.dataset.lng;
  var mapDiv = document.getElementById('storeMap');
  if (lat && lng && lat !== '0' && lat !== 'None') {
    mapDiv.innerHTML = '<iframe src="https://maps.google.com/maps?q='+lat+','+lng+'&z=15&output=embed" allowfullscreen style="width:100%;height:100%;border:0"></iframe>';
  } else {
    mapDiv.innerHTML = '<div class="no-map"><div style="font-size:48px;opacity:0.3;margin-bottom:8px"></div><div>Chưa có thông tin bản đồ</div></div>';
  }
  document.getElementById('infoName').textContent = el.dataset.name;
  document.getElementById('infoAddr').textContent = el.dataset.addr;
  document.getElementById('infoPhone').href = 'tel:' + el.dataset.phone;
  var numSpan = document.getElementById('infoPhoneNum');
  if (numSpan) numSpan.textContent = el.dataset.phone || 'GỌI HOTLINE';
  document.getElementById('infoDir').href = 'https://www.google.com/maps?q=' + lat + ',' + lng;
}
function filterStores(type, tab) {
  document.querySelectorAll('.store-tab').forEach(t => t.classList.remove('active'));
  tab.classList.add('active');
  document.querySelectorAll('.store-card').forEach(function(c) {
    c.style.display = (type === 'all' || c.dataset.type === type) ? '' : 'none';
  });
}
</script>
<?php require __DIR__.'/../partials/foot.php'; ?>
