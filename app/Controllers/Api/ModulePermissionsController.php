<?php

namespace App\Controllers\Api;

use App\Models\ModulePermissionsModel;
use App\Models\ModulesModel;
use App\Models\RolesModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;

class ModulePermissionsController extends BaseApiController
{
    use ResponseTrait;

    protected ModulePermissionsModel $permModel;
    protected ModulesModel $modulesModel;

    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();

        $this->permModel    = new ModulePermissionsModel();
        $this->modulesModel = new ModulesModel();
        $this->rolesModel   = new RolesModel();
        $this->req              = service('request'); // For getFile()
    }

    public function index()
    {
        try {
            $roleId = (int) $this->req->getGet('role_id');

            if (!$roleId) return $this->respondError('role_id is required', 422);

            $role = $this->rolesModel->find($roleId);
            if (!$role) return $this->respondError('Role not found', 404);

            $permissions = $this->permModel
                ->select('modules.module_key, modules.module_name, module_permissions.*')
                ->join('modules', 'modules.id = module_permissions.module_id', 'left')
                ->where('module_permissions.role_id', $roleId)
                ->where('module_permissions.deleted_at', null)
                ->orderBy('modules.module_name', 'ASC')
                ->findAll();

            return $this->respondSuccess([
                'role'        => $role,
                'permissions' => $permissions,
            ], 'Module permissions fetched successfully');

        } catch (\Throwable $e) {
            return $this->respondError('Failed to fetch module permissions', 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->input();

            $roleId = (int) ($data['role_id'] ?? 0);
            $permissionsInput = $data['permissions'] ?? [];

            if (!$roleId || !is_array($permissionsInput)) {
                return $this->respondError('role_id and permissions object required', 422);
            }

            $role = $this->rolesModel->find($roleId);
            if (!$role) return $this->respondError('Invalid role_id', 404);

            $saved = [];

            foreach ($permissionsInput as $moduleKey => $perms) {

                if (is_string($perms)) {
                    $perms = json_decode($perms, true);
                }

                if (!is_array($perms)) {
                    return $this->respondError("Invalid permissions for {$moduleKey}", 422);
                }

                $module = $this->modulesModel
                    ->where('module_key', $moduleKey)
                    ->where('deleted_at', null)
                    ->first();

                if (!$module) {
                    return $this->respondError("Invalid module_key: {$moduleKey}", 422);
                }

                $rowData = [
                    'role_id'          => $roleId,
                    'module_id'        => (int) $module['id'],
                    'permissions_json' => $perms,           // RAW array (model encodes)
                    'status'           => $data['status'] ?? 'active',
                ];

                $existing = $this->permModel
                    ->where('role_id', $roleId)
                    ->where('module_id', $module['id'])
                    ->where('deleted_at', null)
                    ->first();

                if ($existing) {
                    $this->permModel->update($existing['id'], $rowData);
                    $saved[] = $this->permModel->find($existing['id']);
                } else {
                    $newId = $this->permModel->insert($rowData);
                    $saved[] = $this->permModel->find($newId);
                }
            }

            return $this->respondSuccess($saved, 'Module permissions saved successfully');

        } catch (\Throwable $e) {
            return $this->respondError('Failed to save module permissions', 500);
        }
    }

    public function delete($id = null)
    {
        try {
            $id = (int) $id;

            if (!$id) return $this->respondError('Invalid id', 422);

            $record = $this->permModel
                ->where('id', $id)
                ->where('deleted_at', null)
                ->first();

            if (!$record) return $this->respondError('Permission not found', 404);

            $this->permModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Permission deleted');

        } catch (\Throwable $e) {
            return $this->respondError('Failed to delete permission', 500);
        }
    }
}
