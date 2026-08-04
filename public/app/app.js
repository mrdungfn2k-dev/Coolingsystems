/* ==========================================================================
   COOLING SYSTEMS MOBILE APP - COMPLETE SPA & LIVE DATA ENGINE
   ========================================================================== */

(function () {
  'use strict';

  // Seed VPS Products Data
  let productsDB = [
  {
    "id": 6919,
    "sku": "TCA-LIVE-49-3",
    "oem": "97138A5000",
    "name": "Giàn sưởi Kia K3 Giàn sưởi Cerato 2016-2023",
    "price": 810000.0,
    "oldPrice": 900000.0,
    "cat": "Dàn Sưởi Điều Hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/gian-suoi-kia-k3-gian-suoi-cerato-2016-2023-tca-live-49-3-20260803030411-2e299874.webp",
    "rating": 4.9,
    "sold": 104,
    "desc": "1. M&ocirc; Tả Sản Phẩm Gi&agrave;n sưởi Kia K3 Gi&agrave;n sưởi Cerato 2016-2023 ... <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0"
  },
  {
    "id": 6918,
    "sku": "TCA-LIVE-49-2",
    "oem": "DS001",
    "name": "Giàn sưởi Toyota Vios Giàn sưởi Vios 2014 hàng xịn",
    "price": 810000.0,
    "oldPrice": 900000.0,
    "cat": "Dàn Sưởi Điều Hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/gi_n_s_i_toyota_vios_gi_n_s_i_vios_2014_h_ng_x_n_phutungotothanhcong.vn.jpg",
    "rating": 4.9,
    "sold": 103,
    "desc": "1. M&ocirc; Tả Sản Phẩm Gi&agrave;n sưởi Toyota Vios Gi&agrave;n sưởi Vios 2014 h&agrave;ng xịn <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; marg"
  },
  {
    "id": 6917,
    "sku": "TCA-LIVE-49-1",
    "oem": "95193258",
    "name": "Giàn sưởi Daewoo Matiz 4 Giàn sưởi Matiz 4",
    "price": 810000.0,
    "oldPrice": 900000.0,
    "cat": "Dàn Sưởi Điều Hòa",
    "brand": "Daewoo",
    "partBrand": "Cooling",
    "image": "/uploads/products/gi_n_s_i_daewoo_matiz_4_gi_n_s_i_matiz_4_h_ng_x_n_.jpg",
    "rating": 4.9,
    "sold": 102,
    "desc": "1. M&ocirc; Tả Sản Phẩm Gi&agrave;n sưởi Daewoo Matiz 4 Gi&agrave;n sưởi Matiz 4 ... <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;"
  },
  {
    "id": 6916,
    "sku": "TCA-LIVE-27-3",
    "oem": "M149872",
    "name": "Phin lọc ga Mercedes S dài chính hãng",
    "price": 315000.0,
    "oldPrice": 350000.0,
    "cat": "Phin Lọc Ga",
    "brand": "Mercedes-Benz",
    "partBrand": "Cooling",
    "image": "/uploads/products/phin_l_c_ga_mercedes_s_d_i_ch_nh_h_ng.jpg",
    "rating": 4.9,
    "sold": 101,
    "desc": "1. M&ocirc; Tả Sản Phẩm Phin lọc ga Mercedes S d&agrave;i ch&iacute;nh h&atilde;ng <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\">"
  },
  {
    "id": 6915,
    "sku": "TCA-LIVE-27-2",
    "oem": "445910-1150",
    "name": "Phin lọc ga Denso chính hãng",
    "price": 315000.0,
    "oldPrice": 350000.0,
    "cat": "Phin Lọc Ga",
    "brand": "Chính Hãng",
    "partBrand": "Denso",
    "image": "/uploads/products/phin_l_c_gas_denso_ch_nh_h_ng.jpg",
    "rating": 4.9,
    "sold": 100,
    "desc": "1. M&ocirc; Tả Sản Phẩm Phin lọc ga Denso ch&iacute;nh h&atilde;ng <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-"
  },
  {
    "id": 6914,
    "sku": "TCA-LIVE-27-1",
    "oem": "97802-25000",
    "name": "Phin lọc ga Hanon chính hãng 97802-25000",
    "price": 315000.0,
    "oldPrice": 350000.0,
    "cat": "Phin Lọc Ga",
    "brand": "Chính Hãng",
    "partBrand": "Hanon",
    "image": "/uploads/products/phin_l_c_gas_hanon_ch_nh_h_ng.jpg",
    "rating": 4.9,
    "sold": 99,
    "desc": "1. M&ocirc; Tả Sản Phẩm Phin lọc ga Hanon ch&iacute;nh h&atilde;ng 97802-25000 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img"
  },
  {
    "id": 6913,
    "sku": "TCA-LIVE-48-96",
    "oem": "BDL_CIVI2.0",
    "name": "BỘ ĐẦU LỐC CIVIC 2.0 BỘ ĐẦU CRV 2.4",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Honda",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_civic_2.0_x_n.jpg",
    "rating": 4.9,
    "sold": 98,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CIVIC 2.0 BỘ ĐẦU CRV 2.4 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: "
  },
  {
    "id": 6912,
    "sku": "TCA-LIVE-48-95",
    "oem": "700-400392",
    "name": "BỘ ĐẦU LỐC KIA SORENTO",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-kia-sorento-tca-live-48-95-20260803152906-a191cd3c.webp",
    "rating": 4.9,
    "sold": 97,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC KIA SORENTO <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện "
  },
  {
    "id": 6911,
    "sku": "TCA-LIVE-48-94",
    "oem": "SP000025",
    "name": "BỘ ĐẦU LỐC MATIZ 4 BỘ ĐẦU LỐC MATIZ IV",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Daewoo",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-matiz-phu_tung_oto_thanh_cong_auto.jpg",
    "rating": 4.9,
    "sold": 96,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MATIZ 4 BỘ ĐẦU LỐC MATIZ IV <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-widt"
  },
  {
    "id": 6910,
    "sku": "TCA-LIVE-48-93",
    "oem": "B15T8PK",
    "name": "BỘ ĐẦU LỐC 15T 8PK HANON BỘ ĐẦU HCC 8PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-15t-8pk-hanon-phu-tung-oto-thanh-cong-auto.jpg",
    "rating": 4.9,
    "sold": 95,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 15T 8PK HANON BỘ ĐẦU HCC 8PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-wid"
  },
  {
    "id": 6909,
    "sku": "TCA-LIVE-48-92",
    "oem": "SP5502054",
    "name": "Bộ đầu lốc Triton Bộ đầu lốc Zinger 2008 4PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mitsubishi",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_triton_b_u_l_c_zinger_2008_4pk_phutung.jpg",
    "rating": 4.9,
    "sold": 94,
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Triton Bộ đầu lốc Zinger 2008 4PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"ma"
  },
  {
    "id": 6908,
    "sku": "TCA-LIVE-48-91",
    "oem": "P95978957",
    "name": "BỘ ĐẦU LỐC MÁY XÚC DAEWOO BẢN A 12V",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Daewoo",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-may-xuc-daewoo-ban-a-12v-tca-live-48-91-20260804072956-990e9504.webp",
    "rating": 4.9,
    "sold": 93,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC M&Aacute;Y X&Uacute;C DAEWOO BẢN A 12V <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;c"
  },
  {
    "id": 6907,
    "sku": "TCA-LIVE-48-90",
    "oem": "KZH-10-0023",
    "name": "BỘ ĐẦU LỐC GETZ BỘ ĐẦU MORNING BỘ ĐẦU I10 5PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_getz_b_u_morning_b_u_i10_5pk.jpg",
    "rating": 4.9,
    "sold": 92,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC GETZ BỘ ĐẦU MORNING BỘ ĐẦU I10 5PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"m"
  },
  {
    "id": 6906,
    "sku": "TCA-LIVE-48-89",
    "oem": "BDDKIA.4T",
    "name": "BỘ ĐẦU LỐC KIA A 1.4T",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-kia-a-1.webp",
    "rating": 4.9,
    "sold": 91,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC KIA A 1.4T <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <"
  },
  {
    "id": 6905,
    "sku": "TCA-LIVE-48-88",
    "oem": "ZZH-17-0016",
    "name": "BỘ ĐẦU LỐC TRANSIT 2012",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_transit_2012.jpg",
    "rating": 4.9,
    "sold": 90,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC TRANSIT 2012 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height"
  },
  {
    "id": 6904,
    "sku": "TCA-LIVE-48-87",
    "oem": "BDL_MO12",
    "name": "BỘ ĐẦU LỐC MORNING 2012 VAN ĐIỆN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-morning-2012-van-dien-tca-live-48-87-20260804073055-50ab0e7f.webp",
    "rating": 4.9,
    "sold": 89,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MORNING 2012 VAN ĐIỆN <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe "
  },
  {
    "id": 6903,
    "sku": "TCA-LIVE-48-86",
    "oem": "XL24730099372",
    "name": "BỘ ĐẦU LỐC VIOS 2014 4PK DENSO",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Denso",
    "image": "/uploads/products/bo-dau-loc-vios-2014-4pk-phu-tung-oto-thanh-cong-auto.jpg",
    "rating": 4.9,
    "sold": 88,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2014 4PK DENSO <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%;"
  },
  {
    "id": 6902,
    "sku": "TCA-LIVE-48-85",
    "oem": "HZH-90007",
    "name": "BỘ ĐẦU LỐC SANTAFE 2016-2018",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_santafe_2016-2018.jpg",
    "rating": 4.9,
    "sold": 87,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SANTAFE 2016-2018 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; h"
  },
  {
    "id": 6901,
    "sku": "TCA-LIVE-48-84",
    "oem": "B224PK",
    "name": "BỘ ĐẦU LỐC CAMRY 2.2 4PK BỘ ĐẦU CAMRY 4PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_camry_2.2_4pk_b_u_camry_4pk_ph_t_ng_t_th_nh_c_ng.jpg",
    "rating": 4.9,
    "sold": 86,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CAMRY 2.2 4PK BỘ ĐẦU CAMRY 4PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-w"
  },
  {
    "id": 6900,
    "sku": "TCA-LIVE-48-83",
    "oem": "SP5501972",
    "name": "Bộ đầu lốc Doosan 55 Bộ đầu lốc máy xúc Doosan",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_dossan_55_b_u_m_y_x_c_dossan_55_12v_phutungotothanhcong.jpg",
    "rating": 4.9,
    "sold": 85,
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Doosan 55 Bộ đầu lốc m&aacute;y x&uacute;c Doosan ... <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16p"
  },
  {
    "id": 6899,
    "sku": "TCA-LIVE-48-82",
    "oem": "8HZYEU43BO",
    "name": "BỘ ĐẦU LỐC NISSAN XTRAIL 2.6 BỘ ĐẦU XTRAIL 2016",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Nissan",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_nissan_xtrail_2.5_2016.jpg",
    "rating": 4.9,
    "sold": 144,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC NISSAN XTRAIL 2.6 BỘ ĐẦU XTRAIL 2016 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style="
  },
  {
    "id": 6898,
    "sku": "TCA-LIVE-48-81",
    "oem": "SA22C95978755",
    "name": "BỘ ĐẦU LỐC SOLATI XỊN HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-solati-xin-hanon-tca-live-48-81-20260804073202-74d52e73.webp",
    "rating": 4.9,
    "sold": 143,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SOLATI XỊN HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh "
  },
  {
    "id": 6897,
    "sku": "TCA-LIVE-48-80",
    "oem": "FZH-12-0009",
    "name": "BỘ ĐẦU LỐC FORD ESCAPE 3.0 2.0",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_ford_escape_3.0_2.0.jpg",
    "rating": 4.9,
    "sold": 142,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC FORD ESCAPE 3.0 2.0 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%;"
  },
  {
    "id": 6896,
    "sku": "TCA-LIVE-48-79",
    "oem": "BD2003",
    "name": "BỘ ĐẦU LỐC ALTIS 2003 BỘ ĐẦU ALTIS XỊN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-altis-2003_phu_t_ng_oto_th_nh_cong_auto.jpg",
    "rating": 4.9,
    "sold": 141,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ALTIS 2003 BỘ ĐẦU ALTIS XỊN <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-widt"
  },
  {
    "id": 6895,
    "sku": "TCA-LIVE-48-78",
    "oem": "DN5501526",
    "name": "BỘ ĐẦU LỐC NAVARA 2014 BỘ ĐẦU LỐC NISSAN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Nissan",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-navara-2014_phu_tung_oto_thanh_cong_auto.jpg",
    "rating": 4.9,
    "sold": 140,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC NAVARA 2014 BỘ ĐẦU LỐC NISSAN ... <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"ma"
  },
  {
    "id": 6894,
    "sku": "TCA-LIVE-48-77",
    "oem": "BDL_MO4PK",
    "name": "BỘ ĐẦU LỐC MORNING BỘ ĐẦU GETZ 4PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_morning_b_u_getz_4pk.jpg",
    "rating": 4.9,
    "sold": 139,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MORNING BỘ ĐẦU GETZ 4PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 1"
  },
  {
    "id": 6893,
    "sku": "TCA-LIVE-48-76",
    "oem": "mva00013",
    "name": "BỘ ĐẦU LỐC NISSAN SUNNY",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Nissan",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-nissan-sunny-tca-live-48-76-20260804073354-b6ef813f.webp",
    "rating": 4.9,
    "sold": 138,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC NISSAN SUNNY <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện"
  },
  {
    "id": 6892,
    "sku": "TCA-LIVE-48-75",
    "oem": "S825-400746",
    "name": "BỘ ĐẦU LỐC MITSUBISHI LANCER 6 PK 100MM",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mitsubishi",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-mitsubishi-lancer-6-pk-100mm-tca-live-48-75-20260804073433-24138f1c.webp",
    "rating": 4.9,
    "sold": 137,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MITSUBISHI LANCER 6 PK 100MM <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve"
  },
  {
    "id": 6891,
    "sku": "TCA-LIVE-48-74",
    "oem": "S825-400764",
    "name": "BỘ ĐẦU LỐC BẢN A 24V HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-ban-a-24v-hanon-tca-live-48-74-20260804073455-e04ebdc4.webp",
    "rating": 4.9,
    "sold": 136,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC BẢN A 24V HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh k"
  },
  {
    "id": 6890,
    "sku": "TCA-LIVE-48-73",
    "oem": "BDL_HD4PK",
    "name": "BỘ ĐẦU LỐC PORTER 4PK 12V BỘ ĐẦU STAREX HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-porter-4pk-12v-bo-dau-starex-hanon-tca-live-48-73-20260804073525-cffb92e8.webp",
    "rating": 4.9,
    "sold": 135,
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC PORTER 4PK 12V BỘ ĐẦU STAREX HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&"
  }
];
  let categoriesDB = [
    { id: 1, name: 'Dàn lạnh điều hòa', count: '412 mã' },
    { id: 2, name: 'Dàn nóng điều hòa', count: '386 mã' },
    { id: 3, name: 'Lốc điều hòa', count: '524 mã' },
    { id: 4, name: 'Bộ đầu lốc điều hòa', count: '148 mã' },
    { id: 5, name: 'Van tiết lưu điều hòa', count: '231 mã' },
    { id: 6, name: 'Van đuôi lốc', count: '96 mã' },
    { id: 7, name: 'Motor, quạt dàn lạnh', count: '174 mã' },
    { id: 8, name: 'Motor, quạt dàn nóng', count: '168 mã' },
    { id: 9, name: 'Phin lọc ga', count: '88 mã' },
    { id: 10, name: 'Ống dẫn gas điều hòa', count: '142 mã' },
    { id: 11, name: 'Dàn sưởi điều hòa', count: '119 mã' }
  ];
  let carBrandsDB = [
    { name: 'Toyota', count: '649' },
    { name: 'Hyundai', count: '512' },
    { name: 'KIA', count: '486' },
    { name: 'Mazda', count: '318' },
    { name: 'Ford', count: '297' },
    { name: 'Honda', count: '284' },
    { name: 'Mitsubishi', count: '233' },
    { name: 'Chevrolet', count: '189' },
    { name: 'Daewoo', count: '142' },
    { name: 'Mercedes', count: '176' },
    { name: 'BMW', count: '161' },
    { name: 'Audi', count: '118' },
    { name: 'Nissan', count: '131' },
    { name: 'VinFast', count: '72' },
    { name: 'Lexus', count: '96' }
  ];
  let partBrandsDB = [
    { name: 'DENSO', origin: 'Nhật Bản • OEM', count: '1.240 mã' },
    { name: 'VALEO', origin: 'Pháp • OEM', count: '862 mã' },
    { name: 'HANON', origin: 'Hàn Quốc • OEM', count: '744 mã' },
    { name: 'SANDEN', origin: 'Nhật Bản • Lốc điều hòa', count: '631 mã' },
    { name: 'FUJIKOKI', origin: 'Nhật Bản • Van tiết lưu', count: '286 mã' },
    { name: 'DOOWON', origin: 'Hàn Quốc • Dàn lạnh', count: '318 mã' },
    { name: 'BEHR / MAHLE', origin: 'Đức • Làm mát', count: '482 mã' },
    { name: 'KEIHIN', origin: 'Nhật Bản • Lốc & van', count: '164 mã' }
  ];

  // State Management
  const state = {
    currentScreen: 'home',
    cart: [
      { id: productsDB[0].id, sku: productsDB[0].sku, name: productsDB[0].name, price: productsDB[0].price, qty: 1, image: productsDB[0].image },
      { id: productsDB[1].id, sku: productsDB[1].sku, name: productsDB[1].name, price: productsDB[1].price, qty: 2, image: productsDB[1].image }
    ],
    wishlist: [productsDB[0].id, productsDB[1].id],
    user: {
      isLoggedIn: true,
      name: 'Gara Thành Công',
      phone: '0912 345 678',
      tier: 'ĐẠI LÝ',
      points: 12500,
      taxId: '0101234567-001',
      address: 'Số 12 Nguyễn Văn Cừ, P. Gia Thụy, Long Biên, Hà Nội'
    },
    activeCategory: 'all',
    searchQuery: '',
    selectedProduct: productsDB[0]
  };

  // Helper Functions
  const fmtVND = (num) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);

  // Fetch Live Data from VPS API
  function fetchLiveData() {
    fetch('/api/app-data')
      .then(res => res.json())
      .then(data => {
        if (data && data.success && data.products && data.products.length > 0) {
          productsDB = data.products.map(p => ({
            id: p.id,
            sku: p.sku || 'TCA-COOLING',
            oem: p.oem_code || 'OEM-STANDARD',
            name: p.name,
            price: parseFloat(p.price) || 0,
            oldPrice: p.original_price ? parseFloat(p.original_price) : null,
            cat: p.cat_name || 'Phụ Tùng Điều Hòa',
            brand: p.brand_name || 'Chính Hãng',
            partBrand: p.part_brand || 'Cooling',
            image: p.image || '/favicon-512x512.png',
            desc: p.description ? p.description.replace(/<[^>]*>?/gm, '').substring(0, 180) : ''
          }));
          renderScreen(state.currentScreen);
        }
      })
      .catch(err => console.log('Using local embedded products database:', err));
  }

  // Navigation Router (NO FLICKER, NO TEXT JITTER)
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

    // Scroll content to top
    const container = document.querySelector('.app-content');
    if (container) container.scrollTop = 0;

    renderScreen(screenId);
  }

  // Render Screen Switcher
  function renderScreen(screenId) {
    updateCartBadges();
    
    switch (screenId) {
      case 'home':
        renderHomeProducts();
        break;
      case 'search':
        renderSearchResults();
        break;
      case 'vehicle-search':
        renderVehicleSearchView();
        break;
      case 'categories':
        renderCategoriesView();
        break;
      case 'car-brands':
        renderCarBrandsView();
        break;
      case 'part-brands':
        renderPartBrandsView();
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
      case 'order-tracking':
        renderOrderTrackingView();
        break;
      case 'warranty':
        renderWarrantyView();
        break;
      case 'coupons':
        renderCouponsView();
        break;
      case 'stores':
        renderStoresView();
        break;
      case 'account':
        renderAccountView();
        break;
      case 'product-detail':
        renderProductDetailView();
        break;
      case 'login':
        renderLoginView();
        break;
      case 'welcome':
        renderWelcomeView();
        break;
    }
  }

  // 1. Render Home Screen
  function renderHomeProducts() {
    const grid = document.getElementById('home-product-grid');
    if (!grid) return;

    grid.innerHTML = productsDB.slice(0, 12).map(p => `
      <div class="prod-card" onclick="window.App.viewDetail(${p.id})">
        <div class="prod-img-wrap">
          <img src="${p.image}" alt="${p.name}" onerror="this.src='/favicon-512x512.png'">
        </div>
        <div class="prod-info">
          <span class="prod-sku">Cooling • ${p.oem || p.sku}</span>
          <h4 class="prod-name">${p.name}</h4>
          <div class="prod-price-row">
            <span class="prod-price">${fmtVND(p.price)}</span>
            <span class="prod-status-stock">• Còn hàng</span>
          </div>
        </div>
      </div>
    `).join('');
  }

  // 2. Render Search Results
  function renderSearchResults() {
    const grid = document.getElementById('search-product-grid');
    if (!grid) return;

    let filtered = productsDB;
    if (state.activeCategory !== 'all') {
      filtered = filtered.filter(p => p.cat.toLowerCase().includes(state.activeCategory.toLowerCase()));
    }
    if (state.searchQuery) {
      const q = state.searchQuery.toLowerCase();
      filtered = filtered.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q) || p.oem.toLowerCase().includes(q));
    }

    grid.innerHTML = filtered.map(p => `
      <div class="prod-card" onclick="window.App.viewDetail(${p.id})" style="display:flex; flex-direction:row; height:100px; margin-bottom:10px; grid-column: 1 / -1;">
        <div style="width:90px; height:100%; background:#f8fafc; overflow:hidden;">
          <img src="${p.image}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='/favicon-512x512.png'">
        </div>
        <div style="padding:10px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
          <div>
            <div style="font-size:10px; color:var(--gray-text-sub); font-weight:700;">Cooling • ${p.oem || p.sku}</div>
            <div style="font-size:12px; font-weight:700; color:var(--navy-dark); margin:2px 0;">${p.name}</div>
            <span style="background:#ecfdf5; color:#047857; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px;">VALEO Khớp 100%</span>
          </div>
          <div style="font-size:13px; font-weight:800; color:var(--orange-accent);">${fmtVND(p.price)}</div>
        </div>
      </div>
    `).join('');
  }

  // 3. Render Vehicle Search Screen (Matching Screenshot 4)
  function renderVehicleSearchView() {
    const container = document.getElementById('screen-vehicle-search');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Tra theo xe</div>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">Chọn xe → hệ thống lọc đúng mã tương thích</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:12px; border:1px solid var(--gray-border); padding:12px; margin-bottom:12px;">
          <div style="font-size:11px; font-weight:700; color:var(--gray-text-sub); margin-bottom:6px;">XE ĐÃ LƯU</div>
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Toyota Vios</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">2014 · 1.5G · 30A-123.45</div>
            </div>
            <button class="btn-outline" style="width:auto; padding:6px 12px; font-size:11px;">+ Thêm xe</button>
          </div>
        </div>

        <div style="background:#fff; border-radius:12px; border:1px solid var(--gray-border); padding:14px;">
          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Hãng xe</label>
          <select style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; font-size:13px; font-weight:700; margin-bottom:12px;">
            <option>Toyota</option><option>Hyundai</option><option>KIA</option><option>Mazda</option><option>Ford</option>
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Dòng xe</label>
          <select style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; font-size:13px; font-weight:700; margin-bottom:12px;">
            <option>Vios</option><option>Innova</option><option>Camry</option><option>Fortuner</option>
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Đời xe</label>
          <select style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; font-size:13px; font-weight:700; margin-bottom:12px;">
            <option>2014 - 2018</option><option>2019 - 2023</option>
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Danh mục phụ tùng</label>
          <select style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; font-size:13px; font-weight:700; margin-bottom:14px;">
            <option>Dàn lạnh điều hòa</option><option>Lốc điều hòa</option><option>Dàn nóng điều hòa</option>
          </select>

          <button class="btn-orange" onclick="window.App.navigateTo('search')">Tìm kiếm phụ tùng</button>
        </div>
      </div>
    `;
  }

  // 4. Render Categories List (Matching Screenshot 6)
  function renderCategoriesView() {
    const container = document.getElementById('screen-categories');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px; font-size:16px; font-weight:800;">
        Danh mục
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); overflow:hidden;">
          ${categoriesDB.map((c, i) => `
            <div onclick="window.App.filterCategory('${c.name}')" style="padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; cursor:pointer;">
              <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:11px; font-weight:800; color:var(--gray-text-sub);">${String(i + 1).padStart(2, '0')}</span>
                <div>
                  <div style="font-size:13px; font-weight:700; color:var(--navy-dark);">${c.name}</div>
                  <div style="font-size:10.5px; color:var(--gray-text-sub);">${c.count}</div>
                </div>
              </div>
              <span style="color:var(--gray-text-sub); font-size:16px;">›</span>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  // 5. Render Car Brands Grid (Matching Screenshot 7)
  function renderCarBrandsView() {
    const container = document.getElementById('screen-car-brands');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Theo hãng xe</div>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">24 hãng · phổ thông đến châu Âu</div>
      </div>
      <div style="padding:14px;">
        <div class="brand-grid">
          ${carBrandsDB.map(b => `
            <div class="brand-card" onclick="window.App.navigateTo('search')">
              <div class="brand-card-name">${b.name}</div>
              <div class="brand-card-count">${b.count}</div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  // 6. Render Part Brands List (Matching Screenshot 8)
  function renderPartBrandsView() {
    const container = document.getElementById('screen-part-brands');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Thương hiệu</div>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">Nguồn hàng OEM đã xác minh giấy phép</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); overflow:hidden;">
          ${partBrandsDB.map(pb => `
            <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; cursor:pointer;" onclick="window.App.navigateTo('search')">
              <div>
                <div style="font-size:13.5px; font-weight:800; color:var(--navy-dark);">${pb.name}</div>
                <div style="font-size:11px; color:var(--gray-text-sub);">${pb.origin}</div>
              </div>
              <span style="font-size:11px; font-weight:700; color:var(--gray-text-sub);">${pb.count}</span>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  // 7. Render Product Detail
  function renderProductDetailView() {
    const p = state.selectedProduct || productsDB[0];
    const container = document.getElementById('screen-product-detail');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:12px 16px; display:flex; align-items:center; justify-content:space-between;">
        <span class="back-link" onclick="window.App.navigateTo('home')">← Quay lại</span>
        <span style="font-size:14px; font-weight:700;">Chi tiết phụ tùng</span>
        <span></span>
      </div>
      <div style="background:#fff; padding:16px;">
        <div style="width:100%; height:230px; background:#f8fafc; border-radius:14px; overflow:hidden; display:flex; align-items:center; justify-content:center; border:1px solid var(--gray-border);">
          <img src="${p.image}" style="max-width:100%; max-height:100%; object-fit:contain;" onerror="this.src='/favicon-512x512.png'">
        </div>
        <div style="margin-top:14px;">
          <span style="background:var(--navy-dark); color:#fff; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">${p.sku}</span>
          <span style="color:var(--gray-text-sub); font-size:11px; font-weight:600; margin-left:6px;">OEM: ${p.oem}</span>
          <h2 style="font-size:15px; font-weight:800; color:var(--navy-dark); margin:8px 0; line-height:1.3;">${p.name}</h2>
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="font-size:18px; font-weight:800; color:var(--orange-accent);">${fmtVND(p.price)}</span>
            ${p.oldPrice ? `<span style="font-size:13px; color:var(--gray-text-sub); text-decoration:line-through;">${fmtVND(p.oldPrice)}</span>` : ''}
          </div>
        </div>

        <div style="border-top:1px solid var(--gray-border); padding-top:12px; margin-top:12px;">
          <h4 style="font-size:13px; font-weight:700; color:var(--navy-dark); margin-bottom:6px;">Mô tả sản phẩm:</h4>
          <p style="font-size:12px; color:var(--gray-text-sub); line-height:1.5;">${p.desc || 'Sản phẩm linh kiện điện lạnh điều hòa chính hãng.'}</p>
        </div>

        <div style="margin-top:20px; display:flex; gap:10px;">
          <a href="tel:0705070526" class="btn-navy" style="flex:1;">
            Gọi Hotline
          </a>
          <button class="btn-orange" style="flex:1.8;" onclick="window.App.addToCart(${p.id}); window.App.navigateTo('cart');">
            Thêm vào Giỏ Hàng
          </button>
        </div>
      </div>
    `;
  }

  // 8. Render Cart View (Matching Screenshot 11)
  function renderCartView() {
    const container = document.getElementById('cart-items-list');
    const summary = document.getElementById('cart-summary-box');
    if (!container) return;

    if (state.cart.length === 0) {
      container.innerHTML = `<div style="text-align:center; padding:40px 20px; color:var(--gray-text-sub); font-weight:600;">Giỏ hàng đang trống!</div>`;
      if (summary) summary.style.display = 'none';
      return;
    }

    if (summary) summary.style.display = 'block';

    container.innerHTML = state.cart.map(item => `
      <div style="background:#fff; border:1px solid var(--gray-border); border-radius:14px; padding:12px; margin-bottom:10px; display:flex; gap:12px; align-items:center;">
        <img src="${item.image}" style="width:65px; height:65px; object-fit:cover; border-radius:8px;" onerror="this.src='/favicon-512x512.png'">
        <div style="flex:1;">
          <div style="font-size:10px; color:var(--gray-text-sub); font-weight:700;">${item.sku}</div>
          <div style="font-size:12px; font-weight:700; color:var(--navy-dark); line-height:1.3; margin:2px 0;">${item.name}</div>
          <div style="font-size:13.5px; font-weight:800; color:var(--orange-accent);">${fmtVND(item.price)}</div>
        </div>
        <div style="display:flex; align-items:center; gap:6px;">
          <button style="width:26px; height:26px; border:1px solid var(--gray-border); background:#fff; border-radius:6px; font-weight:800;" onclick="window.App.updateCartQty(${item.id}, -1)">-</button>
          <span style="font-size:13px; font-weight:700;">${item.qty}</span>
          <button style="width:26px; height:26px; border:1px solid var(--gray-border); background:#fff; border-radius:6px; font-weight:800;" onclick="window.App.updateCartQty(${item.id}, 1)">+</button>
        </div>
      </div>
    `).join('');

    const total = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    document.getElementById('cart-total-price').textContent = fmtVND(total);
  }

  // 9. Render Checkout View (Matching Screenshot 12)
  function renderCheckoutView() {
    const total = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    document.getElementById('checkout-total-price').textContent = fmtVND(total);
  }

  // 10. Render Orders List View (Matching Screenshot 14)
  function renderOrdersView() {
    const list = document.getElementById('orders-list-container');
    if (!list) return;

    list.innerHTML = `
      <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:14px; margin-bottom:12px;" onclick="window.App.navigateTo('order-tracking')">
        <div style="display:flex; justify-content:space-between; align-items:center; font-size:11.5px; margin-bottom:8px;">
          <span style="color:var(--gray-text-sub); font-weight:700;">Mã đơn: CS-2688-04127</span>
          <span style="background:#e0f2fe; color:#0369a1; font-size:10.5px; font-weight:800; padding:2px 8px; border-radius:10px;">Đang giao</span>
        </div>
        <div style="font-size:13px; font-weight:700; color:var(--navy-dark); margin-bottom:4px;">Dàn lạnh điều hòa Toyota Innova 2017</div>
        <div style="font-size:11.5px; color:var(--gray-text-sub);">+2 sản phẩm khác</div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; border-top:1px solid #f1f5f9; padding-top:10px;">
          <span style="font-size:14px; font-weight:800; color:var(--navy-dark);">4.310.000 ₫</span>
          <span class="section-link">Xem chi tiết ›</span>
        </div>
      </div>
    `;
  }

  // 11. Render Order Tracking View (Matching Screenshot 15)
  function renderOrderTrackingView() {
    const container = document.getElementById('screen-order-tracking');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <span class="back-link" onclick="window.App.navigateTo('orders')">← CS-2688-04127</span>
        <div style="font-size:16px; font-weight:800; margin-top:4px;">Đang trên đường giao</div>
        <div style="font-size:11px; opacity:0.8;">Dự kiến hôm nay 09–12h · Viettel Post</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:14px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Anh Hùng · Shipper</div>
            <div style="font-size:11px; color:var(--gray-text-sub);">0911.456.78</div>
          </div>
          <button class="btn-navy" style="width:auto; padding:6px 14px; font-size:11px;">Gọi</button>
        </div>

        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:16px;">
          <div style="border-left:2px solid var(--navy-dark); padding-left:14px;">
            <div style="margin-bottom:14px;">
              <div style="font-size:12.5px; font-weight:800; color:var(--navy-dark);">● Shipper đang giao</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">Cách bạn 3,2 km — Long Biên, Hà Nội / Hôm nay 08:41</div>
            </div>
            <div style="margin-bottom:14px;">
              <div style="font-size:12.5px; font-weight:700; color:var(--navy-dark);">● Rời kho Cooling Long Biên</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">Đã bàn giao Viettel Post · VN2608412</div>
            </div>
            <div style="margin-bottom:14px;">
              <div style="font-size:12.5px; font-weight:700; color:var(--navy-dark);">● Đóng gói & kiểm mã OE</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">Kĩ thuật đối chiếu 88501-0K390 — khớp</div>
            </div>
            <div>
              <div style="font-size:12.5px; font-weight:700; color:var(--navy-dark);">● Đặt hàng thành công</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">Thanh toán COD khi nhận hàng</div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // 12. Render Warranty View (Matching Screenshot 16)
  function renderWarrantyView() {
    const container = document.getElementById('warranty-container');
    if (!container) return;

    container.innerHTML = `
      <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:16px;">
        <h4 style="font-size:12px; font-weight:700; color:var(--gray-text-sub); margin-bottom:6px;">Mã phiếu bảo hành / Serial</h4>
        <input type="text" value="BH-88501-0K390" style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; font-size:13px; font-weight:700; margin-bottom:14px;">
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:12px; margin-bottom:14px;">
          <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:#047857; margin-bottom:6px;">
            <span>Còn bảo hành</span>
            <span>còn 7 tháng</span>
          </div>
          <div style="font-size:13px; font-weight:700; color:var(--navy-dark);">Dàn lạnh điều hòa Toyota Innova 2017</div>
        </div>
        <button class="btn-orange" style="margin-bottom:10px;" onclick="alert('Đã tạo yêu cầu hỗ trợ bảo hành thành công!')">Tạo yêu cầu bảo hành</button>
        <button class="btn-outline">Gửi ảnh & mô tả lỗi</button>
      </div>
    `;
  }

  // 13. Render Coupons View (Matching Screenshot 17)
  function renderCouponsView() {
    const container = document.getElementById('screen-coupons');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Khuyến mại</div>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">Áp dụng tự động khi thanh toán</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:12px; border:1px solid var(--gray-border); padding:14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Giảm 10% phụ tùng điện lạnh</div>
            <div style="font-size:11px; color:var(--gray-text-sub);">Đơn từ 2 triệu - áp dụng dàn lạnh, lốc...</div>
          </div>
          <button class="btn-outline" style="width:auto; padding:6px 12px; font-size:11px;">Lưu</button>
        </div>
        <div style="background:#fff; border-radius:12px; border:1px solid var(--gray-border); padding:14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Miễn phí giao toàn quốc</div>
            <div style="font-size:11px; color:var(--gray-text-sub);">Tự động áp dụng đơn từ 2 triệu</div>
          </div>
          <button class="btn-outline" style="width:auto; padding:6px 12px; font-size:11px;">Lưu</button>
        </div>
      </div>
    `;
  }

  // 14. Render Stores View (Matching Screenshot 19)
  function renderStoresView() {
    const container = document.getElementById('screen-stores');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Cửa hàng & kho</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:14px; margin-bottom:12px;">
          <div style="font-size:13.5px; font-weight:800; color:var(--navy-dark);">Cooling Long Biên — Kho tổng</div>
          <div style="font-size:11px; color:var(--gray-text-sub); margin:3px 0 8px;">Số 12 Nguyễn Văn Cừ, P. Gia Thụy, Long Biên, Hà Nội</div>
          <div style="display:flex; gap:10px;">
            <a href="tel:0786976626" class="btn-navy" style="flex:1; padding:8px; font-size:11.5px;">Gọi</a>
            <button class="btn-outline" style="flex:1; padding:8px; font-size:11.5px;">Chỉ đường</button>
          </div>
        </div>
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:14px; margin-bottom:12px;">
          <div style="font-size:13.5px; font-weight:800; color:var(--navy-dark);">Cooling Hà Đông</div>
          <div style="font-size:11px; color:var(--gray-text-sub); margin:3px 0 8px;">145 Quang Trung, P. Quang Trung, Hà Đông, Hà Nội</div>
          <div style="display:flex; gap:10px;">
            <a href="tel:0783976315" class="btn-navy" style="flex:1; padding:8px; font-size:11.5px;">Gọi</a>
            <button class="btn-outline" style="flex:1; padding:8px; font-size:11.5px;">Chỉ đường</button>
          </div>
        </div>
      </div>
    `;
  }

  // 15. Render Account View (Matching Screenshot 20)
  function renderAccountView() {
    const container = document.getElementById('account-container');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:20px 16px; text-align:center;">
        <div style="width:54px; height:54px; border-radius:50%; background:#244270; color:#fff; font-weight:800; font-size:20px; display:flex; align-items:center; justify-content:center; margin:0 auto 8px;">TC</div>
        <h3 style="font-size:16px; font-weight:800;">Gara Thành Công</h3>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">0912 345 678</div>
      </div>
      
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; padding:14px; border:1px solid var(--gray-border); margin-bottom:14px; display:flex; text-align:center;">
          <div style="flex:1;">
            <div style="font-size:16px; font-weight:800; color:var(--navy-dark);">12</div>
            <div style="font-size:10.5px; color:var(--gray-text-sub);">Đơn tháng này</div>
          </div>
          <div style="flex:1; border-left:1px solid #f1f5f9; border-right:1px solid #f1f5f9;">
            <div style="font-size:16px; font-weight:800; color:var(--navy-dark);">118tr</div>
            <div style="font-size:10.5px; color:var(--gray-text-sub);">Doanh số</div>
          </div>
          <div style="flex:1;">
            <div style="font-size:16px; font-weight:800; color:var(--orange-accent);">24tr</div>
            <div style="font-size:10.5px; color:var(--gray-text-sub);">Công nợ</div>
          </div>
        </div>

        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); overflow:hidden; margin-bottom:16px;">
          <div style="padding:14px; border-bottom:1px solid #f1f5f9; font-weight:700; color:var(--navy-dark); font-size:13px;" onclick="window.App.navigateTo('coupons')">Bảng giá buôn gốc</div>
          <div style="padding:14px; border-bottom:1px solid #f1f5f9; font-weight:700; color:var(--navy-dark); font-size:13px;" onclick="window.App.navigateTo('orders')">Công nợ & hóa đơn</div>
          <div style="padding:14px; border-bottom:1px solid #f1f5f9; font-weight:700; color:var(--navy-dark); font-size:13px;" onclick="window.App.navigateTo('vehicle-search')">Xe đã lưu</div>
          <div style="padding:14px; border-bottom:1px solid #f1f5f9; font-weight:700; color:var(--navy-dark); font-size:13px;" onclick="window.App.navigateTo('warranty')">Phiếu bảo hành</div>
          <div style="padding:14px; font-weight:700; color:var(--navy-dark); font-size:13px;" onclick="window.App.navigateTo('stores')">Hệ thống cửa hàng & kho</div>
        </div>

        <button class="btn-outline" style="color:var(--red-alert); border-color:#fecaca;" onclick="alert('Đã đăng xuất!')">Đăng xuất</button>
      </div>
    `;
  }

  // 16. Render Welcome Screen (Matching Screenshot 1)
  function renderWelcomeView() {
    const container = document.getElementById('screen-welcome');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); min-height:100%; color:#fff; padding:30px 20px; display:flex; flex-direction:column; justify-content:space-between;">
        <div style="text-align:center;">
          <img src="/favicon-512x512.png" style="width:80px; height:80px; margin-bottom:20px;">
          <h1 style="font-size:22px; font-weight:800; line-height:1.3; margin-bottom:20px;">Phụ tùng điện lạnh ô tô, tra đúng xe trong 30 giây.</h1>
          
          <div style="text-align:left; font-size:13px; line-height:1.8; opacity:0.9;">
            <div style="margin-bottom:8px;">01. <b>Chính hãng OEM</b> (Denso, Valeo, Hanon, Sanden...)</div>
            <div style="margin-bottom:8px;">02. <b>Giao 24h toàn quốc</b> (Miễn phí ship đơn từ 2 triệu)</div>
            <div>03. <b>Bảo hành 6 – 24 tháng</b> (Theo tiêu chuẩn nhà sản xuất)</div>
          </div>
        </div>

        <div>
          <button class="btn-orange" style="margin-bottom:10px;" onclick="window.App.navigateTo('vehicle-search')">Tìm phụ tùng cho xe của tôi</button>
          <button class="btn-navy" style="background:#244270;" onclick="window.App.navigateTo('login')">Đăng nhập Gara / Đại lý</button>
        </div>
      </div>
    `;
  }

  // 17. Render Login Screen (Matching Screenshot 2)
  function renderLoginView() {
    const container = document.getElementById('screen-login');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:20px 16px;">
        <span class="back-link" onclick="window.App.navigateTo('home')">← Quay lại</span>
        <div style="font-size:18px; font-weight:800; margin-top:10px;">Xin chào, Đăng nhập tài khoản</div>
      </div>
      <div style="padding:16px;">
        <div style="background:#fff; border-radius:14px; padding:16px; border:1px solid var(--gray-border);">
          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Số điện thoại</label>
          <input type="text" value="0912 345 678" style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; margin-bottom:12px; font-size:13px;">

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Mật khẩu</label>
          <input type="password" value="12345678" style="width:100%; padding:10px; border:1px solid var(--gray-border); border-radius:8px; margin-bottom:16px; font-size:13px;">

          <button class="btn-orange" onclick="window.App.navigateTo('account')">Đăng nhập</button>
        </div>
      </div>
    `;
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
    fetchLiveData();
    navigateTo('home');
  });

})();
