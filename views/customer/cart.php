<?php require __DIR__.'/../partials/head.php'; ?>

<style>

/* ── Cart layout ── */

.cart-wrap{display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start}

@media(max-width:768px){.cart-wrap{grid-template-columns:1fr}}



/* ── Desktop table (hidden on mobile) ── */

.cart-tbl{width:100%;border-collapse:collapse;display:table}

.cart-tbl th{background:var(--bg-soft);padding:10px 12px;text-align:left;font-size:12px;font-weight:700;color:var(--ink-2);border-bottom:2px solid var(--line)}

.cart-tbl td{padding:12px;border-bottom:1px solid var(--line);vertical-align:middle;font-size:13px}

.cart-tbl tr:last-child td{border-bottom:none}

@media(max-width:640px){.cart-tbl,.cart-tbl thead,.cart-tbl tbody,.cart-tbl tr,.cart-tbl td{display:block;width:100%} .cart-tbl thead{display:none}}



/* ── Mobile card (shown on mobile) ── */

.cart-mobile-card{display:none}

@media(max-width:640px){

  .cart-mobile-card{display:block}

  .cart-table-wrap{display:none}

}



.cmc-item{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;border-bottom:1px solid var(--line);background:#fff;position:relative}

.cmc-item:last-child{border-bottom:none}

.cmc-img{width:72px;height:72px;flex-shrink:0;border-radius:6px;border:1px solid var(--line);object-fit:cover;background:var(--bg-soft);display:flex;align-items:center;justify-content:center;overflow:hidden}

.cmc-img img{width:100%;height:100%;object-fit:cover;border-radius:5px}

.cmc-img svg{color:#ccc}

.cmc-body{flex:1;min-width:0}

.cmc-name{font-size:13px;font-weight:600;color:var(--navy);line-height:1.4;margin-bottom:3px}

.cmc-shop{font-size:11px;color:var(--ink-3);margin-bottom:6px}

.cmc-price{font-size:13px;font-weight:700;color:var(--navy);margin-bottom:8px}

.cmc-footer{display:flex;align-items:center;justify-content:space-between;gap:8px}

.cmc-sub{font-size:14px;font-weight:800;color:var(--navy)}

.cmc-remove{position:absolute;top:12px;right:12px;background:none;border:1px solid #e5e7eb;cursor:pointer;color:#aaa;font-size:16px;padding:4px 8px;border-radius:4px;line-height:1;display:inline-flex;align-items:center;justify-content:center;min-width:28px;min-height:28px}

.cmc-remove:hover{color:#e74c3c;background:#fff0f0}



/* ── Qty control ── */

.qty-ctrl{display:inline-flex;align-items:center;border:1px solid var(--line);border-radius:5px;overflow:hidden}

.qty-ctrl button{background:var(--bg-soft);border:none;width:30px;height:30px;font-size:16px;font-weight:700;cursor:pointer;color:var(--navy);display:flex;align-items:center;justify-content:center}

.qty-ctrl button:hover{background:var(--gold-soft)}

.qty-ctrl input{width:38px;text-align:center;border:none;border-left:1px solid var(--line);border-right:1px solid var(--line);height:30px;font-size:13px;font-weight:700;-moz-appearance:textfield}

.qty-ctrl input::-webkit-outer-spin-button,.qty-ctrl input::-webkit-inner-spin-button{-webkit-appearance:none}



/* ── Summary sidebar ── */

.cart-summary{background:#fff;border:1px solid var(--line);border-radius:8px;padding:18px}

@media(max-width:768px){.cart-summary{margin-top:0}}

.cart-summary h3{font-size:15px;font-weight:700;color:var(--navy);border-bottom:1px solid var(--line);padding-bottom:10px;margin-bottom:14px}

.sum-row{display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:var(--ink-2)}

.sum-row.total{font-size:16px;font-weight:800;color:var(--navy);border-top:2px solid var(--line);padding-top:12px;margin-top:6px}

.cart-notice{font-size:11px;color:var(--ink-3);line-height:1.8;margin-top:12px;padding:10px;background:var(--bg-soft);border-radius:4px}



/* ── Desktop img in table ── */

.tbl-img{width:54px;height:54px;object-fit:cover;border-radius:4px;border:1px solid var(--line);flex-shrink:0}

.tbl-img-ph{width:54px;height:54px;background:var(--bg-soft);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc}

.btn-del{background:none;border:1px solid #e5e7eb;color:#999;cursor:pointer;font-size:20px;font-weight:300;padding:4px 10px;border-radius:4px;line-height:1;display:inline-flex;align-items:center;justify-content:center;min-width:32px;min-height:32px;transition:all 0.15s}

.btn-del:hover{color:#e74c3c;background:#fff0f0;border-color:#e74c3c}

</style>



<section class="block"><div class="wrap">

  <nav class="breadcrumb"><a href="/">Trang chủ</a><span class="sep">›</span><span>Giỏ hàng</span></nav>

  <div class="sec-head"><div class="title"><span class="bar"></span><h1 style="font-size:20px">Giỏ hàng</h1></div></div>



  <?php if(empty($items)):?>

    <div style="text-align:center;padding:60px 0">

      <div style="font-size:48px;margin-bottom:16px"></div>

      <h2 style="color:var(--navy)">Giỏ hàng trống</h2>

      <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng</p>

      <a href="/products" class="btn btn-gold" style="margin-top:16px">Khám phá sản phẩm</a>

    </div>

  <?php else:

    $total=0; $totalQty=0; foreach($items as $it){ $total+=$it['price']*$it['quantity']; $totalQty+=$it['quantity']; }

    

    // Load quantity discount config

    $cfgRows = dbAll("SELECT key, value FROM system_config WHERE key IN ('default_shipping_fee','free_shipping_threshold','discount_quantity_threshold','discount_quantity_percent')");

    $cfg = []; foreach($cfgRows as $r) $cfg[$r['key']] = $r['value'];

    $shipFee = intval($cfg['default_shipping_fee'] ?? 30000);

    $freeShipThreshold = intval($cfg['free_shipping_threshold'] ?? 2000000);

    $discountQtyThreshold = intval($cfg['discount_quantity_threshold'] ?? 0);

    $discountQtyPercent = floatval($cfg['discount_quantity_percent'] ?? 0);

    

    $shipping = 0; // Phí ship tính ở bước thanh toán theo tỉnh + cân nặng

    $discount = 0;

    $qtyDiscount = 0;

    

    // Quantity discount

    if ($discountQtyThreshold > 0 && $totalQty >= $discountQtyThreshold && $discountQtyPercent > 0) {

        $qtyDiscount = (int)ceil($total * ($discountQtyPercent / 100));

        $discount += $qtyDiscount;

    }

    

    // Voucher discount

    $voucherDiscount = 0;

    if (!empty($_SESSION['cart_voucher'])) {

        $v = $_SESSION['cart_voucher'];

        if (($v['discount_type'] ?? '') === 'percent') {

            $voucherDiscount = round(($total * ($v['discount_amount'] ?? 0)) / 100);

        } else {

            $voucherDiscount = $v['discount_amount'] ?? 0;

        }

        if ($voucherDiscount > $total) $voucherDiscount = $total;

        $discount += $voucherDiscount;

    }

    $grand = $total + $shipping - $discount;

  ?>

  <div class="cart-wrap">

    <div>

      <!-- ══════════════════════════════════════════════

           MOBILE CARD LAYOUT (shown ≤640px)

      ══════════════════════════════════════════════ -->

      <div class="cart-mobile-card sec-card" style="padding:0;overflow:hidden">

        <?php foreach($items as $it): $sub=$it['price']*$it['quantity']; ?>

        <div class="cmc-item" id="mrow-<?=$it['product_id']?>">

          <!-- Image -->

          <div class="cmc-img">

            <?php if($it['main_image']):?>

              <img src="/uploads/products/<?=e($it['main_image'])?>" alt="<?=e($it['name'])?>" loading="lazy">

            <?php else:?>

              <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>

            <?php endif;?>

          </div>



          <!-- Info -->

          <div class="cmc-body">

            <div class="cmc-name">

              <a href="/products/<?=$it['product_id']?>" style="color:inherit;text-decoration:none"><?=e($it['name'])?></a>

            </div>

            <div class="cmc-shop"><?=e($it['shop_name'])?><?php if($it['oem_code']):?> · <span class="mono"><?=e($it['oem_code'])?></span><?php endif;?></div>

            <div class="cmc-price"><?=vnd($it['price'])?> / cái</div>

            <div class="cmc-footer">

              <div class="qty-ctrl">

                <button type="button" onclick="updateQty(<?=$it['product_id']?>,-1,<?=$it['stock']?>)">−</button>

                <input type="number" id="mqty-<?=$it['product_id']?>" value="<?=$it['quantity']?>" min="1" max="<?=min(10,$it['stock'])?>" onchange="setQty(<?=$it['product_id']?>,this.value,<?=$it['stock']?>)">

                <button type="button" onclick="updateQty(<?=$it['product_id']?>,1,<?=$it['stock']?>)">+</button>

              </div>

              <div class="cmc-sub" id="msub-<?=$it['product_id']?>"><?=vnd($sub)?></div>

            </div>

          </div>



          <!-- Delete -->

          <button class="cmc-remove" onclick="removeItem(<?=$it['product_id']?>)" title="Xóa">×</button>

        </div>

        <?php endforeach;?>

      </div>



      <!-- ══════════════════════════════════════════════

           DESKTOP TABLE LAYOUT (shown >640px)

      ══════════════════════════════════════════════ -->

      <div class="cart-table-wrap sec-card" style="padding:0;overflow:hidden">

        <table class="cart-tbl">

          <thead><tr>

            <th colspan="2">Sản phẩm</th>

            <th>Đơn giá</th>

            <th>Số lượng</th>

            <th>Thành tiền</th>

            <th></th>

          </tr></thead>

          <tbody>

          <?php foreach($items as $it): $sub=$it['price']*$it['quantity']; ?>

          <tr id="row-<?=$it['product_id']?>">

            <td style="width:66px;padding-right:0">

              <?php if($it['main_image']):?>

                <img class="tbl-img" src="/uploads/products/<?=e($it['main_image'])?>" alt="<?=e($it['name'])?>" loading="lazy">

              <?php else:?>

                <div class="tbl-img-ph">

                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>

                </div>

              <?php endif;?>

            </td>

            <td>

              <a href="/products/<?=$it['product_id']?>" class="fw-600 text-navy" style="font-size:13px"><?=e($it['name'])?></a>

              <div class="fs-12 text-muted"><?=e($it['shop_name'])?></div>

              <?php if($it['oem_code']):?><div class="fs-11 mono text-muted">OEM: <?=e($it['oem_code'])?></div><?php endif;?>

            </td>

            <td class="fw-600" style="white-space:nowrap"><?=vnd($it['price'])?></td>

            <td>

              <div class="qty-ctrl">

                <button type="button" onclick="updateQty(<?=$it['product_id']?>,-1,<?=$it['stock']?>)">−</button>

                <input type="number" id="qty-<?=$it['product_id']?>" value="<?=$it['quantity']?>" min="1" max="<?=min(10,$it['stock'])?>" onchange="setQty(<?=$it['product_id']?>,this.value,<?=$it['stock']?>)">

                <button type="button" onclick="updateQty(<?=$it['product_id']?>,1,<?=$it['stock']?>)">+</button>

              </div>

            </td>

            <td class="fw-700" id="sub-<?=$it['product_id']?>" style="white-space:nowrap"><?=vnd($sub)?></td>

            <td><button class="btn-del" onclick="removeItem(<?=$it['product_id']?>)" title="Xóa">×</button></td>

          </tr>

          <?php endforeach;?>

          </tbody>

        </table>

      </div>



      <div style="margin-top:12px">

        <a href="/products" class="btn btn-outline-navy btn-sm">← Tiếp tục mua sắm</a>

      </div>

    </div>



    <!-- Summary -->

    <div class="cart-summary">

      <h3>Tóm tắt đơn hàng</h3>

      <div class="sum-row"><span>Tạm tính</span><span id="sumSubtotal"><?=vnd($total)?></span></div>

      <div class="sum-row"><span>Phí vận chuyển</span><span style="color:#888;font-size:12.5px">Tính khi thanh toán</span></div>

      <?php if($qtyDiscount > 0): ?>

      <div class="sum-row" style="color:#e65100"><span>Giảm SL (<?= $totalQty ?> SP ≥ <?= $discountQtyThreshold ?>, -<?= $discountQtyPercent ?>%)</span><span>-<?=vnd($qtyDiscount)?></span></div>

      <?php endif; ?>

      <?php if($voucherDiscount > 0): ?>

      <div class="sum-row" style="color:#16a34a"><span>Mã giảm giá</span><span>-<?=vnd($voucherDiscount)?></span></div>

      <?php endif; ?>

      <div class="sum-row total"><span>Tổng cộng</span><span id="sumTotal"><?=vnd($grand)?></span></div>

      <div class="cart-notice"> Phí ship tính theo khu vực &amp; cân nặng<br> Bảo hành chính hãng<br> Đổi trả 7 ngày</div>

      

      <div style="margin-top:16px; border-top:1px dashed var(--line); padding-top:16px;">

        <form method="post" action="/customer/cart/apply-voucher" style="display:flex;gap:8px">

          <?= csrfField() ?>

          <input type="text" name="voucher_code" placeholder="Mã giảm giá" style="flex:1;padding:8px 10px;border:1px solid var(--line);border-radius:4px;font-size:13px;text-transform:uppercase;outline:none;" required>

          <button type="submit" style="padding:8px 14px;background:var(--navy);color:#fff;border:none;border-radius:4px;font-size:13px;font-weight:600;cursor:pointer">Áp dụng</button>

        </form>

        <?php if (!empty($_SESSION['cart_voucher'])): ?>

          <div style="margin-top:8px;font-size:12px;color:#16a34a;font-weight:600;">

             Đã áp dụng mã: <?= e($_SESSION['cart_voucher']['code']) ?> 

            <a href="/customer/cart/remove-voucher" style="color:#dc2626;text-decoration:none;margin-left:8px;font-weight:400">[Xóa]</a>

          </div>

        <?php endif; ?>

      </div>



      <a href="/customer/checkout" class="btn btn-navy btn-block btn-lg" style="margin-top:16px;display:block;text-align:center;border-radius:6px">Thanh toán →</a>

    </div>

  </div>

  <?php endif;?>

</div></section>



<script>

var _csrf = '<?= csrfToken() ?>';

function fmtVnd(n){return new Intl.NumberFormat('vi-VN').format(n)+' ₫';}

function post(url,data,cb){

  var fd=new FormData();

  fd.append('_csrf',_csrf);

  for(var k in data) fd.append(k,data[k]);

  fetch(url,{method:'POST',body:fd,credentials:'same-origin'})

    .then(function(r){return r.json();}).then(cb)

    .catch(function(){alert('Lỗi kết nối');});

}

function updateQty(pid,d,max){

  var inp=document.getElementById('qty-'+pid)||document.getElementById('mqty-'+pid);

  if(!inp) return;

  var v=Math.max(1,Math.min(Math.min(10,max||999),parseInt(inp.value||1)+d));

  inp.value=v;

  // Sync both inputs if they exist

  var inp2=document.getElementById(inp.id==='qty-'+pid?'mqty-'+pid:'qty-'+pid);

  if(inp2) inp2.value=v;

  setQty(pid,v,max);

}

function setQty(pid,qty,max){

  qty=Math.max(1,Math.min(Math.min(10,max||999),parseInt(qty)||1));

  if(qty>10){alert('Mỗi sản phẩm chỉ được mua tối đa 10 số lượng');qty=10;}

  post('/customer/cart/update',{product_id:pid,qty:qty},function(d){

    if(d.ok){ if(window.csNav) csNav("/customer/cart"); else location.reload(); }

  });

}

async function removeItem(pid){

  if(!(await csConfirmAsync('Xóa sản phẩm này?'))) return;

  post('/customer/cart/remove',{product_id:pid},function(d){

    if(d.ok){ if(window.csNav) csNav("/customer/cart"); else location.reload(); }

  });

}

</script>

<?php require __DIR__.'/../partials/foot.php'; ?>

