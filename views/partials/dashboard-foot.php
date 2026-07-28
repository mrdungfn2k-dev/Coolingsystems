  </div><!-- .dash-main -->
</div><!-- .dash -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var sidebar = document.querySelector('.dash-sidebar');
    var main = document.querySelector('.dash-main');
    
    if (sidebar) {
        var sbPos = sessionStorage.getItem('adminSidebarScrollpos');
        if (sbPos) sidebar.scrollTop = sbPos;
    }
    
    // Khôi phục vị trí cuộn cho nội dung chính
    var scrollpos = sessionStorage.getItem('adminMainScrollpos');
    var winScroll = sessionStorage.getItem('adminWindowScrollpos');
    if (scrollpos && main) main.scrollTop = scrollpos;
    if (winScroll) window.scrollTo(0, winScroll);
    
    window.addEventListener('beforeunload', function() {
        if (sidebar) sessionStorage.setItem('adminSidebarScrollpos', sidebar.scrollTop);
        if (main) sessionStorage.setItem('adminMainScrollpos', main.scrollTop);
        sessionStorage.setItem('adminWindowScrollpos', window.scrollY);
    });
});
</script>
<script>
/* admin tables: wrap in horizontal scroll + add column show/hide toggle */
(function(){
  function setCol(tbl, ci, vis){
    tbl.querySelectorAll('tr').forEach(function(r){
      var cell=r.children[ci];
      if(cell && (cell.colSpan||1)===1) cell.style.display = vis ? '' : 'none';
    });
  }
  function enhance(root){
    (root||document).querySelectorAll('table.tbl').forEach(function(tbl, idx){
      if(tbl.dataset.enh) return; tbl.dataset.enh='1';
      var ths = tbl.querySelectorAll('thead th, thead td');
      var wrap=document.createElement('div'); wrap.className='tbl-scroll';
      tbl.parentNode.insertBefore(wrap, tbl); wrap.appendChild(tbl);
      if(ths.length < 3) return;
      var key='tblcols:'+location.pathname+':'+(tbl.id||('t'+idx));
      var hidden=[]; try{ hidden=JSON.parse(localStorage.getItem(key)||'[]'); }catch(e){}
      var bar=document.createElement('div'); bar.className='tbl-coltools';
      var btn=document.createElement('button'); btn.type='button'; btn.className='tbl-colbtn';
      btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9h18M3 15h18M10 3v18"/></svg> Cột hiển thị';
      var menu=document.createElement('div'); menu.className='tbl-colmenu';
      menu.addEventListener('click', function(ev){ ev.stopPropagation(); });
      var _hd=document.createElement('div'); _hd.className='tbl-colmenu-hd'; _hd.innerHTML='<span>Hiển thị cột</span>';
      var _x=document.createElement('button'); _x.type='button'; _x.className='tbl-colmenu-x'; _x.innerHTML='&times;'; _x.title='Đóng';
      _x.addEventListener('click', function(){ menu.classList.remove('open'); });
      _hd.appendChild(_x); menu.appendChild(_hd);
      ths.forEach(function(th, ci){
        var name=(th.textContent||'').trim()||('Cột '+(ci+1));
        var lbl=document.createElement('label');
        var cb=document.createElement('input'); cb.type='checkbox'; cb.checked=hidden.indexOf(ci)===-1;
        cb.addEventListener('change', function(){
          setCol(tbl, ci, cb.checked);
          var h=[]; try{ h=JSON.parse(localStorage.getItem(key)||'[]'); }catch(e){}
          if(cb.checked){ h=h.filter(function(x){return x!==ci}); } else if(h.indexOf(ci)===-1){ h.push(ci); }
          localStorage.setItem(key, JSON.stringify(h));
        });
        lbl.appendChild(cb); lbl.appendChild(document.createTextNode(' '+name));
        menu.appendChild(lbl);
        if(hidden.indexOf(ci)!==-1) setCol(tbl, ci, false);
      });
      btn.addEventListener('click', function(e){ e.stopPropagation(); var o=menu.classList.contains('open'); document.querySelectorAll('.tbl-colmenu.open').forEach(function(m){m.classList.remove('open');}); if(!o) menu.classList.add('open'); });
      bar.appendChild(btn); bar.appendChild(menu);
      wrap.parentNode.insertBefore(bar, wrap);
    });
  }
  document.addEventListener('click', function(){ document.querySelectorAll('.tbl-colmenu.open').forEach(function(m){m.classList.remove('open');}); });
  document.addEventListener('DOMContentLoaded', function(){ enhance(document); });
  window.enhanceTables=enhance;
  window.adminEnhanceTables = enhance;
})();
</script>

<script>
/* Đóng khung tiêu đề mỗi trang admin (giống "Quản lý liên hệ"); bỏ qua trang đã dùng .sec-head */
(function(){
  function frameAdminHead(){
    var main=document.querySelector(".dash-main"); if(!main) return;
    var h1=main.querySelector("h1"); if(!h1) return;
    if(h1.closest(".sec-head")) return;
    var dh=h1.closest(".dash-head");
    if(dh){ dh.classList.add("dash-head--framed"); return; }
    // không có .dash-head: nếu tiêu đề nằm trong 1 hàng flex (con trực tiếp .dash-main) chứa cả nút thao tác -> đóng khung cả hàng
    var p=h1.parentElement;
    if(p && p!==main && p.parentElement===main && getComputedStyle(p).display==="flex"){ p.classList.add("dash-head--framed"); }
    else { h1.classList.add("h1--framed"); }
  }
  if(document.readyState==="loading") document.addEventListener("DOMContentLoaded", frameAdminHead); else frameAdminHead();
})();
</script>

<script>
/* Biến <select.js-cdd> thành dropdown tùy biến (mở xuống + cuộn), vẫn giữ value + bắn change */
(function(){
  function enh(sel){
    if(sel.dataset.csEnh) return; sel.dataset.csEnh="1";
    var wrap=document.createElement("div"); wrap.className="cs-sel";
    wrap.style.width=sel.style.width||"100%"; if(sel.style.minWidth) wrap.style.minWidth=sel.style.minWidth;
    var trg=document.createElement("button"); trg.type="button"; trg.className="cs-sel-trg"; if(sel.style.height) trg.style.height=sel.style.height;
    var lbl=document.createElement("span"); lbl.className="cs-sel-lbl"; lbl.textContent=sel.options[sel.selectedIndex]?sel.options[sel.selectedIndex].textContent:"";
    trg.appendChild(lbl);
    trg.insertAdjacentHTML("beforeend",'<svg class="cs-sel-arrow" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>');
    var panel=document.createElement("div"); panel.className="cs-sel-panel"; panel.addEventListener("click", function(e){ e.stopPropagation(); });
    Array.prototype.forEach.call(sel.options, function(o,i){
      var opt=document.createElement("div"); opt.className="cs-sel-opt"+(i===sel.selectedIndex?" sel":""); opt.textContent=o.textContent;
      opt.addEventListener("click", function(){
        sel.selectedIndex=i; lbl.textContent=o.textContent;
        panel.querySelectorAll(".cs-sel-opt").forEach(function(x){x.classList.remove("sel");}); opt.classList.add("sel");
        wrap.classList.remove("open");
        sel.dispatchEvent(new Event("change",{bubbles:true}));
      });
      panel.appendChild(opt);
    });
    trg.addEventListener("click", function(e){ e.stopPropagation(); var o=wrap.classList.contains("open"); document.querySelectorAll(".cs-sel.open").forEach(function(x){x.classList.remove("open");}); if(!o) wrap.classList.add("open"); });
    sel.parentNode.insertBefore(wrap, sel); sel.style.display="none"; wrap.appendChild(trg); wrap.appendChild(panel); wrap.appendChild(sel);
    sel.addEventListener("change", function(){ if(sel.options[sel.selectedIndex]) lbl.textContent=sel.options[sel.selectedIndex].textContent; });
  }
  function run(root){ (root||document).querySelectorAll("select.js-cdd").forEach(enh); }
  window.enhanceSelects=run;
  document.addEventListener("DOMContentLoaded", function(){ run(document); });
  document.addEventListener("click", function(){ document.querySelectorAll(".cs-sel.open").forEach(function(x){x.classList.remove("open");}); });
  window.enhanceCddSelects=run;
})();
</script>
<script>
/* Lich chon ngay tuy bien thay input type=date (navy, dd/mm/yyyy) */
(function(){
  var WK=['T2','T3','T4','T5','T6','T7','CN'];
  function pad(n){ return (n<10?'0':'')+n; }
  function fmtVN(o){ return pad(o.d)+'/'+pad(o.m+1)+'/'+o.y; }
  function fmtISO(o){ return o.y+'-'+pad(o.m+1)+'-'+pad(o.d); }
  function parseISO(v){ if(!v) return null; var p=String(v).split('-'); if(p.length!==3) return null; var y=+p[0],m=+p[1],d=+p[2]; if(!y||!m||!d) return null; return {y:y,m:m-1,d:d}; }
  function enh(inp){
    if(inp.dataset.csEnh) return; inp.dataset.csEnh='1';
    var wrap=document.createElement('div'); wrap.className='cs-date';
    wrap.style.width = inp.style.width || '100%';
    var trg=document.createElement('button'); trg.type='button'; trg.className='cs-date-trg';
    var h=inp.style.height; if(h) trg.style.height=h;
    var canClear=!inp.hasAttribute('required');
    trg.innerHTML='<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg><span class="cs-date-lbl"></span>';
    var lbl=trg.querySelector('.cs-date-lbl');
    var panel=document.createElement('div'); panel.className='cs-date-panel';
    var minS=inp.getAttribute('min')||null, maxS=inp.getAttribute('max')||null;
    var sel=parseISO(inp.value);
    var today=new Date(), ty=today.getFullYear(), tm=today.getMonth(), td=today.getDate();
    var view = sel ? {y:sel.y,m:sel.m} : {y:ty,m:tm};
    function setLabel(){ if(sel){ lbl.textContent=fmtVN(sel); lbl.classList.remove('ph'); } else { lbl.textContent='dd/mm/yyyy'; lbl.classList.add('ph'); } }
    function render(){
      var first=new Date(view.y, view.m, 1);
      var offset=(first.getDay()+6)%7;
      var dim=new Date(view.y, view.m+1, 0).getDate();
      var html='<div class="cs-date-hd">';
      html+='<button type="button" class="cs-date-nav" data-nav="-1" aria-label="Tháng trước"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"></path></svg></button>';
      html+='<span class="cs-date-title">Tháng '+(view.m+1)+', '+view.y+'</span>';
      html+='<button type="button" class="cs-date-nav" data-nav="1" aria-label="Tháng sau"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"></path></svg></button>';
      html+='</div><div class="cs-date-wk">';
      for(var w=0; w<7; w++) html+='<span>'+WK[w]+'</span>';
      html+='</div><div class="cs-date-grid">';
      for(var i=0;i<offset;i++) html+='<div class="cs-date-cell empty"></div>';
      for(var d=1; d<=dim; d++){
        var o={y:view.y,m:view.m,d:d}; var iso=fmtISO(o); var cls='cs-date-cell';
        if(sel && sel.y===o.y && sel.m===o.m && sel.d===d) cls+=' sel';
        if(o.y===ty && o.m===tm && d===td) cls+=' today';
        var dis=(minS && iso<minS)||(maxS && iso>maxS);
        if(dis) cls+=' disabled';
        html+='<div class="'+cls+'" '+(dis?'':'data-d="'+d+'"')+'>'+d+'</div>';
      }
      html+='</div><div class="cs-date-ft">'+(canClear?'<button type="button" class="cs-date-clear">Xóa</button>':'<span></span>')+'<button type="button" class="cs-date-today">Hôm nay</button></div>';
      panel.innerHTML=html;
    }
    function commit(){ inp.value = sel?fmtISO(sel):''; setLabel(); inp.dispatchEvent(new Event('change',{bubbles:true})); }
    function place(){ panel.style.left='0'; panel.style.right='auto'; panel.style.top='calc(100% + 6px)'; panel.style.bottom='auto'; var r=wrap.getBoundingClientRect(); var box=wrap.closest('.modal-box'); var rb=box?box.getBoundingClientRect().right-8:window.innerWidth-8; if(r.left+272>rb){ panel.style.left='auto'; panel.style.right='0'; } var ph=panel.offsetHeight||330; if(r.bottom+6+ph>window.innerHeight-8 && r.top-6-ph>8){ panel.style.top='auto'; panel.style.bottom='calc(100% + 6px)'; } }
    panel.addEventListener('click', function(e){
      e.stopPropagation();
      var nav=e.target.closest('[data-nav]');
      if(nav){ view.m+=(+nav.getAttribute('data-nav')); if(view.m<0){view.m=11;view.y--;} else if(view.m>11){view.m=0;view.y++;} render(); return; }
      var cell=e.target.closest('[data-d]');
      if(cell){ sel={y:view.y,m:view.m,d:+cell.getAttribute('data-d')}; commit(); render(); wrap.classList.remove('open'); return; }
      if(e.target.closest('.cs-date-clear')){ sel=null; commit(); render(); wrap.classList.remove('open'); return; }
      if(e.target.closest('.cs-date-today')){ sel={y:ty,m:tm,d:td}; view={y:ty,m:tm}; commit(); render(); wrap.classList.remove('open'); return; }
    });
    trg.addEventListener('click', function(e){
      e.stopPropagation();
      var isOpen=wrap.classList.contains('open');
      document.querySelectorAll('.cs-date.open,.cs-sel.open').forEach(function(x){x.classList.remove('open');});
      if(!isOpen){ minS=inp.getAttribute('min')||null; maxS=inp.getAttribute('max')||null; sel=parseISO(inp.value); view = sel?{y:sel.y,m:sel.m}:{y:ty,m:tm}; render(); wrap.classList.add('open'); place(); }
    });
    inp.parentNode.insertBefore(wrap, inp); inp.style.display='none';
    wrap.appendChild(trg); wrap.appendChild(panel); wrap.appendChild(inp);
    setLabel(); render();
    inp.addEventListener('change', function(){ sel=parseISO(inp.value); setLabel(); });
  }
  function run(root){ (root||document).querySelectorAll('input.js-date').forEach(enh); }
  document.addEventListener('DOMContentLoaded', function(){ run(document); });
  document.addEventListener('click', function(){ document.querySelectorAll('.cs-date.open').forEach(function(x){x.classList.remove('open');}); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') document.querySelectorAll('.cs-date.open').forEach(function(x){x.classList.remove('open');}); });
  window.enhanceDateInputs=run;
})();
</script>
<script>
/* Nút chọn ảnh tùy biến cho input.js-filepick (label tiếng Việt + tên tệp đã chọn) */
(function(){
  function enh(inp){
    if(inp.dataset.cfEnh) return; inp.dataset.cfEnh='1';
    var label = inp.getAttribute('data-file-label') || 'Chọn ảnh';
    var wrap = document.createElement('div'); wrap.className='cs-file';
    var btn = document.createElement('button'); btn.type='button'; btn.className='cs-file-btn';
    btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="M21 15l-5-5L5 21"></path></svg><span>'+label+'</span>';
    var nm = document.createElement('span'); nm.className='cs-file-name empty'; nm.textContent='Chưa chọn tệp';
    inp.parentNode.insertBefore(wrap, inp); inp.style.display='none';
    wrap.appendChild(btn); wrap.appendChild(nm); wrap.appendChild(inp);
    btn.addEventListener('click', function(){ inp.click(); });
    inp.addEventListener('change', function(){
      if(inp.files && inp.files.length){ nm.textContent = inp.files.length>1 ? (inp.files.length+' tệp đã chọn') : inp.files[0].name; nm.classList.remove('empty'); }
      else { nm.textContent='Chưa chọn tệp'; nm.classList.add('empty'); }
    });
  }
  function run(root){ (root||document).querySelectorAll('input.js-filepick').forEach(enh); }
  document.addEventListener('DOMContentLoaded', function(){ run(document); });
  window.enhanceFilePick=run;
})();
</script>
<script>
/* ===== PJAX v3: tải nội dung admin (link + form GET/POST + form.submit() + onclick nav) ===== */
(function(){
  var EXCLUDE=/\/(products\/new|products\/\d+\/edit|post-on-behalf|news\/new|news\/\d+\/edit|login|logout)(\/|\?|#|$)/;
  function pathOf(u){ try{ return (new URL(u, location.origin)).pathname.replace(/\/+$/,'')||'/'; }catch(e){ return ''; } }
  function isAdmin(u){ var p=pathOf(u); return p==='/admin'||p.indexOf('/admin/')===0||p==='/staff'||p.indexOf('/staff/')===0; }
  function pjaxable(u){ return isAdmin(u) && !EXCLUDE.test(pathOf(u)+'/'); }
  function getMain(){ return document.querySelector('.dash-main'); }
  function reinit(root){ ['enhanceTables','enhanceSelects','enhanceDateInputs','enhanceFilePick'].forEach(function(n){ if(typeof window[n]==='function'){ try{ window[n](root); }catch(e){} } }); }
  /* theo dõi & dọn timer của trang được swap (vd poll chat) để không rò rỉ khi rời trang */
  var _setInterval=window.setInterval, _pageTimers=[];
  window.setInterval=function(){ var id=_setInterval.apply(window, arguments); _pageTimers.push(id); return id; };
  function clearPageTimers(){ for(var i=0;i<_pageTimers.length;i++){ try{ clearInterval(_pageTimers[i]); }catch(e){} } _pageTimers=[]; }
  function runScripts(root, done){
    var ss=Array.prototype.slice.call(root.querySelectorAll('script'));
    (function nx(i){
      if(i>=ss.length){ if(done) done(); return; }
      var o=ss[i];
      if(o.src){
        var s=document.createElement('script');
        for(var j=0;j<o.attributes.length;j++){ var a=o.attributes[j]; s.setAttribute(a.name,a.value); }
        s.onload=s.onerror=function(){ nx(i+1); }; o.parentNode.replaceChild(s,o);
      } else {
        /* chạy ở phạm vi TOÀN CỤC (indirect eval) -> function/var top-level vẫn global,
           handler inline onclick/onsubmit gọi được sau khi swap. Override DOMContentLoaded để init chạy ngay. */
        var _a=document.addEventListener;
        document.addEventListener=function(t,f,op){ if(t==="DOMContentLoaded"){ try{f()}catch(e){if(window.console)console.error(e)} } else { return _a.call(document,t,f,op); } };
        try{ (0,eval)(o.textContent); }catch(e){ if(window.console)console.error(e); }
        document.addEventListener=_a;
        nx(i+1);
      }
    })(0);
  }
  function setActive(p){ document.querySelectorAll('.dash-nav a').forEach(function(a){ var ap=pathOf(a.getAttribute('href')||''); a.classList.toggle('active', !!ap && (p===ap || (ap!=='/admin' && p.indexOf(ap+'/')===0))); }); }
  function showFlash(doc){ var fs=doc.querySelector('.flash-stack'); if(!fs||typeof window.coolToastShow!=='function') return; var ms=fs.querySelectorAll('.flash'); for(var i=0;i<ms.length;i++){ var bad=/error|warning/.test(ms[i].className); window.coolToastShow(ms[i].textContent.trim(), bad?'⚠️':'✅'); } }
  var busy=false;
  function swap(html, url, mode, doc){
    doc=doc||new DOMParser().parseFromString(html,'text/html');
    var nm=doc.querySelector('.dash-main'), m=getMain();
    if(!nm||!m){ location.href=url; return; }
    if(window.tinymce){ try{ window.tinymce.remove(); }catch(e){} }   /* dọn editor cũ trước khi thay nội dung */
    clearPageTimers();                                                 /* dọn timer trang cũ (poll chat...) */
    m.innerHTML=nm.innerHTML;
    var t=doc.querySelector('title'); if(t) document.title=t.textContent;
    runScripts(m, function(){ reinit(m); });
    if(mode==='push') history.pushState({pjax:1},'',url); else history.replaceState({pjax:1},'',url);
    setActive(pathOf(url)); m.style.opacity=''; m.style.pointerEvents=''; busy=false; window.scrollTo(0,0);
  }
  function load(url, mode){
    var m=getMain(); if(!m){ location.href=url; return; }
    busy=true; m.style.opacity='0.45'; m.style.pointerEvents='none';
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin'})
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){ swap(html, url, mode||'push'); })
      .catch(function(){ location.href=url; });
  }
  /* xử lý 1 form qua pjax — trả về true nếu đã xử lý */
  var _natSubmit = HTMLFormElement.prototype.submit;
  function pjaxSubmit(f){
    if(!f || f.tagName!=='FORM') return false;
    /* multipart (upload file) VẪN cho AJAX qua FormData — chỉ bỏ qua form có trình soạn thảo (dưới) */
    if(f.querySelector('.tox, .ql-container, textarea[id^="tinymce"]')) return false;     /* form có editor -> tránh mất nội dung */
    var action=f.getAttribute('action')||location.pathname; if(!isAdmin(action)) return false;
    if(/\/(login|logout)(\/|$)/.test(pathOf(action))) return false;
    var m=getMain(); if(!m||busy) return false;
    var method=(f.getAttribute('method')||'get').toLowerCase();
    if(method==='get'){
      var qs=new URLSearchParams(new FormData(f)).toString();
      var url=action+(qs?('?'+qs):'');
      if(!pjaxable(url)) return false;
      load(url,'push'); return true;
    }
    busy=true; m.style.opacity='0.45'; m.style.pointerEvents='none';
    fetch(action, {method:'POST', body:new FormData(f), headers:{'X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin', redirect:'follow'})
      .then(function(r){ return r.text().then(function(h){ return {u:r.url||action, h:h}; }); })
      .then(function(res){ var doc=new DOMParser().parseFromString(res.h,'text/html'); if(!doc.querySelector('.dash-main')){ location.href=res.u; return; } swap(res.h, res.u, 'replace', doc); showFlash(doc); })  /* swap trước rồi showFlash (toast không bị xoá) */
      .catch(function(){ busy=false; _natSubmit.call(f); });
    return true;
  }
  /* override form.submit() để bắt cả onchange="this.form.submit()" */
  HTMLFormElement.prototype.submit = function(){ try{ if(pjaxSubmit(this)) return; }catch(e){} return _natSubmit.call(this); };
  /* helper điều hướng pjax cho code gọi tay (vd onclick location.href) */
  window.csNav = function(u){ if(pjaxable(u) && getMain()){ load(u,'push'); } else { location.href=u; } };
  /* click link */
  document.addEventListener('click', function(e){
    if(e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey) return;
    var a=e.target.closest('a'); if(!a) return;
    var href=a.getAttribute('href'); if(!href||href.charAt(0)==='#'||href.lastIndexOf('javascript:',0)===0) return;
    if((a.target&&a.target!=='_self')||a.hasAttribute('download')||a.getAttribute('onclick')) return;
    if(a.origin&&a.origin!==location.origin) return;
    if(!pjaxable(href)||busy) return;
    e.preventDefault(); load(a.href, 'push');
  });
  /* submit event (nút submit / requestSubmit) */
  document.addEventListener('submit', function(e){
    if(e.defaultPrevented) return;
    if(pjaxSubmit(e.target)) e.preventDefault();
  });
  window.addEventListener('popstate', function(){ if(pjaxable(location.href)) load(location.href,'replace'); });
})();
</script>

<script>
// --- TỰ ĐỘNG ĐĂNG XUẤT KHI TREO MÁY 1 GIỜ (IDLE TIMEOUT) ---
(function() {
  var idleTime = 0;
  var idleInterval = setInterval(timerIncrement, 60000); // 60000 ms = 1 phút

  var events = ['mousemove', 'keypress', 'scroll', 'click', 'touchstart'];
  events.forEach(function(evt) {
    window.addEventListener(evt, resetTimer, { passive: true });
  });

  function resetTimer() {
    idleTime = 0;
  }

  function timerIncrement() {
    idleTime++;
    if (idleTime >= 60) { // 60 phút = 1 giờ
      clearInterval(idleInterval);
      
      var logoutUrl = '/auth/logout';
      var path = window.location.pathname;
      if (path.indexOf('/admin') === 0) {
        logoutUrl = '/admin/logout';
      } else if (path.indexOf('/staff') === 0) {
        logoutUrl = '/staff/logout';
      } else if (path.indexOf('/partner') === 0) {
        logoutUrl = '/partner/logout';
      } else if (path.indexOf('/superadmin-k9x27c') === 0) {
        logoutUrl = '/superadmin-k9x27c/logout';
      }

      window.location.href = logoutUrl;
    }
  }
})();
</script>
<script>
/* 3-Second Auto-dismiss Floating Toast Engine for Admin */
window.coolToastShow = function(msg, icon) {
  var stack = document.querySelector('.flash-stack');
  if (!stack) {
    stack = document.createElement('div');
    stack.className = 'flash-stack';
    stack.style.cssText = 'position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;max-width:360px';
    document.body.appendChild(stack);
  }
  var toast = document.createElement('div');
  var isErr = /❌|⚠|error|Lỗi/i.test((icon||'') + (msg||''));
  toast.className = 'flash ' + (isErr ? 'error' : 'success');
  toast.style.cssText = 'padding:14px 20px;background:' + (isErr ? '#fef2f2' : '#f0fdf4') + ';color:' + (isErr ? '#991b1b' : '#166534') + ';border-left:4px solid ' + (isErr ? '#ef4444' : '#22c55e') + ';border-radius:8px;font-size:13.5px;font-weight:600;box-shadow:0 10px 30px rgba(0,0,0,0.15);animation:slideIn 0.3s ease;display:flex;align-items:center;gap:10px';
  toast.innerHTML = '<span>' + (icon || (isErr ? '❌' : '✅')) + '</span> <span>' + msg + '</span>';
  stack.appendChild(toast);
  setTimeout(function() {
    toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(function() { toast.remove(); if (!stack.querySelector('.flash')) stack.remove(); }, 400);
  }, 3000);
};

document.addEventListener("DOMContentLoaded", function() {
  var stack = document.querySelector('.flash-stack');
  if (stack) {
    var flashes = stack.querySelectorAll('.flash');
    flashes.forEach(function(el) {
      setTimeout(function() {
        el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(100%)';
        setTimeout(function() { el.remove(); if (!stack.querySelector('.flash')) stack.remove(); }, 400);
      }, 3000);
    });
  }
});
</script>
<?php require __DIR__ . '/confirm-modal.php'; ?>
<?php require __DIR__ . '/col-picker.php'; ?>
</body>
</html>
