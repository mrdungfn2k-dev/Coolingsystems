<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['system.settings.view', 'P002', ['TQ','QL']],
  ['system.business.manage', 'P002', ['TQ','QL']],
  ['system.smtp.manage', 'P002', ['TQ']],
  ['inventory.alerts.manage', 'P004', ['TQ','QL','TH']],
  ['system.payment.manage', 'P012', ['TQ','QL']],
  ['system.social.manage', 'P002', ['TQ','QL']],
];
$pdo->beginTransaction();
try {
  $statement = $pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)');
  foreach ($rules as [$capability, $permissionCode, $levels]) {
    $statement->execute([$capability, $permissionCode, json_encode($levels)]);
  }
  $pdo->commit();
  echo json_encode(['ok' => true]);
} catch (Throwable $exception) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  throw $exception;
}
