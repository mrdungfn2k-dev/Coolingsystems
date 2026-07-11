import paramiko

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=30)

php_script = r"""<?php
$DB_PATH    = '/var/lib/cooling/cooling.db';
$EXPORT_DIR = '/opt/cooling-php/uploads/';

$pdo = new PDO("sqlite:$DB_PATH", null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// === EXPORT CSV ===
$date = date('Ymd_His');
$tmp  = sys_get_temp_dir() . '/cooling_' . $date;
mkdir($tmp, 0755, true);

function exportCSV(PDO $pdo, string $table, string $file): int {
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) { file_put_contents($file, ""); return 0; }
    $fp = fopen($file, 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, array_keys($rows[0]));
    foreach ($rows as $row) fputcsv($fp, $row);
    fclose($fp);
    return count($rows);
}

$tables = ['products','product_images','product_fitments','product_brand_map','product_brands','categories','brands','car_models'];
foreach ($tables as $t) {
    $n = exportCSV($pdo, $t, "$tmp/$t.csv");
    echo "EXPORT $t: $n rows\n";
}

// Tao README
$readme = "COOLING BACKUP - " . date('Y-m-d H:i:s') . "\n";
$readme .= "products: " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . " rows\n";
$readme .= "product_images: " . $pdo->query("SELECT COUNT(*) FROM product_images")->fetchColumn() . " rows\n";
file_put_contents("$tmp/README.txt", $readme);

// Dung tar.gz thay ZIP
$tar_name = "products_backup_{$date}.tar.gz";
$tar_path = $EXPORT_DIR . $tar_name;
exec("tar -czf '$tar_path' -C '$tmp' . 2>&1", $out, $ret);
echo "TAR: " . ($ret===0 ? "OK - " . round(filesize($tar_path)/1024,1) . " KB" : "FAIL: ".implode(',',$out)) . "\n";
echo "FILE: $tar_path\n";

// Don dep tmp
array_map('unlink', glob("$tmp/*"));
rmdir($tmp);

// === RESET DATA ===
echo "\nBAT DAU RESET...\n";
$pdo->exec('PRAGMA foreign_keys = OFF');
$pdo->exec('BEGIN TRANSACTION');

$del = ['sub_orders','order_items','order_payments','order_returns','order_status_history',
        'orders','payments','commission_transactions','wallet_transactions','wallets',
        'withdrawal_requests','voucher_usage','voucher_saves','user_saved_vouchers','vouchers',
        'product_fitments','product_brand_map','product_brands','product_images','products',
        'review_images','review_reactions','review_reports','review_responses','reviews',
        'cart_items','favorites','user_invoice_info','user_notifications','shipping_addresses',
        'partner_documents','partners','staff_permissions','staff_role_assignments',
        'chat_messages','chat_threads','chat_threads_bak','contact_messages',
        'newsletter_subscribers','admin_notifications','notifications','audit_logs'];

foreach ($del as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $pdo->exec("DELETE FROM `$t`");
    echo "XOA $t: $n rows\n";
}

$admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$total  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pdo->exec("DELETE FROM users WHERE role != 'admin'");
echo "USERS: giu $admins admin, xoa " . ($total-$admins) . " tai khoan\n";

foreach (array_merge($del, ['users']) as $t) {
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name='$t'");
}

$pdo->exec('COMMIT');
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec('VACUUM');

echo "\n=== KET QUA SAU RESET ===\n";
foreach (['categories','brands','car_models','products','orders','users','reviews','vouchers'] as $t) {
    $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo str_pad($t,20) . ": $c\n";
}
echo "\nHOAN TAT! Backup: $tar_path\n";
"""

# Upload va chay
transport = paramiko.Transport((HOST, 22))
transport.connect(username=USER, password=PASS)
sftp = paramiko.SFTPClient.from_transport(transport)
with sftp.open('/tmp/cooling_reset.php', 'w') as f:
    f.write(php_script)
sftp.close()
transport.close()

print("Chay reset tren VPS...\n")
stdin, stdout, stderr = client.exec_command('php8.3 /tmp/cooling_reset.php', timeout=180)
out = stdout.read().decode('utf-8', errors='replace')
err = stderr.read().decode('utf-8', errors='replace')
print(out)
if err: print("ERR:", err[:300])

client.exec_command('rm -f /tmp/cooling_reset.php')
client.close()
