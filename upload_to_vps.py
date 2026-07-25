import paramiko
import os

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)
sftp = client.open_sftp()

files_to_upload = [
    ("views/public/product-detail.php", "/opt/coolingsystems/views/public/product-detail.php")
]

for local, remote in files_to_upload:
    local_path = os.path.join(r"c:\xampp2\htdocs\coolingsystems", local)
    sftp.put(local_path, remote)
    print(f"✅ Uploaded {local} -> {remote}")

sftp.close()
client.close()
print("🎉 Product Detail SKU/OEM display uploaded to VPS successfully!")
