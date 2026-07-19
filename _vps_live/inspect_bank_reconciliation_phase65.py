import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command):
    _, out, err = client.exec_command(command, timeout=60)
    stdout = out.read().decode('utf-8', 'replace')
    stderr = err.read().decode('utf-8', 'replace')
    return f'$ {command}\n{stdout}{stderr}'


print(run("php -l /opt/coolingsystems/routes/admin.php; php -l /opt/coolingsystems/views/partials/dashboard-head.php; php -l /opt/coolingsystems/views/admin/bank-reconciliation.php"))
print(run("curl -s -o /dev/null -w 'HOME=%{http_code}\\n' https://coolingsystems.vn/; curl -s -o /dev/null -w 'CASHBOOK=%{http_code}\\n' https://coolingsystems.vn/admin/cashbook; curl -s -o /dev/null -w 'BANK_RECON=%{http_code}\\n' https://coolingsystems.vn/admin/bank-reconciliation"))
print(run("sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT recon.id, account.name, entry.reference_code FROM bank_reconciliation_transactions recon INNER JOIN cash_accounts account ON account.id=recon.account_id LEFT JOIN cash_ledger_entries entry ON entry.id=recon.ledger_entry_id ORDER BY recon.transaction_date DESC, recon.id DESC LIMIT 3;\""))
print(run("tail -n 80 /var/log/nginx/error.log 2>/dev/null; tail -n 120 /var/log/php*-fpm.log 2>/dev/null; journalctl -u php*-fpm --no-pager -n 120 2>/dev/null"))
client.close()
