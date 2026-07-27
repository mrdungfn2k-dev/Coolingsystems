import paramiko

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)

stdin, stdout, stderr = client.exec_command("curl -sL https://coolingsystems.vn/sitemap.xml | head -n 35")
print("Sitemap Live Output:")
print(stdout.read().decode('utf-8'))

client.close()
