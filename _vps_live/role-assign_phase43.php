<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Phân công nhân viên cho vai trò: <span style="color:var(--gold-warm)"><?= e($staffRole['name']) ?></span></h1></div>
<?php foreach (getFlash() as $message): ?><div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div><?php endforeach; ?>
<?php $permissions = json_decode($staffRole['permissions'] ?? '[]', true) ?: []; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <div class="panel"><div style="padding:12px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px">Phân công nhân viên mới</div><div style="padding:16px">
    <?php if (empty($availableUsers)): ?>
      <div class="alert alert-info" style="margin:0 0 14px">Chưa có tài khoản nhân viên đang hoạt động để phân công. Tài khoản quản trị, đối tác và khách hàng không được hiển thị tại đây.</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap"><a href="/admin/users?role=staff" class="btn btn-outline-navy btn-sm">Danh sách nhân viên</a><a href="/admin/users/new?role=staff" class="btn btn-navy btn-sm">+ Thêm nhân viên</a></div>
    <?php else: ?>
      <form method="post" action="/admin/staff/roles/<?= (int)$staffRole['id'] ?>/assign"><?= csrfField() ?>
        <div class="form-group" style="margin-bottom:12px"><label style="font-size:12px">Chọn tài khoản nhân viên đang hoạt động</label><select name="user_id" required style="width:100%"><option value="">— Chọn nhân viên —</option><?php foreach ($availableUsers as $user): ?><option value="<?= (int)$user['id'] ?>"><?= e($user['full_name']) ?> (<?= e($user['email']) ?>)</option><?php endforeach; ?></select></div>
        <button type="submit" class="btn btn-navy btn-sm">Phân công</button>
      </form>
    <?php endif; ?>
    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--line);font-size:12px;color:#667085">
      <strong>Phạm vi quyền sẽ được cấp:</strong>
      <?php if ($rbacTemplate): ?><p style="margin:7px 0">Vai trò mẫu RBAC có <?= (int)$matrixTaskCount ?> nhiệm vụ theo ma trận. <a href="/admin/staff/roles/<?= (int)$staffRole['id'] ?>/permissions">Xem toàn bộ quyền</a>.</p>
      <?php else: ?><p style="margin:7px 0">Vai trò tùy chỉnh có <?= count($permissions) ?> quyền đã chọn. <a href="/admin/staff/roles/<?= (int)$staffRole['id'] ?>/permissions">Xem quyền</a>.</p><?php endif; ?>
      <p style="margin:0">Hệ thống chỉ gán quyền cho tài khoản có loại <strong>Nhân viên</strong> và trạng thái <strong>Hoạt động</strong>. Mọi thao tác gán hoặc hủy đều được ghi nhật ký.</p>
    </div>
  </div></div>
  <div class="panel"><div style="padding:12px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px">Đang giữ vai trò này (<?= count($assignedUsers) ?>)</div>
    <?php if (empty($assignedUsers)): ?><div style="padding:20px;text-align:center;color:#888;font-size:13px">Chưa có nhân viên nào</div><?php else: ?><table class="tbl"><thead><tr><th>Họ tên</th><th>Email</th><th>Hủy</th></tr></thead><tbody><?php foreach ($assignedUsers as $user): ?><tr><td><?= e($user['full_name']) ?></td><td class="fs-12"><?= e($user['email']) ?></td><td><form method="post" action="/admin/staff/unassign/<?= (int)$user['assignment_id'] ?>" onsubmit="return csConfirmForm(this,'Hủy phân quyền này?')"><?= csrfField() ?><button type="submit" class="btn btn-sm" style="background:#fee;color:#c62828;border:1px solid #fcc">Hủy</button></form></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
  </div>
</div>
<div style="margin-top:16px"><a href="/admin/staff" class="btn btn-outline-navy">Quay lại Phân quyền</a></div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
