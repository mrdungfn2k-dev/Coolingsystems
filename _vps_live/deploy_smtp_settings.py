import paramiko
import sys
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')
HOST='103.97.134.164'; USER='root'; PASSWORD='lcBFDjVF15'; ROOT='/opt/coolingsystems'
LOCAL={'mailer':'cooling-php/_vps_live/phase3_mailer.php','card':'cooling-php/_vps_live/phase3_smtp_settings.php','route':'cooling-php/_vps_live/phase3_smtp_route.php'}
def local(path):
    with open(path,encoding='utf-8') as f: return f.read()
def once(source,old,new,label):
    count=source.count(old)
    if count!=1: raise RuntimeError(f'{label}: expected one match, found {count}')
    return source.replace(old,new,1)
def run(c,command):
    _,out,err=c.exec_command(command,timeout=60); value=out.read().decode('utf-8','replace'); error=err.read().decode('utf-8','replace'); code=out.channel.recv_exit_status()
    if code: raise RuntimeError(f'{command}\n{value}\n{error}')
    return value.strip()
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect(HOST,username=USER,password=PASSWORD,timeout=30)
stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/smtp-settings-{stamp}'
run(c,f"mkdir -p {backup} && cp {ROOT}/includes/mailer.php {backup}/mailer.php && cp {ROOT}/routes/admin.php {backup}/admin.php && cp {ROOT}/views/admin/settings.php {backup}/settings.php && sqlite3 /var/lib/coolingsystems/cooling.db '.backup {backup}/cooling.db'")
s=c.open_sftp()
def read(path):
    with s.open(path,'rb') as f: return f.read().decode('utf-8')
def write(path,value):
    with s.open(path,'wb') as f: f.write(value.encode('utf-8'))
admin=read(f'{ROOT}/routes/admin.php'); settings=read(f'{ROOT}/views/admin/settings.php')
if "post('/admin/settings/smtp'" not in admin:
    admin=once(admin,"post('/admin/settings/inventory-alert', function() {",local(LOCAL['route'])+'\npost(\'/admin/settings/inventory-alert\', function() {','insert smtp route')
if 'Cấu hình SMTP gửi email' not in settings:
    settings_marker = '  <?php $inventoryAlertEmail = dbGet("SELECT value FROM settings WHERE key=\'inventory_alert_email\'")[\'value\'] ?? \'\';'
    settings=once(settings,settings_marker,local(LOCAL['card'])+'\n'+settings_marker,'insert smtp card')
settings=once(settings,'.settings-card input[type=text], .settings-card input[type=url], .settings-card input[type=email], .settings-card input[type=tel],','.settings-card input[type=text], .settings-card input[type=url], .settings-card input[type=email], .settings-card input[type=password], .settings-card input[type=tel],','password field style')
write(f'{ROOT}/routes/admin.php',admin); write(f'{ROOT}/views/admin/settings.php',settings)
s.put(LOCAL['mailer'],f'{ROOT}/includes/mailer.php'); s.close()
run(c,f"chown coolingsystems:www-data {ROOT}/includes/mailer.php {ROOT}/routes/admin.php {ROOT}/views/admin/settings.php")
for path in [f'{ROOT}/includes/mailer.php',f'{ROOT}/routes/admin.php',f'{ROOT}/views/admin/settings.php',f'{ROOT}/includes/inventory-alerts.php']:
    print(run(c,f'php -l {path}'))
print('BACKUP='+backup)
print('HOME='+run(c,"curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
print('SETTINGS='+run(c,"curl -sS -o /dev/null -w '%{http_code} %{redirect_url}' https://coolingsystems.vn/admin/settings"))
c.close()
