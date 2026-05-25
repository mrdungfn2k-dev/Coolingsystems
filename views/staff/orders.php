<?php require __DIR__.'/../partials/dashboard-head.php'; ?>

<h1 style="font-size:20px;color:var(--navy,#1a3258);margin:0">Quản lý đơn hàng</h1>
      <?php if(in_array('create_order',$perms)): ?>
        <a href="/staff/orders/create" class="btn btn-gold">Tạo đơn hàng hộ</a>
      <?php endif; ?>
    </div>
    <table>
      <thead>
        <tr><th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Tổng tiền</th><th>Giao hàng</th><th>Thanh toán</th><th>Ngày đặt</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
        <?php if(empty($orders)): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px;color:#888">Chưa có đơn hàng nào</td></tr>
        <?php endif; ?>
        <?php foreach($orders ?? [] as $o):
          $statusMap = ['pending'=>'Đang chờ','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipped'=>'Đang giao','delivered'=>'Đã giao','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
          $badgeMap = ['pending'=>'pending','confirmed'=>'confirmed','processing'=>'confirmed','shipped'=>'shipped','delivered'=>'completed','completed'=>'completed','cancelled'=>'cancelled'];
        ?>
          <tr>
            <td><strong><?= e($o['code']) ?></strong></td>
            <td><?= e($o['full_name']) ?></td>
            <td><?= e($o['phone'] ?? '') ?></td>
            <td style="font-weight:700;color:#c0392b"><?= number_format($o['grand_total'],0,',','.') ?> đ</td>
            <td><span class="badge badge-<?= $badgeMap[$o['delivery_status']] ?? 'pending' ?>"><?= $statusMap[$o['delivery_status']] ?? $o['delivery_status'] ?></span></td>
            <td><?= $o['payment_status'] === 'paid' ? '<span style="color:#27ae60;font-weight:700">Đã TT</span>' : '<span style="color:#888">Chưa TT</span>' ?></td>
            <td style="font-size:12px;color:#888"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td><a href="/admin/orders/<?= $o['id'] ?>" class="btn btn-navy" style="font-size:11px">Chi tiết</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
