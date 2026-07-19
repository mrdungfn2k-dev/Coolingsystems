<?php
$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)')
    ->execute(['finance.ledger.void', 'P118', json_encode(['TQ', 'QL'])]);
echo json_encode(['ok' => true, 'capability' => 'finance.ledger.void'], JSON_UNESCAPED_UNICODE);
