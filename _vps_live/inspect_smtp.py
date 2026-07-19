import paramiko
import sys
sys.stdout.reconfigure(encoding='utf-8')
c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
commands={
 'php_version_extensions':"php -v | head -1; php -m | grep -Ei 'openssl|sockets|mbstring' || true",
 'mail_tools':"command -v msmtp || true; command -v sendmail || true; ls -la /etc/msmtprc /root/.msmtprc /etc/ssmtp/ssmtp.conf 2>/dev/null || true",
 'mailer_libraries':"find /opt/coolingsystems -maxdepth 4 -type f \( -iname '*phpmailer*' -o -name 'autoload.php' \) -print | head -30",
 'mail_log':"journalctl --since '2 hours ago' --no-pager | grep -Ei 'msmtp|sendmail|mailer|smtp' | tail -80 || true",
}
for label,cmd in commands.items():
 _,o,e=c.exec_command(cmd,timeout=30); print('\n--- '+label+' ---\n'+o.read().decode('utf-8','replace')+e.read().decode('utf-8','replace'))
c.close()
