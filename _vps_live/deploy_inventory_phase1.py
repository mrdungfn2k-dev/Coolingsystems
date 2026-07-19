import io
import re
import sys
from datetime import datetime

import paramiko

sys.stdout.reconfigure(encoding='utf-8')

HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
ROOT = '/opt/coolingsystems'

LOCAL = {
    'routes': 'cooling-php/_vps_live/phase1_inventory_routes.php',
    'view': 'cooling-php/_vps_live/phase1_inventory_view.php',
    'panel': 'cooling-php/_vps_live/phase1_product_inventory_hidden.php',
    'menu': 'cooling-php/_vps_live/phase1_menu_inventory.php',
}

def text(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()

def replace_once(source, old, new, label):
    count = source.count(old)
    if count != 1:
        raise RuntimeError(f'{label}: expected exactly one match, found {count}')
    return source.replace(old, new, 1)

def run(client, command, timeout=60):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    code = stdout.channel.recv_exit_status()
    if code:
        raise RuntimeError(f'Command failed ({code}): {command}\n{output}\n{error}')
    return output.strip()

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/inventory-phase1-{stamp}'
run(client, f"mkdir -p {backup} && cp {ROOT}/routes/admin.php {backup}/admin.php && cp {ROOT}/views/admin/product-form.php {backup}/product-form.php && cp {ROOT}/views/admin/products.php {backup}/products.php && cp {ROOT}/views/partials/dashboard-head.php {backup}/dashboard-head.php && sqlite3 /var/lib/coolingsystems/cooling.db '.backup {backup}/cooling.db'")

sftp = client.open_sftp()
remote_files = {
    'routes': f'{ROOT}/routes/admin.php',
    'form': f'{ROOT}/views/admin/product-form.php',
    'products': f'{ROOT}/views/admin/products.php',
    'menu': f'{ROOT}/views/partials/dashboard-head.php',
}

def read_remote(path):
    with sftp.open(path, 'rb') as f:
        return f.read().decode('utf-8')

def write_remote(path, value):
    with sftp.open(path, 'wb') as f:
        f.write(value.encode('utf-8'))

routes = read_remote(remote_files['routes'])
form = read_remote(remote_files['form'])
products = read_remote(remote_files['products'])
menu = read_remote(remote_files['menu'])

inventory_routes = text(LOCAL['routes'])
if "get('/admin/inventory'" not in routes:
    marker = "// ── PRODUCTS (Admin posts directly) ────────────────────────────────────────"
    routes = replace_once(routes, marker, inventory_routes + '\n' + marker, 'insert inventory routes')

# Product creation is now allowed without a price or stock quantity. Such new records
# begin as drafts and are completed on the dedicated inventory screen.
routes = replace_once(routes, "$d = $_POST;\n    $name = trim($d['name'] ?? '');", "$d = $_POST;\n    $inventoryManagedSeparately = empty($d['_inventory_in_product_form']);\n    $name = trim($d['name'] ?? '');", 'new product inventory flag')
routes = replace_once(routes, "$status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';\n    $slug", "$status = in_array($d['status']??'', ['draft','published']) ? $d['status'] : 'draft';\n    if ($inventoryManagedSeparately) $status = 'draft';\n    $slug", 'new product draft status')
routes = replace_once(routes, "    if ($price <= 0) $valErrors[] = 'Giá bán sau VAT phải lớn hơn 0';\n    if ($stockRaw === '') $valErrors[] = 'Tồn kho hiện tại không được để trống';\n    elseif (!ctype_digit($stockRaw) || $stock > 1000) $valErrors[] = 'Tồn kho hiện tại chỉ được từ 0 đến 1000';\n    if ($maxStockRaw !== '' && (!ctype_digit($maxStockRaw) || $maxStock > 1000)) $valErrors[] = 'Tồn kho tối đa chỉ được từ 0 đến 1000';", "    if (!$inventoryManagedSeparately) {\n        if ($price <= 0) $valErrors[] = 'Giá bán sau VAT phải lớn hơn 0';\n        if ($stockRaw === '') $valErrors[] = 'Tồn kho hiện tại không được để trống';\n        elseif (!ctype_digit($stockRaw) || $stock > 1000) $valErrors[] = 'Tồn kho hiện tại chỉ được từ 0 đến 1000';\n        if ($maxStockRaw !== '' && (!ctype_digit($maxStockRaw) || $maxStock > 1000)) $valErrors[] = 'Tồn kho tối đa chỉ được từ 0 đến 1000';\n    }", 'new product inventory validation')
old_upload_path = "        $uploadDir = '/opt/cooling-php/uploads/products/';"
if routes.count(old_upload_path) != 2:
    raise RuntimeError(f'product upload paths: expected two matches, found {routes.count(old_upload_path)}')
routes = routes.replace(old_upload_path, "        $uploadDir = '/var/lib/coolingsystems/uploads/products/';")

panel_start = '      <!-- GIÁ & TỒN KHO -->'
panel_end = '    </div><!-- /sidebar -->'
start = routes  # keeps linting tools from treating the variables as unrelated across files
del start
form_start = form.find(panel_start)
form_end = form.find(panel_end, form_start)
if form_start < 0 or form_end < 0:
    raise RuntimeError('product form inventory panel boundaries were not found')
form = form[:form_start] + text(LOCAL['panel']) + '\n' + form[form_end:]

old_client_validation = "  if (!price || parseInt(price) <= 0) errors.push('• Giá bán sau VAT phải lớn hơn 0');\n  if (stock === '' || stock === null) errors.push('• Tồn kho hiện tại không được để trống');\n  else if (!/^\\d+$/.test(stock) || parseInt(stock, 10) > 1000) errors.push('• Tồn kho hiện tại chỉ được từ 0 đến 1000');\n  var maxStock = (document.querySelector('input[name=\"max_stock\"]')?.value || '').trim();\n  if (maxStock !== '' && (!/^\\d+$/.test(maxStock) || parseInt(maxStock, 10) > 1000)) errors.push('• Tồn kho tối đa chỉ được từ 0 đến 1000');"
new_client_validation = "  var inventoryManagedSeparately = document.querySelector('input[name=\"_inventory_in_product_form\"]')?.value === '0';\n  if (!inventoryManagedSeparately) {\n    if (!price || parseInt(price) <= 0) errors.push('• Giá bán sau VAT phải lớn hơn 0');\n    if (stock === '' || stock === null) errors.push('• Tồn kho hiện tại không được để trống');\n    else if (!/^\\d+$/.test(stock) || parseInt(stock, 10) > 1000) errors.push('• Tồn kho hiện tại chỉ được từ 0 đến 1000');\n    var maxStock = (document.querySelector('input[name=\"max_stock\"]')?.value || '').trim();\n    if (maxStock !== '' && (!/^\\d+$/.test(maxStock) || parseInt(maxStock, 10) > 1000)) errors.push('• Tồn kho tối đa chỉ được từ 0 đến 1000');\n  }"
form = replace_once(form, old_client_validation, new_client_validation, 'client inventory validation')

products = replace_once(products, "  <th>Giá bán (sau VAT)</th>\n  <th>Kho</th>\n", '', 'product table inventory headers')
products = replace_once(products, "  <td><?=vnd($p['price'])?></td>\n  <td><?=$p['stock']?></td>\n", '', 'product table inventory cells')

if '/admin/inventory' not in menu:
    product_link = re.search(r'(?m)^(.*<a href="/admin/products"[^\n]*Quản lý sản phẩm</a>)$', menu)
    if not product_link:
        raise RuntimeError('sidebar product link not found')
    menu = menu[:product_link.end()] + '\n' + text(LOCAL['menu']).rstrip() + menu[product_link.end():]

write_remote(remote_files['routes'], routes)
write_remote(remote_files['form'], form)
write_remote(remote_files['products'], products)
write_remote(remote_files['menu'], menu)
sftp.put(LOCAL['view'], f'{ROOT}/views/admin/inventory.php')
sftp.close()

run(client, f"chown coolingsystems:www-data {ROOT}/routes/admin.php {ROOT}/views/admin/product-form.php {ROOT}/views/admin/products.php {ROOT}/views/admin/inventory.php {ROOT}/views/partials/dashboard-head.php")
for path in [remote_files['routes'], remote_files['form'], remote_files['products'], f'{ROOT}/views/admin/inventory.php', remote_files['menu']]:
    print(run(client, f'php -l {path}'))
print('BACKUP=' + backup)
print('ROUTE=' + run(client, "curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn/admin/inventory"))
print('HOME=' + run(client, "curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
print('SERVICE=' + run(client, 'systemctl is-active coolingsystems.service'))
client.close()
