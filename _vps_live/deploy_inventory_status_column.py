import paramiko
import sys
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')
local_path = 'cooling-php/_vps_live/phase1_inventory_view.php'
remote_path = '/opt/coolingsystems/views/admin/inventory.php'

with open(local_path, encoding='utf-8') as f:
    content = f.read()

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)
stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/inventory-status-column-{stamp}.php'
_, out, err = client.exec_command(f'cp {remote_path} {backup} && php -l {remote_path}')
if out.channel.recv_exit_status() != 0:
    raise RuntimeError(err.read().decode('utf-8', 'replace'))
sftp = client.open_sftp()
with sftp.open(remote_path, 'wb') as f:
    f.write(content.encode('utf-8'))
sftp.close()
_, out, err = client.exec_command(f'chown coolingsystems:www-data {remote_path} && php -l {remote_path} && curl -sS -o /dev/null -w "%{{http_code}}" https://coolingsystems.vn/admin/inventory')
result = out.read().decode('utf-8', 'replace').strip()
if out.channel.recv_exit_status() != 0:
    raise RuntimeError(result + '\n' + err.read().decode('utf-8', 'replace'))
print('BACKUP=' + backup)
print(result)
client.close()
