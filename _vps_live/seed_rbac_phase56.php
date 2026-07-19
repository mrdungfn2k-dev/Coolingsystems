<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)')->execute(['warranty.documents.print','P112',json_encode(['TQ','QL','TH'])]);
echo json_encode(['ok'=>true,'capability'=>'warranty.documents.print'], JSON_UNESCAPED_UNICODE);
