import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = Path('cooling-php/_vps_live')
APP = '/opt/coolingsystems'
HOST = '103.97.134.164'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username='root', password='lcBFDjVF15', timeout=30)

def run(command):
    _, out, err = client.exec_command(command, timeout=90)
    stdout, stderr = out.read().decode('utf-8', 'replace'), err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()

def read_remote(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        result = handle.read().decode('utf-8')
    sftp.close()
    return result

def write_remote(path, value):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(value.encode('utf-8'))
    sftp.close()

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-validation-{stamp}'
endpoint = """get('/admin/warranties/products', function() { requireStaffPermission('rbac:warranty.cases.view|returns','/admin/login'); $q=trim($_GET['q']??''); header('Content-Type: application/json; charset=utf-8'); if(mb_strlen($q)<2){echo '[]';exit;} $like='%'.$q.'%'; $rows=dbAll('SELECT id,sku,oem_code,name FROM products WHERE name LIKE ? OR sku LIKE ? OR oem_code LIKE ? ORDER BY name LIMIT 12',[$like,$like,$like]); echo json_encode(array_map(fn($row)=>['id'=>(int)$row['id'],'label'=>trim($row['sku'].' | '.$row['name'].(!empty($row['oem_code'])?' | OEM: '.$row['oem_code']:''))],$rows),JSON_UNESCAPED_UNICODE); exit; });
"""
route_start = "$actor=requireStaffPermission('rbac:warranty.cases.create|returns','/admin/login');csrfCheck();$product=dbGet('SELECT id,warranty_months FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);"
purchase_marker = '$purchase='
validation = """$actor=requireStaffPermission('rbac:warranty.cases.create|returns','/admin/login');csrfCheck();$product=dbGet('SELECT id,warranty_months FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);$customerName=trim($_POST['customer_name']??'');$phone=preg_replace('/\\D+/','',$_POST['customer_phone']??'');$issue=trim($_POST['issue_description']??'');$words=$issue===''?0:count(preg_split('/\\s+/u',$issue,-1,PREG_SPLIT_NO_EMPTY));
    if(!$product){flash('error','Chon san pham hop le.');redirect('/admin/warranties');}
    if($customerName===''||mb_strlen($customerName)>100){flash('error','Ten khach hang phai tu 1 den 100 ky tu.');redirect('/admin/warranties');}
    if(!preg_match('/^0[3-9]\\d{8}$/',$phone)){flash('error','So dien thoai phai gom 10 chu so va bat dau tu 03 den 09.');redirect('/admin/warranties');}
    if($words<1||$words>200){flash('error','Noi dung yeu cau phai tu 1 den 200 tu.');redirect('/admin/warranties');}
"""

try:
    run(f"mkdir -p {backup} && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    routes = read_remote(APP + '/routes/admin.php')
    get_marker = "get('/admin/warranties', function() {"
    if routes.count(get_marker) != 1 or routes.count(route_start) != 1 or routes.count(purchase_marker) != 1:
        raise RuntimeError('Warranty route markers are not unique; no deployment was applied.')
    routes = routes.replace(get_marker, endpoint + get_marker, 1)
    before_purchase, after_purchase = routes.split(purchase_marker, 1)
    before_validation, _ = before_purchase.split(route_start, 1)
    routes = before_validation + route_start + validation[len(route_start):] + purchase_marker + after_purchase
    raw_values = "trim($_POST['customer_name']??''),trim($_POST['customer_phone']??''),trim($_POST['serial_no']??''),trim($_POST['issue_description']??'')"
    if routes.count(raw_values) != 1:
        raise RuntimeError('Warranty insert values marker missing; no deployment was applied.')
    routes = routes.replace(raw_values, "$customerName,$phone,trim($_POST['serial_no']??''),$issue", 1)
    write_remote('/tmp/warranty-phase50-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'warranties_phase50.php'), '/tmp/warranty-phase50-view.php')
    sftp.close()
    result = run(f"chown www-data:www-data /tmp/warranty-phase50-* && install -o www-data -g www-data -m 0644 /tmp/warranty-phase50-routes.php {APP}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase50-view.php {APP}/views/admin/warranties.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/warranties.php && rm -f /tmp/warranty-phase50-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('PRODUCT_API_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' 'https://coolingsystems.vn/admin/warranties/products?q=test'"))
except Exception:
    run(f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f /tmp/warranty-phase50-*; chown www-data:www-data {APP}/routes/admin.php {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
