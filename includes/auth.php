<?php
session_start();

function currentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    static $user = false;
    if ($user === false) {
        $user = dbGet('SELECT * FROM users WHERE id = ? AND status = ?', [$_SESSION['user_id'], 'active']);
    }
    return $user;
}

function requireLogin(string $redirect = '/auth/login'): array {
    $user = currentUser();
    if (!$user) {
        header('Location: ' . $redirect);
        exit;
    }
    return $user;
}

function requireRole($role, string $redirect = '/'): array {
    $user = requireLogin($redirect);
    if ((is_array($role) ? !in_array($user['role'], $role) : $user['role'] !== $role)) {
        header('Location: ' . $redirect);
        exit;
    }
    return $user;
}

function loginUser(int $userId): void {
    $_SESSION['user_id'] = $userId;
    session_regenerate_id(true);
}

function logout(string $redirect = '/'): void {
    session_destroy();
    header('Location: ' . $redirect);
    exit;
}

function requireStaffPermission(string $permission, string $redirect = '/auth/login'): array {
    $user = requireLogin($redirect);
    // Admin has all permissions
    if ($user['role'] === 'admin') return $user;
    // Staff: check role-based permissions
    if ($user['role'] === 'staff') {
        $roleAssignment = dbAll(
            "SELECT sr.permissions FROM staff_role_assignments sra 
             INNER JOIN staff_roles sr ON sr.id = sra.role_id 
             WHERE sra.user_id = ?",
            [$user['id']]
        );
        $perms = []; foreach (($roleAssignment ?: []) as $rr) { $a = json_decode($rr['permissions'] ?? '[]', true); if (is_array($a)) $perms = array_merge($perms, $a); }
        foreach (explode('|', $permission) as $__pp) { if (in_array($__pp, $perms)) return $user; }
    }
    header('Location: ' . $redirect);
    exit;
}

// True if this user has at least one staff role assignment (i.e. is an ACTIVE assigned staff).
function staffHasAssignment(int $uid): bool {
    return (bool) dbGet('SELECT 1 FROM staff_role_assignments WHERE user_id=? LIMIT 1', [$uid]);
}

// Super admin = role 'admin' + is_superadmin flag. Highest level; can manage admins.
function requireSuperAdmin(string $redirect = '/admin'): array {
    $user = requireLogin($redirect);
    if (empty($user['is_superadmin'])) { header('Location: ' . $redirect); exit; }
    return $user;
}
