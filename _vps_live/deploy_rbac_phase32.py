import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
LOCAL = Path('cooling-php/_vps_live')


def replace_once(text, old, new, label):
    count = text.count(old)
    if count != 1:
        raise RuntimeError(f'{label}: expected one match, got {count}')
    return text.replace(old, new, 1)


client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command, timeout=120):
    _, out, err = client.exec_command(command, timeout=timeout)
    output = out.read().decode('utf-8', 'replace')
    error = err.read().decode('utf-8', 'replace')
    status = out.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'Exit {status}: {command}\n{output}\n{error}')
    return output.strip()


def read_remote(path):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'rb') as handle:
            return handle.read().decode('utf-8')
    finally:
        sftp.close()


def write_remote(path, content):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'wb') as handle:
            handle.write(content.encode('utf-8'))
    finally:
        sftp.close()


paths = {
    'auth': ROOT + '/includes/auth.php',
    'menu': ROOT + '/views/partials/dashboard-head.php',
    'routes': ROOT + '/routes/admin.php',
    'rbac': ROOT + '/includes/rbac.php',
}
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase32-{stamp}'
baseline = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {paths['auth']} {backup}/auth.php; cp {paths['menu']} {backup}/dashboard-head.php; cp {paths['routes']} {backup}/admin.php")

try:
    auth = read_remote(paths['auth'])
    menu = read_remote(paths['menu'])
    routes = read_remote(paths['routes'])

    auth = replace_once(auth, "session_start();\n", "session_start();\nrequire_once __DIR__ . '/rbac.php';\n", 'auth include')
    auth = replace_once(
        auth,
        "        foreach (explode('|', $permission) as $__pp) { if (in_array($__pp, $perms)) return $user; }\n",
        "        foreach (explode('|', $permission) as $__pp) {\n            if (str_starts_with($__pp, 'rbac:') && rbacHasCapability((int)$user['id'], substr($__pp, 5))) return $user;\n            if (in_array($__pp, $perms, true)) return $user;\n        }\n",
        'permission gateway',
    )
    menu = replace_once(
        menu,
        "$sb = function($perm) use ($__isAdmin, $__perms) { if ($__isAdmin) return true; return in_array($perm, $__perms, true); };",
        "$sb = function($perm) use ($__isAdmin, $__perms, $__sbU) {\n  if ($__isAdmin || in_array($perm, $__perms, true)) return true;\n  return $__sbU && (($__sbU['role'] ?? '') === 'staff') && function_exists('rbacMenuCan') && rbacMenuCan((int)$__sbU['id'], $perm);\n};",
        'menu gateway',
    )
    menu = replace_once(menu, "<?php if($sb('products')||$sb('categories')||$sb('brand_models')||$sb('brands')): ?>", "<?php if($sb('products')||$sb('inventory')||$sb('products_create')||$sb('categories')||$sb('brand_models')||$sb('brands')): ?>", 'product section')
    menu = replace_once(menu, "<?php if($sb('orders')||$sb('returns')): ?>", "<?php if($sb('orders')||$sb('returns')||$sb('create_order')): ?>", 'operations section')

    replacements = [
        ("get('/admin/products', function() {    requireStaffPermission('products', '/admin/login');", "get('/admin/products', function() {    requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');", 'product list'),
        ("post('/admin/products/reorder-images', function() {\n    requireStaffPermission('products', '/admin/login');", "post('/admin/products/reorder-images', function() {\n    requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');", 'image reorder'),
        ("get('/admin/products/export-csv', function() {\n    requireStaffPermission('products', '/admin/login');", "get('/admin/products/export-csv', function() {\n    requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');", 'product export'),
        ("post('/admin/products/import-csv', function() {\n    requireStaffPermission('products', '/admin/login');", "post('/admin/products/import-csv', function() {\n    requireStaffPermission('rbac:catalog.products.import|products', '/admin/login');", 'product import'),
        ("get('/admin/inventory', function() {\n    requireStaffPermission('products', '/admin/login');", "get('/admin/inventory', function() {\n    requireStaffPermission('rbac:inventory.view|products', '/admin/login');", 'inventory view'),
        ("post('/admin/inventory/:id/update', function($p) {\n    $user=requireStaffPermission('products','/admin/login');", "post('/admin/inventory/:id/update', function($p) {\n    $user=requireStaffPermission('rbac:inventory.update|products','/admin/login');", 'inventory update'),
        ("get('/admin/products/new', function() {\n    $user = requireStaffPermission('products', '/admin/login');", "get('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login');", 'product create page'),
        ("post('/admin/products/new', function() {\n    $user = requireStaffPermission('products', '/admin/login');", "post('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login');", 'product create'),
        ("get('/admin/products/:id/edit', function($p) {\n    $user = requireStaffPermission('products', '/admin/login');", "get('/admin/products/:id/edit', function($p) {\n    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');", 'product edit page'),
        ("post('/admin/products/:id/edit', function($p) {\n    $user = requireStaffPermission('products', '/admin/login');", "post('/admin/products/:id/edit', function($p) {\n    $user = requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');", 'product edit'),
        ("get('/admin/products/:id/history', function($p) {\n    $user = requireStaffPermission('products', '/admin/login');", "get('/admin/products/:id/history', function($p) {\n    $user = requireStaffPermission('rbac:catalog.products.view|products', '/admin/login');", 'product history'),
        ("post('/admin/products/:id/toggle-status', function($p) {\n    requireStaffPermission('products', '/admin/login');", "post('/admin/products/:id/toggle-status', function($p) {\n    requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login');", 'product status'),
        ("post('/admin/products/:id/delete', function($p) {\n    requireStaffPermission('products', '/admin/login');", "post('/admin/products/:id/delete', function($p) {\n    requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login');", 'product delete'),
        ("post('/admin/products/bulk-delete', function() {\n    $user = requireStaffPermission('products', '/admin/login');", "post('/admin/products/bulk-delete', function() {\n    $user = requireStaffPermission('rbac:catalog.products.archive|products', '/admin/login');", 'bulk product delete'),
        ("post('/admin/products/delete-image', function() {\n    requireStaffPermission('products', '/admin/login');", "post('/admin/products/delete-image', function() {\n    requireStaffPermission('rbac:catalog.products.edit|products', '/admin/login');", 'product image delete'),
        ("get('/admin/orders', function() {\n    requireStaffPermission('orders', '/admin/login');", "get('/admin/orders', function() {\n    requireStaffPermission('rbac:sales.orders.view|orders', '/admin/login');", 'order list'),
        ("get('/admin/orders/create', function() {\n    $user = requireStaffPermission('create_order', '/admin/login');", "get('/admin/orders/create', function() {\n    $user = requireStaffPermission('rbac:sales.orders.create|create_order|orders', '/admin/login');", 'order create page'),
        ("get('/admin/orders/:id', function($p) {\n    requireStaffPermission('orders', '/admin/login');", "get('/admin/orders/:id', function($p) {\n    requireStaffPermission('rbac:sales.orders.view|orders', '/admin/login');", 'order detail'),
        ("post('/admin/orders/create', function() {\n    $user = requireStaffPermission('orders', '/admin/login');", "post('/admin/orders/create', function() {\n    $user = requireStaffPermission('rbac:sales.orders.create|orders', '/admin/login');", 'order create'),
        ("post('/admin/orders/:id/return/:return_id', function($p) {\n    requireStaffPermission('orders', '/admin/login');", "post('/admin/orders/:id/return/:return_id', function($p) {\n    requireStaffPermission('rbac:sales.returns.approve|orders', '/admin/login');", 'inline return'),
        ("post('/admin/orders/:id/payment-status', function($p) {\n    requireStaffPermission('orders', '/admin/login');", "post('/admin/orders/:id/payment-status', function($p) {\n    requireStaffPermission('rbac:sales.payment.collect|orders', '/admin/login');", 'payment'),
        ("post('/admin/orders/:id/delivery-status', function($p) {\n    requireStaffPermission('orders', '/admin/login');", "post('/admin/orders/:id/delivery-status', function($p) {\n    requireStaffPermission('rbac:sales.delivery.update|orders', '/admin/login');", 'delivery'),
        ("get('/admin/returns', function() {\n    $user = requireStaffPermission('returns', '/auth/login');", "get('/admin/returns', function() {\n    $user = requireStaffPermission('rbac:sales.returns.view|returns', '/auth/login');", 'return list'),
        ("post('/admin/returns/:id/approve', function($p) {\n    requireStaffPermission('returns', '/auth/login');", "post('/admin/returns/:id/approve', function($p) {\n    requireStaffPermission('rbac:sales.returns.approve|returns', '/auth/login');", 'return approval'),
        ("post('/admin/returns/:id/reject', function($p) {\n    requireStaffPermission('returns', '/auth/login');", "post('/admin/returns/:id/reject', function($p) {\n    requireStaffPermission('rbac:sales.returns.approve|returns', '/auth/login');", 'return reject'),
    ]
    for old, new, label in replacements:
        routes = replace_once(routes, old, new, label)

    write_remote('/tmp/rbac.php', (LOCAL / 'rbac.php').read_text(encoding='utf-8'))
    write_remote('/tmp/rbac-seed.php', (LOCAL / 'seed_rbac_phase32.php').read_text(encoding='utf-8'))
    write_remote('/tmp/rbac-auth.php', auth)
    write_remote('/tmp/rbac-menu.php', menu)
    write_remote('/tmp/rbac-admin.php', routes)
    run('chown www-data:www-data /tmp/rbac.php /tmp/rbac-seed.php /tmp/rbac-auth.php /tmp/rbac-menu.php /tmp/rbac-admin.php; runuser -u www-data -- php /tmp/rbac-seed.php')
    run(f'install -o www-data -g www-data -m 0644 /tmp/rbac.php {paths["rbac"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-auth.php {paths["auth"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-menu.php {paths["menu"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-admin.php {paths["routes"]}; php -l {paths["rbac"]}; php -l {paths["auth"]}; php -l {paths["menu"]}; php -l {paths["routes"]}')
    run('rm -f /tmp/rbac.php /tmp/rbac-seed.php /tmp/rbac-auth.php /tmp/rbac-menu.php /tmp/rbac-admin.php')
except Exception:
    run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/auth.php {paths["auth"]}; cp {backup}/dashboard-head.php {paths["menu"]}; cp {backup}/admin.php {paths["routes"]}; rm -f {paths["rbac"]} /tmp/rbac.php /tmp/rbac-seed.php /tmp/rbac-auth.php /tmp/rbac-menu.php /tmp/rbac-admin.php')
    raise

after = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders),(SELECT COUNT(*) FROM rbac_staff_role_links),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP=' + backup)
print('BASELINE=' + baseline)
print('AFTER=' + after)
client.close()
