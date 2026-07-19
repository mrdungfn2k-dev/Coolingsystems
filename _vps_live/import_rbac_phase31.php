<?php

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php import_rbac_phase31.php /path/to/matrix.json\n");
    exit(2);
}

$payload = json_decode((string)file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
if (($payload['schema_version'] ?? '') !== 'rbac-phase-3.1') throw new RuntimeException('Unsupported RBAC payload.');
if (count($payload['roles'] ?? []) !== 13 || count($payload['permissions'] ?? []) !== 157) {
    throw new RuntimeException('Unexpected matrix dimensions.');
}

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->beginTransaction();

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_roles (
      code TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      default_scope TEXT NOT NULL,
      responsibility TEXT,
      restricted_actions TEXT,
      implementation_note TEXT,
      created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
      updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_permissions (
      code TEXT PRIMARY KEY,
      module_code TEXT NOT NULL,
      module_name TEXT NOT NULL,
      feature_name TEXT NOT NULL,
      action_name TEXT NOT NULL,
      data_scope TEXT,
      sensitivity TEXT,
      note TEXT,
      sort_order INTEGER NOT NULL,
      created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_role_permissions (
      role_code TEXT NOT NULL,
      permission_code TEXT NOT NULL,
      access_level TEXT NOT NULL CHECK(access_level IN ('TQ','QL','TH','X','NONE')),
      PRIMARY KEY(role_code, permission_code),
      FOREIGN KEY(role_code) REFERENCES rbac_roles(code) ON DELETE CASCADE,
      FOREIGN KEY(permission_code) REFERENCES rbac_permissions(code) ON DELETE CASCADE
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rbac_import_runs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      source_file TEXT NOT NULL,
      source_sha256 TEXT NOT NULL,
      role_count INTEGER NOT NULL,
      permission_count INTEGER NOT NULL,
      matrix_count INTEGER NOT NULL,
      imported_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )");

    // These tables are introduced in this phase only and are not read by the current auth layer.
    $pdo->exec('DELETE FROM rbac_role_permissions');
    $pdo->exec('DELETE FROM rbac_permissions');
    $pdo->exec('DELETE FROM rbac_roles');

    $insertRole = $pdo->prepare('INSERT INTO rbac_roles (code,name,default_scope,responsibility,restricted_actions,implementation_note,updated_at) VALUES (?,?,?,?,?,?,datetime(\'now\',\'localtime\'))');
    foreach ($payload['roles'] as $role) {
        $insertRole->execute([$role['code'],$role['name'],$role['default_scope'],$role['responsibility'],$role['restricted_actions'],$role['implementation_note']]);
    }

    $insertPermission = $pdo->prepare('INSERT INTO rbac_permissions (code,module_code,module_name,feature_name,action_name,data_scope,sensitivity,note,sort_order) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach ($payload['permissions'] as $permission) {
        $insertPermission->execute([$permission['code'],$permission['module_code'],$permission['module_name'],$permission['feature_name'],$permission['action_name'],$permission['data_scope'],$permission['sensitivity'],$permission['note'],$permission['sort_order']]);
    }

    $insertMatrix = $pdo->prepare('INSERT INTO rbac_role_permissions (role_code,permission_code,access_level) VALUES (?,?,?)');
    foreach ($payload['matrix'] as $entry) {
        $insertMatrix->execute([$entry['role_code'],$entry['permission_code'],$entry['access_level']]);
    }

    $matrixCount = count($payload['matrix']);
    if ($matrixCount !== 13 * 157) throw new RuntimeException('Incomplete role permission matrix.');
    $insertRun = $pdo->prepare('INSERT INTO rbac_import_runs (source_file,source_sha256,role_count,permission_count,matrix_count) VALUES (?,?,?,?,?)');
    $insertRun->execute([$payload['source_file'],$payload['source_sha256'],count($payload['roles']),count($payload['permissions']),$matrixCount]);
    $pdo->commit();
    echo json_encode(['ok'=>true,'roles'=>count($payload['roles']),'permissions'=>count($payload['permissions']),'matrix'=>$matrixCount], JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
