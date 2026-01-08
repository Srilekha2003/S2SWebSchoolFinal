<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendance';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'date',
        'student_id',
        'faculty_id',
        'status',
        'remarks',
        'percentage_report',
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
        'school_id'          => 'permit_empty|integer',
        'branch_id'          => 'permit_empty|integer',
        'class_id'           => 'permit_empty|integer',
        'date' => 'required|valid_date',
        'student_id'         => 'required|integer',
        'faculty_id'         => 'permit_empty|integer',
        'status'             => 'permit_empty|in_list[Present,Absent,Late,Leave]',
        'remarks'            => 'permit_empty|string',
        'percentage_report'  => 'permit_empty|decimal',
        'last_accessed_by'         => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'date' => [
            'required' => 'Attendance date is required.'
        ],
        'student_id' => [
            'required' => 'Student is required.'
        ]
    ];

    protected $skipValidation = false;

   // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        if (isset($data['data']['remarks'])) {
            $data['data']['remarks'] = trim($data['data']['remarks']);
        }
        return $data;
    }

    /**
     * Auto-set last_accessed_by based on logged-in user
     */
    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}
