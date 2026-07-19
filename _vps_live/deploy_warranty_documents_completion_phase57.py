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


def protect_documents(source, start_marker, end_marker):
    start = source.index(start_marker)
    end = source.index(end_marker, start)
    block = source[start:end]
    missing_case = "if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}"
    completion_guard = "if($case['status']!=='completed'){flash('error',html_entity_decode('Ch&#7913;ng t&#7915; ch&#7881; hi&#7875;n th&#7883; sau khi phi&#7871;u &#273;&#227; nghi&#7879;m thu.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}"
    if block.count(missing_case) != 1 or completion_guard in block:
        raise RuntimeError('Warranty document route structure is unexpected.')
    block = block.replace(missing_case, missing_case + completion_guard, 1)
    return source[:start] + block + source[end:]


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-documents-completion-{stamp}'
routes_path = APP + '/routes/admin.php'
view_path = APP + '/views/admin/warranties.php'
hub_marker = "get('/admin/warranties/:id/documents', function($p) {"
print_marker = "get('/admin/warranties/:id/documents/:type', function($p) {"
materials_marker = "get('/admin/warranties/material-products', function() {"

try:
    run(f"mkdir -p {backup} && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/warranties.php")
    routes = read_remote(routes_path)
    if routes.count(hub_marker) != 1 or routes.count(print_marker) != 1 or routes.count(materials_marker) != 1:
        raise RuntimeError('Warranty document route markers are invalid.')
    routes = protect_documents(routes, hub_marker, print_marker)
    routes = protect_documents(routes, print_marker, materials_marker)
    write_remote('/tmp/warranty-phase57-routes.php', routes)

    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'warranties_phase56.php'), '/tmp/warranty-phase57-warranties.php')
    sftp.close()

    result = run(
        f"install -o www-data -g www-data -m 0644 /tmp/warranty-phase57-routes.php {routes_path} "
        f"&& install -o www-data -g www-data -m 0644 /tmp/warranty-phase57-warranties.php {view_path} "
        f"&& php -l {routes_path} && php -l {view_path} && rm -f /tmp/warranty-phase57-*"
    )
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('DOCUMENTS_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties/1/documents"))
    print('ROUTE_GUARDS=' + run(f"grep -o \"Ch&#7913;ng t&#7915; ch&#7881; hi&#7875;n th&#7883;[^']*\" {routes_path} | wc -l"))
except Exception:
    run(
        f"test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; "
        f"test -f {backup}/warranties.php && cp {backup}/warranties.php {view_path}; "
        f"rm -f /tmp/warranty-phase57-*; chown www-data:www-data {routes_path} {view_path}"
    )
    client.close()
    raise

client.close()
