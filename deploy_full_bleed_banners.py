import os
import shutil
import paramiko

b1 = r"C:\Users\PCM\.gemini\antigravity-ide\brain\8be6a2a3-c030-4f05-8f13-0ad68bd0311e\full_bleed_cooling_banner_1_1785143635350.png"
b2 = r"C:\Users\PCM\.gemini\antigravity-ide\brain\8be6a2a3-c030-4f05-8f13-0ad68bd0311e\full_bleed_cooling_banner_2_1785143650670.png"

local_banners_dir = r"c:\xampp2\htdocs\coolingsystems\public\uploads\banners"
os.makedirs(local_banners_dir, exist_ok=True)

dst1_fb = os.path.join(local_banners_dir, "full_bleed_banner_1.png")
dst2_fb = os.path.join(local_banners_dir, "full_bleed_banner_2.png")
dst1_hero = os.path.join(local_banners_dir, "hero_cooling_banner_1.png")
dst2_hero = os.path.join(local_banners_dir, "hero_cooling_banner_2.png")

shutil.copy(b1, dst1_fb)
shutil.copy(b2, dst2_fb)
shutil.copy(b1, dst1_hero)
shutil.copy(b2, dst2_hero)

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)
sftp = client.open_sftp()

dirs = [
    "/opt/cooling-php/uploads/banners",
    "/var/lib/coolingsystems/uploads/banners",
    "/opt/coolingsystems/public/uploads/banners"
]

for d in dirs:
    client.exec_command(f"mkdir -p {d}")
    sftp.put(dst1_fb, f"{d}/full_bleed_banner_1.png")
    sftp.put(dst2_fb, f"{d}/full_bleed_banner_2.png")
    sftp.put(dst1_hero, f"{d}/hero_cooling_banner_1.png")
    sftp.put(dst2_hero, f"{d}/hero_cooling_banner_2.png")

print("Full bleed banners uploaded to VPS successfully!")
sftp.close()
client.close()
