/* ==========================================================================
   COOLING SYSTEMS MOBILE APP - CORE SPA LOGIC & DATA ENGINE
   ========================================================================== */

(function () {
  'use strict';

  // State Management
  const state = {
    currentScreen: 'home',
    cart: [
      { id: 2682, sku: 'BW935F012', name: 'QUẠT DÀN LẠNH TOYOTA INNOVA(REAR) 2008', price: 752000, qty: 1, image: '/public/img/quat-dan-lanh-vios-yais-doi-2010.webp' },
      { id: 6396, sku: 'TCA-8FEF', name: 'Quạt dàn lạnh Xpander', price: 504000, qty: 2, image: '/public/img/mo-to-quat-dan-lanh-hay-quat-gio-dieu-hoa-trong-taplo-mitsubishi-xpander-cross-xforce-doi-2018-2024.jpg' }
    ],
    wishlist: [2682, 6396],
    user: {
      isLoggedIn: true,
      name: 'Garage Hoàng Nam Auto',
      phone: '0988776655',
      tier: 'Kim Cương (Diamond Partner)',
      points: 12500,
      taxId: '0108998877',
      address: 'Số 188 Phạm Văn Đồng, Cầu Giấy, Hà Nội'
    },
    activeCategory: 'all',
    activeBrand: 'all',
    searchQuery: '',
    selectedProduct: null
  };

  // Mock Products Database
  const productsDB = [
    { id: 2682, sku: 'BW935F012', oem: '88501-0K050', name: 'QUẠT DÀN LẠNH TOYOTA INNOVA(REAR) 2008', price: 752000, oldPrice: 950000, cat: 'Dàn lạnh', brand: 'Toyota', partBrand: 'Denso', image: '/public/img/quat-dan-lanh-vios-yais-doi-2010.webp', rating: 4.9, sold: 142, desc: 'Motor quạt dàn lạnh hàng chính hãng Denso lắp cho Toyota Innova đời 2008 - 2016. Bảo hành 12 tháng.' },
    { id: 6396, sku: 'TCA-8FEF', oem: '7802A317', name: 'Quạt dàn lạnh Xpander / Cross / Xforce 2018 - 2024', price: 504000, oldPrice: 630000, cat: 'Dàn lạnh', brand: 'Mitsubishi', partBrand: 'Valeo', image: '/public/img/mo-to-quat-dan-lanh-hay-quat-gio-dieu-hoa-trong-taplo-mitsubishi-xpander-cross-xforce-doi-2018-2024.jpg', rating: 4.8, sold: 98, desc: 'Mô tơ quạt quạt gió dàn lạnh trong táp lô xe Mitsubishi Xpander, Xpander Cross, Xforce. Chuẩn OEM.' },
    { id: 1979, sku: '8832006220-SANDEN', oem: '88320-06220', name: 'Lốc lạnh điều hòa Toyota Camry 2.4L / 3.5L', price: 8160000, oldPrice: 9500000, cat: 'Lốc điều hòa', brand: 'Toyota', partBrand: 'Sanden', image: '/public/img/loc-dieu-hoa-loc-lanh-xe-bmw-520i-f10-528i-f11-320i-f30-328i-x1-e84-x3-f25-z4-vinfast-lux-may-n20-doi-2011-2016.jfif', rating: 5.0, sold: 64, desc: 'Máy nén điều hòa Lốc lạnh cao cấp Sanden lắp xe Toyota Camry đời 2007 - 2015.' },
    { id: 2130, sku: 'VALEO-884600K060', oem: '88460-0K060', name: 'Dàn nóng điều hòa ô tô Toyota Hilux / Fortuner', price: 2400000, oldPrice: 2800000, cat: 'Dàn nóng', brand: 'Toyota', partBrand: 'Valeo', image: '/public/img/dan-nong-dieu-hoa-honda-civic-1-8.gif', rating: 4.7, sold: 112, desc: 'Giàn nóng điều hòa chất liệu nhôm cao cấp tản nhiệt siêu nhanh lắp cho Toyota Hilux, Fortuner.' },
    { id: 2508, sku: 'LL-CRV-SANDEN', oem: '38810-R5A-A01', name: 'Lốc Lạnh Xe Honda CR-V 2.0 / 2.4L 2013-2017', price: 8160000, oldPrice: 9800000, cat: 'Lốc điều hòa', brand: 'Honda', partBrand: 'Sanden', image: '/public/img/loc-lanh-loc-dieu-hoa-bmw-335i-f30-435i-f32-535i-f10-640i-f06-740i-740li-f01f02-x5-x6-e70-lci-f15-e71-f16-doi-2015-2026.jfif', rating: 4.9, sold: 53, desc: 'Mô tơ lốc lạnh máy nén điều hòa Honda CR-V động cơ 2.4L.' },
    { id: 6395, sku: 'TCA-9F7D', oem: '8-98000-123-0', name: 'Trở quạt điều hòa xe tải Isuzu D-Max / Mu-X', price: 504000, oldPrice: 650000, cat: 'Van tiết lưu', brand: 'Isuzu', partBrand: 'Cooling', image: '/public/img/tro-quat-dieu-hoa-bien-tro-quat-gio-manual-ac-ford-escape-f-150-expedition-mustang-f-250350-doi-2004-2014.jfif', rating: 4.8, sold: 76, desc: 'Biến trở quạt gió dàn lạnh điện trở quạt điều hòa Isuzu Dmax.' }
  ];

  // Helper Functions
  const fmtVND = (num) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);

  // Navigation Router
  function navigateTo(screenId, params = {}) {
    state.currentScreen = screenId;
    if (params.product) state.selectedProduct = params.product;

    document.querySelectorAll('.view-screen').forEach(el => el.classList.remove('active'));
    const targetScreen = document.getElementById(`screen-${screenId}`);
    if (targetScreen) targetScreen.classList.add('active');

    // Update Bottom Nav active state
    document.querySelectorAll('.nav-item').forEach(nav => {
      if (nav.dataset.screen === screenId) {
        nav.classList.add('active');
      } else {
        nav.classList.remove('active');
      }
    });

    // Scroll container to top
    const container = document.querySelector('.app-content');
    if (container) container.scrollTop = 0;

    // Render screen specific content
    renderScreen(screenId);
  }

  // Render Screens
  function renderScreen(screenId) {
    updateCartBadges();
    
    switch (screenId) {
      case 'home':
        renderHomeProducts();
        break;
      case 'search':
        renderSearchResults();
        break;
      case 'cart':
        renderCartView();
        break;
      case 'checkout':
        renderCheckoutView();
        break;
      case 'orders':
        renderOrdersView();
        break;
      case 'warranty':
        renderWarrantyView();
        break;
      case 'account':
        renderAccountView();
        break;
      case 'product-detail':
        renderProductDetailView();
        break;
    }
  }

  // Render Home Screen Products
  function renderHomeProducts() {
    const grid = document.getElementById('home-product-grid');
    if (!grid) return;

    grid.innerHTML = productsDB.map(p => `
      <div class="prod-card" onclick="window.App.viewDetail(${p.id})">
        <div class="prod-img-wrap">
          <img src="${p.image}" alt="${p.name}" onerror="this.src='/public/img/logo-cooling-512x512.jpg'">
          ${p.oldPrice ? `<span class="prod-badge-discount">-${Math.round((1 - p.price / p.oldPrice) * 100)}%</span>` : ''}
        </div>
        <div class="prod-info">
          <span class="prod-sku">${p.sku} • ${p.brand}</span>
          <h4 class="prod-name">${p.name}</h4>
          <div class="prod-price-row">
            <span class="prod-price">${fmtVND(p.price)}</span>
            <button class="prod-add-btn" onclick="event.stopPropagation(); window.App.addToCart(${p.id})">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  // Render Search Results
  function renderSearchResults() {
    const grid = document.getElementById('search-product-grid');
    if (!grid) return;

    let filtered = productsDB;
    if (state.activeCategory !== 'all') {
      filtered = filtered.filter(p => p.cat === state.activeCategory);
    }
    if (state.searchQuery) {
      const q = state.searchQuery.toLowerCase();
      filtered = filtered.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q) || p.oem.toLowerCase().includes(q));
    }

    grid.innerHTML = filtered.map(p => `
      <div class="prod-card" onclick="window.App.viewDetail(${p.id})">
        <div class="prod-img-wrap">
          <img src="${p.image}" alt="${p.name}">
        </div>
        <div class="prod-info">
          <span class="prod-sku">${p.sku}</span>
          <h4 class="prod-name">${p.name}</h4>
          <div class="prod-price-row">
            <span class="prod-price">${fmtVND(p.price)}</span>
            <button class="prod-add-btn" onclick="event.stopPropagation(); window.App.addToCart(${p.id})">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            </button>
          </div>
        </div>
      </div>
    `).join('');
  }

  // Render Product Detail
  function renderProductDetailView() {
    const p = state.selectedProduct || productsDB[0];
    const container = document.getElementById('screen-product-detail');
    if (!container) return;

    container.innerHTML = `
      <div style="background:#fff; padding:16px;">
        <button onclick="window.App.navigateTo('home')" style="background:none; border:none; color:var(--navy-dark); font-weight:700; margin-bottom:10px; cursor:pointer; display:flex; align-items:center; gap:4px;">
          ← Quay lại
        </button>
        <div style="width:100%; height:240px; background:#f8fafc; border-radius:12px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
          <img src="${p.image}" style="max-width:100%; max-height:100%; object-fit:contain;">
        </div>
        <div style="margin-top:14px;">
          <span style="background:var(--navy-dark); color:#fff; font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px;">${p.sku}</span>
          <span style="color:var(--gray-text-sub); font-size:11px; font-weight:600; margin-left:6px;">OEM: ${p.oem}</span>
          <h2 style="font-size:16px; font-weight:800; color:var(--navy-dark); margin:8px 0;">${p.name}</h2>
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="font-size:18px; font-weight:800; color:var(--orange-accent);">${fmtVND(p.price)}</span>
            ${p.oldPrice ? `<span style="font-size:13px; color:var(--gray-text-sub); text-decoration:line-through;">${fmtVND(p.oldPrice)}</span>` : ''}
          </div>
        </div>

        <div style="border-top:1px solid var(--gray-border); padding-top:12px; margin-top:12px;">
          <h4 style="font-size:13px; font-weight:700; color:var(--navy-dark); margin-bottom:6px;">Mô tả sản phẩm:</h4>
          <p style="font-size:12.5px; color:var(--gray-text-sub); line-height:1.5;">${p.desc}</p>
        </div>

        <div style="margin-top:20px; display:flex; gap:10px;">
          <a href="tel:0705070526" class="btn-navy" style="flex:1; text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:6px;">
            📞 Gọi Hotline
          </a>
          <button class="btn-primary" style="flex:2;" onclick="window.App.addToCart(${p.id}); window.App.navigateTo('cart');">
            🛒 Thêm vào Giỏ Hàng
          </button>
        </div>
      </div>
    `;
  }

  // Cart View
  function renderCartView() {
    const container = document.getElementById('cart-items-list');
    const summary = document.getElementById('cart-summary-box');
    if (!container) return;

    if (state.cart.length === 0) {
      container.innerHTML = `<div style="text-align:center; padding:40px 20px; color:var(--gray-text-sub);">Giỏ hàng đang trống!</div>`;
      if (summary) summary.style.display = 'none';
      return;
    }

    if (summary) summary.style.display = 'block';

    container.innerHTML = state.cart.map(item => `
      <div style="background:#fff; border:1px solid var(--gray-border); border-radius:12px; padding:12px; margin-bottom:10px; display:flex; gap:10px; align-items:center;">
        <img src="${item.image}" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
        <div style="flex:1;">
          <div style="font-size:10px; color:var(--gray-text-sub); font-weight:700;">${item.sku}</div>
          <div style="font-size:12px; font-weight:700; color:var(--navy-dark); line-height:1.3; margin:2px 0;">${item.name}</div>
          <div style="font-size:13px; font-weight:800; color:var(--orange-accent);">${fmtVND(item.price)}</div>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
          <button style="width:24px; height:24px; border:1px solid var(--gray-border); background:#fff; border-radius:4px;" onclick="window.App.updateCartQty(${item.id}, -1)">-</button>
          <span style="font-size:12px; font-weight:700;">${item.qty}</span>
          <button style="width:24px; height:24px; border:1px solid var(--gray-border); background:#fff; border-radius:4px;" onclick="window.App.updateCartQty(${item.id}, 1)">+</button>
        </div>
      </div>
    `).join('');

    const total = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    document.getElementById('cart-total-price').textContent = fmtVND(total);
  }

  // Checkout View
  function renderCheckoutView() {
    const total = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    document.getElementById('checkout-total-price').textContent = fmtVND(total);
  }

  // Orders View
  function renderOrdersView() {
    const list = document.getElementById('orders-list-container');
    if (!list) return;

    list.innerHTML = `
      <div style="background:#fff; border:1px solid var(--gray-border); border-radius:12px; padding:14px; margin-bottom:12px;">
        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--gray-text-sub); margin-bottom:6px;">
          <span>Mã đơn: <b>#CS-99812</b></span>
          <span style="color:var(--orange-accent); font-weight:700;">Đang vận chuyển 🚚</span>
        </div>
        <div style="font-size:13px; font-weight:700; color:var(--navy-dark);">2x QUẠT DÀN LẠNH TOYOTA INNOVA</div>
        <div style="display:flex; justify-content:space-between; margin-top:10px; align-items:center;">
          <span style="font-size:14px; font-weight:800; color:var(--navy-dark);">Tổng: 1.504.000 ₫</span>
          <button class="btn-navy" style="width:auto; padding:6px 12px; font-size:11px;" onclick="alert('Mã vận đơn VIETTELPOST: 8877112233 - Dự kiến giao hôm nay!')">Tra Vận Đơn</button>
        </div>
      </div>
    `;
  }

  // Warranty View
  function renderWarrantyView() {
    // Warranty UI
  }

  // Account View
  function renderAccountView() {
    // Account Profile UI
  }

  // Actions
  function addToCart(productId) {
    const prod = productsDB.find(p => p.id === productId);
    if (!prod) return;

    const existing = state.cart.find(item => item.id === productId);
    if (existing) {
      existing.qty += 1;
    } else {
      state.cart.push({ id: prod.id, sku: prod.sku, name: prod.name, price: prod.price, qty: 1, image: prod.image });
    }
    updateCartBadges();
    alert(`Đã thêm "${prod.name}" vào giỏ hàng!`);
  }

  function updateCartQty(productId, delta) {
    const item = state.cart.find(i => i.id === productId);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) {
      state.cart = state.cart.filter(i => i.id !== productId);
    }
    renderCartView();
    updateCartBadges();
  }

  function updateCartBadges() {
    const count = state.cart.reduce((sum, i) => sum + i.qty, 0);
    document.querySelectorAll('.cart-badge-count').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'inline-block' : 'none';
    });
  }

  function viewDetail(productId) {
    const prod = productsDB.find(p => p.id === productId);
    if (prod) {
      state.selectedProduct = prod;
      navigateTo('product-detail', { product: prod });
    }
  }

  // Global App API
  window.App = {
    state,
    navigateTo,
    addToCart,
    updateCartQty,
    viewDetail,
    filterCategory: (cat) => {
      state.activeCategory = cat;
      navigateTo('search');
    },
    search: (query) => {
      state.searchQuery = query;
      navigateTo('search');
    }
  };

  // DOM Loaded Init
  document.addEventListener('DOMContentLoaded', () => {
    navigateTo('home');
  });

})();
