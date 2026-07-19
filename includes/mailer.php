<?php
// Mailer with optional database-configured SMTP transport.
define('MAIL_FROM', 'mrdungfn2k@gmail.com');
define('MAIL_FROM_NAME', 'Cooling System');

function smtpConfig(): array {
    $config = ['enabled'=>false,'host'=>'','port'=>587,'encryption'=>'tls','username'=>'','password'=>'','from_email'=>MAIL_FROM,'from_name'=>MAIL_FROM_NAME];
    if (!function_exists('dbGet')) return $config;
    foreach (['smtp_enabled','smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_password','smtp_from_email','smtp_from_name'] as $key) {
        $row = dbGet('SELECT value FROM settings WHERE key=?', [$key]);
        $value = (string)($row['value'] ?? '');
        if ($key === 'smtp_enabled') $config['enabled'] = $value === '1';
        elseif ($key === 'smtp_port') $config['port'] = max(1, min(65535, (int)($value ?: 587)));
        else $config[str_replace('smtp_', '', $key)] = $value;
    }
    $config['from_email'] = $config['from_email'] ?: MAIL_FROM;
    $config['from_name'] = $config['from_name'] ?: MAIL_FROM_NAME;
    return $config;
}

function smtpSetLastError(string $message): void { $GLOBALS['smtp_last_error'] = $message; }
function smtpLastError(): string { return (string)($GLOBALS['smtp_last_error'] ?? ''); }

function smtpReadResponse($socket): array {
    $response = '';
    while (($line = fgets($socket, 1024)) !== false) {
        $response .= $line;
        if (preg_match('/^(\d{3})\s/', $line, $match)) return [(int)$match[1], trim($response)];
    }
    throw new RuntimeException('SMTP server did not return a complete response.');
}

function smtpCommand($socket, string $command, array $expected): void {
    if (fwrite($socket, $command . "\r\n") === false) throw new RuntimeException('Could not write to SMTP server.');
    [$code, $response] = smtpReadResponse($socket);
    if (!in_array($code, $expected, true)) throw new RuntimeException('SMTP ' . $code . ': ' . $response);
}

function sendSmtpEmail(string $to, string $subject, string $htmlBody, array $cfg): bool {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { smtpSetLastError('Email người nhận không hợp lệ.'); return false; }
    if (!preg_match('/^[a-z0-9.-]+$/i', $cfg['host']) || !filter_var($cfg['from_email'], FILTER_VALIDATE_EMAIL)) { smtpSetLastError('Máy chủ hoặc email gửi SMTP không hợp lệ.'); return false; }
    if ($cfg['username'] === '' || $cfg['password'] === '') { smtpSetLastError('Thiếu tài khoản hoặc mật khẩu ứng dụng SMTP.'); return false; }
    $socket = null;
    try {
        $scheme = $cfg['encryption'] === 'ssl' ? 'ssl://' : 'tcp://';
        $socket = stream_socket_client($scheme . $cfg['host'] . ':' . $cfg['port'], $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!$socket) throw new RuntimeException('Không kết nối được SMTP: ' . $errstr);
        stream_set_timeout($socket, 15);
        [$code, $response] = smtpReadResponse($socket);
        if ($code !== 220) throw new RuntimeException('SMTP ' . $code . ': ' . $response);
        smtpCommand($socket, 'EHLO coolingsystems.vn', [250]);
        if ($cfg['encryption'] === 'tls') {
            smtpCommand($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('Không thể bật mã hóa TLS cho SMTP.');
            smtpCommand($socket, 'EHLO coolingsystems.vn', [250]);
        }
        smtpCommand($socket, 'AUTH LOGIN', [334]);
        smtpCommand($socket, base64_encode($cfg['username']), [334]);
        smtpCommand($socket, base64_encode($cfg['password']), [235]);
        smtpCommand($socket, 'MAIL FROM:<' . $cfg['from_email'] . '>', [250]);
        smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250,251]);
        smtpCommand($socket, 'DATA', [354]);
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($cfg['from_name']) . '?=';
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: {$encodedFromName} <{$cfg['from_email']}>\r\nReply-To: {$cfg['from_email']}\r\nTo: <{$to}>\r\nSubject: {$encodedSubject}\r\nX-Mailer: CoolingSystem/SMTP\r\n";
        $payload = preg_replace('/(?m)^\./', '..', $headers . "\r\n" . $htmlBody);
        if (fwrite($socket, $payload . "\r\n.\r\n") === false) throw new RuntimeException('Không gửi được nội dung email tới SMTP.');
        [$code, $response] = smtpReadResponse($socket);
        if ($code !== 250) throw new RuntimeException('SMTP ' . $code . ': ' . $response);
        smtpCommand($socket, 'QUIT', [221]);
        smtpSetLastError('');
        return true;
    } catch (Throwable $e) {
        smtpSetLastError($e->getMessage());
        error_log('[SMTP] ' . $e->getMessage());
        return false;
    } finally {
        if (is_resource($socket)) fclose($socket);
    }
}

function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $smtp = smtpConfig();
    if ($smtp['enabled']) return sendSmtpEmail($to, $subject, $htmlBody, $smtp);
    $from = MAIL_FROM;
    $fromName = MAIL_FROM_NAME;
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$from}>\r\n";
    $headers .= "Reply-To: {$from}\r\n";
    $headers .= "X-Mailer: CoolingSystem/1.0\r\n";
    $result = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, $headers, "-f{$from}");
    if (!$result) smtpSetLastError('SMTP chưa được bật hoặc máy chủ gửi mail mặc định không phản hồi.');
    return (bool)$result;
}

function sendVerificationEmail(string $email, string $name, string $token): bool {
    $link = 'https://coolingsystems.vn/auth/verify-email?token=' . urlencode($token);
    return sendEmail($email, 'Xác nhận tài khoản — Cooling System', _emailLayout('Xác nhận tài khoản', "<h2 style='color:#1a3258;margin:0 0 12px'>Chào mừng bạn đến với Cooling!</h2><p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p><p>Cảm ơn bạn đã đăng ký tài khoản. Bấm nút bên dưới để xác nhận email:</p><p style='text-align:center;margin:28px 0'><a href='{$link}' style='background:#c9972c;color:#fff;padding:14px 36px;text-decoration:none;border-radius:8px;font-weight:700;display:inline-block'>XÁC NHẬN TÀI KHOẢN</a></p>"));
}

function sendOTPEmail(string $email, string $name, string $otp): bool {
    return sendEmail($email, 'Mã OTP đặt lại mật khẩu — Cooling System', _emailLayout('Đặt lại mật khẩu', "<h2 style='color:#1a3258;margin:0 0 12px'>Đặt lại mật khẩu</h2><p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p><p>Mã OTP của bạn là:</p><p style='text-align:center;font-size:34px;font-weight:800;color:#1a3258;letter-spacing:8px'>{$otp}</p><p>Mã có hiệu lực trong 15 phút.</p>"));
}

function _emailLayout(string $title, string $body): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,sans-serif"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:40px 0"><tr><td align="center"><table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%"><tr><td style="background:#1a3258;padding:24px 32px;border-radius:10px 10px 0 0;text-align:center"><div style="color:#f0c040;font-size:28px;font-weight:900;letter-spacing:2px">COOLING</div><div style="color:rgba(255,255,255,0.7);font-size:12px;margin-top:2px">PARTS &amp; SERVICE</div></td></tr><tr><td style="background:#ffffff;padding:32px 36px;border-radius:0 0 10px 10px">' . $body . '</td></tr><tr><td style="padding:16px;text-align:center;color:#aaa;font-size:11px">© ' . date('Y') . ' Cooling System · <a href="https://coolingsystems.vn" style="color:#1a3258">coolingsystems.vn</a></td></tr></table></td></tr></table></body></html>';
}
