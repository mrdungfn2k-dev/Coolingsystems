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

routes = read('/opt/coolingsystems/routes/admin.php')
view = read('/opt/coolingsystems/views/admin/warranties.php')
checks = {
    'PRODUCT_SEARCH_API': "get('/admin/warranties/products'" in routes,
    'API_PERMISSION_GATE': "rbac:warranty.cases.view|returns" in routes,
    'NAME_MAX_100': "mb_strlen($customerName)>100" in routes,
    'PHONE_10_DIGITS_03_09': "^0[3-9]\\d{8}$" in routes,
    'DESCRIPTION_200_WORDS': "$words>200" in routes,
    'PRODUCT_TYPEAHEAD_FIELD': 'id="productSearch"' in view and 'id="productSuggestions"' in view,
    'OLD_PRODUCT_SELECT_REMOVED': '<select name="product_id"' not in view,
    'PHONE_UI_LIMIT': 'maxlength="10"' in view and 'pattern="0[3-9][0-9]{8}"' in view,
    'DESCRIPTION_UI_COUNTER': 'id="wordCount"' in view,
}
for name, result in checks.items():
    print(name + '=' + ('ok' if result else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
