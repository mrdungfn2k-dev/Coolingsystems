import sys
from datetime import datetime
from pathlib import Path
import paramiko
sys.stdout.reconfigure(encoding='utf-8');L=Path('cooling-php/_vps_live');A='/opt/coolingsystems';D='/var/lib/coolingsystems/cooling.db';c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
def run(x):
 _,o,e=c.exec_command(x,timeout=90);a=o.read().decode('utf8','replace');b=e.read().decode('utf8','replace');s=o.channel.recv_exit_status()
 if s:raise RuntimeError(x+'\n'+a+'\n'+b)
 return a.strip()
def rd(x):
 s=c.open_sftp();h=s.open(x,'rb');v=h.read().decode();h.close();s.close();return v
def put(x,v):
 s=c.open_sftp();h=s.open(x,'wb');h.write(v.encode());h.close();s.close()
def once(x,a,b):
 if x.count(a)!=1:raise RuntimeError('marker missing')
 return x.replace(a,b,1)
routes=r'''get('/admin/serials', function() { requireStaffPermission('rbac:catalog.serials.manage|products','/admin/login'); $serials=dbAll("SELECT serial.*,product.name AS product_name,product.sku FROM product_serials serial INNER JOIN products product ON product.id=serial.product_id ORDER BY serial.created_at DESC LIMIT 500"); $products=dbAll("SELECT id,sku,name FROM products ORDER BY name LIMIT 3000"); view('admin/serials',['title'=>'Quản lý Serial & Lô hàng','userRole'=>'admin','serials'=>$serials,'products'=>$products]); });
post('/admin/serials', function() { $actor=requireStaffPermission('rbac:catalog.serials.manage|products','/admin/login');csrfCheck();$productId=(int)($_POST['product_id']??0);$serial=trim($_POST['serial_no']??'');$end=trim($_POST['warranty_end_date']??'');if(!dbGet('SELECT id FROM products WHERE id=?',[$productId])||!$serial||!$end){flash('error','Nhập đầy đủ sản phẩm, serial và hạn bảo hành.');redirect('/admin/serials');}try{$id=dbInsert('INSERT INTO product_serials (product_id,serial_no,manufactured_at,warranty_end_date,created_by) VALUES (?,?,?,?,?)',[$productId,$serial,trim($_POST['manufactured_at']??''),$end,$actor['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role'],'serial_created','product_serial',$id,json_encode(['product_id'=>$productId,'serial'=>$serial]),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã thêm serial.');}catch(Throwable $e){flash('error','Serial đã tồn tại hoặc dữ liệu không hợp lệ.');}redirect('/admin/serials'); });

'''
st=datetime.now().strftime('%Y%m%d-%H%M%S');bk=f'/var/backups/coolingsystems/rbac-phase47-{st}'
try:
 run(f"mkdir -p {bk} && cp {D} {bk}/cooling.db && cp {A}/routes/admin.php {bk}/admin.php && cp {A}/includes/rbac.php {bk}/rbac.php")
 r=once(rd(A+'/routes/admin.php'),"get('/admin/warranties', function() {",routes+"get('/admin/warranties', function() {");rb=once(rd(A+'/includes/rbac.php'),"'warranties' => 'warranty.cases.view',","'warranties' => 'warranty.cases.view',\n        'serials' => 'catalog.serials.manage',")
 put('/tmp/r47-admin.php',r);put('/tmp/r47-rbac.php',rb);s=c.open_sftp();s.put(str(L/'seed_rbac_phase47.php'),'/tmp/r47-seed.php');s.put(str(L/'serials_phase47.php'),'/tmp/r47-view.php');s.close()
 out=run(f"chown www-data:www-data /tmp/r47-* && runuser -u www-data -- php /tmp/r47-seed.php && install -o www-data -g www-data -m 0644 /tmp/r47-admin.php {A}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/r47-rbac.php {A}/includes/rbac.php && install -o www-data -g www-data -m 0644 /tmp/r47-view.php {A}/views/admin/serials.php && php -l {A}/routes/admin.php && php -l {A}/includes/rbac.php && php -l {A}/views/admin/serials.php && rm -f /tmp/r47-*")
 print('BACKUP='+bk);print('RESULT='+out);print('HOME='+run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"));print('SERIALS='+run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/serials"))
except Exception:
 run(f"test -f {bk}/cooling.db && cp {bk}/cooling.db {D};test -f {bk}/admin.php && cp {bk}/admin.php {A}/routes/admin.php;test -f {bk}/rbac.php && cp {bk}/rbac.php {A}/includes/rbac.php;rm -f {A}/views/admin/serials.php /tmp/r47-*;chown www-data:www-data {D} {A}/routes/admin.php {A}/includes/rbac.php")
 c.close();raise
c.close()
