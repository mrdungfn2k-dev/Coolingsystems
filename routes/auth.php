<?php
// ── Login ──
get('/auth/login', function() { view('auth/login', ['title'=>'Đăng nhập']); });

post('/auth/login', function() {
    csrfCheck();
    $input = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$input || !$password) { flash('error','Vui lòng nhập email/SĐT và mật khẩu.'); redirect('/auth/login'); }
    // Support login by phone or email
    if (preg_match('/^0[0-9]{9}$/', $input)) {
        $user = dbGet('SELECT * FROM users WHERE phone=?', [$input]);
    } else {
        $user = dbGet('SELECT * FROM users WHERE email=?', [strtolower($input)]);
    }
    if (!$user || !password_verify($password, $user['password_hash'])) { flash('error','Email/SĐT hoặc mật khẩu không đúng.'); redirect('/auth/login'); }
    if ($user['status'] !== 'active') { flash('error','Tài khoản bị khóa.'); redirect('/auth/login'); }
    // Admin goes to admin dashboard
    if ($user['role'] === 'admin') {
        loginUser($user['id']);
        redirect('/admin');
    }
    // Staff must use the dedicated staff login page (not the customer login)
    if ($user['role'] === 'staff' && staffHasAssignment($user['id'])) {
        flash('error', 'Tài khoản nhân viên vui lòng đăng nhập tại trang dành cho nhân viên.');
        redirect('/staff/login');
    }
    // Customer login
    loginUser($user['id']);
    flash('success', 'Xin chào, '.$user['full_name'].'!');
    redirect($_POST['next'] ?? '/');
});

// ── Register with email verification ──
get('/auth/register', function() { view('auth/register', ['title'=>'Đăng ký tài khoản']); });

post('/auth/register', function() {
    csrfCheck();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $errs = [];
    // Name: no digits, no special chars, min 2 chars
    $name = preg_replace('/[^\p{L}\s]/u', '', $name);
    if (!$name || mb_strlen($name) < 2) $errs[] = 'Họ tên không hợp lệ (tối thiểu 2 ký tự, không chứa số/ký tự đặc biệt).';
    // Email: strict format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'Email không hợp lệ.';
    if ($email && !str_ends_with($email, '@gmail.com')) $errs[] = 'Chỉ chấp nhận email @gmail.com.';
    if ($email && !preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) $errs[] = 'Email sai định dạng (VD: ten@gmail.com).';
    // Phone: exactly 10 digits, starts with 01-09
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (!$phone || !preg_match('/^0[1-9][0-9]{8}$/', $phone)) $errs[] = 'SĐT phải bắt đầu từ 01-09 và đủ 10 số.';
    if ($phone && preg_match('/^(\d)\1{9}$/', $phone)) $errs[] = 'SĐT không hợp lệ (không được toàn số giống nhau).';
    // Address: max 100 chars, no special chars
    $address = preg_replace('/[^\p{L}\d\s,.\/\-]/u', '', $address);
    if (mb_strlen($address) > 100) $address = mb_substr($address, 0, 100);
    // Password: min 8, uppercase, lowercase, digit, special
    if (strlen($pass) < 8) $errs[] = 'Mật khẩu tối thiểu 8 ký tự.';
    if (!preg_match('/[A-Z]/', $pass)) $errs[] = 'Mật khẩu phải có ít nhất 1 chữ hoa (A-Z).';
    if (!preg_match('/[a-z]/', $pass)) $errs[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z).';
    if (!preg_match('/[0-9]/', $pass)) $errs[] = 'Mật khẩu phải có ít nhất 1 chữ số.';
    if (!preg_match('/[^a-zA-Z0-9]/', $pass)) $errs[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%...).';
    if ($pass !== $pass2) $errs[] = 'Mật khẩu nhập lại không khớp.';
    if (dbGet('SELECT 1 FROM users WHERE email=?', [$email])) $errs[] = 'Email đã được sử dụng.';
    if ($errs) { foreach($errs as $e) flash('error',$e); redirect('/auth/register'); }
    
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(32));
    dbInsert("INSERT INTO users (role,email,phone,password_hash,full_name,address,status,email_verified,verify_token) VALUES ('customer',?,?,?,?,?,'active',0,?)", 
        [$email,$phone,$hash,$name,$address,$token]);
    
    // Send verification email
    require_once __DIR__ . '/../includes/mailer.php';
    sendVerificationEmail($email, $name, $token);
    
    flash('success','Đăng ký thành công! Vui lòng kiểm tra email để xác nhận tài khoản.');
    redirect('/auth/login');
});

// ── Verify email ──
get('/auth/verify-email', function() {
    $token = $_GET['token'] ?? '';
    if (!$token) { flash('error','Link xác nhận không hợp lệ.'); redirect('/auth/login'); }
    $user = dbGet("SELECT * FROM users WHERE verify_token=?", [$token]);
    if (!$user) { flash('error','Link xác nhận không hợp lệ hoặc đã hết hạn.'); redirect('/auth/login'); }
    dbRun("UPDATE users SET email_verified=1, verify_token=NULL WHERE id=?", [$user['id']]);
    flash('success','Xác nhận email thành công! Bạn có thể đăng nhập ngay.');
    redirect('/auth/login');
});

// ── Forgot password (OTP) ──
get('/auth/forgot', function() { view('auth/forgot', ['title'=>'Quên mật khẩu']); });

post('/auth/forgot', function() {
    csrfCheck();
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email) { flash('error','Vui lòng nhập email.'); redirect('/auth/forgot'); }
    $user = dbGet("SELECT * FROM users WHERE email=?", [$email]);
    if (!$user) { flash('error','Email không tồn tại trong hệ thống.'); redirect('/auth/forgot'); }
    
    // Generate 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    dbRun("UPDATE users SET otp_code=?, otp_expires=? WHERE id=?", [$otp, $expires, $user['id']]);
    
    // Send OTP email
    require_once __DIR__ . '/../includes/mailer.php';
    sendOTPEmail($email, $user['full_name'], $otp);
    
    $_SESSION['reset_email'] = $email;
    flash('success','Mã OTP đã được gửi đến email của bạn.');
    redirect('/auth/reset');
});

// Also support /auth/forgot-password -> redirect to /auth/forgot
get('/auth/forgot-password', function() { redirect('/auth/forgot'); });

// ── Reset password (enter OTP + new password) ──
get('/auth/reset', function() {
    if (empty($_SESSION['reset_email'])) { redirect('/auth/forgot'); }
    view('auth/reset', ['title'=>'Đặt lại mật khẩu', 'reset_email'=>$_SESSION['reset_email']]);
});

post('/auth/reset', function() {
    csrfCheck();
    $email = $_SESSION['reset_email'] ?? '';
    if (!$email) { flash('error','Phiên hết hạn. Vui lòng thử lại.'); redirect('/auth/forgot'); }
    
    $otp = trim($_POST['otp'] ?? '');
    $newPass = $_POST['password'] ?? '';
    $newPass2 = $_POST['password2'] ?? '';
    
    if (strlen($otp) !== 6) { flash('error','Mã OTP phải có 6 chữ số.'); redirect('/auth/reset'); }
    
    $user = dbGet("SELECT * FROM users WHERE email=? AND otp_code=?", [$email, $otp]);
    if (!$user) { flash('error','Mã OTP không đúng.'); redirect('/auth/reset'); }
    if ($user['otp_expires'] < date('Y-m-d H:i:s')) { flash('error','Mã OTP đã hết hạn. Vui lòng gửi lại.'); redirect('/auth/forgot'); }
    
    if (strlen($newPass) < 8) { flash('error','Mật khẩu mới tối thiểu 8 ký tự.'); redirect('/auth/reset'); }
    if (!preg_match('/[A-Z]/', $newPass)) { flash('error','Mật khẩu phải có ít nhất 1 chữ hoa.'); redirect('/auth/reset'); }
    if (!preg_match('/[a-z]/', $newPass)) { flash('error','Mật khẩu phải có ít nhất 1 chữ thường.'); redirect('/auth/reset'); }
    if (!preg_match('/[0-9]/', $newPass)) { flash('error','Mật khẩu phải có ít nhất 1 chữ số.'); redirect('/auth/reset'); }
    // special char removed for reset
    if ($newPass !== $newPass2) { flash('error','Mật khẩu xác nhận không khớp.'); redirect('/auth/reset'); }
    
    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    dbRun("UPDATE users SET password_hash=?, otp_code=NULL, otp_expires=NULL WHERE id=?", [$hash, $user['id']]);
    unset($_SESSION['reset_email']);
    
    flash('success','Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
    redirect(($user['role'] ?? '')==='admin' ? '/admin/login' : ((($user['role'] ?? '')==='staff') ? '/staff/login' : '/auth/login'));
});

// ── Resend OTP ──
post('/auth/resend-otp', function() {
    csrfCheck();
    $email = $_SESSION['reset_email'] ?? '';
    if (!$email) { flash('error','Phiên hết hạn.'); redirect('/auth/forgot'); }
    $user = dbGet("SELECT * FROM users WHERE email=?", [$email]);
    if (!$user) { redirect('/auth/forgot'); }
    
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    dbRun("UPDATE users SET otp_code=?, otp_expires=? WHERE id=?", [$otp, $expires, $user['id']]);
    
    require_once __DIR__ . '/../includes/mailer.php';
    sendOTPEmail($email, $user['full_name'], $otp);
    
    flash('success','Đã gửi lại mã OTP mới.');
    redirect('/auth/reset');
});

// ── Logout ──
get('/auth/logout', function() { logout('/'); });
