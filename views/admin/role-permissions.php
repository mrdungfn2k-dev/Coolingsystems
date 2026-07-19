<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center"><h1>Quyền của vai trò</h1><a href="/admin/staff" class="btn btn-outline-navy">Quay lại</a></div>
<div class="panel" style="padding:22px"><h2 style="font-size:18px;margin:0 0 6px;color:var(--navy)"><?= e($staffRole['name']) ?></h2><p class="text-muted" style="margin:0 0 18px"><?= e($staffRole['description'] ?? '') ?></p>
<?php if ($rbacTemplate): ?><div class="alert alert-info" style="margin-bottom:18px">Vai trò mẫu RBAC <?= e($rbacTemplate['rbac_role_code']) ?>. Quyền được đồng bộ từ ma trận và không thể sửa trực tiếp.</div><?php endif; ?>
<table class="tbl"><thead><tr><th>Phân hệ</th><th>Chức năng</th><th>Nhiệm vụ</th><th>Mức quyền</th><th>Trạng thái</th></tr></thead><tbody>
<?php foreach ($matrixPermissions ?? [] as $permission): ?><tr><td><?= e($permission['module_name']) ?></td><td><?= e($permission['feature_name']) ?></td><td><?= e($permission['action_name']) ?></td><td><span style="background:#e3f2fd;color:#1565c0;padding:2px 7px;border-radius:4px;font-weight:700;font-size:11px"><?= e($permission['access_level']) ?></span></td><td><?php if (!empty($permission['capabilities'])): ?><span style="color:#18794e;font-size:12px;font-weight:700">Đã gắn chức năng</span><?php else: ?><span style="color:#8a6d1d;font-size:12px">Chưa có màn hình riêng</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (empty($matrixPermissions)): ?><tr><td colspan="5" style="padding:22px;text-align:center;color:#718096">Vai trò tùy chỉnh đang sử dụng các quyền đã chọn trong biểu mẫu.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
