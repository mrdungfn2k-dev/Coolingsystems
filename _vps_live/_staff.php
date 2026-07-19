<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center">
  <h1>Phân quyền nhân viên</h1>
  <a href="/admin/staff/roles/new" class="btn btn-navy">+ Tạo vai trò mới</a>
</div>

<?php foreach (getFlash() as $message): ?>
  <div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
<?php endforeach; ?>

<div class="panel mb-4">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Danh sách vai trò (<?= count($staffRoles ?? []) ?>)</div>
  <table class="tbl">
    <thead><tr><th>Tên vai trò</th><th>Mô tả</th><th>Quyền hạn</th><th>NV được gán</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php if (empty($staffRoles)): ?>
      <tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có vai trò nào.</td></tr>
    <?php endif; ?>
    <?php foreach ($staffRoles ?? [] as $role): ?>
      <?php
        $permissions = json_decode($role['permissions'] ?? '[]', true) ?: [];
        $staffCount = dbGet('SELECT COUNT(*) AS n FROM staff_role_assignments WHERE role_id=?', [$role['id']])['n'] ?? 0;
        $template = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$role['id']]);
        $taskCount = $template ? (int)(dbGet('SELECT COUNT(*) AS n FROM rbac_role_permissions WHERE role_code=?', [$template['rbac_role_code']])['n'] ?? 0) : count($permissions);
      ?>
      <tr>
        <td>
          <strong style="color:var(--navy)"><?= e($role['name']) ?></strong>
          <?php if ($template): ?><div class="fs-11" style="color:#1565c0;font-weight:700;margin-top:3px">Ma trận RBAC: <?= e($template['rbac_role_code']) ?> · Mẫu hệ thống, chỉ đọc</div><?php endif; ?>
        </td>
        <td class="fs-12 text-muted"><?= e($role['description'] ?? '') ?></td>
        <td><span style="background:#e3f2fd;color:#1565c0;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700"><?= $taskCount ?> nhiệm vụ<?= $template ? ' theo ma trận' : '' ?></span></td>
        <td><span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700"><?= $staffCount ?> nhân viên</span></td>
        <td style="display:flex;gap:6px;flex-wrap:wrap">
          <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/permissions" class="adm-edit">Xem quyền</a>
          <?php if ($template): ?>
            <span style="align-self:center;font-size:11px;color:#6b7280;font-weight:700">Không sửa/xóa</span>
          <?php else: ?>
            <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/edit" class="adm-edit">Sửa</a>
          <?php endif; ?>
          <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/assign" class="btn btn-navy btn-sm">Phân công</a>
          <?php if (!$template): ?>
            <form method="post" action="/admin/staff/roles/<?= (int)$role['id'] ?>/delete" style="display:inline" onsubmit="return csConfirmForm(this,'Xóa vai trò này?')"><?= csrfField() ?><button type="submit" class="adm-del">Xóa</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Nhân viên đang được phân quyền (<?= (int)($sTotal ?? 0) ?>)</div>
  <table class="tbl">
    <thead><tr><th>Nhân viên</th><th>Email</th><th>Vai trò</th><th>Phân công lúc</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php if (empty($assignments)): ?><tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có nhân viên nào được phân quyền.</td></tr><?php endif; ?>
    <?php foreach ($assignments ?? [] as $assignment): $roleList = array_filter(explode('|', $assignment['roles'] ?? '')); ?>
      <tr>
        <td><strong><?= e($assignment['full_name']) ?></strong><div class="fs-11 text-muted"><?= e($assignment['phone'] ?? '') ?></div></td>
        <td class="fs-12"><?= e($assignment['email']) ?></td>
        <td><?php foreach ($roleList as $roleText): $item = explode('~', $roleText, 2); ?><span style="display:inline-block;background:var(--gold-warm);color:var(--navy-dark);padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700;margin:2px"><?= e($item[1] ?? '') ?></span><?php endforeach; ?></td>
        <td class="fs-12"><?= date('d/m/Y', strtotime($assignment['assigned_at'])) ?></td>
        <td><form method="post" action="/admin/staff/unassign-all/<?= (int)$assignment['user_id'] ?>" style="display:inline" onsubmit="return csConfirmForm(this,'Hủy toàn bộ quyền của nhân viên này?')"><?= csrfField() ?><button type="submit" class="btn btn-sm" style="background:#fee;color:#c62828;border:1px solid #f5c6cb">Hủy tất cả</button></form></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
