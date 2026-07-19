import sys
from datetime import datetime
import paramiko
sys.stdout.reconfigure(encoding='utf-8');A='/opt/coolingsystems';D='/var/lib/coolingsystems/cooling.db';c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
def run(x):
 _,o,e=c.exec_command(x,timeout=60);a=o.read().decode('utf8','replace');b=e.read().decode('utf8','replace');s=o.channel.recv_exit_status()
 if s:raise RuntimeError(x+'\n'+a+'\n'+b)
 return a.strip()
def rd(x):
 s=c.open_sftp();h=s.open(x,'rb');v=h.read().decode();h.close();s.close();return v
def put(x,v):
 s=c.open_sftp();h=s.open(x,'wb');h.write(v.encode());h.close();s.close()
st=datetime.now().strftime('%Y%m%d-%H%M%S');bk=f'/var/backups/coolingsystems/rbac-phase48-{st}'
old="$purchase=trim($_POST['purchase_date']??'') ?: date('Y-m-d');$months=max(0,(int)$product['warranty_months']);$end=date('Y-m-d',strtotime('+'.$months.' months',strtotime($purchase)));$code='BH'.date('ymdHis').random_int(10,99);"
new="$purchase=trim($_POST['purchase_date']??'') ?: date('Y-m-d');$serialNo=trim($_POST['serial_no']??'');$serial=$serialNo ? dbGet('SELECT product_id,warranty_end_date FROM product_serials WHERE serial_no=?',[$serialNo]) : null;if($serial && (int)$serial['product_id']!==(int)$product['id']){flash('error','Serial không thuộc sản phẩm đã chọn.');redirect('/admin/warranties');}$months=max(0,(int)$product['warranty_months']);$end=$serial ? $serial['warranty_end_date'] : date('Y-m-d',strtotime('+'.$months.' months',strtotime($purchase)));$code='BH'.date('ymdHis').random_int(10,99);"
try:
 run(f"mkdir -p {bk} && cp {D} {bk}/cooling.db && cp {A}/routes/admin.php {bk}/admin.php")
 r=rd(A+'/routes/admin.php')
 if r.count(old)!=1:raise RuntimeError('warranty calculation marker missing')
 put('/tmp/r48-admin.php',r.replace(old,new,1))
 out=run(f"chown www-data:www-data /tmp/r48-admin.php && install -o www-data -g www-data -m 0644 /tmp/r48-admin.php {A}/routes/admin.php && php -l {A}/routes/admin.php && rm -f /tmp/r48-admin.php")
 print('BACKUP='+bk);print('RESULT='+out);print('HOME='+run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
except Exception:
 run(f"test -f {bk}/cooling.db && cp {bk}/cooling.db {D};test -f {bk}/admin.php && cp {bk}/admin.php {A}/routes/admin.php;rm -f /tmp/r48-admin.php;chown www-data:www-data {D} {A}/routes/admin.php")
 c.close();raise
c.close()
