import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'

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


def read_remote(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        content = handle.read().decode('utf-8')
    sftp.close()
    return content


try:
    print('TABLES')
    print(run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE '%warranty%' OR name LIKE '%staff%' OR name LIKE '%user%') ORDER BY name;\""))
    print('WARRANTY_SCHEMA')
    print(run(f"sqlite3 {DB} \"PRAGMA table_info(warranty_cases);\""))
    print('WARRANTY_STATUS_COUNTS')
    print(run(f"sqlite3 -separator '|' {DB} \"SELECT status,COUNT(*) FROM warranty_cases GROUP BY status ORDER BY status;\""))
    print('PERFORMANCE_ROUTES')
    print(run(f"grep -nE \"warrant(y|ies).*(performance|report|kpi)|performance.*warrant\" {APP}/routes/admin.php || true"))
    print('WARRANTY_MENU')
    print(run(f"grep -nE \"warrant(y|ies)\" {APP}/views/partials/dashboard-head.php || true"))
    print('CAPABILITY')
    print(run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability LIKE 'warranty.%' ORDER BY capability;\""))
    print('WARRANTY_ROUTE_SNIPPETS')
    routes = read_remote(APP + '/routes/admin.php')
    start = routes.find("get('/admin/warranties', function()")
    end = routes.find("get('/admin/serials', function()", start)
    print(routes[start:end][:16000])
    print('USER_SCHEMA')
    print(run(f"sqlite3 {DB} \"PRAGMA table_info(users);\""))
    print('STAFF_USERS')
    print(run(f"sqlite3 -separator '|' {DB} \"SELECT * FROM users WHERE role IN ('staff','admin') ORDER BY id LIMIT 30;\""))
finally:
    client.close()
