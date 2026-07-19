<?php

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['catalog.pricing.edit','P021',['TQ','QL','TH']],
  ['catalog.cost.view','P022',['TQ','QL','TH','X']],
  ['catalog.cost.edit','P023',['TQ','QL','TH']],
  ['inventory.thresholds.edit','P024',['TQ','QL','TH']],
];
$pdo->beginTransaction();
try {
    $insert = $pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)');
    foreach ($rules as [$capability, $permission, $levels]) {
        $insert->execute([$capability, $permission, json_encode($levels)]);
    }
    $pdo->commit();
    echo json_encode(['ok'=>true, 'rules'=>count($rules)]) . PHP_EOL;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
