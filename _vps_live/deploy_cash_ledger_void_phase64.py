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


def replace_once(value, old, new, label):
    if value.count(old) != 1:
        raise RuntimeError(f'{label}: expected exactly one matching section.')
    return value.replace(old, new, 1)


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/cash-ledger-void-phase64-{stamp}'
routes_path = APP + '/routes/admin.php'
view_path = APP + '/views/admin/cashbook.php'
get_start = "get('/admin/cashbook', function() {"
get_end = "post('/admin/cashbook/receipts', function() {"
route_marker = "get('/admin/warranties', function() {"

cashbook_get = r'''get('/admin/cashbook', function() {
    $user=requireStaffPermission('rbac:finance.cashbook.view|tax_config','/admin/login');
    $selectedAccount=max(0,(int)($_GET['account']??0));$fromDate=trim($_GET['from']??'');$toDate=trim($_GET['to']??'');
    if($fromDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fromDate))$fromDate='';if($toDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$toDate))$toDate='';
    $accounts=dbAll("SELECT account.*,COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE -entry.amount END),0) AS balance FROM cash_accounts account LEFT JOIN cash_ledger_entries entry ON entry.account_id=account.id AND entry.voided_at IS NULL WHERE account.is_active=1 GROUP BY account.id ORDER BY account.sort_order,account.id");
    if($selectedAccount&&!dbGet('SELECT id FROM cash_accounts WHERE id=? AND is_active=1',[$selectedAccount]))$selectedAccount=0;
    $where=['entry.voided_at IS NULL'];$params=[];if($selectedAccount){$where[]='entry.account_id=?';$params[]=$selectedAccount;}if($fromDate!==''){$where[]='entry.entry_date>=?';$params[]=$fromDate;}if($toDate!==''){$where[]='entry.entry_date<=?';$params[]=$toDate;}$sqlWhere=implode(' AND ',$where);
    $entries=dbAll("SELECT entry.*,account.name AS account_name,void_request.id AS void_request_id,void_request.status AS void_request_status,void_request.created_by AS void_request_created_by,void_request.reason AS void_request_reason,void_request.rejection_reason AS void_request_rejection_reason FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id LEFT JOIN cash_ledger_void_requests void_request ON void_request.ledger_entry_id=entry.id WHERE $sqlWhere ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 500",$params);
    $totalRow=dbGet("SELECT COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE 0 END),0) AS income,COALESCE(SUM(CASE WHEN entry.direction='out' THEN entry.amount ELSE 0 END),0) AS expense FROM cash_ledger_entries entry WHERE $sqlWhere",$params)?:['income'=>0,'expense'=>0];$totals=['income'=>(int)$totalRow['income'],'expense'=>(int)$totalRow['expense'],'net'=>(int)$totalRow['income']-(int)$totalRow['expense']];
    $canCreateReceipt=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.receipts.create');$canCreateDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.create');$canApproveDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.approve');$canVoidEntry=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.ledger.void');$disbursements=dbAll("SELECT request.*,account.name AS account_name FROM cash_disbursement_requests request INNER JOIN cash_accounts account ON account.id=request.account_id ORDER BY CASE request.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,request.created_at DESC LIMIT 200");
    view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate,'canCreateReceipt'=>$canCreateReceipt,'canCreateDisbursement'=>$canCreateDisbursement,'canApproveDisbursement'=>$canApproveDisbursement,'canVoidEntry'=>$canVoidEntry,'currentUserId'=>(int)$user['id'],'disbursements'=>$disbursements]);
});
'''

void_routes = r'''post('/admin/cashbook/entries/:id/void-requests', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['reason']??'');
    if(mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','L&#253; do h&#7911;y ch&#7913;ng t&#7915; ph&#7843;i t&#7915; 5 &#273;&#7871;n 300 k&#253; t&#7921;.');redirect('/admin/cashbook');}
    $entry=dbGet('SELECT id,voided_at FROM cash_ledger_entries WHERE id=?',[$p['id']]);if(!$entry||$entry['voided_at']!==null){flash('error','Ch&#7913;ng t&#7915; kh&#244;ng c&#242;n h&#7907;p l&#7879; &#273;&#7875; y&#234;u c&#7847;u h&#7911;y.');redirect('/admin/cashbook');}
    if(dbGet('SELECT id FROM cash_ledger_void_requests WHERE ledger_entry_id=?',[$entry['id']])){flash('error','Ch&#7913;ng t&#7915; n&#224;y &#273;&#227; c&#243; y&#234;u c&#7847;u h&#7911;y.');redirect('/admin/cashbook');}
    $code='HCT-'.date('Ymd-His').'-'.str_pad((string)$entry['id'],5,'0',STR_PAD_LEFT);$requestId=dbInsert("INSERT INTO cash_ledger_void_requests (code,ledger_entry_id,reason,status,created_by) VALUES (?,?,?,?,?)",[$code,$entry['id'],$reason,'pending',$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_requested','cash_ledger_entry',$entry['id'],json_encode(['request_id'=>$requestId,'code'=>$code,'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7841;o y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915; '.$code.'.');redirect('/admin/cashbook');
});
post('/admin/cashbook/void-requests/:id/approve', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$pdo=db();
    try{$pdo->beginTransaction();$request=dbGet("SELECT request.*,entry.voided_at FROM cash_ledger_void_requests request INNER JOIN cash_ledger_entries entry ON entry.id=request.ledger_entry_id WHERE request.id=?",[$p['id']]);if(!$request||$request['status']!=='pending'||$request['voided_at']!==null)throw new RuntimeException('invalid');if((int)$request['created_by']===(int)$actor['id'])throw new RuntimeException('self');$changed=dbRun("UPDATE cash_ledger_void_requests SET status='approved',approved_by=?,approved_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$request['id']]);if($changed->rowCount()!==1)throw new RuntimeException('invalid');dbRun("UPDATE cash_ledger_entries SET voided_at=datetime('now','localtime') WHERE id=? AND voided_at IS NULL",[$request['ledger_entry_id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_approved','cash_ledger_entry',$request['ledger_entry_id'],json_encode(['request_id'=>$request['id'],'reason'=>$request['reason']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','&#272;&#227; duy&#7879;t h&#7911;y ch&#7913;ng t&#7915;. B&#250;t to&#225;n &#273;&#432;&#7907;c gi&#7919; l&#7841;i trong nh&#7853;t k&#253; v&#224; kh&#244;ng c&#242;n t&#237;nh v&#224;o s&#7893; qu&#7929;.');}catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();flash('error',$exception->getMessage()==='self'?'Ng&#432;&#7901;i y&#234;u c&#7847;u kh&#244;ng th&#7875; t&#7921; duy&#7879;t h&#7911;y.':'Kh&#244;ng th&#7875; duy&#7879;t y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915;.');}redirect('/admin/cashbook');
});
post('/admin/cashbook/void-requests/:id/reject', function($p) {
    $actor=requireStaffPermission('rbac:finance.ledger.void|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['rejection_reason']??'');$request=dbGet('SELECT id,status,created_by,ledger_entry_id FROM cash_ledger_void_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending'||(int)$request['created_by']===(int)$actor['id']||mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','Kh&#244;ng th&#7875; t&#7915; ch&#7889;i y&#234;u c&#7847;u. C&#7847;n l&#253; do t&#7915; 5 &#273;&#7871;n 300 k&#253; t&#7921; v&#224; ng&#432;&#7901;i duy&#7879;t kh&#244;ng &#273;&#432;&#7907;c l&#224; ng&#432;&#7901;i y&#234;u c&#7847;u.');redirect('/admin/cashbook');}dbRun("UPDATE cash_ledger_void_requests SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=?,updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$reason,$request['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_ledger_void_rejected','cash_ledger_entry',$request['ledger_entry_id'],json_encode(['request_id'=>$request['id'],'reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7915; ch&#7889;i y&#234;u c&#7847;u h&#7911;y ch&#7913;ng t&#7915;.');redirect('/admin/cashbook');
});
'''

old_header = '<th>Di&#7877;n gi&#7843;i</th></tr></thead><tbody><?php foreach($entries as $entry): ?>'
new_header = '<th>Di&#7877;n gi&#7843;i</th><th>Thao t&#225;c</th></tr></thead><tbody><?php foreach($entries as $entry): ?>'
old_cell = "<td><?= e($entry['description'] ?: '-') ?></td></tr><?php endforeach; ?>"
new_cell = """<td><?= e($entry['description'] ?: '-') ?></td><td><?php if($canVoidEntry): ?><?php if(!$entry['void_request_id']): ?><form method=\"post\" action=\"/admin/cashbook/entries/<?= (int)$entry['id'] ?>/void-requests\" style=\"display:flex;gap:5px;align-items:center;min-width:260px\"><?= csrfField() ?><input name=\"reason\" maxlength=\"300\" required placeholder=\"Lý do hủy (5-300 ký tự)\"><button class=\"btn btn-sm btn-outline-navy\">Yêu cầu hủy</button></form><?php elseif($entry['void_request_status']==='pending'&&(int)$entry['void_request_created_by']!==(int)$currentUserId): ?><form method=\"post\" action=\"/admin/cashbook/void-requests/<?= (int)$entry['void_request_id'] ?>/approve\" style=\"display:flex;gap:5px;align-items:center;min-width:320px\"><?= csrfField() ?><input name=\"rejection_reason\" maxlength=\"300\" placeholder=\"Lý do nếu từ chối\"><button class=\"btn btn-sm btn-navy\">Duyệt hủy</button><button class=\"btn btn-sm btn-outline-navy\" formaction=\"/admin/cashbook/void-requests/<?= (int)$entry['void_request_id'] ?>/reject\">Từ chối</button></form><?php elseif($entry['void_request_status']==='pending'): ?><span class=\"fs-11 text-muted\">Chờ người khác duyệt</span><?php else: ?><span class=\"fs-11 text-muted\"><?= $entry['void_request_status']==='rejected'?'Yêu cầu bị từ chối':'Đã xử lý' ?></span><?php endif; ?><?php else: ?><span class=\"fs-11 text-muted\">-</span><?php endif; ?></td></tr><?php endforeach; ?>"""

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/cashbook.php")
    if run(f"sqlite3 {DB} \"SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='cash_ledger_void_requests';\"") != '0':
        raise RuntimeError('Cash ledger void-request table already exists; phase 64 will not overwrite it.')
    routes = read_remote(routes_path)
    if 'cash_ledger_void_requested' in routes:
        raise RuntimeError('Cash ledger void routes already exist.')
    start = routes.find(get_start)
    end = routes.find(get_end, start)
    if start < 0 or end < 0:
        raise RuntimeError('Cashbook route markers are invalid.')
    routes = routes[:start] + cashbook_get + routes[end:]
    if routes.count(route_marker) != 1:
        raise RuntimeError('Cashbook void route marker is invalid.')
    routes = routes.replace(route_marker, void_routes + route_marker, 1)
    view = read_remote(view_path)
    view = replace_once(view, old_header, new_header, 'cashbook ledger header')
    view = replace_once(view, old_cell, new_cell, 'cashbook ledger actions')
    view = replace_once(view, 'colspan="6" style="padding:28px;text-align:center;color:#667085">Ch&#432;a c&#243; giao d&#7883;ch trong s&#7893; qu&#7929;.', 'colspan="7" style="padding:28px;text-align:center;color:#667085">Ch&#432;a c&#243; giao d&#7883;ch trong s&#7893; qu&#7929;.', 'cashbook ledger empty row')
    write_remote('/tmp/cash-ledger-void-phase64-routes.php', routes)
    write_remote('/tmp/cash-ledger-void-phase64-view.php', view)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase64.php'), '/tmp/cash-ledger-void-phase64-seed.php')
    sftp.close()
    schema = "CREATE TABLE cash_ledger_void_requests (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,ledger_entry_id INTEGER NOT NULL UNIQUE,reason TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','approved','rejected')),created_by INTEGER NOT NULL,approved_by INTEGER,approved_at TEXT,rejection_reason TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(ledger_entry_id) REFERENCES cash_ledger_entries(id)); CREATE INDEX idx_cash_ledger_void_status ON cash_ledger_void_requests(status,created_at);"
    result = run("chown www-data:www-data /tmp/cash-ledger-void-phase64-* && runuser -u www-data -- php /tmp/cash-ledger-void-phase64-seed.php && " + f"sqlite3 {DB} \"{schema}\" && install -o www-data -g www-data -m 0644 /tmp/cash-ledger-void-phase64-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/cash-ledger-void-phase64-view.php {view_path} && php -l {routes_path} && php -l {view_path} && rm -f /tmp/cash-ledger-void-phase64-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('CASHBOOK_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/cashbook"))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='finance.ledger.void';\""))
    print('VOID_TABLE=' + run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND name='cash_ledger_void_requests';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/cashbook.php && cp {backup}/cashbook.php {view_path}; rm -f /tmp/cash-ledger-void-phase64-*; chown www-data:www-data {DB} {routes_path} {view_path}")
    client.close()
    raise

client.close()
