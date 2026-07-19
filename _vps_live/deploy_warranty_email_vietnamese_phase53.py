import sys
from datetime import datetime
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
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
backup = f'/var/backups/coolingsystems/warranty-email-vietnamese-{stamp}'
replacements = {
    ">Phieu bao hanh</h2>": ">Phi&#7871;u b&#7843;o h&#224;nh</h2>",
    "<p>Xin chao <strong>": "<p>Xin ch&#224;o <strong>",
    "Cooling System da tiep nhan phieu bao hanh cua ban.": "Cooling System &#273;&#227; ti&#7871;p nh&#7853;n phi&#7871;u b&#7843;o h&#224;nh c&#7911;a b&#7841;n.",
    "<strong>Ma phieu</strong>": "<strong>M&#227; phi&#7871;u</strong>",
    "<strong>San pham</strong>": "<strong>S&#7843;n ph&#7849;m</strong>",
    "<strong>Ngay mua</strong>": "<strong>Ng&#224;y mua</strong>",
    "<strong>Han bao hanh</strong>": "<strong>H&#7841;n b&#7843;o h&#224;nh</strong>",
    "<strong>Noi dung yeu cau:</strong>": "<strong>N&#7897;i dung y&#234;u c&#7847;u:</strong>",
    "Chung toi se lien he sau khi kiem tra.": "Ch&#250;ng t&#244;i s&#7869; li&#234;n h&#7879; sau khi ki&#7875;m tra.",
    "'Phieu bao hanh '.$code.' - Cooling System'": "html_entity_decode('Phi&#7871;u b&#7843;o h&#224;nh',ENT_QUOTES,'UTF-8').' '.$code.' - Cooling System'",
    "_emailLayout('Phieu bao hanh',$emailBody)": "_emailLayout(html_entity_decode('Phi&#7871;u b&#7843;o h&#224;nh',ENT_QUOTES,'UTF-8'),$emailBody)",
}

try:
    run(f"mkdir -p {backup} && cp {APP}/routes/admin.php {backup}/admin.php")
    routes = read_remote(APP + '/routes/admin.php')
    for old, new in replacements.items():
        if routes.count(old) != 1:
            raise RuntimeError('Email wording marker is missing or ambiguous: ' + old)
        routes = routes.replace(old, new, 1)
    write_remote('/tmp/warranty-phase53-routes.php', routes)
    result = run(f"chown www-data:www-data /tmp/warranty-phase53-routes.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase53-routes.php {APP}/routes/admin.php && php -l {APP}/routes/admin.php && rm -f /tmp/warranty-phase53-routes.php")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
except Exception:
    run(f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; rm -f /tmp/warranty-phase53-routes.php; chown www-data:www-data {APP}/routes/admin.php")
    client.close()
    raise
client.close()
