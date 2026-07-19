import sys
from datetime import datetime
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'


def replace_once(text, old, new, label):
    count = text.count(old)
    if count != 1:
        raise RuntimeError(f'{label}: expected one match, got {count}')
    return text.replace(old, new, 1)


c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command):
    _, out, err = c.exec_command(command, timeout=90)
    stdout = out.read().decode('utf-8', 'replace')
    stderr = err.read().decode('utf-8', 'replace')
    status = out.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{stdout}\n{stderr}')
    return stdout.strip()


def get(path):
    s = c.open_sftp()
    try:
        with s.open(path, 'rb') as f:
            return f.read().decode('utf-8')
    finally:
        s.close()


def put(path, content):
    s = c.open_sftp()
    try:
        with s.open(path, 'wb') as f:
            f.write(content.encode('utf-8'))
    finally:
        s.close()


routes_path = ROOT + '/routes/admin.php'
view_path = ROOT + '/views/admin/staff.php'
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase33-{stamp}'
baseline = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_staff_role_links);\"")
run(f"mkdir -p {backup}; cp {routes_path} {backup}/admin.php; cp {view_path} {backup}/staff.php")

try:
    routes = get(routes_path)
    view = get(view_path)
    routes = replace_once(
        routes,
        "get('/admin/staff/roles/:id/edit', function($p) {\n    $user = requireStaffPermission('promotions', '/auth/login');\n",
        "get('/admin/staff/roles/:id/edit', function($p) {\n    $user = requireStaffPermission('promotions', '/auth/login');\n    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau chi doc; khong the sua truc tiep.'); redirect('/admin/staff'); }\n",
        'protect template edit form',
    )
    routes = replace_once(
        routes,
        "post('/admin/staff/roles/:id/edit', function($p) {\n    requireRole('admin', '/admin/login'); csrfCheck();\n",
        "post('/admin/staff/roles/:id/edit', function($p) {\n    requireRole('admin', '/admin/login'); csrfCheck();\n    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau chi doc; khong the sua truc tiep.'); redirect('/admin/staff'); }\n",
        'protect template edit action',
    )
    routes = replace_once(
        routes,
        "post('/admin/staff/roles/:id/delete', function($p) {\n    requireRole('admin', '/admin/login'); csrfCheck();\n",
        "post('/admin/staff/roles/:id/delete', function($p) {\n    requireRole('admin', '/admin/login'); csrfCheck();\n    if (dbGet('SELECT 1 FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']])) { flash('error','Vai tro RBAC mau duoc bao ve; khong the xoa.'); redirect('/admin/staff'); }\n",
        'protect template delete',
    )
    view = replace_once(
        view,
        "$staffCount = dbGet(\"SELECT COUNT(*) as n FROM staff_role_assignments WHERE role_id=?\", [$r['id']])['n'] ?? 0;\n",
        "$staffCount = dbGet(\"SELECT COUNT(*) as n FROM staff_role_assignments WHERE role_id=?\", [$r['id']])['n'] ?? 0;\n      $rbacTemplate = dbGet(\"SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?\", [$r['id']]);\n",
        'template marker query',
    )
    view = replace_once(
        view,
        "      <td><strong style=\"color:var(--navy)\"><?= e($r['name']) ?></strong></td>\n",
        "      <td><strong style=\"color:var(--navy)\"><?= e($r['name']) ?></strong><?php if($rbacTemplate): ?><div class=\"fs-11\" style=\"color:#1565c0;font-weight:700;margin-top:3px\">RBAC matrix: <?= e($rbacTemplate['rbac_role_code']) ?> (read-only)</div><?php endif; ?></td>\n",
        'template marker display',
    )
    view = replace_once(
        view,
        "        <a href=\"/admin/staff/roles/<?= $r['id'] ?>/edit\" class=\"adm-edit\">Sửa</a>\n",
        "        <?php if(!$rbacTemplate): ?><a href=\"/admin/staff/roles/<?= $r['id'] ?>/edit\" class=\"adm-edit\">Sửa</a><?php endif; ?>\n",
        'hide template edit',
    )
    view = replace_once(
        view,
        "          <?= csrfField() ?><button type=\"submit\" class=\"adm-del\">Xóa</button>\n",
        "          <?php if(!$rbacTemplate): ?><?= csrfField() ?><button type=\"submit\" class=\"adm-del\">Xóa</button><?php endif; ?>\n",
        'hide template delete',
    )
    put('/tmp/rbac-phase33-admin.php', routes)
    put('/tmp/rbac-phase33-staff.php', view)
    run(f"chown www-data:www-data /tmp/rbac-phase33-admin.php /tmp/rbac-phase33-staff.php; install -o www-data -g www-data -m 0644 /tmp/rbac-phase33-admin.php {routes_path}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase33-staff.php {view_path}; php -l {routes_path}; php -l {view_path}; rm -f /tmp/rbac-phase33-admin.php /tmp/rbac-phase33-staff.php")
except Exception:
    run(f"cp {backup}/admin.php {routes_path}; cp {backup}/staff.php {view_path}; rm -f /tmp/rbac-phase33-admin.php /tmp/rbac-phase33-staff.php")
    raise

after = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_staff_role_links);\"")
print('BACKUP=' + backup)
print('BASELINE=' + baseline)
print('AFTER=' + after)
c.close()
