<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
/* Quill font size dropdown labels */
.ql-snow .ql-picker.ql-size .ql-picker-label::before,
.ql-snow .ql-picker.ql-size .ql-picker-item::before { content: attr(data-value) !important; }
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value=""]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value=""]::before { content: 'Mặc định' !important; }
.ql-snow .ql-picker.ql-font .ql-picker-label::before,
.ql-snow .ql-picker.ql-font .ql-picker-item::before { content: attr(data-value) !important; text-transform: capitalize; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value=""]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value=""]::before { content: 'Mặc định' !important; }
.ql-toolbar{border-radius:6px 6px 0 0!important;border:1px solid var(--line)!important;background:#fafafa}
.ql-container{border:1px solid var(--line)!important;border-top:none!important;border-radius:0 0 6px 6px!important}
.ql-editor{min-height:420px;font-size:15px;font-family:'Inter',sans-serif;line-height:1.9}
.ql-editor h1{font-size:28px;font-weight:800;color:#1a3258;border-bottom:2px solid #e8edf5;padding-bottom:6px;margin:16px 0 10px}
.ql-editor h2{font-size:22px;font-weight:700;color:#1a3258;margin:14px 0 8px}
.ql-editor h3{font-size:18px;font-weight:700;color:#2c4a7c;margin:12px 0 6px}
.news-layout{display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start}
@media(max-width:900px){.news-layout{grid-template-columns:1fr}}
</style>
<div class="dash-head">
  <h1><?= isset($article['id']) ? ' Sửa bài viết' : ' Viết bài mới' ?></h1>
  <a href="/admin/news" class="btn btn-outline-navy btn-sm">← Danh sách</a>
</div>
<form method="post" action="<?= isset($article['id']) ? '/admin/news/'.$article['id'].'/edit' : '/admin/news/new' ?>" enctype="multipart/form-data" id="newsForm">
  <?= csrfField() ?>
  <input type="hidden" name="content" id="contentHidden">
  <div class="news-layout">
    <div>
      <div class="panel">
        <div class="panel-body" style="padding:16px">
          <div class="form-group">
            <label>Tiêu đề bài viết <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($article['title']??'') ?>" required placeholder="Nhập tiêu đề hấp dẫn..." style="font-size:17px;font-weight:600">
          </div>
          <div class="form-group">
            <label>Tóm tắt (hiển thị trong danh sách)</label>
            <textarea name="excerpt" rows="2" placeholder="Mô tả ngắn về bài viết..."><?= e($article['excerpt']??'') ?></textarea>
          </div>
        </div>
      </div>
      <div class="panel" style="margin-top:16px">
        <div class="panel-head"><h3>Nội dung bài viết</h3></div>
        <div class="panel-body" style="padding:0">
          <div id="quillEditor"><?= $article['content']??'' ?></div>
        </div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px">
      <div class="panel">
        <div class="panel-head"><h3> Xuất bản</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
              <option value="draft" <?= ($article['status']??'')==='draft'?'selected':'' ?>>Bản nháp</option>
              <option value="published" <?= ($article['status']??'')==='published'?'selected':'' ?>>Đăng ngay</option>
            </select>
          </div>
          <div class="form-group">
            <label>Ảnh đại diện</label>
            <?php if (!empty($article['thumbnail'])): ?>
              <img src="/uploads/news/<?= e($article['thumbnail']) ?>" style="width:100%;border-radius:4px;margin-bottom:6px">
            <?php endif; ?>
            <input type="file" name="thumbnail" accept="image/*">
          </div>
          <button type="submit" class="btn btn-gold btn-block">Lưu bài viết</button>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><h3>Slug URL</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Đường dẫn thân thiện</label>
            <input type="text" name="slug" value="<?= e($article['slug']??'') ?>" placeholder="ten-bai-viet" pattern="[a-z0-9\-]+">
            <span class="fs-11 text-muted">Tự động tạo từ tiêu đề nếu để trống</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
<script>
var toolbarFull = [
  [{ 'font': ['', 'serif', 'monospace', 'sans-serif', 'arial', 'times', 'verdana', 'tahoma', 'georgia', 'impact', 'courier'] }],
  [{ 'size': ['8px','9px','10px','11px','12px','13px','14px','16px','18px','20px','22px','24px','26px','28px','32px','36px','48px','60px','72px'] }],
  [{ 'header': [1,2,3,4,5,6,false] }],
  ['bold','italic','underline','strike'],
  [{ 'color': [] }, { 'background': [] }],
  ['blockquote','code-block'],
  [{ 'script': 'sub' }, { 'script': 'super' }],
  [{ 'list':'ordered' }, { 'list':'bullet' }, { 'list':'check' }],
  [{ 'indent':'-1' }, { 'indent':'+1' }],
  [{ 'align': ['','center','right','justify'] }],
  ['link','image','video'],
  ['clean']
];
var SizeStyle = Quill.import('attributors/style/size');
SizeStyle.whitelist = ['8px','9px','10px','11px','12px','13px','14px','16px','18px','20px','22px','24px','26px','28px','32px','36px','48px','60px','72px'];
Quill.register(SizeStyle, true);
var FontStyle = Quill.import('attributors/style/font');
FontStyle.whitelist = ['', 'serif', 'monospace', 'sans-serif', 'arial', 'times', 'verdana', 'tahoma', 'georgia', 'impact', 'courier'];
Quill.register(FontStyle, true);

var quill = new Quill('#quillEditor',{theme:'snow',placeholder:'Nhập nội dung bài viết...',modules:{toolbar:{container:toolbarFull}}});


// === Word-like Table Grid Picker ===
function addTableHandler(quillInstance) {
  var toolbarEl = quillInstance.container.previousSibling;
  if (!toolbarEl || !toolbarEl.classList.contains('ql-toolbar')) return;

  var wrap = document.createElement('span');
  wrap.className = 'ql-formats';
  wrap.style.position = 'relative';

  var tableBtn = document.createElement('button');
  tableBtn.type = 'button';
  tableBtn.className = 'ql-table-insert';
  tableBtn.title = 'Chèn bảng';
  tableBtn.innerHTML = '<svg viewBox="0 0 18 18" width="18" height="18"><rect x="1" y="1" width="16" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="1" y1="7" x2="17" y2="7" stroke="currentColor" stroke-width="1"/><line x1="1" y1="12" x2="17" y2="12" stroke="currentColor" stroke-width="1"/><line x1="7" y1="1" x2="7" y2="17" stroke="currentColor" stroke-width="1"/><line x1="12" y1="1" x2="12" y2="17" stroke="currentColor" stroke-width="1"/></svg>';
  tableBtn.style.cssText = 'cursor:pointer;padding:3px 5px;';

  // Grid picker popup
  var popup = document.createElement('div');
  popup.className = 'table-grid-popup';
  popup.style.cssText = 'display:none;position:absolute;top:100%;left:0;z-index:9999;background:#fff;border:1px solid #ddd;border-radius:8px;padding:12px;box-shadow:0 8px 30px rgba(0,0,0,0.15);min-width:200px;';
  
  var label = document.createElement('div');
  label.style.cssText = 'text-align:center;font-size:12px;font-weight:700;color:#0b1d3a;margin-bottom:8px;';
  label.textContent = 'Chọn kích thước bảng';
  popup.appendChild(label);

  var ROWS = 8, COLS = 8;
  var grid = document.createElement('div');
  grid.style.cssText = 'display:grid;grid-template-columns:repeat(' + COLS + ',24px);gap:2px;justify-content:center;';
  
  var cells = [];
  var selR = 0, selC = 0;

  for (var r = 0; r < ROWS; r++) {
    for (var c = 0; c < COLS; c++) {
      var cell = document.createElement('div');
      cell.style.cssText = 'width:24px;height:24px;border:1px solid #ddd;border-radius:3px;cursor:pointer;transition:all 0.1s;';
      cell.dataset.r = r + 1;
      cell.dataset.c = c + 1;
      cells.push(cell);
      grid.appendChild(cell);
    }
  }
  popup.appendChild(grid);

  var info = document.createElement('div');
  info.style.cssText = 'text-align:center;font-size:11px;color:#888;margin-top:6px;';
  info.textContent = '0 × 0';
  popup.appendChild(info);

  // Hover highlight
  grid.addEventListener('mouseover', function(e) {
    var t = e.target;
    if (!t.dataset.r) return;
    selR = parseInt(t.dataset.r);
    selC = parseInt(t.dataset.c);
    info.textContent = selR + ' × ' + selC;
    cells.forEach(function(cl) {
      var cr = parseInt(cl.dataset.r), cc = parseInt(cl.dataset.c);
      if (cr <= selR && cc <= selC) {
        cl.style.background = '#0b1d3a';
        cl.style.borderColor = '#0b1d3a';
      } else {
        cl.style.background = '#f8f9fc';
        cl.style.borderColor = '#ddd';
      }
    });
  });

  // Click to insert
  grid.addEventListener('click', function(e) {
    var t = e.target;
    if (!t.dataset.r) return;
    var rows = parseInt(t.dataset.r), cols = parseInt(t.dataset.c);
    
    var html = '<table style="width:100%;border-collapse:collapse;margin:16px 0"><thead><tr>';
    for (var c = 0; c < cols; c++) {
      html += '<th style="border:1px solid #ddd;padding:10px 14px;background:#0b1d3a;color:#fff;font-weight:700;text-align:left">Cột ' + (c+1) + '</th>';
    }
    html += '</tr></thead><tbody>';
    for (var r = 1; r < rows; r++) {
      html += '<tr>';
      for (var c = 0; c < cols; c++) {
        html += '<td style="border:1px solid #ddd;padding:10px 14px;text-align:left">&nbsp;</td>';
      }
      html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    
    var range = quillInstance.getSelection(true);
    quillInstance.clipboard.dangerouslyPasteHTML(range ? range.index : quillInstance.getLength(), html);
    popup.style.display = 'none';
  });

  // Toggle popup
  tableBtn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
    // Reset grid
    cells.forEach(function(cl) { cl.style.background = '#f8f9fc'; cl.style.borderColor = '#ddd'; });
    info.textContent = '0 × 0';
  });

  // Close on outside click
  document.addEventListener('click', function(e) {
    if (!wrap.contains(e.target)) popup.style.display = 'none';
  });

  wrap.appendChild(tableBtn);
  wrap.appendChild(popup);
  toolbarEl.appendChild(wrap);
}

addTableHandler(quill);




document.getElementById('newsForm').addEventListener('submit',function(){
  document.getElementById('contentHidden').value = quill.root.innerHTML;
});
// Auto-generate slug from title
document.querySelector('[name=title]').addEventListener('input',function(){
  var sl=document.querySelector('[name=slug]');
  if(!sl.dataset.manual){
    sl.value=this.value.toLowerCase().replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g,'a').replace(/[èéẹẻẽêềếệểễ]/g,'e').replace(/[ìíịỉĩ]/g,'i').replace(/[òóọỏõôồốộổỗơờớợởỡ]/g,'o').replace(/[ùúụủũưừứựửữ]/g,'u').replace(/[ỳýỵỷỹ]/g,'y').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
  }
});
document.querySelector('[name=slug]').addEventListener('input',function(){ this.dataset.manual='1'; });
</script>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
