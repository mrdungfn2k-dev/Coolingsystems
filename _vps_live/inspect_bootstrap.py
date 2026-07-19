import paramiko
import sys
sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
for path in ['/opt/coolingsystems/index.php','/opt/coolingsystems/public/index.php','/opt/coolingsystems/config.php','/opt/coolingsystems/includes/config.php','/opt/coolingsystems/includes/db.php']:
 _,o,e=c.exec_command(f'sed -n \'1,100p\' {path} 2>/dev/null',timeout=30); print('\n--- '+path+' ---\n'+o.read().decode('utf-8','replace'))
c.close()
