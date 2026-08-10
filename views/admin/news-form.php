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
.news-layout{display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start}
@media(max-width:900px){.news-layout{grid-template-columns:1fr}}
@keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
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
        <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center">
          <h3>Nội dung bài viết</h3>
          <span style="font-size:12px;color:#0284c7;background:#e0f2fe;padding:4px 10px;border-radius:12px;font-weight:600"> Hỗ trợ kéo thả & Chọn nhiều ảnh từ máy</span>
        </div>
        <div class="panel-body" style="padding:0">
          <textarea id="tinymceNews" name="content"><?= htmlspecialchars($article['content']??'') ?></textarea>
        </div>
      </div>

      <!-- SEO CONTENT ANALYZER (Kiểu Rank Math / Yoast SEO) -->
      <div class="panel" id="seoPanel" style="margin-top:16px">
        <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center">
          <h3> Phân tích SEO nội dung</h3>
          <span id="seoScore" style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:12px;background:#fef3c7;color:#d97706">— Đang phân tích</span>
        </div>
        <div class="panel-body">
          <div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.6;background:#f8fafc;padding:10px 12px;border-radius:6px;border:1px solid #e2e8f0">
             Bài viết bắt buộc phải đạt từ <strong>85/100 điểm SEO</strong> trở lên mới được xuất bản lên Google — tương tự Rank Math / Yoast SEO.
          </div>
          <div class="form-group">
            <label>Tiêu đề Google</label>
            <input type="text" name="seo_title" id="seoTitle" maxlength="70"
                   value="<?= e($article['seo_title']??'') ?>" placeholder="Để trống để hệ thống tự lấy Tiêu đề bài viết">
          </div>
          <div class="form-group">
            <label>Mô tả Google</label>
            <textarea name="seo_description" id="seoDescription" rows="3" maxlength="170"
                      placeholder="Để trống để hệ thống tự lấy Tóm tắt bài viết"><?= e($article['seo_description']??'') ?></textarea>
          </div>
          <div class="form-group">
            <label>Từ khóa mục tiêu <span style="font-weight:400;color:#888;font-size:11px">(Focus Keyword — để kiểm tra bài viết)</span></label>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="text" name="seo_keyword" id="seoKeyword" style="flex:1"
                     value="<?= e($article['seo_keyword']??'') ?>" placeholder="VD: hướng dẫn bảo dưỡng điều hòa ô tô"
                     oninput="runSeoAnalysis()">
              <button type="button" id="btnSuggestKw" onclick="suggestKeywords()" class="btn btn-outline-navy btn-sm" style="white-space:nowrap;height:38px;display:inline-flex;align-items:center;gap:4px;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Gợi ý từ khóa
              </button>
            </div>
            <div id="kwSuggestions" style="display:none;margin-top:8px;padding:10px 12px;background:#f0f4ff;border-radius:8px;border:1px solid #c7d2fe"></div>
          </div>

          <!-- Google SERP Preview -->
          <div style="background:#fff;border:1px solid #dfe1e5;border-radius:8px;padding:14px;margin-bottom:14px">
            <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:6px;display:flex;align-items:center;gap:6px">
              <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
              Xem trước hiển thị trên Google
            </div>
            <div id="seoPreviewTitle" style="font-size:18px;color:#1a0dab;line-height:1.3;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Tiêu đề bài viết...</div>
            <div style="font-size:12px;color:#006621;margin-bottom:4px">https://coolingsystems.vn/news/<span id="seoPreviewSlug">...</span></div>
            <div id="seoPreviewDesc" style="font-size:13px;color:#545454;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">Mô tả bài viết...</div>
          </div>

          <!-- SEO Checklist -->
          <div id="seoChecklist" style="font-size:12px;line-height:2.2"></div>

          <button type="button" onclick="runSeoAnalysis()" class="btn btn-outline-navy btn-sm" style="margin-top:10px;width:100%"> Phân tích lại</button>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div style="display:flex;flex-direction:column;gap:14px;position:sticky;top:80px">
      <div class="panel">
        <div class="panel-head"><h3> Xuất bản</h3></div>
        <div class="panel-body">
          <div class="form-group">
            <label>Trạng thái</label>
            <select name="status" id="statusSelect">
              <option value="draft" <?= ($article['status']??'')==='draft'?'selected':'' ?>>Bản nháp</option>
              <option value="published" <?= ($article['status']??'')==='published'?'selected':'' ?>>Đăng ngay (Yêu cầu ≥85đ SEO)</option>
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
  selector: '#tinymceNews',
  height: 480,
  language: 'vi',
  forced_root_block: 'p',
  force_p_newlines: true,
  force_br_newlines: false,
  convert_newlines_to_brs: false,
  relative_urls: false,
  remove_script_host: true,
  convert_urls: true,
  paste_data_images: true,
  images_upload_url: '/admin/upload-tinymce-image',
  file_picker_types: 'image',
  file_picker_callback: function(cb, value, meta) {
    if (meta.filetype === 'image') {
      var input = document.createElement('input');
      input.type = 'file'; input.accept = 'image/*';
      input.onchange = function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        var csrf = document.querySelector('input[name="_csrf"]');
        if (csrf) fd.append('_csrf', csrf.value);
        fetch('/admin/upload-tinymce-image', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.location) {
            cb(data.location, { title: file.name });
          } else {
            alert('Lỗi upload: ' + (data.msg || 'Không thể tải ảnh từ máy'));
          }
        })
        .catch(err => alert('Lỗi kết nối: ' + err.message));
      };
      input.click();
    }
  },
  plugins: 'table lists link image code wordcount fullscreen preview searchreplace autolink visualblocks',
  toolbar: [
    'undo redo | fontfamily fontsize | blocks | bold italic underline strikethrough | forecolor backcolor | calloutbox removeformat',
    'alignleft aligncenter alignright alignjustify | bullist numlist checklist | outdent indent | table | multiimage image link | code fullscreen'
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
    img { max-width: 100%; height: auto; border-radius: 8px; margin: 12px auto; display: inline-block; vertical-align: middle; }
    p[style*="text-align: center"], div[style*="text-align: center"], p[style*="text-align:center"], div[style*="text-align:center"] { text-align: center !important; }
    p[style*="text-align: center"] img, div[style*="text-align: center"] img, p[style*="text-align:center"] img, div[style*="text-align:center"] img { display: inline-block !important; margin-left: auto !important; margin-right: auto !important; }
    blockquote { border-left: 3px solid #c9a14a; padding: 12px 16px; background: #faf8f3; border-radius: 0 8px 8px 0; }
  `,
  menubar: 'file edit view insert format table',
  promotion: false,
  branding: false,
  license_key: 'gpl',
  setup: function(editor) {
    setupTinyMCECallout(editor);

    // Multi Image Upload Toolbar Button
    editor.ui.registry.addButton('multiimage', {
      text: '🖼 Tải nhiều ảnh từ máy',
      tooltip: 'Chọn và tải lên nhiều file ảnh từ máy tính cùng lúc',
      onAction: function() {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.multiple = true;
        input.onchange = function() {
          var files = Array.from(this.files);
          if (!files.length) return;
          var fd = new FormData();
          files.forEach(function(file) {
            fd.append('files[]', file);
          });
          var csrf = document.querySelector('input[name="_csrf"]');
          if (csrf) fd.append('_csrf', csrf.value);
          
          if (editor.notificationManager) {
            editor.notificationManager.open({
              text: '⏳ Đang tải lên ' + files.length + ' ảnh từ máy tính...',
              type: 'info',
              timeout: 3000
            });
          }

          fetch('/admin/upload-tinymce-image', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(data => {
            if (data.ok && data.locations && data.locations.length) {
              var html = '';
              data.locations.forEach(function(src) {
                html += '<p style="text-align:center;"><img src="' + src + '" style="max-width:100%;height:auto;border-radius:8px;margin:12px auto;" /></p>';
              });
              editor.insertContent(html);
              if (editor.notificationManager) {
                editor.notificationManager.open({
                  text: '✅ Đã tải và chèn thành công ' + data.locations.length + ' ảnh vào bài!',
                  type: 'success',
                  timeout: 3000
                });
              }
              runSeoAnalysis();
            } else {
              alert('Lỗi upload: ' + (data.msg || 'Không thể tải ảnh từ máy'));
            }
          })
          .catch(err => alert('Lỗi kết nối upload: ' + err.message));
        };
        input.click();
      }
    });

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

    editor.on('change', function() {
      editor.save();
      clearTimeout(window._seoT);
      window._seoT = setTimeout(runSeoAnalysis, 500);
    });

    editor.on('keyup', function() {
      clearTimeout(window._seoT);
      window._seoT = setTimeout(runSeoAnalysis, 500);
    });
  }
});

// ── SEO ANALYSIS ENGINE FOR NEWS ARTICLES (Rank Math / Yoast SEO Style) ──
window._latestSeoScore = 0;

function runSeoAnalysis() {
  var title = (document.querySelector('input[name="title"]')?.value || '').trim();
  var excerpt = (document.querySelector('textarea[name="excerpt"]')?.value || '').trim();
  var keyword = (document.getElementById('seoKeyword')?.value || '').toLowerCase().trim();
  var slugInput = document.querySelector('input[name="slug"]');
  var slug = (slugInput?.value || '').trim();

  function getContentText() {
    if (typeof tinymce !== 'undefined') {
      var ed = tinymce.get('tinymceNews');
      if (ed) {
        try {
          return {
            html: ed.getContent() || '',
            text: (ed.getContent({format:'text'}) || '').trim()
          };
        } catch(e) {}
      }
    }
    var ta = document.getElementById('tinymceNews');
    if (ta && ta.value) {
      return { html: ta.value, text: ta.value.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim() };
    }
    return { html: '', text: '' };
  }

  var cObj = getContentText();
  var contentHtml = cObj.html;
  var contentText = cObj.text;
  var allText = (title + ' ' + excerpt + ' ' + contentText).toLowerCase();

  // Preview Google SERP
  var customSeoTitle = (document.getElementById('seoTitle')?.value || '').trim();
  var customSeoDesc = (document.getElementById('seoDescription')?.value || '').trim();
  var previewTitle = customSeoTitle || title;
  var previewDesc = customSeoDesc || excerpt || (contentText ? contentText.substring(0, 155) : 'Mô tả bài viết...');

  document.getElementById('seoPreviewTitle').textContent = previewTitle || 'Tiêu đề bài viết...';
  document.getElementById('seoPreviewDesc').textContent = previewDesc;
  document.getElementById('seoPreviewSlug').textContent = slug || '...';

  // ── WEIGHTED SCORING FOR NEWS ARTICLES (100 điểm) ──
  // Tiêu đề & Meta: 20đ | Tóm tắt & Bài viết: 35đ | Cấu trúc Heading: 20đ | Từ khóa: 25đ
  var score = 0;
  var checks = [];
  function add(pass, msg, cat, pts) { if(pass) score += pts; checks.push({pass:pass,msg:msg,cat:cat,pts:pts}); }

  // ─ TIÊU ĐỀ & META (20đ)
  add(previewTitle.length >= 20 && previewTitle.length <= 70, 'Tiêu đề Google dài ' + previewTitle.length + ' ký tự (tốt nhất: 20-70 ký tự)', 'meta', 10);
  add(previewDesc.length >= 120 && previewDesc.length <= 170, 'Mô tả Google dài ' + previewDesc.length + ' ký tự (tốt nhất: 120-170 ký tự)', 'meta', 10);

  // ─ TÓM TẮT & BÀI VIẾT (35đ)
  add(excerpt.length >= 50, 'Tóm tắt bài viết dài ' + excerpt.length + ' ký tự (≥50 ký tự)', 'content', 10);
  var wordCount = contentText ? contentText.split(/\s+/).filter(Boolean).length : 0;
  add(wordCount >= 300, 'Bài viết dài ' + wordCount + ' từ (đạt chuẩn tối thiểu ≥300 từ)', 'content', 15);
  add(wordCount >= 600, 'Bài viết chuyên sâu ≥600 từ (' + wordCount + ' từ)', 'content', 10);

  // ─ CẤU TRÚC HEADING & MINH HỌA (20đ)
  var h2c = (contentHtml.match(/<h2\b[^>]*>/gi)||[]).length;
  var h3c = (contentHtml.match(/<h3\b[^>]*>/gi)||[]).length;
  add(h2c >= 1, 'Bài viết có ' + h2c + ' thẻ tiêu đề H2 (khuyên dùng ≥1 H2)', 'struct', 8);
  add(h2c + h3c >= 2, 'Bài viết có tổng ' + (h2c+h3c) + ' thẻ H2/H3 phân đoạn', 'struct', 4);
  add(/<(ul|ol)\b[^>]*>/i.test(contentHtml), 'Bài viết sử dụng danh sách gạch đầu dòng / số', 'struct', 4);
  add(/<img\b[^>]*>/i.test(contentHtml) || document.querySelector('input[name="thumbnail"]')?.files?.length > 0, 'Bài viết có hình ảnh minh họa', 'struct', 4);

  // ─ TỪ KHÓA MỤC TIÊU (25đ)
  if (keyword) {
    add(title.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa "' + keyword + '" có trong Tiêu đề bài viết', 'kw', 8);
    add(previewDesc.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa có trong Mô tả / Tóm tắt bài viết', 'kw', 6);
    add(slug.toLowerCase().indexOf(keyword.replace(/\s+/g, '-')) !== -1 || slug.toLowerCase().indexOf(keyword) !== -1, 'Từ khóa xuất hiện trong URL Slug', 'kw', 4);
    var kwr = new RegExp(keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi');
    var kwn = (allText.match(kwr)||[]).length;
    var den = wordCount > 0 ? ((kwn/wordCount)*100).toFixed(1) : 0;
    add(kwn >= 2 && den <= 4, 'Mật độ từ khóa: ' + den + '% (' + kwn + ' lần, tốt: 1-3%)', 'kw', 7);
  } else {
    add(title.length >= 15, 'Tiêu đề chứa từ khóa tự nhiên (Hãy nhập Từ khóa mục tiêu để phân tích chi tiết hơn)', 'kw', 25);
  }

  // Render checklist & badge
  var catLabels = { meta: '📋 Tiêu đề & Meta Google', content: '📝 Tóm tắt & Nội dung bài', struct: '🏗 Cấu trúc bài viết', kw: '🔑 Từ khóa SEO' };
  var html = '';
  ['meta','content','struct','kw'].forEach(function(cat) {
    var items = checks.filter(function(c){return c.cat===cat;});
    if (!items.length) return;
    html += '<div style="font-weight:700;color:#1e293b;margin:10px 0 4px;font-size:13px">' + catLabels[cat] + '</div>';
    items.forEach(function(c){
      html += '<div style="color:' + (c.pass ? '#059669' : '#dc2626') + ';font-size:12px">' + (c.pass ? '✅' : '❌') + ' ' + c.msg + ' <span style="font-size:10px;color:#94a3b8">(' + c.pts + 'đ)</span></div>';
    });
  });
  document.getElementById('seoChecklist').innerHTML = html;

  var pct = Math.min(score, 100);
  window._latestSeoScore = pct;
  var badge = document.getElementById('seoScore');
  if (pct >= 85)      { badge.textContent='✅ Đạt chuẩn xuất bản ('+pct+'/100)'; badge.style.background='#ecfdf5'; badge.style.color='#059669'; }
  else if (pct >= 50) { badge.textContent='⚠️ Chưa đủ 85đ ('+pct+'/100)'; badge.style.background='#fef3c7'; badge.style.color='#d97706'; }
  else                { badge.textContent='❌ Cần cải thiện ('+pct+'/100)'; badge.style.background='#fef2f2'; badge.style.color='#dc2626'; }
}

function suggestKeywords() {
  var title = (document.querySelector('input[name="title"]')?.value || '').trim();
  var box = document.getElementById('kwSuggestions');
  if (!title) {
    alert('Vui lòng nhập Tiêu đề bài viết trước để hệ thống gợi ý từ khóa phù hợp!');
    return;
  }
  box.style.display = 'block';
  box.innerHTML = '<span style="font-size:12px;color:#475569">🔍 Đang phân tích từ khóa gợi ý...</span>';

  // Generate smart keywords from title
  var cleanTitle = title.toLowerCase().replace(/[^a-z0-9àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ\s]/g, '');
  var words = cleanTitle.split(/\s+/).filter(function(w){ return w.length >= 2; });
  var kws = [];
  kws.push(cleanTitle);

  if (words.length >= 3) {
    kws.push(words.slice(0, 3).join(' '));
    kws.push(words.slice(-3).join(' '));
  }
  if (words.length >= 4) {
    kws.push(words.slice(1, 4).join(' '));
  }
  if (cleanTitle.indexOf('ô tô') === -1 && cleanTitle.indexOf('xe') === -1) {
    kws.push(cleanTitle + ' ô tô');
  }

  // Remove duplicates
  kws = kws.filter(function(item, pos) { return kws.indexOf(item) == pos; });

  var html = '<div style="font-size:12px;font-weight:700;color:#1e3a8a;margin-bottom:6px"> Từ khóa gợi ý cho bài viết này:</div><div style="display:flex;flex-wrap:wrap;gap:6px">';
  kws.forEach(function(kw) {
    html += '<button type="button" onclick="selectKeyword(\'' + kw.replace(/'/g, "\\'") + '\')" style="background:#fff;border:1px solid #93c5fd;color:#1e40af;padding:4px 10px;border-radius:16px;font-size:12px;cursor:pointer;font-weight:500">+ ' + kw + '</button>';
  });
  html += '</div>';
  box.innerHTML = html;
}

function selectKeyword(kw) {
  document.getElementById('seoKeyword').value = kw;
  document.getElementById('kwSuggestions').style.display = 'none';
  runSeoAnalysis();
}

// ── BẮT LỖI SUBMIT KHI XUẤT BẢN NẾU ĐIỂM SEO < 85 ──
document.getElementById('newsForm')?.addEventListener('submit', function(e) {
  // Sync TinyMCE content back to textarea before submit
  if (typeof tinymce !== 'undefined') {
    tinymce.triggerSave();
    var ed = tinymce.get('tinymceNews');
    if (ed) ed.save();
  }

  var statusSel = document.querySelector('select[name="status"]');
  var statusVal = statusSel ? statusSel.value : 'draft';

  if (statusVal === 'published') {
    runSeoAnalysis();
    var currentScore = window._latestSeoScore || 0;
    if (currentScore < 85) {
      e.preventDefault();
      
      var oldModal = document.getElementById('seoErrorModal');
      if (oldModal) oldModal.remove();

      var modal = document.createElement('div');
      modal.id = 'seoErrorModal';
      modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.65);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(4px);';
      modal.innerHTML = `
        <div style="background:#fff;border-radius:16px;max-width:520px;width:100%;padding:28px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);border:1px solid #fecaca;animation:popIn 0.25s ease-out">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <div style="width:48px;height:48px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;flex-shrink:0">❌</div>
            <div>
              <h3 style="margin:0;font-size:18px;color:#991b1b;font-weight:800">Chưa đủ tiêu chuẩn xuất bản SEO!</h3>
              <p style="margin:2px 0 0;font-size:13px;color:#7f1d1d">Điểm SEO hiện tại: <strong style="font-size:16px;color:#dc2626">${currentScore}/100</strong> (Yêu cầu tối thiểu: <strong style="color:#059669;font-size:16px">85/100</strong>)</p>
            </div>
          </div>
          <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:14px;font-size:13px;color:#881337;line-height:1.6;margin-bottom:20px">
             <strong>Quy định SEO của Coolingsystems.vn:</strong> Để đảm bảo bài viết được Google chọn lên Top và lập chỉ mục nhanh chóng, bài viết phải đạt ít nhất <strong>85/100 điểm SEO</strong> trước khi Đăng bài.<br><br>👉 Hãy bổ sung các mục màu đỏ trong phần <strong>Phân tích SEO nội dung</strong> bên dưới để nâng điểm!
          </div>
          <div style="display:flex;justify-content:flex-end;gap:10px">
            <button type="button" onclick="document.getElementById('seoErrorModal').remove();document.getElementById('seoPanel').scrollIntoView({behavior:'smooth'});" class="btn btn-navy" style="padding:10px 20px;font-weight:700"> Cải thiện tiêu chí SEO ngay</button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
      document.getElementById('seoPanel').scrollIntoView({ behavior: 'smooth' });
    }
  }
});

// Auto-generate slug from title & bind SEO analysis listeners
document.querySelector('input[name="title"]')?.addEventListener('input', function(){
  var sl = document.querySelector('input[name="slug"]');
  if (sl && !sl.dataset.manual) {
    sl.value = this.value.toLowerCase().replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g,'a').replace(/[èéẹẻẽêềếệểễ]/g,'e').replace(/[ìíịỉĩ]/g,'i').replace(/[òóọỏõôồốộổỗơờớợởỡ]/g,'o').replace(/[ùúụủũưừứựửữ]/g,'u').replace(/[ỳýỵỷỹ]/g,'y').replace(/đ/g,'d').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
  }
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 300);
});

document.querySelector('input[name="slug"]')?.addEventListener('input', function(){
  this.dataset.manual = '1';
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 300);
});

document.querySelector('textarea[name="excerpt"]')?.addEventListener('input', function(){
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 300);
});

document.getElementById('seoTitle')?.addEventListener('input', function(){
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 300);
});

document.getElementById('seoDescription')?.addEventListener('input', function(){
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 300);
});

document.getElementById('seoKeyword')?.addEventListener('input', function(){
  clearTimeout(window._seoT);
  window._seoT = setTimeout(runSeoAnalysis, 500);
});

setTimeout(function() {
  runSeoAnalysis();
}, 1000);
</script>
<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
