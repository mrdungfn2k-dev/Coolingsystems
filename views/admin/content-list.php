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
  <div style="background:#fff;border-radius:12px;padding:28px;max-width:760px;width:95%;max-height:90vh;overflow-y:auto;position:relative">
    <button onclick="document.getElementById('bannerModal').style.display='none'" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#666">&times;</button>
    <h3 style="margin:0 0 20px;font-size:18px;font-weight:700;color:var(--navy)">Chỉnh sửa Banner trang chủ</h3>
    <form method="post" action="/admin/banner/update" enctype="multipart/form-data">
      <?= csrfField() ?>

      <!-- Chế độ hiển thị chữ -->
      <div class="form-group" style="margin-bottom:16px;background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e2e8f0">
        <label style="font-weight:700;font-size:13px;color:var(--navy);margin-bottom:8px;display:block">Chế độ hiển thị chữ trên Banner</label>
        <div style="display:flex;gap:20px;align-items:center">
          <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:600">
            <input type="radio" name="hero_show_text" value="1" <?= ($bannerSettings['hero_show_text'] ?? '1') !== '0' ? 'checked' : '' ?> style="accent-color:var(--navy)">
            <span>Bật chữ đè lên Banner (Tiêu đề, mô tả & nút bấm)</span>
          </label>
          <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:600">
            <input type="radio" name="hero_show_text" value="0" <?= ($bannerSettings['hero_show_text'] ?? '') === '0' ? 'checked' : '' ?> style="accent-color:var(--navy)">
            <span>Tắt chữ — Chỉ hiển thị ảnh Banner đồ họa</span>
          </label>
        </div>
      </div>

      <div class="form-group" style="margin-bottom:16px">
        <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Liên kết khi click vào Banner (tùy chọn)</label>
        <input type="text" name="hero_banner_link" value="<?= e($bannerSettings['hero_banner_link'] ?? '') ?>" placeholder="Ví dụ: /products hoặc /contact" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px">
      </div>

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

      <!-- Chọn Banner Đồ Họa Mẫu Có Sẵn -->
      <div class="form-group" style="margin-top:16px;background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e2e8f0">
        <label style="font-weight:700;font-size:13px;color:var(--navy);margin-bottom:8px;display:block">Chọn Banner Đồ Họa Mẫu Có Sẵn (Hoặc tải tệp bên dưới)</label>
        <input type="hidden" name="preset_banner" id="presetBannerInput" value="">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div onclick="selectPresetBanner('hero_cooling_banner_1.png', this)" class="preset-banner-card" style="border:2px solid <?= ($bannerSettings['hero_bg_image'] ?? '') === 'hero_cooling_banner_1.png' ? 'var(--navy)' : '#cbd5e1' ?>;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;transition:all 0.2s">
            <img src="/uploads/banners/hero_cooling_banner_1.png" style="width:100%;height:90px;object-fit:cover;display:block">
            <div style="padding:6px;font-size:11px;font-weight:600;text-align:center;color:#334155;background:#fff">Mẫu 1: Động cơ & Hệ thống làm mát high-tech</div>
          </div>
          <div onclick="selectPresetBanner('hero_cooling_banner_2.png', this)" class="preset-banner-card" style="border:2px solid <?= ($bannerSettings['hero_bg_image'] ?? '') === 'hero_cooling_banner_2.png' ? 'var(--navy)' : '#cbd5e1' ?>;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;transition:all 0.2s">
            <img src="/uploads/banners/hero_cooling_banner_2.png" style="width:100%;height:90px;object-fit:cover;display:block">
            <div style="padding:6px;font-size:11px;font-weight:600;text-align:center;color:#334155;background:#fff">Mẫu 2: Bộ sưu tập phụ tùng làm mát 3D</div>
          </div>
        </div>
      </div>
      <script>
        function selectPresetBanner(filename, el) {
          document.querySelectorAll('.preset-banner-card').forEach(c => {
            c.style.borderColor = '#cbd5e1';
            c.style.boxShadow = 'none';
          });
          document.getElementById('presetBannerInput').value = filename;
          el.style.borderColor = 'var(--navy)';
          el.style.boxShadow = '0 0 0 3px rgba(26,50,88,0.2)';
        }
      </script>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Hoặc Tải ảnh Banner mới từ máy tính</label>
          <input type="file" name="hero_bg_image" accept="image/*" style="padding:8px">
          <?php if(!empty($bannerSettings['hero_bg_image'])): ?>
            <div style="margin-top:4px;font-size:11px;color:#888">Ảnh hiện tại: <strong><?= e($bannerSettings['hero_bg_image']) ?></strong></div>
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
