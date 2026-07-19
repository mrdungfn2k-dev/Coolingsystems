import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

def read(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        value = handle.read().decode('utf-8')
    sftp.close()
    return value

routes = read('/opt/coolingsystems/routes/admin.php')
view = read('/opt/coolingsystems/views/admin/warranties.php')
checks = {
    'SERVER_WORKFLOW_RANKS': "$rank=['received'=>0,'checking'=>1,'approved'=>2,'assigned'=>3,'in_progress'=>4,'completed'=>5]" in routes,
    'SERVER_REVERSE_BLOCKED': '$rank[$status]<=$rank[$current]' in routes,
    'SERVER_TERMINAL_LOCKED': "in_array($current,['completed','rejected'],true)" in routes,
    'UI_PAST_STEPS_DISABLED': "$isPast?'disabled':''" in view,
    'UI_PAST_STEPS_DIMMED': '&#272;&#227; qua - ' in view,
    'UI_TERMINAL_LOCKED': "in_array($current,['completed','rejected'],true)" in view,
    'UI_SAVE_REQUIRES_CHANGE': "button.disabled=select.value===select.dataset.current" in view,
}
for name, passed in checks.items():
    print(name + '=' + ('ok' if passed else 'FAILED'))
client.close()
if not all(checks.values()):
    raise SystemExit(1)
