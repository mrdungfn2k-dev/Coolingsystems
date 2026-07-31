<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>

<div class="dash-head">
  <div>
    <h1>Quản Lý Yêu Cầu Báo Giá Gara (VIN / Excel)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Tiếp nhận yêu cầu báo giá từ Gara, xem ảnh đăng kiểm/mã OEM/File Excel đính kèm và phản hồi báo giá buôn.</p>
  </div>
</div>

    <?php $fl = getFlash(); if (!empty($fl)): foreach($fl as $f): ?>
      <div style="background:<?= $f['type']==='error'?'#fef2f2':'#dcfce7' ?>;color:<?= $f['type']==='error'?'#991b1b':'#15803d' ?>;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:700;font-size:14px">
        <?= e($f['message']) ?>
      </div>
    <?php endforeach; endif; ?>

    <div style="background:#fff;border-radius:12px;border:1px solid #cbd5e1;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.03)">
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;text-align:left">
          <thead>
            <tr style="background:#0b1d3a;color:#fff">
              <th style="padding:12px 16px">Mã YC</th>
              <th style="padding:12px 16px">Khách Hàng / Gara</th>
              <th style="padding:12px 16px">Số Điện Thoại</th>
              <th style="padding:12px 16px">Ghi Chú Yêu Cầu</th>
              <th style="padding:12px 16px">Tệp Đính Kèm</th>
              <th style="padding:12px 16px">Giá Báo (VNĐ)</th>
              <th style="padding:12px 16px;text-align:center">Trạng Thái</th>
              <th style="padding:12px 16px;text-align:center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($quotations)): ?>
              <?php foreach ($quotations as $q): ?>
                <tr style="border-bottom:1px solid #e2e8f0">
                  <td style="padding:12px 16px;font-weight:700;color:#0b1d3a">#<?= $q['id'] ?></td>
                  <td style="padding:12px 16px">
                    <div style="font-weight:700;color:#1e293b"><?= e($q['full_name']) ?></div>
                    <div style="font-size:12px;color:#64748b"><?= e($q['garage_name'] ?: 'Gara Cá Nhân') ?></div>
                  </td>
                  <td style="padding:12px 16px;font-weight:700;color:#0b1d3a"><?= e($q['phone']) ?></td>
                  <td style="padding:12px 16px;color:#334155;max-width:220px"><?= e($q['note'] ?: '—') ?></td>
                  <td style="padding:12px 16px">
                    <?php if (!empty($q['file_path'])): ?>
                      <a href="<?= e($q['file_path']) ?>" target="_blank" style="color:#2563eb;font-weight:700;text-decoration:none">Xem Tệp Đính Kèm</a>
                    <?php else: ?>
                      <span style="color:#94a3b8">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:12px 16px;font-weight:800;color:#15803d">
                    <?= $q['total_price'] > 0 ? vnd($q['total_price']) : 'Chưa báo giá' ?>
                  </td>
                  <td style="padding:12px 16px;text-align:center">
                    <?php if ($q['status'] === 'replied'): ?>
                      <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:800;padding:3px 10px;border-radius:12px">Đã Phản Hồi</span>
                    <?php else: ?>
                      <span style="background:#fef3c7;color:#b45309;font-size:11px;font-weight:800;padding:3px 10px;border-radius:12px">Chờ Báo Giá</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:12px 16px;text-align:center">
                    <button type="button" onclick="openReplyModal(<?= htmlspecialchars(json_encode($q), ENT_QUOTES) ?>)" style="background:#0b1d3a;color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer">
                      <?= $q['status'] === 'replied' ? 'Sửa Báo Giá' : 'Báo Giá Ngay' ?>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="padding:30px;text-align:center;color:#64748b;font-style:italic">Chưa có yêu cầu báo giá nào từ Gara.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

<!-- Modal Phản Hồi Báo Giá -->
<div id="replyQuoteModal" style="display:none;position:fixed;inset:0;background:rgba(11,29,58,0.75);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:16px;max-width:500px;width:100%;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,0.3);position:relative">
    <button type="button" onclick="closeReplyModal()" style="position:absolute;top:16px;right:16px;border:none;background:#f1f5f9;width:32px;height:32px;border-radius:50%;font-size:18px;font-weight:bold;cursor:pointer;color:#64748b">&times;</button>
    <h3 style="font-size:18px;font-weight:800;color:#0b1d3a;margin:0 0 16px 0">Phản Hồi Báo Giá Phụ Tùng</h3>
    
    <form method="post" action="/admin/quotations/reply">
      <?= csrfField() ?>
      <input type="hidden" name="id" id="replyQuoteId">

      <div style="margin-bottom:14px">
        <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px">Tổng Giá Buôn Phản Hồi (VNĐ) <span style="color:#ef4444">*</span></label>
        <input type="number" step="1000" name="total_price" id="replyTotalPrice" required style="width:100%;height:42px;border-radius:8px;border:1px solid #cbd5e1;padding:0 14px;font-size:15px;font-weight:700;color:#15803d;box-sizing:border-box">
      </div>

      <div style="margin-bottom:16px">
        <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px">Ghi Chú Phản Hồi / Chi Tiết Báo Giá</label>
        <textarea name="admin_reply_note" id="replyNote" rows="4" placeholder="VD: Đã bao gồm lốc nén DENSO chính hãng và giàn nóng MAHLE. Bảo hành 12 tháng." style="width:100%;border-radius:8px;border:1px solid #cbd5e1;padding:10px 14px;font-size:13.5px;box-sizing:border-box"></textarea>
      </div>

      <button type="submit" style="width:100%;height:44px;background:#0b1d3a;color:#fff;border:none;border-radius:8px;font-weight:800;font-size:14.5px;cursor:pointer">
        GỬI BÁO GIÁ VỀ CHO GARA
      </button>
    </form>
  </div>
</div>

<script>
function openReplyModal(q) {
  document.getElementById('replyQuoteId').value = q.id;
  document.getElementById('replyTotalPrice').value = q.total_price || '';
  document.getElementById('replyNote').value = q.admin_reply_note || '';
  document.getElementById('replyQuoteModal').style.display = 'flex';
}
function closeReplyModal() {
  document.getElementById('replyQuoteModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
