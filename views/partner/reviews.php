<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Đánh giá</h1></div>
<div class="panel"><table class="tbl"><thead><tr><th>SP</th><th>Khách</th><th>Sao</th><th>Nội dung</th><th>Ngày</th></tr></thead><tbody>
<?php foreach($reviews as $r):?><tr><td><?=truncate(e($r['product_name']),30)?></td><td><?=e($r['full_name'])?></td><td style="color:var(--gold-warm)"><?=stars($r['rating'])?></td><td class="fs-12"><?=truncate(e($r['comment']??''),50)?></td><td class="fs-12"><?=relTime($r['created_at'])?></td></tr><?php endforeach;?></tbody></table></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>