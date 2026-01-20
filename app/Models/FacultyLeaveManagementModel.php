<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultyLeaveManagementModel extends Model
{
    protected $table            = 'faculty_leave_management';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'faculty_id',
        'leave_type',
        'from_date',
        'to_date',
        'reason',
        'approval_status',
        'approved_by',
        'status',
        'last_accessed_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'school_id'  => 'required|integer',
        'branch_id'  => 'required|integer',
        'faculty_id' => 'required|integer',
        'leave_type' => 'required|in_list[Sick,Casual,Earned,Medical,Maternity,Paternity,Compensatory,LossOfPay]',
        'from_date'  => 'required|valid_date',
        'to_date'    => 'required|valid_date',
        'reason'     => 'permit_empty|string',
        'approval_status' => 'in_list[Pending,Approved,Rejected]',
        'approved_by' => 'permit_empty|string',
        'status' => 'in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['trimFields','updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields','updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['reason','approved_by'] as $field) {
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
