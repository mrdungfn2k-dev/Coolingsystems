<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
if(!function_exists('dbAgo')){function dbAgo($t){ if(!$t) return ''; $s=time()-strtotime($t); if($s<0)$s=0; if($s<60) return 'vừa xong'; if($s<3600) return floor($s/60).' phút trước'; if($s<86400) return floor($s/3600).' giờ trước'; return floor($s/86400).' ngày trước'; }}
if(!function_exists('dbDelta')){function dbDelta($pct){ if($pct===null) return ''; $up=$pct>=0; $cls=$up?'db-up':'db-down'; $ar=$up?'▲':'▼'; return '<span class="db-delta '.$cls.'">'.$ar.' '.number_format(abs($pct),1).'%</span>'; }}
// status donut arrays (buckets come from the route = REAL delivery_status values)
$donutLabels=[];$donutData=[];$donutColors=[];$donutTotal=0;
foreach(($statusBuckets??[]) as $k=>$v){ $donutTotal+=$v['c']; if($v['c']>0){$donutLabels[]=$k;$donutData[]=(int)$v['c'];$donutColors[]=$v['color'];} }
// notifications from real recent events
$notif=[];
foreach(array_slice($recentOrders??[],0,3) as $o){ $ds=$o['delivery_status']??''; $nm=$o['full_name']?:($o['shipping_full_name']??'Khách');
  if($ds==='cancelled') $notif[]=['ic'=>'x','c'=>'#ef4444','t'=>'Đơn '.$o['code'].' bị hủy','s'=>$nm,'time'=>$o['created_at']];
  elseif($ds==='returned') $notif[]=['ic'=>'back','c'=>'#94a3b8','t'=>'Đơn '.$o['code'].' đã trả hàng','s'=>$nm,'time'=>$o['created_at']];
  elseif($ds==='completed') $notif[]=['ic'=>'check','c'=>'#22c55e','t'=>'Đơn '.$o['code'].' đã hoàn thành','s'=>$nm,'time'=>$o['created_at']];
  elseif($ds==='received') $notif[]=['ic'=>'cart','c'=>'#3b82f6','t'=>'Đơn '.$o['code'].' đã tiếp nhận','s'=>$nm,'time'=>$o['created_at']];
  elseif(in_array($ds,['delivering','delivered'])) $notif[]=['ic'=>'cart','c'=>'#8b5cf6','t'=>'Đơn '.$o['code'].' đang giao','s'=>$nm,'time'=>$o['created_at']];
  else $notif[]=['ic'=>'cart','c'=>'#3b82f6','t'=>'Đơn hàng mới '.$o['code'],'s'=>$nm,'time'=>$o['created_at']];
}
foreach(array_slice($lowStockProducts??[],0,2) as $lp){ $notif[]=['ic'=>'warn','c'=>'#f59e0b','t'=>'Sắp hết hàng: '.mb_substr($lp['name'],0,28),'s'=>'Chỉ còn '.$lp['stock'].' sản phẩm','time'=>null]; }
$niSvg=['cart'=>'<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>','warn'=>'<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>','check'=>'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>','x'=>'<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>','back'=>'<polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>'];
$maxSold=1; foreach(($topProducts??[]) as $tp){ if(($tp['sold']??0)>$maxSold)$maxSold=$tp['sold']; }
?>
<style>
.db-wrap{--card-r:14px;width:100%;max-width:100%;box-sizing:border-box;overflow-x:hidden}
.db-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:18px;width:100%;box-sizing:border-box}
.db-card{background:#fff;border-radius:var(--card-r);padding:13px 14px;box-shadow:0 1px 4px rgba(20,40,80,.06);border:1px solid #eef1f6;display:flex;align-items:flex-start;gap:11px;min-width:0;box-sizing:border-box}
.db-card .ic{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.db-card .ic svg{width:21px;height:21px;stroke:#fff;fill:none;stroke-width:2}
.db-body{min-width:0;flex:1}
.db-lbl{font-size:11.5px;color:#7a869a;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.db-num{font-size:17px;font-weight:800;color:#16243f;line-height:1.2;margin-bottom:1px;white-space:nowrap}
.db-delta{font-size:11px;font-weight:700;white-space:nowrap}
.db-up{color:#16a34a}.db-down{color:#e23744}.db-sub{font-size:10.5px;color:#9aa5b5}
.db-row{display:grid;gap:16px;margin-bottom:18px}
.db-r-top{grid-template-columns:1.7fr 1fr 1fr}
.db-r-mid{grid-template-columns:1fr 1.55fr;align-items:start}
@media(max-width:1150px){.db-r-top,.db-r-mid{grid-template-columns:1fr}}
.db-panel{background:#fff;border-radius:var(--card-r);padding:18px;box-shadow:0 1px 4px rgba(20,40,80,.06);border:1px solid #eef1f6}
.db-ph{margin:0 0 14px;font-size:15px;color:#16243f;font-weight:700;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
.db-ph a.lnk{font-size:12px;font-weight:600;color:#3b82f6;text-decoration:none}
.db-pills{display:flex;gap:4px;background:#f1f4f9;border-radius:8px;padding:3px}
.db-pills a{font-size:11.5px;font-weight:600;color:#6b7a99;text-decoration:none;padding:5px 10px;border-radius:6px;white-space:nowrap;cursor:pointer}
.db-pills a.on{background:#16243f;color:#fff}
.db-legend{list-style:none;margin:14px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
.db-legend li{display:flex;align-items:center;justify-content:space-between;font-size:12.5px;color:#5a6679}
.db-legend .dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:8px}
.db-legend b{color:#16243f}
.db-notif{display:flex;flex-direction:column;gap:2px}
.db-notif .it{display:flex;gap:11px;padding:10px 4px;border-bottom:1px solid #f2f4f8}
.db-notif .it:last-child{border-bottom:none}
.db-notif .nic{width:34px;height:34px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.db-notif .nic svg{width:17px;height:17px;stroke:#fff;fill:none;stroke-width:2}
.db-notif .tt{font-size:12.8px;font-weight:600;color:#26334d;line-height:1.3}
.db-notif .ss{font-size:11.5px;color:#9aa5b5;margin-top:1px}
.db-notif .tm{font-size:11px;color:#b3bccb;white-space:nowrap;margin-left:auto}
.db-top{display:flex;flex-direction:column;gap:13px}
.db-top .row{display:flex;align-items:center;gap:11px}
.db-top .rk{width:22px;height:22px;border-radius:6px;background:#eef2f9;color:#5a6679;font-weight:800;font-size:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.db-top .rk.r1{background:#fff4d6;color:#c9972c}.db-top .rk.r2{background:#eef2f9;color:#64748b}.db-top .rk.r3{background:#fde8d7;color:#d2691e}
.db-top .th{width:40px;height:40px;border-radius:8px;object-fit:cover;background:#f1f4f9;flex-shrink:0}
.db-top .nm{font-size:12.8px;font-weight:600;color:#26334d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.db-top .sk{font-size:11px;color:#9aa5b5}
.db-top .bar{height:5px;border-radius:3px;background:#eef2f9;margin-top:5px;overflow:hidden}
.db-top .bar i{display:block;height:100%;background:linear-gradient(90deg,#3b82f6,#1a3258);border-radius:3px}
.db-top .sold{text-align:right;flex-shrink:0}
.db-top .sold b{font-size:14px;color:#16243f;font-weight:800}
.db-top .sold span{font-size:10.5px;color:#9aa5b5;display:block}
.db-mini{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.db-mini .m{background:#f8fafc;border:1px solid #eef1f6;border-radius:10px;padding:12px}
.db-mini .ml{font-size:11px;color:#7a869a;display:flex;align-items:center;gap:6px;margin-bottom:6px}
.db-mini .mv{font-size:16px;font-weight:800;color:#16243f;white-space:nowrap}
.db-mini .md{font-size:11px;font-weight:700;margin-top:2px}
.db-stbl{width:100%;border-collapse:collapse;font-size:13px}
.db-stbl th{text-align:left;font-size:11px;color:#9aa5b5;font-weight:700;text-transform:uppercase;letter-spacing:.4px;padding:8px 10px;border-bottom:1px solid #eef1f6}
.db-stbl td{padding:9px 10px;border-bottom:1px solid #f4f6fa;color:#3a465c}
.db-warn-badge{background:#fff1f0;color:#e23744;font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.db-ds{font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;display:inline-block}
.db-act a{color:#94a3b8;display:inline-flex;padding:4px;transition:color .18s ease,transform .18s ease}
.db-act a:hover{color:#1a3258;transform:scale(1.18)}
/* ── Hiệu ứng (animations) ── */
.db-card,.db-panel{transition:transform .24s cubic-bezier(.22,.61,.36,1),box-shadow .24s ease}
.db-card:hover{transform:translateY(-5px);box-shadow:0 12px 28px rgba(20,40,80,.13)}
.db-panel:hover{box-shadow:0 10px 26px rgba(20,40,80,.10)}
.db-card .ic{transition:transform .26s cubic-bezier(.34,1.56,.64,1)}
.db-card:hover .ic{transform:scale(1.1) rotate(-5deg)}
.db-mini .m{transition:background .2s ease,transform .2s ease,box-shadow .2s ease}
.db-mini .m:hover{background:#eef4ff;transform:translateY(-2px);box-shadow:0 4px 12px rgba(20,40,80,.08)}
.db-notif .it{transition:background .18s ease,transform .18s ease;border-radius:8px}
.db-notif .it:hover{background:#f6f9fd;transform:translateX(2px)}
.db-top .row{transition:transform .18s ease}
.db-top .row:hover{transform:translateX(3px)}
.db-stbl tbody tr{transition:background .15s ease}
.db-stbl tbody tr:hover{background:#f8fafd}
.db-pills a{transition:background .18s ease,color .18s ease}
@keyframes dbFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes dbBarGrow{from{transform:scaleX(0)}to{transform:scaleX(1)}}
.db-card,.db-panel{animation:dbFadeUp .55s cubic-bezier(.22,.61,.36,1) backwards}
.db-cards .db-card:nth-child(1){animation-delay:.03s}
.db-cards .db-card:nth-child(2){animation-delay:.09s}
.db-cards .db-card:nth-child(3){animation-delay:.15s}
.db-cards .db-card:nth-child(4){animation-delay:.21s}
.db-cards .db-card:nth-child(5){animation-delay:.27s}
.db-cards .db-card:nth-child(6){animation-delay:.33s}
.db-row .db-panel:nth-child(1){animation-delay:.12s}
.db-row .db-panel:nth-child(2){animation-delay:.20s}
.db-row .db-panel:nth-child(3){animation-delay:.28s}
.db-top .bar i{transform-origin:left center;animation:dbBarGrow .9s .4s cubic-bezier(.22,.61,.36,1) backwards}
@media (prefers-reduced-motion:reduce){.db-card,.db-panel,.db-top .bar i{animation:none}}
</style>

<div class="db-wrap">
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
  <h1 style="margin:0">Tổng quan</h1>
</div>

<!-- ROW A: stat cards -->
<?php
$icPaths=[
 'money'=>'<line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>',
 'doc'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline>',
 'clock'=>'<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>',
 'truck'=>'<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>',
 'xc'=>'<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>',
 'user'=>'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
];
$cards=[
 ['Doanh thu hôm nay', vnd($kpis['today_revenue']??0), '#3b82f6', 'money', dbDelta($pcts['rev_today']??null), 'so với hôm qua'],
 ['Tổng đơn hôm nay', numFmt($kpis['orders_today']??0).' đơn', '#22c55e', 'doc', dbDelta($pcts['orders_today']??null), 'so với hôm qua'],
 ['Đơn chờ xử lý', numFmt($kpis['orders_pending']??0).' đơn', '#f59e0b', 'clock', '', 'đang chờ + tiếp nhận'],
 ['Đơn đang giao', numFmt($kpis['orders_shipping']??0).' đơn', '#8b5cf6', 'truck', '', 'đang giao + đã giao'],
 ['Đơn hoàn / hủy', numFmt($kpis['orders_cancelled']??0).' đơn', '#ef4444', 'xc', '', 'hủy + trả hàng'],
 ['Khách hàng mới', numFmt($kpis['newcust_month']??0), '#14b8a6', 'user', dbDelta($pcts['newcust']??null), 'tháng này'],
];
?>
<div class="db-cards">
  <?php foreach($cards as $cd): ?>
  <div class="db-card">
    <div class="ic" style="background:<?= $cd[2] ?>"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><?= $icPaths[$cd[3]] ?></svg></div>
    <div class="db-body">
      <div class="db-lbl"><?= $cd[0] ?></div>
      <div class="db-num"><?= $cd[1] ?></div>
      <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $cd[4] ?> <span class="db-sub"><?= $cd[5] ?></span></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ROW B: revenue chart | status donut | notifications -->
<div class="db-row db-r-top">
  <div class="db-panel">
    <div class="db-ph">Doanh thu
      <span style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-left:auto">
        <span class="db-pills" id="dbTypePills">
          <a data-t="line" class="on" onclick="dbSetType('line')">Đường</a>
          <a data-t="bar" onclick="dbSetType('bar')">Cột</a>
          <a data-t="doughnut" onclick="dbSetType('doughnut')">Tròn</a>
        </span>
        <span class="db-pills">
          <a href="/admin?period=week" class="<?= ($period==='week')?'on':'' ?>">7 ngày</a>
          <a href="/admin?period=month" class="<?= ($period==='month'||!in_array($period,['week','year','custom']))?'on':'' ?>">30 ngày</a>
          <a href="/admin?period=year" class="<?= ($period==='year')?'on':'' ?>">1 năm</a>
          <a onclick="var d=document.getElementById('dbDateRange');d.style.display=(d.style.display==='flex'?'none':'flex')" class="<?= ($period==='custom')?'on':'' ?>">Tùy chọn</a>
        </span>
      </span>
    </div>
    <form method="get" action="/admin" id="dbDateRange" style="display:<?= ($period==='custom')?'flex':'none' ?>;gap:8px;align-items:center;flex-wrap:wrap;margin:0 0 12px;padding:10px 12px;background:#f8fafc;border:1px solid #eef1f6;border-radius:10px">
      <input type="hidden" name="period" value="custom">
      <label style="font-size:12px;color:#7a869a;font-weight:600">Từ ngày</label>
      <input type="date" name="date_from" value="<?= e($dateFrom??'') ?>" class="js-date" required style="height:34px;border:1px solid #d7deea;border-radius:7px;padding:0 10px;font-size:13px">
      <label style="font-size:12px;color:#7a869a;font-weight:600">đến ngày</label>
      <input type="date" name="date_to" value="<?= e($dateTo??'') ?>" class="js-date" required style="height:34px;border:1px solid #d7deea;border-radius:7px;padding:0 10px;font-size:13px">
      <button type="submit" style="height:34px;background:#16243f;color:#fff;border:none;border-radius:7px;padding:0 18px;font-size:13px;font-weight:600;cursor:pointer">Lọc</button>
    </form>
    <div style="height:300px;position:relative"><canvas id="revChart"></canvas></div>
  </div>

  <div class="db-panel">
    <div class="db-ph">Tỷ lệ trạng thái đơn hàng</div>
    <div id="statusHolder" style="height:190px;position:relative"><canvas id="statusChart"></canvas></div>
    <ul class="db-legend">
      <?php foreach(($statusBuckets??[]) as $k=>$v): $pc=$donutTotal>0?round($v['c']/$donutTotal*100,1):0; ?>
      <li><span><span class="dot" style="background:<?= $v['color'] ?>"></span><?= e($k) ?></span><span><b><?= $v['c'] ?></b> (<?= $pc ?>%)</span></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="db-panel">
    <div class="db-ph">Thông báo <a class="lnk" href="/admin/orders">Xem tất cả</a></div>
    <div class="db-notif">
      <?php if(empty($notif)): ?><div class="db-sub" style="padding:16px 0">Chưa có thông báo.</div><?php endif; ?>
      <?php foreach($notif as $n): ?>
      <div class="it">
        <div class="nic" style="background:<?= $n['c'] ?>"><svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><?= $niSvg[$n['ic']] ?? '' ?></svg></div>
        <div><div class="tt"><?= e($n['t']) ?></div><div class="ss"><?= e($n['s']) ?></div></div>
        <?php if($n['time']): ?><div class="tm"><?= agoVN($n['time'], true) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ROW C: top products (nhỏ) | business overview (lớn) -->
<div class="db-row db-r-mid">
  <div class="db-panel">
    <div class="db-ph">Top sản phẩm bán chạy <a class="lnk" href="/admin/products">Xem tất cả</a></div>
    <div class="db-top">
      <?php if(empty($topProducts)): ?><div class="db-sub" style="padding:12px 0">Chưa có dữ liệu.</div><?php endif; ?>
      <?php foreach(($topProducts??[]) as $i=>$tp): $w=$maxSold>0?round(($tp['sold']/$maxSold)*100):0; ?>
      <div class="row">
        <div class="rk r<?= $i+1 ?>"><?= $i+1 ?></div>
        <?php if(!empty($tp['img'])): ?><img class="th" src="/uploads/products/<?= e($tp['img']) ?>" onerror="this.style.visibility='hidden'"><?php else: ?><div class="th"></div><?php endif; ?>
        <div style="flex:1;min-width:0">
          <div class="nm"><?= e($tp['name']) ?></div>
          <div class="sk"><?= e($tp['sku']??'') ?></div>
          <div class="bar"><i style="width:<?= max(4,$w) ?>%"></i></div>
        </div>
        <div class="sold"><b><?= numFmt($tp['sold']) ?></b><span>đã bán</span></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="db-panel">
    <div class="db-ph">Tổng quan kinh doanh <span class="db-sub">Tháng này</span></div>
    <div class="db-mini">
      <div class="m"><div class="ml">Doanh thu</div><div class="mv"><?= vnd($kpis['month_revenue']??0) ?></div><div class="md <?= ($pcts['month_rev']??0)>=0?'db-up':'db-down' ?>"><?= (($pcts['month_rev']??0)>=0?'▲ ':'▼ ').number_format(abs($pcts['month_rev']??0),1) ?>%</div></div>
      <div class="m"><div class="ml">Lợi nhuận</div><div class="mv" style="color:<?= ($kpis['monthly_profit']??0)>=0?'#16a34a':'#e23744' ?>"><?= vnd($kpis['monthly_profit']??0) ?></div></div>
      <div class="m"><div class="ml">Tổng đơn</div><div class="mv"><?= numFmt($kpis['orders_month']??0) ?> đơn</div><div class="md <?= ($pcts['orders_month']??0)>=0?'db-up':'db-down' ?>"><?= (($pcts['orders_month']??0)>=0?'▲ ':'▼ ').number_format(abs($pcts['orders_month']??0),1) ?>%</div></div>
      <div class="m"><div class="ml">Tỷ lệ hoàn thành</div><div class="mv"><?= number_format($kpis['completion_rate']??0,1) ?>%</div></div>
      <div class="m"><div class="ml">Giá trị đơn TB</div><div class="mv"><?= vnd($kpis['aov']??0) ?></div></div>
      <div class="m"><div class="ml">Khách hàng mới</div><div class="mv"><?= numFmt($kpis['newcust_month']??0) ?></div></div>
    </div>
  </div>
</div>

<!-- ROW D: latest orders -->
<div class="db-panel">
  <div class="db-ph">Đơn hàng mới nhất <a class="lnk" href="/admin/orders">Xem tất cả</a></div>
  <div style="overflow-x:auto">
  <table class="db-stbl">
    <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Giá trị đơn</th><th>Thanh toán</th><th>Trạng thái</th><th>Thời gian</th><th style="text-align:center">Thao tác</th></tr></thead>
    <tbody>
      <?php
      $dsMap=['pending'=>['Đang chờ','#fff7e6','#d48806'],'received'=>['Tiếp nhận','#e6f4ff','#1677ff'],'delivering'=>['Đang giao','#f3e8ff','#9333ea'],'delivered'=>['Đã giao','#e7f8ee','#16a34a'],'completed'=>['Đã hoàn thành','#e7f8ee','#16a34a'],'cancelled'=>['Đã hủy','#fff1f0','#e23744'],'returned'=>['Đã trả hàng','#f1f3f6','#64748b']];
      $psMap=['paid'=>['Đã thanh toán','#e7f8ee','#16a34a'],'unpaid'=>['Chưa thanh toán','#fff7e6','#d48806'],'partial_paid'=>['TT một phần','#e6f4ff','#1677ff'],'refunded'=>['Đã hoàn tiền','#f1f3f6','#64748b'],'pending_refund'=>['Chưa hoàn tiền','#fff7e6','#d48806']];
      $pmMap=['bank_transfer'=>'Chuyển khoản','bank'=>'Chuyển khoản','full_prepay'=>'Chuyển khoản','cod'=>'COD','momo'=>'MoMo'];
      ?>
      <?php if(empty($recentOrders)): ?><tr><td colspan="8" class="db-sub" style="padding:18px;text-align:center">Chưa có đơn hàng.</td></tr><?php endif; ?>
      <?php foreach(($recentOrders??[]) as $o): $ds=$dsMap[$o['delivery_status']??'']??[($o['delivery_status']?:'—'),'#f1f3f6','#64748b']; $ps=$psMap[$o['payment_status']??'']??[($o['payment_status']?:'—'),'#f1f3f6','#64748b']; ?>
      <tr>
        <td><a href="/admin/orders/<?= $o['id'] ?>" style="color:#1677ff;font-weight:700;text-decoration:none"><?= e($o['code']) ?></a></td>
        <td style="font-weight:600;color:#26334d"><?= e($o['full_name']?:($o['shipping_full_name']??'Khách lẻ')) ?></td>
        <td><?= e($o['shipping_phone']??'') ?></td>
        <td style="font-weight:700;color:#16243f"><?= vnd($o['grand_total']) ?></td>
        <td><?= e($pmMap[strtolower($o['payment_method']??'')]??($o['payment_method']??'')) ?> · <span class="db-ds" style="background:<?= $ps[1] ?>;color:<?= $ps[2] ?>"><?= e($ps[0]) ?></span></td>
        <td><span class="db-ds" style="background:<?= $ds[1] ?>;color:<?= $ds[2] ?>"><?= e($ds[0]) ?></span></td>
        <td class="db-sub"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
        <td style="text-align:center" class="db-act">
          <a href="/admin/orders/<?= $o['id'] ?>" title="Xem chi tiết"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php if(($totalPages??1) > 1): ?>
  <div class="pagination" style="display:flex;gap:6px;justify-content:center;margin-top:16px">
    <?php require_once __DIR__.'/../partials/pagination.php'; renderPagination($page, $totalPages, '/admin', []); ?>
  </div>
  <?php endif; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function fmtVND(n){ if(!n&&n!==0) return '0 ₫'; return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.')+' ₫'; }
if(window.Chart){ Chart.defaults.animation.duration=1400; Chart.defaults.animation.easing='easeOutQuart'; }
// Revenue chart with 3 types (line / bar / doughnut)
var dbRaw = <?= json_encode($chartData ?? []) ?>;
var dbLabels = dbRaw.map(function(r){return r.label;});
var dbVals = dbRaw.map(function(r){return parseInt(r.val)||0;});
var dbQty = dbRaw.map(function(r){return parseInt(r.qty)||0;});
var dbProfit = dbRaw.map(function(r){return parseInt(r.profit)||0;});
var dbPie = ['#1a3258','#c9972c','#3b82f6','#22c55e','#ef4444','#8b5cf6','#f59e0b','#06b6d4','#e67e22','#16a085','#d35400','#8e44ad','#2980b9','#c0392b'];
var dbChart=null, dbType='line';
function dbBuild(){
  var ctx=document.getElementById('revChart'); if(!ctx) return;
  if(dbChart) dbChart.destroy();
  if(dbType==='doughnut'){
    dbChart=new Chart(ctx,{type:'doughnut',data:{labels:dbLabels,datasets:[{data:dbVals,backgroundColor:dbPie.slice(0,Math.max(1,dbVals.length)),borderWidth:2,borderColor:'#fff'}]},
      options:{responsive:true,maintainAspectRatio:false,animation:{animateRotate:true,animateScale:true,duration:1100,easing:'easeOutQuart'},plugins:{legend:{position:'right',labels:{font:{size:10},padding:6,boxWidth:12}},tooltip:{backgroundColor:'rgba(22,36,63,.96)',padding:12,cornerRadius:8,callbacks:{
        afterTitle:function(it){return 'Đã bán: '+(dbQty[it[0].dataIndex]||0)+' sp';},
        label:function(c){var t=c.dataset.data.reduce(function(a,b){return a+b;},0);var p=t>0?((c.parsed/t)*100).toFixed(1):0;return 'Doanh thu: '+fmtVND(c.parsed)+' ('+p+'%)';},
        afterLabel:function(c){return 'Lợi nhuận: '+fmtVND(dbProfit[c.dataIndex]||0);}}}}}});
  } else {
    dbChart=new Chart(ctx,{type:dbType,data:{labels:dbLabels,datasets:[{label:'Doanh thu',data:dbVals.map(function(){return 0;}),borderColor:'#3b82f6',backgroundColor:dbType==='bar'?'rgba(26,50,88,.78)':'rgba(59,130,246,.10)',borderWidth:dbType==='bar'?0:3,borderRadius:dbType==='bar'?6:0,tension:.35,fill:dbType==='line',pointBackgroundColor:'#1a3258',pointBorderColor:'#fff',pointRadius:3,pointHoverRadius:6}]},
      options:{responsive:true,maintainAspectRatio:false,animation:{duration:1500,easing:'easeOutQuart'},interaction:{intersect:false,mode:'index'},plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(22,36,63,.96)',padding:12,cornerRadius:8,displayColors:false,callbacks:{
        label:function(c){return 'Doanh thu: '+fmtVND(c.parsed.y);},
        afterLabel:function(c){return 'Đã bán: '+(dbQty[c.dataIndex]||0)+' sp · Lợi nhuận: '+fmtVND(dbProfit[c.dataIndex]||0);}}}},
        scales:{y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'},ticks:{callback:function(v){if(v>=1000000)return (v/1000000).toFixed(1)+'M';if(v>=1000)return (v/1000).toFixed(0)+'K';return v;},font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11},maxRotation:45}}}}});
    var _c=dbChart; setTimeout(function(){ if(_c){ _c.data.datasets[0].data=dbVals; _c.update(); } }, 80);
  }
}
function dbSetType(t){ dbType=t; var ps=document.querySelectorAll('#dbTypePills a'); for(var i=0;i<ps.length;i++){ ps[i].classList.toggle('on', ps[i].getAttribute('data-t')===t); } dbBuild(); }
setTimeout(dbBuild, 300); // chờ panel hiện xong rồi mới vẽ để thấy rõ hiệu ứng
// Status donut (re-drawable for auto-update)
window.dbDrawDonut=function(d,l,col,total){
  var holder=document.getElementById('statusHolder'); if(!holder) return;
  var ctx=document.getElementById('statusChart');
  if(!ctx){ holder.innerHTML='<canvas id="statusChart"></canvas>'; ctx=document.getElementById('statusChart'); }
  if(window.__statusChart){ try{window.__statusChart.destroy();}catch(e){} window.__statusChart=null; }
  if(!d.length){ holder.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#9aa5b5;font-size:13px">Chưa có đơn hàng</div>'; return; }
  window.__statusChart=new Chart(ctx,{type:'doughnut',data:{labels:l,datasets:[{data:d,backgroundColor:col,borderWidth:3,borderColor:'#fff'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',animation:{animateRotate:true,animateScale:true,duration:900,easing:'easeOutQuart'},plugins:{legend:{display:false},
      tooltip:{backgroundColor:'rgba(22,36,63,.96)',padding:10,cornerRadius:8,callbacks:{label:function(c){var p=total>0?((c.parsed/total)*100).toFixed(1):0;return c.label+': '+c.parsed+' đơn ('+p+'%)';}}}}},
    plugins:[{id:'ctr',afterDraw:function(ch){var x=ch.getDatasetMeta(0).data[0];if(!x)return;var c=ch.ctx;c.save();c.textAlign='center';c.textBaseline='middle';c.fillStyle='#9aa5b5';c.font='11px sans-serif';c.fillText('Tổng',x.x,x.y-9);c.fillStyle='#16243f';c.font='700 20px sans-serif';c.fillText(total+' đơn',x.x,x.y+9);c.restore();}}]});
};
setTimeout(function(){ dbDrawDonut(<?= json_encode($donutData) ?>, <?= json_encode($donutLabels, JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($donutColors) ?>, <?= (int)$donutTotal ?>); }, 300);

// ===== Tự động cập nhật số liệu (poll /admin mỗi 15s) — không reload trang =====
function dbFlash(el){ if(!el)return; el.style.transition='box-shadow .35s ease'; el.style.boxShadow='0 0 0 3px rgba(59,130,246,.55)'; setTimeout(function(){ el.style.boxShadow=''; },1300); }
function dbComputeSig(root){
  var nums=Array.prototype.map.call(root.querySelectorAll('.db-cards .db-num'),function(e){return e.textContent.trim();}).join('|');
  var leg=(root.querySelector('.db-legend')||{textContent:''}).textContent.replace(/\s+/g,' ').trim();
  var ntf=(root.querySelector('.db-notif')||{textContent:''}).textContent.replace(/\s+/g,' ').trim();
  var bg=((root.getElementById&&root.getElementById('notiBadge'))||{textContent:''}).textContent.trim();
  return nums+'~'+leg+'~'+ntf+'~'+bg;
}
var __dbSig=null, __dbPollId=null;
function dbPoll(){
  if(!document.querySelector('.db-cards')){ if(__dbPollId){clearInterval(__dbPollId);__dbPollId=null;} return; }
  fetch('/admin'+location.search, {headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'})
   .then(function(r){ if(!r.ok) throw 0; return r.text(); })
   .then(function(html){
     var doc=new DOMParser().parseFromString(html,'text/html');
     if(!doc.querySelector('.db-cards')) return;
     var sig=dbComputeSig(doc);
     if(__dbSig===null){ __dbSig=sig; return; }
     if(sig===__dbSig) return;
     __dbSig=sig;
     // cards (số + dòng phụ)
     var cur=document.querySelectorAll('.db-cards .db-card'), nw=doc.querySelectorAll('.db-cards .db-card');
     for(var i=0;i<cur.length&&i<nw.length;i++){
       var cn=cur[i].querySelector('.db-num'), nn=nw[i].querySelector('.db-num');
       if(cn&&nn&&cn.textContent.trim()!==nn.textContent.trim()){ cn.textContent=nn.textContent.trim(); dbFlash(cur[i]); }
       var cs=cur[i].querySelector('.db-body>div:last-child'), ns=nw[i].querySelector('.db-body>div:last-child');
       if(cs&&ns&&cs.innerHTML!==ns.innerHTML){ cs.innerHTML=ns.innerHTML; }
     }
     // legend + donut
     var cleg=document.querySelector('.db-legend'), nleg=doc.querySelector('.db-legend');
     if(cleg&&nleg&&cleg.innerHTML.trim()!==nleg.innerHTML.trim()){
       cleg.innerHTML=nleg.innerHTML;
       var L=[],D=[],C=[],T=0;
       cleg.querySelectorAll('li').forEach(function(li){ var b=li.querySelector('b'); var cnt=b?(parseInt(b.textContent,10)||0):0; T+=cnt; if(cnt>0){ var dot=li.querySelector('.dot'); C.push(dot?dot.style.backgroundColor:'#ccc'); L.push((li.querySelector('span')||{textContent:''}).textContent.trim()); D.push(cnt); } });
       dbDrawDonut(D,L,C,T);
     }
     // thông báo
     var cnf=document.querySelector('.db-notif'), nnf=doc.querySelector('.db-notif');
     if(cnf&&nnf&&cnf.innerHTML.trim()!==nnf.innerHTML.trim()){ cnf.innerHTML=nnf.innerHTML; }
     // badge chuông admin
     var nb=doc.getElementById('notiBadge'), cb=document.getElementById('notiBadge');
     if(nb&&cb){ cb.textContent=nb.textContent; } else if(!nb&&cb){ cb.remove(); }
   }).catch(function(){});
}
setTimeout(function(){ __dbSig=dbComputeSig(document); __dbPollId=setInterval(dbPoll, 15000); }, 1800);
// Count-up effect for the 6 stat-card numbers
(function(){
  function fmt(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
  var els=document.querySelectorAll('.db-num');
  for(var i=0;i<els.length;i++){(function(el){
    var m=el.textContent.trim().match(/^([\d.]+)(.*)$/); if(!m) return;
    var target=parseInt(m[1].replace(/\./g,''),10); if(isNaN(target)||target<=0) return;
    var suffix=m[2], dur=950, start=null;
    function step(ts){ if(!start)start=ts; var p=Math.min((ts-start)/dur,1); var e=1-Math.pow(1-p,3); el.textContent=fmt(Math.floor(e*target))+suffix; if(p<1) requestAnimationFrame(step); else el.textContent=fmt(target)+suffix; }
    requestAnimationFrame(step);
  })(els[i]);}
})();
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
