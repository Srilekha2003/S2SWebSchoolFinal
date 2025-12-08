<?php

namespace App\Models;

use CodeIgniter\Model;

class ModulesModel extends Model
{
    protected $table            = 'modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'module_key',
        'module_name',
        'module_image',
        'description',
        'settings',

        'is_system',
        'status',

        'last_accessed_by',
    ];

    protected bool $allowEmptyInserts   = false;
    protected bool $updateOnlyChanged   = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // -------------------------------------------------------
    // Validation
    // -------------------------------------------------------
    protected $validationRules = [
        'module_key'  => 'required|min_length[2]|max_length[100]',
        'module_name' => 'required|min_length[2]|max_length[150]',
        'module_image'      => 'permit_empty|max_length[512]',
        'is_system'   => 'permit_empty|in_list[0,1]',
        'status'      => 'required|in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'module_key' => [
            'required' => 'Module key is required.',
        ],
        'module_name' => [
            'required' => 'Module name is required.',
        ],
        'status' => [
            'in_list' => 'Invalid status. Must be active or inactive.',
        ],
    ];

    protected $skipValidation = false;

    // -------------------------------------------------------
    // Callbacks
    // -------------------------------------------------------
    protected $beforeInsert = ['sanitizeFields', 'setLastAccessedBy'];
    protected $beforeUpdate = ['sanitizeFields', 'setLastAccessedBy'];

    /**
     * Clean up fields
     */
    protected function sanitizeFields(array $data)
    {
        foreach (['module_key', 'module_name', 'description'] as $field) {
            if (!empty($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
            }
        }

        // Normalize key → lowercase
        if (!empty($data['data']['module_key'])) {
            $data['data']['module_key'] = strtolower($data['data']['module_key']);
        }

        // Convert settings array to JSON
    if (isset($data['data']['settings']) && is_array($data['data']['settings'])) {
        $data['data']['settings'] = json_encode($data['data']['settings'], JSON_UNESCAPED_UNICODE);
    }

        return $data;
    }

    /**
     * Auto set last_accessed_by if user is logged in (JWT)
     */
    protected function setLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $uid = getUserId();
            if ($uid) {
                $data['data']['last_accessed_by'] = $uid;
            }
        }

        return $data;
    }
}
