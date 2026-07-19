import re
import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = Path('cooling-php/_vps_live')
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(command, timeout=90):
    _, out, err = client.exec_command(command, timeout=timeout)
    stdout = out.read().decode('utf-8', 'replace')
    stderr = err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()


def read_remote(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        value = handle.read().decode('utf-8')
    sftp.close()
    return value


def write_remote(path, value):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(value.encode('utf-8'))
    sftp.close()


def replace_once(source, old, new, label):
    if source.count(old) != 1:
        raise RuntimeError(f'{label}: expected one match, got {source.count(old)}')
    return source.replace(old, new, 1)


def replace_in_route(source, start_marker, end_marker, old, new, label):
    start = source.find(start_marker)
    end = source.find(end_marker, start)
    if start < 0 or end < 0:
        raise RuntimeError(f'{label}: route markers were not found')
    block = replace_once(source[start:end], old, new, label)
    return source[:start] + block + source[end:]


stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-performance-{stamp}'
routes_path = APP + '/routes/admin.php'
header_path = APP + '/views/partials/dashboard-head.php'
warranties_path = APP + '/views/admin/warranties.php'
performance_path = APP + '/views/admin/warranty-performance.php'
insert_marker = "get('/admin/warranties/:id/documents', function($p) {"

performance_routes = r'''get('/admin/warranties/performance', function() {
    $actor=requireStaffPermission('rbac:warranty.performance.view|returns','/admin/login');
    $technicians=dbAll("SELECT DISTINCT user.id,user.full_name,user.phone FROM users user INNER JOIN staff_role_assignments assignment ON assignment.user_id=user.id INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE user.role='staff' AND user.status='active' AND link.rbac_role_code='TECH' ORDER BY user.full_name");
    $activeCases=dbAll("SELECT warranty.*,product.name AS product_name,product.sku,technician.full_name AS technician_name FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id LEFT JOIN users technician ON technician.id=warranty.assigned_to WHERE warranty.status IN ('approved','assigned','in_progress') ORDER BY warranty.updated_at DESC LIMIT 300");
    $summary=dbGet("SELECT COUNT(*) AS total,SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,SUM(CASE WHEN status IN ('assigned','in_progress') THEN 1 ELSE 0 END) AS active,SUM(CASE WHEN (SELECT COUNT(*) FROM warranty_cases prior WHERE prior.customer_phone=warranty.customer_phone AND prior.product_id=warranty.product_id)>1 THEN 1 ELSE 0 END) AS repeat_cases FROM warranty_cases warranty") ?: [];
    $performance=dbAll("SELECT COALESCE(technician.full_name,'') AS technician_name,COUNT(*) AS total_cases,SUM(CASE WHEN warranty.status IN ('assigned','in_progress') THEN 1 ELSE 0 END) AS active_cases,SUM(CASE WHEN warranty.status='completed' THEN 1 ELSE 0 END) AS completed_cases,ROUND(AVG(CASE WHEN warranty.status='completed' THEN julianday(warranty.updated_at)-julianday(warranty.created_at) END),1) AS average_days,SUM(CASE WHEN (SELECT COUNT(*) FROM warranty_cases prior WHERE prior.customer_phone=warranty.customer_phone AND prior.product_id=warranty.product_id)>1 THEN 1 ELSE 0 END) AS repeat_cases FROM warranty_cases warranty LEFT JOIN users technician ON technician.id=warranty.assigned_to GROUP BY warranty.assigned_to,technician.full_name ORDER BY completed_cases DESC,total_cases DESC,technician_name ASC");
    $canAssign=(($actor['role']??'')==='admin') || rbacHasCapability((int)$actor['id'],'warranty.assign');
    view('admin/warranty-performance',['title'=>'Hi&#7879;u su&#7845;t k&#7929; thu&#7853;t','userRole'=>'admin','technicians'=>$technicians,'activeCases'=>$activeCases,'summary'=>$summary,'performance'=>$performance,'canAssign'=>$canAssign]);
});
post('/admin/warranties/:id/assign', function($p) {
    $actor=requireStaffPermission('rbac:warranty.assign|returns','/admin/login'); csrfCheck();
    $case=dbGet("SELECT id,status,assigned_to FROM warranty_cases WHERE id=?",[$p['id']]);
    if(!$case){flash('error','Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.');redirect('/admin/warranties/performance');}
    if(!in_array($case['status'],['approved','assigned','in_progress'],true)){flash('error','Ch&#7881; ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n cho phi&#7871;u &#273;&#227; duy&#7879;t ho&#7863;c &#273;ang x&#7917; l&#253;.');redirect('/admin/warranties/performance');}
    $technician=dbGet("SELECT user.id,user.full_name FROM users user INNER JOIN staff_role_assignments assignment ON assignment.user_id=user.id INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE user.id=? AND user.role='staff' AND user.status='active' AND link.rbac_role_code='TECH'",[(int)($_POST['assigned_to']??0)]);
    if(!$technician){flash('error','K&#7929; thu&#7853;t vi&#234;n kh&#244;ng h&#7907;p l&#7879; ho&#7863;c ch&#432;a &#273;&#432;&#7907;c g&#225;n vai tr&#242; TECH.');redirect('/admin/warranties/performance');}
    $nextStatus=$case['status']==='approved'?'assigned':$case['status'];
    dbRun("UPDATE warranty_cases SET assigned_to=?,status=?,updated_at=datetime('now','localtime') WHERE id=?",[$technician['id'],$nextStatus,$case['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_technician_assigned','warranty_case',$case['id'],json_encode(['assigned_to'=>$technician['id'],'technician'=>$technician['full_name'],'status'=>$nextStatus],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);
    flash('success','&#272;&#227; ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n cho phi&#7871;u b&#7843;o h&#224;nh.');redirect('/admin/warranties/performance');
});
'''

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {routes_path} {backup}/admin.php && cp {header_path} {backup}/dashboard-head.php && cp {warranties_path} {backup}/warranties.php")
    columns = run(f"sqlite3 {DB} \"PRAGMA table_info(warranty_cases);\"")
    if '|assigned_to|' not in columns:
        run(f"sqlite3 {DB} \"ALTER TABLE warranty_cases ADD COLUMN assigned_to INTEGER; CREATE INDEX IF NOT EXISTS idx_warranty_cases_assigned_to ON warranty_cases(assigned_to);\"")

    routes = read_remote(routes_path)
    if 'warranty.performance.view' in routes:
        raise RuntimeError('Warranty performance feature already exists.')
    routes = replace_once(routes, insert_marker, performance_routes + insert_marker, 'performance route marker')
    status_start = "post('/admin/warranties/:id/status', function($p) {"
    status_end = "get('/admin/staff', function() {"
    routes = replace_in_route(routes, status_start, status_end, "$case=dbGet('SELECT id,status FROM warranty_cases WHERE id=?',[$p['id']]);", "$case=dbGet('SELECT id,status,assigned_to FROM warranty_cases WHERE id=?',[$p['id']]);", 'status case query')
    status_update = "dbRun(\"UPDATE warranty_cases SET status=?,updated_at=datetime('now','localtime') WHERE id=?\",[$status,$p['id']]);"
    status_guard = "if(in_array($status,['assigned','in_progress'],true)&&empty($case['assigned_to'])){flash('error','H&#227;y ph&#226;n c&#244;ng k&#7929; thu&#7853;t vi&#234;n tr&#432;&#7899;c khi chuy&#7875;n phi&#7871;u sang b&#432;&#7899;c n&#224;y.');redirect('/admin/warranties/performance');}\n    "
    routes = replace_in_route(routes, status_start, status_end, status_update, status_guard + status_update, 'status assignment guard')
    write_remote('/tmp/warranty-phase58-routes.php', routes)

    header = read_remote(header_path)
    warranty_menu = "<?php if($sb('warranties')): ?><a href=\"/admin/warranties\" class=\"<?= startsWith(currentPath(),'/admin/warranties')?'active':'' ?>\"><?= sbIcon('tool') ?>Bảo hành & Kỹ thuật</a><?php endif; ?>"
    performance_menu = warranty_menu + "\n      <?php if($__isAdmin || ($__sbU && function_exists('rbacCan') && rbacCan((int)$__sbU['id'],'warranty.performance.view'))): ?><a href=\"/admin/warranties/performance\" class=\"<?= currentPath()==='/admin/warranties/performance'?'active':'' ?>\"><?= sbIcon('chart') ?>Hiệu suất kỹ thuật</a><?php endif; ?>"
    header = replace_once(header, warranty_menu, performance_menu, 'warranty performance menu')
    write_remote('/tmp/warranty-phase58-head.php', header)

    warranties = read_remote(warranties_path)
    warranties, changes = re.subn(r"status\.textContent\.trim\(\)==='[^']*'", "status.textContent.toLowerCase().indexOf('nghi')!==-1", warranties, count=1)
    if changes != 1:
        raise RuntimeError('Document button condition was not found for encoding-safe replacement.')
    write_remote('/tmp/warranty-phase58-warranties.php', warranties)

    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase58.php'), '/tmp/warranty-phase58-seed.php')
    sftp.put(str(ROOT / 'warranty-performance_phase58.php'), '/tmp/warranty-phase58-performance.php')
    sftp.close()
    result = run(
        "chown www-data:www-data /tmp/warranty-phase58-* && "
        "runuser -u www-data -- php /tmp/warranty-phase58-seed.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/warranty-phase58-routes.php {routes_path} && "
        f"install -o www-data -g www-data -m 0644 /tmp/warranty-phase58-head.php {header_path} && "
        f"install -o www-data -g www-data -m 0644 /tmp/warranty-phase58-warranties.php {warranties_path} && "
        f"install -o www-data -g www-data -m 0644 /tmp/warranty-phase58-performance.php {performance_path} && "
        f"php -l {routes_path} && php -l {header_path} && php -l {warranties_path} && php -l {performance_path} && rm -f /tmp/warranty-phase58-*"
    )
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('PERFORMANCE_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties/performance"))
    print('CAPABILITY=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='warranty.performance.view';\""))
    print('ASSIGNMENT_COLUMN=' + run(f"sqlite3 {DB} \"PRAGMA table_info(warranty_cases);\" | grep assigned_to"))
    print('DOCUMENT_UI_GUARD=' + run(f"grep -o \"indexOf('nghi')\" {warranties_path} | wc -l"))
except Exception:
    run(
        f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; "
        f"test -f {backup}/admin.php && cp {backup}/admin.php {routes_path}; "
        f"test -f {backup}/dashboard-head.php && cp {backup}/dashboard-head.php {header_path}; "
        f"test -f {backup}/warranties.php && cp {backup}/warranties.php {warranties_path}; "
        f"rm -f {performance_path} /tmp/warranty-phase58-*; chown www-data:www-data {DB} {routes_path} {header_path} {warranties_path}"
    )
    client.close()
    raise

client.close()
