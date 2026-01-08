<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassesModel extends Model
{
    protected $table            = 'classes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'faculty_id',
        'class_name',
        'total_students',
        'section',
        'subjects_covered',
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
        'branch_id'        => 'required|integer',
        'faculty_id'       => 'permit_empty|integer',
        'class_name'       => 'required|max_length[100]',
        'total_students'   => 'permit_empty|integer',
        'section'          => 'permit_empty|max_length[20]',
        'subjects_covered' => 'permit_empty|string',
        'status'           => 'in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'school_id'  => ['required' => 'School reference is required.'],
        'branch_id'  => ['required' => 'Branch reference is required.'],
        'class_name' => ['required' => 'Class name is required.']
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['class_name', 'section', 'subjects_covered'] as $field) {
            if (isset($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
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

