<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="<?= csrfToken() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1a3258">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-cooling-round-48x48.png?v=20260818-logo-v1">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-cooling-round-32x32.png?v=20260818-logo-v1">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-cooling-round-16x16.png?v=20260818-logo-v1">
<link rel="shortcut icon" href="/favicon-cooling-round.ico?v=20260818-logo-v1">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-cooling-round.png?v=20260818-logo-v1">
<link rel="manifest" href="/site.webmanifest?v=20260818-logo-v1">
<title><?= e($title ?? 'Admin') ?> — Cooling Admin</title>
<meta name="description" content="Trang quản trị hệ thống Cooling — Quản lý sản phẩm, đơn hàng, khách hàng và cài đặt.">
<link rel="stylesheet" href="/css/cooling.css?v=1780930000">
<style id="admin-ui-standardize">
/* === ADMIN FULL BLEED LAYOUT RESET (Zero white gaps top/bottom/left) === */
html, body {
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  min-height: 100vh !important;
  background-color: var(--bg-soft, #f8fafc) !important;
}
.dash {
  display: block !important;
  min-height: 100vh !important;
  width: 100% !important;
  margin: 0 !important;
  padding: 0 !important;
  position: relative !important;
  background: var(--bg-soft, #f8fafc) !important;
}
body .dash-sidebar {
  background: var(--navy-dark, #0f172a) !important;
  color: #fff !important;
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  bottom: 0 !important;
  width: 260px !important;
  height: 100vh !important;
  height: 100dvh !important;
  max-height: none !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
  border-radius: 0 !important;
  margin: 0 !important;
  z-index: 1000 !important;
  padding: 24px 0 40px 0 !important;
}
.dash-main {
  margin-left: 260px !important;
  width: calc(100% - 260px) !important;
  min-height: 100vh !important;
  box-sizing: border-box !important;
}

/* === ADMIN UI STANDARDIZATION (tester feedback) === */
/* Consistent heading hierarchy in admin content — clean Inter, not the slanted Playfair serif */
.dash-main h1 { font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif !important; font-size:24px !important; font-weight:700 !important; font-style:normal !important; letter-spacing:-0.01em !important; line-height:1.3 !important; color:var(--navy-dark) !important; }
.dash-main h2 { font-family:'Inter',sans-serif !important; font-size:18px !important; font-weight:700 !important; font-style:normal !important; letter-spacing:0 !important; line-height:1.35 !important; color:var(--navy-dark) !important; }
.dash-main h3 { font-family:'Inter',sans-serif !important; font-size:15px !important; font-weight:700 !important; font-style:normal !important; letter-spacing:0 !important; color:var(--navy) !important; }
.dash-main h4 { font-family:'Inter',sans-serif !important; font-size:13px !important; font-weight:700 !important; font-style:normal !important; letter-spacing:0 !important; color:var(--navy) !important; }
.dash-main p { font-size:13.5px; line-height:1.6; }
/* Tables: horizontal scroll + column show/hide */
.tbl-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.tbl-scroll > .tbl th, .tbl-scroll > .tbl td { white-space:nowrap; max-width:none !important; }
.tbl-coltools { position:relative; display:flex; justify-content:flex-end; margin:0 0 8px; padding:8px 20px 0 0; }
.tbl-colbtn { display:inline-flex; align-items:center; gap:6px; height:32px; padding:0 12px; border:1px solid var(--line); border-radius:7px; background:#fff; color:var(--navy); font-size:12px; font-weight:600; cursor:pointer; }
.tbl-colbtn:hover { border-color:var(--navy); }
.tbl-colmenu { display:none; position:absolute; right:0; top:calc(100% + 4px); background:#fff; border:1px solid var(--line); border-radius:8px; box-shadow:0 10px 28px rgba(15,35,66,.16); padding:8px; z-index:200; min-width:190px; max-height:320px; overflow:auto; }
.tbl-colmenu.open { display:block; }
.tbl-colmenu label { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:6px; font-size:13px; color:var(--ink-2); cursor:pointer; white-space:nowrap; font-weight:500; }
.tbl-colmenu label:hover { background:#f1f5fb; }
.tbl-colmenu input { width:auto !important; height:auto !important; margin:0 !important; }
.tbl-colmenu-hd { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:2px 6px 8px; margin-bottom:6px; border-bottom:1px solid var(--line); font-size:12px; font-weight:700; color:var(--navy); text-transform:uppercase; letter-spacing:.03em; }
.tbl-colmenu-x { border:none; background:none; font-size:20px; line-height:1; color:#9aa3b2; cursor:pointer; padding:0 2px; }
.tbl-colmenu-x:hover { color:var(--navy); }
/* === Hiệu ứng nhấn nút (admin) === */
.dash-main .btn, .dash-main button, .dash-main .btn-save, .dash-main .tbl td:last-child a, .dash-main .sec-tabs a, .dash-main .cdd-opt {
  transition: transform .1s cubic-bezier(.2,.8,.3,1), box-shadow .18s ease, background-color .15s ease, border-color .15s ease, color .15s ease, filter .12s ease;
}
.dash-main .btn:hover, .dash-main .btn-save:hover, .dash-main .tbl td:last-child a:hover { filter: brightness(1.06); }
.dash-main .btn:active, .dash-main button:active, .dash-main .btn-save:active, .dash-main .tbl td:last-child a:active, .dash-main .sec-tabs a:active {
  transform: scale(0.95) !important; filter: brightness(0.9); box-shadow: none !important;
}
.dash-main .cdd-opt:active { transform: scale(0.98); }
/* === Bỏ viền vàng khi focus checkbox/radio; tích màu navy === */
.dash-main input[type="checkbox"], .dash-main input[type="radio"] { accent-color: var(--navy); }
.dash-main input[type="checkbox"]:focus, .dash-main input[type="radio"]:focus { box-shadow:none !important; outline:none !important; }
/* === Dropdown tùy biến thay <select> (mở xuống dưới + cuộn) === */
.cs-sel { position:relative; display:inline-block; width:100%; vertical-align:middle; }
.cs-sel-trg { width:100%; min-height:38px; padding:0 12px 0 14px; display:flex; align-items:center; justify-content:space-between; gap:8px; border:1px solid #d6deea; border-radius:8px; background:#fff; color:#1a3258; font-size:13.5px; font-weight:500; cursor:pointer; font-family:inherit; transition:border-color .15s, box-shadow .15s; }
.cs-sel-trg:hover { border-color:#b9c4d6; }
.cs-sel.open .cs-sel-trg { border-color:#1a3258; box-shadow:0 0 0 3px rgba(26,50,88,.12); }
.cs-sel-lbl { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; text-align:left; }
.cs-sel-arrow { flex-shrink:0; color:#1a3258; transition:transform .2s; }
.cs-sel.open .cs-sel-arrow { transform:rotate(180deg); }
.cs-sel-panel { display:none; position:absolute; top:calc(100% + 6px); left:0; right:0; min-width:180px; background:#fff; border:1px solid #d6deea; border-radius:8px; box-shadow:0 14px 38px rgba(15,35,66,.18); max-height:280px; overflow-y:auto; z-index:200; padding:6px; }
.cs-sel.open .cs-sel-panel { display:block; }
.cs-sel-opt { padding:9px 12px; border-radius:6px; font-size:13.5px; color:#1a3258; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cs-sel-opt:hover { background:#f1f5fb; }
.cs-sel-opt.sel { background:#1a3258; color:#fff; font-weight:600; }
/* === Lich chon ngay tuy bien (thay input type=date) === */
.cs-date { position:relative; display:inline-block; vertical-align:middle; box-sizing:border-box; }
.cs-date-trg { width:100%; box-sizing:border-box; min-height:38px; padding:0 12px; display:flex; align-items:center; gap:9px; border:1px solid #d6deea; border-radius:8px; background:#fff; color:#1a3258; font-size:13.5px; font-weight:500; font-family:inherit; cursor:pointer; transition:border-color .15s, box-shadow .15s; }
.cs-date-trg:hover { border-color:#b9c4d6; }
.cs-date.open .cs-date-trg { border-color:#1a3258; box-shadow:0 0 0 3px rgba(26,50,88,.12); }
.cs-date-trg > svg { flex-shrink:0; color:#1a3258; }
.cs-date-lbl { flex:1; text-align:left; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cs-date-lbl.ph { color:#9aa7bd; font-weight:400; }
.cs-date-panel { display:none; position:absolute; top:calc(100% + 6px); left:0; width:272px; background:#fff; border:1px solid #d6deea; border-radius:12px; box-shadow:0 16px 44px rgba(15,35,66,.20); z-index:300; padding:14px; box-sizing:border-box; }
.cs-date.open .cs-date-panel { display:block; }
.cs-date-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.cs-date-title { font-size:14px; font-weight:700; color:#1a3258; }
.cs-date-nav { width:30px; height:30px; display:flex; align-items:center; justify-content:center; border:none; background:transparent; border-radius:7px; color:#1a3258; cursor:pointer; transition:background .15s; }
.cs-date-nav:hover { background:#f1f5fb; }
.cs-date-wk { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px; }
.cs-date-wk span { text-align:center; font-size:11px; font-weight:600; color:#9aa7bd; padding:4px 0; }
.cs-date-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.cs-date-cell { height:32px; display:flex; align-items:center; justify-content:center; font-size:13px; color:#1a3258; border-radius:7px; cursor:pointer; transition:background .12s; }
.cs-date-cell:hover { background:#f1f5fb; }
.cs-date-cell.empty { visibility:hidden; cursor:default; }
.cs-date-cell.today { box-shadow:inset 0 0 0 1.5px #1a3258; font-weight:700; }
.cs-date-cell.sel, .cs-date-cell.sel:hover { background:#1a3258; color:#fff; font-weight:700; }
.cs-date-cell.disabled, .cs-date-cell.disabled:hover { color:#cfd6e2; cursor:not-allowed; background:transparent; }
.cs-date-ft { display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding-top:10px; border-top:1px solid #eef2f7; }
.cs-date-ft button { border:none; background:transparent; font-size:13px; font-weight:600; cursor:pointer; padding:5px 8px; border-radius:6px; font-family:inherit; }
.cs-date-clear { color:#9aa7bd; }
.cs-date-clear:hover { color:#1a3258; background:#f1f5fb; }
.cs-date-today { color:#1a3258; }
.cs-date-today:hover { background:#f1f5fb; }
.uf-filter .cs-date, .uf-filter .cs-date-trg { height:42px; }
/* === Nút chọn ảnh tùy biến (thay input type=file mặc định) === */
.cs-file { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.cs-file-btn { display:inline-flex; align-items:center; gap:8px; padding:0 16px; height:40px; border:1px solid #1a3258; border-radius:8px; background:#1a3258; color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:background .15s; white-space:nowrap; }
.cs-file-btn:hover { background:#0f2342; }
.cs-file-btn svg { flex-shrink:0; }
.cs-file-name { font-size:13px; color:#566; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px; }
.cs-file-name.empty { color:#9aa7bd; font-style:italic; }
/* === Chú thích nhóm trong sidebar === */
.dash-sidebar .sb-section { position:relative; }
.dash-sidebar .sb-sec-desc { display:block; margin-top:3px; font-size:10.5px; font-weight:400; text-transform:none; letter-spacing:0; line-height:1.35; color:rgba(255,255,255,0.32); }
/* === Bo góc đồng bộ cho mọi nút admin (hết khung nhọn) === */
.dash-main .btn { border-radius:8px !important; }
/* === Khung tiêu đề trang admin (đồng bộ kiểu "Quản lý liên hệ"): thẻ trắng + viền navy trên + thanh vàng === */
.dash-main .dash-head--framed, .dash-main h1.h1--framed { background:#fff; border:1px solid var(--line); border-top:3px solid var(--navy); border-radius:4px; padding:16px 22px 16px 38px; position:relative; margin-bottom:22px; box-shadow:0 1px 3px rgba(15,35,66,.04); }
.dash-main .dash-head--framed::after, .dash-main h1.h1--framed::after { content:''; position:absolute; top:-3px; left:0; width:80px; height:3px; background:linear-gradient(90deg, var(--gold-warm), var(--gold-deep)); }
.dash-main .dash-head--framed::before, .dash-main h1.h1--framed::before { content:''; position:absolute; left:20px; top:50%; transform:translateY(-50%); width:4px; height:22px; border-radius:2px; background:linear-gradient(180deg, var(--gold-warm), var(--gold-deep)); }
.dash-main .dash-head--framed { align-items:center !important; }
.dash-main h1.h1--framed { margin-bottom:22px !important; }
/* === Đồng bộ màu: nút VÀNG trong admin -> NAVY (vàng chỉ giữ cho thanh/viền accent) === */
.dash-main .btn-gold { background:var(--navy) !important; color:#fff !important; border-color:var(--navy) !important; }
.dash-main .btn-gold::before { display:none !important; }
.dash-main .btn-gold:hover { background:var(--navy-dark) !important; border-color:var(--navy-dark) !important; box-shadow:0 6px 16px rgba(15,35,66,0.3) !important; }
.dash-main .btn-outline-gold { background:transparent !important; color:var(--navy) !important; border-color:var(--navy) !important; }
.dash-main .btn-outline-gold:hover { background:var(--navy) !important; color:#fff !important; }
/* === Đồng đều cỡ nút trong header mỗi mục admin (Xuất/Nhập CSV, Thêm..., Lọc...) === */
.dash-main .dash-head .btn, .dash-main .dash-head--framed .btn { height:40px !important; min-height:40px !important; padding-top:0 !important; padding-bottom:0 !important; padding-left:18px !important; padding-right:18px !important; font-size:12px !important; box-sizing:border-box !important; display:inline-flex !important; align-items:center !important; }
.adm-edit{display:inline-flex;align-items:center;justify-content:center;gap:5px;background:var(--navy,#1a3258);color:#fff;border:1.5px solid var(--navy,#1a3258);padding:7px 16px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;text-transform:none;letter-spacing:.01em;line-height:1.2;text-decoration:none;transition:all .15s}
.adm-edit:hover{background:#0f2342;border-color:#0f2342;color:#fff}
.adm-del{display:inline-flex;align-items:center;justify-content:center;gap:5px;background:#fff;color:var(--navy,#1a3258);border:1.5px solid var(--navy,#1a3258);padding:7px 16px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;text-transform:none;letter-spacing:.01em;line-height:1.2;text-decoration:none;transition:all .15s}
.adm-del:hover{background:#fef2f2;color:#c0392b;border-color:#c0392b}
</style>
<?php
$_canonicalPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$_canonicalUrl = 'https://coolingsystems.vn' . rtrim($_canonicalPath, '/');
if ($_canonicalPath === '/admin' || $_canonicalPath === '/admin/') $_canonicalUrl = 'https://coolingsystems.vn/admin';
?>
<link rel="canonical" href="<?= htmlspecialchars($_canonicalUrl) ?>">
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<?php require __DIR__ . '/svg-logo.php'; ?>
<?php $flash = $flash ?? []; if (!empty($flash)): ?>
<div class="flash-stack" style="position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:8px;max-width:360px">
  <?php foreach ($flash as $f): ?>
    <div class="flash <?= e($f['type']) ?>" style="padding:14px 20px;background:<?= ($f['type']==='error'||$f['type']==='danger')?'#fef2f2':'#f0fdf4' ?>;color:<?= ($f['type']==='error'||$f['type']==='danger')?'#991b1b':'#166534' ?>;border-left:4px solid <?= ($f['type']==='error'||$f['type']==='danger')?'#ef4444':'#22c55e' ?>;border-radius:8px;font-size:13.5px;font-weight:600;box-shadow:0 10px 30px rgba(0,0,0,0.15);animation:slideIn 0.3s ease;display:flex;align-items:center;gap:10px">
      <span><?= ($f['type']==='error'||$f['type']==='danger')?'❌':'✅' ?></span>
      <span><?= e($f['message']) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
