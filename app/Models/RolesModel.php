<?php

namespace App\Models;

use CodeIgniter\Model;

class RolesModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = ['role_name', 'description', 'last_accessed_by'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'role_name'   => 'required|min_length[2]|max_length[100]|is_unique[roles.role_name,id,{id}]',
        'description' => 'permit_empty|string',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'role_name' => [
            'required'   => 'Role name is required',
            'is_unique'  => 'This role name already exists',
            'min_length' => 'Role name must be at least 2 characters long',
        ]
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimName', 'encodePermissions', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimName', 'encodePermissions', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];
    protected $afterFind    = ['decodePermissions'];

    /**
     * Trim whitespace from role name.
     */
    protected function trimName(array $data): array
    {
        if (isset($data['data']['role_name'])) {
            $data['data']['role_name'] = trim($data['data']['role_name']);
        }
        return $data;
    }

    /**
     * Encode permissions to JSON before saving.
     */
    protected function encodePermissions(array $data): array
    {
        if (isset($data['data']['permissions']) && is_array($data['data']['permissions'])) {
            $data['data']['permissions'] = json_encode($data['data']['permissions'], JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }

    /**
     * Decode JSON permissions after fetching.
     */
    protected function decodePermissions(array $data): array
    {
        $decode = function (&$row) {
            if (isset($row['permissions']) && $row['permissions']) {
                $row['permissions'] = json_decode($row['permissions'], true);
            }
        };

        if (isset($data['data'])) {
            $decode($data['data']);
        } elseif (isset($data[0]) && is_array($data[0])) {
            foreach ($data as &$row) {
                $decode($row);
            }
        }

        return $data;
    }
    
    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}

/**
 * Custom validation rule for permissions JSON.
 * Register this function globally or inside Config\Validation.php if not autoloaded.
 */
if (!function_exists('validate_permissions_json')) {
    function validate_permissions_json(string $str): bool
    {
        if (empty($str)) return true;
        if (is_array($str)) return true;

        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
