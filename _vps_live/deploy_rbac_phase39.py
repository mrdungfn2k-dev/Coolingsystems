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

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase39-{stamp}'
baseline = run(
    "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
    "print('|'.join(map(str,[c.execute('select count(*) from products').fetchone()[0],"
    "c.execute('select count(*) from users').fetchone()[0],"
    "c.execute('select count(*) from staff_roles').fetchone()[0],"
    "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
    "c.execute('select count(*) from rbac_capability_rules').fetchone()[0]])))\""
)

try:
    run(f"mkdir -p {backup} && cp {DB} {backup}/cooling.db && cp {APP}/includes/rbac.php {backup}/rbac.php && cp {APP}/routes/admin.php {backup}/admin.php")
    sftp = client.open_sftp()
    with sftp.open('/tmp/rbac39-admin.php', 'wb') as remote:
        with sftp.open(f'{APP}/routes/admin.php', 'rb') as current:
            routes = current.read().decode('utf-8')

        review_old = "get('/admin/reviews', function() {\n    $user = requireStaffPermission('reviews', '/admin/login');"
        review_new = "get('/admin/reviews', function() {\n    $user = requireRbacOrLegacyStaffPermission('crm.complaints.manage', '/admin/login');"
        routes = replace_once(routes, review_old, review_new, 'review gate')

        contact_guard = "    $u = currentUser();\n    if (!$u) { redirect('/admin/login'); return; }\n    if ($u['role'] !== 'admin') {\n        $perm = dbGet(\"SELECT can_contacts FROM staff_permissions WHERE user_id=?\", [$u['id']]);\n        if (!$perm || !$perm['can_contacts']) { http_response_code(403); echo '403 Forbidden'; return; }\n    }"
        contact_new = "    $u = requireRbacOrLegacyStaffPermission('crm.customer_care.manage', '/admin/login');"
        if routes.count(contact_guard) != 3:
            raise RuntimeError(f'contact guards: expected three matches, got {routes.count(contact_guard)}')
        routes = routes.replace(contact_guard, contact_new)
        remote.write(routes.encode('utf-8'))

    sftp.put(str(LOCAL / 'rbac.php'), '/tmp/rbac39.php')
    sftp.put(str(LOCAL / 'seed_rbac_phase39.php'), '/tmp/rbac39-seed.php')
    sftp.close()

    result = run(
        "runuser -u www-data -- php /tmp/rbac39-seed.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac39.php {APP}/includes/rbac.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac39-admin.php {APP}/routes/admin.php && "
        f"php -l {APP}/includes/rbac.php && php -l {APP}/routes/admin.php && "
        "rm -f /tmp/rbac39.php /tmp/rbac39-admin.php /tmp/rbac39-seed.php"
    )
    after = run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from products').fetchone()[0],"
        "c.execute('select count(*) from users').fetchone()[0],"
        "c.execute('select count(*) from staff_roles').fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
        "c.execute('select count(*) from rbac_capability_rules').fetchone()[0]])))\""
    )
    print('BACKUP=' + backup)
    print('BASELINE=' + baseline)
    print('RESULT=' + result)
    print('AFTER=' + after)
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
