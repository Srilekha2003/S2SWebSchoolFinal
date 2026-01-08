<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolsModel extends Model
{
    protected $table            = 'schools';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_name',
        'school_code',
        'address',
        'type',
        'management',
        'chairman_name',
        'contact_number',
        'email',
        'total_students',
        'total_teachers',
        'established_year',
        'status',
        'logo',
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
        'school_name'      => 'required|max_length[150]',
        'school_code'      => 'permit_empty|alpha_numeric_punct|max_length[50]',
        'address'          => 'permit_empty|max_length[255]',
        'type'             => 'permit_empty|in_list[Primary,Secondary,High School]',
        'management'       => 'permit_empty|max_length[100]|in_list[Government,Private,Aided,Unaided,Municipal,NGO]',
        'chairman_name'    => 'permit_empty|max_length[100]',
        'contact_number'   => 'permit_empty|max_length[15]',
        'email'            => 'permit_empty|valid_email',
        'total_students'   => 'permit_empty|integer',
        'total_teachers'   => 'permit_empty|integer',
        'established_year' => 'permit_empty|integer',
        'status'           => 'permit_empty|in_list[active,inactive]',
        'logo'             => 'permit_empty|string|max_length[255]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'school_name' => [
            'required' => 'School name is required.'
        ]
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
        foreach ([
            'school_name',
            'school_code',
            'address',
            'type',
            'management',
            'chairman_name',
            'email'
        ] as $field) {

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
