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


def run(command, timeout=90):
    _, out, err = client.exec_command(command, timeout=timeout)
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


def replace_once(source, old, new, label):
    if source.count(old) != 1:
        raise RuntimeError(f'{label}: expected one match, got {source.count(old)}')
    return source.replace(old, new, 1)


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/cash-disbursements-phase62-{stamp}'
routes_path = APP + '/routes/admin.php'
view_path = APP + '/views/admin/cashbook.php'
route_marker = "get('/admin/warranties', function() {"

disbursement_route = r'''post('/admin/cashbook/disbursements', function() {
    $actor=requireStaffPermission('rbac:finance.disbursements.create|tax_config','/admin/login');csrfCheck();
    $accountId=(int)($_POST['account_id']??0);$rawAmount=preg_replace('/\D+/','',(string)($_POST['amount']??''));$payee=trim($_POST['payee_name']??'');$phone=preg_replace('/\D+/','',$_POST['payee_phone']??'');$email=strtolower(trim($_POST['payee_email']??''));$reference=trim($_POST['reference_code']??'');$description=trim($_POST['description']??'');$words=$description===''?0:count(preg_split('/\s+/u',$description,-1,PREG_SPLIT_NO_EMPTY));$entryDate=trim($_POST['entry_date']??'');
    if(!$accountId||$rawAmount===''||(int)$rawAmount<1||(int)$rawAmount>999999999999||$payee===''||mb_strlen($payee)>120||!preg_match('/^0[35789]\d{8}$/',$phone)||!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>254||mb_strlen($reference)>64||$words>200||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$entryDate)){flash('error','D&#7919; li&#7879;u phi&#7871;u chi kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/cashbook');}
    $account=dbGet('SELECT id,name FROM cash_accounts WHERE id=? AND is_active=1',[$accountId]);if(!$account){flash('error','Qu&#7929; chi kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/cashbook');}
    $amount=(int)$rawAmount;$code='PC'.date('ymdHis').random_int(10,99);
    $requestId=dbInsert('INSERT INTO cash_disbursement_requests (code,account_id,amount,payee_name,payee_phone,payee_email,reference_code,description,entry_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$code,$accountId,$amount,$payee,$phone,$email,$reference,$description,$entryDate,'pending',$actor['id']??null]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_created','cash_disbursement_request',$requestId,json_encode(['code'=>$code,'account_id'=>$accountId,'amount'=>$amount,'payee'=>$payee],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','&#272;&#227; t&#7841;o phi&#7871;u chi '.$code.' ch&#7901; duy&#7879;t.');redirect('/admin/cashbook');
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/cashbook.php")
    if run(f"sqlite3 {DB} \"SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='cash_disbursement_requests';\"") != '0':
        raise RuntimeError('Cash disbursement table already exists; phase 62 will not overwrite it.')
    run(f"sqlite3 {DB} \"CREATE TABLE cash_disbursement_requests (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,account_id INTEGER NOT NULL,amount INTEGER NOT NULL CHECK(amount>0),payee_name TEXT NOT NULL,payee_phone TEXT NOT NULL,payee_email TEXT NOT NULL,reference_code TEXT,description TEXT,entry_date TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','approved','rejected')),created_by INTEGER NOT NULL,approved_by INTEGER,approved_at TEXT,rejection_reason TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(account_id) REFERENCES cash_accounts(id)); CREATE INDEX idx_cash_disbursement_status ON cash_disbursement_requests(status,entry_date);\"")
    routes = read_remote(routes_path)
    if 'cash_disbursement_created' in routes:
        raise RuntimeError('Cash disbursement route already exists.')
    old_view = "$canCreateReceipt=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.receipts.create');view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate,'canCreateReceipt'=>$canCreateReceipt]);"
    new_view = "$canCreateReceipt=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.receipts.create');$canCreateDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.create');$disbursements=dbAll(\"SELECT request.*,account.name AS account_name FROM cash_disbursement_requests request INNER JOIN cash_accounts account ON account.id=request.account_id ORDER BY CASE request.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,request.created_at DESC LIMIT 200\");view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate,'canCreateReceipt'=>$canCreateReceipt,'canCreateDisbursement'=>$canCreateDisbursement,'disbursements'=>$disbursements]);"
    routes = replace_once(routes, old_view, new_view, 'cashbook disbursement data')
    routes = replace_once(routes, route_marker, disbursement_route + route_marker, 'cash disbursement route marker')
    write_remote('/tmp/cash-disbursements-phase62-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase62.php'), '/tmp/cash-disbursements-phase62-seed.php')
    sftp.put(str(ROOT / 'cashbook_phase59.php'), '/tmp/cash-disbursements-phase62-view.php')
    sftp.close()
    result = run("chown www-data:www-data /tmp/cash-disbursements-phase62-* && runuser -u www-data -- php /tmp/cash-disbursements-phase62-seed.php && " + f"install -o www-data -g www-data -m 0644 /tmp/cash-disbursements-phase62-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/cash-disbursements-phase62-view.php {view_path} && php -l {routes_path} && php -l {view_path} && rm -f /tmp/cash-disbursements-phase62-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('CASHBOOK_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/cashbook"))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='finance.disbursements.create';\""))
    print('REQUEST_TABLE=' + run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND name='cash_disbursement_requests';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/cashbook.php && cp {backup}/cashbook.php {view_path}; rm -f /tmp/cash-disbursements-phase62-*; chown www-data:www-data {DB} {routes_path} {view_path}")
    client.close()
    raise

client.close()
