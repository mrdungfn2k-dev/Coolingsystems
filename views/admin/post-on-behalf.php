<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head"><h1>Đăng sản phẩm hộ đối tác</h1></div>
<div class="panel"><div class="panel-body">
  <form method="post" action="/admin/post-on-behalf"><?=csrfField()?>
    <div class="form-row">
      <div class="form-group"><label>Đối tác *</label><select name="partner_id" required>
        <option value="">— Chọn đối tác —</option>
        <?php foreach($partners as $p):?><option value="<?=$p['id']?>"><?=e($p['shop_name'])?></option><?php endforeach;?>
      </select></div>
      <div class="form-group"><label>Danh mục</label><select name="category_id">
        <option value="">— Chọn —</option>
        <?php foreach($categories as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?>
      </select></div>
    </div>
    <div class="form-group"><label>Tên sản phẩm *</label><input type="text" name="name" required></div>
    <div class="form-row">
      <div class="form-group"><label>Mã OEM</label><input type="text" name="oem_code"></div>
      <div class="form-group"><label>Giá (VNĐ) *</label><input type="number" name="price" required></div>
      <div class="form-group"><label>Tồn kho</label><input type="number" name="stock" value="10"></div>
    </div>
    <div class="form-group"><label>Mô tả</label><textarea name="description" rows="4"></textarea></div>
    <button type="submit" class="btn btn-gold btn-lg">Đăng sản phẩm</button>
  </form>
</div></div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
