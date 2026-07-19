import paramiko
import sys
sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
checks={
 'settings_card':"grep -c 'Cảnh báo tồn kho qua email' /opt/coolingsystems/views/admin/settings.php",
 'save_route':"grep -c \"post('/admin/settings/inventory-alert'\" /opt/coolingsystems/routes/admin.php",
 'test_route':"grep -c \"post('/admin/settings/inventory-alert/test'\" /opt/coolingsystems/routes/admin.php",
 'admin_stock_hooks':"grep -c 'inventoryCheckLowStockAlert' /opt/coolingsystems/routes/admin.php",
 'customer_stock_hooks':"grep -c 'inventoryCheckLowStockAlert' /opt/coolingsystems/routes/customer.php",
 'state_table':"sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT name FROM sqlite_master WHERE type='table' AND name IN ('inventory_alert_states','inventory_alert_logs') ORDER BY name;\"",
 'alert_default':"sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT COALESCE((SELECT value FROM settings WHERE key='inventory_alert_enabled'),'0') || '|' || COALESCE((SELECT value FROM settings WHERE key='inventory_alert_email'),'');\"",
 'public_product':"url=$(sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT '/products/' || slug FROM products WHERE status='published' AND slug<>'' LIMIT 1;\"); curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn$url",
 'recent_errors':"journalctl -u coolingsystems.service --since '5 minutes ago' --no-pager | grep -Ei 'fatal|exception|error' | tail -10 || true",
}
for label,cmd in checks.items():
 _,o,e=c.exec_command(cmd,timeout=30); print(label+': '+(o.read().decode('utf-8','replace').strip() or '(empty)'))
c.close()
