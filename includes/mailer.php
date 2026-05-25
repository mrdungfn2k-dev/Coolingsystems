<?php
// Mailer — gửi qua Gmail SMTP (msmtp)
define('MAIL_FROM', 'mrdungfn2k@gmail.com');
define('MAIL_FROM_NAME', 'Cooling System');

function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $from     = MAIL_FROM;
    $fromName = MAIL_FROM_NAME;
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$from}>\r\n";
    $headers .= "Reply-To: {$from}\r\n";
    $headers .= "X-Mailer: CoolingSystem/1.0\r\n";
    $encSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $result = mail($to, $encSubject, $htmlBody, $headers, "-f{$from}");
    if (!$result) {
        error_log("[Mailer] Failed to send to: {$to} | Subject: {$subject}");
    }
    return (bool)$result;
}

function sendVerificationEmail(string $email, string $name, string $token): bool {
    $link = 'https://coolingsystem.vn/auth/verify-email?token=' . urlencode($token);
    $html = _emailLayout('Xác nhận tài khoản', "
        <h2 style='color:#1a3258;margin:0 0 12px'>Chào mừng bạn đến với Cooling!</h2>
        <p style='margin:0 0 8px'>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
        <p style='margin:0 0 20px;color:#555'>Cảm ơn bạn đã đăng ký tài khoản. Bấm nút bên dưới để xác nhận email và bắt đầu mua sắm:</p>
        <div style='text-align:center;margin:28px 0'>
            <a href='{$link}' style='background:#c9972c;color:#fff;padding:14px 36px;text-decoration:none;border-radius:8px;font-size:16px;font-weight:700;display:inline-block'>✅ XÁC NHẬN TÀI KHOẢN</a>
        </div>
        <p style='color:#999;font-size:12px;margin-top:20px'>Liên kết có hiệu lực trong 24 giờ. Nếu bạn không đăng ký, hãy bỏ qua email này.</p>
    ");
    return sendEmail($email, 'Xác nhận tài khoản — Cooling System', $html);
}

function sendOTPEmail(string $email, string $name, string $otp): bool {
    $html = _emailLayout('Đặt lại mật khẩu', "
        <h2 style='color:#1a3258;margin:0 0 12px'>Đặt lại mật khẩu</h2>
        <p style='margin:0 0 8px'>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
        <p style='margin:0 0 16px;color:#555'>Bạn vừa yêu cầu đặt lại mật khẩu. Mã OTP của bạn là:</p>
        <div style='text-align:center;margin:24px 0'>
            <div style='display:inline-block;background:#f0f4ff;border:2px dashed #1a3258;padding:18px 40px;border-radius:10px'>
                <span style='font-size:40px;font-weight:900;color:#1a3258;letter-spacing:10px;font-family:monospace'>{$otp}</span>
            </div>
        </div>
        <p style='color:#555;text-align:center'>Mã có hiệu lực trong <strong>15 phút</strong>.</p>
        <p style='color:#e74c3c;font-size:13px;text-align:center;margin-top:8px'>⚠️ Không chia sẻ mã này với bất kỳ ai.</p>
        <p style='color:#999;font-size:12px;margin-top:16px'>Nếu bạn không yêu cầu, hãy bỏ qua email này. Mật khẩu của bạn sẽ không thay đổi.</p>
    ");
    return sendEmail($email, 'Mã OTP đặt lại mật khẩu — Cooling System', $html);
}

function _emailLayout(string $title, string $body): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,sans-serif">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:40px 0">
    <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">
      <tr><td style="background:#1a3258;padding:24px 32px;border-radius:10px 10px 0 0;text-align:center">
        <div style="color:#f0c040;font-size:28px;font-weight:900;letter-spacing:2px">COOLING</div>
        <div style="color:rgba(255,255,255,0.7);font-size:12px;margin-top:2px">PARTS &amp; SERVICE</div>
      </td></tr>
      <tr><td style="background:#ffffff;padding:32px 36px;border-radius:0 0 10px 10px">' . $body . '</td></tr>
      <tr><td style="padding:16px;text-align:center;color:#aaa;font-size:11px">
        © ' . date('Y') . ' Cooling System · <a href="https://coolingsystem.vn" style="color:#1a3258">coolingsystem.vn</a>
      </td></tr>
    </table>
    </td></tr></table>
    </body></html>';
}
