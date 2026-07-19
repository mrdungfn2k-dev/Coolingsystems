<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['system.dashboard.view', 'P001', ['TQ','QL','TH','X']],
  ['system.staff.view', 'P005', ['TQ','QL','TH','X']],
  ['system.staff.manage', 'P005', ['TQ','QL']],
  ['system.accounts.lock', 'P006', ['TQ','QL']],
  ['system.rbac.view', 'P007', ['TQ','QL','X']],
  ['system.rbac.manage', 'P007', ['TQ','QL']],
  ['system.audit.view', 'P009', ['TQ','QL','X']],
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
