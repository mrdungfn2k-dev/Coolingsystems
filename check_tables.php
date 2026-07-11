<?php
$pdo = new PDO('sqlite:c:/xampp2/htdocs/coolingsystem_backup/cooling-php/cooling.db');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo "=== DANH SACH BANG VA SO DONG ===\n";
foreach($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo str_pad($t, 35) . ": $count rows\n";
}
