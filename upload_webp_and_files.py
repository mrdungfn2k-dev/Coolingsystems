import os
import glob
import paramiko

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)

sftp = client.open_sftp()

client.exec_command("mkdir -p /opt/cooling-php/uploads/banners /var/lib/coolingsystems/uploads/banners /opt/coolingsystems/public/uploads/banners")

local_banners_dir = r"c:\xampp2\htdocs\coolingsystems\public\uploads\banners"
for filepath in glob.glob(os.path.join(local_banners_dir, "*")):
    if os.path.isfile(filepath):
        fname = os.path.basename(filepath)
        sftp.put(filepath, f"/opt/cooling-php/uploads/banners/{fname}")
        sftp.put(filepath, f"/var/lib/coolingsystems/uploads/banners/{fname}")
        sftp.put(filepath, f"/opt/coolingsystems/public/uploads/banners/{fname}")
        print(f"Uploaded banner {fname}")

code_files = [
    ("routes/admin.php", "/opt/coolingsystems/routes/admin.php"),
    ("views/public/home.php", "/opt/coolingsystems/views/public/home.php"),
    ("public/css/cooling.css", "/opt/coolingsystems/public/css/cooling.css"),
    ("public/css/cooling.min.css", "/opt/coolingsystems/public/css/cooling.min.css"),
    ("views/admin/content-list.php", "/opt/coolingsystems/views/admin/content-list.php")
]

for local, remote in code_files:
    local_path = os.path.join(r"c:\xampp2\htdocs\coolingsystems", local)
    sftp.put(local_path, remote)
    print(f"Uploaded {local} -> {remote}")

sftp.close()
client.close()
print("All WebP banners & performance optimizations deployed to VPS successfully!")
