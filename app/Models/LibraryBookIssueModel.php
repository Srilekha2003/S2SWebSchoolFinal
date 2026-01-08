<?php

namespace App\Models;

use CodeIgniter\Model;

class LibraryBookIssueModel extends Model
{
    protected $table            = 'library_book_issue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'book_id',
        'student_id',
        'subject_name',
        'issue_date',
        'due_date',
        'return_date',
        'return_status',
        'fine_amount',
        'issued_by',
        'remarks',
        'last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'school_id'     => 'permit_empty|integer',
        'branch_id'     => 'permit_empty|integer',
        'book_id'       => 'required|integer',
        'student_id'    => 'required|integer',
        'subject_name'  => 'permit_empty|max_length[150]',
        'issue_date'    => 'required|valid_date',
        'due_date'      => 'required|valid_date',
        'return_date'   => 'permit_empty|valid_date',
        'return_status' => 'permit_empty|in_list[Issued,Returned,Overdue]',
        'fine_amount'   => 'permit_empty|decimal',
        'issued_by'     => 'permit_empty|integer',
        'remarks'       => 'permit_empty',
        'last_accessed_by' => 'permit_empty|integer',
    ];

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['subject_name'] as $field) {
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
