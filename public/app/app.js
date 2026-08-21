/* ==========================================================================
   COOLING SYSTEMS MOBILE APP - EXACT DESIGN SPEC (LOGIN & DOWNWARDS SELECTS)
   ========================================================================== */

(function () {
  'use strict';

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
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC PORTER 4PK 12V BỘ ĐẦU STAREX HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&"
  },
  {
    "id": 6889,
    "sku": "TCA-LIVE-48-72",
    "oem": "KZH-10-0025",
    "name": "BỘ ĐẦU LỐC KIA CARENS",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_kia_carens.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC KIA CARENS <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: "
  },
  {
    "id": 6888,
    "sku": "TCA-LIVE-48-71",
    "oem": "ZZH-23-0013",
    "name": "BỘ ĐẦU LỐC ISUZU DMAX",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Isuzu",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_isuzu_dmax_ch_nh_h_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ISUZU DMAX <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: "
  },
  {
    "id": 6887,
    "sku": "TCA-LIVE-48-70",
    "oem": "TZH-50022",
    "name": "BỘ ĐẦU LỐC VIOS 2010 135",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_vios_2010_135_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2010 135 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; heigh"
  },
  {
    "id": 6886,
    "sku": "TCA-LIVE-48-69",
    "oem": "BD",
    "name": "BỘ ĐẦU LỐC 508 12V",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-508-12v-tca-live-48-69-20260804073548-fbf9761c.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 508 12V <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <str"
  },
  {
    "id": 6885,
    "sku": "TCA-LIVE-48-68",
    "oem": "S825-400859",
    "name": "BỘ ĐẦU LỐC TM 16",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-tm-16-tca-live-48-68-20260804073614-59f5862c.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC TM 16 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <stron"
  },
  {
    "id": 6884,
    "sku": "TCA-LIVE-48-67",
    "oem": "E4OOML3AA",
    "name": "BỘ ĐẦU LỐC ESCAPE 2.3 BỘ ĐẦU STAREX 7PK HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/b_u_l_c_escape_2.3_b_u_starex_7pk_hcc_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ESCAPE 2.3 BỘ ĐẦU STAREX 7PK HANON <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"m"
  },
  {
    "id": 6883,
    "sku": "TCA-LIVE-48-66",
    "oem": "TZH-50109",
    "name": "BỘ ĐẦU LỐC INNOVA 2017",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_innova_2017.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC INNOVA 2017 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height:"
  },
  {
    "id": 6882,
    "sku": "TCA-LIVE-48-65",
    "oem": "HD5501226",
    "name": "BỘ ĐẦU LỐC EVEREST RANGER 2010",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-everest-ranger-2010-tca-live-48-65-20260804073631-a6b25488.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC EVEREST RANGER 2010 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Li"
  },
  {
    "id": 6881,
    "sku": "TCA-LIVE-48-64",
    "oem": "BDL_VIOS4PK",
    "name": "BỘ ĐẦU LỐC VIOS 2012 4PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_vios_2012_4pk.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2012 4PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; heigh"
  },
  {
    "id": 6880,
    "sku": "TCA-LIVE-48-63",
    "oem": "BD1214",
    "name": "BỘ ĐẦU LỐC HYUNDAI BẢN A 12V BỘ ĐẦU KIA 1.4T",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-huyndai-ban-a-12v-bo-dau-kia-1-4t-phu-tung-oto-thanh-cong.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HYUNDAI BẢN A 12V BỘ ĐẦU KIA 1.4T <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"ma"
  },
  {
    "id": 6879,
    "sku": "TCA-LIVE-48-62",
    "oem": "HZH-90048",
    "name": "BỘ ĐẦU LỐC SOLATI",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_solati_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SOLATI <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: auto"
  },
  {
    "id": 6878,
    "sku": "TCA-LIVE-48-61",
    "oem": "BDL_LAND",
    "name": "BỘ ĐẦU LỐC LAND CRUISER BẢN A",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-land-cruiser-ban-a-tca-live-48-61-20260804073651-6ffb10bb.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC LAND CRUISER BẢN A <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Lin"
  },
  {
    "id": 6877,
    "sku": "TCA-LIVE-48-60",
    "oem": "YX10301",
    "name": "BỘ ĐẦU LỐC NISSAN TEANA 3.5T",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Nissan",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_nissan_teana_3.5t.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC NISSAN TEANA 3.5T <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; h"
  },
  {
    "id": 6876,
    "sku": "TCA-LIVE-48-59",
    "oem": "245/50R400254",
    "name": "BỘ ĐẦU LỐC 30C",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-30c-tca-live-48-59-20260804073710-c57c8098.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 30C <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <strong>"
  },
  {
    "id": 6875,
    "sku": "TCA-LIVE-48-58",
    "oem": "BDL_HD24V",
    "name": "BỘ ĐẦU LỐC HUYNDAI BẢN A 24V HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-huyndai-ban-a-24v-hanon-tca-live-48-58-20260804073726-e076606c.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HUYNDAI BẢN A 24V HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng X"
  },
  {
    "id": 6874,
    "sku": "TCA-LIVE-48-57",
    "oem": "CZH-16-034",
    "name": "BỘ ĐẦU LỐC SUZUKI ERTIGA BỘ ĐẦU CIAZ",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Suzuki",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_suzuki_ertiga_b_u_ciaz.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SUZUKI ERTIGA BỘ ĐẦU CIAZ <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width:"
  },
  {
    "id": 6873,
    "sku": "TCA-LIVE-48-56",
    "oem": "HD5501254",
    "name": "BỘ ĐẦU LỐC 508 24V 8PK BỘ ĐẦU 508 HÀNG XỊN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_508_24v_8pk_b_u_508_h_ng_x_n_ph_t_ng_t_th_nh_c_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 508 24V 8PK BỘ ĐẦU 508 H&Agrave;NG XỊN <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img styl"
  },
  {
    "id": 6872,
    "sku": "TCA-LIVE-48-55",
    "oem": "BD0007",
    "name": "BỘ ĐẦU LỐC HYUNDAI PORTER 7PK BỘ ĐẦU LỐC PORTER",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-porter-7pk-phu_tung_oto_thanh_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HYUNDAI PORTER 7PK BỘ ĐẦU LỐC PORTER <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style="
  },
  {
    "id": 6871,
    "sku": "TCA-LIVE-48-54",
    "oem": "TZH-50030",
    "name": "BỘ ĐẦU LỐC CAMRY 2.4 2003 5PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_camry_2.4_2003_5pk_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CAMRY 2.4 2003 5PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; "
  },
  {
    "id": 6870,
    "sku": "TCA-LIVE-48-53",
    "oem": "FK402A",
    "name": "Bộ đầu lốc FK40 Bộ đầu xe khách FK40",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_fk40_b_u_xe_kh_ch_fk40_ch_nh_h_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc FK40 Bộ đầu xe kh&aacute;ch FK40 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max"
  },
  {
    "id": 6869,
    "sku": "TCA-LIVE-48-52",
    "oem": "HD5501189",
    "name": "BỘ ĐẦU LỐC HCC BẢN A 12V HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-ban-a-12v-hanon_phu_tung_oto_than_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HCC BẢN A 12V HANON <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%;"
  },
  {
    "id": 6868,
    "sku": "TCA-LIVE-48-51",
    "oem": "165/65R400338 TY",
    "name": "BỘ ĐẦU LỐC FORD ESCAPE 3.0",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-ford-escape-3-3.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC FORD ESCAPE 3.0 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh k"
  },
  {
    "id": 6867,
    "sku": "TCA-LIVE-48-50",
    "oem": "976062P95979085",
    "name": "BỘ ĐẦU LỐC PORTER 4PK XỊN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_porter_4pk_b_u_hyundai.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC PORTER 4PK XỊN <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; heig"
  },
  {
    "id": 6866,
    "sku": "TCA-LIVE-48-49",
    "oem": "CLS-400619",
    "name": "BỘ ĐẦU LỐC TRANSIT 2005",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-transit-2005-tca-live-48-49-20260804073802-8ec3aac1.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC TRANSIT 2005 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện"
  },
  {
    "id": 6865,
    "sku": "TCA-LIVE-48-48",
    "oem": "NZH-60057",
    "name": "BỘ ĐẦU LỐC NISSAN TEANA 2.0",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Nissan",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_nissan_teana_2.0.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC NISSAN TEANA 2.0 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; he"
  },
  {
    "id": 6864,
    "sku": "TCA-LIVE-48-47",
    "oem": "BD0081",
    "name": "BỘ ĐẦU LỐC INNOVA G DENSO BỘ ĐẦU INNOVA G",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Denso",
    "image": "/uploads/products/b_u_l_c_innova_g_denso_b_u_innova_g_ph_t_ng_t_th_nh_c_ng_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC INNOVA G DENSO BỘ ĐẦU INNOVA G <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-w"
  },
  {
    "id": 6863,
    "sku": "TCA-LIVE-48-46",
    "oem": "TZH-50088",
    "name": "BỘ ĐẦU LỐC VIOS 2016 6PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_vios_2016_6pk.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2016 6PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; heigh"
  },
  {
    "id": 6862,
    "sku": "TCA-LIVE-48-45",
    "oem": "SP0000019",
    "name": "BỘ ĐẦU LỐC BONGO 3",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-bongo-3_phu_tung_oto_thanh_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC BONGO 3 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: aut"
  },
  {
    "id": 6861,
    "sku": "TCA-LIVE-48-44",
    "oem": "00022",
    "name": "BỘ ĐẦU LỐC STAREX 6PK HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/b_u_l_c_starex_6pk_hanon_ch_nh_h_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC STAREX 6PK HANON <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; he"
  },
  {
    "id": 6860,
    "sku": "TCA-LIVE-48-43",
    "oem": "B237PL",
    "name": "BỘ ĐẦU LỐC ESCAPE 2.3 BỘ ĐẦU STAREX 7PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_escape_2.3_b_u_starex_7pk_ph_t_ng_t_th_nh_c_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ESCAPE 2.3 BỘ ĐẦU STAREX 7PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-wid"
  },
  {
    "id": 6859,
    "sku": "TCA-LIVE-48-42",
    "oem": "GZH-11-0030",
    "name": "BỘ ĐẦU LỐC LACETTI 2009 CDX",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Daewoo",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_lacetti_09_cdx.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC LACETTI 2009 CDX <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; he"
  },
  {
    "id": 6858,
    "sku": "TCA-LIVE-48-41",
    "oem": "BD21",
    "name": "BỘ ĐẦU LỐC COUNTY 21",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-county-21-phu_tung_oto_thanh_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC COUNTY 21 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: a"
  },
  {
    "id": 6857,
    "sku": "TCA-LIVE-48-40",
    "oem": "BDA17",
    "name": "BỘ ĐẦU LỐC COUNTY A17 HÀNG XỊN CHÍNH HÃNG",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/_u_l_c_county_ph_t_ng_t_th_nh_c_ng_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC COUNTY A17 H&Agrave;NG XỊN CH&Iacute;NH H&Atilde;NG <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px "
  },
  {
    "id": 6856,
    "sku": "TCA-LIVE-48-39",
    "oem": "BDL_505",
    "name": "BỘ ĐẦU LỐC 505",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-505-tca-live-48-39-20260804073819-431fafad.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 505 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <strong>"
  },
  {
    "id": 6855,
    "sku": "TCA-LIVE-48-38",
    "oem": "SP5501653",
    "name": "Bộ đầu lốc Innova Bộ đầu lốc Fortuner 7PK Bộ",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_innova_b_u_l_c_fortuner_7pk_b_u_toyoto_ch_nh_h_ng_denso.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Innova Bộ đầu lốc Fortuner 7PK Bộ ... <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style"
  },
  {
    "id": 6854,
    "sku": "TCA-LIVE-48-37",
    "oem": "DN5501558",
    "name": "BỘ ĐẦU LỐC ALTIS 2011 KHÔNG BÔN",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-altis-2011-khong-bon-tca-live-48-37-20260804074904-fb633a87.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ALTIS 2011 KH&Ocirc;NG B&Ocirc;N <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&og"
  },
  {
    "id": 6853,
    "sku": "TCA-LIVE-48-36",
    "oem": "BDL_MA323",
    "name": "BỘ ĐẦU LỐC FORD LASER 1.6 323 BỘ ĐẦU JOLIE",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_ford_laser_1.6_323_b_u_jolie_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC FORD LASER 1.6 323 BỘ ĐẦU JOLIE <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-"
  },
  {
    "id": 6852,
    "sku": "TCA-LIVE-48-35",
    "oem": "TZH-50048",
    "name": "BỘ ĐẦU LỐC INNOVA BỘ ĐẦU FORTUNER MÁY XĂNG",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_innova_b_u_fortuner_may_xang.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC INNOVA BỘ ĐẦU FORTUNER M&Aacute;Y XĂNG <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img styl"
  },
  {
    "id": 6851,
    "sku": "TCA-LIVE-48-34",
    "oem": "BD0I10",
    "name": "BỘ ĐẦU LỐC HYUNDAI I10 GRAND BỘ ĐẦU LỐC I10 GRAND",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-i10-grand-phu_tung_oto_thanh_cong.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HYUNDAI I10 GRAND BỘ ĐẦU LỐC I10 GRAND <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img styl"
  },
  {
    "id": 6850,
    "sku": "TCA-LIVE-48-33",
    "oem": "HD5501188",
    "name": "BỘ ĐẦU LỐC JOLIE BẢN PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-jolie-ban-pk-tca-live-48-33-20260804074920-89746cf7.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC JOLIE BẢN PK <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện"
  },
  {
    "id": 6849,
    "sku": "TCA-LIVE-48-32",
    "oem": "SP000042",
    "name": "BỘ ĐẦU LỐC HYUNDAI 2.5T 2018-2020 12V 7PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-hyundai-2-5t-phu-tung-oto-thanh-cong.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HYUNDAI 2.5T 2018-2020 12V 7PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-w"
  },
  {
    "id": 6848,
    "sku": "TCA-LIVE-48-31",
    "oem": "P95979140",
    "name": "BỘ ĐẦU LỐC VIOS 2017 6PK DENSO",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Denso",
    "image": "/uploads/products/bo-dau-loc-vios-2017-6pk-denso-tca-live-48-31-20260804074951-365eb3b1.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2017 6PK DENSO <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Li"
  },
  {
    "id": 6847,
    "sku": "TCA-LIVE-48-30",
    "oem": "CLS-SN5H14-24V400628",
    "name": "BỘ ĐẦU LỐC HOVO 24V",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-hovo-24v-tca-live-48-30-20260804075012-fa7f617c.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC HOVO 24V <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <st"
  },
  {
    "id": 6846,
    "sku": "TCA-LIVE-48-29",
    "oem": "DCB03",
    "name": "BỘ ĐẦU LỐC RẮC CO",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-rac-co-tca-live-48-29-20260804075028-d143183b.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC RẮC CO <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <stro"
  },
  {
    "id": 6845,
    "sku": "TCA-LIVE-48-28",
    "oem": "BD_TR",
    "name": "BỘ ĐẦU LỐC TRITON BỘ ĐẦU MITSUBISHI",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mitsubishi",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_triton_b_u_mitsubishi.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC TRITON BỘ ĐẦU MITSUBISHI <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: "
  },
  {
    "id": 6844,
    "sku": "TCA-LIVE-48-27",
    "oem": "97139-S95979157",
    "name": "BỘ ĐẦU LỐC ELANTRA",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_elantra.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ELANTRA <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: aut"
  },
  {
    "id": 6843,
    "sku": "TCA-LIVE-48-26",
    "oem": "245/50R400266",
    "name": "BỘ ĐẦU LỐC MATIZ BÃI",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Daewoo",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-matiz-bai-tca-live-48-26-20260804075046-961981f0.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MATIZ B&Atilde;I <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh "
  },
  {
    "id": 6842,
    "sku": "TCA-LIVE-48-25",
    "oem": "B50824V",
    "name": "BỘ ĐẦU LỐC 508 24V BỘ ĐẦU 508 24V",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_508_24v_h_ng_x_n_ch_nh_h_ng_ph_t_ng_t_th_nh_c_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 508 24V BỘ ĐẦU 508 24V <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 10"
  },
  {
    "id": 6841,
    "sku": "TCA-LIVE-48-24",
    "oem": "HZH-40014",
    "name": "BỘ ĐẦU LỐC CIVIC 1.8 BỘ ĐẦU CRV 2.0",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Honda",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_civic_1.8_b_u_crv_2.0.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CIVIC 1.8 BỘ ĐẦU CRV 2.0 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: "
  },
  {
    "id": 6840,
    "sku": "TCA-LIVE-48-23",
    "oem": "FZH-12-0029",
    "name": "BỘ ĐẦU LỐC RANGER 2013 BỘ ĐẦU LỐC BT50",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_bt50_b_u_l_c_ranger_2013.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC RANGER 2013 BỘ ĐẦU LỐC BT50 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-widt"
  },
  {
    "id": 6839,
    "sku": "TCA-LIVE-48-22",
    "oem": "DN95979040",
    "name": "BỘ ĐẦU LỐC MB 140",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-mb-140-tca-live-48-22-20260804075107-5b3ec39c.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MB 140 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <stro"
  },
  {
    "id": 6838,
    "sku": "TCA-LIVE-48-21",
    "oem": "P95979113 HBS",
    "name": "BỘ ĐẦU LỐC CHENGLONG H7",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-chenglong-h7-tca-live-48-21-20260804075124-fa169ef0.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CHENGLONG H7 <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện"
  },
  {
    "id": 6837,
    "sku": "TCA-LIVE-48-20",
    "oem": "BV323343",
    "name": "BỘ ĐẦU LỐC PAJERO V32 BỘ ĐẦU ZACE V33 V43",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mitsubishi",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_pajero_v32_b_u_zace_v33_v43.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC PAJERO V32 BỘ ĐẦU ZACE V33 V43 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-w"
  },
  {
    "id": 6836,
    "sku": "TCA-LIVE-48-19",
    "oem": "SP5501971",
    "name": "Bộ đầu lốc Honda City Bộ đầu lốc City 6PK 2022",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Honda",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_honda_city_b_u_l_c_city_6pk_2022_phutungotothanhcong.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Honda City Bộ đầu lốc City 6PK 2022 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\""
  },
  {
    "id": 6835,
    "sku": "TCA-LIVE-48-18",
    "oem": "BD006",
    "name": "BỘ ĐẦU LỐC MORNING 6PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-morning-6pk-phu_tung_oto_thanh_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MORNING 6PK <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height:"
  },
  {
    "id": 6834,
    "sku": "TCA-LIVE-48-17",
    "oem": "BDL_VIOS149",
    "name": "BỘ ĐẦU LỐC VIOS 2007 149",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_vios_2007_b_u_149_ch_nh_h_ng.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC VIOS 2007 149 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; heigh"
  },
  {
    "id": 6833,
    "sku": "TCA-LIVE-48-16",
    "oem": "SP00018",
    "name": "BỘ ĐẦU LỐC ALTIS",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-altis_phu_tung_oto_thanh_cong_auto.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC ALTIS <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: auto;"
  },
  {
    "id": 6832,
    "sku": "TCA-LIVE-48-15",
    "oem": "CLS-VN400564T",
    "name": "BỘ ĐẦU LỐC CAMRY 2.2 BÃI",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-camry-2-2.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC CAMRY 2.2 B&Atilde;I <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe L"
  },
  {
    "id": 6831,
    "sku": "TCA-LIVE-48-14",
    "oem": "L1000.5501408",
    "name": "BỘ ĐẦU LỐC SANTAFE GOLD 6PK HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/bo-dau-loc-santafe-gold-6pk-hanon-tca-live-48-14-20260804075230-73031dc7.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SANTAFE GOLD 6PK HANON <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe"
  },
  {
    "id": 6830,
    "sku": "TCA-LIVE-48-13",
    "oem": "DN5501557",
    "name": "BỘ ĐẦU LỐC LIVINA BỘ ĐẦU TIDA",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-livina-bo-dau-tida-tca-live-48-13-20260804075256-97e03571.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC LIVINA BỘ ĐẦU TIDA <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Lin"
  },
  {
    "id": 6829,
    "sku": "TCA-LIVE-48-12",
    "oem": "ZH-8007",
    "name": "BỘ ĐẦU LỐC MAZDA 3 BỘ ĐẦU LỐC MAZDA 6",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mazda",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_mazda_3.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC MAZDA 3 BỘ ĐẦU LỐC MAZDA 6 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width"
  },
  {
    "id": 6828,
    "sku": "TCA-LIVE-48-11",
    "oem": "S825-400732",
    "name": "BỘ ĐẦU LỐC 507 12V",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-507-12v-tca-live-48-11-20260804075311-e0ab131f.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC 507 12V <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <str"
  },
  {
    "id": 6827,
    "sku": "TCA-LIVE-48-10",
    "oem": "BĐBG",
    "name": "BỘ ĐẦU LỐC BITZER",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Chính Hãng",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-bitzer-tca-live-48-10-20260804075324-070efe53.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC BITZER <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <stro"
  },
  {
    "id": 6826,
    "sku": "TCA-LIVE-48-9",
    "oem": "BDL_STG",
    "name": "BỘ ĐẦU LỐC SANTAFE GOLD 6PK HANON",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Hanon",
    "image": "/uploads/products/b_u_santafe_gold_6pk_ch_nh_h_ng_hanon.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SANTAFE GOLD 6PK HANON <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 10"
  },
  {
    "id": 6825,
    "sku": "TCA-LIVE-48-8",
    "oem": "60N4HEMUKF",
    "name": "Bộ đầu lốc Triton Bộ đầu lốc Zinger 2008 4PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Mitsubishi",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_triton_b_u_l_c_zinger_2008_4pk_phutung.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Triton Bộ đầu lốc Zinger 2008 4PK (1) <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style"
  },
  {
    "id": 6824,
    "sku": "TCA-LIVE-48-7",
    "oem": "SA22C95978752",
    "name": "BỘ ĐẦU LỐC SOLATI",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Hyundai",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-solati-tca-live-48-7-20260803102119-52097ce9.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC SOLATI <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <stro"
  },
  {
    "id": 6823,
    "sku": "TCA-LIVE-48-6",
    "oem": "FZH-12-0037",
    "name": "BỘ ĐẦU LỐC RANGER 3.2",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Ford",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_ranger_3.2_x_n.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC RANGER 3.2 <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: "
  },
  {
    "id": 6822,
    "sku": "TCA-LIVE-48-5",
    "oem": "KZH-10-0002",
    "name": "BỘ ĐẦU LỐC KIA FORTE",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "KIA",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_kia_forte.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC KIA FORTE <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-width: 100%; height: a"
  },
  {
    "id": 6821,
    "sku": "TCA-LIVE-48-4",
    "oem": "BĐLSW",
    "name": "Bộ đầu lốc Suzuki Swift Bộ đầu lốc Swift",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Suzuki",
    "partBrand": "Cooling",
    "image": "/uploads/products/b_u_l_c_suzuki_swift_b_u_l_c_swift_phutungotothanhcong.jpg",
    "desc": "1. M&ocirc; Tả Sản Phẩm Bộ đầu lốc Suzuki Swift Bộ đầu lốc Swift <div style=\"text-align: center; margin: 20px 0;\"> <p style=\"text-align: center; margin: 16px 0;\"><img style=\"max-wi"
  },
  {
    "id": 6820,
    "sku": "TCA-LIVE-48-3",
    "oem": "JK95978990",
    "name": "BỘ ĐẦU LỐC PRADO 7PK",
    "price": 675000.0,
    "oldPrice": 750000.0,
    "cat": "Bộ đầu lốc điều hòa",
    "brand": "Toyota",
    "partBrand": "Cooling",
    "image": "/uploads/products/bo-dau-loc-prado-7pk-tca-live-48-3-20260804075403-126147f1.webp",
    "desc": "1. M&ocirc; Tả Sản Phẩm BỘ ĐẦU LỐC PRADO 7PK <div style=\"text-align: center; margin: 20px 0;\">&nbsp;</div> 2. Thiết Kế Kỹ Thuật &amp; Tương Th&iacute;ch D&ograve;ng Xe Linh kiện <s"
  }
];
  let categoriesDB = [
  {
    "id": 48,
    "name": "Bộ đầu lốc điều hòa"
  },
  {
    "id": 44,
    "name": "Cảm biến áp suất gas"
  },
  {
    "id": 21,
    "name": "Dàn Sưởi Điều Hòa"
  },
  {
    "id": 19,
    "name": "Dàn lạnh điều hòa"
  },
  {
    "id": 20,
    "name": "Dàn nóng điều hòa"
  },
  {
    "id": 31,
    "name": "Ket Nuoc Con"
  },
  {
    "id": 11,
    "name": "Két Nước"
  },
  {
    "id": 22,
    "name": "Lốc Điều Hòa"
  },
  {
    "id": 26,
    "name": "Motor, Quạt Dàn Lạnh"
  },
  {
    "id": 25,
    "name": "Motor, Quạt Dàn Nóng"
  },
  {
    "id": 27,
    "name": "Phin Lọc Ga"
  },
  {
    "id": 23,
    "name": "Van tiết lưu điều hòa"
  },
  {
    "id": 24,
    "name": "Van Đuôi Lốc"
  },
  {
    "id": 47,
    "name": "Điều Hòa Điện & Phụ kiện"
  },
  {
    "id": 41,
    "name": "Ống dẫn gas điều hòa"
  }
];
  let carBrandsDB = [
  {
    "id": 22,
    "name": "Audi"
  },
  {
    "id": 21,
    "name": "BMW"
  },
  {
    "id": 5,
    "name": "Chevrolet"
  },
  {
    "id": 6,
    "name": "Daewoo"
  },
  {
    "id": 8,
    "name": "Ford"
  },
  {
    "id": 7,
    "name": "Honda"
  },
  {
    "id": 1,
    "name": "Hyundai"
  },
  {
    "id": 28,
    "name": "Isuzu"
  },
  {
    "id": 2,
    "name": "KIA"
  },
  {
    "id": 27,
    "name": "Land Rover"
  },
  {
    "id": 30,
    "name": "Lexus"
  },
  {
    "id": 19,
    "name": "MG"
  },
  {
    "id": 4,
    "name": "Mazda"
  },
  {
    "id": 20,
    "name": "Mercedes-Benz"
  },
  {
    "id": 9,
    "name": "Mitsubishi"
  },
  {
    "id": 10,
    "name": "Nissan"
  },
  {
    "id": 26,
    "name": "Peugeot"
  },
  {
    "id": 24,
    "name": "Porsche"
  },
  {
    "id": 29,
    "name": "Subaru"
  },
  {
    "id": 11,
    "name": "Suzuki"
  },
  {
    "id": 3,
    "name": "Toyota"
  },
  {
    "id": 12,
    "name": "VinFast"
  },
  {
    "id": 23,
    "name": "Volkswagen"
  },
  {
    "id": 25,
    "name": "Volvo"
  }
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
      address: 'Số 11, ngõ 171, phố Sài Đồng, Phường Phúc Lợi, Thành phố Hà Nội, Việt Nam'
    },
    activeCategory: 'all',
    searchQuery: '',
    selectedProduct: productsDB[0]
  };

  const fmtVND = (num) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);

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
          if (data.categories && data.categories.length > 0) categoriesDB = data.categories;
          if (data.carBrands && data.carBrands.length > 0) carBrandsDB = data.carBrands;
          renderScreen(state.currentScreen);
        }
      })
      .catch(err => console.log('Using local embedded products database:', err));
  }

  function navigateTo(screenId, params = {}) {
    state.currentScreen = screenId;
    if (params.product) state.selectedProduct = params.product;

    document.querySelectorAll('.view-screen').forEach(el => el.classList.remove('active'));
    const targetScreen = document.getElementById(`screen-${screenId}`);
    if (targetScreen) targetScreen.classList.add('active');

    document.querySelectorAll('.nav-item').forEach(nav => {
      if (nav.dataset.screen === screenId) {
        nav.classList.add('active');
      } else {
        nav.classList.remove('active');
      }
    });

    const container = document.querySelector('.app-content');
    if (container) container.scrollTop = 0;

    renderScreen(screenId);
  }

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

  function renderHomeProducts() {
    const grid = document.getElementById('home-product-grid');
    if (!grid) return;

    let displayList = productsDB;
    if (state.activeCategory !== 'all') {
      displayList = productsDB.filter(p => p.cat.toLowerCase().includes(state.activeCategory.toLowerCase()));
    }

    grid.innerHTML = displayList.slice(0, 16).map(p => `
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

    const brandSelect = document.getElementById('home-brand-select');
    if (brandSelect && brandSelect.children.length <= 1) {
      brandSelect.innerHTML = carBrandsDB.map(b => `<option value="${b.name}">${b.name}</option>`).join('');
    }
    const catSelect = document.getElementById('home-cat-select');
    if (catSelect && catSelect.children.length <= 1) {
      catSelect.innerHTML = categoriesDB.map(c => `<option value="${c.name}">${c.name}</option>`).join('');
    }
  }

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

    grid.innerHTML = `
      <div style="font-size:12px; font-weight:700; color:var(--navy-dark); margin-bottom:10px; grid-column:1 / -1;">
        Hiển thị ${filtered.length} sản phẩm${state.activeCategory !== 'all' ? ` thuộc "${state.activeCategory}"` : ''}
      </div>
    ` + filtered.map(p => `
      <div class="prod-card" onclick="window.App.viewDetail(${p.id})" style="display:flex; flex-direction:row; height:105px; margin-bottom:10px; grid-column: 1 / -1;">
        <div style="width:95px; height:100%; background:#f8fafc; overflow:hidden; flex-shrink:0;">
          <img src="${p.image}" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='/favicon-512x512.png'">
        </div>
        <div style="padding:10px; flex:1; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden;">
          <div>
            <div style="font-size:10px; color:var(--gray-text-sub); font-weight:700;">Cooling • ${p.oem || p.sku}</div>
            <div style="font-size:12px; font-weight:700; color:var(--navy-dark); margin:2px 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.name}</div>
            <span style="background:#ecfdf5; color:#047857; font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px;">${p.partBrand || 'VALEO'} Khớp 100%</span>
          </div>
          <div style="font-size:13px; font-weight:800; color:var(--orange-accent);">${fmtVND(p.price)}</div>
        </div>
      </div>
    `).join('');
  }

  function renderVehicleSearchView() {
    const container = document.getElementById('screen-vehicle-search');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Tra theo xe</div>
        <div style="font-size:11px; opacity:0.8; margin-top:2px;">Chọn xe → hệ thống lọc đúng mã tương thích</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:12px; margin-bottom:12px;">
          <div style="font-size:11px; font-weight:700; color:var(--gray-text-sub); margin-bottom:6px;">XE ĐÃ LƯU</div>
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Toyota Vios</div>
              <div style="font-size:11px; color:var(--gray-text-sub);">2014 · 1.5G · 30A-123.45</div>
            </div>
            <button class="btn-outline" style="width:auto; padding:6px 12px; font-size:11px;">+ Thêm xe</button>
          </div>
        </div>

        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:14px;">
          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Hãng xe</label>
          <select style="width:100%; margin-bottom:12px;">
            ${carBrandsDB.map(b => `<option>${b.name}</option>`).join('')}
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Dòng xe</label>
          <select style="width:100%; margin-bottom:12px;">
            <option>Vios</option><option>Innova</option><option>Camry</option><option>Fortuner</option><option>Corolla Altis</option><option>Yaris</option><option>Hilux</option>
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Đời xe</label>
          <select style="width:100%; margin-bottom:12px;">
            <option>2014 - 2018</option><option>2019 - 2023</option><option>2008 - 2013</option>
          </select>

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Danh mục phụ tùng</label>
          <select style="width:100%; margin-bottom:14px;">
            ${categoriesDB.map(c => `<option>${c.name}</option>`).join('')}
          </select>

          <button class="btn-orange" onclick="window.App.navigateTo('search')">Tìm kiếm phụ tùng</button>
        </div>
      </div>
    `;
  }

  function renderCategoriesView() {
    const container = document.getElementById('screen-categories');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px; font-size:16px; font-weight:800;">
        Danh mục phụ tùng
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); overflow:hidden;">
          ${categoriesDB.map((c, i) => `
            <div onclick="window.App.filterCategory('${c.name}')" style="padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; cursor:pointer;">
              <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:11px; font-weight:800; color:var(--gray-text-sub);">${String(i + 1).padStart(2, '0')}</span>
                <div>
                  <div style="font-size:13px; font-weight:700; color:var(--navy-dark);">${c.name}</div>
                  <div style="font-size:10.5px; color:var(--gray-text-sub);">${c.prod_count || '120'} mã sản phẩm</div>
                </div>
              </div>
              <span style="color:var(--gray-text-sub); font-size:16px;">›</span>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

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
              <div class="brand-card-count">${b.prod_count || '150'} mã</div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

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
          <p style="font-size:12px; color:var(--gray-text-sub); line-height:1.5;">${p.desc || 'Linh kiện phụ tùng điện lạnh điều hòa ô tô chính hãng.'}</p>
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

  function renderCheckoutView() {
    const total = state.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    document.getElementById('checkout-total-price').textContent = fmtVND(total);
  }

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
      </div>
    `;
  }

  function renderWarrantyView() {
    const container = document.getElementById('warranty-container');
    if (!container) return;

    container.innerHTML = `
      <div style="background:#fff; border-radius:14px; border:1px solid var(--gray-border); padding:16px;">
        <h4 style="font-size:12px; font-weight:700; color:var(--gray-text-sub); margin-bottom:6px;">Mã phiếu bảo hành / Serial</h4>
        <input type="text" value="BH-88501-0K390" style="width:100%; font-size:13px; font-weight:700; margin-bottom:14px;">
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:12px; margin-bottom:14px;">
          <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:#047857; margin-bottom:6px;">
            <span>Còn bảo hành</span>
            <span>còn 7 tháng</span>
          </div>
          <div style="font-size:13px; font-weight:700; color:var(--navy-dark);">Dàn lạnh điều hòa Toyota Innova 2017</div>
        </div>
        <button class="btn-orange" style="margin-bottom:10px;" onclick="alert('Đã tạo yêu cầu hỗ trợ bảo hành!')">Tạo yêu cầu bảo hành</button>
        <button class="btn-outline">Gửi ảnh & mô tả lỗi</button>
      </div>
    `;
  }

  function renderCouponsView() {
    const container = document.getElementById('screen-coupons');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:14px 16px;">
        <div style="font-size:16px; font-weight:800;">Khuyến mại</div>
      </div>
      <div style="padding:14px;">
        <div style="background:#fff; border-radius:12px; border:1px solid var(--gray-border); padding:14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <div style="font-size:13px; font-weight:800; color:var(--navy-dark);">Giảm 10% phụ tùng điện lạnh</div>
          </div>
          <button class="btn-outline" style="width:auto; padding:6px 12px; font-size:11px;">Lưu</button>
        </div>
      </div>
    `;
  }

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
          <div style="font-size:11px; color:var(--gray-text-sub); margin:3px 0 8px;">Số 11, ngõ 171, phố Sài Đồng, Phường Phúc Lợi, Thành phố Hà Nội, Việt Nam</div>
        </div>
      </div>
    `;
  }

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

        <button class="btn-outline" style="color:var(--red-alert); border-color:#fecaca;" onclick="window.App.navigateTo('login')">Đăng xuất</button>
      </div>
    `;
  }

  function renderWelcomeView() {
    const container = document.getElementById('screen-welcome');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); min-height:100%; color:#fff; padding:30px 20px; display:flex; flex-direction:column; justify-content:space-between;">
        <div style="text-align:center;">
          <img src="/favicon-512x512.png" style="width:80px; height:80px; margin-bottom:20px;">
          <h1 style="font-size:22px; font-weight:800; line-height:1.3; margin-bottom:20px;">Phụ tùng điện lạnh ô tô, tra đúng xe trong 30 giây.</h1>
        </div>

        <div>
          <button class="btn-orange" style="margin-bottom:10px;" onclick="window.App.navigateTo('vehicle-search')">Tìm phụ tùng cho xe của tôi</button>
          <button class="btn-navy" style="background:#244270;" onclick="window.App.navigateTo('login')">Đăng nhập Gara / Đại lý</button>
        </div>
      </div>
    `;
  }

  // 17. REDESIGNED LOGIN SCREEN MATCHING SCREENSHOT 2 (NO GO-BACK LINK, EXACT UI SPEC)
  function renderLoginView() {
    const container = document.getElementById('screen-login');
    if (!container) return;

    container.innerHTML = `
      <div style="background:var(--navy-dark); color:#fff; padding:24px 16px 16px;">
        <div style="font-size:11px; opacity:0.8;">Xin chào,</div>
        <div style="font-size:20px; font-weight:800; margin-top:2px;">Đăng nhập tài khoản</div>
      </div>
      <div style="padding:16px;">
        <div style="background:#fff; border-radius:14px; padding:16px; border:1px solid var(--gray-border); margin-bottom:14px;">
          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Số điện thoại</label>
          <input type="text" value="0912 345 678" style="width:100%; margin-bottom:12px;">

          <label style="font-size:11px; font-weight:700; color:var(--gray-text-sub); display:block; margin-bottom:4px;">Mật khẩu</label>
          <div style="position:relative; margin-bottom:12px;">
            <input type="password" value="12345678" style="width:100%; padding-right:50px;">
            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:11px; font-weight:700; color:var(--navy-dark); cursor:pointer;">Hiện</span>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; font-size:12px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; color:var(--gray-text-sub);">
              <input type="checkbox" checked> Ghi nhớ
            </label>
            <a href="#" style="color:var(--navy-dark); font-weight:700; text-decoration:none;">Quên mật khẩu?</a>
          </div>

          <button class="btn-orange" style="margin-bottom:14px;" onclick="window.App.navigateTo('account')">Đăng nhập</button>

          <div style="text-align:center; font-size:11.5px; color:var(--gray-text-sub); margin-bottom:14px; position:relative;">
            <span style="background:#fff; padding:0 8px; position:relative; z-index:1;">hoặc</span>
            <div style="position:absolute; top:50%; left:0; right:0; height:1px; background:#e8ecf3;"></div>
          </div>

          <button class="btn-outline" style="margin-bottom:14px;" onclick="alert('Mã OTP Zalo đã được gửi tới SĐT 0912 345 678!')">Đăng nhập bằng OTP Zalo</button>

          <div style="text-align:center; font-size:12px; color:var(--gray-text-sub);">
            Chưa có tài khoản? <a href="#" style="color:var(--navy-dark); font-weight:800; text-decoration:none;" onclick="alert('Vui lòng điền form Đăng ký Gara!')">Đăng ký</a>
          </div>
        </div>

        <div style="background:#f8fafc; border-radius:14px; padding:14px; border:1px solid #e2e8f0;">
          <div style="font-size:13px; font-weight:800; color:var(--navy-dark); margin-bottom:2px;">Là Gara / Đại lý?</div>
          <div style="font-size:11px; color:var(--gray-text-sub); line-height:1.4;">Đăng ký để nhận bảng giá buôn gốc và chính sách công nợ gối đầu.</div>
        </div>
      </div>
    `;
  }

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

  document.addEventListener('DOMContentLoaded', () => {
    fetchLiveData();
    navigateTo('home');
  });

})();
