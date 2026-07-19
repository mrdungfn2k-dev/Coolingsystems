import paramiko
import sys
sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
for label, cmd in {
 'admin_top': "sed -n '1,90p' /opt/coolingsystems/routes/admin.php",
 'customer_top': "sed -n '1,45p' /opt/coolingsystems/routes/customer.php",
 'mailer': "sed -n '1,220p' /opt/coolingsystems/includes/mailer.php",
 'settings_route': "sed -n '3010,3135p' /opt/coolingsystems/routes/admin.php",
 'setting_cards': "grep -n 'settings-card' /opt/coolingsystems/views/admin/settings.php | tail -20",
}.items():
 _,out,err=c.exec_command(cmd,timeout=30); print('\n--- '+label+' ---\n'+out.read().decode('utf-8','replace'))
c.close()
