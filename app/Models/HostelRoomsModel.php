<?php

namespace App\Models;

use CodeIgniter\Model;

class HostelRoomsModel extends Model
{
    protected $table            = 'hostel_rooms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'hostel_name',
        'room_number',
        'capacity',
        'room_type',
        'availability',
        'warden_name',
        'warden_contact',
        'remarks',
        'last_accessed_by',
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
        'school_id'       => 'required|integer',
        'branch_id'       => 'required|integer',
        'hostel_name'     => 'required|max_length[150]',
        'room_number'     => 'required|max_length[50]',
        'capacity'        => 'required|integer',
        'room_type'       => 'required|in_list[single,double,triple,dormitory]',
        'availability'    => 'in_list[available,occupied,maintenance]',
        'warden_name'     => 'permit_empty|max_length[150]',
        'warden_contact'  => 'permit_empty|max_length[20]',
        'remarks'         => 'permit_empty|string',
        'last_accessed_by'=> 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'school_id'   => ['required' => 'School reference is required.'],
        'branch_id'   => ['required' => 'Branch reference is required.'],
        'room_number' => ['required' => 'Room number is required.'],
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    /**
     * Trim whitespace for string fields
     */
    protected function trimFields(array $data)
    {
        foreach (['hostel_name', 'room_number', 'warden_name'] as $field) {
            if (isset($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
            }
        }
        return $data;
    }

    /**
     * Auto-set last_accessed_by
     */
    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}
