import sys
from datetime import datetime
from pathlib import Path

import paramiko


sys.stdout.reconfigure(encoding='utf-8')

LOCAL = Path('cooling-php/_vps_live')
HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)


def run(command, timeout=90):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    status = stdout.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{output}\n{error}')
    return output.strip()


def replace_once(source, old, new, label):
    count = source.count(old)
    if count != 1:
        raise RuntimeError(f'{label}: expected one match, got {count}')
    return source.replace(old, new, 1)


def read_remote(path):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'rb') as handle:
            return handle.read().decode('utf-8')
    finally:
        sftp.close()


def put_text(path, content):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'wb') as handle:
            handle.write(content.encode('utf-8'))
    finally:
        sftp.close()


def counts():
    return run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from products').fetchone()[0],"
        "c.execute('select count(*) from users').fetchone()[0],"
        "c.execute('select count(*) from orders').fetchone()[0],"
        "c.execute('select count(*) from staff_roles').fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
        "c.execute('select count(*) from rbac_staff_role_links').fetchone()[0],"
        "c.execute('select count(*) from rbac_capability_rules').fetchone()[0]])))\""
    )


def template_snapshot():
    return run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print(c.execute('select count(*) from staff_roles sr join rbac_staff_role_links l on l.staff_role_id=sr.id').fetchone()[0]); "
        "print(c.execute(\\\"select count(*) from staff_roles where name like '[RBAC] %System administrator%'\\\").fetchone()[0])\""
    )


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase42-{stamp}'
baseline = counts()

try:
    run(
        f"mkdir -p {backup} && cp {DB} {backup}/cooling.db && "
        f"cp {APP}/includes/rbac.php {backup}/rbac.php && "
        f"cp {APP}/routes/admin.php {backup}/admin.php && "
        f"cp {APP}/views/admin/staff.php {backup}/staff.php && "
        f"cp {APP}/views/admin/role-form.php {backup}/role-form.php && "
        f"test -f {APP}/views/admin/role-permissions.php && cp {APP}/views/admin/role-permissions.php {backup}/role-permissions.php || true"
    )
    routes = read_remote(f'{APP}/routes/admin.php')

    helper = """function rbacSanitizeRolePermissions(array $permissions): array {
    $legacy = ['orders','create_order','returns','products','categories','brands','brand_models','content','static_pages','stores','users','reviews','contacts','chat','vouchers','promotions','staff','reports','tax_config'];
    $capabilities = array_column(rbacCapabilityCatalog(), 'capability');
    $allowed = array_flip(array_merge($legacy, array_map(fn($capability) => 'rbac:' . $capability, $capabilities)));
    $result = [];
    foreach ($permissions as $permission) {
        $permission = (string)$permission;
        if (isset($allowed[$permission])) $result[$permission] = true;
    }
    return array_keys($result);
}

"""
    routes = replace_once(routes, "get('/admin/staff', function() {", helper + "get('/admin/staff', function() {", 'role helpers')
    routes = replace_once(
        routes,
        "view('admin/role-form', ['title'=>'Tạo vai trò mới','userRole'=>'admin','staffRole'=>[]]);",
        "view('admin/role-form', ['title'=>'Tạo vai trò mới','userRole'=>'admin','staffRole'=>[],'rbacCapabilities'=>rbacCapabilityCatalog()]);",
        'new role form',
    )
    routes = replace_once(
        routes,
        "$perms = json_encode(array_filter($_POST['permissions'] ?? []));",
        "$perms = json_encode(rbacSanitizeRolePermissions($_POST['permissions'] ?? []), JSON_UNESCAPED_UNICODE);",
        'new role sanitization',
    )
    routes = replace_once(
        routes,
        "view('admin/role-form', ['title'=>'Sửa vai trò','userRole'=>'admin','staffRole'=>$staffRole]);",
        "view('admin/role-form', ['title'=>'Sửa vai trò','userRole'=>'admin','staffRole'=>$staffRole,'rbacCapabilities'=>rbacCapabilityCatalog()]);",
        'edit role form',
    )
    routes = replace_once(
        routes,
        "$perms = json_encode(array_values(array_filter($_POST['permissions'] ?? [])));",
        "$perms = json_encode(rbacSanitizeRolePermissions($_POST['permissions'] ?? []), JSON_UNESCAPED_UNICODE);",
        'edit role sanitization',
    )

    insertion = """post('/admin/staff/roles/:id/duplicate', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $source = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$source) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $template = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $permissions = $template ? array_map(fn($capability) => 'rbac:' . $capability, rbacTemplateCapabilities($template['rbac_role_code'])) : (json_decode($source['permissions'] ?? '[]', true) ?: []);
    $newId = dbInsert('INSERT INTO staff_roles (name,description,permissions) VALUES (?,?,?)', ['Bản sao - ' . $source['name'], 'Vai trò tùy chỉnh được nhân bản từ ' . $source['name'] . '.', json_encode(rbacSanitizeRolePermissions($permissions), JSON_UNESCAPED_UNICODE)]);
    flash('success','Đã tạo bản sao. Bạn có thể chỉnh sửa quyền của vai trò mới.');
    redirect('/admin/staff/roles/' . $newId . '/edit');
});

get('/admin/staff/roles/:id/permissions', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.view', '/admin/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $rbacTemplate = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $matrixPermissions = [];
    if ($rbacTemplate) {
        $matrixPermissions = dbAll("SELECT permission.module_name, permission.feature_name, permission.action_name, role_permission.access_level, GROUP_CONCAT(DISTINCT rule.capability) AS capabilities FROM rbac_role_permissions role_permission INNER JOIN rbac_permissions permission ON permission.code=role_permission.permission_code LEFT JOIN rbac_capability_rules rule ON rule.permission_code=permission.code WHERE role_permission.role_code=? AND role_permission.access_level<>'NONE' GROUP BY permission.code, role_permission.access_level ORDER BY permission.sort_order", [$rbacTemplate['rbac_role_code']]);
    } else {
        $selected = json_decode($staffRole['permissions'] ?? '[]', true) ?: [];
        foreach (rbacCapabilityCatalog() as $capability) {
            if (in_array('rbac:' . $capability['capability'], $selected, true)) $matrixPermissions[] = ['module_name'=>$capability['module_name'], 'feature_name'=>$capability['feature_name'], 'action_name'=>$capability['action_name'], 'access_level'=>'Tùy chỉnh', 'capabilities'=>$capability['capability']];
        }
    }
    view('admin/role-permissions', ['title'=>'Quyền của vai trò','userRole'=>'admin','staffRole'=>$staffRole,'rbacTemplate'=>$rbacTemplate,'matrixPermissions'=>$matrixPermissions]);
});

"""
    routes = replace_once(routes, "get('/admin/staff/roles/:id/assign', function($p) {", insertion + "get('/admin/staff/roles/:id/assign', function($p) {", 'role action routes')

    put_text('/tmp/rbac42-admin.php', routes)
    sftp = client.open_sftp()
    try:
        sftp.put(str(LOCAL / 'rbac_phase42.php'), '/tmp/rbac42.php')
        sftp.put(str(LOCAL / 'seed_rbac_phase42.php'), '/tmp/rbac42-seed.php')
        sftp.put(str(LOCAL / 'staff_phase42.php'), '/tmp/rbac42-staff.php')
        sftp.put(str(LOCAL / 'role-form_phase42.php'), '/tmp/rbac42-role-form.php')
        sftp.put(str(LOCAL / 'role-permissions_phase42.php'), '/tmp/rbac42-role-permissions.php')
    finally:
        sftp.close()

    result = run(
        "chown www-data:www-data /tmp/rbac42.php /tmp/rbac42-seed.php /tmp/rbac42-admin.php /tmp/rbac42-staff.php /tmp/rbac42-role-form.php /tmp/rbac42-role-permissions.php && "
        "runuser -u www-data -- php /tmp/rbac42-seed.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac42.php {APP}/includes/rbac.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac42-admin.php {APP}/routes/admin.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac42-staff.php {APP}/views/admin/staff.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac42-role-form.php {APP}/views/admin/role-form.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac42-role-permissions.php {APP}/views/admin/role-permissions.php && "
        f"php -l {APP}/includes/rbac.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/staff.php && php -l {APP}/views/admin/role-form.php && php -l {APP}/views/admin/role-permissions.php && "
        "rm -f /tmp/rbac42.php /tmp/rbac42-seed.php /tmp/rbac42-admin.php /tmp/rbac42-staff.php /tmp/rbac42-role-form.php /tmp/rbac42-role-permissions.php"
    )
    print('BACKUP=' + backup)
    print('BASELINE=' + baseline)
    print('RESULT=' + result)
    print('AFTER=' + counts())
    print('TEMPLATES=' + template_snapshot().replace('\n', '|'))
    print('HTTP=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/staff"))
except Exception:
    try:
        run(
            f"test -f {backup}/cooling.db && cp {backup}/cooling.db {DB}; "
            f"test -f {backup}/rbac.php && cp {backup}/rbac.php {APP}/includes/rbac.php; "
            f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; "
            f"test -f {backup}/staff.php && cp {backup}/staff.php {APP}/views/admin/staff.php; "
            f"test -f {backup}/role-form.php && cp {backup}/role-form.php {APP}/views/admin/role-form.php; "
            f"test -f {backup}/role-permissions.php && cp {backup}/role-permissions.php {APP}/views/admin/role-permissions.php || true; "
            f"chown www-data:www-data {DB} {APP}/includes/rbac.php {APP}/routes/admin.php {APP}/views/admin/staff.php {APP}/views/admin/role-form.php"
        )
    finally:
        client.close()
    raise

client.close()
