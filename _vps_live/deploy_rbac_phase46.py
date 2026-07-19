import sys
from datetime import datetime
from pathlib import Path
import paramiko
sys.stdout.reconfigure(encoding='utf-8')
L=Path('cooling-php/_vps_live'); H='103.97.134.164'; U='root'; P='lcBFDjVF15'; A='/opt/coolingsystems'; D='/var/lib/coolingsystems/cooling.db'
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect(H,username=U,password=P,timeout=30)
def run(x):
 _,o,e=c.exec_command(x,timeout=90);a=o.read().decode('utf8','replace');b=e.read().decode('utf8','replace');s=o.channel.recv_exit_status()
 if s: raise RuntimeError(x+'\n'+a+'\n'+b)
 return a.strip()
def read(x):
 s=c.open_sftp();h=s.open(x,'rb');v=h.read().decode();h.close();s.close();return v
def put(x,v):
 s=c.open_sftp();h=s.open(x,'wb');h.write(v.encode());h.close();s.close()
def once(x,a,b,n):
 if x.count(a)!=1:raise RuntimeError(n)
 return x.replace(a,b,1)
stamp=datetime.now().strftime('%Y%m%d-%H%M%S');bk=f'/var/backups/coolingsystems/rbac-phase46-{stamp}'
routes=r'''get('/admin/warranties', function() {
    requireStaffPermission('rbac:warranty.cases.view|returns', '/admin/login');
    $cases=dbAll("SELECT warranty.*,product.name AS product_name,product.sku FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id ORDER BY warranty.updated_at DESC LIMIT 300");
    $products=dbAll("SELECT id,sku,name FROM products ORDER BY name LIMIT 3000");
    view('admin/warranties',['title'=>'Bảo hành & Kỹ thuật','userRole'=>'admin','cases'=>$cases,'products'=>$products]);
});
post('/admin/warranties', function() {
    $actor=requireStaffPermission('rbac:warranty.cases.create|returns','/admin/login');csrfCheck();$product=dbGet('SELECT id,warranty_months FROM products WHERE id=?',[(int)($_POST['product_id']??0)]);
    if(!$product){flash('error','Chọn sản phẩm hợp lệ.');redirect('/admin/warranties');}$purchase=trim($_POST['purchase_date']??'') ?: date('Y-m-d');$months=max(0,(int)$product['warranty_months']);$end=date('Y-m-d',strtotime('+'.$months.' months',strtotime($purchase)));$code='BH'.date('ymdHis').random_int(10,99);
    $id=dbInsert('INSERT INTO warranty_cases (case_code,product_id,order_code,customer_name,customer_phone,serial_no,issue_description,purchase_date,warranty_end_date,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)',[$code,$product['id'],trim($_POST['order_code']??''),trim($_POST['customer_name']??''),trim($_POST['customer_phone']??''),trim($_POST['serial_no']??''),trim($_POST['issue_description']??''),$purchase,$end,'received',$actor['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role'],'warranty_case_created','warranty_case',$id,json_encode(['case_code'=>$code,'product_id'=>$product['id']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã tạo phiếu bảo hành '.$code.'.');redirect('/admin/warranties');
});
post('/admin/warranties/:id/status', function($p) {
    $actor=requireStaffPermission('rbac:warranty.cases.view|returns','/admin/login');csrfCheck();$status=$_POST['status']??'';$cap=['checking'=>'warranty.eligibility.check','approved'=>'warranty.approve','assigned'=>'warranty.assign','in_progress'=>'warranty.progress.update','completed'=>'warranty.close','rejected'=>'warranty.approve'][$status]??null;if(!$cap||!rbacHasCapability((int)$actor['id'],$cap)){if(($actor['role']??'')!=='admin'){flash('error','Bạn không có quyền cập nhật trạng thái này.');redirect('/admin/warranties');}}
    dbRun("UPDATE warranty_cases SET status=?,updated_at=datetime('now','localtime') WHERE id=?",[$status,$p['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id'],$actor['role'],'warranty_case_status','warranty_case',$p['id'],json_encode(['status'=>$status]),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã cập nhật phiếu bảo hành.');redirect('/admin/warranties');
});

'''
try:
 run(f"mkdir -p {bk} && cp {D} {bk}/cooling.db && cp {A}/routes/admin.php {bk}/admin.php && cp {A}/includes/rbac.php {bk}/rbac.php && cp {A}/views/partials/dashboard-head.php {bk}/head.php")
 r=read(A+'/routes/admin.php');r=once(r,"get('/admin/staff', function() {",routes+"get('/admin/staff', function() {",'route marker')
 rb=read(A+'/includes/rbac.php');rb=once(rb,"'returns' => 'sales.returns.view',","'returns' => 'sales.returns.view',\n        'warranties' => 'warranty.cases.view',",'rbac menu')
 h=read(A+'/views/partials/dashboard-head.php');h=once(h,"<?php if($sb('returns')): ?><a href=\"/admin/returns\" class=\"<?= startsWith(currentPath(),'/admin/returns')?'active':'' ?>\">", "<?php if($sb('returns')): ?><a href=\"/admin/returns\" class=\"<?= startsWith(currentPath(),'/admin/returns')?'active':'' ?>\">",'head marker');h=h.replace("<?php endif; ?>\n      <?php endif; ?>\n      <?php if($__isAdmin||$sb('users')", "<?php endif; ?>\n      <?php if($sb('warranties')): ?><a href=\"/admin/warranties\" class=\"<?= startsWith(currentPath(),'/admin/warranties')?'active':'' ?>\"><?= sbIcon('tool') ?>Bảo hành & Kỹ thuật</a><?php endif; ?>\n      <?php endif; ?>\n      <?php if($__isAdmin||$sb('users')",1)
 put('/tmp/r46-admin.php',r);put('/tmp/r46-rbac.php',rb);put('/tmp/r46-head.php',h);s=c.open_sftp();s.put(str(L/'seed_rbac_phase46.php'),'/tmp/r46-seed.php');s.put(str(L/'warranties_phase46.php'),'/tmp/r46-view.php');s.close()
 out=run(f"chown www-data:www-data /tmp/r46-* && runuser -u www-data -- php /tmp/r46-seed.php && install -o www-data -g www-data -m 0644 /tmp/r46-admin.php {A}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/r46-rbac.php {A}/includes/rbac.php && install -o www-data -g www-data -m 0644 /tmp/r46-head.php {A}/views/partials/dashboard-head.php && install -o www-data -g www-data -m 0644 /tmp/r46-view.php {A}/views/admin/warranties.php && php -l {A}/routes/admin.php && php -l {A}/includes/rbac.php && php -l {A}/views/admin/warranties.php && rm -f /tmp/r46-*")
 print('BACKUP='+bk);print('RESULT='+out);print('HOME='+run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"));print('WARRANTIES='+run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties"))
except Exception:
 run(f"test -f {bk}/cooling.db && cp {bk}/cooling.db {D}; test -f {bk}/admin.php && cp {bk}/admin.php {A}/routes/admin.php; test -f {bk}/rbac.php && cp {bk}/rbac.php {A}/includes/rbac.php; test -f {bk}/head.php && cp {bk}/head.php {A}/views/partials/dashboard-head.php; rm -f {A}/views/admin/warranties.php /tmp/r46-*;chown www-data:www-data {D} {A}/routes/admin.php {A}/includes/rbac.php {A}/views/partials/dashboard-head.php")
 c.close();raise
c.close()
