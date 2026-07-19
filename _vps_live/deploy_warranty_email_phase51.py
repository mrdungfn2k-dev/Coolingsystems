import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = Path('cooling-php/_vps_live')
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'

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

def replace_once(source, old, new, label):
    if source.count(old) != 1:
        raise RuntimeError(label + ': expected one source marker.')
    return source.replace(old, new, 1)

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-email-{stamp}'
old_input = "$product=dbGet('SELECT id,warranty_months FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);$customerName=trim($_POST['customer_name']??'');$phone=preg_replace('/\\D+/','',$_POST['customer_phone']??'');$issue=trim($_POST['issue_description']??'');"
new_input = "$product=dbGet('SELECT id,warranty_months,name,sku FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);$customerName=trim($_POST['customer_name']??'');$phone=preg_replace('/\\D+/','',$_POST['customer_phone']??'');$customerEmail=strtolower(trim($_POST['customer_email']??''));$issue=trim($_POST['issue_description']??'');"
old_phone_check = "if(!preg_match('/^0[3-9]\\d{8}$/',$phone)){flash('error','So dien thoai phai gom 10 chu so va bat dau tu 03 den 09.');redirect('/admin/warranties');}"
new_phone_check = "if(!preg_match('/^0[35789]\\d{8}$/',$phone)){flash('error','So dien thoai phai gom 10 chu so va bat dau bang 03, 05, 07, 08 hoac 09.');redirect('/admin/warranties');}if(!filter_var($customerEmail,FILTER_VALIDATE_EMAIL)||mb_strlen($customerEmail)>254){flash('error','Email khach hang khong hop le.');redirect('/admin/warranties');}"
old_columns = '(case_code,product_id,order_code,customer_name,customer_phone,serial_no,issue_description,purchase_date,warranty_end_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)'
new_columns = '(case_code,product_id,order_code,customer_name,customer_phone,customer_email,serial_no,issue_description,purchase_date,warranty_end_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
old_values = "$customerName,$phone,trim($_POST['serial_no']??''),$issue,$purchase,$end,'received',$actor['id']"
new_values = "$customerName,$phone,$customerEmail,trim($_POST['serial_no']??''),$issue,$purchase,$end,'received',$actor['id']"
email_tail = """$emailBody='<h2 style="color:#1a3258;margin:0 0 12px">Phieu bao hanh</h2><p>Xin chao <strong>'.htmlspecialchars($customerName,ENT_QUOTES,'UTF-8').'</strong>,</p><p>Cooling System da tiep nhan phieu bao hanh cua ban.</p><table cellpadding="6" cellspacing="0" style="border-collapse:collapse;width:100%"><tr><td><strong>Ma phieu</strong></td><td>'.htmlspecialchars($code,ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>San pham</strong></td><td>'.htmlspecialchars($product['name'],ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>SKU</strong></td><td>'.htmlspecialchars($product['sku'],ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>Ngay mua</strong></td><td>'.htmlspecialchars($purchase,ENT_QUOTES,'UTF-8').'</td></tr><tr><td><strong>Han bao hanh</strong></td><td>'.htmlspecialchars($end,ENT_QUOTES,'UTF-8').'</td></tr></table><p><strong>Noi dung yeu cau:</strong><br>'.nl2br(htmlspecialchars($issue,ENT_QUOTES,'UTF-8')).'</p><p>Chung toi se lien he sau khi kiem tra.</p>';$mailSent=sendEmail($customerEmail,'Phieu bao hanh '.$code.' - Cooling System',_emailLayout('Phieu bao hanh',$emailBody));if($mailSent){flash('success',"\\u{0110}\\u{00E3} t\\u{1EA1}o phi\\u{1EBF}u b\\u{1EA3}o h\\u{00E0}nh ".$code." v\\u{00E0} \\u{0111}\\u{00E3} g\\u{1EED}i email cho kh\\u{00E1}ch h\\u{00E0}ng.");}else{flash('warning',"\\u{0110}\\u{00E3} t\\u{1EA1}o phi\\u{1EBF}u b\\u{1EA3}o h\\u{00E0}nh ".$code." nh\\u{01B0}ng ch\\u{01B0}a g\\u{1EED}i \\u{0111}\\u{01B0}\\u{1EE3}c email. H\\u{00E3}y ki\\u{1EC3}m tra SMTP.");}redirect('/admin/warranties');"""

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    routes = read_remote(APP + '/routes/admin.php')
    routes = replace_once(routes, old_input, new_input, 'warranty input validation')
    routes = replace_once(routes, old_phone_check, new_phone_check, 'phone validation')
    routes = replace_once(routes, old_columns, new_columns, 'warranty insert columns')
    routes = replace_once(routes, old_values, new_values, 'warranty insert values')
    audit_marker = "'warranty_case_created'"
    audit_at = routes.find(audit_marker)
    if audit_at < 0:
        raise RuntimeError('warranty audit marker missing.')
    tail_start = routes.find(']);flash(', audit_at)
    tail_end = routes.find("redirect('/admin/warranties');", tail_start)
    if tail_start < 0 or tail_end < 0:
        raise RuntimeError('warranty success tail marker missing.')
    routes = routes[:tail_start + 3] + email_tail + routes[tail_end + len("redirect('/admin/warranties');"):]
    write_remote('/tmp/warranty-phase51-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'warranties_phase50.php'), '/tmp/warranty-phase51-view.php')
    sftp.close()
    columns = run(f"sqlite3 {DB} \"PRAGMA table_info(warranty_cases);\"")
    if '|customer_email|' not in columns:
        run(f"sqlite3 {DB} \"ALTER TABLE warranty_cases ADD COLUMN customer_email TEXT NOT NULL DEFAULT '';\"")
    result = run(f"chown www-data:www-data /tmp/warranty-phase51-* && install -o www-data -g www-data -m 0644 /tmp/warranty-phase51-routes.php {APP}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase51-view.php {APP}/views/admin/warranties.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/warranties.php && rm -f /tmp/warranty-phase51-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('SCHEMA=' + run(f"sqlite3 {DB} \"PRAGMA table_info(warranty_cases);\" | grep customer_email"))
    print('PRODUCT_API_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' 'https://coolingsystems.vn/admin/warranties/products?q=test'"))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f /tmp/warranty-phase51-*; chown www-data:www-data {DB} {APP}/routes/admin.php {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
