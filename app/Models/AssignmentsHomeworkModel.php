<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentsHomeworkModel extends Model
{
    protected $table            = 'assignments_homework';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'class_id',
        'faculty_id',
        'subject',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'submission_status',
        'marks_obtained',
        'attachment',
        'attachment_type',
        'last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
    'school_id'          => 'required|integer',
    'branch_id'          => 'required|integer',
    'class_id'           => 'required|integer',
    'faculty_id'         => 'required|integer',
    'subject'            => 'required|string|max_length[150]',
    'title'              => 'required|string|max_length[255]',
    'description'        => 'permit_empty|string',
    'assigned_date'      => 'required|valid_date[Y-m-d]',
    'due_date'           => 'required|valid_date[Y-m-d]',
    'submission_status'  => 'required|in_list[pending,submitted,evaluated]',
    'marks_obtained'     => 'permit_empty|decimal',
    'attachment'         => 'permit_empty|string|max_length[512]',
    'attachment_type'    => 'permit_empty|in_list[image,video,document]',
    'last_accessed_by'   => 'permit_empty|integer',
    ];


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
