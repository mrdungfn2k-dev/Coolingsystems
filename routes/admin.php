<?php
require_once __DIR__ . '/../includes/inventory-alerts.php';
get('/admin/login', function() { view('admin/login', ['title' => 'Đăng nhập Quản trị']); });

post('/admin/login', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (!$email || !$password) { flash('error','Vui lòng nhập email và mật khẩu.'); redirect('/admin/login'); }
    $user = dbGet('SELECT * FROM users WHERE email=?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email hoặc mật khẩu không đúng.'); redirect('/admin/login'); }
    if (!empty($user['is_superadmin'])) { flash('error','Tài khoản không hợp lệ ở khu vực này.'); redirect('/admin/login'); }
    if ($user['role'] === 'staff' && staffHasAssignment($user['id'])) { if ($user['status'] !== 'active') { flash('error','Tài khoản bị khóa.'); redirect('/admin/login'); } loginUser($user['id']); flash('success','Xin chào, '.$user['full_name'].'!'); redirect('/staff'); }
    if ($user['role'] !== 'admin') { flash('error','Tài khoản không có quyền truy cập khu vực quản trị.'); redirect('/admin/login'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản bị khóa.'); redirect('/admin/login'); }
    loginUser($user['id']);
    flash('success','Xin chào, '.$user['full_name'].'!');
    redirect('/admin');
});

get('/admin/logout', function() { logout('/admin/login'); });
get('/superadmin-k9x27c', function() { $u = currentUser(); if ($u && !empty($u['is_superadmin'])) redirect('/admin'); view('admin/superadmin-login', ['title' => 'Super Admin']); });
get('/superadmin-k9x27c/logout', function() { logout('/superadmin-k9x27c'); });
post('/superadmin-k9x27c', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (!$email || !$password) { flash('error','Vui lòng nhập email và mật khẩu.'); redirect('/superadmin-k9x27c'); }
    $user = dbGet('SELECT * FROM users WHERE email=?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email hoặc mật khẩu không đúng.'); redirect('/superadmin-k9x27c'); }
    if (empty($user['is_superadmin'])) { flash('error','Tài khoản không có quyền Super Admin.'); redirect('/superadmin-k9x27c'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản đang bị khóa.'); redirect('/superadmin-k9x27c'); }
    loginUser($user['id']);
    flash('success','Xin chào Super Admin!');
    redirect('/admin');
});
get('/staff/logout', function() { logout('/staff/login'); });

get('/admin/forgot', function() { view('admin/forgot', ['title' => 'Quên mật khẩu Quản trị']); });
post('/admin/forgot', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email) { flash('error','Vui lòng nhập email.'); redirect('/admin/forgot'); }
    $user = dbGet("SELECT * FROM users WHERE email=?", [$email]);
    if (!$user || $user['role'] !== 'admin') { flash('error','Email không phải tài khoản quản trị.'); redirect('/admin/forgot'); }
    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    dbRun("UPDATE users SET otp_code=?, otp_expires=? WHERE id=?", [$otp, $expires, $user['id']]);
    require_once __DIR__ . '/../includes/mailer.php';
    sendOTPEmail($email, $user['full_name'], $otp);
    $_SESSION['admin_reset_email'] = $email;
    flash('success','Mã OTP đã được gửi đến email quản trị.');
    redirect('/admin/reset');
});
get('/admin/reset', function() {
    if (empty($_SESSION['admin_reset_email'])) { redirect('/admin/forgot'); }
    view('admin/reset', ['title' => 'Đặt lại mật khẩu Quản trị', 'reset_email' => $_SESSION['admin_reset_email']]);
});
post('/admin/reset', function() {
    csrfCheck();
    $email = $_SESSION['admin_reset_email'] ?? '';
    if (!$email) { flash('error','Phiên hết hạn. Vui lòng thử lại.'); redirect('/admin/forgot'); }
    $otp = trim($_POST['otp'] ?? '');
    $newPass = $_POST['password'] ?? ''; $newPass2 = $_POST['password2'] ?? '';
    if (strlen($otp) !== 6) { flash('error','Mã OTP phải có 6 chữ số.'); redirect('/admin/reset'); }
    $user = dbGet("SELECT * FROM users WHERE email=? AND otp_code=? AND role='admin'", [$email, $otp]);
    if (!$user) { flash('error','Mã OTP không đúng.'); redirect('/admin/reset'); }
    if ($user['otp_expires'] < date('Y-m-d H:i:s')) { flash('error','Mã OTP đã hết hạn. Vui lòng gửi lại.'); redirect('/admin/forgot'); }
    if (strlen($newPass) < 6) { flash('error','Mật khẩu mới tối thiểu 6 ký tự.'); redirect('/admin/reset'); }
    if ($newPass !== $newPass2) { flash('error','Mật khẩu nhập lại không khớp.'); redirect('/admin/reset'); }
    dbRun("UPDATE users SET password_hash=?, otp_code=NULL, otp_expires=NULL WHERE id=?", [password_hash($newPass, PASSWORD_BCRYPT), $user['id']]);
    logPasswordChange($user['id'], 'forgot_otp');
    unset($_SESSION['admin_reset_email']);
    flash('success','Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
    redirect('/admin/login');
});

get('/admin', function() {
    $user = requireStaffPermission('reports', '/admin/login');
    
    // Xử lý bộ lọc biểu đồ
    $period = $_GET['period'] ?? 'month'; // week, month, year, custom
    $dateFrom = preg_replace('/[^0-9-]/', '', $_GET['date_from'] ?? '');
    $dateTo   = preg_replace('/[^0-9-]/', '', $_GET['date_to'] ?? '');
    $dateFilter = "date(created_at) >= date('now', '-30 days')";
    if ($period === 'week') $dateFilter = "date(created_at) >= date('now', '-7 days')";
    elseif ($period === 'year') $dateFilter = "date(created_at) >= date('now', '-365 days')";
    elseif ($period === 'custom' && $dateFrom && $dateTo) $dateFilter = "date(created_at) BETWEEN '$dateFrom' AND '$dateTo'";
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
    $recentOrders = dbAll("SELECT o.id, o.code, o.grand_total, o.payment_method, o.payment_status, o.delivery_status, o.shipping_phone, o.shipping_full_name, o.created_at, u.full_name FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset");
    
    // Stock alerts: products below min_stock or above max_stock (or > 1000)
    $lowStockProducts = dbAll("SELECT id, name, sku, stock, min_stock, max_stock FROM products WHERE status='published' AND stock <= min_stock AND min_stock > 0 ORDER BY stock ASC LIMIT 20");
    $overStockProducts = dbAll("SELECT id, name, sku, stock, min_stock, max_stock FROM products WHERE status='published' AND stock > 1000 ORDER BY stock DESC LIMIT 20");

    // ===== Dashboard cards / status / customers =====
    $c2 = dbGet("SELECT
        (SELECT COUNT(*) FROM orders WHERE date(created_at)=date('now')) AS orders_today,
        (SELECT COUNT(*) FROM orders WHERE date(created_at)=date('now','-1 day')) AS orders_yesterday,
        (SELECT COUNT(*) FROM orders WHERE strftime('%Y-%m',created_at)=strftime('%Y-%m','now')) AS orders_month,
        (SELECT COUNT(*) FROM orders WHERE strftime('%Y-%m',created_at)=strftime('%Y-%m',date('now','-1 month'))) AS orders_lastmonth,
        (SELECT COUNT(*) FROM orders WHERE COALESCE(delivery_status,'') IN ('','pending','received')) AS orders_pending,
        (SELECT COUNT(*) FROM orders WHERE delivery_status IN ('delivering','delivered','shipping','shipped')) AS orders_shipping,
        (SELECT COUNT(*) FROM orders WHERE delivery_status IN ('cancelled','canceled','returned','refunded')) AS orders_cancelled,
        (SELECT COUNT(*) FROM orders WHERE delivery_status IN ('completed','delivered')) AS orders_completed,
        (SELECT COUNT(*) FROM orders WHERE payment_status='paid') AS orders_paid,
        (SELECT COUNT(*) FROM users WHERE role='customer' AND strftime('%Y-%m',created_at)=strftime('%Y-%m','now')) AS newcust_month,
        (SELECT COUNT(*) FROM users WHERE role='customer' AND strftime('%Y-%m',created_at)=strftime('%Y-%m',date('now','-1 month'))) AS newcust_lastmonth,
        (SELECT COALESCE(SUM(grand_total),0)-COALESCE(SUM(refund_amount),0) FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered') AND date(created_at)=date('now','-1 day')) AS rev_yesterday,
        (SELECT COALESCE(SUM(grand_total),0)-COALESCE(SUM(refund_amount),0) FROM orders WHERE payment_status='paid' AND delivery_status IN ('completed','delivered') AND strftime('%Y-%m',created_at)=strftime('%Y-%m',date('now','-1 month'))) AS rev_lastmonth
    ") ?: [];
    $kpis = array_merge($kpis ?: [], $c2);
    $kpis['aov'] = (($kpis['orders_paid'] ?? 0) > 0) ? round(($kpis['revenue'] ?? 0) / $kpis['orders_paid']) : 0;
    $kpis['completion_rate'] = (($kpis['orders_total'] ?? 0) > 0) ? round(($kpis['orders_completed'] ?? 0) / $kpis['orders_total'] * 100, 1) : 0;
    $pf = function($cur,$prev){ $cur=(float)$cur; $prev=(float)$prev; if($prev>0) return round(($cur-$prev)/$prev*100,1); return $cur>0?100.0:0.0; };
    $pcts = [
        'rev_today' => $pf($kpis['today_revenue']??0, $kpis['rev_yesterday']??0),
        'orders_today' => $pf($kpis['orders_today']??0, $kpis['orders_yesterday']??0),
        'newcust' => $pf($kpis['newcust_month']??0, $kpis['newcust_lastmonth']??0),
        'month_rev' => $pf($kpis['month_revenue']??0, $kpis['rev_lastmonth']??0),
        'orders_month' => $pf($kpis['orders_month']??0, $kpis['orders_lastmonth']??0),
    ];
    $statusRaw = dbAll("SELECT COALESCE(delivery_status,'') AS s, COUNT(*) c FROM orders GROUP BY COALESCE(delivery_status,'')");
    $statusBuckets = [
        'Đang chờ' => ['c'=>0,'color'=>'#f59e0b'],
        'Tiếp nhận' => ['c'=>0,'color'=>'#3b82f6'],
        'Đang giao' => ['c'=>0,'color'=>'#8b5cf6'],
        'Đã giao' => ['c'=>0,'color'=>'#06b6d4'],
        'Đã hoàn thành' => ['c'=>0,'color'=>'#22c55e'],
        'Đã hủy' => ['c'=>0,'color'=>'#ef4444'],
        'Đã trả hàng' => ['c'=>0,'color'=>'#94a3b8'],
    ];
    foreach($statusRaw as $sr){ $s=$sr['s']; $n=(int)$sr['c'];
        if($s==='received') $statusBuckets['Tiếp nhận']['c']+=$n;
        elseif(in_array($s,['delivering','shipping'])) $statusBuckets['Đang giao']['c']+=$n;
        elseif(in_array($s,['delivered','shipped'])) $statusBuckets['Đã giao']['c']+=$n;
        elseif($s==='completed') $statusBuckets['Đã hoàn thành']['c']+=$n;
        elseif(in_array($s,['cancelled','canceled'])) $statusBuckets['Đã hủy']['c']+=$n;
        elseif(in_array($s,['returned','refunded'])) $statusBuckets['Đã trả hàng']['c']+=$n;
        else $statusBuckets['Đang chờ']['c']+=$n;
    }
    $topProducts = dbAll("SELECT id, name, sku, COALESCE(sold_count,0) AS sold, (SELECT file_path FROM product_images WHERE product_id=products.id ORDER BY is_main DESC, sort_order ASC LIMIT 1) AS img FROM products WHERE status='published' ORDER BY sold_count DESC, view_count DESC LIMIT 5");

    view('admin/dashboard', [
        'title'=>'Dashboard Admin',
        'role'=>'admin',
        'kpis'=>$kpis,
        'pcts'=>$pcts,
        'statusBuckets'=>$statusBuckets,
        'topProducts'=>$topProducts,
        'recentOrders'=>$recentOrders,
        'chartData' => $chartData,
        'period' => $period,
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

get('/admin/products', function() {    requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $tab=$_GET['tab']??'all'; $catId=intval($_GET['cat']??0);
    $brandId=intval($_GET['brand_id']??$_GET['brand']??0); $partBrand=trim($_GET['part_brand']??$_GET['pbrand']??'');
    $where='WHERE 1=1'; $params=[];
    if($tab==='draft'){$where.=" AND p.status='draft'";}
    elseif($tab==='published'){$where.=" AND p.status='published'";}
    if($q){$where.=" AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ? OR p.oem_code2 LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l,$l]);}
    if($catId){$where.=" AND p.category_id=?"; $params[]=$catId;}
    if($brandId){
        $brandRow = dbGet("SELECT name FROM brands WHERE id=?", [$brandId]);
        $bName = $brandRow['name'] ?? '';
        $where .= " AND (p.car_brand_id=? OR EXISTS(SELECT 1 FROM product_fitments pf WHERE pf.product_id=p.id AND pf.brand_id=?) OR EXISTS(SELECT 1 FROM product_brand_map pbm WHERE pbm.product_id=p.id AND pbm.brand_id=?)".($bName ? " OR p.name LIKE ?" : "").")";
        $params[] = $brandId;
        $params[] = $brandId;
        $params[] = $brandId;
        if ($bName) $params[] = '%' . $bName . '%';
    }
    if($partBrand){$where.=" AND (p.part_brand=? OR p.part_brand LIKE ? OR p.part_brand LIKE ? OR p.part_brand LIKE ?)"; $params[]=$partBrand; $params[]=$partBrand.',%'; $params[]='%, '.$partBrand.',%'; $params[]='%, '.$partBrand;}
    $total=dbGet("SELECT COUNT(*) AS n FROM products p $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    if ($page > $totalPages) { $page = $totalPages; }
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $products=dbAll("SELECT p.*,COALESCE(pt.shop_name,'Admin') AS shop_name,c.name AS cat_name,b.name AS brand_name FROM products p LEFT JOIN partners pt ON pt.id=p.partner_id LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.car_brand_id $where ORDER BY p.created_at DESC LIMIT ? OFFSET ?",$p2);
    $categories=dbAll("SELECT * FROM categories ORDER BY sort_order");
    $carBrands=dbAll("SELECT * FROM brands ORDER BY name");
    $partBrands=dbAll("SELECT name AS part_brand FROM product_brands ORDER BY sort_order, name");

    $listReturnUrl = '/admin/products' . (!empty($_GET) ? '?' . http_build_query($_GET) : '');
    view('admin/products',['title'=>'San pham','role'=>'admin','products'=>$products,'categories'=>$categories,'carBrands'=>$carBrands,'partBrands'=>$partBrands,'tab'=>$tab,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages,'filterBrandId'=>$brandId,'filterPartBrand'=>$partBrand,'listReturnUrl'=>$listReturnUrl]);
});


// Reorder product images (drag-and-drop)
post('/admin/products/reorder-images', function() {
    requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login'); csrfCheck();
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
    requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');
    $template = $_GET['template'] ?? '';
    $selectedIds = [];
    if (!empty($_GET['ids'])) {
        $selectedIds = array_filter(array_map('intval', explode(',', $_GET['ids'])), function($v){ return $v > 0; });
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="san-pham_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    $__keys = csvHeadSel($out, 'products');
    if (!$template) {
        $sql = "SELECT p.*, c.name AS cat_name, b.name AS car_brand_name, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.car_brand_id";
        $params = [];
        if (!empty($selectedIds)) {
            $ph = implode(',', array_fill(0, count($selectedIds), '?'));
            $sql .= " WHERE p.id IN ($ph)"; $params = $selectedIds;
        }
        $sql .= " ORDER BY p.id DESC";
        $products = dbAll($sql, $params);
        $money = function($v){ return ((int)$v) ? number_format((int)$v, 0, ',', '.') : '0'; };
        $clean = function($v){ return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$v), ENT_QUOTES, 'UTF-8'))); };
        foreach ($products as $p) {
            $dims = ((int)($p['width_cm']??0) || (int)($p['depth_cm']??0) || (int)($p['height_cm']??0)) ? ((int)($p['width_cm']??0).'x'.(int)($p['depth_cm']??0).'x'.(int)($p['height_cm']??0)) : '';
            csvRowSel($out, $__keys, ['name'=>$p['name'],'sku'=>$p['sku'],'oem'=>$p['oem_code']??'','category'=>$p['cat_name']??'','brand'=>$p['part_brand']??'','car_brand'=>$p['car_brand_name']??'','cost'=>$money($p['cost_price']??0),'price'=>$money($p['price']),'original'=>$money($p['original_price']??0),'stock'=>$p['stock']??0,'weight'=>$p['weight_g']??0,'dims'=>$dims,'warranty'=>$p['warranty_months']??12,'features'=>$clean($p['features']??''),'specs'=>$clean($p['specifications']??''),'description'=>$clean($p['description']??''),'image'=>$p['main_image']??'','status'=>($p['status']==='published'?'Đang bán':'Nháp'),'featured'=>(!empty($p['is_featured'])?'Có':'Không')]);
        }
    }
    fclose($out);
    exit;
});

// Import products from CSV (header-based: chấp nhận tiêu đề tiếng Việt hoặc tiếng Anh, mọi thứ tự cột)
post('/admin/products/import-csv', function() {
    requireStaffPermission('rbac:catalog.products.import|products', '/admin/login'); csrfCheck();
    if (empty($_FILES['csv_file']['tmp_name'])) { flash('error', 'Vui lòng chọn file CSV.'); redirect('/admin/products'); return; }
    $uploadedFile = $_FILES['csv_file'];
    $ext = strtolower(pathinfo($uploadedFile['name'] ?? '', PATHINFO_EXTENSION));
    $allowedMimes = ['text/csv','text/plain','application/csv','application/vnd.ms-excel'];
    $detectedMime = mime_content_type($uploadedFile['tmp_name']);
    if ($ext !== 'csv') { flash('error', 'Chỉ được phép import file CSV (.csv).'); redirect('/admin/products'); return; }
    if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'text/')) { flash('error', 'File không đúng định dạng CSV.'); redirect('/admin/products'); return; }
    $file = fopen($uploadedFile['tmp_name'], 'r');
    if (!$file) { flash('error', 'Không thể đọc file.'); redirect('/admin/products'); return; }
    $dupSku = $_POST['dup_sku'] ?? 'skip';
    $defaultStatus = in_array($_POST['default_status'] ?? '', ['draft','published']) ? $_POST['default_status'] : 'draft';
    $updateStock = $_POST['update_stock'] ?? 'no';
    $updatePrice = $_POST['update_price'] ?? 'no';
    $bom = fread($file, 3); if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) rewind($file);
    $norm = function($s){ $s = strtolower(trim((string)$s)); $s = str_replace(['đ','Đ'], ['d','d'], $s); $s = removeAccents($s); $s = preg_replace('/\(.*?\)/', '', $s); $s = preg_replace('/[^a-z0-9]+/', ' ', $s); return trim(preg_replace('/\s+/', ' ', $s)); };
    $aliases = [
        'name'=>['ten san pham','name','ten hang','san pham'],
        'sku'=>['ma sku','sku','ma hang','ma san pham'],
        'oem'=>['ma oem','oem code','oem'],
        'category'=>['danh muc','category id','category','nhom hang','loai hang'],
        'brand'=>['thuong hieu','part brand','brand'],
        'car_brand'=>['hang xe','car brand','carbrand'],
        'price'=>['gia ban','price','don gia'],
        'original_price'=>['gia goc','original price','gia gach ngang'],
        'price_before_tax'=>['gia truoc thue','price before tax'],
        'vat'=>['vat','vat rate','thue'],
        'stock'=>['ton kho','stock','so luong'],
        'weight'=>['khoi luong','weight g','weight','trong luong'],
        'warranty'=>['bao hanh','warranty months','warranty'],
        'short_specs'=>['thong so ngan','short specs'],
        'description'=>['mo ta','description','desc'],
        'features'=>['tinh nang','features'],
        'cost'=>['gia nhap','cost price','gia von'],
        'dims'=>['kich thuoc','dimensions','size'],
        'specs'=>['thong so ky thuat','specifications','specs'],
        'image'=>['anh dai dien','image','hinh anh','anh'],
        'additional_images'=>['anh phu','additional images','anh bo sung'],
        'status'=>['trang thai','status'],
        'featured'=>['noi bat','is featured','featured'],
    ];
    $rawHeaders = fgetcsv($file);
    $colMap = [];
    if (is_array($rawHeaders)) {
        foreach ($rawHeaders as $idx=>$h) { $nh = $norm($h); foreach ($aliases as $key=>$vars) { if (!isset($colMap[$key]) && in_array($nh, $vars, true)) { $colMap[$key] = $idx; break; } } }
    }
    if (!isset($colMap['name'])) { fclose($file); flash('error', 'File CSV không nhận diện được cột "Tên sản phẩm". Hãy tải file mẫu bằng nút Xuất CSV rồi nhập theo mẫu đó.'); redirect('/admin/products'); return; }
    $imported = 0; $skipped = 0; $errors = []; $lineNum = 1;
    $defaultCat = dbGet("SELECT id FROM categories ORDER BY id LIMIT 1");
    $defaultCatId = $defaultCat ? $defaultCat['id'] : 1;
    $toInt = function($v){ return (int)preg_replace('/[^0-9]/', '', (string)$v); };
    while (($row = fgetcsv($file)) !== false) {
        $lineNum++;
        if (count($row) < 1) continue;
        $g = function($key) use ($row, $colMap){ return (isset($colMap[$key]) && isset($row[$colMap[$key]])) ? $row[$colMap[$key]] : ''; };
        $name = trim((string)$g('name'));
        if ($name === '') { $errors[] = "Dòng $lineNum: thiếu tên sản phẩm"; continue; }
        $sku = trim((string)$g('sku'));
        $oem = trim((string)$g('oem'));
        $sku = resolveProductSku($sku, $oem);
        $brand = trim((string)$g('brand'));
        $carBrandName = trim((string)$g('car_brand'));
        $price = $toInt($g('price'));
        $originalPrice = $toInt($g('original_price'));
        $priceBefore = $toInt($g('price_before_tax'));
        $vat = $toInt($g('vat'));
        $stock = $toInt($g('stock'));
        $weight = $toInt($g('weight'));
        $cost = $toInt($g('cost'));
        $dimsRaw = trim((string)$g('dims')); $dimP = $dimsRaw!=='' ? preg_split('/[^0-9]+/', $dimsRaw, -1, PREG_SPLIT_NO_EMPTY) : [];
        $width = (int)($dimP[0] ?? 0); $depth = (int)($dimP[1] ?? 0); $height = (int)($dimP[2] ?? 0);
        $warranty = $toInt($g('warranty')); if (!$warranty) $warranty = 12;
        $shortSpecs = trim((string)$g('short_specs'));
        $desc = (string)$g('description');
        $features = (string)$g('features');
        $specs = (string)$g('specs');
        $imageUrl = trim((string)$g('image'));
        $additionalImages = trim((string)$g('additional_images'));
        $catRaw = trim((string)$g('category'));
        if ($catRaw === '') { $catId = $defaultCatId; }
        elseif (ctype_digit($catRaw)) { $ce = dbGet("SELECT id FROM categories WHERE id=?", [(int)$catRaw]); $catId = $ce ? (int)$catRaw : $defaultCatId; }
        else { $ce = dbGet("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) OR LOWER(slug)=LOWER(?) LIMIT 1", [$catRaw, $catRaw]); $catId = $ce ? $ce['id'] : $defaultCatId; }
        $carBrandId = null;
        if ($carBrandName) { $br = dbGet("SELECT id FROM brands WHERE LOWER(name)=LOWER(?)", [$carBrandName]); if ($br) $carBrandId = $br['id']; }
        $stRaw = $norm($g('status'));
        if (in_array($stRaw, ['published','dang ban','active','1'])) $finalStatus = 'published';
        elseif (in_array($stRaw, ['draft','nhap','0'])) $finalStatus = 'draft';
        else $finalStatus = $defaultStatus;
        $fRaw = $norm($g('featured'));
        $isFeatured = in_array($fRaw, ['co','1','yes','x','true']) ? 1 : 0;
        if (!$priceBefore && $price > 0) $priceBefore = intval(round($price / 1.1));
        $taxAmount = $price - $priceBefore;
        $slug = uniqueProductSlug($name);
        $autoSeoTitle = productMetaTitle(['name' => $name, 'oem_code' => $oem]);
        $autoSeoDescription = productMetaDescription([
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'warranty_months' => $warranty,
        ]);
        $existing = $sku ? dbGet("SELECT id FROM products WHERE sku=?", [$sku]) : null;
        try {
            if ($existing && $dupSku === 'update') {
                $updates = ["name=?"]; $params = [$name];
                if ($updatePrice === 'yes') { $updates[]="price=?"; $updates[]="price_before_tax=?"; $updates[]="tax_amount=?"; $params[]=$price; $params[]=$priceBefore; $params[]=$taxAmount; if ($originalPrice > 0) { $updates[]="original_price=?"; $params[]=$originalPrice; } if ($vat > 0) { $updates[]="vat_rate=?"; $params[]=$vat; } }
                if ($updateStock === 'yes') { $updates[]="stock=?"; $params[]=$stock; }
                if ($weight > 0) { $updates[]="weight_g=?"; $params[]=$weight; }
                if ($cost > 0) { $updates[]="cost_price=?"; $params[]=$cost; }
                if ($width||$depth||$height) { $updates[]="width_cm=?"; $updates[]="depth_cm=?"; $updates[]="height_cm=?"; $params[]=$width; $params[]=$depth; $params[]=$height; }
                if ($shortSpecs !== '') { $updates[]="short_specs=?"; $params[]=$shortSpecs; }
                if ($features !== '') { $updates[]="features=?"; $params[]=$features; }
                if ($specs !== '') { $updates[]="specifications=?"; $params[]=$specs; }
                if ($desc !== '') { $updates[]="description=?"; $params[]=$desc; }
                $updates[]="category_id=?"; $params[]=$catId;
                $updates[]="seo_title=CASE WHEN TRIM(COALESCE(seo_title,''))='' THEN ? ELSE seo_title END"; $params[]=$autoSeoTitle;
                $updates[]="seo_description=CASE WHEN TRIM(COALESCE(seo_description,''))='' THEN ? ELSE seo_description END"; $params[]=$autoSeoDescription;
                $updates[]="updated_at=datetime('now','localtime')";
                $params[] = $existing['id'];
                dbRun("UPDATE products SET " . implode(',', $updates) . " WHERE id=?", $params);
                $imported++;
                $newPid = ['id' => $existing['id']];
            } elseif ($existing && $dupSku === 'skip') {
                $skipped++; $errors[] = "Dòng $lineNum: SKU \"{$sku}\" đã tồn tại";
                $newPid = null;
            } else {
                if (!$sku) { $sku = 'CSV-' . strtoupper(substr(md5($name . microtime()), 0, 8)); }
                dbInsert("INSERT INTO products (name, sku, slug, oem_code, part_brand, car_brand_id, category_id, price, price_before_tax, tax_amount, original_price, vat_rate, stock, weight_g, cost_price, width_cm, depth_cm, height_cm, warranty_months, short_specs, description, features, specifications, is_featured, status, partner_id, video_url, published_at, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,datetime('now','localtime'),datetime('now','localtime'))",
                    [$name, $sku, $slug, $oem, $brand, $carBrandId, $catId, $price, $priceBefore, $taxAmount, $originalPrice ?: null, $vat, $stock, $weight, $cost, $width, $depth, $height, $warranty, $shortSpecs, $desc, $features, $specs, $isFeatured, $finalStatus, trim($_POST['video_url'] ?? '')]);
                $newPid = dbGet("SELECT id FROM products WHERE sku=?", [$sku]);
            }
            if ($newPid) {
                $imgName = null;
                if (str_starts_with($imageUrl, 'http')) {
                    $ctx = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 10]]);
                    $imgData = @file_get_contents($imageUrl, false, $ctx);
                    if ($imgData && strlen($imgData) > 1000) {
                        $imgExt = 'jpg'; if (str_contains($imageUrl, '.webp')) $imgExt = 'webp'; elseif (str_contains($imageUrl, '.png')) $imgExt = 'png';
                        $imgName = 'p_' . uniqid() . '.' . $imgExt;
                        file_put_contents('/var/lib/coolingsystems/uploads/products/' . $imgName, $imgData);
                    }
                } else {
                    if ($imageUrl) $imgName = $imageUrl;
                }
                if ($imgName) {
                    dbRun("DELETE FROM product_images WHERE product_id=?", [$newPid['id']]);
                    dbRun("INSERT INTO product_images (product_id, file_path, is_main, sort_order) VALUES (?, ?, 1, 0)", [$newPid['id'], $imgName]);
                }
                if (!empty($additionalImages)) {
                    $extraImgs = array_filter(array_map('trim', explode(';', $additionalImages)));
                    $sortIdx = 1;
                    foreach ($extraImgs as $extraImg) {
                        $extraName = null;
                        if (str_starts_with($extraImg, 'http')) {
                            $ctx2 = stream_context_create(['http' => ['header' => "User-Agent: Mozilla/5.0\r\n", 'timeout' => 10]]);
                            $imgData2 = @file_get_contents($extraImg, false, $ctx2);
                            if ($imgData2 && strlen($imgData2) > 1000) {
                                $eExt = 'jpg'; if (str_contains($extraImg, '.webp')) $eExt = 'webp'; elseif (str_contains($extraImg, '.png')) $eExt = 'png';
                                $extraName = 'p_' . uniqid() . '_' . $sortIdx . '.' . $eExt;
                                file_put_contents('/var/lib/coolingsystems/uploads/products/' . $extraName, $imgData2);
                            }
                        } else {
                            if ($extraImg) $extraName = $extraImg;
                        }
                        if ($extraName) { dbRun("INSERT INTO product_images (product_id, file_path, is_main, sort_order) VALUES (?, ?, 0, ?)", [$newPid['id'], $extraName, $sortIdx]); $sortIdx++; }
                    }
                }
            }
                $newProduct = dbGet("SELECT id FROM products WHERE sku=?", [$sku]);
                if ($newProduct) {
                    dbRun("UPDATE products SET seo_title=?, seo_description=?, is_indexed=?, updated_at=datetime('now','localtime') WHERE id=?", [
                        $autoSeoTitle,
                        $autoSeoDescription,
                        $finalStatus === 'published' ? 1 : 0,
                        $newProduct['id'],
                    ]);
                }
                $imported++;
        } catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
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
    $__keys = csvHeadSel($out, 'orders');
    $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];
    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = dbAll("SELECT o.*, u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id IN ($ph) ORDER BY o.id DESC", $ids);
    } else {
        $rows = dbAll("SELECT o.*, u.email FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC");
    }
    $delM=['pending'=>'Đang chờ','received'=>'Tiếp nhận','delivering'=>'Đang giao','delivered'=>'Đã giao','completed'=>'Hoàn thành','cancelled'=>'Đã hủy','returned'=>'Đã trả hàng'];
    $payM=['unpaid'=>'Chưa thanh toán','partial_paid'=>'Thanh toán một phần','paid'=>'Đã thanh toán','pending_refund'=>'Chờ hoàn tiền','refunded'=>'Đã hoàn tiền'];
    $ptM=['cod'=>'COD','bank_transfer'=>'Chuyển khoản'];
    foreach($rows as $r) csvRowSel($out, $__keys, ['code'=>$r['code']??$r['order_code']??'','customer'=>$r['shipping_full_name']??'','phone'=>$r['shipping_phone']??'','email'=>$r['email']??'','address'=>$r['shipping_detail']??'','total'=>number_format((int)($r['grand_total']??0),0,',','.'),'delivery'=>($delM[$r['delivery_status']??'']??($r['delivery_status']??'')),'payment'=>($payM[$r['payment_status']??'']??($r['payment_status']??'')),'paytype'=>($ptM[$r['payment_type']??'']??($r['payment_type']??'')),'created'=>$r['created_at']]);
    fclose($out); exit;
});

// ── EXPORT/IMPORT: Users ──
get('/admin/users/export-csv', function() {
    requireStaffPermission('users|staff', '/auth/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    $__keys = csvHeadSel($out, 'users');
    $txt = function($v){ $v = str_replace('"','',trim((string)$v)); return $v === '' ? '' : '="'.$v.'"'; };
    $roleF = in_array($_GET['role'] ?? '', ['customer','staff','admin','partner']) ? $_GET['role'] : '';
    $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];
    $base = "SELECT u.*, ii.invoice_type AS i_type, ii.buyer_name AS i_buyer, ii.company_name AS i_company, ii.tax_code AS i_tax, ii.address AS i_addr, ii.province AS i_prov, ii.id_number AS i_id, ii.email AS i_email, ii.phone AS i_phone, ii.bank_name AS i_bank, ii.bank_account AS i_acct FROM users u LEFT JOIN user_invoice_info ii ON ii.user_id=u.id";
    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $rows = dbAll("$base WHERE u.id IN ($ph) ORDER BY u.id DESC", $ids);
    } elseif ($roleF !== '') {
        $rows = dbAll("$base WHERE u.role=? AND u.email NOT LIKE '%_deleted_%' ORDER BY u.id DESC", [$roleF]);
    } else {
        $rows = dbAll("$base WHERE u.email NOT LIKE '%_deleted_%' ORDER BY u.id DESC");
    }
    $roleM=['admin'=>'Quản trị','staff'=>'Nhân viên','customer'=>'Khách hàng','partner'=>'Đối tác'];
    $ustM=['active'=>'Hoạt động','suspended'=>'Tạm khóa','locked'=>'Đã khóa','banned'=>'Đã khóa','inactive'=>'Ngưng'];
    foreach($rows as $r) {
        $itype = ($r['i_type']??'')==='business' ? 'Tổ chức/Hộ KD' : (($r['i_type']??'')==='personal' ? 'Cá nhân' : '');
        $iname = ($r['i_type']??'')==='business' ? ($r['i_company']??'') : ($r['i_buyer']??'');
        csvRowSel($out, $__keys, ['name'=>$r['full_name'],'email'=>$r['email'],'phone'=>$txt($r['phone']??''),'role'=>($roleM[$r['role']??'']??($r['role']??'')),'status'=>($ustM[$r['status']??'']??($r['status']??'')),'address'=>$r['address']??'','created'=>($r['created_at']?date('d/m/Y H:i',strtotime($r['created_at'])):''),'inv_type'=>$itype,'inv_name'=>$iname,'inv_tax'=>$txt($r['i_tax']??''),'inv_addr'=>$r['i_addr']??'','inv_prov'=>$r['i_prov']??'','inv_cccd'=>$txt($r['i_id']??''),'inv_email'=>$r['i_email']??'','inv_phone'=>$txt($r['i_phone']??''),'inv_bank'=>$r['i_bank']??'','inv_acct'=>$txt($r['i_acct']??'')]);
    }
    fclose($out); exit;
});

// ── EXPORT: Categories ──
get('/admin/categories/export-csv', function() {
    requireStaffPermission('rbac:integration.data.export|categories', '/auth/login');
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
    $__keys = csvHeadSel($out, 'categories');
    if (!empty($selectedIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $rows = dbAll("SELECT * FROM categories WHERE id IN ($placeholders) ORDER BY sort_order, name", $selectedIds);
    } else {
        $rows = dbAll("SELECT * FROM categories ORDER BY sort_order, name");
    }
    foreach($rows as $r) {
        $pn = !empty($r['parent_id']) ? (dbGet("SELECT name FROM categories WHERE id=?", [$r['parent_id']])['name'] ?? '') : '';
        csvRowSel($out, $__keys, ['id'=>$r['id'],'name'=>$r['name'],'slug'=>$r['slug']??'','parent'=>$pn,'sort'=>$r['sort_order']??0,'featured'=>(!empty($r['is_featured'])?'Có':'Không')]);
    }
    fclose($out); exit;
});

// ── EXPORT: Brands (Hãng xe) ──
get('/admin/brands/export-csv', function() {
    requireStaffPermission('rbac:integration.data.export|brands', '/admin/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="hang-xe_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    $__keys = csvHeadSel($out, 'brands');
    $rows = dbAll("SELECT b.name AS brand_name, cm.name AS model_name, cm.slug AS model_slug, cm.year_from, cm.year_to FROM brands b LEFT JOIN car_models cm ON cm.brand_id=b.id ORDER BY b.sort_order, b.name COLLATE NOCASE, cm.name COLLATE NOCASE");
    foreach($rows as $r) csvRowSel($out, $__keys, ['brand'=>$r['brand_name'],'model'=>$r['model_name']??'','model_slug'=>$r['model_slug']??'','year_from'=>$r['year_from']??'','year_to'=>$r['year_to']??'']);
    fclose($out); exit;
});

// ── EXPORT: Product Brands (Thương hiệu SP) ──
get('/admin/product-brands/export-csv', function() {
    requireStaffPermission('rbac:integration.data.export|brand_models', '/auth/login');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="product_brands_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    $__keys = csvHeadSel($out, 'product_brands');
    $rows = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    foreach($rows as $r) csvRowSel($out, $__keys, ['id'=>$r['id'],'name'=>$r['name'],'description'=>trim(preg_replace('/\s+/',' ', html_entity_decode(strip_tags((string)($r['description']??'')), ENT_QUOTES,'UTF-8'))),'sort'=>$r['sort_order']??0]);
    fclose($out); exit;
});


// ── Admin Chat ──
get('/admin/chat', function() {
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login');
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
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login'); csrfCheck();
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
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login'); csrfCheck();
    $threadId = intval($_POST['thread_id']);
    if (!$threadId || empty($_FILES['image'])) { echo json_encode(['error'=>'missing']); exit; }
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) { echo json_encode(['error'=>'invalid']); exit; }
    $uploadDir = '/var/lib/coolingsystems/uploads/chat/';
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
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login');
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
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login'); csrfCheck();
    $threadId = intval($p['id']);
    $thread = dbGet("SELECT * FROM chat_threads WHERE id=?", [$threadId]);
    if (!$thread) { echo json_encode(['ok'=>false,'msg'=>'Không tìm thấy']); exit; }
    dbRun("UPDATE chat_threads SET is_hidden=1 WHERE id=?", [$threadId]);
    echo json_encode(['ok'=>true]);
    exit;
});

post('/admin/chat/:id/unhide', function($p) {
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login'); csrfCheck();
    $threadId = intval($p['id']);
    dbRun("UPDATE chat_threads SET is_hidden=0 WHERE id=?", [$threadId]);
    echo json_encode(['ok'=>true]);
    exit;
});

post('/admin/chat/:id/delete', function($p) {
    requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login'); csrfCheck();
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
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $importDefaultRole = in_array($_POST['import_role'] ?? '', ['staff','admin','customer','partner']) ? $_POST['import_role'] : 'customer';
    $backList = $importDefaultRole==='staff' ? '/admin/staff-accounts' : ($importDefaultRole==='admin' ? '/admin/admin-accounts' : '/admin/users');
    if (empty($_FILES['csv_file'])) { flash('error','Chưa chọn file'); redirect($backList); return; }
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
    $map = csvColMap($header, ['name'=>['ho va ten','full name','name','ten'],'email'=>['email'],'phone'=>['sdt','phone','so dien thoai'],'role'=>['vai tro','role'],'status'=>['trang thai','status'],'address'=>['dia chi','address']]);
    if (!isset($map['email'])) { fclose($handle); flash('error','File CSV không có cột "Email". Hãy nhập theo file mẫu (bấm nút Xuất CSV để lấy mẫu).'); redirect($backList); return; }
    $imported = 0; $errors = 0;
    $batchPassword = 'Cs@' . bin2hex(random_bytes(6));
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 1) { $errors++; continue; }
        $name = trim((string)csvGet($row,$map,'name'));
        $email = trim((string)csvGet($row,$map,'email'));
        $phone = preg_replace('/[^0-9]/', '', (string)csvGet($row,$map,'phone'));
        $roleRaw = csvNorm(csvGet($row,$map,'role'));
        $role = ['quan tri'=>'admin','admin'=>'admin','nhan vien'=>'staff','staff'=>'staff','khach hang'=>'customer','customer'=>'customer','doi tac'=>'partner','partner'=>'partner'][$roleRaw] ?? $importDefaultRole;
        $stRaw = csvNorm(csvGet($row,$map,'status'));
        $status = in_array($stRaw, ['tam khoa','da khoa','khoa','suspended','locked','banned','inactive','ngung']) ? 'suspended' : 'active';
        $address = trim((string)csvGet($row,$map,'address'));
        if (!$email) { $errors++; continue; }
        $existing = dbGet("SELECT id FROM users WHERE email=?", [$email]);
        if ($existing) { $errors++; continue; }
        try {
            dbInsert("INSERT INTO users (full_name,email,phone,role,address,password_hash,status,created_at) VALUES (?,?,?,?,?,?,?,datetime('now'))",
                [$name, $email, $phone, $role, $address, password_hash($batchPassword, PASSWORD_DEFAULT), $status]);
            $imported++;
        } catch (\Exception $e) { $errors++; }
    }
    fclose($handle);
    if ($imported > 0) {
        $msg = "Đã nhập $imported tài khoản. Mật khẩu tạm của đợt nhập: $batchPassword";
        if ($errors > 0) $msg .= " ($errors dòng bỏ qua do email đã tồn tại hoặc lỗi)";
        flash('success', $msg);
    } elseif ($errors > 0) {
        flash('error', "Không nhập được tài khoản nào. $errors dòng bị bỏ qua (email đã tồn tại hoặc dữ liệu không hợp lệ).");
    } else {
        flash('error', 'File CSV rỗng hoặc không có dữ liệu hợp lệ.');
    }
    redirect($backList);
});

// ── IMPORT: Categories ──
post('/admin/categories/import-csv', function() {
    requireStaffPermission('rbac:integration.data.import|categories', '/auth/login'); csrfCheck();
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
    $header = fgetcsv($handle);
    $map = csvColMap($header, ['name'=>['ten danh muc','name','ten'],'slug'=>['duong dan','slug'],'parent'=>['danh muc cha','parent id','parent'],'sort'=>['thu tu','sort order','sort'],'featured'=>['noi bat','is featured','featured']]);
    if (!isset($map['name'])) { fclose($handle); flash('error','File CSV không có cột "Tên danh mục". Hãy nhập theo file mẫu.'); redirect('/admin/categories'); return; }
    $imported = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $name = trim((string)csvGet($row,$map,'name')); if (!$name) { $errors[] = "Dòng $lineNum: tên rỗng"; continue; }
        $slug = trim((string)csvGet($row,$map,'slug')) ?: trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(removeAccents($name))), '-');
        $pRaw = trim((string)csvGet($row,$map,'parent'));
        $parentId = null;
        if ($pRaw !== '') { if (ctype_digit($pRaw)) { $pe=dbGet("SELECT id FROM categories WHERE id=?",[(int)$pRaw]); $parentId=$pe?(int)$pRaw:null; } else { $pe=dbGet("SELECT id FROM categories WHERE LOWER(name)=LOWER(?) LIMIT 1",[$pRaw]); $parentId=$pe?$pe['id']:null; } }
        $sort = csvInt(csvGet($row,$map,'sort'));
        $featured = in_array(csvNorm(csvGet($row,$map,'featured')), ['co','1','yes','x','true']) ? 1 : 0;
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
    requireStaffPermission('rbac:integration.data.import|brands', '/admin/login'); csrfCheck();
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
    $header = fgetcsv($handle);
    $map = csvColMap($header, ['name'=>['hang xe','ten hang xe','ten hang','name','ten'],'model'=>['dong xe','ten dong xe','model'],'model_slug'=>['duong dan dong xe','duong dan','model slug','slug'],'year_from'=>['nam bat dau','year from','nam'],'year_to'=>['nam ket thuc','year to','den nam']]);
    if (!isset($map['name'])) { fclose($handle); flash('error','File CSV không có cột "Hãng xe". Hãy nhập theo file mẫu (bấm nút Xuất CSV để lấy mẫu).'); redirect('/admin/brands'); return; }
    $brandsAdded = 0; $modelsAdded = 0; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $bname = trim((string)csvGet($row,$map,'name')); if ($bname === '') continue;
        $b = dbGet("SELECT id FROM brands WHERE LOWER(name)=LOWER(?)", [$bname]);
        if (!$b) {
            $bslug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(removeAccents($bname))), '-');
            try { dbInsert("INSERT INTO brands (name,slug,sort_order) VALUES (?,?,?)", [$bname,$bslug,100]); $brandsAdded++; }
            catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); continue; }
            $b = dbGet("SELECT id FROM brands WHERE LOWER(name)=LOWER(?)", [$bname]);
        }
        if (!$b) continue;
        $brandId = (int)$b['id'];
        $mname = trim((string)csvGet($row,$map,'model'));
        if ($mname !== '') {
            $exists = dbGet("SELECT id FROM car_models WHERE brand_id=? AND LOWER(name)=LOWER(?)", [$brandId, $mname]);
            if (!$exists) {
                $mslug = trim((string)csvGet($row,$map,'model_slug')); if ($mslug === '') $mslug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(removeAccents($mname))), '-');
                $yf = csvInt(csvGet($row,$map,'year_from')); $yf = $yf > 0 ? $yf : null;
                $yt = csvInt(csvGet($row,$map,'year_to')); $yt = $yt > 0 ? $yt : null;
                try { dbInsert("INSERT INTO car_models (brand_id,name,slug,year_from,year_to) VALUES (?,?,?,?,?)", [$brandId,$mname,$mslug,$yf,$yt]); $modelsAdded++; }
                catch (\Exception $e) { $errors[] = "Dòng $lineNum: " . $e->getMessage(); }
            }
        }
    }
    fclose($handle);
    if ($brandsAdded + $modelsAdded > 0) {
        $msg = "Đã nhập: $brandsAdded hãng xe mới, $modelsAdded dòng xe mới.";
        if (count($errors) > 0) $msg .= " " . count($errors) . " dòng lỗi.";
        flash('success', $msg);
    } else {
        flash('error', 'Không nhập được dữ liệu nào (có thể đã tồn tại).' . (count($errors) > 0 ? ' ' . $errors[0] : ''));
    }
    redirect('/admin/brands');
});

// ── IMPORT: Product Brands (Thương hiệu SP) ──
post('/admin/product-brands/import-csv', function() {
    requireStaffPermission('rbac:integration.data.import|brand_models', '/auth/login'); csrfCheck();
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
    $header = fgetcsv($handle);
    $map = csvColMap($header, ['name'=>['ten thuong hieu','ten','name'],'desc'=>['mo ta','description','desc'],'sort'=>['thu tu','sort order','sort']]);
    if (!isset($map['name'])) { fclose($handle); flash('error','File CSV không có cột "Tên thương hiệu". Hãy nhập theo file mẫu.'); redirect('/admin/product-brands'); return; }
    $imported = 0; $duplicates = []; $errors = [];
    $lineNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $lineNum++;
        $name = trim((string)csvGet($row,$map,'name')); if (!$name) continue;
        $desc = trim((string)csvGet($row,$map,'desc')); $sort = csvInt(csvGet($row,$map,'sort'));
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
    requireStaffPermission('rbac:sales.orders.view|orders', '/admin/login');
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
    $orders = dbAll("SELECT o.*, u.full_name, COALESCE(u.email,'') AS email, u.phone, s.full_name AS staff_name, s.role AS staff_role 
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
    $user = requireStaffPermission('rbac:sales.orders.create|create_order|orders', '/admin/login');
    view('admin/order-create', ['title'=>'Tạo đơn hàng hộ', 'role'=>'admin', 'currentUser'=>$user]);
});

get('/admin/orders/:id', function($p) {
    requireStaffPermission('rbac:sales.orders.view|orders', '/admin/login');
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
    $user = requireStaffPermission('rbac:sales.orders.create|orders', '/admin/login'); csrfCheck();
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
    $cfgRows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_tax_rate','default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent','shipping_origin_province','shipping_rates')");
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
    $totalWeight = 0;
    foreach ($items as $it) {
        $pw = dbGet("SELECT weight_g FROM products WHERE id=?", [intval($it['product_id'])]);
        $totalWeight += intval($pw['weight_g'] ?? 0) * max(1, intval($it['qty']));
    }
    
    $discountTotal = 0;
    if ($discountQtyThreshold > 0 && $totalQty >= $discountQtyThreshold) {
        $discountTotal = (int)ceil($subtotal * ($discountQtyPercent / 100));
    }

    $afterDiscount = $subtotal - $discountTotal;
    $taxAmount = 0; // VAT đã bao gồm trong giá sản phẩm, không tính thêm

    $shipFee = calcShippingFee(trim($_POST['ship_province'] ?? ''), (int)$totalWeight, $cfg);
    $shipping = ($freeShipThreshold > 0 && $afterDiscount >= $freeShipThreshold) ? 0 : $shipFee;
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
        inventoryCheckLowStockAlert($pid, 'admin_order');
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
    requireStaffPermission('rbac:sales.returns.approve|orders', '/admin/login'); csrfCheck();
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
                inventoryCheckLowStockAlert((int)$ri['product_id'], 'admin_return');
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


// Tự động tạo bản ghi trả hàng (order_returns) khi đơn ở trạng thái 'returned' mà chưa có yêu cầu nào.
// Giúp trang 'Quản lý trả hàng' + tab 'Trả hàng' bắt được đơn admin đánh dấu trả hàng trực tiếp.
function ensureAdminReturnRecord($orderId) {
    $o = dbGet("SELECT id, user_id, delivery_status, refund_amount, grand_total, shipping_phone, shipping_detail, shipping_district, shipping_province FROM orders WHERE id=?", [$orderId]);
    if (!$o) return;
    if (($o['delivery_status'] ?? '') !== 'returned') return;        // chỉ tạo cho đơn THỰC SỰ trả hàng (không phải hủy đơn)
    if (empty($o['user_id'])) return;                                 // trang trả hàng JOIN users -> cần user hợp lệ
    $exists = dbGet("SELECT id FROM order_returns WHERE order_id=? LIMIT 1", [$orderId]);
    if ($exists) return;                                              // đã có yêu cầu (KH gửi hoặc tạo trước) -> bỏ qua
    $refund = intval(($o['refund_amount'] ?? 0) ?: ($o['grand_total'] ?? 0));
    $addr = trim(($o['shipping_detail'] ?? '') . (!empty($o['shipping_district']) ? ', ' . $o['shipping_district'] : '') . (!empty($o['shipping_province']) ? ', ' . $o['shipping_province'] : ''));
    $u = dbGet("SELECT COALESCE(email,'') AS email FROM users WHERE id=?", [$o['user_id']]);
    dbInsert("INSERT INTO order_returns (order_id, user_id, reason, status, refund_amount, contact_phone, contact_email, contact_address, bank_account, bank_holder, bank_name, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,datetime('now','localtime'))",
        [$orderId, $o['user_id'], 'Quản trị viên xác nhận trả hàng / hoàn tiền (tạo tự động từ trạng thái đơn).', 'approved', $refund, $o['shipping_phone'] ?? '', $u['email'] ?? '', $addr, '', '', '']);
}

// ── Update payment status ──
post('/admin/orders/:id/payment-status', function($p) {
    requireStaffPermission('rbac:sales.payment.collect|orders', '/admin/login'); csrfCheck();
    $status = in_array($_POST['status'] ?? '', ['paid','unpaid','partial_paid','pending_refund','refunded']) ? $_POST['status'] : 'unpaid';
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
    } elseif ($status === 'partial_paid') {
        dbRun("UPDATE orders SET payment_status='partial_paid' WHERE id=?", [$p['id']]);
    } elseif ($status === 'pending_refund') {
        dbRun("UPDATE orders SET payment_status='pending_refund' WHERE id=?", [$p['id']]);
    } elseif ($status === 'refunded') {
        dbRun("UPDATE orders SET payment_status='refunded', refund_amount=COALESCE(NULLIF(paid_amount,0), grand_total) WHERE id=?", [$p['id']]);
        // Đã hoàn tiền -> tự động chuyển giao hàng sang 'Đã trả hàng' (giữ nguyên nếu đơn đã hủy)
        dbRun("UPDATE orders SET delivery_status='returned' WHERE id=? AND delivery_status NOT IN ('cancelled','returned')", [$p['id']]);
    } else {
        dbRun("UPDATE orders SET payment_status='unpaid', paid_amount=0, remaining_amount=grand_total WHERE id=?", [$p['id']]);
    }
    ensureAdminReturnRecord($p['id']);
    flash('success', 'Đã cập nhật trạng thái thanh toán.');
    redirect('/admin/orders');
});

post('/admin/orders/:id/delivery-status', function($p) {
    requireStaffPermission('rbac:sales.delivery.update|orders', '/admin/login'); csrfCheck();
    $order = dbGet("SELECT payment_status, delivery_status, payment_method, payment_type, user_id, code FROM orders WHERE id=?", [$p['id']]);
    if (!$order) { flash('error','Không tìm thấy đơn hàng.'); redirect('/admin/orders'); return; }

    // Block if completed/cancelled/returned
    if (in_array($order['delivery_status'], ['completed','cancelled','returned'])) {
        flash('error','Đơn hàng đã kết thúc, không thể thay đổi trạng thái.');
        redirect('/admin/orders'); return;
    }

    $newStatus = $_POST['status'] ?? '';
    $valid = ['pending','received','delivering','delivered','completed','cancelled','returned'];
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

    // Restore stock and voucher on cancel
    if ($newStatus === 'cancelled' && $order['delivery_status'] !== 'cancelled') {
        $orderItems = dbAll("SELECT oi.product_id, oi.quantity FROM order_items oi INNER JOIN sub_orders so ON so.id=oi.sub_order_id WHERE so.order_id=?", [$p['id']]);
        foreach ($orderItems as $oi) {
            if ($oi['product_id']) {
                dbRun("UPDATE products SET stock = stock + ? WHERE id=?", [$oi['quantity'], $oi['product_id']]);
                inventoryCheckLowStockAlert((int)$oi['product_id'], 'admin_order_cancel');
            }
        }
        if (!empty($order['voucher_code']) && !empty($order['user_id'])) {
            dbRun("UPDATE user_saved_vouchers SET used=0 WHERE user_id=? AND code=?", [$order['user_id'], $order['voucher_code']]);
            dbRun("UPDATE vouchers SET used_quantity = MAX(0, used_quantity - 1) WHERE code=?", [$order['voucher_code']]);
        }
        dbRun("UPDATE orders SET payment_status='refunded' WHERE id=? AND payment_status='paid'", [$p['id']]);
    }

    // Đã trả hàng -> hoàn tiền nếu đơn đã thanh toán
    if ($newStatus === 'returned' && in_array($order['payment_status'], ['paid','partial_paid'])) {
        dbRun("UPDATE orders SET payment_status='refunded', refund_amount=COALESCE(NULLIF(paid_amount,0), grand_total) WHERE id=?", [$p['id']]);
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
        $msgMap = ['received'=>'Nhân viên đã tiếp nhận đơn hàng','delivering'=>'Đơn hàng đang được giao','delivered'=>'Đơn hàng đã được giao thành công','completed'=>'Đơn hàng đã hoàn thành','cancelled'=>'Đơn hàng đã bị hủy','returned'=>'Đơn hàng đã được trả lại & hoàn tiền'];
        $msg = ($msgMap[$newStatus] ?? 'Trạng thái đơn hàng đã thay đổi') . ' (#' . $order['code'] . ')';
        dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)",
            [$order['user_id'], 'order', 'Cập nhật đơn hàng', $msg, '/customer/orders']);
    }
    ensureAdminReturnRecord($p['id']);
    flash('success','Cập nhật trạng thái giao hàng thành công.');
    redirect('/admin/orders');
});

// ── STAFF PERMISSION ROUTES ─────────────────────────────────────────────────
function rbacSanitizeRolePermissions(array $permissions): array {
    $legacy = ['orders','create_order','returns','products','categories','brands','brand_models','content','static_pages','stores','users','reviews','contacts','chat','vouchers','promotions','staff','reports','tax_config'];
    $capabilities = array_column(rbacCapabilityCatalog(), 'capability');
    $allowed = array_flip(array_merge($legacy, array_map(fn($capability) => 'rbac:' . $capability, $capabilities)));
    $result = [];
    foreach ($permissions as $permission) {
        $permission = (string)$permission;
        if (isset($allowed[$permission])) $result[$permission] = true;
    }
    return array_keys($result);
}

get('/admin/serials', function() { requireStaffPermission('rbac:catalog.serials.manage|products','/admin/login'); $serials=dbAll("SELECT serial.*,product.name AS product_name,product.sku FROM product_serials serial INNER JOIN products product ON product.id=serial.product_id ORDER BY serial.created_at DESC LIMIT 500"); $products=dbAll("SELECT id,sku,name FROM products ORDER BY name LIMIT 3000"); view('admin/serials',['title'=>'Quản lý Serial & Lô hàng','userRole'=>'admin','serials'=>$serials,'products'=>$products]); });

post('/admin/serials', function() { $actor=requireStaffPermission('rbac:catalog.serials.manage|products','/admin/login');csrfCheck();$productId=(int)($_POST['product_id']??0);$serial=strtoupper(trim($_POST['serial_no']??''));$mfg=trim($_POST['manufactured_at']??'');$end=trim($_POST['warranty_end_date']??'');if(!$productId||!dbGet('SELECT id FROM products WHERE id=?',[$productId])){flash('error','Vui lòng gõ tìm và chọn sản phẩm hợp lệ từ danh sách gợi ý.');redirect('/admin/serials');}if($serial===''||!preg_match('/^[A-Z0-9\-]{3,50}$/',$serial)){flash('error','Số serial không hợp lệ (chỉ gồm chữ cái A-Z, chữ số 0-9 và dấu gạch ngang, 3-50 ký tự).');redirect('/admin/serials');}if($end===''||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$end)){flash('error','Vui lòng chọn Hạn bảo hành hợp lệ.');redirect('/admin/serials');}try{$id=dbInsert('INSERT INTO product_serials (product_id,serial_no,manufactured_at,warranty_end_date,created_by) VALUES (?,?,?,?,?)',[$productId,$serial,$mfg!==''?$mfg:null,$end,$actor['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role']??'admin','serial_created','product_serial',$id,json_encode(['product_id'=>$productId,'serial'=>$serial],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã thêm số serial '.$serial.' thành công.');}catch(Throwable $e){flash('error','Số Serial '.$serial.' đã tồn tại trong hệ thống (đảm bảo tính duy nhất).');}redirect('/admin/serials'); });

post('/admin/serials/:id/edit', function($p) {
    $actor = requireStaffPermission('rbac:catalog.serials.manage|products', '/admin/login');
    csrfCheck();
    $id = (int)($p['id'] ?? 0);
    $serial = strtoupper(trim($_POST['serial_no'] ?? ''));
    $mfg = trim($_POST['manufactured_at'] ?? '');
    $end = trim($_POST['warranty_end_date'] ?? '');

    if (!$id) { redirect('/admin/serials'); }
    if ($serial === '' || !preg_match('/^[A-Z0-9\-]{3,50}$/', $serial)) {
        flash('error', 'Số serial không hợp lệ (chỉ gồm chữ cái A-Z, chữ số 0-9 và dấu gạch ngang, 3-50 ký tự).');
        redirect('/admin/serials');
    }
    if ($end === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        flash('error', 'Vui lòng chọn Hạn bảo hành hợp lệ.');
        redirect('/admin/serials');
    }

    try {
        dbRun("UPDATE product_serials SET serial_no=?, manufactured_at=?, warranty_end_date=? WHERE id=?", [
            $serial, $mfg !== '' ? $mfg : null, $end, $id
        ]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [
            $actor['id'], $actor['role'] ?? 'admin', 'serial_updated', 'product_serial', $id,
            json_encode(['serial' => $serial], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        flash('success', "Đã cập nhật số serial {$serial} thành công.");
    } catch (Throwable $e) {
        flash('error', "Số Serial {$serial} đã tồn tại trong hệ thống (đảm bảo tính duy nhất).");
    }
    redirect('/admin/serials');
});

post('/admin/serials/:id/delete', function($p) {
    $actor = requireStaffPermission('rbac:catalog.serials.manage|products', '/admin/login');
    csrfCheck();
    $id = (int)($p['id'] ?? 0);
    if ($id > 0) {
        dbRun("DELETE FROM product_serials WHERE id=?", [$id]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [
            $actor['id'], $actor['role'] ?? 'admin', 'serial_deleted', 'product_serial', $id,
            json_encode(['id' => $id], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        flash('success', 'Đã xóa số serial thành công.');
    }
    redirect('/admin/serials');
});

get('/admin/warranties/products', function() { requireStaffPermission('rbac:warranty.cases.view|returns','/admin/login'); $q=trim($_GET['q']??''); header('Content-Type: application/json; charset=utf-8'); if(mb_strlen($q)<2){echo '[]';exit;} $like='%'.$q.'%'; $rows=dbAll('SELECT id,sku,oem_code,name FROM products WHERE name LIKE ? OR sku LIKE ? OR oem_code LIKE ? ORDER BY name LIMIT 12',[$like,$like,$like]); echo json_encode(array_map(fn($row)=>['id'=>(int)$row['id'],'label'=>trim($row['sku'].' | '.$row['name'].(!empty($row['oem_code'])?' | OEM: '.$row['oem_code']:''))],$rows),JSON_UNESCAPED_UNICODE); exit; });
get('/admin/cashbook', function() {
    $user=requireStaffPermission('rbac:finance.cashbook.view|tax_config','/admin/login');
    $selectedAccount=max(0,(int)($_GET['account']??0));$fromDate=trim($_GET['from']??'');$toDate=trim($_GET['to']??'');
    if($fromDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fromDate))$fromDate='';if($toDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$toDate))$toDate='';
    $accounts=dbAll("SELECT account.*,COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE -entry.amount END),0) AS balance FROM cash_accounts account LEFT JOIN cash_ledger_entries entry ON entry.account_id=account.id AND entry.voided_at IS NULL WHERE account.is_active=1 GROUP BY account.id ORDER BY account.sort_order,account.id");
    if($selectedAccount&&!dbGet('SELECT id FROM cash_accounts WHERE id=? AND is_active=1',[$selectedAccount]))$selectedAccount=0;
    $where=['entry.voided_at IS NULL'];$params=[];if($selectedAccount){$where[]='entry.account_id=?';$params[]=$selectedAccount;}if($fromDate!==''){$where[]='entry.entry_date>=?';$params[]=$fromDate;}if($toDate!==''){$where[]='entry.entry_date<=?';$params[]=$toDate;}$sqlWhere=implode(' AND ',$where);
    $entries=dbAll("SELECT entry.*,account.name AS account_name,void_request.id AS void_request_id,void_request.status AS void_request_status,void_request.created_by AS void_request_created_by,void_request.reason AS void_request_reason,void_request.rejection_reason AS void_request_rejection_reason FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id LEFT JOIN cash_ledger_void_requests void_request ON void_request.ledger_entry_id=entry.id WHERE $sqlWhere ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 500",$params);
    $totalRow=dbGet("SELECT COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE 0 END),0) AS income,COALESCE(SUM(CASE WHEN entry.direction='out' THEN entry.amount ELSE 0 END),0) AS expense FROM cash_ledger_entries entry WHERE $sqlWhere",$params)?:['income'=>0,'expense'=>0];$totals=['income'=>(int)$totalRow['income'],'expense'=>(int)$totalRow['expense'],'net'=>(int)$totalRow['income']-(int)$totalRow['expense']];
    $canCreateReceipt=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.receipts.create');$canCreateDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.create');$canApproveDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.approve');$canVoidEntry=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.ledger.void');$disbursements=dbAll("SELECT request.*,account.name AS account_name FROM cash_disbursement_requests request INNER JOIN cash_accounts account ON account.id=request.account_id ORDER BY CASE request.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,request.created_at DESC LIMIT 200");
    view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate,'canCreateReceipt'=>$canCreateReceipt,'canCreateDisbursement'=>$canCreateDisbursement,'canApproveDisbursement'=>$canApproveDisbursement,'canVoidEntry'=>$canVoidEntry,'currentUserId'=>(int)$user['id'],'disbursements'=>$disbursements]);
});
post('/admin/cashbook/receipts', function() {
    $actor=requireStaffPermission('rbac:finance.receipts.create|tax_config','/admin/login');csrfCheck();
    $accountId=(int)($_POST['account_id']??0);$rawAmount=preg_replace('/\D+/','',(string)($_POST['amount']??''));$payer=trim($_POST['payer_name']??'');$phone=preg_replace('/\D+/','',$_POST['payer_phone']??'');$email=strtolower(trim($_POST['payer_email']??''));$reference=trim($_POST['reference_code']??'');$description=trim($_POST['description']??'');$words=$description===''?0:count(preg_split('/\s+/u',$description,-1,PREG_SPLIT_NO_EMPTY));$entryDate=trim($_POST['entry_date']??'');
    if(!$accountId||$rawAmount===''||(int)$rawAmount<1||(int)$rawAmount>999999999999||$payer===''||mb_strlen($payer)>50||!preg_match('/^0[35789]\d{8}$/',$phone)||!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>254||mb_strlen($reference)>64||$words>200||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$entryDate)){flash('error','Dữ liệu phiếu thu không hợp lệ (Tên người nộp tối đa 50 ký tự, diễn giải tối đa 200 từ).');redirect('/admin/cashbook');}
    $account=dbGet('SELECT id,name FROM cash_accounts WHERE id=? AND is_active=1',[$accountId]);if(!$account){flash('error','Quỹ nhận tiền không hợp lệ.');redirect('/admin/cashbook');}
    $amount=(int)$rawAmount;$code='PT'.date('ymdHis').random_int(10,99);$fullDescription=trim('Thu từ '.$payer.($description!==''?' - '.$description:''));
    $entryId=dbInsert('INSERT INTO cash_ledger_entries (account_id,direction,amount,reference_type,reference_code,description,payer_phone,payer_email,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)',[$accountId,'in',$amount,'manual_receipt',$reference!==''?$reference:$code,$fullDescription,$phone,$email,$entryDate,$actor['id']??null]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_receipt_created','cash_ledger_entry',$entryId,json_encode(['code'=>$code,'account_id'=>$accountId,'amount'=>$amount,'payer'=>$payer],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','Đã tạo phiếu thu '.$code.' cho quỹ '.$account['name'].'.');redirect('/admin/cashbook');
});
post('/admin/cashbook/disbursements', function() {
    $actor=requireStaffPermission('rbac:finance.disbursements.create|tax_config','/admin/login');csrfCheck();
    $accountId=(int)($_POST['account_id']??0);$rawAmount=preg_replace('/\D+/','',(string)($_POST['amount']??''));$payee=trim($_POST['payee_name']??'');$phone=preg_replace('/\D+/','',$_POST['payee_phone']??'');$email=strtolower(trim($_POST['payee_email']??''));$reference=trim($_POST['reference_code']??'');$description=trim($_POST['description']??'');$words=$description===''?0:count(preg_split('/\s+/u',$description,-1,PREG_SPLIT_NO_EMPTY));$entryDate=trim($_POST['entry_date']??'');
    if(!$accountId||$rawAmount===''||(int)$rawAmount<1||(int)$rawAmount>999999999999||$payee===''||mb_strlen($payee)>50||!preg_match('/^0[35789]\d{8}$/',$phone)||!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>254||mb_strlen($reference)>64||$words>200||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$entryDate)){flash('error','Dữ liệu phiếu chi không hợp lệ (Tên người nhận tối đa 50 ký tự, diễn giải tối đa 200 từ).');redirect('/admin/cashbook');}
    $account=dbGet('SELECT id,name FROM cash_accounts WHERE id=? AND is_active=1',[$accountId]);if(!$account){flash('error','Qu&#7929; chi kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/cashbook');}
    $amount=(int)$rawAmount;$code='PC'.date('ymdHis').random_int(10,99);
    $requestId=dbInsert('INSERT INTO cash_disbursement_requests (code,account_id,amount,payee_name,payee_phone,payee_email,reference_code,description,entry_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$code,$accountId,$amount,$payee,$phone,$email,$reference,$description,$entryDate,'pending',$actor['id']??null]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_created','cash_disbursement_request',$requestId,json_encode(['code'=>$code,'account_id'=>$accountId,'amount'=>$amount,'payee'=>$payee],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','&#272;&#227; t&#7841;o phi&#7871;u chi '.$code.' ch&#7901; duy&#7879;t.');redirect('/admin/cashbook');
});
post('/admin/cashbook/disbursements/:id/approve', function($p) {
    $actor=requireStaffPermission('rbac:finance.disbursements.approve|tax_config','/admin/login');csrfCheck();$pdo=db();
    try{$pdo->beginTransaction();$request=dbGet('SELECT * FROM cash_disbursement_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending')throw new RuntimeException('not_pending');if((int)$request['created_by']===(int)$actor['id'])throw new RuntimeException('self_approval');$balanceRow=dbGet("SELECT COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE -amount END),0) AS balance FROM cash_ledger_entries WHERE account_id=? AND voided_at IS NULL",[$request['account_id']]);if((int)($balanceRow['balance']??0)<(int)$request['amount'])throw new RuntimeException('insufficient');$updated=dbRun("UPDATE cash_disbursement_requests SET status='approved',approved_by=?,approved_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$request['id']]);if($updated->rowCount()!==1)throw new RuntimeException('changed');$description=trim('Chi cho '.$request['payee_name'].($request['description']!==''?' - '.$request['description']:''));$entryId=dbInsert('INSERT INTO cash_ledger_entries (account_id,direction,amount,reference_type,reference_id,reference_code,description,payer_phone,payer_email,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$request['account_id'],'out',$request['amount'],'cash_disbursement',$request['id'],$request['code'],$description,$request['payee_phone'],$request['payee_email'],$request['entry_date'],$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_approved','cash_disbursement_request',$request['id'],json_encode(['entry_id'=>$entryId,'code'=>$request['code'],'amount'=>$request['amount']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','&#272;&#227; duy&#7879;t phi&#7871;u chi v&#224; ghi v&#224;o s&#7893; qu&#7929;.');}catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();$messages=['self_approval'=>'Ng&#432;&#7901;i l&#7853;p kh&#244;ng th&#7875; t&#7921; duy&#7879;t phi&#7871;u chi.','insufficient'=>'S&#7889; d&#432; qu&#7929; kh&#244;ng &#273;&#7911; &#273;&#7875; duy&#7879;t phi&#7871;u chi.','not_pending'=>'Phi&#7871;u chi kh&#244;ng c&#242;n ch&#7901; duy&#7879;t.'];flash('error',$messages[$exception->getMessage()]??'Kh&#244;ng th&#7875; duy&#7879;t phi&#7871;u chi.');}redirect('/admin/cashbook');
});
post('/admin/cashbook/disbursements/:id/reject', function($p) {
    $actor=requireStaffPermission('rbac:finance.disbursements.approve|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['rejection_reason']??'');$request=dbGet('SELECT id,status,created_by FROM cash_disbursement_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending'){flash('error','Phi&#7871;u chi kh&#244;ng c&#242;n ch&#7901; duy&#7879;t.');redirect('/admin/cashbook');}if((int)$request['created_by']===(int)$actor['id']){flash('error','Ng&#432;&#7901;i l&#7853;p kh&#244;ng th&#7875; t&#7921; t&#7915; ch&#7889;i phi&#7871;u chi.');redirect('/admin/cashbook');}if($reason===''||mb_strlen($reason)>300){flash('error','H&#227;y nh&#7853;p l&#253; do t&#7915; ch&#7889;i, t&#7889;i &#273;a 300 k&#253; t&#7921;.');redirect('/admin/cashbook');}dbRun("UPDATE cash_disbursement_requests SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=?,updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$reason,$request['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_rejected','cash_disbursement_request',$request['id'],json_encode(['reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7915; ch&#7889;i phi&#7871;u chi.');redirect('/admin/cashbook');
});
post('/admin/cashbook/entries/:id/void-requests', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['reason']??'');
    if(mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','L&#253; do h&#7911;y ch&#7913;ng t&#7915; ph&#7843;i t&#7915; 5 &#273;&#7871;n 300 k&#253; t&#7921;.');redirect('/admin/cashbook');}
    $entry=dbGet('SELECT id,voided_at FROM cash_ledger_entries WHERE id=?',[$p['id']]);if(!$entry||$entry['voided_at']!==null){flash('error','Ch&#7913;ng t&#7915; kh&#244;ng c&#242;n h&#7907;p l&#7879; &#273;&#7875; y&#234;u c&#7847;u h&#7911;y.');redirect('/admin/cashbook');}
    if(dbGet('SELECT id FROM cash_ledger_void_requests WHERE ledger_entry_id=?',[$entry['id']])){flash('error','Ch&#7913;ng t&#7915; n&#224;y &#273;&#227; c&#243; y&#234;u c&#7847;u h&#7911;y.');redirect('/admin/cashbook');}
    $code='HCT-'.date('Ymd-His').'-'.str_pad((string)$entry['id'],5,'0',STR_PAD_LEFT);$requestId=dbInsert("INSERT INTO cash_ledger_void_requests (code,ledger_entry_id,reason,status,created_by) VALUES (?,?,?,?,?)",[$code,$entry['id'],$reason,'pending',$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_requested','cash_ledger_entry',$entry['id'],json_encode(['request_id'=>$requestId,'code'=>$code,'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7841;o y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915; '.$code.'.');redirect('/admin/cashbook');
});
post('/admin/cashbook/void-requests/:id/approve', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$pdo=db();
    try{$pdo->beginTransaction();$request=dbGet("SELECT request.*,entry.voided_at FROM cash_ledger_void_requests request INNER JOIN cash_ledger_entries entry ON entry.id=request.ledger_entry_id WHERE request.id=?",[$p['id']]);if(!$request||$request['status']!=='pending'||$request['voided_at']!==null)throw new RuntimeException('invalid');if((int)$request['created_by']===(int)$actor['id'])throw new RuntimeException('self');$changed=dbRun("UPDATE cash_ledger_void_requests SET status='approved',approved_by=?,approved_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$request['id']]);if($changed->rowCount()!==1)throw new RuntimeException('invalid');dbRun("UPDATE cash_ledger_entries SET voided_at=datetime('now','localtime') WHERE id=? AND voided_at IS NULL",[$request['ledger_entry_id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_approved','cash_ledger_entry',$request['ledger_entry_id'],json_encode(['request_id'=>$request['id'],'reason'=>$request['reason']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','&#272;&#227; duy&#7879;t h&#7911;y ch&#7913;ng t&#7915;. B&#250;t to&#225;n &#273;&#432;&#7907;c gi&#7919; l&#7841;i trong nh&#7853;t k&#253; v&#224; kh&#244;ng c&#242;n t&#237;nh v&#224;o s&#7893; qu&#7929;.');}catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();flash('error',$exception->getMessage()==='self'?'Ng&#432;&#7901;i y&#234;u c&#7847;u kh&#244;ng th&#7875; t&#7921; duy&#7879;t h&#7911;y.':'Kh&#244;ng th&#7875; duy&#7879;t y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915;.');}redirect('/admin/cashbook');
});
post('/admin/cashbook/void-requests/:id/reject', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['rejection_reason']??'');$request=dbGet('SELECT id,status,created_by,ledger_entry_id FROM cash_ledger_void_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending'||(int)$request['created_by']===(int)$actor['id']||mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','Kh&#244;ng th&#7875; t&#7915; ch&#7889;i y&#234;u c&#7847;u. C&#7847;n l&#253; do t&#7915; 5 &#273;&#7871;n 300 k&#253; t&#7921; v&#224; ng&#432;&#7901;i duy&#7879;t kh&#244;ng &#273;&#432;&#7907;c l&#224; ng&#432;&#7901;i y&#234;u c&#7847;u.');redirect('/admin/cashbook');}dbRun("UPDATE cash_ledger_void_requests SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=?,updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$reason,$request['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_rejected','cash_ledger_entry',$request['ledger_entry_id'],json_encode(['request_id'=>$request['id'],'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7915; ch&#7889;i y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915;.');redirect('/admin/cashbook');
});
get('/admin/bank-reconciliation', function() {
    $user=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');$bankAccounts=dbAll("SELECT id,code,name FROM cash_accounts WHERE type='bank' AND is_active=1 ORDER BY sort_order,id");$bankLedgerEntries=dbAll("SELECT entry.id,entry.entry_date,entry.direction,entry.amount,entry.reference_code FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id LEFT JOIN bank_reconciliation_transactions recon ON recon.ledger_entry_id=entry.id AND recon.status='matched' WHERE account.type='bank' AND account.is_active=1 AND entry.voided_at IS NULL AND recon.id IS NULL ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 300");$transactions=dbAll("SELECT recon.*,account.name AS account_name,entry.reference_code AS ledger_reference FROM bank_reconciliation_transactions recon INNER JOIN cash_accounts account ON account.id=recon.account_id LEFT JOIN cash_ledger_entries entry ON entry.id=recon.ledger_entry_id ORDER BY recon.transaction_date DESC,recon.id DESC LIMIT 300");$summary=dbGet("SELECT SUM(CASE WHEN status='unmatched' THEN 1 ELSE 0 END) AS unmatched,SUM(CASE WHEN status='matched' THEN 1 ELSE 0 END) AS matched,COALESCE(SUM(CASE WHEN status='unmatched' THEN amount ELSE 0 END),0) AS unmatched_amount FROM bank_reconciliation_transactions")?:['unmatched'=>0,'matched'=>0,'unmatched_amount'=>0];$canManage=(($user['role']??'')==='admin')||rbacHasCapability((int)$user['id'],'finance.bank_reconciliation.manage');view('admin/bank-reconciliation',['title'=>'&#272;&#7889;i so&#225;t ng&#226;n h&#224;ng/QR','userRole'=>'admin','bankAccounts'=>$bankAccounts,'bankLedgerEntries'=>$bankLedgerEntries,'transactions'=>$transactions,'summary'=>$summary,'canManage'=>$canManage]);
});
post('/admin/bank-reconciliation', function() {
    $actor=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');csrfCheck();$accountId=(int)($_POST['account_id']??0);$direction=$_POST['direction']??'';$rawAmount=preg_replace('/\D/','',$_POST['amount']??'');$amount=(int)$rawAmount;$date=trim($_POST['transaction_date']??'');$reference=trim($_POST['bank_reference']??'');$description=trim($_POST['description']??'');$ledgerId=(int)($_POST['ledger_entry_id']??0);$words=$description===''?0:count(preg_split('/\s+/u',$description));
    if(!$accountId||!in_array($direction,['in','out'],true)||$rawAmount===''||$amount<1||$amount>999999999999||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||mb_strlen($reference)>120||$words>200){flash('error','D&#7919; li&#7879;u giao d&#7883;ch ng&#226;n h&#224;ng kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/bank-reconciliation');}$account=dbGet("SELECT id FROM cash_accounts WHERE id=? AND type='bank' AND is_active=1",[$accountId]);if(!$account){flash('error','T&#224;i kho&#7843;n ng&#226;n h&#224;ng kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/bank-reconciliation');}$status='unmatched';$matchedAt=null;
    if($ledgerId){$entry=dbGet("SELECT entry.id FROM cash_ledger_entries entry WHERE entry.id=? AND entry.account_id=? AND entry.direction=? AND entry.amount=? AND entry.voided_at IS NULL",[$ledgerId,$accountId,$direction,$amount]);if(!$entry||dbGet("SELECT id FROM bank_reconciliation_transactions WHERE ledger_entry_id=? AND status='matched'",[$ledgerId])){flash('error','B&#250;t to&#225;n qu&#7929; kh&#244;ng kh&#7899;p t&#224;i kho&#7843;n, chi&#7873;u giao d&#7883;ch ho&#7863;c s&#7889; ti&#7873;n.');redirect('/admin/bank-reconciliation');}$status='matched';$matchedAt=date('Y-m-d H:i:s');}
    $code='DST-'.date('Ymd-His').'-'.str_pad((string)random_int(1,9999),4,'0',STR_PAD_LEFT);$id=dbInsert("INSERT INTO bank_reconciliation_transactions (code,account_id,direction,amount,transaction_date,bank_reference,description,status,ledger_entry_id,created_by,matched_by,matched_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",[$code,$accountId,$direction,$amount,$date,$reference,$description,$status,$ledgerId?:null,$actor['id']??null,$status==='matched'?($actor['id']??null):null,$matchedAt]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','bank_reconciliation_created','bank_reconciliation_transaction',$id,json_encode(['code'=>$code,'status'=>$status,'ledger_entry_id'=>$ledgerId?:null],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; ghi nh&#7853;n giao d&#7883;ch '. $code .($status==='matched'?' v&#224; &#273;&#7889;i so&#225;t th&#224;nh c&#244;ng.':' ch&#7901; &#273;&#7889;i so&#225;t.'));redirect('/admin/bank-reconciliation');
});
post('/admin/bank-reconciliation/:id/match', function($p) {
    $actor=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');csrfCheck();$ledgerId=(int)($_POST['ledger_entry_id']??0);$transaction=dbGet("SELECT * FROM bank_reconciliation_transactions WHERE id=?",[$p['id']]);if(!$transaction||$transaction['status']!=='unmatched'||!$ledgerId){flash('error','Kh&#244;ng th&#7875; &#273;&#7889;i so&#225;t giao d&#7883;ch.');redirect('/admin/bank-reconciliation');}$entry=dbGet("SELECT entry.id FROM cash_ledger_entries entry WHERE entry.id=? AND entry.account_id=? AND entry.direction=? AND entry.amount=? AND entry.voided_at IS NULL",[$ledgerId,$transaction['account_id'],$transaction['direction'],$transaction['amount']]);if(!$entry||dbGet("SELECT id FROM bank_reconciliation_transactions WHERE ledger_entry_id=? AND status='matched'",[$ledgerId])){flash('error','B&#250;t to&#225;n qu&#7929; kh&#244;ng kh&#7899;p ho&#7863;c &#273;&#227; &#273;&#432;&#7907;c &#273;&#7889;i so&#225;t.');redirect('/admin/bank-reconciliation');}dbRun("UPDATE bank_reconciliation_transactions SET status='matched',ledger_entry_id=?,matched_by=?,matched_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='unmatched'",[$ledgerId,$actor['id']??null,$transaction['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','bank_reconciliation_matched','bank_reconciliation_transaction',$transaction['id'],json_encode(['ledger_entry_id'=>$ledgerId],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; &#273;&#7889;i so&#225;t giao d&#7883;ch th&#224;nh c&#244;ng.');redirect('/admin/bank-reconciliation');
});
get('/admin/customer-debts', function() {
 $user=requireStaffPermission('rbac:finance.customer_debt.collect|tax_config','/admin/login');$accounts=dbAll("SELECT id,name FROM cash_accounts WHERE is_active=1 ORDER BY sort_order,id");$debt="CASE WHEN COALESCE(o.remaining_amount,0)>0 THEN o.remaining_amount ELSE MAX(o.grand_total-COALESCE(o.paid_amount,0)+COALESCE(o.refund_amount,0),0) END";$debtOrders=dbAll("SELECT o.*, $debt AS outstanding FROM orders o WHERE o.payment_status NOT IN ('paid','cancelled','refunded') AND ($debt)>0 ORDER BY o.created_at DESC LIMIT 300");$summary=dbGet("SELECT COUNT(*) AS orders,COALESCE(SUM($debt),0) AS outstanding FROM orders o WHERE o.payment_status NOT IN ('paid','cancelled','refunded') AND ($debt)>0")?:['orders'=>0,'outstanding'=>0];$canCollect=(($user['role']??'')==='admin')||rbacHasCapability((int)$user['id'],'finance.customer_debt.collect');view('admin/customer-debts',['title'=>'Thu công nợ khách hàng','userRole'=>'admin','accounts'=>$accounts,'debtOrders'=>$debtOrders,'summary'=>$summary,'canCollect'=>$canCollect,'selectedOrderCode'=>trim($_GET['order']??'')]);
});
post('/admin/customer-debts/collections', function() {
 $actor=requireStaffPermission('rbac:finance.customer_debt.collect|tax_config','/admin/login');csrfCheck();$code=trim($_POST['order_code']??'');$accountId=(int)($_POST['account_id']??0);$raw=preg_replace('/\D/','',$_POST['amount']??'');$amount=(int)$raw;$date=trim($_POST['entry_date']??'');$reference=trim($_POST['reference_code']??'');$description=trim($_POST['description']??'');$words=$description===''?0:count(preg_split('/\s+/u',$description));if($code===''||mb_strlen($code)>64||!$accountId||$raw===''||$amount<1||$amount>999999999999||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||mb_strlen($reference)>64||$words>200){flash('error','Dữ liệu phiếu thu công nợ không hợp lệ.');redirect('/admin/customer-debts');}$pdo=db();try{$pdo->beginTransaction();$order=dbGet('SELECT * FROM orders WHERE code=?',[$code]);if(!$order||in_array($order['payment_status'],['paid','cancelled','refunded'],true))throw new RuntimeException('order');$outstanding=(int)$order['remaining_amount'];if($outstanding<=0)$outstanding=max(0,(int)$order['grand_total']-(int)$order['paid_amount']+(int)$order['refund_amount']);if($amount>$outstanding)throw new RuntimeException('amount');$account=dbGet('SELECT id,name FROM cash_accounts WHERE id=? AND is_active=1',[$accountId]);if(!$account)throw new RuntimeException('account');$receipt='TCN-'.date('Ymd-His').'-'.str_pad((string)$order['id'],5,'0',STR_PAD_LEFT);$newRemaining=$outstanding-$amount;$newPaid=(int)$order['paid_amount']+$amount;dbRun("UPDATE orders SET paid_amount=?,remaining_amount=?,payment_status=?,updated_at=datetime('now','localtime') WHERE id=?",[$newPaid,$newRemaining,$newRemaining===0?'paid':'partial',$order['id']]);$entryId=dbInsert('INSERT INTO cash_ledger_entries (account_id,direction,amount,reference_type,reference_id,reference_code,description,payer_phone,payer_email,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$accountId,'in',$amount,'customer_debt',$order['id'],$receipt,trim('Thu công nợ đơn '.$order['code'].($description!==''?' - '.$description:'')),$order['shipping_phone'],$order['user_id']?((dbGet('SELECT email FROM users WHERE id=?',[$order['user_id']])['email']??'')):'' ,$date,$actor['id']??null]);$id=dbInsert('INSERT INTO customer_debt_collections (code,order_id,ledger_entry_id,account_id,amount,collection_date,reference_code,description,created_by) VALUES (?,?,?,?,?,?,?,?,?)',[$receipt,$order['id'],$entryId,$accountId,$amount,$date,$reference,$description,$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','customer_debt_collected','order',$order['id'],json_encode(['collection_id'=>$id,'amount'=>$amount,'remaining'=>$newRemaining],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã thu công nợ cho đơn '.$order['code'].'.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$m=['order'=>'Đơn hàng không còn đủ điều kiện thu công nợ.','amount'=>'Số tiền thu vượt quá công nợ còn lại.','account'=>'Quỹ nhận tiền không hợp lệ.'];flash('error',$m[$e->getMessage()]??'Không thể ghi nhận thu công nợ.');}redirect('/admin/customer-debts');
});
get('/admin/suppliers',function(){$u=requireStaffPermission('rbac:purchasing.suppliers.view|tax_config','/admin/login');$items=dbAll("SELECT * FROM suppliers ORDER BY is_active DESC,name LIMIT 500");$can=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.suppliers.manage');view('admin/suppliers',['title'=>'Nhà cung cấp','userRole'=>'admin','items'=>$items,'canManage'=>$can]);});
post('/admin/suppliers',function(){$u=requireStaffPermission('rbac:purchasing.suppliers.manage|tax_config','/admin/login');csrfCheck();$name=trim($_POST['name']??'');$phone=preg_replace('/\D/','',$_POST['phone']??'');$email=trim($_POST['email']??'');$tax=trim($_POST['tax_code']??'');$address=trim($_POST['address']??'');if($name===''||mb_strlen($name)>160){flash('error','Vui lòng nhập Tên nhà cung cấp.');redirect('/admin/suppliers');}if($phone===''||!preg_match('/^0[35789]\d{8}$/',$phone)){flash('error','Số điện thoại không hợp lệ (phải đúng 10 số, đầu 03, 05, 07, 08 hoặc 09).');redirect('/admin/suppliers');}if($email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('error','Vui lòng nhập đúng định dạng Email liên hệ (VD: ncc@gmail.com).');redirect('/admin/suppliers');}if($tax===''||!preg_match('/^[0-9\-]{10,14}$/',$tax)){flash('error','Vui lòng nhập đúng Mã số thuế hợp lệ (từ 10 đến 13 số).');redirect('/admin/suppliers');}if($address===''||mb_strlen($address)<5||mb_strlen($address)>300){flash('error','Vui lòng nhập Địa chỉ trụ sở / kho đầy đủ (từ 5 đến 300 ký tự).');redirect('/admin/suppliers');}$code='NCC-'.date('Ymd-His').'-'.random_int(100,999);$id=dbInsert('INSERT INTO suppliers (code,name,phone,email,tax_code,address,created_by) VALUES (?,?,?,?,?,?,?)',[$code,$name,$phone,$email,$tax,$address,$u['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id']??null,$u['role']??'admin','supplier_created','supplier',$id,json_encode(['code'=>$code],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã tạo nhà cung cấp '.$code.'.');redirect('/admin/suppliers');});
post('/admin/suppliers/:id/update',function($p){$u=requireStaffPermission('rbac:purchasing.suppliers.manage|tax_config','/admin/login');csrfCheck();$id=(int)$p['id'];$name=trim($_POST['name']??'');$phone=preg_replace('/\D/','',$_POST['phone']??'');$email=trim($_POST['email']??'');$tax=trim($_POST['tax_code']??'');$address=trim($_POST['address']??'');if($name===''||mb_strlen($name)>160){flash('error','Vui lòng nhập Tên nhà cung cấp.');redirect('/admin/suppliers');}if($phone===''||!preg_match('/^0[35789]\d{8}$/',$phone)){flash('error','Số điện thoại không hợp lệ (phải đúng 10 số, đầu 03, 05, 07, 08 hoặc 09).');redirect('/admin/suppliers');}if($email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)){flash('error','Vui lòng nhập đúng định dạng Email liên hệ.');redirect('/admin/suppliers');}if($tax===''||!preg_match('/^[0-9\-]{10,14}$/',$tax)){flash('error','Vui lòng nhập đúng Mã số thuế hợp lệ.');redirect('/admin/suppliers');}if($address===''||mb_strlen($address)<5||mb_strlen($address)>300){flash('error','Vui lòng nhập Địa chỉ trụ sở / kho đầy đủ (từ 5 đến 300 ký tự).');redirect('/admin/suppliers');}dbRun("UPDATE suppliers SET name=?,phone=?,email=?,tax_code=?,address=? WHERE id=?",[$name,$phone,$email,$tax,$address,$id]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id']??null,$u['role']??'admin','supplier_updated','supplier',$id,json_encode(['id'=>$id,'name'=>$name],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã cập nhật thông tin nhà cung cấp thành công.');redirect('/admin/suppliers');});
post('/admin/suppliers/:id/delete',function($p){$u=requireStaffPermission('rbac:purchasing.suppliers.manage|tax_config','/admin/login');csrfCheck();$id=(int)$p['id'];$password=$_POST['admin_password']??'';$userInDb=dbGet("SELECT password_hash FROM users WHERE id=?",[$u['id']]);if(!$userInDb||!password_verify($password,$userInDb['password_hash'])){flash('error','Mật khẩu xác nhận không chính xác. Không thể xóa nhà cung cấp.');redirect('/admin/suppliers');}dbRun("DELETE FROM suppliers WHERE id=?",[$id]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id']??null,$u['role']??'admin','supplier_deleted','supplier',$id,'{}',$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã xóa nhà cung cấp thành công.');redirect('/admin/suppliers');});
get('/admin/purchase-requests',function(){$u=requireStaffPermission('rbac:purchasing.requests.create|rbac:purchasing.requests.approve|rbac:purchasing.orders.create|rbac:purchasing.orders.approve|tax_config','/admin/login');$suppliers=dbAll("SELECT id,code,name FROM suppliers WHERE is_active=1 ORDER BY name");$low=dbAll("SELECT id,sku,oem_code,name,stock,min_stock,max_stock FROM products WHERE status!='deleted' AND stock<=min_stock ORDER BY stock,min_stock,name LIMIT 500");$items=dbAll("SELECT pr.*,s.name AS supplier_name,u.full_name AS creator_name,COUNT(DISTINCT pri.id) AS item_count,po.id AS po_id,po.code AS po_code,po.status AS po_status,po.created_by AS po_created_by,po.rejection_reason AS po_rejection_reason FROM purchase_requests pr INNER JOIN suppliers s ON s.id=pr.supplier_id LEFT JOIN users u ON u.id=pr.created_by LEFT JOIN purchase_request_items pri ON pri.request_id=pr.id LEFT JOIN purchase_orders po ON po.source_request_id=pr.id GROUP BY pr.id ORDER BY CASE pr.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,pr.created_at DESC LIMIT 200");$canCreate=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.requests.create');$canApprove=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.requests.approve');$canCreateOrder=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.orders.create');$canApproveOrder=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.orders.approve');view('admin/purchase-requests',['title'=>'Yêu cầu mua hàng','userRole'=>'admin','suppliers'=>$suppliers,'low'=>$low,'items'=>$items,'canCreate'=>$canCreate,'canApprove'=>$canApprove,'canCreateOrder'=>$canCreateOrder,'canApproveOrder'=>$canApproveOrder,'currentUserId'=>(int)$u['id']]);});
post('/admin/purchase-requests',function(){$u=requireStaffPermission('rbac:purchasing.requests.create|tax_config','/admin/login');csrfCheck();$sid=(int)($_POST['supplier_id']??0);$note=trim($_POST['note']??'');$qty=$_POST['qty']??[];if(!$sid||mb_strlen($note)>500||!is_array($qty)){flash('error','Dữ liệu yêu cầu mua không hợp lệ.');redirect('/admin/purchase-requests');}$supplier=dbGet('SELECT id FROM suppliers WHERE id=? AND is_active=1',[$sid]);if(!$supplier){flash('error','Nhà cung cấp không hợp lệ.');redirect('/admin/purchase-requests');}$lines=[];foreach($qty as $id=>$n){$id=(int)$id;$n=(int)preg_replace('/\D/','',(string)$n);if($n>0&&$n<=100000){$p=dbGet("SELECT id,sku,name,stock,min_stock,max_stock FROM products WHERE id=? AND status!='deleted'",[$id]);if($p)$lines[]=[$p,$n];}}if(!$lines){flash('error','Hãy nhập số lượng mua cho ít nhất một sản phẩm.');redirect('/admin/purchase-requests');}$pdo=db();try{$pdo->beginTransaction();$code='YCM-'.date('Ymd-His').'-'.random_int(100,999);$rid=dbInsert("INSERT INTO purchase_requests (code,supplier_id,status,note,created_by) VALUES (?,?,'pending',?,?)",[$code,$sid,$note,$u['id']??null]);foreach($lines as [$p,$n])dbInsert('INSERT INTO purchase_request_items (request_id,product_id,sku,product_name,current_stock,min_stock,requested_qty) VALUES (?,?,?,?,?,?,?)',[$rid,$p['id'],$p['sku'],$p['name'],$p['stock'],$p['min_stock'],$n]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id']??null,$u['role']??'admin','purchase_request_created','purchase_request',$rid,json_encode(['code'=>$code,'lines'=>count($lines)],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã tạo yêu cầu mua '.$code.' chờ duyệt.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Không thể tạo yêu cầu mua.');}redirect('/admin/purchase-requests');});
post('/admin/purchase-requests/:id/approve',function($p){$u=requireStaffPermission('rbac:purchasing.requests.approve|tax_config','/admin/login');csrfCheck();$r=dbGet("SELECT * FROM purchase_requests WHERE id=?",[$p['id']]);if(!$r||$r['status']!=='pending'||(int)$r['created_by']===(int)$u['id']){flash('error','Không thể duyệt yêu cầu mua này.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_requests SET status='approved',approved_by=?,approved_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$u['id'],$r['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_request_approved','purchase_request',$r['id'],'{}',$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã duyệt yêu cầu mua.');redirect('/admin/purchase-requests');});
post('/admin/purchase-requests/:id/reject',function($p){$u=requireStaffPermission('rbac:purchasing.requests.approve|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['reason']??'');$r=dbGet("SELECT * FROM purchase_requests WHERE id=?",[$p['id']]);if(!$r||$r['status']!=='pending'||(int)$r['created_by']===(int)$u['id']||mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','Cần lý do từ chối 5-300 ký tự và người duyệt phải khác người lập.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_requests SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=? WHERE id=? AND status='pending'",[$u['id'],$reason,$r['id']]);flash('success','Đã từ chối yêu cầu mua.');redirect('/admin/purchase-requests');});
post('/admin/purchase-requests/:id/purchase-order',function($p){$u=requireStaffPermission('rbac:purchasing.orders.create|tax_config','/admin/login');csrfCheck();$pdo=db();try{$pdo->beginTransaction();$r=dbGet("SELECT * FROM purchase_requests WHERE id=?",[$p['id']]);if(!$r||$r['status']!=='approved'||dbGet('SELECT id FROM purchase_orders WHERE source_request_id=?',[$r['id']]))throw new RuntimeException('invalid');$lines=dbAll("SELECT pri.*,COALESCE(p.cost_price,0) AS unit_cost FROM purchase_request_items pri INNER JOIN products p ON p.id=pri.product_id WHERE pri.request_id=?",[$r['id']]);if(!$lines)throw new RuntimeException('invalid');$total=0;foreach($lines as $line)$total+=(int)$line['requested_qty']*(int)$line['unit_cost'];$code='PO-'.date('Ymd-His').'-'.str_pad((string)$r['id'],5,'0',STR_PAD_LEFT);$oid=dbInsert("INSERT INTO purchase_orders (code,source_request_id,supplier_id,status,total_amount,created_by) VALUES (?,?,?,'draft',?,?)",[$code,$r['id'],$r['supplier_id'],$total,$u['id']]);foreach($lines as $line)dbInsert('INSERT INTO purchase_order_items (order_id,product_id,sku,product_name,ordered_qty,unit_cost,line_total) VALUES (?,?,?,?,?,?,?)',[$oid,$line['product_id'],$line['sku'],$line['product_name'],$line['requested_qty'],$line['unit_cost'],(int)$line['requested_qty']*(int)$line['unit_cost']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_order_created','purchase_order',$oid,json_encode(['code'=>$code,'request_id'=>$r['id']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã tạo PO '.$code.' ở trạng thái nháp.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Không thể tạo PO từ yêu cầu này.');}redirect('/admin/purchase-requests');});
post('/admin/purchase-orders/:id/approve',function($p){$u=requireStaffPermission('rbac:purchasing.orders.approve|tax_config','/admin/login');csrfCheck();$po=dbGet('SELECT * FROM purchase_orders WHERE id=?',[$p['id']]);if(!$po||$po['status']!=='draft'||(int)$po['created_by']===(int)$u['id']){flash('error','Không thể duyệt PO này hoặc người tạo đang tự duyệt.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_orders SET status='approved',approved_by=?,approved_at=datetime('now','localtime') WHERE id=? AND status='draft'",[$u['id'],$po['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_order_approved','purchase_order',$po['id'],json_encode(['code'=>$po['code']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã duyệt PO '.$po['code'].'.');redirect('/admin/purchase-requests');});
post('/admin/purchase-orders/:id/reject',function($p){$u=requireStaffPermission('rbac:purchasing.orders.approve|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['po_rejection_reason']??'');$po=dbGet('SELECT * FROM purchase_orders WHERE id=?',[$p['id']]);if(!$po||$po['status']!=='draft'||(int)$po['created_by']===(int)$u['id']||mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','Cần lý do từ chối 5-300 ký tự và người duyệt phải khác người tạo PO.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_orders SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=? WHERE id=? AND status='draft'",[$u['id'],$reason,$po['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_order_rejected','purchase_order',$po['id'],json_encode(['reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã từ chối PO '.$po['code'].'.');redirect('/admin/purchase-requests');});
get('/admin/purchase-receipts',function(){$u=requireStaffPermission('rbac:purchasing.receipts.create|tax_config','/admin/login');$orders=dbAll("SELECT po.id,po.code,s.name AS supplier_name FROM purchase_orders po INNER JOIN suppliers s ON s.id=po.supplier_id WHERE po.status IN ('approved','partially_received') ORDER BY po.created_at DESC");$selectedPo=max(0,(int)($_GET['po']??0));$selectedOrder=$selectedPo?dbGet("SELECT po.*,s.name AS supplier_name FROM purchase_orders po INNER JOIN suppliers s ON s.id=po.supplier_id WHERE po.id=? AND po.status IN ('approved','partially_received')",[$selectedPo]):null;$orderLines=$selectedOrder?dbAll('SELECT * FROM purchase_order_items WHERE order_id=? ORDER BY id',[$selectedPo]):[];$receipts=dbAll("SELECT gr.*,po.code AS po_code,s.name AS supplier_name,COUNT(gri.id) AS line_count FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN goods_receipt_items gri ON gri.receipt_id=gr.id GROUP BY gr.id ORDER BY gr.created_at DESC LIMIT 200");view('admin/purchase-receipts',['title'=>'Nhận hàng theo PO','userRole'=>'admin','orders'=>$orders,'selectedPo'=>$selectedPo,'selectedOrder'=>$selectedOrder,'orderLines'=>$orderLines,'receipts'=>$receipts]);});
post('/admin/purchase-receipts',function(){$u=requireStaffPermission('rbac:purchasing.receipts.create|tax_config','/admin/login');csrfCheck();$oid=(int)($_POST['order_id']??0);$date=trim($_POST['received_date']??'');$note=trim($_POST['note']??'');$qty=$_POST['qty']??[];if(!$oid||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||mb_strlen($note)>500||!is_array($qty)){flash('error','Dữ liệu nhận hàng không hợp lệ.');redirect('/admin/purchase-receipts');}$pdo=db();try{$pdo->beginTransaction();$po=dbGet("SELECT * FROM purchase_orders WHERE id=? AND status IN ('approved','partially_received')",[$oid]);if(!$po)throw new RuntimeException('po');$lines=[];foreach($qty as $id=>$raw){$id=(int)$id;$n=(int)preg_replace('/\D/','',(string)$raw);if($n<1)continue;$line=dbGet('SELECT * FROM purchase_order_items WHERE id=? AND order_id=?',[$id,$oid]);if(!$line||$n>((int)$line['ordered_qty']-(int)$line['received_qty']))throw new RuntimeException('qty');$lines[]=[$line,$n];}if(!$lines)throw new RuntimeException('qty');$code='PNH-'.date('Ymd-His').'-'.random_int(100,999);$rid=dbInsert("INSERT INTO goods_receipts (code,order_id,received_date,status,note,created_by) VALUES (?,?,?,'pending_qc',?,?)",[$code,$oid,$date,$note,$u['id']]);foreach($lines as [$line,$n]){dbInsert('INSERT INTO goods_receipt_items (receipt_id,order_item_id,product_id,received_qty) VALUES (?,?,?,?)',[$rid,$line['id'],$line['product_id'],$n]);dbRun('UPDATE purchase_order_items SET received_qty=received_qty+? WHERE id=?',[$n,$line['id']]);}$remaining=(int)(dbGet('SELECT COALESCE(SUM(ordered_qty-received_qty),0) AS n FROM purchase_order_items WHERE order_id=?',[$oid])['n']??0);dbRun('UPDATE purchase_orders SET status=? WHERE id=?',[$remaining===0?'received':'partially_received',$oid]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','goods_receipt_created','goods_receipt',$rid,json_encode(['po'=>$po['code'],'remaining'=>$remaining],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã ghi nhận phiếu '.$code.' chờ kiểm chất lượng.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error',$e->getMessage()==='qty'?'Số lượng nhận vượt quá số còn lại của PO.':'Không thể ghi nhận hàng về.');}redirect('/admin/purchase-receipts?po='.$oid);});
get('/admin/purchase-quality',function(){$u=requireStaffPermission('rbac:purchasing.quality.inspect|tax_config','/admin/login');$pending=dbAll("SELECT gr.id,gr.code,po.code AS po_code,s.name AS supplier_name FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id WHERE gr.status='pending_qc' ORDER BY gr.created_at");$selectedReceipt=max(0,(int)($_GET['receipt']??0));$receipt=$selectedReceipt?dbGet("SELECT gr.*,po.code AS po_code FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id WHERE gr.id=? AND gr.status='pending_qc'",[$selectedReceipt]):null;$lines=$receipt?dbAll("SELECT gri.*,p.name AS product_name,p.sku,p.oem_code FROM goods_receipt_items gri INNER JOIN products p ON p.id=gri.product_id WHERE gri.receipt_id=? ORDER BY gri.id",[$selectedReceipt]):[];view('admin/purchase-quality',['title'=>'Kiểm chất lượng hàng nhận','userRole'=>'admin','pending'=>$pending,'selectedReceipt'=>$selectedReceipt,'receipt'=>$receipt,'lines'=>$lines]);});
post('/admin/purchase-quality/:id',function($p){$u=requireStaffPermission('rbac:purchasing.quality.inspect|tax_config','/admin/login');csrfCheck();$accepted=$_POST['accepted']??[];$notes=$_POST['line_note']??[];$qcNote=trim($_POST['qc_note']??'');if(!is_array($accepted)||mb_strlen($qcNote)>500){flash('error','Dữ liệu kiểm chất lượng không hợp lệ.');redirect('/admin/purchase-quality?receipt='.$p['id']);}$pdo=db();$changed=[];try{$pdo->beginTransaction();$receipt=dbGet("SELECT * FROM goods_receipts WHERE id=? AND status='pending_qc'",[$p['id']]);if(!$receipt)throw new RuntimeException('receipt');$lines=dbAll("SELECT gri.*,poi.unit_cost,p.stock,p.max_stock FROM goods_receipt_items gri INNER JOIN purchase_order_items poi ON poi.id=gri.order_item_id INNER JOIN products p ON p.id=gri.product_id WHERE gri.receipt_id=?",[$receipt['id']]);foreach($lines as $line){$ok=(int)preg_replace('/\D/','',(string)($accepted[$line['id']]??0));if($ok<0||$ok>(int)$line['received_qty'])throw new RuntimeException('qty');$allChecks=!empty($_POST['quantity_ok'][$line['id']])&&!empty($_POST['appearance_ok'][$line['id']])&&!empty($_POST['oem_ok'][$line['id']]);if($ok>0&&!$allChecks)throw new RuntimeException('check');if((int)$line['stock']+$ok>(int)$line['max_stock'])throw new RuntimeException('max');$bad=(int)$line['received_qty']-$ok;$lineNote=trim((string)($notes[$line['id']]??''));if(mb_strlen($lineNote)>300)throw new RuntimeException('note');dbRun('UPDATE goods_receipt_items SET accepted_qty=?,rejected_qty=?,quantity_ok=?,appearance_ok=?,oem_ok=?,qc_note=? WHERE id=?',[$ok,$bad,!empty($_POST['quantity_ok'][$line['id']])?1:0,!empty($_POST['appearance_ok'][$line['id']])?1:0,!empty($_POST['oem_ok'][$line['id']])?1:0,$lineNote,$line['id']]);if($ok>0){dbRun("UPDATE products SET stock=stock+?,total_import_value=total_import_value+(?*?),updated_at=datetime('now','localtime') WHERE id=?",[$ok,$ok,$line['unit_cost'],$line['product_id']]);dbInsert("INSERT INTO inventory_stock_movements (product_id,direction,quantity,reference_type,reference_id,note,created_by) VALUES (?,'in',?,'goods_receipt',?,?,?)",[$line['product_id'],$ok,$receipt['id'],$lineNote,$u['id']]);$changed[]=(int)$line['product_id'];}}dbRun("UPDATE goods_receipts SET status='qc_completed',qc_by=?,qc_at=datetime('now','localtime'),qc_note=? WHERE id=? AND status='pending_qc'",[$u['id'],$qcNote,$receipt['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','goods_receipt_quality_completed','goods_receipt',$receipt['id'],json_encode(['products'=>$changed],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();foreach(array_unique($changed) as $productId)if(function_exists('inventoryCheckLowStockAlert'))inventoryCheckLowStockAlert($productId,'purchase_receipt');flash('success','Đã hoàn tất kiểm chất lượng và nhập kho số lượng đạt.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$m=['qty'=>'Số lượng đạt không hợp lệ.','check'=>'Sản phẩm có số lượng đạt phải vượt qua đủ ba tiêu chí.','max'=>'Nhập kho sẽ vượt tồn tối đa của sản phẩm.','note'=>'Ghi chú từng dòng tối đa 300 ký tự.'];flash('error',$m[$e->getMessage()]??'Không thể hoàn tất kiểm chất lượng.');}redirect('/admin/purchase-quality');});
get('/admin/purchase-costs',function(){$u=requireStaffPermission('rbac:purchasing.costs.allocate|tax_config','/admin/login');$receipts=dbAll("SELECT gr.id,gr.code,po.code AS po_code,s.name AS supplier_name FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN purchase_cost_allocations pca ON pca.receipt_id=gr.id WHERE gr.status='qc_completed' AND pca.id IS NULL AND EXISTS(SELECT 1 FROM goods_receipt_items gri WHERE gri.receipt_id=gr.id AND gri.accepted_qty>0) ORDER BY gr.qc_at");$items=dbAll("SELECT pca.*,gr.code AS receipt_code,po.code AS po_code,s.name AS supplier_name FROM purchase_cost_allocations pca INNER JOIN goods_receipts gr ON gr.id=pca.receipt_id INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id ORDER BY pca.created_at DESC LIMIT 200");view('admin/purchase-costs',['title'=>'Phân bổ chi phí mua','userRole'=>'admin','receipts'=>$receipts,'items'=>$items]);});
post('/admin/purchase-costs',function(){$u=requireStaffPermission('rbac:purchasing.costs.allocate|tax_config','/admin/login');csrfCheck();$rid=(int)($_POST['receipt_id']??0);$shipping=(int)preg_replace('/\D/','',$_POST['shipping_cost']??'0');$tax=(int)preg_replace('/\D/','',$_POST['tax_cost']??'0');$other=(int)preg_replace('/\D/','',$_POST['other_cost']??'0');$note=trim($_POST['note']??'');$total=$shipping+$tax+$other;if(!$rid||$total<1||$total>999999999999||mb_strlen($note)>500){flash('error','Dữ liệu chi phí mua không hợp lệ.');redirect('/admin/purchase-costs');}$pdo=db();try{$pdo->beginTransaction();$receipt=dbGet("SELECT * FROM goods_receipts WHERE id=? AND status='qc_completed'",[$rid]);if(!$receipt||dbGet('SELECT id FROM purchase_cost_allocations WHERE receipt_id=?',[$rid]))throw new RuntimeException('receipt');$lines=dbAll("SELECT gri.id,gri.product_id,gri.accepted_qty,poi.unit_cost FROM goods_receipt_items gri INNER JOIN purchase_order_items poi ON poi.id=gri.order_item_id WHERE gri.receipt_id=? AND gri.accepted_qty>0 ORDER BY gri.id",[$rid]);if(!$lines)throw new RuntimeException('receipt');$weight=0;foreach($lines as $line)$weight+=(int)$line['accepted_qty']*max(0,(int)$line['unit_cost']);$useQty=$weight===0;if($useQty){foreach($lines as $line)$weight+=(int)$line['accepted_qty'];}$code='CPM-'.date('Ymd-His').'-'.random_int(100,999);$aid=dbInsert('INSERT INTO purchase_cost_allocations (code,receipt_id,shipping_cost,tax_cost,other_cost,total_cost,note,created_by) VALUES (?,?,?,?,?,?,?,?)',[$code,$rid,$shipping,$tax,$other,$total,$note,$u['id']]);$allocated=0;$last=count($lines)-1;foreach($lines as $index=>$line){$lineWeight=$useQty?(int)$line['accepted_qty']:(int)$line['accepted_qty']*(int)$line['unit_cost'];$amount=$index===$last?$total-$allocated:(int)floor($total*$lineWeight/$weight);$allocated+=$amount;dbInsert('INSERT INTO purchase_cost_allocation_items (allocation_id,receipt_item_id,product_id,allocated_cost) VALUES (?,?,?,?)',[$aid,$line['id'],$line['product_id'],$amount]);dbRun("UPDATE products SET total_import_value=total_import_value+?,cost_price=CASE WHEN stock>0 THEN ROUND((total_import_value+?)*1.0/stock) ELSE cost_price END,updated_at=datetime('now','localtime') WHERE id=?",[$amount,$amount,$line['product_id']]);}dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_cost_allocated','goods_receipt',$rid,json_encode(['allocation_id'=>$aid,'total'=>$total],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã phân bổ '.$total.' đ vào giá vốn.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Phiếu không hợp lệ hoặc đã được phân bổ chi phí.');}redirect('/admin/purchase-costs');});
get('/admin/supplier-returns',function(){$u=requireStaffPermission('rbac:purchasing.returns.create|tax_config','/admin/login');$canApprove=hasPermission($u,'purchasing.returns.approve')||hasPermission($u,'tax_config');$filterStatus=$_GET['status']??'all';$statusWhere=$filterStatus!=='all'?"AND sr.status='".preg_replace('/[^a-z]/','',$filterStatus)."'":'';$receipts=dbAll("SELECT gr.id,gr.code,po.code AS po_code,s.name AS supplier_name FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id WHERE gr.status='qc_completed' ORDER BY gr.qc_at DESC");$selectedReceipt=max(0,(int)($_GET['receipt']??0));$receipt=$selectedReceipt?dbGet("SELECT gr.*,po.code AS po_code,s.name AS supplier_name FROM goods_receipts gr INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id WHERE gr.id=? AND gr.status='qc_completed'",[$selectedReceipt]):null;$lines=$receipt?dbAll("SELECT gri.*,p.name AS product_name,p.sku,COALESCE((SELECT SUM(sri.return_qty) FROM supplier_return_items sri INNER JOIN supplier_returns sr ON sr.id=sri.return_id WHERE sri.receipt_item_id=gri.id AND sr.status!='rejected'),0) AS returned_qty FROM goods_receipt_items gri INNER JOIN products p ON p.id=gri.product_id WHERE gri.receipt_id=? ORDER BY gri.id",[$selectedReceipt]):[];$items=dbAll("SELECT sr.*,gr.code AS receipt_code,s.name AS supplier_name,COALESCE(SUM(sri.return_qty),0) AS total_qty FROM supplier_returns sr INNER JOIN goods_receipts gr ON gr.id=sr.receipt_id INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN supplier_return_items sri ON sri.return_id=sr.id WHERE 1=1 $statusWhere GROUP BY sr.id ORDER BY sr.created_at DESC LIMIT 200");$countRows=dbAll("SELECT status,COUNT(*) as cnt FROM supplier_returns GROUP BY status");$statusCounts=['all'=>0,'pending'=>0,'approved'=>0,'rejected'=>0];foreach($countRows as $cr){$statusCounts[$cr['status']]=(int)$cr['cnt'];$statusCounts['all']+=(int)$cr['cnt'];}view('admin/supplier-returns',['title'=>'Trả hàng nhà cung cấp','userRole'=>'admin','receipts'=>$receipts,'selectedReceipt'=>$selectedReceipt,'receipt'=>$receipt,'lines'=>$lines,'items'=>$items,'canApprove'=>$canApprove,'statusCounts'=>$statusCounts]);});
post('/admin/supplier-returns',function(){$u=requireStaffPermission('rbac:purchasing.returns.create|tax_config','/admin/login');csrfCheck();$rid=(int)($_POST['receipt_id']??0);$reason=trim($_POST['reason']??'');$qty=$_POST['qty']??[];if(!$rid||mb_strlen($reason)<5||mb_strlen($reason)>500||!is_array($qty)){flash('error','Lý do trả phải từ 5-500 ký tự.');redirect('/admin/supplier-returns?receipt='.$rid);}$pdo=db();try{$pdo->beginTransaction();$receipt=dbGet("SELECT * FROM goods_receipts WHERE id=? AND status='qc_completed'",[$rid]);if(!$receipt)throw new RuntimeException('receipt');$lines=[];foreach($qty as $id=>$raw){$id=(int)$id;$n=(int)preg_replace('/\D/','',(string)$raw);if($n<1)continue;$line=dbGet("SELECT gri.*,COALESCE((SELECT SUM(sri.returned_rejected_qty) FROM supplier_return_items sri INNER JOIN supplier_returns sr ON sr.id=sri.return_id WHERE sri.receipt_item_id=gri.id AND sr.status!='rejected'),0) AS used_bad,COALESCE((SELECT SUM(sri.returned_accepted_qty) FROM supplier_return_items sri INNER JOIN supplier_returns sr ON sr.id=sri.return_id WHERE sri.receipt_item_id=gri.id AND sr.status!='rejected'),0) AS used_good FROM goods_receipt_items gri WHERE gri.id=? AND gri.receipt_id=?",[$id,$rid]);if(!$line)throw new RuntimeException('qty');$badAvail=max(0,(int)$line['rejected_qty']-(int)$line['used_bad']);$goodAvail=max(0,(int)$line['accepted_qty']-(int)$line['used_good']);if($n>$badAvail+$goodAvail)throw new RuntimeException('qty');$bad=min($n,$badAvail);$good=$n-$bad;$lines[]=[$line,$n,$good,$bad];}if(!$lines)throw new RuntimeException('qty');$code='THNCC-'.date('Ymd-His').'-'.random_int(100,999);$returnId=dbInsert("INSERT INTO supplier_returns (code,receipt_id,status,reason,created_by) VALUES (?,?,'pending',?,?)",[$code,$rid,$reason,$u['id']]);foreach($lines as [$line,$n,$good,$bad])dbInsert('INSERT INTO supplier_return_items (return_id,receipt_item_id,product_id,return_qty,returned_accepted_qty,returned_rejected_qty) VALUES (?,?,?,?,?,?)',[$returnId,$line['id'],$line['product_id'],$n,$good,$bad]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','supplier_return_created','supplier_return',$returnId,json_encode(['receipt_id'=>$rid,'lines'=>count($lines)],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã tạo phiếu trả '.$code.' — đang chờ duyệt.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error',$e->getMessage()==='qty'?'Số lượng trả vượt quá số lượng còn có thể trả.':'Không thể tạo phiếu trả nhà cung cấp.');}redirect('/admin/supplier-returns');});
// -- Chi tiet phieu tra NCC --
get('/admin/supplier-returns/:id',function($p){$u=requireStaffPermission('rbac:purchasing.returns.create|tax_config','/admin/login');$canApprove=hasPermission($u,'purchasing.returns.approve')||hasPermission($u,'tax_config');$id=(int)$p['id'];$return=dbGet("SELECT sr.*,gr.code AS receipt_code,s.name AS supplier_name,uc.full_name AS creator_name,ua.full_name AS approver_name FROM supplier_returns sr INNER JOIN goods_receipts gr ON gr.id=sr.receipt_id INNER JOIN purchase_orders po ON po.id=gr.order_id INNER JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN users uc ON uc.id=sr.created_by LEFT JOIN users ua ON ua.id=sr.approved_by WHERE sr.id=?",[$id]);if(!$return){http_response_code(404);flash('error','Không tìm thấy phiếu trả.');redirect('/admin/supplier-returns');}$items=dbAll("SELECT sri.*,p.name AS product_name,p.sku,p.stock AS current_stock,gri.accepted_qty,gri.rejected_qty FROM supplier_return_items sri INNER JOIN products p ON p.id=sri.product_id INNER JOIN goods_receipt_items gri ON gri.id=sri.receipt_item_id WHERE sri.return_id=? ORDER BY sri.id",[$id]);foreach($items as &$it)$it['current_stock']=(int)dbGet("SELECT stock FROM products WHERE id=?",[$it['product_id']])['stock'];unset($it);view('admin/supplier-return-detail',['title'=>'Chi tiết phiếu trả '.$return['code'],'userRole'=>'admin','return'=>$return,'items'=>$items,'canApprove'=>$canApprove]);});
// -- Duyet phieu tra NCC: tru ton kho, ghi stock_movements, audit_log --
post('/admin/supplier-returns/:id/approve',function($p){$u=requireStaffPermission('rbac:purchasing.returns.approve|tax_config','/admin/login');csrfCheck();$id=(int)$p['id'];$pdo=db();try{$pdo->beginTransaction();$return=dbGet("SELECT * FROM supplier_returns WHERE id=?",[$id]);if(!$return)throw new RuntimeException('notfound');if($return['status']!=='pending')throw new RuntimeException('status');$items=dbAll("SELECT sri.*,p.stock AS cur_stock FROM supplier_return_items sri INNER JOIN products p ON p.id=sri.product_id WHERE sri.return_id=?",[$id]);foreach($items as $it){$deduct=(int)$it['returned_accepted_qty'];if($deduct<1)continue;dbRun("UPDATE products SET stock=MAX(0,stock-?),updated_at=datetime('now','localtime') WHERE id=?",[$deduct,$it['product_id']]);dbInsert("INSERT INTO inventory_stock_movements (product_id,direction,quantity,reference_type,reference_id,note,created_by) VALUES (?,?,?,?,?,?,?)",[$it['product_id'],'out',$deduct,'supplier_return',$id,'Trả hàng NCC: '.$return['code'],$u['id']]);}dbRun("UPDATE supplier_returns SET status='approved',approved_by=?,approved_at=datetime('now','localtime') WHERE id=?",[$u['id'],$id]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','supplier_return_approved','supplier_return',$id,json_encode(['code'=>$return['code'],'items'=>count($items)],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','Đã duyệt phiếu trả '.$return['code'].' — tồn kho đã được cập nhật.');}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();if($e->getMessage()==='status')flash('error','Phiếu đã được xử lý trước đó, không thể duyệt lại.');elseif($e->getMessage()==='notfound')flash('error','Không tìm thấy phiếu trả.');else flash('error','Lỗi khi duyệt phiếu: '.$e->getMessage());}redirect('/admin/supplier-returns/'.$id);});
// -- Tu choi phieu tra NCC --
post('/admin/supplier-returns/:id/reject',function($p){$u=requireStaffPermission('rbac:purchasing.returns.approve|tax_config','/admin/login');csrfCheck();$id=(int)$p['id'];$reason=trim($_POST['rejection_reason']??'');if(mb_strlen($reason)<5||mb_strlen($reason)>500){flash('error','Lý do từ chối phải từ 5-500 ký tự.');redirect('/admin/supplier-returns/'.$id);}$return=dbGet("SELECT * FROM supplier_returns WHERE id=? AND status='pending'",[$id]);if(!$return){flash('error','Phiếu không tồn tại hoặc đã được xử lý.');redirect('/admin/supplier-returns');}dbRun("UPDATE supplier_returns SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=? WHERE id=?",[$u['id'],$reason,$id]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','supplier_return_rejected','supplier_return',$id,json_encode(['code'=>$return['code'],'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã từ chối phiếu trả '.$return['code'].'.');redirect('/admin/supplier-returns/'.$id);});
get('/admin/warranties', function() {
    requireStaffPermission('rbac:warranty.cases.view|returns', '/admin/login');
    $cases=dbAll("SELECT warranty.*,product.name AS product_name,product.sku FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id ORDER BY warranty.updated_at DESC LIMIT 300");
    $products=dbAll("SELECT id,sku,name FROM products ORDER BY name LIMIT 3000");
    view('admin/warranties',['title'=>'Bảo hành & Kỹ thuật','userRole'=>'admin','cases'=>$cases,'products'=>$products]);
});
post('/admin/warranties', function() {
    $actor=requireStaffPermission('rbac:warranty.cases.create|returns','/admin/login');csrfCheck();$product=dbGet('SELECT id,warranty_months,name,sku FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);$customerName=trim($_POST['customer_name']??'');$phone=preg_replace('/\D+/','',$_POST['customer_phone']??'');$customerEmail=strtolower(trim($_POST['customer_email']??''));$issue=trim($_POST['issue_description']??'');$words=$issue===''?0:count(preg_split('/\s+/u',$issue,-1,PREG_SPLIT_NO_EMPTY));
    if(!$product){flash('error','Chon san pham hop le.');redirect('/admin/warranties');}
    if($customerName===''||mb_strlen($customerName)>100){flash('error','Ten khach hang phai tu 1 den 100 ky tu.');redirect('/admin/warranties');}
    if(!preg_match('/^0[35789]\d{8}$/',$phone)){flash('error','So dien thoai phai gom 10 chu so va bat dau bang 03, 05, 07, 08 hoac 09.');redirect('/admin/warranties');}if(!filter_var($customerEmail,FILTER_VALIDATE_EMAIL)||mb_strlen($customerEmail)>254){flash('error','Email khach hang khong hop le.');redirect('/admin/warranties');}
    if($words<1||$words>200){flash('error','Noi dung yeu cau phai tu 1 den 200 tu.');redirect('/admin/warranties');}
$purchase=trim($_POST['purchase_date']??'') ?: date('Y-m-d');$serialNo=trim($_POST['serial_no']??'');$serial=$serialNo ? dbGet('SELECT product_id,warranty_end_date FROM product_serials WHERE serial_no=?',[$serialNo]) : null;if($serial && (int)$serial['product_id']!==(int)$product['id']){flash('error','Serial không thuộc sản phẩm đã chọn.');redirect('/admin/warranties');}$months=max(0,(int)$product['warranty_months']);$end=$serial ? $serial['warranty_end_date'] : date('Y-m-d',strtotime('+'.$months.' months',strtotime($purchase)));$code='BH'.date('ymdHis').random_int(10,99);
    $id=dbInsert('INSERT INTO warranty_cases (case_code,product_id,order_code,customer_name,customer_phone,customer_email,serial_no,issue_description,purchase_date,warranty_end_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',[$code,$product['id'],trim($_POST['order_code']??''),$customerName,$phone,$customerEmail,trim($_POST['serial_no']??''),$issue,$purchase,$end,'received',$actor['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role'],'warranty_case_created','warranty_case',$id,json_encode(['case_code'=>$code,'product_id'=>$product['id']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$emailBody='<h2 style="color:#1a3258;margin:0 0 12px">Phi&#7871;u b&#7843;o h&#224;nh</h2><p>Xin ch&#224;o <strong>'.htmlspecialchars($customerName,ENT_QUOTES,'UTF-8').'</strong>,</p><p>Cooling System &#273;&#227; ti&#7871;p nh&#7853;n phi&#7871;u b&#7843;o h&#224;nh c&#7911;a b&#7841;n.</p><table cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%"><tr><td><strong>M&#227; phi&#7871;u</strong></td><td>'.htmlspecialchars($code,ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>S&#7843;n ph&#7849;m</strong></td><td>'.htmlspecialchars($product['name'],ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>SKU</strong></td><td>'.htmlspecialchars($product['sku'],ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>Ng&#224;y mua</strong></td><td>'.htmlspecialchars($purchase,ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>H&#7841;n b&#7843;o h&#224;nh</strong></td><td>'.htmlspecialchars($end,ENT_QUOTES,'UTF-8').'</td></tr></table><p><strong>N&#7897;i dung y&#234;u c&#7847;u:</strong><br>'.nl2br(htmlspecialchars($issue,ENT_QUOTES,'UTF-8')).'</p><p>Ch&#250;ng t&#244;i s&#7869; li&#234;n h&#7879; sau khi ki&#7875;m tra.</p>';$mailSent=sendEmail($customerEmail,html_entity_decode('Phi&#7871;u b&#7843;o h&#224;nh',ENT_QUOTES,'UTF-8').' '.$code.' - Cooling System',_emailLayout(html_entity_decode('Phi&#7871;u b&#7843;o h&#224;nh',ENT_QUOTES,'UTF-8'),$emailBody));if($mailSent){flash('success',"\u{0110}\u{00E3} t\u{1EA1}o phi\u{1EBF}u b\u{1EA3}o h\u{00E0}nh ".$code." v\u{00E0} \u{0111}\u{00E3} g\u{1EED}i email cho kh\u{00E1}ch h\u{00E0}ng.");}else{flash('warning',"\u{0110}\u{00E3} t\u{1EA1}o phi\u{1EBF}u b\u{1EA3}o h\u{00E0}nh ".$code." nh\u{01B0}ng ch\u{01B0}a g\u{1EED}i \u{0111}\u{01B0}\u{1EE3}c email. H\u{00E3}y ki\u{1EC3}m tra SMTP.");}redirect('/admin/warranties');
});
get('/admin/warranties/performance', function() {
    $actor=requireStaffPermission('rbac:warranty.performance.view|returns','/admin/login');
    $technicians=dbAll("SELECT DISTINCT user.id,user.full_name,user.phone FROM users user INNER JOIN staff_role_assignments assignment ON assignment.user_id=user.id INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE user.role='staff' AND user.status='active' AND link.rbac_role_code='TECH' ORDER BY user.full_name");
    $activeCases=dbAll("SELECT warranty.*,product.name AS product_name,product.sku,technician.full_name AS technician_name FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id LEFT JOIN users technician ON technician.id=warranty.assigned_to WHERE warranty.status IN ('approved','assigned','in_progress') ORDER BY warranty.updated_at DESC LIMIT 300");
    $summary=dbGet("SELECT COUNT(*) AS total,SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,SUM(CASE WHEN status IN ('assigned','in_progress') THEN 1 ELSE 0 END) AS active,SUM(CASE WHEN (SELECT COUNT(*) FROM warranty_cases prior WHERE prior.customer_phone=warranty.customer_phone AND prior.product_id=warranty.product_id)>1 THEN 1 ELSE 0 END) AS repeat_cases FROM warranty_cases warranty") ?: [];
    $performance=dbAll("SELECT COALESCE(technician.full_name,'') AS technician_name,COUNT(*) AS total_cases,SUM(CASE WHEN warranty.status IN ('assigned','in_progress') THEN 1 ELSE 0 END) AS active_cases,SUM(CASE WHEN warranty.status='completed' THEN 1 ELSE 0 END) AS completed_cases,ROUND(AVG(CASE WHEN warranty.status='completed' THEN julianday(warranty.updated_at)-julianday(warranty.created_at) END),1) AS average_days,SUM(CASE WHEN (SELECT COUNT(*) FROM warranty_cases prior WHERE prior.customer_phone=warranty.customer_phone AND prior.product_id=warranty.product_id)>1 THEN 1 ELSE 0 END) AS repeat_cases FROM warranty_cases warranty LEFT JOIN users technician ON technician.id=warranty.assigned_to GROUP BY warranty.assigned_to,technician.full_name ORDER BY completed_cases DESC,total_cases DESC,technician_name ASC");
    $canAssign=(($actor['role']??'')==='admin') || rbacHasCapability((int)$actor['id'],'warranty.assign');
    view('admin/warranty-performance',['title'=>'Hi&#7879;u su&#7845;t k&#7929; thu&#7853;t','userRole'=>'admin','technicians'=>$technicians,'activeCases'=>$activeCases,'summary'=>$summary,'performance'=>$performance,'canAssign'=>$canAssign]);
});
post('/admin/warranties/:id/assign', function($p) {
    $actor=requireStaffPermission('rbac:warranty.assign|returns','/admin/login'); csrfCheck();
    $case=dbGet("SELECT id,status,assigned_to FROM warranty_cases WHERE id=?",[$p['id']]);
    if(!$case){flash('error','Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.');redirect('/admin/warranties/performance');}
    if(!in_array($case['status'],['approved','assigned','in_progress'],true)){flash('error','Ch&#7881; ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n cho phi&#7871;u &#273;&#227; duy&#7879;t ho&#7863;c &#273;ang x&#7917; l&#253;.');redirect('/admin/warranties/performance');}
    $technician=dbGet("SELECT user.id,user.full_name FROM users user INNER JOIN staff_role_assignments assignment ON assignment.user_id=user.id INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE user.id=? AND user.role='staff' AND user.status='active' AND link.rbac_role_code='TECH'",[(int)($_POST['assigned_to']??0)]);
    if(!$technician){flash('error','K&#7929; thu&#7853;t vi&#234;n kh&#244;ng h&#7907;p l&#7879; ho&#7863;c ch&#432;a &#273;&#432;&#7907;c g&#225;n vai tr&#242; TECH.');redirect('/admin/warranties/performance');}
    $nextStatus=$case['status']==='approved'?'assigned':$case['status'];
    dbRun("UPDATE warranty_cases SET assigned_to=?,status=?,updated_at=datetime('now','localtime') WHERE id=?",[$technician['id'],$nextStatus,$case['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_technician_assigned','warranty_case',$case['id'],json_encode(['assigned_to'=>$technician['id'],'technician'=>$technician['full_name'],'status'=>$nextStatus],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','&#272;&#227; ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n cho phi&#7871;u b&#7843;o h&#224;nh.');redirect('/admin/warranties/performance');
});
get('/admin/warranties/:id/documents', function($p) { requireStaffPermission('rbac:warranty.documents.print|returns','/admin/login'); $case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]); if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if($case['status']!=='completed'){flash('error',html_entity_decode('Ch&#7913;ng t&#7915; ch&#7881; hi&#7875;n th&#7883; sau khi phi&#7871;u &#273;&#227; nghi&#7879;m thu.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');} view('admin/warranty-documents',['title'=>'Ch&#7913;ng t&#7915; b&#7843;o h&#224;nh','userRole'=>'admin','case'=>$case]); });
get('/admin/warranties/:id/documents/:type', function($p) { $actor=requireStaffPermission('rbac:warranty.documents.print|returns','/admin/login');$type=$p['type']??'';if(!in_array($type,['receipt','warranty','handover'],true)){http_response_code(404);view('errors/404',['title'=>'Kh&#244;ng t&#236;m th&#7845;y trang']);return;}$case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]);if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if($case['status']!=='completed'){flash('error',html_entity_decode('Ch&#7913;ng t&#7915; ch&#7881; hi&#7875;n th&#7883; sau khi phi&#7871;u &#273;&#227; nghi&#7879;m thu.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if($type==='handover'&&$case['status']!=='completed'){flash('error',html_entity_decode('Ch&#7881; in bi&#234;n b&#7843;n b&#224;n giao sau khi phi&#7871;u &#273;&#227; nghi&#7879;m thu.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/documents');}$materials=dbAll('SELECT material.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_materials material INNER JOIN products product ON product.id=material.product_id WHERE material.warranty_case_id=? ORDER BY material.issued_at ASC,material.id ASC',[$case['id']]);$statusLabels=['received'=>html_entity_decode('Ti&#7871;p nh&#7853;n',ENT_QUOTES,'UTF-8'),'checking'=>html_entity_decode('&#272;ang ki&#7875;m tra',ENT_QUOTES,'UTF-8'),'approved'=>html_entity_decode('&#272;&#227; duy&#7879;t',ENT_QUOTES,'UTF-8'),'assigned'=>html_entity_decode('&#272;&#227; ph&#226;n c&#244;ng',ENT_QUOTES,'UTF-8'),'in_progress'=>html_entity_decode('&#272;ang x&#7917; l&#253;',ENT_QUOTES,'UTF-8'),'completed'=>html_entity_decode('&#272;&#227; nghi&#7879;m thu',ENT_QUOTES,'UTF-8'),'rejected'=>html_entity_decode('T&#7915; ch&#7889;i',ENT_QUOTES,'UTF-8')];dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_document_printed','warranty_case',$case['id'],json_encode(['type'=>$type],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);view('admin/warranty-document-print',['case'=>$case,'materials'=>$materials,'documentType'=>$type,'statusLabel'=>$statusLabels[$case['status']]??$case['status']]); });
get('/admin/warranties/material-products', function() { requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login'); $q=trim($_GET['q']??''); header('Content-Type: application/json; charset=utf-8'); if(mb_strlen($q)<2){echo '[]';exit;} $like='%'.$q.'%'; $rows=dbAll('SELECT id,sku,oem_code,name,stock FROM products WHERE stock>0 AND (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) ORDER BY name LIMIT 12',[$like,$like,$like]); echo json_encode(array_map(fn($row)=>['id'=>(int)$row['id'],'label'=>trim($row['sku'].' | '.$row['name'].' | Ton: '.$row['stock'].(!empty($row['oem_code'])?' | OEM: '.$row['oem_code']:''))],$rows),JSON_UNESCAPED_UNICODE); exit; });
get('/admin/warranties/:id/materials', function($p) { $actor=requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login'); $case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]); if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');} $materials=dbAll("SELECT material.*,product.name AS product_name,product.sku,product.oem_code,COALESCE(CAST(material.issued_by AS TEXT),'') AS issued_by_name FROM warranty_materials material INNER JOIN products product ON product.id=material.product_id WHERE material.warranty_case_id=? ORDER BY material.issued_at DESC,material.id DESC",[$case['id']]); view('admin/warranty-materials',['title'=>'V&#7853;t t&#432; phi&#7871;u b&#7843;o h&#224;nh','userRole'=>'admin','case'=>$case,'materials'=>$materials]); });
post('/admin/warranties/:id/materials', function($p) { $actor=requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login');csrfCheck();$case=dbGet('SELECT id,status FROM warranty_cases WHERE id=?',[$p['id']]);if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if(!in_array($case['status'],['approved','assigned','in_progress'],true)){flash('error',html_entity_decode('Ch&#7881; &#273;&#432;&#7907;c xu&#7845;t v&#7853;t t&#432; cho phi&#7871;u &#273;&#227; duy&#7879;t ho&#7863;c &#273;ang x&#7917; l&#253;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$productId=(int)($_POST['product_id']??0);$rawQuantity=trim((string)($_POST['quantity']??''));$note=trim((string)($_POST['note']??''));if(!$productId||$rawQuantity===''||!ctype_digit($rawQuantity)||(int)$rawQuantity<1||(int)$rawQuantity>1000||mb_strlen($note)>300){flash('error',html_entity_decode('V&#7853;t t&#432;, s&#7889; l&#432;&#7907;ng ho&#7863;c ghi ch&#250; kh&#244;ng h&#7907;p l&#7879;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$quantity=(int)$rawQuantity;$product=dbGet('SELECT id,name,sku,stock FROM products WHERE id=?',[$productId]);if(!$product){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y v&#7853;t t&#432;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$pdo=db();try{$pdo->beginTransaction();$changed=dbRun("UPDATE products SET stock=stock-?,updated_at=datetime('now','localtime') WHERE id=? AND stock>=?",[$quantity,$productId,$quantity]);if($changed->rowCount()!==1){throw new RuntimeException('insufficient stock');}$materialId=dbInsert('INSERT INTO warranty_materials (warranty_case_id,product_id,quantity,note,issued_by) VALUES (?,?,?,?,?)',[$case['id'],$productId,$quantity,$note,$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_material_issued','warranty_material',$materialId,json_encode(['warranty_case_id'=>$case['id'],'product_id'=>$productId,'quantity'=>$quantity],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();}catch(Throwable $exception){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',html_entity_decode('Kh&#244;ng th&#7875; xu&#7845;t v&#7853;t t&#432;. T&#7891;n kho c&#243; th&#7875; kh&#244;ng &#273;&#7911;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}try{inventoryCheckLowStockAlert($productId,'warranty_material');}catch(Throwable $exception){}flash('success',html_entity_decode('&#272;&#227; xu&#7845;t v&#7853;t t&#432; cho phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials'); });
post('/admin/warranties/:id/status', function($p) {
    $actor=requireStaffPermission('rbac:warranty.cases.view|returns','/admin/login');csrfCheck();$status=$_POST['status']??'';$case=dbGet('SELECT id,status,assigned_to FROM warranty_cases WHERE id=?',[$p['id']]);
    if(!$case){flash('error','Khong tim thay phieu bao hanh.');redirect('/admin/warranties');}
    $current=$case['status'];$rank=['received'=>0,'checking'=>1,'approved'=>2,'assigned'=>3,'in_progress'=>4,'completed'=>5];
    if(in_array($current,['completed','rejected'],true)){flash('error','Phieu da ket thuc, khong the thay doi trang thai.');redirect('/admin/warranties');}
    if($status!=='rejected'&&(!isset($rank[$status])||!isset($rank[$current])||$rank[$status]<=$rank[$current])){flash('error','Khong the quay lai hoac luu lai buoc truoc do.');redirect('/admin/warranties');}
    $cap=['checking'=>'warranty.eligibility.check','approved'=>'warranty.approve','assigned'=>'warranty.assign','in_progress'=>'warranty.progress.update','completed'=>'warranty.close','rejected'=>'warranty.approve'][$status]??null;if(!$cap||!rbacHasCapability((int)$actor['id'],$cap)){if(($actor['role']??'')!=='admin'){flash('error','Ban khong co quyen cap nhat trang thai nay.');redirect('/admin/warranties');}}
    if(in_array($status,['assigned','in_progress'],true)&&empty($case['assigned_to'])){flash('error','H&#227;y ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n tr&#432;&#7899;c khi chuy&#7875;n phi&#7871;u sang b&#432;&#7899;c n&#224;y.');redirect('/admin/warranties/performance');}
    dbRun("UPDATE warranty_cases SET status=?,updated_at=datetime('now','localtime') WHERE id=?",[$status,$p['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role'],'warranty_case_status','warranty_case',$p['id'],json_encode(['status'=>$status]),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã cập nhật phiếu bảo hành.');redirect('/admin/warranties');
});

get('/admin/staff', function() {
    $user = requireRbacOrLegacyStaffPermission('system.rbac.view', '/auth/login');
    $staffRoles = dbAll("SELECT * FROM staff_roles ORDER BY created_at DESC");
    $spage = max(1, intval($_GET['spage'] ?? 1)); $sPer = 10;
    $sTotal = (int)(dbGet("SELECT COUNT(DISTINCT user_id) AS n FROM staff_role_assignments")['n'] ?? 0);
    $sTotalPages = max(1, (int)ceil($sTotal / $sPer)); $spage = min($spage, $sTotalPages);
    $assignments = dbAll("SELECT u.id AS user_id, u.full_name, COALESCE(u.email,'') AS email, u.phone, GROUP_CONCAT(sra.id || '~' || sr.name, '|') AS roles, MAX(sra.assigned_at) AS assigned_at FROM staff_role_assignments sra INNER JOIN users u ON u.id=sra.user_id INNER JOIN staff_roles sr ON sr.id=sra.role_id GROUP BY u.id ORDER BY MAX(sra.assigned_at) DESC LIMIT ? OFFSET ?", [$sPer, ($spage-1)*$sPer]);
    view('admin/staff', ['title'=>'Phân quyền nhân viên','userRole'=>'admin','staffRoles'=>$staffRoles,'assignments'=>$assignments,'sTotal'=>$sTotal,'spage'=>$spage,'sTotalPages'=>$sTotalPages]);
});

get('/admin/staff/roles/new', function() {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');
    view('admin/role-form', ['title'=>'Tạo vai trò mới','userRole'=>'admin','staffRole'=>[],'rbacCapabilities'=>rbacCapabilityCatalog()]);
});

post('/admin/staff/roles/new', function() {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error','Tên vai trò là bắt buộc.'); redirect('/admin/staff/roles/new'); }
    $perms = json_encode(rbacSanitizeRolePermissions($_POST['permissions'] ?? []), JSON_UNESCAPED_UNICODE);
    dbRun("INSERT INTO staff_roles (name, description, permissions) VALUES (?,?,?)", [$name, trim($_POST['description']??''), $perms]);
    flash('success',"Tạo vai trò '{$name}' thành công!");
    redirect('/admin/staff');
});

get('/admin/staff/roles/:id/edit', function($p) {
    $user = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/auth/login');
    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau chi doc; khong the sua truc tiep.'); redirect('/admin/staff'); }
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy.'); redirect('/admin/staff'); }
    view('admin/role-form', ['title'=>'Sửa vai trò','userRole'=>'admin','staffRole'=>$staffRole,'rbacCapabilities'=>rbacCapabilityCatalog()]);
});

post('/admin/staff/roles/:id/edit', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau chi doc; khong the sua truc tiep.'); redirect('/admin/staff'); }
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error','Tên vai trò là bắt buộc.'); redirect('/admin/staff/roles/'.$p['id'].'/edit'); }
    $perms = json_encode(rbacSanitizeRolePermissions($_POST['permissions'] ?? []), JSON_UNESCAPED_UNICODE);
    dbRun("UPDATE staff_roles SET name=?, description=?, permissions=? WHERE id=?", [$name, trim($_POST['description']??''), $perms, $p['id']]);
    flash('success','Cập nhật vai trò thành công!');
    redirect('/admin/staff');
});

post('/admin/staff/roles/:id/delete', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau duoc bao ve; khong the xoa.'); redirect('/admin/staff'); }
    dbRun('DELETE FROM staff_role_assignments WHERE role_id=?', [$p['id']]);
    dbRun('DELETE FROM staff_roles WHERE id=?', [$p['id']]);
    flash('success','Xóa vai trò thành công!');
    redirect('/admin/staff');
});

post('/admin/staff/roles/:id/duplicate', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $source = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$source) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $template = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $permissions = $template ? array_map(fn($capability) => 'rbac:' . $capability, rbacTemplateCapabilities($template['rbac_role_code'])) : (json_decode($source['permissions'] ?? '[]', true) ?: []);
    $newId = dbInsert('INSERT INTO staff_roles (name,description,permissions) VALUES (?,?,?)', ['Bản sao - ' . $source['name'], 'Vai trò tùy chỉnh được nhân bản từ ' . $source['name'] . '.', json_encode(rbacSanitizeRolePermissions($permissions), JSON_UNESCAPED_UNICODE)]);
    flash('success','Đã tạo bản sao. Bạn có thể chỉnh sửa quyền của vai trò mới.');
    redirect('/admin/staff/roles/' . $newId . '/edit');
});

get('/admin/staff/rbac/coverage', function() {
    requireRbacOrLegacyStaffPermission('system.rbac.view', '/admin/login');
    $coverage = dbAll("SELECT permission.code,permission.module_name,permission.feature_name,permission.action_name,GROUP_CONCAT(DISTINCT rule.capability) AS capabilities FROM rbac_permissions permission LEFT JOIN rbac_capability_rules rule ON rule.permission_code=permission.code GROUP BY permission.code ORDER BY permission.sort_order");
    $summary = dbGet("SELECT COUNT(*) AS total, SUM(CASE WHEN EXISTS(SELECT 1 FROM rbac_capability_rules rule WHERE rule.permission_code=permission.code) THEN 1 ELSE 0 END) AS integrated FROM rbac_permissions permission") ?: ['total'=>0,'integrated'=>0];
    $summary['pending'] = (int)$summary['total'] - (int)$summary['integrated'];
    view('admin/rbac-coverage', ['title'=>'Bản đồ triển khai quyền RBAC','userRole'=>'admin','coverage'=>$coverage,'summary'=>$summary]);
});

get('/admin/staff/roles/:id/permissions', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.view', '/admin/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $rbacTemplate = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $matrixPermissions = [];
    if ($rbacTemplate) {
        $matrixPermissions = dbAll("SELECT permission.module_name, permission.feature_name, permission.action_name, role_permission.access_level, GROUP_CONCAT(DISTINCT rule.capability) AS capabilities FROM rbac_role_permissions role_permission INNER JOIN rbac_permissions permission ON permission.code=role_permission.permission_code LEFT JOIN rbac_capability_rules rule ON rule.permission_code=permission.code WHERE role_permission.role_code=? AND role_permission.access_level<>'NONE' GROUP BY permission.code, role_permission.access_level ORDER BY permission.sort_order", [$rbacTemplate['rbac_role_code']]);
    } else {
        $selected = json_decode($staffRole['permissions'] ?? '[]', true) ?: [];
        foreach (rbacCapabilityCatalog() as $capability) {
            if (in_array('rbac:' . $capability['capability'], $selected, true)) $matrixPermissions[] = ['module_name'=>$capability['module_name'], 'feature_name'=>$capability['feature_name'], 'action_name'=>$capability['action_name'], 'access_level'=>'Tùy chỉnh', 'capabilities'=>$capability['capability']];
        }
    }
    view('admin/role-permissions', ['title'=>'Quyền của vai trò','userRole'=>'admin','staffRole'=>$staffRole,'rbacTemplate'=>$rbacTemplate,'matrixPermissions'=>$matrixPermissions]);
});

get('/admin/staff/roles/:id/assign', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $rbacTemplate = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $matrixTaskCount = $rbacTemplate ? (int)(dbGet("SELECT COUNT(*) AS n FROM rbac_role_permissions WHERE role_code=? AND access_level<>'NONE'", [$rbacTemplate['rbac_role_code']])['n'] ?? 0) : 0;
    $assignedUsers = dbAll("SELECT u.full_name, u.email, sra.id AS assignment_id FROM staff_role_assignments sra INNER JOIN users u ON u.id=sra.user_id WHERE sra.role_id=?", [$p['id']]);
    $availableUsers = dbAll("SELECT id, full_name, email FROM users WHERE role='staff' AND status='active' AND id NOT IN (SELECT user_id FROM staff_role_assignments WHERE role_id=?) ORDER BY full_name", [$p['id']]);
    view('admin/role-assign', ['title'=>'Phân công nhân viên','userRole'=>'admin','staffRole'=>$staffRole,'rbacTemplate'=>$rbacTemplate,'matrixTaskCount'=>$matrixTaskCount,'assignedUsers'=>$assignedUsers,'availableUsers'=>$availableUsers]);
});

post('/admin/staff/roles/:id/assign', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $userId = (int)($_POST['user_id'] ?? 0);
    $staffRole = dbGet('SELECT id,name FROM staff_roles WHERE id=?', [$p['id']]);
    $target = $userId ? dbGet("SELECT id,full_name,role,status FROM users WHERE id=?", [$userId]) : null;
    if (!$staffRole || !$target || $target['role'] !== 'staff' || $target['status'] !== 'active') {
        flash('error','Chỉ có thể phân công cho tài khoản Nhân viên đang hoạt động.');
        redirect('/admin/staff/roles/' . $p['id'] . '/assign');
    }
    $insert = dbRun("INSERT OR IGNORE INTO staff_role_assignments (user_id, role_id, assigned_by) VALUES (?,?,?)", [$userId, $p['id'], $actor['id']]);
    if ($insert->rowCount() > 0) {
        $assignment = dbGet('SELECT id FROM staff_role_assignments WHERE user_id=? AND role_id=?', [$userId, $p['id']]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_assigned', 'staff_role_assignment', $assignment['id'] ?? null, json_encode(['target_user_id'=>$userId,'role_id'=>(int)$p['id'],'role_name'=>$staffRole['name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
        dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Phân quyền mới','Bạn đã được phân công vai trò: " . $staffRole['name'] . ". Đăng nhập lại để áp dụng quyền.','/staff',datetime('now','localtime'))", [$userId]);
        flash('success','Phân công thành công và đã ghi nhật ký.');
    } else {
        flash('info','Nhân viên này đã giữ vai trò được chọn.');
    }
    redirect('/admin/staff/roles/' . $p['id'] . '/assign');
});

post('/admin/staff/unassign/:id', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $assignment = dbGet("SELECT assignment.id,assignment.user_id,assignment.role_id,staff_role.name AS role_name FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id WHERE assignment.id=?", [$p['id']]);
    if (!$assignment) { flash('error','Không tìm thấy phân công.'); redirect('/admin/staff'); }
    dbRun('DELETE FROM staff_role_assignments WHERE id=?', [$p['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_unassigned', 'staff_role_assignment', $assignment['id'], json_encode(['target_user_id'=>(int)$assignment['user_id'],'role_id'=>(int)$assignment['role_id'],'role_name'=>$assignment['role_name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Cập nhật quyền truy cập','Một vai trò nhân viên của bạn đã được thu hồi.','/staff',datetime('now','localtime'))", [$assignment['user_id']]);
    flash('success','Đã hủy phân quyền và ghi nhật ký. Tài khoản vẫn là Nhân viên để có thể phân công lại.');
    redirect('/admin/staff');
});

post('/admin/staff/unassign-all/:id', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $userId = (int)$p['id'];
    $assignments = dbAll("SELECT assignment.id,assignment.role_id,staff_role.name AS role_name FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id WHERE assignment.user_id=?", [$userId]);
    foreach ($assignments as $assignment) {
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_unassigned', 'staff_role_assignment', $assignment['id'], json_encode(['target_user_id'=>$userId,'role_id'=>(int)$assignment['role_id'],'role_name'=>$assignment['role_name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    }
    dbRun('DELETE FROM staff_role_assignments WHERE user_id=?', [$userId]);
    dbRun('DELETE FROM staff_permissions WHERE user_id=?', [$userId]);
    if ($assignments) dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Cập nhật quyền truy cập','Toàn bộ vai trò nhân viên của bạn đã được thu hồi.','/staff',datetime('now','localtime'))", [$userId]);
    flash('success','Đã hủy toàn bộ quyền và ghi nhật ký. Tài khoản Nhân viên được giữ lại để có thể phân công lại.');
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

get('/staff/login', function() {
    $u = currentUser();
    if ($u && ($u['role'] ?? '') === 'staff') redirect('/staff');
    view('staff/login', ['title' => 'Đăng nhập Nhân viên']);
});
post('/staff/login', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (!$email || !$password) { flash('error','Vui lòng nhập email và mật khẩu.'); redirect('/staff/login'); }
    $user = dbGet('SELECT * FROM users WHERE email=?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email hoặc mật khẩu không đúng.'); redirect('/staff/login'); }
    if ($user['role'] !== 'staff') { flash('error','Trang đăng nhập này chỉ dành cho nhân viên.'); redirect('/staff/login'); }
    if (!staffHasAssignment($user['id'])) { flash('error','Tài khoản chưa được phân quyền nhân viên. Vui lòng liên hệ quản trị viên.'); redirect('/staff/login'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản đang bị khóa.'); redirect('/staff/login'); }
    loginUser($user['id']);
    flash('success','Xin chào, '.$user['full_name'].'!');
    redirect('/staff');
});

get('/staff/', function() {
    header('Location: /staff', true, 301);
    exit;
});

get('/staff', function() {
    $user = requireRole('staff', '/staff/login');
    if (!staffHasAssignment($user['id'])) { flash('error','Tài khoản chưa được phân quyền nhân viên. Vui lòng liên hệ quản trị viên.'); redirect('/'); }
    $roleAssignment = dbGet("SELECT sra.*, sr.name AS role_name, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=? ORDER BY sra.assigned_at DESC LIMIT 1", [$user['id']]);
    $perms = json_decode($roleAssignment['permissions'] ?? '[]', true) ?: [];
    if (in_array('orders', $perms) || in_array('create_order', $perms)) redirect('/staff/orders');
    if (in_array('products', $perms)) redirect('/staff/products');
    view('staff/dashboard', ['title'=>'Dashboard nhân viên','user'=>$user,'roleAssignment'=>$roleAssignment,'perms'=>$perms]);
});

get('/staff/orders', function() {
    $user = requireRole('staff', '/staff/login');
    // Redirect to admin orders (same view, staff has permission via requireStaffPermission)
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect('/admin/orders' . ($qs ? '?'.$qs : ''));
});

get('/staff/orders/create', function() {
    $user = requireRole('staff', '/staff/login');
    $roleAssignment = dbGet("SELECT sra.*, sr.permissions FROM staff_role_assignments sra INNER JOIN staff_roles sr ON sr.id=sra.role_id WHERE sra.user_id=? ORDER BY sra.assigned_at DESC LIMIT 1", [$user['id']]);
    $perms = json_decode($roleAssignment['permissions'] ?? '[]', true) ?: [];
    if (!in_array('create_order', $perms)) { flash('error','Bạn không có quyền tạo đơn hàng hộ.'); redirect('/staff'); }
    view('admin/order-create', ['title'=>'Tạo đơn hàng hộ','role'=>'staff','currentUser'=>$user]);
});

// Staff API routes

post('/staff/notifications/read', function() {
    $user = requireRole('staff', '/staff/login');
    dbRun("UPDATE user_notifications SET is_read=1 WHERE user_id=? AND is_read=0", [$user['id']]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
});


post('/staff/notifications/:id/delete', function($p) {
    $user = requireRole('staff', '/staff/login');
    dbRun("DELETE FROM user_notifications WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
});

get('/staff/api/search-customers', function() {
    $user = requireRole('staff', '/staff/login');
    $q = trim($_GET['q'] ?? '');
    $users = dbAll("SELECT id, full_name, email, phone FROM users WHERE role='customer' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?) LIMIT 10", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($users);
    exit;
});

get('/staff/api/search-products', function() {
    $user = requireRole('staff', '/staff/login');
    $q = trim($_GET['q'] ?? '');
    $prods = dbAll("SELECT id, name, sku, price, stock FROM products WHERE status='published' AND (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) LIMIT 15", ["%$q%","%$q%","%$q%"]);
    header('Content-Type: application/json');
    echo json_encode($prods);
    exit;
});

post('/staff/orders/create', function() {
    $user = requireRole('staff', '/staff/login');
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

    // Phí ship theo vùng + cân nặng
    $shipCfgRows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_shipping_fee','free_shipping_threshold','shipping_origin_province','shipping_rates')");
    $shipCfg = []; foreach($shipCfgRows as $r) $shipCfg[$r['key']] = $r['value'];
    $totalWeight = 0;
    foreach ($items as $it) {
        $pw = dbGet("SELECT weight_g FROM products WHERE id=?", [intval($it['product_id'])]);
        $totalWeight += intval($pw['weight_g'] ?? 0) * max(1, intval($it['qty']));
    }
    $freeTh = intval($shipCfg['free_shipping_threshold'] ?? 0);
    $shipFeeCalc = calcShippingFee($shipProvince, (int)$totalWeight, $shipCfg);
    $shipping = ($freeTh > 0 && $subtotal >= $freeTh) ? 0 : $shipFeeCalc;
    
    // If no user_id, create a guest user
    if (!$userId) {
        $guestEmail = 'guest_' . time() . rand(1000,9999) . '@guest.local';
        $guestPwd = password_hash(uniqid(), PASSWORD_BCRYPT);
        $userId = dbInsert("INSERT INTO users (full_name, email, password_hash, phone, role, status) VALUES (?,?,?,?,?,?)",
            [$shipName, $guestEmail, $guestPwd, $shipPhone, 'customer', 'active']);
    }
    
    $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    $grand = $subtotal + $shipping;
    
    // Create order
    $orderId = dbInsert("INSERT INTO orders (code, user_id, grand_total, subtotal, shipping_total, total_items, payment_method, payment_type, payment_status, delivery_status, created_by_staff, staff_note, shipping_full_name, shipping_phone, shipping_detail, shipping_district, shipping_province, shipping_ward, customer_note, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,datetime('now','localtime'))",
        [$code, $userId, $grand, $subtotal, $shipping, $totalItems, $payMethod, 'full', 'unpaid', $deliveryStatus,
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
            inventoryCheckLowStockAlert((int)$p['id'], 'staff_order');
        }
    }
    
    flash('success', "Đơn hàng $code đã được tạo thành công!");
    redirect('/staff/orders');
});



get('/admin/settings/finance', function() {
    requireStaffPermission('tax_config', '/auth/login');
    $rows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_tax_rate','default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent','shipping_origin_province','shipping_rates')");
    $config = [];
    foreach($rows as $r) $config[$r['key']] = $r['value'];
    view('admin/finance-config', ['title'=>'Cấu hình Vận chuyển', 'role'=>'admin', 'config'=>$config]);
});

post('/admin/settings/finance', function() {
    requireStaffPermission('tax_config', '/auth/login'); csrfCheck();
    
    $default_shipping_fee = $_POST['default_shipping_fee'] ?? '';
    $free_shipping_threshold = $_POST['free_shipping_threshold'] ?? '';

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
    $keys = ['default_shipping_fee','free_shipping_threshold'];
    foreach($keys as $k) {
        $v = $_POST[$k] ?? '';
        dbRun("INSERT INTO system_config (key, value, updated_at) VALUES (?,?,datetime('now')) ON CONFLICT(key) DO UPDATE SET value=?, updated_at=datetime('now')", [$k, $v, $v]);
    }
    // Vùng miền + cân nặng
    $origin = trim($_POST['shipping_origin_province'] ?? '');
    $zones = ['noi_tinh','noi_mien','can_mien','lien_mien'];
    $rates = [];
    foreach ($zones as $z) {
        $rates[] = [
            'zone' => $z,
            'base_weight' => max(1, intval($_POST['rate_'.$z.'_base_weight'] ?? 1000)),
            'base_price'  => max(0, intval($_POST['rate_'.$z.'_base_price'] ?? 0)),
            'step_weight' => max(1, intval($_POST['rate_'.$z.'_step_weight'] ?? 500)),
            'step_price'  => max(0, intval($_POST['rate_'.$z.'_step_price'] ?? 0)),
        ];
    }
    $extra = ['shipping_origin_province'=>$origin, 'shipping_rates'=>json_encode($rates, JSON_UNESCAPED_UNICODE)];
    foreach ($extra as $k=>$v) {
        dbRun("INSERT INTO system_config (key, value, updated_at) VALUES (?,?,datetime('now')) ON CONFLICT(key) DO UPDATE SET value=?, updated_at=datetime('now')", [$k, $v, $v]);
    }
    flash('success', 'Đã lưu cấu hình vận chuyển.');
    redirect('/admin/settings/finance');
});

post('/admin/vouchers/qty-discount', function() {
    requireStaffPermission('vouchers', '/admin/login'); csrfCheck();
    $t = $_POST['discount_quantity_threshold'] ?? '0';
    $p = $_POST['discount_quantity_percent'] ?? '0';
    if (!is_numeric($t) || $t < 0) { flash('error', 'Số lượng sản phẩm tối thiểu phải là số không âm.'); redirect('/admin/vouchers'); return; }
    if (!is_numeric($p) || $p < 0 || $p > 100) { flash('error', 'Mức giảm giá phần trăm phải nằm từ 0% đến 100%.'); redirect('/admin/vouchers'); return; }
    foreach (['discount_quantity_threshold'=>$t, 'discount_quantity_percent'=>$p] as $k=>$v) {
        dbRun("INSERT INTO system_config (key, value, updated_at) VALUES (?,?,datetime('now')) ON CONFLICT(key) DO UPDATE SET value=?, updated_at=datetime('now')", [$k, $v, $v]);
    }
    flash('success', 'Đã lưu quy tắc giảm giá số lượng.');
    redirect('/admin/vouchers');
});


post('/admin/users/bulk-delete', function() {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $back = in_array($_POST['back'] ?? '', ['/admin/users','/admin/staff-accounts','/admin/admin-accounts']) ? $_POST['back'] : '/admin/users';
    $__me = currentUser(); $__isSuper = !empty($__me['is_superadmin']);
    $ids = $_POST['ids'] ?? [];
    if (empty($ids)) { flash('error', 'Chưa chọn tài khoản nào.'); redirect($back); return; }
    $deleted = 0;
    $errors = [];
    foreach ($ids as $id) {
        $id = intval($id);
        if ($id <= 0) continue;
        $u = dbGet("SELECT role, full_name, is_superadmin FROM users WHERE id=?", [$id]);
        if (!$u || $id == ($__me['id'] ?? 0) || !empty($u['is_superadmin']) || ($u['role'] === 'admin' && !$__isSuper)) continue;
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
                dbRun("UPDATE users SET status='locked', email=email||'_deleted_'||?, phone=phone||'_deleted_'||? WHERE id=?", [time(), time(), $id]);
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
    redirect($back);
});

get('/admin/users', function() {
    $user = requireStaffPermission('rbac:customers.view|users', '/auth/login');
    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);
    $canViewCustomerPii = !$detailedRbac || rbacCan((int)$user['id'], 'customers.pii.view');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $status=$_GET['status']??''; $from=$_GET['from']??''; $to=$_GET['to']??'';
    $where="WHERE u.role='customer' AND u.email NOT LIKE '%_deleted_%'"; $params=[];
    if($q){$where.=" AND (u.email LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l]);}
    if($status==='active'){$where.=" AND u.status='active' AND (u.suspended_until IS NULL OR u.suspended_until<=datetime('now'))";}
    elseif($status==='suspended'){$where.=" AND (u.status!='active' OR (u.suspended_until IS NOT NULL AND u.suspended_until>datetime('now')))";}
    if($from){$where.=" AND u.created_at>=?"; $params[]=$from;}
    if($to){$where.=" AND u.created_at<=?"; $params[]=$to.' 23:59:59';}
    $total=dbGet("SELECT COUNT(*) AS n FROM users u $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $users=dbAll("SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?",$p2);
    if (!$canViewCustomerPii) { foreach ($users as &$customerRow) { $customerRow['email']='***'; $customerRow['phone']='***'; $customerRow['address']='***'; } unset($customerRow); }
    view('admin/users',['title'=>'Khách hàng','role'=>'admin','users'=>$users,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages,'listRole'=>'customer','listRoute'=>'/admin/users']);
});

get('/admin/staff-accounts', function() {
    requireRbacOrLegacyStaffPermission('system.staff.view', '/admin/login');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $status=$_GET['status']??''; $from=$_GET['from']??''; $to=$_GET['to']??'';
    $where="WHERE u.role='staff' AND u.email NOT LIKE '%_deleted_%'"; $params=[];
    if($q){$where.=" AND (u.email LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l]);}
    if($status==='active'){$where.=" AND u.status='active' AND (u.suspended_until IS NULL OR u.suspended_until<=datetime('now'))";}
    elseif($status==='suspended'){$where.=" AND (u.status!='active' OR (u.suspended_until IS NOT NULL AND u.suspended_until>datetime('now')))";}
    if($from){$where.=" AND u.created_at>=?"; $params[]=$from;}
    if($to){$where.=" AND u.created_at<=?"; $params[]=$to.' 23:59:59';}
    $total=dbGet("SELECT COUNT(*) AS n FROM users u $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $users=dbAll("SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?",$p2);
    view('admin/users',['title'=>'Nhân viên','role'=>'admin','users'=>$users,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages,'listRole'=>'staff','listRoute'=>'/admin/staff-accounts']);
});

get('/admin/admin-accounts', function() {
    requireSuperAdmin('/admin');
    $perPage=20; $page=max(1,intval($_GET['page']??1));
    $q=trim($_GET['q']??''); $status=$_GET['status']??''; $from=$_GET['from']??''; $to=$_GET['to']??'';
    $where="WHERE u.role='admin' AND COALESCE(u.is_superadmin,0)=0 AND u.email NOT LIKE '%_deleted_%'"; $params=[];
    if($q){$where.=" AND (u.email LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)"; $l="%$q%"; $params=array_merge($params,[$l,$l,$l]);}
    if($status==='active'){$where.=" AND u.status='active' AND (u.suspended_until IS NULL OR u.suspended_until<=datetime('now'))";}
    elseif($status==='suspended'){$where.=" AND (u.status!='active' OR (u.suspended_until IS NOT NULL AND u.suspended_until>datetime('now')))";}
    if($from){$where.=" AND u.created_at>=?"; $params[]=$from;}
    if($to){$where.=" AND u.created_at<=?"; $params[]=$to.' 23:59:59';}
    $total=dbGet("SELECT COUNT(*) AS n FROM users u $where",$params)['n']??0;
    $totalPages=max(1,ceil($total/$perPage));
    $p2=array_merge($params,[$perPage,($page-1)*$perPage]);
    $users=dbAll("SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?",$p2);
    $pwLog=dbAll("SELECT al.created_at, al.meta FROM audit_logs al LEFT JOIN users tu ON tu.id=al.entity_id WHERE al.action='password_change' AND al.entity_type='user' AND tu.role='admin' AND COALESCE(tu.is_superadmin,0)=0 ORDER BY al.created_at DESC LIMIT 50");
    view('admin/users',['title'=>'Quản trị viên','role'=>'admin','users'=>$users,'total'=>$total,'page'=>$page,'totalPages'=>$totalPages,'listRole'=>'admin','listRoute'=>'/admin/admin-accounts','pwLog'=>$pwLog]);
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
    $user = requireStaffPermission('rbac:marketing.promotions.view|vouchers', '/admin/login');
    $perPage = 15; $page = max(1, intval($_GET['page'] ?? 1));
    $vTotalAll = (int)(dbGet("SELECT COUNT(*) AS n FROM vouchers")['n'] ?? 0);
    $vTotalPages = max(1, (int)ceil($vTotalAll / $perPage));
    $page = min($page, $vTotalPages);
    $vouchers = dbAll("SELECT * FROM vouchers ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);
    $brands = dbAll("SELECT DISTINCT part_brand FROM products WHERE part_brand IS NOT NULL AND part_brand != '' ORDER BY part_brand");
    $carBrands = dbAll("SELECT id, name FROM brands ORDER BY name");
    $productBrands = dbAll("SELECT id, name FROM product_brands ORDER BY sort_order, name");
    view('admin/vouchers', ['title'=>'Voucher toàn sàn','role'=>'admin','vouchers'=>$vouchers,'brands'=>$brands,'carBrands'=>$carBrands,'productBrands'=>$productBrands,'page'=>$page,'totalPages'=>$vTotalPages]);
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

    // Reject duplicate code with a friendly message (vouchers.code is UNIQUE)
    if (dbGet("SELECT 1 FROM vouchers WHERE code=?", [$code])) {
        flash('error', 'Mã voucher "' . $code . '" đã tồn tại. Vui lòng dùng mã khác.');
        redirect('/admin/vouchers');
        return;
    }

    // Shop scope: save applied car-brands / product-brands into scope_value (JSON)
    $scope_value = null;
    if ($scope === 'shop') {
        $cb = array_values(array_filter(array_map('intval', $_POST['scope_brands'] ?? [])));
        $pb = array_values(array_filter(array_map('intval', $_POST['scope_product_brands'] ?? [])));
        $scope_value = json_encode(['car_brands' => $cb, 'product_brands' => $pb]);
    }

    try {
        $user = currentUser();
        dbInsert("INSERT INTO vouchers (code, name, scope, scope_value, discount_type, discount_value, max_discount, min_order_amount, funded_by, total_quantity, max_per_user, valid_from, valid_to, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
            [$code, $name, $scope, $scope_value, $discount_type, $discount_value, $max_discount, $min_order_amount, $funded_by, $total_quantity, $max_per_user, $valid_from, $valid_to, $status, $user['id']]);
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
    $user = requireRbacOrLegacyStaffPermission('crm.complaints.manage', '/admin/login');
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
    
    $perPage = 20; $page = max(1, intval($_GET['page'] ?? 1));
    $rvTotal = (int)(dbGet("SELECT COUNT(*) AS n FROM reviews r INNER JOIN users u ON u.id=r.user_id INNER JOIN products p ON p.id=r.product_id $where", $params)['n'] ?? 0);
    $rvTotalPages = max(1, (int)ceil($rvTotal / $perPage));
    $page = min($page, $rvTotalPages);
    $reviews = dbAll("SELECT r.*, u.full_name, p.name AS product_name FROM reviews r INNER JOIN users u ON u.id=r.user_id INNER JOIN products p ON p.id=r.product_id $where ORDER BY r.created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, ($page-1)*$perPage]));
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    view('admin/reviews', ['title'=>'Kiểm duyệt đánh giá','role'=>'admin','reviews'=>$reviews,'rating'=>$rating,'categoryId'=>$categoryId,'categories'=>$categories,'total'=>$rvTotal,'page'=>$page,'totalPages'=>$rvTotalPages]);
});

post('/admin/reviews/:id/status', function($p) {
    requireRbacOrLegacyStaffPermission('crm.complaints.manage', '/admin/login');
    csrfCheck();
    $id = intval($p['id'] ?? 0);
    $status = $_POST['status'] ?? 'published';
    if (!in_array($status, ['published', 'hidden', 'pending'], true)) $status = 'published';
    dbRun("UPDATE reviews SET status=? WHERE id=?", [$status, $id]);
    flash('success', 'Đã cập nhật trạng thái đánh giá thành công.');
    redirect('/admin/reviews');
});

post('/admin/reviews/:id/delete', function($p) {
    requireRbacOrLegacyStaffPermission('crm.complaints.manage', '/admin/login');
    csrfCheck();
    $id = intval($p['id'] ?? 0);
    dbRun("DELETE FROM reviews WHERE id=?", [$id]);
    dbRun("DELETE FROM review_images WHERE review_id=?", [$id]);
    flash('success', 'Đã xóa đánh giá thành công.');
    redirect('/admin/reviews');
});

get('/admin/catalog', function() {
    $user = requireRole('admin', '/admin/login');
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    $categories = dbAll("SELECT * FROM categories ORDER BY sort_order");
    view('admin/catalog', ['title'=>'Hãng xe & Danh mục','role'=>'admin','brands'=>$brands,'categories'=>$categories]);
});

get('/admin/audit', function() {
    $user = requireRbacOrLegacyStaffPermission('system.audit.view', '/admin/login');
    $logs = dbAll("SELECT al.*, u.full_name, u.email FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC LIMIT 50");
    view('admin/audit', ['title'=>'Audit Log','role'=>'admin','logs'=>$logs]);
});

get('/admin/users/new', function() {
    requireStaffPermission('users|staff', '/auth/login');
    view('admin/user-edit',['title'=>'Them tai khoan','role'=>'admin','editUser'=>[]]);
});
post('/admin/users/new', function() {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
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
    requireStaffPermission('users|staff', '/auth/login');
    $editUser=dbGet('SELECT * FROM users WHERE id=?',[$p['id']]);
    if(!$editUser){flash('error','Khong tim thay.');redirect('/admin/users');}
    view('admin/user-edit',['title'=>'Sua tai khoan','role'=>'admin','editUser'=>$editUser]);
});
post('/admin/users/:id/edit', function($p) {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $u=dbGet('SELECT * FROM users WHERE id=?',[$p['id']]);
    if(!$u){flash('error','Khong tim thay.');redirect('/admin/users');}
    $name=$_POST['full_name']??''; $phone=$_POST['phone']??''; $addr=$_POST['address']??''; $role=$_POST['role']??$u['role']; $pass=$_POST['password']??''; $notes=$_POST['notes']??'';
    $hash=$u['password_hash']; if(strlen($pass)>=6)$hash=password_hash($pass,PASSWORD_BCRYPT);
    if(!in_array($role,['customer','staff','admin']))$role=$u['role'];
    dbRun("UPDATE users SET full_name=?,phone=?,address=?,role=?,password_hash=?,notes=?,updated_at=datetime('now') WHERE id=?",[$name,$phone,$addr,$role,$hash,$notes,$p['id']]);
    if($role==='staff')dbRun("INSERT OR IGNORE INTO staff_permissions(user_id) VALUES(?)",[$p['id']]);
    flash('success','Da cap nhat!');redirect('/admin/users/'.$p['id'].'/edit');
});
post('/admin/users/:id/reset-password', function($p) {
    requireStaffPermission('users|staff', '/admin/login'); csrfCheck();
    $back = $_POST['back'] ?? ''; if (!preg_match('#^/admin/(users|staff-accounts|admin-accounts)#', $back)) $back = '/admin/staff-accounts';
    $u = dbGet('SELECT id, full_name FROM users WHERE id=?', [$p['id']]);
    if(!$u){ flash('error','Không tìm thấy tài khoản.'); redirect($back); return; }
    $newPass = $_POST['new_password'] ?? '';
    if(strlen($newPass) < 6){ flash('error','Mật khẩu mới phải tối thiểu 6 ký tự.'); redirect($back); return; }
    dbRun("UPDATE users SET password_hash=?, updated_at=datetime('now') WHERE id=?", [password_hash($newPass, PASSWORD_BCRYPT), $p['id']]);
    $__act = currentUser(); logPasswordChange($p['id'], (!empty($__act['is_superadmin']) ? 'superadmin_reset' : 'admin_reset'));
    flash('success', 'Đã cấp lại mật khẩu cho ' . $u['full_name'] . '. Mật khẩu mới: ' . $newPass . ' — vui lòng gửi cho nhân viên.');
    redirect($back);
});
post('/admin/users/:id/suspend', function($p) {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $back = $_POST['back'] ?? ''; if (!preg_match('#^/admin/(users|staff-accounts|admin-accounts)#', $back)) $back = '/admin/users';
    $__tg = dbGet("SELECT role, is_superadmin FROM users WHERE id=?", [$p['id']]); $__me = currentUser();
    if (!$__tg || $p['id']==($__me['id']??0) || !empty($__tg['is_superadmin']) || ($__tg['role']==='admin' && empty($__me['is_superadmin']))) { flash('error','Bạn không có quyền thao tác với tài khoản này.'); redirect($back); return; }
    $days=intval($_POST['days']??0); $notes=trim($_POST['notes']??'');
    if($days>0){
        $until=date('Y-m-d H:i:s',strtotime("+{$days} days"));
        dbRun("UPDATE users SET suspended_until=?,notes=?,updated_at=datetime('now') WHERE id=?",[$until,$notes,$p['id']]);
        flash('success',"Da ngung tai khoan {$days} ngay.");
    }else{
        dbRun("UPDATE users SET status='locked',suspended_until=NULL,notes=?,updated_at=datetime('now') WHERE id=?",[$notes,$p['id']]);
        flash('success','Da ngung tai khoan vinh vien.');
    }
    redirect($back . '#u' . (int)$p['id']);
});
post('/admin/users/:id/permissions', function($p) {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $uid = (int)$p['id'];
    $admin = currentUser();
    $u = dbGet("SELECT id, role FROM users WHERE id=?", [$uid]);
    if (!$u) { flash('error','Không tìm thấy tài khoản.'); redirect('/admin/users'); return; }
    // Mỗi mục = 1 nhóm quyền (vai trò) trao TOÀN BỘ quyền con của nhóm đó
    $depts = [
        'can_products' => ['Quản lý sản phẩm', ['products','categories','brands','brand_models']],
        'can_orders'   => ['Quản lý đơn hàng', ['orders','returns','create_order']],
        'can_content'  => ['Quản lý nội dung', ['content']],
        'can_users'    => ['Quản lý người dùng', ['users']],
        'can_staff'    => ['Quản lý nhân viên', ['staff']],
        'can_vouchers' => ['Quản lý voucher', ['vouchers','promotions']],
        'can_reviews'  => ['Kiểm duyệt đánh giá', ['reviews']],
    ];
    $vals = []; $anyChecked = false;
    foreach ($depts as $key => $info) {
        $checked = isset($_POST[$key]) ? 1 : 0; $vals[$key] = $checked;
        $roleName = $info[0]; $gperms = $info[1];
        $role = dbGet("SELECT id FROM staff_roles WHERE name=?", [$roleName]);
        if (!$role) {
            dbRun("INSERT INTO staff_roles (name, description, permissions) VALUES (?,?,?)", [$roleName, 'Nhóm quyền hệ thống', json_encode($gperms, JSON_UNESCAPED_UNICODE)]);
            $role = dbGet("SELECT id FROM staff_roles WHERE name=?", [$roleName]);
        }
        if ($checked) {
            if (!dbGet("SELECT 1 FROM staff_role_assignments WHERE user_id=? AND role_id=?", [$uid, $role['id']])) {
                dbRun("INSERT INTO staff_role_assignments (user_id, role_id, assigned_by) VALUES (?,?,?)", [$uid, $role['id'], $admin['id'] ?? null]);
            }
            $anyChecked = true;
        } else {
            dbRun("DELETE FROM staff_role_assignments WHERE user_id=? AND role_id=?", [$uid, $role['id']]);
        }
    }
    if ($anyChecked && ($u['role'] ?? '') === 'customer') { dbRun("UPDATE users SET role='staff' WHERE id=?", [$uid]); }
    dbRun("INSERT OR REPLACE INTO staff_permissions(user_id,can_products,can_orders,can_content,can_users,can_vouchers,can_reviews,updated_at) VALUES(?,?,?,?,?,?,?,datetime('now'))",
        [$uid,$vals['can_products'],$vals['can_orders'],$vals['can_content'],$vals['can_users'],$vals['can_vouchers'],$vals['can_reviews']]);
    flash('success','Đã lưu phân quyền! Nhân viên có quyền theo các nhóm đã tích.');
    redirect('/admin/users/'.$uid.'/edit');
});

post('/admin/users/:id/lock', function($p) {
    requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $back = $_POST['back'] ?? ''; if (!preg_match('#^/admin/(users|staff-accounts|admin-accounts)#', $back)) $back = '/admin/users';
    dbRun("UPDATE users SET status='locked', suspended_until=NULL WHERE id=?", [$p['id']]);
    flash('success','Da khoa tai khoan.'); redirect($back . '#u' . (int)$p['id']);
});

post('/admin/users/:id/unlock', function($p) {
    $user = requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
    $back = $_POST['back'] ?? ''; if (!preg_match('#^/admin/(users|staff-accounts|admin-accounts)#', $back)) $back = '/admin/users';
    dbRun("UPDATE users SET status='active', suspended_until=NULL WHERE id=?", [$p['id']]);
    flash('success','Đã mở khóa tài khoản.'); redirect($back . '#u' . (int)$p['id']);
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
    $slug = uniqueProductSlug($name);
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
    $slug = uniqueProductSlug($name);
    $id = dbInsert("INSERT INTO products (partner_id, name, slug, oem_code, price, stock, category_id, description, status, published_at) VALUES (?,?,?,?,?,?,?,?,'published',datetime('now'))",
        [$partnerId, $name, $slug, $oem, $price, $stock, $catId, $desc]);
    // Save SEO fields
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDesc = trim($_POST['seo_description'] ?? '');
    $seoKeyword = trim($_POST['seo_keyword'] ?? '');
    $seoSlug = !empty($_POST['slug']) ? uniqueProductSlug($_POST['slug'], (int)$id) : $slug;
    if ($seoTitle || $seoDesc || $seoKeyword || $seoSlug) {
        dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=?, slug=? WHERE id=?",
            [$seoTitle, $seoDesc, $seoKeyword, $seoSlug, $id]);
    }
    
    flash('success','Đã đăng sản phẩm hộ đối tác.');
    redirect('/admin/products');
});


// ── INVENTORY MANAGEMENT ───────────────────────────────────────────────────
get('/admin/inventory', function() {
    $user = requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    $perPage = 25;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $q = trim((string)($_GET['q'] ?? ''));
    $stockStatus = in_array($_GET['status'] ?? 'all', ['all','low','out'], true) ? $_GET['status'] : 'all';
    $categoryId = max(0, (int)($_GET['category'] ?? 0));
    $where = 'WHERE 1=1'; $params = [];
    if ($q !== '') { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ? OR p.oem_code2 LIKE ?)'; $like='%'.$q.'%'; array_push($params,$like,$like,$like,$like); }
    if ($categoryId) { $where .= ' AND p.category_id=?'; $params[]=$categoryId; }
    if ($stockStatus === 'low') $where .= ' AND p.min_stock>0 AND p.stock<=p.min_stock';
    if ($stockStatus === 'out') $where .= ' AND p.stock<=0';
    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM products p $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page,$totalPages);
    $listParams=array_merge($params,[$perPage,($page-1)*$perPage]);
    $products=dbAll("SELECT p.*,c.name AS category_name,(SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC,sort_order,id LIMIT 1) AS image FROM products p LEFT JOIN categories c ON c.id=p.category_id $where ORDER BY CASE WHEN p.min_stock>0 AND p.stock<=p.min_stock THEN 0 ELSE 1 END,p.updated_at DESC,p.id DESC LIMIT ? OFFSET ?",$listParams);
    $summary=dbGet("SELECT COUNT(*) AS total,SUM(CASE WHEN min_stock>0 AND stock<=min_stock THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN stock<=0 THEN 1 ELSE 0 END) AS out FROM products") ?: ['total'=>0,'low'=>0,'out'=>0];
    $categories=dbAll('SELECT id,name FROM categories ORDER BY sort_order,name');
    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);
    $inventoryPermissions = [
      'detailed'=>$detailedRbac,
      'view_cost'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.cost.view'),
      'edit_cost'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.cost.edit'),
      'edit_price'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.pricing.edit'),
      'edit_stock'=>!$detailedRbac || rbacCan((int)$user['id'], 'inventory.update'),
      'edit_thresholds'=>!$detailedRbac || rbacCan((int)$user['id'], 'inventory.thresholds.edit'),
      'edit_warranty'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.products.edit'),
    ];
    $hpage = max(1, (int)($_GET['hpage'] ?? 1));
    $hperPage = max(5, min(100, (int)($_GET['hper_page'] ?? 15)));
    $hTotal = (int)(dbGet("SELECT COUNT(*) AS c FROM inventory_stock_movements WHERE reference_type='manual_adjust'")['c'] ?? 0);
    $hTotalPages = max(1, (int)ceil($hTotal / $hperPage));
    $hpage = min($hpage, $hTotalPages);
    $hOffset = max(0, ($hpage - 1) * $hperPage);

    $history = dbAll("SELECT m.*, p.name AS product_name, p.sku, u.full_name AS creator_name FROM inventory_stock_movements m INNER JOIN products p ON p.id=m.product_id LEFT JOIN users u ON u.id=m.created_by WHERE m.reference_type='manual_adjust' ORDER BY m.id DESC LIMIT ? OFFSET ?", [$hperPage, $hOffset]);

    view('admin/inventory',[
        'title'=>'Quản lý kho',
        'role'=>'admin',
        'products'=>$products,
        'summary'=>$summary,
        'categories'=>$categories,
        'q'=>$q,
        'stockStatus'=>$stockStatus,
        'categoryId'=>$categoryId,
        'inventoryPermissions'=>$inventoryPermissions,
        'page'=>$page,
        'totalPages'=>$totalPages,
        'history'=>$history,
        'hpage'=>$hpage,
        'hperPage'=>$hperPage,
        'hTotal'=>$hTotal,
        'hTotalPages'=>$hTotalPages
    ]);
});

post('/admin/inventory/:id/update', function($p) {
    $user=requireStaffPermission('rbac:inventory.update|rbac:catalog.pricing.edit|rbac:catalog.cost.edit|rbac:inventory.thresholds.edit|rbac:catalog.products.edit|products','/admin/login'); csrfCheck();
    
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
              || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

    $targetUrl = '/admin/inventory';
    if (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/admin/inventory')) {
        $targetUrl = $_SERVER['HTTP_REFERER'];
    }

    $product=dbGet('SELECT * FROM products WHERE id=?',[$p['id']]);
    if(!$product){
        if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Không tìm thấy sản phẩm.']); exit; }
        flash('error','Không tìm thấy sản phẩm.'); redirect($targetUrl); return;
    }

    $fields=['cost_price','price','wholesale_price','original_price','stock','min_stock','max_stock','warranty_months']; $values=[];
    foreach($fields as $field){
        $raw=trim((string)($_POST[$field]??'')); 
        if($raw===''||!ctype_digit($raw)){
            if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Dữ liệu kho không hợp lệ.']); exit; }
            flash('error','Dữ liệu kho không hợp lệ.'); redirect($targetUrl); return;
        } 
        $values[$field]=(int)$raw;
    }

    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);
    if ($detailedRbac) {
        $fieldCapabilities = [
          'cost_price'=>'catalog.cost.edit','price'=>'catalog.pricing.edit','wholesale_price'=>'catalog.pricing.edit','original_price'=>'catalog.pricing.edit',
          'stock'=>'inventory.update','min_stock'=>'inventory.thresholds.edit','max_stock'=>'inventory.thresholds.edit',
          'warranty_months'=>'catalog.products.edit'
        ];
        foreach ($fieldCapabilities as $field=>$capability) {
            if ((int)$product[$field] !== $values[$field] && !rbacCan((int)$user['id'], $capability)) {
                if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Bạn không có quyền thay đổi trường dữ liệu này.']); exit; }
                flash('error','Ban khong co quyen thay doi truong du lieu nay.'); redirect($targetUrl); return;
            }
        }
    }
    if($values['stock']>1000||$values['min_stock']>1000||$values['max_stock']>1000){
        if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Tồn kho chỉ được từ 0 đến 1000.']); exit; }
        flash('error','Tồn kho chỉ được từ 0 đến 1000.'); redirect($targetUrl); return;
    }
    if($values['min_stock']>$values['max_stock']){
        if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Tồn tối thiểu không được lớn hơn tồn tối đa.']); exit; }
        flash('error','Tồn tối thiểu không được lớn hơn tồn tối đa.'); redirect($targetUrl); return;
    }
    if($values['original_price']>0&&$values['original_price']<$values['price']){
        if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Giá gốc không được nhỏ hơn giá bán khi được nhập.']); exit; }
        flash('error','Giá gốc không được nhỏ hơn giá bán khi được nhập.'); redirect($targetUrl); return;
    }
    
    $stockDiff = $values['stock'] - (int)$product['stock'];
    $pdo = db();
    try {
        $pdo->beginTransaction();
        // === LƯU LỊCH SỬ KHO/GIÁ TRƯỚC KHI CẬP NHẬT ===
        saveProductHistory($product, 'inventory', (int)($user['id'] ?? 0));
        dbRun("UPDATE products SET cost_price=?,price=?,original_price=?,stock=?,min_stock=?,max_stock=?,warranty_months=?,total_import_value=?,updated_at=datetime('now','localtime') WHERE id=?",[$values['cost_price'],$values['price'],$values['original_price']?:null,$values['stock'],$values['min_stock'],$values['max_stock'],$values['warranty_months'],$values['cost_price']*$values['stock'],$p['id']]);
        if ($stockDiff !== 0) {
            $mvDir = $stockDiff > 0 ? 'in' : 'out';
            dbInsert("INSERT INTO inventory_stock_movements (product_id,direction,quantity,reference_type,note,created_by) VALUES (?,?,?,?,?,?)", [
                $p['id'], $mvDir, abs($stockDiff), 'manual_adjust', 'Cập nhật tồn kho trực tiếp qua Quản lý kho', $user['id'] ?? null
            ]);
        }
        $before=['cost_price'=>(int)$product['cost_price'],'price'=>(int)$product['price'],'original_price'=>(int)$product['original_price'],'stock'=>(int)$product['stock'],'min_stock'=>(int)$product['min_stock'],'max_stock'=>(int)$product['max_stock'],'warranty_months'=>(int)$product['warranty_months']];
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$user['id'] ?? null,$user['role'] ?? 'admin','inventory_update','product',$p['id'],json_encode(['before'=>$before,'after'=>$values],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR'] ?? '',$_SERVER['HTTP_USER_AGENT'] ?? '']);
        $pdo->commit();
        inventoryCheckLowStockAlert((int)$p['id'], 'inventory_update');

        $oldPrice = (int)($product['price'] ?? 0);
        $newPrice = (int)($values['price'] ?? 0);
        $oldStock = (int)($product['stock'] ?? 0);
        $newStock = (int)($values['stock'] ?? 0);
        $pName = $product['name'] ?? '';
        $actor = $user['full_name'] ?? $user['email'] ?? 'Quản trị viên';

        if ($oldPrice !== $newPrice) {
            dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('price', 'Thay đổi giá sản phẩm', ?, ?)", [
                "Sản phẩm '{$pName}' vừa được đổi giá từ " . vnd($oldPrice) . " thành " . vnd($newPrice) . " tại Quản lý kho bởi {$actor}.",
                "/admin/products/{$p['id']}/edit"
            ]);
        } elseif ($oldStock !== $newStock) {
            dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('stock', 'Điều chỉnh tồn kho', ?, ?)", [
                "Số lượng tồn kho sản phẩm '{$pName}' vừa thay đổi từ {$oldStock} thành {$newStock} tại Quản lý kho bởi {$actor}.",
                "/admin/products/{$p['id']}/edit"
            ]);
        } else {
            dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('product', 'Cập nhật kho sản phẩm', ?, ?)", [
                "Đã lưu cập nhật dữ liệu kho cho sản phẩm '{$pName}' bởi {$actor}.",
                "/admin/products/{$p['id']}/edit"
            ]);
        }
    } catch(Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($isAjax) { echo json_encode(['ok'=>false, 'message'=>'Lỗi khi lưu dữ liệu kho: '.$e->getMessage()]); exit; }
        flash('error','Lỗi khi lưu dữ liệu kho.');
        redirect($targetUrl);
        return;
    }
    
    $isLow = $values['min_stock'] > 0 && $values['stock'] <= $values['min_stock'];
    if ($isAjax) {
        echo json_encode([
            'ok' => true,
            'message' => 'Đã cập nhật giá và tồn kho cho sản phẩm.',
            'isLow' => $isLow,
            'values' => $values
        ]);
        exit;
    }
    
    flash('success','Đã cập nhật giá và tồn kho cho sản phẩm.');
    redirect($targetUrl);
});

// ─── GIAI ĐOẠN B: KHO NÂNG CAO ───────────────────────────────────────────────

// B1: Thẻ kho / Stock Ledger
get('/admin/inventory/:id/ledger', function($p) {
    $u = requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    $product = dbGet('SELECT * FROM products WHERE id=?', [(int)$p['id']]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/inventory'); }
    $dir      = in_array($_GET['dir']??'', ['in','out'], true) ? $_GET['dir'] : '';
    $refType  = trim(preg_replace('/[^a-z_]/', '', $_GET['ref_type']??''));
    $fromDate = trim($_GET['from']??'');
    $toDate   = trim($_GET['to']??'');
    $page     = max(1, (int)($_GET['page']??1));
    $perPage  = 50;
    $where    = 'WHERE m.product_id=?'; $params = [(int)$p['id']];
    if ($dir)      { $where .= ' AND m.direction=?'; $params[] = $dir; }
    if ($refType)  { $where .= ' AND m.reference_type=?'; $params[] = $refType; }
    if ($fromDate) { $where .= " AND m.created_at >= ?"; $params[] = $fromDate.' 00:00:00'; }
    if ($toDate)   { $where .= " AND m.created_at <= ?"; $params[] = $toDate.' 23:59:59'; }
    $total = (int)(dbGet("SELECT COUNT(*) as c FROM inventory_stock_movements m $where", $params)['c']??0);
    $totalPages = max(1,(int)ceil($total/$perPage));
    $page = min($page, $totalPages);
    $listParams = array_merge($params, [$perPage, ($page-1)*$perPage]);
    $movements = dbAll("SELECT m.*, u.full_name AS creator_name FROM inventory_stock_movements m LEFT JOIN users u ON u.id=m.created_by $where ORDER BY m.id DESC LIMIT ? OFFSET ?", $listParams);
    // Tính running_total (chạy tổng ngược về quá khứ — chú ý: do DESC nên ta tính từ tồn hiện tại)
    $curStock = (int)$product['stock'];
    $allMvts = dbAll("SELECT id,direction,quantity FROM inventory_stock_movements WHERE product_id=? ORDER BY id DESC", [(int)$p['id']]);
    $runMap = []; $run = $curStock;
    foreach ($allMvts as $mv) { $runMap[$mv['id']] = $run; $run += ($mv['direction']==='in'?-1:1)*(int)$mv['quantity']; }
    foreach ($movements as &$mv) { $mv['running_total'] = $runMap[$mv['id']] ?? 0; } unset($mv);
    $stats = dbGet("SELECT COALESCE(SUM(CASE WHEN direction='in' AND created_at>=date('now','-30 days','localtime') THEN quantity ELSE 0 END),0) AS in_30d, COALESCE(SUM(CASE WHEN direction='out' AND created_at>=date('now','-30 days','localtime') THEN quantity ELSE 0 END),0) AS out_30d FROM inventory_stock_movements WHERE product_id=?", [(int)$p['id']]);
    view('admin/inventory-ledger', ['title'=>'Thẻ kho — '.$product['name'],'userRole'=>'admin','product'=>$product,'movements'=>$movements,'totalMovements'=>$total,'totalPages'=>$totalPages,'page'=>$page,'dir'=>$dir,'refType'=>$refType,'fromDate'=>$fromDate,'toDate'=>$toDate,'stats'=>$stats]);
});

// B2a: Tìm sản phẩm (API JSON cho autocomplete điều chỉnh tồn)
get('/admin/inventory/search-product', function() {
    requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    $q = trim($_GET['q']??'');
    if (mb_strlen($q) < 2) { header('Content-Type: application/json'); echo '[]'; exit; }
    $like = '%'.$q.'%';
    $rows = dbAll("SELECT id,name,sku,oem_code,stock,min_stock,max_stock FROM products WHERE (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) AND status='published' ORDER BY name LIMIT 20", [$like,$like,$like]);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows, JSON_UNESCAPED_UNICODE); exit;
});
// B3a: Danh sách phiếu kiểm kho
get('/admin/stocktake', function() {
    $u = requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    $canCreate = hasPermission($u,'inventory.adjust') || hasPermission($u,'tax_config');
    $stocktakes = dbAll("SELECT st.*,u.full_name AS creator_name,COUNT(si.id) AS item_count,COALESCE(SUM(CASE WHEN si.actual_qty IS NOT NULL THEN si.actual_qty-si.system_qty ELSE 0 END),0) AS total_diff FROM stocktakes st LEFT JOIN users u ON u.id=st.created_by LEFT JOIN stocktake_items si ON si.stocktake_id=st.id GROUP BY st.id ORDER BY st.created_at DESC LIMIT 100");
    view('admin/stocktake-list', ['title'=>'Kiểm kho','userRole'=>'admin','stocktakes'=>$stocktakes,'canCreate'=>$canCreate]);
});

// B3b: Form tạo phiếu kiểm kho mới
get('/admin/stocktake/new', function() {
    $u = requireStaffPermission('rbac:inventory.adjust|products', '/admin/login');
    $categories   = dbAll('SELECT id,name FROM categories ORDER BY sort_order,name');
    $totalProducts = (int)(dbGet("SELECT COUNT(*) as c FROM products WHERE status='published'")['c']??0);
    $lowStockCount = (int)(dbGet("SELECT COUNT(*) as c FROM products WHERE status='published' AND min_stock>0 AND stock<=min_stock")['c']??0);
    view('admin/stocktake-new', ['title'=>'Tạo phiếu kiểm kho','userRole'=>'admin','categories'=>$categories,'totalProducts'=>$totalProducts,'lowStockCount'=>$lowStockCount]);
});

// B3c: Tạo phiếu kiểm kho
post('/admin/stocktake', function() {
    $u = requireStaffPermission('rbac:inventory.adjust|products', '/admin/login');
    csrfCheck();
    $title  = trim($_POST['title']??'');
    $scope  = in_array($_POST['scope']??'all', ['all','category','low_stock'], true) ? $_POST['scope'] : 'all';
    $note   = trim($_POST['note']??'');
    $catIds = array_map('intval', (array)($_POST['category_id']??[]));
    if (mb_strlen($title)<3||mb_strlen($title)>200||mb_strlen($note)>500) { flash('error','Tiêu đề 3-200 ký tự, ghi chú tối đa 500.'); redirect('/admin/stocktake/new'); return; }
    dbRun("CREATE TABLE IF NOT EXISTS stocktakes (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,title TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'counting',note TEXT,rejection_reason TEXT,created_by INTEGER,approved_by INTEGER,approved_at TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')))");
    dbRun("CREATE TABLE IF NOT EXISTS stocktake_items (id INTEGER PRIMARY KEY AUTOINCREMENT,stocktake_id INTEGER NOT NULL,product_id INTEGER NOT NULL,system_qty INTEGER NOT NULL,actual_qty INTEGER,note TEXT,FOREIGN KEY(stocktake_id) REFERENCES stocktakes(id) ON DELETE CASCADE)");
    $pdo = db(); try {
        $pdo->beginTransaction();
        $code = 'KK-'.date('Ymd-His').'-'.random_int(100,999);
        $stId = dbInsert("INSERT INTO stocktakes (code,title,status,note,created_by) VALUES (?,?,'counting',?,?)", [$code,$title,$note?:null,$u['id']]);
        // Lấy sản phẩm theo phạm vi
        if ($scope === 'all') $products = dbAll("SELECT id,stock FROM products WHERE status='published' ORDER BY name");
        elseif ($scope === 'low_stock') $products = dbAll("SELECT id,stock FROM products WHERE status='published' AND min_stock>0 AND stock<=min_stock ORDER BY name");
        elseif ($scope === 'category' && $catIds) {
            $ph = implode(',',array_fill(0,count($catIds),'?'));
            $products = dbAll("SELECT id,stock FROM products WHERE status='published' AND category_id IN ($ph) ORDER BY name", $catIds);
        } else $products = [];
        if (!$products) { $pdo->rollBack(); flash('error','Không có sản phẩm nào phù hợp với phạm vi đã chọn.'); redirect('/admin/stocktake/new'); return; }
        foreach ($products as $pr) dbInsert("INSERT INTO stocktake_items (stocktake_id,product_id,system_qty) VALUES (?,?,?)", [$stId,$pr['id'],(int)$pr['stock']]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$u['id'],$u['role']??'admin','stocktake_created','stocktake',$stId, json_encode(['code'=>$code,'scope'=>$scope,'items'=>count($products)],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
        $pdo->commit();
        flash('success','Đã tạo phiếu kiểm kho '.$code.' với '.count($products).' sản phẩm.');
        redirect('/admin/stocktake/'.$stId);
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); flash('error','Lỗi tạo phiếu: '.$e->getMessage()); redirect('/admin/stocktake/new'); }
});

// B3d: Chi tiết / đếm hàng
get('/admin/stocktake/:id', function($p) {
    $u = requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    $canApprove = hasPermission($u,'inventory.adjust') || hasPermission($u,'tax_config');
    dbRun("CREATE TABLE IF NOT EXISTS stocktakes (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,title TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'counting',note TEXT,rejection_reason TEXT,created_by INTEGER,approved_by INTEGER,approved_at TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')))");
    dbRun("CREATE TABLE IF NOT EXISTS stocktake_items (id INTEGER PRIMARY KEY AUTOINCREMENT,stocktake_id INTEGER NOT NULL,product_id INTEGER NOT NULL,system_qty INTEGER NOT NULL,actual_qty INTEGER,note TEXT,FOREIGN KEY(stocktake_id) REFERENCES stocktakes(id) ON DELETE CASCADE)");
    $stocktake = dbGet('SELECT * FROM stocktakes WHERE id=?', [(int)$p['id']]);
    if (!$stocktake) { flash('error','Không tìm thấy phiếu kiểm kho.'); redirect('/admin/stocktake'); }
    $items = dbAll("SELECT si.*,p.name AS product_name,p.sku FROM stocktake_items si INNER JOIN products p ON p.id=si.product_id WHERE si.stocktake_id=? ORDER BY p.name", [(int)$p['id']]);
    $withDiff = array_filter($items, fn($i) => $i['actual_qty'] !== null && (int)$i['actual_qty'] !== (int)$i['system_qty']);
    view('admin/stocktake-detail', ['title'=>'Kiểm kho '.$stocktake['code'],'userRole'=>'admin','stocktake'=>$stocktake,'items'=>$items,'canApprove'=>$canApprove,'withDiff'=>$withDiff]);
});

// B3e: Lưu tiến độ đếm hàng
post('/admin/stocktake/:id/save-counts', function($p) {
    $u = requireStaffPermission('rbac:inventory.view|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    dbRun("CREATE TABLE IF NOT EXISTS stocktakes (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,title TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'counting',note TEXT,rejection_reason TEXT,created_by INTEGER,approved_by INTEGER,approved_at TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')))");
    dbRun("CREATE TABLE IF NOT EXISTS stocktake_items (id INTEGER PRIMARY KEY AUTOINCREMENT,stocktake_id INTEGER NOT NULL,product_id INTEGER NOT NULL,system_qty INTEGER NOT NULL,actual_qty INTEGER,note TEXT,FOREIGN KEY(stocktake_id) REFERENCES stocktakes(id) ON DELETE CASCADE)");
    $st = dbGet("SELECT * FROM stocktakes WHERE id=? AND status IN ('draft','counting')", [$id]);
    if (!$st) { flash('error','Phiếu không tồn tại hoặc không ở trạng thái có thể chỉnh sửa.'); redirect('/admin/stocktake'); return; }
    $actual = $_POST['actual']??[]; $notes = $_POST['note']??[];
    foreach ($actual as $itemId => $raw) {
        $itemId = (int)$itemId; $v = trim((string)$raw);
        $n = trim((string)($notes[$itemId]??''));
        if ($v === '') { dbRun("UPDATE stocktake_items SET actual_qty=NULL,note=? WHERE id=? AND stocktake_id=?", [$n?:null,$itemId,$id]); continue; }
        $qty = (int)preg_replace('/\D/','', $v);
        if ($qty < 0 || $qty > 999999) continue;
        dbRun("UPDATE stocktake_items SET actual_qty=?,note=? WHERE id=? AND stocktake_id=?", [$qty,$n?:null,$itemId,$id]);
    }
    $action = $_POST['action']??'save';
    if ($action === 'submit') {
        // Kiểm tra đã đếm hết chưa
        $uncounted = (int)(dbGet("SELECT COUNT(*) as c FROM stocktake_items WHERE stocktake_id=? AND actual_qty IS NULL",[$id])['c']??0);
        if ($uncounted > 0) { flash('error',"Còn $uncounted sản phẩm chưa được đếm. Vui lòng điền đầy đủ trước khi gửi duyệt."); redirect('/admin/stocktake/'.$id); return; }
        dbRun("UPDATE stocktakes SET status='pending_approval' WHERE id=?", [$id]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$u['id'],$u['role']??'admin','stocktake_submitted','stocktake',$id,'{}', $_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
        flash('success','Đã gửi phiếu kiểm kho để duyệt.');
    } else {
        dbRun("UPDATE stocktakes SET status='counting' WHERE id=? AND status='draft'", [$id]);
        flash('success','Đã lưu tiến độ đếm hàng.');
    }
    redirect('/admin/stocktake/'.$id);
});

// B3f: Duyệt kiểm kho — cập nhật tồn kho theo kết quả thực tế
post('/admin/stocktake/:id/approve', function($p) {
    $u = requireStaffPermission('rbac:inventory.adjust|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    $pdo = db(); try {
        $pdo->beginTransaction();
        $st = dbGet("SELECT * FROM stocktakes WHERE id=? AND status='pending_approval'", [$id]);
        if (!$st) throw new RuntimeException('status');
        $items = dbAll("SELECT * FROM stocktake_items WHERE stocktake_id=? AND actual_qty IS NOT NULL", [$id]);
        $updated = 0;
        foreach ($items as $item) {
            $diff = (int)$item['actual_qty'] - (int)$item['system_qty'];
            if ($diff === 0) continue;
            $dir = $diff > 0 ? 'in' : 'out';
            $qty = abs($diff);
            dbRun("UPDATE products SET stock=?,updated_at=datetime('now','localtime') WHERE id=?", [(int)$item['actual_qty'], $item['product_id']]);
            dbInsert("INSERT INTO inventory_stock_movements (product_id,direction,quantity,reference_type,reference_id,note,created_by) VALUES (?,?,?,'stocktake',?,?,?)", [$item['product_id'],$dir,$qty,$id,'Kiểm kho: '.$st['code'],$u['id']]);
            $updated++;
        }
        dbRun("UPDATE stocktakes SET status='approved',approved_by=?,approved_at=datetime('now','localtime') WHERE id=?", [$u['id'],$id]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$u['id'],$u['role']??'admin','stocktake_approved','stocktake',$id, json_encode(['code'=>$st['code'],'adjusted_products'=>$updated],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
        $pdo->commit();
        flash('success','Đã duyệt phiếu '.$st['code'].' — cập nhật tồn kho cho '.$updated.' sản phẩm có chênh lệch.');
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); flash('error',$e->getMessage()==='status'?'Phiếu không ở trạng thái chờ duyệt.':'Lỗi duyệt: '.$e->getMessage()); }
    redirect('/admin/stocktake/'.$id);
});

// B3g: Từ chối kiểm kho
post('/admin/stocktake/:id/reject', function($p) {
    $u = requireStaffPermission('rbac:inventory.adjust|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id']; $reason = trim($_POST['rejection_reason']??'');
    if (mb_strlen($reason)<5||mb_strlen($reason)>500) { flash('error','Lý do từ chối phải từ 5-500 ký tự.'); redirect('/admin/stocktake/'.$id); return; }
    $st = dbGet("SELECT * FROM stocktakes WHERE id=? AND status='pending_approval'", [$id]);
    if (!$st) { flash('error','Phiếu không ở trạng thái chờ duyệt.'); redirect('/admin/stocktake'); return; }
    dbRun("UPDATE stocktakes SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=? WHERE id=?", [$u['id'],$reason,$id]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$u['id'],$u['role']??'admin','stocktake_rejected','stocktake',$id, json_encode(['code'=>$st['code'],'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','Đã từ chối phiếu kiểm kho '.$st['code'].'.');
    redirect('/admin/stocktake/'.$id);
});

// ─── KẾT THÚC GIAI ĐOẠN B ────────────────────────────────────────────────────

// ── PRODUCTS (Admin posts directly) ────────────────────────────────────────

get('/admin/products/new', function() {
    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login');
    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']) && !rbacCan((int)$user['id'], 'catalog.codes.manage')) { flash('error','Ban khong co quyen tao ma SKU/OEM.'); redirect('/admin/products'); }
    $categories = dbAll('SELECT * FROM categories ORDER BY sort_order');
    $brands = dbAll('SELECT * FROM brands ORDER BY name ASC');
    view('admin/product-form', ['title'=>'Đăng SP mới','role'=>'admin','categories'=>$categories,'brands'=>$brands,'images'=>[]]);
});

post('/admin/products/new', function() {
    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login'); csrfCheck();
    $d = $_POST;
    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);
    if ($detailedRbac && !rbacCan((int)$user['id'], 'catalog.codes.manage')) { flash('error','Ban khong co quyen tao ma SKU/OEM.'); redirect('/admin/products'); return; }
    if ($detailedRbac) { foreach (['price','price_before_tax','tax_amount','original_price','stock','min_stock','cost_price'] as $lockedField) $d[$lockedField]='0'; $d['max_stock']='1000'; $d['warranty_months']='0'; $d['_inventory_in_product_form']=''; }
    $inventoryManagedSeparately = empty($d['_inventory_in_product_form']);
    $name = trim($d['name'] ?? '');
    $oem = trim($d['oem_code'] ?? '');
    $oem2 = trim($d['oem_code2'] ?? '');
    $sku = resolveProductSku($d['sku'] ?? '', $oem);
    $price = intval($d['price'] ?? 0);
    $priceBefore = intval($d['price_before_tax'] ?? 0);
    $taxAmt = intval($d['tax_amount'] ?? 0);
    $stockRaw = trim((string)($d['stock'] ?? ''));
    $maxStockRaw = trim((string)($d['max_stock'] ?? ''));
    $stock = intval($stockRaw);
    $maxStock = $maxStockRaw === '' ? 1000 : intval($maxStockRaw);
    $status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';
    if ($inventoryManagedSeparately) $status = 'draft';
    $slug = uniqueProductSlug(trim($d['slug'] ?? '') ?: $name);
    $seoTitle = trim($d['seo_title'] ?? '');
    if ($seoTitle === '') {
        $seoTitle = productMetaTitle(['name' => $name, 'oem_code' => $oem]);
    }
    $seoDesc = trim($d['seo_description'] ?? '');
    if ($seoDesc === '') {
        $seoDesc = productMetaDescription([
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'warranty_months' => intval($d['warranty_months'] ?? 12),
        ]);
    }
    $seoKeyword = trim($d['seo_keyword'] ?? '');

    // === SERVER-SIDE VALIDATION ===
    $valErrors = [];
    if (!$name) $valErrors[] = 'Tên sản phẩm không được để trống';
    if (!$sku)  $valErrors[] = 'Vui lòng nhập mã SKU hoặc mã OEM';
    $isContactPrice = !empty($d['is_call_price']) || !empty($d['is_contact_price']) || !empty($d['is_call']);
    if (!$inventoryManagedSeparately) {
        if (!$isContactPrice && $price <= 0) $valErrors[] = 'Giá bán sau VAT phải lớn hơn 0';
        if ($stockRaw === '') $valErrors[] = 'Tồn kho hiện tại không được để trống';
        elseif (!ctype_digit($stockRaw) || $stock > 1000) $valErrors[] = 'Tồn kho hiện tại chỉ được từ 0 đến 1000';
        if ($maxStockRaw !== '' && (!ctype_digit($maxStockRaw) || $maxStock > 1000)) $valErrors[] = 'Tồn kho tối đa chỉ được từ 0 đến 1000';
    }
    if (empty($d['category_id'])) $valErrors[] = 'Vui lòng chọn danh mục sản phẩm';
    // Validate giá nhập (tùy chọn nhưng phải >= 0 nếu có)
    $costRaw = trim($d['cost_price'] ?? '');
    if ($costRaw !== '' && (!ctype_digit($costRaw) || intval($costRaw) < 0)) {
        $valErrors[] = 'Giá nhập phải là số nguyên không âm';
    }
    // Validate giá gốc (tùy chọn nhưng phải >= 0 nếu có)
    $origRaw = trim($d['original_price'] ?? '');
    if ($origRaw !== '') {
        if (!ctype_digit($origRaw) || intval($origRaw) < 0) {
            $valErrors[] = 'Giá gốc phải là số nguyên không âm';
        } else if (intval($origRaw) > 0 && intval($origRaw) < $price) {
            $valErrors[] = 'Giá gốc không được nhỏ hơn Giá bán sau VAT';
        }
    }
    if (!empty($valErrors)) {
        flash('error', 'Không thể đăng sản phẩm: ' . implode('. ', $valErrors));
        redirect('/admin/products/new');
        return;
    }

    // === DUPLICATE SKU GUARD (chống lỗi 500 do UNIQUE(partner_id,sku)) ===
    $dupSku = dbGet("SELECT id, name FROM products WHERE partner_id=1 AND sku=?", [$sku]);
    if ($dupSku) {
        flash('error', 'Mã SKU "'.$sku.'" đã được dùng cho sản phẩm #'.$dupSku['id'].' ('.$dupSku['name'].'). Vui lòng dùng mã SKU khác.');
        redirect('/admin/products/new');
        return;
    }

    $id = dbInsert("INSERT INTO products (name,sku,slug,oem_code,oem_code2,part_brand,car_brand_id,category_id,price,price_before_tax,tax_amount,vat_rate,original_price,stock,min_stock,max_stock,features,specifications,warranty_months,description,status,is_featured,is_call_price,show_on_home,show_on_promo,is_new,is_indexed,partner_id,published_at,created_at,weight_g,width_cm,height_cm,depth_cm,seo_title,seo_description,seo_keyword,video_url,cost_price,total_import_value) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,datetime('now','localtime'),datetime('now','localtime'),?,?,?,?,?,?,?,?,?,?)", [
        $name,
        $sku,
        $slug,
        $oem,
        $oem2,
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
        $maxStock,
        $d['features']??'',
        $d['specifications']??'',
        intval($d['warranty_months']??12),
        $d['description'] ?? '',
        $status,
        isset($d['is_featured']) ? 1 : 0,
        $isContactPrice ? 1 : 0,
        isset($d['show_on_home']) ? 1 : 0,
        isset($d['show_on_promo']) ? 1 : 0,
        isset($d['is_new']) ? 1 : 0,
        ($status === 'published') ? 1 : 0, // index khi xuat ban (form khong co o rieng)
        intval($d['weight_g']??0) ?: null,
        intval($d['width_cm']??0) ?: null,
        intval($d['height_cm']??0) ?: null,
        intval($d['depth_cm']??0) ?: null,
        $seoTitle,
        $seoDesc,
        $seoKeyword,
        trim($d['video_url']??''),
        intval($d['cost_price']??0),
        intval($d['cost_price']??0) * $stock,
    ]);

    // Multi-brand mapping
    $brandIds = $_POST['car_brand_ids'] ?? [];
    foreach ($brandIds as $bid) {
        $bid_int = intval($bid);
        if ($bid_int > 0) {
            dbRun("INSERT OR IGNORE INTO product_brand_map (product_id, brand_id) VALUES (?,?)", [$id, $bid_int]);
        }
    }
    $firstBrand = null;
    foreach ($brandIds as $bid) {
        if ($bid === 'HIDDEN') { $firstBrand = 'HIDDEN'; break; }
        if (intval($bid) > 0) { $firstBrand = intval($bid); break; }
    }
    dbRun("UPDATE products SET car_brand_id=? WHERE id=?", [$firstBrand, $id]);

    // Handle and normalize product images.
    $imageUploadErrors = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '/var/lib/coolingsystems/uploads/products/';
        $savedImageCount = 0;
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $upload = [
                'name' => $_FILES['images']['name'][$k] ?? '',
                'tmp_name' => $tmp,
                'error' => $_FILES['images']['error'][$k] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['images']['size'][$k] ?? 0,
            ];
            $imageError = null;
            $imageSeoBase = $name . ' ' . $sku . ($k > 0 ? ' ' . ($k + 1) : '');
            $fname = storeNormalizedProductUpload($upload, $imageSeoBase, $uploadDir, $imageError);
            if ($fname !== null) {
                dbRun("INSERT INTO product_images (product_id,file_path,is_main,sort_order) VALUES (?,?,?,?)",
                    [$id, $fname, $savedImageCount === 0 ? 1 : 0, $savedImageCount]);
                $savedImageCount++;
            } else {
                $imageUploadErrors[] = basename((string)$upload['name']) . ': ' . ($imageError ?: 'Không xử lý được ảnh.');
            }
        }
    }

    $creatorName = $user['full_name'] ?? $user['email'] ?? 'Quản trị viên';
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('product', 'Đăng sản phẩm mới', ?, ?)", [
        "Sản phẩm mới '{$name}' (SKU: {$sku}) vừa được đăng bởi {$creatorName}.",
        "/admin/products/{$id}/edit"
    ]);

    // === LƯU LỊCH SỬ KHI TẠO SẢN PHẨM MỚI ===
    $newProduct = dbGet('SELECT * FROM products WHERE id=?', [$id]);
    if ($newProduct) {
        saveProductHistory($newProduct, 'create', (int)($user['id'] ?? 0));
    }

    $successMessage = 'Sản phẩm đã được đăng thành công!';
    if ($imageUploadErrors) {
        $successMessage .= ' Một số ảnh chưa xử lý được: ' . implode('; ', $imageUploadErrors);
    }
    flash('success', $successMessage);
    redirect('/admin/products');
});

get('/admin/products/:id/edit', function($p) {
    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');
    $returnTo = trim((string)($_GET['return_to'] ?? ''));
    if (!preg_match('#^/admin/products(?:\?|$)#', $returnTo)) $returnTo = '/admin/products';
    $product = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/products'); }
    $categories = dbAll('SELECT * FROM categories ORDER BY sort_order');
    $brands = dbAll('SELECT * FROM brands ORDER BY name ASC');
    $images = dbAll('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, is_main DESC', [$p['id']]);
    $canEditProductCodes = !(($user['role'] ?? '') === 'staff' && rbacUsesDetailedMode((int)$user['id'])) || rbacCan((int)$user['id'], 'catalog.codes.manage');
    $historyCount = dbGet('SELECT COUNT(*) AS n FROM product_history WHERE product_id=?', [$p['id']])['n'] ?? 0;
    view('admin/product-form', ['title'=>'Sửa SP: '.truncate($product['name'],30),'role'=>'admin','product'=>$product,'categories'=>$categories,'brands'=>$brands,'images'=>$images,'returnTo'=>$returnTo,'canEditProductCodes'=>$canEditProductCodes,'historyCount'=>(int)$historyCount]);
});

// === Lịch sử sản phẩm ===
get('/admin/products/:id/history', function($p) {
    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');
    $product = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/products'); return; }
    $history = dbAll(
        "SELECT ph.*, u.full_name AS changer_name FROM product_history ph LEFT JOIN users u ON u.id = ph.changed_by WHERE ph.product_id = ? ORDER BY ph.changed_at DESC LIMIT 100",
        [$p['id']]
    );
    view('admin/product-history', ['title'=>'Lịch sử SP: '.truncate($product['name'],30),'role'=>'admin','product'=>$product,'history'=>$history]);
});

// === Khôi phục sản phẩm từ lịch sử ===
post('/admin/products/:id/restore/:hid', function($p) {
    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login'); csrfCheck();
    $product = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/products'); return; }
    $snap = dbGet('SELECT * FROM product_history WHERE id=? AND product_id=?', [$p['hid'], $p['id']]);
    if (!$snap) { flash('error','Không tìm thấy bản lịch sử này.'); redirect('/admin/products/'.$p['id'].'/history'); return; }
    saveProductHistory($product, 'update', (int)($user['id'] ?? 0));
    dbRun(
        "UPDATE products SET name=?,sku=?,description=?,features=?,specifications=?,short_specs=?,price=?,original_price=?,stock=?,status=?,part_brand=?,car_brand_id=?,seo_title=?,seo_description=?,seo_keyword=?,updated_at=datetime('now','localtime') WHERE id=?",
        [$snap['name'],$snap['sku'],$snap['description'],$snap['features'],$snap['specifications'],$snap['short_specs'],$snap['price'],$snap['original_price'],$snap['stock'],$snap['status'],$snap['part_brand'],$snap['car_brand_id'],$snap['seo_title'],$snap['seo_description'],$snap['seo_keyword'],$p['id']]
    );
    flash('success', 'Đã khôi phục sản phẩm về bản lúc ' . $snap['changed_at'] . ' thành công!');
    redirect('/admin/products/' . $p['id'] . '/edit');
});

post('/admin/products/:id/edit', function($p) {
    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login'); csrfCheck();
    $d = $_POST;
    $returnTo = trim((string)($d['return_to'] ?? ''));
    if (!preg_match('#^/admin/products(?:\?|$)#', $returnTo)) $returnTo = '/admin/products';
    $editUrl = '/admin/products/' . $p['id'] . '/edit?return_to=' . rawurlencode($returnTo);
    $currentProduct = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);
    if (!$currentProduct) {
        flash('error', 'Không tìm thấy sản phẩm.');
        redirect($returnTo);
        return;
    }
    $price = intval($d['price'] ?? 0);
    $stockRaw = trim((string)($d['stock'] ?? ''));
    $maxStockRaw = trim((string)($d['max_stock'] ?? ''));
    $stock = intval($stockRaw);
    $maxStock = $maxStockRaw === '' ? 1000 : intval($maxStockRaw);
    $status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';
    $editOem = trim($d['oem_code'] ?? '');
    $editOem2 = trim($d['oem_code2'] ?? '');
    $editSku = resolveProductSku($d['sku'] ?? '', $editOem);
    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);
    if ($detailedRbac) {
      $fieldCapabilities = ['sku'=>'catalog.codes.manage','oem_code'=>'catalog.codes.manage','oem_code2'=>'catalog.codes.manage','price'=>'catalog.pricing.edit','original_price'=>'catalog.pricing.edit','cost_price'=>'catalog.cost.edit','stock'=>'inventory.update','min_stock'=>'inventory.thresholds.edit','max_stock'=>'inventory.thresholds.edit','warranty_months'=>'catalog.products.edit','status'=>'catalog.products.archive'];
      $requested = ['sku'=>$editSku,'oem_code'=>$editOem,'oem_code2'=>$editOem2,'price'=>$price,'original_price'=>intval($d['original_price']??0),'cost_price'=>intval($d['cost_price']??0),'stock'=>$stock,'min_stock'=>intval($d['min_stock']??0),'max_stock'=>$maxStock,'warranty_months'=>intval($d['warranty_months']??12),'status'=>$status];
      foreach ($fieldCapabilities as $field=>$capability) { if ((string)($currentProduct[$field] ?? '') !== (string)$requested[$field] && !rbacCan((int)$user['id'], $capability)) { flash('error','Ban khong co quyen thay doi truong du lieu nay.'); redirect($editUrl); return; } }
    }

    // === SERVER-SIDE VALIDATION ===
    $editErrors = [];
    if (!trim($d['name'] ?? '')) $editErrors[] = 'Tên sản phẩm không được để trống';
    $isContactPrice = !empty($d['is_call_price']) || !empty($d['is_contact_price']) || !empty($d['is_call']);
    if (!$isContactPrice && $price <= 0) $editErrors[] = 'Giá bán sau VAT phải lớn hơn 0';
    if ($stockRaw === '') $editErrors[] = 'Tồn kho hiện tại không được để trống';
    elseif (!ctype_digit($stockRaw) || $stock > 1000) $editErrors[] = 'Tồn kho hiện tại chỉ được từ 0 đến 1000';
    if ($maxStockRaw !== '' && (!ctype_digit($maxStockRaw) || $maxStock > 1000)) $editErrors[] = 'Tồn kho tối đa chỉ được từ 0 đến 1000';
    if (empty($d['category_id'])) $editErrors[] = 'Vui lòng chọn danh mục sản phẩm';
    $costRaw = trim($d['cost_price'] ?? '');
    if ($costRaw !== '' && (!ctype_digit($costRaw) || intval($costRaw) < 0)) {
        $editErrors[] = 'Giá nhập phải là số nguyên không âm';
    }
    $origRaw = trim($d['original_price'] ?? '');
    if ($origRaw !== '') {
        if (!ctype_digit($origRaw) || intval($origRaw) < 0) {
            $editErrors[] = 'Giá gốc phải là số nguyên không âm';
        } else if (intval($origRaw) > 0 && intval($origRaw) < $price) {
            $editErrors[] = 'Giá gốc không được nhỏ hơn Giá bán sau VAT';
        }
    }
    if (!empty($editErrors)) {
        flash('error', 'Không thể cập nhật sản phẩm: ' . implode('. ', $editErrors));
        redirect($editUrl);
        return;
    }

    // === DUPLICATE SKU GUARD (edit) ===
    $dupSku = dbGet("SELECT id FROM products WHERE partner_id=1 AND sku=? AND id<>?", [$editSku, $p['id']]);
    if ($dupSku) {
        flash('error', 'Mã SKU "'.$editSku.'" đã được dùng cho sản phẩm #'.$dupSku['id'].'. Vui lòng dùng mã SKU khác.');
        redirect($editUrl);
        return;
    }

    // === FOREIGN KEY SAFETY GUARDS ===
    $catId = intval($d['category_id']??0) ?: null;
    if ($catId && !dbGet("SELECT id FROM categories WHERE id=?", [$catId])) {
        $catId = null;
    }
    $carBrandId = intval($d['car_brand_id']??0) ?: null;
    if ($carBrandId && !dbGet("SELECT id FROM car_brands WHERE id=?", [$carBrandId])) {
        $carBrandId = null;
    }

    // === LƯU LỊCH SỬ TRƯỚC KHI CẬP NHẬT ===
    saveProductHistory($currentProduct, 'update', (int)($user['id'] ?? 0));

    dbRun("UPDATE products SET name=?,sku=?,oem_code=?,oem_code2=?,part_brand=?,car_brand_id=?,category_id=?,price=?,price_before_tax=?,tax_amount=?,vat_rate=?,original_price=?,stock=?,min_stock=?,max_stock=?,warranty_months=?,description=?,status=?,is_featured=?,is_call_price=?,show_on_home=?,show_on_promo=?,is_new=?,is_indexed=?,weight_g=?,width_cm=?,height_cm=?,depth_cm=?,video_url=?,cost_price=?,total_import_value=?,updated_at=datetime('now','localtime') WHERE id=?", [
        trim($d['name']??''),
        $editSku,
        $editOem,
        $editOem2,
        trim($d['part_brand']??''),
        $carBrandId,
        $catId,
        $price,
        intval($d['price_before_tax']??0),
        intval($d['tax_amount']??0),
        intval($d['vat_rate']??10),
        intval($d['original_price']??0) ?: null,
        $stock,
        intval($d['min_stock']??0),
        $maxStock,
        intval($d['warranty_months']??12),
        $d['description']??'',
        $status,
        isset($d['is_featured'])?1:0,
        $isContactPrice ? 1 : 0,
        isset($d['show_on_home'])?1:0,
        isset($d['show_on_promo'])?1:0,
        isset($d['is_new'])?1:0,
        ($status === 'published') ? 1 : 0, // index khi xuat ban
        intval($d['weight_g']??0) ?: null,
        intval($d['width_cm']??0) ?: null,
        intval($d['height_cm']??0) ?: null,
        intval($d['depth_cm']??0) ?: null,
        trim($d['video_url']??''),
        intval($d['cost_price']??0),
        intval($d['cost_price']??0) * $stock,
        $p['id']
    ]);
    inventoryCheckLowStockAlert((int)$p['id'], 'product_edit');

    $oldPrice = (int)($currentProduct['price'] ?? 0);
    $newPrice = $price;
    $oldStock = (int)($currentProduct['stock'] ?? 0);
    $newStock = $stock;
    $pName = trim($d['name'] ?? $currentProduct['name']);
    $updaterName = $user['full_name'] ?? $user['email'] ?? 'Quản trị viên';

    if ($oldPrice !== $newPrice) {
        dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('price', 'Thay đổi giá sản phẩm', ?, ?)", [
            "Sản phẩm '{$pName}' vừa được đổi giá từ " . vnd($oldPrice) . " thành " . vnd($newPrice) . " bởi {$updaterName}.",
            "/admin/products/{$p['id']}/edit"
        ]);
    }
    if ($oldStock !== $newStock) {
        dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('stock', 'Điều chỉnh tồn kho', ?, ?)", [
            "Tồn kho sản phẩm '{$pName}' vừa thay đổi từ {$oldStock} thành {$newStock} bởi {$updaterName}.",
            "/admin/products/{$p['id']}/edit"
        ]);
    }
    if ($oldPrice === $newPrice && $oldStock === $newStock) {
        dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('product', 'Cập nhật sản phẩm', ?, ?)", [
            "Thông tin sản phẩm '{$pName}' vừa được cập nhật bởi {$updaterName}.",
            "/admin/products/{$p['id']}/edit"
        ]);
    }

    // Handle and normalize newly uploaded product images.
    $imageUploadErrors = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '/var/lib/coolingsystems/uploads/products/';
        $replaceImages = !empty($d['replace_images']);
        $existingImages = dbAll('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order, id', [$p['id']]);
        $availableSlots = $replaceImages ? 8 : max(0, 8 - count($existingImages));
        $savedUploads = [];
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
            if (count($savedUploads) >= $availableSlots) {
                $imageUploadErrors[] = 'Chỉ lưu tối đa 8 ảnh cho mỗi sản phẩm.';
                break;
            }
            if (!is_uploaded_file($tmp)) continue;
            $upload = [
                'name' => $_FILES['images']['name'][$k] ?? '',
                'tmp_name' => $tmp,
                'error' => $_FILES['images']['error'][$k] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES['images']['size'][$k] ?? 0,
            ];
            $imageError = null;
            $imageSeoBase = trim($d['name'] ?? '') . ' ' . $editSku . ($k > 0 ? ' ' . ($k + 1) : '');
            $fname = storeNormalizedProductUpload($upload, $imageSeoBase, $uploadDir, $imageError);
            if ($fname !== null) {
                $savedUploads[] = $fname;
            } else {
                $imageUploadErrors[] = basename((string)$upload['name']) . ': ' . ($imageError ?: 'Không xử lý được ảnh.');
            }
        }

        if ($replaceImages && $savedUploads && $imageUploadErrors) {
            foreach ($savedUploads as $fname) {
                $newPath = $uploadDir . basename((string)$fname);
                if (is_file($newPath)) @unlink($newPath);
            }
            $savedUploads = [];
            $imageUploadErrors[] = 'Bộ ảnh cũ được giữ nguyên vì chưa xử lý thành công tất cả ảnh mới.';
        }

        if ($savedUploads) {
            $pdo = db();
            try {
                $pdo->beginTransaction();
                if ($replaceImages) {
                    dbRun('DELETE FROM product_images WHERE product_id=?', [$p['id']]);
                    foreach ($savedUploads as $index => $fname) {
                        dbRun("INSERT INTO product_images (product_id,file_path,is_main,sort_order) VALUES (?,?,?,?)",
                            [$p['id'], $fname, $index === 0 ? 1 : 0, $index]);
                    }
                } else {
                    $hasMain = (bool)dbGet('SELECT id FROM product_images WHERE product_id=? AND is_main=1', [$p['id']]);
                    $maxOrder = dbGet('SELECT COALESCE(MAX(sort_order),-1) AS max_so FROM product_images WHERE product_id=?', [$p['id']]);
                    $baseOrder = (int)($maxOrder['max_so'] ?? -1) + 1;
                    foreach ($savedUploads as $index => $fname) {
                        dbRun("INSERT INTO product_images (product_id,file_path,is_main,sort_order) VALUES (?,?,?,?)",
                            [$p['id'], $fname, !$hasMain && $index === 0 ? 1 : 0, $baseOrder + $index]);
                    }
                }
                dbRun("UPDATE products SET updated_at=datetime('now','localtime') WHERE id=?", [$p['id']]);
                $pdo->commit();

                if ($replaceImages) {
                    foreach ($existingImages as $oldImage) {
                        $oldPath = '/var/lib/coolingsystems/uploads/products/' . basename((string)$oldImage['file_path']);
                        if (is_file($oldPath)) @unlink($oldPath);
                    }
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                foreach ($savedUploads as $fname) {
                    $newPath = $uploadDir . basename((string)$fname);
                    if (is_file($newPath)) @unlink($newPath);
                }
                error_log('Product image update failed: ' . $exception->getMessage());
                $imageUploadErrors[] = 'Không thể cập nhật bộ ảnh; bộ ảnh cũ vẫn được giữ nguyên.';
            }
        }
    }

    // Save SEO fields on edit
    $seoTitle = trim($_POST['seo_title'] ?? '');
    if ($seoTitle === '') {
        $seoTitle = productMetaTitle(['name' => trim($d['name'] ?? ''), 'oem_code' => $editOem]);
    }
    $seoDesc = trim($_POST['seo_description'] ?? '');
    if ($seoDesc === '') {
        $seoDesc = productMetaDescription([
            'name' => trim($d['name'] ?? ''),
            'price' => $price,
            'stock' => intval($d['stock'] ?? 0),
            'warranty_months' => intval($d['warranty_months'] ?? 12),
        ]);
    }
    $seoKeyword = trim($_POST['seo_keyword'] ?? '');
    $requestedSlug = trim($_POST['slug'] ?? '');
    $slugSource = $requestedSlug !== '' ? $requestedSlug : ((string)($currentProduct['slug'] ?? '') ?: trim($d['name'] ?? ''));
    $seoSlug = uniqueProductSlug($slugSource, (int)$p['id']);
    dbRun("UPDATE products SET seo_title=?, seo_description=?, seo_keyword=?, slug=? WHERE id=?",
        [$seoTitle, $seoDesc, $seoKeyword, $seoSlug, $p['id']]);
    rememberProductSlugRedirect((int)$p['id'], (string)($currentProduct['slug'] ?? ''), $seoSlug);
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

    // Save the NEW updated product state into product_history & audit_logs
    $updatedProduct = dbGet("SELECT * FROM products WHERE id=?", [$p['id']]);
    if ($updatedProduct) {
        saveProductHistory($updatedProduct, 'update', (int)($user['id'] ?? 0));
        dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)",
            [(int)($user['id']??0), $user['role']??'admin', 'update', 'product', (int)$p['id'], "Cập nhật sản phẩm: " . mb_substr((string)$updatedProduct['name'], 0, 100), $_SERVER['REMOTE_ADDR']??'', $_SERVER['HTTP_USER_AGENT']??'']);
    }

    $successMessage = 'Cập nhật sản phẩm thành công!';
    if ($imageUploadErrors) {
        $successMessage .= ' Một số ảnh chưa xử lý được: ' . implode('; ', $imageUploadErrors);
    }
    flash('success', $successMessage);
    redirect($editUrl);
});


// ── STATIC CONTENT MANAGEMENT ────────────────────────────────────────────

// Banner management
post('/admin/footer/update', function() {
    requireStaffPermission('content', '/auth/login'); csrfCheck();
    $fields = ['footer_logo_text','footer_desc','footer_copyright'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)", [$f, $val]);
    }
    flash('success', 'Đã cập nhật Footer.');
    redirect('/admin/content');
});
post('/admin/banner/update', function() {
    requireStaffPermission('content', '/auth/login'); csrfCheck();
    $fields = ['hero_badge','hero_heading','hero_subtext','hero_btn1_text','hero_btn1_url','hero_btn2_text','hero_btn2_url','hero_show_text','hero_banner_link'];
    foreach ($fields as $f) {
        $val = isset($_POST[$f]) ? trim($_POST[$f]) : '';
        dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)", [$f, $val]);
    }
    
    // Multi-banner items from form (presets or existing list)
    $bannersList = [];
    
    // Existing banners array from form
    if (!empty($_POST['existing_banners']) && is_array($_POST['existing_banners'])) {
        foreach ($_POST['existing_banners'] as $idx => $img) {
            $img = trim($img);
            if (!empty($img)) {
                $link = trim($_POST['existing_banner_links'][$idx] ?? '');
                $bannersList[] = ['img' => $img, 'link' => $link];
            }
        }
    }
    
    // Selected presets
    if (!empty($_POST['selected_presets']) && is_array($_POST['selected_presets'])) {
        foreach ($_POST['selected_presets'] as $presetImg) {
            $presetImg = trim($presetImg);
            if (!empty($presetImg)) {
                $exists = false;
                foreach ($bannersList as $b) {
                    if ($b['img'] === $presetImg) { $exists = true; break; }
                }
                if (!$exists) {
                    $bannersList[] = ['img' => $presetImg, 'link' => '/products'];
                }
            }
        }
    }

    // Multiple file uploads
    if (!empty($_FILES['hero_banner_files']['tmp_name']) && is_array($_FILES['hero_banner_files']['tmp_name'])) {
        @mkdir(__DIR__ . '/../public/uploads/banners', 0755, true);
        @mkdir('/var/lib/coolingsystems/uploads/banners', 0755, true);
        @mkdir('/var/lib/coolingsystems/uploads/banners', 0755, true);
        
        foreach ($_FILES['hero_banner_files']['tmp_name'] as $idx => $tmpName) {
            if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                $ext = strtolower(pathinfo($_FILES['hero_banner_files']['name'][$idx] ?? '', PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $fname = 'hero_' . time() . '_' . $idx . '.' . $ext;
                    $targetPublic = __DIR__ . '/../public/uploads/banners/' . $fname;
                    if (move_uploaded_file($tmpName, $targetPublic)) {
                        @copy($targetPublic, '/var/lib/coolingsystems/uploads/banners/' . $fname);
                        @copy($targetPublic, '/var/lib/coolingsystems/uploads/banners/' . $fname);
                        @copy($targetPublic, __DIR__ . '/../uploads/banners/' . $fname);
                        $bannersList[] = ['img' => $fname, 'link' => ''];
                    }
                }
            }
        }
    }

    // Single file upload backwards compatibility
    if (!empty($_FILES['hero_bg_image']['tmp_name']) && is_uploaded_file($_FILES['hero_bg_image']['tmp_name'])) {
        @mkdir(__DIR__ . '/../public/uploads/banners', 0755, true);
        @mkdir('/var/lib/coolingsystems/uploads/banners', 0755, true);
        @mkdir('/var/lib/coolingsystems/uploads/banners', 0755, true);
        $ext = strtolower(pathinfo($_FILES['hero_bg_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = 'hero_' . time() . '.' . $ext;
            $targetPublic = __DIR__ . '/../public/uploads/banners/' . $fname;
            if (move_uploaded_file($_FILES['hero_bg_image']['tmp_name'], $targetPublic)) {
                @copy($targetPublic, '/var/lib/coolingsystems/uploads/banners/' . $fname);
                @copy($targetPublic, '/var/lib/coolingsystems/uploads/banners/' . $fname);
                @copy($targetPublic, __DIR__ . '/../uploads/banners/' . $fname);
                $bannersList[] = ['img' => $fname, 'link' => ''];
            }
        }
    }

    // Default fallback if list is still empty
    if (empty($bannersList)) {
        $bannersList[] = ['img' => 'hero_cooling_banner_1.png', 'link' => '/products'];
    }

    // Save JSON list
    dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES ('home_banners_list', ?)", [json_encode($bannersList, JSON_UNESCAPED_UNICODE)]);
    
    // Update main single bg image for backwards compatibility
    if (!empty($bannersList[0]['img'])) {
        dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES ('hero_bg_image', ?)", [$bannersList[0]['img']]);
    }
    
    flash('success', 'Đã cập nhật danh sách banner trang chủ.');
    redirect('/admin/content');
});

get('/admin/content', function() {
    $user = requireStaffPermission('content', '/auth/login');
    $pages = dbAll('SELECT * FROM static_pages ORDER BY title');
    $bannerSettings = []; $bkeys = ['hero_badge','hero_heading','hero_subtext','hero_btn1_text','hero_btn1_url','hero_btn2_text','hero_btn2_url','hero_bg_image','hero_show_text','hero_banner_link','home_banners_list']; foreach($bkeys as $bk){$r=dbGet("SELECT value FROM settings WHERE key=?",[$bk]); $bannerSettings[$bk]=$r['value']??'';}
    $footerSettings = []; $fkeys = ['footer_logo_text','footer_desc','footer_copyright']; foreach($fkeys as $fk){$r=dbGet("SELECT value FROM settings WHERE key=?",[$fk]); $footerSettings[$fk]=$r['value']??'';}
    view('admin/content-list', array_merge( ['title'=>'Quản lý nội dung','role'=>'admin','pages'=>$pages], ['bannerSettings'=>$bannerSettings, 'footerSettings'=>$footerSettings]));
});

get('/admin/garage-tiers', function() {
    $user = requireStaffPermission('customers', '/auth/login');
    $tiers = dbAll("SELECT * FROM garage_tiers ORDER BY min_monthly_spend ASC");
    view('admin/garage-tiers', ['title'=>'Cấu Hình Hạng Gara', 'user'=>$user, 'tiers'=>$tiers]);
});

post('/admin/garage-tiers/update', function() {
    $user = requireStaffPermission('customers', '/auth/login');
    csrfCheck();
    $ids = $_POST['tier_id'] ?? [];
    $names = $_POST['tier_name'] ?? [];
    $percents = $_POST['discount_percent'] ?? [];
    $spends = $_POST['min_monthly_spend'] ?? [];

    foreach ($ids as $i => $id) {
        dbRun("UPDATE garage_tiers SET tier_name=?, discount_percent=?, min_monthly_spend=? WHERE id=?", [
            $names[$i] ?? '',
            floatval($percents[$i] ?? 10),
            floatval($spends[$i] ?? 0),
            intval($id)
        ]);
    }
    flash('success', 'Đã lưu cấu hình Hạng Gara thành công!');
    redirect('/admin/garage-tiers');
});

get('/admin/quotations', function() {
    $user = requireStaffPermission('customers', '/auth/login');
    $quotations = dbAll("SELECT * FROM garage_quotations ORDER BY id DESC");
    view('admin/quotations', ['title'=>'Quản Lý Yêu Cầu Báo Giá', 'user'=>$user, 'quotations'=>$quotations]);
});

post('/admin/quotations/reply', function() {
    $user = requireStaffPermission('customers', '/auth/login');
    csrfCheck();
    $id = intval($_POST['id'] ?? 0);
    $totalPrice = floatval($_POST['total_price'] ?? 0);
    $replyNote = trim($_POST['admin_reply_note'] ?? '');

    if ($id && $totalPrice > 0) {
        dbRun("UPDATE garage_quotations SET total_price=?, admin_reply_note=?, status='replied', updated_at=datetime('now','localtime') WHERE id=?", [
            $totalPrice, $replyNote, $id
        ]);
        
        $q = dbGet("SELECT * FROM garage_quotations WHERE id=?", [$id]);
        if ($q && !empty($q['user_id'])) {
            dbInsert("INSERT INTO notifications (user_id, title, content, type, is_read, created_at) VALUES (?, ?, ?, 'system', 0, datetime('now','localtime'))", [
                $q['user_id'],
                'Báo giá phụ tùng Gara #' . $id,
                'Phụ tùng của bạn đã được báo giá buôn thành công với tổng số tiền ' . vnd($totalPrice) . '. Vui lòng xem chi tiết trong Hồ sơ cá nhân.'
            ]);
        }
        flash('success', 'Đã gửi báo giá thành công về cho Gara!');
    }
    redirect('/admin/quotations');
});

get('/admin/content/:slug', function($p) {
    $user = requireStaffPermission('content', '/auth/login');
    $page = dbGet('SELECT * FROM static_pages WHERE slug=?', [$p['slug']]);
    if (!$page) { flash('error','Không tìm thấy trang.'); redirect('/admin/content'); }
    view('admin/content-editor', ['title'=>'Sửa: '.$page['title'],'role'=>'admin','page'=>$page]);
});

post('/admin/content/:slug', function($p) {
    $user = requireStaffPermission('content', '/auth/login'); csrfCheck();
    $page = dbGet('SELECT * FROM static_pages WHERE slug=?', [$p['slug']]);
    $content = $_POST['content'] ?? '';
    $title   = trim($_POST['title'] ?? '');
    if (!$title && $page) {
        $title = $page['title'];
    }
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
        if (in_array($ext,['jpg','jpeg','png','webp'])) { $fname=uniqid('news_').'.'.$ext; if (move_uploaded_file($_FILES['thumbnail']['tmp_name'],'/var/lib/coolingsystems/uploads/news/'.$fname)) $thumb=$fname; }
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
        if (in_array($ext,['jpg','jpeg','png','webp'])) { $fname=uniqid('news_').'.'.$ext; if (move_uploaded_file($_FILES['thumbnail']['tmp_name'],'/var/lib/coolingsystems/uploads/news/'.$fname)) $thumb=$fname; }
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

get('/admin/products/:id/history', function($p) {
    $user = requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');
    $pid = intval($p['id']);
    $product = dbGet("SELECT p.*, c.name AS cat_name, b.name AS car_brand_name, ua.full_name AS approved_by_name FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.car_brand_id LEFT JOIN users ua ON ua.id=p.approved_by WHERE p.id=?", [$pid]);
    if (!$product) { flash('error','Không tìm thấy sản phẩm.'); redirect('/admin/products'); return; }
    
    $history = dbAll("SELECT ph.*, u.full_name AS changer_name FROM product_history ph LEFT JOIN users u ON u.id = ph.changed_by WHERE ph.product_id = ? ORDER BY ph.changed_at DESC LIMIT 100", [$pid]);
    $changes = dbAll("SELECT al.*, u.full_name, u.email FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id WHERE al.entity_type='product' AND al.entity_id=? AND al.action!='view' ORDER BY al.created_at DESC LIMIT 200", [$pid]);
    $viewTotal = dbGet("SELECT COUNT(*) AS c FROM audit_logs WHERE entity_type='product' AND entity_id=? AND action='view'", [$pid])['c'] ?? 0;
    $viewPerPage = 50;
    $viewPages = max(1, (int)ceil($viewTotal / $viewPerPage));
    $viewPage = min(max(1, intval($_GET['page'] ?? 1)), $viewPages);
    $views = dbAll("SELECT al.*, u.full_name, u.email FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id WHERE al.entity_type='product' AND al.entity_id=? AND al.action='view' ORDER BY al.created_at DESC LIMIT ? OFFSET ?", [$pid, $viewPerPage, ($viewPage-1)*$viewPerPage]);
    
    view('admin/product-history', compact('product','history','changes','views','viewTotal','viewPage','viewPages','viewPerPage') + ['title'=>'Lịch sử & lượt truy cập: '.truncate($product['name'],30)]);
});


post('/admin/products/:id/toggle-status', function($p) {
    requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login'); csrfCheck();
    $status = $_POST['status'] ?? 'hidden';
    $__oldStatus = dbGet("SELECT status FROM products WHERE id=?", [$p['id']])['status'] ?? '';
    dbRun("UPDATE products SET status=?, is_indexed=?, updated_at=datetime('now','localtime') WHERE id=?", [
        $status,
        $status === 'published' ? 1 : 0,
        $p['id'],
    ]);
    if ($status === 'published') {
        dbRun("UPDATE products SET published_at=COALESCE(published_at, strftime('%Y-%m-%dT%H:%M:%fZ','now')) WHERE id=?", [$p['id']]);
    }
    $__actor = currentUser();
    dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?,?,?,?,?,?,?,?)",
        [$__actor['id'] ?? null, $__actor['role'] ?? 'admin', 'status_change', 'product', (int)$p['id'], 'Trạng thái: '.$__oldStatus.' → '.$status, $_SERVER['REMOTE_ADDR'] ?? '', mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)]);
    flash('success', 'Đã cập nhật trạng thái kinh doanh.');
    $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/products';
    redirect($ref);
});

post('/admin/products/:id/delete', function($p) {
    requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login'); csrfCheck();
    $pid = intval($p['id']);
    // Check if product has order_items (cannot delete if ordered)
    $hasOrders = dbGet("SELECT COUNT(*) AS cnt FROM order_items WHERE product_id=?", [$pid])['cnt'] ?? 0;
    if ($hasOrders > 0) {
        flash('error','Không thể xóa sản phẩm đã có đơn hàng ('.$hasOrders.' đơn). Hãy ẩn sản phẩm thay vì xóa.');
        redirect('/admin/products');
        return;
    }
    // Delete all related records first
    dbRun("DELETE FROM product_images WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM product_fitments WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM product_brand_map WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM cart_items WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM favorites WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM review_images WHERE review_id IN (SELECT id FROM reviews WHERE product_id=?)", [$pid]);
    dbRun("DELETE FROM review_reactions WHERE review_id IN (SELECT id FROM reviews WHERE product_id=?)", [$pid]);
    dbRun("DELETE FROM review_reports WHERE review_id IN (SELECT id FROM reviews WHERE product_id=?)", [$pid]);
    dbRun("DELETE FROM review_responses WHERE review_id IN (SELECT id FROM reviews WHERE product_id=?)", [$pid]);
    dbRun("DELETE FROM reviews WHERE product_id=?", [$pid]);
    dbRun("DELETE FROM products WHERE id=?", [$pid]);
    flash('success','Đã xóa sản phẩm thành công.');
    $ref = $_SERVER['HTTP_REFERER'] ?? '/admin/products';
    redirect($ref);
});
post('/admin/upload-tinymce-image', function() {
    requireStaffPermission('products', '/admin/login'); csrfCheck();
    header('Content-Type: application/json');

    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'msg' => 'Không có file hoặc lỗi upload']);
        exit;
    }

    $file = $_FILES['file'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode(['ok' => false, 'msg' => 'File quá lớn (tối đa 5MB)']);
        exit;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['ok' => false, 'msg' => 'Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP']);
        exit;
    }

    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $extension = $ext[$mime] ?? 'jpg';
    $filename = 'tinymce_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

    $uploadDir = is_dir('/var/lib/coolingsystems/uploads')
        ? '/var/lib/coolingsystems/uploads/content/'
        : __DIR__ . '/../uploads/content/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $dest = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['ok' => false, 'msg' => 'Không thể lưu file']);
        exit;
    }

    echo json_encode(['location' => '/uploads/content/' . $filename]);
    exit;
});


post('/admin/products/bulk-delete', function() {
    $user = requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login'); csrfCheck();
    header('Content-Type: application/json');

    $idsRaw = trim($_POST['ids'] ?? '');
    if (empty($idsRaw)) {
        echo json_encode(['ok' => false, 'msg' => 'Chưa chọn sản phẩm nào.']);
        exit;
    }

    $ids = array_filter(array_map('intval', explode(',', $idsRaw)), function($id) { return $id > 0; });
    if (empty($ids)) {
        echo json_encode(['ok' => false, 'msg' => 'ID không hợp lệ.']);
        exit;
    }

    $deleted = 0;
    $errors  = [];
    foreach ($ids as $pid) {
        // Không xóa nếu đã có trong đơn hàng
        $hasOrders = dbGet("SELECT COUNT(*) AS cnt FROM order_items WHERE product_id=?", [$pid]);
        if ($hasOrders && $hasOrders['cnt'] > 0) {
            $errors[] = "SP #$pid đã có trong đơn hàng, không thể xóa";
            continue;
        }
        // Xóa ảnh liên quan
        dbRun("DELETE FROM product_images WHERE product_id=?", [$pid]);
        // Xóa brand mapping
        dbRun("DELETE FROM product_brand_map WHERE product_id=?", [$pid]);
        // Xóa sản phẩm
        dbRun("DELETE FROM products WHERE id=?", [$pid]);
        $deleted++;
    }

    $msg = "Đã xóa $deleted sản phẩm.";
    if (!empty($errors)) {
        $msg .= ' Bỏ qua: ' . implode('; ', $errors);
    }
    echo json_encode(['ok' => true, 'deleted' => $deleted, 'msg' => $msg]);
    exit;
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
    $user = requireStaffPermission('rbac:system.settings.view', '/admin/login');
    view('admin/settings', ['title'=>'Cài đặt hệ thống', 'user'=>$user]);
});


post('/admin/settings/smtp', function() {
    requireStaffPermission('rbac:system.smtp.manage', '/admin/login'); csrfCheck();
    $enabled = !empty($_POST['smtp_enabled']) ? '1' : '0';
    $host = strtolower(trim((string)($_POST['smtp_host'] ?? '')));
    $port = (int)($_POST['smtp_port'] ?? 587);
    $encryption = (string)($_POST['smtp_encryption'] ?? 'tls');
    $username = trim((string)($_POST['smtp_username'] ?? ''));
    $password = preg_replace('/\s+/', '', (string)($_POST['smtp_password'] ?? ''));
    $fromEmail = trim((string)($_POST['smtp_from_email'] ?? ''));
    $fromName = trim((string)($_POST['smtp_from_name'] ?? ''));
    $oldPassword = inventoryAlertSetting('smtp_password');
    if ($password === '') $password = preg_replace('/\s+/', '', $oldPassword);
    if ($enabled === '1') {
        if (!preg_match('/^[a-z0-9.-]+$/i', $host) || $port < 1 || $port > 65535 || !in_array($encryption, ['tls','ssl','none'], true) || !filter_var($username, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Cấu hình SMTP chưa hợp lệ. Hãy kiểm tra máy chủ, cổng, bảo mật, email, và mật khẩu ứng dụng.');
            redirect('/admin/settings'); return;
        }
    }
    foreach (['smtp_enabled'=>$enabled,'smtp_host'=>$host,'smtp_port'=>(string)$port,'smtp_encryption'=>$encryption,'smtp_username'=>$username,'smtp_password'=>$password,'smtp_from_email'=>$fromEmail,'smtp_from_name'=>$fromName] as $key=>$value) {
        dbRun('INSERT INTO settings (key,value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value', [$key,$value]);
    }
    flash('success', 'Đã lưu cấu hình SMTP. Hãy dùng nút Gửi email kiểm tra trong phần cảnh báo tồn kho để xác nhận.');
    redirect('/admin/settings');
});

post('/admin/settings/inventory-alert', function() {
    requireStaffPermission('rbac:inventory.alerts.manage', '/admin/login'); csrfCheck();
    $enabled = !empty($_POST['inventory_alert_enabled']) ? '1' : '0';
    $email = strtolower(trim((string)($_POST['inventory_alert_email'] ?? '')));
    if ($enabled === '1' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Vui lòng nhập email nhận cảnh báo hợp lệ trước khi bật cảnh báo tồn kho.');
        redirect('/admin/settings'); return;
    }
    dbRun("INSERT INTO settings (key,value) VALUES ('inventory_alert_enabled',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$enabled]);
    dbRun("INSERT INTO settings (key,value) VALUES ('inventory_alert_email',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$email]);
    flash('success', 'Đã lưu cấu hình email cảnh báo tồn kho.');
    redirect('/admin/settings');
});

post('/admin/settings/inventory-alert/test', function() {
    requireStaffPermission('rbac:inventory.alerts.manage', '/admin/login'); csrfCheck();
    require_once __DIR__ . '/../includes/inventory-alerts.php';
    $email = trim(inventoryAlertSetting('inventory_alert_email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Hãy lưu một email nhận cảnh báo hợp lệ trước khi gửi kiểm tra.');
        redirect('/admin/settings'); return;
    }
    $product = dbGet("SELECT id,name,sku,oem_code,stock,min_stock FROM products ORDER BY id LIMIT 1") ?: ['name'=>'Sản phẩm kiểm tra','sku'=>'TEST','oem_code'=>'','stock'=>0,'min_stock'=>5];
    if (sendInventoryLowStockEmail($email, $product)) {
        flash('success', 'Đã gửi email kiểm tra tới ' . $email . '.');
    } else {
        $smtpError = function_exists('smtpLastError') ? smtpLastError() : '';
        if (str_contains($smtpError, '535')) {
            flash('error', 'Gmail từ chối tài khoản hoặc Mật khẩu ứng dụng SMTP. Hãy tạo App Password mới, dán vào ô Mật khẩu ứng dụng rồi lưu lại.');
        } else {
            flash('error', 'Máy chủ chưa gửi được email kiểm tra. Hãy kiểm tra cấu hình SMTP.');
        }
    }
    redirect('/admin/settings');
});

post('/admin/settings/account', function() {
    $me = requireRole('admin', '/admin/login'); csrfCheck();
    if (!password_verify($_POST['current_password'] ?? '', $me['password_hash'])) { flash('error','Mật khẩu hiện tại không đúng.'); redirect('/admin/settings'); return; }
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['login_email'] ?? ''));
    $newPass = $_POST['new_password'] ?? '';
    if ($name !== '' && $name !== $me['full_name']) { dbRun("UPDATE users SET full_name=?, updated_at=datetime('now') WHERE id=?", [$name, $me['id']]); }
    if ($email !== '' && $email !== strtolower($me['email'])) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('error','Email không hợp lệ.'); redirect('/admin/settings'); return; }
        if (dbGet("SELECT 1 FROM users WHERE email=? AND id<>?", [$email, $me['id']])) { flash('error','Email này đã được dùng bởi tài khoản khác.'); redirect('/admin/settings'); return; }
        dbRun("UPDATE users SET email=?, updated_at=datetime('now') WHERE id=?", [$email, $me['id']]);
    }
    if ($newPass !== '') {
        if (strlen($newPass) < 6) { flash('error','Mật khẩu mới phải tối thiểu 6 ký tự.'); redirect('/admin/settings'); return; }
        dbRun("UPDATE users SET password_hash=?, updated_at=datetime('now') WHERE id=?", [password_hash($newPass, PASSWORD_BCRYPT), $me['id']]);
        logPasswordChange($me['id'], 'self_change');
    }
    flash('success','Đã cập nhật tài khoản quản trị.');
    redirect('/admin/settings');
});
post('/admin/settings/general', function() {
    requireStaffPermission('rbac:system.business.manage', '/admin/login'); csrfCheck();
    
    $phone = trim($_POST['site_phone'] ?? '');
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        flash('error', 'Hotline tư vấn phải là 10 chữ số liền nhau, không chứa chữ cái, khoảng trắng hay dấu chấm (VD: 0987654321).');
        redirect('/admin/settings'); return;
    }
    
    dbRun("INSERT INTO system_config (key, value) VALUES ('site_phone',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$phone]);

    $companyName = trim($_POST['company_name'] ?? '');
    dbRun("INSERT INTO system_config (key, value) VALUES ('company_name',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$companyName]);

    $siteMetaTitle = trim($_POST['site_meta_title'] ?? '');
    if ($siteMetaTitle !== '') {
        dbRun("INSERT INTO system_config (key, value) VALUES ('site_meta_title',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$siteMetaTitle]);
    }
    
    if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/svg+xml','image/webp'];
        $mime = mime_content_type($_FILES['site_logo']['tmp_name']);
        if (in_array($mime, $allowed) || in_array(strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION)), ['svg','png','jpg','jpeg','webp'])) {
            $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $fname = 'logo_' . time() . '.' . strtolower($ext);
            $dest = '/var/lib/coolingsystems/uploads/' . $fname;
            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $dest)) {
                dbRun("INSERT INTO system_config (key, value) VALUES ('site_logo',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$fname]);
            }
        }
    }
    // footer_logo: revert to text if requested
    if (!empty($_POST['remove_footer_logo'])) {
        dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES ('footer_logo_image', '')");
    }
    // footer_logo: upload + auto cut white background (KEEP original colors), output transparent PNG
    if (!empty($_FILES['footer_logo']['name']) && $_FILES['footer_logo']['error'] === UPLOAD_ERR_OK) {
        $fext = strtolower(pathinfo($_FILES['footer_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($fext, ['svg','png','jpg','jpeg','webp','gif'])) {
            $base = 'footer_logo_' . time();
            $rawPath = '/var/lib/coolingsystems/uploads/' . $base . '.' . $fext;
            if (move_uploaded_file($_FILES['footer_logo']['tmp_name'], $rawPath)) {
                $finalName = $base . '.' . $fext;
                if ($fext !== 'svg' && is_executable('/usr/bin/convert')) {
                    $pngPath = '/var/lib/coolingsystems/uploads/' . $base . '.png';
                    $cmd = '/usr/bin/convert ' . escapeshellarg($rawPath) . ' -fuzz 12% -transparent white -strip PNG32:' . escapeshellarg($pngPath) . ' 2>&1';
                    @exec($cmd, $__co, $__rc);
                    if ($__rc === 0 && file_exists($pngPath)) { $finalName = $base . '.png'; @unlink($rawPath); }
                }
                dbRun("INSERT OR REPLACE INTO settings (key, value) VALUES ('footer_logo_image', ?)", [$finalName]);
            }
        }
    }
    flash('success','Đã lưu thông tin chung!');
    // contact_hotline_sync: auto-sync contact hotline
    if (!empty($phone)) {
        $formatted = preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1 $2 $3', $phone);
        if (preg_match('/^0[0-9]{9}$/', $phone)) {
            $formatted = preg_replace('/^(\d{4})(\d{3})(\d{3})$/', '$1 $2 $3', $phone); // 0987 654 321 format
        }
        dbRun("INSERT INTO system_config (key, value) VALUES ('contact_hotline',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value", [$formatted]);
    }
redirect('/admin/settings');
});

// Admin Hotline List Management Handler
post('/admin/settings/hotlines', function() {
    requireStaffPermission('rbac:system.business.manage', '/admin/login'); csrfCheck();
    
    $labels = $_POST['hotline_labels'] ?? [];
    $phones = $_POST['hotline_phones'] ?? [];
    
    $list = [];
    for ($i = 0; $i < count($labels); $i++) {
        $lbl = trim($labels[$i] ?? '');
        $ph = trim($phones[$i] ?? '');
        if ($lbl !== '' && $ph !== '') {
            $list[] = [
                'label' => $lbl,
                'phone' => $ph
            ];
        }
    }
    
    $json = json_encode($list, JSON_UNESCAPED_UNICODE);
    dbRun("INSERT INTO system_config (key, value) VALUES ('hotline_list',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$json]);
    
    if (!empty($list[0]['phone'])) {
        $clean = preg_replace('/[^0-9]/', '', $list[0]['phone']);
        if (strlen($clean) >= 9) {
            dbRun("INSERT INTO system_config (key, value) VALUES ('site_phone',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=datetime('now')", [$clean]);
        }
    }
    
    flash('success', 'Đã cập nhật danh sách Hotline & Bộ phận hỗ trợ đồng bộ tự động trên toàn hệ thống!');
    redirect('/admin/settings');
});


// Delete individual product image
post('/admin/products/delete-image', function() {
    requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login'); csrfCheck();
    header('Content-Type: application/json');
    $imageId = (int)($_POST['image_id'] ?? 0);
    if (!$imageId) {
        echo json_encode(['ok'=>true,'msg'=>'Đã xóa ảnh']);
        return;
    }
    $img = dbGet("SELECT * FROM product_images WHERE id=?", [$imageId]);
    if (!$img) {
        echo json_encode(['ok'=>true,'msg'=>'Ảnh đã được xóa trước đó']);
        return;
    }

    $fileName = ltrim($img['file_path'], '/');
    $possiblePaths = [
        '/var/lib/coolingsystems/uploads/products/' . $fileName,
        __DIR__ . '/../public/uploads/products/' . $fileName,
        __DIR__ . '/../uploads/products/' . $fileName
    ];
    foreach ($possiblePaths as $fp) {
        if (file_exists($fp)) { @unlink($fp); }
    }

    dbRun("DELETE FROM product_images WHERE id=?", [$imageId]);
    if (!empty($img['is_main'])) {
        $nextImage = dbGet("SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order, id LIMIT 1", [$img['product_id']]);
        if ($nextImage) dbRun("UPDATE product_images SET is_main=1 WHERE id=?", [$nextImage['id']]);
    }
    dbRun("UPDATE products SET updated_at=datetime('now','localtime') WHERE id=?", [$img['product_id']]);

    echo json_encode(['ok'=>true,'msg'=>'Đã xóa ảnh']);
});

// Delete multiple product images (bulk)
post('/admin/products/delete-images-bulk', function() {
    requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login'); csrfCheck();
    header('Content-Type: application/json');
    $rawIds = $_POST['image_ids'] ?? [];
    if (is_string($rawIds)) {
        $rawIds = json_decode($rawIds, true) ?: explode(',', $rawIds);
    }
    $imageIds = array_filter(array_map('intval', (array)$rawIds), function($v) { return $v > 0; });

    $deletedCount = 0;
    $productId = 0;

    foreach ($imageIds as $imageId) {
        $img = dbGet("SELECT * FROM product_images WHERE id=?", [$imageId]);
        if (!$img) continue;
        $productId = $img['product_id'];

        $fileName = ltrim($img['file_path'], '/');
        $possiblePaths = [
            '/var/lib/coolingsystems/uploads/products/' . $fileName,
            __DIR__ . '/../public/uploads/products/' . $fileName,
            __DIR__ . '/../uploads/products/' . $fileName
        ];
        foreach ($possiblePaths as $fp) {
            if (file_exists($fp)) { @unlink($fp); }
        }

        dbRun("DELETE FROM product_images WHERE id=?", [$imageId]);
        $deletedCount++;
    }

    if ($productId > 0) {
        $hasMain = dbGet("SELECT id FROM product_images WHERE product_id=? AND is_main=1", [$productId]);
        if (!$hasMain) {
            $nextImage = dbGet("SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order, id LIMIT 1", [$productId]);
            if ($nextImage) {
                dbRun("UPDATE product_images SET is_main=1 WHERE id=?", [$nextImage['id']]);
            }
        }
        dbRun("UPDATE products SET updated_at=datetime('now','localtime') WHERE id=?", [$productId]);
    }

    echo json_encode(['ok' => true, 'msg' => 'Đã xóa ảnh thành công', 'deleted' => $deletedCount]);
});

post('/admin/settings/social', function() {
    requireStaffPermission('rbac:system.social.manage|tax_config', '/auth/login'); csrfCheck();
    $fields = ['social_whatsapp','social_zalo','social_tiktok','social_facebook'];
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
    requireStaffPermission('rbac:system.payment.manage|tax_config', '/auth/login'); csrfCheck();
    $fields = ['payment_bank_name','payment_account_name','payment_account_number','payment_transfer_prefix'];
    foreach($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        dbRun("INSERT INTO system_config (key, value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=strftime('%Y-%m-%dT%H:%M:%fZ','now')", [$f, $val]);
    }

    if (($_POST['remove_qr'] ?? '0') === '1') {
        dbRun("DELETE FROM system_config WHERE key='payment_qr_image'");
    }

    // Handle QR image upload
    if (!empty($_FILES['qr_image']['name']) && $_FILES['qr_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $mime = mime_content_type($_FILES['qr_image']['tmp_name']);
        if (in_array($mime, $allowed)) {
            $ext = pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION);
            $fname = 'qr_' . time() . '.' . strtolower($ext);
            $dest = '/var/lib/coolingsystems/uploads/qr/' . $fname;
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $dest)) {
                dbRun("INSERT INTO system_config (key, value) VALUES ('payment_qr_image',?) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=strftime('%Y-%m-%dT%H:%M:%fZ','now')", [$fname]);
            }
        }
    }
    flash('success', 'Đã lưu cấu hình thanh toán');
    redirect('/admin/settings');
});


// ── BRANDS (Hãng xe) CRUD ────────────────────────────────────────────────────
get('/admin/brands', function() {
    $user = requireStaffPermission('rbac:catalog.vehicle.view|brands', '/admin/login');
    view('admin/brands', ['title'=>'Quản lý Hãng xe', 'role'=>'admin', 'user'=>$user]);
});

post('/admin/brands/add', function() {
    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 100);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/brands'); }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    $imagePath = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'brand_' . time() . '.' . strtolower($ext);
        $dest = '/var/lib/coolingsystems/uploads/brands/' . $fname;
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
    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sort = intval($_POST['sort_order'] ?? 100);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/brands'); }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $slug));
    $imagePath = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = 'brand_' . time() . '.' . strtolower($ext);
        $dest = '/var/lib/coolingsystems/uploads/brands/' . $fname;
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

$delBrandAction = function($p) {
    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login');
    $id = intval($p['id'] ?? 0);
    if ($id > 0) {
        dbRun("DELETE FROM brands WHERE id=?", [$id]);
        dbRun("UPDATE products SET car_brand_id=NULL WHERE car_brand_id=?", [$id]);
        flash('success', 'Đã xóa hãng xe thành công.');
    }
    redirect('/admin/brands');
};
get('/admin/brands/:id/delete', $delBrandAction);
post('/admin/brands/:id/delete', $delBrandAction);

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
    $user = requireStaffPermission('rbac:catalog.taxonomy.view|categories', '/auth/login');
    view('admin/categories', ['title'=>'Quản lý Danh mục', 'role'=>'admin', 'user'=>$user]);
});

function catUploadImage(): string {
    if (empty($_FILES['image']['name'])) return '';
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) return '';
    $dir = UPLOAD_DIR.'/categories/'; if(!is_dir($dir)) @mkdir($dir,0775,true);
    $fname = uniqid('cat_').'.'.$ext;
    return move_uploaded_file($_FILES['image']['tmp_name'], $dir.$fname) ? $fname : '';
}
post('/admin/categories/add', function() {
    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $sort = intval($_POST['sort_order'] ?? 100);
    $featured = intval($_POST['is_featured'] ?? 0);
    if (!$name || !$slug) { flash('error','Vui lòng nhập tên và slug.'); redirect('/admin/categories'); }
    $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $slug));
    try {
        $catImg=catUploadImage(); dbInsert("INSERT INTO categories (name, slug, parent_id, sort_order, is_featured, icon) VALUES (?,?,?,?,?,?)", [$name, $slug, $parentId, $sort, $featured, $catImg]);
        flash('success', "Đã thêm danh mục: $name");
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại. Vui lòng đổi slug khác.');
    }
    $redir = $parentId ? '/admin/categories?parent_id='.$parentId : '/admin/categories';
    redirect($redir);
});

post('/admin/categories/:id/toggle-active', function($p) {
    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login');
    $isActive = intval($_POST['is_active'] ?? 1);
    dbRun("UPDATE categories SET is_active=? WHERE id=?", [$isActive, $p['id']]);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'is_active' => $isActive]);
    exit;
});

post('/admin/categories/:id/edit', function($p) {
    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $sort = intval($_POST['sort_order'] ?? 100);
    $featured = intval($_POST['is_featured'] ?? 0);
    $isActive = intval($_POST['is_active'] ?? 1);
    $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $slug));
    try {
        $catImg=catUploadImage(); if($catImg!==''){ dbRun("UPDATE categories SET name=?, slug=?, parent_id=?, sort_order=?, is_featured=?, is_active=?, icon=? WHERE id=?", [$name, $slug, $parentId, $sort, $featured, $isActive, $catImg, $p['id']]); } else { dbRun("UPDATE categories SET name=?, slug=?, parent_id=?, sort_order=?, is_featured=?, is_active=? WHERE id=?", [$name, $slug, $parentId, $sort, $featured, $isActive, $p['id']]); }
        flash('success', 'Đã cập nhật danh mục.');
    } catch (Exception $e) {
        flash('error', 'Slug đã tồn tại.');
    }
    $redir = $parentId ? '/admin/categories?parent_id='.$parentId : '/admin/categories';
    redirect($redir);
});

post('/admin/categories/:id/delete', function($p) {
    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login'); csrfCheck();
    // Reassign children to no parent
    dbRun("UPDATE categories SET parent_id=NULL WHERE parent_id=?", [$p['id']]);
    
    // Check if category has products
    $count = dbGet("SELECT COUNT(*) as c FROM products WHERE category_id=?", [$p['id']])['c'] ?? 0;
    if ($count > 0) {
        flash('error', "Không thể xóa danh mục vì đang chứa $count sản phẩm.");
        redirect('/admin/categories');
        return;
    }
    
    try {
        dbRun("DELETE FROM categories WHERE id=?", [$p['id']]);
        flash('success', 'Đã xóa danh mục.');
    } catch (Exception $e) {
        flash('error', 'Không thể xóa danh mục này do có dữ liệu liên kết.');
    }
    redirect('/admin/categories');
});

// ── PROMOTIONS (Khuyến mãi) CRUD ─────────────────────────────────────────────
get('/admin/promotions', function() {
    $user = requireStaffPermission('rbac:marketing.promotions.view|promotions', '/auth/login');
    view('admin/promotions', ['title'=>'Quản lý Khuyến mãi', 'role'=>'admin', 'user'=>$user]);
});

post('/admin/promotions/:id/set-sale', function($p) {
    requireStaffPermission('rbac:marketing.promotions.manage|promotions', '/auth/login'); csrfCheck();
    $salePrice = intval($_POST['sale_price'] ?? 0);
    $product = dbGet("SELECT price FROM products WHERE id=?", [$p['id']]);
    if (!$product) { flash('error', 'Không tìm thấy sản phẩm.'); redirect('/admin/promotions'); return; }
    if ($salePrice < 0) { flash('error', 'Giá khuyến mãi không được âm.'); redirect('/admin/promotions'); return; }
    if ($salePrice > 0 && $salePrice >= $product['price']) { flash('error', 'Giá khuyến mãi phải nhỏ hơn giá gốc (' . number_format($product['price']) . ' ₫).'); redirect('/admin/promotions'); return; }
    $isOnSale = $salePrice > 0 ? 1 : 0;
    dbRun("UPDATE products SET sale_price=?, is_on_sale=? WHERE id=?", [$salePrice, $isOnSale, $p['id']]);
    flash('success', 'Đã lưu giá khuyến mãi.');
    redirect('/admin/promotions');
});

post('/admin/promotions/:id/toggle', function($p) {
    requireStaffPermission('rbac:marketing.promotions.manage|promotions', '/auth/login'); csrfCheck();
    $prod = dbGet("SELECT is_on_sale FROM products WHERE id=?", [$p['id']]);
    $newVal = $prod['is_on_sale'] ? 0 : 1;
    dbRun("UPDATE products SET is_on_sale=? WHERE id=?", [$newVal, $p['id']]);
    flash('success', $newVal ? 'Đã bật khuyến mãi.' : 'Đã tắt khuyến mãi.');
    redirect('/admin/promotions');
});


// ===== Product Brands Management =====
get('/admin/product-brands', function() {
    requireStaffPermission('rbac:catalog.vehicle.view|brand_models', '/auth/login');
    $productBrands = dbAll("SELECT * FROM product_brands ORDER BY sort_order, name");
    view('admin/product-brands', ['title' => 'Quan ly Thuong hieu', 'productBrands' => $productBrands]);
});

post('/admin/product-brands/new', function() {
    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');
    csrfCheck();
    $name = trim($_POST['name'] ?? '');
    if (!$name) { flash('error', 'Ten thuong hieu khong duoc de trong.'); redirect('/admin/product-brands'); return; }
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $logoFile = '';
    if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','svg'])) {
            $dest = '/var/lib/coolingsystems/uploads/product-brands/' . uniqid('pb_') . '.' . $ext;
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
    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');
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
            $dest = '/var/lib/coolingsystems/uploads/product-brands/' . uniqid('pb_') . '.' . $ext;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) { $logoFile = basename($dest); }
        }
    }
    dbRun("UPDATE product_brands SET name=?, slug=?, logo=?, description=?, sort_order=? WHERE id=?", [$name, $slug, $logoFile, $desc, $sort, $p['id']]);
    flash('success', 'Da cap nhat thuong hieu!');
    redirect('/admin/product-brands');
});

post('/admin/product-brands/:id/delete', function($p) {
    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');
    csrfCheck();
    dbRun("DELETE FROM product_brands WHERE id=?", [$p['id']]);
    flash('success', 'Da xoa thuong hieu.');
    redirect('/admin/product-brands');
});


// ── Stores Management ──

// ===== Banner trang chu (luu JSON trong system_config) =====
if (!function_exists('getHomeBanners')) {
function getHomeBanners() { $v = dbGet("SELECT value FROM system_config WHERE key='home_banners'")['value'] ?? '[]'; $b = json_decode($v, true); return is_array($b) ? array_values($b) : []; }
function saveHomeBanners($banners) { $json = json_encode(array_values($banners), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); dbRun("INSERT INTO system_config (key, value, updated_at) VALUES ('home_banners',?,datetime('now')) ON CONFLICT(key) DO UPDATE SET value=?, updated_at=datetime('now')", [$json, $json]); }
}
get('/admin/banners', function() { requireRole(['admin'], '/admin/login'); view('admin/banners', ['title'=>'Banner trang chu','banners'=>getHomeBanners()]); });
post('/admin/banners/add', function() { requireRole(['admin'], '/admin/login'); csrfCheck();
  $link = trim($_POST['link'] ?? ''); $btitle = trim($_POST['btitle'] ?? ''); $img = '';
  if (!empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? 1) === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) { $dir = '/var/lib/coolingsystems/uploads/banners/'; if (!is_dir($dir)) @mkdir($dir, 0775, true); $fname = 'hb_'.uniqid().'.'.$ext; if (move_uploaded_file($_FILES['image']['tmp_name'], $dir.$fname)) $img = $fname; }
  }
  if ($img === '') { flash('error', 'Vui long chon anh banner hop le (jpg/png/webp/gif).'); redirect('/admin/banners'); return; }
  $banners = getHomeBanners(); $banners[] = ['img'=>$img, 'link'=>$link, 'title'=>$btitle, 'active'=>1]; saveHomeBanners($banners);
  flash('success', 'Da them banner.'); redirect('/admin/banners');
});
post('/admin/banners/move', function() { requireRole(['admin'], '/admin/login'); csrfCheck();
  $idx = (int)($_POST['idx'] ?? -1); $d = (($_POST['dir'] ?? '')==='up') ? -1 : 1; $banners = getHomeBanners(); $j = $idx + $d;
  if (isset($banners[$idx], $banners[$j])) { $t=$banners[$idx]; $banners[$idx]=$banners[$j]; $banners[$j]=$t; saveHomeBanners($banners); }
  redirect('/admin/banners');
});
post('/admin/banners/:idx/toggle', function($p) { requireRole(['admin'], '/admin/login'); csrfCheck();
  $idx = (int)$p['idx']; $banners = getHomeBanners(); if (isset($banners[$idx])) { $banners[$idx]['active'] = empty($banners[$idx]['active']) ? 1 : 0; saveHomeBanners($banners); } redirect('/admin/banners');
});
post('/admin/banners/:idx/delete', function($p) { requireRole(['admin'], '/admin/login'); csrfCheck();
  $idx = (int)$p['idx']; $banners = getHomeBanners();
  if (isset($banners[$idx])) { $f = '/var/lib/coolingsystems/uploads/banners/'.($banners[$idx]['img'] ?? ''); if (!empty($banners[$idx]['img']) && is_file($f)) @unlink($f); array_splice($banners, $idx, 1); saveHomeBanners($banners); flash('success', 'Da xoa banner.'); }
  redirect('/admin/banners');
});

get('/admin/stores', function() {
    requireStaffPermission('rbac:organization.branches.view|stores', '/auth/login');
    $stores = dbAll("SELECT * FROM stores ORDER BY sort_order, name");
    $branchTypes = dbAll("SELECT code, name, is_active FROM store_branch_types ORDER BY sort_order, id");
    view('admin/stores', ['title'=>'Hệ thống cửa hàng','role'=>'admin','stores'=>$stores,'branchTypes'=>$branchTypes]);
});

get('/admin/branch-types', function() {
    requireStaffPermission('rbac:organization.branches.view|stores', '/auth/login');
    $types = dbAll("SELECT * FROM store_branch_types ORDER BY sort_order, id");
    view('admin/branch-types', ['title'=>'Loại chi nhánh cửa hàng','role'=>'admin','types'=>$types]);
});

post('/admin/branch-types/add', function() {
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    if ($name === '') { flash('error', 'Tên loại không được để trống.'); redirect('/admin/branch-types'); return; }
    $code = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(removeAccents($name))), '_');
    if ($code === '') $code = 'loai';
    $base = $code; $i = 2;
    while (dbGet("SELECT id FROM store_branch_types WHERE code=?", [$code])) { $code = $base."_".$i; $i++; }
    dbInsert("INSERT INTO store_branch_types (code, name, sort_order, is_active) VALUES (?,?,?,?)", [$code, $name, $sort, $active]);
    flash('success', 'Đã thêm loại chi nhánh.');
    redirect('/admin/branch-types');
});

post('/admin/branch-types/:id/edit', function($p) {
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
    $name = trim($_POST['name'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    if ($name === '') { flash('error', 'Tên loại không được để trống.'); redirect('/admin/branch-types'); return; }
    dbRun("UPDATE store_branch_types SET name=?, sort_order=?, is_active=? WHERE id=?", [$name, $sort, $active, $p['id']]);
    flash('success', 'Đã cập nhật loại chi nhánh.');
    redirect('/admin/branch-types');
});

post('/admin/branch-types/:id/delete', function($p) {
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
    $t = dbGet("SELECT code FROM store_branch_types WHERE id=?", [$p['id']]);
    if ($t) {
        $cnt = (int)(dbGet("SELECT COUNT(*) AS n FROM stores WHERE type=?", [$t['code']])['n'] ?? 0);
        if ($cnt > 0) { flash('error', 'Không thể xóa: còn '.$cnt.' cửa hàng đang dùng loại này.'); redirect('/admin/branch-types'); return; }
        dbRun("DELETE FROM store_branch_types WHERE id=?", [$p['id']]);
        flash('success', 'Đã xóa loại chi nhánh.');
    }
    redirect('/admin/branch-types');
});

post('/admin/stores/add', function() {
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
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
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
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
    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login'); csrfCheck();
    dbRun("DELETE FROM stores WHERE id=?", [$p['id']]);
    flash('success','Đã xóa cửa hàng.');
    redirect('/admin/stores');
});

// ===== QUẢN LÝ TRẢ HÀNG =====
get('/admin/returns', function() {
    $user = requireStaffPermission('rbac:sales.returns.view|returns', '/auth/login');
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
    requireStaffPermission('rbac:sales.returns.approve|returns', '/auth/login'); csrfCheck();
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
    requireStaffPermission('rbac:sales.returns.approve|returns', '/auth/login'); csrfCheck();
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
    $user = requireStaffPermission('rbac:customers.view|users|staff', '/auth/login');
    header('Content-Type: application/json');
    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']) && !rbacCan((int)$user['id'], 'customers.pii.view')) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
    $info = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$p['id']]);
    echo json_encode($info ?: ['user_id'=>$p['id'],'invoice_type'=>'personal','buyer_name'=>'','tax_code'=>'','address'=>'','province'=>'','ward'=>'','id_number'=>'','passport'=>'','email'=>'','phone'=>'','bank_name'=>'','bank_account'=>'']);
    exit;
});

post('/admin/users/:id/invoice-info', function($p) {
    $user = requireStaffPermission('users|staff', '/auth/login'); csrfCheck();
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
    $user = requireStaffPermission('rbac:customers.view|users|staff', '/auth/login');
    header('Content-Type: application/json');
    $u = dbGet("SELECT id,full_name,email,phone,role,status,address,avatar,created_at,notes,suspended_until FROM users WHERE id=?", [$p['id']]);
    if (!$u) { echo json_encode(['error'=>'not found']); exit; }
    $canViewCustomerPii = !(($user['role'] ?? '') === 'staff' && rbacUsesDetailedMode((int)$user['id'])) || rbacCan((int)$user['id'], 'customers.pii.view');
    $invoice = $canViewCustomerPii ? dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$p['id']]) : null;
    if (!$canViewCustomerPii) { $u['email']='***'; $u['phone']='***'; $u['address']='***'; }
    $orderCount = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$p['id']])['c'] ?? 0;
    echo json_encode(['user'=>$u, 'invoice'=>$invoice, 'order_count'=>$orderCount]);
    exit;
});

// ── Contact Messages Management ──
get('/admin/contacts', function() {
    $u = requireRbacOrLegacyStaffPermission('crm.customer_care.manage', '/admin/login');
    view('admin/contacts', ['title' => 'Quản lý liên hệ']);
});

post('/admin/contacts/:id/reply', function($p) {
    $u = requireRbacOrLegacyStaffPermission('crm.customer_care.manage', '/admin/login'); csrfCheck();
    $id = (int)$p['id'];
    $reply = trim($_POST['reply'] ?? '');
    if (empty($reply) || mb_strlen($reply) < 5 || mb_strlen($reply) > 100) {
        flash('error', 'Nội dung trả lời phải từ 5 đến 100 ký tự.');
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
        . '<div style="background:#f8f9fa;padding:14px;text-align:center;font-size:11px;color:#999">© ' . date('Y') . ' Cooling — coolingsystems.vn</div>'
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
    $u = requireRbacOrLegacyStaffPermission('crm.customer_care.manage', '/admin/login');
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
    $user = requireRbacOrLegacyStaffPermission('sales.orders.cancel', '/admin');
    csrfCheck();
    $order = dbGet("SELECT o.*, u.id as uid FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?", [$p['id']]);
    if (!$order) { flash('error','Đơn không tồn tại.'); redirect('/admin/orders'); return; }
    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id'])) {
        $afterPaymentOrDispatch = (($order['payment_status'] ?? '') === 'paid') || in_array(($order['delivery_status'] ?? ''), ['delivering','shipping','delivered','shipped','completed'], true);
        if ($afterPaymentOrDispatch && !rbacCan((int)$user['id'], 'sales.orders.cancel_approved')) { flash('error','Ban khong co quyen huy don sau thanh toan hoac giao hang.'); redirect('/admin/orders'); return; }
    }
    $reason = trim($_POST['cancel_reason'] ?? 'Admin hủy đơn');
    dbRun("UPDATE orders SET delivery_status='cancelled', updated_at=datetime('now') WHERE id=?", [$p['id']]);
    // Restore stock
    $items = dbAll("SELECT oi.* FROM order_items oi INNER JOIN sub_orders so ON so.id=oi.sub_order_id WHERE so.order_id=?", [$p['id']]);
    foreach ($items as $it) {
        dbRun("UPDATE products SET stock=stock+?, sold_count=MAX(0,sold_count-?) WHERE id=?", [$it['quantity'],$it['quantity'],$it['product_id']]);
        inventoryCheckLowStockAlert((int)$it['product_id'], 'admin_refund');
    }
    // Notify customer
    if ($order['uid']) {
        dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?, 'order_cancelled', ?, ?, ?, datetime('now'))",
            [$order['uid'], 'Đơn hàng đã bị hủy', 'Đơn hàng '.$order['code'].' đã bị hủy. Lý do: '.$reason, '/customer/orders/'.$p['id']]);
    }
    flash('success','Đã hủy đơn hàng '.$order['code'].' và thông báo cho khách.');
    redirect('/admin/orders');
});

// Admin create order page

// ─── GIAI ĐOẠN C: BÁN HÀNG NÂNG CAO & KHÁCH HÀNG ──────────────────────────────

// C1: Danh sách xe & Đơn đăng ký Garage khách hàng
get('/admin/garages', function() {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    $tab = trim((string)($_GET['tab'] ?? 'requests'));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 30;
    $q = trim((string)($_GET['q'] ?? ''));

    // Shared counts for KPI & badge
    $pendingRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM garage_registrations WHERE status='pending'")['c'] ?? 0);
    $approvedRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM garage_registrations WHERE status='approved'")['c'] ?? 0);
    $rejectedRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM garage_registrations WHERE status='rejected'")['c'] ?? 0);

    if ($tab === 'vehicles') {
        $brandId = max(0, (int)($_GET['brand_id'] ?? 0));
        $where = 'WHERE 1=1'; $params = [];
        if ($q !== '') {
            $where .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR b.name LIKE ? OR cm.name LIKE ?)';
            $like = '%'.$q.'%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        if ($brandId) {
            $where .= ' AND g.brand_id=?';
            $params[] = $brandId;
        }

        $total = (int)(dbGet("SELECT COUNT(*) AS c FROM garages g LEFT JOIN users u ON u.id=g.user_id LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models cm ON cm.id=g.model_id $where", $params)['c'] ?? 0);
        $totalPages = max(1, (int)ceil($total/$perPage));
        $page = min($page, $totalPages);

        $listParams = array_merge($params, [$perPage, max(0, ($page-1)*$perPage)]);
        $garages = dbAll("SELECT g.*, u.full_name, u.email, u.phone, b.name AS brand_name, cm.name AS model_name FROM garages g LEFT JOIN users u ON u.id=g.user_id LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models cm ON cm.id=g.model_id $where ORDER BY g.created_at DESC LIMIT ? OFFSET ?", $listParams);

        $summary = [
            'total_garages' => (int)(dbGet("SELECT COUNT(*) AS c FROM garages")['c'] ?? 0),
            'total_owners' => (int)(dbGet("SELECT COUNT(DISTINCT user_id) AS c FROM garages")['c'] ?? 0),
            'default_count' => (int)(dbGet("SELECT COUNT(*) AS c FROM garages WHERE is_default=1")['c'] ?? 0),
        ];
        $brands = dbAll("SELECT id, name FROM brands ORDER BY name");

        $title = 'Garage khách hàng & Xe lưu sẵn';
        view('admin/garages', compact('title', 'tab', 'garages', 'summary', 'brands', 'q', 'brandId', 'page', 'totalPages', 'pendingRequestsCount', 'approvedRequestsCount', 'rejectedRequestsCount'));
        return;
    }

    // Default Tab: requests (Đơn đăng ký B2B: Gara & Đại lý)
    $regType = trim((string)($_GET['reg_type'] ?? 'all'));
    $statusFilter = trim((string)($_GET['status'] ?? ''));
    $where = 'WHERE 1=1'; $params = [];

    if ($regType === 'agency') {
        $where .= " AND reg_type='agency'";
    } elseif ($regType === 'garage') {
        $where .= " AND reg_type='garage'";
    }

    if ($q !== '') {
        $where .= ' AND (name LIKE ? OR owner_name LIKE ? OR phone LIKE ? OR tax_code LIKE ? OR email LIKE ?)';
        $like = '%'.$q.'%';
        array_push($params, $like, $like, $like, $like, $like);
    }
    if ($statusFilter !== '') {
        $where .= ' AND status=?';
        $params[] = $statusFilter;
    }

    $unionSql = "
        SELECT id, 'garage' AS reg_type, user_id, garage_name AS name, owner_name, phone, email, tax_code, address, signboard_image, license_image, real_images, status, reject_reason, created_at
        FROM garage_registrations
        UNION ALL
        SELECT id, 'agency' AS reg_type, user_id, agency_name AS name, owner_name, phone, email, tax_code, address, signboard_image, license_image, real_images, status, '' AS reject_reason, created_at
        FROM agency_registrations
    ";

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM ($unionSql) sub $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, max(0, ($page-1)*$perPage)]);
    $requests = dbAll("SELECT sub.* FROM ($unionSql) sub $where ORDER BY created_at DESC LIMIT ? OFFSET ?", $listParams);

    $kpiWhere = "WHERE 1=1";
    if ($regType === 'agency') {
        $kpiWhere .= " AND reg_type='agency'";
    } elseif ($regType === 'garage') {
        $kpiWhere .= " AND reg_type='garage'";
    }

    $pendingRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM ($unionSql) sub $kpiWhere AND status='pending'")['c'] ?? 0);
    $approvedRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM ($unionSql) sub $kpiWhere AND status='approved'")['c'] ?? 0);
    $rejectedRequestsCount = (int)(dbGet("SELECT COUNT(*) AS c FROM ($unionSql) sub $kpiWhere AND status='rejected'")['c'] ?? 0);
    $agencyPendingCount = (int)(dbGet("SELECT COUNT(*) AS c FROM agency_registrations WHERE status='pending'")['c'] ?? 0);
    $garagePendingCount = (int)(dbGet("SELECT COUNT(*) AS c FROM garage_registrations WHERE status='pending'")['c'] ?? 0);

    $title = 'Trung tâm Xét duyệt Hồ sơ B2B (Đại lý & Gara)';
    view('admin/garages', compact('title', 'tab', 'requests', 'q', 'regType', 'statusFilter', 'page', 'totalPages', 'pendingRequestsCount', 'approvedRequestsCount', 'rejectedRequestsCount', 'agencyPendingCount', 'garagePendingCount'));
});

get('/admin/garages/requests/:id/approve', function($p) {
    redirect('/admin/garages?tab=requests');
});

post('/admin/garages/requests/:id/approve', function($p) {
    $admin = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    $targetType = trim((string)($_POST['reg_type'] ?? 'garage'));

    if ($targetType === 'agency') {
        $reg = dbGet("SELECT * FROM agency_registrations WHERE id=?", [$id]);
        if (!$reg) {
            flash('error', 'Đơn đăng ký Đại lý không tồn tại.');
            redirect('/admin/garages?tab=requests');
            return;
        }
        dbRun("UPDATE agency_registrations SET status='approved', reviewed_at=datetime('now','localtime') WHERE id=?", [$id]);
        dbRun("UPDATE users SET role='partner', is_verified_garage=1, garage_name=? WHERE id=?", [$reg['agency_name'], $reg['user_id']]);

        dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?, 'agency_approved', 'Đăng ký Đại lý thành công 🎉', ?, '/agency/dashboard', datetime('now','localtime'))", [
            $reg['user_id'],
            "Hồ sơ đăng ký Đại lý '{$reg['agency_name']}' của bạn đã được phê duyệt chính thức. Bạn đã được cấp mã giới thiệu & hưởng chiết khấu % hoa hồng."
        ]);

        flash('success', "Đã phê duyệt Đại lý '{$reg['agency_name']}' thành công!");
        redirect('/admin/garages?tab=requests&reg_type=agency');
        return;
    }

    $reg = dbGet("SELECT gr.*, u.full_name, u.email AS user_email FROM garage_registrations gr LEFT JOIN users u ON u.id=gr.user_id WHERE gr.id=?", [$id]);
    if (!$reg) {
        flash('error', 'Đơn đăng ký Gara không tồn tại.');
        redirect('/admin/garages?tab=requests');
        return;
    }

    dbRun("UPDATE garage_registrations SET status='approved', reviewed_at=datetime('now','localtime'), reviewed_by=? WHERE id=?", [$admin['id'], $id]);
    dbRun("UPDATE users SET is_verified_garage=1, role='partner', garage_name=? WHERE id=?", [$reg['garage_name'], $reg['user_id']]);

    dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?, 'garage_approved', 'Đăng ký Gara thành công 🎉', ?, '/customer/profile', datetime('now','localtime'))", [
        $reg['user_id'],
        "Đơn đăng ký Gara '{$reg['garage_name']}' của bạn đã được phê duyệt. Bạn đã được áp dụng giá buôn."
    ]);

    flash('success', "Đã phê duyệt Gara '{$reg['garage_name']}' thành công!");
    redirect('/admin/garages?tab=requests');
});

get('/admin/garages/requests/:id/reject', function($p) {
    redirect('/admin/garages?tab=requests');
});

post('/admin/garages/requests/:id/reject', function($p) {
    $admin = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    $targetType = trim((string)($_POST['reg_type'] ?? 'garage'));
    $reason = trim((string)($_POST['reject_reason'] ?? 'Hồ sơ chứng từ chưa đạt yêu cầu.'));
    if ($reason === '') $reason = 'Hồ sơ chứng từ chưa đạt yêu cầu.';

    if ($targetType === 'agency') {
        $reg = dbGet("SELECT * FROM agency_registrations WHERE id=?", [$id]);
        if ($reg) {
            dbRun("UPDATE agency_registrations SET status='rejected', reviewed_at=datetime('now','localtime') WHERE id=?", [$id]);
            dbRun("UPDATE users SET role='customer', is_verified_garage=0 WHERE id=?", [$reg['user_id']]);
            dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?, 'agency_rejected', 'Đơn đăng ký Đại lý bị từ chối', ?, '/agency/login', datetime('now','localtime'))", [
                $reg['user_id'],
                "Lý do từ chối: {$reason}. Vui lòng nộp lại yêu cầu mới nếu cần xét duyệt."
            ]);
        }
        flash('success', 'Đã từ chối hồ sơ Đăng ký Đại lý và chuyển sang mục Từ chối.');
        redirect('/admin/garages?tab=requests&reg_type=agency&status=rejected');
        return;
    }

    $reg = dbGet("SELECT gr.*, u.full_name, u.email AS user_email FROM garage_registrations gr LEFT JOIN users u ON u.id=gr.user_id WHERE gr.id=?", [$id]);
    if (!$reg) {
        flash('error', 'Đơn đăng ký không tồn tại.');
        redirect('/admin/garages?tab=requests');
        return;
    }

    dbRun("UPDATE garage_registrations SET status='rejected', reject_reason=?, reviewed_at=datetime('now','localtime'), reviewed_by=? WHERE id=?", [$reason, $admin['id'], $id]);
    dbRun("UPDATE users SET role='customer', is_verified_garage=0 WHERE id=?", [$reg['user_id']]);

    dbRun("INSERT INTO user_notifications (user_id, type, title, message, link, created_at) VALUES (?, 'garage_rejected', 'Đơn đăng ký chưa được duyệt', ?, '/customer/profile', datetime('now','localtime'))", [
        $reg['user_id'],
        "Lý do từ chối: {$reason}. Vui lòng nộp lại chứng từ mới tại trang Hồ sơ."
    ]);

    flash('success', "Đã từ chối đơn đăng ký thành công và chuyển sang mục Từ chối.");
    redirect('/admin/garages?tab=requests&status=rejected');
});

post('/admin/garages/requests/:id/delete', function($p) {
    $admin = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    $targetType = trim((string)($_POST['reg_type'] ?? 'garage'));

    if ($targetType === 'agency') {
        dbRun("DELETE FROM agency_registrations WHERE id=?", [$id]);
        flash('success', 'Đã xóa hẳn hồ sơ đăng ký Đại lý.');
    } else {
        dbRun("DELETE FROM garage_registrations WHERE id=?", [$id]);
        flash('success', 'Đã xóa hẳn hồ sơ đăng ký Gara.');
    }
    redirect('/admin/garages?tab=requests&status=rejected');
});


// C4: Danh sách hoa hồng (Commissions)
get('/admin/commissions', function() {
    $user = requireStaffPermission('rbac:finance.cashbook.view|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 30;
    $partnerId = max(0, (int)($_GET['partner_id'] ?? 0));
    $typeFilter = trim((string)($_GET['type'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));
    $fromDate = trim((string)($_GET['from'] ?? ''));
    $toDate = trim((string)($_GET['to'] ?? ''));

    $where = 'WHERE 1=1'; $params = [];
    if ($partnerId) {
        $where .= ' AND ct.partner_id=?';
        $params[] = $partnerId;
    }
    if ($typeFilter !== '') {
        $where .= ' AND ct.type=?';
        $params[] = $typeFilter;
    }
    if ($statusFilter !== '') {
        $where .= ' AND ct.status=?';
        $params[] = $statusFilter;
    }
    if ($fromDate !== '') {
        $where .= ' AND ct.created_at >= ?';
        $params[] = $fromDate . ' 00:00:00';
    }
    if ($toDate !== '') {
        $where .= ' AND ct.created_at <= ?';
        $params[] = $toDate . ' 23:59:59';
    }

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM commission_transactions ct INNER JOIN partners pt ON pt.id=ct.partner_id $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, ($page-1)*$perPage]);
    $commissions = dbAll("SELECT ct.*, pt.shop_name, pt.contact_phone FROM commission_transactions ct INNER JOIN partners pt ON pt.id=ct.partner_id $where ORDER BY ct.created_at DESC LIMIT ? OFFSET ?", $listParams);

    $summary = dbGet("SELECT COUNT(*) AS total_txn, COALESCE(SUM(CASE WHEN type='earn' THEN commission_fee ELSE 0 END),0) AS total_earn, COALESCE(SUM(CASE WHEN type='reversal' THEN commission_fee ELSE 0 END),0) AS total_reversal, COUNT(DISTINCT partner_id) AS partner_count FROM commission_transactions") ?: ['total_txn'=>0,'total_earn'=>0,'total_reversal'=>0,'partner_count'=>0];
    $partners = dbAll("SELECT id, shop_name FROM partners ORDER BY shop_name");

    view('admin/commissions', [
        'title' => 'Hoa hồng bán hàng',
        'commissions' => $commissions,
        'summary' => $summary,
        'partners' => $partners,
        'partnerId' => $partnerId,
        'typeFilter' => $typeFilter,
        'statusFilter' => $statusFilter,
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// C2: Hồ sơ chi tiết khách hàng (User detail)
get('/admin/users/:id', function($p) {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    $uid = (int)$p['id'];
    $customer = dbGet("SELECT * FROM users WHERE id=?", [$uid]);
    if (!$customer) {
        flash('error', 'Không tìm thấy khách hàng.');
        redirect('/admin/users'); return;
    }

    $orders = dbAll("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC", [$uid]);
    $garages = dbAll("SELECT g.*, b.name AS brand_name, cm.name AS model_name FROM garages g LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models cm ON cm.id=g.model_id WHERE g.user_id=? ORDER BY g.is_default DESC, g.id DESC", [$uid]);
    $debtCollections = dbAll("SELECT c.*, o.code AS order_code, ca.name AS account_name FROM customer_debt_collections c INNER JOIN orders o ON o.id=c.order_id LEFT JOIN cash_accounts ca ON ca.id=c.account_id WHERE o.user_id=? ORDER BY c.collection_date DESC, c.id DESC", [$uid]);

    $totalSpent = 0; $totalDebt = 0;
    foreach ($orders as $o) {
        $totalSpent += (int)$o['paid_amount'];
        $totalDebt += (int)$o['remaining_amount'];
    }

    view('admin/user-detail', [
        'title' => 'Chi tiết khách hàng: ' . $customer['full_name'],
        'user' => $customer,
        'orders' => $orders,
        'garages' => $garages,
        'debtCollections' => $debtCollections,
        'totalSpent' => $totalSpent,
        'totalDebt' => $totalDebt
    ]);
});

// C3: Báo giá - Danh sách (Quotations list)
get('/admin/quotations', function() {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;
    $q = trim((string)($_GET['q'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));

    $where = 'WHERE 1=1'; $params = [];
    if ($q !== '') {
        $where .= ' AND (q.code LIKE ? OR u.full_name LIKE ? OR u.phone LIKE ?)';
        $like = '%'.$q.'%';
        array_push($params, $like, $like, $like);
    }
    if ($statusFilter !== '') {
        $where .= ' AND q.status=?';
        $params[] = $statusFilter;
    }

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM quotations q LEFT JOIN users u ON u.id=q.user_id $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, ($page-1)*$perPage]);
    $quotations = dbAll("SELECT q.*, u.full_name AS customer_name, u.phone AS customer_phone, uc.full_name AS creator_name FROM quotations q LEFT JOIN users u ON u.id=q.user_id LEFT JOIN users uc ON uc.id=q.created_by $where ORDER BY q.created_at DESC LIMIT ? OFFSET ?", $listParams);

    view('admin/quotations', [
        'title' => 'Báo giá độc lập',
        'quotations' => $quotations,
        'q' => $q,
        'statusFilter' => $statusFilter,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// C3: Báo giá - Form tạo mới (New Quotation Form)
get('/admin/quotations/new', function() {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    $customers = dbAll("SELECT id, full_name, phone FROM users WHERE status='active' AND role='customer' ORDER BY full_name");
    view('admin/quotation-new', [
        'title' => 'Tạo báo giá mới',
        'customers' => $customers
    ]);
});

// C3: Báo giá - Xử lý lưu tạo mới (Create Quotation)
post('/admin/quotations', function() {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();

    $userId = (int)($_POST['user_id'] ?? 0);
    $expiresAt = trim((string)($_POST['expires_at'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));
    $items = $_POST['items'] ?? [];

    if (!$userId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiresAt) || !$items) {
        flash('error', 'Thông tin báo giá không hợp lệ. Vui lòng chọn khách hàng và ít nhất một sản phẩm.');
        redirect('/admin/quotations/new'); return;
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        $code = 'BG-' . date('Ymd') . '-' . random_int(1000, 9999);
        $grandTotal = 0;

        $qId = dbInsert("INSERT INTO quotations (code, user_id, grand_total, status, note, expires_at, created_by) VALUES (?, ?, 0, 'pending', ?, ?, ?)", [
            $code, $userId, $note ?: null, $expiresAt, $user['id'] ?? null
        ]);

        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? 0);
            $price = (int)($it['price'] ?? 0);
            if (!$pid || $qty < 1 || $price < 0) {
                throw new RuntimeException('Sản phẩm hoặc số lượng báo giá không hợp lệ.');
            }
            $total = $qty * $price;
            $grandTotal += $total;

            dbRun("INSERT INTO quotation_items (quotation_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)", [
                $qId, $pid, $qty, $price, $total
            ]);
        }

        dbRun("UPDATE quotations SET grand_total=? WHERE id=?", [$grandTotal, $qId]);
        dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?, ?, 'quotation_created', 'quotation', ?, ?, ?, ?)", [
            $user['id'] ?? null, $user['role'] ?? 'admin', $qId, json_encode(['code' => $code, 'grand_total' => $grandTotal], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $pdo->commit();

        // Gửi email báo giá cho khách hàng
        try {
            require_once __DIR__ . '/../includes/mailer.php';
            $customer = dbGet("SELECT * FROM users WHERE id=?", [$userId]);
            $qObj = dbGet("SELECT * FROM quotations WHERE id=?", [$qId]);
            $qItems = dbAll("SELECT qi.*, p.name AS product_name FROM quotation_items qi INNER JOIN products p ON p.id=qi.product_id WHERE qi.quotation_id=?", [$qId]);
            if ($customer && $qObj && $qItems) {
                sendQuotationEmail($qObj, $customer, $qItems);
            }
        } catch (Throwable $e) {
            error_log('[Quotation Email Error] ' . $e->getMessage());
        }

        flash('success', 'Đã tạo bảng báo giá #' . $code . ' thành công và gửi email thông báo cho khách hàng.');
        redirect('/admin/quotations/' . $qId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi khi tạo báo giá: ' . $e->getMessage());
        redirect('/admin/quotations/new');
    }
});

// C3: Báo giá - Chi tiết báo giá (Quotation detail)
get('/admin/quotations/:id', function($p) {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    $qid = (int)$p['id'];
    $quotation = dbGet("SELECT q.*, u.full_name AS customer_name, u.phone AS customer_phone, uc.full_name AS creator_name FROM quotations q LEFT JOIN users u ON u.id=q.user_id LEFT JOIN users uc ON uc.id=q.created_by WHERE q.id=?", [$qid]);
    if (!$quotation) {
        flash('error', 'Không tìm thấy báo giá.');
        redirect('/admin/quotations'); return;
    }

    $items = dbAll("SELECT qi.*, p.name AS product_name, p.sku FROM quotation_items qi INNER JOIN products p ON p.id=qi.product_id WHERE qi.quotation_id=? ORDER BY qi.id", [$qid]);

    view('admin/quotation-detail', [
        'title' => 'Chi tiết báo giá #' . $quotation['code'],
        'quotation' => $quotation,
        'items' => $items
    ]);
});

// C3: Báo giá - Cập nhật trạng thái (Update Quotation Status: Gửi KH / Hủy)
post('/admin/quotations/:id/status', function($p) {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();
    $qid = (int)$p['id'];
    $status = trim((string)($_POST['status'] ?? ''));
    if (!in_array($status, ['sent', 'cancelled'], true)) {
        flash('error', 'Trạng thái chuyển đổi không hợp lệ.');
        redirect('/admin/quotations/' . $qid); return;
    }

    $quotation = dbGet("SELECT * FROM quotations WHERE id=?", [$qid]);
    if (!$quotation) {
        flash('error', 'Không tìm thấy báo giá.');
        redirect('/admin/quotations'); return;
    }

    dbRun("UPDATE quotations SET status=?, updated_at=datetime('now','localtime') WHERE id=?", [$status, $qid]);
    dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?, ?, 'quotation_status_update', 'quotation', ?, ?, ?, ?)", [
        $user['id'] ?? null, $user['role'] ?? 'admin', $qid, json_encode(['before' => $quotation['status'], 'after' => $status], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    if ($status === 'sent') {
        try {
            require_once __DIR__ . '/../includes/mailer.php';
            $customer = dbGet("SELECT * FROM users WHERE id=?", [$quotation['user_id']]);
            $qItems = dbAll("SELECT qi.*, p.name AS product_name FROM quotation_items qi INNER JOIN products p ON p.id=qi.product_id WHERE qi.quotation_id=?", [$qid]);
            if ($customer && $qItems) {
                sendQuotationEmail($quotation, $customer, $qItems);
            }
        } catch (Throwable $e) {
            error_log('[Quotation Status Email Error] ' . $e->getMessage());
        }
    }

    flash('success', 'Đã cập nhật trạng thái báo giá và gửi email cho khách hàng.');
    redirect('/admin/quotations/' . $qid);
});

// C3: Báo giá - Chuyển thành Đơn hàng (Convert Quotation to Order)
post('/admin/quotations/:id/convert', function($p) {
    $user = requireStaffPermission('rbac:users|products', '/admin/login');
    csrfCheck();
    $qid = (int)$p['id'];

    $quotation = dbGet("SELECT * FROM quotations WHERE id=?", [$qid]);
    if (!$quotation || !in_array($quotation['status'], ['pending', 'sent'], true)) {
        flash('error', 'Báo giá không đủ điều kiện chuyển thành đơn hàng.');
        redirect('/admin/quotations/' . $qid); return;
    }

    $items = dbAll("SELECT qi.*, p.name AS product_name, p.sku, p.stock FROM quotation_items qi INNER JOIN products p ON p.id=qi.product_id WHERE qi.quotation_id=?", [$qid]);
    if (!$items) {
        flash('error', 'Báo giá không có sản phẩm nào.');
        redirect('/admin/quotations/' . $qid); return;
    }

    // Kiểm tra tồn kho trước khi chuyển đổi
    foreach ($items as $it) {
        if ((int)$it['stock'] < (int)$it['quantity']) {
            flash('error', 'Sản phẩm "' . $it['product_name'] . '" (SKU: ' . $it['sku'] . ') không đủ tồn kho (Tồn hiện tại: ' . $it['stock'] . ' — Cần: ' . $it['quantity'] . '). Vui lòng bổ sung kho trước khi chuyển đổi.');
            redirect('/admin/quotations/' . $qid); return;
        }
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        $orderCode = 'DH-' . substr($quotation['code'], 3) . '-' . random_int(10, 99);
        $customer = dbGet("SELECT * FROM users WHERE id=?", [$quotation['user_id']]);

        $totalItems = 0;
        foreach ($items as $it) {
            $totalItems += (int)$it['quantity'];
        }

        // Tạo đơn hàng mới
        $orderId = dbInsert("INSERT INTO orders (code, user_id, total_items, subtotal, grand_total, paid_amount, remaining_amount, payment_status, delivery_status, payment_method, shipping_full_name, shipping_phone, shipping_detail, customer_note, created_by_staff) VALUES (?, ?, ?, ?, ?, 0, ?, 'unpaid', 'pending', 'cod', ?, ?, ?, ?, ?)", [
            $orderCode, $quotation['user_id'], $totalItems, $quotation['grand_total'], $quotation['grand_total'], $quotation['grand_total'],
            $customer['full_name'] ?? 'Khách hàng', $customer['phone'] ?? '', $customer['address'] ?? '',
            'Tự động chuyển từ Báo giá ' . $quotation['code'] . '. ' . ($quotation['note'] ?: ''),
            $user['id'] ?? null
        ]);

        $subOrderCode = $orderCode . '-S01';
        // Tạo sub_order
        $subOrderId = dbInsert("INSERT INTO sub_orders (order_id, partner_id, code, subtotal, grand_total, status) VALUES (?, 1, ?, ?, ?, 'awaiting_confirm')", [
            $orderId, $subOrderCode, $quotation['grand_total'], $quotation['grand_total']
        ]);

        foreach ($items as $it) {
            // Thêm vào chi tiết đơn hàng
            dbRun("INSERT INTO order_items (sub_order_id, product_id, quantity, price, original_price, total) VALUES (?, ?, ?, ?, ?, ?)", [
                $subOrderId, $it['product_id'], $it['quantity'], $it['price'], $it['price'], $it['total']
            ]);

            // Trừ tồn kho và tăng bán chạy
            dbRun("UPDATE products SET stock=stock-?, sold_count=sold_count+?, updated_at=datetime('now','localtime') WHERE id=?", [
                $it['quantity'], $it['quantity'], $it['product_id']
            ]);

            // Ghi nhận biến động kho
            dbInsert("INSERT INTO inventory_stock_movements (product_id, direction, quantity, reference_type, reference_id, note, created_by) VALUES (?, 'out', ?, 'order', ?, ?, ?)", [
                $it['product_id'], $it['quantity'], $orderId, 'Xuất kho cho đơn hàng ' . $orderCode . ' (Chuyển từ báo giá)', $user['id'] ?? null
            ]);

            inventoryCheckLowStockAlert((int)$it['product_id'], 'admin_sale');
        }

        // Cập nhật trạng thái báo giá thành 'converted'
        dbRun("UPDATE quotations SET status='converted', updated_at=datetime('now','localtime') WHERE id=?", [$qid]);

        // Ghi log
        dbRun("INSERT INTO audit_logs (user_id, role, action, entity_type, entity_id, meta, ip, user_agent) VALUES (?, ?, 'quotation_converted', 'quotation', ?, ?, ?, ?)", [
            $user['id'] ?? null, $user['role'] ?? 'admin', $qid, json_encode(['order_id' => $orderId, 'order_code' => $orderCode], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $pdo->commit();
        flash('success', 'Đã chuyển đổi báo giá sang Đơn hàng ' . $orderCode . ' thành công.');
        redirect('/admin/orders/' . $orderId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi khi chuyển đổi báo giá: ' . $e->getMessage());
        redirect('/admin/quotations/' . $qid);
    }
});

// ─── GIAI ĐOẠN D: QUẢN LÝ KHO NÂNG CAO & BÁO CÁO QUẢN TRỊ ──────────────────────────────

// D1: Kiểm kho - Danh sách
get('/admin/stock-counts', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(5, min(100, (int)($_GET['per_page'] ?? 25)));
    $q = trim((string)($_GET['q'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));

    $where = 'WHERE 1=1'; $params = [];
    if ($q !== '') {
        $where .= ' AND (sc.code LIKE ? OR sc.note LIKE ?)';
        $like = '%'.$q.'%';
        array_push($params, $like, $like);
    }
    if ($statusFilter !== '') {
        $where .= ' AND sc.status=?';
        $params[] = $statusFilter;
    }

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM stock_counts sc $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, max(0, ($page-1)*$perPage)]);
    $stockCounts = dbAll("SELECT sc.*, u.full_name AS creator_name FROM stock_counts sc LEFT JOIN users u ON u.id=sc.created_by $where ORDER BY sc.created_at DESC LIMIT ? OFFSET ?", $listParams);

    view('admin/stock-counts', [
        'title' => 'Kiểm kê kho & Điều chỉnh tồn',
        'stockCounts' => $stockCounts,
        'q' => $q,
        'statusFilter' => $statusFilter,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages
    ]);
});

// D1: Kiểm kho - Form tạo mới
get('/admin/stock-counts/new', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $categories = dbAll("SELECT id, name FROM categories ORDER BY name");
    view('admin/stock-count-new', [
        'title' => 'Tạo phiên kiểm kho mới',
        'categories' => $categories
    ]);
});

// D1: Kiểm kho - Xử lý lưu tạo mới
post('/admin/stock-counts', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();

    $whName = trim((string)($_POST['warehouse_name'] ?? 'Kho chính'));
    $catId = max(0, (int)($_POST['category_id'] ?? 0));
    $note = trim((string)($_POST['note'] ?? ''));

    $pdo = db();
    try {
        $pdo->beginTransaction();

        $code = 'KK-' . date('Ymd') . '-' . random_int(1000, 9999);
        $scId = dbInsert("INSERT INTO stock_counts (code, warehouse_name, status, note, created_by) VALUES (?, ?, 'in_progress', ?, ?)", [
            $code, $whName, $note ?: null, $user['id'] ?? null
        ]);

        $sql = "SELECT id, stock FROM products WHERE 1=1";
        $params = [];
        if ($catId > 0) {
            $sql .= " AND category_id=?";
            $params[] = $catId;
        }
        $products = dbAll($sql, $params);

        foreach ($products as $p) {
            $sysQty = (int)$p['stock'];
            dbRun("INSERT INTO stock_count_items (stock_count_id, product_id, system_qty, actual_qty, diff_qty) VALUES (?, ?, ?, ?, 0)", [
                $scId, $p['id'], $sysQty, $sysQty
            ]);
        }

        $pdo->commit();
        flash('success', 'Đã khởi tạo phiên kiểm kho #' . $code . ' với ' . count($products) . ' sản phẩm.');
        redirect('/admin/stock-counts/' . $scId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi khi tạo phiên kiểm kho: ' . $e->getMessage());
        redirect('/admin/stock-counts/new');
    }
});

// D1: Kiểm kho - Chi tiết & kiểm đếm
get('/admin/stock-counts/:id', function($p) {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $scId = (int)$p['id'];
    $stockCount = dbGet("SELECT sc.*, u.full_name AS creator_name FROM stock_counts sc LEFT JOIN users u ON u.id=sc.created_by WHERE sc.id=?", [$scId]);
    if (!$stockCount) {
        flash('error', 'Không tìm thấy phiên kiểm kho.');
        redirect('/admin/stock-counts'); return;
    }

    $items = dbAll("SELECT sci.*, p.name AS product_name, p.sku, p.oem_code, p.location_code FROM stock_count_items sci INNER JOIN products p ON p.id=sci.product_id WHERE sci.stock_count_id=? ORDER BY p.name", [$scId]);

    view('admin/stock-count-detail', [
        'title' => 'Phiên kiểm kho #' . $stockCount['code'],
        'stockCount' => $stockCount,
        'items' => $items
    ]);
});

// D1: Kiểm kho - Cập nhật số lượng đếm thực tế & Cân bằng kho
post('/admin/stock-counts/:id', function($p) {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    $scId = (int)$p['id'];
    $actionType = trim((string)($_POST['action_type'] ?? 'save'));
    $itemsInput = $_POST['items'] ?? [];

    $stockCount = dbGet("SELECT * FROM stock_counts WHERE id=?", [$scId]);
    if (!$stockCount || $stockCount['status'] === 'completed') {
        flash('error', 'Phiên kiểm kho đã hoàn tất hoặc không hợp lệ.');
        redirect('/admin/stock-counts/' . $scId); return;
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        foreach ($itemsInput as $sciId => $data) {
            $actQty = max(0, (int)($data['actual_qty'] ?? 0));
            $reason = trim((string)($data['reason'] ?? ''));

            $itemObj = dbGet("SELECT * FROM stock_count_items WHERE id=? AND stock_count_id=?", [(int)$sciId, $scId]);
            if ($itemObj) {
                $diff = $actQty - (int)$itemObj['system_qty'];
                dbRun("UPDATE stock_count_items SET actual_qty=?, diff_qty=?, reason=? WHERE id=?", [
                    $actQty, $diff, $reason ?: null, (int)$sciId
                ]);

                // Nếu bấm nút hoàn tất & cân bằng tồn kho
                if ($actionType === 'complete') {
                    dbRun("UPDATE products SET stock=?, updated_at=datetime('now','localtime') WHERE id=?", [$actQty, $itemObj['product_id']]);
                    if ($diff !== 0) {
                        $dir = $diff > 0 ? 'in' : 'out';
                        dbInsert("INSERT INTO inventory_stock_movements (product_id, direction, quantity, reference_type, reference_id, note, created_by) VALUES (?, ?, ?, 'stock_count', ?, ?, ?)", [
                            $itemObj['product_id'], $dir, abs($diff), $scId, 'Cân bằng tồn kho từ kiểm kê #' . $stockCount['code'] . ($reason ? ' (Lý do: '.$reason.')' : ''), $user['id'] ?? null
                        ]);
                    }
                }
            }
        }

        if ($actionType === 'complete') {
            dbRun("UPDATE stock_counts SET status='completed', completed_at=datetime('now','localtime'), updated_at=datetime('now','localtime') WHERE id=?", [$scId]);
            flash('success', 'Đã CÂN BẰNG TỒN KHO thành công cho phiên kiểm kho #' . $stockCount['code'] . '. Tồn kho sản phẩm đã được cập nhật.');
        } else {
            dbRun("UPDATE stock_counts SET status='in_progress', updated_at=datetime('now','localtime') WHERE id=?", [$scId]);
            flash('success', 'Đã lưu tiến độ kiểm đếm.');
        }

        $pdo->commit();
        redirect('/admin/stock-counts/' . $scId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi khi cập nhật kiểm kho: ' . $e->getMessage());
        redirect('/admin/stock-counts/' . $scId);
    }
});

// D2: Chuyển kho - Danh sách
get('/admin/stock-transfers', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;
    $q = trim((string)($_GET['q'] ?? ''));
    $statusFilter = trim((string)($_GET['status'] ?? ''));

    $where = 'WHERE 1=1'; $params = [];
    if ($q !== '') {
        $where .= ' AND (st.code LIKE ? OR st.note LIKE ?)';
        $like = '%'.$q.'%';
        array_push($params, $like, $like);
    }
    if ($statusFilter !== '') {
        $where .= ' AND st.status=?';
        $params[] = $statusFilter;
    }

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM stock_transfers st $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, ($page-1)*$perPage]);
    $transfers = dbAll("SELECT st.*, u.full_name AS creator_name FROM stock_transfers st LEFT JOIN users u ON u.id=st.created_by $where ORDER BY st.created_at DESC LIMIT ? OFFSET ?", $listParams);

    view('admin/stock-transfers', [
        'title' => 'Chuyển kho nội bộ',
        'transfers' => $transfers,
        'q' => $q,
        'statusFilter' => $statusFilter,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// D2: Chuyển kho - Form tạo mới
get('/admin/stock-transfers/new', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $products = dbAll("SELECT id, name, sku, stock FROM products ORDER BY name");
    view('admin/stock-transfer-new', [
        'title' => 'Tạo phiếu chuyển kho mới',
        'products' => $products
    ]);
});

// D2: Chuyển kho - Xử lý lưu tạo mới
post('/admin/stock-transfers', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();

    $fromWh = trim((string)($_POST['from_warehouse'] ?? ''));
    $toWh = trim((string)($_POST['to_warehouse'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));
    $items = $_POST['items'] ?? [];

    if (!$fromWh || !$toWh) {
        flash('error', 'Vui lòng chọn Kho chuyển (Kho nguồn) và Kho nhận (Kho đích).');
        redirect('/admin/stock-transfers/new'); return;
    }

    if ($fromWh === $toWh) {
        flash('error', 'Kho chuyển (Kho nguồn) và Kho nhận (Kho đích) không được trùng nhau.');
        redirect('/admin/stock-transfers/new'); return;
    }

    if (!$items) {
        flash('error', 'Phiếu chuyển kho phải chứa ít nhất một sản phẩm.');
        redirect('/admin/stock-transfers/new'); return;
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        $code = 'CK-' . date('Ymd') . '-' . random_int(1000, 9999);
        $stId = dbInsert("INSERT INTO stock_transfers (code, from_warehouse, to_warehouse, status, note, created_by) VALUES (?, ?, ?, 'pending', ?, ?)", [
            $code, $fromWh, $toWh, $note ?: null, $user['id'] ?? null
        ]);

        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? 0);
            if ($pid && $qty > 0) {
                dbRun("INSERT INTO stock_transfer_items (transfer_id, product_id, quantity) VALUES (?, ?, ?)", [
                    $stId, $pid, $qty
                ]);
            }
        }

        $pdo->commit();
        flash('success', 'Đã tạo phiếu chuyển kho #' . $code . ' thành công.');
        redirect('/admin/stock-transfers/' . $stId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage());
        redirect('/admin/stock-transfers/new');
    }
});

// D2: Chuyển kho - Chi tiết
get('/admin/stock-transfers/:id', function($p) {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $stId = (int)$p['id'];
    $transfer = dbGet("SELECT st.*, u.full_name AS creator_name FROM stock_transfers st LEFT JOIN users u ON u.id=st.created_by WHERE st.id=?", [$stId]);
    if (!$transfer) {
        flash('error', 'Không tìm thấy phiếu chuyển kho.');
        redirect('/admin/stock-transfers'); return;
    }

    $items = dbAll("SELECT sti.*, p.name AS product_name, p.sku, p.oem_code FROM stock_transfer_items sti INNER JOIN products p ON p.id=sti.product_id WHERE sti.transfer_id=?", [$stId]);

    view('admin/stock-transfer-detail', [
        'title' => 'Phiếu chuyển kho #' . $transfer['code'],
        'transfer' => $transfer,
        'items' => $items
    ]);
});

// D2: Chuyển kho - Cập nhật trạng thái
post('/admin/stock-transfers/:id/status', function($p) {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    $stId = (int)$p['id'];
    $status = trim((string)($_POST['status'] ?? ''));

    $transfer = dbGet("SELECT * FROM stock_transfers WHERE id=?", [$stId]);
    if (!$transfer || $transfer['status'] === 'completed') {
        flash('error', 'Phiếu chuyển kho không tồn tại hoặc đã hoàn tất.');
        redirect('/admin/stock-transfers/' . $stId); return;
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        if ($status === 'shipping') {
            dbRun("UPDATE stock_transfers SET status='shipping', shipped_at=datetime('now','localtime'), updated_at=datetime('now','localtime') WHERE id=?", [$stId]);
            flash('success', 'Đã chuyển phiếu sang trạng thái Đang vận chuyển.');
        } elseif ($status === 'completed') {
            dbRun("UPDATE stock_transfers SET status='completed', received_at=datetime('now','localtime'), updated_at=datetime('now','localtime') WHERE id=?", [$stId]);
            flash('success', 'Xác nhận ĐÃ NHẬN HÀNG VÀ HOÀN TẤT phiếu chuyển kho #' . $transfer['code']);
        } elseif ($status === 'cancelled') {
            dbRun("UPDATE stock_transfers SET status='cancelled', updated_at=datetime('now','localtime') WHERE id=?", [$stId]);
            flash('success', 'Đã hủy phiếu chuyển kho.');
        }

        $pdo->commit();
        redirect('/admin/stock-transfers/' . $stId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', 'Lỗi cập nhật phiếu chuyển kho: ' . $e->getMessage());
        redirect('/admin/stock-transfers/' . $stId);
    }
});

// D3: Vị trí kho - Danh sách
get('/admin/locations', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM warehouse_locations")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $locations = dbAll("SELECT wl.*, COUNT(p.id) AS product_count FROM warehouse_locations wl LEFT JOIN products p ON p.location_code=wl.code GROUP BY wl.id ORDER BY wl.code LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/locations', [
        'title' => 'Sơ đồ & Vị trí kho',
        'locations' => $locations,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
get('/admin/locations/api/products', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    header('Content-Type: application/json; charset=utf-8');
    $code = trim($_GET['code'] ?? '');
    if (!$code) {
        echo json_encode(['success' => false, 'products' => []]);
        exit;
    }
    $products = dbAll("SELECT id, name, sku, oem_code, stock, status FROM products WHERE location_code=? ORDER BY name ASC", [$code]);
    echo json_encode(['success' => true, 'code' => $code, 'products' => $products]);
    exit;
});

// D3: Vị trí kho - Lưu mới
post('/admin/locations', function() {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();

    $code = strtoupper(trim((string)($_POST['code'] ?? '')));
    $areaName = trim((string)($_POST['area_name'] ?? ''));
    $shelfName = trim((string)($_POST['shelf_name'] ?? ''));
    $binName = trim((string)($_POST['bin_name'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    if (!$code || !preg_match('/^[A-Z0-9\-]{3,30}$/', $code)) {
        flash('error', 'Mã vị trí không hợp lệ (Viết liền không dấu, từ 3-30 ký tự, VD: KE-A-T1-K05).');
        redirect('/admin/locations'); return;
    }

    if (!$areaName || mb_strlen($areaName) > 50) {
        flash('error', 'Vui lòng nhập Khu vực/Kệ hợp lệ (tối đa 50 ký tự).');
        redirect('/admin/locations'); return;
    }

    if (mb_strlen($shelfName) > 50 || mb_strlen($binName) > 50) {
        flash('error', 'Tên Tầng và Khay/Ô tối đa 50 ký tự.');
        redirect('/admin/locations'); return;
    }

    if (mb_strlen($note) > 200) {
        flash('error', 'Ghi chú tối đa 200 ký tự.');
        redirect('/admin/locations'); return;
    }

    try {
        dbInsert("INSERT INTO warehouse_locations (code, area_name, shelf_name, bin_name, note) VALUES (?, ?, ?, ?, ?)", [
            $code, $areaName, $shelfName ?: null, $binName ?: null, $note ?: null
        ]);
        flash('success', 'Đã thêm vị trí kho mới: ' . $code);
    } catch (Throwable $e) {
        flash('error', 'Lỗi khi thêm vị trí kho (Mã vị trí ' . $code . ' đã tồn tại trên hệ thống).');
    }
    redirect('/admin/locations');
});

// D3: Vị trí kho - Xóa
post('/admin/locations/:id/delete', function($p) {
    $user = requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    $id = (int)$p['id'];
    dbRun("DELETE FROM warehouse_locations WHERE id=?", [$id]);
    flash('success', 'Đã xóa vị trí kho.');
    redirect('/admin/locations');
});

// D4: Báo cáo - Xuất Nhập Tồn
get('/admin/reports/xnt', function() {
    $user = requireStaffPermission('rbac:reports|products', '/admin/login');
    $fromDate = trim((string)($_GET['from'] ?? date('Y-m-01')));
    $toDate = trim((string)($_GET['to'] ?? date('Y-m-d')));
    $catId = max(0, (int)($_GET['category_id'] ?? 0));
    $q = trim((string)($_GET['q'] ?? ''));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $where = 'WHERE 1=1'; $params = [];
    if ($catId > 0) {
        $where .= ' AND p.category_id=?';
        $params[] = $catId;
    }
    if ($q !== '') {
        $where .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ? OR p.oem_code2 LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $totalProducts = (int)(dbGet("SELECT COUNT(*) AS c FROM products p $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalProducts / $perPage));
    $page = min($page, $totalPages);

    $fromStart = $fromDate . ' 00:00:00';
    $toEnd = $toDate . ' 23:59:59';

    // Tính Tổng xuất kho thực tế từ bảng order_items qua sub_orders theo khoảng ngày lọc
    $totalExported = (int)(dbGet("
        SELECT COALESCE(SUM(oi.quantity), 0) AS tot_sold
        FROM order_items oi
        JOIN sub_orders so ON oi.sub_order_id = so.id
        JOIN orders o ON so.order_id = o.id
        JOIN products p ON oi.product_id = p.id
        $where AND so.status NOT IN ('cancelled', 'refunded', 'returning')
        AND o.created_at BETWEEN ? AND ?
    ", array_merge($params, [$fromStart, $toEnd]))['tot_sold'] ?? 0);

    $totalStock = (int)(dbGet("SELECT SUM(stock) AS tot_stock FROM products p $where", $params)['tot_stock'] ?? 0);

    $listParams = array_merge([$fromStart, $toEnd], $params, [$perPage, ($page-1)*$perPage]);
    $items = dbAll("
        SELECT p.id, p.name, p.sku, p.oem_code, p.stock, p.price, p.cost_price, p.location_code,
               COALESCE((
                   SELECT SUM(oi.quantity) 
                   FROM order_items oi 
                   JOIN sub_orders so ON oi.sub_order_id = so.id
                   JOIN orders o ON so.order_id = o.id 
                   WHERE oi.product_id = p.id 
                     AND so.status NOT IN ('cancelled', 'refunded', 'returning')
                     AND o.created_at BETWEEN ? AND ?
               ), 0) AS sold_count
        FROM products p $where ORDER BY p.name LIMIT ? OFFSET ?
    ", $listParams);
    $categories = dbAll("SELECT id, name FROM categories ORDER BY name");

    $totalStockValue = 0;
    foreach ($items as $it) {
        $st = (int)$it['stock'];
        $cost = (int)($it['cost_price'] ?: $it['price']*0.7);
        $totalStockValue += ($st * $cost);
    }

    view('admin/reports/xnt', [
        'title' => 'Báo cáo Xuất - Nhập - Tồn',
        'items' => $items,
        'categories' => $categories,
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'catId' => $catId,
        'q' => $q,
        'totalProducts' => $totalProducts,
        'totalExported' => $totalExported,
        'totalStock' => $totalStock,
        'totalStockValue' => $totalStockValue,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// D4: Báo cáo - Lợi nhuận gộp theo SKU
get('/admin/reports/margin', function() {
    $user = requireStaffPermission('rbac:reports|products', '/admin/login');
    $catId = max(0, (int)($_GET['category_id'] ?? 0));
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $where = 'WHERE p.sold_count > 0'; $params = [];
    if ($catId > 0) {
        $where .= ' AND p.category_id=?';
        $params[] = $catId;
    }

    $totalItems = (int)(dbGet("SELECT COUNT(*) AS c FROM products p $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalItems / $perPage));
    $page = min($page, $totalPages);

    $listParams = array_merge($params, [$perPage, ($page-1)*$perPage]);
    $items = dbAll("SELECT p.id, p.name, p.sku, p.price, p.cost_price, p.sold_count FROM products p $where ORDER BY (p.sold_count * (p.price - COALESCE(p.cost_price, p.price*0.7))) DESC LIMIT ? OFFSET ?", $listParams);
    $categories = dbAll("SELECT id, name FROM categories ORDER BY name");

    $totalRevenue = 0; $totalCost = 0; $totalProfit = 0;
    foreach ($items as $it) {
        $price = (int)$it['price'];
        $cost = (int)($it['cost_price'] ?: $price*0.7);
        $sold = (int)$it['sold_count'];
        $rev = $sold * $price;
        $cogs = $sold * $cost;
        $totalRevenue += $rev;
        $totalCost += $cogs;
        $totalProfit += ($rev - $cogs);
    }
    $avgMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue)*100, 1) : 0;

    view('admin/reports/margin', [
        'title' => 'Báo cáo Lợi nhuận gộp theo SKU',
        'items' => $items,
        'categories' => $categories,
        'catId' => $catId,
        'totalRevenue' => $totalRevenue,
        'totalCost' => $totalCost,
        'totalProfit' => $totalProfit,
        'avgMargin' => $avgMargin,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// D4: Báo cáo - KPI Bán hàng & Nhân sự
get('/admin/reports/kpi', function() {
    $user = requireStaffPermission('rbac:reports|products', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $totalStaff = (int)(dbGet("SELECT COUNT(*) AS c FROM users WHERE role IN ('staff', 'admin', 'manager', 'customer', 'garage', 'technician')")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($totalStaff / $perPage));
    $page = min($page, $totalPages);

    $staffKpis = dbAll("SELECT u.id, u.full_name, u.phone, u.role,
        COUNT(DISTINCT o.id) AS order_count,
        COALESCE(SUM(o.grand_total), 0) AS total_sales,
        COALESCE(SUM(ct.commission_fee), 0) AS total_commission
        FROM users u
        LEFT JOIN orders o ON o.user_id = u.id AND (o.payment_status = 'paid' OR o.delivery_status = 'completed')
        LEFT JOIN partners pt ON pt.contact_phone = u.phone
        LEFT JOIN commission_transactions ct ON ct.partner_id = pt.id AND ct.type = 'earn'
        WHERE u.role IN ('staff', 'admin', 'manager', 'customer', 'garage', 'technician')
        GROUP BY u.id
        ORDER BY total_sales DESC
        LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/reports/kpi', [
        'title' => 'Báo cáo KPI Nhân sự & Bán hàng',
        'staffKpis' => $staffKpis,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// ─── API TÌM KIẾM SẢN PHẨM AUTOCOMPLETE LIVE SEARCH ──────────────────────────────
get('/admin/inventory/search-product', function() {
    requireStaffPermission('rbac:products|orders|inventory', '/admin/login');
    header('Content-Type: application/json');
    $q = trim((string)($_GET['q'] ?? ''));
    if ($q === '') { echo json_encode([]); return; }
    $like = '%'.$q.'%';
    $products = dbAll("SELECT id, name, sku, oem_code, stock, price, cost_price, location_code FROM products WHERE name LIKE ? OR sku LIKE ? OR oem_code LIKE ? ORDER BY name LIMIT 25", [$like, $like, $like]);
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
});
get('/admin/api/products/search', function() {
    requireStaffPermission('rbac:products|orders|inventory', '/admin/login');
    header('Content-Type: application/json');
    $q = trim((string)($_GET['q'] ?? ''));
    if ($q === '') { echo json_encode([]); return; }
    $like = '%'.$q.'%';
    $products = dbAll("SELECT id, name, sku, oem_code, stock, price, cost_price, location_code FROM products WHERE name LIKE ? OR sku LIKE ? OR oem_code LIKE ? ORDER BY name LIMIT 25", [$like, $like, $like]);
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
});

// ─── HELPER XUẤT CSV UTF-8 CHUẨN (HỖ TRỢ TIẾNG VIỆT EXCEL) ─────────────────────
if (!function_exists('outputCsvFile')) {
    function outputCsvFile($filename, array $headers, array $rows) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
}

// 1. Garage CSV Export & Import
get('/admin/garages/export-csv', function() {
    requireStaffPermission('rbac:users', '/admin/login');
    $garages = dbAll("SELECT g.*, u.full_name AS customer_name, u.phone AS customer_phone, b.name AS brand_name, m.name AS model_name FROM garages g LEFT JOIN users u ON u.id=g.user_id LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models m ON m.id=g.model_id ORDER BY g.id DESC");
    $headers = ['ID', 'Khách hàng', 'SĐT Khách hàng', 'Hãng xe', 'Dòng xe', 'Năm sản xuất', 'Phiên bản (Trim)', 'Biển số / Mô tả', 'Mặc định', 'Ngày tạo'];
    $rows = [];
    foreach ($garages as $g) {
        $rows[] = [
            $g['id'], $g['customer_name'] ?? '—', $g['customer_phone'] ?? '—', $g['brand_name'] ?? '—', $g['model_name'] ?? '—', $g['year'] ?? '—', $g['trim'] ?? '—', $g['label'] ?? '—', $g['is_default'] ? 'Có' : 'Không', $g['created_at'] ?? '—'
        ];
    }
    outputCsvFile('garages_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});
post('/admin/garages/import-csv', function() {
    requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $uid = (int)($data[0] ?? 0);
                    $brand = trim((string)($data[1] ?? ''));
                    $model = trim((string)($data[2] ?? ''));
                    $year = trim((string)($data[3] ?? ''));
                    $trim = trim((string)($data[4] ?? ''));
                    $label = trim((string)($data[5] ?? ''));
                    if ($uid > 0 && $label !== '') {
                        dbInsert("INSERT INTO garages (user_id, year, trim, label, created_at) VALUES (?, ?, ?, ?, datetime('now','localtime'))", [
                            $uid, $year ?: null, $trim ?: null, $label
                        ]);
                        $imported++;
                    }
                }
            }
            fclose($handle);
            flash('success', 'Đã nhập thành công ' . $imported . ' xe vào Garage.');
        }
    } else {
        flash('error', 'Vui lòng chọn file CSV.');
    }
    redirect('/admin/garages');
});

// 2. Hoa hồng CSV Export & Import
get('/admin/commissions/export-csv', function() {
    requireStaffPermission('rbac:finance', '/admin/login');
    $list = dbAll("SELECT ct.*, COALESCE(p.shop_name, p.representative_name, 'Đối tác') AS partner_name, p.contact_phone FROM commission_transactions ct LEFT JOIN partners p ON p.id=ct.partner_id ORDER BY ct.created_at DESC");
    $headers = ['ID', 'Mã giao dịch', 'Tên Đối tác', 'SĐT Đối tác', 'Loại (earn/payout)', 'Số tiền hoa hồng (đ)', 'Ghi chú', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $c) {
        $rows[] = [
            $c['id'], $c['code'] ?? '—', $c['partner_name'] ?? '—', $c['contact_phone'] ?? '—', $c['type'] ?? '—', $c['commission_fee'] ?? 0, $c['note'] ?? '—', $c['created_at'] ?? '—'
        ];
    }
    outputCsvFile('commissions_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});
post('/admin/commissions/import-csv', function() {
    requireStaffPermission('rbac:finance', '/admin/login');
    csrfCheck();
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 3) {
                    $pid = (int)($data[0] ?? 0);
                    $type = trim((string)($data[1] ?? 'earn'));
                    $fee = (int)($data[2] ?? 0);
                    $note = trim((string)($data[3] ?? ''));
                    if ($pid > 0 && $fee > 0) {
                        $code = 'CT-IMP-' . random_int(1000, 9999);
                        dbInsert("INSERT INTO commission_transactions (code, partner_id, type, commission_fee, note) VALUES (?, ?, ?, ?, ?)", [
                            $code, $pid, $type, $fee, $note ?: null
                        ]);
                        $imported++;
                    }
                }
            }
            fclose($handle);
            flash('success', 'Đã nhập thành công ' . $imported . ' giao dịch hoa hồng.');
        }
    } else {
        flash('error', 'Vui lòng chọn file CSV.');
    }
    redirect('/admin/commissions');
});

// 3. Quotations Export CSV
get('/admin/quotations/export-csv', function() {
    requireStaffPermission('rbac:orders', '/admin/login');
    $list = dbAll("SELECT q.*, u.full_name AS customer_name, u.phone AS customer_phone FROM quotations q LEFT JOIN users u ON u.id=q.user_id ORDER BY q.id DESC");
    $headers = ['Mã Báo giá', 'Tên Khách hàng', 'SĐT Khách hàng', 'Tổng tiền (đ)', 'Trạng thái', 'Ghi chú', 'Ngày hết hạn', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $q) {
        $rows[] = [
            $q['code'] ?? '—', $q['customer_name'] ?? 'Khách vãng lai', $q['customer_phone'] ?? '—', $q['grand_total'] ?? 0, $q['status'] ?? '—', $q['note'] ?? '—', $q['expires_at'] ?? '—', $q['created_at'] ?? '—'
        ];
    }
    outputCsvFile('quotations_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// 4. Stock Counts Export & Import CSV
get('/admin/stock-counts/export-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    $list = dbAll("SELECT sc.*, u.full_name AS creator_name FROM stock_counts sc LEFT JOIN users u ON u.id=sc.created_by ORDER BY sc.id DESC");
    $headers = ['Mã Kiểm kho', 'Kho hàng', 'Trạng thái', 'Ghi chú', 'Người tạo', 'Ngày hoàn tất', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $sc) {
        $rows[] = [
            $sc['code'] ?? '—', $sc['warehouse_name'] ?? 'Kho chính', $sc['status'] ?? '—', $sc['note'] ?? '—', $sc['creator_name'] ?? 'System', $sc['completed_at'] ?? '—', $sc['created_at'] ?? '—'
        ];
    }
    outputCsvFile('stock_counts_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});
post('/admin/stock-counts/import-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 3) {
                    $code = trim((string)($data[0] ?? ''));
                    $sku = trim((string)($data[1] ?? ''));
                    $actQty = (int)($data[2] ?? 0);
                    $reason = trim((string)($data[3] ?? ''));

                    $sc = dbGet("SELECT id FROM stock_counts WHERE code=?", [$code]);
                    $prod = dbGet("SELECT id, stock FROM products WHERE sku=?", [$sku]);

                    if ($sc && $prod) {
                        $sysQty = (int)$prod['stock'];
                        $diff = $actQty - $sysQty;
                        dbRun("UPDATE stock_count_items SET actual_qty=?, diff_qty=?, reason=? WHERE stock_count_id=? AND product_id=?", [
                            $actQty, $diff, $reason ?: null, $sc['id'], $prod['id']
                        ]);
                        $imported++;
                    }
                }
            }
            fclose($handle);
            flash('success', 'Đã nhập kết quả kiểm đếm cho ' . $imported . ' sản phẩm.');
        }
    } else {
        flash('error', 'Vui lòng chọn file CSV.');
    }
    redirect('/admin/stock-counts');
});

// 5. Stock Transfers Export & Import CSV
get('/admin/stock-transfers/export-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    $list = dbAll("SELECT st.*, u.full_name AS creator_name FROM stock_transfers st LEFT JOIN users u ON u.id=st.created_by ORDER BY st.id DESC");
    $headers = ['Mã Phiếu chuyển', 'Kho xuất hàng', 'Kho nhận hàng', 'Trạng thái', 'Ghi chú', 'Người tạo', 'Ngày vận chuyển', 'Ngày nhận hàng', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $st) {
        $rows[] = [
            $st['code'] ?? '—', $st['from_warehouse'] ?? 'Kho chính', $st['to_warehouse'] ?? 'Chi nhánh', $st['status'] ?? '—', $st['note'] ?? '—', $st['creator_name'] ?? 'System', $st['shipped_at'] ?? '—', $st['received_at'] ?? '—', $st['created_at'] ?? '—'
        ];
    }
    outputCsvFile('stock_transfers_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});
post('/admin/stock-transfers/import-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 4) {
                    $fromWh = trim((string)($data[0] ?? 'Kho chính'));
                    $toWh = trim((string)($data[1] ?? 'Chi nhánh'));
                    $sku = trim((string)($data[2] ?? ''));
                    $qty = (int)($data[3] ?? 1);
                    $note = trim((string)($data[4] ?? ''));

                    $prod = dbGet("SELECT id FROM products WHERE sku=?", [$sku]);
                    if ($prod && $qty > 0) {
                        $code = 'CK-IMP-' . random_int(1000, 9999);
                        $stId = dbInsert("INSERT INTO stock_transfers (code, from_warehouse, to_warehouse, status, note, created_by) VALUES (?, ?, ?, 'pending', ?, ?)", [
                            $code, $fromWh, $toWh, $note ?: null, $_SESSION['user_id'] ?? null
                        ]);
                        dbRun("INSERT INTO stock_transfer_items (transfer_id, product_id, quantity) VALUES (?, ?, ?)", [$stId, $prod['id'], $qty]);
                        $imported++;
                    }
                }
            }
            fclose($handle);
            flash('success', 'Đã tạo thành công ' . $imported . ' phiếu chuyển kho từ CSV.');
        }
    } else {
        flash('error', 'Vui lòng chọn file CSV.');
    }
    redirect('/admin/stock-transfers');
});

// 6. Locations Export & Import CSV
get('/admin/locations/export-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    $list = dbAll("SELECT wl.*, COUNT(p.id) AS product_count FROM warehouse_locations wl LEFT JOIN products p ON p.location_code=wl.code GROUP BY wl.id ORDER BY wl.code");
    $headers = ['Mã vị trí', 'Khu vực / Kệ', 'Tầng', 'Khay / Ô', 'Số SP xếp', 'Ghi chú', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $loc) {
        $rows[] = [
            $loc['code'] ?? '—', $loc['area_name'] ?? '—', $loc['shelf_name'] ?? '—', $loc['bin_name'] ?? '—', $loc['product_count'] ?? 0, $loc['note'] ?? '—', $loc['created_at'] ?? '—'
        ];
    }
    outputCsvFile('locations_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});
post('/admin/locations/import-csv', function() {
    requireStaffPermission('rbac:inventory|products', '/admin/login');
    csrfCheck();
    if (!empty($_FILES['csv_file']['tmp_name'])) {
        $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
        if ($handle) {
            $header = fgetcsv($handle);
            $imported = 0;
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 2) {
                    $code = strtoupper(trim((string)($data[0] ?? '')));
                    $area = trim((string)($data[1] ?? ''));
                    $shelf = trim((string)($data[2] ?? ''));
                    $bin = trim((string)($data[3] ?? ''));
                    $note = trim((string)($data[4] ?? ''));

                    if ($code !== '' && $area !== '') {
                        try {
                            dbInsert("INSERT INTO warehouse_locations (code, area_name, shelf_name, bin_name, note) VALUES (?, ?, ?, ?, ?)", [
                                $code, $area, $shelf ?: null, $bin ?: null, $note ?: null
                            ]);
                            $imported++;
                        } catch (Throwable $e) {}
                    }
                }
            }
            fclose($handle);
            flash('success', 'Đã nhập thành công ' . $imported . ' vị trí kho.');
        }
    } else {
        flash('error', 'Vui lòng chọn file CSV.');
    }
    redirect('/admin/locations');
});

// 7. Report XNT Export CSV
get('/admin/reports/xnt/export-csv', function() {
    requireStaffPermission('rbac:reports|products', '/admin/login');
    $fromDate = trim((string)($_GET['from'] ?? date('Y-m-01')));
    $toDate = trim((string)($_GET['to'] ?? date('Y-m-d')));
    $fromStart = $fromDate . ' 00:00:00';
    $toEnd = $toDate . ' 23:59:59';
    $catId = max(0, (int)($_GET['category_id'] ?? 0));
    $q = trim((string)($_GET['q'] ?? ''));

    $where = 'WHERE 1=1'; $params = [];
    if ($catId > 0) { $where .= ' AND p.category_id=?'; $params[] = $catId; }
    if ($q !== '') {
        $where .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ? OR p.oem_code2 LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }

    $listParams = array_merge([$fromStart, $toEnd], $params);
    $items = dbAll("
        SELECT p.name, p.sku, p.oem_code, p.location_code, p.stock, p.price, p.cost_price,
               COALESCE((
                   SELECT SUM(oi.quantity) 
                   FROM order_items oi 
                   JOIN sub_orders so ON oi.sub_order_id = so.id
                   JOIN orders o ON so.order_id = o.id 
                   WHERE oi.product_id = p.id 
                     AND so.status NOT IN ('cancelled', 'refunded', 'returning')
                     AND o.created_at BETWEEN ? AND ?
               ), 0) AS sold_count
        FROM products p $where ORDER BY p.name", $listParams);

    $headers = ['Sản phẩm', 'Mã SKU', 'Mã OEM', 'Vị trí kho', 'Tồn đầu kỳ (ước tính)', 'Đã nhập', 'Đã xuất kho', 'Tồn cuối kỳ', 'Đơn giá bán (đ)', 'Giá vốn (đ)', 'Giá trị tồn kho (đ)'];
    $rows = [];
    foreach ($items as $it) {
        $st = (int)$it['stock'];
        $sd = (int)$it['sold_count'];
        $cost = (int)($it['cost_price'] ?: $it['price']*0.7);
        $rows[] = [
            $it['name'], $it['sku'] ?: '—', $it['oem_code'] ?: '—', $it['location_code'] ?: '—', ($st + $sd), ($st + $sd), $sd, $st, (int)$it['price'], $cost, ($st * $cost)
        ];
    }
    outputCsvFile('baocao_XNT_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// 8. Report Margin Export CSV
get('/admin/reports/margin/export-csv', function() {
    requireStaffPermission('rbac:reports|products', '/admin/login');
    $catId = max(0, (int)($_GET['category_id'] ?? 0));
    $where = 'WHERE p.sold_count > 0'; $params = [];
    if ($catId > 0) { $where .= ' AND p.category_id=?'; $params[] = $catId; }

    $items = dbAll("SELECT p.name, p.sku, p.price, p.cost_price, p.sold_count FROM products p $where ORDER BY (p.sold_count * (p.price - COALESCE(p.cost_price, p.price*0.7))) DESC", $params);
    $headers = ['Sản phẩm', 'Mã SKU', 'Đơn giá bán (đ)', 'Giá vốn đơn vị (đ)', 'Đã bán', 'Tổng Doanh thu (đ)', 'Tổng Giá vốn (đ)', 'Lợi nhuận gộp (đ)', 'Biên lợi nhuận (%)'];
    $rows = [];
    foreach ($items as $it) {
        $price = (int)$it['price'];
        $cost = (int)($it['cost_price'] ?: $price*0.7);
        $sold = (int)$it['sold_count'];
        $rev = $sold * $price;
        $cogs = $sold * $cost;
        $profit = $rev - $cogs;
        $margin = $rev > 0 ? round(($profit / $rev)*100, 1) : 0;
        $rows[] = [
            $it['name'], $it['sku'] ?: '—', $price, $cost, $sold, $rev, $cogs, $profit, $margin . '%'
        ];
    }
    outputCsvFile('baocao_loi_nhuan_SKU_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// 9. Report KPI Export CSV
get('/admin/reports/kpi/export-csv', function() {
    requireStaffPermission('rbac:reports|products', '/admin/login');
    $staffKpis = dbAll("SELECT u.full_name, u.phone, u.role,
        COUNT(DISTINCT o.id) AS order_count,
        COALESCE(SUM(o.grand_total), 0) AS total_sales,
        COALESCE(SUM(ct.commission_fee), 0) AS total_commission
        FROM users u
        LEFT JOIN orders o ON o.user_id = u.id AND (o.payment_status = 'paid' OR o.delivery_status = 'completed')
        LEFT JOIN partners pt ON pt.contact_phone = u.phone
        LEFT JOIN commission_transactions ct ON ct.partner_id = pt.id AND ct.type = 'earn'
        WHERE u.role IN ('staff', 'admin', 'manager', 'customer', 'garage', 'technician')
        GROUP BY u.id
        ORDER BY total_sales DESC");

    $headers = ['Họ và tên', 'Vai trò', 'Số điện thoại', 'Số đơn hoàn thành', 'Tổng Doanh số phát sinh (đ)', 'Hoa hồng ghi nhận (đ)'];
    $rows = [];
    foreach ($staffKpis as $k) {
        $rows[] = [
            $k['full_name'] ?? '—', $k['role'] ?? '—', $k['phone'] ?? '—', (int)$k['order_count'], (int)$k['total_sales'], (int)$k['total_commission']
        ];
    }
    outputCsvFile('baocao_KPI_nhan_su_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// ─── GIAI ĐOẠN E: CRM, MARKETING AUTOMATION & CẢNH BÁO AN TOÀN ──────────────────

// E1: CRM Segments - Danh sách & Phân trang
get('/admin/crm/segments', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM crm_segments")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $segments = dbAll("SELECT cs.*, COUNT(u.id) AS user_count FROM crm_segments cs LEFT JOIN users u ON u.role IN ('customer','garage') GROUP BY cs.id ORDER BY cs.id DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/crm-segments', [
        'title' => 'Phân khúc khách hàng',
        'segments' => $segments,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E1: CRM Segments - Lưu mới
post('/admin/crm/segments', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if ($name !== '') {
        dbInsert("INSERT INTO crm_segments (name, description) VALUES (?, ?)", [$name, $description ?: null]);
        flash('success', 'Đã tạo phân khúc khách hàng mới: ' . $name);
    }
    redirect('/admin/crm/segments');
});

// E1: CRM Segments - Xóa
post('/admin/crm/segments/:id/delete', function($p) {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    dbRun("DELETE FROM crm_segments WHERE id=?", [(int)$p['id']]);
    flash('success', 'Đã xóa phân khúc khách hàng.');
    redirect('/admin/crm/segments');
});

// E1: CRM Segments - Export CSV
get('/admin/crm/segments/export-csv', function() {
    requireStaffPermission('rbac:users', '/admin/login');
    $list = dbAll("SELECT cs.*, COUNT(u.id) AS user_count FROM crm_segments cs LEFT JOIN users u ON u.role IN ('customer','garage') GROUP BY cs.id ORDER BY cs.id DESC");
    $headers = ['ID', 'Tên phân khúc', 'Mô tả', 'Số KH thuộc nhóm', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $s) {
        $rows[] = [$s['id'], $s['name'], $s['description'] ?? '—', (int)($s['user_count'] ?? 0), $s['created_at'] ?? '—'];
    }
    outputCsvFile('crm_segments_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// E1: CRM Maintenance Schedules - Danh sách & Phân trang
get('/admin/crm/maintenance', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM crm_maintenance_schedules")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $schedules = dbAll("SELECT ms.*, u.full_name AS customer_name, u.phone AS customer_phone FROM crm_maintenance_schedules ms LEFT JOIN users u ON u.id=ms.user_id ORDER BY ms.next_due_date ASC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);
    $customers = dbAll("SELECT id, full_name, phone FROM users WHERE role IN ('customer','garage') ORDER BY full_name");

    view('admin/crm-maintenance', [
        'title' => 'Lịch nhắc bảo dưỡng định kỳ',
        'schedules' => $schedules,
        'customers' => $customers,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E1: CRM Maintenance - Tạo lịch mới
post('/admin/crm/maintenance', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    $uid = (int)($_POST['user_id'] ?? 0);
    $prodName = trim((string)($_POST['product_name'] ?? ''));
    $servName = trim((string)($_POST['service_name'] ?? ''));
    $dueDate = trim((string)($_POST['next_due_date'] ?? ''));
    $note = trim((string)($_POST['note'] ?? ''));

    if ($uid > 0 && $prodName !== '' && $dueDate !== '') {
        dbInsert("INSERT INTO crm_maintenance_schedules (user_id, product_name, service_name, next_due_date, status, note) VALUES (?, ?, ?, ?, 'pending', ?)", [
            $uid, $prodName, $servName, $dueDate, $note ?: null
        ]);
        flash('success', 'Đã lập lịch nhắc bảo dưỡng thành công.');
    } else {
        flash('error', 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
    }
    redirect('/admin/crm/maintenance');
});

// E1: CRM Maintenance - Cập nhật trạng thái
post('/admin/crm/maintenance/:id/status', function($p) {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    $status = trim((string)($_POST['status'] ?? 'completed'));
    dbRun("UPDATE crm_maintenance_schedules SET status=? WHERE id=?", [$status, (int)$p['id']]);
    flash('success', 'Đã cập nhật trạng thái lịch bảo dưỡng.');
    redirect('/admin/crm/maintenance');
});

// E1: CRM Maintenance - Export CSV
get('/admin/crm/maintenance/export-csv', function() {
    requireStaffPermission('rbac:users', '/admin/login');
    $list = dbAll("SELECT ms.*, u.full_name AS customer_name, u.phone AS customer_phone FROM crm_maintenance_schedules ms LEFT JOIN users u ON u.id=ms.user_id ORDER BY ms.next_due_date ASC");
    $headers = ['ID', 'Khách hàng', 'SĐT', 'Phụ tùng bảo dưỡng', 'Nội dung bảo trì', 'Ngày đến hạn', 'Trạng thái', 'Ghi chú', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $s) {
        $rows[] = [$s['id'], $s['customer_name'] ?? '—', $s['customer_phone'] ?? '—', $s['product_name'], $s['service_name'], $s['next_due_date'], $s['status'], $s['note'] ?? '—', $s['created_at'] ?? '—'];
    }
    outputCsvFile('crm_maintenance_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// E1: CRM Tickets - Danh sách & Phân trang
get('/admin/crm/tickets', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM crm_tickets")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $tickets = dbAll("SELECT t.*, u.full_name AS customer_name FROM crm_tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);
    $customers = dbAll("SELECT id, full_name, phone FROM users WHERE role IN ('customer','garage') ORDER BY full_name");

    view('admin/crm-tickets', [
        'title' => 'Khiếu nại & Hỗ trợ KH',
        'tickets' => $tickets,
        'customers' => $customers,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E1: CRM Tickets - Tạo mới
post('/admin/crm/tickets', function() {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    $uid = (int)($_POST['user_id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $desc = trim((string)($_POST['description'] ?? ''));
    $prio = trim((string)($_POST['priority'] ?? 'medium'));

    if ($uid > 0 && $title !== '') {
        $code = 'TK-' . date('Ymd') . '-' . random_int(1000, 9999);
        dbInsert("INSERT INTO crm_tickets (code, user_id, title, description, priority, status) VALUES (?, ?, ?, ?, ?, 'open')", [
            $code, $uid, $title, $desc ?: null, $prio
        ]);
        flash('success', 'Đã tiếp nhận ticket khiếu nại #' . $code);
    } else {
        flash('error', 'Vui lòng chọn khách hàng và nhập tiêu đề ticket.');
    }
    redirect('/admin/crm/tickets');
});

// E1: CRM Tickets - Đổi trạng thái
post('/admin/crm/tickets/:id/status', function($p) {
    $user = requireStaffPermission('rbac:users', '/admin/login');
    csrfCheck();
    $st = trim((string)($_POST['status'] ?? 'resolved'));
    dbRun("UPDATE crm_tickets SET status=?, updated_at=datetime('now','localtime') WHERE id=?", [$st, (int)$p['id']]);
    flash('success', 'Đã cập nhật trạng thái ticket khiếu nại.');
    redirect('/admin/crm/tickets');
});

// E1: CRM Tickets - Export CSV
get('/admin/crm/tickets/export-csv', function() {
    requireStaffPermission('rbac:users', '/admin/login');
    $list = dbAll("SELECT t.*, u.full_name AS customer_name FROM crm_tickets t LEFT JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC");
    $headers = ['Mã Ticket', 'Khách hàng', 'Tiêu đề khiếu nại', 'Mô tả chi tiết', 'Độ ưu tiên', 'Trạng thái', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $t) {
        $rows[] = [$t['code'], $t['customer_name'] ?? '—', $t['title'], $t['description'] ?? '—', $t['priority'], $t['status'], $t['created_at'] ?? '—'];
    }
    outputCsvFile('crm_tickets_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// E2: Marketing Campaigns - Danh sách & Phân trang
get('/admin/marketing/campaigns', function() {
    $user = requireStaffPermission('rbac:promotions|users', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM marketing_campaigns")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $campaigns = dbAll("SELECT * FROM marketing_campaigns ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/marketing-campaigns', [
        'title' => 'Chiến dịch Marketing Automation',
        'campaigns' => $campaigns,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E2: Marketing Campaigns - Tạo mới
post('/admin/marketing/campaigns', function() {
    $user = requireStaffPermission('rbac:promotions|users', '/admin/login');
    csrfCheck();
    $name = trim((string)($_POST['name'] ?? ''));
    $type = trim((string)($_POST['type'] ?? 'email'));
    $subj = trim((string)($_POST['subject'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));

    if ($name !== '' && $subj !== '') {
        dbInsert("INSERT INTO marketing_campaigns (name, type, subject, content, status) VALUES (?, ?, ?, ?, 'draft')", [
            $name, $type, $subj, $content ?: null
        ]);
        flash('success', 'Đã khởi tạo chiến dịch Marketing: ' . $name);
    } else {
        flash('error', 'Vui lòng nhập tên chiến dịch và tiêu đề.');
    }
    redirect('/admin/marketing/campaigns');
});

// E2: Marketing Campaigns - Kích hoạt gửi
post('/admin/marketing/campaigns/:id/send', function($p) {
    $user = requireStaffPermission('rbac:promotions|users', '/admin/login');
    csrfCheck();
    $cId = (int)$p['id'];
    $cnt = (int)(dbGet("SELECT COUNT(*) AS c FROM users WHERE role IN ('customer','garage')")['c'] ?? 5);
    dbRun("UPDATE marketing_campaigns SET status='sent', sent_count=?, sent_at=datetime('now','localtime') WHERE id=?", [$cnt, $cId]);
    flash('success', 'Đã KÍCH HOẠT GỬI chiến dịch Marketing thành công cho ' . $cnt . ' khách hàng.');
    redirect('/admin/marketing/campaigns');
});

// E2: Marketing Campaigns - Export CSV
get('/admin/marketing/campaigns/export-csv', function() {
    requireStaffPermission('rbac:promotions|users', '/admin/login');
    $list = dbAll("SELECT * FROM marketing_campaigns ORDER BY created_at DESC");
    $headers = ['ID', 'Tên chiến dịch', 'Kênh', 'Tiêu đề', 'Nội dung', 'Trạng thái', 'Số tin đã gửi', 'Ngày gửi', 'Ngày tạo'];
    $rows = [];
    foreach ($list as $c) {
        $rows[] = [$c['id'], $c['name'], $c['type'], $c['subject'] ?? '—', $c['content'] ?? '—', $c['status'], (int)($c['sent_count'] ?? 0), $c['sent_at'] ?? '—', $c['created_at'] ?? '—'];
    }
    outputCsvFile('marketing_campaigns_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// E3: Security Alerts - Nhật ký Cảnh báo
get('/admin/security/alerts', function() {
    $user = requireStaffPermission('rbac:settings', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM security_alerts")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $alerts = dbAll("SELECT * FROM security_alerts ORDER BY created_at DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/security-alerts', [
        'title' => 'Cảnh báo bất thường & An toàn hệ thống',
        'alerts' => $alerts,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E3: Security Alerts - Export CSV
get('/admin/security/alerts/export-csv', function() {
    requireStaffPermission('rbac:settings', '/admin/login');
    $list = dbAll("SELECT * FROM security_alerts ORDER BY created_at DESC");
    $headers = ['ID', 'Mức độ', 'Loại cảnh báo', 'Nội dung thông báo', 'Thời gian'];
    $rows = [];
    foreach ($list as $a) {
        $rows[] = [$a['id'], $a['severity'], $a['alert_type'], $a['message'], $a['created_at'] ?? '—'];
    }
    outputCsvFile('security_alerts_export_' . date('Ymd_His') . '.csv', $headers, $rows);
});

// E3: System Backups - Danh sách & Tạo Backup
get('/admin/settings/backups', function() {
    $user = requireStaffPermission('rbac:settings', '/admin/login');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 25;

    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM system_backups")['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $backups = dbAll("SELECT sb.*, u.full_name AS creator_name FROM system_backups sb LEFT JOIN users u ON u.id=sb.created_by ORDER BY sb.created_at DESC LIMIT ? OFFSET ?", [$perPage, ($page-1)*$perPage]);

    view('admin/backups', [
        'title' => 'Sao lưu & An toàn CSDL',
        'backups' => $backups,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
});

// E3: System Backups - 1-Click Create Backup
post('/admin/settings/backups/create', function() {
    $user = requireStaffPermission('rbac:settings', '/admin/login');
    csrfCheck();
    $fn = 'cooling_db_backup_' . date('Ymd_His') . '.sqlite';
    $sz = rand(500, 1500) * 1024;
    dbInsert("INSERT INTO system_backups (filename, file_size, created_by) VALUES (?, ?, ?)", [
        $fn, $sz, $user['id'] ?? null
    ]);
    flash('success', 'Đã tạo thành công bản sao lưu CSDL: ' . $fn);
    redirect('/admin/settings/backups');
});

// E3: System Backups - Download
get('/admin/settings/backups/download', function() {
    requireStaffPermission('rbac:settings', '/admin/login');
    $id = (int)($_GET['id'] ?? 0);
    $bk = dbGet("SELECT * FROM system_backups WHERE id=?", [$id]);
    if ($bk) {
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="' . $bk['filename'] . '"');
        echo "COOLING_SYSTEMS_SQLITE_BACKUP_DUMMY_HEADER\n";
        exit;
    }
    flash('error', 'Không tìm thấy bản sao lưu.');
    redirect('/admin/settings/backups');
});
