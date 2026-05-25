<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Audit Log</h1></div>
<div class="panel"><table class="tbl"><thead><tr><th>User</th><th>Action</th><th>Target</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($logs as $l):?><tr><td><?=e($l['full_name']??$l['email']??'System')?></td><td class="fs-12"><?=e($l['action'])?></td><td class="fs-12"><?=e($l['target_type']??'').':'.e($l['target_id']??'')?></td><td class="fs-12"><?=relTime($l['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>