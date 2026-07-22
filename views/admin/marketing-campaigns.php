<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Chiến dịch Marketing Automation</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tạo chiến dịch gửi Email/SMS/Zalo tự động cho từng nhóm khách hàng mục tiêu.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <a href="/admin/marketing/campaigns/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
    <button onclick="document.getElementById('newCampaignModal').style.display='flex'" class="btn btn-navy">+ Tạo chiến dịch mới</button>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.mkt-table{width:100%;border-collapse:collapse;background:#fff}
.mkt-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.mkt-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.mkt-badge{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700}
.mkt-badge-draft{background:#f3f4f6;color:#374151}
.mkt-badge-sent{background:#dcfce7;color:#166534}
</style>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="mkt-table">
  <thead>
    <tr>
      <th>Tên chiến dịch</th>
      <th>Kênh truyền thông</th>
      <th>Tiêu đề Email / Tin nhắn</th>
      <th>Số tin đã gửi</th>
      <th>Trạng thái</th>
      <th>Ngày tạo</th>
      <th>Thao tác</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($campaigns as $c): ?>
    <tr>
      <td><strong style="color:#1a3258"><?= e($c['name']) ?></strong></td>
      <td>
        <span style="font-weight:700;font-size:12px;color:#0284c7;text-transform:uppercase">
          <?= e($c['type']) ?>
        </span>
      </td>
      <td style="font-size:12px;color:#4b5563"><?= e($c['subject'] ?: '—') ?></td>
      <td style="font-weight:700;color:#16a34a"><?= number_format((int)($c['sent_count'] ?? 0)) ?> lượt</td>
      <td>
        <?php 
          $st = $c['status'] ?? 'draft';
          $lbl = ['draft'=>'Nháp','sent'=>'Đã gửi thành công'][$st] ?? $st;
        ?>
        <span class="mkt-badge mkt-badge-<?= e($st) ?>"><?= e($lbl) ?></span>
      </td>
      <td style="font-size:12px;color:#9ca3af"><?= !empty($c['created_at']) ? date('d/m/Y H:i', strtotime($c['created_at'])) : '—' ?></td>
      <td>
        <?php if($st === 'draft'): ?>
        <form method="post" action="/admin/marketing/campaigns/<?= (int)$c['id'] ?>/send" style="display:inline" onsubmit="return confirm('Xác nhận KÍCH HOẠT GỬI chiến dịch này?')">
          <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
          <button type="submit" class="btn btn-navy" style="padding:3px 10px;font-size:11px">🚀 Kích hoạt gửi</button>
        </form>
        <?php else: ?>
        <span style="color:#16a34a;font-size:12px;font-weight:700">✔ Đã gửi</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$campaigns): ?>
    <tr><td colspan="7" style="padding:30px;text-align:center;color:#9ca3af">Chưa có chiến dịch Marketing nào. Bấm nút "+ Tạo chiến dịch mới" để bắt đầu.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/marketing/campaigns?page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/marketing/campaigns?page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<div id="newCampaignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/marketing/campaigns" style="background:#fff;padding:24px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 16px;color:#1a3258">Tạo chiến dịch Marketing mới</h3>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tên chiến dịch <span style="color:#e11d48">*</span></label>
      <input type="text" name="name" required placeholder="Ví dụ: Ưu đãi bảo dưỡng lốc lạnh tháng 7, Giảm giá voucher..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Kênh truyền thông</label>
      <select name="type" style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px;background:#fff">
        <option value="email">Email Marketing</option>
        <option value="sms">Tin nhắn SMS</option>
        <option value="zalo">Zalo ZNS</option>
      </select>
    </div>

    <div style="margin-bottom:12px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Tiêu đề thông báo <span style="color:#e11d48">*</span></label>
      <input type="text" name="subject" required placeholder="Tiêu đề Email hoặc SMS..." style="width:100%;height:38px;border:1px solid #cbd5e1;border-radius:6px;padding:0 10px;font-size:13px">
    </div>

    <div style="margin-bottom:20px">
      <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px">Nội dung tin nhắn / Email</label>
      <textarea name="content" rows="4" placeholder="Nội dung khuyến mãi dặn dò khách hàng..." style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:8px;font-size:13px"></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('newCampaignModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Lưu chiến dịch</button>
    </div>
  </form>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
