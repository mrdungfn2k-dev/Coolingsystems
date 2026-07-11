<?php
/**
 * ===================================================
 * COOLING SYSTEM — Export Products + Reset Data
 * 
 * Bước 1: Xuất toàn bộ sản phẩm ra file ZIP (CSV)
 * Bước 2: Reset dữ liệu vận hành (giữ danh mục, thương hiệu, hãng xe)
 * Bước 3: Tự xóa script
 *
 * TOKEN BẢO MẬT: Chỉ chạy được 1 lần
 * ===================================================
 */

define('RESET_TOKEN', 'COOLING_RESET_2026_X9K2M');

$token = $_GET['token'] ?? '';
if ($token !== RESET_TOKEN) {
    http_response_code(403);
    die('<h2>403 Forbidden</h2>');
}

$step = $_GET['step'] ?? 'menu';

$DB_PATH      = '/var/lib/cooling/cooling.db';
$BACKUP_DIR   = '/var/lib/cooling/';
$EXPORT_DIR   = '/opt/cooling-php/uploads/';

if (!file_exists($DB_PATH)) {
    die("❌ Không tìm thấy DB: $DB_PATH");
}

$pdo = new PDO("sqlite:$DB_PATH", null, null, [
    PDO::ATTR_ERRMODE      => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Cooling — Export & Reset</title>
<style>
  body{font-family:monospace;background:#0d1117;color:#c9d1d9;margin:0;padding:20px}
  h2{color:#58a6ff}
  pre{background:#161b22;border:1px solid #30363d;padding:16px;border-radius:8px;overflow-x:auto;white-space:pre-wrap}
  .btn{display:inline-block;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;margin:8px 4px;cursor:pointer;border:none}
  .btn-green{background:#238636;color:#fff}
  .btn-red{background:#da3633;color:#fff}
  .btn-blue{background:#1f6feb;color:#fff}
  .btn-gray{background:#30363d;color:#c9d1d9}
  .ok{color:#3fb950}
  .err{color:#f85149}
  .warn{color:#d29922}
  .info{color:#58a6ff}
  .box{background:#161b22;border:1px solid #30363d;padding:20px;border-radius:8px;margin:16px 0}
  table{border-collapse:collapse;width:100%;margin:12px 0}
  th,td{border:1px solid #30363d;padding:8px 12px;text-align:left}
  th{background:#21262d}
</style>
</head>
<body>

<?php

// ================================================================
// MENU CHÍNH
// ================================================================
if ($step === 'menu') {
?>
<h2>⚙️ Cooling System — Export & Reset Dữ Liệu</h2>
<div class="box">
  <b>📋 Thống kê hiện tại:</b>
  <table>
    <tr><th>Bảng</th><th>Số dòng</th><th>Hành động</th></tr>
    <?php
    $tables = ['products','product_images','product_fitments','orders','order_items','users','reviews','vouchers','partners','cart_items','favorites'];
    foreach($tables as $t):
        $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $action = in_array($t,['products','product_images','product_fitments']) ? '<span class="warn">→ Xuất CSV + Xóa</span>' : '<span class="err">→ Xóa</span>';
        echo "<tr><td>$t</td><td>$c</td><td>$action</td></tr>";
    endforeach;
    ?>
  </table>
  <p class="warn">⚠️ GIỮ LẠI: categories, brands, car_models, settings, system_config, static_pages, stores, garages, articles, tài khoản admin</p>
</div>

<div class="box">
  <b>🔢 Thực hiện theo thứ tự:</b><br><br>
  <a class="btn btn-blue" href="?token=<?= RESET_TOKEN ?>&step=export">
    Bước 1: Xuất sản phẩm ra CSV
  </a>
  &nbsp;&nbsp;
  <span class="btn btn-gray">Bước 2: Reset dữ liệu (sau khi xuất xong)</span>
</div>

<?php
}

// ================================================================
// BƯỚC 1: EXPORT CSV
// ================================================================
elseif ($step === 'export') {
    echo '<h2>📦 Đang xuất dữ liệu sản phẩm...</h2><pre>';

    $date     = date('Ymd_His');
    $zip_name = "products_backup_{$date}.zip";
    $zip_path = $EXPORT_DIR . $zip_name;

    // Tạo thư mục tạm
    $tmp_dir = sys_get_temp_dir() . '/cooling_export_' . $date;
    mkdir($tmp_dir, 0755, true);

    // ---- Hàm xuất CSV ----
    function exportTableToCSV(PDO $pdo, string $table, string $file): int {
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll();
        if (empty($rows)) {
            file_put_contents($file, "");
            return 0;
        }
        $fp = fopen($file, 'w');
        // UTF-8 BOM cho Excel
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        return count($rows);
    }

    // ---- Xuất các bảng sản phẩm ----
    $export_tables = [
        'products'         => 'products.csv',
        'product_images'   => 'product_images.csv',
        'product_fitments' => 'product_fitments.csv',
        'product_brand_map'=> 'product_brand_map.csv',
        'product_brands'   => 'product_brands.csv',
        'categories'       => 'categories.csv',
        'brands'           => 'brands.csv',
        'car_models'       => 'car_models.csv',
    ];

    $exported = [];
    foreach ($export_tables as $table => $filename) {
        $filepath = $tmp_dir . '/' . $filename;
        $count    = exportTableToCSV($pdo, $table, $filepath);
        $exported[$filename] = $count;
        echo "✅ $table → $filename ($count dòng)\n";
    }

    // Tạo README
    $readme = "=== COOLING SYSTEM — PRODUCT BACKUP ===\n";
    $readme .= "Ngày xuất: " . date('Y-m-d H:i:s') . "\n\n";
    $readme .= "Danh sách file:\n";
    foreach ($exported as $f => $c) {
        $readme .= "  - $f: $c dòng\n";
    }
    $readme .= "\nHướng dẫn import lại:\n";
    $readme .= "  1. Import products.csv vào bảng products\n";
    $readme .= "  2. Import product_images.csv vào bảng product_images\n";
    $readme .= "  3. Import product_fitments.csv vào bảng product_fitments\n";
    $readme .= "  4. Import product_brand_map.csv vào bảng product_brand_map\n";
    file_put_contents($tmp_dir . '/README.txt', $readme);

    // ---- Tạo ZIP ----
    if (!class_exists('ZipArchive')) {
        echo "\n<span class='err'>❌ PHP ZipArchive không khả dụng. Dùng tar thay thế...</span>\n";
        // Fallback: dùng tar
        $tar_path = $EXPORT_DIR . "products_backup_{$date}.tar.gz";
        exec("tar -czf '$tar_path' -C '$tmp_dir' .", $out, $ret);
        if ($ret === 0) {
            $zip_path = $tar_path;
            $zip_name = basename($tar_path);
            echo "<span class='ok'>✅ Tạo tar.gz thành công: $zip_name</span>\n";
        } else {
            // Fallback: lưu từng file CSV riêng
            echo "<span class='warn'>⚠️ Không tạo được archive. Các file CSV đã lưu tại $EXPORT_DIR</span>\n";
            foreach ($export_tables as $table => $filename) {
                copy($tmp_dir . '/' . $filename, $EXPORT_DIR . "backup_{$date}_{$filename}");
                echo "  → $EXPORT_DIR backup_{$date}_{$filename}\n";
            }
            $zip_path = null;
        }
    } else {
        $zip = new ZipArchive();
        $zip->open($zip_path, ZipArchive::CREATE);
        foreach (array_keys($export_tables) as $i => $table) {
            $filename = array_values($export_tables)[$i];
            $filepath = $tmp_dir . '/' . $filename;
            if (file_exists($filepath)) {
                $zip->addFile($filepath, $filename);
            }
        }
        $zip->addFile($tmp_dir . '/README.txt', 'README.txt');
        $zip->close();
        echo "\n<span class='ok'>✅ Đã tạo ZIP: $zip_name</span>\n";
    }

    // Dọn thư mục tạm
    array_map('unlink', glob("$tmp_dir/*.*"));
    @rmdir($tmp_dir);

    $file_size = $zip_path && file_exists($zip_path) ? round(filesize($zip_path) / 1024, 1) . ' KB' : 'N/A';
    echo "\n<span class='info'>📁 File lưu tại: $zip_path</span>";
    echo "\n<span class='info'>📏 Kích thước: $file_size</span>";
    echo "</pre>";

    $download_url = '/uploads/' . $zip_name;
    ?>
    <div class="box">
      <b class="ok">✅ Xuất dữ liệu hoàn tất!</b><br><br>
      <a class="btn btn-blue" href="<?= htmlspecialchars($download_url) ?>" download>
        ⬇️ Tải file backup (<?= $file_size ?>)
      </a>
      <br><br>
      <b>Tiếp theo:</b><br>
      <a class="btn btn-red" href="?token=<?= RESET_TOKEN ?>&step=reset"
         onclick="return confirm('⚠️ XÁC NHẬN RESET TOÀN BỘ DỮ LIỆU?\n\nThao tác này không thể hoàn tác!\nChỉ thực hiện sau khi đã tải file backup.')">
        🗑️ Bước 2: Reset dữ liệu ngay
      </a>
      <a class="btn btn-gray" href="?token=<?= RESET_TOKEN ?>&step=menu">← Quay lại</a>
    </div>
    <?php
}

// ================================================================
// BƯỚC 2: RESET DATA
// ================================================================
elseif ($step === 'reset') {
    echo '<h2>🗑️ Đang reset dữ liệu...</h2><pre>';

    // Backup DB file trước
    $backup_path = $BACKUP_DIR . 'cooling_backup_' . date('Ymd_His') . '.db';
    if (copy($DB_PATH, $backup_path)) {
        echo "<span class='ok'>✅ Backup DB: $backup_path</span>\n\n";
    } else {
        echo "<span class='warn'>⚠️ Không backup được file DB (tiếp tục...)</span>\n\n";
    }

    try {
        $pdo->exec('PRAGMA foreign_keys = OFF');
        $pdo->exec('BEGIN TRANSACTION');

        $tables_to_clear = [
            // Đơn hàng & thanh toán
            'sub_orders', 'order_items', 'order_payments',
            'order_returns', 'order_status_history', 'orders', 'payments',
            'commission_transactions',
            // Ví & rút tiền
            'wallet_transactions', 'wallets', 'withdrawal_requests',
            // Voucher
            'voucher_usage', 'voucher_saves', 'user_saved_vouchers', 'vouchers',
            // Sản phẩm
            'product_fitments', 'product_brand_map', 'product_brands',
            'product_images', 'products',
            // Đánh giá
            'review_images', 'review_reactions', 'review_reports',
            'review_responses', 'reviews',
            // Giỏ hàng & yêu thích
            'cart_items', 'favorites',
            // User data
            'user_invoice_info', 'user_notifications', 'shipping_addresses',
            'partner_documents', 'partners',
            // Nhân sự
            'staff_permissions', 'staff_role_assignments',
            // Chat & liên hệ
            'chat_messages', 'chat_threads', 'chat_threads_bak',
            'contact_messages', 'newsletter_subscribers',
            // Thông báo & logs
            'admin_notifications', 'notifications', 'audit_logs',
        ];

        foreach ($tables_to_clear as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $pdo->exec("DELETE FROM `$table`");
            echo "🗑️  [$table] — xóa $count dòng\n";
        }

        // Giữ lại admin
        $admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
        $total  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $pdo->exec("DELETE FROM users WHERE role != 'admin'");
        echo "\n<span class='ok'>👤 Users: giữ $admins admin, xóa " . ($total - $admins) . " tài khoản</span>\n";

        // Reset auto-increment
        $all = array_merge($tables_to_clear, ['users']);
        foreach ($all as $t) {
            $pdo->exec("DELETE FROM sqlite_sequence WHERE name = '$t'");
        }
        echo "<span class='ok'>🔄 Reset auto-increment xong</span>\n";

        $pdo->exec('COMMIT');
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('VACUUM');

        echo "\n<span class='ok'>✅ ===== RESET HOÀN TẤT =====</span>\n";
        echo "📅 " . date('Y-m-d H:i:s') . "\n\n";
        echo "=== KIỂM TRA SAU RESET ===\n";
        $check = ['categories'=>'GIỮ','brands'=>'GIỮ','car_models'=>'GIỮ',
                  'products'=>'XÓA','orders'=>'XÓA','users'=>'GIỮ ADMIN','reviews'=>'XÓA'];
        foreach ($check as $t => $note) {
            $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $cl = $c > 0 && $note==='XÓA' ? 'err' : 'ok';
            echo "<span class='$cl'>" . str_pad($t,20) . ": $c dòng [$note]</span>\n";
        }

    } catch (Exception $e) {
        $pdo->exec('ROLLBACK');
        echo "\n<span class='err'>❌ LỖI: " . $e->getMessage() . "</span>\n";
        echo "<span class='warn'>⚠️ Đã rollback, dữ liệu an toàn.</span>\n";
    }

    echo '</pre>';
    ?>
    <div class="box">
      <b class="ok">✅ Hệ thống đã được làm mới!</b><br><br>
      <a class="btn btn-blue" href="/admin">Vào trang Admin</a>
    </div>
    <?php
    // Tự xóa script
    @unlink(__FILE__);
}
?>
</body>
</html>
