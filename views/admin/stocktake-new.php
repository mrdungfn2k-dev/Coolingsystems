<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="/admin/stocktake" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
        <div>
            <h1>Tạo phiếu kiểm kho mới</h1>
            <p style="color:#667085;margin:0">Chọn danh sách sản phẩm cần kiểm tra tồn kho thực tế.</p>
        </div>
    </div>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<div class="panel" style="padding:24px;max-width:700px">
    <form method="post" action="/admin/stocktake">
        <?= csrfField() ?>

        <div class="form-group">
            <label>Tiêu đề phiếu kiểm kho <span style="color:#dc2626">*</span></label>
            <input type="text" name="title" required maxlength="200"
                   placeholder="VD: Kiểm kho tháng 7/2026 — Kho chính"
                   value="<?= e($_POST['title']??'Kiểm kho '.date('m/Y')) ?>">
        </div>

        <div class="form-group">
            <label>Phạm vi sản phẩm</label>
            <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px">
                <label style="cursor:pointer;display:flex;align-items:center;gap:8px">
                    <input type="radio" name="scope" value="all" <?= ($_POST['scope']??'all')==='all'?'checked':'' ?>>
                    <span><strong>Toàn bộ sản phẩm đang hoạt động</strong> (<?= number_format($totalProducts) ?> sản phẩm)</span>
                </label>
                <label style="cursor:pointer;display:flex;align-items:center;gap:8px">
                    <input type="radio" name="scope" value="category" <?= ($_POST['scope']??'')==='category'?'checked':'' ?>>
                    <span>Theo danh mục</span>
                </label>
                <label style="cursor:pointer;display:flex;align-items:center;gap:8px">
                    <input type="radio" name="scope" value="low_stock" <?= ($_POST['scope']??'')==='low_stock'?'checked':'' ?>>
                    <span>Chỉ hàng tồn thấp (<?= number_format($lowStockCount) ?> sản phẩm)</span>
                </label>
            </div>
        </div>

        <div class="form-group" id="category-select" style="display:none">
            <label>Chọn danh mục</label>
            <select name="category_id" multiple style="height:160px">
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small style="color:#667085">Giữ Ctrl/Cmd để chọn nhiều</small>
        </div>

        <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" maxlength="500" rows="3" placeholder="Ghi chú thêm về đợt kiểm kho..."></textarea>
        </div>

        <div style="display:flex;gap:10px;align-items:center">
            <button type="submit" class="btn btn-navy">Tạo phiếu kiểm kho</button>
            <span style="font-size:12px;color:#667085">Phiếu sẽ ở trạng thái "Đang đếm" cho đến khi gửi duyệt.</span>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[name=scope]').forEach(function(r){
    r.addEventListener('change',function(){
        document.getElementById('category-select').style.display=this.value==='category'?'block':'none';
    });
});
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
