<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>Phân công nhân viên cho vai trò: <span style="color:var(--gold-warm)"><?= e($staffRole['name']) ?></span></h1>
</div>

<?php $flashMsgs = getFlash(); foreach($flashMsgs as $fm): ?>
  <div class="alert alert-<?= e($fm['type']) ?>"><?= e($fm['message']) ?></div>
<?php endforeach; ?>

<?php $perms = json_decode($staffRole['permissions'] ?? '[]', true) ?: []; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Phân công mới -->
<div class="panel">
  <div style="padding:12px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px">Phân công nhân viên mới</div>
  <div style="padding:16px">
    <form method="post" action="/admin/staff/roles/<?= $staffRole['id'] ?>/assign">
      <?= csrfField() ?>
      <div class="form-group" style="margin-bottom:12px">
        <label style="font-size:12px">Chọn tài khoản để phân công</label>
        <select name="user_id" required style="width:100%">
          <option value="">— Chọn tài khoản —</option>
          <?php foreach($availableUsers as $u): ?>
            <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?> (<?= e($u['email']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <?php if(empty($availableUsers)): ?>
          <p style="font-size:12px;color:#888;margin-top:6px">Tất cả người dùng đã được gán vai trò này.</p>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-navy btn-sm">Phân công</button>
    </form>
    
    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--line);font-size:12px;color:#888">
      <strong>Quyền của vai trò này:</strong>
      <?php 
      $pLabels = ['orders'=>'Đơn hàng','create_order'=>'Tạo đơn hộ','products'=>'Sản phẩm','reviews'=>'Đánh giá','contacts'=>'Quản lý liên hệ','users'=>'Người dùng','vouchers'=>'Voucher','content'=>'Nội dung','reports'=>'Báo cáo'];
      ?>
      <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px">
        <?php foreach($perms as $p): ?>
          <span style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:600"><?= $pLabels[$p] ?? $p ?></span>
        <?php endforeach; ?>
        <?php if(empty($perms)): ?><span style="color:#e74c3c">Chưa có quyền nào!</span><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Nhân viên đang giữ vai trò này -->
<div class="panel">
  <div style="padding:12px 16px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px">Đang giữ vai trò này (<?= count($assignedUsers) ?>)</div>
  <?php if(empty($assignedUsers)): ?>
    <div style="padding:20px;text-align:center;color:#888;font-size:13px">Chưa có nhân viên nào</div>
  <?php else: ?>
  <table class="tbl">
    <thead><tr><th>Họ tên</th><th>Email</th><th>Hủy</th></tr></thead>
    <tbody>
    <?php foreach($assignedUsers as $u): ?>
    <tr>
      <td><?= e($u['full_name']) ?></td>
      <td class="fs-12"><?= e($u['email']) ?></td>
      <td>
        <form method="post" action="/admin/staff/unassign/<?= $u['assignment_id'] ?>" onsubmit="return confirm('Hủy phân quyền?')">
          <?= csrfField() ?><button type="submit" class="btn btn-sm" style="background:#fee;color:#c62828;border:1px solid #fcc">Hủy</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</div>

<div style="margin-top:16px"><a href="/admin/staff" class="btn btn-outline-navy">← Quay lại Phân quyền</a></div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
