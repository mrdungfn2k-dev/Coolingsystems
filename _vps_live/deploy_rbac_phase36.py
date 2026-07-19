import re
import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
LOCAL = Path('cooling-php/_vps_live')


def once(text, old, new, label):
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


def put(path, data):
    s = c.open_sftp()
    try:
        with s.open(path, 'wb') as f:
            f.write(data.encode('utf-8'))
    finally:
        s.close()


paths = {'auth':ROOT + '/includes/auth.php', 'rbac':ROOT + '/includes/rbac.php', 'routes':ROOT + '/routes/admin.php'}
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase36-{stamp}'
baseline = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {paths['auth']} {backup}/auth.php; cp {paths['rbac']} {backup}/rbac.php; cp {paths['routes']} {backup}/admin.php")

try:
    auth = get(paths['auth'])
    routes = get(paths['routes'])
    auth = once(
        auth,
        "// True if this user has at least one staff role assignment (i.e. is an ACTIVE assigned staff).\n",
        "function requireRbacOrLegacyStaffPermission(string $capability, string $redirect = '/auth/login'): array {\n"
        "    $user = requireLogin($redirect);\n"
        "    if (($user['role'] ?? '') === 'admin') return $user;\n"
        "    if (($user['role'] ?? '') === 'staff') {\n"
        "        if (!rbacUsesDetailedMode((int)$user['id']) || rbacHasCapability((int)$user['id'], $capability)) return $user;\n"
        "    }\n"
        "    header('Location: ' . $redirect); exit;\n"
        "}\n\n"
        "// True if this user has at least one staff role assignment (i.e. is an ACTIVE assigned staff).\n",
        'detailed staff gateway',
    )
    old_chat = "requireRole(['admin','staff'], '/admin/login');"
    chat_count = routes.count(old_chat)
    if chat_count != 7:
        raise RuntimeError(f'chat routes: expected seven generic staff checks, got {chat_count}')
    routes = routes.replace(old_chat, "requireRbacOrLegacyStaffPermission('crm.engagement.manage', '/admin/login');")
    routes = once(
        routes,
        "post('/admin/orders/:id/cancel', function($p) {\n    requireRole(['admin','staff'], '/admin');\n    csrfCheck();\n    $order = dbGet(\"SELECT o.*, u.id as uid FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?\", [$p['id']]);",
        "post('/admin/orders/:id/cancel', function($p) {\n    $user = requireRbacOrLegacyStaffPermission('sales.orders.cancel', '/admin');\n    csrfCheck();\n    $order = dbGet(\"SELECT o.*, u.id as uid FROM orders o LEFT JOIN users u ON u.id=o.user_id WHERE o.id=?\", [$p['id']]);",
        'order cancel gateway',
    )
    cancel_pattern = re.compile(r"(    if \(!\$order\) \{ flash\('error','[^']*'\); redirect\('/admin/orders'\); return; \}\n)(    \$reason = trim\(\$_POST\['cancel_reason'\].*;)")
    cancel_replacement = (
        r"\1"
        "    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id'])) {\n"
        "        $afterPaymentOrDispatch = (($order['payment_status'] ?? '') === 'paid') || in_array(($order['delivery_status'] ?? ''), ['delivering','shipping','delivered','shipped','completed'], true);\n"
        "        if ($afterPaymentOrDispatch && !rbacCan((int)$user['id'], 'sales.orders.cancel_approved')) { flash('error','Ban khong co quyen huy don sau thanh toan hoac giao hang.'); redirect('/admin/orders'); return; }\n"
        "    }\n"
        r"\2"
    )
    routes, cancel_count = cancel_pattern.subn(cancel_replacement, routes, count=1)
    if cancel_count != 1:
        raise RuntimeError(f'post-payment cancellation approval: expected one match, got {cancel_count}')
    put('/tmp/rbac-phase36-auth.php', auth)
    put('/tmp/rbac-phase36-rbac.php', (LOCAL / 'rbac.php').read_text(encoding='utf-8'))
    put('/tmp/rbac-phase36-seed.php', (LOCAL / 'seed_rbac_phase36.php').read_text(encoding='utf-8'))
    put('/tmp/rbac-phase36-admin.php', routes)
    run('chown www-data:www-data /tmp/rbac-phase36-auth.php /tmp/rbac-phase36-rbac.php /tmp/rbac-phase36-seed.php /tmp/rbac-phase36-admin.php; runuser -u www-data -- php /tmp/rbac-phase36-seed.php')
    run(f'install -o www-data -g www-data -m 0644 /tmp/rbac-phase36-auth.php {paths["auth"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase36-rbac.php {paths["rbac"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase36-admin.php {paths["routes"]}; php -l {paths["auth"]}; php -l {paths["rbac"]}; php -l {paths["routes"]}; rm -f /tmp/rbac-phase36-auth.php /tmp/rbac-phase36-rbac.php /tmp/rbac-phase36-seed.php /tmp/rbac-phase36-admin.php')
except Exception:
    run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/auth.php {paths["auth"]}; cp {backup}/rbac.php {paths["rbac"]}; cp {backup}/admin.php {paths["routes"]}; rm -f /tmp/rbac-phase36-auth.php /tmp/rbac-phase36-rbac.php /tmp/rbac-phase36-seed.php /tmp/rbac-phase36-admin.php')
    raise

after = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP=' + backup)
print('BASELINE=' + baseline)
print('AFTER=' + after)
c.close()
