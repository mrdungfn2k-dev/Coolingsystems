import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

sql = "SELECT key, CASE WHEN key='smtp_password' AND value<>'' THEN '[saved]' ELSE value END FROM settings WHERE key IN ('inventory_alert_enabled','inventory_alert_email','smtp_enabled','smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_password','smtp_from_email') ORDER BY key;"
command = "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"" + sql + "\""
_, output, error = client.exec_command(command, timeout=30)
print(output.read().decode('utf-8', 'replace'))
print(error.read().decode('utf-8', 'replace'))
client.close()
