import json
import secrets
import sys
from datetime import datetime

import paramiko

sys.stdout.reconfigure(encoding='utf-8')
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
EMAIL = 'rbac-test-technician-phase58@coolingsystems.vn'
NAME = 'Tài khoản kỹ thuật kiểm thử'
NOTES = 'Tài khoản kiểm thử nội bộ cho giai đoạn hiệu suất kỹ thuật P113.'
PASSWORD = secrets.token_urlsafe(24)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command, timeout=90):
    _, out, err = client.exec_command(command, timeout=timeout)
    stdout = out.read().decode('utf-8', 'replace')
    stderr = err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()


def put_text(path, content):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(content.encode('utf-8'))
    sftp.close()


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-performance-test-{stamp}'

seed = """<?php
$pdo=new PDO('sqlite:%s',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$email=%s;$name=%s;$notes=%s;$hash=password_hash(%s,PASSWORD_DEFAULT);
$pdo->beginTransaction();
try {
  $old=$pdo->prepare('SELECT id,notes FROM users WHERE email=?');$old->execute([$email]);$existing=$old->fetch(PDO::FETCH_ASSOC);
  if($existing && $existing['notes']!==$notes) throw new RuntimeException('Existing account is not the expected phase 58 test account.');
  if($existing){$userId=(int)$existing['id'];$pdo->prepare("UPDATE users SET password_hash=?,full_name=?,status='active',updated_at=datetime('now','localtime') WHERE id=?")->execute([$hash,$name,$userId]);$pdo->prepare('DELETE FROM staff_role_assignments WHERE user_id=?')->execute([$userId]);}
  else {$add=$pdo->prepare("INSERT INTO users (role,email,password_hash,full_name,status,email_verified,notes,created_at,updated_at) VALUES ('staff',?,?,?,'active',1,?,datetime('now','localtime'),datetime('now','localtime'))");$add->execute([$email,$hash,$name,$notes]);$userId=(int)$pdo->lastInsertId();}
  $roles=$pdo->query("SELECT rbac_role_code,staff_role_id FROM rbac_staff_role_links WHERE rbac_role_code IN ('TECH','BM')")->fetchAll(PDO::FETCH_KEY_PAIR);
  if(!isset($roles['TECH'],$roles['BM'])) throw new RuntimeException('TECH or BM template is missing.');
  $addRole=$pdo->prepare('INSERT INTO staff_role_assignments (user_id,role_id,assigned_by) VALUES (?,?,1)');$addRole->execute([$userId,$roles['TECH']]);$addRole->execute([$userId,$roles['BM']]);
  $productId=(int)$pdo->query('SELECT id FROM products ORDER BY id LIMIT 1')->fetchColumn();if(!$productId) throw new RuntimeException('No product exists for the isolated test case.');
  $caseCode='TESTP58%s';$addCase=$pdo->prepare("INSERT INTO warranty_cases (case_code,product_id,customer_name,customer_phone,customer_email,issue_description,purchase_date,warranty_end_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)");$addCase->execute([$caseCode,$productId,'Khách hàng kiểm thử P113','0900000000','test-p113@invalid.local','Phiếu kiểm thử nội bộ, sẽ được tự động xóa sau khi xác nhận luồng phân công và báo cáo.','2026-07-19','2027-07-19','approved',$userId]);$caseId=(int)$pdo->lastInsertId();
  $pdo->commit();echo json_encode(['user_id'=>$userId,'case_id'=>$caseId,'case_code'=>$caseCode]);
} catch(Throwable $e) {if($pdo->inTransaction())$pdo->rollBack();throw $e;}
""" % (DB, json.dumps(EMAIL, ensure_ascii=False), json.dumps(NAME, ensure_ascii=False), json.dumps(NOTES, ensure_ascii=False), json.dumps(PASSWORD), stamp)

web_test = """import http.cookiejar
import re
import sys
import urllib.parse
import urllib.request

sys.stdout.reconfigure(encoding='utf-8')
base='https://coolingsystems.vn'
email=__EMAIL__
password=__PASSWORD__
case_id=__CASE_ID__
case_code=__CASE_CODE__
jar=http.cookiejar.CookieJar()
opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def csrf(html):
    match=re.search(r'name=[\"\\\']_csrf[\"\\\']\\s+value=[\"\\\']([^\"\\\']+)',html)
    if not match: raise RuntimeError('CSRF token missing')
    return match.group(1)

login=opener.open(base+'/staff/login',timeout=30).read().decode('utf-8','replace')
payload=urllib.parse.urlencode({'_csrf':csrf(login),'email':email,'password':password}).encode()
response=opener.open(urllib.request.Request(base+'/staff/login',data=payload,method='POST'),timeout=30)
if not response.geturl().rstrip('/').endswith('/staff'): raise RuntimeError('Staff login failed: '+response.geturl())
page=opener.open(base+'/admin/warranties/performance',timeout=30).read().decode('utf-8','replace')
if case_code not in page: raise RuntimeError('Approved test case is absent from assignment page')
token=csrf(page)
payload=urllib.parse.urlencode({'_csrf':token,'assigned_to':__USER_ID__}).encode()
response=opener.open(urllib.request.Request(base+'/admin/warranties/'+str(case_id)+'/assign',data=payload,method='POST'),timeout=30)
if not response.geturl().endswith('/admin/warranties/performance'): raise RuntimeError('Assignment did not return to performance page')
page=opener.open(base+'/admin/warranties',timeout=30).read().decode('utf-8','replace')
payload=urllib.parse.urlencode({'_csrf':csrf(page),'status':'completed'}).encode()
response=opener.open(urllib.request.Request(base+'/admin/warranties/'+str(case_id)+'/status',data=payload,method='POST'),timeout=30)
if not response.geturl().endswith('/admin/warranties'): raise RuntimeError('Completion status update failed')
page=opener.open(base+'/admin/warranties',timeout=30).read().decode('utf-8','replace')
if '/admin/warranties/'+str(case_id)+'/documents' not in page: raise RuntimeError('Document button was not shown after the saved completed status')
print('LOGIN=ok|PERFORMANCE=ok|ASSIGN=ok|COMPLETE=ok|DOCUMENT_BUTTON=ok')
"""

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\"")
    put_text('/tmp/warranty-phase58-seed-test.php', seed)
    seed_output = run('chown www-data:www-data /tmp/warranty-phase58-seed-test.php && runuser -u www-data -- php /tmp/warranty-phase58-seed-test.php')
    fixture = json.loads(seed_output)
    web_test = (web_test
        .replace('__EMAIL__', repr(EMAIL))
        .replace('__PASSWORD__', repr(PASSWORD))
        .replace('__CASE_ID__', str(fixture['case_id']))
        .replace('__CASE_CODE__', repr(fixture['case_code']))
        .replace('__USER_ID__', str(fixture['user_id'])))
    put_text('/tmp/warranty-phase58-web-test.py', web_test)
    web_output = run('python3 /tmp/warranty-phase58-web-test.py')
    db_output = run(
        f"sqlite3 -separator '|' {DB} \"SELECT status,assigned_to FROM warranty_cases WHERE id={fixture['case_id']}; "
        f"SELECT COUNT(*) FROM audit_logs WHERE entity_type='warranty_case' AND entity_id={fixture['case_id']} AND action IN ('warranty_technician_assigned','warranty_case_status');\""
    )
    if db_output.splitlines()[0] != f"completed|{fixture['user_id']}" or db_output.splitlines()[1] != '2':
        raise RuntimeError('Database assertions for assignment/completion did not pass: ' + db_output)
    cleanup = (
        f"DELETE FROM audit_logs WHERE entity_type='warranty_case' AND entity_id={fixture['case_id']}; "
        f"DELETE FROM warranty_cases WHERE id={fixture['case_id']}; "
        f"DELETE FROM staff_role_assignments WHERE user_id={fixture['user_id']} AND role_id IN (SELECT staff_role_id FROM rbac_staff_role_links WHERE rbac_role_code='BM');"
    )
    run(f"sqlite3 {DB} \"{cleanup}\"")
    final_roles = run(f"sqlite3 -separator '|' {DB} \"SELECT link.rbac_role_code FROM staff_role_assignments assignment INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE assignment.user_id={fixture['user_id']} ORDER BY link.rbac_role_code;\"")
    if final_roles != 'TECH':
        raise RuntimeError('Test account did not end with TECH-only role: ' + final_roles)
    run('rm -f /tmp/warranty-phase58-seed-test.php /tmp/warranty-phase58-web-test.py')
    print('BACKUP=' + backup)
    print('TEST_ACCOUNT=' + EMAIL)
    print('WEB=' + web_output)
    print('DATABASE=' + db_output.replace('\n', '|'))
    print('FINAL_ROLE=' + final_roles)
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; chown www-data:www-data {DB}; rm -f /tmp/warranty-phase58-seed-test.php /tmp/warranty-phase58-web-test.py")
    client.close()
    raise

client.close()
