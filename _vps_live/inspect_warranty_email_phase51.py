import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

commands = {
    'mailer_loaders': "grep -R \"mailer.php\" -n /opt/coolingsystems --include='*.php'",
    'warranty_schema': "sqlite3 /var/lib/coolingsystems/cooling.db \"PRAGMA table_info(warranty_cases);\"",
    'warranty_route': "grep -n \"admin/warranties\|warranty_cases\" /opt/coolingsystems/routes/admin.php",
}
for label, command in commands.items():
    _, out, err = client.exec_command(command, timeout=30)
    print('--- ' + label + ' ---')
    print(out.read().decode('utf-8', 'replace') + err.read().decode('utf-8', 'replace'))
client.close()
