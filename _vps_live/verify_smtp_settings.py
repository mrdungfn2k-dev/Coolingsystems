import paramiko
import sys
sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
checks={
 'smtp_card':"grep -c 'Cấu hình SMTP gửi email' /opt/coolingsystems/views/admin/settings.php",
 'smtp_route':"grep -c \"post('/admin/settings/smtp'\" /opt/coolingsystems/routes/admin.php",
 'smtp_transport':"grep -c 'function sendSmtpEmail' /opt/coolingsystems/includes/mailer.php",
 'smtp_default':"sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT COALESCE((SELECT value FROM settings WHERE key='smtp_enabled'),'0');\"",
 'product':"url=$(sqlite3 /var/lib/coolingsystems/cooling.db \"SELECT '/products/' || slug FROM products WHERE status='published' AND slug<>'' LIMIT 1;\"); curl -sS -o /dev/null -w '%{http_code}' https://coolingsystems.vn$url",
 'errors':"journalctl -u coolingsystems.service --since '5 minutes ago' --no-pager | grep -Ei 'fatal|exception|error' | tail -10 || true",
}
for label,cmd in checks.items():
 _,o,e=c.exec_command(cmd,timeout=30); print(label+': '+(o.read().decode('utf-8','replace').strip() or '(empty)'))
c.close()
