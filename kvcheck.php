<?php
// KiotViet product status checker - REMOVE AFTER USE
header('Content-Type: application/json; charset=utf-8');
$token = $_GET['t'] ?? '';
if ($token !== 'kv2307') { http_response_code(403); die('403'); }

$db_paths = ['/var/lib/coolingsystems/cooling.db','/var/www/coolingsystems/cooling.db'];
$db = null;
foreach ($db_paths as $p) {
    if (file_exists($p)) { $db = new PDO("sqlite:$p"); break; }
}
if (!$db) die(json_encode(['error'=>'no db']));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Query by KiotViet SKU patterns
// KV SKUs bắt đầu bằng: VDE-, TDP-, DNP-, VHE-, VRE-, VDE-, MQ-, DL-, DN-, LO-
// Hoặc is_admin_created=0 (partner upload)
// Hoặc partner_id IS NOT NULL

// Cach 1: theo is_admin_created
$r1 = $db->query("SELECT is_admin_created, status, COUNT(*) as cnt FROM products GROUP BY is_admin_created, status")->fetchAll(PDO::FETCH_ASSOC);

// Cach 2: theo partner_id
$r2 = $db->query("SELECT CASE WHEN partner_id IS NULL THEN 'admin/import' ELSE 'partner' END as src, status, COUNT(*) as cnt FROM products GROUP BY src, status")->fetchAll(PDO::FETCH_ASSOC);

// Cach 3: theo SKU pattern KiotViet (prefix)
$kv_prefixes = ['VDE-1','VDE-2','VDE-S','VDE-P','TDP-','DNP-','VHE-','VRE-','MQ-','DN-0','DL-','LO-','PH-'];
$pattern_results = [];
foreach ($kv_prefixes as $prefix) {
    $stmt = $db->prepare("SELECT status, COUNT(*) as cnt FROM products WHERE sku LIKE ? GROUP BY status");
    $stmt->execute([$prefix.'%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) $pattern_results[$prefix] = $rows;
}

// Cach 4: tong hop theo created_at (KV duoc import truoc, backup sau)
$r4 = $db->query("SELECT DATE(created_at) as date, status, COUNT(*) as cnt FROM products GROUP BY DATE(created_at), status ORDER BY date")->fetchAll(PDO::FETCH_ASSOC);

// Cach 5: check partner table
try {
    $r5 = $db->query("SELECT p.full_name, p.email, COUNT(pr.id) as cnt FROM users p JOIN products pr ON pr.partner_id=p.id GROUP BY p.id")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $r5 = ['error'=>$e->getMessage()]; }

echo json_encode([
    'by_admin_created' => $r1,
    'by_partner'       => $r2,
    'by_sku_pattern'   => $pattern_results,
    'by_date'          => $r4,
    'by_partner_user'  => $r5,
], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
