<?php

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['crm.engagement.manage','P133',['TQ','QL','TH']],
  ['sales.orders.cancel','P056',['TQ','QL','TH']],
  ['sales.orders.cancel_approved','P057',['TQ','QL']],
];
$pdo->beginTransaction();
try {
    $insert = $pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)');
    foreach ($rules as [$capability, $permission, $levels]) $insert->execute([$capability, $permission, json_encode($levels)]);
    $pdo->commit();
    echo json_encode(['ok'=>true, 'rules'=>count($rules)]) . PHP_EOL;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
