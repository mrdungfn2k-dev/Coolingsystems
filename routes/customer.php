<?php
require_once __DIR__ . '/../includes/inventory-alerts.php';

// Customer notifications page
get('/customer/notifications', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    $perPage = 4;
    $page = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $total = dbGet("SELECT COUNT(*) AS cnt FROM user_notifications WHERE user_id=?", [$user['id']])['cnt'] ?? 0;
    $totalPages = max(1, ceil($total / $perPage));
    $notifications = dbAll("SELECT * FROM user_notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ? OFFSET ?", [$user['id'], $perPage, $offset]);
    view('customer/notifications', ['title'=>'Thông báo','notifications'=>$notifications,'page'=>$page,'totalPages'=>$totalPages,'total'=>$total]);
});

get('/customer/orders', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    $orders = dbAll("SELECT o.*, (SELECT COUNT(*) FROM sub_orders WHERE order_id=o.id) AS shop_count FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC", [$user['id']]);
    $returns = dbAll("SELECT * FROM order_returns WHERE user_id=?", [$user['id']]);
    $returnsByOrderId = [];
    foreach ($returns as $rt) { $returnsByOrderId[$rt['order_id']][] = $rt; }
    view('customer/orders', ['title'=>'Đơn hàng của tôi','orders'=>$orders,'returnsByOrderId'=>$returnsByOrderId]);
});



// Customer order detail
get('/customer/orders/:id', function($p) {
    $user = requireRole(['customer','staff'], '/auth/login');
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    if (!$order) { flash('error','Không tìm thấy đơn hàng.'); redirect('/customer/orders'); return; }
    $items = dbAll("SELECT oi.*, p.name AS product_name, p.slug, (SELECT file_path FROM product_images WHERE product_id=p.id LIMIT 1) AS image FROM order_items oi LEFT JOIN sub_orders so ON so.id=oi.sub_order_id LEFT JOIN products p ON p.id=oi.product_id WHERE so.order_id=?", [$order['id']]);
    $invoice = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$user['id']]);
    view('customer/order-detail', ['title'=>'Chi tiết đơn hàng #'.$order['code'],'order'=>$order,'items'=>$items,'invoice'=>$invoice]);
});

post('/customer/orders/:id/return', function($p) {
    $user = $user = currentUser();
    if (!$user) {
        flash('info', 'Vui lòng đăng nhập hoặc đăng ký để sử dụng giỏ hàng.');
        redirect('/auth/login');
        return;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        redirect('/auth/login'); exit;
    } csrfCheck();
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    if (!$order) { flash('error', 'Không tìm thấy đơn hàng.'); redirect('/customer/orders'); return; }
    // Allow return for delivered or completed orders
    if (!in_array($order['delivery_status'], ['delivered','completed'])) {
        flash('error', 'Chỉ có thể yêu cầu trả hàng cho đơn đã giao.'); redirect('/customer/orders'); return;
    }
    
    $reason = trim($_POST['reason'] ?? '');
    $phone = trim($_POST['contact_phone'] ?? '');
    $email = trim($_POST['contact_email'] ?? '');
    $address = trim($_POST['contact_address'] ?? '');
    
    if (!$reason || !$phone || !$address) {
        flash('error', 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
        redirect('/customer/orders');
    }
    
    $imagePath = '';
    if (!empty($_FILES['return_image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['return_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $dest = '/var/lib/coolingsystems/uploads/returns/'.uniqid().'.'.$ext;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            if (move_uploaded_file($_FILES['return_image']['tmp_name'], $dest)) {
                $imagePath = '/uploads/returns/'.basename($dest);
            }
        }
    }
    $videoPath = '';
    if (!empty($_FILES['return_video']['tmp_name'])) {
        $vext = strtolower(pathinfo($_FILES['return_video']['name'], PATHINFO_EXTENSION));
        if (in_array($vext, ['mp4','webm','mov'])) {
            $vdest = '/var/lib/coolingsystems/uploads/returns/'.uniqid().'.'.$vext;
            if (!is_dir(dirname($vdest))) mkdir(dirname($vdest), 0777, true);
            if (move_uploaded_file($_FILES['return_video']['tmp_name'], $vdest)) {
                $videoPath = '/uploads/returns/'.basename($vdest);
            }
        }
    }
    
        $bankAccount = trim($_POST['bank_account'] ?? '');
    $bankHolder = trim($_POST['bank_holder'] ?? '');
    $refundAmount = intval($_POST['refund_amount'] ?? 0);
    
    dbRun("INSERT INTO order_returns (order_id, user_id, reason, image_path, contact_phone, contact_email, contact_address, bank_account, bank_holder, refund_amount, video_path, bank_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
        [$order['id'], $user['id'], $reason, $imagePath, $phone, $email, $address, $bankAccount, $bankHolder, $refundAmount, $videoPath, trim($_POST['bank_name']??'')]);
        
    
    // Gửi thông báo cho admin
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES (?,?,?,?)", [
        'return_request',
        'Yêu cầu trả hàng mới',
        'Khách hàng ' . ($user['full_name'] ?? 'N/A') . ' yêu cầu trả hàng đơn #' . $order['code'] . '. Lý do: ' . mb_substr($reason, 0, 80),
        '/admin/returns'
    ]);
    
    // Gửi thông báo cho customer
    dbRun("INSERT INTO user_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)", [
        $user['id'],
        'return_submitted',
        'Đã gửi yêu cầu trả hàng',
        'Yêu cầu trả hàng cho đơn #' . $order['code'] . ' đã được ghi nhận. Chúng tôi sẽ phản hồi trong 24h.',
        '/customer/orders'
    ]);
    
flash('success', 'Đã gửi yêu cầu trả hàng. Vui lòng chờ admin duyệt.');
    redirect('/customer/orders');
});

// Customer notification routes
post('/customer/notifications/:id/read', function($p) {
    $user = requireRole(['customer','staff'], '/auth/login');
    dbRun("UPDATE user_notifications SET is_read=1 WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    flash('success', 'Đã đánh dấu đã đọc.');
    redirect('/customer/notifications');
});

post('/customer/notifications/read-all', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    dbRun("UPDATE user_notifications SET is_read=1 WHERE user_id=? AND is_read=0", [$user['id']]);
    flash('success', 'Đã đánh dấu tất cả đã đọc.');
    redirect('/customer/notifications');
});

post('/customer/notifications/:id/delete', function($p) {
    $user = requireRole(['customer','staff'], '/auth/login');
    dbRun("DELETE FROM user_notifications WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    flash('success', 'Đã xóa thông báo.');
    redirect('/customer/notifications');
});


get('/customer/garage', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    $garages = dbAll("SELECT g.*, b.name AS brand_name, m.name AS model_name FROM garages g INNER JOIN brands b ON b.id=g.brand_id INNER JOIN car_models m ON m.id=g.model_id WHERE g.user_id=? ORDER BY g.is_default DESC", [$user['id']]);
    $brands = dbAll("SELECT * FROM brands ORDER BY sort_order, name");
    view('customer/garage', ['title'=>'Garage của tôi','garages'=>$garages,'brands'=>$brands,'max'=>5]);
});

get('/customer/cart', function() {
    $user = currentUser();
    if (!$user) {
        $items = [];
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $pid => $qty) {
                $p = dbGet("SELECT p.id as product_id, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.oem_code, pt.shop_name, pt.id AS partner_id, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p LEFT JOIN partners pt ON pt.id=p.partner_id WHERE p.id=?", [$pid]);
                if ($p) {
                    $p['quantity'] = $qty;
                    $items[] = $p;
                }
            }
        }
        view('customer/cart', ['title'=>'Giỏ hàng','items'=>$items]);
        return;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        redirect('/auth/login'); exit;
    }
    $items = dbAll("SELECT ci.*, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.oem_code, pt.shop_name, pt.id AS partner_id, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM cart_items ci INNER JOIN products p ON p.id=ci.product_id LEFT JOIN partners pt ON pt.id=p.partner_id WHERE ci.user_id=?", [$user['id']]);
    view('customer/cart', ['title'=>'Giỏ hàng','items'=>$items]);
});

get('/customer/profile', function() {
    $user = requireRole(['customer','staff','admin'], '/auth/login');
    $userGarages = dbAll("SELECT g.*, b.name AS brand_name, m.name AS model_name FROM garages g LEFT JOIN brands b ON b.id=g.brand_id LEFT JOIN car_models m ON m.id=g.model_id WHERE g.user_id=? ORDER BY g.is_default DESC, g.id DESC", [$user['id']]);
    $userQuotations = dbAll("SELECT * FROM garage_quotations WHERE user_id=? ORDER BY id DESC", [$user['id']]);
    $carBrands = dbAll("SELECT * FROM brands ORDER BY name ASC");
    $carModels = dbAll("SELECT * FROM car_models ORDER BY name ASC");
    $title = 'Hồ sơ tài khoản & Quản lý Gara';
    view('customer/profile', compact('title', 'userGarages', 'userQuotations', 'carBrands', 'carModels'));
});

post('/customer/quotation/request', function() {
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false, 'error'=>'Vui lòng đăng nhập']); exit; }
    csrfCheck();
    
    $note = trim($_POST['note'] ?? '');
    $filePath = '';

    if (!empty($_FILES['quote_file']['name'])) {
        $file = $_FILES['quote_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','pdf','xls','xlsx'])) {
            $fname = 'quote_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dir = __DIR__ . '/../public/uploads/quotations/';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            move_uploaded_file($file['tmp_name'], $dir . $fname);
            $filePath = '/uploads/quotations/' . $fname;
        }
    }

    dbInsert("INSERT INTO garage_quotations (user_id, full_name, phone, garage_name, note, file_path, status, created_at) VALUES (?,?,?,?,?,?,'pending',datetime('now','localtime'))", [
        $user['id'],
        $user['full_name'],
        $user['phone'] ?? '',
        $user['garage_name'] ?? 'Gara Cá Nhân',
        $note,
        $filePath
    ]);

    echo json_encode(['ok'=>true, 'msg'=>'Đã gửi yêu cầu báo giá thành công! Kỹ thuật viên sẽ phản hồi trong ít phút.']);
    exit;
});

post('/customer/garage/add', function() {
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false, 'error'=>'Vui lòng đăng nhập']); exit; }
    csrfCheck();
    $brandId = intval($_POST['brand_id'] ?? 0);
    $modelId = intval($_POST['model_id'] ?? 0);
    $year = intval($_POST['year'] ?? date('Y'));
    $trim = trim($_POST['trim'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $isDefault = !empty($_POST['is_default']) ? 1 : 0;

    if (!$brandId || !$modelId) {
        echo json_encode(['ok'=>false, 'error'=>'Vui lòng chọn Hãng xe và Dòng xe']);
        exit;
    }

    if ($isDefault) {
        dbRun("UPDATE garages SET is_default=0 WHERE user_id=?", [$user['id']]);
    }

    $id = dbInsert("INSERT INTO garages (user_id, brand_id, model_id, year, trim, label, is_default, created_at) VALUES (?,?,?,?,?,?,?,datetime('now','localtime'))", 
        [$user['id'], $brandId, $modelId, $year, $trim, $label, $isDefault]);

    if (!empty($_POST['garage_name'])) {
        dbRun("UPDATE users SET is_verified_garage=1, garage_name=? WHERE id=?", [trim($_POST['garage_name']), $user['id']]);
    }

    echo json_encode(['ok'=>true, 'id'=>$id]);
    exit;
});

post('/customer/garage/delete', function() {
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false, 'error'=>'Vui lòng đăng nhập']); exit; }
    csrfCheck();
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok'=>false, 'error'=>'Mã xe không hợp lệ']); exit; }

    dbRun("DELETE FROM garages WHERE id=? AND user_id=?", [$id, $user['id']]);

    echo json_encode(['ok'=>true, 'msg'=>'Đã xóa xe khỏi danh sách']);
    exit;
});

post('/customer/garage/set-default', function() {
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false, 'error'=>'Vui lòng đăng nhập']); exit; }
    csrfCheck();
    $id = intval($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['ok'=>false, 'error'=>'Mã xe không hợp lệ']); exit; }

    dbRun("UPDATE garages SET is_default=0 WHERE user_id=?", [$user['id']]);
    dbRun("UPDATE garages SET is_default=1 WHERE id=? AND user_id=?", [$id, $user['id']]);

    echo json_encode(['ok'=>true, 'msg'=>'Đã đặt làm xe mặc định']);
    exit;
});

post('/customer/profile', function() {
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') { redirect('/auth/login'); exit; }
    csrfCheck();
    $badChars = '/[!@#$%^&*()+=[\\]{};:\'"<>?|]/';
    $name = preg_replace($badChars, '', trim($_POST['full_name'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    
    $shipping_province = preg_replace($badChars, '', trim($_POST['shipping_province'] ?? ''));
    $shipping_district = preg_replace($badChars, '', trim($_POST['shipping_district'] ?? ''));
    $shipping_ward = preg_replace($badChars, '', trim($_POST['shipping_ward'] ?? ''));
    $shipping_detail = preg_replace($badChars, '', trim($_POST['shipping_detail'] ?? ''));
    $addressParts = array_filter([$shipping_detail, $shipping_ward, $shipping_district, $shipping_province]);
    $address = implode(', ', $addressParts);

    if (!$name || preg_match('/[0-9]/', $name)) { flash('error','Họ tên không hợp lệ.'); redirect('/customer/profile'); return; }
    if ($phone && !preg_match('/^0[1-9][0-9]{8}$/', $phone)) { flash('error','Số điện thoại không hợp lệ.'); redirect('/customer/profile'); return; }
    $avatarFile = $_FILES['avatar'] ?? null;
    $newAvatar = $user['avatar'] ?? '';
    if ($avatarFile && $avatarFile['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($avatarFile['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $dest = '/var/lib/coolingsystems/uploads/avatars/' . uniqid('av_') . '.' . $ext;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);
            if (move_uploaded_file($avatarFile['tmp_name'], $dest)) { $newAvatar = basename($dest); }
        }
    }
    dbRun("UPDATE users SET full_name=?, phone=?, address=?, avatar=?, updated_at=datetime('now','localtime') WHERE id=?",
        [$name, $phone, $address, $newAvatar, $user['id']]);
    flash('success','Cập nhật thành công!');
    redirect('/customer/profile');
});

get('/customer/favorites', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    $items = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM favorites f INNER JOIN products p ON p.id=f.product_id WHERE f.user_id=?", [$user['id']]);
    view('customer/favorites', ['title'=>'Yêu thích','items'=>$items]);
});

post('/customer/favorites/add', function() {
    $user = currentUser();
    if (!$user) {
        redirect('/auth/login'); exit;
    }
    csrfCheck();
    $pid = intval($_POST['product_id'] ?? 0);
    if ($pid) {
        dbRun("INSERT OR IGNORE INTO favorites (user_id, product_id, created_at) VALUES (?,?,datetime('now','localtime'))", [$user['id'], $pid]);
        flash('success', 'Đã thêm vào yêu thích.');
    }
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
});

get('/customer/vouchers', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    $vouchers = dbAll("SELECT v.* FROM voucher_saves vs INNER JOIN vouchers v ON v.id=vs.voucher_id WHERE vs.user_id=? ORDER BY v.valid_to DESC", [$user['id']]);
    view('customer/vouchers', ['title'=>'Voucher của tôi','vouchers'=>$vouchers]);
});

// ── Cart: Add to cart (AJAX + form fallback) ──────────────────────────────
post('/customer/cart/add', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    $pid = intval($_POST['product_id'] ?? 0);
    $qty = max(1, intval($_POST['qty'] ?? 1));
    if (!$pid) { echo json_encode(['ok'=>false,'msg'=>'Sản phẩm không hợp lệ']); exit; }

    $p = dbGet("SELECT id, stock, status FROM products WHERE id=? AND status='published'", [$pid]);
    if (!$p) { echo json_encode(['ok'=>false,'msg'=>'Sản phẩm không tồn tại hoặc đã ngừng bán']); exit; }
    if ($p['stock'] < $qty) { echo json_encode(['ok'=>false,'msg'=>'Không đủ hàng trong kho']); exit; }
    if ($qty > 10) { echo json_encode(['ok'=>false,'msg'=>'Mỗi sản phẩm chỉ được đặt tối đa 10 số lượng']); exit; }

    if (!$user) {
        if (!isset($_SESSION['guest_cart'])) $_SESSION['guest_cart'] = [];
        $cart = &$_SESSION['guest_cart'];
        

        $newQty = min(($cart[$pid] ?? 0) + $qty, min(10, $p['stock']));
        if ($newQty > 10) $newQty = 10;
        $cart[$pid] = $newQty;
        
        
        $cnt = 0;
        $total = 0;
        foreach ($_SESSION['guest_cart'] as $c_pid => $c_qty) {
            $cp = dbGet("SELECT CASE WHEN is_on_sale=1 AND sale_price>0 AND sale_price<price THEN sale_price ELSE price END AS final_price FROM products WHERE id=?", [$c_pid]);
            if ($cp) {
                $cnt += $c_qty;
                $total += $cp['final_price'] * $c_qty;
            }
        }
        echo json_encode(['ok'=>true,'msg'=>'Đã thêm vào giỏ hàng!','cartCount'=>$cnt,'cart_count'=>$cnt,'cart_total'=>number_format($total, 0, ',', '.') . ' ₫']);
        exit;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        echo json_encode(['ok'=>false,'redirect'=>'/auth/login']); exit;
    }



    $existing = dbGet("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
    if ($existing) {
        $newQty = min($existing['quantity'] + $qty, min(10, $p['stock']));
        if ($newQty > 10) { $newQty = 10; }
        dbRun("UPDATE cart_items SET quantity=? WHERE id=?", [$newQty, $existing['id']]);
    } else {
        dbRun("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?,?,?)", [$user['id'], $pid, $qty]);
    }

    $cartCount = dbGet("SELECT SUM(quantity) AS n FROM cart_items WHERE user_id=?", [$user['id']])['n'] ?? 0;
    
    $cartInfo = dbGet("SELECT COALESCE(SUM(ci.quantity),0) AS cnt, COALESCE(SUM((CASE WHEN pr.is_on_sale=1 AND pr.sale_price>0 AND pr.sale_price<pr.price THEN pr.sale_price ELSE pr.price END) * ci.quantity), 0) AS total FROM cart_items ci INNER JOIN products pr ON pr.id = ci.product_id WHERE ci.user_id = ?", [$user['id']]);
    $cnt = $cartInfo['cnt'] ?? 0;
    $total = $cartInfo['total'] ?? 0;
    echo json_encode(['ok'=>true,'msg'=>'Đã thêm vào giỏ hàng!','cartCount'=>(int)$cnt,'cart_count'=>(int)$cnt,'cart_total'=>number_format($total, 0, ',', '.') . ' ₫']);
    exit;
});


// ── Cart: Quick Add (AJAX - JSON response) ───────────────────────────────────
post('/customer/cart/quick-add', function() {
    header('Content-Type: application/json');
    $user = currentUser();

    $pid = intval($_POST['product_id'] ?? 0);
    $qty = max(1, intval($_POST['quantity'] ?? 1));
    if (!$pid) {
        echo json_encode(['ok' => false, 'msg' => 'Sản phẩm không hợp lệ.']);
        exit;
    }

    $p = dbGet("SELECT id, stock, status, price FROM products WHERE id=? AND status='published'", [$pid]);
    if (!$p || $p['stock'] < $qty) {
        echo json_encode(['ok' => false, 'msg' => 'Sản phẩm hết hàng hoặc không tồn tại.']);
        exit;
    }

    if (!$user) {
        if (!isset($_SESSION['guest_cart'])) $_SESSION['guest_cart'] = [];
        $cart = &$_SESSION['guest_cart'];
        
        $newQty = min(($cart[$pid] ?? 0) + $qty, min(10, $p['stock']));
        if ($newQty > 10) $newQty = 10;
        $cart[$pid] = $newQty;
        
        $cnt = 0;
        $total = 0;
        foreach ($_SESSION['guest_cart'] as $c_pid => $c_qty) {
            $cp = dbGet("SELECT CASE WHEN is_on_sale=1 AND sale_price>0 AND sale_price<price THEN sale_price ELSE price END AS final_price FROM products WHERE id=?", [$c_pid]);
            if ($cp) {
                $cnt += $c_qty;
                $total += $cp['final_price'] * $c_qty;
            }
        }
        
        echo json_encode([
            'ok' => true,
            'success' => true,
            'msg' => 'Đã thêm sản phẩm vào giỏ hàng.',
            'cart_count' => $cnt,
            'cart_total' => number_format($total, 0, ',', '.') . ' ₫',
        ]);
        exit;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        echo json_encode(['ok' => false, 'msg' => 'Tài khoản không hợp lệ.']);
        exit;
    }

    $existing = dbGet("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
    if ($existing) {
        dbRun("UPDATE cart_items SET quantity=MIN(quantity+?, ?) WHERE id=?", [$qty, $p['stock'], $existing['id']]);
    } else {
        dbRun("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?,?,?)", [$user['id'], $pid, $qty]);
    }

    $cartInfo = dbGet("SELECT COALESCE(SUM(ci.quantity),0) AS cnt, COALESCE(SUM((CASE WHEN pr.is_on_sale=1 AND pr.sale_price>0 AND pr.sale_price<pr.price THEN pr.sale_price ELSE pr.price END) * ci.quantity), 0) AS total
        FROM cart_items ci INNER JOIN products pr ON pr.id = ci.product_id
        WHERE ci.user_id = ?", [$user['id']]);
    $cnt = $cartInfo['cnt'] ?? 0;
    $total = $cartInfo['total'] ?? 0;

    echo json_encode([
        'ok' => true,
        'success' => true,
        'msg' => 'Đã thêm sản phẩm vào giỏ hàng.',
        'cart_count' => $cnt,
        'cart_total' => number_format($total, 0, ',', '.') . ' ₫',
    ]);
    exit;
});

// ── Cart: Remove item ────────────────────────────────────────────────────────
post('/customer/cart/remove', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    $pid = intval($_POST['product_id'] ?? 0);
    
    if (!$user) {
        if (isset($_SESSION['guest_cart'][$pid])) unset($_SESSION['guest_cart'][$pid]);
        echo json_encode(['ok'=>true,'cartCount'=>array_sum($_SESSION['guest_cart'] ?? [])]);
        exit;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        echo json_encode(['ok'=>false,'redirect'=>'/auth/login']); exit;
    }
    if ($pid) dbRun("DELETE FROM cart_items WHERE user_id=? AND product_id=?", [$user['id'], $pid]);
    $cartCount = dbGet("SELECT SUM(quantity) AS n FROM cart_items WHERE user_id=?", [$user['id']])['n'] ?? 0;
    echo json_encode(['ok'=>true,'cartCount'=>(int)$cartCount]);
    exit;
});

// ── Cart: Update quantity ────────────────────────────────────────────────────
post('/customer/cart/update', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    $pid = intval($_POST['product_id'] ?? 0);
    $qty = max(1, intval($_POST['qty'] ?? 1));
    $p = dbGet("SELECT stock FROM products WHERE id=?", [$pid]);
    
    if (!$user) {
        if ($p && $qty <= $p['stock'] && isset($_SESSION['guest_cart'][$pid])) {
            $_SESSION['guest_cart'][$pid] = $qty;
        }
        echo json_encode(['ok'=>true]);
        exit;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        echo json_encode(['ok'=>false,'redirect'=>'/auth/login']); exit;
    }
    
    if ($p && $qty <= $p['stock']) {
        dbRun("UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?", [$qty, $user['id'], $pid]);
    }
    echo json_encode(['ok'=>true]);
    exit;
});

// ── Voucher: Apply & Remove ──────────────────────────────────────────────────
post('/customer/cart/apply-voucher', function() {
    $user = currentUser();
    if (!$user) {
        flash('info', 'Vui lòng đăng nhập hoặc đăng ký để sử dụng giỏ hàng.');
        redirect('/auth/login');
        return;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        redirect('/auth/login'); exit;
    } csrfCheck();
    $code = strtoupper(trim($_POST['voucher_code'] ?? ''));
    if (!$code) {
        flash('error', 'Vui lòng nhập mã giảm giá.');
        redirect('/customer/cart');
    }
    
    // Check voucher in DB
    $v = dbGet("SELECT * FROM vouchers WHERE code = ?", [$code]);
    if (!$v) {
        flash('error', 'Mã giảm giá không tồn tại.');
        redirect('/customer/cart');
    }
    
    if (($v['valid_to'] ?? $v['expires_at'] ?? '') && substr(($v['valid_to'] ?? $v['expires_at'] ?? ''), 0, 10) < date('Y-m-d')) {
        flash('error', 'Mã giảm giá đã hết hạn.');
        redirect('/customer/cart');
    }
    
    if (($v['total_quantity'] ?? $v['max_uses'] ?? 0) > 0 && ($v['used_quantity'] ?? $v['used_count'] ?? 0) >= ($v['total_quantity'] ?? $v['max_uses'] ?? 0)) {
        flash('error', 'Mã giảm giá đã hết lượt sử dụng.');
        redirect('/customer/cart');
    }
    
    // Validate voucher scope
    $scope = $v['scope'] ?? 'platform';
    $user = currentUser();
    
    if ($scope === 'shop') {
        $sv = json_decode($v['scope_value'] ?? '', true);
        $brandIds = (is_array($sv) && !empty($sv['brands'])) ? array_values(array_filter(array_map('intval', $sv['brands']))) : [];
        $pbIds = (is_array($sv) && !empty($sv['product_brands'])) ? array_values(array_filter(array_map('intval', $sv['product_brands']))) : [];
        if ($brandIds || $pbIds) {
            $matched = false;
            if ($brandIds) {
                $phb = implode(',', array_fill(0, count($brandIds), '?'));
                if (dbGet("SELECT 1 FROM cart_items ci INNER JOIN products p ON p.id=ci.product_id WHERE ci.user_id=? AND (p.car_brand_id IN ($phb) OR p.id IN (SELECT product_id FROM product_fitments WHERE brand_id IN ($phb))) LIMIT 1", array_merge([$user['id']], $brandIds, $brandIds))) $matched = true;
            }
            if (!$matched && $pbIds) {
                $php2 = implode(',', array_fill(0, count($pbIds), '?'));
                foreach (dbAll("SELECT name FROM product_brands WHERE id IN ($php2)", $pbIds) as $nm) {
                    $bn = $nm['name'];
                    if (dbGet("SELECT 1 FROM cart_items ci INNER JOIN products p ON p.id=ci.product_id WHERE ci.user_id=? AND (p.part_brand=? OR p.part_brand LIKE ? OR p.part_brand LIKE ? OR p.part_brand LIKE ?) LIMIT 1", [$user['id'], $bn, $bn.',%', '%, '.$bn.',%', '%, '.$bn])) { $matched = true; break; }
                }
            }
            if (!$matched) {
                $labels = [];
                if ($brandIds) { $pl = implode(',', array_fill(0, count($brandIds), '?')); foreach (dbAll("SELECT name FROM brands WHERE id IN ($pl)", $brandIds) as $b) $labels[] = $b['name']; }
                if ($pbIds) { $pl = implode(',', array_fill(0, count($pbIds), '?')); foreach (dbAll("SELECT name FROM product_brands WHERE id IN ($pl)", $pbIds) as $b) $labels[] = $b['name']; }
                flash('error', 'Mã giảm giá chỉ áp dụng cho: ' . implode(', ', $labels) . '. Giỏ hàng chưa có sản phẩm phù hợp.');
                redirect('/customer/cart'); return;
            }
        }
    } elseif ($scope === 'new_customer') {
        // Check if user has 0 previous completed orders
        $orderCount = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$user['id']])['c'] ?? 0;
        if ($orderCount > 0) {
            flash('error', 'Mã giảm giá này chỉ dành cho khách hàng mới (chưa có đơn hàng nào).');
            redirect('/customer/cart'); return;
        }
    }
    
    // Normalize voucher fields for checkout
    $v['discount_amount'] = $v['discount_value'] ?? $v['discount_amount'] ?? 0;
    $_SESSION['cart_voucher'] = $v;
    flash('success', 'Áp dụng mã giảm giá thành công!');
    redirect('/customer/cart');
});

post('/customer/orders/:id/rebuy', function($p) {
    header('Content-Type: application/json');
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') {
        echo json_encode(['ok'=>false,'msg'=>'Vui lòng đăng nhập']); exit;
    }
    // CSRF check (JSON-safe - don't redirect)
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(['ok'=>false,'msg'=>'Phiên làm việc hết hạn. Vui lòng tải lại trang.']); exit;
    }
    $orderId = intval($p['id']);
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [$orderId, $user['id']]);
    if (!$order) { echo json_encode(['ok'=>false,'msg'=>'Không tìm thấy đơn hàng']); exit; }
    
    // Get items from order (via sub_orders join)
    $items = dbAll("SELECT oi.product_id, oi.quantity, p.stock, p.status 
                    FROM order_items oi 
                    JOIN sub_orders so ON so.id=oi.sub_order_id
                    JOIN products p ON p.id=oi.product_id 
                    WHERE so.order_id=?", [$orderId]);
    
    if (empty($items)) { echo json_encode(['ok'=>false,'msg'=>'Đơn hàng không có sản phẩm']); exit; }
    
    $added = 0;
    foreach ($items as $item) {
        if ($item['status'] !== 'published' || $item['stock'] < 1) continue;
        $qty = min($item['quantity'], min(10, $item['stock']));
        // Check if already in cart
        $existing = dbGet("SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?", 
                          [$user['id'], $item['product_id']]);
        if ($existing) {
            $newQty = min(10, $existing['quantity'] + $qty);
            dbRun("UPDATE cart_items SET quantity=? WHERE id=?", [$newQty, $existing['id']]);
        } else {
            dbRun("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?,?,?)",
                  [$user['id'], $item['product_id'], $qty]);
        }
        $added++;
    }
    
    if ($added === 0) { echo json_encode(['ok'=>false,'msg'=>'Các sản phẩm trong đơn hàng hiện không còn hàng']); exit; }
    echo json_encode(['ok'=>true,'msg'=>"Đã thêm $added sản phẩm vào giỏ hàng"]);
});

get('/customer/cart/remove-voucher', function() {
    $user = currentUser();
    if (!$user) {
        flash('info', 'Vui lòng đăng nhập hoặc đăng ký để sử dụng giỏ hàng.');
        redirect('/auth/login');
        return;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        redirect('/auth/login'); exit;
    }
    unset($_SESSION['cart_voucher']);
    flash('success', 'Đã gỡ mã giảm giá.');
    redirect('/customer/cart');
});

// ── Checkout: Show form ─────────────────────────────────────────────────────

// ── Pay remaining 30% after delivery ─────────────────────────────────────────
post('/customer/orders/:id/pay-remaining', function($p) {
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') { redirect('/auth/login'); exit; }
    csrfCheck();
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [(int)$p['id'], $user['id']]);
    if (!$order) { flash('error', 'Đơn hàng không tồn tại'); redirect('/customer/orders'); return; }
    if ($order['delivery_status'] !== 'delivered') { flash('error', 'Đơn hàng chưa được giao'); redirect('/customer/orders'); return; }
    if ($order['payment_status'] === 'paid') { flash('error', 'Đơn hàng đã thanh toán đầy đủ'); redirect('/customer/orders'); return; }
    
    // Mark as fully paid
    dbRun("UPDATE orders SET payment_status='paid', remaining_amount=0, paid_amount=grand_total WHERE id=?", [$order['id']]);
    
    // Notify admin
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('payment', 'Thanh toán 30% còn lại', ?, ?)", [
        "Khách hàng đã thanh toán 30% còn lại cho đơn #" . $order['code'],
        "/admin/orders/" . $order['id']
    ]);
    
    flash('success', 'Cảm ơn bạn! Đã xác nhận thanh toán phần còn lại cho đơn #' . $order['code']);
    redirect('/customer/orders');
});

get('/customer/checkout', function() {
    $user = currentUser();
    if (!$user) {
        $items = [];
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $pid => $qty) {
                $p = dbGet("SELECT p.id as product_id, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.weight_g, pt.shop_name, pt.id AS partner_id, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p LEFT JOIN partners pt ON pt.id=p.partner_id WHERE p.id=?", [$pid]);
                if ($p) {
                    $p['quantity'] = $qty;
                    $items[] = $p;
                }
            }
        }
        if (empty($items)) { flash('error','Giỏ hàng trống'); redirect('/customer/cart'); return; }
        view('customer/checkout', ['title'=>'Thanh toán', 'items'=>$items]);
        return;
    } elseif (!in_array($user['role'], ['customer','admin','staff'])) {
        redirect('/auth/login'); exit;
    }
    $items = dbAll("SELECT ci.*, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.weight_g, pt.shop_name, pt.id AS partner_id,
        (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image
        FROM cart_items ci
        INNER JOIN products p ON p.id=ci.product_id
        INNER JOIN partners pt ON pt.id=p.partner_id
        WHERE ci.user_id=?", [$user['id']]);
    if (empty($items)) { flash('error','Giỏ hàng trống'); redirect('/customer/cart'); return; }
    view('customer/checkout', ['title'=>'Thanh toán', 'items'=>$items]);
});

// ── Checkout: Place order ────────────────────────────────────────────────────
post('/customer/checkout', function() {
    $user = currentUser();
    csrfCheck();
    
    $userId = 0;
    if (!$user) {
        $items = [];
        if (!empty($_SESSION['guest_cart']) && is_array($_SESSION['guest_cart'])) {
            foreach ($_SESSION['guest_cart'] as $pid => $qty) {
                $p = dbGet("SELECT p.id as product_id, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.partner_id, p.part_brand, p.weight_g FROM products p WHERE p.id=?", [$pid]);
                if ($p) {
                    $p['quantity'] = $qty;
                    $items[] = $p;
                }
            }
        }
        if (empty($items)) { flash('error','Giỏ hàng trống'); redirect('/customer/cart'); return; }
        
        $req = ['shipping_full_name','shipping_phone','shipping_province','shipping_district','shipping_ward','shipping_detail'];
        foreach($req as $r) {
            if (empty(trim($_POST[$r]??''))) { flash('error','Vui lòng điền đầy đủ địa chỉ giao hàng'); redirect('/customer/checkout'); return; }
        }
        
        $customerName = trim($_POST['shipping_full_name']);
        $customerPhone = trim($_POST['shipping_phone']);
        $guestEmail = 'guest_' . time() . rand(1000,9999) . '@guest.local';
        
        $userId = dbInsert("INSERT INTO users (full_name, phone, email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, 'customer', 'active', datetime('now', 'localtime'))", 
            [$customerName, $customerPhone, $guestEmail, password_hash(uniqid(), PASSWORD_DEFAULT)]
        );
        $user = dbGet("SELECT * FROM users WHERE id=?", [$userId]);
        $_SESSION['user_id'] = $userId;
    } else {
        if (!in_array($user['role'], ['customer','admin','staff'])) { redirect('/auth/login'); exit; }
        $userId = $user['id'];
        $items = dbAll("SELECT ci.*, p.name, CASE WHEN p.is_on_sale=1 AND p.sale_price>0 AND p.sale_price<p.price THEN p.sale_price ELSE p.price END AS price, p.stock, p.partner_id, p.part_brand, p.weight_g
            FROM cart_items ci INNER JOIN products p ON p.id=ci.product_id
            WHERE ci.user_id=?", [$user['id']]);
        if (empty($items)) { flash('error','Giỏ hàng trống'); redirect('/customer/cart'); return; }
    }



    // Validate quantity limit (max 10 per product)
    foreach ($items as $it) {
        if ($it['quantity'] > 10) {
            flash('error', 'Sản phẩm "'.mb_substr($it['name'],0,30).'" vượt quá giới hạn 10 số lượng. Vui lòng giảm số lượng.');
            redirect('/customer/cart');
            return;
        }
    }

    // Validate address
    $req = ['shipping_full_name','shipping_phone','shipping_province','shipping_district','shipping_ward','shipping_detail'];
    foreach($req as $r) {
        if (empty(trim($_POST[$r]??''))) { flash('error','Vui lòng điền đầy đủ địa chỉ giao hàng'); redirect('/customer/checkout'); return; }
    }

        // Phone: must start with 0, exactly 10 digits
    $phoneCheck = trim($_POST['shipping_phone'] ?? '');
    if (!preg_match('/^0[0-9]{9}$/', $phoneCheck)) {
        flash('error', 'Số điện thoại phải bắt đầu từ 0 và có đúng 10 chữ số.');
        redirect('/customer/checkout'); return;
    }

    // Address: no special characters
    $addrCheck = trim($_POST['shipping_detail'] ?? '');
    if (!empty($addrCheck) && preg_match('/[!@#\$%\^&\*\(\)+={}\|;:<>\?~`]/', $addrCheck)) {
        flash('error', 'Địa chỉ cụ thể không được chứa ký tự đặc biệt.');
        redirect('/customer/checkout'); return;
    }
    // Note: max 100 chars
    $noteCheck = trim($_POST['customer_note'] ?? '');
    if (mb_strlen($noteCheck) > 100) {
        flash('error', 'Ghi chú đơn hàng tối đa 100 ký tự.');
        redirect('/customer/checkout'); return;
    }

    $cfgRows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_tax_rate','default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent','shipping_origin_province','shipping_rates')");
    $cfg = []; foreach($cfgRows as $r) $cfg[$r['key']] = $r['value'];
    
    $taxRate = 0; // VAT đã bao gồm trong giá sản phẩm (hiển thị "Đã bao gồm 10% VAT"), không tính thêm
    $shipFee = intval($cfg['default_shipping_fee'] ?? 30000);
    $freeShipThreshold = intval($cfg['free_shipping_threshold'] ?? 2000000);
    $discountQtyThreshold = intval($cfg['discount_quantity_threshold'] ?? 0);
    $discountQtyPercent = floatval($cfg['discount_quantity_percent'] ?? 0);

    $subtotal = 0;
    $totalQty = 0;
    $totalWeight = 0;
    foreach($items as $it) {
        $subtotal += $it['price'] * $it['quantity'];
        $totalQty += $it['quantity'];
        $totalWeight += intval($it['weight_g'] ?? 0) * $it['quantity'];
    }

    $discountTotal = 0;
    if ($discountQtyThreshold > 0 && $totalQty >= $discountQtyThreshold) {
        $discountTotal += (int)ceil($subtotal * ($discountQtyPercent / 100));
    }
    
    $isFreeship = false;
    if (!empty($_SESSION['cart_voucher'])) {
        $v = $_SESSION['cart_voucher'];
        $vScope = $v['scope'] ?? 'platform';
        $vDiscount = 0;
        
        if ($vScope === 'freeship') {
            $isFreeship = true;
        } elseif ($vScope === 'shop') {
            $shopBrand = $v['scope_value'] ?? '';
            $shopSubtotal = 0;
            foreach ($items as $sit) {
                if (($sit['part_brand'] ?? '') === $shopBrand) {
                    $shopSubtotal += $sit['price'] * $sit['quantity'];
                }
            }
            if (($v['discount_type'] ?? '') === 'percent') {
                $vDiscount = (int)ceil($shopSubtotal * (($v['discount_amount'] ?? 0) / 100));
            } else {
                $vDiscount = intval($v['discount_amount'] ?? 0);
            }
            if (isset($v['max_discount']) && $v['max_discount'] > 0 && $vDiscount > $v['max_discount']) $vDiscount = $v['max_discount'];
            if ($vDiscount > $shopSubtotal) $vDiscount = $shopSubtotal;
        } else {
            if (($v['discount_type'] ?? '') === 'percent') {
                $vDiscount = (int)ceil($subtotal * (($v['discount_amount'] ?? 0) / 100));
            } else {
                $vDiscount = intval($v['discount_amount'] ?? 0);
            }
            if (isset($v['max_discount']) && $v['max_discount'] > 0 && $vDiscount > $v['max_discount']) $vDiscount = $v['max_discount'];
            if ($vDiscount > $subtotal) $vDiscount = $subtotal;
        }
        $discountTotal += $vDiscount;
        
        dbRun("UPDATE vouchers SET used_quantity = used_quantity + 1 WHERE id=?", [$v['id']]);
    }
    
    // Newsletter first order discount
    $isSub = dbGet("SELECT 1 FROM newsletter_subscribers WHERE email=?", [$user['email']]);
    $oCount = dbGet("SELECT COUNT(*) as c FROM orders WHERE user_id=?", [$user['id']])['c'];
    if ($isSub && $oCount == 0) {
        $newsletterDiscount = 100000;
        if ($newsletterDiscount + $discountTotal > $subtotal) {
            $newsletterDiscount = $subtotal - $discountTotal;
        }
        $discountTotal += $newsletterDiscount;
    }

    $afterDiscount = $subtotal - $discountTotal;
    $taxAmount = (int)ceil($afterDiscount * ($taxRate / 100));

    $shipFee = calcShippingFee(trim($_POST['shipping_province'] ?? ''), (int)$totalWeight, $cfg);
    $shipping = ($isFreeship || ($freeShipThreshold > 0 && $afterDiscount >= $freeShipThreshold)) ? 0 : $shipFee;
    $grand = $afterDiscount + $taxAmount + $shipping;
    $rawMethod = $_POST['payment_method'] ?? 'cod';
    $payMethod = in_array($rawMethod, ['cod','bank_transfer']) ? $rawMethod : 'cod';
    $dbPayMethod = ($payMethod === 'bank_transfer') ? 'bank_transfer' : 'cod';
    // Payment: bank_transfer = full prepayment, cod = pay on delivery
    $paidAmount = 0;
    $remainingAmount = 0;
    $paymentType = 'cod';
    if ($payMethod === 'bank_transfer') {
        $paidAmount = $grand;
        $remainingAmount = 0;
        $initPayStatus = 'paid';
        $paymentType = 'bank_transfer';
    } else {
        $initPayStatus = 'unpaid';
        $paymentType = 'cod';
    }
    $code = strtoupper(substr(base_convert(time(), 10, 36), -4) . substr(md5(uniqid()), 0, 6));

    // Insert order
    // Handle payment receipt upload
    $paymentReceipt = null;
    if (!empty($_FILES['payment_receipt']['name'])) {
        $ext = strtolower(pathinfo($_FILES['payment_receipt']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = 'receipt_' . time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir(UPLOAD_DIR . '/receipts')) {
                mkdir(UPLOAD_DIR . '/receipts', 0755, true);
            }
            if (move_uploaded_file($_FILES['payment_receipt']['tmp_name'], UPLOAD_DIR . '/receipts/' . $fname)) {
                $paymentReceipt = $fname;
            }
        }
    }

    $orderId = dbInsert("INSERT INTO orders (code, user_id, total_items, subtotal, discount_total, tax_amount, shipping_total, grand_total,
        payment_method, payment_status, delivery_status, paid_amount, remaining_amount, payment_type,
        shipping_full_name, shipping_phone, shipping_province, shipping_district, shipping_ward, shipping_detail, customer_note, payment_receipt, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,datetime('now', 'localtime'))", [
        $code, $user['id'], count($items), $subtotal, $discountTotal, $taxAmount, $shipping, $grand,
        $dbPayMethod, $initPayStatus, 'pending', $paidAmount, $remainingAmount, $paymentType,
        trim($_POST['shipping_full_name']), trim($_POST['shipping_phone']),
        trim($_POST['shipping_province']), trim($_POST['shipping_district']),
        trim($_POST['shipping_ward']), trim($_POST['shipping_detail']),
        trim($_POST['customer_note']??''), $paymentReceipt
    ]);
    // Create notification for admin
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('order', 'Đơn hàng mới', ?, ?)", [
        "Có đơn hàng mới #".$code." trị giá ".vnd($grand),
        "/admin/orders/".$orderId
    ]);

    // Group items by partner
    $byPartner = [];
    foreach($items as $it) { $byPartner[$it['partner_id']][] = $it; }

    foreach($byPartner as $partnerId => $pitems) {
        $pSub = 0; foreach($pitems as $pi) $pSub += $pi['price']*$pi['quantity'];
        $pCode = $code.'-'.strtoupper(substr(md5($partnerId), 0, 4));
        $soId = dbInsert("INSERT INTO sub_orders (order_id, partner_id, code, subtotal, grand_total, created_at) VALUES (?,?,?,?,?,datetime('now', 'localtime'))",
            [$orderId, $partnerId, $pCode, $pSub, $pSub]);
        foreach($pitems as $pi) {
            dbInsert("INSERT INTO order_items (sub_order_id, product_id, snapshot_name, unit_price, quantity, line_total)
                VALUES (?,?,?,?,?,?)", [$soId, $pi['product_id'], $pi['name'], $pi['price'], $pi['quantity'], $pi['price']*$pi['quantity']]);
            // Reduce stock
            dbRun("UPDATE products SET stock=MAX(0, stock-?) WHERE id=?", [$pi['quantity'], $pi['product_id']]);
            inventoryCheckLowStockAlert((int)$pi['product_id'], 'customer_order');
        }
    }

    // Clear cart
    dbRun("DELETE FROM cart_items WHERE user_id=?", [$user['id']]);
    unset($_SESSION['cart_voucher']);

    flash('success', "Đặt hàng thành công! Mã đơn: $code");
    redirect('/customer/orders');
});

post('/customer/change-password', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    csrfCheck();
    $curr = $_POST['current_password'] ?? '';
    $new  = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password2'] ?? '';
    $u    = dbGet('SELECT * FROM users WHERE id=?', [$user['id']]);
    if (!password_verify($curr, $u['password_hash'])) { flash('error','Mat khau hien tai khong dung.'); redirect('/customer/profile'); }
    if (strlen($new) < 6) { flash('error','Mat khau moi toi thieu 6 ky tu.'); redirect('/customer/profile'); }
    if ($new !== $new2) { flash('error','Mat khau nhap lai khong khop.'); redirect('/customer/profile'); }
    dbRun("UPDATE users SET password_hash=? WHERE id=?", [password_hash($new, PASSWORD_BCRYPT), $user['id']]);
    flash('success','Đổi mật khẩu thành công!');
    redirect('/customer/profile');
});

// ── Submit Review ────────────────────────────────────────────────────────────
post('/products/:id/reviews', function($p) {
    $user = requireRole(['customer','staff'], '/auth/login');
    csrfCheck();
    $pid = intval($p['id']);
    $rating = max(1, min(5, intval($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    $imageName = null;
    $uploadedImages = [];
    // Handle multiple images
    if (!empty($_FILES['images']['tmp_name'])) {
        $files = $_FILES['images'];
        $maxFiles = min(count($files['tmp_name']), 5);
        for ($i = 0; $i < $maxFiles; $i++) {
            if (!empty($files['tmp_name'][$i]) && is_uploaded_file($files['tmp_name'][$i])) {
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $fname = uniqid('rev_') . '.' . $ext;
                    if (move_uploaded_file($files['tmp_name'][$i], '/opt/cooling-php/uploads/reviews/' . $fname)) {
                        $uploadedImages[] = $fname;
                    }
                }
            }
        }
    }
    // Fallback: single image (backward compat)
    if (empty($uploadedImages) && !empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $fname = uniqid('rev_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '/opt/cooling-php/uploads/reviews/' . $fname)) {
                $uploadedImages[] = $fname;
            }
        }
    }
    $imageName = !empty($uploadedImages) ? implode(',', $uploadedImages) : null;

    // Check if user already reviewed this product
    $existingReview = dbGet("SELECT id FROM reviews WHERE product_id=? AND user_id=?", [$pid, $user['id']]);
    if ($existingReview) {
        // UPDATE existing review
        dbRun("UPDATE reviews SET rating_overall=?, comment=?, updated_at=datetime('now','localtime')" . ($imageName ? ", image=?" : "") . " WHERE id=?",
            $imageName ? [$rating, $comment, $imageName, $existingReview['id']] : [$rating, $comment, $existingReview['id']]);
    } else {
        dbRun("INSERT INTO reviews (product_id, sub_order_id, user_id, rating_overall, comment, status, created_at, image) VALUES (?, NULL, ?, ?, ?, 'published', datetime('now','localtime'), ?)", [$pid, $user['id'], $rating, $comment, $imageName]);
    }

    // Recalculate average rating from actual reviews in DB
    $stats = dbGet("SELECT COUNT(*) as cnt, ROUND(AVG(rating_overall), 1) as avg_rating FROM reviews WHERE product_id=? AND status IN ('published','approved')", [$pid]);
    // Custom rating display algorithm
    $rawAvg = floatval($stats['avg_rating'] ?? 0);
    $cnt = intval($stats['cnt'] ?? 0);
    $fiveStarCount = intval(dbGet("SELECT COUNT(*) as c FROM reviews WHERE product_id=? AND rating_overall=5 AND status IN ('published','approved')", [$pid])['c'] ?? 0);
    
    if ($rawAvg > 0) {
        if ($rawAvg <= 4.0) {
            // 4 sao trở xuống: trừ 0.1
            $displayAvg = max(1.0, round($rawAvg - 0.1, 1));
        } else {
            // Trên 4 sao: cứ 3 đánh giá 5 sao thì cộng 0.1
            $bonus = floor($fiveStarCount / 3) * 0.1;
            $displayAvg = min(5.0, round($rawAvg + $bonus, 1));
            // Nếu trung bình thực tế dưới 5 nhưng bonus đẩy lên, giới hạn tại 5.0
        }
        $stats['avg_rating'] = $displayAvg;
    }
    $newCount = intval($stats['cnt'] ?? 0);
    $newAvg = floatval($stats['avg_rating'] ?? 0);
    dbRun("UPDATE products SET rating_avg=?, rating_count=? WHERE id=?", [$newAvg, $newCount, $pid]);

    // Add admin notification (only for new reviews, not edits)
    if (!$existingReview) {
        $prod = dbGet("SELECT name FROM products WHERE id=?", [$pid]);
        $prodName = e($prod['name'] ?? '');
        dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('review', 'Đánh giá mới', ?, '/admin/reviews')", ["Khách hàng vừa đánh giá {$rating} sao cho sản phẩm '{$prodName}'."]);
    }

    flash('success', 'Đánh giá của bạn đã được gửi thành công!');
    // Redirect using slug if available
    $prodSlug = dbGet("SELECT slug FROM products WHERE id=?", [$pid]);
    if ($prodSlug && $prodSlug['slug']) {
        redirect('/products/' . $prodSlug['slug']);
    } else {
        redirect('/products/' . $pid);
    }
});

// Customer cancel order (only if pending)
post('/customer/orders/:id/cancel', function($p) {
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') { redirect('/auth/login'); exit; }
    csrfCheck();
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    if (!$order) { flash('error','Không tìm thấy đơn hàng.'); redirect('/customer/orders'); return; }
    if (!in_array($order['delivery_status'], ['pending'])) {
        flash('error','Chi co the huy don khi dang cho xac nhan.'); redirect('/customer/orders'); return;
    }
    $reason = trim($_POST['cancel_reason'] ?? '');
    if ($reason === '') { flash('error','Vui lòng nhập lý do hủy đơn.'); redirect('/customer/orders'); return; }
    // Restore stock
    $orderItems = dbAll("SELECT oi.product_id, oi.quantity FROM order_items oi INNER JOIN sub_orders so ON so.id=oi.sub_order_id WHERE so.order_id=?", [$p['id']]);
    foreach ($orderItems as $oi) {
        if ($oi['product_id']) {
            dbRun("UPDATE products SET stock = stock + ? WHERE id=?", [$oi['quantity'], $oi['product_id']]);
            inventoryCheckLowStockAlert((int)$oi['product_id'], 'customer_order_cancel');
        }
    }
    // Cancel images (optional, max 5)
    $cancelImgs = [];
    if (!empty($_FILES['cancel_images']['name'][0])) {
        $cdir = UPLOAD_DIR . '/cancellations/';
        if (!is_dir($cdir)) @mkdir($cdir, 0775, true);
        $cf = $_FILES['cancel_images']; $cn = count($cf['name']);
        for ($i = 0; $i < $cn && count($cancelImgs) < 5; $i++) {
            if (($cf['error'][$i] ?? 1) !== UPLOAD_ERR_OK) continue;
            $cext = strtolower(pathinfo($cf['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($cext, ['jpg','jpeg','png','gif','webp'])) continue;
            if (($cf['size'][$i] ?? 0) > 5 * 1024 * 1024) continue;
            $cfname = 'cancel_' . $p['id'] . '_' . time() . '_' . $i . '.' . $cext;
            if (move_uploaded_file($cf['tmp_name'][$i], $cdir . $cfname)) $cancelImgs[] = $cfname;
        }
    }
    dbRun("UPDATE orders SET delivery_status='cancelled', payment_status='unpaid', cancel_reason=?, cancel_images=? WHERE id=?", [$reason, json_encode($cancelImgs), $p['id']]);
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('order', 'Khách hàng hủy đơn hàng', ?, ?)", [
        "Đơn hàng #" . ($order['code'] ?? $p['id']) . " vừa bị khách hàng hủy. Lý do: " . ($reason ?: 'Không nêu lý do'),
        "/admin/orders/" . $p['id']
    ]);
    flash('success','Đã hủy đơn hàng thành công. Số lượng sản phẩm đã được khôi phục.');
    redirect('/customer/orders');
});

// Customer confirm received order
post('/customer/orders/:id/received', function($p) {
    $user = currentUser();
    if (!$user || $user['role'] !== 'customer') { redirect('/auth/login'); exit; }
    csrfCheck();
    $order = dbGet("SELECT * FROM orders WHERE id=? AND user_id=?", [$p['id'], $user['id']]);
    if (!$order) { flash('error','Không tìm thấy đơn hàng.'); redirect('/customer/orders'); return; }
    if ($order['delivery_status'] !== 'delivered') {
        flash('error','Đơn hàng chưa ở trạng thái đã giao.'); redirect('/customer/orders'); return;
    }
    dbRun("UPDATE orders SET delivery_status='completed', payment_status='paid', completed_at=datetime('now','localtime') WHERE id=?", [$p['id']]);
    // Notify admin
    dbRun("INSERT INTO admin_notifications (type, title, message, link) VALUES ('order','Khach hang xac nhan nhan hang','Don hang #' || ? || ' da duoc khach hang xac nhan.','/admin/orders/' || ?)", [$order['code'], $p['id']]);
    flash('success','Cảm ơn bạn đã xác nhận! Đơn hàng hoàn thành.');
    redirect('/customer/orders');
});


// Favorites toggle (AJAX)
post('/customer/favorites/toggle', function() {
    header('Content-Type: application/json');
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false,'redirect'=>'/auth/login']); exit; }
    $productId = intval($_POST['product_id'] ?? 0);
    if (!$productId) { echo json_encode(['ok'=>false]); exit; }
    $exists = dbGet("SELECT 1 FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $productId]);
    if ($exists) {
        dbRun("DELETE FROM favorites WHERE user_id=? AND product_id=?", [$user['id'], $productId]);
        echo json_encode(['ok'=>true,'fav'=>false,'count'=>favCount()]);
    } else {
        dbRun("INSERT OR IGNORE INTO favorites (user_id, product_id, created_at) VALUES (?,?,datetime('now','localtime'))", [$user['id'], $productId]);
        echo json_encode(['ok'=>true,'fav'=>true,'count'=>favCount()]);
    }
    exit;
});


// Review reactions (AJAX)
post('/products/:id/reactions', function($p) {
    header('Content-Type: application/json');
    $user = currentUser();
    if (!$user) { echo json_encode(['ok'=>false,'redirect'=>'/auth/login']); exit; }
    csrfCheck();
    $rid = intval($p['id']);
    $reaction = trim($_POST['reaction'] ?? 'like');
    $allowed = ['like','heart','laugh','wow','sad','angry'];
    if (!in_array($reaction, $allowed)) { echo json_encode(['ok'=>false]); exit; }
    $existing = dbGet("SELECT id,reaction FROM review_reactions WHERE review_id=? AND user_id=?", [$rid, $user['id']]);
    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            dbRun("DELETE FROM review_reactions WHERE id=?", [$existing['id']]);
            echo json_encode(['ok'=>true,'removed'=>true]);
        } else {
            dbRun("UPDATE review_reactions SET reaction=? WHERE id=?", [$reaction, $existing['id']]);
            echo json_encode(['ok'=>true,'removed'=>false,'reaction'=>$reaction]);
        }
    } else {
        dbRun("INSERT INTO review_reactions (review_id, user_id, reaction, created_at) VALUES (?,?,?,datetime('now','localtime'))", [$rid, $user['id'], $reaction]);
        echo json_encode(['ok'=>true,'removed'=>false,'reaction'=>$reaction]);
    }
    exit;
});


// ── Customer Invoice Info API ──
get('/customer/invoice-info', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    header('Content-Type: application/json');
    $info = dbGet("SELECT * FROM user_invoice_info WHERE user_id=?", [$user['id']]);
    echo json_encode($info ?: ['user_id'=>$user['id'],'invoice_type'=>'personal','buyer_name'=>'','tax_code'=>'','address'=>'','province'=>'','ward'=>'','id_number'=>'','passport'=>'','email'=>'','phone'=>'','bank_name'=>'','bank_account'=>'']);
    exit;
});

post('/customer/invoice-info', function() {
    $user = requireRole(['customer','staff'], '/auth/login'); csrfCheck();
    header('Content-Type: application/json');
    $fields = ['invoice_type','buyer_name','tax_code','address','province','ward','id_number','passport','email','phone','bank_name','bank_account','company_name','legal_representative'];
    $data = [];
    foreach($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    
    // Validate MST: phải là 10 hoặc 14 chữ số
    if ($data['tax_code'] && !preg_match('/^\d{10,13}$/', $data['tax_code'])) {
        echo json_encode(['ok'=>false, 'error'=>'Mã số thuế / CCCD không hợp lệ (MST 10-13 số hoặc CCCD 12 số)']);
        exit;
    }
    // Validate CCCD: phải 9 hoặc 12 chữ số
    if ($data['id_number'] && !preg_match('/^\d{9}(\d{3})?$/', $data['id_number'])) {
        echo json_encode(['ok'=>false, 'error'=>'Số CCCD/CMND không hợp lệ (phải 9 hoặc 12 số)']);
        exit;
    }
    // Validate email hóa đơn
    if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok'=>false, 'error'=>'Email hóa đơn không đúng định dạng']);
        exit;
    }
    // Validate SĐT hóa đơn: 10 số
    if ($data['phone'] && !preg_match('/^0[1-9]\d{8}$/', $data['phone'])) {
        echo json_encode(['ok'=>false, 'error'=>'Số điện thoại không hợp lệ (phải 10 số)']);
        exit;
    }
    // Validate trường bắt buộc khi loại hình = Tổ chức
    if ($data['invoice_type'] === 'business') {
        if (!$data['tax_code']) {
            echo json_encode(['ok'=>false, 'error'=>'Mã số thuế là bắt buộc cho Tổ chức/Hộ kinh doanh']);
            exit;
        }
        if (!$data['company_name']) {
            echo json_encode(['ok'=>false, 'error'=>'Tên công ty là bắt buộc cho Tổ chức/Hộ kinh doanh']);
            exit;
        }
    }
    $exists = dbGet("SELECT id FROM user_invoice_info WHERE user_id=?", [$user['id']]);
    if ($exists) {
        dbRun("UPDATE user_invoice_info SET invoice_type=?,buyer_name=?,tax_code=?,address=?,province=?,ward=?,id_number=?,passport=?,email=?,phone=?,bank_name=?,bank_account=?,company_name=?,legal_representative=?,updated_at=datetime('now','localtime') WHERE user_id=?",
            [$data['invoice_type'],$data['buyer_name'],$data['tax_code'],$data['address'],$data['province'],$data['ward'],$data['id_number'],$data['passport'],$data['email'],$data['phone'],$data['bank_name'],$data['bank_account'],$data['company_name'],$data['legal_representative'],$user['id']]);
    } else {
        dbRun("INSERT INTO user_invoice_info (user_id,invoice_type,buyer_name,tax_code,address,province,ward,id_number,passport,email,phone,bank_name,bank_account,company_name,legal_representative) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$user['id'],$data['invoice_type'],$data['buyer_name'],$data['tax_code'],$data['address'],$data['province'],$data['ward'],$data['id_number'],$data['passport'],$data['email'],$data['phone'],$data['bank_name'],$data['bank_account'],$data['company_name'],$data['legal_representative']]);
    }
    echo json_encode(['ok'=>true]);
    exit;
});

// ── Start chat with admin from product page ──
post('/customer/chat/start', function() {
    $user = currentUser();
    if (!$user) { redirect('/auth/login'); return; }
    csrfCheck();
    $pid = intval($_POST['product_id'] ?? 0);
    
    // Find or create thread with admin
    $thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
    if (!$thread) {
        dbRun("INSERT INTO chat_threads (customer_id, partner_id, created_at) VALUES (?, 1, datetime('now'))", [$user['id']]);
        $thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
    }
    
    // Auto-send message about the product
    if ($pid > 0) {
        $prod = dbGet("SELECT name, slug FROM products WHERE id=?", [$pid]);
        if ($prod) {
            $msg = "Xin chào, tôi muốn hỏi về sản phẩm: " . $prod['name'];
            dbRun("INSERT INTO chat_messages (thread_id, sender_user_id, sender_role, content, status, created_at) VALUES (?, ?, 'customer', ?, 'sent', datetime('now'))", 
                [$thread['id'], $user['id'], $msg]);
        }
    }
    
    redirect('/customer/chat?thread=' . $thread['id']);
});

// Customer chat page
get('/customer/chat', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    view('customer/chat', ['title' => 'Tin nhắn', 'user' => $user]);
});

// Send chat message
post('/customer/chat/send', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    csrfCheck();
    $msg = trim($_POST['message'] ?? '');
    $imagePath = '';
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dir = '/var/lib/coolingsystems/uploads/chat/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = uniqid('chat_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname);
        $imagePath = $fname;
    }
    if (!$msg && !$imagePath) { redirect('/customer/chat'); return; }
    $thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
    if (!$thread) {
        dbRun("INSERT INTO chat_threads (customer_id, partner_id, created_at) VALUES (?, 1, datetime('now'))", [$user['id']]);
        $thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
    }
    $content = $msg ?: '[Ảnh]';
    dbRun("INSERT INTO chat_messages (thread_id, sender_user_id, sender_role, content, image_path, attachment_path, status, created_at) VALUES (?, ?, 'customer', ?, ?, ?, 'sent', datetime('now','localtime'))", [$thread['id'], $user['id'], $content, $imagePath, $imagePath]);
    dbRun("UPDATE chat_threads SET last_message=?, last_message_at=datetime('now') WHERE id=?", [$content, $thread['id']]);

    // Insert or update Admin Notification for new chat message
    try {
      $cName = !empty($user['full_name']) ? $user['full_name'] : 'Khách hàng';
      $cLink = "/admin/chat?thread=" . $thread['id'];
      $cMsg = "Khách hàng {$cName} vừa gửi tin nhắn: " . mb_substr($content, 0, 50);
      $cTitle = "Tin nhắn mới từ " . $cName;
      $cExists = dbGet("SELECT id FROM admin_notifications WHERE type='chat' AND link=?", [$cLink]);
      if ($cExists) {
        dbRun("UPDATE admin_notifications SET message=?, is_read=0, created_at=datetime('now','localtime') WHERE id=?", [$cMsg, $cExists['id']]);
      } else {
        dbRun("INSERT INTO admin_notifications (type, title, message, link, is_read, created_at) VALUES ('chat', ?, ?, ?, 0, datetime('now','localtime'))", [$cTitle, $cMsg, $cLink]);
      }
    } catch(\Exception $e){}

    redirect('/customer/chat');
});

get('/customer/chat/poll', function() {
    $user = requireRole(['customer','staff'], '/auth/login');
    header('Content-Type: application/json');
    $thread = dbGet("SELECT * FROM chat_threads WHERE customer_id=?", [$user['id']]);
    if (!$thread) { echo json_encode(['messages'=>[]]); return; }
    $lastId = intval($_GET['after'] ?? 0);
    $msgs = dbAll("SELECT * FROM chat_messages WHERE thread_id=? AND id>? ORDER BY created_at ASC", [$thread['id'], $lastId]);
    echo json_encode(['messages' => $msgs]);
});
