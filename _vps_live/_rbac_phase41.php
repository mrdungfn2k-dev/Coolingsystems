<?php

function rbacRoleCodesForUser(int $userId): array {
    $rows = dbAll("SELECT DISTINCT link.rbac_role_code FROM staff_role_assignments assignment INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE assignment.user_id=?", [$userId]);
    return array_values(array_filter(array_map(fn($row) => (string)$row['rbac_role_code'], $rows)));
}

function rbacCan(int $userId, string $capability): bool {
    $roleCodes = rbacRoleCodesForUser($userId);
    if (!$roleCodes) return false;
    $rules = dbAll('SELECT permission_code, allowed_levels FROM rbac_capability_rules WHERE capability=?', [$capability]);
    if (!$rules) return false;
    foreach ($rules as $rule) {
        $levels = json_decode((string)$rule['allowed_levels'], true);
        if (!is_array($levels) || !$levels) continue;
        $placeholders = implode(',', array_fill(0, count($roleCodes), '?'));
        $levelPlaceholders = implode(',', array_fill(0, count($levels), '?'));
        $params = array_merge([$rule['permission_code']], $roleCodes, $levels);
        $allowed = dbGet("SELECT 1 FROM rbac_role_permissions WHERE permission_code=? AND role_code IN ($placeholders) AND access_level IN ($levelPlaceholders) LIMIT 1", $params);
        if ($allowed) return true;
    }
    return false;
}

function rbacMenuCan(int $userId, string $menuPermission): bool {
    if ($menuPermission === 'products_create') {
        return rbacCan($userId, 'catalog.products.create') && rbacCan($userId, 'catalog.codes.manage');
    }
    $capabilityMap = [
        'products' => 'catalog.products.view',
        'inventory' => 'inventory.view',
        'orders' => 'sales.orders.view',
        'create_order' => 'sales.orders.create',
        'returns' => 'sales.returns.view',
        'categories' => 'catalog.taxonomy.view',
        'brands' => 'catalog.vehicle.view',
        'brand_models' => 'catalog.vehicle.view',
        'stores' => 'organization.branches.view',
        'promotions' => 'marketing.promotions.view',
        'vouchers' => 'marketing.promotions.view',
        'chat' => 'crm.engagement.manage',
        'users' => 'customers.view',
        'contacts' => 'crm.customer_care.manage',
        'reviews' => 'crm.complaints.manage',
        'staff' => 'system.rbac.view',
        'audit' => 'system.audit.view',
        'settings' => 'system.settings.view',
    ];
    return isset($capabilityMap[$menuPermission]) && rbacCan($userId, $capabilityMap[$menuPermission]);
}

function rbacHasCapability(int $userId, string $capability): bool {
    return rbacCan($userId, $capability);
}

// A staff member with any legacy role keeps the legacy authorization behavior.
// Detailed field-level controls apply only to users assigned exclusively to RBAC templates.
function rbacUsesDetailedMode(int $userId): bool {
    $row = dbGet(
        "SELECT SUM(CASE WHEN link.staff_role_id IS NOT NULL THEN 1 ELSE 0 END) AS template_count,
                SUM(CASE WHEN link.staff_role_id IS NULL THEN 1 ELSE 0 END) AS legacy_count
         FROM staff_role_assignments assignment
         LEFT JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id
         WHERE assignment.user_id=?",
        [$userId]
    ) ?: [];
    return (int)($row['template_count'] ?? 0) > 0 && (int)($row['legacy_count'] ?? 0) === 0;
}
