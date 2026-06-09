<!-- Custom cancel-order modal (replaces native confirm). Include ONCE per page. -->
<div id="cancelOrderModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(10,25,47,0.55);align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:14px;max-width:460px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,0.3);overflow:hidden;max-height:92vh;display:flex;flex-direction:column">
    <div style="background:#0a192f;color:#fff;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
      <strong style="font-size:16px">Hủy đơn hàng</strong>
      <button type="button" onclick="closeCancelModal()" style="background:none;border:none;color:#fff;font-size:24px;cursor:pointer;line-height:1;padding:0">&times;</button>
    </div>
    <form method="post" id="cancelOrderForm" enctype="multipart/form-data" style="padding:20px;overflow-y:auto">
      <?= csrfField() ?>
      <p style="margin:0 0 14px;font-size:13px;color:#555;line-height:1.5">Vui lòng cho biết lý do hủy đơn. Bạn có thể đính kèm hình ảnh minh họa (không bắt buộc).</p>
      <label style="display:block;font-size:13px;font-weight:700;color:#0a192f;margin-bottom:6px">Lý do hủy đơn <span style="color:#e74c3c">*</span></label>
      <textarea name="cancel_reason" required rows="3" maxlength="500" placeholder="VD: Đặt nhầm sản phẩm, thay đổi nhu cầu mua hàng..." style="width:100%;padding:10px 12px;border:1px solid #d6deea;border-radius:8px;font-size:14px;box-sizing:border-box;resize:vertical;font-family:inherit"></textarea>
      <label style="display:block;font-size:13px;font-weight:700;color:#0a192f;margin:14px 0 6px">Hình ảnh đính kèm <span style="font-weight:400;color:#888">(tùy chọn, tối đa 5 ảnh)</span></label>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <input type="file" id="cancelImgInput" name="cancel_images[]" accept="image/*" multiple onchange="cancelImgPreview(this)" style="display:none">
        <label for="cancelImgInput" style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1px dashed #c3cfe2;border-radius:8px;background:#f8fafc;font-size:13px;cursor:pointer;color:#0a192f;font-weight:600"><span style="font-size:16px;line-height:1">📷</span> Chọn ảnh</label>
        <span id="cancelImgName" style="font-size:12px;color:#888">Chưa chọn ảnh nào</span>
      </div>
      <div id="cancelImgPreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px"></div>
      <div style="display:flex;gap:10px;margin-top:20px">
        <button type="button" onclick="closeCancelModal()" style="flex:1;padding:11px;background:#eef2f7;color:#0a192f;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">Đóng</button>
        <button type="submit" style="flex:2;padding:11px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">Xác nhận hủy đơn</button>
      </div>
    </form>
  </div>
</div>
<script>
function openCancelModal(id){
  var f=document.getElementById('cancelOrderForm');
  f.action='/customer/orders/'+id+'/cancel';
  f.reset();
  document.getElementById('cancelImgPreview').innerHTML='';
  var nm=document.getElementById('cancelImgName'); if(nm) nm.textContent='Chưa chọn ảnh nào';
  document.getElementById('cancelOrderModal').style.display='flex';
}
function closeCancelModal(){ document.getElementById('cancelOrderModal').style.display='none'; }
function cancelImgPreview(inp){
  var box=document.getElementById('cancelImgPreview'); box.innerHTML='';
  var nm=document.getElementById('cancelImgName');
  var files=Array.prototype.slice.call(inp.files||[]);
  if(files.length>5){ alert('Chỉ được chọn tối đa 5 ảnh.'); inp.value=''; if(nm) nm.textContent='Chưa chọn ảnh nào'; return; }
  if(nm) nm.textContent = files.length ? ('Đã chọn ' + files.length + ' ảnh') : 'Chưa chọn ảnh nào';
  files.forEach(function(file){
    if(!/^image\//.test(file.type)) return;
    var img=document.createElement('img');
    img.style.cssText='width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0';
    img.src=URL.createObjectURL(file);
    box.appendChild(img);
  });
}
document.addEventListener('click',function(e){ if(e.target&&e.target.id==='cancelOrderModal') closeCancelModal(); });
</script>
