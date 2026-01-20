<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolCalendarModel extends Model
{
    protected $table            = 'school_calendar';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'title',
        'calendar_type',
        'start_date',
        'end_date',
        'description',
        'is_working_day',
        'visibility',
        'status',
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
        'school_id'      => 'required|integer',
        'branch_id'      => 'required|integer',
        'title'          => 'required|max_length[150]',
        'calendar_type'  => 'required|in_list[Holiday,Event,Exam,Meeting,Festival]',
        'start_date'     => 'required|valid_date',
        'end_date'       => 'required|valid_date',
        'is_working_day' => 'required|in_list[Yes,No]',
        'visibility'     => 'required|in_list[Students,Parents,Staff,All]',
        'status'         => 'in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['title'] as $field) {
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
