import sys
from datetime import datetime
from pathlib import Path
import paramiko

sys.stdout.reconfigure(encoding='utf-8')
ROOT='/opt/coolingsystems'; DB='/var/lib/coolingsystems/cooling.db'; LOCAL=Path('cooling-php/_vps_live')

def once(text, old, new, label):
    count=text.count(old)
    if count != 1: raise RuntimeError(f'{label}: expected one match, got {count}')
    return text.replace(old,new,1)

c=paramiko.SSHClient(); c.set_missing_host_key_policy(paramiko.AutoAddPolicy()); c.connect('103.97.134.164',username='root',password='lcBFDjVF15',timeout=30)
def run(cmd):
    _,o,e=c.exec_command(cmd,timeout=90); out=o.read().decode('utf-8','replace'); err=e.read().decode('utf-8','replace'); status=o.channel.recv_exit_status()
    if status: raise RuntimeError(f'{cmd}\n{out}\n{err}')
    return out.strip()
def get(path):
    s=c.open_sftp()
    try:
        with s.open(path,'rb') as f: return f.read().decode('utf-8')
    finally: s.close()
def put(path,data):
    s=c.open_sftp()
    try:
        with s.open(path,'wb') as f: f.write(data.encode('utf-8'))
    finally: s.close()

paths={'rbac':ROOT+'/includes/rbac.php','routes':ROOT+'/routes/admin.php','view':ROOT+'/views/admin/product-form.php'}
stamp=datetime.now().strftime('%Y%m%d-%H%M%S'); backup=f'/var/backups/coolingsystems/rbac-phase37-{stamp}'
baseline=run("sqlite3 -separator '|' "+DB+" \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
run(f"mkdir -p {backup}; sqlite3 {DB} '.backup {backup}/cooling.db'; cp {paths['rbac']} {backup}/rbac.php; cp {paths['routes']} {backup}/admin.php; cp {paths['view']} {backup}/product-form.php")
try:
    routes=get(paths['routes']); view=get(paths['view'])
    routes=once(routes,"get('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login');", "get('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login');\n    if ((($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']) && !rbacCan((int)$user['id'], 'catalog.codes.manage')) { flash('error','Ban khong co quyen tao ma SKU/OEM.'); redirect('/admin/products'); }",'new form code gate')
    routes=once(routes,"post('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login'); csrfCheck();\n    $d = $_POST;", "post('/admin/products/new', function() {\n    $user = requireStaffPermission('rbac:catalog.products.create|products', '/admin/login'); csrfCheck();\n    $d = $_POST;\n    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);\n    if ($detailedRbac && !rbacCan((int)$user['id'], 'catalog.codes.manage')) { flash('error','Ban khong co quyen tao ma SKU/OEM.'); redirect('/admin/products'); return; }\n    if ($detailedRbac) { foreach (['price','price_before_tax','tax_amount','original_price','stock','min_stock','cost_price'] as $lockedField) $d[$lockedField]='0'; $d['max_stock']='1000'; $d['warranty_months']='0'; $d['_inventory_in_product_form']=''; }",'new action code and inventory gate')
    routes=once(routes,"    $currentProduct = dbGet('SELECT slug, seo_title, seo_description FROM products WHERE id=?', [$p['id']]);", "    $currentProduct = dbGet('SELECT * FROM products WHERE id=?', [$p['id']]);",'full product comparison')
    routes=once(routes,"    $editSku = resolveProductSku($d['sku'] ?? '', $editOem);\n", "    $editSku = resolveProductSku($d['sku'] ?? '', $editOem);\n    $detailedRbac = (($user['role'] ?? '') === 'staff') && rbacUsesDetailedMode((int)$user['id']);\n    if ($detailedRbac) {\n      $fieldCapabilities = ['sku'=>'catalog.codes.manage','oem_code'=>'catalog.codes.manage','price'=>'catalog.pricing.edit','original_price'=>'catalog.pricing.edit','cost_price'=>'catalog.cost.edit','stock'=>'inventory.update','min_stock'=>'inventory.thresholds.edit','max_stock'=>'inventory.thresholds.edit','warranty_months'=>'catalog.products.edit','status'=>'catalog.products.archive'];\n      $requested = ['sku'=>$editSku,'oem_code'=>$editOem,'price'=>$price,'original_price'=>intval($d['original_price']??0),'cost_price'=>intval($d['cost_price']??0),'stock'=>$stock,'min_stock'=>intval($d['min_stock']??0),'max_stock'=>$maxStock,'warranty_months'=>intval($d['warranty_months']??12),'status'=>$status];\n      foreach ($fieldCapabilities as $field=>$capability) { if ((string)($currentProduct[$field] ?? '') !== (string)$requested[$field] && !rbacCan((int)$user['id'], $capability)) { flash('error','Ban khong co quyen thay doi truong du lieu nay.'); redirect($editUrl); return; } }\n    }\n",'edit field authorization')
    routes=once(routes,"    $images = dbAll('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, is_main DESC', [$p['id']]);\n    view('admin/product-form',", "    $images = dbAll('SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, is_main DESC', [$p['id']]);\n    $canEditProductCodes = !(($user['role'] ?? '') === 'staff' && rbacUsesDetailedMode((int)$user['id'])) || rbacCan((int)$user['id'], 'catalog.codes.manage');\n    view('admin/product-form',",'edit form code flag')
    routes=once(routes,"'images'=>$images,'returnTo'=>$returnTo]);", "'images'=>$images,'returnTo'=>$returnTo,'canEditProductCodes'=>$canEditProductCodes]);",'edit form flag data')
    view=once(view,"<?php\n$returnTo = $returnTo ?? '/admin/products';", "<?php\n$canEditProductCodes = $canEditProductCodes ?? true;\n$returnTo = $returnTo ?? '/admin/products';",'form default flag')
    view=once(view,"<input type=\"text\" name=\"sku\" value=\"<?= e($product['sku']??'') ?>\" placeholder=\"Để trống sẽ dùng mã OEM\">", "<input type=\"text\" name=\"sku\" <?= $canEditProductCodes ? '' : 'readonly' ?> value=\"<?= e($product['sku']??'') ?>\" placeholder=\"Để trống sẽ dùng mã OEM\">",'sku readonly')
    view=once(view,"<input type=\"text\" name=\"oem_code\" value=\"<?= e($product['oem_code']??'') ?>\" placeholder=\"VD: PFR6V\">", "<input type=\"text\" name=\"oem_code\" <?= $canEditProductCodes ? '' : 'readonly' ?> value=\"<?= e($product['oem_code']??'') ?>\" placeholder=\"VD: PFR6V\">",'oem readonly')
    put('/tmp/rbac37.php',(LOCAL/'rbac.php').read_text(encoding='utf-8')); put('/tmp/rbac37-seed.php',(LOCAL/'seed_rbac_phase37.php').read_text(encoding='utf-8')); put('/tmp/rbac37-admin.php',routes); put('/tmp/rbac37-product-form.php',view)
    run('chown www-data:www-data /tmp/rbac37.php /tmp/rbac37-seed.php /tmp/rbac37-admin.php /tmp/rbac37-product-form.php; runuser -u www-data -- php /tmp/rbac37-seed.php')
    run(f'install -o www-data -g www-data -m 0644 /tmp/rbac37.php {paths["rbac"]}; install -o www-data -g www-data -m 0644 /tmp/rbac37-admin.php {paths["routes"]}; install -o www-data -g www-data -m 0644 /tmp/rbac37-product-form.php {paths["view"]}; php -l {paths["rbac"]}; php -l {paths["routes"]}; php -l {paths["view"]}; rm -f /tmp/rbac37.php /tmp/rbac37-seed.php /tmp/rbac37-admin.php /tmp/rbac37-product-form.php')
except Exception:
    run(f'cp {backup}/cooling.db {DB}; chown www-data:www-data {DB}; cp {backup}/rbac.php {paths["rbac"]}; cp {backup}/admin.php {paths["routes"]}; cp {backup}/product-form.php {paths["view"]}; rm -f /tmp/rbac37.php /tmp/rbac37-seed.php /tmp/rbac37-admin.php /tmp/rbac37-product-form.php')
    raise
after=run("sqlite3 -separator '|' "+DB+" \"SELECT (SELECT COUNT(*) FROM products),(SELECT COUNT(*) FROM users),(SELECT COUNT(*) FROM staff_roles),(SELECT COUNT(*) FROM staff_role_assignments),(SELECT COUNT(*) FROM rbac_capability_rules);\"")
print('BACKUP='+backup); print('BASELINE='+baseline); print('AFTER='+after); c.close()
