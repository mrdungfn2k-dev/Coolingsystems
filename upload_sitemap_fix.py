import paramiko
import os

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)

sftp = client.open_sftp()

sftp.put(r"c:\xampp2\htdocs\coolingsystems\routes\public.php", "/opt/coolingsystems/routes/public.php")
sftp.put(r"c:\xampp2\htdocs\coolingsystems\public\robots.txt", "/opt/coolingsystems/public/robots.txt")

print("Uploaded routes/public.php and public/robots.txt to VPS!")

stdin, stdout, stderr = client.exec_command("curl -s http://127.0.0.1:8080/sitemap.xml | head -n 25")
print("Sitemap test output:")
print(stdout.read().decode('utf-8'))

sftp.close()
client.close()
