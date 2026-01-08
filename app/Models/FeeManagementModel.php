<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeManagementModel extends Model
{
    protected $table            = 'fee_management';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'academic_year',
        'class_id',
        'student_id',
        'fee_type',
        'installment_no',
        'is_installment',
        'amount_due',
        'amount_paid',
        'discount',
        'late_fee',
        'due_date',
        'payment_date',
        'payment_status',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'remarks',
        'last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'academic_year'  => 'required|max_length[9]',
        'fee_type'       => 'required|max_length[100]',
        'amount_due'     => 'required|decimal',
        'payment_status' => 'permit_empty|in_list[Paid,Unpaid,Partial]',
    ];

    protected $beforeInsert = ['trimFields','updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields','updateLastAccessedBy'];
    protected $beforeDelete = ['trimFields','updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach ([
            'fee_type',
            'payment_method',
            'transaction_id',
            'receipt_number',
            'remarks'
        ] as $field) {
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
