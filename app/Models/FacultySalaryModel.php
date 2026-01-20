<?php

namespace App\Models;

use CodeIgniter\Model;

class FacultySalaryModel extends Model
{
    protected $table            = 'faculty_salary';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id','branch_id','faculty_id',
        'salary_month','salary_type',
        'basic_salary','allowances','deductions','net_salary',
        'payment_date','payment_method','status', 'payment_status',
        'processed_by','remarks','last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'school_id'    => 'required|integer',
        'branch_id'    => 'required|integer',
        'faculty_id'   => 'required|integer',
        'salary_month' => 'required',
        'salary_type'  => 'required|in_list[Monthly,Hourly,Yearly]',
        'basic_salary' => 'required|decimal',
        'net_salary'   => 'required|decimal',
        'payment_status'       => 'in_list[Paid,Unpaid]',
        'status'    => 'in_list[active,inactive]'
    ];

    protected $beforeInsert = ['trimFields','updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields','updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['salary_month','remarks'] as $field) {
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
