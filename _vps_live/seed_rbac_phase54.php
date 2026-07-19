<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->beginTransaction();
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS warranty_materials (id INTEGER PRIMARY KEY AUTOINCREMENT,warranty_case_id INTEGER NOT NULL,product_id INTEGER NOT NULL,quantity INTEGER NOT NULL CHECK(quantity>0),note TEXT,issued_by INTEGER,issued_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(warranty_case_id) REFERENCES warranty_cases(id),FOREIGN KEY(product_id) REFERENCES products(id))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_warranty_materials_case ON warranty_materials(warranty_case_id,issued_at DESC)");
    $pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)')->execute(['warranty.materials.consume','P109',json_encode(['TQ','QL','TH'])]);
    $pdo->commit();
    echo json_encode(['ok'=>true,'capability'=>'warranty.materials.consume'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
