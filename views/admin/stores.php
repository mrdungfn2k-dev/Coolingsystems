<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <h1 style="margin:0">Quản lý Hệ thống cửa hàng</h1>
    <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;padding:6px 14px;border-radius:20px;border:1.5px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
      <span style="font-size:13px;font-weight:600;color:#334155">Hiển thị trên Menu:</span>
      <button type="button" id="btnToggleNavVis" class="btn btn-sm <?= ($navVisible ?? true) ? 'btn-navy' : 'btn-outline-secondary' ?>" data-status="<?= ($navVisible ?? true) ? 1 : 0 ?>" onclick="toggleStoreNavVis(this)" style="min-width:100px;font-weight:700;border-radius:14px;transition:all 0.2s">
        <?= ($navVisible ?? true) ? '✓ Hiển thị' : '✕ Đã ẩn' ?>
      </button>
    </div>
  </div>
  <button class="btn btn-navy" onclick="document.getElementById('addModal').style.display='flex'">+ Thêm cửa hàng</button>
</div>

<?php $flashMsgs = getFlash(); foreach($flashMsgs as $fm): ?>
  <div class="flash flash-<?= e($fm['type']) ?>"><?= e($fm['message']) ?></div>
<?php endforeach; ?>

<div class="panel" style="padding:0;overflow:hidden">
<table class="tbl" style="width:100%;font-size:13px">
  <thead>
    <tr>
      <th style="width:40px">#</th>
      <th>Tên cửa hàng</th>
      <th>Loại</th>
      <th>Địa chỉ</th>
      <th>SĐT</th>
      <th>Giờ mở cửa</th>
      <th style="width:60px">TT</th>
      <th style="width:140px">Thao tác</th>
    </tr>
  </thead>
  <tbody>
  <?php $typeMap=[]; foreach(($branchTypes??[]) as $bt){ $typeMap[$bt['code']]=$bt['name']; } if(empty($typeMap)) $typeMap=['chi_nhanh'=>'Chi nhánh','dai_ly'=>'Đại lý','bao_hanh'=>'Trạm BH']; ?>
  <?php foreach($stores as $s): ?>
    <tr>
      <td><?= $s['id'] ?></td>
      <td style="font-weight:700"><?= e($s['name']) ?></td>
      <td><span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:#e3f2fd;color:#1565c0"><?= $typeMap[$s['type']]??$s['type'] ?></span></td>
      <td style="font-size:12px"><?= e($s['address']??'') ?></td>
      <td style="font-size:12px"><?= e($s['phone']??'') ?></td>
      <td style="font-size:12px"><?= e($s['hours']??'') ?></td>
      <td><?= $s['is_active']?'<span style="color:#059669;font-weight:700">ON</span>':'<span style="color:#999">OFF</span>' ?></td>
      <td>
        <button class="adm-edit" onclick="editStore(<?= htmlspecialchars(json_encode($s),ENT_QUOTES) ?>)">Sửa</button>
        <form method="post" action="/admin/stores/<?= $s['id'] ?>/delete" style="display:inline" onsubmit="return csConfirmForm(this,'Xóa cửa hàng này?')">
          <?= csrfField() ?>
          <button type="submit" class="adm-del">Xóa</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if(empty($stores)): ?>
    <tr><td colspan="8" style="text-align:center;padding:30px;color:#888">Chưa có cửa hàng nào</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:24px;max-width:560px;width:calc(100% - 32px);max-height:90vh;overflow-y:auto">
    <h3 style="margin-bottom:16px;color:var(--navy)">Thêm cửa hàng mới</h3>
    <form method="post" action="/admin/stores/add">
      <?= csrfField() ?>
      <div class="form-group mb-3">
        <label>Tên cửa hàng *</label>
        <input type="text" name="name" required maxlength="50" class="form-control" placeholder="VD: CoolingSystem - Chi nhánh Đà Nẵng">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="mb-3">
        <div class="form-group">
          <label>Loại</label>
          <select name="type" class="form-control">
            <?php foreach(($branchTypes??[]) as $bt): ?><option value="<?= e($bt['code']) ?>"><?= e($bt['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>SĐT</label>
          <input type="tel" name="phone" class="form-control" placeholder="VD: 0987654321" pattern="0[1-9][0-9]{8}" maxlength="10" title="SĐT phải gồm 10 chữ số, bắt đầu từ 01 đến 09" oninput="let v=this.value.replace(/[^0-9]/g,''); if(v.length>0 && v[0]!=='0') v=''; if(v.length>1 && v[1]==='0') v='0'; this.value=v;">
        </div>
      </div>
      <div class="form-group mb-3">
        <label>Địa chỉ</label>
        <input type="text" name="address" id="addAddress" maxlength="50" class="form-control" placeholder="Số nhà, đường, quận, TP">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="mb-3">
        <div class="form-group">
          <label>Giờ mở cửa</label>
          <input type="text" name="hours" maxlength="50" class="form-control" value="8:00 - 18:00">
        </div>
        <div class="form-group">
          <label>Thứ tự</label>
          <input type="number" name="sort_order" class="form-control" value="0">
        </div>
      </div>
      <div class="form-group mb-3">
        <label>Tìm vị trí trên bản đồ</label>
        <div style="display:flex;gap:8px">
          <input type="text" id="addGeoAddress" maxlength="50" class="form-control" placeholder="Nhập địa chỉ rồi bấm Tìm..." style="flex:1">
          <button type="button" onclick="geocodeAddress('add')" class="btn btn-navy" style="white-space:nowrap">Tìm</button>
        </div>
        <input type="hidden" name="lat" id="addLat">
        <input type="hidden" name="lng" id="addLng">
        <div id="addMapResult" style="font-size:12px;color:#059669;margin-top:6px"></div>
        <iframe id="addMap" src="https://maps.google.com/maps?q=Vietnam&z=5&output=embed" style="width:100%;height:240px;border:1px solid #d6deea;border-radius:8px;margin-top:10px" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <div style="display:flex;align-items:center;gap:10px;margin:12px 0">
        <input type="checkbox" name="is_active" id="addActive" checked style="width:18px;height:18px;cursor:pointer">
        <label for="addActive" style="cursor:pointer;font-size:13px;font-weight:600;margin:0">Hiển thị trên trang</label>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="this.closest('[id=addModal]').style.display='none'" class="btn btn-outline-navy">Hủy</button>
        <button type="submit" class="btn btn-navy">Thêm cửa hàng</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:12px;padding:24px;max-width:560px;width:calc(100% - 32px);max-height:90vh;overflow-y:auto">
    <h3 style="margin-bottom:16px;color:var(--navy)">Sửa cửa hàng</h3>
    <form method="post" id="editForm">
      <?= csrfField() ?>
      <div class="form-group mb-3">
        <label>Tên cửa hàng *</label>
        <input type="text" name="name" id="eName" required maxlength="50" class="form-control">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="mb-3">
        <div class="form-group">
          <label>Loại</label>
          <select name="type" id="eType" class="form-control">
            <?php foreach(($branchTypes??[]) as $bt): ?><option value="<?= e($bt['code']) ?>"><?= e($bt['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>SĐT</label>
          <input type="tel" name="phone" id="ePhone" class="form-control" placeholder="VD: 0987654321" pattern="0[1-9][0-9]{8}" maxlength="10" title="SĐT phải gồm 10 chữ số, bắt đầu từ 01 đến 09" oninput="let v=this.value.replace(/[^0-9]/g,''); if(v.length>0 && v[0]!=='0') v=''; if(v.length>1 && v[1]==='0') v='0'; this.value=v;">
        </div>
      </div>
      <div class="form-group mb-3">
        <label>Địa chỉ</label>
        <input type="text" name="address" id="eAddress" maxlength="50" class="form-control">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="mb-3">
        <div class="form-group">
          <label>Giờ mở cửa</label>
          <input type="text" name="hours" id="eHours" maxlength="50" class="form-control">
        </div>
        <div class="form-group">
          <label>Thứ tự</label>
          <input type="number" name="sort_order" id="eSort" class="form-control">
        </div>
      </div>
      <div class="form-group mb-3">
        <label>Tìm vị trí trên bản đồ</label>
        <div style="display:flex;gap:8px">
          <input type="text" id="editGeoAddress" maxlength="50" class="form-control" placeholder="Nhập địa chỉ rồi bấm Tìm..." style="flex:1">
          <button type="button" onclick="geocodeAddress('edit')" class="btn btn-navy" style="white-space:nowrap">Tìm</button>
        </div>
        <input type="hidden" name="lat" id="eLat">
        <input type="hidden" name="lng" id="eLng">
        <div id="editMapResult" style="font-size:12px;color:#059669;margin-top:6px"></div>
        <iframe id="editMap" src="https://maps.google.com/maps?q=Vietnam&z=5&output=embed" style="width:100%;height:240px;border:1px solid #d6deea;border-radius:8px;margin-top:10px" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      <div style="display:flex;align-items:center;gap:10px;margin:12px 0">
        <input type="checkbox" name="is_active" id="eActive" style="width:18px;height:18px;cursor:pointer">
        <label for="eActive" style="cursor:pointer;font-size:13px;font-weight:600;margin:0">Hiển thị trên trang</label>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-outline-navy">Hủy</button>
        <button type="submit" class="btn btn-navy">Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

<script>
function editStore(s) {
  document.getElementById('editForm').action = '/admin/stores/' + s.id + '/edit';
  document.getElementById('eName').value = s.name || '';
  document.getElementById('eType').value = s.type || 'chi_nhanh';
  document.getElementById('ePhone').value = s.phone || '';
  document.getElementById('eAddress').value = s.address || '';
  document.getElementById('eHours').value = s.hours || '';
  document.getElementById('eSort').value = s.sort_order || 0;
  document.getElementById('eLat').value = s.lat || '';
  document.getElementById('editGeoAddress').value = s.address || '';
  if(s.lat && s.lng) document.getElementById('editMapResult').textContent = 'Vị trí hiện tại: ' + (s.lat ? parseFloat(s.lat).toFixed(4) : '') + ', ' + (s.lng ? parseFloat(s.lng).toFixed(4) : '');
  var _em=document.getElementById('editMap'); if(_em) _em.src=(s.lat&&s.lng)?('https://maps.google.com/maps?q='+s.lat+','+s.lng+'&z=16&output=embed'):'https://maps.google.com/maps?q=Vietnam&z=5&output=embed';
  document.getElementById('eLng').value = s.lng || '';
  document.getElementById('eActive').checked = !!s.is_active;
  document.getElementById('editModal').style.display = 'flex';
}
function geocodeAddress(mode) {
  var input = mode === 'add' ? document.getElementById('addGeoAddress') : document.getElementById('editGeoAddress');
  var addr = input.value.trim();
  if (!addr) { alert('Vui lòng nhập địa chỉ'); return; }
  var resultEl = document.getElementById(mode === 'add' ? 'addMapResult' : 'editMapResult');
  var latEl = document.getElementById(mode === 'add' ? 'addLat' : 'eLat');
  var lngEl = document.getElementById(mode === 'add' ? 'addLng' : 'eLng');
  resultEl.textContent = 'Đang tìm...';
  resultEl.style.color = '#666';
  fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(addr) + '&limit=1&countrycodes=vn')
    .then(function(r){return r.json()})
    .then(function(data){
      if(data.length > 0) {
        latEl.value = data[0].lat;
        lngEl.value = data[0].lon;
        // Auto-update address field with the user's typed address
        var addrField = document.getElementById(mode === 'add' ? 'addAddress' : 'eAddress');
        if (addrField) { addrField.value = addr; }
        resultEl.textContent = 'Tìm thấy: ' + data[0].display_name.substring(0,80) + '... (' + parseFloat(data[0].lat).toFixed(4) + ', ' + parseFloat(data[0].lon).toFixed(4) + ')';
        var _mp=document.getElementById(mode === 'add' ? 'addMap' : 'editMap'); if(_mp) _mp.src='https://maps.google.com/maps?q='+data[0].lat+','+data[0].lon+'&z=16&output=embed';
        resultEl.style.color = '#059669';
      } else {
        resultEl.textContent = 'Không tìm thấy vị trí. Hãy thử nhập chi tiết hơn.';
        resultEl.style.color = '#dc2626';
      }
    })
    .catch(function(){
      resultEl.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
      resultEl.style.color = '#dc2626';
    });
}
document.getElementById('addModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none'});
document.getElementById('editModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none'});

function toggleStoreNavVis(btn) {
  var curStatus = parseInt(btn.getAttribute('data-status') || '1');
  var newStatus = curStatus === 1 ? 0 : 1;
  btn.disabled = true;
  var origText = btn.innerHTML;
  btn.textContent = 'Đang lưu...';

  var formData = new FormData();
  formData.append('slug', 'he-thong-cua-hang');
  formData.append('status', newStatus);

  fetch('/admin/content/toggle-visibility', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    btn.disabled = false;
    if (res && res.ok) {
      btn.setAttribute('data-status', newStatus);
      if (newStatus === 1) {
        btn.className = 'btn btn-sm btn-navy';
        btn.innerHTML = '✓ Hiển thị';
      } else {
        btn.className = 'btn btn-sm btn-outline-secondary';
        btn.innerHTML = '✕ Đã ẩn';
      }
      if (typeof csToast === 'function') {
        csToast(newStatus === 1 ? 'Đã hiện "Hệ thống cửa hàng" trên thanh menu' : 'Đã ẩn "Hệ thống cửa hàng" khỏi thanh menu', 'success');
      } else {
        alert(newStatus === 1 ? 'Đã hiện "Hệ thống cửa hàng" trên thanh menu' : 'Đã ẩn "Hệ thống cửa hàng" khỏi thanh menu');
      }
    } else {
      alert('Không thể cập nhật trạng thái hiển thị.');
      btn.innerHTML = origText;
    }
  })
  .catch(function(err) {
    btn.disabled = false;
    alert('Lỗi kết nối máy chủ.');
    btn.innerHTML = origText;
  });
}
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
