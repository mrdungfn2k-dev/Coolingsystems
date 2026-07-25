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
    ("includes/gemini_watermark_service.php", "/opt/coolingsystems/includes/gemini_watermark_service.php"),
    ("clean_watermark_ai.py", "/opt/coolingsystems/includes/clean_watermark_ai.py"),
    ("routes/admin.php", "/opt/coolingsystems/routes/admin.php"),
    ("views/admin/product-form.php", "/opt/coolingsystems/views/admin/product-form.php")
]

for local, remote in files_to_upload:
    local_path = os.path.join(r"c:\xampp2\htdocs\coolingsystems", local)
    sftp.put(local_path, remote)
    print(f"✅ Uploaded {local} -> {remote}")

sftp.close()
client.close()
print("🎉 AI Inpainting deployment to VPS complete!")
