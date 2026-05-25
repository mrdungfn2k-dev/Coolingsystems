<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>
.editor-layout{display:grid;grid-template-columns:1fr 280px;gap:24px;align-items:start}
@media(max-width:900px){.editor-layout{grid-template-columns:1fr}}

/* Quill font size dropdown labels */
.ql-snow .ql-picker.ql-size .ql-picker-label::before,
.ql-snow .ql-picker.ql-size .ql-picker-item::before { content: attr(data-value) !important; }
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value=""]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value=""]::before { content: 'Mặc định' !important; }
/* Quill font family dropdown labels */
.ql-snow .ql-picker.ql-font .ql-picker-label::before,
.ql-snow .ql-picker.ql-font .ql-picker-item::before { content: attr(data-value) !important; text-transform: capitalize; }
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value=""]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value=""]::before { content: 'Mặc định' !important; }

.ql-toolbar{border-radius:6px 6px 0 0 !important;border:1px solid var(--line) !important;background:#fafafa}
.ql-container{border:1px solid var(--line) !important;border-top:none !important;border-radius:0 0 6px 6px !important;min-height:500px;font-size:15px}
.ql-editor{min-height:500px;font-family:'Inter',sans-serif;line-height:1.9}
.ql-editor h1{font-size:32px;font-weight:800;color:#1a3258}
.ql-editor h2{font-size:24px;font-weight:700;color:#1a3258}
.ql-editor h3{font-size:20px;font-weight:700;color:#2c4a7c}
.ql-editor img{max-width:100%;height:auto;border-radius:8px;cursor:pointer}
.ql-editor img.selected{outline:3px solid var(--navy);outline-offset:2px}
.ql-size-small{font-size:0.75em}.ql-size-large{font-size:1.5em}.ql-size-huge{font-size:2.5em}
.sidebar-box{display:flex;flex-direction:column;gap:16px;position:sticky;top:80px}
.img-resize-bar{display:none;position:fixed;background:var(--navy);color:#fff;padding:6px 12px;border-radius:8px;z-index:999;gap:6px;align-items:center;font-size:12px;box-shadow:0 4px 12px rgba(0,0,0,0.2)}
.img-resize-bar.show{display:flex}
.img-resize-bar button{background:rgba(255,255,255,0.2);border:none;color:#fff;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:12px;font-weight:700}
.img-resize-bar button:hover{background:rgba(255,255,255,0.35)}
</style>

<div class="dash-head">
  <h1>Chỉnh sửa: <?= e($page['title']) ?></h1>
  <a href="/admin/content" class="btn btn-outline-navy btn-sm">← Quay lại</a>
</div>

<form method="post" action="/admin/content/<?= e($page['slug']) ?>" id="editorForm" enctype="multipart/form-data">
  <?= csrfField() ?>
  <input type="hidden" name="content" id="contentHidden">
  <div class="editor-layout">
    <div>
      <div class="panel">
        <div class="panel-head">
          <h3>Nội dung trang <code style="font-size:12px;background:#f0f0f0;padding:2px 8px;border-radius:4px"><?= e($page['slug']) ?></code></h3>
        </div>
        <div class="panel-body" style="padding:0">
          <div id="quillEditor"><?= $page['content'] ?></div>
        </div>
      </div>
    </div>
    <div class="sidebar-box">
      <div class="panel">
        <div class="panel-head"><h3>Tùy chọn</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Tiêu đề trang</label>
            <input type="text" name="title" value="<?= e($page['title']) ?>" required>
          </div>
          <div class="form-group">
            <label>Upload ảnh vào nội dung</label>
            <input type="file" id="imgUpload" accept="image/*" style="font-size:12px">
            <small style="color:#888;display:block;margin-top:4px">Chọn ảnh rồi nó sẽ được chèn tại vị trí con trỏ</small>
          </div>
          <button type="submit" class="btn btn-gold btn-block btn-lg">Lưu nội dung</button>
          <div class="mt-2 fs-12 text-muted text-center">
            Cập nhật: <?= $page['updated_at'] ? relTime($page['updated_at']) : 'Chưa lưu' ?>
          </div>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><h3>Hướng dẫn</h3></div>
        <div class="panel-body fs-12" style="color:var(--ink-2);line-height:1.7">
          <div style="margin-bottom:4px"><strong>H1-H6</strong> — Tiêu đề</div>
          <div style="margin-bottom:4px"><strong>Cỡ chữ</strong> — Nhỏ / Bình thường / Lớn / Rất lớn</div>
          <div style="margin-bottom:4px"><strong>B I U</strong> — Đậm, Nghiêng, Gạch chân</div>
          <div style="margin-bottom:4px">Click vào ảnh để thay đổi kích thước</div>
        </div>
      </div>
    </div>
  </div>
</form>

<div class="img-resize-bar" id="imgResizeBar">
  <span>Kích thước ảnh:</span>
  <button onclick="resizeImg(25)">25%</button>
  <button onclick="resizeImg(50)">50%</button>
  <button onclick="resizeImg(75)">75%</button>
  <button onclick="resizeImg(100)">100%</button>
</div>

<script>
// Full toolbar matching product form
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

var quill = new Quill('#quillEditor', {
  theme: 'snow',
  placeholder: 'Nhập nội dung...',
  modules: {
    toolbar: {
      container: toolbarFull
    }
  }
});

// Image upload from sidebar
document.getElementById('imgUpload').addEventListener('change', function(e) {
  var file = e.target.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(ev) {
    var range = quill.getSelection(true);
    quill.insertEmbed(range.index, 'image', ev.target.result);
    quill.setSelection(range.index + 1);
  };
  reader.readAsDataURL(file);
  e.target.value = '';
});

// Image click to resize
var selectedImg = null;
var resizeBar = document.getElementById('imgResizeBar');
document.querySelector('.ql-editor').addEventListener('click', function(e) {
  if (e.target.tagName === 'IMG') {
    if (selectedImg) selectedImg.classList.remove('selected');
    selectedImg = e.target;
    selectedImg.classList.add('selected');
    var rect = selectedImg.getBoundingClientRect();
    resizeBar.style.top = (rect.top - 40) + 'px';
    resizeBar.style.left = rect.left + 'px';
    resizeBar.classList.add('show');
  } else {
    if (selectedImg) selectedImg.classList.remove('selected');
    selectedImg = null;
    resizeBar.classList.remove('show');
  }
});

function resizeImg(pct) {
  if (!selectedImg) return;
  selectedImg.style.width = pct + '%';
  selectedImg.style.height = 'auto';
  resizeBar.classList.remove('show');
  selectedImg.classList.remove('selected');
  selectedImg = null;
}

document.getElementById('editorForm').addEventListener('submit', function(e) {
  document.getElementById('contentHidden').value = quill.root.innerHTML;
});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>