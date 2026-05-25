<?php
get('/partner/login', function() { view('partner/login', ['title'=>'Đăng nhập đối tác']); });

post('/partner/login', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? '')); $password = $_POST['password'] ?? '';
    if (!$email || !$password) { flash('error','Vui lòng nhập email và mật khẩu.'); redirect('/partner/login'); }
    $user = dbGet('SELECT * FROM users WHERE email=?', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email hoặc mật khẩu không đúng.'); redirect('/partner/login'); }
    if ($user['role'] !== 'partner') { flash('error','Tài khoản không phải đối tác.'); redirect('/partner/login'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản bị khóa.'); redirect('/partner/login'); }
    loginUser($user['id']);
    flash('success','Xin chào, '.$user['full_name'].'!');
    redirect('/partner/dashboard');
});

get('/partner/logout', function() { logout('/partner/login'); });

get('/partner/register', function() { view('partner/register', ['title'=>'Đăng ký đối tác bán hàng']); });

get('/partner/dashboard', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    if (!$partner) { flash('error','Chưa có gian hàng.'); redirect('/'); }
    $wallet = dbGet('SELECT * FROM wallets WHERE partner_id=?', [$partner['id']]) ?: ['balance_available'=>0];
    $stats = dbGet("SELECT (SELECT COUNT(*) FROM products WHERE partner_id=? AND status='published') AS active_products, (SELECT COUNT(*) FROM products WHERE partner_id=? AND status='pending') AS pending_products, (SELECT COUNT(*) FROM sub_orders WHERE partner_id=? AND status IN ('awaiting_confirm','processing','shipping')) AS active_orders, (SELECT COALESCE(SUM(grand_total),0) FROM sub_orders WHERE partner_id=? AND status='completed') AS total_revenue, (SELECT COUNT(*) FROM sub_orders WHERE partner_id=? AND status='completed') AS completed_orders", [$partner['id'],$partner['id'],$partner['id'],$partner['id'],$partner['id']]);
    $recentOrders = dbAll("SELECT so.*, o.code, u.full_name AS customer FROM sub_orders so INNER JOIN orders o ON o.id=so.order_id INNER JOIN users u ON u.id=o.user_id WHERE so.partner_id=? ORDER BY so.created_at DESC LIMIT 5", [$partner['id']]);
    $recentReviews = dbAll("SELECT r.*, u.full_name, p.name AS product_name FROM reviews r INNER JOIN users u ON u.id=r.user_id INNER JOIN products p ON p.id=r.product_id WHERE p.partner_id=? ORDER BY r.created_at DESC LIMIT 5", [$partner['id']]);
    view('partner/dashboard', ['title'=>'Dashboard','role'=>'partner','partner'=>$partner,'partnerWallet'=>$wallet,'stats'=>$stats,'recentOrders'=>$recentOrders,'recentReviews'=>$recentReviews]);
});

get('/partner/products', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    $products = dbAll("SELECT p.*, (SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC LIMIT 1) AS main_image FROM products p WHERE p.partner_id=? ORDER BY p.created_at DESC", [$partner['id']]);
    view('partner/products', ['title'=>'Sản phẩm','role'=>'partner','products'=>$products,'partner'=>$partner]);
});

get('/partner/orders', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    $orders = dbAll("SELECT so.*, o.code AS order_code, u.full_name AS customer FROM sub_orders so INNER JOIN orders o ON o.id=so.order_id INNER JOIN users u ON u.id=o.user_id WHERE so.partner_id=? ORDER BY so.created_at DESC", [$partner['id']]);
    view('partner/orders', ['title'=>'Đơn hàng','role'=>'partner','orders'=>$orders,'partner'=>$partner]);
});

get('/partner/wallet', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    $wallet = dbGet('SELECT * FROM wallets WHERE partner_id=?', [$partner['id']]) ?: ['balance_available'=>0,'balance_pending'=>0,'total_earned'=>0,'total_withdrawn'=>0];
    $txns = dbAll('SELECT * FROM wallet_transactions WHERE partner_id=? ORDER BY created_at DESC LIMIT 20', [$partner['id']]);
    view('partner/wallet', ['title'=>'Ví của tôi','role'=>'partner','wallet'=>$wallet,'txns'=>$txns,'partner'=>$partner]);
});

get('/partner/reviews', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    $reviews = dbAll("SELECT r.*, u.full_name, p.name AS product_name FROM reviews r INNER JOIN users u ON u.id=r.user_id INNER JOIN products p ON p.id=r.product_id WHERE p.partner_id=? ORDER BY r.created_at DESC", [$partner['id']]);
    view('partner/reviews', ['title'=>'Đánh giá','role'=>'partner','reviews'=>$reviews,'partner'=>$partner]);
});

get('/partner/vouchers', function() {
    $user = requireRole('partner', '/partner/login');
    $partner = dbGet('SELECT * FROM partners WHERE user_id=?', [$user['id']]);
    $vouchers = dbAll("SELECT * FROM vouchers WHERE partner_id=? ORDER BY created_at DESC", [$partner['id']]);
    view('partner/vouchers', ['title'=>'Voucher Shop','role'=>'partner','vouchers'=>$vouchers,'partner'=>$partner]);
});
