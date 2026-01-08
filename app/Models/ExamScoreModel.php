<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamScoreModel extends Model
{
    protected $table            = 'exam_scores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'exam_id',
        'student_id',
        'marks_obtained',
        'grade',
        'result_status',
        'remarks',
        'last_accessed_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation Rules
    protected $validationRules = [
        'school_id'      => 'required|integer',
        'branch_id'      => 'required|integer',
        'exam_id'        => 'required|integer',
        'student_id'     => 'required|integer',
        'marks_obtained' => 'required|decimal',
        'grade'          => 'permit_empty|max_length[10]',
        'result_status'  => 'required|in_list[Pass,Fail]',
        'remarks'        => 'permit_empty|string',
        'last_accessed_by' => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'student_id' => ['required' => 'Student is required.'],
        'exam_id'    => ['required' => 'Exam reference is required.']
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['grade'] as $field) {
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
