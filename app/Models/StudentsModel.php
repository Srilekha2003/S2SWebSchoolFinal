<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentsModel extends Model
{
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'aadhaar_number',
        'class_id',
        'roll_number',
        'phone_number',
        'profile_photo',
        'address',
        'email',
        'admission_date',
        'status',
        'discontinuation_status',
        'discontinuation_reason',
        'discontinuation_date',
        'rejoined',
        'rejoining_date',
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
        'school_id'      => 'permit_empty|integer',
        'branch_id'      => 'permit_empty|integer',
        'first_name'     => 'required|max_length[100]',
        'last_name'      => 'permit_empty|max_length[100]',
        'date_of_birth'  => 'permit_empty|valid_date',
        'gender'         => 'permit_empty|in_list[M,F,Other]',
        'aadhaar_number' => 'permit_empty|numeric|exact_length[12]|is_unique[students.aadhaar_number,id,{id}]',
        'class_id'       => 'permit_empty|integer',
        'roll_number'    => 'permit_empty|max_length[50]',
        'phone_number'   => 'permit_empty|string|max_length[15]',
        'profile_photo'  => 'permit_empty|string|max_length[255]',
        'address'        => 'permit_empty|max_length[255]',
        'email'          => 'permit_empty|valid_email|max_length[150]',
        'admission_date' => 'permit_empty|valid_date',
        'status'         => 'permit_empty|in_list[Active,Inactive]',
        'discontinuation_status' => 'permit_empty|in_list[Yes,No]',
        'discontinuation_reason' => 'permit_empty|string',
        'discontinuation_date'   => 'permit_empty|valid_date',
        'rejoined'       => 'permit_empty|in_list[Yes,No]',
        'rejoining_date' => 'permit_empty|valid_date',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['trimFields','updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields','updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['first_name','last_name','address'] as $field) {
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

