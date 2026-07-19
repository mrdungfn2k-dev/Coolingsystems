<?php

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->beginTransaction();
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_staff_role_links (
      staff_role_id INTEGER PRIMARY KEY,
      rbac_role_code TEXT NOT NULL UNIQUE,
      created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
      FOREIGN KEY(staff_role_id) REFERENCES staff_roles(id) ON DELETE CASCADE,
      FOREIGN KEY(rbac_role_code) REFERENCES rbac_roles(code) ON DELETE RESTRICT
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_capability_rules (
      capability TEXT NOT NULL,
      permission_code TEXT NOT NULL,
      allowed_levels TEXT NOT NULL,
      PRIMARY KEY(capability, permission_code),
      FOREIGN KEY(permission_code) REFERENCES rbac_permissions(code) ON DELETE CASCADE
    )");

    $templates = [
      'SA'=>'System administrator','OWN'=>'Business owner / Director','BM'=>'Branch manager',
      'SAL'=>'Sales staff','CAS'=>'Cashier','WH'=>'Warehouse staff','PUR'=>'Purchasing staff',
      'ACC'=>'Accountant','CS'=>'Customer service / Warranty','TECH'=>'Technician / Installer',
      'DEL'=>'Delivery staff','MKT'=>'Marketing / CRM','AUD'=>'Internal auditor',
    ];
    $findRole = $pdo->prepare('SELECT staff_role_id FROM rbac_staff_role_links WHERE rbac_role_code=?');
    $createRole = $pdo->prepare('INSERT INTO staff_roles (name,description,permissions) VALUES (?,?,?)');
    $linkRole = $pdo->prepare('INSERT INTO rbac_staff_role_links (staff_role_id,rbac_role_code) VALUES (?,?)');
    foreach ($templates as $code=>$name) {
        $findRole->execute([$code]);
        if ($findRole->fetchColumn()) continue;
        $createRole->execute(['[RBAC] '.$name.' ('.$code.')','Read-only role template imported from the KiotViet permission matrix. Access is enforced by detailed capability rules.','[]']);
        $linkRole->execute([(int)$pdo->lastInsertId(),$code]);
    }

    $rules = [
      ['catalog.products.view','P013',['TQ','QL','TH','X']],
      ['catalog.products.create','P014',['TQ','QL','TH']],
      ['catalog.products.edit','P015',['TQ','QL','TH']],
      ['catalog.products.import','P027',['TQ','QL','TH']],
      ['catalog.products.archive','P028',['TQ','QL','TH']],
      ['inventory.view','P029',['TQ','QL','TH','X']],
      ['inventory.update','P041',['TQ','QL','TH']],
      ['sales.orders.view','P053',['TQ','QL','TH','X']],
      ['sales.orders.create','P053',['TQ','QL','TH']],
      ['sales.orders.manage','P054',['TQ','QL','TH']],
      ['sales.returns.view','P065',['TQ','QL','TH','X']],
      ['sales.returns.approve','P066',['TQ','QL']],
      ['sales.payment.collect','P059',['TQ','QL','TH']],
      ['sales.payment.collect','P060',['TQ','QL','TH']],
      ['sales.delivery.update','P070',['TQ','QL','TH']],
    ];
    $pdo->exec('DELETE FROM rbac_capability_rules');
    $insertRule = $pdo->prepare('INSERT INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)');
    foreach ($rules as [$capability,$permission,$levels]) $insertRule->execute([$capability,$permission,json_encode($levels)]);
    $pdo->commit();
    echo json_encode(['ok'=>true,'templates'=>count($templates),'rules'=>count($rules)]) . PHP_EOL;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
