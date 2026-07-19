import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
commands={
 'role_counts': "sqlite3 -header -column /var/lib/coolingsystems/cooling.db \"SELECT role,COUNT(*) AS users FROM users GROUP BY role ORDER BY role;\"",
 'permission_tables': "sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT name FROM sqlite_master WHERE type='table' AND (name LIKE '%role%' OR name LIKE '%permission%' OR name LIKE '%staff%' OR name LIKE '%branch%' OR name LIKE '%warehouse%') ORDER BY name;\"",
 'permissions_schema': "for t in staff_roles staff_role_permissions staff_assignments staff_permissions role_permissions permissions user_roles branches warehouses; do echo ==== $t ====; sqlite3 /var/lib/coolingsystems/cooling.db \"PRAGMA table_info($t);\" 2>/dev/null; done",
 'permission_functions': "grep -RIn --include='*.php' -E 'function (requireStaffPermission|staffHasAssignment|requireRole)|staff_role|permission' /opt/coolingsystems/includes /opt/coolingsystems/routes | head -240",
 'sidebar_permissions': "grep -RIn --include='*.php' -E 'function sb|\$sb\(' /opt/coolingsystems/views/partials /opt/coolingsystems/includes | head -160",
 'admin_menu': "sed -n '1,180p' /opt/coolingsystems/views/partials/dashboard-head.php",
 'permission_data': "for t in staff_roles staff_role_permissions staff_assignments; do echo ==== $t ====; sqlite3 -header -column /var/lib/coolingsystems/cooling.db \"SELECT * FROM $t LIMIT 80;\" 2>/dev/null; done",
}
for label,cmd in commands.items():
 _,o,e=c.exec_command(cmd,timeout=60); print('\n--- '+label+' ---\n'+o.read().decode('utf-8','replace')); err=e.read().decode('utf-8','replace').strip();
 if err: print('STDERR:',err)
c.close()
