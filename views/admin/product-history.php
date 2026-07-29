<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
if (!function_exists('_phFmt')) {
  function _phFmt($ts){ if(!$ts) return '—'; try{ $d=new DateTime($ts); $d->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh')); return $d->format('d/m/Y H:i'); }catch(\Exception $e){ return $ts; } }
  function _phBrowser($ua){ $ua=(string)$ua; $m=['Edg'=>'Edge','OPR'=>'Opera','Chrome'=>'Chrome','Firefox'=>'Firefox','Safari'=>'Safari']; foreach($m as $k=>$v){ if(stripos($ua,$k)!==false) return $v; } return $ua!=='' ? mb_substr($ua,0,22) : '—'; }
  function _phDevice($ua){ return preg_match('/Mobile|Android|iPhone|iPad/i',(string)$ua) ? 'Mobile' : 'Desktop'; }
  function _phStatus($s){ $m=['draft'=>'Bản nháp','pending'=>'Chờ duyệt','published'=>'Xuất bản','hidden'=>'Ẩn / Ngừng KD','out_of_stock'=>'Hết hàng','rejected'=>'Từ chối','blocked'=>'Khóa']; return $m[$s] ?? ($s ?: '—'); }
  function _phParseMeta($ch) {
    $action = $ch['action'] ?? '';
    $rawMeta = $ch['meta'] ?? '';
    $actionMap = [
      'create' => 'Tạo mới sản phẩm',
      'update' => 'Cập nhật sản phẩm',
      'quick_edit' => 'Sửa nhanh sản phẩm',
      'update_price' => 'Cập nhật giá bán',
      'update_stock' => 'Cập nhật tồn kho',
      'change_status' => 'Thay đổi trạng thái',
      'publish' => 'Xuất bản sản phẩm',
      'unpublish' => 'Chuyển về bản nháp',
      'delete' => 'Xóa sản phẩm',
      'archive' => 'Lưu trữ sản phẩm',
      'restore' => 'Khôi phục sản phẩm',
    ];
    $actionTitle = $actionMap[$action] ?? ($action ? ucfirst(str_replace('_', ' ', $action)) : 'Thay đổi sản phẩm');

    if (empty($rawMeta)) return $actionTitle;

    $meta = is_string($rawMeta) ? json_decode($rawMeta, true) : $rawMeta;
    if (!is_array($meta)) {
      return $actionTitle . (!empty($rawMeta) ? ': ' . e($rawMeta) : '');
    }

    if (!empty($meta['message'])) return e($meta['message']);
    if (!empty($meta['note'])) return e($meta['note']);
    if (!empty($meta['action_name'])) return e($meta['action_name']);

    if (isset($meta['before']) || isset($meta['after'])) {
      $before = is_array($meta['before'] ?? null) ? $meta['before'] : [];
      $after = is_array($meta['after'] ?? null) ? $meta['after'] : [];
      $diffs = [];

      if (array_key_exists('price', $before) && array_key_exists('price', $after) && $before['price'] != $after['price']) {
        $diffs[] = "Giá bán: " . number_format((float)$before['price']) . "đ ➔ " . number_format((float)$after['price']) . "đ";
      }
      if (array_key_exists('original_price', $before) && array_key_exists('original_price', $after) && $before['original_price'] != $after['original_price']) {
        $diffs[] = "Giá niêm yết: " . number_format((float)$before['original_price']) . "đ ➔ " . number_format((float)$after['original_price']) . "đ";
      }
      if (array_key_exists('stock', $before) && array_key_exists('stock', $after) && $before['stock'] != $after['stock']) {
        $diffs[] = "Tồn kho: " . number_format((float)$before['stock']) . " ➔ " . number_format((float)$after['stock']);
      }
      if (array_key_exists('status', $before) && array_key_exists('status', $after) && $before['status'] != $after['status']) {
        $diffs[] = "Trạng thái: " . _phStatus($before['status']) . " ➔ " . _phStatus($after['status']);
      }
      if (array_key_exists('warranty_months', $before) && array_key_exists('warranty_months', $after) && $before['warranty_months'] != $after['warranty_months']) {
        $diffs[] = "Bảo hành: " . $before['warranty_months'] . " tháng ➔ " . $after['warranty_months'] . " tháng";
      }
      if (array_key_exists('cost_price', $before) && array_key_exists('cost_price', $after) && $before['cost_price'] != $after['cost_price']) {
        $diffs[] = "Giá nhập: " . number_format((float)$before['cost_price']) . "đ ➔ " . number_format((float)$after['cost_price']) . "đ";
      }

      if (!empty($diffs)) {
        return '<strong style="color:#1a3258">' . e($actionTitle) . ':</strong> ' . implode(' · ', $diffs);
      }

      return '<strong style="color:#1a3258">' . e($actionTitle) . '</strong> <span style="color:#888">(Cập nhật lưu thông tin sản phẩm)</span>';
    }

    return e($actionTitle);
  }
}
?>
<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
  <h1 style="margin:0">Lịch sử & lượt truy cập</h1>
  <a href="/admin/products" style="display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 16px;border-radius:6px;background:#fff;border:1px solid #d6deea;color:#1a3258;text-decoration:none;font-weight:600;font-size:13px">← Quay lại danh sách</a>
</div>

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:18px 22px;margin-top:16px;display:flex;gap:18px;flex-wrap:wrap;align-items:center">
  <div style="flex:1;min-width:240px">
    <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.04em">SKU: <?= e($product['sku'] ?? '—') ?></div>
    <div style="font-size:17px;font-weight:700;color:#1a3258;margin-top:2px"><?= e($product['name'] ?? '') ?></div>
    <div style="font-size:12px;color:#666;margin-top:4px"><?= e(trim(($product['cat_name'] ?? '').' · '.($product['car_brand_name'] ?? '').' · '.($product['part_brand'] ?? ''), ' ·')) ?></div>
    <a href="/admin/products/<?= (int)$product['id'] ?>/edit" style="font-size:12px;color:#1a3258;font-weight:600;text-decoration:none">✎ Sửa sản phẩm</a>
  </div>
  <div style="text-align:center;padding:0 14px;border-left:1px solid #eef0f4">
    <div style="font-size:24px;font-weight:800;color:#1a8c5b"><?= number_format((int)($product['view_count'] ?? 0)) ?></div>
    <div style="font-size:11px;color:#888">Tổng lượt xem</div>
  </div>
  <div style="text-align:center;padding:0 14px;border-left:1px solid #eef0f4">
    <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:#eef6ff;color:#1a3258"><?= e(_phStatus($product['status'] ?? '')) ?></span>
    <div style="font-size:11px;color:#888;margin-top:6px">Trạng thái</div>
  </div>
</div>

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:18px 22px;margin-top:16px">
  <h3 style="margin:0 0 14px;font-size:15px;color:#1a3258">📅 Lịch sử đăng bài</h3>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px">
    <?php
      $isPub = ($product['status'] ?? '') === 'published';
      $createdTime = $product['created_at'] ?? '';
      $updatedTime = $product['updated_at'] ?? '';

      // Ngày xuất bản
      $pubDate = !empty($product['published_at']) ? $product['published_at'] : ($isPub ? ($createdTime ?: $updatedTime) : '');

      // Ngày duyệt & Người duyệt
      $appDate = !empty($product['approved_at']) ? $product['approved_at'] : ($isPub ? ($pubDate ?: $createdTime) : '');
      $appUser = !empty($product['approved_by_name']) ? $product['approved_by_name'] : ($appDate ? 'Quản trị viên (Admin)' : '');
    ?>
    <?php foreach([
      ['Ngày tạo', $createdTime, '#1a3258', ''],
      ['Ngày xuất bản', $pubDate, '#1a8c5b', ''],
      ['Ngày duyệt', $appDate, '#c9972c', $appUser],
      ['Cập nhật gần nhất', $updatedTime ?: $createdTime, '#6b7a99', '']
    ] as $row): ?>
      <div style="border:1px solid #eef0f4;border-radius:8px;padding:12px 14px">
        <div style="font-size:11px;color:#888"><?= e($row[0]) ?></div>
        <div style="font-size:14px;font-weight:700;color:<?= $row[2] ?>;margin-top:3px"><?= e(_phFmt($row[1])) ?></div>
        <?php if($row[0]==='Ngày duyệt' && !empty($row[3]) && !empty($row[1])): ?>
          <div style="font-size:11px;color:#888;margin-top:2px">bởi <?= e($row[3]) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if(!empty($changes)): ?>
  <div style="margin-top:16px">
    <div style="font-size:12px;font-weight:700;color:#666;margin-bottom:6px">Nhật ký thay đổi trạng thái</div>
    <?php foreach($changes as $ch): ?>
      <div style="display:flex;gap:10px;align-items:center;padding:8px 0;border-top:1px solid #f3f4f7;font-size:13px;flex-wrap:wrap">
        <span style="color:#888;min-width:120px"><?= e(_phFmt($ch['created_at'])) ?></span>
        <span style="flex:1;min-width:160px"><?= _phParseMeta($ch) ?></span>
        <span style="color:#1a3258;font-weight:600"><?= e($ch['full_name'] ?? $ch['email'] ?? ('User #'.($ch['user_id']??'?'))) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:18px 22px;margin-top:16px">
  <h3 style="margin:0 0 6px;font-size:15px;color:#1a3258">👁 Lượt truy cập của người dùng</h3>
  <p style="margin:0 0 14px;font-size:12px;color:#888">Đã ghi <?= number_format((int)$viewTotal) ?> lượt truy cập (đã bỏ qua bot)<?php if(($viewPages??1)>1): ?> — trang <?= (int)$viewPage ?>/<?= (int)$viewPages ?>, mỗi trang <?= (int)$viewPerPage ?> lượt<?php endif; ?>.</p>
  <?php if(empty($views)): ?>
    <div style="padding:26px;text-align:center;color:#999;font-size:13px;background:#fafbfc;border-radius:8px">Chưa có lượt truy cập nào được ghi lại.<br>Dữ liệu sẽ xuất hiện ngay khi có người mở trang sản phẩm này.</div>
  <?php else: ?>
  <div style="overflow-x:auto">
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead><tr style="text-align:left;color:#888;font-size:11px;text-transform:uppercase">
      <th style="padding:8px 10px;border-bottom:2px solid #eef0f4">Thời gian</th>
      <th style="padding:8px 10px;border-bottom:2px solid #eef0f4">Người dùng</th>
      <th style="padding:8px 10px;border-bottom:2px solid #eef0f4">Thiết bị</th>
      <th style="padding:8px 10px;border-bottom:2px solid #eef0f4">Trình duyệt</th>
      <th style="padding:8px 10px;border-bottom:2px solid #eef0f4">IP</th>
    </tr></thead>
    <tbody>
    <?php foreach($views as $v): ?>
      <tr>
        <td style="padding:8px 10px;border-bottom:1px solid #f3f4f7;white-space:nowrap"><?= e(_phFmt($v['created_at'])) ?></td>
        <td style="padding:8px 10px;border-bottom:1px solid #f3f4f7">
          <?php if(!empty($v['user_id'])): ?>
            <span style="font-weight:600;color:#1a3258"><?= e($v['full_name'] ?? $v['email'] ?? ('User #'.$v['user_id'])) ?></span>
          <?php else: ?>
            <span style="color:#999">Khách vãng lai</span>
          <?php endif; ?>
        </td>
        <td style="padding:8px 10px;border-bottom:1px solid #f3f4f7"><?= e(_phDevice($v['user_agent'] ?? '')) ?></td>
        <td style="padding:8px 10px;border-bottom:1px solid #f3f4f7"><?= e(_phBrowser($v['user_agent'] ?? '')) ?></td>
        <td style="padding:8px 10px;border-bottom:1px solid #f3f4f7;color:#888;font-family:monospace;font-size:12px"><?= e($v['ip'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php if(($viewPages??1) > 1): ?>
  <div style="margin-top:14px">
    <?php require_once __DIR__.'/../partials/pagination.php'; renderPagination((int)$viewPage, (int)$viewPages, '/admin/products/'.$product['id'].'/history', []); ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
