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


def replace_once(source, old, new, label):
    count = source.count(old)
    if count != 1:
        raise RuntimeError(f'{label}: expected one match, got {count}')
    return source.replace(old, new, 1)


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase45-{stamp}'

try:
    run(f"mkdir -p {backup} && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/staff.php {backup}/staff.php")
    routes = read_remote(f'{APP}/routes/admin.php')
    coverage_route = r'''get('/admin/staff/rbac/coverage', function() {
    requireRbacOrLegacyStaffPermission('system.rbac.view', '/admin/login');
    $coverage = dbAll("SELECT permission.code,permission.module_name,permission.feature_name,permission.action_name,GROUP_CONCAT(DISTINCT rule.capability) AS capabilities FROM rbac_permissions permission LEFT JOIN rbac_capability_rules rule ON rule.permission_code=permission.code GROUP BY permission.code ORDER BY permission.sort_order");
    $summary = dbGet("SELECT COUNT(*) AS total, SUM(CASE WHEN EXISTS(SELECT 1 FROM rbac_capability_rules rule WHERE rule.permission_code=permission.code) THEN 1 ELSE 0 END) AS integrated FROM rbac_permissions permission") ?: ['total'=>0,'integrated'=>0];
    $summary['pending'] = (int)$summary['total'] - (int)$summary['integrated'];
    view('admin/rbac-coverage', ['title'=>'Bản đồ triển khai quyền RBAC','userRole'=>'admin','coverage'=>$coverage,'summary'=>$summary]);
});

'''
    routes = replace_once(routes, "get('/admin/staff/roles/:id/permissions', function($p) {", coverage_route + "get('/admin/staff/roles/:id/permissions', function($p) {", 'coverage route')
    staff = read_remote(f'{APP}/views/admin/staff.php')
    staff = replace_once(staff, '<a href="/admin/staff/roles/new" class="btn btn-navy">+ Tạo vai trò mới</a>', '<div style="display:flex;gap:8px;flex-wrap:wrap"><a href="/admin/staff/rbac/coverage" class="btn btn-outline-navy">Bản đồ quyền</a><a href="/admin/staff/roles/new" class="btn btn-navy">+ Tạo vai trò mới</a></div>', 'staff header actions')
    put_text('/tmp/rbac45-admin.php', routes)
    put_text('/tmp/rbac45-staff.php', staff)
    sftp = client.open_sftp()
    try:
        sftp.put(str(LOCAL / 'rbac-coverage_phase45.php'), '/tmp/rbac45-coverage.php')
    finally:
        sftp.close()
    result = run(
        "chown www-data:www-data /tmp/rbac45-admin.php /tmp/rbac45-staff.php /tmp/rbac45-coverage.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac45-admin.php {APP}/routes/admin.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac45-staff.php {APP}/views/admin/staff.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac45-coverage.php {APP}/views/admin/rbac-coverage.php && "
        f"php -l {APP}/routes/admin.php && php -l {APP}/views/admin/staff.php && php -l {APP}/views/admin/rbac-coverage.php && "
        "rm -f /tmp/rbac45-admin.php /tmp/rbac45-staff.php /tmp/rbac45-coverage.php"
    )
    print('BACKUP=' + backup)
    print('RESULT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('COVERAGE=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/staff/rbac/coverage"))
except Exception:
    try:
        run(
            f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; "
            f"test -f {backup}/staff.php && cp {backup}/staff.php {APP}/views/admin/staff.php; "
            f"rm -f {APP}/views/admin/rbac-coverage.php /tmp/rbac45-admin.php /tmp/rbac45-staff.php /tmp/rbac45-coverage.php; "
            f"chown www-data:www-data {APP}/routes/admin.php {APP}/views/admin/staff.php"
        )
    finally:
        client.close()
    raise

client.close()
