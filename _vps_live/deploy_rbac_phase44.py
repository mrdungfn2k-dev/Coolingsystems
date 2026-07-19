import secrets
import sys
from datetime import datetime
from pathlib import Path

import paramiko


sys.stdout.reconfigure(encoding='utf-8')
HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
TEST_EMAIL = 'rbac-test-warehouse-20260719@coolingsystems.vn'
TEST_NAME = 'Tài khoản kiểm thử RBAC - Kho'
TEMP_PASSWORD = secrets.token_urlsafe(20)

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)


def run(command, timeout=90):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    status = stdout.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{output}\n{error}')
    return output.strip()


def put_text(path, content):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'wb') as handle:
            handle.write(content.encode('utf-8'))
    finally:
        sftp.close()


def counts():
    return run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from users').fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
        "c.execute(\\\"select count(*) from audit_logs where action like 'rbac_%'\\\").fetchone()[0]])))\""
    )


def backup_database(path):
    run(
        "python3 -c \"import sqlite3; source=sqlite3.connect('" + DB + "'); "
        "target=sqlite3.connect('" + path + "'); source.backup(target); target.close(); source.close()\""
    )


def restore_database(path):
    run(
        "python3 -c \"import sqlite3; source=sqlite3.connect('" + path + "'); "
        "target=sqlite3.connect('" + DB + "'); source.backup(target); target.close(); source.close()\""
    )


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase44-{stamp}'
baseline = counts()

seed = f'''<?php
$pdo = new PDO('sqlite:{DB}', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$email = {TEST_EMAIL!r};
$name = {TEST_NAME!r};
$passwordHash = {""!r};
'''
# PHP literals are generated below using JSON to preserve Vietnamese safely.
import json
seed = """<?php
$pdo = new PDO('sqlite:%s', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$email = %s;
$name = %s;
$passwordHash = %s;
$pdo->beginTransaction();
try {
    $existing = $pdo->prepare('SELECT id,role,notes FROM users WHERE email=?');
    $existing->execute([$email]);
    $old = $existing->fetch(PDO::FETCH_ASSOC);
    if ($old) {
        if ($old['role'] !== 'staff' || $old['notes'] !== 'Tài khoản kiểm thử RBAC nội bộ.') throw new RuntimeException('Existing account is not the expected test account.');
        $pdo->prepare('DELETE FROM staff_role_assignments WHERE user_id=?')->execute([$old['id']]);
        $pdo->prepare('DELETE FROM staff_permissions WHERE user_id=?')->execute([$old['id']]);
        $pdo->prepare('DELETE FROM user_notifications WHERE user_id=?')->execute([$old['id']]);
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$old['id']]);
    }
    $create = $pdo->prepare("INSERT INTO users (role,email,password_hash,full_name,status,email_verified,notes,created_at,updated_at) VALUES ('staff',?,?,?,'active',1,?,datetime('now'),datetime('now'))");
    $create->execute([$email, $passwordHash, $name, 'Tài khoản kiểm thử RBAC nội bộ.']);
    $userId = (int)$pdo->lastInsertId();
    $roleId = (int)$pdo->query("SELECT staff_role_id FROM rbac_staff_role_links WHERE rbac_role_code='WH'")->fetchColumn();
    if (!$roleId) throw new RuntimeException('Warehouse template missing.');
    $pdo->prepare('INSERT INTO staff_role_assignments (user_id,role_id,assigned_by) VALUES (?,?,?)')->execute([$userId, $roleId, 1]);
    $assignmentId = (int)$pdo->lastInsertId();
    $meta = json_encode(['target_user_id'=>$userId,'role_id'=>$roleId,'role_code'=>'WH','test_account'=>true], JSON_UNESCAPED_UNICODE);
    $pdo->prepare("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (1,'admin','rbac_test_account_created','staff_role_assignment',?,?,?,?)")->execute([$assignmentId,$meta,'127.0.0.1','RBAC phase 44']);
    $pdo->commit();
    echo json_encode(['ok'=>true,'user_id'=>$userId,'role_id'=>$roleId,'assignment_id'=>$assignmentId]);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
""" % (DB, json.dumps(TEST_EMAIL, ensure_ascii=False), json.dumps(TEST_NAME, ensure_ascii=False), "password_hash(" + json.dumps(TEMP_PASSWORD) + ", PASSWORD_DEFAULT)")

web_test = """import http.cookiejar
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

base = 'https://coolingsystems.vn'
email = %s
password = %s
jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
html = opener.open(base + '/staff/login', timeout=30).read().decode('utf-8', 'replace')
match = re.search(r'name=["\\\']_csrf["\\\']\\s+value=["\\\']([^"\\\']+)', html)
if not match:
    raise RuntimeError('CSRF token missing from staff login.')
payload = urllib.parse.urlencode({'_csrf': match.group(1), 'email': email, 'password': password}).encode()
response = opener.open(urllib.request.Request(base + '/staff/login', data=payload, method='POST'), timeout=30)
if not response.geturl().rstrip('/').endswith('/staff'):
    raise RuntimeError('Staff login did not redirect to dashboard: ' + response.geturl())
inventory = opener.open(base + '/admin/inventory', timeout=30)
if inventory.status != 200:
    raise RuntimeError('Warehouse inventory page is not available.')

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None

restricted = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())
try:
    restricted.open(base + '/admin/staff', timeout=30)
    raise RuntimeError('Warehouse account unexpectedly accessed staff RBAC page.')
except urllib.error.HTTPError as exc:
    if exc.code not in (301, 302, 303):
        raise
print('LOGIN=ok|INVENTORY=ok|STAFF_RBAC=blocked')
""" % (repr(TEST_EMAIL), repr(TEMP_PASSWORD))

try:
    run(f'mkdir -p {backup}')
    backup_database(f'{backup}/cooling.db')
    put_text('/tmp/rbac44-seed.php', seed)
    run('chown www-data:www-data /tmp/rbac44-seed.php && runuser -u www-data -- php /tmp/rbac44-seed.php')
    put_text('/tmp/rbac44-webtest.py', web_test)
    web_result = run('python3 /tmp/rbac44-webtest.py')
    helper_result = run(
        "cd " + APP + " && runuser -u www-data -- php -r 'require \"includes/db.php\"; require \"includes/rbac.php\"; "
        "$u=dbGet(\"SELECT id FROM users WHERE email=?\", [\"" + TEST_EMAIL + "\"]); "
        "echo (rbacCan((int)$u[\"id\"], \"inventory.view\") ? \"inventory-allowed\" : \"inventory-denied\") . \"|\" . (rbacCan((int)$u[\"id\"], \"system.rbac.manage\") ? \"rbac-allowed\" : \"rbac-denied\");'"
    )
    run('rm -f /tmp/rbac44-seed.php /tmp/rbac44-webtest.py')
    print('BACKUP=' + backup)
    print('BASELINE=' + baseline)
    print('WEB=' + web_result)
    print('CAPABILITIES=' + helper_result)
    print('AFTER=' + counts())
except Exception:
    try:
        restore_database(f'{backup}/cooling.db')
        run(f'chown www-data:www-data {DB}; rm -f /tmp/rbac44-seed.php /tmp/rbac44-webtest.py')
    finally:
        client.close()
    raise

client.close()
