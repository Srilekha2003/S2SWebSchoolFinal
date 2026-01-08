<?php

namespace App\Models;

use CodeIgniter\Model;

class LibraryBooksModel extends Model
{
    protected $table            = 'library_books';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'title',
        'author',
        'category',
        'isbn_number',
        'copies_total',
        'copies_available',
        'availability_status',
        'remarks',
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
        'school_id'          => 'required|integer',
        'branch_id'          => 'required|integer',
        'title'              => 'required|max_length[150]',
        'author'             => 'required|max_length[150]',
        'category'           => 'required|max_length[100]',
        'isbn_number'        => 'permit_empty|max_length[20]',
        'copies_total'       => 'required|integer',
        'copies_available'   => 'required|integer',
        'availability_status' => 'required|in_list[Available,Issued]',
        'remarks'            => 'permit_empty|string',
        'last_accessed_by'   => 'permit_empty|integer',
    ];

    protected $validationMessages = [
        'title'  => ['required' => 'Book title is required.'],
        'author' => ['required' => 'Author name is required.'],
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['title', 'author', 'category', 'isbn_number'] as $field) {
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
