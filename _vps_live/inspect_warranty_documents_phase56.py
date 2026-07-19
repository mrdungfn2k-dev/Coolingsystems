import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

commands = {
    'p112_rule': "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT capability,permission_code,allowed_levels FROM rbac_capability_rules WHERE permission_code='P112' OR capability LIKE '%document%';\"",
    'warranty_print_routes': "grep -nEi 'warrant|print|document|bien ban|chung tu' /opt/coolingsystems/routes/admin.php | tail -160",
    'print_views': "find /opt/coolingsystems/views -type f -iname '*print*' -o -iname '*document*' -o -iname '*warrant*' | sort",
    'warranty_audit': "sqlite3 -separator '|' /var/lib/coolingsystems/cooling.db \"SELECT action,COUNT(*) FROM audit_logs WHERE entity_type='warranty_case' GROUP BY action ORDER BY action;\"",
}
for label, command in commands.items():
    _, out, err = client.exec_command(command, timeout=30)
    print('--- ' + label + ' ---')
    print(out.read().decode('utf-8', 'replace') + err.read().decode('utf-8', 'replace'))
client.close()
