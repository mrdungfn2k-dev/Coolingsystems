import sys
from datetime import datetime
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
APP='/opt/coolingsystems'; DB='/var/lib/coolingsystems/cooling.db'
client=paramiko.SSHClient(); client.set_missing_host_key_policy(paramiko.AutoAddPolicy()); client.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)

def run(command):
    _,out,err=client.exec_command(command,timeout=90); stdout=out.read().decode('utf-8','replace'); stderr=err.read().decode('utf-8','replace')
    if out.channel.recv_exit_status(): raise RuntimeError(command+'\n'+stdout+'\n'+stderr)
    return stdout.strip()

def read_remote(path):
    sftp=client.open_sftp(); handle=sftp.open(path,'rb'); value=handle.read().decode('utf-8'); handle.close(); sftp.close(); return value

def write_remote(path,value):
    sftp=client.open_sftp(); handle=sftp.open(path,'wb'); handle.write(value.encode('utf-8')); handle.close(); sftp.close()

stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/purchase-order-approval-phase70-{stamp}'
routes_path=APP+'/routes/admin.php'; view_path=APP+'/views/admin/purchase-requests.php'; start="get('/admin/purchase-requests',function()"; end="post('/admin/purchase-requests',function()"; marker="get('/admin/warranties', function() {"

get_route=r'''get('/admin/purchase-requests',function(){$u=requireStaffPermission('rbac:purchasing.requests.create|rbac:purchasing.requests.approve|rbac:purchasing.orders.create|rbac:purchasing.orders.approve|tax_config','/admin/login');$suppliers=dbAll("SELECT id,code,name FROM suppliers WHERE is_active=1 ORDER BY name");$low=dbAll("SELECT id,sku,oem_code,name,stock,min_stock,max_stock FROM products WHERE status!='deleted' AND stock<=min_stock ORDER BY stock,min_stock,name LIMIT 500");$items=dbAll("SELECT pr.*,s.name AS supplier_name,u.full_name AS creator_name,COUNT(DISTINCT pri.id) AS item_count,po.id AS po_id,po.code AS po_code,po.status AS po_status,po.created_by AS po_created_by,po.rejection_reason AS po_rejection_reason FROM purchase_requests pr INNER JOIN suppliers s ON s.id=pr.supplier_id LEFT JOIN users u ON u.id=pr.created_by LEFT JOIN purchase_request_items pri ON pri.request_id=pr.id LEFT JOIN purchase_orders po ON po.source_request_id=pr.id GROUP BY pr.id ORDER BY CASE pr.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,pr.created_at DESC LIMIT 200");$canCreate=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.requests.create');$canApprove=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.requests.approve');$canCreateOrder=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.orders.create');$canApproveOrder=(($u['role']??'')==='admin')||rbacHasCapability((int)$u['id'],'purchasing.orders.approve');view('admin/purchase-requests',['title'=>'Yêu cầu mua hàng','userRole'=>'admin','suppliers'=>$suppliers,'low'=>$low,'items'=>$items,'canCreate'=>$canCreate,'canApprove'=>$canApprove,'canCreateOrder'=>$canCreateOrder,'canApproveOrder'=>$canApproveOrder,'currentUserId'=>(int)$u['id']]);});
'''

routes_add=r'''post('/admin/purchase-orders/:id/approve',function($p){$u=requireStaffPermission('rbac:purchasing.orders.approve|tax_config','/admin/login');csrfCheck();$po=dbGet('SELECT * FROM purchase_orders WHERE id=?',[$p['id']]);if(!$po||$po['status']!=='draft'||(int)$po['created_by']===(int)$u['id']){flash('error','Không thể duyệt PO này hoặc người tạo đang tự duyệt.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_orders SET status='approved',approved_by=?,approved_at=datetime('now','localtime') WHERE id=? AND status='draft'",[$u['id'],$po['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_order_approved','purchase_order',$po['id'],json_encode(['code'=>$po['code']],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã duyệt PO '.$po['code'].'.');redirect('/admin/purchase-requests');});
post('/admin/purchase-orders/:id/reject',function($p){$u=requireStaffPermission('rbac:purchasing.orders.approve|tax_config','/admin/login');csrfCheck();$reason=trim($_POST['po_rejection_reason']??'');$po=dbGet('SELECT * FROM purchase_orders WHERE id=?',[$p['id']]);if(!$po||$po['status']!=='draft'||(int)$po['created_by']===(int)$u['id']||mb_strlen($reason)<5||mb_strlen($reason)>300){flash('error','Cần lý do từ chối 5-300 ký tự và người duyệt phải khác người tạo PO.');redirect('/admin/purchase-requests');}dbRun("UPDATE purchase_orders SET status='rejected',approved_by=?,approved_at=datetime('now','localtime'),rejection_reason=? WHERE id=? AND status='draft'",[$u['id'],$reason,$po['id']]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$u['id'],$u['role']??'admin','purchase_order_rejected','purchase_order',$po['id'],json_encode(['reason'=>$reason],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);flash('success','Đã từ chối PO '.$po['code'].'.');redirect('/admin/purchase-requests');});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {view_path} {backup}/purchase-requests.php")
    if run(f"sqlite3 {DB} \"SELECT COUNT(*) FROM pragma_table_info('purchase_orders') WHERE name='approved_by';\"")!='0': raise RuntimeError('P83 schema already exists.')
    routes=read_remote(routes_path); a=routes.find(start); b=routes.find(end,a)
    if a<0 or b<0 or 'purchase_order_approved' in routes or routes.count(marker)!=1: raise RuntimeError('P83 route markers are invalid.')
    routes=routes[:a]+get_route+routes[b:]; routes=routes.replace(marker,routes_add+marker,1); write_remote('/tmp/p70-routes.php',routes)
    view=read_remote(view_path)
    old='<th>PO</th><th>Thao tác</th>'
    new='<th>PO</th><th>Duyệt PO</th><th>Thao tác YC</th>'
    if view.count(old)!=1: raise RuntimeError('P83 view header marker invalid.')
    view=view.replace(old,new,1)
    old_cell="<td><?= e($i['po_code'] ?: '-') ?></td><td>"
    new_cell="""<td><?= e($i['po_code'] ?: '-') ?><?php if($i['po_code']): ?><div class=\"fs-11 text-muted\"><?= $i['po_status']==='approved'?'Đã duyệt':($i['po_status']==='rejected'?'Từ chối':'Nháp') ?></div><?php if($i['po_rejection_reason']): ?><div class=\"fs-11 text-muted\"><?= e($i['po_rejection_reason']) ?></div><?php endif; ?><?php endif; ?></td><td><?php if($i['po_id']&&$i['po_status']==='draft'&&$canApproveOrder&&(int)$i['po_created_by']!==$currentUserId): ?><form method=\"post\" action=\"/admin/purchase-orders/<?= (int)$i['po_id'] ?>/approve\" style=\"display:flex;gap:5px;min-width:300px\"><?= csrfField() ?><input name=\"po_rejection_reason\" maxlength=\"300\" placeholder=\"Lý do nếu từ chối\"><button class=\"btn btn-sm btn-navy\">Duyệt PO</button><button class=\"btn btn-sm btn-outline-navy\" formaction=\"/admin/purchase-orders/<?= (int)$i['po_id'] ?>/reject\">Từ chối</button></form><?php elseif($i['po_id']&&$i['po_status']==='draft'&&(int)$i['po_created_by']===$currentUserId): ?><span class=\"fs-11 text-muted\">Không thể tự duyệt PO</span><?php else: ?>-<?php endif; ?></td><td>"""
    if view.count(old_cell)!=1: raise RuntimeError('P83 view action marker invalid.')
    view=view.replace(old_cell,new_cell,1).replace('colspan="7" style="padding:25px;text-align:center">Chưa có yêu cầu mua.','colspan="8" style="padding:25px;text-align:center">Chưa có yêu cầu mua.',1); write_remote('/tmp/p70-view.php',view)
    seed="<?php $p=new PDO('sqlite:"+DB+"');$p->prepare('INSERT OR REPLACE INTO rbac_capability_rules (capability,permission_code,allowed_levels) VALUES (?,?,?)')->execute(['purchasing.orders.approve','P83',json_encode(['TQ','QL'])]);"; write_remote('/tmp/p70-seed.php',seed)
    schema="ALTER TABLE purchase_orders ADD COLUMN approved_by INTEGER;ALTER TABLE purchase_orders ADD COLUMN approved_at TEXT;ALTER TABLE purchase_orders ADD COLUMN rejection_reason TEXT;"
    result=run("chown www-data:www-data /tmp/p70-* && runuser -u www-data -- php /tmp/p70-seed.php && "+f"sqlite3 {DB} \"{schema}\" && install -o www-data -g www-data -m 0644 /tmp/p70-routes.php {routes_path} && install -o www-data -g www-data -m 0644 /tmp/p70-view.php {view_path} && php -l {routes_path} && php -l {view_path} && rm -f /tmp/p70-*")
    print('BACKUP='+backup); print('LINT='+result); print('HOME='+run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/")); print('P83='+run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE permission_code='P83';\""))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\";test -f {backup}/admin.php && cp {backup}/admin.php {routes_path};test -f {backup}/purchase-requests.php && cp {backup}/purchase-requests.php {view_path};rm -f /tmp/p70-*;chown www-data:www-data {DB} {routes_path} {view_path}"); client.close(); raise
client.close()
