<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgressCardModel extends Model
{
    protected $table            = 'progress_cards';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'student_id',
        'exam_id',
        'class_id',
        'total_marks',
        'obtained_marks',
        'percentage',
        'rank',
        'grade',
        'result_status',
        'overall_remarks',
        'teacher_signature',
        'principal_signature',
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
        'school_id'      => 'permit_empty|integer',
        'branch_id'      => 'permit_empty|integer',
        'student_id'     => 'required|integer',
        'exam_id'        => 'required|integer',
        'class_id'       => 'permit_empty|integer',
        'total_marks'    => 'required|integer',
        'obtained_marks' => 'required|integer',
        'percentage'     => 'permit_empty|decimal',
        'rank'           => 'permit_empty|integer',
        'grade'          => 'permit_empty|max_length[10]',
        'result_status'  => 'permit_empty|in_list[Pass,Fail]',
        'overall_remarks'=> 'permit_empty|string',
        'teacher_signature'   => 'permit_empty|max_length[255]',
        'principal_signature' => 'permit_empty|max_length[255]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['trimFields', 'updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['overall_remarks', 'grade'] as $field) {
            if (isset($data['data'][$field]) && is_string($data['data'][$field])) {
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
