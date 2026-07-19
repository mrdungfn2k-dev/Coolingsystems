import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command):
    _, out, err = client.exec_command(command, timeout=60)
    stdout = out.read().decode('utf-8', 'replace')
    stderr = err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()


db = '/var/lib/coolingsystems/cooling.db'
print('TABLES=' + run(f"sqlite3 -separator '|' {db} \"SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE '%order%' OR name LIKE '%customer%' OR name LIKE '%debt%' OR name LIKE '%ledger%') ORDER BY name;\""))
for table in ['orders', 'customers', 'users', 'cash_ledger_entries']:
    exists = run(f"sqlite3 {db} \"SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='{table}';\"")
    if exists == '1':
        print(table.upper() + '_COLUMNS=' + run(f"sqlite3 -separator '|' {db} \"PRAGMA table_info({table});\""))
client.close()
