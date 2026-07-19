import paramiko
import sys
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')
ROOT='/opt/coolingsystems'
with open('cooling-php/_vps_live/phase3_smtp_settings.php',encoding='utf-8') as f: smtp_card=f.read()
with open('cooling-php/_vps_live/phase2_inventory_alert_routes.php',encoding='utf-8') as f: alert_routes=f.read()
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
s=c.open_sftp()
def read(path):
    with s.open(path,'rb') as f: return f.read().decode('utf-8')
def write(path,value):
    with s.open(path,'wb') as f: f.write(value.encode('utf-8'))
admin_path=f'{ROOT}/routes/admin.php'; settings_path=f'{ROOT}/views/admin/settings.php'
admin=read(admin_path); settings=read(settings_path)
settings_start=settings.find("  <?php $smtp = function_exists('smtpConfig')")
settings_end=settings.find("  <?php $inventoryAlertEmail",settings_start)
alert_start=admin.find("post('/admin/settings/inventory-alert', function() {")
alert_end=admin.find("post('/admin/settings/account', function() {",alert_start)
if min(settings_start,settings_end,alert_start,alert_end)<0: raise RuntimeError('Expected SMTP and alert boundaries were not found')
stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/smtp-feedback-{stamp}'
_,o,e=c.exec_command(f'mkdir -p {backup} && cp {admin_path} {backup}/admin.php && cp {settings_path} {backup}/settings.php');
if o.channel.recv_exit_status()!=0: raise RuntimeError(e.read().decode())
write(settings_path,settings[:settings_start]+smtp_card+'\n'+settings[settings_end:])
write(admin_path,admin[:alert_start]+alert_routes+'\n'+admin[alert_end:])
s.close()
_,o,e=c.exec_command(f'chown coolingsystems:www-data {admin_path} {settings_path} && php -l {admin_path} && php -l {settings_path}',timeout=30); result=o.read().decode('utf-8','replace'); error=e.read().decode('utf-8','replace')
if o.channel.recv_exit_status()!=0: raise RuntimeError(result+'\n'+error)
print('BACKUP='+backup); print(result)
c.close()
