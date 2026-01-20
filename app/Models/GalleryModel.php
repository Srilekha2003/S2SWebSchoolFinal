<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table            = 'gallery';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'event_name',
        'image_title',
        'attachment',
        'attachment_type',
        'uploaded_by',
        'uploaded_date',
        'status',
        'last_accessed_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'event_name'=> 'required|max_length[150]',
        'status'    => 'in_list[active,inactive]'
    ];

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['event_name', 'image_title'] as $field) {
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
