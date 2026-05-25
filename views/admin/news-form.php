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
