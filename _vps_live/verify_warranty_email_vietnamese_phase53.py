import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)
sftp = client.open_sftp()
with sftp.open('/opt/coolingsystems/routes/admin.php', 'rb') as handle:
    routes = handle.read().decode('utf-8')
sftp.close()
checks = {
    'VIETNAMESE_EMAIL_TITLE': 'Phi&#7871;u b&#7843;o h&#224;nh' in routes,
    'VIETNAMESE_EMAIL_GREETING': 'Xin ch&#224;o' in routes,
    'VIETNAMESE_EMAIL_LABELS': 'M&#227; phi&#7871;u' in routes and 'S&#7843;n ph&#7849;m' in routes and 'N&#7897;i dung y&#234;u c&#7847;u' in routes,
    'UTF8_EMAIL_SUBJECT': "html_entity_decode('Phi&#7871;u b&#7843;o h&#224;nh',ENT_QUOTES,'UTF-8').' '.$code" in routes,
}
for name, passed in checks.items():
    print(name + '=' + ('ok' if passed else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
