import sys
from datetime import datetime

import paramiko

sys.stdout.reconfigure(encoding='utf-8')
APP = '/opt/coolingsystems'
routes_path = APP + '/routes/admin.php'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command):
    _, out, err = client.exec_command(command, timeout=90)
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


def write_remote(path, value):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(value.encode('utf-8'))
    sftp.close()


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/bank-reconciliation-hotfix-{stamp}'
try:
    run(f'mkdir -p {backup} && cp {routes_path} {backup}/admin.php')
    routes = read_remote(routes_path)
    if 'bank_reconciliation_created' not in routes:
        raise RuntimeError('P119 routes are not present.')
    if 'bank_reconciliation_transactions transaction' not in routes:
        raise RuntimeError('The expected SQL alias is not present or has already been fixed.')
    routes = routes.replace('bank_reconciliation_transactions transaction', 'bank_reconciliation_transactions recon')
    routes = routes.replace('transaction.', 'recon.')
    write_remote('/tmp/bank-reconciliation-hotfix-routes.php', routes)
    result = run(f'install -o www-data -g www-data -m 0644 /tmp/bank-reconciliation-hotfix-routes.php {routes_path} && php -l {routes_path} && rm -f /tmp/bank-reconciliation-hotfix-routes.php')
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('ALIAS_CHECK=' + run(f"grep -c 'bank_reconciliation_transactions transaction' {routes_path} || true"))
except Exception:
    run(f'test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; chown www-data:www-data {routes_path}; rm -f /tmp/bank-reconciliation-hotfix-routes.php')
    client.close()
    raise

client.close()
