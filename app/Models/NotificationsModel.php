<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationsModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'title',
        'message',
        'date_time',
        'recipient_type',
        'type',
        'status',
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
        'school_id'       => 'required|integer',
        'branch_id'       => 'required|integer',
        'class_id'        => 'permit_empty|integer',
        'title'           => 'required|max_length[200]',
        'message'         => 'required',
        'date_time'       => 'required|valid_date',
        'recipient_type'  => 'required|in_list[Students,Teachers,Parents,All]',
        'type'            => 'required|in_list[general,event,exam,homework,announcement,holiday,fee]',
        'status'          => 'permit_empty|in_list[Read,Unread]',
        'last_accessed_by'=> 'permit_empty|integer',
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        if (isset($data['data']['title'])) {
            $data['data']['title'] = trim($data['data']['title']);
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
