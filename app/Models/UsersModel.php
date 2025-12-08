<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'role_id',
        'village_id',
        'name',
        'email',
        'password',
        'phone',
        'status',
        'access_code',
        'profile_pic',
        'gender',
        'dob',
        'address',
        'device_token',
        'last_login',
        'last_ip',
        'login_attempts',
        'is_verified',
        'refresh_token',
        'last_accessed_by', // ✅ corrected to match migration
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'role_id'          => 'required|integer',
        'village_id'       => 'required|integer',
        'name'             => 'required|max_length[150]',
        'email'            => 'required_without[phone]|permit_empty|valid_email|is_unique[users.email,id,{id}]',
        'password'         => 'permit_empty|max_length[255]',
        'phone'            => 'required_without[email]|permit_empty|max_length[15]|is_unique[users.phone,id,{id}]',
        'access_code'      => 'permit_empty|max_length[50]',
        'profile_pic'      => 'permit_empty|max_length[255]',
        'gender'           => 'permit_empty|in_list[male,female,other]',
        'dob'              => 'permit_empty|valid_date',
        'address'          => 'permit_empty|string',
        'device_token'     => 'permit_empty|max_length[255]',
        'last_login'       => 'permit_empty|valid_date',
        'last_ip'          => 'permit_empty|max_length[50]',
        'login_attempts'   => 'permit_empty|integer',
        'is_verified'      => 'in_list[yes,no]',
        'refresh_token'    => 'permit_empty',
        'status'           => 'in_list[active,inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already registered.',
        ],
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimFields', 'hashPassword', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'hashPassword', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    /**
     * Trim fields like name and access_code before saving
     */
    protected function trimFields(array $data)
    {
        foreach (['name', 'access_code', 'email'] as $field) {
            if (isset($data['data'][$field])) {
                $data['data'][$field] = trim($data['data'][$field]);
            }
        }
        return $data;
    }

    /**
     * Automatically hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (!empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['data']['password']); // prevent overwriting with empty value on update
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

    public function updateLastLogin($id, $ip)
    {
        return $this->update($id, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip'    => $ip
        ]);
    }
}
