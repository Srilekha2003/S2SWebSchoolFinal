<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchesModel extends Model
{
    protected $table            = 'branches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_code',
        'branch_name',
        'principal_name',
        'address',
        'contact_email',
        'contact_phone',
        'established_year',
        'latitude',
        'longitude',
        'remarks',
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

    // Validation Rules
    protected $validationRules = [
        'school_id'        => 'required|integer',
        'branch_code'      => 'permit_empty|alpha_numeric_punct|max_length[50]',
        'branch_name'      => 'required|max_length[150]',
        'principal_name'   => 'permit_empty|max_length[100]',
        'address'          => 'permit_empty|max_length[255]',
        'contact_email'    => 'permit_empty|valid_email|max_length[150]',
        'contact_phone'    => 'permit_empty|max_length[20]',
        'established_year' => 'permit_empty|integer',
        'latitude'         => 'permit_empty|decimal',
        'longitude'        => 'permit_empty|decimal',
        'remarks'          => 'permit_empty',
        'status'           => 'permit_empty|in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'school_id' => ['required' => 'School reference is required.'],
        'branch_name' => ['required' => 'Branch name is required.']
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    /**
     * Trim whitespace for string fields
     */
    protected function trimFields(array $data)
    {
        foreach (['branch_name', 'branch_code', 'principal_name'] as $field) {
            if (isset($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
            }
        }
        return $data;
    }

    /**
     * Auto-set last_accessed_by based on logged-in user
     */
    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}
