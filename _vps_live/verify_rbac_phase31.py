import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
commands={
 'dimensions': "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT (SELECT COUNT(*) FROM rbac_roles),(SELECT COUNT(*) FROM rbac_permissions),(SELECT COUNT(*) FROM rbac_role_permissions),(SELECT COUNT(*) FROM rbac_import_runs);\"",
 'matrix_completeness': "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT (SELECT MIN(c) FROM (SELECT COUNT(*) c FROM rbac_role_permissions GROUP BY role_code)),(SELECT MAX(c) FROM (SELECT COUNT(*) c FROM rbac_role_permissions GROUP BY role_code)),(SELECT MIN(c) FROM (SELECT COUNT(*) c FROM rbac_role_permissions GROUP BY permission_code)),(SELECT MAX(c) FROM (SELECT COUNT(*) c FROM rbac_role_permissions GROUP BY permission_code));\"",
 'access_levels': "sqlite3 -header -column /var/lib/coolingsystems/cooling.db \"SELECT role_code,access_level,COUNT(*) AS count FROM rbac_role_permissions GROUP BY role_code,access_level ORDER BY role_code,access_level;\"",
 'foreign_keys': "sqlite3 /var/lib/coolingsystems/cooling.db 'PRAGMA foreign_key_check;'",
 'rbac_foreign_keys': "sqlite3 /var/lib/coolingsystems/cooling.db 'PRAGMA foreign_key_check(rbac_role_permissions);'",
 'legacy_unchanged': "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders);\"",
 'code_unchanged': "latest=$(ls -dt /var/backups/coolingsystems/rbac-phase31-* | head -1); for f in auth.php admin.php dashboard-head.php; do cmp -s $latest/$f /opt/coolingsystems/$(case $f in auth.php) echo includes/auth.php;; admin.php) echo routes/admin.php;; dashboard-head.php) echo views/partials/dashboard-head.php;; esac) && echo $f:same || echo $f:changed; done",
 'public_route': "url=$(sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT '/products/' || slug FROM products WHERE status='published' AND slug<>'' LIMIT 1;\"); curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn$url",
 'errors': "journalctl -u coolingsystems.service --since '10 minutes ago' --no-pager | grep -Ei 'fatal|exception|error' | tail -20 || true",
}
for label,command in commands.items():
 _,out,err=c.exec_command(command,timeout=60); print('\n'+label+':\n'+(out.read().decode('utf-8','replace').strip() or '(empty)')); error=err.read().decode('utf-8','replace').strip();
 if error: print('stderr:',error)
c.close()
