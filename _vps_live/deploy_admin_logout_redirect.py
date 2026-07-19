import sys
from datetime import datetime

import paramiko

sys.stdout.reconfigure(encoding='utf-8')

HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
VIEW = '/opt/coolingsystems/views/partials/dashboard-head.php'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)

def run(command, timeout=45):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    status = stdout.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{output}\n{error}')
    return output.strip()

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/admin-logout-{stamp}'

try:
    run(f'mkdir -p {backup} && cp {VIEW} {backup}/dashboard-head.php')
    sftp = client.open_sftp()
    with sftp.open(VIEW, 'rb') as current:
        content = current.read().decode('utf-8')
    old = "<a href=\"<?= $__isAdmin ? '/admin/logout' : '/staff/logout' ?>\">"
    new = '<a href="/admin/logout">'
    if content.count(old) != 1:
        raise RuntimeError(f'logout link: expected one match, got {content.count(old)}')
    with sftp.open('/tmp/dashboard-head-logout.php', 'wb') as remote:
        remote.write(content.replace(old, new, 1).encode('utf-8'))
    sftp.close()
    result = run(f'install -o www-data -g www-data -m 0644 /tmp/dashboard-head-logout.php {VIEW} && php -l {VIEW} && rm -f /tmp/dashboard-head-logout.php')
    print('BACKUP=' + backup)
    print('RESULT=' + result)
except Exception:
    try:
        run(f'test -f {backup}/dashboard-head.php && cp {backup}/dashboard-head.php {VIEW} && chown www-data:www-data {VIEW}')
    finally:
        client.close()
    raise

client.close()
