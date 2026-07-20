<?php

// ── Chat start redirect (from product detail) ──


// ── Quên mật khẩu ──
get('/cart', function() {
    header('Location: /customer/cart', true, 301);
    exit;
});

get('/', function() {
    $newDaysRow = dbGet("SELECT value FROM site_config WHERE key='new_product_days'");
    $newDays = intval($newDaysRow['value'] ?? 30);

    $featured = dbAll("SELECT p.*, 'Cooling' AS shop_name,
        (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image,
        COALESCE((SELECT AVG(rating_overall) FROM reviews WHERE product_id=p.id ),0) AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE product_id=p.id ) AS review_count
        FROM products p
        WHERE p.status='published'
        AND p.created_at >= datetime('now', '-" . $newDays . " days', 'localtime')
        ORDER BY p.created_at DESC LIMIT 40");

    $bestSellers = dbAll("SELECT p.*, 'Cooling' AS shop_name,
        (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image,
        COALESCE((SELECT AVG(rating_overall) FROM reviews WHERE product_id=p.id ),0) AS avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE product_id=p.id ) AS review_count,
        COALESCE((SELECT SUM(oi.quantity) FROM order_items oi
            INNER JOIN sub_orders so ON so.id=oi.sub_order_id
            INNER JOIN orders o ON o.id=so.order_id
            WHERE oi.product_id=p.id AND o.payment_status != 'cancelled'), 0) AS total_purchased
        FROM products p
        WHERE p.status='published' AND p.stock>0
        GROUP BY p.id
        HAVING total_purchased >= 20
        ORDER BY total_purchased DESC, avg_rating DESC LIMIT 10");

    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    $categories = dbAll("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.status='published') AS cnt FROM categories c ORDER BY sort_order LIMIT 12");
    $sidebarCategories = dbAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY is_featured DESC, sort_order LIMIT 12");
    $productBrands = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    $trustSteps = dbAll("SELECT * FROM trust_steps WHERE is_active=1 ORDER BY sort_order");
    view('public/home', compact('featured','bestSellers','brands','categories','sidebarCategories','productBrands','trustSteps'));
});


// ── Vouchers Page ────────────────────────────────────────────────────────────
get('/vouchers', function() {
    $vWhere = "(status='active' OR status='1' OR status='published') AND (valid_to IS NULL OR date(valid_to) >= date('now','localtime'))";
    $vPerPage = 4;
    $vTotal = (int)(dbGet("SELECT COUNT(*) AS n FROM vouchers WHERE $vWhere")['n'] ?? 0);
    $vTotalPages = max(1, (int)ceil($vTotal / $vPerPage));
    $vPage = max(1, min((int)($_GET['vpage'] ?? 1), $vTotalPages));
    $vouchers = dbAll("SELECT * FROM vouchers WHERE $vWhere ORDER BY created_at DESC LIMIT $vPerPage OFFSET " . (($vPage-1)*$vPerPage));
    $saleProducts = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.status='published' AND p.original_price IS NOT NULL AND p.original_price > p.price ORDER BY p.updated_at DESC LIMIT 8");
    view('public/vouchers', ['title'=>'Khuyến mãi & Mã giảm giá', 'vouchers'=>$vouchers, 'saleProducts'=>$saleProducts, 'vPage'=>$vPage, 'vTotalPages'=>$vTotalPages]);
});

get('/products', function() {
    $limit = 12; $page = max(1, intval($_GET['page'] ?? 1)); $offset = ($page-1)*$limit;
    $where = ["p.status='published'"]; $params = []; $joins = '';
    if (!empty($_GET['q'])) {
        $qRaw = trim($_GET['q']);
        // Check length
        if (mb_strlen($qRaw) > 100) $qRaw = mb_substr($qRaw, 0, 100);
        // Block dangerous special characters
        $origLen = mb_strlen($qRaw, 'UTF-8');
        // Remove anything that's not a Unicode letter/digit, space, hyphen, dot, comma, slash
        $qRaw = preg_replace('/[^\pL\pN\s.,\-\/]/u', '', $qRaw);
        $qRaw = trim($qRaw);
        if (mb_strlen($qRaw, 'UTF-8') < $origLen) {
            if (empty($qRaw)) {
                // All chars were special - show error, no results
                flash('error', 'Vui long nhap tu khoa hop le.');
                $where[] = '0=1'; // Force empty results
            }
        }
        if (!empty($qRaw)) {
            // Embed search directly in SQL to avoid PDO/SQLite UTF-8 LIKE bug
            $escaped = str_replace("'", "''", $qRaw);
            $normalizedCode = strtoupper((string)preg_replace('/[^a-z0-9]+/i', '', $qRaw));
            $searchSql = "(p.name LIKE '%" . $escaped . "%'"
                . " OR p.oem_code LIKE '%" . $escaped . "%'"
                . " OR p.sku LIKE '%" . $escaped . "%'";
            if ($normalizedCode !== '') {
                $normalizedSql = "REPLACE(REPLACE(REPLACE(REPLACE(UPPER(COALESCE(%s,'')),'-',''),' ',''),'.',''),'/','')";
                $searchSql .= " OR " . sprintf($normalizedSql, 'p.oem_code') . " LIKE '%" . $normalizedCode . "%'"
                    . " OR " . sprintf($normalizedSql, 'p.sku') . " LIKE '%" . $normalizedCode . "%'";
            }
            $where[] = $searchSql . ')';
        }
    }
    if (!empty($_GET['category'])) { $where[] = "p.category_id=?"; $params[] = intval($_GET['category']); }
    else if (!empty($_GET['cat'])) {
        $joins .= " LEFT JOIN categories c ON c.id=p.category_id";
        // Include child categories
        $catRow = dbGet("SELECT id FROM categories WHERE slug=?", [$_GET['cat']]);
        if ($catRow) {
            $catIds = [$catRow['id']];
            $childCats = dbAll("SELECT id FROM categories WHERE parent_id=?", [$catRow['id']]);
            foreach ($childCats as $cc) $catIds[] = $cc['id'];
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $where[] = "p.category_id IN ($placeholders)";
            $params = array_merge($params, $catIds);
        } else {
            $where[] = "c.slug=?"; $params[] = $_GET['cat'];
        }
    }
    if (!empty($_GET['brand_id']) && empty($_GET['model_id']) && empty($_GET['year'])) {
        // Brand-only filter: search fitments + brand_map + car_brand_id + product name
        $brandId = intval($_GET['brand_id']);
        $brandRow = dbGet("SELECT name FROM brands WHERE id=?", [$brandId]);
        $brandName = $brandRow['name'] ?? '';
        $joins .= " LEFT JOIN product_fitments pf ON pf.product_id=p.id";
        $joins .= " LEFT JOIN product_brand_map pbm ON pbm.product_id=p.id";
        $where[] = "(pf.brand_id=? OR pbm.brand_id=? OR p.car_brand_id=? OR p.name LIKE ?)";
        $params[] = $brandId;
        $params[] = $brandId;
        $params[] = $brandId;
        $params[] = '%' . $brandName . '%';
    } else if (!empty($_GET['brand_id']) || !empty($_GET['model_id'])) {
        $brandId = intval($_GET['brand_id'] ?? 0);
        $modelId = intval($_GET['model_id'] ?? 0);
        
        if ($modelId > 0) {
            // Model selected → search fitments + fallback to product name
            $modelRow = dbGet("SELECT name, brand_id FROM car_models WHERE id=?", [$modelId]);
            $modelName = $modelRow['name'] ?? '';
            $joins .= " LEFT JOIN product_fitments pf ON pf.product_id=p.id";
            $joins .= " LEFT JOIN product_brand_map pbm ON pbm.product_id=p.id";
            // Match: fitment model_id OR product name contains model name
            $modelWhere = "(pf.model_id=? OR p.name LIKE ?)";
            $params[] = $modelId;
            $params[] = '%' . $modelName . '%';
            if ($brandId > 0) {
                $brandRow = dbGet("SELECT name FROM brands WHERE id=?", [$brandId]);
                $brandName = $brandRow['name'] ?? '';
                $modelWhere .= " AND (pf.brand_id=? OR pbm.brand_id=? OR p.car_brand_id=? OR p.name LIKE ?)";
                $params[] = $brandId;
                $params[] = $brandId;
                $params[] = $brandId;
                $params[] = '%' . $brandName . '%';
            }
            $where[] = $modelWhere;
        } elseif ($brandId > 0) {
            // Brand only → search across fitments + brand_map + car_brand_id + product name
            $joins .= " LEFT JOIN product_fitments pf ON pf.product_id=p.id";
            $joins .= " LEFT JOIN product_brand_map pbm ON pbm.product_id=p.id";
            $brandRow = dbGet("SELECT name FROM brands WHERE id=?", [$brandId]);
            $brandName = $brandRow['name'] ?? '';
            $where[] = "(pf.brand_id=? OR pbm.brand_id=? OR p.car_brand_id=? OR p.name LIKE ?)";
            $params[] = $brandId;
            $params[] = $brandId;
            $params[] = $brandId;
            $params[] = '%' . $brandName . '%';
        }
    }
    // Year filter
    if (!empty($_GET['year'])) {
        $year = intval($_GET['year']);
        $joins2 = '';
        if (strpos($joins, 'product_fitments') === false) {
            $joins .= " LEFT JOIN product_fitments pf ON pf.product_id=p.id";
        }
        $where[] = "((pf.year_from <= ? AND (pf.year_to IS NULL OR pf.year_to >= ?)) OR (pf.year_from IS NULL AND p.name LIKE ?))";
        $params[] = $year;
        $params[] = $year;
        $params[] = '%' . $year . '%';
    }

    if (!empty($_GET['promo'])) {
        // Show only products with active promotions
        $where[] = "p.is_on_sale=1 AND p.sale_price > 0";
    }
    if (!empty($_GET['pb'])) { $where[] = "(p.part_brand=? OR p.part_brand LIKE ? OR p.part_brand LIKE ? OR p.part_brand LIKE ?)"; $params[] = $_GET['pb']; $params[] = $_GET['pb'].',%'; $params[] = '%, '.$_GET['pb'].',%'; $params[] = '%, '.$_GET['pb']; }
    $sort = match($_GET['sort'] ?? 'newest') { 'bestseller'=>'p.sold_count DESC','price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','rating'=>'p.rating_avg DESC', default=>'p.published_at DESC' };
    $wStr = implode(' AND ', $where);
    $groupBy = (!empty($_GET['brand_id']) || !empty($_GET['model_id'])) ? 'GROUP BY p.id' : '';
    $_sql_total = "SELECT COUNT(DISTINCT p.id) AS n FROM products p $joins WHERE $wStr";
    // Also test with fresh PDO using same SQL string
    $total = (int)dbGet("SELECT COUNT(DISTINCT p.id) AS n FROM products p $joins WHERE $wStr", $params)['n'];
    $totalPages = max(1, (int)ceil($total/$limit));

    // Keep stale or malformed pagination URLs from serving empty 200 pages.
    if (array_key_exists('page', $_GET)) {
        $rawPage = is_scalar($_GET['page']) ? trim((string)$_GET['page']) : '';
        $targetPage = min($page, $totalPages);
        if ($targetPage <= 1 || $page > $totalPages || $rawPage !== (string)$page) {
            $normalizedQuery = $_GET;
            if ($targetPage <= 1) {
                unset($normalizedQuery['page']);
            } else {
                $normalizedQuery['page'] = $targetPage;
            }
            $queryString = http_build_query($normalizedQuery, '', '&', PHP_QUERY_RFC3986);
            header('Location: /products' . ($queryString !== '' ? '?' . $queryString : ''), true, 301);
            exit;
        }
    }

    $products = dbAll("SELECT DISTINCT p.*, 'Cooling' AS shop_name, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p $joins WHERE $wStr ORDER BY $sort LIMIT $limit OFFSET $offset", $params);
    $categories = dbAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY sort_order");
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    $activeVehicle = null;
    if (!empty($_GET['brand_id'])) {
        $activeVehicle = dbGet("SELECT b.name AS brand_name FROM brands b WHERE b.id=?", [intval($_GET['brand_id'])]);
    }
    $productBrands = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    $seo = [];
    if ($page > 1 && count($_GET) === 1 && array_key_exists('page', $_GET)) {
        $seo['canonical'] = 'https://coolingsystems.vn/products?page=' . $page;
    }
    view('public/products', compact('products','total','page','totalPages','limit','categories','brands','activeVehicle','productBrands','seo'));
});

// Slug-based SEO-friendly URL: /products/dan-lanh-toyota-camry-2018-xyz
get('/products/:slug', function($p) {
    // Browser links may carry legacy percent-encoded slugs from old imports.
    // Decode once so they can be resolved through the redirect history.
    $param = rawurldecode((string)$p['slug']);
    $product = null;
    $productSelect = "SELECT p.*, COALESCE(pt.shop_name,'Cooling') AS shop_name, pt.shop_slug, pt.id AS partner_id, b.name as car_brand_name, c.name as category_name FROM products p LEFT JOIN partners pt ON pt.id=p.partner_id LEFT JOIN brands b ON b.id=p.car_brand_id LEFT JOIN categories c ON c.id=p.category_id";
    // Legacy numeric IDs were reused after catalog resets, so they cannot be redirected safely.
    if(is_numeric($param)) {
        $product = null;
    } else {
        // Lookup by slug
        $product = dbGet($productSelect . " WHERE p.slug=? AND p.status='published'", [$param]);

        // Preserve every previous slug recorded during migrations or product edits.
        if (!$product) {
            $redirectTable = dbGet("SELECT 1 AS found FROM sqlite_master WHERE type='table' AND name='product_slug_redirects'");
            if ($redirectTable) {
                $redirectProduct = dbGet($productSelect . " INNER JOIN product_slug_redirects psr ON psr.product_id=p.id WHERE psr.slug=? AND p.status='published'", [$param]);
                if ($redirectProduct) {
                    header('Location: ' . productPath($redirectProduct), true, 301);
                    exit;
                }
            }
        }

        // Fallback for legacy installs that have not created the redirect history yet.
        if (!$product) {
            $publishedProducts = dbAll($productSelect . " WHERE p.status='published'");
            foreach ($publishedProducts as $candidate) {
                if (legacyProductSlug((string)$candidate['name']) === $param) {
                    header('Location: ' . productPath($candidate), true, 301);
                    exit;
                }
            }
        }
    }
    if (!$product) { http_response_code(404); view('errors/404',['title'=>'SP không tìm thấy']); return; }
    $images = dbAll("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, is_main DESC", [(int)$product['id']]);
    $reviews = dbAll("SELECT r.*, u.full_name, u.avatar FROM reviews r INNER JOIN users u ON u.id=r.user_id WHERE r.product_id=? AND r.status='published' ORDER BY r.created_at DESC", [(int)$product['id']]);
    $related = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.category_id=? AND p.id!=? AND p.status='published' ORDER BY p.rating_avg DESC LIMIT 5", [$product['category_id'], (int)$product['id']]);
    $fitments = dbAll("SELECT b.name AS brand_name, m.name AS model_name, pf.year_from, pf.year_to FROM product_fitments pf INNER JOIN brands b ON b.id=pf.brand_id LEFT JOIN car_models m ON m.id=pf.model_id WHERE pf.product_id=?", [(int)$product['id']]);
    dbRun("UPDATE products SET view_count=view_count+1 WHERE id=?", [(int)$product['id']]);
    // log_product_view: ghi lai luot truy cap (khach + thanh vien) vao audit_logs, bo qua bot
    $__pvUa = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternal|headless/i', $__pvUa)) {
        $__pvU = currentUser();
        dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)",
            [$__pvU['id'] ?? null, $__pvU['role'] ?? 'guest', 'view', 'product', (int)$product['id'], mb_substr((string)($product['name'] ?? ''), 0, 120), $_SERVER['REMOTE_ADDR'] ?? '', mb_substr($__pvUa, 0, 250)]);
    }
    view('public/product-detail', compact('product','images','reviews','related','fitments'));
});

get('/brands', function() {
    $brands = dbAll("SELECT b.*, (
        SELECT COUNT(DISTINCT p2.id) FROM products p2
        LEFT JOIN product_fitments pf2 ON pf2.product_id=p2.id
        LEFT JOIN product_brand_map pbm2 ON pbm2.product_id=p2.id
        INNER JOIN partners pt2 ON pt2.id=p2.partner_id AND pt2.status='active'
        WHERE p2.status='published'
        AND (pf2.brand_id=b.id OR pbm2.brand_id=b.id OR p2.car_brand_id=b.id OR p2.name LIKE '%' || b.name || '%')
    ) AS real_count FROM brands b ORDER BY b.sort_order, b.name");
    view('public/brands', ['title'=>'Phụ tùng theo hãng','brands'=>$brands]);
});

get('/shops/:slug', function($p) {
    $partner = dbGet("SELECT * FROM partners WHERE shop_slug=? AND status='active'", [$p['slug']]);
    if (!$partner) { http_response_code(404); view('errors/404',['title'=>'Gian hàng không tìm thấy']); return; }
    $products = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.partner_id=? AND p.status='published' ORDER BY p.sold_count DESC", [$partner['id']]);
    $pUser = dbGet("SELECT full_name, created_at FROM users WHERE id=?", [$partner['user_id']]);
    view('public/shop', ['title'=>$partner['shop_name'],'partner'=>$partner,'products'=>$products,'pUser'=>$pUser]);
});

get('/about', function() { view('public/static', ['title'=>'Giới thiệu','page'=>'gioi-thieu']); });
get('/lien-he', function() { redirect('/contact'); });
get('/contact', function() {
    view('public/contact', ['title' => 'Liên hệ — Cooling Parts & Service', 'success' => false, 'errors' => []]);
});
post('/contact', function() {
    csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors = [];

    // Required fields check
    if (empty($name)) $errors[] = 'Vui lòng nhập họ và tên.';
    elseif (mb_strlen($name) < 2) $errors[] = 'Họ và tên phải có ít nhất 2 ký tự.';
    elseif (preg_match('/[<>";\'`!@#$%^&*()=+{}\[\]|\\]/', $name)) $errors[] = 'Họ và tên không được chứa ký tự đặc biệt.';

    if (empty($email)) $errors[] = 'Vui lòng nhập email.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không đúng định dạng.';
    elseif (!preg_match('/^[a-zA-Z0-9._%+\-]+@(gmail\.com|yahoo\.com|outlook\.com|hotmail\.com|[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})$/', $email)) {
        $errors[] = 'Email không hợp lệ. Vui lòng nhập email đúng (ví dụ: ten@gmail.com).';
    } else {
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        $invalidDomains = ['gmil.com', 'gamil.com', 'yaho.com', 'yahu.com', 'hotmal.com', 'outlok.com', 'gml.com', 'gma.com', 'gmai.com', 'gmail.con', 'gmail.co'];
        if (in_array($domain, $invalidDomains)) {
            $errors[] = 'Email có vẻ bị sai lỗi chính tả tên miền (ví dụ: gmil.com -> gmail.com). Vui lòng kiểm tra lại.';
        }
    }

    if (empty($phone)) $errors[] = 'Vui lòng nhập số điện thoại.';
    elseif (!preg_match('/^0[0-9]{9}$/', $phone)) $errors[] = 'Số điện thoại phải bắt đầu từ 0 và có đúng 10 chữ số.';

    if (empty($message)) $errors[] = 'Vui lòng nhập nội dung.';
    elseif (mb_strlen($message) < 10) $errors[] = 'Nội dung phải có ít nhất 10 ký tự.';
    elseif (mb_strlen($message) > 100) $errors[] = 'Nội dung tối đa 100 ký tự.';

    if (!empty($errors)) {
        view('public/contact', ['title' => 'Liên hệ — Cooling Parts & Service', 'success' => false, 'errors' => $errors]);
        return;
    }

    // Map subject to label
    $subjectLabels = ['product'=>'Hỏi về sản phẩm','order'=>'Đơn hàng / Vận chuyển','warranty'=>'Bảo hành / Đổi trả','partner'=>'Hợp tác / Đại lý','other'=>'Khác'];
    $subjectLabel = $subjectLabels[$subject] ?? $subject;

    dbRun("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?,?,?,?,?,?,datetime('now','localtime'))",
        [$name, $email, $phone, $subjectLabel, $message, 'new']);

    // Notify admin (chuông thông báo) về tin nhắn liên hệ mới
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('contact', ?, ?, '/admin/contacts')",
        ['Tin nhắn liên hệ mới', 'Khách "' . $name . '" vừa gửi tin nhắn (' . $subjectLabel . '): ' . mb_substr($message, 0, 60)]);

    view('public/contact', ['title' => 'Liên hệ — Cooling Parts & Service', 'success' => true, 'errors' => []]);
});

get('/stores', function() { view('public/stores', ['title'=>'Hệ thống cửa hàng']); });
get('/careers', function() { view('public/static', ['title'=>'Tuyển dụng','page'=>'tuyen-dung']); });
get('/cam-ket', function() { view('public/static', ['title'=>'4 Bước cam kết','page'=>'4-buoc-cam-ket']); });
get('/policies', function() {
    header('Location: /policies/huong-dan-mua-hang', true, 301);
    exit;
});
get('/policies/:slug', function($p) {
    $slug = $p['slug'] ?? '';
    $page = dbGet("SELECT * FROM static_pages WHERE slug=?", [$slug]);
    if (!$page) { http_response_code(404); view('errors/404', ['title'=>'404']); return; }
    view('public/static-page', ['title' => $page['title'], 'page' => $page]);
});

// ── Promotions page ───────────────────────────────────────────────────────────
get('/promotions', function() {
    $limit = 12; $page = max(1, intval($_GET['page'] ?? 1)); $offset = ($page-1)*$limit;
    $products = dbAll("SELECT p.*, 'Cooling' AS shop_name,
        (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image
        FROM products p
        LEFT JOIN partners pt ON pt.id=p.partner_id
        WHERE p.status='published' AND p.original_price IS NOT NULL AND p.original_price > p.price
        ORDER BY p.updated_at DESC LIMIT $limit OFFSET $offset");
    $total = dbGet("SELECT COUNT(*) as n FROM products p WHERE p.status='published' AND p.original_price IS NOT NULL AND p.original_price > p.price")['n'];
    $totalPages = max(1, ceil($total/$limit));
    $categories = dbAll("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY sort_order");
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    view('public/promotions', compact('products','total','page','totalPages','limit','categories','brands'));
});

// ── Sitemap XML ──────────────────────────────────────────────────────────────
get('/sitemap.xml', function() {
    http_response_code(200);
    header('Content-Type: application/xml; charset=UTF-8');
    $products = dbAll("SELECT p.id, p.slug, p.name, p.updated_at,
        (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC, sort_order ASC LIMIT 1) AS main_image
        FROM products p
        WHERE p.status='published' AND COALESCE(p.is_indexed,1)=1
        ORDER BY p.updated_at DESC LIMIT 50000");
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

    // Static pages
    $statics = [
        '/',
        '/products',
        '/brands',
        '/about',
        '/contact',
        '/news',
        '/stores',
        '/policies/huong-dan-mua-hang',
        '/policies/chinh-sach-doi-tra',
        '/policies/chinh-sach-bao-hanh',
        '/policies/dieu-khoan-bao-mat',
    ];
    foreach ($statics as $s) {
        echo '<url><loc>https://coolingsystems.vn'.e($s).'</loc>';
        echo '<changefreq>weekly</changefreq><priority>'.($s==='/'?'1.0':'0.8').'</priority>';
        echo '</url>';
    }

    // Products
    foreach ($products as $p) {
        $mod = substr($p['updated_at'] ?? '', 0, 10);
        echo '<url>';
        echo '<loc>'.e(productCanonicalUrl($p)).'</loc>';
        echo '<changefreq>weekly</changefreq><priority>0.9</priority>';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $mod)) echo '<lastmod>'.$mod.'</lastmod>';
        if (!empty($p['main_image'])) {
            $imageUrl = 'https://coolingsystems.vn/uploads/products/' . str_replace('%2F', '/', rawurlencode($p['main_image']));
            echo '<image:image><image:loc>'.e($imageUrl).'</image:loc><image:title>'.e(seoPlainText($p['name'])).'</image:title></image:image>';
        }
        echo '</url>';
    }

    echo '</urlset>';
    exit;
});

// ── robots.txt ───────────────────────────────────────────────────────────────
get('/robots.txt', function() {
    header('Content-Type: text/plain');
    readfile(__DIR__.'/../public/robots.txt');
    exit;
});

get('/about/story', function() { view('public/static', ['title'=>'Câu chuyện Cooling','page'=>'cau-chuyen-cooling']); });
get('/about/careers', function() { view('public/static', ['title'=>'Tuyển dụng','page'=>'tuyen-dung']); });

// ── PUBLIC NEWS ──────────────────────────────────────────────────────────────
get('/news', function() {
    $perPage=9; $pg=max(1,intval($_GET['page']??1)); $offset=($pg-1)*$perPage;
    $total=dbGet("SELECT COUNT(*) AS n FROM articles WHERE status='published'")['n']??0;
    $pages=ceil($total/$perPage);
    $articles=dbAll("SELECT * FROM articles WHERE status='published' ORDER BY published_at DESC, created_at DESC LIMIT ? OFFSET ?",[$perPage,$offset]);
    view('public/news-list',['title'=>'Tin tức','articles'=>$articles,'totalPages'=>$pages,'currentPage'=>$pg]);
});
get('/news/:slug', function($p) {
    $article=dbGet("SELECT * FROM articles WHERE slug=? AND status='published'",[$p['slug']]);
    if (!$article) { http_response_code(404); echo '404'; return; }
    $related=dbAll("SELECT id,title,slug,thumbnail,published_at FROM articles WHERE status='published' AND id<>? ORDER BY published_at DESC LIMIT 3",[$article['id']]);
    view('public/news-detail',['title'=>$article['title'],'article'=>$article,'related'=>$related]);
});

// ── NEWSLETTER ──────────────────────────────────────────────────────────────
post('/newsletter/subscribe', function() {
    header('Content-Type: application/json');
    global $user;
    $user = currentUser();
    if (!$user || strpos($user['email'] ?? '', '@guest.local') !== false) { echo json_encode(['ok'=>false,'msg'=>'Vui lòng đăng ký tài khoản hoặc đăng nhập để nhận ưu đãi Voucher']); exit; }
    $email = strtolower(trim($_POST['email'] ?? ''));
    // Validate gmail.com
    if (!preg_match('/^[a-z0-9._%+\-]+@gmail\.com$/', $email)) {
        echo json_encode(['ok'=>false,'msg'=>'Chi chap nhan email @gmail.com.']); exit;
    }
    // Must match logged in user email
    if ($email !== strtolower($user['email'] ?? '')) {
        echo json_encode(['ok'=>false,'msg'=>'Email khong khop voi tai khoan dang dang nhap.']); exit;
    }
    // Check if user already has any orders (not first-time buyer)
    $hasOrder = dbGet("SELECT 1 FROM orders WHERE user_id=?", [$user['id']]);
    if ($hasOrder) {
        echo json_encode(['ok'=>false,'msg'=>'Tài khoản của bạn đã đặt đơn hàng trước đó. Ưu đãi này chỉ dành cho đơn hàng đầu tiên.']);
        exit;
    }
    // Check already subscribed
    $already = dbGet("SELECT 1 FROM newsletter_subscribers WHERE email=?", [$email]);
    if ($already) { echo json_encode(['ok'=>false,'already'=>true,'msg'=>'Email này đã đăng ký nhận ưu đãi rồi. Mã UUDAI100K đã được lưu trong tài khoản của bạn.']); exit; }
    // Subscribe
    dbRun("CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INTEGER PRIMARY KEY, email TEXT UNIQUE, user_id INTEGER, created_at DATETIME)");
    dbRun("INSERT OR IGNORE INTO newsletter_subscribers (email, user_id, created_at) VALUES (?,?,datetime('now','localtime'))", [$email, $user['id']]);
    // Create voucher UUDAI100K if not exists
    $v = dbGet("SELECT id FROM vouchers WHERE code='UUDAI100K'");
    if (!$v) {
        dbInsert("INSERT OR IGNORE INTO vouchers (code, discount_amount, discount_type, max_uses, min_order_amount, is_active, created_at) VALUES ('UUDAI100K', 100000, 'fixed', 0, 0, 1, datetime('now','localtime'))");
    }
    // Save to user_saved_vouchers
    dbRun("INSERT OR IGNORE INTO user_saved_vouchers (user_id, code, discount_amount, description, created_at) VALUES (?,?,100000,'Uu dai don dau tien - Chi dung 1 lan',datetime('now','localtime'))", [$user['id'], 'UUDAI100K']);
    echo json_encode(['ok'=>true,'voucher_code'=>'UUDAI100K']);
    exit;
});

post('/newsletter', function() {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            dbRun("CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INTEGER PRIMARY KEY, email TEXT UNIQUE, created_at DATETIME)");
            dbRun("INSERT OR IGNORE INTO newsletter_subscribers (email, created_at) VALUES (?, datetime('now', 'localtime'))", [$email]);
        } catch (\Exception $e) {}
        flash('success', 'Cảm ơn bạn đã đăng ký nhận tin!');
    } else {
        flash('error', 'Email không hợp lệ.');
    }
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
});


// Product Brands public pages
get('/product-brands', function() {
    $productBrands = dbAll("SELECT pb.* FROM product_brands pb
        WHERE EXISTS (
            SELECT 1 FROM products p
            WHERE p.status='published'
            AND instr(',' || replace(lower(COALESCE(p.part_brand,'')), ', ', ',') || ',', ',' || lower(pb.name) || ',') > 0
        )
        ORDER BY pb.sort_order, pb.name");
    $seo = empty($productBrands) ? ['noindex' => true] : [];
    view('public/product-brands/index', ['title' => 'Thuong hieu san pham', 'productBrands' => $productBrands, 'seo' => $seo]);
});

get('/product-brands/:slug', function($p) {
    $brand = dbGet("SELECT * FROM product_brands WHERE slug=? OR id=?", [$p['slug'], (int)$p['slug']]);
    if (!$brand) { http_response_code(404); echo '404'; exit; }
    $limit = 12; $page = max(1, intval($_GET['page'] ?? 1)); $offset = ($page-1)*$limit;
    $bWhere = "p.status='published' AND instr(',' || replace(lower(COALESCE(p.part_brand,'')), ', ', ',') || ',', ',' || lower(?) || ',') > 0";
    $bParams = [$brand['name']];
    $total = (int)dbGet("SELECT COUNT(*) AS n FROM products p WHERE $bWhere", $bParams)['n'];
    if ($total === 0) {
        http_response_code(404);
        view('errors/404', ['title' => 'Thuong hieu chua co san pham']);
        return;
    }
    $totalPages = max(1, ceil($total/$limit));
    $products = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image, NULL AS shop_name, NULL AS partner_id FROM products p WHERE $bWhere ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset", $bParams);
    view('public/product-brands/detail', ['title' => $brand['name'] . ' - Thuong hieu', 'brand' => $brand, 'products' => $products, 'page' => $page, 'totalPages' => $totalPages, 'total' => $total]);
});


// Apply voucher to cart session (AJAX, for newsletter use-now)
post('/cart/apply-voucher', function() {
    header('Content-Type: application/json');
    $code = strtoupper(trim($_POST['code'] ?? ''));
    if (!$code) { echo json_encode(['ok'=>false]); exit; }
    $v = dbGet("SELECT * FROM vouchers WHERE code=? AND (is_active=1 OR is_active IS NULL)", [$code]);
    if (!$v) { echo json_encode(['ok'=>false,'msg'=>'Ma khong hop le.']); exit; }
    $_SESSION['cart_voucher'] = ['id'=>$v['id'],'code'=>$v['code'],'amount'=>$v['discount_amount'],'type'=>$v['discount_type']];
    echo json_encode(['ok'=>true,'code'=>$code,'amount'=>$v['discount_amount']]);
    exit;
});

// ── Customer Chat Routes ──


post('/reviews/:id/react', function($p) {
    header('Content-Type: application/json');
    global $user;
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false,'msg'=>'Đăng nhập để thả cảm xúc']); exit; }
    $reviewId = intval($p['id']);
    $reaction = trim($_POST['reaction'] ?? '');
    $allowed = ['like','love','haha','wow','sad'];
    if (!in_array($reaction, $allowed)) { echo json_encode(['ok'=>false]); exit; }
    
    // Toggle: nếu đã react cùng loại → xóa, khác loại → đổi
    $existing = dbGet("SELECT reaction FROM review_reactions WHERE review_id=? AND user_id=?", [$reviewId, $user['id']]);
    $active = false;
    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            dbRun("DELETE FROM review_reactions WHERE review_id=? AND user_id=?", [$reviewId, $user['id']]);
            $active = false;
        } else {
            dbRun("UPDATE review_reactions SET reaction=? WHERE review_id=? AND user_id=?", [$reaction, $reviewId, $user['id']]);
            $active = true;
        }
    } else {
        dbRun("INSERT INTO review_reactions (review_id, user_id, reaction) VALUES (?,?,?)", [$reviewId, $user['id'], $reaction]);
        $active = true;
    }
    
    // Return updated counts
    $rows = dbAll("SELECT reaction, COUNT(*) as cnt FROM review_reactions WHERE review_id=? GROUP BY reaction", [$reviewId]);
    $counts = [];
    foreach ($rows as $row) $counts[$row['reaction']] = intval($row['cnt']);
    
    echo json_encode(['ok'=>true, 'active'=>$active, 'counts'=>$counts]);
    exit;
});
// ── Static page by slug ──
get('/page/:slug', function($params) {
    $slug = $params['slug'] ?? '';
    $page = dbGet("SELECT * FROM static_pages WHERE slug=?", [$slug]);
    if (!$page) { http_response_code(404); view('errors/404', ['title'=>'404']); return; }
    view('public/static-page', ['title' => $page['title'], 'page' => $page]);
});

get('/chat', function() {
    redirect('/customer/chat');
});

