<!-- Global confirm popup (replaces native confirm). Included once via foot.php. -->
<div id="csConfirmModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(10,25,47,0.5);align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:14px;max-width:380px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,0.3);padding:24px 22px;text-align:center">
    <div style="font-size:16px;font-weight:700;color:#0a192f;margin-bottom:8px">Xác nhận</div>
    <p id="csConfirmMsg" style="font-size:13.5px;color:#555;margin:0 0 20px;line-height:1.5"></p>
    <div style="display:flex;gap:10px">
      <button type="button" id="csConfirmCancel" style="flex:1;padding:11px;background:#eef2f7;color:#0a192f;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px">Hủy</button>
      <button type="button" id="csConfirmOk" style="flex:1;padding:11px;background:#ef4444;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px">Xác nhận</button>
    </div>
  </div>
</div>
<script>
(function(){
  var _ok=null,_cancel=null,bound=false;
  function close(){_ok=null;_cancel=null;var m=document.getElementById('csConfirmModal');if(m)m.style.display='none';}
  function bind(){ if(bound)return; var m=document.getElementById('csConfirmModal'); if(!m)return; bound=true;
    document.getElementById('csConfirmOk').addEventListener('click',function(){var f=_ok;close();if(typeof f==='function')f();});
    document.getElementById('csConfirmCancel').addEventListener('click',function(){var f=_cancel;close();if(typeof f==='function')f();});
    m.addEventListener('click',function(ev){if(ev.target===m){var f=_cancel;close();if(typeof f==='function')f();}});
    document.addEventListener('keydown', function(e) {
      var modal = document.getElementById('csConfirmModal');
      if (!modal || modal.style.display !== 'flex') return;
      if (e.key === 'Enter') {
        e.preventDefault(); e.stopPropagation();
        var okBtn = document.getElementById('csConfirmOk');
        if (okBtn) okBtn.click();
      } else if (e.key === 'Escape') {
        e.preventDefault(); e.stopPropagation();
        var cancelBtn = document.getElementById('csConfirmCancel');
        if (cancelBtn) cancelBtn.click();
      }
    }, true);
  }
  window.csConfirm=function(message,onOk,onCancel){ var m=document.getElementById('csConfirmModal');
    if(!m){ if(window.confirm(message)){if(onOk)onOk();}else{if(onCancel)onCancel();} return; }
    bind(); document.getElementById('csConfirmMsg').textContent=message||'Bạn có chắc chắn?'; _ok=onOk; _cancel=onCancel; m.style.display='flex'; };
  window.csConfirmAsync=function(message){ return new Promise(function(res){ csConfirm(message,function(){res(true);},function(){res(false);}); }); };
  window.csConfirmForm=function(form,message){ csConfirm(message,function(){ form.submit(); }); return false; };  /* form.submit() -> đi qua override prototype.submit (pjax AJAX), KHÔNG kích hoạt lại onsubmit -> tránh lặp modal */
  window.csConfirmBtn=function(btn,message){ csConfirm(message,function(){ var f=btn.form; if(f){ if(f.requestSubmit)f.requestSubmit(btn); else f.submit(); } }); return false; };
})();
</script>
