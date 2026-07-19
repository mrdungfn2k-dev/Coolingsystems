<?php

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['catalog.taxonomy.view','P013',['TQ','QL','TH','X']],
  ['catalog.taxonomy.manage','P015',['TQ','QL','TH']],
  ['catalog.vehicle.view','P017',['TQ','QL','TH','X']],
  ['catalog.vehicle.manage','P017',['TQ','QL','TH']],
  ['organization.branches.view','P003',['TQ','QL','X']],
  ['organization.branches.manage','P003',['TQ','QL']],
  ['marketing.promotions.view','P131',['TQ','QL','TH','X']],
  ['marketing.promotions.manage','P131',['TQ','QL','TH']],
  ['integration.data.import','P147',['TQ','QL']],
  ['integration.data.export','P148',['TQ','QL']],
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
