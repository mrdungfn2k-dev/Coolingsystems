import sys
from datetime import datetime
import paramiko
sys.stdout.reconfigure(encoding='utf-8');A='/opt/coolingsystems';c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
def run(x):
 _,o,e=c.exec_command(x,timeout=60);a=o.read().decode('utf8','replace');b=e.read().decode('utf8','replace');s=o.channel.recv_exit_status()
 if s:raise RuntimeError(x+'\n'+a+'\n'+b)
 return a.strip()
def rd(x):
 s=c.open_sftp();h=s.open(x,'rb');v=h.read().decode();h.close();s.close();return v
def put(x,v):
 s=c.open_sftp();h=s.open(x,'wb');h.write(v.encode());h.close();s.close()
st=datetime.now().strftime('%Y%m%d-%H%M%S');bk=f'/var/backups/coolingsystems/rbac-phase49-{st}'
try:
 run(f"mkdir -p {bk} && cp {A}/views/partials/dashboard-head.php {bk}/head.php")
 h=rd(A+'/views/partials/dashboard-head.php')
 if "sb('serials')" not in h:
  h=h.replace("<?php if($sb('products')", "<?php if($sb('serials')||$sb('products')",1)
  m="<?php if($sb('categories')): ?>"
  if h.count(m)!=1:raise RuntimeError('serial menu marker missing')
  h=h.replace(m,"<?php if($sb('serials')): ?><a href=\"/admin/serials\" class=\"<?= startsWith(currentPath(),'/admin/serials')?'active':'' ?>\"><?= sbIcon('list') ?>Serial & Lô hàng</a><?php endif; ?>\n      "+m,1)
 put('/tmp/r49-head.php',h)
 out=run(f"chown www-data:www-data /tmp/r49-head.php && install -o www-data -g www-data -m 0644 /tmp/r49-head.php {A}/views/partials/dashboard-head.php && php -l {A}/views/partials/dashboard-head.php && rm -f /tmp/r49-head.php")
 print('BACKUP='+bk);print('RESULT='+out)
except Exception:
 run(f"test -f {bk}/head.php && cp {bk}/head.php {A}/views/partials/dashboard-head.php;rm -f /tmp/r49-head.php;chown www-data:www-data {A}/views/partials/dashboard-head.php")
 c.close();raise
c.close()
