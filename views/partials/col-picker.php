<!-- CSV export column picker (chọn cột xuất) — admin only -->
<style>
.cscol-ov{display:none;position:fixed;inset:0;z-index:100000;background:rgba(10,25,47,.5);align-items:center;justify-content:center;padding:16px}
.cscol-box{background:#fff;border-radius:14px;max-width:560px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.3);padding:22px 24px;max-height:85vh;display:flex;flex-direction:column}
.cscol-box h3{margin:0 0 4px;font-size:17px;color:#16243f}
.cscol-sub{font-size:12.5px;color:#7a869a;margin-bottom:12px}
.cscol-tools{display:flex;gap:16px;margin-bottom:10px;font-size:12.5px}
.cscol-tools a{color:#3b82f6;cursor:pointer;font-weight:600}
.cscol-list{display:grid;grid-template-columns:1fr 1fr;gap:4px 16px;overflow-y:auto;padding:8px 2px;border-top:1px solid #eef1f6;border-bottom:1px solid #eef1f6;margin-bottom:14px}
@media(max-width:520px){.cscol-list{grid-template-columns:1fr}}
.cscol-item{display:flex;align-items:center;gap:8px;font-size:13px;color:#26334d;padding:7px 8px;border-radius:7px;cursor:pointer}
.cscol-item:hover{background:#f6f9fd}
.cscol-item input{width:16px;height:16px;accent-color:#16243f;cursor:pointer;flex-shrink:0}
.cscol-actions{display:flex;gap:10px;justify-content:flex-end}
.cscol-actions button{padding:10px 20px;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:13.5px}
.cscol-cancel{background:#eef2f7;color:#16243f}
.cscol-ok{background:#16243f;color:#fff}
</style>
<div id="csColOv" class="cscol-ov" onclick="if(event.target===this)csColClose()">
  <div class="cscol-box">
    <h3 id="csColTitle">Chọn cột muốn xuất</h3>
    <div class="cscol-sub">Tích các trường dữ liệu cần xuất ra file CSV (mặc định chọn tất cả).</div>
    <div class="cscol-tools"><a onclick="csColAll(true)">✓ Chọn tất cả</a><a onclick="csColAll(false)">✕ Bỏ chọn</a><span id="csColCount" style="margin-left:auto;color:#7a869a;font-weight:600"></span></div>
    <div id="csColList" class="cscol-list"></div>
    <div class="cscol-actions">
      <button type="button" class="cscol-cancel" onclick="csColClose()">Hủy</button>
      <button type="button" class="cscol-ok" onclick="csColExport()">⬇ Xuất CSV</button>
    </div>
  </div>
</div>
<script>
window.CS_EXPORT_COLS = <?= json_encode(['products'=>exportColMeta('products'),'orders'=>exportColMeta('orders'),'users'=>exportColMeta('users'),'categories'=>exportColMeta('categories'),'brands'=>exportColMeta('brands'),'product_brands'=>exportColMeta('product_brands')], JSON_UNESCAPED_UNICODE) ?>;
var _csColOpt={url:'',extra:{}};
function csColCount(){ var t=document.querySelectorAll('#csColList input[type=checkbox]').length; var n=document.querySelectorAll('#csColList input[type=checkbox]:checked').length; var el=document.getElementById('csColCount'); if(el) el.textContent=n+'/'+t+' cột'; }
function csColPick(opts){
  _csColOpt.url=opts.url; _csColOpt.extra=opts.extra||{};
  document.getElementById('csColTitle').textContent='Chọn cột muốn xuất'+(opts.title?(' — '+opts.title):'');
  var cols=(window.CS_EXPORT_COLS||{})[opts.section]||{}; var h='';
  for(var k in cols){ h+='<label class="cscol-item"><input type="checkbox" value="'+k+'" checked onchange="csColCount()"> '+cols[k]+'</label>'; }
  document.getElementById('csColList').innerHTML=h||'<div style="color:#888;padding:10px">Không có cột.</div>';
  document.getElementById('csColOv').style.display='flex'; csColCount();
}
function csColAll(v){ document.querySelectorAll('#csColList input[type=checkbox]').forEach(function(c){c.checked=v;}); csColCount(); }
function csColClose(){ document.getElementById('csColOv').style.display='none'; }
function csColExport(){
  var keys=[]; document.querySelectorAll('#csColList input[type=checkbox]:checked').forEach(function(c){keys.push(c.value);});
  if(!keys.length){ alert('Vui lòng chọn ít nhất 1 cột để xuất.'); return; }
  var u=_csColOpt.url+(_csColOpt.url.indexOf('?')>=0?'&':'?')+'cols='+encodeURIComponent(keys.join(','));
  var ex=_csColOpt.extra; for(var p in ex){ var val=ex[p]; if(val!==undefined&&val!==null&&val!=='') u+='&'+encodeURIComponent(p)+'='+encodeURIComponent(val); }
  csColClose(); window.location=u;
}
</script>
