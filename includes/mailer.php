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

function sendQuotationEmail(array $quotation, array $customer, array $items): bool {
    $email = trim((string)($customer['email'] ?? ''));
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $name = htmlspecialchars($customer['full_name'] ?? 'Khách hàng');
    $code = htmlspecialchars($quotation['code'] ?? '');
    $totalFormatted = number_format((int)($quotation['grand_total'] ?? 0), 0, ',', '.') . ' đ';
    $expiresAt = !empty($quotation['expires_at']) ? date('d/m/Y', strtotime($quotation['expires_at'])) : '—';
    $note = !empty($quotation['note']) ? htmlspecialchars($quotation['note']) : '';

    $itemRows = '';
    foreach ($items as $it) {
        $pName = htmlspecialchars($it['product_name'] ?? '');
        $qty = (int)($it['quantity'] ?? 1);
        $price = number_format((int)($it['price'] ?? 0), 0, ',', '.') . ' đ';
        $subtotal = number_format((int)($it['total'] ?? 0), 0, ',', '.') . ' đ';
        $itemRows .= "<tr>
            <td style='padding:10px;border-bottom:1px solid #edf2f7;'>{$pName}</td>
            <td style='padding:10px;border-bottom:1px solid #edf2f7;text-align:center;'>{$qty}</td>
            <td style='padding:10px;border-bottom:1px solid #edf2f7;text-align:right;'>{$price}</td>
            <td style='padding:10px;border-bottom:1px solid #edf2f7;text-align:right;font-weight:bold;'>{$subtotal}</td>
        </tr>";
    }

    $noteBlock = $note !== '' ? "<div style='margin-top:6px;font-size:13px;color:#475569;'>Ghi chú: {$note}</div>" : '';

    $body = "<h2 style='color:#1a3258;margin:0 0 12px'>Báo giá mới từ Cooling System</h2>
    <p>Xin chào <strong>{$name}</strong>,</p>
    <p>Chúng tôi xin gửi đến bạn bảng báo giá chi tiết <strong>#{$code}</strong>:</p>
    
    <table width='100%' cellpadding='0' cellspacing='0' style='margin:20px 0;border-collapse:collapse;font-size:14px;'>
        <thead>
            <tr style='background:#f8fafc;color:#64748b;'>
                <th style='padding:10px;text-align:left;border-bottom:2px solid #e2e8f0;'>Sản phẩm</th>
                <th style='padding:10px;text-align:center;border-bottom:2px solid #e2e8f0;'>Số lượng</th>
                <th style='padding:10px;text-align:right;border-bottom:2px solid #e2e8f0;'>Đơn giá</th>
                <th style='padding:10px;text-align:right;border-bottom:2px solid #e2e8f0;'>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            {$itemRows}
        </tbody>
    </table>
    
    <div style='background:#f1f5f9;padding:16px;border-radius:8px;margin-bottom:20px;'>
        <div style='display:flex;justify-content:space-between;font-size:15px;font-weight:bold;color:#1a3258;'>
            <span>TỔNG GIÁ TRỊ BÁO GIÁ:</span>
            <span style='color:#1e3a8a;'>{$totalFormatted}</span>
        </div>
        <div style='margin-top:8px;font-size:13px;color:#64748b;'>Hạn hiệu lực báo giá: <strong>{$expiresAt}</strong></div>
        {$noteBlock}
    </div>
    
    <p style='font-size:14px;color:#475569;'>Nếu bạn đồng ý với báo giá này hoặc có câu hỏi, vui lòng liên hệ hotline <strong>0947.795.471</strong> hoặc phản hồi email này.</p>";

    return sendEmail($email, "Báo giá #{$code} — Cooling System", _emailLayout("Báo giá #{$code}", $body));
}


function sendGarageApprovedEmail(string $email, string $name, string $garageName): bool {
    $subject = "Chúc mừng! Đơn đăng ký Gara của bạn đã được duyệt — Cooling System";
    $body = "
    <div style='text-align:center;margin-bottom:24px;'>
        <div style='display:inline-block;background:#dcfce7;color:#15803d;padding:10px 20px;border-radius:50px;font-weight:800;font-size:14px;'>
            ✓ XÁC THỰC GARA THÀNH CÔNG
        </div>
    </div>
    <h2 style='color:#1a3258;margin:0 0 12px;text-align:center;'>Đơn đăng ký Gara đã được chấp thuận!</h2>
    <p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
    <p>Cooling System xin thông báo: Đơn đăng ký Gara <strong>" . htmlspecialchars($garageName) . "</strong> của bạn đã được Ban quản trị thẩm định và phê duyệt thành công.</p>
    
    <div style='background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:18px;margin:20px 0;'>
        <h4 style='color:#1a3258;margin:0 0 10px;font-size:15px;'>🎁 QUYỀN LỢI & ĐẶC QUYỀN GARA CỦA BẠN:</h4>
        <ul style='margin:0;padding-left:20px;color:#334155;font-size:14px;line-height:1.7;'>
            <li><strong>Áp dụng Bảng giá buôn Gara gốc</strong> (Chiết khấu ưu đãi trực tiếp trên từng sản phẩm).</li>
            <li><strong>Được áp dụng Chính sách công nợ gối đầu</strong> (Gối đầu đơn hàng sau hoặc thanh toán định kỳ cuối mỗi tháng).</li>
            <li>Đội ngũ hỗ trợ kỹ thuật và tra mã OEM ưu tiên 24/7.</li>
        </ul>
    </div>
    
    <p style='text-align:center;margin:28px 0;'>
        <a href='https://coolingsystems.vn/customer/profile' style='background:#1a3258;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:8px;font-weight:700;display:inline-block;'>TRUY CẬP TÀI KHOẢN GARA</a>
    </p>
    <p style='font-size:13px;color:#64748b;'>Nếu cần hỗ trợ thêm thông tin về đơn hàng hoặc công nợ, vui lòng liên hệ Hotline: <strong>0947.795.471</strong>.</p>
    ";
    return sendEmail($email, $subject, _emailLayout("Xác thực Gara thành công", $body));
}

function sendGarageRejectedEmail(string $email, string $name, string $garageName, string $reason): bool {
    $subject = "Thông báo về đơn đăng ký Gara — Cooling System";
    $reasonClean = htmlspecialchars($reason);
    $body = "
    <div style='text-align:center;margin-bottom:24px;'>
        <div style='display:inline-block;background:#fef2f2;color:#b91c1c;padding:10px 20px;border-radius:50px;font-weight:800;font-size:14px;'>
            ✕ ĐƠN ĐĂNG KÝ CHƯA ĐƯỢC PHÊ DUYỆT
        </div>
    </div>
    <h2 style='color:#1a3258;margin:0 0 12px;text-align:center;'>Thông báo kết quả xét duyệt Gara</h2>
    <p>Xin chào <strong>" . htmlspecialchars($name) . "</strong>,</p>
    <p>Cooling System xin cảm ơn bạn đã gửi thông tin đăng ký Gara <strong>" . htmlspecialchars($garageName) . "</strong>.</p>
    <p>Rất tiếc, sau khi kiểm tra hồ sơ, Ban quản trị chưa thể duyệt hồ sơ của bạn do lý do sau:</p>
    
    <div style='background:#fff5f5;border-left:4px solid #ef4444;padding:16px;margin:20px 0;border-radius:0 8px 8px 0;'>
        <strong style='color:#991b1b;display:block;margin-bottom:4px;'>📌 Lý do từ chối:</strong>
        <span style='color:#7f1d1d;font-size:14px;'>{$reasonClean}</span>
    </div>
    
    <p>Vui lòng kiểm tra và chuẩn bị lại ảnh chứng từ hợp lệ (Ảnh bảng hiệu Gara, GPKD/MS HKD, và tối thiểu 3 ảnh chụp thực tế Gara/Cửa hàng) để gửi lại đơn đăng ký mới.</p>
    
    <p style='text-align:center;margin:28px 0;'>
        <a href='https://coolingsystems.vn/customer/profile' style='background:#c9a14a;color:#0b1d3a;padding:14px 32px;text-decoration:none;border-radius:8px;font-weight:800;display:inline-block;'>NỘP LẠI HỒ SƠ ĐĂNG KÝ</a>
    </p>
    <p style='font-size:13px;color:#64748b;'>Nếu bạn có thắc mắc cần làm rõ, vui lòng liên hệ Hotline: <strong>0947.795.471</strong>.</p>
    ";
    return sendEmail($email, $subject, _emailLayout("Thông báo đăng ký Gara", $body));
}

function _emailLayout(string $title, string $body): string {
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,sans-serif"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:40px 0"><tr><td align="center"><table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%"><tr><td style="background:#1a3258;padding:24px 32px;border-radius:10px 10px 0 0;text-align:center"><div style="color:#f0c040;font-size:28px;font-weight:900;letter-spacing:2px">COOLING</div><div style="color:rgba(255,255,255,0.7);font-size:12px;margin-top:2px">PARTS &amp; SERVICE</div></td></tr><tr><td style="background:#ffffff;padding:32px 36px;border-radius:0 0 10px 10px">' . $body . '</td></tr><tr><td style="padding:16px;text-align:center;color:#aaa;font-size:11px">© ' . date('Y') . ' Cooling System · <a href="https://coolingsystems.vn" style="color:#1a3258">coolingsystems.vn</a></td></tr></table></td></tr></table></body></html>';
}

