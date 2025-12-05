<?php

use App\Libraries\JWTService;
use App\Models\UsersModel;
use App\Models\RolesModel;

/**
 * -------------------------------------------------------
 * JWT + Auth Helper
 * Provides secure helpers for authentication and ACL
 * -------------------------------------------------------
 */

/**
 * Extract Bearer token from Authorization header
 *
 * @return string|null The token string or null if not found
 */
if (!function_exists('getBearerToken')) {
    function getBearerToken(): ?string
    {
        $req = service('request');
        $auth = $req->getHeaderLine('Authorization');

        if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $m)) {
            return null;
        }
        return $m[1];
    }
}

/**
 * Decode JWT and cache for one request
 */
if (!function_exists('getDecodedUser')) {
    function getDecodedUser(): ?object
    {
        static $cache = null;

        if ($cache !== null) return $cache;

        $token = getBearerToken();
        if (!$token) return null;

        // If token was blacklisted, reject it
        if (function_exists('isTokenBlacklisted') && isTokenBlacklisted($token)) {
            return null;
        }

        $jwt = new JWTService();
        $cache = $jwt->decodeToken($token);

        return $cache ?: null;
    }
}

/**
 * Return authenticated user ID
 */
if (!function_exists('getUserId')) {
    function getUserId(): ?int
    {
        $d = getDecodedUser();
        return isset($d->id) ? intval($d->id) : null;
    }
}

/**
 * Return user role_name from decoded token
 */
if (!function_exists('getUserRole')) {
    function getUserRole(): ?string
    {
        $d = getDecodedUser();
        return $d->role_name ?? null;
    }
}

/**
 * Return complete user record from DB
 */
if (!function_exists('getAuthenticatedUser')) {
    function getAuthenticatedUser(): ?array
    {
        $id = getUserId();
        if (!$id) return null;

        return (new UsersModel())
            ->select('users.*, roles.role_name')
            ->join('roles', 'roles.id = users.role_id', 'left')
            ->where('users.id', $id)
            ->where('users.deleted_at', null)
            ->first();
    }
}

/**
 * Check if user has ANY of the specified roles
 * Example :
 *   hasRole("admin")
 *   hasRole(["admin","manager"])
 */
if (!function_exists('hasRole')) {
    function hasRole(array|string|null $roles): bool
    {
        $current = strtolower(getUserRole() ?? '');
        if (!$current || !$roles) return false;

        $roles = (array)$roles;
        $roles = array_map('strtolower', $roles);

        return in_array($current, $roles, true);
    }
}

/**
 * Backward Compatibility
 * createJWT() → generate Access Token by default
 */
if (!function_exists('createJWT')) {
    function createJWT(array $payload, int $expiry = 3600): string
    {
        $jwt = new JWTService();
        return $jwt->createAccessToken($payload, $expiry);
    }
}

/**
 * Generate access token explicitly
 */
if (!function_exists('createAccessToken')) {
    function createAccessToken(array $payload, int $ttl = 3600): string
    {
        $jwt = new JWTService();
        return $jwt->createAccessToken($payload, $ttl);
    }
}

/**
 * Generate refresh token explicitly (example: 30 days)
 */
if (!function_exists('createRefreshToken')) {
    function createRefreshToken(array $payload, int $ttl = 2592000): string
    {
        $jwt = new JWTService();
        return $jwt->createRefreshToken($payload, $ttl);
    }
}

/**
 * Check if a user is authorized for a module/action
 * (Old roles.permissions JSON)
 */
if (!function_exists('isAuthorized')) {
    function isAuthorized(string $module, string $action): bool
    {
        $user = getAuthenticatedUser();
        if (!$user) return false;

        // Superadmin always true
        if (hasRole('superadmin')) return true;

        $role = (new RolesModel())->find($user['role_id']);
        if (!$role || empty($role['permissions'])) return false;

        $perm = json_decode($role['permissions'], true);

        return isset($perm[$module][$action]) && $perm[$module][$action] === true;
    }
}

/**
 * Blacklist token on logout
 */
if (!function_exists('addTokenToBlacklist')) {
    function addTokenToBlacklist(string $token): bool
    {
        $file = WRITEPATH . 'blacklist.json';
        $list = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        $list[$token] = time();

        file_put_contents($file, json_encode($list));
        return true;
    }
}

/**
 * Check if token is blacklisted
 */
if (!function_exists('isTokenBlacklisted')) {
    function isTokenBlacklisted(string $token): bool
    {
        $file = WRITEPATH . 'blacklist.json';
        if (!file_exists($file)) return false;

        $list = json_decode(file_get_contents($file), true) ?? [];

        return isset($list[$token]);
    }
}
