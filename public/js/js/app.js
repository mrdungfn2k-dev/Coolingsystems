'use strict';

(function () {
  // Auto-hide flash messages after 4 seconds
  setTimeout(() => {
    document.querySelectorAll('.flash').forEach((el) => {
      el.style.transition = 'opacity 0.4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    });
  }, 4000);

  // Smooth scroll for in-page links
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href');
      if (href.length > 1) {
        const t = document.querySelector(href);
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
      }
    });
  });

  // Tab switcher
  document.querySelectorAll('.sec-tabs').forEach((tabs) => {
    tabs.querySelectorAll('button').forEach((btn) => {
      btn.addEventListener('click', () => {
        tabs.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.dataset.target;
        if (target) {
          document.querySelectorAll(`[data-tab]`).forEach((el) => {
            el.style.display = el.dataset.tab === target ? '' : 'none';
          });
        }
      });
    });
  });

  // Vehicle selector — cascading brand → model → year
  const brandSel = document.getElementById('vs-brand');
  const modelSel = document.getElementById('vs-model');
  const yearSel = document.getElementById('vs-year');
  const submitBtn = document.getElementById('vs-submit');
  const stepEls = document.querySelectorAll('.vs-progress .step');

  function markStep(i, done) {
    if (stepEls[i]) stepEls[i].classList.toggle('done', !!done);
  }

  if (brandSel && modelSel) {
    brandSel.addEventListener('change', async () => {
      markStep(0, !!brandSel.value);
      modelSel.innerHTML = '<option value="">— Đang tải —</option>';
      modelSel.disabled = true;
      yearSel.innerHTML = '<option value="">— Chọn dòng xe trước —</option>';
      yearSel.disabled = true;
      markStep(1, false); markStep(2, false);
      if (!brandSel.value) {
        modelSel.innerHTML = '<option value="">— Chọn hãng trước —</option>';
        return;
      }
      try {
        const r = await fetch(`/api/models?brand=${brandSel.value}`);
        const data = await r.json();
        modelSel.innerHTML = '<option value="">— Chọn dòng xe —</option>';
        data.models.forEach((m) => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = m.name;
          opt.dataset.yearFrom = m.year_from;
          opt.dataset.yearTo = m.year_to || (new Date().getFullYear());
          modelSel.appendChild(opt);
        });
        modelSel.disabled = false;
      } catch (e) {
        modelSel.innerHTML = '<option value="">— Lỗi tải —</option>';
      }
    });

    modelSel.addEventListener('change', () => {
      markStep(1, !!modelSel.value);
      yearSel.innerHTML = '<option value="">— Chọn năm —</option>';
      markStep(2, false);
      if (!modelSel.value) { yearSel.disabled = true; return; }
      const opt = modelSel.options[modelSel.selectedIndex];
      const yfrom = parseInt(opt.dataset.yearFrom, 10);
      const yto = parseInt(opt.dataset.yearTo, 10);
      for (let y = yto; y >= yfrom; y--) {
        const o = document.createElement('option');
        o.value = y; o.textContent = y;
        yearSel.appendChild(o);
      }
      yearSel.disabled = false;
    });

    yearSel.addEventListener('change', () => {
      markStep(2, !!yearSel.value);
      if (submitBtn) submitBtn.disabled = !(brandSel.value && modelSel.value && yearSel.value);
    });
  }

  // Quantity input
  document.querySelectorAll('.qty-input').forEach((box) => {
    const input = box.querySelector('input');
    box.querySelector('.qty-dec')?.addEventListener('click', () => {
      input.value = Math.max(1, parseInt(input.value, 10) - 1);
    });
    box.querySelector('.qty-inc')?.addEventListener('click', () => {
      input.value = parseInt(input.value, 10) + 1;
    });
  });
})();
