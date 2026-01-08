<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamModel extends Model
{
    protected $table            = 'exams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'exam_name',
        'class_id',
        'faculty_id',
        'exam_type',
        'term',
        'subject',
        'date_time',
        'total_marks',
        'passing_marks',
        'status',
        'last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation Rules
    protected $validationRules = [

    'school_id' => 'required|integer',

    'branch_id' => 'permit_empty|integer',

    'exam_name' => 'required|string',

    'class_id' => 'required|integer',

    'faculty_id' => 'permit_empty|integer',

    'exam_type' => 'permit_empty|string|max_length[100]',

    'term' => 'permit_empty|string|max_length[100]',

    'subject' => 'required|string|max_length[150]',

    'date_time' => 'permit_empty|valid_date',

    'total_marks' => 'required|integer|greater_than[0]',

    'passing_marks' => 'required|integer',

    'status' => 'permit_empty|in_list[active,inactive]',

    'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'school_id' => ['required' => 'School is required'],
        'exam_name' => ['required' => 'Exam name is required'],
        'class_id'  => ['required' => 'Class is required'],
        'subject'   => ['required' => 'Subject is required'],
    ];

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['exam_name', 'exam_type', 'term', 'subject'] as $field) {
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
