import paramiko
import sys
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')
remote = '/opt/coolingsystems/routes/admin.php'
local = 'cooling-php/_vps_live/phase3_smtp_route.php'
with open(local, encoding='utf-8') as f: replacement = f.read()

c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
s=c.open_sftp()
with s.open(remote,'rb') as f: source=f.read().decode('utf-8')
start=source.find("post('/admin/settings/smtp', function() {")
end=source.find("post('/admin/settings/inventory-alert', function() {",start)
if start<0 or end<0: raise RuntimeError('SMTP route boundaries not found')
stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/smtp-normalize-{stamp}.php'
_,out,err=c.exec_command(f'cp {remote} {backup}')
if out.channel.recv_exit_status()!=0: raise RuntimeError(err.read().decode())
with s.open(remote,'wb') as f: f.write((source[:start]+replacement+'\n'+source[end:]).encode('utf-8'))
s.close()
cmd="chown coolingsystems:www-data /opt/coolingsystems/routes/admin.php && sqlite3 /var/lib/coolingsystems/cooling.db \"UPDATE settings SET value=replace(replace(value,' ',''),char(10),'') WHERE key='smtp_password';\" && php -l /opt/coolingsystems/routes/admin.php"
_,out,err=c.exec_command(cmd,timeout=30); result=out.read().decode('utf-8','replace'); error=err.read().decode('utf-8','replace')
if out.channel.recv_exit_status()!=0: raise RuntimeError(result+'\n'+error)
print('BACKUP='+backup); print(result)
c.close()
