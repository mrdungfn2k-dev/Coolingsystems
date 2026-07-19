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
    print('USERS')
    print(run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print(c.execute(\\\"select id, role, case when email is null or email='' then 'no-email' else 'email-set' end, case when full_name is null or full_name='' then 'no-name' else 'name-set' end, status from users order by id\\\").fetchall()); "
        "print('assignments=',c.execute(\\\"select count(*) from staff_role_assignments\\\").fetchone()[0]); "
        "print('audit=',c.execute(\\\"select name from sqlite_master where type='table' and name like '%audit%' or name like '%log%'\\\").fetchall()); "
        "print('audit_schema=',c.execute(\\\"pragma table_info(audit_logs)\\\").fetchall())\""
    ))
    source = read(f'{APP}/views/admin/role-assign.php')
    print('ASSIGN_VIEW')
    print(source)
    routes = read(f'{APP}/routes/admin.php')
    print('UNASSIGN_ROUTES')
    start = routes.find("post('/admin/staff/unassign/:id'")
    end = routes.find("get('/staff', function()", start)
    print(routes[start:end])
finally:
    client.close()
