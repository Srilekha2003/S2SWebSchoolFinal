<?php

namespace App\Models;

use CodeIgniter\Model;

class CulturalActivitiesModel extends Model
{
    protected $table            = 'cultural_activities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'event_name',
        'date_time',
        'venue',
        'category',
        'awards_recognitions',
        'description',
        'coordinator_name',
        'attachment',
        'attachment_type',
        'status',
        'last_accessed_by'
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
        'school_id' => 'required|integer',
        'branch_id' => 'required|integer',
        'class_id'  => 'permit_empty|integer',
        'event_name'=> 'required|max_length[150]',
        'venue'     => 'required|max_length[150]',
        'category'  => 'required|max_length[100]',
        'status'    => 'in_list[upcoming,ongoing,completed,cancelled]',
        'attachment_type' => 'permit_empty|in_list[image,video,document]',
        'last_accessed_by' => 'permit_empty|integer',
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['event_name','venue','category','coordinator_name'] as $field) {
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
