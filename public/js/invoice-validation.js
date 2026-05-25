// ═══ Invoice Form Validation & Dynamic Fields ═══

// Bank account length rules per bank
var BANK_RULES = {
  "Vietcombank":{min:13,max:13},"Techcombank":{min:14,max:14},"BIDV":{min:14,max:14},
  "VietinBank":{min:12,max:14},"MB Bank":{min:13,max:13},"ACB":{min:12,max:13},
  "Sacombank":{min:12,max:12},"VPBank":{min:12,max:12},"TPBank":{min:11,max:11},
  "HDBank":{min:13,max:13},"SHB":{min:13,max:13},"SeABank":{min:13,max:13},
  "OCB":{min:12,max:12},"LienVietPostBank":{min:13,max:13},"MSB":{min:14,max:14},
  "Eximbank":{min:13,max:13},"VIB":{min:12,max:12},"ABBank":{min:14,max:14},
  "BacABank":{min:12,max:12},"NCB":{min:12,max:12},"PVcomBank":{min:15,max:15},
  "SCB":{min:12,max:12},"CIMB":{min:10,max:10},"UOB":{min:10,max:10},
  "BanVietBank":{min:12,max:12},"Agribank":{min:13,max:13}
};

function setupInvoiceForm(formId, opts) {
  opts = opts || {};
  var form = document.getElementById(formId);
  if (!form) return;

  // ── Dynamic fields based on invoice_type ──
  var radios = form.querySelectorAll('input[name="invoice_type"]');
  var personalFields = form.querySelector('.inv-personal-fields');
  var businessFields = form.querySelector('.inv-business-fields');

  function toggleType() {
    var val = form.querySelector('input[name="invoice_type"]:checked');
    if (!val) return;
    if (personalFields) personalFields.style.display = val.value === 'personal' ? '' : 'none';
    if (businessFields) businessFields.style.display = val.value === 'business' ? '' : 'none';
  }
  radios.forEach(function(r) { r.addEventListener('change', toggleType); });
  toggleType();

  // ── Email validation: must contain @ ──
  var emailEl = form.querySelector('input[name="inv_email"]');
  if (emailEl) {
    emailEl.addEventListener('blur', function() {
      var v = this.value.trim();
      if (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
        showFieldError(this, 'Email không đúng định dạng (ví dụ: ten@gmail.com)');
      } else { clearFieldError(this); }
    });
  }

  // ── CCCD: exactly 12 digits ──
  var cccdEl = form.querySelector('input[name="id_number"]');
  if (cccdEl) {
    cccdEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);
    });
    cccdEl.addEventListener('blur', function() {
      var v = this.value.trim();
      if (v && v.length !== 12) {
        showFieldError(this, 'Số CCCD/CMND phải đủ 12 số');
      } else { clearFieldError(this); }
    });
  }

  // ── Passport: 1 uppercase + 7 digits ──
  var passportEl = form.querySelector('input[name="passport"]');
  if (passportEl) {
    passportEl.addEventListener('input', function() {
      var v = this.value.toUpperCase();
      // First char must be letter, rest digits
      if (v.length > 0) v = v[0].replace(/[^A-Z]/g, '') + v.slice(1).replace(/[^0-9]/g, '');
      this.value = v.slice(0, 8);
    });
    passportEl.addEventListener('blur', function() {
      var v = this.value.trim();
      if (v && !/^[A-Z][0-9]{7}$/.test(v)) {
        showFieldError(this, 'Số hộ chiếu: 1 chữ in hoa + 7 số (VD: B1234567)');
      } else { clearFieldError(this); }
    });
  }

  // ── Phone: 10 digits, start 01-09 ──
  var phoneEl = form.querySelector('input[name="inv_phone"]');
  if (phoneEl) {
    phoneEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
      if (this.value.length > 0 && this.value[0] !== '0') this.value = '';
      if (this.value.length > 1 && !/^0[1-9]/.test(this.value)) this.value = this.value[0];
    });
    phoneEl.addEventListener('blur', function() {
      var v = this.value.trim();
      if (v && v.length !== 10) {
        showFieldError(this, 'Số điện thoại phải đủ 10 số');
      } else { clearFieldError(this); }
    });
  }

  // ── Bank account: 8-15 digits, per-bank rules ──
  var bankSelect = form.querySelector('select[name="bank_name"]');
  var bankAccEl = form.querySelector('input[name="bank_account"]');
  if (bankAccEl) {
    bankAccEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
    });
    bankAccEl.addEventListener('blur', function() {
      var v = this.value.trim();
      if (!v) { clearFieldError(this); return; }
      var bank = bankSelect ? bankSelect.value : '';
      var rule = BANK_RULES[bank] || {min:8,max:15};
      if (v.length < rule.min || v.length > rule.max) {
        var msg = bank ? bank + ': ' + rule.min + (rule.min===rule.max ? '' : '-'+rule.max) + ' số' : '8-15 số';
        showFieldError(this, 'Số tài khoản phải ' + msg);
      } else { clearFieldError(this); }
    });
  }
  if (bankSelect) {
    bankSelect.addEventListener('change', function() {
      if (bankAccEl && bankAccEl.value) bankAccEl.dispatchEvent(new Event('blur'));
    });
  }

  // ── Tax code: digits only ──
  // Tax code: 10 digits (business) or 10-3 format (dependent unit)
  var taxEl = form.querySelector('input[name="tax_code"]');
  if (taxEl) {
    taxEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9-]/g, '');
      // Auto-format: after 10 digits, add dash
      var digits = this.value.replace(/-/g, '');
      if (digits.length > 10) {
        this.value = digits.slice(0,10) + '-' + digits.slice(10,13);
      } else {
        this.value = digits.slice(0,10);
      }
    });
    taxEl.addEventListener('blur', function() {
      var v = this.value.trim().replace(/-/g, '');
      if (v && v.length !== 10 && v.length !== 13) {
        showFieldError(this, 'MST phải 10 số (doanh nghiệp) hoặc 13 số (đơn vị phụ thuộc)');
      } else { clearFieldError(this); }
    });
  }
  // Same for tax_code_biz (business form)
  var taxBizEl = form.querySelector('input[name="tax_code_biz"]');
  if (taxBizEl) {
    taxBizEl.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9-]/g, '');
      var digits = this.value.replace(/-/g, '');
      if (digits.length > 10) {
        this.value = digits.slice(0,10) + '-' + digits.slice(10,13);
      } else {
        this.value = digits.slice(0,10);
      }
    });
    taxBizEl.addEventListener('blur', function() {
      var v = this.value.trim().replace(/-/g, '');
      if (v && v.length !== 10 && v.length !== 13) {
        showFieldError(this, 'MST phải 10 số (doanh nghiệp) hoặc 13 số (đơn vị phụ thuộc)');
      } else { clearFieldError(this); }
    });
  }
}

function showFieldError(el, msg) {
  clearFieldError(el);
  el.style.borderColor = '#e74c3c';
  var span = document.createElement('div');
  span.className = 'field-err';
  span.style.cssText = 'color:#e74c3c;font-size:11px;margin-top:2px';
  span.textContent = msg;
  el.parentNode.appendChild(span);
}
function clearFieldError(el) {
  el.style.borderColor = '';
  var err = el.parentNode.querySelector('.field-err');
  if (err) err.remove();
}

// Validate form before submit
function validateInvoiceForm(formId) {
  var form = document.getElementById(formId);
  if (!form) return true;
  var valid = true;

  var email = form.querySelector('input[name="inv_email"]');
  if (email && email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    showFieldError(email, 'Email không đúng định dạng'); valid = false;
  }
  var cccd = form.querySelector('input[name="id_number"]');
  if (cccd && cccd.value.trim() && cccd.value.trim().length !== 12) {
    showFieldError(cccd, 'CCCD phải đủ 12 số'); valid = false;
  }
  var pp = form.querySelector('input[name="passport"]');
  if (pp && pp.value.trim() && !/^[A-Z][0-9]{7}$/.test(pp.value.trim())) {
    showFieldError(pp, 'Hộ chiếu: 1 chữ + 7 số'); valid = false;
  }
  var ph = form.querySelector('input[name="inv_phone"]');
  if (ph && ph.value.trim() && ph.value.trim().length !== 10) {
    showFieldError(ph, 'SĐT phải đủ 10 số'); valid = false;
  }
  var ba = form.querySelector('input[name="bank_account"]');
  if (ba && ba.value.trim()) {
    var bank = (form.querySelector('select[name="bank_name"]') || {}).value || '';
    var rule = BANK_RULES[bank] || {min:8,max:15};
    if (ba.value.trim().length < rule.min || ba.value.trim().length > rule.max) {
      showFieldError(ba, 'Số TK không đúng'); valid = false;
    }
  }
  return valid;
}
