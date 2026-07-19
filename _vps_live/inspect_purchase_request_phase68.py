import paramiko
c=paramiko.SSHClient();c.set_missing_host_key_policy(paramiko.AutoAddPolicy());c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
_,o,e=c.exec_command("sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"PRAGMA table_info(products);\"",timeout=60)
print(o.read().decode('utf-8','replace')+e.read().decode('utf-8','replace'))
c.close()
