<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolsModel extends Model
{
    protected $table            = 'schools';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'school_name', 
        'school_code',
        'address', 
        'type', 
        'management', 
        'chairman_name',
        'contact_number', 
        'email', 
        'total_students',
        'total_teachers', 
        'established_year',
        'status',
        'logo', 
        'last_accessed_by'
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
        'school_name'      => 'required|max_length[150]',
        'school_code' => 'permit_empty|alpha_numeric_punct|max_length[50]',
        'address'          => 'permit_empty|max_length[255]',
        'type'             => 'permit_empty|in_list[Primary,Secondary,High School]',
        'management'       => 'permit_empty|max_length[100]|in_list[Government,Private,Aided,Unaided,Municipal,NGO]',
        'chairman_name'    => 'permit_empty|max_length[100]',
        'contact_number'   => 'permit_empty|max_length[15]',
        'email'            => 'permit_empty|valid_email',
        'total_students'   => 'permit_empty|integer',
        'total_teachers'   => 'permit_empty|integer',
        'established_year' => 'permit_empty|integer',
        'status'           => 'in_list[active,inactive]',
        'logo' => 'permit_empty|string|max_length[255]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    // Callbacks
    protected $beforeInsert = ['trimSchoolName', 'updateLastAccessedBy'];
    protected $beforeUpdate = ['trimSchoolName', 'updateLastAccessedBy'];
    protected $beforeDelete = ['updateLastAccessedBy'];

    // Trin Fields
    protected function trimSchoolName(array $data)
{
    if (isset($data['data']['school_name'])) {
        $data['data']['school_name'] = trim($data['data']['school_name']);
    }
    return $data;
}

    
    /**
     * Auto-set last_accessed_by
     */
protected function updateLastAccessedBy(array $data)
    {
        if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
            $data['data']['last_accessed_by'] = getUserId();
        }
        return $data;
    }
// {
//     "success": false,
//     "status": 500,
//     "message": "Server error while creating school record: Call to undefined function App\\Models\\getUserId()",
//     "data": null
// }


// protected function updateLastAccessedBy(array $data)
//     {
//         if (!isset($data['data']['last_accessed_by']) && function_exists('getUserId')) {
//             $data['data']['last_accessed_by'] = getUserId();
//         }
//         return $data;
//     }

// {
//     "success": false,
//     "status": 500,
//     "message": "Server error while creating school record: Call to undefined function App\\Models\\getUserId()",
//     "data": null
// }



//     protected function updateLastAccessedBy(array $data)
// {
//     // Get current user ID, fallback to 1 for testing
//     $userId = (function_exists('getUserId') && getUserId()) ? getUserId() : 1;

//     if (isset($data['data']) && is_array($data['data'])) {
//         $data['data']['last_accessed_by'] = $userId;
//     }

//     return $data;
// }


}
