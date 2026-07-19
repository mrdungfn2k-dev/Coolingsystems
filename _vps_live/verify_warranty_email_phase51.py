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
    'PHONE_SERVER_03_05_07_08_09': "^0[35789]\\d{8}$" in routes,
    'PHONE_UI_03_05_07_08_09': 'pattern="0[35789][0-9]{8}"' in view,
    'PHONE_INLINE_FEEDBACK': 'validatePhone' in view and 'phoneHint' in view,
    'CUSTOMER_EMAIL_UI_REQUIRED': 'name="customer_email" type="email" required' in view,
    'CUSTOMER_EMAIL_SERVER_VALIDATED': 'filter_var($customerEmail,FILTER_VALIDATE_EMAIL)' in routes,
    'CUSTOMER_EMAIL_PERSISTED': 'customer_phone,customer_email,serial_no' in routes,
    'WARRANTY_EMAIL_SENT': '$mailSent=sendEmail($customerEmail' in routes,
    'SMTP_FAILURE_KEEPS_CASE': "flash('warning'" in routes,
}
for name, passed in checks.items():
    print(name + '=' + ('ok' if passed else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
