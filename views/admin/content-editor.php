<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<script src="/tinymce/tinymce.min.js"></script>
<style>
.tox-tinymce { border: 1px solid var(--line) !important; border-radius: 6px !important; }
.tox .tox-toolbar { background: #fafafa !important; }
.tox .tox-statusbar { border-top: 1px solid var(--line) !important; }
.tox-notifications-container { display: none !important; }
.tox-promotion { display: none !important; }
</style>
<style>
.editor-layout{display:grid;grid-template-columns:1fr 280px;gap:24px;align-items:start}
@media(max-width:900px){.editor-layout{grid-template-columns:1fr}}
.sidebar-box{display:flex;flex-direction:column;gap:16px;position:sticky;top:80px}
</style>

<div class="dash-head">
  <h1>Chỉnh sửa: <?= e($page['title']) ?></h1>
  <a href="/admin/content" class="btn btn-outline-navy btn-sm">← Quay lại</a>
</div>

<form method="post" action="/admin/content/<?= e($page['slug']) ?>" id="editorForm" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="editor-layout">
    <div>
      <div class="panel">
        <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px">
          <div style="flex:1">
            <label style="font-size:11px;font-weight:700;color:var(--navy);margin-bottom:4px;display:block;text-transform:uppercase">Tiêu đề trang</label>
            <input type="text" name="title" value="<?= e($page['title'] ?? '') ?>" class="form-control" placeholder="Nhập tiêu đề trang..." required style="font-weight:700;font-size:15px;padding:6px 12px">
          </div>
          <div style="text-align:right">
            <label style="font-size:11px;font-weight:700;color:#888;margin-bottom:4px;display:block">MÃ SLUG</label>
            <code style="font-size:12px;background:#f0f0f0;padding:4px 8px;border-radius:4px"><?= e($page['slug']) ?></code>
          </div>
        </div>
        <div class="panel-body" style="padding:0">
          <textarea id="tinymceContent" name="content"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
    <div class="sidebar-box">
      <div class="panel">
        <div class="panel-head"><h3>🖼 Chèn ảnh</h3></div>
        <div class="panel-body">
          <input type="file" id="imgUpload" accept="image/*" class="form-control" style="font-size:12px">
          <small style="color:#888;font-size:11px">Upload ảnh rồi chèn vào nội dung</small>
        </div>
      </div>
      <button type="submit" class="btn btn-navy" style="width:100%;padding:14px;font-size:15px;font-weight:700">💾 Lưu nội dung</button>
    </div>
  </div>
</form>

<script>
tinymce.init({
  selector: '#tinymceContent',
  height: 550,
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

// Image upload
document.getElementById('imgUpload').addEventListener('change', function(e) {
  var file = e.target.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(ev) {
    tinymce.activeEditor.insertContent('<img src="' + ev.target.result + '" alt="' + file.name + '" style="max-width:100%">');
  };
  reader.readAsDataURL(file);
});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
