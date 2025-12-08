<?php

namespace App\Models;

use CodeIgniter\Model;

class ModulePermissionsModel extends Model
{
    protected $table            = 'module_permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'role_id',
        'module_id',
        'permissions_json',
        'status',
        'last_accessed_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'role_id'           => 'required|integer',
        'module_id'         => 'required|integer',
        'permissions_json'  => 'required',
        'status'            => 'in_list[active,inactive]',
        'last_accessed_by'  => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'role_id' => [
            'required' => 'Role ID is required.'
        ],
        'module_id' => [
            'required' => 'Module ID is required.'
        ],
        'permissions_json' => [
            'required' => 'Permissions JSON cannot be empty.'
        ],
    ];

    protected $skipValidation = false;

    // ---------------------------------------------------------
    // CALLBACKS
    // ---------------------------------------------------------

    protected $beforeInsert = ['encodePermissions', 'setLastAccessedBy'];
    protected $beforeUpdate = ['encodePermissions', 'setLastAccessedBy'];
    protected $afterFind    = ['decodePermissions'];

    /**
     * Convert PHP array → JSON before saving.
     */
    protected function encodePermissions(array $data): array
    {
        if (isset($data['data']['permissions_json']) && is_array($data['data']['permissions_json'])) {
            $data['data']['permissions_json'] = json_encode($data['data']['permissions_json'], JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }

    /**
     * Convert JSON → PHP array after fetching.
     */
    protected function decodePermissions(array $data): array
    {
        if (!empty($data['data'])) {
            foreach ($data['data'] as &$row) {
                if (isset($row['permissions_json']) && is_string($row['permissions_json'])) {
                    $row['permissions'] = json_decode($row['permissions_json'], true) ?? [];
                }
            }
        }

        return $data;
    }

    /**
     * Set last_accessed_by automatically from helper
     */
    protected function setLastAccessedBy(array $data): array
    {
        if (function_exists('getUserId') && empty($data['data']['last_accessed_by'])) {
            $uid = getUserId();
            if ($uid) {
                $data['data']['last_accessed_by'] = $uid;
            }
        }

        return $data;
    }

    // ---------------------------------------------------------
    // CUSTOM HELPERS
    // ---------------------------------------------------------

    /**
     * Get all permissions for a role (RAW LIST)
     */
    public function getPermissionsForRole(int $roleId): array
    {
        return $this->where('role_id', $roleId)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Get permissions as a module-wise map:
     *
     * Example return:
     * [
     *     5 => ["create" => true, "update" => false],  // module_id => permissions
     *     7 => ["view" => true, "delete" => false],
     * ]
     *
     * Perfect for `checkPermission()` helper.
     */
    public function getPermissionMapByRole(int $roleId): array
    {
        $rows = $this->getPermissionsForRole($roleId);
        $map = [];

        foreach ($rows as $row) {
            $map[$row['module_id']] = json_decode($row['permissions_json'], true) ?? [];
        }

        return $map;
    }
}
