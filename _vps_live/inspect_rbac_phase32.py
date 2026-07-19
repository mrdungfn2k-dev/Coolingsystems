import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
commands={
 'auth': "sed -n '1,95p' /opt/coolingsystems/includes/auth.php",
 'product_routes': "grep -n -A3 -B1 -E \"get\('/admin/products|post\('/admin/products|/admin/inventory\" /opt/coolingsystems/routes/admin.php | head -200",
 'order_routes': "grep -n -A3 -B1 -E \"get\('/admin/orders|post\('/admin/orders|/admin/returns\" /opt/coolingsystems/routes/admin.php | head -220",
 'staff_views': "ls /opt/coolingsystems/views/admin/*staff*; sed -n '1,240p' /opt/coolingsystems/views/admin/staff.php 2>/dev/null",
 'permission_form': "sed -n '1400,1535p' /opt/coolingsystems/routes/admin.php; sed -n '1,260p' /opt/coolingsystems/views/admin/staff.php 2>/dev/null",
 'matrix_items': "sqlite3 -header -column /var/lib/coolingsystems/cooling.db \"SELECT code,module_name,feature_name,action_name FROM rbac_permissions WHERE module_code IN ('02','03','04') ORDER BY sort_order;\"",
}
for label,cmd in commands.items():
 _,o,e=c.exec_command(cmd,timeout=60); print('\n--- '+label+' ---\n'+o.read().decode('utf-8','replace'))
c.close()
