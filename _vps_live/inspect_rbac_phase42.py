import sys

import paramiko


HOST = '103.97.134.164'
USER = 'root'
PASSWORD = 'lcBFDjVF15'
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'

sys.stdout.reconfigure(encoding='utf-8')

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=30)


def run(command):
    _, stdout, stderr = client.exec_command(command, timeout=60)
    output = stdout.read().decode('utf-8', 'replace')
    error = stderr.read().decode('utf-8', 'replace')
    status = stdout.channel.recv_exit_status()
    if status:
        raise RuntimeError(f'{command}\n{output}\n{error}')
    return output


def read(path):
    sftp = client.open_sftp()
    try:
        with sftp.open(path, 'rb') as handle:
            return handle.read().decode('utf-8')
    finally:
        sftp.close()


try:
    auth = read(f'{APP}/includes/auth.php')
    print('AUTH_GATE')
    auth_start = auth.find('function requireStaffPermission')
    auth_end = auth.find('function requireRbacOrLegacyStaffPermission', auth_start)
    print(auth[auth_start:auth_end])
    admin = read(f'{APP}/routes/admin.php')
    print('STAFF_ROUTES')
    start = admin.find("get('/admin/staff', function()")
    end = admin.find("post('/admin/staff/unassign/:id'", start)
    print(admin[start:end])
    print('DB_COUNTS')
    print(run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print(c.execute(\\\"select count(*) from rbac_roles\\\").fetchone()[0], "
        "c.execute(\\\"select count(*) from rbac_permissions\\\").fetchone()[0], "
        "c.execute(\\\"select count(*) from rbac_capability_rules\\\").fetchone()[0]); "
        "print(c.execute(\\\"select code,name from rbac_roles order by code\\\").fetchall()); "
        "print(c.execute(\\\"select sr.id,sr.name,sr.description,l.rbac_role_code from staff_roles sr left join rbac_staff_role_links l on l.staff_role_id=sr.id order by sr.id\\\").fetchall())\""
    ))
finally:
    client.close()
