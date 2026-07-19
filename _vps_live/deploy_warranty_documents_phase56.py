import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT = Path('cooling-php/_vps_live')
APP = '/opt/coolingsystems'
DB = '/var/lib/coolingsystems/cooling.db'
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('103.97.134.164', username='root', password='lcBFDjVF15', timeout=30)

def run(command):
    _, out, err = client.exec_command(command, timeout=90)
    stdout, stderr = out.read().decode('utf-8', 'replace'), err.read().decode('utf-8', 'replace')
    if out.channel.recv_exit_status():
        raise RuntimeError(command + '\n' + stdout + '\n' + stderr)
    return stdout.strip()

def read_remote(path):
    sftp = client.open_sftp()
    with sftp.open(path, 'rb') as handle:
        value = handle.read().decode('utf-8')
    sftp.close()
    return value

def write_remote(path, value):
    sftp = client.open_sftp()
    with sftp.open(path, 'wb') as handle:
        handle.write(value.encode('utf-8'))
    sftp.close()

stamp = datetime.now().strftime('%Y%m%d-%H%M%S')
backup = f'/var/backups/coolingsystems/warranty-documents-{stamp}'
marker = "get('/admin/warranties/material-products', function() {"
routes_addition = """get('/admin/warranties/:id/documents', function($p) { requireStaffPermission('rbac:warranty.documents.print|returns','/admin/login'); $case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]); if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');} view('admin/warranty-documents',['title'=>'Ch&#7913;ng t&#7915; b&#7843;o h&#224;nh','userRole'=>'admin','case'=>$case]); });
get('/admin/warranties/:id/documents/:type', function($p) { $actor=requireStaffPermission('rbac:warranty.documents.print|returns','/admin/login');$type=$p['type']??'';if(!in_array($type,['receipt','warranty','handover'],true)){http_response_code(404);view('errors/404',['title'=>'Kh&#244;ng t&#236;m th&#7845;y trang']);return;}$case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]);if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if($type==='handover'&&$case['status']!=='completed'){flash('error',html_entity_decode('Ch&#7881; in bi&#234;n b&#7843;n b&#224;n giao sau khi phi&#7871;u &#273;&#227; nghi&#7879;m thu.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/documents');}$materials=dbAll('SELECT material.*,product.name AS product_name,product.sku,product.oem_code FROM warranty_materials material INNER JOIN products product ON product.id=material.product_id WHERE material.warranty_case_id=? ORDER BY material.issued_at ASC,material.id ASC',[$case['id']]);$statusLabels=['received'=>html_entity_decode('Ti&#7871;p nh&#7853;n',ENT_QUOTES,'UTF-8'),'checking'=>html_entity_decode('&#272;ang ki&#7875;m tra',ENT_QUOTES,'UTF-8'),'approved'=>html_entity_decode('&#272;&#227; duy&#7879;t',ENT_QUOTES,'UTF-8'),'assigned'=>html_entity_decode('&#272;&#227; ph&#226;n c&#244;ng',ENT_QUOTES,'UTF-8'),'in_progress'=>html_entity_decode('&#272;ang x&#7917; l&#253;',ENT_QUOTES,'UTF-8'),'completed'=>html_entity_decode('&#272;&#227; nghi&#7879;m thu',ENT_QUOTES,'UTF-8'),'rejected'=>html_entity_decode('T&#7915; ch&#7889;i',ENT_QUOTES,'UTF-8')];dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_document_printed','warranty_case',$case['id'],json_encode(['type'=>$type],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);view('admin/warranty-document-print',['case'=>$case,'materials'=>$materials,'documentType'=>$type,'statusLabel'=>$statusLabels[$case['status']]??$case['status']]); });
"""

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    routes = read_remote(APP + '/routes/admin.php')
    if routes.count(marker) != 1 or 'warranty.documents.print' in routes:
        raise RuntimeError('Warranty document route marker is invalid or feature already exists.')
    routes = routes.replace(marker, routes_addition + marker, 1)
    write_remote('/tmp/warranty-phase56-routes.php', routes)
    sftp = client.open_sftp()
    for source, target in [('seed_rbac_phase56.php','/tmp/warranty-phase56-seed.php'),('warranties_phase56.php','/tmp/warranty-phase56-warranties.php'),('warranty-documents_phase56.php','/tmp/warranty-phase56-documents.php'),('warranty-document-print_phase56.php','/tmp/warranty-phase56-print.php')]:
        sftp.put(str(ROOT / source), target)
    sftp.close()
    result = run(f"chown www-data:www-data /tmp/warranty-phase56-* && runuser -u www-data -- php /tmp/warranty-phase56-seed.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase56-routes.php {APP}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase56-warranties.php {APP}/views/admin/warranties.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase56-documents.php {APP}/views/admin/warranty-documents.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase56-print.php {APP}/views/admin/warranty-document-print.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/warranties.php && php -l {APP}/views/admin/warranty-documents.php && php -l {APP}/views/admin/warranty-document-print.php && rm -f /tmp/warranty-phase56-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('P112=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='warranty.documents.print';\""))
    print('DOCUMENTS_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties/1/documents"))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f {APP}/views/admin/warranty-documents.php {APP}/views/admin/warranty-document-print.php /tmp/warranty-phase56-*; chown www-data:www-data {DB} {APP}/routes/admin.php {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
