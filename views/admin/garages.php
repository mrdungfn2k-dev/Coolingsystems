<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Trung tâm Xét duyệt Hồ sơ B2B (Đại lý &amp; Gara)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Thẩm định hồ sơ pháp lý (Bảng hiệu, GPKD, 3+ ảnh thực tế), duyệt phân quyền Đại lý phân phối &amp; Gara mua buôn.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importGaragesModal').style.display='flex'" class="btn btn-navy btn-sm" style="background:#0b1d3a;color:#fff;font-weight:700">Nhập CSV</button>
    <a href="/admin/garages/export-csv" class="btn btn-navy btn-sm" style="background:#0b1d3a;color:#fff;font-weight:700">Xuất CSV</a>
  </div>
</div>

<div id="importGaragesModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/garages/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập dữ liệu Garage từ file CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>User_ID, Brand_Name, Model_Name, Year, Trim, License_Plate</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importGaragesModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên &amp; Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.tab-nav{display:flex;gap:8px;border-bottom:2px solid #e2e8f0;margin-bottom:20px}
.tab-nav a{padding:10px 20px;font-weight:700;font-size:14px;color:#64748b;text-decoration:none;border-bottom:3px solid transparent;margin-bottom:-2px;display:flex;align-items:center;gap:8px}
.tab-nav a.active{color:#0b1d3a;border-bottom-color:#0b1d3a}
.tab-badge{background:#0b1d3a;color:#fff;font-size:11px;font-weight:800;padding:2px 8px;border-radius:12px}
.tab-badge.gold{background:#0b1d3a}
.garage-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}
.garage-kpi{border:1px solid #e3e9f1;background:#fff;padding:16px;border-radius:8px}
.garage-kpi b{display:block;font-size:22px;font-weight:800;color:#0b1d3a}
.garage-kpi span{font-size:12.5px;color:#64748b;font-weight:600}
.garage-table{width:100%;border-collapse:collapse;background:#fff}
.garage-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.garage-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.garage-table tr:hover td{background:#f9fbff}
.badge-status{display:inline-block;padding:4px 10px;border-radius:12px;font-size:11.5px;font-weight:700;text-transform:uppercase}
.badge-pending{background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd}
.badge-approved{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
.badge-rejected{background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}
.thumb-box{width:60px;height:45px;border-radius:4px;overflow:hidden;border:1px solid #cbd5e1;background:#f8fafc;display:flex;align-items:center;justify-content:center;cursor:pointer}
.thumb-box img{width:100%;height:100%;object-fit:cover}
.btn-action-detail{background:#0b1d3a;color:#fff;border:none;border-radius:6px;font-weight:700;font-size:12px;padding:6px 14px;cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
.btn-action-detail:hover{background:#1a3258;transform:translateY(-1px)}
.btn-action-approve{background:#16a34a;color:#fff;border-radius:6px;font-weight:700;font-size:12px;padding:6px 16px;border:none;cursor:pointer;transition:all 0.2s;box-shadow:0 2px 4px rgba(22,163,74,0.15)}
.btn-action-approve:hover{background:#15803d;transform:translateY(-1px)}
.btn-action-reject{background:#dc2626;color:#fff;border-radius:6px;font-weight:700;font-size:12px;padding:6px 16px;border:none;cursor:pointer;transition:all 0.2s;box-shadow:0 2px 4px rgba(220,38,38,0.15)}
.btn-action-reject:hover{background:#b91c1c;transform:translateY(-1px)}
</style>

<!-- MAIN NAVIGATION TABS -->
<div class="tab-nav">
  <a href="/admin/garages?tab=requests" class="<?= ($tab ?? 'requests') === 'requests' ? 'active' : '' ?>">
    Tất cả Đơn Đăng ký B2B
    <?php if (($pendingRequestsCount ?? 0) > 0): ?>
      <span class="tab-badge gold"><?= $pendingRequestsCount ?> chờ duyệt</span>
    <?php endif; ?>
  </a>
  <a href="/admin/garages?tab=vehicles" class="<?= ($tab ?? '') === 'vehicles' ? 'active' : '' ?>">
    Xe khách hàng lưu sẵn
  </a>
</div>

<?php if (($tab ?? 'requests') === 'requests'): ?>

  <!-- SUB-FILTERS FOR AGENCY VS GARAGE -->
  <div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
    <a href="/admin/garages?tab=requests&reg_type=all<?= !empty($statusFilter) ? '&status='.e($statusFilter) : '' ?>" class="btn" style="font-size:13px; padding:8px 16px; border-radius:8px; font-weight:700; <?= ($regType ?? 'all') === 'all' ? 'background:#0b1d3a; color:#fff; border:none;' : 'background:#fff; color:#0b1d3a; border:1px solid #cbd5e1;' ?>">
      Tất cả Hồ sơ B2B
    </a>
    <a href="/admin/garages?tab=requests&reg_type=agency<?= !empty($statusFilter) ? '&status='.e($statusFilter) : '' ?>" class="btn" style="font-size:13px; padding:8px 16px; border-radius:8px; font-weight:700; <?= ($regType ?? '') === 'agency' ? 'background:#0b1d3a; color:#fff; border:none;' : 'background:#fff; color:#0b1d3a; border:1px solid #cbd5e1;' ?>">
      Hồ sơ Đăng ký Đại lý <?= ($agencyPendingCount ?? 0) > 0 ? '('.$agencyPendingCount.' chờ)' : '' ?>
    </a>
    <a href="/admin/garages?tab=requests&reg_type=garage<?= !empty($statusFilter) ? '&status='.e($statusFilter) : '' ?>" class="btn" style="font-size:13px; padding:8px 16px; border-radius:8px; font-weight:700; <?= ($regType ?? '') === 'garage' ? 'background:#0b1d3a; color:#fff; border:none;' : 'background:#fff; color:#0b1d3a; border:1px solid #cbd5e1;' ?>">
      Hồ sơ Đăng ký Gara <?= ($garagePendingCount ?? 0) > 0 ? '('.$garagePendingCount.' chờ)' : '' ?>
    </a>
  </div>

  <!-- KPI SUMMARY -->
  <div class="garage-kpis">
    <div class="garage-kpi" style="border-left:4px solid #0b1d3a">
      <b style="color:#0b1d3a"><?= number_format($pendingRequestsCount ?? 0) ?></b>
      <span>Hồ sơ đang chờ duyệt</span>
    </div>
    <div class="garage-kpi" style="border-left:4px solid #1a3258">
      <b style="color:#1a3258"><?= number_format($approvedRequestsCount ?? 0) ?></b>
      <span>Hồ sơ đã phê duyệt</span>
    </div>
    <div class="garage-kpi" style="border-left:4px solid #475569">
      <b style="color:#475569"><?= number_format($rejectedRequestsCount ?? 0) ?></b>
      <span>Hồ sơ bị từ chối</span>
    </div>
  </div>

  <!-- SEARCH & STATUS FILTER -->
  <form class="garage-filter" method="get" action="/admin/garages" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:center">
    <input type="hidden" name="tab" value="requests">
    <input type="hidden" name="reg_type" value="<?= e($regType ?? 'all') ?>">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên Đơn vị, chủ Gara/Đại lý, SĐT, MST..." style="min-width:280px;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 12px;font-size:13px">
    
    <div style="display:flex;gap:6px">
      <a href="/admin/garages?tab=requests&reg_type=<?= e($regType ?? 'all') ?>" class="btn" style="font-size:12px;padding:7px 14px;border-radius:6px;font-weight:700;<?= ($statusFilter ?? '') === '' ? 'background:#0b1d3a;color:#fff;border:none' : 'background:#fff;color:#0b1d3a;border:1px solid #cbd5e1' ?>">Tất cả</a>
      <a href="/admin/garages?tab=requests&reg_type=<?= e($regType ?? 'all') ?>&status=pending" class="btn" style="font-size:12px;padding:7px 14px;border-radius:6px;<?= ($statusFilter ?? '') === 'pending' ? 'background:#0b1d3a;color:#fff;font-weight:700;border:none' : 'background:#fff;color:#0b1d3a;border:1px solid #cbd5e1' ?>">Chờ duyệt</a>
      <a href="/admin/garages?tab=requests&reg_type=<?= e($regType ?? 'all') ?>&status=approved" class="btn" style="font-size:12px;padding:7px 14px;border-radius:6px;<?= ($statusFilter ?? '') === 'approved' ? 'background:#0b1d3a;color:#fff;font-weight:700;border:none' : 'background:#fff;color:#0b1d3a;border:1px solid #cbd5e1' ?>">Đã duyệt</a>
      <a href="/admin/garages?tab=requests&reg_type=<?= e($regType ?? 'all') ?>&status=rejected" class="btn" style="font-size:12px;padding:7px 14px;border-radius:6px;<?= ($statusFilter ?? '') === 'rejected' ? 'background:#0b1d3a;color:#fff;font-weight:700;border:none' : 'background:#fff;color:#0b1d3a;border:1px solid #cbd5e1' ?>">Từ chối</a>
    </div>

    <button class="btn btn-navy" type="submit" style="margin-left:auto;height:38px;padding:0 18px;border-radius:6px;font-weight:700;background:#0b1d3a;color:#fff">Lọc kết quả</button>
  </form>

  <!-- REGISTRATIONS TABLE -->
  <div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
    <table class="garage-table">
      <thead>
        <tr>
          <th>#Mã</th>
          <th>Phân Loại</th>
          <th>Tên Đơn Vị / Cửa hàng</th>
          <th>Người đại diện</th>
          <th>SĐT &amp; Email</th>
          <th>Mã số thuế / HKD</th>
          <th>Địa chỉ thực tế</th>
          <th>Ngày gửi</th>
          <th>Trạng thái</th>
          <th style="text-align:center">Thao tác xét duyệt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($requests as $r): 
          $realImgs = json_decode($r['real_images'] ?? '[]', true) ?: [];
          $isAgency = ($r['reg_type'] ?? '') === 'agency';
        ?>
        <tr>
          <td style="color:#9ca3af;font-size:12px">#<?= (int)$r['id'] ?></td>
          <td>
            <?php if ($isAgency): ?>
              <span style="display:inline-block; background:#0b1d3a; color:#fff; font-size:10px; font-weight:800; padding:3px 8px; border-radius:6px; letter-spacing:0.3px;">
                ĐẠI LÝ PHÂN PHỐI
              </span>
            <?php else: ?>
              <span style="display:inline-block; background:#0284c7; color:#fff; font-size:10px; font-weight:800; padding:3px 8px; border-radius:6px; letter-spacing:0.3px;">
                GARA MUA BUÔN
              </span>
            <?php endif; ?>
          </td>
          <td>
            <strong style="color:#0b1d3a;font-size:14px"><?= e($r['name']) ?></strong>
          </td>
          <td style="font-weight:600;color:#334155"><?= e($r['owner_name']) ?></td>
          <td>
            <div style="font-weight:700;color:#0b1d3a"><?= e($r['phone']) ?></div>
            <div style="font-size:11px;color:#64748b"><?= e($r['email']) ?></div>
          </td>
          <td>
            <span style="background:#f1f5f9;color:#0f172a;font-family:monospace;font-weight:700;padding:3px 8px;border-radius:4px;border:1px solid #cbd5e1;font-size:12px"><?= e($r['tax_code']) ?></span>
          </td>
          <td style="color:#475569;max-width:200px;font-size:12.5px"><?= e($r['address']) ?></td>
          <td style="color:#64748b;font-size:12px;white-space:nowrap"><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
              <span class="badge-status badge-pending">Chờ duyệt</span>
            <?php elseif ($r['status'] === 'approved'): ?>
              <span class="badge-status badge-approved">Đã duyệt</span>
            <?php else: ?>
              <span class="badge-status badge-rejected" title="<?= e($r['reject_reason'] ?? '') ?>">Từ chối</span>
              <?php if (!empty($r['reject_reason'])): ?>
                <div style="font-size:11px;color:#dc2626;margin-top:2px;max-width:140px"><?= e($r['reject_reason']) ?></div>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td style="text-align:center;white-space:nowrap">
            <div style="display:flex;gap:6px;justify-content:center">
              <button type="button" onclick="showRegistrationDetail(<?= e(json_encode($r)) ?>)" class="btn-action-detail">Chi tiết</button>
              
              <?php if ($r['status'] === 'pending'): ?>
                <form method="post" action="/admin/garages/requests/<?= $r['id'] ?>/approve" style="display:inline" onsubmit="return confirm('Xác nhận ĐÃ DUYỆT <?= $isAgency ? 'Đại lý' : 'Gara' ?> <?= e($r['name']) ?>?')">
                  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                  <input type="hidden" name="reg_type" value="<?= e($r['reg_type']) ?>">
                  <button type="submit" class="btn-action-approve">Duyệt</button>
                </form>
              <?php endif; ?>

              <?php if ($r['status'] === 'pending' || $r['status'] === 'approved'): ?>
                <button type="button" onclick="openRejectModal(<?= $r['id'] ?>, '<?= e($r['name']) ?>', '<?= e($r['reg_type']) ?>')" class="btn-action-reject">Từ chối</button>
              <?php endif; ?>

              <?php if ($r['status'] === 'rejected'): ?>
                <form method="post" action="/admin/garages/requests/<?= $r['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Xác nhận XÓA HẲN hồ sơ từ chối này khỏi hệ thống?')">
                  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
                  <input type="hidden" name="reg_type" value="<?= e($r['reg_type']) ?>">
                  <button type="submit" class="btn-action-reject" style="background:#475569">Xóa hẳn</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$requests): ?>
        <tr>
          <td colspan="10" style="padding:50px 20px;text-align:center;color:#64748b;font-size:14px">
            <div style="font-weight:700;color:#0b1d3a;font-size:15px;margin-bottom:4px">
              <?= ($regType === 'agency') ? 'Chưa có hồ sơ Đăng ký Đại lý nào' : (($regType === 'garage') ? 'Chưa có hồ sơ Đăng ký Gara nào' : 'Chưa có hồ sơ B2B nào') ?>
            </div>
            <div style="font-size:13px;color:#94a3b8">
              Không tìm thấy kết quả phù hợp với điều kiện lọc hiện tại. Hãy thử chọn tab bộ lọc khác hoặc tìm kiếm lại.
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if($totalPages > 1): $base = ['tab'=>'requests','reg_type'=>$regType,'q'=>$q,'status'=>$statusFilter?:null]; ?>
  <div class="pagination" style="margin-top:16px">
    <?php if($page>1): ?><a href="/admin/garages?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
    <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
    <?php if($page<$totalPages): ?><a href="/admin/garages?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
  </div>
  <?php endif; ?>

<?php else: ?>

  <!-- TAB 2: XE KHÁCH HÀNG LƯU SẴN -->
  <div class="garage-kpis">
    <div class="garage-kpi"><b><?= number_format($summary['total_garages']) ?></b><span>Tổng số xe đã đăng ký</span></div>
    <div class="garage-kpi"><b><?= number_format($summary['total_owners']) ?></b><span>Khách hàng có xe</span></div>
    <div class="garage-kpi"><b><?= number_format($summary['default_count']) ?></b><span>Xe mặc định</span></div>
  </div>

  <form class="garage-filter" method="get" action="/admin/garages" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:center">
    <input type="hidden" name="tab" value="vehicles">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên KH, email, hãng xe..." style="min-width:260px;height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;font-size:13px">
    <select name="brand_id" style="height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;font-size:13px">
      <option value="0">Tất cả hãng xe</option>
      <?php foreach($brands as $b): ?>
      <option value="<?= (int)$b['id'] ?>" <?= ($brandId ?? 0)===(int)$b['id']?'selected':'' ?>><?= e($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-navy" type="submit" style="background:#0b1d3a;color:#fff">Lọc</button>
  </form>

  <div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
  <table class="garage-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Khách hàng</th>
        <th>Xe</th>
        <th>Năm SX</th>
        <th>Nhãn xe</th>
        <th>Mặc định</th>
        <th>Ngày đăng ký</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($garages as $g): ?>
      <tr>
        <td style="color:#9ca3af;font-size:12px">#<?= (int)$g['id'] ?></td>
        <td>
          <strong style="color:#1f365b"><?= e($g['full_name'] ?: '—') ?></strong>
          <div style="font-size:11px;color:#718096;margin-top:2px"><?= e($g['email'] ?: '') ?></div>
          <?php if($g['phone']): ?>
          <div style="font-size:11px;color:#718096"><?= e($g['phone']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <span class="car-badge"><?= e($g['brand_name'] ?: '?') ?></span>
          <span style="margin-left:5px;color:#374151;font-weight:500"><?= e($g['model_name'] ?: '?') ?></span>
          <?php if($g['trim']): ?><div style="font-size:11px;color:#9ca3af;margin-top:2px"><?= e($g['trim']) ?></div><?php endif; ?>
        </td>
        <td style="font-weight:600;color:#374151"><?= (int)$g['year'] ?></td>
        <td style="color:#4b5563"><?= e($g['label'] ?: '—') ?></td>
        <td style="text-align:center">
          <?php if($g['is_default']): ?>
          <span style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;border-radius:4px;padding:2px 8px;font-size:11px;font-weight:700">Mặc định</span>
          <?php else: ?>
          <span style="color:#9ca3af;font-size:11px">—</span>
          <?php endif; ?>
        </td>
        <td style="color:#9ca3af;font-size:12px;white-space:nowrap"><?= e(substr($g['created_at'],0,10)) ?></td>
        <td>
          <a href="/admin/users/<?= (int)$g['user_id'] ?>" class="btn-action-detail" style="font-size:11px;padding:4px 10px">Hồ sơ KH</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

<?php endif; ?>

<!-- MODAL CHI TIẾT ĐỒNG THỜI XEM ẢNH XÁC THỰC -->
<div id="detailRegModal" style="display:none;position:fixed;inset:0;background:rgba(11,29,58,0.75);z-index:99999;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:12px;max-width:760px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 40px rgba(0,0,0,0.3);margin:auto">
    <div style="background:#0b1d3a;color:#ffffff;padding:16px 24px;border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:space-between">
      <h3 style="margin:0;font-size:17px;color:#ffffff !important;font-weight:800">CHI TIẾT HỒ SƠ ĐĂNG KÝ GARA #<span id="dt_id" style="color:#ffffff !important"></span></h3>
      <button type="button" onclick="document.getElementById('detailRegModal').style.display='none'" style="background:none;border:none;color:#ffffff !important;font-size:24px;cursor:pointer">&times;</button>
    </div>
    
    <div style="padding:24px">
      <!-- Thông tin chung -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:20px;font-size:13.5px">
        <div><strong>Tên Gara:</strong> <span id="dt_garage_name" style="color:#0b1d3a;font-weight:700"></span></div>
        <div><strong>Chủ Gara / Đại diện:</strong> <span id="dt_owner_name"></span></div>
        <div><strong>Số điện thoại:</strong> <span id="dt_phone" style="font-weight:700"></span></div>
        <div><strong>Mã số thuế / HKD:</strong> <span id="dt_tax_code" style="background:#e2e8f0;padding:2px 6px;border-radius:4px;font-weight:700"></span></div>
        <div><strong>Email:</strong> <span id="dt_email"></span></div>
        <div><strong>Ngày nộp đơn:</strong> <span id="dt_created_at"></span></div>
        <div style="grid-column:1 / -1"><strong>Địa chỉ thực tế:</strong> <span id="dt_address" style="color:#1e293b"></span></div>
      </div>

      <!-- Bộ ảnh chứng từ -->
      <h4 style="margin:0 0 12px;color:#0b1d3a;font-size:14.5px;font-weight:800;letter-spacing:0.3px">
        BỘ ẢNH XÁC THỰC PHÁP LÝ &amp; THỰC TẾ GARA
      </h4>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <!-- 1. Bảng hiệu -->
        <div style="border:1px solid #cbd5e1;padding:12px;border-radius:8px;background:#fff">
          <div style="font-weight:700;font-size:13px;margin-bottom:8px;color:#0b1d3a">1. Ảnh bảng hiệu Gara</div>
          <div id="box_signboard" style="height:160px;background:#f1f5f9;border-radius:6px;overflow:hidden;display:flex;align-items:center;justify-content:center">
          </div>
        </div>

        <!-- 2. GPKD -->
        <div style="border:1px solid #cbd5e1;padding:12px;border-radius:8px;background:#fff">
          <div style="font-weight:700;font-size:13px;margin-bottom:8px;color:#0b1d3a">2. Giấy phép kinh doanh / HKD</div>
          <div id="box_license" style="height:160px;background:#f1f5f9;border-radius:6px;overflow:hidden;display:flex;align-items:center;justify-content:center">
          </div>
        </div>
      </div>

      <!-- 3. Bộ ảnh thực tế (≥ 3 ảnh) -->
      <div style="border:1px solid #cbd5e1;padding:14px;border-radius:8px;background:#fff;margin-bottom:20px">
        <div style="font-weight:700;font-size:13px;margin-bottom:8px;color:#0b1d3a">3. Bộ ảnh thực tế Cửa hàng / Xưởng Gara (≥ 3 ảnh)</div>
        <div id="box_real_images" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;padding-top:16px">
        <button type="button" onclick="document.getElementById('detailRegModal').style.display='none'" class="btn btn-outline">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL KHỞI TẠO TỪ CHỐI ĐƠN -->
<div id="rejectRegModal" style="display:none;position:fixed;inset:0;background:rgba(11,29,58,0.75);z-index:99999;align-items:center;justify-content:center;padding:16px">
  <form id="rejectForm" method="post" action="" style="background:#fff;border-radius:10px;max-width:480px;width:100%;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,0.3)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <input type="hidden" name="reg_type" id="reject_reg_type" value="garage">
    <h3 style="margin:0 0 10px;color:#dc2626">Từ chối Đơn đăng ký</h3>
    <p style="margin:0 0 14px;font-size:13px;color:#475569">Đơn vị: <strong id="reject_garage_name"></strong></p>
    
    <div style="margin-bottom:18px">
      <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:6px">Lý do từ chối (Sẽ gửi thông báo cho đối tác):</label>
      <textarea name="reject_reason" required rows="4" placeholder="VD: Ảnh bảng hiệu không rõ địa chỉ, Giấy phép kinh doanh không đúng MST, Thiếu ảnh thực tế xưởng..." style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px"></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('rejectRegModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn" style="background:#dc2626;color:#fff;font-weight:800;padding:8px 18px;border-radius:6px">XÁC NHẬN TỪ CHỐI</button>
    </div>
  </form>
</div>

<!-- LIGHTBOX VIEW IMAGE -->
<div id="imgLightboxModal" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:999999;align-items:center;justify-content:center;padding:20px;cursor:zoom-out">
  <img id="lightboxImg" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.5)">
</div>

<script>
function showRegistrationDetail(r) {
  document.getElementById('dt_id').textContent = r.id;
  document.getElementById('dt_garage_name').textContent = r.name || r.garage_name || r.agency_name || '';
  document.getElementById('dt_owner_name').textContent = r.owner_name || '';
  document.getElementById('dt_phone').textContent = r.phone || '';
  document.getElementById('dt_tax_code').textContent = r.tax_code || '';
  document.getElementById('dt_email').textContent = r.email || r.user_email || '—';
  document.getElementById('dt_address').textContent = r.address || '';
  document.getElementById('dt_created_at').textContent = r.created_at || '';

  // Render Signboard
  var boxSign = document.getElementById('box_signboard');
  if (r.signboard_image) {
    if (r.signboard_image.toLowerCase().endsWith('.pdf')) {
      boxSign.innerHTML = '<a href="' + r.signboard_image + '" target="_blank" style="color:#0b1d3a;font-weight:700;text-decoration:underline">Xem File PDF Bảng hiệu</a>';
    } else {
      boxSign.innerHTML = '<img src="' + r.signboard_image + '" onclick="zoomImg(\'' + r.signboard_image + '\')" style="width:100%;height:100%;object-fit:cover;cursor:zoom-in" title="Click để phóng to">';
    }
  } else {
    boxSign.innerHTML = '<span style="color:#94a3b8;font-size:12px">Chưa có ảnh</span>';
  }

  // Render License
  var boxLic = document.getElementById('box_license');
  if (r.license_image) {
    if (r.license_image.toLowerCase().endsWith('.pdf')) {
      boxLic.innerHTML = '<a href="' + r.license_image + '" target="_blank" style="color:#0b1d3a;font-weight:700;text-decoration:underline">Xem File PDF GPKD</a>';
    } else {
      boxLic.innerHTML = '<img src="' + r.license_image + '" onclick="zoomImg(\'' + r.license_image + '\')" style="width:100%;height:100%;object-fit:cover;cursor:zoom-in" title="Click để phóng to">';
    }
  } else {
    boxLic.innerHTML = '<span style="color:#94a3b8;font-size:12px">Chưa có ảnh</span>';
  }

  // Render Real Images
  var boxReal = document.getElementById('box_real_images');
  boxReal.innerHTML = '';
  var imgs = [];
  try { imgs = JSON.parse(r.real_images || '[]'); } catch(e) {}
  if (Array.isArray(imgs) && imgs.length > 0) {
    imgs.forEach(function(src, idx) {
      var d = document.createElement('div');
      d.className = 'thumb-box';
      d.style.height = '90px';
      d.innerHTML = '<img src="' + src + '" onclick="zoomImg(\'' + src + '\')" title="Ảnh thực tế ' + (idx+1) + '">';
      boxReal.appendChild(d);
    });
  } else {
    boxReal.innerHTML = '<span style="color:#94a3b8;font-size:12px">Chưa có ảnh thực tế</span>';
  }

  document.getElementById('detailRegModal').style.display = 'flex';
}

function openRejectModal(id, garageName, regType) {
  document.getElementById('reject_garage_name').textContent = garageName;
  document.getElementById('reject_reg_type').value = regType || 'garage';
  document.getElementById('rejectForm').action = '/admin/garages/requests/' + id + '/reject';
  document.getElementById('rejectRegModal').style.display = 'flex';
}

function zoomImg(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('imgLightboxModal').style.display = 'flex';
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
