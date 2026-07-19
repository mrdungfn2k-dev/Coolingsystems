import paramiko
import sys
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')
HOST='103.97.134.164'; USER='root'; PASSWORD='lcBFDjVF15'; ROOT='/opt/coolingsystems'
LOCAL={
  'helper':'cooling-php/_vps_live/inventory-alerts.php',
  'card':'cooling-php/_vps_live/phase2_inventory_alert_settings.php',
  'routes':'cooling-php/_vps_live/phase2_inventory_alert_routes.php',
}

def local(path):
  with open(path,encoding='utf-8') as f: return f.read()
def run(client, command):
  _,out,err=client.exec_command(command,timeout=60); value=out.read().decode('utf-8','replace'); error=err.read().decode('utf-8','replace'); code=out.channel.recv_exit_status()
  if code: raise RuntimeError(f'{command}\n{value}\n{error}')
  return value.strip()
def once(source, old, new, label):
  count=source.count(old)
  if count!=1: raise RuntimeError(f'{label}: expected one match, got {count}')
  return source.replace(old,new,1)

c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect(HOST,username=USER,password=PASSWORD,timeout=30)
stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/inventory-phase2-{stamp}'
run(c,f"mkdir -p {backup} && cp {ROOT}/routes/admin.php {backup}/admin.php && cp {ROOT}/routes/customer.php {backup}/customer.php && cp {ROOT}/views/admin/settings.php {backup}/settings.php && cp {ROOT}/includes/mailer.php {backup}/mailer.php && sqlite3 /var/lib/coolingsystems/cooling.db '.backup {backup}/cooling.db'")
s=c.open_sftp()
def read(path):
  with s.open(path,'rb') as f: return f.read().decode('utf-8')
def write(path,value):
  with s.open(path,'wb') as f: f.write(value.encode('utf-8'))
admin=read(f'{ROOT}/routes/admin.php'); customer=read(f'{ROOT}/routes/customer.php'); settings=read(f'{ROOT}/views/admin/settings.php')

include="require_once __DIR__ . '/../includes/inventory-alerts.php';\n"
if 'inventory-alerts.php' not in admin:
  if not admin.startswith('<?php\n'): raise RuntimeError('admin route php opening not found')
  admin=admin.replace('<?php\n','<?php\n'+include,1)
if 'inventory-alerts.php' not in customer:
  if not customer.startswith('<?php\n'): raise RuntimeError('customer route php opening not found')
  customer=customer.replace('<?php\n','<?php\n'+include,1)

if "post('/admin/settings/inventory-alert'" not in admin:
  marker="post('/admin/settings/account', function() {"
  admin=once(admin,marker,local(LOCAL['routes'])+'\n'+marker,'insert inventory alert setting routes')
if 'Cảnh báo tồn kho qua email' not in settings:
  marker='  <!-- Social Media Links -->'
  settings=once(settings,marker,local(LOCAL['card'])+'\n'+marker,'insert inventory alert settings card')

# Dedicated inventory updates.
inventory_update='    dbRun("UPDATE products SET cost_price=?,price=?,original_price=?,stock=?,min_stock=?,max_stock=?,warranty_months=?,total_import_value=?,updated_at=datetime(\'now\',\'localtime\') WHERE id=?",[$values[\'cost_price\'],$values[\'price\'],$values[\'original_price\']?:null,$values[\'stock\'],$values[\'min_stock\'],$values[\'max_stock\'],$values[\'warranty_months\'],$values[\'cost_price\']*$values[\'stock\'],$p[\'id\']]);'
admin=once(admin,inventory_update,inventory_update+'\n    inventoryCheckLowStockAlert((int)$p[\'id\'], \'inventory_update\');','inventory update hook')

# All standard order flows that change inventory on the admin side.
admin=once(admin,"        dbRun(\"UPDATE products SET stock=MAX(0,stock-?) WHERE id=?\", [$qty, $pid]);\n        dbRun(\"UPDATE products SET total_import_value=cost_price*stock WHERE id=?\", [$pid]);","        dbRun(\"UPDATE products SET stock=MAX(0,stock-?) WHERE id=?\", [$qty, $pid]);\n        dbRun(\"UPDATE products SET total_import_value=cost_price*stock WHERE id=?\", [$pid]);\n        inventoryCheckLowStockAlert($pid, 'admin_order');",'admin order stock hook')
admin=once(admin,"                dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [intval($ri['quantity']), $ri['product_id']]);\n                dbRun(\"UPDATE products SET total_import_value=cost_price*stock WHERE id=?\", [$ri['product_id']]);","                dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [intval($ri['quantity']), $ri['product_id']]);\n                dbRun(\"UPDATE products SET total_import_value=cost_price*stock WHERE id=?\", [$ri['product_id']]);\n                inventoryCheckLowStockAlert((int)$ri['product_id'], 'admin_return');",'admin return stock hook')
admin=once(admin,"        foreach ($orderItems as $oi) {\n            if ($oi['product_id']) dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [$oi['quantity'], $oi['product_id']]);\n        }","        foreach ($orderItems as $oi) {\n            if ($oi['product_id']) {\n                dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [$oi['quantity'], $oi['product_id']]);\n                inventoryCheckLowStockAlert((int)$oi['product_id'], 'admin_order_cancel');\n            }\n        }",'admin cancel stock hook')
admin=once(admin,"            dbRun(\"UPDATE products SET stock = MAX(0, stock - ?) WHERE id=?\", [intval($it['qty']), $p['id']]);","            dbRun(\"UPDATE products SET stock = MAX(0, stock - ?) WHERE id=?\", [intval($it['qty']), $p['id']]);\n            inventoryCheckLowStockAlert((int)$p['id'], 'staff_order');",'staff order stock hook')
admin=once(admin,"    foreach ($items as $it) { dbRun(\"UPDATE products SET stock=stock+?, sold_count=MAX(0,sold_count-?) WHERE id=?\", [$it['quantity'],$it['quantity'],$it['product_id']]); }","    foreach ($items as $it) {\n        dbRun(\"UPDATE products SET stock=stock+?, sold_count=MAX(0,sold_count-?) WHERE id=?\", [$it['quantity'],$it['quantity'],$it['product_id']]);\n        inventoryCheckLowStockAlert((int)$it['product_id'], 'admin_refund');\n    }",'admin refund stock hook')
admin=once(admin,"    ]);\n\n    // Handle and normalize newly uploaded product images.","    ]);\n    inventoryCheckLowStockAlert((int)$p['id'], 'product_edit');\n\n    // Handle and normalize newly uploaded product images.",'product edit stock hook')

# Customer checkout and cancellation flows.
customer=once(customer,"            dbRun(\"UPDATE products SET stock=MAX(0, stock-?) WHERE id=?\", [$pi['quantity'], $pi['product_id']]);","            dbRun(\"UPDATE products SET stock=MAX(0, stock-?) WHERE id=?\", [$pi['quantity'], $pi['product_id']]);\n            inventoryCheckLowStockAlert((int)$pi['product_id'], 'customer_order');",'customer checkout stock hook')
customer=once(customer,"            dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [$oi['quantity'], $oi['product_id']]);","            dbRun(\"UPDATE products SET stock = stock + ? WHERE id=?\", [$oi['quantity'], $oi['product_id']]);\n            inventoryCheckLowStockAlert((int)$oi['product_id'], 'customer_order_cancel');",'customer cancel stock hook')

write(f'{ROOT}/routes/admin.php',admin); write(f'{ROOT}/routes/customer.php',customer); write(f'{ROOT}/views/admin/settings.php',settings)
s.put(LOCAL['helper'],f'{ROOT}/includes/inventory-alerts.php'); s.close()
run(c,f"chown coolingsystems:www-data {ROOT}/routes/admin.php {ROOT}/routes/customer.php {ROOT}/views/admin/settings.php {ROOT}/includes/inventory-alerts.php")
run(c,"sqlite3 /var/lib/coolingsystems/cooling.db \"CREATE TABLE IF NOT EXISTS inventory_alert_states (product_id INTEGER PRIMARY KEY,is_low INTEGER NOT NULL DEFAULT 0,last_stock INTEGER NOT NULL DEFAULT 0,last_attempt_at TEXT,last_sent_at TEXT,last_status TEXT,updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE); CREATE TABLE IF NOT EXISTS inventory_alert_logs (id INTEGER PRIMARY KEY AUTOINCREMENT,product_id INTEGER NOT NULL,recipient TEXT NOT NULL,stock INTEGER NOT NULL,min_stock INTEGER NOT NULL,source TEXT,status TEXT NOT NULL,created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE);\"")
for path in [f'{ROOT}/routes/admin.php',f'{ROOT}/routes/customer.php',f'{ROOT}/views/admin/settings.php',f'{ROOT}/includes/inventory-alerts.php']:
  print(run(c,f'php -l {path}'))
print('BACKUP='+backup)
print('SETTINGS='+run(c,"curl -sS -o /dev/null -w '%{http_code} %{redirect_url}' https://coolingsystems.vn/admin/settings"))
print('HOME='+run(c,"curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
c.close()
