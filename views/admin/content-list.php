<?php require __DIR__.'/../partials/dashboard-head.php'; ?>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
  <h1 style="margin:0">Quản lý nội dung trang tĩnh</h1>


</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>Tên trang</th><th>Slug</th><th>Cập nhật lần cuối</th><th>Xem</th><th>Sửa</th></tr></thead>
    <tbody>
    <!-- Footer row -->
    <tr>
      <td><strong>Footer (Chân trang)</strong></td>
      <td class="fs-12"><code>footer-info</code></td>
      <td class="fs-12">—</td>
      <td><a href="/" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
      <td><button onclick="document.getElementById('footerModal').style.display='flex'" class="btn btn-gold btn-sm">Sửa</button></td>
    </tr>
    <!-- Banner row (hero) -->
    <tr>
      <td><strong>Banner trang chủ</strong></td>
      <td class="fs-12"><code>hero-banner</code></td>
      <td class="fs-12">—</td>
      <td><a href="/" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
      <td><button onclick="document.getElementById('bannerModal').style.display='flex'" class="btn btn-gold btn-sm">Sửa</button></td>
    </tr>
    <!-- Banner 2 (carousel trượt trang chủ) -->
    <tr>
      <td><strong>Banner 2</strong></td>
      <td class="fs-12"><code>home-banners</code></td>
      <td class="fs-12">—</td>
      <td><a href="/" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
      <td><a href="/admin/banners" class="btn btn-gold btn-sm">Sửa</a></td>
    </tr>
    <!-- 4 Bước cam kết -->
    <tr>
      <td><strong>4 bước cam kết</strong></td>
      <td class="fs-12"><code>trust-steps</code></td>
      <td class="fs-12">—</td>
      <td><a href="/" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
      <td><a href="/admin/trust-steps" class="btn btn-gold btn-sm">Sửa</a></td>
    </tr>
    <?php foreach($pages as $p): ?>
      <tr>
        <td><strong><?= e($p['title']) ?></strong></td>
        <td class="fs-12"><code><?= e($p['slug']) ?></code></td>
        <td class="fs-12"><?= $p['updated_at'] ? relTime($p['updated_at']) : '—' ?></td>
        <?php
          $slug = $p['slug'];
          $urlMap = [
            'gioi-thieu'           => '/about',
            'lien-he'              => '/contact',
            'tuyen-dung'           => '/careers',
            'cau-chuyen-cooling'   => '/about/story',
            'he-thong-cua-hang'    => '/stores',
            'tin-tuc-tong-hop'     => '/news',
            '4-buoc-cam-ket'       => '/#cam-ket',
            'dieu-khoan-bao-mat'   => '/policies/dieu-khoan-bao-mat',
            'huong-dan-mua-hang'   => '/policies/huong-dan-mua-hang',
            'chinh-sach-doi-tra'   => '/policies/chinh-sach-doi-tra',
            'chinh-sach-bao-hanh'  => '/policies/chinh-sach-bao-hanh',
          ];
          $viewUrl = $urlMap[$slug] ?? ('/policies/'.$slug);
        ?>
        <td><a href="<?= $viewUrl ?>" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
        <td><a href="/admin/content/<?= e($p['slug']) ?>" class="btn btn-gold btn-sm">Sửa</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Banner Edit -->
<div id="bannerModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center">
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:700px;width:95%;max-height:85vh;overflow-y:auto;position:relative">
    <button onclick="document.getElementById('bannerModal').style.display='none'" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#666">&times;</button>
    <h3 style="margin:0 0 20px;font-size:18px;font-weight:700;color:var(--navy)">Chỉnh sửa Banner trang chủ</h3>
    <form method="post" action="/admin/banner/update" enctype="multipart/form-data">
      <?= csrfField() ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Dòng badge</label>
          <input type="text" name="hero_badge" value="<?= e($bannerSettings['hero_badge'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Tiêu đề chính (HTML)</label>
          <input type="text" name="hero_heading" value="<?= e($bannerSettings['hero_heading'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
      </div>
      <div class="form-group" style="margin-top:12px">
        <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Mô tả phụ</label>
        <textarea name="hero_subtext" rows="2" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px"><?= e($bannerSettings['hero_subtext'] ?? '') ?></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Nút 1 — Tên</label>
          <input type="text" name="hero_btn1_text" value="<?= e($bannerSettings['hero_btn1_text'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Nút 1 — URL</label>
          <input type="text" name="hero_btn1_url" value="<?= e($bannerSettings['hero_btn1_url'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Nút 2 — Tên</label>
          <input type="text" name="hero_btn2_text" value="<?= e($bannerSettings['hero_btn2_text'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Nút 2 — URL</label>
          <input type="text" name="hero_btn2_url" value="<?= e($bannerSettings['hero_btn2_url'] ?? '') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Ảnh nền banner (tùy chọn)</label>
          <input type="file" name="hero_bg_image" accept="image/*" style="padding:8px">
          <?php if(!empty($bannerSettings['hero_bg_image'])): ?>
            <div style="margin-top:4px;font-size:11px;color:#888">Hiện tại: <?= e($bannerSettings['hero_bg_image']) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end">
          <button type="submit" class="btn btn-navy btn-sm" style="padding:10px 24px">Lưu Banner</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Modal Footer Edit -->
<div id="footerModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center">
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:600px;width:95%;max-height:85vh;overflow-y:auto;position:relative">
    <button onclick="document.getElementById('footerModal').style.display='none'" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#666">&times;</button>
    <h3 style="margin:0 0 20px;font-size:18px;font-weight:700;color:var(--navy)">Chỉnh sửa Chân trang (Footer)</h3>
    <form method="post" action="/admin/footer/update">
      <?= csrfField() ?>
      <div class="form-group">
        <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Tên Logo Text</label>
        <input type="text" name="footer_logo_text" value="<?= e(!empty($footerSettings['footer_logo_text']) ? $footerSettings['footer_logo_text'] : 'COOLING') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
      </div>
      <div class="form-group" style="margin-top:12px">
        <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Đoạn mô tả ngắn (Dưới logo)</label>
        <textarea name="footer_desc" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px"><?= e(!empty($footerSettings['footer_desc']) ? $footerSettings['footer_desc'] : 'Sàn TMĐT phụ tùng ô tô chính hãng — chuyên sâu hệ thống làm mát. Cung cấp phụ tùng uy tín cho hàng triệu khách hàng trên toàn quốc.') ?></textarea>
      </div>
      <div class="form-group" style="margin-top:12px">
        <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Đoạn text Cuối trang (Copyright)</label>
        <input type="text" name="footer_copyright" value="<?= e(!empty($footerSettings['footer_copyright']) ? $footerSettings['footer_copyright'] : '&copy; ' . date('Y') . ' Cooling. Bảo lưu mọi quyền.') ?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px" placeholder="VD: &copy; 2026 Cooling System. Bảo lưu mọi quyền.">
        <div style="font-size:11px;color:#888;margin-top:4px">Ghi chú: Dùng &amp;copy; cho ký hiệu ©</div>
      </div>
      <div class="form-group" style="margin-top:16px;text-align:right">
        <button type="submit" class="btn btn-navy btn-sm" style="padding:10px 24px">Lưu Chân trang</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
