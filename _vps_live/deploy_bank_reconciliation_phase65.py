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
backup = f'/var/backups/coolingsystems/bank-reconciliation-phase65-{stamp}'
routes_path = APP + '/routes/admin.php'
header_path = APP + '/views/partials/dashboard-head.php'
view_path = APP + '/views/admin/bank-reconciliation.php'
route_marker = "get('/admin/warranties', function() {"

routes_add = r'''get('/admin/bank-reconciliation', function() {
    $user=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');$bankAccounts=dbAll("SELECT id,code,name FROM cash_accounts WHERE type='bank' AND is_active=1 ORDER BY sort_order,id");$bankLedgerEntries=dbAll("SELECT entry.id,entry.entry_date,entry.direction,entry.amount,entry.reference_code FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id LEFT JOIN bank_reconciliation_transactions transaction ON transaction.ledger_entry_id=entry.id AND transaction.status='matched' WHERE account.type='bank' AND account.is_active=1 AND entry.voided_at IS NULL AND transaction.id IS NULL ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 300");$transactions=dbAll("SELECT transaction.*,account.name AS account_name,entry.reference_code AS ledger_reference FROM bank_reconciliation_transactions transaction INNER JOIN cash_accounts account ON account.id=transaction.account_id LEFT JOIN cash_ledger_entries entry ON entry.id=transaction.ledger_entry_id ORDER BY transaction.transaction_date DESC,transaction.id DESC LIMIT 300");$summary=dbGet("SELECT SUM(CASE WHEN status='unmatched' THEN 1 ELSE 0 END) AS unmatched,SUM(CASE WHEN status='matched' THEN 1 ELSE 0 END) AS matched,COALESCE(SUM(CASE WHEN status='unmatched' THEN amount ELSE 0 END),0) AS unmatched_amount FROM bank_reconciliation_transactions")?:['unmatched'=>0,'matched'=>0,'unmatched_amount'=>0];$canManage=(($user['role']??'')==='admin')||rbacHasCapability((int)$user['id'],'finance.bank_reconciliation.manage');view('admin/bank-reconciliation',['title'=>'&#272;&#7889;i so&#225;t ng&#226;n h&#224;ng/QR','userRole'=>'admin','bankAccounts'=>$bankAccounts,'bankLedgerEntries'=>$bankLedgerEntries,'transactions'=>$transactions,'summary'=>$summary,'canManage'=>$canManage]);
});
post('/admin/bank-reconciliation', function() {
    $actor=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');csrfCheck();$accountId=(int)($_POST['account_id']??0);$direction=$_POST['direction']??'';$rawAmount=preg_replace('/\D/','',$_POST['amount']??'');$amount=(int)$rawAmount;$date=trim($_POST['transaction_date']??'');$reference=trim($_POST['bank_reference']??'');$description=trim($_POST['description']??'');$ledgerId=(int)($_POST['ledger_entry_id']??0);$words=$description===''?0:count(preg_split('/\s+/u',$description));
    if(!$accountId||!in_array($direction,['in','out'],true)||$rawAmount===''||$amount<1||$amount>999999999999||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||mb_strlen($reference)>120||$words>200){flash('error','D&#7919; li&#7879;u giao d&#7883;ch ng&#226;n h&#224;ng kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/bank-reconciliation');}$account=dbGet("SELECT id FROM cash_accounts WHERE id=? AND type='bank' AND is_active=1",[$accountId]);if(!$account){flash('error','T&#224;i kho&#7843;n ng&#226;n h&#224;ng kh&#244;ng h&#7907;p l&#7879;.');redirect('/admin/bank-reconciliation');}$status='unmatched';$matchedAt=null;
    if($ledgerId){$entry=dbGet("SELECT entry.id FROM cash_ledger_entries entry WHERE entry.id=? AND entry.account_id=? AND entry.direction=? AND entry.amount=? AND entry.voided_at IS NULL",[$ledgerId,$accountId,$direction,$amount]);if(!$entry||dbGet("SELECT id FROM bank_reconciliation_transactions WHERE ledger_entry_id=? AND status='matched'",[$ledgerId])){flash('error','B&#250;t to&#225;n qu&#7929; kh&#244;ng kh&#7899;p t&#224;i kho&#7843;n, chi&#7873;u giao d&#7883;ch ho&#7863;c s&#7889; ti&#7873;n.');redirect('/admin/bank-reconciliation');}$status='matched';$matchedAt=date('Y-m-d H:i:s');}
    $code='DST-'.date('Ymd-His').'-'.str_pad((string)random_int(1,9999),4,'0',STR_PAD_LEFT);$id=dbInsert("INSERT INTO bank_reconciliation_transactions (code,account_id,direction,amount,transaction_date,bank_reference,description,status,ledger_entry_id,created_by,matched_by,matched_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",[$code,$accountId,$direction,$amount,$date,$reference,$description,$status,$ledgerId?:null,$actor['id']??null,$status==='matched'?($actor['id']??null):null,$matchedAt]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','bank_reconciliation_created','bank_reconciliation_transaction',$id,json_encode(['code'=>$code,'status'=>$status,'ledger_entry_id'=>$ledgerId?:null],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; ghi nh&#7853;n giao d&#7883;ch '. $code .($status==='matched'?' v&#224; &#273;&#7889;i so&#225;t th&#224;nh c&#244;ng.':' ch&#7901; &#273;&#7889;i so&#225;t.'));redirect('/admin/bank-reconciliation');
});
post('/admin/bank-reconciliation/:id/match', function($p) {
    $actor=requireStaffPermission('rbac:finance.bank_reconciliation.manage|tax_config','/admin/login');csrfCheck();$ledgerId=(int)($_POST['ledger_entry_id']??0);$transaction=dbGet("SELECT * FROM bank_reconciliation_transactions WHERE id=?",[$p['id']]);if(!$transaction||$transaction['status']!=='unmatched'||!$ledgerId){flash('error','Kh&#244;ng th&#7875; &#273;&#7889;i so&#225;t giao d&#7883;ch.');redirect('/admin/bank-reconciliation');}$entry=dbGet("SELECT entry.id FROM cash_ledger_entries entry WHERE entry.id=? AND entry.account_id=? AND entry.direction=? AND entry.amount=? AND entry.voided_at IS NULL",[$ledgerId,$transaction['account_id'],$transaction['direction'],$transaction['amount']]);if(!$entry||dbGet("SELECT id FROM bank_reconciliation_transactions WHERE ledger_entry_id=? AND status='matched'",[$ledgerId])){flash('error','B&#250;t to&#225;n qu&#7929; kh&#244;ng kh&#7899;p ho&#7863;c &#273;&#227; &#273;&#432;&#7907;c &#273;&#7889;i so&#225;t.');redirect('/admin/bank-reconciliation');}dbRun("UPDATE bank_reconciliation_transactions SET status='matched',ledger_entry_id=?,matched_by=?,matched_at=datetime('now','localtime'),updated_at=datetime('now','localtime') WHERE id=? AND status='unmatched'",[$ledgerId,$actor['id']??null,$transaction['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','bank_reconciliation_matched','bank_reconciliation_transaction',$transaction['id'],json_encode(['ledger_entry_id'=>$ledgerId],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','&#272;&#227; &#273;&#7889;i so&#225;t giao d&#7883;ch th&#224;nh c&#244;ng.');redirect('/admin/bank-reconciliation');
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {header_path} {backup}/dashboard-head.php")
    if run(f"sqlite3 {DB} \"SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='bank_reconciliation_transactions';\"") != '0':
        raise RuntimeError('Bank reconciliation table already exists; phase 65 will not overwrite it.')
    routes = read_remote(routes_path)
    if 'bank_reconciliation_created' in routes or routes.count(route_marker) != 1:
        raise RuntimeError('Bank reconciliation route marker is invalid or routes already exist.')
    routes = routes.replace(route_marker, routes_add + route_marker, 1)
    header = read_remote(header_path)
    old_link = '<a href="/admin/cashbook" class="<?= startsWith(currentPath(),\'/admin/cashbook\')?\'active\':\'\' ?>"><?= sbIcon(\'list\') ?>S&#7893; qu&#7929;</a>'
    new_link = old_link + '\n      <?php if($__isAdmin || ($__sbU && function_exists(\'rbacCan\') && rbacCan((int)$__sbU[\'id\'],\'finance.bank_reconciliation.manage\'))): ?>\n      <a href="/admin/bank-reconciliation" class="<?= startsWith(currentPath(),\'/admin/bank-reconciliation\')?\'active\':\'\' ?>"><?= sbIcon(\'list\') ?>&#272;&#7889;i so&#225;t NH/QR</a>\n      <?php endif; ?>'
    header = replace_once(header, old_link, new_link, 'bank-reconciliation menu link')
    write_remote('/tmp/bank-reconciliation-phase65-routes.php', routes)
    write_remote('/tmp/bank-reconciliation-phase65-head.php', header)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase65.php'), '/tmp/bank-reconciliation-phase65-seed.php')
    sftp.put(str(ROOT / 'bank-reconciliation_phase65.php'), '/tmp/bank-reconciliation-phase65-view.php')
    sftp.close()
    schema = "CREATE TABLE bank_reconciliation_transactions (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,account_id INTEGER NOT NULL,direction TEXT NOT NULL CHECK(direction IN ('in','out')),amount INTEGER NOT NULL CHECK(amount>0),transaction_date TEXT NOT NULL,bank_reference TEXT,description TEXT,status TEXT NOT NULL DEFAULT 'unmatched' CHECK(status IN ('unmatched','matched')),ledger_entry_id INTEGER,created_by INTEGER NOT NULL,matched_by INTEGER,matched_at TEXT,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(account_id) REFERENCES cash_accounts(id),FOREIGN KEY(ledger_entry_id) REFERENCES cash_ledger_entries(id)); CREATE INDEX idx_bank_reconciliation_status ON bank_reconciliation_transactions(status,transaction_date); CREATE UNIQUE INDEX idx_bank_reconciliation_matched_ledger ON bank_reconciliation_transactions(ledger_entry_id) WHERE ledger_entry_id IS NOT NULL;"
    result = run("chown www-data:www-data /tmp/bank-reconciliation-phase65-* && runuser -u www-data -- php /tmp/bank-reconciliation-phase65-seed.php && " + f"sqlite3 {DB} \"{schema}\" && install -o www-data -g www-data -m 0644 /tmp/bank-reconciliation-phase65-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/bank-reconciliation-phase65-head.php {header_path} && install -o www-data -g www-data -m 0644 /tmp/bank-reconciliation-phase65-view.php {view_path} && php -l {routes_path} && php -l {header_path} && php -l {view_path} && rm -f /tmp/bank-reconciliation-phase65-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('BANK_RECON_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/bank-reconciliation"))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='finance.bank_reconciliation.manage';\""))
    print('RECON_TABLE=' + run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND name='bank_reconciliation_transactions';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/dashboard-head.php && cp {backup}/dashboard-head.php {header_path}; test -f {backup}/bank-reconciliation.php && cp {backup}/bank-reconciliation.php {view_path} || rm -f {view_path}; rm -f /tmp/bank-reconciliation-phase65-*; chown www-data:www-data {DB} {routes_path} {header_path}")
    client.close()
    raise

client.close()
