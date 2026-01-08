<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicalRecordsModel extends Model
{
    protected $table            = 'medical_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'student_id',
        'medical_date',
        'medical_issues',
        'severity',
        'first_aid_given',
        'referred_to_hospital',
        'guardian_notified',
        'remarks',
        'last_accessed_by'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'school_id' => 'permit_empty|integer',
        'branch_id' => 'permit_empty|integer',
        'student_id' => 'required|integer',
        'medical_date' => 'required|valid_date',
        'medical_issues' => 'required|string',
        'severity' => 'permit_empty|in_list[Mild,Moderate,Severe]',
        'first_aid_given' => 'permit_empty|string',
        'referred_to_hospital' => 'permit_empty|in_list[Yes,No]',
        'guardian_notified' => 'permit_empty|in_list[Yes,No]',
        'remarks' => 'permit_empty|string',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['medical_issues', 'first_aid_given', 'remarks'] as $field) {
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
