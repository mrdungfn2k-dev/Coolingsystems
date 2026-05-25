<?php
get('/admin/login', function() { view('admin/login', ['title' => 'Đăng nhập Quản trị']); });

post('/admin/login', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (!$email || !$password) { flash('error','Vui lòng nhập email và mật khẩu.'); redirect('/admin/login'); }
    $user = dbGet('SELECT * FROM users WHERE email=?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email hoặc mật khẩu không đúng.'); redirect('/admin/login'); }
    if ($user['role'] !== 'admin') { flash('error','Tài khoản không có quyền quản trị.'); redirect('/admin/login'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản bị khóa.'); redirect('/admin/login'); }
    loginUser($user['id']);
    flash('success','Xin chào, '.$user['full_name'].'!');
    redirect('/admin');
});

get('/admin/logout', function() { logout('/admin/login'); });

get('/admin', function() {
    $user = requireStaffPermission('reports', '/admin/login');
    
    // Xử lý bộ lọc biểu đồ
    $period = $_GET['period'] ?? 'month'; // week, month, year
    $dateFilter = "date(created_at) >= date('now', '-30 days')";
    if ($period === 'week') $dateFilter = "date(created_at) >= date('now', '-7 days')";
    if ($period === 'year') $dateFilter = "date(created_at) >= date('now', '-365 days')";
    $dateFilterO = str_replace('created_at', 'o.created_at', $dateFilter);

    $kpis = dbGet("SELECT 
        (SELECT COUNT(*) FROM users WHERE role='customer' AND status='active') AS customers, 
        (SELECT COUNT(*) FROM products WHERE status='published') AS products, 
        (SELECT COUNT(*) FROM orders) AS orders_total, 
        (SELECT COALESCE(SUM(grand_total),0) - COALESCE(SUM(refund_amount),0) FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered')) AS revenue,
        (SELECT COALESCE(SUM(grand_total),0) - COALESCE(SUM(refund_amount),0) FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered') AND date(created_at) = date('now')) AS today_revenue,
        (SELECT COALESCE(SUM(grand_total),0) - COALESCE(SUM(refund_amount),0) FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered') AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')) AS month_revenue,
        (SELECT COALESCE(SUM(oi.quantity * COALESCE(p.cost_price, 0)), 0)
            FROM order_items oi
            INNER JOIN sub_orders so ON so.id = oi.sub_order_id
            INNER JOIN orders o ON o.id = so.order_id
            INNER JOIN products p ON p.id = oi.product_id
            WHERE o.payment_status = 'paid' AND o.delivery_status = 'completed'
            AND strftime('%Y-%m', o.created_at) = strftime('%Y-%m', 'now')
        ) AS month_import_cost");
    // Lợi nhuận tháng = Doanh thu tháng - Chi phí nhập hàng đã bán trong tháng (completed orders)
    $kpis['monthly_profit'] = ($kpis['month_revenue'] ?? 0) - ($kpis['month_import_cost'] ?? 0);
        
    // Lấy dữ liệu biểu đồ doanh thu (theo ngày nếu week/month, theo tháng nếu year)
    $groupFormat = $period === 'year' ? '%Y-%m' : '%Y-%m-%d';
    // Chart data: revenue per period
    $chartData = dbAll("SELECT strftime(?, created_at) as label, SUM(grand_total) as val FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered') AND $dateFilter GROUP BY label ORDER BY label ASC", [$groupFormat]);
    
    // Chart data: qty sold per period  
    $chartQty = dbAll("SELECT strftime(?, o.created_at) as label, SUM(oi.quantity) as qty 
        FROM orders o 
        INNER JOIN sub_orders so ON so.order_id=o.id 
        INNER JOIN order_items oi ON oi.sub_order_id=so.id 
        WHERE o.payment_status='paid' AND o.delivery_status IN ('completed','delivered') AND $dateFilterO 
        GROUP BY label ORDER BY label ASC", [$groupFormat]);
    
    // Chart data: import cost per period (profit = revenue - import cost of sold items)
    $chartCost = dbAll("SELECT strftime(?, o.created_at) as label, SUM(oi.quantity * COALESCE(p.cost_price,0)) as cost 
        FROM orders o 
        INNER JOIN sub_orders so ON so.order_id=o.id 
        INNER JOIN order_items oi ON oi.sub_order_id=so.id 
        INNER JOIN products p ON p.id=oi.product_id 
        WHERE o.payment_status='paid' AND o.delivery_status IN ('completed','delivered') AND $dateFilterO 
        GROUP BY label ORDER BY label ASC", [$groupFormat]);
    
    // Merge qty + cost into chartData
    $qtyMap = []; foreach($chartQty as $q) $qtyMap[$q['label']] = $q['qty'];
    $costMap = []; foreach($chartCost as $c) $costMap[$c['label']] = $c['cost'];
    foreach($chartData as &$cd) {
        $cd['qty'] = $qtyMap[$cd['label']] ?? 0;
        $cd['cost'] = $costMap[$cd['label']] ?? 0;
        $cd['profit'] = ($cd['val'] ?? 0) - ($cd['cost'] ?? 0);
    }
    unset($cd);
    
    // Phân trang đơn hàng trên dashboard
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    $totalOrders = dbGet("SELECT COUNT(*) as c FROM orders")['c'];
    $recentOrders = dbAll("SELECT o.id, o.code, o.grand_total, o.payment_method, o.payment_status, o.created_at, u.full_name FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset");
    
    // Stock alerts: products below min_stock or above max_stock
    $lowStockProducts = dbAll("SELECT id, name, sku, stock, min_stock, max_stock FROM products WHERE status='published' AND stock <= min_stock AND min_stock > 0 ORDER BY stock ASC LIMIT 20");
    $overStockProducts = dbAll("SELECT id, name, sku, stock, min_stock, max_stock FROM products WHERE status='published' AND stock >= max_stock AND max_stock > 0 ORDER BY stock DESC LIMIT 20");

    view('admin/dashboard', [
        'title'=>'Dashboard Admin',
        'role'=>'admin',
        'kpis'=>$kpis,
        'recentOrders'=>$recentOrders,
        'chartData' => $chartData,
        'period' => $period,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'dateFrom' => $dateFrom ?? '',
        'dateTo' => $dateTo ?? '',
        'page' => $page,
        'totalPages' => ceil($totalOrders / $perPage),
        'lowStockProducts' => $lowStockProducts,
        'overStockProducts' => $overStockProducts,
    ]);
});

get('/admin/partners', function() {
    $user = requireStaffPermission('returns', '/auth/login');
    $status = $_GET['status'] ?? 'all';
    $where = $status !== 'all' ? "WHERE p.status='".addslashes($status)."'" : '';
    $partners = dbAll("SELECT p.*, u.email, u.full_name FROM partners p INNER JOIN users u ON u.id=p.user_id $where ORDER BY p.created_at DESC");
    view('admin/partners', ['title'=>'Quản lý đối tác','role'=>'admin','partners'=>$partners,'filterStatus'=>$status]);
});

get('/admin/products', function() {    requireStaffPermission('products', '/admin/login');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $tab=$_GET['tab']??'all'; $catId=intval($_GET['cat']??0);
    $brandId=intval($_GET['brand_id']??0); $partBrand=trim($_GET['part_brand']??'');
    $where='WHERE 1=1'; $params=[];
    if($tab==='draft'){$where.=" AND p.status='draft'";}
    elseif($tab==='published'){$where.=" AND p.status='published'";}
    if($q){$where.=" AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l]);}
    if($catId){$where.=" AND p.category_id=?"; $params[]=$catId;}
    if($brandId){$where.=" AND p.car_brand_id=?"; $params[]=$brandId;}
    if($partBrand){$where.=" AND p.part_brand=?"; $params[]=$partBrand;}
    $total=dbGet("SELECT COUNT(*) AS n FROM products p $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $products=dbAll("SELECT p.*,COALESCE(pt.shop_name,'Admin') AS shop_name,c.name AS cat_name,b.name AS brand_name FROM products p LEFT JOIN partners pt ON pt.id=p.partner_id LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.car_brand_id $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?",$p2);
    $categories=dbAll("SELECT * FROM categories ORDER BY sort_order");
    $carBrands=dbAll("SELECT * FROM brands ORDER BY name");
    $partBrands=dbAll("SELECT DISTINCT part_brand FROM products WHERE part_brand IS NOT NULL AND part_brand != '' ORDER BY part_brand");
    view('admin/products',['title'=>'San pham','role'=>'admin','products'=>$products,'categories'=>$categories,'carBrands'=>$carBrands,'partBrands'=>$partBrands,'tab'=>$tab,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages,'filterBrandId'=>$brandId,'filterPartBrand'=>$partBrand]);
});


// Reorder product images (drag-and-drop)
post('/admin/products/reorder-images', function() {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    if (!is_array($ids) || empty($ids)) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => 'No IDs']);
        exit;
    }
    foreach ($ids as $order => $imgId) {
        $isMain = $order === 0 ? 1 : 0;
        dbRun("UPDATE product_images SET sort_order=?, is_main=? WHERE id=?", [$order, $isMain, intval($imgId)]);
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
});


// Export products to CSV
get('/admin/products/export-csv', function() {
    requireStaffPermission('products', '/admin/login');
    $template = $_GET['template'] ?? '';
    $selectedIds = [];
    if (!empty($_GET['ids'])) {
        $selectedIds = array_map('intval', explode(',', $_GET['ids']));
        $selectedIds = array_filter($selectedIds, function($v){ return $v > 0; });
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="products_' . date('Ymd_His') . '.csv"');
    
    $out = fopen('php://output', 'w');
    // BOM for UTF-8
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    
    $headers = ['name','sku','oem_code','part_brand','category_id','price','price_before_tax','stock','warranty_months','description','status'];
    fputcsv($out, $headers);
    
    if (!$template) {
        $whereClause = '';
        if (!empty($selectedIds)) {
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $whereClause = " WHERE id IN ($placeholders)";
            $products = dbAll("SELECT name,sku,oem_code,part_brand,category_id,price,price_before_tax,stock,warranty_months,description,status FROM products" . $whereClause . " ORDER BY id DESC", $selectedIds);
        } else {
            $products = dbAll("SELECT name,sku,oem_code,part_brand,category_id,price,price_before_tax,stock,warranty_months,description,status FROM products ORDER BY id DESC");
        }
        foreach ($products as $p) {
            fputcsv($out, [
                $p['name'], $p['sku'], $p['oem_code']??'', $p['part_brand']??'',
                $p['category_id']??'', $p['price'], $p['price_before_tax']??0,
                $p['stock'], $p['warranty_months']??12,
                strip_tags($p['description']??''), $p['status']
            ]);
        }
    }
    
    fclose($out);
    exit;
});

// Import products from CSV
// Import products from CSV
post('/admin/products/import-csv', function() {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    
    if (empty($_FILES['csv_file']['tmp_name'])) {
        flash('error', 'Vui lòng chọn file CSV.');
        redirect('/admin/products');
        return;
    }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/products');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/products');
        return;
    }
    // ============================================

    
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$file) {
        flash('error', 'Không thể đọc file.');
        redirect('/admin/products');
        return;
    }
    
    $dupSku = $_POST['dup_sku'] ?? 'skip';
    $defaultStatus = $_POST['default_status'] ?? 'draft';
    $updateStock = $_POST['update_stock'] ?? 'no';
    $updatePrice = $_POST['update_price'] ?? 'no';
    
    // Skip BOM if present
    $bom = fread($file, 3);
    if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
        rewind($file);
    }
    
    $headers = fgetcsv($file); // skip header row
    $imported = 0;
    $skipped = 0;
    $errors = [];
    $lineNum = 1;
    
    // Get default category (first category)
    $defaultCat = dbGet("SELECT id FROM categories ORDER BY id LIMIT 1");
    $defaultCatId = $defaultCat ? $defaultCat['id'] : 1;
    
    while (($row = fgetcsv($file)) !== false) {
        $lineNum++;
        if (count($row) < 2) { $errors[] = "Dòng $lineNum: thiếu dữ liệu"; continue; }
        
        $name = trim($row[0] ?? '');
        $sku = trim($row[1] ?? '');
        $oem = trim($row[2] ?? '');
        $brand = trim($row[3] ?? '');
        $catId = intval($row[4] ?? 0);
        $price = intval($row[5] ?? 0);
        $priceBefore = intval($row[6] ?? 0);
        $stock = intval($row[7] ?? 0);
        $warranty = intval($row[8] ?? 12);
        $desc = $row[9] ?? '';
        $status = in_array($row[10] ?? '', ['draft','published']) ? $row[10] : 'draft';
        $carBrandName = trim($row[11] ?? '');
        $imageUrl = trim($row[12] ?? '');
        
        // Resolve car_brand_id from name
        $carBrandId = null;
        if ($carBrandName) {
            $br = dbGet("SELECT id FROM brands WHERE LOWER(name)=LOWER(?)", [$carBrandName]);
            if ($br) $carBrandId = $br['id'];
        }
        
        if (!$name || !$price) { $errors[] = "Dòng $lineNum: thiếu tên hoặc giá"; continue; }
        
        // Use default category if not specified
        if (!$catId) $catId = $defaultCatId;
        
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name)));
        $finalStatus = in_array($row[10] ?? '', ['draft','published']) ? $row[10] : $defaultStatus;
        
        // Check for existing product by SKU
        $existing = null;
        if ($sku) {
            $existing = dbGet("SELECT id FROM products WHERE sku=?", [$sku]);
        }
        
        try {
            if ($existing && $dupSku === 'update') {
                // Update existing product
                $updates = ["name=?"];
                $params = [$name];
                if ($updatePrice === 'yes') { $updates[] = "price=?"; $updates[] = "price_before_tax=?"; $params[] = $price; $params[] = $priceBefore; }
                if ($updateStock === 'yes') { $updates[] = "stock=?"; $params[] = $stock; }
                $params[] = $existing['id'];
                dbRun("UPDATE products SET " . implode(',', $updates) . " WHERE id=?", $params);
                $imported++;
            } elseif ($existing && $dupSku === 'skip') {
                $skipped++;
                $errors[] = "Dòng $lineNum: SKU \"{$sku}\" đã tồn tại";
            } else {
                // Generate unique SKU if empty
                if (!$sku) { $sku = 'CSV-' . strtoupper(substr(md5($name . time()), 0, 8)); }
                
                dbInsert("INSERT INTO products (name, sku, slug, oem_code, part_brand, car_brand_id, category_id, price, price_before_tax, stock, warranty_months, description, status, partner_id, video_url, published_at, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,datetime('now','localtime'),datetime('now','localtime'))",
                    [$name, $sku, $slug, $oem, $brand, $carBrandId, $catId, $price, $priceBefore, $stock, $warranty, $desc, $finalStatus, trim($_POST['video_url'] ?? '')]);
                // Handle image: local filename or URL
                if ($imageUrl) {
                    $newPid = dbGet("SELECT id FROM products WHERE sku=?", [$sku]);
                    if ($newPid) {
                        $imgName = null;
                        if (str_starts_with($imageUrl, 'http')) {
                            // Download from URL
                            $ctx = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 10]]);
                            $imgData = @file_get_contents($imageUrl, false, $ctx);
                            if ($imgData && strlen($imgData) > 1000) {
                                $imgExt = 'jpg';
                                if (str_contains($imageUrl, '.webp')) $imgExt = 'webp';
                                elseif (str_contains($imageUrl, '.png')) $imgExt = 'png';
                                $imgName = 'p_' . uniqid() . '.' . $imgExt;
                                file_put_contents('/opt/cooling-php/uploads/products/' . $imgName, $imgData);
                            }
                        } else {
                            // Local filename already in uploads/products/
                            $localPath = '/opt/cooling-php/uploads/products/' . $imageUrl;
                            if (file_exists($localPath)) {
                                $imgName = $imageUrl;
                            }
                        }
                        if ($imgName) {
                            dbRun("INSERT INTO product_images (product_id, file_path, is_main, sort_order) VALUES (?, ?, 1, 0)", [$newPid['id'], $imgName]);
                        }
                    }
                }
                $imported++;
            }
        } catch (\Exception $e) {
            $errors[] = "Dòng $lineNum: " . $e->getMessage();
        }
    }
    
    fclose($file);
    
    if ($imported > 0) {
        $msg = "Đã nhập thành công {$imported} sản phẩm.";
        if ($skipped > 0) $msg .= " Bỏ qua {$skipped} SP đã tồn tại (trùng SKU).";
        if (count($errors) > 0 && $skipped == 0) $msg .= " " . count($errors) . " dòng bị lỗi.";
        flash('success', $msg);
    } elseif ($skipped > 0) {
        flash('error', "Không nhập được SP nào. {$skipped} dòng bị bỏ qua do trùng SKU: " . implode(', ', array_slice($errors, 0, 5)));
    } elseif (count($errors) > 0) {
        flash('error', 'Không nhập được SP nào. ' . $errors[0]);
    } else {
        flash('error', 'File CSV rỗng hoặc không có dữ liệu hợp lệ.');
    }
    redirect('/admin/products');
});


// ── Trust Steps Management ──
get('/admin/trust-steps', function() {
    requireStaffPermission('stores', '/auth/login');
    view('admin/trust-steps');
});
post('/admin/trust-steps/add', function() {
    requireStaffPermission('orders', '/admin'); csrfCheck();
    $d = $_POST;
    

    dbRun("INSERT INTO trust_steps (step_number,title,description,icon,sort_order,is_active) VALUES (?,?,?,?,?,?)",
        [intval($d['step_number']), $d['title'], $d['description']??'', $d['icon']??'📦', intval($d['sort_order']??0), isset($d['is_active'])?1:0]);
    flash('success','Đã thêm bước cam kết.');
    redirect('/admin/trust-steps');
});
// Trust steps reorder
post('/admin/trust-steps/reorder', function() {
    requireStaffPermission('tax_config', '/auth/login'); csrfCheck();
    $ids = $_POST['ids'] ?? [];
    if (is_array($ids)) {
        foreach ($ids as $order => $id) {
            dbRun("UPDATE trust_steps SET sort_order=? WHERE id=?", [$order + 1, intval($id)]);
        }
        flash('success', 'Đã cập nhật thứ tự các bước!');
    }
    redirect('/admin/trust-steps');
});

post('/admin/trust-steps/:id/edit', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $d = $_POST;
    dbRun("UPDATE trust_steps SET step_number=?,title=?,description=?,icon=?,sort_order=?,is_active=?,updated_at=datetime('now') WHERE id=?",
        [intval($d['step_number']), $d['title'], $d['description']??'', $d['icon']??'📦', intval($d['sort_order']??0), isset($d['is_active'])?1:0, $p['id']]);
    flash('success','Đã cập nhật.');
    redirect('/admin/trust-steps');
});
post('/admin/trust-steps/:id/delete', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    dbRun("DELETE FROM trust_steps WHERE id=?", [$p['id']]);
    flash('success','Đã xóa.');
    redirect('/admin/trust-steps');
});
post('/admin/trust-steps/:id/order', function($p) {
    requireRole('admin', '/admin/login'); csrfCheck();
    dbRun("UPDATE trust_steps SET sort_order=? WHERE id=?", [intval($_POST['sort_order']), $p['id']]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
});


// ── EXPORT/IMPORT: Orders ──
get('/admin/orders/export-csv', function() {
    requireStaffPermission('orders', '/admin/login');
    $selectedIds = [];
    if (!empty($_GET['ids'])) {
        $selectedIds = array_map('intval', explode(',', $_GET['ids']));
        $selectedIds = array_filter($selectedIds, function($v){ return $v > 0; });
    }
    header('Content-Type: text/csv; charset=utf-8');
    $fname = count($selectedIds) > 0 ? 'orders_selected_' . count($selectedIds) : 'orders_' . date('Ymd');
    header('Content-Disposition: attachment; filename="' . $fname . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['code','full_name','phone','email','address','grand_total','delivery_status','payment_status','payment_type','created_at']);
    $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];
    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = dbAll("SELECT o.*, u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id IN ($ph) ORDER BY o.id DESC", $ids);
    } else {
        $rows = dbAll("SELECT o.*, u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC");
    }
    foreach($rows as $r) fputcsv($out, [$r['code']??$r['order_code']??'',$r['shipping_full_name']??'',$r['shipping_phone']??'',$r['email']??'',$r['shipping_detail']??'',$r['grand_total'],$r['delivery_status'],$r['payment_status'],$r['payment_type']??'',$r['created_at']]);
    fclose($out); exit;
});

// ── EXPORT/IMPORT: Users ──
get('/admin/users/export-csv', function() {
    requireStaffPermission('users', '/auth/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['full_name','email','phone','role','status','address','created_at']);
    $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];
    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = dbAll("SELECT * FROM users WHERE id IN ($ph) ORDER BY id DESC", $ids);
    } else {
        $rows = dbAll("SELECT * FROM users ORDER BY id DESC");
    }
    foreach($rows as $r) fputcsv($out, [$r['full_name'],$r['email'],$r['phone']??'',$r['role'],$r['status'],$r['address']??'',$r['created_at']]);
    fclose($out); exit;
});

// ── EXPORT: Categories ──
get('/admin/categories/export-csv', function() {
    requireStaffPermission('categories', '/auth/login');
    $selectedIds = [];
    if (!empty($_GET['ids'])) {
        $selectedIds = array_map('intval', explode(',', $_GET['ids']));
        $selectedIds = array_filter($selectedIds, function($v){ return $v > 0; });
    }
    header('Content-Type: text/csv; charset=utf-8');
    $fname = count($selectedIds) > 0 ? 'categories_selected_' . count($selectedIds) : 'categories_' . date('Ymd');
    header('Content-Disposition: attachment; filename="' . $fname . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['id','name','slug','parent_id','sort_order','is_featured']);
    if (!empty($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $rows = dbAll("SELECT * FROM categories WHERE id IN ($placeholders) ORDER BY sort_order, name", $selectedIds);
    } else {
        $rows = dbAll("SELECT * FROM categories ORDER BY sort_order, name");
    }
    foreach($rows as $r) fputcsv($out, [$r['id'],$r['name'],$r['slug']??'',$r['parent_id']??'',$r['sort_order']??0,$r['is_featured']??0]);
    fclose($out); exit;
});

// ── EXPORT: Brands (Hãng xe) ──
get('/admin/brands/export-csv', function() {
    requireStaffPermission('brands', '/admin/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="brands_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['id','name','slug','country','sort_order']);
    $rows = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    foreach($rows as $r) fputcsv($out, [$r['id'],$r['name'],$r['slug']??'',$r['country']??'',$r['sort_order']??0]);
    fclose($out); exit;
});

// ── EXPORT: Product Brands (Thương hiệu SP) ──
get('/admin/product-brands/export-csv', function() {
    requireStaffPermission('brand_models', '/auth/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="product_brands_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['id','name','description','sort_order']);
    $rows = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    foreach($rows as $r) fputcsv($out, [$r['id'],$r['name'],$r['description']??'',$r['sort_order']??0]);
    fclose($out); exit;
});


// ── Admin Chat ──
get('/admin/chat', function() {
    requireRole(['admin','staff'], '/admin/login');
    try { dbRun("ALTER TABLE chat_threads ADD COLUMN is_hidden INTEGER DEFAULT 0"); } catch(\Exception $e) {}
    
    $threads = dbAll("SELECT t.*, u.full_name, u.email, u.avatar 
        FROM chat_threads t 
        INNER JOIN users u ON u.id = t.customer_id 
        ORDER BY t.last_message_at DESC");
    
    $activeThread = null;
    $messages = [];
    $threadId = intval($_GET['thread'] ?? 0);
    if ($threadId) {
        $activeThread = dbGet("SELECT t.*, u.full_name, u.email, u.avatar 
            FROM chat_threads t 
            INNER JOIN users u ON u.id = t.customer_id 
            WHERE t.id=?", [$threadId]);
        if ($activeThread) {
            $messages = dbAll("SELECT * FROM chat_messages WHERE thread_id=? ORDER BY created_at ASC", [$threadId]);
            dbRun("UPDATE chat_messages SET status='read' WHERE thread_id=? AND sender_role='customer' AND status!='read'", [$threadId]);
        }
    }
    
    view('admin/chat', [
        'title' => 'Tin nhắn',
        'role' => 'admin',
        'threads' => $threads,
        'activeThread' => $activeThread,
        'messages' => $messages
    ]);
});
post('/admin/chat/send', function() {
    requireRole(['admin','staff'], '/admin/login'); csrfCheck();
    $threadId = intval($_POST['thread_id']);
    $content = trim($_POST['content'] ?? '');
    if (!$threadId || !$content) { echo json_encode(['error'=>'missing']); exit; }
    $user = currentUser();
    $id = dbInsert("INSERT INTO chat_messages (thread_id, sender_user_id, sender_role, content, status, created_at) VALUES (?,?,?,?,?,datetime('now','localtime'))",
        [$threadId, $user['id'], 'admin', $content, 'sent']);
    try { dbRun("UPDATE chat_threads SET last_message=?, last_message_at=datetime('now','localtime'), customer_unread=customer_unread+1 WHERE id=?", [mb_substr($content,0,100), $threadId]); } catch (Throwable $e) {}
    // Notify customer
    $thread = dbGet("SELECT customer_id FROM chat_threads WHERE id=?", [$threadId]);
    if ($thread) {
        dbInsert("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?,'chat','Tin nhắn mới','Bạn có tin nhắn mới từ Hỗ trợ khách hàng','/chat?thread=" . $threadId . "',datetime('now','localtime'))",
            [$thread['customer_id']]);
    }
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true,'id'=>$id]);
    exit;
});
post('/admin/chat/send-image', function() {
    requireRole(['admin','staff'], '/admin/login'); csrfCheck();
    $threadId = intval($_POST['thread_id']);
    if (!$threadId || empty($_FILES['image'])) { echo json_encode(['error'=>'missing']); exit; }
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) { echo json_encode(['error'=>'invalid']); exit; }
    $uploadDir = '/opt/cooling-php/uploads/chat/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    $user = currentUser();
    $id = dbInsert("INSERT INTO chat_messages (thread_id, sender_user_id, sender_role, content, attachment_path, status, created_at) VALUES (?,?,?,?,?,?,datetime('now','localtime'))",
        [$threadId, $user['id'], 'admin', '', $filename, 'sent']);
    try { dbRun("UPDATE chat_threads SET last_message='[Hình ảnh]', last_message_at=datetime('now','localtime'), customer_unread=customer_unread+1 WHERE id=?", [$threadId]); } catch (Throwable $e) {}
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true,'id'=>$id,'path'=>$filename]);
    exit;
});
get('/admin/chat/poll', function() {
    requireRole(['admin','staff'], '/admin/login');
    $threadId = intval($_GET['thread_id'] ?? 0);
    $afterId = intval($_GET['after_id'] ?? 0);
    if (!$threadId) { echo '[]'; exit; }
    $msgs = dbAll("SELECT id, content, sender_role, COALESCE(attachment_path, image_path) AS attachment_path, created_at FROM chat_messages WHERE thread_id=? AND id>? ORDER BY created_at ASC", [$threadId, $afterId]);
    $result = [];
    foreach($msgs as $m) {
        $result[] = ['id'=>$m['id'],'content'=>$m['content'],'sender_role'=>$m['sender_role'],'attachment_path'=>$m['attachment_path']??'','time'=>date('H:i d/m',strtotime($m['created_at']))];
    }
    // Mark customer messages as read
    dbRun("UPDATE chat_messages SET status='read' WHERE thread_id=? AND sender_role='customer' AND status='sent' AND id>?", [$threadId, $afterId]);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
});

// ── ẨN / XÓA HỘI THOẠI ────────────────────────────────────────────
post('/admin/chat/:id/hide', function($p) {
    requireRole(['admin','staff'], '/admin/login'); csrfCheck();
    $threadId = intval($p['id']);
    $thread = dbGet("SELECT * FROM chat_threads WHERE id=?", [$threadId]);
    if (!$thread) { echo json_encode(['ok'=>false,'msg'=>'Không tìm thấy']); exit; }
    dbRun("UPDATE chat_threads SET is_hidden=1 WHERE id=?", [$threadId]);
    echo json_encode(['ok'=>true]);
    exit;
});

post('/admin/chat/:id/unhide', function($p) {
    requireRole(['admin','staff'], '/admin/login'); csrfCheck();
    $threadId = intval($p['id']);
    dbRun("UPDATE chat_threads SET is_hidden=0 WHERE id=?", [$threadId]);
    echo json_encode(['ok'=>true]);
    exit;
});

post('/admin/chat/:id/delete', function($p) {
    requireRole(['admin','staff'], '/admin/login'); csrfCheck();
    $threadId = intval($p['id']);
    $thread = dbGet("SELECT * FROM chat_threads WHERE id=?", [$threadId]);
    if (!$thread) { echo json_encode(['ok'=>false,'msg'=>'Không tìm thấy']); exit; }
    dbRun("DELETE FROM chat_messages WHERE thread_id=?", [$threadId]);
    dbRun("DELETE FROM chat_threads WHERE id=?", [$threadId]);
    echo json_encode(['ok'=>true]);
    exit;
});



// ── IMPORT: Users ──
post('/admin/users/import-csv', function() {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect('/admin/users'); return; }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/users');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/users');
        return;
    }
    // ============================================

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $bom = fread($handle, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    $header = fgetcsv($handle);
    $imported = 0; $errors = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 2) { $errors++; continue; }
        $name = trim($row[0]); $email = trim($row[1]); $phone = trim($row[2]??'');
        $role = trim($row[3]??'customer'); $address = trim($row[4]??'');
        if (!$email) { $errors++; continue; }
        $existing = dbGet("SELECT id FROM users WHERE email=?", [$email]);
        if ($existing) { $errors++; continue; }
        try {
            dbInsert("INSERT INTO users (full_name,email,phone,role,address,password_hash,status,created_at) VALUES (?,?,?,?,?,?,?,datetime('now'))",
                [$name, $email, $phone, $role, $address, password_hash('Cooling@123', PASSWORD_DEFAULT), 'active']);
            $imported++;
        } catch (\Exception $e) { $errors++; }
    }
    fclose($handle);
    if ($imported > 0) {
        $msg = "Đã nhập $imported tài khoản. Mật khẩu mặc định: Cooling@123";
        if ($errors > 0) $msg .= " ($errors dòng bỏ qua do email đã tồn tại hoặc lỗi)";
        flash('success', $msg);
    } elseif ($errors > 0) {
        flash('error', "Không nhập được tài khoản nào. $errors dòng bị bỏ qua (email đã tồn tại hoặc dữ liệu không hợp lệ).");
    } else {
        flash('error', 'File CSV rỗng hoặc không có dữ liệu hợp lệ.');
    }
    redirect('/admin/users');
});

// ── IMPORT: Categories ──
post('/admin/categories/import-csv', function() {
    requireStaffPermission('categories', '/auth/login'); csrfCheck();
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect('/admin/categories'); return; }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/categories');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/categories');
        return;
    }
    // ============================================

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $bom = fread($handle, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    fgetcsv($handle);
    $imported = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $name = trim($row[0]??''); if (!$name) { $errors[] = "Dòng $lineNum: tên rỗng"; continue; }
        $slug = trim($row[1]??'') ?: preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $parentId = intval($row[2]??0) ?: null; $sort = intval($row[3]??0); $featured = intval($row[4]??0);
        $existing = dbGet("SELECT id FROM categories WHERE name=?", [$name]);
        if ($existing) { $duplicates[] = "\"{$name}\""; continue; }
        try {
            dbInsert("INSERT INTO categories (name,slug,parent_id,sort_order,is_featured) VALUES (?,?,?,?,?)", [$name,$slug,$parentId,$sort,$featured]);
            $imported++;
        } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
    }
    fclose($handle);
    if ($imported > 0) {
        $msg = "Đã nhập thành công $imported danh mục.";
        if (count($duplicates) > 0) $msg .= " Bỏ qua " . count($duplicates) . " danh mục đã tồn tại.";
        flash('success', $msg);
    } elseif (count($duplicates) > 0) {
        flash('error', "Không nhập được danh mục nào. Tất cả " . count($duplicates) . " danh mục đã tồn tại: " . implode(', ', array_slice($duplicates, 0, 5)));
    } else {
        flash('error', 'Không nhập được danh mục nào.' . (count($errors) > 0 ? ' ' . $errors[0] : ''));
    }
    if (count($duplicates) > 0 && $imported > 0) {
        flash('warning', 'Trùng lặp: ' . implode(', ', array_slice($duplicates, 0, 5)) . (count($duplicates) > 5 ? ' ... và ' . (count($duplicates)-5) . ' mục khác' : ''));
    }
    redirect('/admin/categories');
});

// ── IMPORT: Brands (Hãng xe) ──
post('/admin/brands/import-csv', function() {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect('/admin/brands'); return; }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/brands');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/brands');
        return;
    }
    // ============================================

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $bom = fread($handle, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    fgetcsv($handle);
    $imported = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $name = trim($row[0]??''); if (!$name) { $errors[] = "Dòng $lineNum: tên rỗng"; continue; }
        $slug = trim($row[1]??'') ?: preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $sort = intval($row[2]??0);
        $existing = dbGet("SELECT id FROM brands WHERE name=?", [$name]);
        if ($existing) { $duplicates[] = "\"{$name}\""; continue; }
        try {
            dbInsert("INSERT INTO brands (name,slug,sort_order) VALUES (?,?,?)", [$name,$slug,$sort]);
            $imported++;
        } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
    }
    fclose($handle);
    if ($imported > 0) {
        $msg = "Đã nhập thành công $imported hãng xe.";
        if (count($duplicates) > 0) $msg .= " Bỏ qua " . count($duplicates) . " hãng đã tồn tại.";
        flash('success', $msg);
    } elseif (count($duplicates) > 0) {
        flash('error', "Không nhập được hãng nào. Tất cả " . count($duplicates) . " hãng đã tồn tại: " . implode(', ', array_slice($duplicates, 0, 5)));
    } else {
        flash('error', 'Không nhập được hãng nào.' . (count($errors) > 0 ? ' ' . $errors[0] : ''));
    }
    redirect('/admin/brands');
});

// ── IMPORT: Product Brands (Thương hiệu SP) ──
post('/admin/product-brands/import-csv', function() {
    requireStaffPermission('brand_models', '/auth/login'); csrfCheck();
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect('/admin/product-brands'); return; }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/product-brands');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/product-brands');
        return;
    }
    // ============================================

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $bom = fread($handle, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    fgetcsv($handle);
    $imported = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $name = trim($row[0]??''); if (!$name) continue;
        $desc = trim($row[1]??''); $sort = intval($row[2]??0);
        $existing = dbGet("SELECT id FROM product_brands WHERE name=?", [$name]);
        if ($existing) { $duplicates[] = "\"{$name}\""; continue; }
        try {
            dbInsert("INSERT INTO product_brands (name,description,sort_order) VALUES (?,?,?)", [$name,$desc,$sort]);
            $imported++;
        } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
    }
    fclose($handle);
    if ($imported > 0) {
        $msg = "Đã nhập thành công $imported thương hiệu SP.";
        if (count($duplicates) > 0) $msg .= " Bỏ qua " . count($duplicates) . " thương hiệu đã tồn tại.";
        flash('success', $msg);
    } elseif (count($duplicates) > 0) {
        flash('error', "Không nhập được thương hiệu nào. Tất cả " . count($duplicates) . " thương hiệu đã tồn tại: " . implode(', ', array_slice($duplicates, 0, 5)));
    } else {
        flash('error', 'Không nhập được thương hiệu nào.' . (count($errors) > 0 ? ' ' . $errors[0] : ''));
    }
    redirect('/admin/product-brands');
});

// ── IMPORT: Orders ──
// ── IMPORT: Orders ──
post('/admin/orders/import-csv', function() {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect('/admin/orders'); return; }
    // ===== KIỂM TRA CHỈ CHẤP NHẬN FILE CSV =====
    $uploadedFile = $_FILES['csv_file'];
    $originalName = $uploadedFile['name'] ?? '';
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') {
        flash('error', 'Chỉ được phép import file CSV (.csv). File "' . htmlspecialchars($originalName) . '" không hợp lệ.');
        redirect('/admin/orders');
        return;
    }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) {
        flash('error', 'File không đúng định dạng CSV. Vui lòng kiểm tra lại.');
        redirect('/admin/orders');
        return;
    }
    // ============================================

    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $bom = fread($handle, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($handle);
    fgetcsv($handle);
    $updated = 0; $created = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $code = trim($row[0]??''); $name = trim($row[1]??''); $phone = trim($row[2]??'');
        $address = trim($row[3]??''); $total = intval($row[4]??0);
        $delStatus = trim($row[5]??''); if(!in_array($delStatus,['pending','received','delivering','delivered','completed','cancelled'])) $delStatus='pending'; $payStatus = trim($row[6]??'unpaid');
        if (!$code) { $errors[] = "Dòng $lineNum: mã đơn rỗng"; continue; }
        $existing = dbGet("SELECT id, delivery_status, payment_status FROM orders WHERE code=?", [$code]);
        if ($existing) {
            if ($existing['delivery_status'] === $delStatus && $existing['payment_status'] === $payStatus) {
                $duplicates[] = "\"$code\" (trạng thái không đổi)";
                continue;
            }
            try {
                dbRun("UPDATE orders SET delivery_status=?, payment_status=?, shipping_full_name=COALESCE(NULLIF(?,''),shipping_full_name), shipping_phone=COALESCE(NULLIF(?,''),shipping_phone), updated_at=datetime('now') WHERE id=?",
                    [$delStatus, $payStatus, $name, $phone, $existing['id']]);
                $updated++;
            } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
        } else {
            if (!$name) { $errors[] = "Dòng $lineNum: thiếu tên KH cho đơn mới"; continue; }
            // Find or create a guest user for this order
            $guestUser = dbGet("SELECT id FROM users WHERE email='guest@cooling.vn'");
            $userId = $guestUser ? $guestUser['id'] : 1;
            try {
                dbInsert("INSERT INTO orders (code,user_id,shipping_full_name,shipping_phone,shipping_detail,grand_total,subtotal,delivery_status,payment_status,created_at) VALUES (?,?,?,?,?,?,?,?,?,datetime('now','localtime'))",
                    [$code,$userId,$name,$phone,$address,$total,$total,$delStatus,$payStatus]);
                $created++;
            } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
        }
    }
    fclose($handle);
    $total = $updated + $created;
    if ($total > 0) {
        $msg = '';
        if ($updated > 0) $msg .= "Cập nhật $updated đơn. ";
        if ($created > 0) $msg .= "Tạo mới $created đơn. ";
        if (count($duplicates) > 0) $msg .= "Bỏ qua " . count($duplicates) . " đơn không đổi.";
        flash('success', $msg);
    } elseif (count($duplicates) > 0) {
        flash('error', "Không cập nhật được đơn nào. " . count($duplicates) . " đơn đã có trạng thái giống nhau: " . implode(', ', array_slice($duplicates, 0, 3)));
    } else {
        flash('error', 'Không nhập được đơn nào.' . (count($errors) > 0 ? ' ' . $errors[0] : ''));
    }
    if (count($errors) > 0 && $total > 0) {
        flash('warning', 'Lỗi: ' . implode(' | ', array_slice($errors, 0, 5)));
    }
    redirect('/admin/orders');
});

// ── EXPORT/IMPORT: Categories ──

get('/admin/orders', function() {
    requireStaffPermission('orders', '/admin/login');
    $perPage = 20;
    $page = max(1, intval($_GET['page'] ?? 1));
    $q = trim($_GET['q'] ?? '');
    $delivery = $_GET['delivery'] ?? '';
    $payment  = $_GET['payment'] ?? '';
    
    $where = "WHERE 1=1";
    $params = [];
    
    if ($q) {
        $where .= " AND (o.code LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)";
        $like = "%$q%";
        $params = array_merge($params, [$like, $like, $like]);
    }
    if ($delivery) {
        $where .= " AND o.delivery_status = ?";
        $params[] = $delivery;
    }
    if ($payment) {
        $where .= " AND o.payment_status = ?";
        $params[] = $payment;
    }
    
    $total = dbGet("SELECT COUNT(*) AS n FROM orders o LEFT JOIN users u ON u.id=o.user_id $where", $params)['n'] ?? 0;
    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    
    $p2 = array_merge($params, [$perPage, $offset]);
    $orders = dbAll("SELECT o.*, u.full_name, COALESCE(u.email,'') AS email, u.phone, s.full_name AS staff_name 
        FROM orders o LEFT JOIN users u ON u.id=o.user_id 
        LEFT JOIN users s ON s.id=o.created_by_staff
        $where ORDER BY o.created_at DESC LIMIT ? OFFSET ?", $p2);
    
    view('admin/orders', [
        'title' => 'Quản lý đơn hàng',
        'role'  => 'admin',
        'orders' => $orders,
        'total' => $total,
        'page'  => $page,
        'totalPages' => $totalPages
    ]);
});

get('/admin/orders/create', function() {
    $user = requireStaffPermission('create_order', '/admin/login');
    view('admin/order-create', ['title'=>'Tạo đơn hàng hộ', 'role'=>'admin', 'currentUser'=>$user]);
});

get('/admin/orders/:id', function($p) {
    requireStaffPermission('orders', '/admin/login');
    $order = dbGet("SELECT o.*, u.full_name, COALESCE(u.email,'') AS email, u.phone, s.full_name AS staff_name
        FROM orders o LEFT JOIN users u ON u.id=o.user_id
        LEFT JOIN users s ON s.id=o.created_by_staff
        WHERE o.id=?", [$p['id']]);
    if (!$order) { http_response_code(404); echo '<h2>Không tìm thấy đơn hàng</h2>'; exit; }
    $items = dbAll("SELECT oi.*, p.name AS product_name, (SELECT file_path FROM product_images WHERE product_id=p.id AND is_main=1 LIMIT 1) AS image_url FROM order_items oi
        INNER JOIN sub_orders so ON so.id = oi.sub_order_id
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE so.order_id=?", [$p['id']]);
    $orderInvoice = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$order['user_id']]);
    $currentUser = currentUser();
    view('admin/order-detail', ['title'=>'Chi tiết đơn hàng','role'=>$currentUser['role'],'order'=>$order,'items'=>$items,'invoice'=>$orderInvoice]);
});




post('/admin/orders/create', function() {
    $user = requireStaffPermission('orders', '/admin/login'); csrfCheck();
    $items = json_decode($_POST['items'] ?? '[]', true) ?: [];
    if (empty($items)) { flash('error','Vui lòng thêm ít nhất 1 sản phẩm.'); redirect('/admin/orders/create'); }
    $shipName  = trim($_POST['ship_name'] ?? '');
    $shipPhone = trim($_POST['ship_phone'] ?? '');
    if (!$shipName || !$shipPhone) { flash('error','Thiếu thông tin khách hàng.'); redirect('/admin/orders/create'); }
    
    $userId      = intval($_POST['user_id'] ?? 0);
    $payMethod   = in_array($_POST['payment_method']??'',['cod','bank_transfer']) ? $_POST['payment_method'] : 'cod';
    $payType     = $_POST['payment_type'] ?? 'cod';
    $delStatus   = in_array($_POST['delivery_status']??'',['pending','received']) ? $_POST['delivery_status'] : 'pending';
    $staffNote   = trim($_POST['staff_note'] ?? '');
    
    // Calculate totals using Finance Engine
    $cfgRows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_tax_rate','default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent')");
    $cfg = []; foreach($cfgRows as $r) $cfg[$r['key']] = $r['value'];
    
    $taxRate = floatval($cfg['default_tax_rate'] ?? 0);
    $shipFee = intval($cfg['default_shipping_fee'] ?? 30000);
    $freeShipThreshold = intval($cfg['free_shipping_threshold'] ?? 2000000);
    $discountQtyThreshold = intval($cfg['discount_quantity_threshold'] ?? 0);
    $discountQtyPercent = floatval($cfg['discount_quantity_percent'] ?? 0);

    $subtotal = 0;
    $totalQty = 0;
    foreach ($items as $it) { 
        $subtotal += intval($it['price']) * max(1, intval($it['qty'])); 
        $totalQty += max(1, intval($it['qty']));
    }
    
    $discountTotal = 0;
    if ($discountQtyThreshold > 0 && $totalQty >= $discountQtyThreshold) {
        $discountTotal = (int)ceil($subtotal * ($discountQtyPercent / 100));
    }

    $afterDiscount = $subtotal - $discountTotal;
    $taxAmount = 0; // VAT đã bao gồm trong giá sản phẩm, không tính thêm

    $shipping = $afterDiscount >= $freeShipThreshold ? 0 : $shipFee;
    $grandTotal = $afterDiscount + $shipping; // taxAmount = 0, VAT included in price
    
    $depositPct = 0; $depositAmt = 0; $remainAmt = 0; $payStatus = 'unpaid';
    if ($payMethod === 'bank_transfer' && $payType === 'deposit_70') {
        $depositPct = 70; $depositAmt = (int)ceil($grandTotal * 0.7);
        $remainAmt  = $grandTotal - $depositAmt; $payStatus = 'partial_paid';
    } elseif ($payMethod === 'bank_transfer') {
        $payStatus = 'unpaid';
    }
    
    $code = 'DH'.date('ymd').strtoupper(substr(uniqid('',true), -4));
    
    dbRun("PRAGMA foreign_keys=OFF");
    $orderId = dbInsert("INSERT INTO orders (code, user_id, grand_total, subtotal, discount_total, tax_amount, shipping_total, total_items, payment_method, payment_type, payment_status, delivery_status, deposit_percent, deposit_amount, remaining_amount, created_by_staff, staff_note, shipping_full_name, shipping_phone, shipping_detail, shipping_district, shipping_province, shipping_ward, customer_note, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'',datetime('now', 'localtime'))", [
        $code, $userId, $grandTotal, $subtotal, $discountTotal, $taxAmount, $shipping, count($items),
        $payMethod, $payType, $payStatus, $delStatus,
        $depositPct, $depositAmt, $remainAmt, $user['id'], $staffNote,
        $shipName, $shipPhone, trim($_POST['ship_address']??''),
        trim($_POST['ship_district']??''), trim($_POST['ship_province']??''),
        trim($_POST['ship_ward']??'')
    ]);
    dbRun("PRAGMA foreign_keys=ON");
    
    $soCode = $code.'-'.strtoupper(substr(uniqid('',true), -4));
    $soId = dbInsert("INSERT INTO sub_orders (order_id, partner_id, code, subtotal, grand_total, created_at) VALUES (?,1,?,?,?,datetime('now', 'localtime'))", [$orderId, $soCode, $subtotal, $grandTotal]);
    foreach ($items as $it) {
        $pid = intval($it['product_id']); $qty = max(1, intval($it['qty'])); $price = intval($it['price']);
        $prod = dbGet("SELECT name, sku, oem_code FROM products WHERE id=?", [$pid]);
        dbRun("INSERT INTO order_items (sub_order_id, product_id, quantity, unit_price, line_total, snapshot_name, snapshot_oem) VALUES (?,?,?,?,?,?,?)",
            [$soId, $pid, $qty, $price, $price*$qty, $prod['name']??'', $prod['oem_code']??'']);
        dbRun("UPDATE products SET stock=MAX(0,stock-?) WHERE id=?", [$qty, $pid]);
        dbRun("UPDATE products SET total_import_value=cost_price*stock WHERE id=?", [$pid]);
    }
    
    // Notification for customer
    if ($userId) {
        dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)",
            [$userId, 'order', 'Đơn hàng mới', "Nhân viên vừa tạo đơn hàng #{$code} cho bạn.", '/customer/orders']);
    }
    
    flash('success',"Tạo đơn hàng #{$code} thành công!");
    redirect('/admin/orders');
});

post('/admin/orders/:id/return/:return_id', function($p) {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        dbRun("UPDATE order_returns SET status='approved' WHERE id=?", [$p['return_id']]);
        dbRun("UPDATE orders SET delivery_status='returned', payment_status='refunded' WHERE id=?", [$p['id']]);
        // Restore product stock for returned items
        $returnItems = dbAll("SELECT so.product_id, so.quantity FROM sub_orders so WHERE so.order_id=?", [$p['id']]);
        foreach ($returnItems as $ri) {
            if (!empty($ri['product_id']) && intval($ri['quantity']) > 0) {
                dbRun("UPDATE products SET stock = stock + ? WHERE id=?", [intval($ri['quantity']), $ri['product_id']]);
                dbRun("UPDATE products SET total_import_value=cost_price*stock WHERE id=?", [$ri['product_id']]);
            }
        }
        // Deduct refund from revenue
        $retInfo = dbGet("SELECT refund_amount FROM order_returns WHERE id=?", [$p['return_id']]);
        $refAmt = intval($retInfo['refund_amount'] ?? 0);
        if ($refAmt > 0) {
            dbRun("UPDATE orders SET refund_amount=? WHERE id=?", [$refAmt, $p['id']]);
        }

        // Notify customer about approved return
        $retOrder = dbGet("SELECT o.user_id, o.code, o.total FROM orders o WHERE o.id=?", [$p['id']]);
        if ($retOrder) {
            dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, is_read, created_at) VALUES (?,?,?,?,?,0,datetime('now','localtime'))",
                [$retOrder['user_id'], 'return', 'Yêu cầu trả hàng được chấp nhận',
                'Đơn hàng ' . $retOrder['code'] . ' đã được xác nhận hoàn hàng. Chúng tôi sẽ cử người đến lấy hàng về.',
                '/customer/orders']);
        }
        flash('success', 'Đã duyệt yêu cầu trả hàng. Tiền đã được hoàn và khấu trừ khỏi doanh thu.');
    } elseif ($action === 'reject') {
        dbRun("UPDATE order_returns SET status='rejected' WHERE id=?", [$p['return_id']]);
        flash('error', 'Đã từ chối yêu cầu trả hàng.');
    }
    redirect('/admin/orders/'.$p['id']);
});



// API search
get('/admin/api/search-customers', function() {
    requireStaffPermission('tax_config', '/auth/login');
    $q = trim($_GET['q'] ?? '');
    $users = dbAll("SELECT id, full_name, email, phone FROM users WHERE role='customer' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?) LIMIT 10", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($users);
    exit;
});

get('/admin/api/search-products', function() {
    requireStaffPermission('products', '/admin/login');
    $q = trim($_GET['q'] ?? '');
    $prods = dbAll("SELECT id, name, sku, price, stock FROM products WHERE status='published' AND (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) LIMIT 15", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($prods);
    exit;
});


// ── Update payment status ──
post('/admin/orders/:id/payment-status', function($p) {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    $status = in_array($_POST['status'], ['paid','unpaid']) ? $_POST['status'] : 'unpaid';
    if ($status === 'paid') {
        $order = dbGet("SELECT grand_total, code FROM orders WHERE id=?", [$p['id']]);
        $gt = $order['grand_total'] ?? 0;
        dbRun("UPDATE orders SET payment_status='paid', paid_amount=?, remaining_amount=0 WHERE id=?", [$gt, $p['id']]);
        // Auto-create payment record
        $payCode = 'PAY-' . strtoupper(substr(md5($order['code'] . time()), 0, 8));
        $exists = dbGet("SELECT 1 FROM order_payments WHERE order_id=? AND amount=?", [$p['id'], $gt]);
        if (!$exists) {
            dbInsert("INSERT INTO order_payments (order_id, payment_code, payment_type, payment_method, amount, created_at) VALUES (?,?,'thu_tien','admin_confirm',?,datetime('now','localtime'))",
                [$p['id'], $payCode, $gt]);
        }
    } else {
        dbRun("UPDATE orders SET payment_status='unpaid', paid_amount=0, remaining_amount=grand_total WHERE id=?", [$p['id']]);
    }
    flash('success', 'Đã cập nhật trạng thái thanh toán.');
    redirect('/admin/orders');
});

post('/admin/orders/:id/delivery-status', function($p) {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    $order = dbGet("SELECT payment_status, delivery_status, payment_method, payment_type, user_id, code FROM orders WHERE id=?", [$p['id']]);
    if (!$order) { flash('error','Không tìm thấy đơn hàng.'); redirect('/admin/orders'); return; }

    // Block if completed/cancelled/returned
    if (in_array($order['delivery_status'], ['completed','cancelled','returned'])) {
        flash('error','Đơn hàng đã kết thúc, không thể thay đổi trạng thái.');
        redirect('/admin/orders'); return;
    }

    $newStatus = $_POST['status'] ?? '';
    $valid = ['pending','received','delivering','delivered','completed','cancelled'];
    if (!in_array($newStatus, $valid)) { flash('error','Trạng thái không hợp lệ.'); redirect('/admin/orders'); return; }

    // Forward-only check (except cancel which is always allowed)
    $levels = ['pending'=>0,'received'=>1,'delivering'=>2,'delivered'=>3,'completed'=>4];
    $curLevel = $levels[$order['delivery_status']] ?? 0;
    $newLevel = $levels[$newStatus] ?? 99;
    if ($newStatus !== 'cancelled' && $newLevel < $curLevel) {
        flash('error','Không thể lùi trạng thái giao hàng.');
        redirect('/admin/orders'); return;
    }

    // If completing, auto-set payment to paid
    if ($newStatus === 'completed') {
        dbRun("UPDATE orders SET delivery_status='completed', payment_status='paid' WHERE id=?", [$p['id']]);
    } else {
        dbRun("UPDATE orders SET delivery_status=? WHERE id=?", [$newStatus, $p['id']]);
    }

    // Restore stock on cancel
    if ($newStatus === 'cancelled' && $order['delivery_status'] !== 'cancelled') {
        $orderItems = dbAll("SELECT oi.product_id, oi.quantity FROM order_items oi INNER JOIN sub_orders so ON so.id=oi.sub_order_id WHERE so.order_id=?", [$p['id']]);
        foreach ($orderItems as $oi) {
            if ($oi['product_id']) dbRun("UPDATE products SET stock = stock + ? WHERE id=?", [$oi['quantity'], $oi['product_id']]);
        }
        dbRun("UPDATE orders SET payment_status='refunded' WHERE id=? AND payment_status='paid'", [$p['id']]);
    }

    // If delivered + paid → auto complete
    if ($newStatus === 'delivered') {
        $pay = dbGet("SELECT payment_status FROM orders WHERE id=?", [$p['id']]);
        if ($pay && $pay['payment_status'] === 'paid') {
            dbRun("UPDATE orders SET delivery_status='completed' WHERE id=?", [$p['id']]);
        }
    }

    // Notify customer
    if ($order['user_id']) {
        $msgMap = ['received'=>'Nhân viên đã tiếp nhận đơn hàng','delivering'=>'Đơn hàng đang được giao','delivered'=>'Đơn hàng đã được giao thành công','completed'=>'Đơn hàng đã hoàn thành','cancelled'=>'Đơn hàng đã bị hủy'];
        $msg = ($msgMap[$newStatus] ?? 'Trạng thái đơn hàng đã thay đổi') . ' (#' . $order['code'] . ')';
        dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)",
            [$order['user_id'], 'order', 'Cập nhật đơn hàng', $msg, '/customer/orders']);
    }
    flash('success','Cập nhật trạng thái giao hàng thành công.');
    redirect('/admin/orders');
});

// ── STAFF PERMISSION ROUTES ─────────────────────────────────────────────────
get('/admin/staff', function() {
    $user = requireStaffPermission('categories', '/auth/login');
    $staffRoles = dbAll("SELECT * FROM staff_roles ORDER BY created_at DESC");
    $assignments = dbAll("SELECT sra.id AS assignment_id, sra.assigned_at, u.full_name, COALESCE(u.email,'') AS email, u.phone, sr.name AS role_name FROM staff_role_assignments sra INNER JOIN users u ON u.id=sra.user_id INNER JOIN staff_roles sr ON sr.id=sra.role_id ORDER BY sra.assigned_at DESC");
    view('admin/staff', ['title'=>'Phân quyền nhân viên','userRole'=>'admin','staffRoles'=>$staffRoles,'assignments'=>$assignments]);
});

get('/admin/staff/roles/new', function() {
    requireRole('admin', '/admin/login');
    view('admin/role-form', ['title'=>'Tạo vai trò mới','userRole'=>'admin','staffRole'=>[]]);
});

post('/admin/staff/roles/new', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error','Tên vai trò là bắt buộc.'); redirect('/admin/staff/roles/new'); }
    $perms = json_encode(array_filter($_POST['permissions'] ?? []));
    dbRun("INSERT INTO staff_roles (name, description, permissions) VALUES (?,?,?)", [$name, trim($_POST['description']??''), $perms]);
    flash('success',"Tạo vai trò '{$name}' thành công!");
    redirect('/admin/staff');
});

get('/admin/staff/roles/:id/edit', function($p) {
    $user = requireStaffPermission('promotions', '/auth/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy.'); redirect('/admin/staff'); }
    view('admin/role-form', ['title'=>'Sửa vai trò','userRole'=>'admin','staffRole'=>$staffRole]);
});

post('/admin/staff/roles/:id/edit', function($p) {
    requireRole('admin', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error','Tên vai trò là bắt buộc.'); redirect('/admin/staff/roles/'.$p['id'].'/edit'); }
    $perms = json_encode(array_values(array_filter($_POST['permissions'] ?? [])));
    dbRun("UPDATE staff_roles SET name=?, description=?, permissions=? WHERE id=?", [$name, trim($_POST['description']??''), $perms, $p['id']]);
    flash('success','Cập nhật vai trò thành công!');
    redirect('/admin/staff');
});

post('/admin/staff/roles/:id/delete', function($p) {
    requireRole('admin', '/admin/login'); csrfCheck();
    dbRun('DELETE FROM staff_role_assignments WHERE role_id=?', [$p['id']]);
    dbRun('DELETE FROM staff_roles WHERE id=?', [$p['id']]);
    flash('success','Xóa vai trò thành công!');
    redirect('/admin/staff');
});

get('/admin/staff/roles/:id/assign', function($p) {
    $user = requireRole('admin', '/admin/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy.'); redirect('/admin/staff'); }
    $assignedUsers = dbAll("SELECT u.full_name, u.email, sra.id AS assignment_id FROM staff_role_assignments sra INNER JOIN users u ON u.id=sra.user_id WHERE sra.role_id=?", [$p['id']]);
    $availableUsers = dbAll("SELECT id, full_name, email FROM users WHERE role IN ('customer','staff') AND id NOT IN (SELECT user_id FROM staff_role_assignments WHERE role_id=?) ORDER BY full_name", [$p['id']]);
    view('admin/role-assign', ['title'=>'Phân công nhân viên','userRole'=>'admin','staffRole'=>$staffRole,'assignedUsers'=>$assignedUsers,'availableUsers'=>$availableUsers]);
});

post('/admin/staff/roles/:id/assign', function($p) {
    requireRole('admin', '/admin/login'); csrfCheck();
    $userId = intval($_POST['user_id'] ?? 0);
    if (!$userId) { flash('error','Chọn người dùng.'); redirect('/admin/staff/roles/'.$p['id'].'/assign'); }
    $adminUser = requireRole('admin','/admin/login');
    dbRun("INSERT OR IGNORE INTO staff_role_assignments (user_id, role_id, assigned_by) VALUES (?,?,?)", [$userId, $p['id'], $adminUser['id']]);
    // Auto update role to staff
    dbRun("UPDATE users SET role='staff' WHERE id=? AND role='customer'", [$userId]);
    // Notify user
    $roleName = dbGet("SELECT name FROM staff_roles WHERE id=?", [$p['id']])['name'] ?? 'Nhân viên';
    dbInsert("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?,'system','Phân quyền mới','Bạn đã được phân công vai trò: " . $roleName . ". Đăng nhập lại để truy cập.','/staff',datetime('now','localtime'))", [$userId]);
    dbRun("UPDATE users SET role='staff' WHERE id=? AND role='customer'", [$userId]);
    flash('success','Phân công thành công!');
    redirect('/admin/staff/roles/'.$p['id'].'/assign');
});

post('/admin/staff/unassign/:id', function($p) {
    requireRole('admin', '/admin/login'); csrfCheck();
    // Get user before deleting
    $asgn = dbGet('SELECT user_id FROM staff_role_assignments WHERE id=?', [$p['id']]);
    dbRun('DELETE FROM staff_role_assignments WHERE id=?', [$p['id']]);
    // Revert to customer if no remaining assignments
    if ($asgn) {
        $left = dbGet('SELECT COUNT(*) as c FROM staff_role_assignments WHERE user_id=?', [$asgn['user_id']]);
        if (($left['c'] ?? 0) == 0) {
            dbRun("UPDATE users SET role='customer' WHERE id=?", [$asgn['user_id']]);
            // Notify user
            dbInsert("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?,'system','Cập nhật quyền truy cập','Quyền nhân viên đã được thu hồi. Tài khoản đã chuyển về khách hàng.','/customer/orders',datetime('now','localtime'))", [$asgn['user_id']]);
        }
    }
    flash('success','Hủy phân quyền thành công!');
    redirect('/admin/staff');
});

// ── STAFF DASHBOARD ─────────────────────────────────────────────────────────

post('/admin/chat/mark-read', function() {
    $user = currentUser();
    if (!$user || !in_array($user['role'], ['admin','staff'])) { echo json_encode(['ok'=>false]); exit; }
    $threadId = intval($_POST['thread_id'] ?? 0);
    if ($threadId) {
        dbRun("UPDATE chat_messages SET status='read' WHERE thread_id=? AND sender_role='customer' AND status='sent'", [$threadId]);
        dbRun("UPDATE chat_threads SET partner_unread=0 WHERE id=?", [$threadId]);
    }
    echo json_encode(['ok'=>true]);
    exit;
});

get('/staff', function() {
    $user = requireRole('staff', '/auth/login');
    $roleAssignment = dbGet("SELECT sra.*, sr.name AS role_name, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=? ORDER BY sra.assigned_at DESC LIMIT 1", [$user['id']]);
    $perms = json_decode($roleAssignment['permissions'] ?? '[]', true) ?: [];
    if (in_array('orders', $perms) || in_array('create_order', $perms)) redirect('/staff/orders');
    if (in_array('products', $perms)) redirect('/staff/products');
    view('staff/dashboard', ['title'=>'Dashboard nhân viên','user'=>$user,'roleAssignment'=>$roleAssignment,'perms'=>$perms]);
});

get('/staff/orders', function() {
    $user = requireRole('staff', '/auth/login');
    // Redirect to admin orders (same view, staff has permission via requireStaffPermission)
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect('/admin/orders' . ($qs ? '?'.$qs : ''));
});

get('/staff/orders/create', function() {
    $user = requireRole('staff', '/auth/login');
    $roleAssignment = dbGet("SELECT sra.*, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=? ORDER BY sra.assigned_at DESC LIMIT 1", [$user['id']]);
    $perms = json_decode($roleAssignment['permissions'] ?? '[]', true) ?: [];
    if (!in_array('create_order', $perms)) { flash('error','Bạn không có quyền tạo đơn hàng hộ.'); redirect('/staff'); }
    view('admin/order-create', ['title'=>'Tạo đơn hàng hộ','role'=>'staff','currentUser'=>$user]);
});

// Staff API routes

post('/staff/notifications/read', function() {
    $user = requireRole('staff', '/auth/login');
    dbRun("UPDATE user_notifications SET is_read=1 WHERE user_id=? AND is_read=0", [$user['id']]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
});


post('/staff/notifications/:id/delete', function($p) {
    $user = requireRole('staff', '/auth/login');
    dbRun("DELETE FROM user_notifications WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
});

get('/staff/api/search-customers', function() {
    $user = requireRole('staff', '/auth/login');
    $q = trim($_GET['q'] ?? '');
    $users = dbAll("SELECT id, full_name, email, phone FROM users WHERE role='customer' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?) LIMIT 10", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($users);
    exit;
});

get('/staff/api/search-products', function() {
    $user = requireRole('staff', '/auth/login');
    $q = trim($_GET['q'] ?? '');
    $prods = dbAll("SELECT id, name, sku, price, stock FROM products WHERE status='published' AND (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) LIMIT 15", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($prods);
    exit;
});

post('/staff/orders/create', function() {
    $user = requireRole('staff', '/auth/login');
    $roleAssignment = dbGet("SELECT sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=?", [$user['id']]);
    $perms = json_decode($roleAssignment['permissions'] ?? '[]', true) ?: [];
    if (!in_array('create_order', $perms)) { flash('error','Không có quyền.'); redirect('/staff'); return; }
    
    csrfCheck();
    $items = json_decode($_POST['items'] ?? '[]', true);
    if (empty($items)) { flash('error','Vui lòng thêm ít nhất 1 sản phẩm.'); redirect('/staff/orders/create'); return; }
    
    // Form uses ship_name, ship_phone, ship_address etc
    $shipName = trim($_POST['ship_name'] ?? $_POST['shipping_full_name'] ?? '');
    $shipPhone = trim($_POST['ship_phone'] ?? $_POST['shipping_phone'] ?? '');
    $shipAddress = trim($_POST['ship_address'] ?? $_POST['shipping_detail'] ?? '');
    $shipDistrict = trim($_POST['ship_district'] ?? $_POST['shipping_district'] ?? '');
    $shipProvince = trim($_POST['ship_province'] ?? $_POST['shipping_province'] ?? '');
    $shipWard = trim($_POST['ship_ward'] ?? $_POST['shipping_ward'] ?? '');
    
    if (!$shipName || !$shipPhone) { flash('error','Thiếu thông tin khách hàng.'); redirect('/staff/orders/create'); return; }
    if (!preg_match('/^0[0-9]{9}$/', $shipPhone)) { flash('error','Số điện thoại phải đúng 10 chữ số và bắt đầu bằng 0!'); redirect('/staff/orders/create'); return; }
    
    
    $userId = intval($_POST['user_id'] ?? 0);
    $payMethod = in_array($_POST['payment_method'] ?? '', ['cod','bank_transfer']) ? $_POST['payment_method'] : 'cod';
    $staffNote = trim($_POST['staff_note'] ?? '');
    $deliveryStatus = in_array($_POST['delivery_status'] ?? '', ['pending','received']) ? $_POST['delivery_status'] : 'pending';
    
    // Calculate totals
    $subtotal = 0; $totalItems = 0;
    foreach ($items as $it) {
        $p = dbGet("SELECT price FROM products WHERE id=?", [$it['product_id']]);
        if ($p) { $subtotal += $p['price'] * intval($it['qty']); $totalItems += intval($it['qty']); }
    }
    
    // If no user_id, create a guest user
    if (!$userId) {
        $guestEmail = 'guest_' . time() . rand(1000,9999) . '@guest.local';
        $guestPwd = password_hash(uniqid(), PASSWORD_BCRYPT);
        $userId = dbInsert("INSERT INTO users (full_name, email, password_hash, phone, role, status) VALUES (?,?,?,?,?,?)",
            [$shipName, $guestEmail, $guestPwd, $shipPhone, 'customer', 'active']);
    }
    
    $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    $grand = $subtotal;
    
    // Create order
    $orderId = dbInsert("INSERT INTO orders (code, user_id, grand_total, subtotal, total_items, payment_method, payment_type, payment_status, delivery_status, created_by_staff, staff_note, shipping_full_name, shipping_phone, shipping_detail, shipping_district, shipping_province, shipping_ward, customer_note, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,datetime('now','localtime'))",
        [$code, $userId, $grand, $subtotal, $totalItems, $payMethod, 'full', 'unpaid', $deliveryStatus,
         $user['id'], $staffNote, $shipName, $shipPhone, $shipAddress, $shipDistrict, $shipProvince, $shipWard, $staffNote]);
    
    // Create sub_order (required for order_items FK)
    $subCode = $code . '-C4CA';
    $subOrderId = dbInsert("INSERT INTO sub_orders (order_id, partner_id, code, subtotal, grand_total, status, created_at) VALUES (?, 1, ?, ?, ?, 'pending_payment', datetime('now','localtime'))",
        [$orderId, $subCode, $subtotal, $grand]);
    
    // Create order items with correct schema (snapshot_name, unit_price)
    foreach ($items as $it) {
        $p = dbGet("SELECT id, name, sku, price, oem_code FROM products WHERE id=?", [$it['product_id']]);
        if ($p) {
            $img = dbGet("SELECT file_path FROM product_images WHERE product_id=? AND is_main=1 LIMIT 1", [$p['id']]);
            dbInsert("INSERT INTO order_items (sub_order_id, product_id, snapshot_name, snapshot_oem, snapshot_image, unit_price, quantity, line_total) VALUES (?,?,?,?,?,?,?,?)",
                [$subOrderId, $p['id'], $p['name'], $p['oem_code']??'', $img['file_path']??'', $p['price'], intval($it['qty']), $p['price']*intval($it['qty'])]);
            // Reduce stock
            dbRun("UPDATE products SET stock = MAX(0, stock - ?) WHERE id=?", [intval($it['qty']), $p['id']]);
        }
    }
    
    flash('success', "Đơn hàng $code đã được tạo thành công!");
    redirect('/staff/orders');
});



get('/admin/settings/finance', function() {
    requireStaffPermission('tax_config', '/auth/login');
    $rows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_tax_rate','default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent')");
    $config = [];
    foreach($rows as $r) $config[$r['key']] = $r['value'];
    view('admin/finance-config', ['title'=>'Cấu hình Thuế & Giảm giá', 'role'=>'admin', 'config'=>$config]);
});

post('/admin/settings/finance', function() {
    requireStaffPermission('tax_config', '/auth/login'); csrfCheck();
    
    $default_shipping_fee = $_POST['default_shipping_fee'] ?? '';
    $free_shipping_threshold = $_POST['free_shipping_threshold'] ?? '';
    $discount_quantity_threshold = $_POST['discount_quantity_threshold'] ?? '';
    $discount_quantity_percent = $_POST['discount_quantity_percent'] ?? '';

    // Phí vận chuyển mặc định phải là số không âm
    if (!is_numeric($default_shipping_fee) || $default_shipping_fee < 0) {
        flash('error', 'Phí vận chuyển mặc định phải là số không âm.');
        redirect('/admin/settings/finance');
        return;
    }
    // Ngưỡng miễn phí vận chuyển phải là số không âm
    if (!is_numeric($free_shipping_threshold) || $free_shipping_threshold < 0) {
        flash('error', 'Ngưỡng miễn phí vận chuyển phải là số không âm.');
        redirect('/admin/settings/finance');
        return;
    }
    // Số lượng SP để đạt giảm giá phải là số nguyên không âm
    if (!is_numeric($discount_quantity_threshold) || $discount_quantity_threshold < 0) {
        flash('error', 'Số lượng sản phẩm tối thiểu phải là số không âm.');
        redirect('/admin/settings/finance');
        return;
    }
    // Mức giảm giá phần trăm phải nằm trong khoảng từ 0 đến 100
    if (!is_numeric($discount_quantity_percent) || $discount_quantity_percent < 0 || $discount_quantity_percent > 100) {
        flash('error', 'Mức giảm giá phần trăm phải nằm từ 0% đến 100%.');
        redirect('/admin/settings/finance');
        return;
    }

    $keys = ['default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent'];
    foreach($keys as $k) {
        $v = $_POST[$k] ?? '';
        dbRun("INSERT INTO system_config (key, value, updated_at) VALUES (?,?,datetime('now')) ON CONFLICT(key) DO UPDATE SET value=?, updated_at=datetime('now')", [$k, $v, $v]);
    }
    flash('success', 'Đã lưu cấu hình tài chính & thuế.');
    redirect('/admin/settings/finance');
});


post('/admin/users/bulk-delete', function() {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    $ids = $_POST['ids'] ?? [];
    if (empty($ids)) { flash('error', 'Chưa chọn tài khoản nào.'); redirect('/admin/users'); return; }
    $deleted = 0;
    $errors = [];
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id <= 0) continue;
        $u = dbGet("SELECT role, full_name FROM users WHERE id=?", [$id]);
        if (!$u || $u['role'] === 'admin') continue;
        try {
            // Delete related records first to avoid FK constraint
            dbRun("DELETE FROM cart_items WHERE user_id=?", [$id]);
            dbRun("DELETE FROM favorites WHERE user_id=?", [$id]);
            dbRun("DELETE FROM user_saved_vouchers WHERE user_id=?", [$id]);
            dbRun("DELETE FROM user_notifications WHERE user_id=?", [$id]);
            dbRun("DELETE FROM notifications WHERE user_id=?", [$id]);
            dbRun("DELETE FROM review_reactions WHERE user_id=?", [$id]);
            dbRun("DELETE FROM shipping_addresses WHERE user_id=?", [$id]);
            dbRun("DELETE FROM user_invoice_info WHERE user_id=?", [$id]);
            dbRun("DELETE FROM garages WHERE user_id=?", [$id]);
            dbRun("DELETE FROM chat_messages WHERE sender_user_id=?", [$id]);
            dbRun("DELETE FROM audit_logs WHERE user_id=?", [$id]);
            dbRun("DELETE FROM reviews WHERE user_id=?", [$id]);
            dbRun("DELETE FROM review_images WHERE review_id NOT IN (SELECT id FROM reviews)", []);
            dbRun("DELETE FROM review_responses WHERE review_id NOT IN (SELECT id FROM reviews)", []);
            $threads = dbAll("SELECT id FROM chat_threads WHERE customer_id=?", [$id]);
            foreach ($threads as $t) { dbRun("DELETE FROM chat_messages WHERE thread_id=?", [$t['id']]); }
            dbRun("DELETE FROM chat_threads WHERE customer_id=?", [$id]);
            dbRun("DELETE FROM voucher_usage WHERE user_id=?", [$id]);
            dbRun("DELETE FROM voucher_saves WHERE user_id=?", [$id]);
            // wallet_transactions uses partner_id, not user_id - skip for non-partner users
            // wallets uses partner_id - skip for customer deletion
            dbRun("DELETE FROM newsletter_subscribers WHERE email=(SELECT email FROM users WHERE id=?)", [$id]);
            // Check if user has orders - if so, don't delete, just deactivate
            $hasOrders = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$id])['c'];
            if ($hasOrders > 0) {
                dbRun("UPDATE users SET status='locked', email=email||'_deleted_'||? WHERE id=?", [time(), $id]);
                $deleted++;
            } else {
                dbRun("DELETE FROM users WHERE id=?", [$id]);
                $deleted++;
            }
        } catch (Exception $e) {
            $errors[] = ($u['full_name'] ?? "ID $id") . ': ' . $e->getMessage();
        }
    }
    if ($deleted > 0) flash('success', "Đã xóa/vô hiệu hóa $deleted tài khoản.");
    if (!empty($errors)) flash('error', 'Một số lỗi: ' . implode('; ', $errors));
    redirect('/admin/users');
});

get('/admin/users', function() {
    requireStaffPermission('users', '/auth/login');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $status=$_GET['status']??''; $from=$_GET['from']??''; $to=$_GET['to']??'';
    $where="WHERE u.role IN ('customer','staff')"; $params=[];
    if($q){$where.=" AND (u.email LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l]);}
    if($status==='active'){$where.=" AND u.status='active' AND (u.suspended_until IS NULL OR u.suspended_until<=datetime('now'))";}
    elseif($status==='suspended'){$where.=" AND (u.status!='active' OR (u.suspended_until IS NOT NULL AND u.suspended_until>datetime('now')))";}
    if($from){$where.=" AND u.created_at>=?"; $params[]=$from;}
    if($to){$where.=" AND u.created_at<=?"; $params[]=$to.' 23:59:59';}
    $total=dbGet("SELECT COUNT(*) AS n FROM users u $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $users=dbAll("SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?",$p2);
    view('admin/users',['title'=>'Nguoi dung','role'=>'admin','users'=>$users,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages]);
});

get('/admin/reconciliation', function() {
    $user = requireRole('admin', '/admin/login');
    $summary = dbGet("SELECT COUNT(*) AS total_txn, SUM(CASE WHEN type='earn' THEN 1 ELSE 0 END) AS earn_count, SUM(CASE WHEN type='reversal' THEN 1 ELSE 0 END) AS reversal_count, COALESCE(SUM(gross_amount),0) AS total_gmv, COALESCE(SUM(commission_fee),0) AS total_fee, COALESCE(SUM(net_amount),0) AS total_net FROM commission_transactions");
    $byPartner = dbAll("SELECT ct.partner_id, p.shop_name, COUNT(*) AS txn, COALESCE(SUM(ct.gross_amount),0) AS gmv, COALESCE(SUM(ct.commission_fee),0) AS fee, COALESCE(SUM(ct.net_amount),0) AS net FROM commission_transactions ct INNER JOIN partners p ON p.id=ct.partner_id GROUP BY ct.partner_id ORDER BY fee DESC");
    view('admin/reconciliation', ['title'=>'Đối soát phí sàn','role'=>'admin','summary'=>$summary,'byPartner'=>$byPartner]);
});

get('/admin/withdrawals', function() {
    $user = requireRole('admin', '/admin/login');
    $items = dbAll("SELECT wr.*, p.shop_name, u.full_name, u.email, p.bank_name, p.bank_account_number, p.bank_account_holder FROM withdrawal_requests wr INNER JOIN partners p ON p.id=wr.partner_id INNER JOIN users u ON u.id=p.user_id ORDER BY wr.created_at DESC");
    view('admin/withdrawals', ['title'=>'Yêu cầu rút tiền','role'=>'admin','items'=>$items]);
});

get('/admin/vouchers', function() {
    $user = requireStaffPermission('vouchers', '/admin/login');
    $vouchers = dbAll("SELECT * FROM vouchers ORDER BY created_at DESC");
    $brands = dbAll("SELECT DISTINCT part_brand FROM products WHERE part_brand IS NOT NULL AND part_brand != '' ORDER BY part_brand");
    view('admin/vouchers', ['title'=>'Voucher toàn sàn','role'=>'admin','vouchers'=>$vouchers,'brands'=>$brands]);
});

post('/admin/vouchers/add', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    $scope = $_POST['scope'] ?? 'platform';
    $discount_type = $_POST['discount_type'] ?? 'amount';
    $discount_value = intval($_POST['discount_value'] ?? 0);
    $max_discount = !empty($_POST['max_discount']) ? intval($_POST['max_discount']) : null;
    $min_order_amount = intval($_POST['min_order_amount'] ?? $_POST['min_order_amount_fixed'] ?? 0);
    $funded_by = 'platform';
    $total_quantity = intval($_POST['total_quantity'] ?? 100);
    $max_per_user = intval($_POST['max_per_user'] ?? 1);
    $valid_from = $_POST['valid_from'] ?? date('Y-m-d');
    $valid_to = $_POST['valid_to'] ?? date('Y-m-d', strtotime('+30 days'));
    $status = $_POST['status'] ?? 'active';

    if (!$code || $discount_value <= 0 || !$name) {
        flash('error', 'Vui lòng nhập đầy đủ mã, tên và mức giảm.');
        redirect('/admin/vouchers');
        return;
    }
    
    // Validate dates: start date cannot be in the past
    $today = date('Y-m-d');
    if ($valid_from < $today) {
        flash('error', 'Ngày bắt đầu không thể là ngày đã qua. Vui lòng chọn từ hôm nay trở đi.');
        redirect('/admin/vouchers');
        return;
    }
    if ($valid_to < $today) {
        flash('error', 'Ngày kết thúc không thể là ngày đã qua.');
        redirect('/admin/vouchers');
        return;
    }
    if ($valid_to < $valid_from) {
        flash('error', 'Ngày kết thúc phải sau ngày bắt đầu.');
        redirect('/admin/vouchers');
        return;
    }
    
    // Validate scope
    $validScopes = ['platform','shop','freeship','new_customer'];
    if (!in_array($scope, $validScopes)) $scope = 'platform';
    
    // Validate discount_type
    if (!in_array($discount_type, ['percent','amount'])) $discount_type = 'amount';

    try {
        $user = currentUser();
        dbInsert("INSERT INTO vouchers (code, name, scope, discount_type, discount_value, max_discount, min_order_amount, funded_by, total_quantity, max_per_user, valid_from, valid_to, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
            [$code, $name, $scope, $discount_type, $discount_value, $max_discount, $min_order_amount, $funded_by, $total_quantity, $max_per_user, $valid_from, $valid_to, $status, $user['id']]);
        flash('success', "Đã thêm voucher: $code");
    } catch (Exception $e) {
        flash('error', 'Lỗi: ' . $e->getMessage());
    }
    redirect('/admin/vouchers');
});


post('/admin/vouchers/:id/edit', function($p) {
    requireStaffPermission('vouchers', '/auth/login'); csrfCheck();
    $id = intval($p['id']);
    $v = dbGet("SELECT * FROM vouchers WHERE id=?", [$id]);
    if (!$v) { flash('error', 'Không tìm thấy voucher.'); redirect('/admin/vouchers'); return; }
    
    $name = trim($_POST['name'] ?? '');
    $scope = $_POST['scope'] ?? $v['scope'];
    $discount_type = $_POST['discount_type'] ?? $v['discount_type'];
    $discount_value = intval($_POST['discount_value'] ?? $v['discount_value']);
    $max_discount = !empty($_POST['max_discount']) ? intval($_POST['max_discount']) : null;
    $min_order_amount = intval($_POST['min_order_amount'] ?? $_POST['min_order_amount_fixed'] ?? 0);
    $total_quantity = intval($_POST['total_quantity'] ?? $v['total_quantity']);
    $max_per_user = intval($_POST['max_per_user'] ?? $v['max_per_user']);

    if ($discount_value <= 0) { flash('error', 'Mức giảm phải lớn hơn 0.'); redirect('/admin/vouchers'); return; }
    if ($min_order_amount < 0) { flash('error', 'Đơn tối thiểu không được âm.'); redirect('/admin/vouchers'); return; }
    if ($total_quantity < 1) { flash('error', 'Số lượng phát hành phải >= 1.'); redirect('/admin/vouchers'); return; }
    if ($max_per_user < 1) { flash('error', 'Lượt/người tối đa phải >= 1.'); redirect('/admin/vouchers'); return; }    $valid_from = $_POST['valid_from'] ?? $v['valid_from'];
    $valid_to = $_POST['valid_to'] ?? $v['valid_to'];
    $status = $_POST['status'] ?? $v['status'];
    $scope_value = trim($_POST['scope_value'] ?? '');
    
    $validScopes = ['platform','shop','freeship','new_customer'];
    if (!in_array($scope, $validScopes)) $scope = 'platform';
    if (!in_array($discount_type, ['percent','amount'])) $discount_type = 'amount';
    
    try {
        dbRun("UPDATE vouchers SET name=?, scope=?, discount_type=?, discount_value=?, max_discount=?, min_order_amount=?, total_quantity=?, max_per_user=?, valid_from=?, valid_to=?, status=?, scope_value=? WHERE id=?",
            [$name, $scope, $discount_type, $discount_value, $max_discount, $min_order_amount, $total_quantity, $max_per_user, $valid_from, $valid_to, $status, $scope_value ?: null, $id]);
        flash('success', "Đã cập nhật voucher: " . $v['code']);
    } catch (Exception $e) {
        flash('error', 'Lỗi: ' . $e->getMessage());
    }
    redirect('/admin/vouchers');
});

post('/admin/vouchers/:id/delete', function($p) {
    requireStaffPermission('vouchers', '/auth/login'); csrfCheck();
    $id = intval($p['id']);
    $v = dbGet("SELECT code FROM vouchers WHERE id=?", [$id]);
    if (!$v) { flash('error', 'Không tìm thấy voucher.'); redirect('/admin/vouchers'); return; }
    try {
        // Delete related records first to avoid FK constraint
        dbRun("DELETE FROM voucher_usage WHERE voucher_id=?", [$id]);
        dbRun("DELETE FROM voucher_saves WHERE voucher_id=?", [$id]);
        dbRun("DELETE FROM user_saved_vouchers WHERE voucher_id=?", [$id]);
        dbRun("DELETE FROM vouchers WHERE id=?", [$id]);
        flash('success', 'Đã xóa voucher: ' . $v['code']);
    } catch (Exception $e) {
        // If still fails, try with PRAGMA
        try {
            dbRun("PRAGMA foreign_keys=OFF");
            dbRun("DELETE FROM vouchers WHERE id=?", [$id]);
            dbRun("PRAGMA foreign_keys=ON");
            flash('success', 'Đã xóa voucher: ' . $v['code']);
        } catch (Exception $e2) {
            flash('error', 'Không thể xóa voucher: ' . $e2->getMessage());
        }
    }
    redirect('/admin/vouchers');
});

get('/admin/reviews', function() {
    $user = requireStaffPermission('reviews', '/admin/login');
    $rating = intval($_GET['rating'] ?? 0);
    $categoryId = intval($_GET['category_id'] ?? 0);
    
    $where = 'WHERE 1=1';
    $params = [];
    if ($rating > 0 && $rating <= 5) {
        $where .= ' AND r.rating_overall=?';
        $params[] = $rating;
    }
    if ($categoryId > 0) {
        $where .= ' AND p.category_id=?';
        $params[] = $categoryId;
    }
    
    $reviews = dbAll("SELECT r.*, u.full_name, p.name AS product_name FROM reviews r INNER JOIN users u ON u.id=r.user_id INNER JOIN products p ON p.id=r.product_id $where ORDER BY r.created_at DESC LIMIT 100", $params);
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    view('admin/reviews', ['title'=>'Kiểm duyệt đánh giá','role'=>'admin','reviews'=>$reviews,'rating'=>$rating,'categoryId'=>$categoryId,'categories'=>$categories]);
});

get('/admin/catalog', function() {
    $user = requireRole('admin', '/admin/login');
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    view('admin/catalog', ['title'=>'Hãng xe & Danh mục','role'=>'admin','brands'=>$brands,'categories'=>$categories]);
});

get('/admin/audit', function() {
    $user = requireRole('admin', '/admin/login');
    $logs = dbAll("SELECT al.*, u.full_name, u.email FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC LIMIT 50");
    view('admin/audit', ['title'=>'Audit Log','role'=>'admin','logs'=>$logs]);
});

get('/admin/users/new', function() {
    requireStaffPermission('users', '/auth/login');
    view('admin/user-edit',['title'=>'Them tai khoan','role'=>'admin','editUser'=>[]]);
});
post('/admin/users/new', function() {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    $name=$_POST['full_name']??''; $email=strtolower(trim($_POST['email']??'')); $phone=$_POST['phone']??''; $addr=$_POST['address']??''; $role=$_POST['role']??'customer'; $pass=$_POST['password']??''; $notes=$_POST['notes']??'';
    if(!$name||!$email||strlen($pass)<6){flash('error','Thieu thong tin bat buoc.');redirect('/admin/users/new');}
    if(dbGet('SELECT 1 FROM users WHERE email=?',[$email])){flash('error','Email da ton tai.');redirect('/admin/users/new');}
    if(!in_array($role,['customer','staff','admin']))$role='customer';
    $uid=dbInsert("INSERT INTO users(role,email,phone,password_hash,full_name,address,status,email_verified,notes) VALUES(?,?,?,?,?,?,'active',1,?)",[$role,$email,$phone,password_hash($pass,PASSWORD_BCRYPT),$name,$addr,$notes]);
    if($role==='staff')dbRun("INSERT OR IGNORE INTO staff_permissions(user_id) VALUES(?)",[$uid]);
    // Save invoice info for new user
    if (!empty($_POST['buyer_name']) || !empty($_POST['tax_code']) || !empty($_POST['id_number'])) {
        dbRun("INSERT OR IGNORE INTO user_invoice_info (user_id,invoice_type,buyer_name,tax_code,address,province,ward,id_number,passport,email,phone,bank_name,bank_account) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$uid,trim($_POST['invoice_type']??'personal'),trim($_POST['buyer_name']??''),trim($_POST['tax_code']??''),trim($_POST['inv_address']??''),trim($_POST['province']??''),trim($_POST['ward']??''),trim($_POST['id_number']??''),trim($_POST['passport']??''),trim($_POST['inv_email']??''),trim($_POST['inv_phone']??''),trim($_POST['bank_name']??''),trim($_POST['bank_account']??'')]);
    }
    flash('success','Da tao tai khoan!');redirect('/admin/users');
});
get('/admin/users/:id/edit', function($p) {
    requireStaffPermission('users', '/auth/login');
    $editUser=dbGet('SELECT * FROM users WHERE id=?',[$p['id']]);
    if(!$editUser){flash('error','Khong tim thay.');redirect('/admin/users');}
    view('admin/user-edit',['title'=>'Sua tai khoan','role'=>'admin','editUser'=>$editUser]);
});
post('/admin/users/:id/edit', function($p) {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    $u=dbGet('SELECT * FROM users WHERE id=?',[$p['id']]);
    if(!$u){flash('error','Khong tim thay.');redirect('/admin/users');}
    $name=$_POST['full_name']??''; $phone=$_POST['phone']??''; $addr=$_POST['address']??''; $role=$_POST['role']??$u['role']; $pass=$_POST['password']??''; $notes=$_POST['notes']??'';
    $hash=$u['password_hash']; if(strlen($pass)>=6)$hash=password_hash($pass,PASSWORD_BCRYPT);
    if(!in_array($role,['customer','staff','admin']))$role=$u['role'];
    dbRun("UPDATE users SET full_name=?,phone=?,address=?,role=?,password_hash=?,notes=?,updated_at=datetime('now') WHERE id=?",[$name,$phone,$addr,$role,$hash,$notes,$p['id']]);
    if($role==='staff')dbRun("INSERT OR IGNORE INTO staff_permissions(user_id) VALUES(?)",[$p['id']]);
    flash('success','Da cap nhat!');redirect('/admin/users/'.$p['id'].'/edit');
});
post('/admin/users/:id/suspend', function($p) {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    $days=intval($_POST['days']??0); $notes=trim($_POST['notes']??'');
    if($days>0){
        $until=date('Y-m-d H:i:s',strtotime("+{$days} days"));
        dbRun("UPDATE users SET suspended_until=?,notes=?,updated_at=datetime('now') WHERE id=?",[$until,$notes,$p['id']]);
        flash('success',"Da ngung tai khoan {$days} ngay.");
    }else{
        dbRun("UPDATE users SET status='locked',suspended_until=NULL,notes=?,updated_at=datetime('now') WHERE id=?",[$notes,$p['id']]);
        flash('success','Da ngung tai khoan vinh vien.');
    }
    redirect('/admin/users');
});
post('/admin/users/:id/permissions', function($p) {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    $perms=['can_products','can_orders','can_content','can_users','can_vouchers','can_reviews'];
    $vals=[]; foreach($perms as $k)$vals[$k]=isset($_POST[$k])?1:0;
    dbRun("INSERT OR REPLACE INTO staff_permissions(user_id,can_products,can_orders,can_content,can_users,can_vouchers,can_reviews,updated_at) VALUES(?,?,?,?,?,?,?,datetime('now'))",
        [$p['id'],$vals['can_products'],$vals['can_orders'],$vals['can_content'],$vals['can_users'],$vals['can_vouchers'],$vals['can_reviews']]);
    flash('success','Da luu phan quyen!');redirect('/admin/users/'.$p['id'].'/edit');
});

post('/admin/users/:id/lock', function($p) {
    requireStaffPermission('users', '/auth/login'); csrfCheck();
    dbRun("UPDATE users SET status='locked', suspended_until=NULL WHERE id=?", [$p['id']]);
    flash('success','Da khoa tai khoan.'); redirect('/admin/users');
});

post('/admin/users/:id/unlock', function($p) {
    $user = requireStaffPermission('users', '/auth/login'); csrfCheck();
    dbRun("UPDATE users SET status='active' WHERE id=?", [$p['id']]);
    flash('success','Đã mở khóa tài khoản.'); redirect('/admin/users');
});

get('/admin/post-on-behalf', function() {
    $user = requireRole('admin', '/admin/login');
    $partners = dbAll("SELECT * FROM partners WHERE status='active' ORDER BY shop_name");
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    view('admin/post-on-behalf', ['title'=>'Đăng SP hộ','role'=>'admin','partners'=>$partners,'categories'=>$categories,'brands'=>$brands]);
});

post('/admin/post-on-behalf', function() {
    $user = requireRole('admin', '/admin/login'); csrfCheck();
    $partnerId = intval($_POST['partner_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');;
    $sku = trim($_POST['sku'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    if (!$partnerId || !$name || !$sku || !$price) { flash('error','Thiếu thông tin bắt buộc.'); redirect('/admin/post-on-behalf'); }
    $slug = preg_replace('/[^a-z0-9]+/','-',strtolower(removeAccents($name)));
    $id = dbInsert("INSERT INTO products (partner_id, sku, oem_code, part_brand, category_id, name, slug, description, price, compare_price, stock, warranty_months, status, admin_created, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'published',1,datetime('now'))",
        [$partnerId, $sku, $_POST['oem_code']??'', $_POST['part_brand']??'', intval($_POST['category_id']??0), $name, $slug, $_POST['description']??'', $price, intval($_POST['original_price']??0), $stock, intval($_POST['warranty_months']??12)]);
    flash('success','Đã đăng SP #'.$id.' thành công!');
    redirect('/admin/products');
});

get('/admin/post-on-behalf', function() {
    $user = requireRole('admin', '/admin/login');
    $partners = dbAll("SELECT * FROM partners WHERE status='active' ORDER BY shop_name");
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    view('admin/post-on-behalf', ['title'=>'Đăng SP hộ đối tác','role'=>'admin','partners'=>$partners,'categories'=>$categories,'brands'=>$brands]);
});

post('/admin/post-on-behalf', function() {
    $user = requireRole('admin', '/admin/login'); csrfCheck();
    $partnerId = intval($_POST['partner_id'] ?? 0);
    if (!$partnerId) { flash('error','Chọn đối tác'); redirect('/admin/post-on-behalf'); return; }
    $name = trim($_POST['name'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');;
    $oem = trim($_POST['oem_code'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $catId = intval($_POST['category_id'] ?? 0) ?: null;
    $desc = trim($_POST['description'] ?? '');
    if (!$name || !$price) { flash('error','Thiếu tên hoặc giá'); redirect('/admin/post-on-behalf'); return; }
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
    dbInsert("INSERT INTO products (partner_id, name, slug, oem_code, price, stock, category_id, description, status, published_at) VALUES (?,?,?,?,?,?,?,?,'published',datetime('now'))",
        [$partnerId, $name, $slug, $oem, $price, $stock, $catId, $desc]);
    // Save SEO fields
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc = trim($_POST['seo_description'] ?? '');
    $seoKeyword = trim($_POST['seo_keyword'] ?? '');
    $seoSlug = trim($_POST['slug'] ?? '');
    if ($seoTitle || $seoDesc || $seoKeyword || $seoSlug) {
        dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=?, slug=COALESCE(NULLIF(?,'''),slug) WHERE id=?",
            [$seoTitle, $seoDesc, $seoKeyword, $seoSlug, $p['id']]);
    }
    
    flash('success','Đã đăng sản phẩm hộ đối tác.');
    redirect('/admin/products');
});

// ── PRODUCTS (Admin posts directly) ────────────────────────────────────────
get('/admin/products/new', function() {
    $user = requireStaffPermission('products', '/admin/login');
    $categories = dbAll('SELECT * FROM categories ORDER BY sort_order');
    $brands = dbAll('SELECT * FROM brands ORDER BY name ASC');
    view('admin/product-form', ['title'=>'Đăng SP mới','role'=>'admin','categories'=>$categories,'brands'=>$brands,'images'=>[]]);
});

post('/admin/products/new', function() {
    $user = requireStaffPermission('products', '/admin/login'); csrfCheck();
    $d = $_POST;
    $name = trim($d['name'] ?? '');
    $sku  = trim($d['sku']  ?? '');
    $price = intval($d['price'] ?? 0);
    $priceBefore = intval($d['price_before_tax'] ?? 0);
    $taxAmt = intval($d['tax_amount'] ?? 0);
    $stock = intval($d['stock'] ?? 0);
    if ($stock > 1000) { $stock = 1000; } // Max 1000
    $status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';
    $slug = trim($d['slug'] ?? '') ?: preg_replace('/\s+/','-',strtolower(trim($name)));
    $slug = preg_replace('/[^a-z0-9\-]/','',$slug);

    if (!$name) { flash('error','Vui lòng nhập tên sản phẩm.'); redirect('/admin/products/new'); }

    $id = dbInsert("INSERT INTO products (name,sku,slug,oem_code,part_brand,car_brand_id,category_id,price,price_before_tax,tax_amount,vat_rate,original_price,stock,min_stock,max_stock,features,specifications,warranty_months,description,status,is_featured,show_on_home,show_on_promo,is_new,is_indexed,partner_id,published_at,created_at,weight_g,width_cm,height_cm,depth_cm,seo_title,seo_description,seo_keyword,video_url,cost_price,total_import_value) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,datetime('now','localtime'),datetime('now','localtime'),?,?,?,?,?,?,?,?,?,?)", [
        $name,
        $sku,
        $slug,
        trim($d['oem_code']??''),
        trim($d['part_brand']??''),
        intval($d['car_brand_id']??0) ?: null,
        intval($d['category_id']??0) ?: null,
        $price,
        $priceBefore,
        $taxAmt,
        intval($d['vat_rate']??10),
        intval($d['original_price']??0) ?: null,
        $stock,
        intval($d['min_stock']??5),
        intval($d['max_stock']??1000),
        $d['features']??'',
        $d['specifications']??'',
        intval($d['warranty_months']??12),
        $d['description'] ?? '',
        $status,
        isset($d['is_featured']) ? 1 : 0,
        isset($d['show_on_home']) ? 1 : 0,
        isset($d['show_on_promo']) ? 1 : 0,
        isset($d['is_new']) ? 1 : 0,
        isset($d['is_indexed']) ? 1 : 0,
        intval($d['weight_g']??0) ?: null,
        intval($d['width_cm']??0) ?: null,
        intval($d['height_cm']??0) ?: null,
        intval($d['depth_cm']??0) ?: null,
        trim($d['seo_title']??''),
        trim($d['seo_description']??''),
        trim($d['seo_keyword']??''),
        trim($d['video_url']??''),
        intval($d['cost_price']??0),
        intval($d['cost_price']??0) * $stock,
    ]);

    // Handle images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '/opt/cooling-php/uploads/products/';
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
            $fname = uniqid('p_') . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $fname)) {
                dbRun("INSERT INTO product_images (product_id,file_path,is_main) VALUES (?,?,?)",
                    [$id, $fname, $k===0 ? 1 : 0]);
            }
        }
    }

    // Save SEO fields
    $seoTitle = trim($d['seo_title'] ?? '');
    $seoDesc = trim($d['seo_description'] ?? '');
    $seoKeyword = trim($d['seo_keyword'] ?? '');
    $seoSlug = trim($d['slug'] ?? '') ?: $slug;
    dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=?, slug=? WHERE id=?",
        [$seoTitle, $seoDesc, $seoKeyword, $seoSlug, $id]);
    
    flash('success','Sản phẩm đã được đăng thành công!');
    redirect('/admin/products');
});

get('/admin/products/:id/edit', function($p) {
    $user = requireStaffPermission('products', '/admin/login');
    $product = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/products'); }
    $categories = dbAll('SELECT * FROM categories ORDER BY sort_order');
    $brands = dbAll('SELECT * FROM brands ORDER BY name ASC');
    $images = dbAll('SELECT * FROM product_images WHERE product_id=? ORDER BY is_main DESC', [$p['id']]);
    view('admin/product-form', ['title'=>'Sửa SP: '.truncate($product['name'],30),'role'=>'admin','product'=>$product,'categories'=>$categories,'brands'=>$brands,'images'=>$images]);
});

post('/admin/products/:id/edit', function($p) {
    $user = requireStaffPermission('products', '/admin/login'); csrfCheck();
    $d = $_POST;
    $price = intval($d['price'] ?? 0);
    $status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';

    dbRun("UPDATE products SET name=?,sku=?,oem_code=?,part_brand=?,car_brand_id=?,category_id=?,price=?,price_before_tax=?,tax_amount=?,vat_rate=?,original_price=?,stock=?,warranty_months=?,description=?,status=?,is_featured=?,show_on_home=?,show_on_promo=?,is_new=?,is_indexed=?,weight_g=?,width_cm=?,height_cm=?,depth_cm=?,video_url=?,cost_price=?,total_import_value=? WHERE id=?", [
        trim($d['name']??''),
        trim($d['sku']??''),
        trim($d['oem_code']??''),
        trim($d['part_brand']??''),
        intval($d['car_brand_id']??0) ?: null,
        intval($d['category_id']??0) ?: null,
        $price,
        intval($d['price_before_tax']??0),
        intval($d['tax_amount']??0),
        intval($d['vat_rate']??10),
        intval($d['original_price']??0) ?: null,
        intval($d['stock']??0),
        intval($d['warranty_months']??12),
        $d['description']??'',
        $status,
        isset($d['is_featured'])?1:0,
        isset($d['show_on_home'])?1:0,
        isset($d['show_on_promo'])?1:0,
        isset($d['is_new'])?1:0,
        isset($d['is_indexed'])?1:0,
        intval($d['weight_g']??0) ?: null,
        intval($d['width_cm']??0) ?: null,
        intval($d['height_cm']??0) ?: null,
        intval($d['depth_cm']??0) ?: null,
        trim($d['video_url']??''),
        intval($d['cost_price']??0),
        intval($d['cost_price']??0) * intval($d['stock']??0),
        $p['id']
    ]);

    // Handle new images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '/opt/cooling-php/uploads/products/';
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
            $fname = uniqid('p_') . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $fname)) {
                $hasMain = dbGet('SELECT id FROM product_images WHERE product_id=? AND is_main=1', [$p['id']]);
                dbRun("INSERT INTO product_images (product_id,file_path,is_main) VALUES (?,?,?)",
                    [$p['id'], $fname, $hasMain ? 0 : 1]);
            }
        }
    }

    // Save SEO fields on edit
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc = trim($_POST['seo_description'] ?? '');
    $seoKeyword = trim($_POST['seo_keyword'] ?? '');
    $seoSlug = trim($_POST['slug'] ?? '');
    if ($seoSlug) {
        dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=?, slug=? WHERE id=?",
            [$seoTitle, $seoDesc, $seoKeyword, $seoSlug, $p['id']]);
    } else {
        dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=? WHERE id=?",
            [$seoTitle, $seoDesc, $seoKeyword, $p['id']]);
    }
    // Save video_url if provided
    $videoUrl = trim($d['video_url'] ?? '');
    if ($videoUrl !== '') {
        dbRun("UPDATE products SET video_url=? WHERE id=?", [$videoUrl, $p['id']]);
    }
    
    // Save features and specifications
    $features = trim($_POST['features'] ?? '');
    $specs = trim($_POST['specifications'] ?? '');
    if ($features || $specs) {
        dbRun("UPDATE products SET features=?, specifications=? WHERE id=?", [$features, $specs, $p['id']]);
    }

    
    // Save multi-brand mapping
    $brandIds = $_POST['car_brand_ids'] ?? [];
    dbRun("DELETE FROM product_brand_map WHERE product_id=?", [$p['id']]);
    foreach ($brandIds as $bid) {
        $bid = intval($bid);
        if ($bid > 0) {
            dbRun("INSERT OR IGNORE INTO product_brand_map (product_id, brand_id) VALUES (?,?)", [$p['id'], $bid]);
        }
    }
    // Keep car_brand_id for backward compat (first selected brand)
    $firstBrand = null;
    foreach ($brandIds as $bid) {
        if ($bid === 'HIDDEN') { $firstBrand = 'HIDDEN'; break; }
        if (intval($bid) > 0) { $firstBrand = intval($bid); break; }
    }
    dbRun("UPDATE products SET car_brand_id=? WHERE id=?", [$firstBrand, $p['id']]);

    flash('success','Cập nhật sản phẩm thành công!');
    redirect('/admin/products/'.$p['id'].'/edit');
});

// ── STATIC CONTENT MANAGEMENT ────────────────────────────────────────────

// Banner management
post('/admin/banner/update', function() {
    requireStaffPermission('content', '/auth/login'); csrfCheck();
    $fields = ['hero_badge','hero_heading','hero_subtext','hero_btn1_text','hero_btn1_url','hero_btn2_text','hero_btn2_url'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)", [$f, $val]);
    }
    // Handle bg image upload
    if (!empty($_FILES['hero_bg_image']['tmp_name']) && is_uploaded_file($_FILES['hero_bg_image']['tmp_name'])) {
        @mkdir('/opt/cooling-php/uploads/banners', 0755, true);
        $ext = strtolower(pathinfo($_FILES['hero_bg_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = 'hero_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['hero_bg_image']['tmp_name'], '/opt/cooling-php/uploads/banners/' . $fname);
            dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES ('hero_bg_image', ?)", [$fname]);
        }
    }
    flash('success', 'Đã cập nhật banner trang chủ.');
    redirect('/admin/content');
});

get('/admin/content', function() {
    $user = requireStaffPermission('content', '/auth/login');
    $pages = dbAll('SELECT * FROM static_pages ORDER BY title');
    $bannerSettings = []; $bkeys = ['hero_badge','hero_heading','hero_subtext','hero_btn1_text','hero_btn1_url','hero_btn2_text','hero_btn2_url','hero_bg_image']; foreach($bkeys as $bk){$r=dbGet("SELECT value FROM settings WHERE key=?",[$bk]); $bannerSettings[$bk]=$r['value']??'';}
    view('admin/content-list', array_merge( ['title'=>'Quản lý nội dung','role'=>'admin','pages'=>$pages], ['bannerSettings'=>$bannerSettings]));
});

get('/admin/content/:slug', function($p) {
    $user = requireStaffPermission('content', '/auth/login');
    $page = dbGet('SELECT * FROM static_pages WHERE slug=?', [$p['slug']]);
    if (!$page) { flash('error','Không tìm thấy trang.'); redirect('/admin/content'); }
    view('admin/content-editor', ['title'=>'Sửa: '.$page['title'],'role'=>'admin','page'=>$page]);
});

post('/admin/content/:slug', function($p) {
    $user = requireStaffPermission('content', '/auth/login'); csrfCheck();
    $content = $_POST['content'] ?? '';
    $title   = trim($_POST['title'] ?? '');
    if (!$title) { flash('error','Cần có tiêu đề.'); redirect('/admin/content/'.$p['slug']); }
    $exists = dbGet('SELECT id FROM static_pages WHERE slug=?', [$p['slug']]);
    if ($exists) {
        dbRun("UPDATE static_pages SET content=?,title=?,updated_at=datetime('now'),updated_by=? WHERE slug=?",
            [$content, $title, $user['id'], $p['slug']]);
    } else {
        dbInsert("INSERT INTO static_pages(slug,title,content,updated_at,updated_by) VALUES(?,?,?,datetime('now'),?)",
            [$p['slug'], $title, $content, $user['id']]);
    }
    flash('success','Nội dung trang đã được lưu!');
    redirect('/admin/content/'.$p['slug']);
});

// ── NEWS / ARTICLES ─────────────────────────────────────────────────────────
get('/admin/news', function() {
    requireStaffPermission('content', '/auth/login');
    $perPage = 10;
    $page = max(1, intval($_GET['page'] ?? 1));
    $total = dbGet("SELECT COUNT(*) AS n FROM articles")['n'] ?? 0;
    $totalPages = max(1, ceil($total / $perPage));
    $offset = ($page - 1) * $perPage;
    
    $articles = dbAll("SELECT * FROM articles ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset]);
    view('admin/news-list', [
        'title' => 'Tin tức',
        'role' => 'admin',
        'articles' => $articles,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});
get('/admin/news/new', function() {
    requireStaffPermission('content', '/auth/login');
    view('admin/news-form', ['title'=>'Viết bài mới','role'=>'admin','article'=>[]]);
});
post('/admin/news/new', function() {
    $user = requireStaffPermission('content', '/auth/login'); csrfCheck();
    $title=$_POST['title']??''; $slug=trim($_POST['slug']??''); $excerpt=$_POST['excerpt']??''; $content=$_POST['content']??''; $status=in_array($_POST['status']??'',['draft','published'])?$_POST['status']:'draft';
    if (!$title) { flash('error','Cần có tiêu đề.'); redirect('/admin/news/new'); }
    if (!$slug) {
        $slug=mb_strtolower($title);
        $maps=['à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a','è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e','ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i','ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o','ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u','ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d'];
        $slug=strtr($slug,$maps);
        $slug=preg_replace('/[^a-z0-9]+/','-',trim($slug));
    }
    $thumb=null;
    if (!empty($_FILES['thumbnail']['tmp_name'])&&is_uploaded_file($_FILES['thumbnail']['tmp_name'])) {
        $ext=strtolower(pathinfo($_FILES['thumbnail']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp'])) { $fname=uniqid('news_').'.'.$ext; if (move_uploaded_file($_FILES['thumbnail']['tmp_name'],'/opt/cooling-php/uploads/news/'.$fname)) $thumb=$fname; }
    }
    $pub=$status==='published'?date('Y-m-d H:i:s'):null;
    dbInsert("INSERT INTO articles (title,slug,excerpt,content,thumbnail,author_id,status,published_at) VALUES (?,?,?,?,?,?,?,?)",[$title,$slug,$excerpt,$content,$thumb,$user['id'],$status,$pub]);
    flash('success','Đã đăng bài!'); redirect('/admin/news');
});
get('/admin/news/:id/edit', function($p) {
    requireStaffPermission('content', '/auth/login');
    $article=dbGet('SELECT * FROM articles WHERE id=?',[$p['id']]);
    if (!$article) { flash('error','Không tìm thấy.'); redirect('/admin/news'); }
    view('admin/news-form',['title'=>'Sửa bài','role'=>'admin','article'=>$article]);
});
post('/admin/news/:id/edit', function($p) {
    $user=requireStaffPermission('content', '/auth/login'); csrfCheck();
    $article=dbGet('SELECT * FROM articles WHERE id=?',[$p['id']]);
    if (!$article) { flash('error','Không tìm thấy.'); redirect('/admin/news'); }
    $title=$_POST['title']??''; $slug=trim($_POST['slug']??'')?:$article['slug']; $excerpt=$_POST['excerpt']??''; $content=$_POST['content']??''; $status=in_array($_POST['status']??'',['draft','published'])?$_POST['status']:'draft';
    if (!$title) { flash('error','Cần tiêu đề.'); redirect('/admin/news/'.$p['id'].'/edit'); }
    $thumb=$article['thumbnail'];
    if (!empty($_FILES['thumbnail']['tmp_name'])&&is_uploaded_file($_FILES['thumbnail']['tmp_name'])) {
        $ext=strtolower(pathinfo($_FILES['thumbnail']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp'])) { $fname=uniqid('news_').'.'.$ext; if (move_uploaded_file($_FILES['thumbnail']['tmp_name'],'/opt/cooling-php/uploads/news/'.$fname)) $thumb=$fname; }
    }
    $pub=($status==='published'&&!$article['published_at'])?date('Y-m-d H:i:s'):$article['published_at'];
    dbRun("UPDATE articles SET title=?,slug=?,excerpt=?,content=?,thumbnail=?,status=?,published_at=?,updated_at=datetime('now') WHERE id=?",[$title,$slug,$excerpt,$content,$thumb,$status,$pub,$p['id']]);
    flash('success','Đã lưu!'); redirect('/admin/news/'.$p['id'].'/edit');
});
post('/admin/news/:id/delete', function($p) {
    requireStaffPermission('content', '/auth/login'); csrfCheck();
    // Clean up child records to avoid FK constraint
    try { dbRun("DELETE FROM article_categories WHERE article_id=?",[$p['id']]); } catch (Exception $e) {}
    try { dbRun("DELETE FROM article_tags WHERE article_id=?",[$p['id']]); } catch (Exception $e) {}
    dbRun("DELETE FROM articles WHERE id=?",[$p['id']]);
    flash('success','Đã xóa bài.'); redirect('/admin/news');
});

post('/admin/products/:id/toggle-status', function($p) {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    $status = $_POST['status'] ?? 'hidden';
    dbRun("UPDATE products SET status=? WHERE id=?", [$status, $p['id']]);
    flash('success', 'Đã cập nhật trạng thái kinh doanh.');
    redirect('/admin/products');
});

post('/admin/products/:id/delete', function($p) {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    dbRun("DELETE FROM product_images WHERE product_id=?", [$p['id']]);
    dbRun("DELETE FROM products WHERE id=?", [$p['id']]);
    flash('success','Da xoa san pham.');
    redirect('/admin/products');
});

post('/admin/notifications/read-all', function() {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    dbRun("UPDATE admin_notifications SET is_read=1 WHERE is_read=0");
    echo "ok";
});

post('/admin/notifications/:id/read', function($p) {
    requireStaffPermission('orders', '/admin/login');
    // no csrfCheck required for simple status update via fetch without sensitive data change, but good practice
    dbRun("UPDATE admin_notifications SET is_read=1 WHERE id=?", [$p['id']]);
    echo "ok";
});

post('/admin/notifications/:id/delete', function($p) {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    dbRun("DELETE FROM admin_notifications WHERE id=?", [$p['id']]);
    echo "ok";
});

// ── System Settings ─────────────────────────────────────────────────────────
get('/admin/settings', function() {
    $user = requireRole('admin', '/admin/login');
    view('admin/settings', ['title'=>'Cài đặt hệ thống', 'user'=>$user]);
});


post('/admin/settings/general', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    $phone = trim($_POST['site_phone'] ?? '');
    dbRun("INSERT INTO system_config (key, value) VALUES ('site_phone',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$phone]);
    
    if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/svg+xml','image/webp'];
        $mime = mime_content_type($_FILES['site_logo']['tmp_name']);
        if (in_array($mime, $allowed) || in_array(strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION)), ['svg','png','jpg','jpeg','webp'])) {
            $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $fname = 'logo_' . time() . '.' . strtolower($ext);
            $dest = '/var/lib/cooling/uploads/' . $fname;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $dest)) {
                dbRun("INSERT INTO system_config (key, value) VALUES ('site_logo',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$fname]);
            }
        }
    }
    flash('success','Đã lưu thông tin chung!');
    // contact_hotline_sync: auto-sync contact hotline
    if (!empty($phone)) {
        $formatted = preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1 $2 $3', $phone);
        dbRun("INSERT INTO system_config (key, value) VALUES ('contact_hotline',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$formatted]);
    }
        $phone = trim($_POST['site_phone'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (!empty($phone) && !preg_match('/^0[0-9]{9}$/', $phone)) {
        flash('error', 'Hotline phải là 10 chữ số bắt đầu từ 0.');
        redirect('/admin/settings'); return;
    }
redirect('/admin/settings');
});


// Delete individual product image
post('/admin/products/delete-image', function() {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    $imageId = (int)($_POST['image_id'] ?? 0);
    if (!$imageId) { echo json_encode(['ok'=>false,'msg'=>'ID ảnh không hợp lệ']); return; }
    $img = dbGet("SELECT * FROM product_images WHERE id=?", [$imageId]);
    if (!$img) { echo json_encode(['ok'=>false,'msg'=>'Ảnh không tồn tại']); return; }
    // Delete file
    $filePath = '/var/lib/cooling/uploads/products/' . $img['file_path'];
    if (file_exists($filePath)) { unlink($filePath); }
    // Delete DB record
    dbRun("DELETE FROM product_images WHERE id=?", [$imageId]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true,'msg'=>'Đã xóa ảnh']);
});

post('/admin/settings/social', function() {
    requireStaffPermission('tax_config', '/auth/login'); csrfCheck();
    $fields = ['social_whatsapp','social_tiktok','social_facebook'];
    foreach($fields as $f) {
        $val = trim($_POST[$f] ?? '');
                if (!empty($val) && !preg_match('#^https?://.+\\..+#', $val)) {
            flash('error', 'Link phải là URL hợp lệ (bắt đầu bằng https://)');
            redirect('/admin/settings'); return;
        }
        dbRun("INSERT INTO system_config (key, value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=strftime('%Y-%m-%dT%H:%M:%fZ','now')", [$f, $val]);
    }
    flash('success','Đã lưu liên kết mạng xã hội!');
    redirect('/admin/settings');
});

post('/admin/settings/payment', function() {
    requireStaffPermission('tax_config', '/auth/login'); csrfCheck();
    $fields = ['payment_bank_name','payment_account_name','payment_account_number'];
    foreach($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        dbRun("INSERT INTO system_config (key, value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=strftime('%Y-%m-%dT%H:%M:%fZ','now')", [$f, $val]);
    }
    // Handle QR image upload
    if (!empty($_FILES['qr_image']['name']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $mime = mime_content_type($_FILES['qr_image']['tmp_name']);
        if (in_array($mime, $allowed)) {
            $ext = pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION);
            $fname = 'qr_' . time() . '.' . strtolower($ext);
            $dest = '/var/lib/cooling/uploads/qr/' . $fname;
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $dest)) {
                dbRun("INSERT INTO system_config (key, value) VALUES ('payment_qr_image',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=strftime('%Y-%m-%dT%H:%M:%fZ','now')", [$fname]);
            }
        }
    }
    flash('success','Đã lưu cài đặt thanh toán!');
    redirect('/admin/settings');
});


// ── BRANDS (Hãng xe) CRUD ────────────────────────────────────────────────────
get('/admin/brands', function() {
    $user = requireStaffPermission('brands', '/admin/login');
    view('admin/brands', ['title'=>'Quản lý Hãng xe', 'role'=>'admin', 'user'=>$user]);
});

post('/admin/brands/add', function() {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 100);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/brands'); }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    $imagePath = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'brand_' . time() . '.' . strtolower($ext);
        $dest = '/var/lib/cooling/uploads/brands/' . $fname;
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $imagePath = $fname;
        }
    }

    try {
        if ($imagePath) {
            dbInsert("INSERT INTO brands (name, slug, sort_order, image) VALUES (?,?,?,?)", [$name, $slug, $sort, $imagePath]);
        } else {
            dbInsert("INSERT INTO brands (name, slug, sort_order) VALUES (?,?,?)", [$name, $slug, $sort]);
        }
        flash('success', "Đã thêm hãng xe: $name");
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại. Vui lòng đổi slug khác.');
    }
    redirect('/admin/brands');
});

post('/admin/brands/:id/edit', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 100);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/brands'); }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    $imagePath = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'brand_' . time() . '.' . strtolower($ext);
        $dest = '/var/lib/cooling/uploads/brands/' . $fname;
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $imagePath = $fname;
        }
    }

    try {
        if ($imagePath) {
            dbRun("UPDATE brands SET name=?, slug=?, sort_order=?, image=? WHERE id=?", [$name, $slug, $sort, $imagePath, $p['id']]);
        } else {
            dbRun("UPDATE brands SET name=?, slug=?, sort_order=? WHERE id=?", [$name, $slug, $sort, $p['id']]);
        }
        flash('success', "Đã cập nhật hãng xe.");
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại. Vui lòng đổi slug khác.');
    }
    redirect('/admin/brands');
});

post('/admin/brands/:id/delete', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    dbRun("DELETE FROM brands WHERE id=?", [$p['id']]);
    flash('success', 'Đã xóa hãng xe.');
    redirect('/admin/brands');
});

// ── CAR MODELS CRUD ──────────────────────────────────────────────────────────
post('/admin/car-models/add', function() {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $brandId = intval($_POST['brand_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $yearFrom = intval($_POST['year_from'] ?? date('Y'));
    $yearTo = !empty($_POST['year_to']) ? intval($_POST['year_to']) : null;
    if (!$brandId || !$name || !$slug) { flash('error','Thiếu thông tin bắt buộc.'); redirect('/admin/brands?brand_id='.$brandId); }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    try {
        dbInsert("INSERT INTO car_models (brand_id, name, slug, year_from, year_to) VALUES (?,?,?,?,?)", [$brandId, $name, $slug, $yearFrom, $yearTo]);
        flash('success', "Đã thêm dòng xe: $name");
    } catch (Exception $e) {
        flash('error', 'Dòng xe đã tồn tại trong hãng này.');
    }
    redirect('/admin/brands?brand_id='.$brandId);
});

post('/admin/car-models/:id/edit', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $brandId = intval($_POST['brand_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $yearFrom = intval($_POST['year_from'] ?? date('Y'));
    $yearTo = !empty($_POST['year_to']) ? intval($_POST['year_to']) : null;
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    dbRun("UPDATE car_models SET name=?, slug=?, year_from=?, year_to=? WHERE id=?", [$name, $slug, $yearFrom, $yearTo, $p['id']]);
    flash('success', 'Đã cập nhật dòng xe.');
    redirect('/admin/brands?brand_id='.$brandId);
});

post('/admin/car-models/:id/delete', function($p) {
    requireStaffPermission('brands', '/admin/login'); csrfCheck();
    $brandId = intval($_POST['brand_id'] ?? 0);
    dbRun("DELETE FROM car_models WHERE id=?", [$p['id']]);
    flash('success', 'Đã xóa dòng xe.');
    redirect('/admin/brands?brand_id='.$brandId);
});

// ── CATEGORIES CRUD ──────────────────────────────────────────────────────────
get('/admin/categories', function() {
    $user = requireStaffPermission('categories', '/auth/login');
    view('admin/categories', ['title'=>'Quản lý Danh mục', 'role'=>'admin', 'user'=>$user]);
});

post('/admin/categories/add', function() {
    requireStaffPermission('categories', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $sort = intval($_POST['sort_order'] ?? 100);
    $featured = intval($_POST['is_featured'] ?? 0);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/categories'); }
    $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $slug));
    try {
        dbInsert("INSERT INTO categories (name, slug, parent_id, sort_order, is_featured) VALUES (?,?,?,?,?)", [$name, $slug, $parentId, $sort, $featured]);
        flash('success', "Đã thêm danh mục: $name");
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại. Vui lòng đổi slug khác.');
    }
    $redir = $parentId ? '/admin/categories?parent_id='.$parentId : '/admin/categories';
    redirect($redir);
});

post('/admin/categories/:id/edit', function($p) {
    requireStaffPermission('categories', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $sort = intval($_POST['sort_order'] ?? 100);
    $featured = intval($_POST['is_featured'] ?? 0);
    $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $slug));
    try {
        dbRun("UPDATE categories SET name=?, slug=?, parent_id=?, sort_order=?, is_featured=? WHERE id=?", [$name, $slug, $parentId, $sort, $featured, $p['id']]);
        flash('success', 'Đã cập nhật danh mục.');
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại.');
    }
    $redir = $parentId ? '/admin/categories?parent_id='.$parentId : '/admin/categories';
    redirect($redir);
});

post('/admin/categories/:id/delete', function($p) {
    requireStaffPermission('categories', '/auth/login'); csrfCheck();
    // Reassign children to no parent
    dbRun("UPDATE categories SET parent_id=NULL WHERE parent_id=?", [$p['id']]);
    dbRun("DELETE FROM categories WHERE id=?", [$p['id']]);
    flash('success', 'Đã xóa danh mục.');
    redirect('/admin/categories');
});

// ── PROMOTIONS (Khuyến mãi) CRUD ─────────────────────────────────────────────
get('/admin/promotions', function() {
    $user = requireStaffPermission('promotions', '/auth/login');
    view('admin/promotions', ['title'=>'Quản lý Khuyến mãi', 'role'=>'admin', 'user'=>$user]);
});

post('/admin/promotions/:id/set-sale', function($p) {
    requireStaffPermission('promotions', '/auth/login'); csrfCheck();
    $salePrice = intval($_POST['sale_price'] ?? 0);
    $product = dbGet("SELECT price FROM products WHERE id=?", [$p['id']]);
    if (!$product) { flash('error', 'Không tìm thấy sản phẩm.'); redirect('/admin/promotions'); return; }
    if ($salePrice < 0) { flash('error', 'Giá khuyến mãi không được âm.'); redirect('/admin/promotions'); return; }
    if ($salePrice > 0 && $salePrice >= $product['price']) { flash('error', 'Giá khuyến mãi phải nhỏ hơn giá gốc (' . number_format($product['price']) . ' ₫).'); redirect('/admin/promotions'); return; }
    dbRun("UPDATE products SET sale_price=? WHERE id=?", [$salePrice, $p['id']]);
    flash('success', 'Đã lưu giá khuyến mãi.');
    redirect('/admin/promotions');
});

post('/admin/promotions/:id/toggle', function($p) {
    requireStaffPermission('promotions', '/auth/login'); csrfCheck();
    $prod = dbGet("SELECT is_on_sale FROM products WHERE id=?", [$p['id']]);
    $newVal = $prod['is_on_sale'] ? 0 : 1;
    dbRun("UPDATE products SET is_on_sale=? WHERE id=?", [$newVal, $p['id']]);
    flash('success', $newVal ? 'Đã bật khuyến mãi.' : 'Đã tắt khuyến mãi.');
    redirect('/admin/promotions');
});


// ===== Product Brands Management =====
get('/admin/product-brands', function() {
    requireStaffPermission('brand_models', '/auth/login');
    $productBrands = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    view('admin/product-brands', ['title' => 'Quan ly Thuong hieu', 'productBrands' => $productBrands]);
});

post('/admin/product-brands/new', function() {
    requireStaffPermission('brand_models', '/auth/login');
    csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error', 'Ten thuong hieu khong duoc de trong.'); redirect('/admin/product-brands'); return; }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $logoFile = '';
    if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','svg'])) {
            $dest = '/var/lib/cooling/uploads/product-brands/' . uniqid('pb_') . '.' . $ext;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) { $logoFile = basename($dest); }
        }
    }
    $desc = trim($_POST['description'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 0);
    try {
        dbInsert("INSERT INTO product_brands (name, slug, logo, description, sort_order) VALUES (?,?,?,?,?)", [$name, $slug, $logoFile, $desc, $sort]);
        flash('success', 'Da them thuong hieu thanh cong!');
    } catch (\Exception $e) {
        flash('error', 'Ten thuong hieu da ton tai.');
    }
    redirect('/admin/product-brands');
});

post('/admin/product-brands/:id/edit', function($p) {
    requireStaffPermission('brand_models', '/auth/login');
    csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error', 'Ten khong duoc de trong.'); redirect('/admin/product-brands'); return; }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $desc = trim($_POST['description'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 0);
    $existing = dbGet("SELECT logo FROM product_brands WHERE id=?", [$p['id']]);
    $logoFile = $existing['logo'] ?? '';
    if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','svg'])) {
            $dest = '/var/lib/cooling/uploads/product-brands/' . uniqid('pb_') . '.' . $ext;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) { $logoFile = basename($dest); }
        }
    }
    dbRun("UPDATE product_brands SET name=?, slug=?, logo=?, description=?, sort_order=? WHERE id=?", [$name, $slug, $logoFile, $desc, $sort, $p['id']]);
    flash('success', 'Da cap nhat thuong hieu!');
    redirect('/admin/product-brands');
});

post('/admin/product-brands/:id/delete', function($p) {
    requireStaffPermission('brand_models', '/auth/login');
    csrfCheck();
    dbRun("DELETE FROM product_brands WHERE id=?", [$p['id']]);
    flash('success', 'Da xoa thuong hieu.');
    redirect('/admin/product-brands');
});


// ── Stores Management ──
get('/admin/stores', function() {
    requireStaffPermission('stores', '/auth/login');
    $stores = dbAll("SELECT * FROM stores ORDER BY sort_order, name");
    view('admin/stores', ['title'=>'Hệ thống cửa hàng','role'=>'admin','stores'=>$stores]);
});

post('/admin/stores/add', function() {
    requireStaffPermission('stores', '/auth/login'); csrfCheck();
    $name = trim($_POST['name']??'');
    if (!$name) { flash('error','Tên cửa hàng không được trống.'); redirect('/admin/stores'); return; }
    dbInsert("INSERT INTO stores (name,type,address,phone,hours,lat,lng,sort_order,is_active) VALUES (?,?,?,?,?,?,?,?,?)", [
        $name, $_POST['type']??'chi_nhanh', trim($_POST['address']??''), trim($_POST['phone']??''),
        trim($_POST['hours']??'8:00 - 18:00'), floatval($_POST['lat']??0), floatval($_POST['lng']??0),
        intval($_POST['sort_order']??0), isset($_POST['is_active'])?1:0
    ]);
    flash('success','Đã thêm cửa hàng mới.');
    redirect('/admin/stores');
});

post('/admin/stores/:id/edit', function($p) {
    requireStaffPermission('stores', '/auth/login'); csrfCheck();
    $name = trim($_POST['name']??'');
    if (!$name) { flash('error','Tên không được trống.'); redirect('/admin/stores'); return; }
    dbRun("UPDATE stores SET name=?,type=?,address=?,phone=?,hours=?,lat=?,lng=?,sort_order=?,is_active=? WHERE id=?", [
        $name, $_POST['type']??'chi_nhanh', trim($_POST['address']??''), trim($_POST['phone']??''),
        trim($_POST['hours']??'8:00 - 18:00'), floatval($_POST['lat']??0), floatval($_POST['lng']??0),
        intval($_POST['sort_order']??0), isset($_POST['is_active'])?1:0, intval($p['id'])
    ]);
    flash('success','Đã cập nhật cửa hàng.');
    redirect('/admin/stores');
});

post('/admin/stores/:id/delete', function($p) {
    requireStaffPermission('stores', '/auth/login'); csrfCheck();
    dbRun("DELETE FROM stores WHERE id=?", [$p['id']]);
    flash('success','Đã xóa cửa hàng.');
    redirect('/admin/stores');
});

// ===== QUẢN LÝ TRẢ HÀNG =====
get('/admin/returns', function() {
    $user = requireStaffPermission('returns', '/auth/login');
    $status = $_GET['status'] ?? '';
    $where = '';
    $whereCount = '';
    $params = [];
    if ($status && in_array($status, ['pending','approved','rejected'])) {
        $where = ' WHERE r.status = ?';
        $whereCount = " WHERE status = ?";
        $params[] = $status;
    }
    // Pagination
    $perPage = 10;
    $page = max(1, intval($_GET['page'] ?? 1));
    $totalCount = dbGet("SELECT COUNT(*) as c FROM order_returns" . $whereCount, $params)['c'] ?? 0;
    $totalPages = max(1, ceil($totalCount / $perPage));
    $offset = ($page - 1) * $perPage;
    
    $returns = dbAll("SELECT r.*, o.code as order_code, o.grand_total, u.full_name, u.phone as user_phone, u.email as user_email
                      FROM order_returns r
                      JOIN orders o ON o.id = r.order_id
                      JOIN users u ON u.id = r.user_id
                      $where
                      ORDER BY r.created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]));
    $counts = [
        'all' => dbGet("SELECT COUNT(*) as c FROM order_returns")['c'],
        'pending' => dbGet("SELECT COUNT(*) as c FROM order_returns WHERE status='pending'")['c'],
        'approved' => dbGet("SELECT COUNT(*) as c FROM order_returns WHERE status='approved'")['c'],
        'rejected' => dbGet("SELECT COUNT(*) as c FROM order_returns WHERE status='rejected'")['c'],
    ];
    view('admin/returns', ['title'=>'Quản lý trả hàng','returns'=>$returns,'counts'=>$counts,'currentStatus'=>$status,'page'=>$page,'totalPages'=>$totalPages]);
});

post('/admin/returns/:id/approve', function($p) {
    requireStaffPermission('returns', '/auth/login'); csrfCheck();
    $ret = dbGet("SELECT r.*, o.code, o.user_id, o.grand_total FROM order_returns r JOIN orders o ON o.id=r.order_id WHERE r.id=?", [$p['id']]);
    if (!$ret) { flash('error','Không tìm thấy yêu cầu'); redirect('/admin/returns'); return; }
    
    $refundAmount = intval($ret['refund_amount'] ?: $ret['grand_total']);
    
    dbRun("UPDATE order_returns SET status='approved' WHERE id=?", [$p['id']]);
    dbRun("UPDATE orders SET delivery_status='returned' WHERE id=?", [$ret['order_id']]);
    
    // Ghi nhan so tien hoan tra (tru doanh thu)
    // Luu vao order de dashboard biet tru
    dbRun("UPDATE orders SET refund_amount=? WHERE id=?", [$refundAmount, $ret['order_id']]);
    
    // Thong bao cho customer
    $refundFormatted = number_format($refundAmount, 0, ',', '.') . ' d';
    dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)", [
        $ret['user_id'], 'return_approved', 'Yêu cầu trả hàng được duyệt',
        'Đơn #' . $ret['code'] . ' đã được duyệt trả hàng. Số tiền hoàn trả: ' . $refundFormatted . '. Vui lòng kiểm tra tài khoản.',
        '/customer/orders'
    ]);
    flash('success','Đã duyệt trả hàng đơn #' . $ret['code'] . '. Hoàn trả: ' . $refundFormatted);
    redirect('/admin/returns');
});

post('/admin/returns/:id/reject', function($p) {
    requireStaffPermission('returns', '/auth/login'); csrfCheck();
    $ret = dbGet("SELECT r.*, o.code, o.user_id FROM order_returns r JOIN orders o ON o.id=r.order_id WHERE r.id=?", [$p['id']]);
    if (!$ret) { flash('error','Không tìm thấy yêu cầu'); redirect('/admin/returns'); return; }
    dbRun("UPDATE order_returns SET status='rejected' WHERE id=?", [$p['id']]);
    // Thông báo cho customer
    dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)", [
        $ret['user_id'], 'return_rejected', 'Yêu cầu trả hàng bị từ chối',
        'Yêu cầu trả hàng cho đơn #' . $ret['code'] . ' đã bị từ chối. Liên hệ hotline để biết thêm chi tiết.',
        '/customer/orders'
    ]);
    flash('success','Đã từ chối yêu cầu trả hàng đơn #' . $ret['code']);
    redirect('/admin/returns');
});

// ── XÓA YÊU CẦU TRẢ HÀNG ──────────────────────────────────────────
post('/admin/returns/:id/delete', function($p) {
    requireStaffPermission('returns', '/auth/login'); csrfCheck();
    $ret = dbGet("SELECT r.*, o.code FROM order_returns r JOIN orders o ON o.id=r.order_id WHERE r.id=?", [$p['id']]);
    if (!$ret) { flash('error', 'Không tìm thấy yêu cầu trả hàng.'); redirect('/admin/returns'); return; }
    dbRun("DELETE FROM order_returns WHERE id=?", [$p['id']]);
    flash('success', 'Đã xóa yêu cầu trả hàng #' . $p['id'] . ' (đơn #' . ($ret['code'] ?? '') . ')');
    redirect('/admin/returns');
});


// ── XÓA ĐƠN HÀNG ──────────────────────────────────────────────────
post('/admin/orders/:id/delete', function($p) {
    requireStaffPermission('orders', '/admin/login'); csrfCheck();
    $order = dbGet("SELECT * FROM orders WHERE id=?", [$p['id']]);
    if (!$order) { flash('error', 'Không tìm thấy đơn hàng.'); redirect('/admin/orders'); return; }
    // Delete related records
    dbRun("DELETE FROM sub_orders WHERE order_id=?", [$p['id']]);
    dbRun("DELETE FROM order_items WHERE order_id=?", [$p['id']]);
    dbRun("DELETE FROM order_returns WHERE order_id=?", [$p['id']]);
    dbRun("DELETE FROM orders WHERE id=?", [$p['id']]);
    flash('success', 'Đã xóa đơn hàng #' . ($order['code'] ?? $order['order_code'] ?? $p['id']));
    redirect('/admin/orders');
});



// ── Invoice Info API ──
get('/admin/users/:id/invoice-info', function($p) {
    $user = requireStaffPermission('users', '/auth/login');
    header('Content-Type: application/json');
    $info = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$p['id']]);
    echo json_encode($info ?: ['user_id'=>$p['id'],'invoice_type'=>'personal','buyer_name'=>'','tax_code'=>'','address'=>'','province'=>'','ward'=>'','id_number'=>'','passport'=>'','email'=>'','phone'=>'','bank_name'=>'','bank_account'=>'']);
    exit;
});

post('/admin/users/:id/invoice-info', function($p) {
    $user = requireStaffPermission('users', '/auth/login'); csrfCheck();
    header('Content-Type: application/json');
    $uid = intval($p['id']);
    $fields = ['invoice_type','buyer_name','tax_code','address','province','ward','id_number','passport','email','phone','bank_name','bank_account','company_name','legal_representative'];
    $data = [];
    foreach($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    $exists = dbGet("SELECT id FROM user_invoice_info WHERE user_id=?", [$uid]);
    if ($exists) {
        dbRun("UPDATE user_invoice_info SET invoice_type=?,buyer_name=?,tax_code=?,address=?,province=?,ward=?,id_number=?,passport=?,email=?,phone=?,bank_name=?,bank_account=?,company_name=?,legal_representative=?,updated_at=datetime('now','localtime') WHERE user_id=?",
            [$data['invoice_type'],$data['buyer_name'],$data['tax_code'],$data['address'],$data['province'],$data['ward'],$data['id_number'],$data['passport'],$data['email'],$data['phone'],$data['bank_name'],$data['bank_account'],$data['company_name'],$data['legal_representative'],$uid]);
    } else {
        dbRun("INSERT INTO user_invoice_info (user_id,invoice_type,buyer_name,tax_code,address,province,ward,id_number,passport,email,phone,bank_name,bank_account,company_name,legal_representative) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$uid,$data['invoice_type'],$data['buyer_name'],$data['tax_code'],$data['address'],$data['province'],$data['ward'],$data['id_number'],$data['passport'],$data['email'],$data['phone'],$data['bank_name'],$data['bank_account'],$data['company_name'],$data['legal_representative']]);
    }
    echo json_encode(['ok'=>true]);
    exit;
});

// Admin user detail (AJAX)
get('/admin/users/:id/detail', function($p) {
    $user = requireStaffPermission('users', '/auth/login');
    header('Content-Type: application/json');
    $u = dbGet("SELECT id,full_name,email,phone,role,status,address,avatar,created_at,notes,suspended_until FROM users WHERE id=?", [$p['id']]);
    if (!$u) { echo json_encode(['error'=>'not found']); exit; }
    $invoice = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$p['id']]);
    $orderCount = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$p['id']])['c'] ?? 0;
    echo json_encode(['user'=>$u, 'invoice'=>$invoice, 'order_count'=>$orderCount]);
    exit;
});

// ── Contact Messages Management ──
get('/admin/contacts', function() {
    $u = currentUser();
    if (!$u) { redirect('/admin/login'); return; }
    if ($u['role'] !== 'admin') {
        $perm = dbGet("SELECT can_contacts FROM staff_permissions WHERE user_id=?", [$u['id']]);
        if (!$perm || !$perm['can_contacts']) { http_response_code(403); echo '403 Forbidden'; return; }
    }
    view('admin/contacts', ['title' => 'Quản lý liên hệ']);
});

post('/admin/contacts/:id/reply', function($p) {
    $u = currentUser();
    if (!$u) { redirect('/admin/login'); return; }
    if ($u['role'] !== 'admin') {
        $perm = dbGet("SELECT can_contacts FROM staff_permissions WHERE user_id=?", [$u['id']]);
        if (!$perm || !$perm['can_contacts']) { http_response_code(403); echo '403 Forbidden'; return; }
    } csrfCheck();
    $id = (int)$p['id'];
    $reply = trim($_POST['reply'] ?? '');
    if (empty($reply) || mb_strlen($reply) < 5) {
        flash('error', 'Nội dung trả lời phải có ít nhất 5 ký tự.');
        redirect('/admin/contacts');
    }

    $msg = dbGet("SELECT * FROM contact_messages WHERE id=?", [$id]);
    if (!$msg) { flash('error', 'Tin nhắn không tồn tại.'); redirect('/admin/contacts'); }

    // Update DB
    $adminUser = currentUser();
    dbRun("UPDATE contact_messages SET reply=?, status='replied', replied_at=datetime('now','localtime'), replied_by=? WHERE id=?",
        [$reply, $adminUser['id'] ?? 0, $id]);

    // Send email to customer
    require_once __DIR__ . '/../includes/mailer.php';
    $subject = 'Phản hồi từ Cooling System — ' . ($msg['subject'] ?? 'Liên hệ');
    $html = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#fff;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden">'
        . '<div style="background:#0d1b3e;padding:20px;text-align:center"><h1 style="color:#d4a84b;margin:0;font-size:22px">COOLING</h1><p style="color:#ccc;margin:4px 0 0;font-size:12px">Phụ Tùng & Dịch Vụ Ô Tô</p></div>'
        . '<div style="padding:24px">'
        . '<p style="color:#333;font-size:15px">Xin chào <strong>' . htmlspecialchars($msg['name']) . '</strong>,</p>'
        . '<p style="color:#555;font-size:14px">Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi cho tin nhắn của bạn:</p>'
        . '<div style="background:#f0f4f8;border-left:4px solid #0d1b3e;padding:12px 16px;margin:16px 0;border-radius:4px">'
        . '<p style="color:#888;font-size:12px;margin:0 0 4px">Tin nhắn của bạn:</p>'
        . '<p style="color:#555;font-size:13px;margin:0">' . htmlspecialchars($msg['message']) . '</p>'
        . '</div>'
        . '<div style="background:#edf7ed;border-left:4px solid #28a745;padding:12px 16px;margin:16px 0;border-radius:4px">'
        . '<p style="color:#155724;font-size:12px;margin:0 0 4px">Phản hồi từ Cooling:</p>'
        . '<p style="color:#333;font-size:14px;margin:0">' . nl2br(htmlspecialchars($reply)) . '</p>'
        . '</div>'
        . '<hr style="border:none;border-top:1px solid #e0e0e0;margin:20px 0">'
        . '<p style="color:#999;font-size:12px">Nếu bạn cần thêm hỗ trợ, hãy gọi hotline: <strong>08 6585 6585</strong> hoặc trả lời email này.</p>'
        . '</div>'
        . '<div style="background:#f8f9fa;padding:14px;text-align:center;font-size:11px;color:#999">© ' . date('Y') . ' Cooling — coolingsystem.vn</div>'
        . '</div>';

    $emailSent = sendEmail($msg['email'], $subject, $html);

    if ($emailSent) {
        flash('success', 'Đã gửi phản hồi đến ' . $msg['email'] . ' thành công!');
    } else {
        flash('warning', 'Đã lưu phản hồi nhưng không gửi được email. Kiểm tra cấu hình mail server.');
    }
    redirect('/admin/contacts');
});

post('/admin/contacts/:id/mark-read', function($p) {
    $u = currentUser();
    if (!$u) { redirect('/admin/login'); return; }
    if ($u['role'] !== 'admin') {
        $perm = dbGet("SELECT can_contacts FROM staff_permissions WHERE user_id=?", [$u['id']]);
        if (!$perm || !$perm['can_contacts']) { http_response_code(403); echo '403 Forbidden'; return; }
    }
    $id = (int)$p['id'];
    dbRun("UPDATE contact_messages SET status='read', is_read=1 WHERE id=? AND status='new'", [$id]);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) { header('Content-Type: application/json'); echo '{"ok":true}'; exit; }
    redirect('/admin/contacts');
});

get('/admin/contacts/:id/delete', function($p) {
    requireRole('admin', '/admin/login');
    $id = (int)$p['id'];
    dbRun("DELETE FROM contact_messages WHERE id=?", [$id]);
    flash('success', 'Đã xóa tin nhắn.');
    redirect('/admin/contacts');
});



// ── Contact Info Settings ──
post('/admin/settings/contact-info', function() {
    requireRole('admin', '/admin/login'); csrfCheck();
    
    $email = trim($_POST['contact_email'] ?? '');
    $address = trim($_POST['contact_address'] ?? '');
    $hours = trim($_POST['contact_hours'] ?? '');

    // Validate email
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Cấu trúc email liên hệ không hợp lệ.');
        redirect('/admin/settings');
        return;
    }

    // Validate address
    if (mb_strlen($address) > 100) {
        flash('error', 'Địa chỉ tối đa 100 ký tự.');
        redirect('/admin/settings');
        return;
    }
    if (!empty($address) && preg_match('/[^\p{L}\p{N}\s,\.\-\/\(\):]/u', $address)) {
        flash('error', 'Địa chỉ không được chứa ký tự đặc biệt.');
        redirect('/admin/settings');
        return;
    }

    // Validate hours
    if (mb_strlen($hours) > 100) {
        flash('error', 'Giờ làm việc tối đa 100 ký tự.');
        redirect('/admin/settings');
        return;
    }
    if (!empty($hours) && preg_match('/[^\p{L}\p{N}\s,\.\-\/\(\):]/u', $hours)) {
        flash('error', 'Giờ làm việc không được chứa ký tự đặc biệt.');
        redirect('/admin/settings');
        return;
    }

    $fields = ['contact_email' => $email, 'contact_address' => $address, 'contact_hours' => $hours];
    foreach($fields as $f => $val) {
        dbRun("INSERT INTO system_config (key, value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$f, $val]);
    }
    // Sync hotline from site_phone
    $phone = dbGet("SELECT value FROM system_config WHERE key='site_phone'")['value'] ?? '';
    if ($phone) {
        $formatted = preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1 $2 $3', $phone);
        dbRun("INSERT INTO system_config (key, value) VALUES ('contact_hotline',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$formatted]);
    }
    flash('success', 'Đã cập nhật thông tin liên hệ!');
    redirect('/admin/settings');
});

// ── Newsletter / Promotion settings ──
get('/admin/settings/newsletter', function() {
    global $user;
    $nl = [];
    $keys = ['newsletter_title','newsletter_subtitle','newsletter_voucher_amount','newsletter_voucher_code','newsletter_btn_text'];
    foreach($keys as $k) {
        $r = dbGet("SELECT value FROM system_config WHERE key=?", [$k]);
        $nl[$k] = $r['value'] ?? '';
    }
    view('admin/newsletter-settings', ['title'=>'Cài đặt ưu đãi đăng ký', 'user'=>$user, 'nl'=>$nl]);
});

post('/admin/settings/newsletter', function() {
    $fields = ['newsletter_title','newsletter_subtitle','newsletter_voucher_amount','newsletter_voucher_code','newsletter_btn_text'];
    foreach($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        dbRun("INSERT OR REPLACE INTO system_config (key, value) VALUES (?, ?)", [$f, $val]);
    }
    flash('success', 'Đã cập nhật cài đặt ưu đãi đăng ký!');
    redirect('/admin/settings/newsletter');
});
// ── Cấu hình hiển thị sản phẩm mới ──
get('/admin/settings/products', function() {
    requireRole('admin', '/admin');
    $days = dbGet("SELECT value FROM site_config WHERE key='new_product_days'");
    $days = $days['value'] ?? 7;
    view('admin/settings-products', ['title' => 'Cấu hình SP mới', 'days' => $days]);
});

post('/admin/settings/products', function() {
    requireRole('admin', '/admin');
    csrfCheck();
    $days = max(1, min(90, intval($_POST['new_product_days'] ?? 7)));
    dbRun("INSERT OR REPLACE INTO site_config (key, value, updated_at) VALUES ('new_product_days', ?, datetime('now'))", [$days]);
    flash('success', 'Đã lưu: Sản phẩm mới hiển thị trong ' . $days . ' ngày.');
    redirect('/admin/settings/products');
});

// Admin cancel order + notify user
post('/admin/orders/:id/cancel', function($p) {
    requireRole(['admin','staff'], '/admin');
    csrfCheck();
    $order = dbGet("SELECT o.*, u.id as uid FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?", [$p['id']]);
    if (!$order) { flash('error','Đơn không tồn tại.'); redirect('/admin/orders'); return; }
    $reason = trim($_POST['cancel_reason'] ?? 'Admin hủy đơn');
    dbRun("UPDATE orders SET delivery_status='cancelled', updated_at=datetime('now') WHERE id=?", [$p['id']]);
    // Restore stock
    $items = dbAll("SELECT oi.* FROM order_items oi INNER JOIN sub_orders so ON so.id=oi.sub_order_id WHERE so.order_id=?", [$p['id']]);
    foreach ($items as $it) { dbRun("UPDATE products SET stock=stock+?, sold_count=MAX(0,sold_count-?) WHERE id=?", [$it['quantity'],$it['quantity'],$it['product_id']]); }
    // Notify customer
    if ($order['uid']) {
        dbRun("INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (?, 'order_cancelled', ?, ?, ?, datetime('now'))",
            [$order['uid'], 'Đơn hàng đã bị hủy', 'Đơn hàng '.$order['code'].' đã bị hủy. Lý do: '.$reason, '/customer/orders/'.$p['id']]);
    }
    flash('success','Đã hủy đơn hàng '.$order['code'].' và thông báo cho khách.');
    redirect('/admin/orders');
});

// Admin create order page
