import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

def read(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        value = handle.read().decode('utf-8')
    sftp.close()
    return value

def run(command):
    _, out, err = client.exec_command(command, timeout=30)
    value = out.read().decode('utf-8', 'replace') + err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(value)
    return value.strip()

routes = read('/opt/coolingsystems/routes/admin.php')
warranties = read('/opt/coolingsystems/views/admin/warranties.php')
hub = read('/opt/coolingsystems/views/admin/warranty-documents.php')
print_view = read('/opt/coolingsystems/views/admin/warranty-document-print.php')
checks = {
    'P112_CAPABILITY': 'warranty.documents.print|P112' in run("sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='warranty.documents.print';\""),
    'P112_CATALOG_ENTRY': bool(run("sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT code FROM rbac_permissions WHERE code='P112';\"")),
    'DOCUMENT_ROUTE_PERMISSION': "rbac:warranty.documents.print|returns" in routes,
    'HANDOVER_REQUIRES_COMPLETED': "$type==='handover'&&$case['status']!=='completed'" in routes,
    'PRINT_AUDIT_LOG': "'warranty_document_printed'" in routes,
    'WARRANTY_DOCUMENT_LINK': '/documents">Ch&#7913;ng t&#7915;</a>' in warranties,
    'THREE_DOCUMENT_TYPES': '/documents/receipt' in hub and '/documents/warranty' in hub and '/documents/handover' in hub,
    'PRINT_LAYOUT': '@media print' in print_view and 'window.print()' in print_view,
}
for name, passed in checks.items():
    print(name + '=' + ('ok' if passed else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
