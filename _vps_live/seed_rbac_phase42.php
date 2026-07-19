<?php

$pdo = new PDO('sqlite:/var/lib/coolingsystems/cooling.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$labels = [
    'SA' => ['Quản trị viên hệ thống', 'Vai trò mẫu theo ma trận phân quyền. Có toàn quyền quản trị hệ thống.'],
    'OWN' => ['Chủ doanh nghiệp/Giám đốc', 'Vai trò mẫu theo ma trận phân quyền dành cho chủ doanh nghiệp hoặc giám đốc.'],
    'BM' => ['Quản lý chi nhánh', 'Vai trò mẫu theo ma trận phân quyền dành cho quản lý chi nhánh.'],
    'SAL' => ['Nhân viên kinh doanh', 'Vai trò mẫu theo ma trận phân quyền dành cho nhân viên bán hàng.'],
    'CAS' => ['Thu ngân', 'Vai trò mẫu theo ma trận phân quyền dành cho thu ngân.'],
    'WH' => ['Nhân viên kho', 'Vai trò mẫu theo ma trận phân quyền dành cho nhân viên kho.'],
    'PUR' => ['Nhân viên mua hàng', 'Vai trò mẫu theo ma trận phân quyền dành cho nhân viên mua hàng.'],
    'ACC' => ['Kế toán', 'Vai trò mẫu theo ma trận phân quyền dành cho kế toán.'],
    'CS' => ['Chăm sóc khách hàng/Bảo hành', 'Vai trò mẫu theo ma trận phân quyền dành cho chăm sóc khách hàng và bảo hành.'],
    'TECH' => ['Kỹ thuật viên/Lắp đặt', 'Vai trò mẫu theo ma trận phân quyền dành cho kỹ thuật viên.'],
    'DEL' => ['Nhân viên giao hàng', 'Vai trò mẫu theo ma trận phân quyền dành cho giao hàng.'],
    'MKT' => ['Marketing/CRM', 'Vai trò mẫu theo ma trận phân quyền dành cho marketing và CRM.'],
    'AUD' => ['Kiểm soát nội bộ', 'Vai trò mẫu theo ma trận phân quyền dành cho kiểm soát nội bộ.'],
];

$pdo->beginTransaction();
try {
    $updateTemplate = $pdo->prepare("UPDATE staff_roles SET name=?, description=? WHERE id=(SELECT staff_role_id FROM rbac_staff_role_links WHERE rbac_role_code=?)");
    $updateMatrix = $pdo->prepare("UPDATE rbac_roles SET name=?, updated_at=datetime('now','localtime') WHERE code=?");
    foreach ($labels as $code => [$name, $description]) {
        $updateTemplate->execute(['[RBAC] ' . $name . ' (' . $code . ')', $description . ' Chỉ đọc; dùng nút Phân công hoặc Nhân bản để sử dụng.', $code]);
        $updateMatrix->execute([$name, $code]);
    }
    $pdo->commit();
    echo json_encode(['ok' => true, 'localized_templates' => count($labels)], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
