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


routes_path = ROOT + '/routes/admin.php'
menu_path = ROOT + '/views/partials/dashboard-head.php'
rbac_path = ROOT + '/includes/rbac.php'
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase35-{stamp}'
baseline = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {routes_path} {backup}/admin.php; cp {menu_path} {backup}/dashboard-head.php; cp {rbac_path} {backup}/rbac.php")

try:
    routes = get(routes_path)
    replacements = [
      ("get('/admin/categories/export-csv', function() {\n    requireStaffPermission('categories', '/auth/login');", "get('/admin/categories/export-csv', function() {\n    requireStaffPermission('rbac:integration.data.export|categories', '/auth/login');", 'category export'),
      ("get('/admin/brands/export-csv', function() {\n    requireStaffPermission('brands', '/admin/login');", "get('/admin/brands/export-csv', function() {\n    requireStaffPermission('rbac:integration.data.export|brands', '/admin/login');", 'brand export'),
      ("get('/admin/product-brands/export-csv', function() {\n    requireStaffPermission('brand_models', '/auth/login');", "get('/admin/product-brands/export-csv', function() {\n    requireStaffPermission('rbac:integration.data.export|brand_models', '/auth/login');", 'part brand export'),
      ("post('/admin/categories/import-csv', function() {\n    requireStaffPermission('categories', '/auth/login');", "post('/admin/categories/import-csv', function() {\n    requireStaffPermission('rbac:integration.data.import|categories', '/auth/login');", 'category import'),
      ("post('/admin/brands/import-csv', function() {\n    requireStaffPermission('brands', '/admin/login');", "post('/admin/brands/import-csv', function() {\n    requireStaffPermission('rbac:integration.data.import|brands', '/admin/login');", 'brand import'),
      ("post('/admin/product-brands/import-csv', function() {\n    requireStaffPermission('brand_models', '/auth/login');", "post('/admin/product-brands/import-csv', function() {\n    requireStaffPermission('rbac:integration.data.import|brand_models', '/auth/login');", 'part brand import'),
      ("get('/admin/vouchers', function() {\n    $user = requireStaffPermission('vouchers', '/admin/login');", "get('/admin/vouchers', function() {\n    $user = requireStaffPermission('rbac:marketing.promotions.view|vouchers', '/admin/login');", 'voucher view'),
      ("get('/admin/brands', function() {\n    $user = requireStaffPermission('brands', '/admin/login');", "get('/admin/brands', function() {\n    $user = requireStaffPermission('rbac:catalog.vehicle.view|brands', '/admin/login');", 'brand view'),
      ("post('/admin/brands/add', function() {\n    requireStaffPermission('brands', '/admin/login');", "post('/admin/brands/add', function() {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login');", 'brand add'),
      ("post('/admin/brands/:id/edit', function($p) {\n    requireStaffPermission('brands', '/admin/login');", "post('/admin/brands/:id/edit', function($p) {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login');", 'brand edit'),
      ("post('/admin/brands/:id/delete', function($p) {\n    requireStaffPermission('brands', '/admin/login');", "post('/admin/brands/:id/delete', function($p) {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brands', '/admin/login');", 'brand delete'),
      ("get('/admin/categories', function() {\n    $user = requireStaffPermission('categories', '/auth/login');", "get('/admin/categories', function() {\n    $user = requireStaffPermission('rbac:catalog.taxonomy.view|categories', '/auth/login');", 'category view'),
      ("post('/admin/categories/add', function() {\n    requireStaffPermission('categories', '/auth/login');", "post('/admin/categories/add', function() {\n    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login');", 'category add'),
      ("post('/admin/categories/:id/edit', function($p) {\n    requireStaffPermission('categories', '/auth/login');", "post('/admin/categories/:id/edit', function($p) {\n    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login');", 'category edit'),
      ("post('/admin/categories/:id/delete', function($p) {\n    requireStaffPermission('categories', '/auth/login');", "post('/admin/categories/:id/delete', function($p) {\n    requireStaffPermission('rbac:catalog.taxonomy.manage|categories', '/auth/login');", 'category delete'),
      ("get('/admin/promotions', function() {\n    $user = requireStaffPermission('promotions', '/auth/login');", "get('/admin/promotions', function() {\n    $user = requireStaffPermission('rbac:marketing.promotions.view|promotions', '/auth/login');", 'promotion view'),
      ("post('/admin/promotions/:id/set-sale', function($p) {\n    requireStaffPermission('promotions', '/auth/login');", "post('/admin/promotions/:id/set-sale', function($p) {\n    requireStaffPermission('rbac:marketing.promotions.manage|promotions', '/auth/login');", 'promotion price'),
      ("post('/admin/promotions/:id/toggle', function($p) {\n    requireStaffPermission('promotions', '/auth/login');", "post('/admin/promotions/:id/toggle', function($p) {\n    requireStaffPermission('rbac:marketing.promotions.manage|promotions', '/auth/login');", 'promotion toggle'),
      ("get('/admin/product-brands', function() {\n    requireStaffPermission('brand_models', '/auth/login');", "get('/admin/product-brands', function() {\n    requireStaffPermission('rbac:catalog.vehicle.view|brand_models', '/auth/login');", 'part brand view'),
      ("post('/admin/product-brands/new', function() {\n    requireStaffPermission('brand_models', '/auth/login');", "post('/admin/product-brands/new', function() {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');", 'part brand add'),
      ("post('/admin/product-brands/:id/edit', function($p) {\n    requireStaffPermission('brand_models', '/auth/login');", "post('/admin/product-brands/:id/edit', function($p) {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');", 'part brand edit'),
      ("post('/admin/product-brands/:id/delete', function($p) {\n    requireStaffPermission('brand_models', '/auth/login');", "post('/admin/product-brands/:id/delete', function($p) {\n    requireStaffPermission('rbac:catalog.vehicle.manage|brand_models', '/auth/login');", 'part brand delete'),
      ("get('/admin/stores', function() {\n    requireStaffPermission('stores', '/auth/login');", "get('/admin/stores', function() {\n    requireStaffPermission('rbac:organization.branches.view|stores', '/auth/login');", 'store view'),
      ("get('/admin/branch-types', function() {\n    requireStaffPermission('stores', '/auth/login');", "get('/admin/branch-types', function() {\n    requireStaffPermission('rbac:organization.branches.view|stores', '/auth/login');", 'branch type view'),
      ("post('/admin/branch-types/add', function() {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/branch-types/add', function() {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'branch type add'),
      ("post('/admin/branch-types/:id/edit', function($p) {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/branch-types/:id/edit', function($p) {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'branch type edit'),
      ("post('/admin/branch-types/:id/delete', function($p) {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/branch-types/:id/delete', function($p) {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'branch type delete'),
      ("post('/admin/stores/add', function() {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/stores/add', function() {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'store add'),
      ("post('/admin/stores/:id/edit', function($p) {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/stores/:id/edit', function($p) {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'store edit'),
      ("post('/admin/stores/:id/delete', function($p) {\n    requireStaffPermission('stores', '/auth/login');", "post('/admin/stores/:id/delete', function($p) {\n    requireStaffPermission('rbac:organization.branches.manage|stores', '/auth/login');", 'store delete'),
    ]
    for old, new, label in replacements:
        routes = once(routes, old, new, label)
    put('/tmp/rbac-phase35.php', (LOCAL / 'rbac.php').read_text(encoding='utf-8'))
    put('/tmp/rbac-phase35-seed.php', (LOCAL / 'seed_rbac_phase35.php').read_text(encoding='utf-8'))
    put('/tmp/rbac-phase35-admin.php', routes)
    run('chown www-data:www-data /tmp/rbac-phase35.php /tmp/rbac-phase35-seed.php /tmp/rbac-phase35-admin.php; runuser -u www-data -- php /tmp/rbac-phase35-seed.php')
    run(f'install -o www-data -g www-data -m 0644 /tmp/rbac-phase35.php {rbac_path}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase35-admin.php {routes_path}; php -l {rbac_path}; php -l {routes_path}; rm -f /tmp/rbac-phase35.php /tmp/rbac-phase35-seed.php /tmp/rbac-phase35-admin.php')
except Exception:
    run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/admin.php {routes_path}; cp {backup}/dashboard-head.php {menu_path}; cp {backup}/rbac.php {rbac_path}; rm -f /tmp/rbac-phase35.php /tmp/rbac-phase35-seed.php /tmp/rbac-phase35-admin.php')
    raise

after = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP=' + backup)
print('BASELINE=' + baseline)
print('AFTER=' + after)
c.close()
