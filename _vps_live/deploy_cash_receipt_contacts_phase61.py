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


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/cash-receipt-contacts-phase61-{stamp}'
routes_path = APP + '/routes/admin.php'
view_path = APP + '/views/admin/cashbook.php'
start_marker = "post('/admin/cashbook/receipts', function() {"
end_marker = "get('/admin/warranties', function() {"

receipt_route = r'''post('/admin/cashbook/receipts', function() {
    $actor=requireStaffPermission('rbac:finance.receipts.create|tax_config','/admin/login');csrfCheck();
    $accountId=(int)($_POST['account_id']??0);$rawAmount=preg_replace('/\D+/','',(string)($_POST['amount']??''));$payer=trim($_POST['payer_name']??'');$phone=preg_replace('/\D+/','',$_POST['payer_phone']??'');$email=strtolower(trim($_POST['payer_email']??''));$reference=trim($_POST['reference_code']??'');$description=trim($_POST['description']??'');$words=$description===''?0:count(preg_split('/\s+/u',$description,-1,PREG_SPLIT_NO_EMPTY));$entryDate=trim($_POST['entry_date']??'');
    if(!$accountId||$rawAmount===''||(int)$rawAmount<1||(int)$rawAmount>999999999999||$payer===''||mb_strlen($payer)>120||!preg_match('/^0[35789]\d{8}$/',$phone)||!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>254||mb_strlen($reference)>64||$words>200||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$entryDate)){flash('error','D&#7919; li&#7879;u phi&#7871;u thu kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/cashbook');}
    $account=dbGet('SELECT id,name FROM cash_accounts WHERE id=? AND is_active=1',[$accountId]);if(!$account){flash('error','Qu&#7929; nh&#7853;n ti&#7873;n kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/cashbook');}
    $amount=(int)$rawAmount;$code='PT'.date('ymdHis').random_int(10,99);$fullDescription=trim('Thu từ '.$payer.($description!==''?' - '.$description:''));
    $entryId=dbInsert('INSERT INTO cash_ledger_entries (account_id,direction,amount,reference_type,reference_code,description,payer_phone,payer_email,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)',[$accountId,'in',$amount,'manual_receipt',$reference!==''?$reference:$code,$fullDescription,$phone,$email,$entryDate,$actor['id']??null]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_receipt_created','cash_ledger_entry',$entryId,json_encode(['code'=>$code,'account_id'=>$accountId,'amount'=>$amount,'payer'=>$payer],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','&#272;&#227; t&#7841;o phi&#7871;u thu '.$code.' cho qu&#7929; '.$account['name'].'.');redirect('/admin/cashbook');
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/cashbook.php")
    columns = run(f"sqlite3 {DB} \"PRAGMA table_info(cash_ledger_entries);\"")
    additions = []
    if '|payer_phone|' not in columns:
        additions.append('ALTER TABLE cash_ledger_entries ADD COLUMN payer_phone TEXT')
    if '|payer_email|' not in columns:
        additions.append('ALTER TABLE cash_ledger_entries ADD COLUMN payer_email TEXT')
    if additions:
        run(f"sqlite3 {DB} \"{'; '.join(additions)};\"")
    routes = read_remote(routes_path)
    start = routes.find(start_marker)
    end = routes.find(end_marker, start)
    if start < 0 or end < 0:
        raise RuntimeError('Cash receipt route markers are invalid.')
    routes = routes[:start] + receipt_route + routes[end:]
    write_remote('/tmp/cash-receipt-contacts-phase61-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'cashbook_phase59.php'), '/tmp/cash-receipt-contacts-phase61-view.php')
    sftp.close()
    result = run("chown www-data:www-data /tmp/cash-receipt-contacts-phase61-* && " + f"install -o www-data -g www-data -m 0644 /tmp/cash-receipt-contacts-phase61-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/cash-receipt-contacts-phase61-view.php {view_path} && php -l {routes_path} && php -l {view_path} && rm -f /tmp/cash-receipt-contacts-phase61-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('CASHBOOK_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/cashbook"))
    print('CONTACT_COLUMNS=' + run(f"sqlite3 {DB} \"PRAGMA table_info(cash_ledger_entries);\" | grep -E 'payer_phone|payer_email' | wc -l"))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/cashbook.php && cp {backup}/cashbook.php {view_path}; rm -f /tmp/cash-receipt-contacts-phase61-*; chown www-data:www-data {DB} {routes_path} {view_path}")
    client.close()
    raise

client.close()
