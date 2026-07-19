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


try:
    print(run(
        "python3 -c \"import sqlite3; c=sqlite3.connect('" + DB + "'); "
        "print(c.execute(\\\"pragma table_info(users)\\\").fetchall()); "
        "print(c.execute(\\\"select id,name from staff_roles where id in (select staff_role_id from rbac_staff_role_links where rbac_role_code='WH')\\\").fetchall())\""
    ))
finally:
    client.close()
