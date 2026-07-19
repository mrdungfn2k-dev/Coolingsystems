<?php

function rbacRoleCodesForUser(int $userId): array {
    $rows = dbAll("SELECT DISTINCT link.rbac_role_code FROM staff_role_assignments assignment INNER JOIN rbac_staff_role_links link ON link.staff_role_id=assignment.role_id WHERE assignment.user_id=?", [$userId]);
    return array_values(array_filter(array_map(fn($row) => (string)$row['rbac_role_code'], $rows)));
}

function rbacCapabilityCatalog(): array {
    return dbAll("SELECT rule.capability, MIN(permission.module_name) AS module_name, MIN(permission.feature_name) AS feature_name, MIN(permission.action_name) AS action_name, COUNT(*) AS rule_count FROM rbac_capability_rules rule INNER JOIN rbac_permissions permission ON permission.code=rule.permission_code GROUP BY rule.capability ORDER BY module_name, feature_name, action_name");
}

function rbacTemplateCapabilities(string $roleCode): array {
    $rows = dbAll("SELECT DISTINCT rule.capability, rule.allowed_levels, role_permission.access_level FROM rbac_role_permissions role_permission INNER JOIN rbac_capability_rules rule ON rule.permission_code=role_permission.permission_code WHERE role_permission.role_code=?", [$roleCode]);
    $capabilities = [];
    foreach ($rows as $row) {
        $levels = json_decode((string)$row['allowed_levels'], true);
        if (is_array($levels) && in_array((string)$row['access_level'], $levels, true)) {
            $capabilities[] = (string)$row['capability'];
        }
    }
    return array_values(array_unique($capabilities));
}

function rbacCan(int $userId, string $capability): bool {
    $roleCodes = rbacRoleCodesForUser($userId);
    if ($roleCodes) {
        $rules = dbAll('SELECT permission_code, allowed_levels FROM rbac_capability_rules WHERE capability=?', [$capability]);
        foreach ($rules as $rule) {
            $levels = json_decode((string)$rule['allowed_levels'], true);
            if (!is_array($levels) || !$levels) continue;
            $placeholders = implode(',', array_fill(0, count($roleCodes), '?'));
            $levelPlaceholders = implode(',', array_fill(0, count($levels), '?'));
            $params = array_merge([$rule['permission_code']], $roleCodes, $levels);
            $allowed = dbGet("SELECT 1 FROM rbac_role_permissions WHERE permission_code=? AND role_code IN ($placeholders) AND access_level IN ($levelPlaceholders) LIMIT 1", $params);
            if ($allowed) return true;
        }
    }

    $customRoles = dbAll("SELECT staff_role.permissions FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id LEFT JOIN rbac_staff_role_links template ON template.staff_role_id=staff_role.id WHERE assignment.user_id=? AND template.staff_role_id IS NULL", [$userId]);
    foreach ($customRoles as $role) {
        $permissions = json_decode((string)($role['permissions'] ?? '[]'), true);
        if (is_array($permissions) && in_array('rbac:' . $capability, $permissions, true)) return true;
    }
    return false;
}

function rbacMenuCan(int $userId, string $menuPermission): bool {
    if ($menuPermission === 'products_create') {
        return rbacCan($userId, 'catalog.products.create') && rbacCan($userId, 'catalog.codes.manage');
    }
    $capabilityMap = [
        'products' => 'catalog.products.view', 'inventory' => 'inventory.view',
        'orders' => 'sales.orders.view', 'create_order' => 'sales.orders.create',
        'returns' => 'sales.returns.view',
        'warranties' => 'warranty.cases.view',
        'serials' => 'catalog.serials.manage', 'categories' => 'catalog.taxonomy.view',
        'brands' => 'catalog.vehicle.view', 'brand_models' => 'catalog.vehicle.view',
        'stores' => 'organization.branches.view', 'promotions' => 'marketing.promotions.view',
        'vouchers' => 'marketing.promotions.view', 'chat' => 'crm.engagement.manage',
        'users' => 'customers.view', 'contacts' => 'crm.customer_care.manage',
        'reviews' => 'crm.complaints.manage', 'staff' => 'system.rbac.view',
        'audit' => 'system.audit.view', 'settings' => 'system.settings.view',
    ];
    return isset($capabilityMap[$menuPermission]) && rbacCan($userId, $capabilityMap[$menuPermission]);
}

function rbacHasCapability(int $userId, string $capability): bool {
    return rbacCan($userId, $capability);
}

function rbacUsesDetailedMode(int $userId): bool {
    $roles = dbAll("SELECT staff_role.permissions, template.staff_role_id AS template_role_id FROM staff_role_assignments assignment INNER JOIN staff_roles staff_role ON staff_role.id=assignment.role_id LEFT JOIN rbac_staff_role_links template ON template.staff_role_id=staff_role.id WHERE assignment.user_id=?", [$userId]);
    if (!$roles) return false;
    $hasDetailedRole = false;
    foreach ($roles as $role) {
        if (!empty($role['template_role_id'])) {
            $hasDetailedRole = true;
            continue;
        }
        $permissions = json_decode((string)($role['permissions'] ?? '[]'), true);
        if (!is_array($permissions)) return false;
        foreach ($permissions as $permission) {
            if (!str_starts_with((string)$permission, 'rbac:')) return false;
        }
        $hasDetailedRole = true;
    }
    return $hasDetailedRole;
}
