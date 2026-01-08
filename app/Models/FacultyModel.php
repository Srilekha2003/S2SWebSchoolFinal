<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultyModel extends Model
{
    protected $table            = 'faculty';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'email',
        'phone_number',
        'address',
        'joining_date',
        'employment_status',
        'retired',
        'resigned',
        'reason_for_leaving',
        'rejoined',
        'rejoining_date',
        'designation',
        'qualification',
        'photo',
        'experience_years',
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
        'school_id'         => 'required|integer',
        'branch_id'         => 'permit_empty|integer',
        'first_name'        => 'required|max_length[100]',
        'last_name'         => 'permit_empty|max_length[100]',
        'date_of_birth'     => 'permit_empty|valid_date',
        'gender'            => 'permit_empty|in_list[Male,Female,Other]',
        'email'             => 'permit_empty|valid_email|max_length[150]',
        'phone_number'      => 'permit_empty|max_length[20]',
        'address'           => 'permit_empty',
        'joining_date'      => 'permit_empty|valid_date',
        'employment_status' => 'permit_empty|in_list[Full-time,Part-time,Contract]',
        'retired'           => 'permit_empty|in_list[Yes,No]',
        'resigned'          => 'permit_empty|in_list[Yes,No]',
        'reason_for_leaving'=> 'permit_empty',
        'rejoined'          => 'permit_empty|in_list[Yes,No]',
        'rejoining_date'    => 'permit_empty|valid_date',
        'designation'       => 'permit_empty|string|max_length[100]',
        'qualification'     => 'permit_empty|string|max_length[150]',
        'photo'             => 'permit_empty|string|max_length[255]',
        'experience_years'  => 'permit_empty|integer',
        'status'            => 'permit_empty|in_list[active,inactive]',
        'last_accessed_by'  => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'school_id' => [ 'required' => 'School reference is required.' ],
        'first_name'  => [ 'required' => 'First name is required.' ]
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    /**
     * Trim whitespace for important fields
     */
    protected function trimFields(array $data)
    {
        foreach (['first_name', 'last_name', 'designation', 'qualification'] as $field) {
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
