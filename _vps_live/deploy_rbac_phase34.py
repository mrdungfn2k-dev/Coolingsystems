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


def replace_input(line, field, flag):
    if f'name="{field}"' not in line:
        raise RuntimeError(f'Missing inventory input: {field}')
    return line.replace(' value="<?= (int)$product', f' <?= {flag} ? \'\' : \'readonly\' ?> value="<?= (int)$product', 1)


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


def put(path, text):
    s = c.open_sftp()
    try:
        with s.open(path, 'wb') as f:
            f.write(text.encode('utf-8'))
    finally:
        s.close()


paths = {
    'rbac': ROOT + '/includes/rbac.php',
    'routes': ROOT + '/routes/admin.php',
    'view': ROOT + '/views/admin/inventory.php',
}
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase34-{stamp}'
baseline = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {paths['rbac']} {backup}/rbac.php; cp {paths['routes']} {backup}/admin.php; cp {paths['view']} {backup}/inventory.php")

try:
    routes = get(paths['routes'])
    view = get(paths['view'])
    rbac = (LOCAL / 'rbac.php').read_text(encoding='utf-8')

    routes = once(
        routes,
        "get('/admin/inventory', function() {\n    requireStaffPermission('rbac:inventory.view|products', '/admin/login');",
        "get('/admin/inventory', function() {\n    $user = requireStaffPermission('rbac:inventory.view|products', '/admin/login');",
        'inventory view user',
    )
    routes = once(
        routes,
        "    $categories=dbAll('SELECT id,name FROM categories ORDER BY sort_order,name');\n",
        "    $categories=dbAll('SELECT id,name FROM categories ORDER BY sort_order,name');\n"
        "    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);\n"
        "    $inventoryPermissions = [\n"
        "      'detailed'=>$detailedRbac,\n"
        "      'view_cost'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.cost.view'),\n"
        "      'edit_cost'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.cost.edit'),\n"
        "      'edit_price'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.pricing.edit'),\n"
        "      'edit_stock'=>!$detailedRbac || rbacCan((int)$user['id'], 'inventory.update'),\n"
        "      'edit_thresholds'=>!$detailedRbac || rbacCan((int)$user['id'], 'inventory.thresholds.edit'),\n"
        "      'edit_warranty'=>!$detailedRbac || rbacCan((int)$user['id'], 'catalog.products.edit'),\n"
        "    ];\n",
        'inventory permission flags',
    )
    routes = once(routes, "'categoryId'=>$categoryId,'page'=>$page", "'categoryId'=>$categoryId,'inventoryPermissions'=>$inventoryPermissions,'page'=>$page", 'inventory view data')
    routes = once(
        routes,
        "$user=requireStaffPermission('rbac:inventory.update|products','/admin/login'); csrfCheck();",
        "$user=requireStaffPermission('rbac:inventory.update|rbac:catalog.pricing.edit|rbac:catalog.cost.edit|rbac:inventory.thresholds.edit|rbac:catalog.products.edit|products','/admin/login'); csrfCheck();",
        'inventory action gateway',
    )
    routes = once(
        routes,
        "    if($values['stock']>1000||$values['min_stock']>1000||$values['max_stock']>1000){",
        "    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);\n"
        "    if ($detailedRbac) {\n"
        "        $fieldCapabilities = [\n"
        "          'cost_price'=>'catalog.cost.edit','price'=>'catalog.pricing.edit','original_price'=>'catalog.pricing.edit',\n"
        "          'stock'=>'inventory.update','min_stock'=>'inventory.thresholds.edit','max_stock'=>'inventory.thresholds.edit',\n"
        "          'warranty_months'=>'catalog.products.edit'\n"
        "        ];\n"
        "        foreach ($fieldCapabilities as $field=>$capability) {\n"
        "            if ((int)$product[$field] !== $values[$field] && !rbacCan((int)$user['id'], $capability)) {\n"
        "                flash('error','Ban khong co quyen thay doi truong du lieu nay.'); redirect('/admin/inventory'); return;\n"
        "            }\n"
        "        }\n"
        "    }\n"
        "    if($values['stock']>1000||$values['min_stock']>1000||$values['max_stock']>1000){",
        'inventory field authorization',
    )

    view = once(
        view,
        "<?php require __DIR__.'/../partials/dashboard-head.php'; ?>\n",
        "<?php require __DIR__.'/../partials/dashboard-head.php'; ?>\n<?php\n"
        "$inventoryPermissions = $inventoryPermissions ?? [];\n"
        "$canViewCost = !empty($inventoryPermissions['view_cost']);\n"
        "$canEditCost = !empty($inventoryPermissions['edit_cost']);\n"
        "$canEditPrice = !empty($inventoryPermissions['edit_price']);\n"
        "$canEditStock = !empty($inventoryPermissions['edit_stock']);\n"
        "$canEditThresholds = !empty($inventoryPermissions['edit_thresholds']);\n"
        "$canEditWarranty = !empty($inventoryPermissions['edit_warranty']);\n"
        "$canSaveInventory = $canEditCost || $canEditPrice || $canEditStock || $canEditThresholds || $canEditWarranty;\n"
        "?>\n",
        'inventory template flags',
    )
    header_match = re.search(r'<thead><tr>(.*?)</tr></thead>', view, re.DOTALL)
    if not header_match:
        raise RuntimeError('inventory header not found')
    header_cells = re.findall(r'<th>.*?</th>', header_match.group(1), re.DOTALL)
    if len(header_cells) != 11:
        raise RuntimeError(f'expected 11 inventory header cells, got {len(header_cells)}')
    header_cells[2] = '<?php if($canViewCost): ?>' + header_cells[2] + '<?php endif; ?>'
    new_header = '<thead><tr>' + ''.join(header_cells) + '</tr></thead>'
    view = view[:header_match.start()] + new_header + view[header_match.end():]

    lines = view.splitlines()
    changed = []
    for line in lines:
        if 'name="cost_price"' in line:
            visible = replace_input(line, 'cost_price', '$canEditCost')
            hidden = '    <td class="num"><input form="<?= $formId ?>" type="hidden" name="cost_price" value="<?= (int)$product[\'cost_price\'] ?>"><span>--</span></td>'
            changed.append('<?php if($canViewCost): ?>' + visible + '<?php else: ?>' + hidden + '<?php endif; ?>')
        elif 'name="price"' in line:
            changed.append(replace_input(line, 'price', '$canEditPrice'))
        elif 'name="original_price"' in line:
            changed.append(replace_input(line, 'original_price', '$canEditPrice'))
        elif 'name="stock"' in line:
            changed.append(replace_input(line, 'stock', '$canEditStock'))
        elif 'name="min_stock"' in line:
            changed.append(replace_input(line, 'min_stock', '$canEditThresholds'))
        elif 'name="max_stock"' in line:
            changed.append(replace_input(line, 'max_stock', '$canEditThresholds'))
        elif 'name="warranty_months"' in line:
            changed.append(replace_input(line, 'warranty_months', '$canEditWarranty'))
        elif 'class="save" type="submit"' in line:
            changed.append('<?php if($canSaveInventory): ?>' + line + '<?php endif; ?>')
        else:
            changed.append(line)
    view = '\n'.join(changed) + ('\n' if view.endswith('\n') else '')
    view = once(view, 'colspan="11"', 'colspan="<?= $canViewCost ? 11 : 10 ?>"', 'inventory empty colspan')

    put('/tmp/rbac-phase34.php', rbac)
    put('/tmp/rbac-phase34-seed.php', (LOCAL / 'seed_rbac_phase34.php').read_text(encoding='utf-8'))
    put('/tmp/rbac-phase34-admin.php', routes)
    put('/tmp/rbac-phase34-inventory.php', view)
    run('chown www-data:www-data /tmp/rbac-phase34.php /tmp/rbac-phase34-seed.php /tmp/rbac-phase34-admin.php /tmp/rbac-phase34-inventory.php; runuser -u www-data -- php /tmp/rbac-phase34-seed.php')
    run(f'install -o www-data -g www-data -m 0644 /tmp/rbac-phase34.php {paths["rbac"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase34-admin.php {paths["routes"]}; install -o www-data -g www-data -m 0644 /tmp/rbac-phase34-inventory.php {paths["view"]}; php -l {paths["rbac"]}; php -l {paths["routes"]}; php -l {paths["view"]}; rm -f /tmp/rbac-phase34.php /tmp/rbac-phase34-seed.php /tmp/rbac-phase34-admin.php /tmp/rbac-phase34-inventory.php')
except Exception:
    run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/rbac.php {paths["rbac"]}; cp {backup}/admin.php {paths["routes"]}; cp {backup}/inventory.php {paths["view"]}; rm -f /tmp/rbac-phase34.php /tmp/rbac-phase34-seed.php /tmp/rbac-phase34-admin.php /tmp/rbac-phase34-inventory.php')
    raise

after = run("sqlite3 -separator '|' " + DB + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP=' + backup)
print('BASELINE=' + baseline)
print('AFTER=' + after)
c.close()
