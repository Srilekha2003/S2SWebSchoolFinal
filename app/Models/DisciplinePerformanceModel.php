<?php

namespace App\Models;

use CodeIgniter\Model;

class DisciplinePerformanceModel extends Model
{
    protected $table            = 'discipline_performance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'student_id',
        'faculty_id',
        'category',
        'remarks',
        'visibility',
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
        'category' => 'required|max_length[50]',
        'remarks'  => 'required|string',
        'visibility' => 'permit_empty|in_list[admin,teacher,parent,all]',
        'status'     => 'in_list[active,inactive]',
        'school_id'  => 'permit_empty|integer',
        'branch_id'  => 'permit_empty|integer',
        'student_id' => 'permit_empty|integer',
        'faculty_id' => 'permit_empty|integer',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'category' => ['required' => 'Category is required.'],
        'remarks'  => ['required' => 'Remarks are required.']
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['remarks', 'category', 'visibility'] as $field) {
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
