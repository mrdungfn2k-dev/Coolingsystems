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

def run(command, timeout=60):
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

def counts():
    return run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from products').fetchone()[0],"
        "c.execute('select count(*) from users').fetchone()[0],"
        "c.execute('select count(*) from staff_roles').fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
        "c.execute('select count(*) from rbac_capability_rules').fetchone()[0]])))\""
    )

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase40-{stamp}'
baseline = counts()

try:
    run(f"mkdir -p {backup} && cp {DB} {backup}/cooling.db && cp {APP}/includes/rbac.php {backup}/rbac.php && cp {APP}/routes/admin.php {backup}/admin.php")
    sftp = client.open_sftp()
    with sftp.open(f'{APP}/routes/admin.php', 'rb') as current:
        routes = current.read().decode('utf-8')

    routes = replace_once(routes, "$user = requireStaffPermission('categories', '/auth/login');", "$user = requireRbacOrLegacyStaffPermission('system.rbac.view', '/auth/login');", 'staff list gate')
    routes = replace_once(routes, "$user = requireStaffPermission('promotions', '/auth/login');", "$user = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/auth/login');", 'staff role edit gate')
    roleGates = [
        ("get('/admin/staff/roles/new', function() {\n    requireRole('admin', '/admin/login');", "get('/admin/staff/roles/new', function() {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("post('/admin/staff/roles/new', function() {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/roles/new', function() {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("post('/admin/staff/roles/:id/edit', function($p) {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/roles/:id/edit', function($p) {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("post('/admin/staff/roles/:id/delete', function($p) {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/roles/:id/delete', function($p) {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("get('/admin/staff/roles/:id/assign', function($p) {\n    $user = requireRole('admin', '/admin/login');", "get('/admin/staff/roles/:id/assign', function($p) {\n    $user = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("post('/admin/staff/roles/:id/assign', function($p) {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/roles/:id/assign', function($p) {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("$adminUser = requireRole('admin','/admin/login');", "$adminUser = requireRbacOrLegacyStaffPermission('system.rbac.manage','/admin/login');"),
        ("post('/admin/staff/unassign/:id', function($p) {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/unassign/:id', function($p) {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
        ("post('/admin/staff/unassign-all/:id', function($p) {\n    requireRole('admin', '/admin/login');", "post('/admin/staff/unassign-all/:id', function($p) {\n    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');"),
    ]
    for old, new in roleGates:
        routes = replace_once(routes, old, new, 'staff role management gate')

    routes = replace_once(routes, "get('/admin/staff-accounts', function() {\n    requireStaffPermission('staff', '/admin/login');", "get('/admin/staff-accounts', function() {\n    requireRbacOrLegacyStaffPermission('system.staff.view', '/admin/login');", 'staff accounts gate')
    routes = replace_once(routes, "get('/admin/audit', function() {\n    $user = requireRole('admin', '/admin/login');", "get('/admin/audit', function() {\n    $user = requireRbacOrLegacyStaffPermission('system.audit.view', '/admin/login');", 'audit gate')

    with sftp.open('/tmp/rbac40-admin.php', 'wb') as remote:
        remote.write(routes.encode('utf-8'))
    sftp.put(str(LOCAL / 'rbac.php'), '/tmp/rbac40.php')
    sftp.put(str(LOCAL / 'seed_rbac_phase40.php'), '/tmp/rbac40-seed.php')
    sftp.close()

    result = run(
        "runuser -u www-data -- php /tmp/rbac40-seed.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac40.php {APP}/includes/rbac.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac40-admin.php {APP}/routes/admin.php && "
        f"php -l {APP}/includes/rbac.php && php -l {APP}/routes/admin.php && "
        "rm -f /tmp/rbac40.php /tmp/rbac40-admin.php /tmp/rbac40-seed.php"
    )
    print('BACKUP=' + backup)
    print('BASELINE=' + baseline)
    print('RESULT=' + result)
    print('AFTER=' + counts())
except Exception:
    try:
        run(
            f"test -f {backup}/cooling.db && cp {backup}/cooling.db {DB}; "
            f"test -f {backup}/rbac.php && cp {backup}/rbac.php {APP}/includes/rbac.php; "
            f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; "
            f"chown www-data:www-data {DB} {APP}/includes/rbac.php {APP}/routes/admin.php"
        )
    finally:
        client.close()
    raise

client.close()
