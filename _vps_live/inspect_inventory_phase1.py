import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

commands = {
    'audit_schema': "sqlite3 /var/lib/coolingsystems/cooling.db 'PRAGMA table_info(audit_logs);'",
    'products_view': "sed -n '180,245p' /opt/coolingsystems/views/admin/products.php",
    'form_js': "sed -n '750,845p' /opt/coolingsystems/views/admin/product-form.php",
    'edit_route': "sed -n '2400,2570p' /opt/coolingsystems/routes/admin.php",
    'create_route': "sed -n '2270,2445p' /opt/coolingsystems/routes/admin.php",
    'symlinks': "ls -ld /opt/coolingsystems/uploads /opt/coolingsystems/public/uploads /opt/coolingsystems/public/img/img; find /opt/coolingsystems/public/uploads -maxdepth 1 -type l -printf '%p -> %l\\n' 2>/dev/null",
    'stock_writes': "grep -RIn --include='*.php' -E 'UPDATE products SET.*stock|stock[[:space:]]*=' /opt/coolingsystems/routes /opt/coolingsystems/includes | head -120",
    'csrf': "grep -RIn -E 'function csrf(Field|Token)|function csrfCheck' /opt/coolingsystems/includes /opt/coolingsystems/routes | head -30",
}

for name, command in commands.items():
    _, stdout, stderr = client.exec_command(command, timeout=30)
    print(f'\n--- {name} ---')
    print(stdout.read().decode('utf-8', 'replace'))
    err = stderr.read().decode('utf-8', 'replace').strip()
    if err:
        print('STDERR:', err)

client.close()
