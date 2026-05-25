<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <h1>⚡ Cài đặt ưu đãi đăng ký</h1>
  <a href="/admin/settings" class="btn btn-outline" style="font-size:13px">← Quay lại cài đặt</a>
</div>

<style>
.nl-settings-card { background: #fff; border: 1px solid #eaeaea; border-radius: 12px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); max-width: 700px; }
.nl-settings-card h3 { font-size: 16px; font-weight: 800; color: var(--navy); margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0; }
.nl-fg { margin-bottom: 18px; }
.nl-fg label { display: block; font-size: 13px; font-weight: 700; color: #444; margin-bottom: 6px; }
.nl-fg label small { font-weight: 400; color: #888; }
.nl-fg input[type=text], .nl-fg input[type=number], .nl-fg textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 10px 12px; font-size: 14px; box-sizing: border-box; transition: border-color 0.2s; }
.nl-fg input:focus, .nl-fg textarea:focus { border-color: var(--navy); outline: none; box-shadow: 0 0 0 3px rgba(26,50,88,0.1); }
.nl-fg textarea { min-height: 60px; resize: vertical; }
.nl-preview { background: linear-gradient(135deg, #1a3258, #0b1f40); color: #fff; border-radius: 12px; padding: 24px; margin-top: 24px; text-align: center; }
.nl-preview h3 { color: #fff; border: none; margin-bottom: 8px; }
.nl-preview em { color: #c8962b; font-style: italic; }
.nl-preview p { color: rgba(255,255,255,0.7); font-size: 14px; }
.nl-preview .voucher-box { background: #fef3c7; border: 2px dashed #c8962b; border-radius: 10px; padding: 14px; margin: 16px auto; max-width: 280px; }
.nl-preview .voucher-code { font-size: 24px; font-weight: 900; letter-spacing: 4px; color: #1a3258; font-family: 'Courier New', monospace; }
.nl-preview .voucher-amount { font-size: 18px; font-weight: 700; color: #c8962b; }
</style>

<div class="nl-settings-card">
  <h3>📧 Nội dung phần Đăng ký nhận ưu đãi</h3>
  <form method="post" action="/admin/settings/newsletter">
    <?= csrfField() ?>
    <div class="nl-fg">
      <label>Tiêu đề <small>(hiển thị ở footer)</small></label>
      <input type="text" name="newsletter_title" value="<?= htmlspecialchars($nl['newsletter_title'] ?? 'Đăng ký nhận ưu đãi') ?>" placeholder="Đăng ký nhận ưu đãi">
    </div>
    <div class="nl-fg">
      <label>Mô tả phụ</label>
      <textarea name="newsletter_subtitle" rows="2"><?= htmlspecialchars($nl['newsletter_subtitle'] ?? '') ?></textarea>
    </div>
    <div class="nl-fg" style="display:flex; gap:16px; flex-wrap:wrap;">
      <div style="flex:1; min-width:200px;">
        <label>Mã voucher</label>
        <input type="text" name="newsletter_voucher_code" value="<?= htmlspecialchars($nl['newsletter_voucher_code'] ?? 'UUDAI100K') ?>" placeholder="UUDAI100K" style="text-transform:uppercase; font-weight:700; letter-spacing:2px;">
      </div>
      <div style="flex:1; min-width:200px;">
        <label>Giá trị ưu đãi (VNĐ)</label>
        <input type="number" name="newsletter_voucher_amount" value="<?= htmlspecialchars($nl['newsletter_voucher_amount'] ?? '100000') ?>" placeholder="100000" min="0" step="1000">
      </div>
    </div>
    <div class="nl-fg">
      <label>Nút đăng ký <small>(text trên nút)</small></label>
      <input type="text" name="newsletter_btn_text" value="<?= htmlspecialchars($nl['newsletter_btn_text'] ?? 'Đăng ký nhận tin') ?>" placeholder="Đăng ký nhận tin">
    </div>
    <button type="submit" class="btn-save" style="background:var(--navy);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-size:14px;font-weight:700;cursor:pointer;">💾 Lưu cài đặt</button>
  </form>

  <div class="nl-preview" id="nlPreview">
    <h3 id="prevTitle"><?= htmlspecialchars($nl['newsletter_title'] ?? 'Đăng ký nhận ưu đãi') ?></h3>
    <p id="prevSubtitle"><?= htmlspecialchars($nl['newsletter_subtitle'] ?? '') ?></p>
    <div class="voucher-box">
      <div style="font-size:11px;color:#888;margin-bottom:4px">Mã giảm giá</div>
      <div class="voucher-code" id="prevCode"><?= htmlspecialchars($nl['newsletter_voucher_code'] ?? 'UUDAI100K') ?></div>
      <div class="voucher-amount" id="prevAmount"><?= number_format(intval($nl['newsletter_voucher_amount'] ?? 100000), 0, ',', '.') ?>₫</div>
    </div>
  </div>
</div>

<script>
// Live preview
document.querySelector('[name=newsletter_title]').addEventListener('input', function(){ document.getElementById('prevTitle').textContent = this.value; });
document.querySelector('[name=newsletter_subtitle]').addEventListener('input', function(){ document.getElementById('prevSubtitle').textContent = this.value; });
document.querySelector('[name=newsletter_voucher_code]').addEventListener('input', function(){ document.getElementById('prevCode').textContent = this.value.toUpperCase(); });
document.querySelector('[name=newsletter_voucher_amount]').addEventListener('input', function(){
  var v = parseInt(this.value)||0;
  document.getElementById('prevAmount').textContent = v.toLocaleString('vi-VN') + '₫';
});
</script>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
