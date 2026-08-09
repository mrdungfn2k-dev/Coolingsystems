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
function setupTinyMCECallout(editor) {
  editor.ui.registry.addMenuButton('calloutbox', {
    text: '🎨 Khung Nền',
    tooltip: 'Tạo / Đổi màu khung background & màu viền',
    fetch: function(callback) {
      var applyBox = function(bgColor, borderColor) {
        var sel = editor.selection;
        var node = sel.getNode();
        var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol');
        
        if (!box || box.nodeName.toLowerCase() === 'body') {
          var content = sel.getContent() || '<p>Nhập nội dung khung thông tin...</p>';
          editor.insertContent('<div style="background-color: ' + bgColor + '; border: 1px solid ' + borderColor + '; border-radius: 12px; padding: 16px; margin: 12px 0;">' + content + '</div>');
        } else {
          editor.dom.setStyle(box, 'background-color', bgColor);
          editor.dom.setStyle(box, 'border', '1px solid ' + borderColor);
          editor.dom.setStyle(box, 'border-radius', '12px');
          editor.dom.setStyle(box, 'padding', '16px');
          editor.dom.setStyle(box, 'margin', '12px 0');
        }
      };

      var items = [
        {
          type: 'menuitem',
          text: '🟢 Khung Xanh lá (Giống mẫu ảnh)',
          onAction: function() { applyBox('#f0fdf4', '#4ade80'); }
        },
        {
          type: 'menuitem',
          text: '🔵 Khung Xanh dương (Blue Callout)',
          onAction: function() { applyBox('#eff6ff', '#60a5fa'); }
        },
        {
          type: 'menuitem',
          text: '🟡 Khung Vàng Gold (Yellow Callout)',
          onAction: function() { applyBox('#fefce8', '#facc15'); }
        },
        {
          type: 'menuitem',
          text: '🔴 Khung Đỏ (Red Alert)',
          onAction: function() { applyBox('#fef2f2', '#f87171'); }
        },
        {
          type: 'menuitem',
          text: '⚪ Khung Xám (Modern Gray)',
          onAction: function() { applyBox('#f8fafc', '#cbd5e1'); }
        },
        {
          type: 'menuitem',
          text: '🟣 Khung Tím (Purple)',
          onAction: function() { applyBox('#faf5ff', '#c084fc'); }
        },
        {
          type: 'menuitem',
          text: '🎨 Tự chọn Màu Nền & Màu Viền...',
          onAction: function() {
            var node = editor.selection.getNode();
            var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol') || node;
            var curBg = editor.dom.getStyle(box, 'background-color') || '#f0fdf4';
            var curBorder = editor.dom.getStyle(box, 'border-color') || '#4ade80';
            
            var bg = prompt('Nhập mã màu nền (ví dụ: #f0fdf4, #fff7ed, #e0f2fe...):', curBg);
            if (bg !== null && bg.trim() !== '') {
              var border = prompt('Nhập mã màu viền (ví dụ: #4ade80, #f97316, #3b82f6...):', curBorder);
              if (!border) border = bg;
              applyBox(bg.trim(), border.trim());
            }
          }
        },
        {
          type: 'menuitem',
          text: '❌ Xóa màu khung nền',
          onAction: function() {
            var node = editor.selection.getNode();
            var box = editor.dom.getParent(node, 'div, p, blockquote, ul, ol');
            if (box) {
              editor.dom.setStyle(box, 'background-color', '');
              editor.dom.setStyle(box, 'border', '');
              editor.dom.setStyle(box, 'border-radius', '');
              editor.dom.setStyle(box, 'padding', '');
              editor.dom.setStyle(box, 'margin', '');
            }
          }
        }
      ];
      callback(items);
    }
  });
}

tinymce.init({
  selector: '#tinymceContent',
  height: 550,
  language: 'vi',
  forced_root_block: 'p',
  force_p_newlines: true,
  force_br_newlines: false,
  convert_newlines_to_brs: false,
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | calloutbox removeformat',
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
    setupTinyMCECallout(editor);
    editor.on('BeforeExecCommand', function(e) {
      if (['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull'].indexOf(e.command) !== -1) {
        var node = editor.selection.getNode();
        if (node && (node.nodeName === 'P' || node.nodeName === 'DIV')) {
          if (node.innerHTML.search(/<br\s*\/?>/i) !== -1) {
            var parts = node.innerHTML.split(/<br\s*\/?>/i).filter(function(l){ return l.trim() !== ''; });
            if (parts.length > 1) {
              var replacement = parts.map(function(l){ return '<p>' + l + '</p>'; }).join('');
              node.outerHTML = replacement;
            }
          }
        }
      }
    });

    editor.on('SetContent', function(e) {
      var body = editor.getBody();
      if (!body) return;
      var blocks = body.querySelectorAll('p, div');
      blocks.forEach(function(b) {
        if (b.querySelectorAll('table, ul, ol, img').length === 0 && b.innerHTML.search(/<br\s*\/?>/i) !== -1) {
          var parts = b.innerHTML.split(/<br\s*\/?>/i).filter(function(p){ return p.trim() !== ''; });
          if (parts.length > 1) {
            b.outerHTML = parts.map(function(p){ return '<p>' + p + '</p>'; }).join('');
          }
        }
      });
    });

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
