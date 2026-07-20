<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<style>
.adj-form{max-width:640px;margin:0 auto}
.adj-form .field-group{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px}
.adj-type-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:4px}
.adj-type-card{border:2px solid #e5e7eb;border-radius:10px;padding:12px 10px;cursor:pointer;text-align:center;transition:.15s}
.adj-type-card:hover,.adj-type-card.selected{border-color:#1a3258;background:#f0f4ff}
.adj-type-card input{display:none}
.adj-type-card .icon{font-size:22px;margin-bottom:4px}
.adj-type-card .label{font-size:12px;font-weight:700;color:#1a3258}
.adj-type-card .desc{font-size:11px;color:#667085;margin-top:2px}
.history-table tbody tr:hover{background:#f9fafb}
.adj-badge-in{background:#d1fae5;color:#059669;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
.adj-badge-out{background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
</style>

<div class="dash-head">
    <h1>Điều chỉnh tồn kho thủ công</h1>
    <p style="color:#667085">Tạo bút toán điều chỉnh với lý do rõ ràng. Mọi thao tác đều được ghi vào sổ kho và nhật ký kiểm toán.</p>
</div>

<?php foreach (getFlash() as $x): ?>
    <div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<div class="adj-form">
    <!-- Form điều chỉnh -->
    <div class="field-group">
        <h2 style="font-size:15px;font-weight:700;margin:0 0 16px">Tạo điều chỉnh mới</h2>
        <form method="post" action="/admin/inventory/adjust" id="adjust-form">
            <?= csrfField() ?>

            <div class="form-group">
                <label>Sản phẩm <span style="color:#dc2626">*</span></label>
                <input type="text" id="product-search" placeholder="Nhập SKU, OEM code hoặc tên sản phẩm..."
                       autocomplete="off" style="width:100%"
                       value="<?= e($_GET['product_name'] ?? '') ?>">
                <input type="hidden" name="product_id" id="product_id" value="<?= (int)($_GET['product_id']??0) ?>">
                <div id="product-info" style="display:<?= isset($_GET['product_id'])&&$_GET['product_id']>0?'block':'none' ?>;
                     margin-top:8px;padding:10px;background:#f0f4ff;border-radius:8px;font-size:13px">
                    Tồn hiện tại: <strong id="cur-stock">—</strong> |
                    Tối thiểu: <strong id="min-stock">—</strong> |
                    Tối đa: <strong id="max-stock">—</strong>
                </div>
                <div id="product-suggestions" style="position:relative;z-index:100"></div>
            </div>

            <div class="form-group">
                <label>Loại điều chỉnh <span style="color:#dc2626">*</span></label>
                <div class="adj-type-cards">
                    <label class="adj-type-card" id="card-add">
                        <input type="radio" name="direction" value="in" required>
                        <div class="icon">➕</div>
                        <div class="label">Tăng tồn</div>
                        <div class="desc">Thêm vào tồn kho</div>
                    </label>
                    <label class="adj-type-card" id="card-sub">
                        <input type="radio" name="direction" value="out" required>
                        <div class="icon">➖</div>
                        <div class="label">Giảm tồn</div>
                        <div class="desc">Bớt khỏi tồn kho</div>
                    </label>
                    <label class="adj-type-card" id="card-set">
                        <input type="radio" name="direction" value="set" required>
                        <div class="icon">🎯</div>
                        <div class="label">Đặt về số cụ thể</div>
                        <div class="desc">Ghi đè tồn hiện tại</div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label id="qty-label">Số lượng <span style="color:#dc2626">*</span></label>
                <input type="number" name="quantity" id="quantity" min="1" max="99999" required
                       placeholder="Nhập số lượng..." style="max-width:200px">
                <div id="qty-preview" style="margin-top:6px;color:#667085;font-size:13px"></div>
            </div>

            <div class="form-group">
                <label>Lý do điều chỉnh <span style="color:#dc2626">*</span>
                    <span style="font-weight:400;color:#667085;font-size:12px">(5–500 ký tự)</span>
                </label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px" id="reason-presets">
                    <?php
                    $presets = ['Hàng kiểm kê thực tế lệch','Hàng nhập bổ sung chưa có PO',
                                'Hàng hỏng / hết hạn hủy','Điều chuyển nội bộ','Xuất thử nghiệm kỹ thuật',
                                'Thiếu hụt do vận chuyển','Tặng mẫu khách hàng'];
                    foreach ($presets as $ps): ?>
                    <button type="button" class="btn btn-ghost" style="padding:4px 10px;font-size:12px"
                            onclick="document.getElementById('reason').value='<?= addslashes($ps) ?>'">
                        <?= e($ps) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <textarea name="reason" id="reason" required minlength="5" maxlength="500" rows="3"
                          placeholder="Mô tả lý do điều chỉnh chi tiết..."></textarea>
            </div>

            <button type="submit" class="btn btn-navy" id="submit-btn" disabled>Xác nhận điều chỉnh</button>
            <span style="font-size:12px;color:#667085;margin-left:10px">Thao tác này không thể hoàn tác.</span>
        </form>
    </div>
</div>

<!-- Lịch sử điều chỉnh thủ công gần đây -->
<div class="panel" style="margin-top:24px">
    <div style="padding:14px 16px;border-bottom:1px solid #f0f0f0">
        <h2 style="font-size:15px;font-weight:700;margin:0">Lịch sử điều chỉnh thủ công gần đây</h2>
    </div>
    <div style="overflow:auto">
        <table class="tbl history-table">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Sản phẩm</th>
                    <th>Chiều</th>
                    <th style="text-align:right">SL</th>
                    <th>Lý do</th>
                    <th>Người thực hiện</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td style="color:#667085;font-size:12px;white-space:nowrap"><?= e(substr($h['created_at'],0,16)) ?></td>
                    <td>
                        <strong><?= e($h['product_name']) ?></strong>
                        <div style="font-size:11px;color:#667085"><?= e($h['sku']) ?></div>
                    </td>
                    <td>
                        <span class="<?= $h['direction']==='in'?'adj-badge-in':'adj-badge-out' ?>">
                            <?= $h['direction']==='in'?'↑ Tăng':'↓ Giảm' ?>
                        </span>
                    </td>
                    <td style="text-align:right;font-weight:700;color:<?= $h['direction']==='in'?'#059669':'#dc2626' ?>">
                        <?= $h['direction']==='in'?'+':'-' ?><?= number_format((int)$h['quantity']) ?>
                    </td>
                    <td style="font-size:12px;max-width:220px;overflow:hidden;text-overflow:ellipsis" title="<?= e($h['note']) ?>"><?= e($h['note']) ?></td>
                    <td style="font-size:12px;color:#667085"><?= e($h['creator_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$history): ?>
                <tr><td colspan="6" style="padding:25px;text-align:center;color:#667085">Chưa có điều chỉnh thủ công nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
    // Type card selection
    document.querySelectorAll('.adj-type-card').forEach(function(card){
        card.addEventListener('click',function(){
            document.querySelectorAll('.adj-type-card').forEach(function(c){c.classList.remove('selected')});
            card.classList.add('selected');
            updatePreview();
        });
    });
    // Product search autocomplete
    var searchEl=document.getElementById('product-search');
    var productIdEl=document.getElementById('product_id');
    var suggestionsEl=document.getElementById('product-suggestions');
    var infoEl=document.getElementById('product-info');
    var curStockEl=document.getElementById('cur-stock');
    var minStockEl=document.getElementById('min-stock');
    var maxStockEl=document.getElementById('max-stock');
    var curStock=0,minStock=0,maxStock=0;
    var searchTimer=null;
    searchEl.addEventListener('input',function(){
        clearTimeout(searchTimer);
        var v=this.value.trim();
        if(v.length<2){suggestionsEl.innerHTML='';return;}
        searchTimer=setTimeout(function(){
            fetch('/admin/inventory/search-product?q='+encodeURIComponent(v))
            .then(function(r){return r.json();})
            .then(function(data){
                if(!data.length){suggestionsEl.innerHTML='<div style="padding:8px 12px;color:#667085;font-size:13px;background:#fff;border:1px solid #e5e7eb;border-radius:8px">Không tìm thấy sản phẩm.</div>';return;}
                var html='<div style="position:absolute;top:2px;left:0;right:0;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.1);max-height:240px;overflow-y:auto;z-index:200">';
                data.forEach(function(p){
                    html+='<div class="suggest-item" data-id="'+p.id+'" data-stock="'+p.stock+'" data-min="'+p.min_stock+'" data-max="'+p.max_stock+'" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f0f0f0">'
                        +'<strong>'+p.name+'</strong><br>'
                        +'<span style="font-size:11px;color:#667085">SKU: '+p.sku+' | Tồn: <b>'+p.stock+'</b></span>'
                        +'</div>';
                });
                html+='</div>';
                suggestionsEl.innerHTML=html;
                document.querySelectorAll('.suggest-item').forEach(function(item){
                    item.addEventListener('click',function(){
                        productIdEl.value=this.dataset.id;
                        curStock=parseInt(this.dataset.stock)||0;
                        minStock=parseInt(this.dataset.min)||0;
                        maxStock=parseInt(this.dataset.max)||0;
                        searchEl.value=this.querySelector('strong').textContent;
                        curStockEl.textContent=curStock;
                        minStockEl.textContent=minStock;
                        maxStockEl.textContent=maxStock;
                        infoEl.style.display='block';
                        suggestionsEl.innerHTML='';
                        updatePreview();
                        document.getElementById('submit-btn').disabled=false;
                    });
                });
            }).catch(function(){});
        },300);
    });
    document.addEventListener('click',function(e){if(!suggestionsEl.contains(e.target)&&e.target!==searchEl)suggestionsEl.innerHTML='';});
    // Quantity preview
    function updatePreview(){
        var dir=document.querySelector('input[name=direction]:checked');
        var qty=parseInt(document.getElementById('quantity').value)||0;
        var preview=document.getElementById('qty-preview');
        var lbl=document.getElementById('qty-label');
        if(!dir||qty<1){preview.textContent='';return;}
        if(dir.value==='set'){
            lbl.querySelector('span:first-child').textContent='Đặt tồn về số';
            preview.textContent='Tồn kho sẽ được đặt thành: '+qty+' (thay đổi: '+(qty-curStock>0?'+':'')+(qty-curStock)+')';
        } else if(dir.value==='in'){
            lbl.querySelector('span:first-child').textContent='Số lượng';
            preview.textContent='Tồn sau điều chỉnh: '+(curStock+qty);
        } else {
            lbl.querySelector('span:first-child').textContent='Số lượng';
            var after=curStock-qty;
            preview.textContent='Tồn sau điều chỉnh: '+Math.max(0,after)+(after<0?' ⚠️ (tồn kho sẽ bằng 0 do vượt số lượng hiện có)':'');
        }
    }
    document.getElementById('quantity').addEventListener('input',updatePreview);
    document.querySelectorAll('input[name=direction]').forEach(function(r){r.addEventListener('change',updatePreview);});
})();
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
