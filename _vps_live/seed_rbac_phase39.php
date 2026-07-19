<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['crm.customer_care.manage', 'P133', ['TQ','QL','TH']],
  ['crm.complaints.manage', 'P134', ['TQ','QL','TH']],
  ['marketing.campaign.analytics', 'P135', ['TQ','QL','TH']],
  ['marketing.leads.export', 'P136', ['TQ','QL']],
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
