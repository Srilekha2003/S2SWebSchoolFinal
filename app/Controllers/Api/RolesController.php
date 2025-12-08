<?php

namespace App\Controllers\Api;

use App\Models\RolesModel;
use App\Models\UsersModel;

class RolesController extends BaseApiController
{
    protected UsersModel $usersModel;

    public function __construct()
    {
        parent::__construct();
        $this->rolesModel  = new RolesModel();
        $this->usersModel  = new UsersModel();
    }

    /**
     * GET /api/v1/roles
     * List all roles (public view allowed)
     */
    public function index()
    {
        // if ($resp = $this->checkPermission('roles', 'index', true)) return $resp;

        try {
            $roles = $this->rolesModel
                ->select("roles.*, COUNT(users.id) AS user_count")
                ->join("users", "users.role_id = roles.id", "left")
                ->where("roles.deleted_at", null)
                ->groupBy("roles.id")
                ->orderBy("roles.id", "ASC")
                ->findAll();

            return $this->respondSuccess([
                'count' => count($roles),
                'data'  => $roles
            ], 'Roles fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[RolesController::index] ' . $e->getMessage());
            return $this->respondError('Failed to fetch roles', 500);
        }
    }

    /**
     * GET /api/v1/roles/{id}
     */
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('roles', 'show', true)) return $resp;

        try {
            $role = $this->rolesModel
                ->select("roles.*, COUNT(users.id) AS user_count")
                ->join("users", "users.role_id = roles.id", "left")
                ->where("roles.id", $id)
                ->where("roles.deleted_at", null)
                ->groupBy("roles.id")
                ->first();

            if (!$role) {
                return $this->respondError('Role not found', 404);
            }

            return $this->respondSuccess($role, 'Role fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[RolesController::show] ' . $e->getMessage());
            return $this->respondError('Failed to fetch role', 500);
        }
    }

    /**
     * POST /api/v1/roles
     * Create new role
     */
    public function create()
    {
        if ($resp = $this->checkPermission('roles', 'create')) return $resp;

        try {
            $data = $this->input();

            if (empty($data['role_name'])) {
                return $this->respondError('Role name is required', 422);
            }

            // Check duplicate role
            $exists = $this->rolesModel
                ->where('role_name', $data['role_name'])
                ->where('deleted_at', null)
                ->first();

            if ($exists) {
                return $this->respondError('Role name already exists', 422);
            }

            $data['description']      = $data['description'] ?? null;
            $data['last_accessed_by'] = $this->user->id ?? null;

            $id = $this->rolesModel->insert($data);
            if (!$id) {
                return $this->respondError('Failed to create role', 500);
            }

            // IMPORTANT: A role is created first.
            // Permissions are handled separately through ModulePermissionsController.

            return $this->respondSuccess(
                $this->rolesModel->find($id),
                'Role created successfully',
                201
            );

        } catch (\Throwable $e) {
            log_message('error', '[RolesController::create] ' . $e->getMessage());
            return $this->respondError('Failed to create role', 500);
        }
    }

    /**
     * PUT /api/v1/roles/{id}
     */
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('roles', 'update')) return $resp;

        try {
            $role = $this->rolesModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$role) {
                return $this->respondError('Role not found', 404);
            }

            if (!empty($role['is_system']) && $role['is_system'] == 1) {
                return $this->respondError('System roles cannot be modified', 403);
            }

            $data = $this->input();

            // Validate unique role name (if changed)
            if (!empty($data['role_name'])) {
                $exists = $this->rolesModel
                    ->where('role_name', $data['role_name'])
                    ->where('id !=', $id)
                    ->where('deleted_at', null)
                    ->first();

                if ($exists) {
                    return $this->respondError('Role name already exists', 422);
                }
            }

            $data['last_accessed_by'] = $this->user->id ?? null;

            $this->rolesModel->update($id, $data);

            return $this->respondSuccess(
                $this->rolesModel->find($id),
                'Role updated successfully'
            );
        } catch (\Throwable $e) {
            log_message('error', '[RolesController::update] ' . $e->getMessage());
            return $this->respondError('Failed to update role', 500);
        }
    }

    /**
     * DELETE /api/v1/roles/{id}
     */
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('roles', 'delete')) return $resp;

        try {
            $role = $this->rolesModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$role) {
                return $this->respondError('Role not found', 404);
            }

            if (!empty($role['is_system']) && $role['is_system'] == 1) {
                return $this->respondError('System roles cannot be deleted', 403);
            }

            // Check if any users assigned
            $assigned = $this->usersModel->where('role_id', $id)->countAllResults();
            if ($assigned > 0) {
                return $this->respondError('Cannot delete role: assigned to users', 422);
            }

            // Perform soft delete
            $this->rolesModel->update($id, [
                'deleted_at'       => date('Y-m-d H:i:s'),
                'last_accessed_by' => $this->user->id ?? null
            ]);

            return $this->respondSuccess(['id' => $id], 'Role deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[RolesController::delete] ' . $e->getMessage());
            return $this->respondError('Failed to delete role', 500);
        }
    }
}
