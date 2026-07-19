<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<?php $listRole = $listRole ?? 'customer'; $listRoute = $listRoute ?? '/admin/users'; ?>
<div class="dash-head">
  <h1><?= ($listRole??'')==='staff' ? 'Nhân viên' : (($listRole??'')==='admin' ? 'Quản trị viên' : 'Khách hàng') ?></h1>
  <div style="display:flex;gap:8px;align-items:center">
    <?php if(in_array(($listRole??'customer'), ['customer','staff'])): ?>
    <a href="#" onclick="csColPick({section:'users',url:'/admin/users/export-csv',title:'<?= ($listRole??'')==='staff'?'Nhân viên':(($listRole??'')==='admin'?'Quản trị viên':'Khách hàng') ?>',extra:{role:'<?= e($listRole??'customer') ?>'}});return false" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button type="button" onclick="document.getElementById('csvImportUsers').style.display='flex'" class="btn btn-outline-navy btn-sm">↑ Nhập CSV</button>
    <?php endif; ?>
    <a href="/admin/users/new?role=<?= e($listRole??'customer') ?>" class="btn btn-navy btn-sm">+ <?= ($listRole??'')==='staff' ? 'Thêm nhân viên' : (($listRole??'')==='admin' ? 'Thêm quản trị viên' : 'Thêm tài khoản') ?></a>
  </div>
</div>
<style>
.uf-filter { align-items:flex-end !important; }
.uf-filter .frm-input, .uf-filter .cs-sel-trg, .uf-filter .btn { height:42px !important; min-height:42px !important; box-sizing:border-box !important; }
.uf-filter .btn { display:inline-flex !important; align-items:center !important; }
</style>
<form method="get" action="<?= e($listRoute) ?>" class="uf-filter" style="background:#fff;padding:14px 16px;border-radius:8px;border:1px solid var(--line);margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
  <div style="flex:2;min-width:200px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">TÌM KIẾM</label>
    <input type="text" name="q" class="frm-input" style="border-radius:6px;width:100%" value="<?= e($_GET['q']??'') ?>" placeholder="Email, họ tên, SĐT...">
  </div>
  <div style="flex:1;min-width:140px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">TRẠNG THÁI</label>
    <select name="status" class="frm-input js-cdd" style="border-radius:6px;width:100%">
      <option value="">Tất cả</option>
      <option value="active" <?= ($_GET['status']??'')==='active'?'selected':'' ?>>Hoạt động</option>
      <option value="suspended" <?= ($_GET['status']??'')==='suspended'?'selected':'' ?>>Đang ngưng</option>
    </select>
  </div>
  <div style="flex:1;min-width:140px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">TỪ NGÀY</label>
    <input type="date" name="from" class="frm-input js-date" style="border-radius:6px;width:100%" value="<?= e($_GET['from']??'') ?>">
  </div>
  <div style="flex:1;min-width:140px">
    <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">ĐẾN NGÀY</label>
    <input type="date" name="to" class="frm-input js-date" style="border-radius:6px;width:100%" value="<?= e($_GET['to']??'') ?>">
  </div>
  <button type="submit" class="btn btn-navy btn-sm" style="border-radius:6px">Lọc</button>
  <a href="<?= e($listRoute) ?>" class="btn btn-outline-navy btn-sm" style="border-radius:6px">Đặt lại</a>
</form>
<div class="panel">
  <div style="padding:12px 16px;border-bottom:1px solid var(--line);font-size:13px;color:#888;display:flex;justify-content:space-between;align-items:center">
    <span>Hiển thị <?= count($users) ?>/<?= $total ?> tài khoản (trang <?= $page ?>/<?= $totalPages ?>)</span>
    <div>
      <button id="bulkDeleteBtn" onclick="bulkDeleteUsers()" class="btn btn-sm" style="background:#dc2626;color:#fff;border:none;border-radius:4px;font-weight:600;padding:6px 14px;display:none;cursor:pointer;text-transform:none;letter-spacing:.01em">Xóa đã chọn</button>
    </div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th><input type="checkbox" id="checkAll" onclick="toggleAllUsers(this)" style="cursor:pointer"></th><th>#</th><th>Người dùng</th><th>Email</th><th>SĐT</th><th>Địa chỉ</th><th>Trạng thái</th><th>Ngày đăng ký</th><th style="width:190px">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php 
    $stt = ($page - 1) * 20;
    foreach ($users as $u):
      $stt++;
      $isSuspended = $u['status'] !== 'active' || (!empty($u['suspended_until']) && strtotime($u['suspended_until']) > time());
      $suspendedUntil = !empty($u['suspended_until']) && strtotime($u['suspended_until']) > time() ? date('d/m/Y', strtotime($u['suspended_until'])) : null;
    ?>
    <tr id="u<?= $u['id'] ?>" style="<?= $isSuspended ? 'background:#fff5f5' : '' ?>">
      <td><input type="checkbox" class="row-check" value="<?=$u['id']?>"></td>
      <td class="fs-12 text-muted"><?= $stt ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:50%;background:#f0f0f0;overflow:hidden;flex-shrink:0">
            <?php if (!empty($u['avatar'])): ?>
              <img src="/uploads/avatars/<?= e($u['avatar']) ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <img src="/assets/images/default-avatar.png" style="width:100%;height:100%;object-fit:cover" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2NjYyI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00czEuNzktNCA0LTRtMCAxMGMtMi42NyAwLTggMS4zNC04IDR2MmgxNnYtMmMwLTIuNjYtNS4zMy00LTgtNHoiLz48L3N2Zz4='">
            <?php endif; ?>
          </div>
          <div style="font-weight:600"><?= e($u['full_name']) ?></div>
        </div>
      </td>
      <td class="fs-12"><?= e($u['email']) ?></td>
      <td class="fs-12"><?= e($u['phone']??'—') ?></td>
      <td class="fs-11" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($u['address']??'') ?>"><?= e(mb_substr($u['address']??'—',0,28)) ?></td>
      <td>
        <?php if ($isSuspended): ?>
          <span style="background:#fde8e8;color:#c0392b;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700">Ngưng<?= $suspendedUntil ? ' đến '.$suspendedUntil : '' ?></span>
        <?php else: ?>
          <span style="background:#e8f5e9;color:#27ae60;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700">Hoạt động</span>
        <?php endif; ?>
      </td>
      <td class="fs-12"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
      <td style="padding:10px 12px;white-space:nowrap">
        <div style="display:flex;flex-wrap:nowrap;gap:5px;align-items:center">
          <a href="/admin/users/<?= $u['id'] ?>/edit" class="btn btn-sm" style="background:#1a3258;color:#fff;text-align:center;padding:6px 10px;border-radius:5px;font-weight:600;text-transform:none;letter-spacing:.01em">Sửa</a>

          <button class="btn btn-sm" style="background:#fff;color:#1a3258;border:1.5px solid #1a3258;padding:6px 10px;border-radius:5px;font-weight:600;text-transform:none;letter-spacing:.01em" onclick="showUserDetail(<?= $u['id'] ?>)">Chi tiết</button>
          <?php if ($isSuspended): ?>
            <form method="post" action="/admin/users/<?= $u['id'] ?>/unlock" style="display:block"><?= csrfField() ?><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? $listRoute) ?>">
              <button class="btn btn-sm" style="background:#fff;color:#1a3258;border:1.5px solid #1a3258;padding:6px 10px;border-radius:5px;font-weight:600;text-transform:none;letter-spacing:.01em">Mở</button></form>
          <?php else: ?>
            <button class="btn btn-sm" style="background:#fff;color:#1a3258;border:1.5px solid #1a3258;padding:6px 10px;border-radius:5px;font-weight:600;text-transform:none;letter-spacing:.01em" onclick="showSuspendModal(<?= $u['id'] ?>,'<?= e($u['full_name']) ?>')">Ngưng</button>
          <?php endif; ?>
        <?php if(in_array(($listRole??''), ['staff','admin'])): ?>
        <button type="button" class="btn btn-sm" style="background:#fff;color:#1a3258;border:1.5px solid #1a3258;padding:6px 10px;border-radius:5px;font-weight:600;text-transform:none;letter-spacing:.01em" onclick="showResetPwModal(<?= $u['id'] ?>,'<?= e($u['full_name']) ?>')">Cấp lại MK</button>
        <?php endif; ?>
        <form method="post" action="/admin/users/bulk-delete" style="margin:0" onsubmit="return csConfirmForm(this,'Xóa vĩnh viễn tài khoản này? Hành động này không thể hoàn tác.')">
          <?= csrfField() ?>
          <input type="hidden" name="ids[]" value="<?= $u['id'] ?>">
          <input type="hidden" name="back" value="<?= e($listRoute ?? '/admin/users') ?>">
          <button type="submit" title="Xóa tài khoản" class="btn btn-sm" style="background:#fff;color:#6b7280;border:1.5px solid #d1d5db;padding:6px 9px;border-radius:5px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
        </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="9" class="text-center text-muted" style="padding:24px">Không tìm thấy tài khoản nào</td></tr><?php endif; ?>
    </tbody>
  </table>
  <?php if ($totalPages > 1): ?>
  <div style="padding:12px 16px;display:flex;gap:6px;justify-content:center">
    <?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, $listRoute, ['q' => $_GET['q'] ?? '', 'role' => $_GET['role'] ?? '']);
?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Ngưng tài khoản -->
<div id="suspendModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:10px;padding:28px;max-width:400px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2)">
    <h3 style="margin:0 0 16px;color:#1a3258">Ngưng tài khoản</h3>
    <p style="margin:0 0 16px;color:#555">Tài khoản: <strong id="suspendName"></strong></p>
    <form method="post" id="suspendForm"><?= csrfField() ?><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? $listRoute) ?>">
      <div class="form-group">
        <label>Số ngày ngưng (để trống = ngưng vĩnh viễn)</label>
        <input type="number" name="days" min="1" max="3650" placeholder="VD: 7" style="width:100%">
      </div>
      <div class="form-group">
        <label>Lý do (tùy chọn)</label>
        <input type="text" name="notes" placeholder="Lý do ngưng tài khoản..." style="width:100%">
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
        <button type="button" onclick="document.getElementById('suspendModal').style.display='none'" class="btn btn-outline-navy">Hủy</button>
        <button type="submit" class="btn" style="background:#e74c3c;color:#fff">Xác nhận ngưng</button>
      </div>
    </form>
  </div>
</div>
<script>
function showSuspendModal(id,name){
  document.getElementById('suspendName').textContent=name;
  document.getElementById('suspendForm').action='/admin/users/'+id+'/suspend';
  document.getElementById('suspendModal').style.display='flex';
}
</script>

<!-- Modal Cấp lại mật khẩu nhân viên -->
<div id="resetPwModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:10px;padding:28px;max-width:420px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2)">
    <h3 style="margin:0 0 8px;color:#1a3258">Cấp lại mật khẩu</h3>
    <p style="margin:0 0 14px;color:#555;font-size:13px">Nhân viên: <strong id="resetPwName"></strong><br><span style="color:#888;font-size:12px">Mật khẩu cũ đã được mã hóa nên không xem lại được. Đặt mật khẩu mới rồi gửi cho nhân viên.</span></p>
    <form method="post" id="resetPwForm"><?= csrfField() ?><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? $listRoute) ?>">
      <div class="form-group">
        <label>Mật khẩu mới <span style="color:#e74c3c">*</span></label>
        <div style="display:flex;gap:8px">
          <input type="text" name="new_password" id="resetPwInput" required minlength="6" placeholder="Tối thiểu 6 ký tự" style="flex:1">
          <button type="button" class="btn btn-outline-navy btn-sm" onclick="genPw()">Ngẫu nhiên</button>
        </div>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
        <button type="button" onclick="document.getElementById('resetPwModal').style.display='none'" class="btn btn-outline-navy">Hủy</button>
        <button type="submit" class="btn btn-navy">Cấp lại mật khẩu</button>
      </div>
    </form>
  </div>
</div>
<script>
function showResetPwModal(id,name){
  document.getElementById('resetPwName').textContent=name;
  document.getElementById('resetPwForm').action='/admin/users/'+id+'/reset-password';
  document.getElementById('resetPwInput').value='';
  document.getElementById('resetPwModal').style.display='flex';
}
function genPw(){
  var ch='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; var p='';
  for(var i=0;i<10;i++) p+=ch.charAt(Math.floor(Math.random()*ch.length));
  document.getElementById('resetPwInput').value=p;
}
</script>




<!-- Modal Xem chi tiết người dùng -->
<div id="userDetailModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;overflow-y:auto">
  <div style="background:#fff;border-radius:12px;padding:0;max-width:700px;width:95%;margin:30px auto;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #eee">
      <h3 style="margin:0;color:#1a3258;font-size:18px">Thông tin chi tiết người dùng</h3>
      <button onclick="closeDetailModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#888">&times;</button>
    </div>
    <div id="userDetailContent" style="padding:24px">
      <div style="text-align:center;padding:40px;color:#888">Đang tải...</div>
    </div>
  </div>
</div>

<script>
var _detailPollTimer = null;
function closeDetailModal(){
  document.getElementById("userDetailModal").style.display="none";
  if(_detailPollTimer) clearInterval(_detailPollTimer);
}
function showUserDetail(uid) {
  document.getElementById("userDetailModal").style.display="flex";
  document.getElementById("userDetailContent").innerHTML="<div style='text-align:center;padding:40px;color:#888'>Đang tải...</div>";
  loadUserDetail(uid);
  if(_detailPollTimer) clearInterval(_detailPollTimer);
  _detailPollTimer = setInterval(function(){ loadUserDetail(uid); }, 5000);
}
document.getElementById("userDetailModal").addEventListener("click", function(e){
  if(e.target === this) closeDetailModal();
});

function escH(s) { if(!s) return ""; var d=document.createElement("div"); d.textContent=s; return d.innerHTML; }
function df(label, value) {
  return "<div><div style='font-size:11px;font-weight:700;color:#888;margin-bottom:2px'>" + label + "</div><div style='font-size:13px;color:#333'>" + escH(value) + "</div></div>";
}

function loadUserDetail(uid) {
  fetch("/admin/users/"+uid+"/detail").then(function(r){return r.json();}).then(function(d){
    if(d.error){ document.getElementById("userDetailContent").innerHTML="<p style='color:red'>Không tìm thấy</p>"; return; }
    var u = d.user, inv = d.invoice, oc = d.order_count;
    var avatar = "";
    if(u.avatar && u.avatar.indexOf("/") === -1) avatar = "/uploads/avatars/"+u.avatar;
    else if(u.avatar) avatar = u.avatar;
    else avatar = "/assets/images/default-avatar.png";
    var statusHtml = u.status==="active"
      ? "<span style='background:#e8f5e9;color:#27ae60;padding:2px 10px;border-radius:4px;font-size:12px;font-weight:700'>Hoạt động</span>"
      : "<span style='background:#fde8e8;color:#c0392b;padding:2px 10px;border-radius:4px;font-size:12px;font-weight:700'>Ngưng</span>";

    var html = "<div style='display:flex;gap:16px;align-items:center;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f0f0f0'>";
    html += "<img src='"+avatar+"' style='width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #ddd;background:#e0e0e0' onerror='this.src=\"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2NjYyI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00czEuNzktNCA0LTRtMCAxMGMtMi42NyAwLTggMS4zNC04IDR2MmgxNnYtMmMwLTIuNjYtNS4zMy00LTgtNHoiLz48L3N2Zz4=\"'>";
    html += "<div><div style='font-size:18px;font-weight:700;color:#1a3258'>"+escH(u.full_name)+"</div>";
    html += "<div style='font-size:13px;color:#666;margin-top:2px'>"+escH(u.email)+"</div>";
    html += "<div style='margin-top:6px'>"+statusHtml+" <span style='font-size:12px;color:#888;margin-left:8px'>"+oc+" đơn hàng</span></div></div></div>";

    html += "<div style='display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px'>";
    html += df("SĐT", u.phone||"\u2014");
    html += df("Vai trò", u.role);
    html += df("Địa chỉ", u.address||"\u2014");
    html += df("Ngày đăng ký", u.created_at ? new Date(u.created_at).toLocaleDateString("vi-VN") : "\u2014");
    if(u.notes) html += "<div style='grid-column:1/-1'>"+df("Ghi chú", u.notes)+"</div>";
    html += "</div>";

    html += "<div style='border-top:2px solid #1a3258;padding-top:16px;margin-top:8px'>";
    html += "<h4 style='color:#1a3258;font-size:15px;margin:0 0 14px'>THÔNG TIN XUẤT HÓA ĐƠN</h4>";
    if(inv && (inv.buyer_name || inv.tax_code || inv.id_number)) {
      var typeLabel = inv.invoice_type==="business" ? "Tổ chức/Hộ kinh doanh" : "Cá nhân";
      html += "<div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px'>";
      html += "<div style='display:grid;grid-template-columns:1fr 1fr;gap:10px'>";
      html += df("Loại", typeLabel);
      html += df("Tên người mua", inv.buyer_name||"\u2014");
      html += df("Mã số thuế", inv.tax_code||"\u2014");
      html += df("Địa chỉ", inv.address||"\u2014");
      html += df("Tỉnh/TP", inv.province||"\u2014");
      html += df("Phường/Xã", inv.ward||"\u2014");
      html += df("Số CCCD/CMND", inv.id_number||"\u2014");
      html += df("Số hộ chiếu", inv.passport||"\u2014");
      html += df("Email", inv.email||"\u2014");
      html += df("Số điện thoại", inv.phone||"\u2014");
      html += df("Ngân hàng", inv.bank_name||"\u2014");
      html += df("Số TK ngân hàng", inv.bank_account||"\u2014");
      html += "</div></div>";
    } else {
      html += "<div style='background:#f8f9fa;padding:16px;border-radius:8px;text-align:center;color:#888;font-size:13px'>Chưa có thông tin xuất hóa đơn</div>";
    }
    html += "</div>";
    document.getElementById("userDetailContent").innerHTML = html;
  }).catch(function(){ document.getElementById("userDetailContent").innerHTML="<p style='color:red'>Lỗi tải dữ liệu</p>"; });
}
</script>

<script>
function toggleAllUsers(master) {
  var checks = document.querySelectorAll('.row-check');
  checks.forEach(function(cb){ cb.checked = master.checked; });
  updateBulkBtn();
}
document.addEventListener('change', function(e) {
  if (e.target.classList.contains('row-check')) updateBulkBtn();
});
function updateBulkBtn() {
  var checked = document.querySelectorAll('.row-check:checked').length;
  var btn = document.getElementById('bulkDeleteBtn');
  if (btn) btn.style.display = checked > 0 ? 'inline-block' : 'none';
  if (btn && checked > 0) btn.textContent = 'Xóa đã chọn (' + checked + ')';
}
async function bulkDeleteUsers() {
  var ids = [];
  document.querySelectorAll('.row-check:checked').forEach(function(cb){ ids.push(cb.value); });
  if (!ids.length) return;
  if (!(await csConfirmAsync('Bạn có chắc muốn xóa ' + ids.length + ' tài khoản đã chọn?'))) return;
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = '/admin/users/bulk-delete';
  var __bk=document.createElement('input'); __bk.type='hidden'; __bk.name='back'; __bk.value='<?= e($listRoute ?? '/admin/users') ?>'; form.appendChild(__bk);
  var csrf = document.createElement('input');
  csrf.type = 'hidden'; csrf.name = '_csrf';
  csrf.value = document.querySelector('meta[name=csrf-token]')?.content || document.querySelector('input[name=_csrf]')?.value || '';
  form.appendChild(csrf);
  ids.forEach(function(id) {
    var inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
    form.appendChild(inp);
  });
  document.body.appendChild(form);
  form.submit();
}
</script>
<?php if(($listRole??'')==='admin'): ?>
<div style="background:#fff;border:1px solid var(--line);border-radius:8px;padding:16px 18px;margin-top:18px">
  <h3 style="margin:0 0 4px;color:var(--navy)">Nhật ký đổi mật khẩu</h3>
  <p style="margin:0 0 14px;font-size:12px;color:#888;line-height:1.5">Theo dõi việc đổi mật khẩu của các tài khoản quản trị viên: ai đổi, khi nào, bằng cách nào. Vì mật khẩu được mã hóa 1 chiều nên hệ thống <strong>không lưu và không hiển thị mật khẩu thật</strong>. Cần đặt lại mật khẩu cho admin, hãy dùng nút <strong>Cấp lại MK</strong> ở bảng trên.</p>
  <?php $__pwLog = $pwLog ?? []; if(empty($__pwLog)): ?>
    <div style="background:#f8f9fa;padding:16px;border-radius:8px;text-align:center;color:#888;font-size:13px">Chưa có thay đổi mật khẩu nào được ghi nhận.</div>
  <?php else: $__pml=['self_change'=>'Admin tự đổi (Cài đặt)','forgot_otp'=>'Quên mật khẩu (OTP)','superadmin_reset'=>'Super admin cấp lại','admin_reset'=>'Admin cấp lại']; ?>
    <div style="overflow-x:auto"><table class="tbl" style="width:100%;min-width:560px">
      <thead><tr><th>Email admin</th><th>Thời gian</th><th>Cách đổi</th><th>Người thực hiện</th></tr></thead>
      <tbody>
      <?php foreach($__pwLog as $__lg): $__m=json_decode($__lg['meta']??'[]',true)?:[]; $__mk=$__m['method']??''; ?>
        <tr>
          <td><?= e(($__m['target_email']??'') ?: '—') ?></td>
          <td style="white-space:nowrap"><?= e(fmtVnDateTime($__lg['created_at']??'')) ?></td>
          <td><?= e($__pml[$__mk] ?? ($__mk ?: '—')) ?></td>
          <td><?= e(($__m['actor_email']??'') ?: '— (chính chủ)') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>

<div id="csvImportUsers" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:12px;padding:28px;max-width:500px;width:95%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h3 style="margin:0;color:var(--navy)"> Nhập người dùng từ CSV</h3><button onclick="document.getElementById('csvImportUsers').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer">&times;</button></div>
<form method="post" action="/admin/users/import-csv" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
<input type="hidden" name="import_role" value="<?= e($listRole??'customer') ?>">
<div style="margin-bottom:12px;font-size:12px;color:#666;background:#f0f4ff;padding:10px;border-radius:6px">Cột: Họ và tên, Email, SĐT, Vai trò, Trạng thái, Địa chỉ<br><a href="/admin/users/export-csv?role=<?= e($listRole??'customer') ?>" style="color:var(--navy);font-weight:600">⬇ Tải file mẫu</a></div>
<div class="csv-dropzone" id="dz-users">
  <input type="file" name="csv_file" accept=".csv" required>
  <span class="dz-icon">📂</span>
  <div class="dz-text">Kéo thả file CSV vào đây hoặc nhấn để chọn file</div>
  <div class="dz-subtext">Chỉ chấp nhận file <strong>.csv</strong> — Không hỗ trợ Word (.docx), Excel (.xlsx), PDF...</div>
  <div class="dz-filename"></div>
  <div class="dz-error"></div>
</div>
<button type="submit" class="btn btn-gold" style="width:100%"> Nhập</button><script>
(function() {
  var dz = document.getElementById('dz-users');
  var inp = dz ? dz.querySelector('input[type="file"]') : null;
  if (!dz || !inp) return;

  var fnEl = dz.querySelector('.dz-filename');
  var errEl = dz.querySelector('.dz-error');
  var txtEl = dz.querySelector('.dz-text');

  function validateFile(file) {
    if (!file) return false;
    var name = file.name.toLowerCase();
    var ext = name.split('.').pop();
    var allowedMimes = ['text/csv','text/plain','application/csv','application/vnd.ms-excel'];
    var mimeOk = allowedMimes.indexOf(file.type) > -1 || file.type === '' || file.type.indexOf('text') === 0;
    return ext === 'csv' && (mimeOk || file.type === '');
  }

  function showOk(file) {
    dz.classList.remove('drag-over','drag-reject');
    dz.classList.add('file-ok');
    if (fnEl) { fnEl.style.display='block'; fnEl.textContent = '✅ ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)'; }
    if (errEl) errEl.style.display='none';
    if (txtEl) txtEl.style.opacity='0.5';
  }

  function showError(msg) {
    dz.classList.remove('drag-over','file-ok');
    dz.classList.add('drag-reject');
    if (errEl) { errEl.style.display='block'; errEl.textContent = msg; }
    if (fnEl) fnEl.style.display='none';
    if (txtEl) txtEl.style.opacity='1';
    inp.value = '';
    setTimeout(function(){ dz.classList.remove('drag-reject'); }, 1500);
  }

  // Sự kiện chọn file qua click
  inp.addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    if (validateFile(file)) {
      showOk(file);
    } else {
      showError('❌ Chỉ chấp nhận file CSV! File "' + file.name + '" không hợp lệ.');
    }
  });

  // Drag events
  dz.addEventListener('dragover', function(e) {
    e.preventDefault(); e.stopPropagation();
    var items = e.dataTransfer.items;
    var hasInvalid = false;
    for (var i=0; i<items.length; i++) {
      if (items[i].kind === 'file') {
        var t = items[i].type;
        if (t && t.indexOf('text') < 0 && t !== 'application/vnd.ms-excel' && t !== '') hasInvalid = true;
      }
    }
    dz.classList.toggle('drag-reject', hasInvalid);
    dz.classList.toggle('drag-over', !hasInvalid);
  });

  dz.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dz.classList.remove('drag-over','drag-reject');
  });

  dz.addEventListener('drop', function(e) {
    e.preventDefault(); e.stopPropagation();
    dz.classList.remove('drag-over','drag-reject');
    var files = e.dataTransfer.files;
    if (!files || files.length === 0) return;
    var file = files[0];
    if (files.length > 1) {
      showError('❌ Chỉ được chọn 1 file CSV tại một thời điểm.');
      return;
    }
    if (!validateFile(file)) {
      var ext = file.name.split('.').pop().toLowerCase();
      showError('❌ Không chấp nhận file .' + ext + '! Chỉ được import file CSV.');
      return;
    }
    // Gán file vào input
    var dt = new DataTransfer();
    dt.items.add(file);
    inp.files = dt.files;
    showOk(file);
  });
})();
</script>
</form></div></div>
