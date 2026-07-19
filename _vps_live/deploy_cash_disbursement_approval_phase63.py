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
backup = f'/var/backups/coolingsystems/cash-disbursement-approval-phase63-{stamp}'
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
    $entries=dbAll("SELECT entry.*,account.name AS account_name FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id WHERE $sqlWhere ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 500",$params);
    $totalRow=dbGet("SELECT COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE 0 END),0) AS income,COALESCE(SUM(CASE WHEN entry.direction='out' THEN entry.amount ELSE 0 END),0) AS expense FROM cash_ledger_entries entry WHERE $sqlWhere",$params)?:['income'=>0,'expense'=>0];$totals=['income'=>(int)$totalRow['income'],'expense'=>(int)$totalRow['expense'],'net'=>(int)$totalRow['income']-(int)$totalRow['expense']];
    $canCreateReceipt=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.receipts.create');$canCreateDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.create');$canApproveDisbursement=(($user['role']??'')==='admin') || rbacHasCapability((int)$user['id'],'finance.disbursements.approve');$disbursements=dbAll("SELECT request.*,account.name AS account_name FROM cash_disbursement_requests request INNER JOIN cash_accounts account ON account.id=request.account_id ORDER BY CASE request.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,request.created_at DESC LIMIT 200");
    view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate,'canCreateReceipt'=>$canCreateReceipt,'canCreateDisbursement'=>$canCreateDisbursement,'canApproveDisbursement'=>$canApproveDisbursement,'currentUserId'=>(int)$user['id'],'disbursements'=>$disbursements]);
});
'''

approval_routes = r'''post('/admin/cashbook/disbursements/:id/approve', function($p) {
    $actor=requireStaffPermission('rbac:finance.disbursements.approve|tax_config','/admin/login');csrfCheck();$pdo=db();
    try{$pdo->beginTransaction();$request=dbGet('SELECT * FROM cash_disbursement_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending')throw new RuntimeException('not_pending');if((int)$request['created_by']===(int)$actor['id'])throw new RuntimeException('self_approval');$balanceRow=dbGet("SELECT COALESCE(SUM(CASE WHEN direction='in' THEN amount ELSE -amount END),0) AS balance FROM cash_ledger_entries WHERE account_id=? AND voided_at IS NULL",[$request['account_id']]);if((int)($balanceRow['balance']??0)<(int)$request['amount'])throw new RuntimeException('insufficient');$updated=dbRun("UPDATE cash_disbursement_requests SET status='approved',approved_by=?,approved_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$request['id']]);if($updated->rowCount()!==1)throw new RuntimeException('changed');$description=trim('Chi cho '.$request['payee_name'].($request['description']!==''?' - '.$request['description']:''));$entryId=dbInsert('INSERT INTO cash_ledger_entries (account_id,direction,amount,reference_type,reference_id,reference_code,description,payer_phone,payer_email,entry_date,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$request['account_id'],'out',$request['amount'],'cash_disbursement',$request['id'],$request['code'],$description,$request['payee_phone'],$request['payee_email'],$request['entry_date'],$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_approved','cash_disbursement_request',$request['id'],json_encode(['entry_id'=>$entryId,'code'=>$request['code'],'amount'=>$request['amount']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();flash('success','&#272;&#227; duy&#7879;t phi&#7871;u chi v&#224; ghi v&#224;o s&#7893; qu&#7929;.');}catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();$messages=['self_approval'=>'Ng&#432;&#7901;i l&#7853;p kh&#244;ng th&#7875; t&#7921; duy&#7879;t phi&#7871;u chi.','insufficient'=>'S&#7889; d&#432; qu&#7929; kh&#244;ng &#273;&#7911; &#273;&#7875; duy&#7879;t phi&#7871;u chi.','not_pending'=>'Phi&#7871;u chi kh&#244;ng c&#242;n ch&#7901; duy&#7879;t.'];flash('error',$messages[$exception->getMessage()]??'Kh&#244;ng th&#7875; duy&#7879;t phi&#7871;u chi.');}redirect('/admin/cashbook');
});
post('/admin/cashbook/disbursements/:id/reject', function($p) {
    $actor=requireStaffPermission('rbac:finance.disbursements.approve|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['rejection_reason']??'');$request=dbGet('SELECT id,status,created_by FROM cash_disbursement_requests WHERE id=?',[$p['id']]);if(!$request||$request['status']!=='pending'){flash('error','Phi&#7871;u chi kh&#244;ng c&#242;n ch&#7901; duy&#7879;t.');redirect('/admin/cashbook');}if((int)$request['created_by']===(int)$actor['id']){flash('error','Ng&#432;&#7901;i l&#7853;p kh&#244;ng th&#7875; t&#7921; t&#7915; ch&#7889;i phi&#7871;u chi.');redirect('/admin/cashbook');}if($reason===''||mb_strlen($reason)>300){flash('error','H&#227;y nh&#7853;p l&#253; do t&#7915; ch&#7889;i, t&#7889;i &#273;a 300 k&#253; t&#7921;.');redirect('/admin/cashbook');}dbRun("UPDATE cash_disbursement_requests SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=?,updated_at=datetime('now','localtime') WHERE id=? AND status='pending'",[$actor['id']??null,$reason,$request['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','cash_disbursement_rejected','cash_disbursement_request',$request['id'],json_encode(['reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; t&#7915; ch&#7889;i phi&#7871;u chi.');redirect('/admin/cashbook');
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/cashbook.php")
    routes = read_remote(routes_path)
    if 'cash_disbursement_approved' in routes:
        raise RuntimeError('Cash disbursement approval routes already exist.')
    start = routes.find(get_start)
    end = routes.find(get_end, start)
    if start < 0 or end < 0:
        raise RuntimeError('Cashbook route markers are invalid.')
    routes = routes[:start] + cashbook_get + routes[end:]
    if routes.count(route_marker) != 1:
        raise RuntimeError('Cashbook approval route marker is invalid.')
    routes = routes.replace(route_marker, approval_routes + route_marker, 1)
    write_remote('/tmp/cash-disbursement-approval-phase63-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase63.php'), '/tmp/cash-disbursement-approval-phase63-seed.php')
    sftp.put(str(ROOT / 'cashbook_phase59.php'), '/tmp/cash-disbursement-approval-phase63-view.php')
    sftp.close()
    result = run("chown www-data:www-data /tmp/cash-disbursement-approval-phase63-* && runuser -u www-data -- php /tmp/cash-disbursement-approval-phase63-seed.php && " + f"install -o www-data -g www-data -m 0644 /tmp/cash-disbursement-approval-phase63-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/cash-disbursement-approval-phase63-view.php {view_path} && php -l {routes_path} && php -l {view_path} && rm -f /tmp/cash-disbursement-approval-phase63-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('CASHBOOK_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/cashbook"))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='finance.disbursements.approve';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/cashbook.php && cp {backup}/cashbook.php {view_path}; rm -f /tmp/cash-disbursement-approval-phase63-*; chown www-data:www-data {DB} {routes_path} {view_path}")
    client.close()
    raise

client.close()
