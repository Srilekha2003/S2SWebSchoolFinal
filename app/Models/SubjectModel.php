<?php

namespace App\Models;

use CodeIgniter\Model;

class SubjectModel extends Model
{
    protected $table            = 'subjects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'faculty_id',
        'name',
        'code',
        'sub_type',
        'passing_marks',
        'max_marks',
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
        'school_id'     => 'permit_empty|integer',
        'branch_id'     => 'permit_empty|integer',
        'class_id'      => 'permit_empty|integer',
        'faculty_id'    => 'permit_empty|integer',
        'name'          => 'required|max_length[150]',
        'code'          => 'required|max_length[50]',
        'sub_type'      => 'permit_empty|max_length[50]',
        'passing_marks' => 'permit_empty|integer',
        'max_marks'     => 'permit_empty|integer',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'name' => ['required' => 'Subject name is required.'],
        'code' => ['required' => 'Subject code is required.'],
    ];

    protected $skipValidation = false;

    // Callbacks (same as Agriculture)
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['name', 'code', 'sub_type'] as $field) {
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
