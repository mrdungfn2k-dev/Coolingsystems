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
    return output.strip()


try:
    print('SERVICE=' + run('systemctl is-active coolingsystems.service'))
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('ROLE_DETAIL=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/staff/roles/15/permissions"))
    print('DB=' + run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print('|'.join(map(str,[c.execute('select count(*) from rbac_roles').fetchone()[0],"
        "c.execute('select count(*) from rbac_permissions').fetchone()[0],"
        "c.execute('select count(*) from rbac_capability_rules').fetchone()[0],"
        "c.execute('select count(*) from staff_roles sr join rbac_staff_role_links l on l.staff_role_id=sr.id').fetchone()[0],"
        "c.execute(\\\"select count(*) from staff_roles where name like '[RBAC] %' and description like '%Read-only%'\\\").fetchone()[0],"
        "c.execute('select count(*) from staff_role_assignments').fetchone()[0]])))\""
    ))
    print('LABELS=' + run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print(';'.join(r[0] for r in c.execute(\\\"select name from staff_roles where name like '[RBAC] %' order by id\\\")))\""
    ))
    print('RBAC_HELPER=' + run(
        "cd " + APP + " && runuser -u www-data -- php -r 'require \"includes/db.php\"; require \"includes/rbac.php\"; echo count(rbacCapabilityCatalog()) . \"|\" . count(rbacTemplateCapabilities(\"SA\"));'"
    ))
finally:
    client.close()
