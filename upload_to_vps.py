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
    ("includes/helpers.php", "/opt/coolingsystems/includes/helpers.php"),
    ("views/partials/confirm-modal.php", "/opt/coolingsystems/views/partials/confirm-modal.php"),
    ("views/admin/product-form.php", "/opt/coolingsystems/views/admin/product-form.php"),
    ("routes/admin.php", "/opt/coolingsystems/routes/admin.php"),
    ("views/partials/head.php", "/opt/coolingsystems/views/partials/head.php"),
    ("views/public/home.php", "/opt/coolingsystems/views/public/home.php"),
    ("views/public/partials/prod-card.php", "/opt/coolingsystems/views/public/partials/prod-card.php"),
    ("public/css/cooling.css", "/opt/coolingsystems/public/css/cooling.css"),
    ("public/css/cooling.min.css", "/opt/coolingsystems/public/css/cooling.min.css"),
    ("views/partials/foot.php", "/opt/coolingsystems/views/partials/foot.php")
]

for local, remote in files_to_upload:
    local_path = os.path.join(r"c:\xampp2\htdocs\coolingsystems", local)
    sftp.put(local_path, remote)
    print(f"Uploaded {local} -> {remote}")

sftp.close()
client.close()
print("Performance optimizations & Zero CLS deployed to VPS successfully!")
