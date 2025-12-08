<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RolesModel;

class BaseApiController extends ResourceController
{
    public ?object $user = null; // Authenticated user payload
    protected ?RolesModel $rolesModel = null;

    public function __construct()
    {
        helper(['api', 'request', 'upload', 'auth', 'permission']); // use your new helpers
        header('Content-Type: application/json');

        $this->rolesModel = new RolesModel();
        $this->setAuthenticatedUser(); // ✅ Centralized auth setup
    }

    // -------------------------------------------------------
    // 🔐 Auth & User Handling
    // -------------------------------------------------------

    /**
     * Automatically decode and set the authenticated user (from JWT).
     * Safe to call again if token refresh or recheck is needed.
     */
    protected function setAuthenticatedUser(): void
    {
        $decoded = getDecodedUser();
        $this->user = $decoded ? (object) $decoded : null;
    }

    /**
     * Get sanitized input from request (GET/POST/JSON)
     */
    protected function input(string $key = null, $default = null)
    {
        $data = getRequestData($this->request);
        return $key ? ($data[$key] ?? $default) : $data;
    }

    // -------------------------------------------------------
    // ✅ Global Response Helpers (using api_helper)
    // -------------------------------------------------------

    protected function respondSuccess(mixed $data = [], string $message = 'Success', int $code = 200)
    {
        return apiSuccess($data, $message, $code);
    }

    protected function respondError(string $message = 'Error', int $code = 400)
    {
        return apiError($message, $code);
    }

    protected function respondData(array $payload, int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON($payload);
    }

    // -------------------------------------------------------
    // 🔐 Role Permission System (Using roles.permissions field)
    // -------------------------------------------------------

    /**
     * Check permission for module and action.
     * - Public GET access for index/show
     * - Auth required for all other actions
     *
     * @param string $module
     * @param string $action (view|add|edit|delete)
     * @param bool $allowPublicGet
     */
    protected function checkPermission(string $moduleKey, string $action, bool $allowPublic = false)
{
    if (!hasPermission($moduleKey, $action, $allowPublic)) {
        return $this->respondError('Permission denied', 403);
    }

    return null; // continue controller
}

    // -------------------------------------------------------
    // 🧩 Role-based Access Helper
    // -------------------------------------------------------

    /**
     * Quickly check if user has one of the given roles.
     * Example:
     * if ($resp = $this->hasRole(['admin', 'superadmin'])) return $resp;
     */
    protected function hasRole(array|string $roles): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (!$this->user) {
            return $this->respondError('Unauthorized: Login required', 401);
        }

        $role = $this->rolesModel->find($this->user->role_id ?? 0);
        $roleName = strtolower($role['role_name'] ?? '');

        $allowedRoles = array_map('strtolower', (array) $roles);
        if (!in_array($roleName, $allowedRoles)) {
            return $this->respondError('Access denied: Insufficient privileges', 403);
        }

        return null; // ✅ Allowed
    }
}
