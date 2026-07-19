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
backup = f'/var/backups/coolingsystems/warranty-materials-{stamp}'
marker = "post('/admin/warranties/:id/status', function($p) {"
routes_addition = """get('/admin/warranties/material-products', function() { requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login'); $q=trim($_GET['q']??''); header('Content-Type: application/json; charset=utf-8'); if(mb_strlen($q)<2){echo '[]';exit;} $like='%'.$q.'%'; $rows=dbAll('SELECT id,sku,oem_code,name,stock FROM products WHERE stock>0 AND (name LIKE ? OR sku LIKE ? OR oem_code LIKE ?) ORDER BY name LIMIT 12',[$like,$like,$like]); echo json_encode(array_map(fn($row)=>['id'=>(int)$row['id'],'label'=>trim($row['sku'].' | '.$row['name'].' | Ton: '.$row['stock'].(!empty($row['oem_code'])?' | OEM: '.$row['oem_code']:''))],$rows),JSON_UNESCAPED_UNICODE); exit; });
get('/admin/warranties/:id/materials', function($p) { $actor=requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login'); $case=dbGet('SELECT warranty.*,product.name AS product_name,product.sku FROM warranty_cases warranty INNER JOIN products product ON product.id=warranty.product_id WHERE warranty.id=?',[$p['id']]); if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');} $materials=dbAll("SELECT material.*,product.name AS product_name,product.sku,product.oem_code,COALESCE(CAST(material.issued_by AS TEXT),'') AS issued_by_name FROM warranty_materials material INNER JOIN products product ON product.id=material.product_id WHERE material.warranty_case_id=? ORDER BY material.issued_at DESC,material.id DESC",[$case['id']]); view('admin/warranty-materials',['title'=>'V&#7853;t t&#432; phi&#7871;u b&#7843;o h&#224;nh','userRole'=>'admin','case'=>$case,'materials'=>$materials]); });
post('/admin/warranties/:id/materials', function($p) { $actor=requireStaffPermission('rbac:warranty.materials.consume|returns','/admin/login');csrfCheck();$case=dbGet('SELECT id,status FROM warranty_cases WHERE id=?',[$p['id']]);if(!$case){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties');}if(!in_array($case['status'],['approved','assigned','in_progress'],true)){flash('error',html_entity_decode('Ch&#7881; &#273;&#432;&#7907;c xu&#7845;t v&#7853;t t&#432; cho phi&#7871;u &#273;&#227; duy&#7879;t ho&#7863;c &#273;ang x&#7917; l&#253;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$productId=(int)($_POST['product_id']??0);$rawQuantity=trim((string)($_POST['quantity']??''));$note=trim((string)($_POST['note']??''));if(!$productId||$rawQuantity===''||!ctype_digit($rawQuantity)||(int)$rawQuantity<1||(int)$rawQuantity>1000||mb_strlen($note)>300){flash('error',html_entity_decode('V&#7853;t t&#432;, s&#7889; l&#432;&#7907;ng ho&#7863;c ghi ch&#250; kh&#244;ng h&#7907;p l&#7879;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$quantity=(int)$rawQuantity;$product=dbGet('SELECT id,name,sku,stock FROM products WHERE id=?',[$productId]);if(!$product){flash('error',html_entity_decode('Kh&#244;ng t&#236;m th&#7845;y v&#7853;t t&#432;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}$pdo=db();try{$pdo->beginTransaction();$changed=dbRun("UPDATE products SET stock=stock-?,updated_at=datetime('now','localtime') WHERE id=? AND stock>=?",[$quantity,$productId,$quantity]);if($changed->rowCount()!==1){throw new RuntimeException('insufficient stock');}$materialId=dbInsert('INSERT INTO warranty_materials (warranty_case_id,product_id,quantity,note,issued_by) VALUES (?,?,?,?,?)',[$case['id'],$productId,$quantity,$note,$actor['id']??null]);dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$actor['id']??null,$actor['role']??'admin','warranty_material_issued','warranty_material',$materialId,json_encode(['warranty_case_id'=>$case['id'],'product_id'=>$productId,'quantity'=>$quantity],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']);$pdo->commit();}catch(Throwable $exception){if($pdo->inTransaction()){$pdo->rollBack();}flash('error',html_entity_decode('Kh&#244;ng th&#7875; xu&#7845;t v&#7853;t t&#432;. T&#7891;n kho c&#243; th&#7875; kh&#244;ng &#273;&#7911;.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials');}try{inventoryCheckLowStockAlert($productId,'warranty_material');}catch(Throwable $exception){}flash('success',html_entity_decode('&#272;&#227; xu&#7845;t v&#7853;t t&#432; cho phi&#7871;u b&#7843;o h&#224;nh.',ENT_QUOTES,'UTF-8'));redirect('/admin/warranties/'.$case['id'].'/materials'); });
"""

try:
    run(f"mkdir -p {backup} && sqlite3 {DB} \".backup '{backup}/cooling.db'\" && cp {APP}/routes/admin.php {backup}/admin.php && cp {APP}/views/admin/warranties.php {backup}/warranties.php")
    routes = read_remote(APP + '/routes/admin.php')
    if routes.count(marker) != 1 or "warranty.materials.consume" in routes:
        raise RuntimeError('Warranty material route marker is invalid or feature already exists.')
    routes = routes.replace(marker, routes_addition + marker, 1)
    write_remote('/tmp/warranty-phase54-routes.php', routes)
    sftp = client.open_sftp()
    sftp.put(str(ROOT / 'seed_rbac_phase54.php'), '/tmp/warranty-phase54-seed.php')
    sftp.put(str(ROOT / 'warranties_phase50.php'), '/tmp/warranty-phase54-warranties.php')
    sftp.put(str(ROOT / 'warranty-materials_phase54.php'), '/tmp/warranty-phase54-materials.php')
    sftp.close()
    result = run(f"chown www-data:www-data /tmp/warranty-phase54-* && runuser -u www-data -- php /tmp/warranty-phase54-seed.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase54-routes.php {APP}/routes/admin.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase54-warranties.php {APP}/views/admin/warranties.php && install -o www-data -g www-data -m 0644 /tmp/warranty-phase54-materials.php {APP}/views/admin/warranty-materials.php && php -l {APP}/routes/admin.php && php -l {APP}/views/admin/warranties.php && php -l {APP}/views/admin/warranty-materials.php && rm -f /tmp/warranty-phase54-*")
    print('BACKUP=' + backup)
    print('LINT=' + result)
    print('HOME=' + run("curl -s -o /dev/null -w '%{http_code}' https://coolingsystems.vn/"))
    print('MATERIAL_TABLE=' + run(f"sqlite3 {DB} \"SELECT COUNT(*) FROM warranty_materials;\""))
    print('P109=' + run(f"sqlite3 -separator '|' {DB} \"SELECT capability,permission_code FROM rbac_capability_rules WHERE capability='warranty.materials.consume';\""))
    print('MATERIAL_UNAUTH=' + run("curl -s -o /dev/null -w '%{http_code}|%{redirect_url}' https://coolingsystems.vn/admin/warranties/1/materials"))
except Exception:
    run(f"test -f {backup}/cooling.db && sqlite3 {DB} \".restore '{backup}/cooling.db'\"; test -f {backup}/admin.php && cp {backup}/admin.php {APP}/routes/admin.php; test -f {backup}/warranties.php && cp {backup}/warranties.php {APP}/views/admin/warranties.php; rm -f {APP}/views/admin/warranty-materials.php /tmp/warranty-phase54-*; chown www-data:www-data {DB} {APP}/routes/admin.php {APP}/views/admin/warranties.php")
    client.close()
    raise
client.close()
