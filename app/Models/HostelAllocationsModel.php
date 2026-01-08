<?php

namespace App\Models;

use CodeIgniter\Model;

class HostelAllocationsModel extends Model
{
    protected $table            = 'hostel_allocations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'room_id',
        'student_id',
        'allocation_date',
        'vacate_date',
        'allocation_status',
        'remarks',
        // 'allocated_by',
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
        'school_id' => 'required|integer',
        'room_id'   => 'required|integer',
        'student_id'=> 'required|integer',
        'allocation_status' => 'in_list[allocated,vacated]',
        'remarks'   => 'permit_empty|string',
        // 'allocated_by' => 'required|integer',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

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

    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}
