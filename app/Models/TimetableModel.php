<?php

namespace App\Models;

use CodeIgniter\Model;

class TimetableModel extends Model
{
    protected $table            = 'timetable';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'day_of_week',
        'period_number',
        'subject',
        'faculty_id',
        'start_time',
        'end_time',
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
        'school_id'      => 'permit_empty|integer',
        'branch_id'      => 'permit_empty|integer',
        'class_id'       => 'permit_empty|integer',
        'day_of_week'    => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
        'period_number'  => 'required|integer',
        'subject'        => 'required|max_length[100]',
        'faculty_id'     => 'permit_empty|integer',
        'start_time'     => 'permit_empty|valid_date[H:i:s]',
        'end_time'       => 'permit_empty|valid_date[H:i:s]',
        'status'         => 'in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'day_of_week' => [
            'required' => 'Day of week is required.'
        ],
        'period_number' => [
            'required' => 'Period number is required.'
        ],
        'subject' => [
            'required' => 'Subject is required.'
        ]
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['updateLastAccessedBy'];
    protected $beforeUpdate = ['updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
}
