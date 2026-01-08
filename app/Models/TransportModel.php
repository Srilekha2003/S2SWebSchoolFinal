<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportModel extends Model
{
    protected $table            = 'transport';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'school_id',
        'branch_id',
        'student_id',
        'route_number',
        'bus_number',
        'driver_name',
        'driver_contact',
        'pickup_point',
        'drop_point',
        'pickup_time',
        'drop_time',
        'transport_fee',
        'transport_status',
        'remarks',
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
        'student_id'       => 'required|integer',
        'route_number'     => 'required|max_length[50]',
        'bus_number'       => 'required|max_length[50]',
        'driver_name'      => 'permit_empty|max_length[100]',
        'driver_contact'   => 'permit_empty|max_length[15]',
        'pickup_point'     => 'permit_empty|max_length[255]',
        'drop_point'       => 'permit_empty|max_length[255]',
        'pickup_time'      => 'permit_empty|valid_date[H:i:s]',
        'drop_time'        => 'permit_empty|valid_date[H:i:s]',
        'transport_fee'    => 'permit_empty|decimal',
        'transport_status' => 'permit_empty|in_list[Active,Inactive]',
        'last_accessed_by' => 'permit_empty|integer'
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimFields', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    protected function trimFields(array $data)
    {
        foreach (['route_number', 'bus_number', 'driver_name'] as $field) {
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
