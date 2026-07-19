import sys
from datetime import datetime
from pathlib import Path

import paramiko


sys.stdout.reconfigure(encoding='utf-8')
LOCAL = Path('cooling-php/_vps_live')
HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)


def run(command, timeout=90):
    _, stdout, stderr = client.exec_command(command, timeout=timeout)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    status = stdout.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{output}\n{error}')
    return output.strip()


def read_remote(path):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'rb') as handle:
            return handle.read().decode('utf-8')
    finally:
        sftp.close()


def put_text(path, content):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'wb') as handle:
            handle.write(content.encode('utf-8'))
    finally:
        sftp.close()


def replace_section(source, start_marker, end_marker, replacement, label):
    start = source.find(start_marker)
    end = source.find(end_marker, start)
    if start < 0 or end < 0:
        raise RuntimeError(f'{label}: section not found')
    return source[:start] + replacement + source[end:]


def counts():
    return run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from products').fetchone()[0],"
        "c.execute('select count(*) from users').fetchone()[0],"
        "c.execute('select count(*) from orders').fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0],"
        "c.execute(\\\"select count(*) from audit_logs where action like 'rbac_role_%'\\\").fetchone()[0]])))\""
    )


assign_routes = r'''get('/admin/staff/roles/:id/assign', function($p) {
    requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login');
    $staffRole = dbGet('SELECT * FROM staff_roles WHERE id=?', [$p['id']]);
    if (!$staffRole) { flash('error','Không tìm thấy vai trò.'); redirect('/admin/staff'); }
    $rbacTemplate = dbGet('SELECT rbac_role_code FROM rbac_staff_role_links WHERE staff_role_id=?', [$p['id']]);
    $matrixTaskCount = $rbacTemplate ? (int)(dbGet("SELECT COUNT(*) AS n FROM rbac_role_permissions WHERE role_code=? AND access_level<>'NONE'", [$rbacTemplate['rbac_role_code']])['n'] ?? 0) : 0;
    $assignedUsers = dbAll("SELECT u.full_name, u.email, sra.id AS assignment_id FROM staff_role_assignments sra INNER JOIN users u ON u.id=sra.user_id WHERE sra.role_id=?", [$p['id']]);
    $availableUsers = dbAll("SELECT id, full_name, email FROM users WHERE role='staff' AND status='active' AND id NOT IN (SELECT user_id FROM staff_role_assignments WHERE role_id=?) ORDER BY full_name", [$p['id']]);
    view('admin/role-assign', ['title'=>'Phân công nhân viên','userRole'=>'admin','staffRole'=>$staffRole,'rbacTemplate'=>$rbacTemplate,'matrixTaskCount'=>$matrixTaskCount,'assignedUsers'=>$assignedUsers,'availableUsers'=>$availableUsers]);
});

post('/admin/staff/roles/:id/assign', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $userId = (int)($_POST['user_id'] ?? 0);
    $staffRole = dbGet('SELECT id,name FROM staff_roles WHERE id=?', [$p['id']]);
    $target = $userId ? dbGet("SELECT id,full_name,role,status FROM users WHERE id=?", [$userId]) : null;
    if (!$staffRole || !$target || $target['role'] !== 'staff' || $target['status'] !== 'active') {
        flash('error','Chỉ có thể phân công cho tài khoản Nhân viên đang hoạt động.');
        redirect('/admin/staff/roles/' . $p['id'] . '/assign');
    }
    $insert = dbRun("INSERT OR IGNORE INTO staff_role_assignments (user_id, role_id, assigned_by) VALUES (?,?,?)", [$userId, $p['id'], $actor['id']]);
    if ($insert->rowCount() > 0) {
        $assignment = dbGet('SELECT id FROM staff_role_assignments WHERE user_id=? AND role_id=?', [$userId, $p['id']]);
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_assigned', 'staff_role_assignment', $assignment['id'] ?? null, json_encode(['target_user_id'=>$userId,'role_id'=>(int)$p['id'],'role_name'=>$staffRole['name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
        dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Phân quyền mới','Bạn đã được phân công vai trò: " . $staffRole['name'] . ". Đăng nhập lại để áp dụng quyền.','/staff',datetime('now','localtime'))", [$userId]);
        flash('success','Phân công thành công và đã ghi nhật ký.');
    } else {
        flash('info','Nhân viên này đã giữ vai trò được chọn.');
    }
    redirect('/admin/staff/roles/' . $p['id'] . '/assign');
});

'''

unassign_routes = r'''post('/admin/staff/unassign/:id', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $assignment = dbGet("SELECT assignment.id,assignment.user_id,assignment.role_id,staff_role.name AS role_name FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id WHERE assignment.id=?", [$p['id']]);
    if (!$assignment) { flash('error','Không tìm thấy phân công.'); redirect('/admin/staff'); }
    dbRun('DELETE FROM staff_role_assignments WHERE id=?', [$p['id']]);
    dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_unassigned', 'staff_role_assignment', $assignment['id'], json_encode(['target_user_id'=>(int)$assignment['user_id'],'role_id'=>(int)$assignment['role_id'],'role_name'=>$assignment['role_name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Cập nhật quyền truy cập','Một vai trò nhân viên của bạn đã được thu hồi.','/staff',datetime('now','localtime'))", [$assignment['user_id']]);
    flash('success','Đã hủy phân quyền và ghi nhật ký. Tài khoản vẫn là Nhân viên để có thể phân công lại.');
    redirect('/admin/staff');
});

post('/admin/staff/unassign-all/:id', function($p) {
    $actor = requireRbacOrLegacyStaffPermission('system.rbac.manage', '/admin/login'); csrfCheck();
    $userId = (int)$p['id'];
    $assignments = dbAll("SELECT assignment.id,assignment.role_id,staff_role.name AS role_name FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id WHERE assignment.user_id=?", [$userId]);
    foreach ($assignments as $assignment) {
        dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)", [$actor['id'], $actor['role'], 'rbac_role_unassigned', 'staff_role_assignment', $assignment['id'], json_encode(['target_user_id'=>$userId,'role_id'=>(int)$assignment['role_id'],'role_name'=>$assignment['role_name']], JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? '', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255)]);
    }
    dbRun('DELETE FROM staff_role_assignments WHERE user_id=?', [$userId]);
    dbRun('DELETE FROM staff_permissions WHERE user_id=?', [$userId]);
    if ($assignments) dbInsert("INSERT INTO user_notifications (user_id,type,title,message,link,created_at) VALUES (?,'system','Cập nhật quyền truy cập','Toàn bộ vai trò nhân viên của bạn đã được thu hồi.','/staff',datetime('now','localtime'))", [$userId]);
    flash('success','Đã hủy toàn bộ quyền và ghi nhật ký. Tài khoản Nhân viên được giữ lại để có thể phân công lại.');
    redirect('/admin/staff');
});

'''

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/rbac-phase43-{stamp}'
baseline = counts()

try:
    run(f"mkdir -p {backup} && cp {DB} {backup}/cooling.db && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/role-assign.php {backup}/role-assign.php")
    routes = read_remote(f'{APP}/routes/admin.php')
    routes = replace_section(routes, "get('/admin/staff/roles/:id/assign', function($p) {", "post('/admin/staff/unassign/:id'", assign_routes, 'assignment routes')
    routes = replace_section(routes, "post('/admin/staff/unassign/:id'", "// ── STAFF DASHBOARD", unassign_routes, 'unassignment routes')
    put_text('/tmp/rbac43-admin.php', routes)
    sftp = client.open_sftp()
    try:
        sftp.put(str(LOCAL / 'role-assign_phase43.php'), '/tmp/rbac43-role-assign.php')
    finally:
        sftp.close()
    result = run(
        "chown www-data:www-data /tmp/rbac43-admin.php /tmp/rbac43-role-assign.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac43-admin.php {APP}/routes/admin.php && "
        f"install -o www-data -g www-data -m 0644 /tmp/rbac43-role-assign.php {APP}/views/admin/role-assign.php && "
        f"php -l {APP}/routes/admin.php && php -l {APP}/views/admin/role-assign.php && "
        "rm -f /tmp/rbac43-admin.php /tmp/rbac43-role-assign.php"
    )
    print('BACKUP=' + backup)
    print('BASELINE=' + baseline)
    print('RESULT=' + result)
    print('AFTER=' + counts())
    print('HTTP=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/staff/roles/15/assign"))
except Exception:
    try:
        run(
            f"test -f {backup}/cooling.db && cp {backup}/cooling.db {DB}; "
            f"test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; "
            f"test -f {backup}/role-assign.php && cp {backup}/role-assign.php {APP}/views/admin/role-assign.php; "
            f"chown www-data:www-data {DB} {APP}/routes/admin.php {APP}/views/admin/role-assign.php"
        )
    finally:
        client.close()
    raise

client.close()
