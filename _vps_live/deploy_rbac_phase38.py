import sys
from datetime import datetime
from pathlib import Path
import paramiko
sys.stdout.reconfigure(encoding='utf-8')
ROOT='/opt/coolingsystems'; DB='/var/lib/coolingsystems/cooling.db'; LOCAL=Path('cooling-php/_vps_live')
def once(t,o,n,label):
 c=t.count(o)
 if c!=1: raise RuntimeError(f'{label}: expected one match, got {c}')
 return t.replace(o,n,1)
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
def run(cmd):
 _,o,e=c.exec_command(cmd,timeout=90); out=o.read().decode('utf-8','replace'); err=e.read().decode('utf-8','replace'); status=o.channel.recv_exit_status()
 if status: raise RuntimeError(f'{cmd}\n{out}\n{err}')
 return out.strip()
def get(p):
 s=c.open_sftp()
 try:
  with s.open(p,'rb') as f: return f.read().decode('utf-8')
 finally: s.close()
def put(p,d):
 s=c.open_sftp()
 try:
  with s.open(p,'wb') as f: f.write(d.encode('utf-8'))
 finally: s.close()
paths={'rbac':ROOT+'/includes/rbac.php','routes':ROOT+'/routes/admin.php'}; stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/rbac-phase38-{stamp}'
baseline=run("sqlite3 -separator '|' "+DB+" \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {paths['rbac']} {backup}/rbac.php; cp {paths['routes']} {backup}/admin.php")
try:
 routes=get(paths['routes'])
 routes=once(routes,"get('/admin/users', function() {\n    requireStaffPermission('users', '/auth/login');", "get('/admin/users', function() {\n    $user = requireStaffPermission('rbac:customers.view|users', '/auth/login');\n    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);\n    $canViewCustomerPii = !$detailedRbac || rbacCan((int)$user['id'], 'customers.pii.view');",'customer list gate')
 routes=routes.replace("    $users=dbAll(\"SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?\",$p2);\n", "    $users=dbAll(\"SELECT u.* FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?\",$p2);\n    if (!$canViewCustomerPii) { foreach ($users as &$customerRow) { $customerRow['email']='***'; $customerRow['phone']='***'; $customerRow['address']='***'; } unset($customerRow); }\n",1)
 routes=once(routes,"get('/admin/users/:id/invoice-info', function($p) {\n    $user = requireStaffPermission('users|staff', '/auth/login');\n    header('Content-Type: application/json');", "get('/admin/users/:id/invoice-info', function($p) {\n    $user = requireStaffPermission('rbac:customers.view|users|staff', '/auth/login');\n    header('Content-Type: application/json');\n    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']) && !rbacCan((int)$user['id'], 'customers.pii.view')) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }",'invoice pii gate')
 routes=once(routes,"get('/admin/users/:id/detail', function($p) {\n    $user = requireStaffPermission('users|staff', '/auth/login');\n    header('Content-Type: application/json');", "get('/admin/users/:id/detail', function($p) {\n    $user = requireStaffPermission('rbac:customers.view|users|staff', '/auth/login');\n    header('Content-Type: application/json');",'customer detail gate')
 routes=once(routes,"    $invoice = dbGet(\"SELECT * FROM user_invoice_info WHERE user_id=?\", [$p['id']]);\n    $orderCount", "    $canViewCustomerPii = !(($user['role'] ?? '') === 'staff' && rbacUsesDetailedMode((int)$user['id'])) || rbacCan((int)$user['id'], 'customers.pii.view');\n    $invoice = $canViewCustomerPii ? dbGet(\"SELECT * FROM user_invoice_info WHERE user_id=?\", [$p['id']]) : null;\n    if (!$canViewCustomerPii) { $u['email']='***'; $u['phone']='***'; $u['address']='***'; }\n    $orderCount",'customer detail masking')
 put('/tmp/rbac38.php',(LOCAL/'rbac.php').read_text(encoding='utf-8')); put('/tmp/rbac38-seed.php',(LOCAL/'seed_rbac_phase38.php').read_text(encoding='utf-8')); put('/tmp/rbac38-admin.php',routes)
 run('chown www-data:www-data /tmp/rbac38.php /tmp/rbac38-seed.php /tmp/rbac38-admin.php; runuser -u www-data -- php /tmp/rbac38-seed.php')
 run(f'install -o www-data -g www-data -m 0644 /tmp/rbac38.php {paths["rbac"]}; install -o www-data -g www-data -m 0644 /tmp/rbac38-admin.php {paths["routes"]}; php -l {paths["rbac"]}; php -l {paths["routes"]}; rm -f /tmp/rbac38.php /tmp/rbac38-seed.php /tmp/rbac38-admin.php')
except Exception:
 run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/rbac.php {paths["rbac"]}; cp {backup}/admin.php {paths["routes"]}; rm -f /tmp/rbac38.php /tmp/rbac38-seed.php /tmp/rbac38-admin.php'); raise
after=run("sqlite3 -separator '|' "+DB+" \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP='+backup); print('BASELINE='+baseline); print('AFTER='+after); c.close()
