import paramiko
import sys
import io

sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=10)

def cmd(c):
    stdin, stdout, stderr = client.exec_command(c)
    return stdout.read().decode('utf-8').strip()

print("1. File gemini_watermark_service.php:", cmd("ls -lh /opt/coolingsystems/includes/gemini_watermark_service.php"))
print("2. Route clean-watermark:", cmd("grep -n 'clean-watermark' /opt/coolingsystems/routes/admin.php"))
print("3. JS autoCleanWatermark:", cmd("grep -n 'autoCleanWatermark' /opt/coolingsystems/views/admin/product-form.php"))
print("4. Config GEMINI_API_KEY:", cmd("grep -n 'GEMINI_API_KEY' /opt/coolingsystems/includes/config.php"))

client.close()
