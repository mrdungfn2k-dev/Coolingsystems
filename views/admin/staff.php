<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center">
  <h1>Phân quyền nhân viên</h1>
  <a href="/admin/staff/roles/new" class="btn btn-navy">+ Tạo vai trò mới</a>
</div>

<?php $flashMsgs = getFlash(); foreach($flashMsgs as $fm): ?>
  <div class="alert alert-<?= e($fm['type']) ?>"><?= e($fm['message']) ?></div>
<?php endforeach; ?>

<!-- Danh sách vai trò -->
<div class="panel mb-4">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Danh sách vai trò (<?= count($staffRoles ?? []) ?>)</div>
  <table class="tbl">
    <thead><tr><th>Tên vai trò</th><th>Mô tả</th><th>Quyền hạn</th><th>NV được gán</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php if(empty($staffRoles)): ?>
      <tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có vai trò nào. Hãy bấm "Tạo vai trò mới" để bắt đầu.</td></tr>
    <?php else: ?>
    <?php foreach($staffRoles as $r): ?>
    <?php
      $perms = json_decode($r['permissions'] ?? '[]', true) ?: [];
      $permLabels = [
        'orders'=>'Đơn hàng','products'=>'Sản phẩm',
        'reviews'=>'Đánh giá','contacts'=>'Quản lý liên hệ','users'=>'Người dùng',
        'vouchers'=>'Voucher','content'=>'Nội dung',
        'reports'=>'Báo cáo','create_order'=>'Tạo đơn hộ'
      ];
      $staffCount = dbGet("SELECT COUNT(*) as n FROM staff_role_assignments WHERE role_id=?", [$r['id']])['n'] ?? 0;
    ?>
    <tr>
      <td><strong style="color:var(--navy)"><?= e($r['name']) ?></strong></td>
      <td class="fs-12 text-muted"><?= e($r['description'] ?? '') ?></td>
      <td>
        <div style="display:flex;flex-wrap:wrap;gap:4px">
        <?php foreach($perms as $p): ?>
          <span style="background:#e3f2fd;color:#1565c0;padding:1px 6px;border-radius:3px;font-size:11px;font-weight:600"><?= $permLabels[$p] ?? $p ?></span>
        <?php endforeach; ?>
        <?php if(empty($perms)): ?><span class="text-muted fs-11">Chưa có quyền</span><?php endif; ?>
        </div>
      </td>
      <td><span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700"><?= $staffCount ?> nhân viên</span></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <a href="/admin/staff/roles/<?= $r['id'] ?>/edit" class="btn btn-outline-navy btn-sm">Sửa</a>
        <a href="/admin/staff/roles/<?= $r['id'] ?>/assign" class="btn btn-navy btn-sm">Phân công</a>
        <form method="post" action="/admin/staff/roles/<?= $r['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Xóa vai trò này?')">
          <?= csrfField() ?><button type="submit" class="btn btn-sm" style="background:#fee;color:#c62828;border:1px solid #f5c6cb">Xóa</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Danh sách nhân viên được phân quyền -->
<div class="panel">
  <div style="padding:14px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px;color:var(--navy)">Nhân viên đang được phân quyền (<?= count($assignments ?? []) ?>)</div>
  <table class="tbl">
    <thead><tr><th>Nhân viên</th><th>Email</th><th>Vai trò</th><th>Phân công lúc</th><th>Thao tác</th></tr></thead>
    <tbody>
    <?php if(empty($assignments)): ?>
      <tr><td colspan="5" style="text-align:center;padding:30px;color:#888">Chưa có nhân viên nào được phân quyền.</td></tr>
    <?php else: ?>
    <?php foreach($assignments as $a): ?>
    <tr>
      <td><strong><?= e($a['full_name']) ?></strong><div class="fs-11 text-muted"><?= e($a['phone'] ?? '') ?></div></td>
      <td class="fs-12"><?= e($a['email']) ?></td>
      <td><span style="background:var(--gold-warm);color:var(--navy-dark);padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700"><?= e($a['role_name']) ?></span></td>
      <td class="fs-12"><?= date('d/m/Y', strtotime($a['assigned_at'])) ?></td>
      <td>
        <form method="post" action="/admin/staff/unassign/<?= $a['assignment_id'] ?>" style="display:inline" onsubmit="return confirm('Hủy phân quyền nhân viên này?')">
          <?= csrfField() ?><button type="submit" class="btn btn-sm" style="background:#fee;color:#c62828;border:1px solid #f5c6cb">Hủy quyền</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
