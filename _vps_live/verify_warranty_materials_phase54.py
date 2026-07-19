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
materials = read('/opt/coolingsystems/views/admin/warranty-materials.php')
checks = {
    'MATERIAL_TABLE': 'warranty_materials' in run("sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT name FROM sqlite_master WHERE type='table' AND name='warranty_materials';\""),
    'P109_CAPABILITY': 'warranty.materials.consume|P109' in run("sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='warranty.materials.consume';\""),
    'MATERIAL_API_PERMISSION': "rbac:warranty.materials.consume|returns" in routes,
    'ONLY_APPROVED_CASES': "['approved','assigned','in_progress']" in routes,
    'ATOMIC_STOCK_DEDUCT': "stock=stock-?,updated_at=datetime('now','localtime') WHERE id=? AND stock>=?" in routes,
    'MATERIAL_AUDIT': "'warranty_material_issued'" in routes,
    'LOW_STOCK_ALERT': "inventoryCheckLowStockAlert($productId,'warranty_material')" in routes,
    'MATERIAL_SEARCH_UI': 'id="materialSearch"' in materials and 'id="materialSuggestions"' in materials,
    'WARRANTY_MATERIAL_LINK': "select.form.action.replace(/\\/status$/,'/materials')" in warranties,
}
for name, passed in checks.items():
    print(name + '=' + ('ok' if passed else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
