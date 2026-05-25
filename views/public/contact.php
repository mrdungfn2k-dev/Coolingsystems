<?php
// Load contact config from DB
$_contactCfg = [];
foreach(['contact_hotline','contact_email','contact_address','contact_hours','site_phone'] as $_ck) {
    $_cr = dbGet("SELECT value FROM system_config WHERE key=?", [$_ck]);
    $_contactCfg[$_ck] = $_cr['value'] ?? '';
}
$_hotline = $_contactCfg['contact_hotline'] ?: ($_contactCfg['site_phone'] ? preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1 $2 $3', $_contactCfg['site_phone']) : '<?= htmlspecialchars($_hotline) ?>');
$_hotlineClean = preg_replace('/[^0-9]/', '', $_hotline);
$_contactEmail = $_contactCfg['contact_email'] ?: '<?= htmlspecialchars($_contactEmail) ?>';
$_contactAddr = $_contactCfg['contact_address'] ?: '<?= htmlspecialchars($_contactAddr) ?>';
$_contactHours = $_contactCfg['contact_hours'] ?: "<?= nl2br(htmlspecialchars($_contactHours)) ?>\nChủ nhật: 9:00 — 16:00";
?>
<?php require __DIR__ . '/../partials/head.php'; ?>
<section class="block">
  <div class="wrap">
    <div class="breadcrumb">
      <a href="/">Trang chủ</a><span class="sep">›</span>
      <span>Liên hệ</span>
    </div>
  </div>
</section>

<section class="block" style="padding-top:0">
  <div class="wrap">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px">
      <!-- Contact Info -->
      <div>
        <div class="sec-card">
          <div class="sec-head">
            <div class="title"><span class="bar"></span><h1 style="font-size:22px">Thông tin liên hệ</h1></div>
          </div>
          <div class="panel-body" style="padding:24px">
            <div style="display:flex;flex-direction:column;gap:20px">
              <div style="display:flex;align-items:flex-start;gap:16px">
                <div style="width:44px;height:44px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.64A2 2 0 012 .18L5 0a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L5.67 7.9a16 16 0 006.42 6.42l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.5z"/></svg>
                </div>
                <div>
                  <div style="font-weight:700;color:var(--navy);margin-bottom:4px">Hotline hỗ trợ 24/7</div>
                  <a href="tel:<?= $_hotlineClean ?>" style="font-size:18px;font-weight:800;color:var(--gold-warm)"><?= htmlspecialchars($_hotline) ?></a>
                </div>
              </div>
              <div style="display:flex;align-items:flex-start;gap:16px">
                <div style="width:44px;height:44px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div>
                  <div style="font-weight:700;color:var(--navy);margin-bottom:4px">Email</div>
                  <a href="mailto:<?= htmlspecialchars($_contactEmail) ?>" style="color:var(--ink-2)"><?= htmlspecialchars($_contactEmail) ?></a>
                </div>
              </div>
              <div style="display:flex;align-items:flex-start;gap:16px">
                <div style="width:44px;height:44px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                  <div style="font-weight:700;color:var(--navy);margin-bottom:4px">Địa chỉ</div>
                  <div style="color:var(--ink-2);line-height:1.6"><?= htmlspecialchars($_contactAddr) ?></div>
                </div>
              </div>
              <div style="display:flex;align-items:flex-start;gap:16px">
                <div style="width:44px;height:44px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                  <div style="font-weight:700;color:var(--navy);margin-bottom:4px">Giờ làm việc</div>
                  <div style="color:var(--ink-2);line-height:1.6"><?= nl2br(htmlspecialchars($_contactHours)) ?><br>Chủ nhật: 9:00 — 16:00</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div>
        <div class="sec-card">
          <div class="sec-head">
            <div class="title"><span class="bar"></span><h2 style="font-size:20px">Gửi tin nhắn cho chúng tôi</h2></div>
          </div>
          <div class="panel-body" style="padding:24px">
            <?php if (!empty($success)): ?>
              <div class="alert alert-success" style="margin-bottom:20px;padding:16px;background:#d4edda;border-radius:8px;color:#155724;border:1px solid #c3e6cb">
                <strong>✅ Cảm ơn bạn!</strong> Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ lại trong 24 giờ.
              </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger" style="margin-bottom:20px;padding:16px;background:#f8d7da;border-radius:8px;color:#721c24;border:1px solid #f5c6cb">
                <?php foreach ($errors as $err): ?><div>⚠️ <?= e($err) ?></div><?php endforeach; ?>
              </div>
            <?php endif; ?>

            <form method="POST" action="/contact" id="contactForm">
              <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
              <div class="form-group" style="margin-bottom:16px">
                <label for="contact_name" style="display:block;font-weight:700;margin-bottom:6px;color:var(--navy);font-size:13px">Họ và tên <span style="color:#e74c3c">*</span></label>
                <input type="text" id="contact_name" name="name" value="<?= e($_POST['name'] ?? '') ?>" 
                  placeholder="Nhập họ và tên của bạn" required minlength="2" maxlength="50" pattern="[a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềểễệỉịọỏốồổỗộớờởỡợụủứừửữựỳỵỷỹ ]{2,50}" title="Họ và tên chỉ được chứa chữ cái và khoảng trắng"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:6px;font-size:14px;transition:border-color 0.2s"
                  onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--line)'">
              </div>
              <div class="form-group" style="margin-bottom:16px">
                <label for="contact_email" style="display:block;font-weight:700;margin-bottom:6px;color:var(--navy);font-size:13px">Email <span style="color:#e74c3c">*</span></label>
                <input type="email" id="contact_email" name="email" value="<?= e($_POST['email'] ?? '') ?>"
                  placeholder="example@email.com" required
                  pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}"
                  title="Vui lòng nhập email đúng định dạng (ví dụ: ten@gmail.com)"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:6px;font-size:14px;transition:border-color 0.2s"
                  onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--line)'">
              </div>
              <div class="form-group" style="margin-bottom:16px">
                <label for="contact_phone" style="display:block;font-weight:700;margin-bottom:6px;color:var(--navy);font-size:13px">Số điện thoại <span style="color:#e74c3c">*</span></label>
                <input type="tel" id="contact_phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"
                  placeholder="0xxx xxx xxx" required pattern="0[0-9]{9}" maxlength="10" minlength="10" title="Số điện thoại phải bắt đầu từ 0 và có đúng 10 số"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:6px;font-size:14px;transition:border-color 0.2s"
                  onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--line)'">
                <div style="font-size:11px;color:var(--ink-4);margin-top:4px">Nhập đúng 10 chữ số</div>
              </div>
              <div class="form-group" style="margin-bottom:16px">
                <label for="contact_subject" style="display:block;font-weight:700;margin-bottom:6px;color:var(--navy);font-size:13px">Chủ đề</label>
                <select id="contact_subject" name="subject"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:6px;font-size:14px">
                  <option value="product">Hỏi về sản phẩm</option>
                  <option value="order">Đơn hàng / Vận chuyển</option>
                  <option value="warranty">Bảo hành / Đổi trả</option>
                  <option value="partner">Hợp tác / Đại lý</option>
                  <option value="other">Khác</option>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:20px">
                <label for="contact_message" style="display:block;font-weight:700;margin-bottom:6px;color:var(--navy);font-size:13px">Nội dung <span style="color:#e74c3c">*</span></label>
                <textarea id="contact_message" name="message" rows="5" required minlength="10" maxlength="100"
                  placeholder="Mô tả chi tiết câu hỏi hoặc yêu cầu của bạn..."
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--line);border-radius:6px;font-size:14px;resize:vertical;transition:border-color 0.2s"
                  onfocus="this.style.borderColor='var(--navy)'" onblur="this.style.borderColor='var(--line)'"><?= e($_POST['message'] ?? '') ?></textarea>
              </div>
              <button type="submit" class="btn btn-navy" style="width:100%;padding:12px;font-size:15px;font-weight:700">
                Gửi tin nhắn →
              </button>
            </form>

            <script>
(function(){
  var nameF = document.getElementById('contact_name');
  var emailF = document.getElementById('contact_email');
  var phoneF = document.getElementById('contact_phone');
  var msgF = document.getElementById('contact_message');
  var form = document.getElementById('contactForm');

  // === Helper: show/hide inline error ===
  function showErr(el, msg) {
    var errId = el.id + '_err';
    var existing = document.getElementById(errId);
    if (!existing) {
      existing = document.createElement('div');
      existing.id = errId;
      existing.style.cssText = 'color:#e74c3c;font-size:12px;margin-top:4px;font-weight:600';
      el.parentNode.appendChild(existing);
    }
    existing.textContent = '⚠ ' + msg;
    existing.style.display = 'block';
    el.style.borderColor = '#e74c3c';
  }
  function hideErr(el) {
    var errId = el.id + '_err';
    var existing = document.getElementById(errId);
    if (existing) existing.style.display = 'none';
    el.style.borderColor = '';
  }

  // === 1. HỌ TÊN: Chặn ký tự đặc biệt ===
  nameF.addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-Z\u00C0-\u024F\u1E00-\u1EFF ]/g, '');
    if (this.value.length >= 2) hideErr(this);
  });
  nameF.addEventListener('blur', function() {
    if (this.value.trim().length < 2 && this.value.trim().length > 0) {
      showErr(this, 'Họ và tên phải có ít nhất 2 ký tự');
    }
  });

  // === 2. EMAIL: Validate realtime khi blur ===
  emailF.addEventListener('input', function() {
    // Chỉ cho nhập ký tự hợp lệ cho email
    this.value = this.value.replace(/[^a-zA-Z0-9@._+\-]/g, '');
  });
  emailF.addEventListener('blur', function() {
    var val = this.value.trim();
    if (val.length === 0) return;
    if (!/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val)) {
      showErr(this, 'Email không đúng định dạng (ví dụ: ten@gmail.com)');
    } else {
      var domain = val.split('@')[1].toLowerCase();
      var invalidDomains = ['gmil.com', 'gamil.com', 'yaho.com', 'yahu.com', 'hotmal.com', 'outlok.com', 'gml.com', 'gma.com', 'gmai.com', 'gmail.con', 'gmail.co'];
      if (invalidDomains.indexOf(domain) !== -1) {
        showErr(this, 'Email có vẻ bị sai chính tả tên miền (ví dụ: gmail.com)');
      } else {
        hideErr(this);
      }
    }
  });

  // === 3. SỐ ĐIỆN THOẠI: Chặn chữ, chỉ cho nhập số, bắt đầu từ 0, đúng 10 số ===
  phoneF.addEventListener('keypress', function(e) {
    // Chỉ cho nhập số 0-9
    if (e.key.length === 1 && !/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  });
  phoneF.addEventListener('input', function() {
    // Xóa mọi ký tự không phải số
    this.value = this.value.replace(/[^0-9]/g, '');
    // Giới hạn 10 số
    if (this.value.length > 10) this.value = this.value.substring(0, 10);
    
    var val = this.value;
    // Validate realtime
    if (val.length > 0 && val[0] !== '0') {
      showErr(this, 'Số điện thoại phải bắt đầu bằng số 0');
    } else if (val.length > 0 && val.length < 10) {
      showErr(this, 'Số điện thoại phải đủ 10 chữ số (đang nhập ' + val.length + '/10)');
    } else if (val.length === 10 && val[0] === '0') {
      hideErr(this);
    } else if (val.length === 0) {
      hideErr(this);
    }
  });
  phoneF.addEventListener('blur', function() {
    var val = this.value.trim();
    if (val.length === 0) return;
    if (!/^0[0-9]{9}$/.test(val)) {
      if (val[0] !== '0') {
        showErr(this, 'Số điện thoại phải bắt đầu bằng số 0');
      } else {
        showErr(this, 'Số điện thoại phải đủ 10 chữ số (đang có ' + val.length + ' số)');
      }
    } else {
      hideErr(this);
    }
  });
  // Chặn paste ký tự không hợp lệ
  phoneF.addEventListener('paste', function(e) {
    e.preventDefault();
    var pasted = (e.clipboardData || window.clipboardData).getData('text');
    var cleaned = pasted.replace(/[^0-9]/g, '').substring(0, 10);
    this.value = cleaned;
    this.dispatchEvent(new Event('input'));
  });

  // === 4. NỘI DUNG: Giới hạn 100 ký tự với counter ===
  var counter = document.createElement('div');
  counter.style.cssText = 'font-size:11px;color:var(--ink-4);margin-top:4px;text-align:right';
  counter.id = 'msgCounter';
  msgF.parentNode.appendChild(counter);
  function updateCounter() {
    var len = msgF.value.length;
    counter.textContent = len + '/100 ký tự';
    counter.style.color = len >= 100 ? '#e74c3c' : 'var(--ink-4)';
  }
  msgF.addEventListener('input', function() {
    if (this.value.length > 100) this.value = this.value.substring(0, 100);
    updateCounter();
    if (this.value.length >= 10) hideErr(this);
  });
  updateCounter();

  // === 5. FORM SUBMIT: Final validation ===
  form.addEventListener('submit', function(e) {
    var name = nameF.value.trim();
    var email = emailF.value.trim();
    var phone = phoneF.value.trim();
    var message = msgF.value.trim();
    var errs = [];
    var firstErr = null;

    if (name.length < 2) {
      errs.push('Họ và tên phải có ít nhất 2 ký tự.');
      showErr(nameF, 'Họ và tên phải có ít nhất 2 ký tự');
      if (!firstErr) firstErr = nameF;
    }
    if (!/^[a-zA-Z\u00C0-\u024F\u1E00-\u1EFF ]+$/.test(name) && name.length > 0) {
      errs.push('Họ và tên không được chứa ký tự đặc biệt.');
      showErr(nameF, 'Họ và tên không được chứa ký tự đặc biệt');
      if (!firstErr) firstErr = nameF;
    }
    if (!/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(email)) {
      errs.push('Email không đúng định dạng (ví dụ: ten@gmail.com).');
      showErr(emailF, 'Email không đúng định dạng (ví dụ: ten@gmail.com)');
      if (!firstErr) firstErr = emailF;
    } else {
      var domain = email.split('@')[1].toLowerCase();
      var invalidDomains = ['gmil.com', 'gamil.com', 'yaho.com', 'yahu.com', 'hotmal.com', 'outlok.com', 'gml.com', 'gma.com', 'gmai.com', 'gmail.con', 'gmail.co'];
      if (invalidDomains.indexOf(domain) !== -1) {
        errs.push('Email có vẻ bị sai chính tả tên miền (ví dụ: gmil.com -> gmail.com).');
        showErr(emailF, 'Email có vẻ bị sai chính tả tên miền (ví dụ: gmail.com)');
        if (!firstErr) firstErr = emailF;
      }
    }
    if (!/^0[0-9]{9}$/.test(phone)) {
      var phoneMsg = 'Số điện thoại phải bắt đầu từ 0 và có đúng 10 chữ số.';
      errs.push(phoneMsg);
      if (phone.length > 0 && phone[0] !== '0') {
        showErr(phoneF, 'Số điện thoại phải bắt đầu bằng số 0');
      } else {
        showErr(phoneF, 'Số điện thoại phải đủ 10 chữ số');
      }
      if (!firstErr) firstErr = phoneF;
    }
    if (message.length < 10) {
      errs.push('Nội dung phải có ít nhất 10 ký tự.');
      showErr(msgF, 'Nội dung phải có ít nhất 10 ký tự');
      if (!firstErr) firstErr = msgF;
    }
    if (message.length > 100) {
      errs.push('Nội dung tối đa 100 ký tự.');
      showErr(msgF, 'Nội dung tối đa 100 ký tự');
      if (!firstErr) firstErr = msgF;
    }

    if (errs.length) {
      e.preventDefault();
      if (firstErr) firstErr.focus();
      // Also show toast
      if (typeof coolToastShow === 'function') {
        coolToastShow(errs.join('<br>'), '⚠️');
      }
    }
  });
})();
</script>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<script>



<script>
// Client-side email validation
document.addEventListener('DOMContentLoaded', function() {
    var emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            var val = this.value.trim();
            var errEl = this.parentNode.querySelector('.email-err-msg');
            if (!errEl) {
                errEl = document.createElement('span');
                errEl.className = 'email-err-msg';
                errEl.style.cssText = 'color:#e74c3c;font-size:11px;display:block;margin-top:4px';
                this.parentNode.appendChild(errEl);
            }
            if (val && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(val)) {
                errEl.textContent = 'Email không đúng định dạng (ví dụ: ten@gmail.com)';
                errEl.style.display = 'block';
                this.style.borderColor = '#e74c3c';
            } else {
                errEl.style.display = 'none';
                this.style.borderColor = '';
            }
        });
        // Also validate on form submit
        var form = emailInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                var val = emailInput.value.trim();
                if (val && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(val)) {
                    e.preventDefault();
                    alert('Vui lòng nhập email đúng định dạng (ví dụ: ten@gmail.com)');
                    emailInput.focus();
                    emailInput.style.borderColor = '#e74c3c';
                }
            });
        }
    }
});
function validateContactEmail(input) {
    var val = input.value.trim();
    var ok = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(val);
    if (val && !ok) {
        input.style.borderColor = '#e74c3c';
    } else {
        input.style.borderColor = '';
    }
}
</script>

<?php require __DIR__ . '/../partials/foot.php'; ?>
