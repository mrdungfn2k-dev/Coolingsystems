import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)


def run(label, command):
    _, out, err = c.exec_command(command, timeout=90)
    stdout = out.read().decode('utf-8', 'replace').strip()
    stderr = err.read().decode('utf-8', 'replace').strip()
    status = out.channel.recv_exit_status()
    print(f'--- {label} [{status}] ---')
    print(stdout)
    if stderr:
        print('STDERR:', stderr)
    if status:
        raise RuntimeError(label)


db = '/var/lib/coolingsystems/cooling.db'
run('php lint', "php -l /opt/coolingsystems/includes/rbac.php && php -l /opt/coolingsystems/includes/auth.php && php -l /opt/coolingsystems/views/partials/dashboard-head.php && php -l /opt/coolingsystems/routes/admin.php")
run('data counts', "sqlite3 -separator '|' " + db + " \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM orders),(SELECT COUNT(*) FROM rbac_roles),(SELECT COUNT(*) FROM rbac_permissions),(SELECT COUNT(*) FROM rbac_role_permissions),(SELECT COUNT(*) FROM rbac_staff_role_links),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run('template and rule integrity', "sqlite3 -header -column " + db + " \"SELECT (SELECT COUNT(*) FROM rbac_staff_role_links)=13 AS templates_ok,(SELECT COUNT(*) FROM rbac_capability_rules)=15 AS rules_ok,(SELECT COUNT(*) FROM staff_roles WHERE name LIKE '[RBAC]%')=13 AS names_ok,(SELECT COUNT(*) FROM rbac_staff_role_links l LEFT JOIN rbac_roles r ON r.code=l.rbac_role_code WHERE r.code IS NULL) AS bad_role_links,(SELECT COUNT(*) FROM rbac_capability_rules c LEFT JOIN rbac_permissions p ON p.code=c.permission_code WHERE p.code IS NULL) AS bad_rule_links;\"")
run('matrix completeness', "sqlite3 -header -column " + db + " \"SELECT MIN(n) AS min_permissions_per_role, MAX(n) AS max_permissions_per_role FROM (SELECT role_code,COUNT(*) n FROM rbac_role_permissions GROUP BY role_code); SELECT MIN(n) AS min_roles_per_permission,MAX(n) AS max_roles_per_permission FROM (SELECT permission_code,COUNT(*) n FROM rbac_role_permissions GROUP BY permission_code);\"")
run('foreign keys', "sqlite3 " + db + " \"PRAGMA foreign_key_check(rbac_staff_role_links); PRAGMA foreign_key_check(rbac_capability_rules);\"")
run('permission wiring', "grep -c \"rbac:\" /opt/coolingsystems/routes/admin.php; grep -c \"rbacMenuCan\" /opt/coolingsystems/views/partials/dashboard-head.php; grep -c \"rbacHasCapability\" /opt/coolingsystems/includes/auth.php")
run('http checks', "set -e; echo HOME=$(curl -sk -o /dev/null -w '%{http_code}' https://coolingsystems.vn/); slug=$(sqlite3 " + db + " \"SELECT slug FROM products WHERE status='published' AND slug<>'' LIMIT 1;\"); echo PRODUCT=$(curl -sk -o /dev/null -w '%{http_code}' https://coolingsystems.vn/products/$slug); echo ADMIN=$(curl -sk -o /dev/null -w '%{http_code}' -I https://coolingsystems.vn/admin/products)")
run('service and new fatal errors', "systemctl is-active coolingsystems.service; journalctl -u coolingsystems.service --since '15 minutes ago' --no-pager | grep -Ei 'fatal|parse error|uncaught' || true")
run('pre-existing global FK audit', "sqlite3 " + db + " \"PRAGMA foreign_key_check;\" | head -20")
c.close()
