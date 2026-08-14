import paramiko
import os
import sys

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

HOST = "103.97.134.164"
USER = "root"
PASS = "lcBFDjVF15"

BASE_DIR = r"c:\xampp2\htdocs\coolingsystems"

files_to_sync = [
    "routes/admin.php",
    "routes/customer.php",
    "routes/public.php",
    "views/public/products.php",
    "views/public/product-detail.php",
    "views/public/partials/prod-card.php",
    "views/admin/products.php",
    "views/admin/garage-tiers.php",
    "views/admin/product-form.php",
    "views/admin/content-list.php",
    "views/partials/nav.php",
    "views/partials/footer.php",
    "includes/helpers.php",
    "includes/auth.php",
]

print("Connecting to VPS...")
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASS, timeout=15)

# Find all webroot directories containing routes/admin.php
stdin, stdout, stderr = client.exec_command("find /opt /var/www -name 'admin.php' 2>/dev/null")
found_paths = [p.strip() for p in stdout.read().decode('utf-8').splitlines() if 'routes/admin.php' in p]

webroots = set()
for p in found_paths:
    # Get root path before /routes/admin.php
    webroots.add(p.replace('/routes/admin.php', ''))

if not webroots:
    webroots = {'/opt/coolingsystems'}

print(f"Found webroots on VPS: {webroots}")

sftp = client.open_sftp()

for rel_path in files_to_sync:
    local_path = os.path.join(BASE_DIR, rel_path)
    if not os.path.exists(local_path):
        print(f"Warning: Local file not found: {local_path}")
        continue
    for w in webroots:
        remote_path = os.path.join(w, rel_path).replace("\\", "/")
        remote_dir = os.path.dirname(remote_path)
        # Ensure remote dir exists
        client.exec_command(f"mkdir -p {remote_dir}")
        sftp.put(local_path, remote_path)
        print(f"Uploaded: {rel_path} -> {remote_path}")

# Run setup garage tiers script on VPS
setup_script = """import sqlite3
import os

db_paths = ["/opt/coolingsystems/cooling.db", "/opt/coolingsystems/database/cooling.sqlite", "/opt/cooling-php/cooling.db"]
for path in db_paths:
    if not os.path.exists(os.path.dirname(path)) and not os.path.exists(path):
        continue
    print(f"Checking SQLite DB: {path}")
    conn = sqlite3.connect(path)
    c = conn.cursor()
    c.execute('''
    CREATE TABLE IF NOT EXISTS garage_tiers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tier_code TEXT UNIQUE NOT NULL,
        tier_name TEXT NOT NULL,
        discount_percent REAL DEFAULT 0,
        min_monthly_spend REAL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
    ''')
    
    tiers = [
        ('BRONZE', 'Gara Đồng (Bronze)', 5.0, 0),
        ('SILVER', 'Gara Bạc (Silver)', 10.0, 10000000),
        ('GOLD', 'Gara Vàng (Gold)', 15.0, 30000000),
        ('DIAMOND', 'Gara Kim Cương (Diamond)', 20.0, 50000000),
    ]
    for code, name, disc, min_spend in tiers:
        c.execute('''
        INSERT INTO garage_tiers (tier_code, tier_name, discount_percent, min_monthly_spend)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(tier_code) DO NOTHING
        ''', (code, name, disc, min_spend))
    
    conn.commit()
    conn.close()
    print(f"Successfully initialized garage_tiers in {path}")
"""

# Upload python script to /tmp/setup_garage_tiers.py
with sftp.open("/tmp/setup_garage_tiers.py", "w") as f:
    f.write(setup_script)

sftp.put(os.path.join(BASE_DIR, "update_policies_db.py"), "/tmp/update_policies_db.py")
sftp.put(os.path.join(BASE_DIR, "fix_product_categories.py"), "/tmp/fix_product_categories.py")

sftp.close()

# Execute script on VPS
stdin, stdout, stderr = client.exec_command("python3 /tmp/setup_garage_tiers.py")
print("Database setup output:")
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

stdin, stdout, stderr = client.exec_command("python3 /tmp/update_policies_db.py")
print("Policies DB update output:")
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

stdin, stdout, stderr = client.exec_command("python3 /tmp/fix_product_categories.py")
print("Categories DB fix output:")
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))

# Reset OPcache & restart services
print("Resetting OPcache and restarting Nginx / PHP-FPM...")
client.exec_command("php -r 'if(function_exists(\"opcache_reset\")) opcache_reset();'")
client.exec_command("systemctl restart php8.3-fpm || systemctl restart php-fpm || true")
client.exec_command("systemctl restart nginx || true")

client.close()
print("=== ALL CHANGES DEPLOYED TO VPS SUCCESSFULLY ===")
