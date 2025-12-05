<?php

use App\Models\ModulePermissionsModel;
use App\Models\ModulesModel;

/**
 * Get the current controller instance safely (CI4 compatible)
 */
if (!function_exists('getControllerInstance')) {
    function getControllerInstance()
    {
        $router = service('router');
        $controllerClass = $router->controllerName();

        if (!$controllerClass) return null;

        return \CodeIgniter\Config\Factories::controller($controllerClass);
    }
}

/**
 * Get authenticated user set in BaseApiController::$user
 */
if (!function_exists('getUser')) {
    function getUser()
    {
        $controller = getControllerInstance();

        if ($controller && property_exists($controller, 'user')) {
            return $controller->user;
        }

        return null;
    }
}

/**
 * Check if user has permission for module + action
 */
if (!function_exists('hasPermission')) {
    function hasPermission(string $moduleKey, string $action, bool $allowPublic = false): bool
    {
        $user = getUser();

        // Allow public GET index/show
        if ($allowPublic && in_array($action, ['index', 'show'])) {
            return true;
        }

        if (!$user) return false;

        $roleId = $user->role_id;
        if (!$roleId) return false;

        // Action → JSON permission key
        $map = [
            'index'  => 'index',
            'show'   => 'show',
            'view'   => 'index',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete'
        ];

        if (!isset($map[$action])) {
            return false;
        }

        $permKey = $map[$action];

        // Cache request
        static $cache = [];
        $cacheKey = "{$roleId}_{$moduleKey}";

        if (!isset($cache[$cacheKey])) {

            // Module
            $module = (new ModulesModel())
                ->where('module_key', $moduleKey)
                ->where('deleted_at', null)
                ->first();

            if (!$module) return false;

            // Permissions row
            $row = (new ModulePermissionsModel())
                ->where('role_id', $roleId)
                ->where('module_id', $module['id'])
                ->where('deleted_at', null)
                ->first();

            if (!$row) return false;

            $cache[$cacheKey] = json_decode($row['permissions_json'], true);
        }

        $perms = $cache[$cacheKey];

        return !empty($perms[$permKey]);
    }
}

/**
 * Wrapper for controllers
 */
if (!function_exists('checkPermission')) {
    function checkPermission(string $moduleKey, string $action, bool $allowPublic = false)
    {
        if (!hasPermission($moduleKey, $action, $allowPublic)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'status'  => 403,
                    'message' => "Permission denied for {$moduleKey} → {$action}"
                ]);
        }

        return null;
    }
}
