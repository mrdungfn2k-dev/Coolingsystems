import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
local = 'cooling-php/_vps_live/test_saved_smtp.php'
remote = '/tmp/coolingsystems-smtp-test.php'
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
s=c.open_sftp(); s.put(local,remote); s.close()
_,out,err=c.exec_command(f'chown www-data:www-data {remote} && runuser -u www-data -- php {remote}; rm -f {remote}',timeout=60)
result=out.read().decode('utf-8','replace').strip(); error=err.read().decode('utf-8','replace').strip(); code=out.channel.recv_exit_status()
print('EXIT='+str(code)); print(result); print(error)
c.close()
