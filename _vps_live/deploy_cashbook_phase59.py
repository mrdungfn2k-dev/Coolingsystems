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
backup = f'/var/backups/coolingsystems/cashbook-phase59-{stamp}'
routes_path = APP + '/routes/admin.php'
header_path = APP + '/views/partials/dashboard-head.php'
view_path = APP + '/views/admin/cashbook.php'
route_marker = "get('/admin/warranties', function() {"

route = r'''get('/admin/cashbook', function() {
    requireStaffPermission('rbac:finance.cashbook.view|tax_config','/admin/login');
    $selectedAccount=max(0,(int)($_GET['account']??0));$fromDate=trim($_GET['from']??'');$toDate=trim($_GET['to']??'');
    if($fromDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$fromDate))$fromDate='';if($toDate!==''&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$toDate))$toDate='';
    $accounts=dbAll("SELECT account.*,COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE -entry.amount END),0) AS balance FROM cash_accounts account LEFT JOIN cash_ledger_entries entry ON entry.account_id=account.id AND entry.voided_at IS NULL WHERE account.is_active=1 GROUP BY account.id ORDER BY account.sort_order,account.id");
    if($selectedAccount&&!dbGet('SELECT id FROM cash_accounts WHERE id=? AND is_active=1',[$selectedAccount]))$selectedAccount=0;
    $where=['entry.voided_at IS NULL'];$params=[];if($selectedAccount){$where[]='entry.account_id=?';$params[]=$selectedAccount;}if($fromDate!==''){$where[]='entry.entry_date>=?';$params[]=$fromDate;}if($toDate!==''){$where[]='entry.entry_date<=?';$params[]=$toDate;}$sqlWhere=implode(' AND ',$where);
    $entries=dbAll("SELECT entry.*,account.name AS account_name FROM cash_ledger_entries entry INNER JOIN cash_accounts account ON account.id=entry.account_id WHERE $sqlWhere ORDER BY entry.entry_date DESC,entry.id DESC LIMIT 500",$params);
    $totalRow=dbGet("SELECT COALESCE(SUM(CASE WHEN entry.direction='in' THEN entry.amount ELSE 0 END),0) AS income,COALESCE(SUM(CASE WHEN entry.direction='out' THEN entry.amount ELSE 0 END),0) AS expense FROM cash_ledger_entries entry WHERE $sqlWhere",$params)?:['income'=>0,'expense'=>0];$totals=['income'=>(int)$totalRow['income'],'expense'=>(int)$totalRow['expense'],'net'=>(int)$totalRow['income']-(int)$totalRow['expense']];
    view('admin/cashbook',['title'=>'S&#7893; qu&#7929;','userRole'=>'admin','accounts'=>$accounts,'entries'=>$entries,'totals'=>$totals,'selectedAccount'=>$selectedAccount,'fromDate'=>$fromDate,'toDate'=>$toDate]);
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {header_path} {backup}/dashboard-head.php")
    tables = run(f"sqlite3 {DB} \"SELECT name FROM sqlite_master WHERE type='table' AND name IN ('cash_accounts','cash_ledger_entries');\"")
    if tables:
        raise RuntimeError('Cashbook tables already exist; phase 59 will not overwrite them.')
    run(f"sqlite3 {DB} \"CREATE TABLE cash_accounts (id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT NOT NULL UNIQUE,name TEXT NOT NULL,type TEXT NOT NULL CHECK(type IN ('cash','bank')),is_active INTEGER NOT NULL DEFAULT 1,sort_order INTEGER NOT NULL DEFAULT 0,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))); CREATE TABLE cash_ledger_entries (id INTEGER PRIMARY KEY AUTOINCREMENT,account_id INTEGER NOT NULL,direction TEXT NOT NULL CHECK(direction IN ('in','out')),amount INTEGER NOT NULL CHECK(amount>0),reference_type TEXT,reference_id INTEGER,reference_code TEXT,description TEXT,entry_date TEXT NOT NULL,created_by INTEGER,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),voided_at TEXT,FOREIGN KEY(account_id) REFERENCES cash_accounts(id)); CREATE INDEX idx_cash_ledger_account_date ON cash_ledger_entries(account_id,entry_date); CREATE INDEX idx_cash_ledger_reference ON cash_ledger_entries(reference_type,reference_id); INSERT INTO cash_accounts (code,name,type,sort_order) VALUES ('TM-TIENMAT','Tiền mặt','cash',1),('NH-NGANHANG','Ngân hàng','bank',2);\"")
    routes = read_remote(routes_path)
    if "get('/admin/cashbook', function()" in routes:
        raise RuntimeError('Cashbook route already exists.')
    routes = replace_once(routes, route_marker, route + route_marker, 'cashbook route marker')
    write_remote('/tmp/cashbook-phase59-routes.php', routes)
    header = read_remote(header_path)
    marker = "<?php if($sb('orders')||$sb('returns')||$sb('create_order')): ?>"
    menu = "<?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'finance.cashbook.view'))): ?>\n      <div class=\"sb-section\">T&#192;I CH&#205;NH<span class=\"sb-sec-desc\">Qu&#7929; &middot; Thu chi</span></div>\n      <a href=\"/admin/cashbook\" class=\"<?= startsWith(currentPath(),'/admin/cashbook')?'active':'' ?>\"><?= sbIcon('list') ?>S&#7893; qu&#7929;</a>\n      <?php endif; ?>\n      " + marker
    header = replace_once(header, marker, menu, 'cashbook menu marker')
    write_remote('/tmp/cashbook-phase59-head.php', header)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase59.php'), '/tmp/cashbook-phase59-seed.php')
    sftp.put(str(ROOT / 'cashbook_phase59.php'), '/tmp/cashbook-phase59-view.php')
    sftp.close()
    result = run("chown www-data:www-data /tmp/cashbook-phase59-* && runuser -u www-data -- php /tmp/cashbook-phase59-seed.php && " + f"install -o www-data -g www-data -m 0644 /tmp/cashbook-phase59-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/cashbook-phase59-head.php {header_path} && install -o www-data -g www-data -m 0644 /tmp/cashbook-phase59-view.php {view_path} && php -l {routes_path} && php -l {header_path} && php -l {view_path} && rm -f /tmp/cashbook-phase59-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('CASHBOOK_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/cashbook"))
    print('ACCOUNTS=' + run(f"sqlite3 -separator '|' {DB} \"SELECT code,type FROM cash_accounts ORDER BY sort_order;\""))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='finance.cashbook.view';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; test -f {backup}/dashboard-head.php && cp {backup}/dashboard-head.php {header_path}; rm -f {view_path} /tmp/cashbook-phase59-*; chown www-data:www-data {DB} {routes_path} {header_path}")
    client.close()
    raise

client.close()
