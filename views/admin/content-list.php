<?php require __DIR__.'/../partials/dashboard-head.php'; ?>

<div class="dash-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
  <h1 style="margin:0">Quản lý nội dung trang tĩnh</h1>


</div>

<div class="panel">
  <table class="tbl">
    <thead><tr><th>Tên trang</th><th>Slug</th><th>Cập nhật lần cuối</th><th>Trạng thái hiển thị</th><th>Xem</th><th>Sửa</th></tr></thead>
    <tbody>
    <?php
      $pageVis = $pageVis ?? [];
      $customItems = [
        ['title' => 'Footer (Chân trang)', 'slug' => 'footer-info', 'type' => 'modal', 'modal' => 'footerModal'],
        ['title' => 'Banner trang chủ', 'slug' => 'hero-banner', 'type' => 'modal', 'modal' => 'bannerModal'],
        ['title' => 'Banner 2', 'slug' => 'home-banners', 'type' => 'link', 'url' => '/admin/banners'],
        ['title' => '4 bước cam kết', 'slug' => 'trust-steps', 'type' => 'link', 'url' => '/admin/trust-steps'],
      ];
    ?>
    <?php foreach ($customItems as $cItem): ?>
      <?php $cVis = ($pageVis[$cItem['slug']] ?? '1') !== '0'; ?>
      <tr>
        <td><strong><?= e($cItem['title']) ?></strong></td>
        <td class="fs-12"><code><?= e($cItem['slug']) ?></code></td>
        <td class="fs-12">—</td>
        <td>
          <button type="button" class="btn btn-sm <?= $cVis ? 'btn-navy' : 'btn-outline-secondary' ?>" data-slug="<?= e($cItem['slug']) ?>" data-status="<?= $cVis ? 1 : 0 ?>" onclick="togglePageVis('<?= e($cItem['slug']) ?>', this)" style="min-width:95px;font-weight:700">
            <?= $cVis ? '✓ Hiển thị' : '✕ Đã ẩn' ?>
          </button>
        </td>
        <td><a href="/" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
        <td>
          <?php if ($cItem['type'] === 'modal'): ?>
            <button onclick="document.getElementById('<?= $cItem['modal'] ?>').style.display='flex'" class="btn btn-gold btn-sm">Sửa</button>
          <?php else: ?>
            <a href="<?= $cItem['url'] ?>" class="btn btn-gold btn-sm">Sửa</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>

    <?php foreach($pages as $p): ?>
      <?php
        $slug = $p['slug'];
        $pVis = ($pageVis[$slug] ?? '1') !== '0';
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
      <tr>
        <td><strong><?= e($p['title']) ?></strong></td>
        <td class="fs-12"><code><?= e($slug) ?></code></td>
        <td class="fs-12"><?= $p['updated_at'] ? relTime($p['updated_at']) : '—' ?></td>
        <td>
          <button type="button" class="btn btn-sm <?= $pVis ? 'btn-navy' : 'btn-outline-secondary' ?>" data-slug="<?= e($slug) ?>" data-status="<?= $pVis ? 1 : 0 ?>" onclick="togglePageVis('<?= e($slug) ?>', this)" style="min-width:95px;font-weight:700">
            <?= $pVis ? '✓ Hiển thị' : '✕ Đã ẩn' ?>
          </button>
        </td>
        <td><a href="<?= $viewUrl ?>" target="_blank" class="btn btn-outline-navy btn-sm">Xem</a></td>
        <td><a href="/admin/content/<?= e($slug) ?>" class="btn btn-gold btn-sm">Sửa</a></td>
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

      <!-- Quản Lý Danh Sách Nhiều Banner (Multi-Banner Slider) -->
      <?php
        $rawList = json_decode($bannerSettings['home_banners_list'] ?? '[]', true);
        if (empty($rawList) && !empty($bannerSettings['hero_bg_image'])) {
            $rawList = [['img' => $bannerSettings['hero_bg_image'], 'link' => $bannerSettings['hero_banner_link'] ?? '']];
        }
        if (empty($rawList)) {
            $rawList = [
                ['img' => 'hero_cooling_banner_1.webp', 'link' => '/products'],
                ['img' => 'hero_cooling_banner_2.webp', 'link' => '/contact']
            ];
        }
        $activeImgNames = array_column($rawList, 'img');
      ?>

      <div class="form-group" style="margin-top:16px;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0">
        <label style="font-weight:700;font-size:13px;color:var(--navy);margin-bottom:8px;display:block">Danh Sách Banner Hiện Tại (Chạy Slide Tự Động)</label>
        <div id="bannerListWrap" style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($rawList as $idx => $bn): ?>
            <div class="banner-item-row" style="display:flex;align-items:center;gap:12px;background:#fff;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px">
              <img src="/uploads/banners/<?= e($bn['img']) ?>" style="width:90px;height:50px;object-fit:cover;border-radius:4px">
              <input type="hidden" name="existing_banners[]" value="<?= e($bn['img']) ?>">
              <div style="flex:1">
                <div style="font-size:11px;font-weight:700;color:#334155;margin-bottom:2px"><?= e($bn['img']) ?></div>
                <input type="text" name="existing_banner_links[]" value="<?= e($bn['link'] ?? '') ?>" placeholder="Đường dẫn liên kết (ví dụ: /products)" style="width:100%;padding:4px 8px;font-size:12px;border:1px solid #ddd;border-radius:4px">
              </div>
              <button type="button" onclick="this.closest('.banner-item-row').remove()" style="background:#ef4444;color:#fff;border:none;padding:6px 10px;border-radius:4px;font-size:12px;font-weight:700;cursor:pointer">Xóa</button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Chọn Banner Đồ Họa Mẫu Có Sẵn -->
      <div class="form-group" style="margin-top:16px;background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e2e8f0">
        <label style="font-weight:700;font-size:13px;color:var(--navy);margin-bottom:8px;display:block">Tích Chọn Thêm Banner Đồ Họa Mẫu Có Sẵn Vào Slide</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <label class="preset-banner-card" style="border:2px solid <?= (in_array('hero_cooling_banner_1.webp', $activeImgNames) || in_array('hero_cooling_banner_1.png', $activeImgNames)) ? 'var(--navy)' : '#cbd5e1' ?>;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;display:block">
            <input type="checkbox" name="selected_presets[]" value="hero_cooling_banner_1.webp" <?= (in_array('hero_cooling_banner_1.webp', $activeImgNames) || in_array('hero_cooling_banner_1.png', $activeImgNames)) ? 'checked' : '' ?> style="position:absolute;top:8px;left:8px;z-index:2;width:18px;height:18px;accent-color:var(--navy)">
            <img src="/uploads/banners/hero_cooling_banner_1.webp" style="width:100%;height:90px;object-fit:cover;display:block">
            <div style="padding:6px;font-size:11px;font-weight:600;text-align:center;color:#334155;background:#fff">Mẫu 1: Lốc nén điều hòa OEM DENSO chính hãng</div>
          </label>
          <label class="preset-banner-card" style="border:2px solid <?= (in_array('hero_cooling_banner_2.webp', $activeImgNames) || in_array('hero_cooling_banner_2.png', $activeImgNames)) ? 'var(--navy)' : '#cbd5e1' ?>;border-radius:8px;overflow:hidden;cursor:pointer;position:relative;display:block">
            <input type="checkbox" name="selected_presets[]" value="hero_cooling_banner_2.webp" <?= (in_array('hero_cooling_banner_2.webp', $activeImgNames) || in_array('hero_cooling_banner_2.png', $activeImgNames)) ? 'checked' : '' ?> style="position:absolute;top:8px;left:8px;z-index:2;width:18px;height:18px;accent-color:var(--navy)">
            <img src="/uploads/banners/hero_cooling_banner_2.webp" style="width:100%;height:90px;object-fit:cover;display:block">
            <div style="padding:6px;font-size:11px;font-weight:600;text-align:center;color:#334155;background:#fff">Mẫu 2: Bộ giàn nóng &amp; két nước làm mát MAHLE</div>
          </label>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
        <div class="form-group">
          <label style="font-weight:600;font-size:12px;text-transform:uppercase;color:#555;margin-bottom:4px;display:block">Tải lên nhiều ảnh Banner mới từ máy tính</label>
          <input type="file" name="hero_banner_files[]" multiple accept="image/*" style="padding:8px;width:100%">
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end">
          <button type="submit" class="btn btn-navy btn-sm" style="padding:10px 24px">Lưu Danh Sách Banner</button>
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

<script>
function togglePageVis(slug, btn) {
  var curStatus = parseInt(btn.getAttribute('data-status') || '1');
  var newStatus = curStatus === 1 ? 0 : 1;
  btn.disabled = true;
  var fd = new FormData();
  fd.append('slug', slug);
  fd.append('status', newStatus);
  if (window._CSRF) fd.append('_csrf', window._CSRF);
  fetch('/admin/content/toggle-visibility', {
    method: 'POST',
    body: fd
  }).then(function(r){ return r.json(); }).then(function(data){
    btn.disabled = false;
    if (data.ok) {
      btn.setAttribute('data-status', newStatus);
      if (newStatus === 1) {
        btn.className = 'btn btn-sm btn-navy';
        btn.innerHTML = '✓ Hiển thị';
      } else {
        btn.className = 'btn btn-sm btn-outline-secondary';
        btn.innerHTML = '✕ Đã ẩn';
      }
    } else {
      alert('Không thể cập nhật trạng thái.');
    }
  }).catch(function(err){
    btn.disabled = false;
    alert('Lỗi kết nối.');
  });
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
