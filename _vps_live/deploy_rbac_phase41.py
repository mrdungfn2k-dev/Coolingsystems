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
backup = f'/var/backups/coolingsystems/rbac-phase41-{stamp}'
baseline = counts()

try:
    run(f"mkdir -p {backup} && cp {DB} {backup}/cooling.db && cp {APP}/includes/rbac.php {backup}/rbac.php && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/partials/dashboard-head.php {backup}/dashboard-head.php")
    sftp = client.open_sftp()
    with sftp.open(f'{APP}/routes/admin.php', 'rb') as current:
        routes = current.read().decode('utf-8')

    replacements = [
        ("get('/admin/settings', function() {\n    $user = requireRole('admin', '/admin/login');", "get('/admin/settings', function() {\n    $user = requireStaffPermission('rbac:system.settings.view', '/admin/login');", 'settings view'),
        ("post('/admin/settings/smtp', function() {\n    requireRole('admin', '/admin/login');", "post('/admin/settings/smtp', function() {\n    requireStaffPermission('rbac:system.smtp.manage', '/admin/login');", 'smtp settings'),
        ("post('/admin/settings/inventory-alert', function() {\n    requireRole('admin', '/admin/login');", "post('/admin/settings/inventory-alert', function() {\n    requireStaffPermission('rbac:inventory.alerts.manage', '/admin/login');", 'inventory alert settings'),
        ("post('/admin/settings/inventory-alert/test', function() {\n    requireRole('admin', '/admin/login');", "post('/admin/settings/inventory-alert/test', function() {\n    requireStaffPermission('rbac:inventory.alerts.manage', '/admin/login');", 'inventory alert test'),
        ("post('/admin/settings/general', function() {\n    requireRole('admin', '/admin/login');", "post('/admin/settings/general', function() {\n    requireStaffPermission('rbac:system.business.manage', '/admin/login');", 'general settings'),
        ("post('/admin/settings/social', function() {\n    requireStaffPermission('tax_config', '/auth/login');", "post('/admin/settings/social', function() {\n    requireStaffPermission('rbac:system.social.manage|tax_config', '/auth/login');", 'social settings'),
        ("post('/admin/settings/payment', function() {\n    requireStaffPermission('tax_config', '/auth/login');", "post('/admin/settings/payment', function() {\n    requireStaffPermission('rbac:system.payment.manage|tax_config', '/auth/login');", 'payment settings'),
    ]
    for old, new, label in replacements:
        routes = replace_once(routes, old, new, label)

    with sftp.open(f'{APP}/views/partials/dashboard-head.php', 'rb') as current:
        sidebar = current.read().decode('utf-8')
    sidebar = replace_once(sidebar, "<?php if($__isAdmin): ?><a href=\"/admin/settings\"", "<?php if($__isAdmin||$sb('settings')): ?><a href=\"/admin/settings\"", 'settings menu')

    with sftp.open('/tmp/rbac41-admin.php', 'wb') as remote:
        remote.write(routes.encode('utf-8'))
    with sftp.open('/tmp/rbac41-dashboard-head.php', 'wb') as remote:
        remote.write(sidebar.encode('utf-8'))
    sftp.put(str(LOCAL / 'rbac.php'), '/tmp/rbac41.php')
    sftp.put(str(LOCAL / 'seed_rbac_phase41.php'), '/tmp/rbac41-seed.php')
    sftp.close()

    result = run(
        "runuser -u www-data -- php /tmp/rbac41-seed.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac41.php {APP}/includes/rbac.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac41-admin.php {APP}/routes/admin.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac41-dashboard-head.php {APP}/views/partials/dashboard-head.php && "
        f"php -l {APP}/includes/rbac.php && php -l {APP}/routes/admin.php && php -l {APP}/views/partials/dashboard-head.php && "
        "rm -f /tmp/rbac41.php /tmp/rbac41-admin.php /tmp/rbac41-dashboard-head.php /tmp/rbac41-seed.php"
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
            f"test -f {backup}/dashboard-head.php && cp {backup}/dashboard-head.php {APP}/views/partials/dashboard-head.php; "
            f"chown www-data:www-data {DB} {APP}/includes/rbac.php {APP}/routes/admin.php {APP}/views/partials/dashboard-head.php"
        )
    finally:
        client.close()
    raise

client.close()
