<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$rules = [
  ['customers.view','P093',['TQ','QL','TH','X']],
  ['customers.pii.view','P096',['TQ','QL','TH','X']],
  ['customers.manage','P094',['TQ','QL','TH']],
  ['customers.manage','P095',['TQ','QL','TH']],
  ['customers.export','P103',['TQ','QL']],
];
$pdo->beginTransaction();
try { $q=$pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)'); foreach($rules as [$c,$p,$l]) $q->execute([$c,$p,json_encode($l)]); $pdo->commit(); echo json_encode(['ok'=>true]); }
catch(Throwable $e) { if($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
