import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = Path('cooling-php/_vps_live')
APP = '/opt/coolingsystems'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

def run(command):
    _, out, err = client.exec_command(command, timeout=90)
    stdout, stderr = out.read().decode('utf-8', 'replace'), err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-material-button-{stamp}'
try:
    run(f"mkdir -p {backup} && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'warranties_phase50.php'), '/tmp/warranty-phase55-view.php')
    sftp.close()
    result = run(f"chown www-data:www-data /tmp/warranty-phase55-view.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase55-view.php {APP}/views/admin/warranties.php && php -l {APP}/views/admin/warranties.php && rm -f /tmp/warranty-phase55-view.php")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
except Exception:
    run(f"test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f /tmp/warranty-phase55-view.php; chown www-data:www-data {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
