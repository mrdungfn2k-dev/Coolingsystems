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
backup = f'/var/backups/coolingsystems/warranty-workflow-{stamp}'
route_marker = "post('/admin/warranties/:id/status', function($p) {"
update_marker = '    dbRun("UPDATE warranty_cases SET status=?'
workflow = """    $actor=requireStaffPermission('rbac:warranty.cases.view|returns','/admin/login');csrfCheck();$status=$_POST['status']??'';$case=dbGet('SELECT id,status FROM warranty_cases WHERE id=?',[$p['id']]);
    if(!$case){flash('error','Khong tim thay phieu bao hanh.');redirect('/admin/warranties');}
    $current=$case['status'];$rank=['received'=>0,'checking'=>1,'approved'=>2,'assigned'=>3,'in_progress'=>4,'completed'=>5];
    if(in_array($current,['completed','rejected'],true)){flash('error','Phieu da ket thuc, khong the thay doi trang thai.');redirect('/admin/warranties');}
    if($status!=='rejected'&&(!isset($rank[$status])||!isset($rank[$current])||$rank[$status]<=$rank[$current])){flash('error','Khong the quay lai hoac luu lai buoc truoc do.');redirect('/admin/warranties');}
    $cap=['checking'=>'warranty.eligibility.check','approved'=>'warranty.approve','assigned'=>'warranty.assign','in_progress'=>'warranty.progress.update','completed'=>'warranty.close','rejected'=>'warranty.approve'][$status]??null;if(!$cap||!rbacHasCapability((int)$actor['id'],$cap)){if(($actor['role']??'')!=='admin'){flash('error','Ban khong co quyen cap nhat trang thai nay.');redirect('/admin/warranties');}}
"""

try:
    run(f"mkdir -p {backup} && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    routes = read_remote(APP + '/routes/admin.php')
    if routes.count(route_marker) != 1 or routes.count(update_marker) != 1:
        raise RuntimeError('Warranty status route markers are not unique; no deployment was applied.')
    start = routes.index(route_marker) + len(route_marker)
    end = routes.index(update_marker, start)
    routes = routes[:start] + '\n' + workflow + routes[end:]
    write_remote('/tmp/warranty-phase52-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'warranties_phase50.php'), '/tmp/warranty-phase52-view.php')
    sftp.close()
    result = run(f"chown www-data:www-data /tmp/warranty-phase52-* && install -o www-data -g www-data -m 0644 /tmp/warranty-phase52-routes.php {APP}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase52-view.php {APP}/views/admin/warranties.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/warranties.php && rm -f /tmp/warranty-phase52-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('WARRANTIES_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties"))
except Exception:
    run(f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f /tmp/warranty-phase52-*; chown www-data:www-data {APP}/routes/admin.php {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
