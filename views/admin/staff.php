<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<style>
.role-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 96px !important;
  height: 32px !important;
  font-size: 12px !important;
  font-weight: 600 !important;
  border-radius: 6px !important;
  box-sizing: border-box !important;
  text-decoration: none !important;
  cursor: pointer !important;
  margin: 0 !important;
  padding: 0 8px !important;
  line-height: 1 !important;
  transition: all 0.15s ease-in-out;
  border: 1px solid transparent;
}
.role-btn-blue { background: #1565c0 !important; color: #ffffff !important; border-color: #1565c0 !important; }
.role-btn-blue:hover { background: #0d47a1 !important; }

.role-btn-navy { background: #1a3258 !important; color: #ffffff !important; border-color: #1a3258 !important; }
.role-btn-navy:hover { background: #0f213d !important; }

.role-btn-assign { background: #0284c7 !important; color: #ffffff !important; border-color: #0284c7 !important; }
.role-btn-assign:hover { background: #0369a1 !important; }

.role-btn-red { background: #ffffff !important; color: #dc2626 !important; border: 1px solid #fca5a5 !important; }
.role-btn-red:hover { background: #fef2f2 !important; border-color: #f87171 !important; }

.role-btn-group {
  display: flex !important;
  flex-direction: column !important;
  gap: 6px !important;
  align-items: center !important;
  justify-content: center !important;
  width: 104px !important;
}
</style>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center">
  <h1>Phân quyền nhân viên</h1>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="/admin/staff/rbac/coverage" class="btn btn-outline-navy">Bản đồ quyền</a>
    <a href="/admin/staff/roles/new" class="btn btn-navy">+ Tạo vai trò mới</a>
  </div>
</div>

<?php foreach (getFlash() as $message): ?>
<div class="alert alert-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
<?php endforeach; ?>

<div class="panel mb-4">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Danh sách vai trò (<?= count($staffRoles ?? []) ?>)</div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Tên vai trò</th>
        <th>Mô tả</th>
        <th>Quyền hạn</th>
        <th>NV được gán</th>
        <th style="text-align:center;width:120px">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($staffRoles)): ?>
      <tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có vai trò nào.</td></tr>
    <?php endif; ?>
    <?php foreach ($staffRoles ?? [] as $role): ?>
      <?php 
        $permissions = json_decode($role['permissions'] ?? '[]', true) ?: []; 
        $staffCount = dbGet('SELECT COUNT(*) AS n FROM staff_role_assignments WHERE role_id=?', [$role['id']])['n'] ?? 0; 
        $template = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$role['id']]); 
        $taskCount = $template ? (int)(dbGet('SELECT COUNT(*) AS n FROM rbac_role_permissions WHERE role_code=? AND access_level<>?', [$template['rbac_role_code'], 'NONE'])['n'] ?? 0) : count($permissions); 
      ?>
      <tr>
        <td>
          <strong style="color:var(--navy)"><?= e($role['name']) ?></strong>
          <?php if ($template): ?>
          <div class="fs-11" style="color:#1565c0;font-weight:700;margin-top:3px">Vai trò mẫu RBAC <?= e($template['rbac_role_code']) ?> · Chỉ đọc</div>
          <?php endif; ?>
        </td>
        <td class="fs-12 text-muted"><?= e($role['description'] ?? '') ?></td>
        <td><span style="background:#e3f2fd;color:#1565c0;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700"><?= $taskCount ?> quyền<?= $template ? ' theo ma trận' : '' ?></span></td>
        <td><span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700"><?= $staffCount ?> nhân viên</span></td>
        <td style="vertical-align:middle;text-align:center">
          <div class="role-btn-group" style="margin:0 auto">
            <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/permissions" class="role-btn role-btn-blue">Xem quyền</a>
            <?php if ($template): ?>
              <form method="post" action="/admin/staff/roles/<?= (int)$role['id'] ?>/duplicate" style="margin:0;padding:0">
                <?= csrfField() ?>
                <button type="submit" class="role-btn role-btn-navy">Nhân bản</button>
              </form>
            <?php else: ?>
              <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/edit" class="role-btn role-btn-navy">Sửa</a>
            <?php endif; ?>
            <a href="/admin/staff/roles/<?= (int)$role['id'] ?>/assign" class="role-btn role-btn-assign">Phân công</a>
            <?php if (!$template): ?>
              <form method="post" action="/admin/staff/roles/<?= (int)$role['id'] ?>/delete" style="margin:0;padding:0" onsubmit="return csConfirmForm(this,'Xóa vai trò này?')">
                <?= csrfField() ?>
                <button type="submit" class="role-btn role-btn-red">Xóa</button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Nhân viên đang được phân quyền (<?= (int)($sTotal ?? 0) ?>)</div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Nhân viên</th>
        <th>Email</th>
        <th>Vai trò</th>
        <th>Phân công lúc</th>
        <th style="text-align:center;width:120px">Thao tác</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($assignments)): ?>
      <tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có nhân viên nào được phân quyền.</td></tr>
    <?php endif; ?>
    <?php foreach ($assignments ?? [] as $assignment): $roleList = array_filter(explode('|', $assignment['roles'] ?? '')); ?>
      <tr>
        <td><strong><?= e($assignment['full_name']) ?></strong><div class="fs-11 text-muted"><?= e($assignment['phone'] ?? '') ?></div></td>
        <td class="fs-12"><?= e($assignment['email']) ?></td>
        <td>
          <?php foreach ($roleList as $roleText): $item = explode('~', $roleText, 2); ?>
          <span style="display:inline-block;background:var(--gold-warm);color:var(--navy-dark);padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700;margin:2px"><?= e($item[1] ?? '') ?></span>
          <?php endforeach; ?>
        </td>
        <td class="fs-12"><?= date('d/m/Y', strtotime($assignment['assigned_at'])) ?></td>
        <td style="vertical-align:middle;text-align:center">
          <form method="post" action="/admin/staff/unassign-all/<?= (int)$assignment['user_id'] ?>" style="margin:0;display:inline-block" onsubmit="return csConfirmForm(this,'Hủy toàn bộ quyền của nhân viên này?')">
            <?= csrfField() ?>
            <button type="submit" class="role-btn role-btn-red" style="width:104px !important">Hủy tất cả</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
