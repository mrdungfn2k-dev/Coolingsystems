/**
 * CoolingSystem Advanced Input Validation v2
 * - Phone: must start with 0[1-9], exactly 10 digits, blocks invalid chars in realtime
 * - Email: validates format + checks common domain typos
 */
var CoolingValidation = {
  // Common valid email domains
  validDomains: [
    'gmail.com','yahoo.com','yahoo.com.vn','hotmail.com','outlook.com',
    'icloud.com','mail.com','protonmail.com','zoho.com','aol.com',
    'yandex.com','live.com','msn.com','me.com','mac.com',
    'fpt.edu.vn','hust.edu.vn','vnu.edu.vn','edu.vn',
    'fpt.com.vn','viettel.com.vn','vnpt.vn','mobifone.vn'
  ],
  // Common typos mapping
  domainTypos: {
    'gmai.com':'gmail.com', 'gmial.com':'gmail.com', 'gmal.com':'gmail.com',
    'gamil.com':'gmail.com', 'gnail.com':'gmail.com', 'gmail.co':'gmail.com',
    'gmail.cm':'gmail.com', 'gmail.om':'gmail.com', 'gmail.con':'gmail.com',
    'gmail.coom':'gmail.com', 'gmailcom':'gmail.com', 'gmail.vom':'gmail.com',
    'yaho.com':'yahoo.com', 'yahooo.com':'yahoo.com', 'yahoo.co':'yahoo.com',
    'yahoo.cm':'yahoo.com', 'yahoo.con':'yahoo.com',
    'hotmai.com':'hotmail.com', 'hotmal.com':'hotmail.com', 'hotmial.com':'hotmail.com',
    'hotmail.co':'hotmail.com', 'hotmail.cm':'hotmail.com', 'hotmail.con':'hotmail.com',
    'outlok.com':'outlook.com', 'outloo.com':'outlook.com', 'outlool.com':'outlook.com',
    'outlook.co':'outlook.com', 'outlook.cm':'outlook.com'
  },

  // Setup phone field with strict blocking
  setupPhone: function(inputEl, errorEl) {
    if (!inputEl) return;
    // Block paste of invalid content
    inputEl.addEventListener('paste', function(e) {
      e.preventDefault();
      var paste = (e.clipboardData || window.clipboardData).getData('text');
      var clean = paste.replace(/[^0-9]/g, '').slice(0, 10);
      // If first char is not 0, block entire paste
      if (clean.length > 0 && clean[0] !== '0') {
        errorEl.textContent = 'SĐT phải bắt đầu bằng số 0. VD: 0912345678';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }
      // If second char is 0, block
      if (clean.length > 1 && clean[1] === '0') {
        clean = clean[0]; // keep only the 0
        errorEl.textContent = 'Sau số 0 phải là 1-9. VD: 09, 03, 07...';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
      }
      this.value = clean;
      this.dispatchEvent(new Event('input'));
    });

    inputEl.addEventListener('keydown', function(e) {
      // Allow control keys
      if (e.key === 'Backspace' || e.key === 'Delete' || e.key === 'Tab' ||
          e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'Home' || e.key === 'End' ||
          e.ctrlKey || e.metaKey) return;

      // Block non-digit
      if (!/^[0-9]$/.test(e.key)) {
        e.preventDefault();
        errorEl.textContent = 'Chỉ được nhập số (0-9)';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }

      var val = this.value;
      var pos = this.selectionStart;
      var newVal = val.slice(0, pos) + e.key + val.slice(this.selectionEnd);

      // Block if over 10 digits
      if (newVal.length > 10) { e.preventDefault(); return; }

      // First digit must be 0
      if (newVal.length >= 1 && newVal[0] !== '0') {
        e.preventDefault();
        errorEl.textContent = 'SĐT phải bắt đầu bằng số 0. VD: 0912345678';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }

      // Second digit must be 1-9
      if (newVal.length >= 2 && (newVal[1] === '0' || !/[1-9]/.test(newVal[1]))) {
        e.preventDefault();
        errorEl.textContent = 'Sau số 0 phải là 1-9. VD: 09, 03, 07...';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }
    });

    inputEl.addEventListener('input', function() {
      // Clean value
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value.length > 10) this.value = this.value.slice(0, 10);

      // Re-enforce first digit = 0
      if (this.value.length > 0 && this.value[0] !== '0') {
        this.value = '';
        errorEl.textContent = 'SĐT phải bắt đầu bằng số 0';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }
      // Re-enforce second digit = 1-9
      if (this.value.length > 1 && !/[1-9]/.test(this.value[1])) {
        this.value = this.value[0];
        errorEl.textContent = 'Sau số 0 phải là 1-9';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }

      var ok = /^0[1-9][0-9]{8}$/.test(this.value);
      if (this.value.length === 0) {
        errorEl.style.display = 'none';
        inputEl.style.borderColor = '';
      } else if (this.value.length < 10) {
        errorEl.textContent = 'SĐT cần đủ 10 số. Còn thiếu ' + (10 - this.value.length) + ' số';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = '#e67e22';
      } else if (ok) {
        errorEl.textContent = '✓ Số điện thoại hợp lệ';
        errorEl.style.color = '#27ae60';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = '#27ae60';
        setTimeout(function(){ errorEl.style.display='none'; errorEl.style.color='red'; }, 1500);
      } else {
        errorEl.textContent = 'SĐT không hợp lệ. Phải bắt đầu từ 01-09 và đủ 10 số';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
      }
    });
  },

  // Setup email field with domain typo detection
  setupEmail: function(inputEl, errorEl) {
    if (!inputEl) return;
    var self = this;

    inputEl.addEventListener('input', function() {
      var val = this.value.trim().toLowerCase();
      if (val.length < 4) { errorEl.style.display = 'none'; inputEl.style.borderColor = ''; return; }

      // Basic format check
      var basicOk = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val);

      if (val.indexOf('@') === -1) {
        if (val.length > 5) {
          errorEl.textContent = 'Email phải có dấu @. VD: ten@gmail.com';
          errorEl.style.display = 'block';
          inputEl.style.borderColor = 'red';
        }
        return;
      }

      var parts = val.split('@');
      if (parts.length !== 2 || parts[0].length === 0) {
        errorEl.textContent = 'Phần trước @ không được để trống';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }

      var domain = parts[1];
      if (domain.length === 0) {
        errorEl.textContent = 'Vui lòng nhập tên miền email. VD: gmail.com';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = '#e67e22';
        return;
      }

      // Check for common domain typos
      if (self.domainTypos[domain]) {
        var suggest = self.domainTypos[domain];
        errorEl.innerHTML = '⚠ Có phải bạn muốn nhập <b>' + parts[0] + '@' + suggest + '</b>? <a href="#" onclick="document.getElementById(\'' + inputEl.id + '\').value=\'' + parts[0] + '@' + suggest + '\';document.getElementById(\'' + inputEl.id + '\').dispatchEvent(new Event(\'input\'));return false;" style="color:#2980b9;text-decoration:underline">Sửa ngay</a>';
        errorEl.style.color = '#e67e22';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = '#e67e22';
        return;
      }

      if (!basicOk) {
        if (domain.indexOf('.') === -1) {
          errorEl.textContent = 'Tên miền thiếu phần mở rộng. VD: gmail.com (không phải gmail)';
        } else {
          errorEl.textContent = 'Email không đúng định dạng. VD: ten@gmail.com';
        }
        errorEl.style.color = 'red';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
        return;
      }

      // Valid format - check domain exists in common list (warning only)
      errorEl.textContent = '✓ Email hợp lệ';
      errorEl.style.color = '#27ae60';
      errorEl.style.display = 'block';
      inputEl.style.borderColor = '#27ae60';
      setTimeout(function(){ errorEl.style.display='none'; errorEl.style.color='red'; }, 1500);
    });

    // Also validate on blur for final check
    inputEl.addEventListener('blur', function() {
      var val = this.value.trim().toLowerCase();
      if (val.length === 0) { errorEl.style.display = 'none'; inputEl.style.borderColor = ''; return; }
      var basicOk = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(val);
      if (!basicOk) {
        errorEl.textContent = 'Email không đúng định dạng. VD: ten@gmail.com';
        errorEl.style.color = 'red';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = 'red';
      }
      // Check for domain typo on blur too
      var parts = val.split('@');
      if (parts.length === 2 && self.domainTypos[parts[1]]) {
        var suggest = self.domainTypos[parts[1]];
        errorEl.innerHTML = '⚠ Có phải bạn muốn nhập <b>' + parts[0] + '@' + suggest + '</b>? <a href="#" onclick="document.getElementById(\'' + inputEl.id + '\').value=\'' + parts[0] + '@' + suggest + '\';document.getElementById(\'' + inputEl.id + '\').dispatchEvent(new Event(\'input\'));return false;" style="color:#2980b9;text-decoration:underline">Sửa ngay</a>';
        errorEl.style.color = '#e67e22';
        errorEl.style.display = 'block';
        inputEl.style.borderColor = '#e67e22';
      }
    });
  },

  // Setup name field (block digits)
  setupName: function(inputEl, errorEl) {
    if (!inputEl) return;
    inputEl.addEventListener('input', function() {
      this.value = this.value.replace(/[0-9]/g, '');
      var ok = this.value.trim().length >= 2;
      errorEl.style.display = ok ? 'none' : 'block';
      inputEl.style.borderColor = ok ? '' : (this.value.length > 0 ? 'red' : '');
    });
  }
};
