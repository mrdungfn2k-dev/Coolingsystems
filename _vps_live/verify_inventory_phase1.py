import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

checks = {
    'inventory_route': "grep -c \"get('/admin/inventory'\" /opt/coolingsystems/routes/admin.php",
    'inventory_post': "grep -c \"post('/admin/inventory/:id/update'\" /opt/coolingsystems/routes/admin.php",
    'menu_entry': "grep -c 'href=\"/admin/inventory\"' /opt/coolingsystems/views/partials/dashboard-head.php",
    'form_notice': "grep -c '_inventory_in_product_form' /opt/coolingsystems/views/admin/product-form.php",
    'list_price_column': "grep -c 'Giá bán (sau VAT)' /opt/coolingsystems/views/admin/products.php || true",
    'list_stock_column': "grep -c '<th>Kho</th>' /opt/coolingsystems/views/admin/products.php || true",
    'upload_dir': "test -d /var/lib/coolingsystems/uploads/products && test -w /var/lib/coolingsystems/uploads/products && echo writable",
    'clone_data': "sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT COUNT(*) || ' products; max stock=' || MAX(stock) || '; max stock limit=' || MAX(max_stock) FROM products;\"",
    'route_response': "curl -sS -o /dev/null -w '%{http_code} %{redirect_url}' https://coolingsystems.vn/admin/inventory",
    'public_product': "url=$(sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT '/products/' || slug FROM products WHERE status='published' AND slug<>'' LIMIT 1;\"); curl -sS -o /dev/null -w '%{http_code} ' https://coolingsystems.vn$url; echo $url",
    'errors': "journalctl -u coolingsystems.service --since '10 minutes ago' --no-pager | grep -Ei 'fatal|exception|error' | tail -20 || true",
}
for label, command in checks.items():
    _, out, err = c.exec_command(command, timeout=30)
    value = out.read().decode('utf-8', 'replace').strip()
    error = err.read().decode('utf-8', 'replace').strip()
    print(f'{label}: {value or "(empty)"}')
    if error:
        print(f'{label}_stderr: {error}')
c.close()
