import json
import secrets
import sys
from datetime import datetime

import paramiko

sys.stdout.reconfigure(encoding='utf-8')

DB = '/var/lib/coolingsystems/cooling.db'
BASE = 'https://coolingsystems.vn'
STAMP = datetime.now().strftime('%Y%m%d%H%M%S')
EMAIL = f'p87-test-{STAMP}@coolingsystems.vn'
PASSWORD = secrets.token_urlsafe(24)
ROLE_NAME = f'P87 TEST {STAMP}'
NOTE = f'P87 isolated test account {STAMP}'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command, timeout=90):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    if stdout.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + output + '\n' + error)
    return output.strip()


def put_text(path, content):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(content.encode('utf-8'))
    sftp.close()


backup = f'/var/backups/coolingsystems/supplier-returns-test-{STAMP}'
fixture = None

seed = """<?php
$pdo=new PDO('sqlite:%s',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->beginTransaction();
try {
    $email=%s;$password=%s;$roleName=%s;$note=%s;$stamp=%s;
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $role=$pdo->prepare('INSERT INTO staff_roles (name,description,permissions) VALUES (?,?,?)');
    $role->execute([$roleName,'Vai trò tạm để kiểm thử P87',json_encode(['rbac:purchasing.returns.create'])]);
    $roleId=(int)$pdo->lastInsertId();
    $user=$pdo->prepare("INSERT INTO users (role,email,password_hash,full_name,status,email_verified,notes,created_at,updated_at) VALUES ('staff',?,?,?,'active',1,?,datetime('now','localtime'),datetime('now','localtime'))");
    $user->execute([$email,$hash,'Nhân viên kiểm thử P87',$note]);
    $userId=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO staff_role_assignments (user_id,role_id,assigned_by) VALUES (?,?,1)')->execute([$userId,$roleId]);

    $product=$pdo->query("SELECT id,sku,name,stock FROM products ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if(!$product) throw new RuntimeException('Không có sản phẩm để kiểm thử.');
    $supplierCode='TEST-NCC-'.$stamp;
    $pdo->prepare('INSERT INTO suppliers (code,name,is_active,created_by) VALUES (?,?,1,?)')->execute([$supplierCode,'Nhà cung cấp kiểm thử P87',$userId]);
    $supplierId=(int)$pdo->lastInsertId();

    $requestCode='TEST-YCM-'.$stamp;
    $pdo->prepare("INSERT INTO purchase_requests (code,supplier_id,status,note,created_by,approved_by,approved_at) VALUES (?,?,'approved',?,?,?,datetime('now','localtime'))")->execute([$requestCode,$supplierId,'Dữ liệu kiểm thử P87',$userId,1]);
    $requestId=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO purchase_request_items (request_id,product_id,sku,product_name,current_stock,min_stock,requested_qty) VALUES (?,?,?,?,?,?,3)')->execute([$requestId,$product['id'],$product['sku'],$product['name'],$product['stock'],0]);

    $orderCode='TEST-PO-'.$stamp;
    $pdo->prepare("INSERT INTO purchase_orders (code,source_request_id,supplier_id,status,total_amount,created_by,approved_by,approved_at) VALUES (?,?,?,'approved',300000,?,?,datetime('now','localtime'))")->execute([$orderCode,$requestId,$supplierId,$userId,1]);
    $orderId=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO purchase_order_items (order_id,product_id,sku,product_name,ordered_qty,unit_cost,line_total,received_qty) VALUES (?,?,?,?,3,100000,300000,3)')->execute([$orderId,$product['id'],$product['sku'],$product['name']]);
    $orderItemId=(int)$pdo->lastInsertId();

    $receiptCode='TEST-PN-'.$stamp;
    $pdo->prepare("INSERT INTO goods_receipts (code,order_id,received_date,status,note,created_by,qc_by,qc_at,qc_note) VALUES (?,?,date('now'),'qc_completed',?,?,?,datetime('now','localtime'),?)")->execute([$receiptCode,$orderId,'Dữ liệu kiểm thử P87',$userId,1,'Kiểm thử: 2 đạt, 1 lỗi']);
    $receiptId=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO goods_receipt_items (receipt_id,order_item_id,product_id,received_qty,accepted_qty,rejected_qty,quantity_ok,appearance_ok,oem_ok,qc_note) VALUES (?,?,?,3,2,1,1,0,1,?)')->execute([$receiptId,$orderItemId,$product['id'],'Một sản phẩm lỗi để thử trả NCC']);
    $receiptItemId=(int)$pdo->lastInsertId();
    $pdo->commit();
    echo json_encode(['role_id'=>$roleId,'user_id'=>$userId,'product_id'=>(int)$product['id'],'stock_before'=>(int)$product['stock'],'supplier_id'=>$supplierId,'request_id'=>$requestId,'order_id'=>$orderId,'order_item_id'=>$orderItemId,'receipt_id'=>$receiptId,'receipt_item_id'=>$receiptItemId,'receipt_code'=>$receiptCode]);
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    throw $e;
}
""" % (
    DB,
    json.dumps(EMAIL),
    json.dumps(PASSWORD),
    json.dumps(ROLE_NAME),
    json.dumps(NOTE),
    json.dumps(STAMP),
)

web_test = """import http.cookiejar
import re
import sys
import urllib.parse
import urllib.request

sys.stdout.reconfigure(encoding='utf-8')
base=__BASE__
email=__EMAIL__
password=__PASSWORD__
receipt_id=__RECEIPT_ID__
receipt_item_id=__RECEIPT_ITEM_ID__
receipt_code=__RECEIPT_CODE__
jar=http.cookiejar.CookieJar()
opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def csrf(html):
    match=re.search(r'name=["\\\']_csrf["\\\']\\s+value=["\\\']([^"\\\']+)',html)
    if not match: raise RuntimeError('CSRF token missing')
    return match.group(1)

login=opener.open(base+'/staff/login',timeout=30).read().decode('utf-8','replace')
payload=urllib.parse.urlencode({'_csrf':csrf(login),'email':email,'password':password}).encode()
response=opener.open(urllib.request.Request(base+'/staff/login',data=payload,method='POST'),timeout=30)
if not response.geturl().rstrip('/').endswith('/staff'): raise RuntimeError('Staff login failed: '+response.geturl())

url=base+'/admin/supplier-returns?receipt='+str(receipt_id)
page=opener.open(url,timeout=30).read().decode('utf-8','replace')
if receipt_code not in page or 'Tạo phiếu chờ duyệt' not in page: raise RuntimeError('P87 form or fixture receipt is missing')
payload=urllib.parse.urlencode({'_csrf':csrf(page),'receipt_id':receipt_id,'reason':'Trả sản phẩm lỗi trong kiểm thử P87','qty['+str(receipt_item_id)+']':'1'}).encode()
response=opener.open(urllib.request.Request(base+'/admin/supplier-returns',data=payload,method='POST'),timeout=30)
created=response.read().decode('utf-8','replace')
if 'Đã tạo phiếu trả' not in created: raise RuntimeError('Create-return success message is missing')

page=opener.open(url,timeout=30).read().decode('utf-8','replace')
payload=urllib.parse.urlencode({'_csrf':csrf(page),'receipt_id':receipt_id,'reason':'Yêu cầu vượt quá số lượng còn lại','qty['+str(receipt_item_id)+']':'3'}).encode()
response=opener.open(urllib.request.Request(base+'/admin/supplier-returns',data=payload,method='POST'),timeout=30)
blocked=response.read().decode('utf-8','replace')
if 'vượt quá số lượng' not in blocked: raise RuntimeError('Over-return was not rejected by server validation')
print('LOGIN=OK|PAGE=OK|CREATE=OK|OVER_RETURN_BLOCKED=OK')
"""


def cleanup(data):
    if not data:
        return
    sql = (
        f"DELETE FROM audit_logs WHERE entity_type='supplier_return' AND entity_id IN (SELECT id FROM supplier_returns WHERE receipt_id={data['receipt_id']});"
        f"DELETE FROM supplier_return_items WHERE return_id IN (SELECT id FROM supplier_returns WHERE receipt_id={data['receipt_id']});"
        f"DELETE FROM supplier_returns WHERE receipt_id={data['receipt_id']};"
        f"DELETE FROM goods_receipt_items WHERE receipt_id={data['receipt_id']};"
        f"DELETE FROM goods_receipts WHERE id={data['receipt_id']};"
        f"DELETE FROM purchase_order_items WHERE order_id={data['order_id']};"
        f"DELETE FROM purchase_orders WHERE id={data['order_id']};"
        f"DELETE FROM purchase_request_items WHERE request_id={data['request_id']};"
        f"DELETE FROM purchase_requests WHERE id={data['request_id']};"
        f"DELETE FROM suppliers WHERE id={data['supplier_id']};"
        f"DELETE FROM staff_role_assignments WHERE user_id={data['user_id']};"
        f"DELETE FROM staff_permissions WHERE user_id={data['user_id']};"
        f"DELETE FROM user_notifications WHERE user_id={data['user_id']};"
        f"DELETE FROM users WHERE id={data['user_id']};"
        f"DELETE FROM staff_roles WHERE id={data['role_id']};"
    )
    run(f'sqlite3 {DB} "{sql}"')


try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\"")
    put_text('/tmp/p87-seed-test.php', seed)
    fixture = json.loads(run('chown www-data:www-data /tmp/p87-seed-test.php && runuser -u www-data -- php /tmp/p87-seed-test.php'))
    web_test = (web_test
        .replace('__BASE__', repr(BASE))
        .replace('__EMAIL__', repr(EMAIL))
        .replace('__PASSWORD__', repr(PASSWORD))
        .replace('__RECEIPT_ID__', str(fixture['receipt_id']))
        .replace('__RECEIPT_ITEM_ID__', str(fixture['receipt_item_id']))
        .replace('__RECEIPT_CODE__', repr(fixture['receipt_code'])))
    put_text('/tmp/p87-web-test.py', web_test)
    web_output = run('python3 /tmp/p87-web-test.py')
    db_output = run(
        f"sqlite3 -separator '|' {DB} \"SELECT sr.status,sri.return_qty,sri.returned_rejected_qty,sri.returned_accepted_qty FROM supplier_returns sr INNER JOIN supplier_return_items sri ON sri.return_id=sr.id WHERE sr.receipt_id={fixture['receipt_id']};"
        f"SELECT stock FROM products WHERE id={fixture['product_id']};"
        f"SELECT COUNT(*) FROM supplier_returns WHERE receipt_id={fixture['receipt_id']};"
        f"SELECT COUNT(*) FROM audit_logs WHERE entity_type='supplier_return' AND entity_id IN (SELECT id FROM supplier_returns WHERE receipt_id={fixture['receipt_id']}) AND action='supplier_return_created';\""
    )
    rows = db_output.splitlines()
    expected = f"pending|1|1|0"
    if len(rows) != 4 or rows[0] != expected or rows[1] != str(fixture['stock_before']) or rows[2] != '1' or rows[3] != '1':
        raise RuntimeError('P87 database assertions failed: ' + db_output)
    cleanup(fixture)
    leftover = run(
        f"sqlite3 -separator '|' {DB} \"SELECT COUNT(*) FROM supplier_returns WHERE receipt_id={fixture['receipt_id']};"
        f"SELECT COUNT(*) FROM users WHERE id={fixture['user_id']};"
        f"SELECT stock FROM products WHERE id={fixture['product_id']};\""
    ).splitlines()
    if leftover != ['0', '0', str(fixture['stock_before'])]:
        raise RuntimeError('Fixture cleanup assertions failed: ' + '|'.join(leftover))
    run('rm -f /tmp/p87-seed-test.php /tmp/p87-web-test.py')
    print('BACKUP=' + backup)
    print('WEB=' + web_output)
    print('DATABASE=STATUS_PENDING_OK|REJECTED_FIRST_OK|STOCK_UNCHANGED_OK|AUDIT_OK')
    print('CLEANUP=OK')
except Exception:
    try:
        cleanup(fixture)
        run('rm -f /tmp/p87-seed-test.php /tmp/p87-web-test.py')
    finally:
        client.close()
    raise

client.close()
