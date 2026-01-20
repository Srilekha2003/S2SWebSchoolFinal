<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentLeaveManagementModel extends Model
{
    protected $table = 'student_leave_management';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'school_id','branch_id','student_id','class_id',
        'leave_type','from_date','to_date',
        'reason','guardian_note',
        'approval_status','approved_by',
        'status','last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'student_id' => 'required|integer',
        'leave_type' => 'required|in_list[Sick,Casual,Festival,Emergency]',
        'from_date' => 'required|valid_date',
        'to_date' => 'required|valid_date',
        'approval_status' => 'in_list[Pending,Approved,Rejected]'
    ];

    protected $beforeInsert = ['trimFields','updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields','updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['reason','guardian_note'] as $field) {
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
