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
        value = handle.read().decode('utf-8')
    sftp.close()
    return value


try:
    print('TABLES')
    print(run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE '%cash%' OR name LIKE '%payment%' OR name LIKE '%transaction%' OR name LIKE '%finance%' OR name LIKE '%order%') ORDER BY name;\""))
    print('FINANCE_ROUTES')
    routes = read_remote(APP + '/routes/admin.php')
    for needle in ['/admin/cash', '/admin/finance', '/admin/payments', '/admin/orders']:
        print(needle + '=' + str(routes.count(needle)))
    print('ORDER_PAYMENT_COLUMNS')
    print(run(f"sqlite3 {DB} \"PRAGMA table_info(orders);\""))
    print('PAYMENT_TABLE_COLUMNS')
    print(run(f"sqlite3 {DB} \"PRAGMA table_info(order_payments); PRAGMA table_info(payments);\""))
    print('PAYMENT_SAMPLES')
    print(run(f"sqlite3 -separator '|' {DB} \"SELECT code,payment_method,payment_type,payment_status,paid_amount,refund_amount,created_at,completed_at FROM orders ORDER BY id DESC LIMIT 12;\""))
    print('RBAC_FINANCE')
    print(run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability LIKE 'finance.%' OR permission_code BETWEEN 'P114' AND 'P127' ORDER BY permission_code,capability;\""))
    print('MENU_FINANCE')
    print(run(f"grep -nE \"Quỹ|Tài chính|Công nợ|cash|finance\" {APP}/views/partials/dashboard-head.php || true"))
    print('MENU_ORDER_MARKERS')
    header = read_remote(APP + '/views/partials/dashboard-head.php')
    start = header.find("$sb('orders')")
    print(header[max(0, start - 180):start + 420])
finally:
    client.close()
