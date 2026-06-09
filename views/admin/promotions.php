<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<?php
/* ─────────────────────────────────────────────────
   ADMIN: Quản lý Khuyến mãi / Giảm giá sản phẩm
   ───────────────────────────────────────────────── */

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$filter = $_GET['filter'] ?? 'all'; // all, on_sale, no_sale
$q = trim($_GET['q'] ?? '');

$where = "WHERE p.status='published'";
if ($filter === 'on_sale') $where .= " AND p.is_on_sale=1";
if ($filter === 'no_sale') $where .= " AND (p.is_on_sale=0 OR p.is_on_sale IS NULL)";

$params = [];
if ($q !== '') {
    $where .= " AND p.name LIKE ?";
    $params[] = "%" . $q . "%";
}

$total = dbGet("SELECT COUNT(*) as n FROM products p $where", $params)['n'] ?? 0;
$products = dbAll("SELECT p.id, p.name, p.price, p.original_price, p.sale_price, p.is_on_sale,
    (SELECT file_path FROM product_images WHERE product_id=p.id AND is_main=1 LIMIT 1) AS main_image
    FROM products p $where ORDER BY p.is_on_sale DESC, p.name LIMIT $perPage OFFSET $offset", $params);
$totalPages = max(1, ceil($total / $perPage));
?>
<style>
.promo-grid { display:grid; gap:0; }
.promo-row { display:grid; grid-template-columns:60px 1fr 120px 190px 100px 100px; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid #f0f0f0; transition:background .15s; }
.promo-row:hover { background:#f8f9fa; }
.promo-row.header { background:#f1f5f9; font-size:11px; font-weight:700; color:#666; text-transform:uppercase; border-radius:8px 8px 0 0; }
.on-sale-badge { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.no-sale-badge { background:#f1f5f9; color:#888; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.inline-form { display:flex; gap:6px; align-items:center; }
.price-input { width:110px; padding:6px 8px; border:1px solid #ddd; border-radius:5px; font-size:13px; }
.price-input:focus { border-color:var(--navy); outline:none; }
.save-btn { padding:6px 12px; background:var(--navy); color:#fff; border:none; border-radius:5px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap; }
.save-btn:hover { opacity:0.85; }
.toggle-btn { padding:5px 12px; border:1px solid; border-radius:5px; font-size:12px; font-weight:600; cursor:pointer; }
.filter-tabs { display:flex; gap:0; border-bottom:1px solid var(--line); margin-bottom:20px; }
.filter-tab { padding:10px 20px; font-size:13px; font-weight:600; color:#666; cursor:pointer; border-bottom:2px solid transparent; text-decoration:none; }
.filter-tab.active { color:var(--navy); border-bottom-color:var(--navy); }
</style>

<?php $flash = getFlash(); foreach($flash as $f): ?>
<div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<div class="dash-head">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
        <div>
            <h1 style="font-size:20px;font-weight:800;color:var(--navy);margin:0">Quản lý Khuyến mãi</h1>
            <p style="color:#888;font-size:13px;margin:4px 0 0">Thiết lập giá khuyến mãi cho từng sản phẩm. Sản phẩm bật khuyến mãi sẽ hiển thị ở trang Khuyến mại.</p>
        </div>
    </div>
</div>

<form method="get" action="/admin/promotions" class="admin-filter" style="background:#fff;padding:14px 16px;border-radius:8px;border:1px solid var(--line);margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div style="flex:1;min-width:220px">
        <label style="font-size:11px;font-weight:700;color:#888;display:block;margin-bottom:4px">TÌM KIẾM SẢN PHẨM</label>
        <input type="text" name="q" class="form-control" value="<?= e($q) ?>" placeholder="Tên sản phẩm..." style="width:100%">
        <?php if($filter !== 'all'): ?>
        <input type="hidden" name="filter" value="<?= e($filter) ?>">
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-navy" style="height:42px;padding:0 24px">Tìm kiếm</button>
    <a href="/admin/promotions" class="btn btn-outline-navy" style="height:42px;padding:0 20px;display:flex;align-items:center;">Đặt lại</a>
</form>

<div class="filter-tabs">
    <a href="/admin/promotions" class="filter-tab <?= $filter==='all' ? 'active' : '' ?>">Tất cả (<?= $total ?>)</a>
    <a href="/admin/promotions?filter=on_sale" class="filter-tab <?= $filter==='on_sale' ? 'active' : '' ?>">Đang khuyến mãi</a>
    <a href="/admin/promotions?filter=no_sale" class="filter-tab <?= $filter==='no_sale' ? 'active' : '' ?>">Chưa khuyến mãi</a>
</div>

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden">
    <div class="promo-grid">
        <div class="promo-row header">
            <div>Ảnh</div>
            <div>Tên sản phẩm</div>
            <div>Giá gốc</div>
            <div>Giá KM (%)</div>
            <div>Trạng thái</div>
            <div>Thao tác</div>
        </div>
        <?php foreach ($products as $p): ?>
        <div class="promo-row">
            <div>
                <?php if ($p['main_image']): ?>
                <img src="/uploads/products/<?= e($p['main_image']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px" onerror="this.src='/assets/images/no-image.png'">
                <?php else: ?>
                <div style="width:48px;height:48px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#999">N/A</div>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-weight:600;font-size:13px;color:#333"><?= e($p['name']) ?></div>
                <?php if ($p['is_on_sale'] && $p['sale_price'] > 0): ?>
                <div style="font-size:11px;color:#16a34a;margin-top:2px">
                    Giảm <?= number_format((($p['price'] - $p['sale_price'])/$p['price'])*100, 0) ?>%
                    – Tiết kiệm <?= vnd($p['price'] - $p['sale_price']) ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="font-size:13px;color:#333"><?= vnd($p['price']) ?></div>
            <div>
                <form method="post" action="/admin/promotions/<?= $p['id'] ?>/set-sale" class="inline-form" onsubmit="return validateSalePrice(this, <?= $p['price'] ?>)">
                    <?= csrfField() ?>
                    <input type="number" name="sale_price" class="price-input" value="<?= $p['sale_price'] ?: '' ?>" placeholder="0 ₫" min="0">
                    <button type="submit" class="save-btn">Lưu</button>
                </form>
            </div>
            <div>
                <?php if ($p['is_on_sale']): ?>
                <span class="on-sale-badge">Đang KM</span>
                <?php else: ?>
                <span class="no-sale-badge">Chưa KM</span>
                <?php endif; ?>
            </div>
            <div>
                <form method="post" action="/admin/promotions/<?= $p['id'] ?>/toggle" style="margin:0">
                    <?= csrfField() ?>
                    <?php if ($p['is_on_sale']): ?>
                    <button type="submit" class="toggle-btn" style="border-color:#dc2626;color:#dc2626;background:#fef2f2">Tắt KM</button>
                    <?php else: ?>
                    <button type="submit" class="toggle-btn" style="border-color:#16a34a;color:#16a34a;background:#dcfce7">Bật KM</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<div style="padding:12px 16px;display:flex;gap:6px;justify-content:center">
<?php
require_once __DIR__.'/../partials/pagination.php';
renderPagination($page, $totalPages, '', $_GET);
?>
</div>
<?php endif; ?>

<script>
function validateSalePrice(form, originalPrice) {
    var input = form.querySelector('input[name="sale_price"]');
    var val = parseInt(input.value) || 0;
    if (val < 0) {
        alert('Giá khuyến mãi không được âm!');
        input.focus();
        return false;
    }
    if (val > 0 && val >= originalPrice) {
        alert('Giá khuyến mãi (' + val.toLocaleString('vi-VN') + ' ₫) phải nhỏ hơn giá gốc (' + originalPrice.toLocaleString('vi-VN') + ' ₫)!');
        input.focus();
        return false;
    }
    return true;
}
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
