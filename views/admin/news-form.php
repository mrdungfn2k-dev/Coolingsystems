<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>
<script src="/tinymce/tinymce.min.js"></script>
<style>
.tox-tinymce{border:1px solid var(--line)!important;border-radius:6px!important}
.tox .tox-toolbar{background:#fafafa!important}
.tox-notifications-container{display:none!important}
.tox-promotion{display:none!important}
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
          <textarea id="tinymceNews" name="content"><?= htmlspecialchars($article['content']??'') ?></textarea>
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
            <input type="file" name="thumbnail" accept="image/*" class="js-filepick" data-file-label="Chọn ảnh đại diện">
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
tinymce.init({
  selector: '#tinymceNews',
  height: 400,
  language: 'vi',
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | removeformat',
    'alignleft aligncenter alignright alignjustify | bullist numlist checklist | outdent indent | table | link image | code fullscreen'
  ],
  font_family_formats: 'Mặc định=; Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,serif; Verdana=verdana,geneva,sans-serif; Tahoma=tahoma,arial,sans-serif; Georgia=georgia,serif; Courier New=courier new,monospace',
  font_size_formats: '8px 9px 10px 11px 12px 13px 14px 16px 18px 20px 22px 24px 28px 32px 36px 48px 60px 72px',
  table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
  table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
  table_default_attributes: { 'border': '1' },
  table_class_list: [
    { title: 'Mặc định', value: '' },
    { title: 'Bảng sọc', value: 'table-striped' }
  ],
  content_style: `
    body { font-family: Inter, Arial, sans-serif; font-size: 15px; line-height: 1.9; color: #333; padding: 16px; }
    table { border-collapse: collapse; width: 100%; margin: 12px 0; }
    th { background: #0b1d3a; color: #fff; font-weight: 700; padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
    tr:nth-child(even) td { background: #f8f9fc; }
    h1 { font-size: 28px; color: #0b1d3a; font-weight: 900; }
    h2 { font-size: 22px; color: #0b1d3a; font-weight: 800; }
    h3 { font-size: 18px; color: #0b1d3a; font-weight: 700; }
    img { max-width: 100%; height: auto; border-radius: 8px; }
    blockquote { border-left: 3px solid #c9a14a; padding: 12px 16px; background: #faf8f3; border-radius: 0 8px 8px 0; }
  `,
  menubar: 'file edit view insert format table',
  promotion: false,
  branding: false,
  license_key: 'gpl',
  setup: function(editor) {
    editor.on('change', function() { editor.save(); });
  }
});




// TinyMCE auto-syncs on form submit
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
