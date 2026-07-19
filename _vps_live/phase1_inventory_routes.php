
// ── INVENTORY MANAGEMENT ───────────────────────────────────────────────────
get('/admin/inventory', function() {
    requireStaffPermission('products', '/admin/login');
    $perPage = 25;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $q = trim((string)($_GET['q'] ?? ''));
    $stockStatus = in_array($_GET['status'] ?? 'all', ['all','low','out'], true) ? $_GET['status'] : 'all';
    $categoryId = max(0, (int)($_GET['category'] ?? 0));
    $where = 'WHERE 1=1'; $params = [];
    if ($q !== '') { $where .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.oem_code LIKE ?)'; $like='%'.$q.'%'; array_push($params,$like,$like,$like); }
    if ($categoryId) { $where .= ' AND p.category_id=?'; $params[]=$categoryId; }
    if ($stockStatus === 'low') $where .= ' AND p.min_stock>0 AND p.stock<=p.min_stock';
    if ($stockStatus === 'out') $where .= ' AND p.stock<=0';
    $total = (int)(dbGet("SELECT COUNT(*) AS c FROM products p $where", $params)['c'] ?? 0);
    $totalPages = max(1, (int)ceil($total/$perPage));
    $page = min($page,$totalPages);
    $listParams=array_merge($params,[$perPage,($page-1)*$perPage]);
    $products=dbAll("SELECT p.*,c.name AS category_name,(SELECT file_path FROM product_images WHERE product_id=p.id ORDER BY is_main DESC,sort_order,id LIMIT 1) AS image FROM products p LEFT JOIN categories c ON c.id=p.category_id $where ORDER BY CASE WHEN p.min_stock>0 AND p.stock<=p.min_stock THEN 0 ELSE 1 END,p.updated_at DESC,p.id DESC LIMIT ? OFFSET ?",$listParams);
    $summary=dbGet("SELECT COUNT(*) AS total,SUM(CASE WHEN min_stock>0 AND stock<=min_stock THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN stock<=0 THEN 1 ELSE 0 END) AS out FROM products") ?: ['total'=>0,'low'=>0,'out'=>0];
    $categories=dbAll('SELECT id,name FROM categories ORDER BY sort_order,name');
    view('admin/inventory',['title'=>'Quản lý kho','role'=>'admin','products'=>$products,'summary'=>$summary,'categories'=>$categories,'q'=>$q,'stockStatus'=>$stockStatus,'categoryId'=>$categoryId,'page'=>$page,'totalPages'=>$totalPages]);
});

post('/admin/inventory/:id/update', function($p) {
    $user=requireStaffPermission('products','/admin/login'); csrfCheck();
    $product=dbGet('SELECT * FROM products WHERE id=?',[$p['id']]);
    if(!$product){flash('error','Không tìm thấy sản phẩm.');redirect('/admin/inventory');return;}
    $fields=['cost_price','price','original_price','stock','min_stock','max_stock','warranty_months']; $values=[];
    foreach($fields as $field){$raw=trim((string)($_POST[$field]??'')); if($raw===''||!ctype_digit($raw)){flash('error','Dữ liệu kho không hợp lệ.');redirect('/admin/inventory');return;} $values[$field]=(int)$raw;}
    if($values['stock']>1000||$values['min_stock']>1000||$values['max_stock']>1000){flash('error','Tồn kho chỉ được từ 0 đến 1000.');redirect('/admin/inventory');return;}
    if($values['min_stock']>$values['max_stock']){flash('error','Tồn tối thiểu không được lớn hơn tồn tối đa.');redirect('/admin/inventory');return;}
    if($values['original_price']>0&&$values['original_price']<=$values['price']){flash('error','Giá gốc phải lớn hơn giá bán khi được nhập.');redirect('/admin/inventory');return;}
    dbRun("UPDATE products SET cost_price=?,price=?,original_price=?,stock=?,min_stock=?,max_stock=?,warranty_months=?,total_import_value=?,updated_at=datetime('now','localtime') WHERE id=?",[$values['cost_price'],$values['price'],$values['original_price']?:null,$values['stock'],$values['min_stock'],$values['max_stock'],$values['warranty_months'],$values['cost_price']*$values['stock'],$p['id']]);
    $before=['cost_price'=>(int)$product['cost_price'],'price'=>(int)$product['price'],'original_price'=>(int)$product['original_price'],'stock'=>(int)$product['stock'],'min_stock'=>(int)$product['min_stock'],'max_stock'=>(int)$product['max_stock'],'warranty_months'=>(int)$product['warranty_months']];
    try { dbRun("INSERT INTO audit_logs (user_id,role,action,entity_type,entity_id,meta,ip,user_agent) VALUES (?,?,?,?,?,?,?,?)",[$user['id'] ?? null,$user['role'] ?? 'admin','inventory_update','product',$p['id'],json_encode(['before'=>$before,'after'=>$values],JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR'] ?? '',$_SERVER['HTTP_USER_AGENT'] ?? '']); } catch(Throwable $e) {}
    flash('success','Đã cập nhật giá và tồn kho cho sản phẩm.');
    redirect('/admin/inventory');
});
