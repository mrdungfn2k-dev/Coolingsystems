<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="margin:0">Tổng quan hệ thống</h1>
</div>

<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr))">
  <div class="kpi"><div class="num"><?=numFmt($kpis['customers'])?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Khách hàng</div></div>
  <div class="kpi"><div class="num"><?=numFmt($kpis['products'])?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Sản phẩm</div></div>
  <div class="kpi"><div class="num"><?=numFmt($kpis['orders_total'])?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Đơn hàng</div></div>
  <div class="kpi"><div class="num"><?=vnd($kpis['revenue'])?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Tổng doanh thu</div></div>
  <div class="kpi"><div class="num"><?=vnd($kpis['today_revenue'])?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Doanh thu hôm nay</div></div>
  <div class="kpi"><div class="num"><?=vnd($kpis['month_revenue']??0)?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Doanh thu tháng</div></div>
  <div class="kpi"><div class="num" style="color:<?= ($kpis['monthly_profit']??0) >= 0 ? '#27ae60' : '#e74c3c' ?>"><?=vnd($kpis['monthly_profit']??0)?></div><div class="lbl" style="text-transform:uppercase;letter-spacing:1px;font-size:11px">Lợi nhuận tháng</div></div>
</div>

<!-- Stock Alerts -->
<?php if (!empty($lowStockProducts) || !empty($overStockProducts)): ?>
<div style="display:grid;grid-template-columns:1fr;gap:20px;margin-top:24px">
  <?php if (!empty($lowStockProducts)): ?>
  <div class="panel">
    <div class="sec-head"><div class="title"><h2> Tồn kho thấp (<?= count($lowStockProducts) ?>)</h2></div></div>
    <table class="tbl" style="font-size:13px">
      <thead><tr><th>Sản phẩm</th><th style="text-align:center">Tồn kho</th><th style="text-align:center">Tối thiểu</th><th></th></tr></thead>
      <tbody>
      <?php foreach($lowStockProducts as $lp): ?>
      <tr style="background:#fff5f5">
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <a href="/admin/products/<?= $lp['id'] ?>/edit" class="text-navy fw-600"><?= e(mb_substr($lp['name'],0,40)) ?></a>
          <?php if($lp['sku']): ?><div class="fs-12 text-muted"><?= e($lp['sku']) ?></div><?php endif; ?>
        </td>
        <td style="text-align:center"><span style="color:#e74c3c;font-weight:700;font-size:16px"><?= $lp['stock'] ?></span></td>
        <td style="text-align:center;color:#888"><?= $lp['min_stock'] ?></td>
        <td><a href="/admin/products/<?= $lp['id'] ?>/edit" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;font-size:11px;padding:3px 8px;border-radius:4px">Nạp hàng</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if (!empty($overStockProducts)): ?>
  <div class="panel">
    <div class="sec-head"><div class="title"><h2> Tồn kho vượt mức (<?= count($overStockProducts) ?>)</h2></div></div>
    <table class="tbl" style="font-size:13px">
      <thead><tr><th>Sản phẩm</th><th style="text-align:center">Tồn kho</th><th style="text-align:center">Tối đa</th></tr></thead>
      <tbody>
      <?php foreach($overStockProducts as $op): ?>
      <tr style="background:#fffbeb">
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <a href="/admin/products/<?= $op['id'] ?>/edit" class="text-navy fw-600"><?= e(mb_substr($op['name'],0,40)) ?></a>
          <?php if($op['sku']): ?><div class="fs-12 text-muted"><?= e($op['sku']) ?></div><?php endif; ?>
        </td>
        <td style="text-align:center"><span style="color:#f39c12;font-weight:700;font-size:16px"><?= $op['stock'] ?></span></td>
        <td style="text-align:center;color:#888"><?= $op['max_stock'] ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="panel" style="margin-top:24px">
    <div class="sec-head" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;padding-bottom:12px;gap:10px">
        <div class="title"><span class="bar"></span><h2>Biểu đồ doanh thu</h2></div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
            <div style="display:flex;gap:2px;background:#f0f2f7;border-radius:6px;padding:2px">
                <button onclick="switchChartType('line')" id="btn-line" class="chart-type-btn active">Đường</button>
                <button onclick="switchChartType('bar')" id="btn-bar" class="chart-type-btn">Cột</button>
                <button onclick="switchChartType('pie')" id="btn-pie" class="chart-type-btn">Tròn</button>
            </div>
            <form method="get" action="/admin" id="chartFilterForm" style="display:flex;gap:8px;align-items:center">
                <select name="period" class="frm-input" style="width:auto;min-width:120px;height:36px;font-size:13px" onchange="if(this.value!='custom'){document.getElementById('dateRange').style.display='none';this.form.submit();}else{document.getElementById('dateRange').style.display='flex';}">
                    <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>7 Ngày qua</option>
                    <option value="month" <?= ($period === 'month' || ($period === 'custom' && empty($dateFrom))) ? 'selected' : '' ?>>30 Ngày qua</option>
                    <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>1 Năm qua</option>
                    <option value="custom" <?= $period === 'custom' ? 'selected' : '' ?>>Tùy chọn</option>
                </select>
                <div id="dateRange" style="display:<?= $period === 'custom' ? 'flex' : 'none' ?>;gap:6px;align-items:center">
                    <input type="date" name="date_from" class="frm-input" value="<?= e($dateFrom ?? '') ?>" style="height:36px;font-size:13px;width:140px">
                    <span style="color:#888;font-size:12px">→</span>
                    <input type="date" name="date_to" class="frm-input" value="<?= e($dateTo ?? '') ?>" style="height:36px;font-size:13px;width:140px">
                    <button type="submit" class="btn btn-navy btn-sm" style="height:36px;font-size:12px;padding:0 14px;white-space:nowrap">Lọc</button>
                </div>
            </form>
        </div>
    </div>
    <div id="chartContainer" style="height:340px;position:relative">
        <canvas id="revChart"></canvas>
    </div>
</div>
<style>
.chart-type-btn{min-width:50px;height:32px;border:none;background:transparent;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600;color:#6b7a99;display:flex;align-items:center;justify-content:center;transition:all .2s;padding:0 10px}
.chart-type-btn.active{background:#1a3258;color:#fff;box-shadow:0 2px 6px rgba(26,50,88,.3)}
.chart-type-btn:hover{background:#e0e4ed;color:#1a3258}
.chart-type-btn.active:hover{background:#1a3258;color:#fff}
</style>


<div class="panel" style="margin-top:24px">
    <div class="sec-head">
        <div class="title"><span class="bar"></span><h2>Danh sách đơn hàng mới</h2></div>
    </div>
    <table class="tbl" style="margin-bottom:12px">
        <thead>
            <tr><th>Mã</th><th>Khách</th><th>Tổng</th><th>PTTT</th><th>TT</th><th>Ngày</th></tr>
        </thead>
        <tbody>
            <?php foreach($recentOrders as $o):?>
            <tr>
                <td><a href="/admin/orders/<?= $o['id'] ?>" class="text-navy" style="font-weight:600"><?=e($o['code'])?></a></td>
                <td><?=e($o['full_name'])?></td>
                <td style="color:#e74c3c;font-weight:600"><?=vnd($o['grand_total'])?></td>
                <td class="fs-12" style="text-transform:uppercase"><?=e($o['payment_method'])?></td>
                <td><span class="badge-status <?=e($o['payment_status'])?>"><?=e($o['payment_status'])?></span></td>
                <td class="fs-12"><?=relTime($o['created_at'])?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    
    <!-- Phân trang Đơn hàng Dashboard -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="display:flex;gap:6px;justify-content:center;margin-top:16px">
        <?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, '/admin', []);
?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var revChart = null;
var currentType = 'line';
var rawData = <?= json_encode($chartData) ?>;
var labels = rawData.map(function(r){ return r.label; });
var data = rawData.map(function(r){ return parseInt(r.val) || 0; });
var qtyData = rawData.map(function(r){ return parseInt(r.qty) || 0; });
var profitData = rawData.map(function(r){ return parseInt(r.profit) || 0; });

var pieColors = ['#1a3258','#c9972c','#27ae60','#e74c3c','#3498db','#9b59b6','#f39c12','#1abc9c','#e67e22','#2c3e50','#16a085','#d35400','#8e44ad','#2980b9','#c0392b','#7f8c8d','#f1c40f','#e91e63','#00bcd4','#ff5722','#795548','#607d8b','#4caf50','#ff9800','#673ab7','#03a9f4','#009688','#ff5252','#448aff','#69f0ae','#ffd740'];

function formatVND(n) {
  if (!n && n !== 0) return '0 ₫';
  return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';
}

function buildChart(type) {
  var ctx = document.getElementById('revChart');
  if (!ctx) return;
  if (revChart) revChart.destroy();
  
  var tooltipCallbacks = {
    title: function(items) { return items[0].label; },
    afterTitle: function(items) {
      var idx = items[0].dataIndex;
      return 'Đã bán: ' + qtyData[idx] + ' sản phẩm';
    },
    label: function(ctx2) { return 'Doanh thu: ' + formatVND(ctx2.parsed.y || ctx2.parsed); },
    afterLabel: function(items) {
      var idx = items.dataIndex;
      return 'Lợi nhuận: ' + formatVND(profitData[idx]);
    }
  };
  
  var config = {};
  if (type === 'pie' || type === 'doughnut') {
    config = {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: pieColors.slice(0, data.length),
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right', labels: { font: { size: 11 }, padding: 8 } },
          tooltip: {
            backgroundColor: 'rgba(26,50,88,0.95)',
            titleFont: { size: 13, weight: '600' },
            bodyFont: { size: 13 },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              title: function(items) { return items[0].label; },
              beforeLabel: function(ctx2) {
                var idx = ctx2.dataIndex;
                return 'Đã bán: ' + qtyData[idx] + ' sản phẩm';
              },
              label: function(ctx2) {
                var total = ctx2.dataset.data.reduce(function(a,b){return a+b;},0);
                var pct = total > 0 ? ((ctx2.parsed / total) * 100).toFixed(1) : 0;
                return 'Doanh thu: ' + formatVND(ctx2.parsed) + ' (' + pct + '%)';
              },
              afterLabel: function(ctx2) {
                var idx = ctx2.dataIndex;
                return 'Lợi nhuận: ' + formatVND(profitData[idx]);
              }
            }
          }
        }
      }
    };
  } else {
    config = {
      type: type,
      data: {
        labels: labels,
        datasets: [{
          label: 'Doanh thu (VNĐ)',
          data: data,
          borderColor: '#c9972c',
          backgroundColor: type === 'bar' ? 'rgba(26,50,88,0.75)' : 'rgba(201,151,44,0.1)',
          borderWidth: type === 'bar' ? 0 : 3,
          tension: 0.3,
          fill: type === 'line',
          borderRadius: type === 'bar' ? 6 : 0,
          pointBackgroundColor: '#1a3258',
          pointBorderColor: '#fff',
          pointRadius: 4,
          pointHoverRadius: 7,
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: '#c9972c',
          pointHoverBorderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(26,50,88,0.95)',
            titleFont: { size: 13, weight: '600' },
            bodyFont: { size: 13 },
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
            callbacks: tooltipCallbacks
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: {
              callback: function(v) {
                if (v >= 1000000) return (v/1000000).toFixed(1) + 'M';
                if (v >= 1000) return (v/1000).toFixed(0) + 'K';
                return v;
              },
              font: { size: 11 }
            }
          },
          x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 45 } }
        }
      }
    };
  }
  
  revChart = new Chart(ctx, config);
}

function switchChartType(type) {
  currentType = type;
  document.querySelectorAll('.chart-type-btn').forEach(function(b){ b.classList.remove('active'); });
  document.getElementById('btn-' + type).classList.add('active');
  buildChart(type);
}

document.addEventListener('DOMContentLoaded', function() { buildChart('line'); });
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
