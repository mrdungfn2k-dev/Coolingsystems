import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)
commands = {
    'settings_routes': "grep -n \"/admin/settings\" /opt/coolingsystems/routes/admin.php",
    'settings_view': "sed -n '1,280p' /opt/coolingsystems/views/admin/settings.php",
    'mailer': "sed -n '1,260p' /opt/coolingsystems/includes/mailer.php",
    'bootstrap': "grep -RIn --include='*.php' \"mailer.php\|includes/mailer\" /opt/coolingsystems | head -30",
    'stock_contexts': "for n in 800 930 1230 1255 1385 1695 3850; do echo ==== $n ====; sed -n \"${n},$((n+35))p\" /opt/coolingsystems/routes/admin.php; done; echo ==== customer ====; sed -n '800,835p' /opt/coolingsystems/routes/customer.php; sed -n '935,965p' /opt/coolingsystems/routes/customer.php",
    'settings_schema': "sqlite3 /var/lib/coolingsystems/cooling.db 'PRAGMA table_info(settings);'",
}
for label, command in commands.items():
    _, out, err = c.exec_command(command, timeout=60)
    print(f'\n--- {label} ---')
    print(out.read().decode('utf-8', 'replace'))
    error = err.read().decode('utf-8', 'replace').strip()
    if error: print('STDERR:', error)
c.close()
