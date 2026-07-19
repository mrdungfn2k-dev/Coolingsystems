import base64
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
    query = """import sqlite3
c=sqlite3.connect('%s')
print('PERMISSIONS', c.execute(\"select code,module_name,feature_name,action_name from rbac_permissions where module_name like '%%Bảo%%' or feature_name like '%%Bảo%%' or action_name like '%%Bảo%%' or module_name like '%%Kỹ%%' or feature_name like '%%Kỹ%%' or action_name like '%%Kỹ%%' order by sort_order\").fetchall())
for table in ['orders','order_items','products','returns','return_requests']:
    try: print(table, c.execute('pragma table_info('+table+')').fetchall())
    except Exception as e: print(table, str(e))
print('ORDERS', c.execute('select count(*) from orders').fetchone()[0])
""" % DB
    encoded = base64.b64encode(query.encode('utf-8')).decode('ascii')
    print(run("echo " + encoded + " | base64 -d | python3"))
finally:
    client.close()
