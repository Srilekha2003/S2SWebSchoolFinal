<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $builder = $this->db->table('roles');

        // Define default roles and permissions (Boolean-based structure)
        $roles = [
            [
                'role_name'       => 'superadmin',
                'description'     => 'System or Project Administrator with full access to all modules',
                'last_accessed_by' => null, // System default (no user yet)
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
                'deleted_at'      => null,
            ],
        ];

        foreach ($roles as $role) {
            $exists = $builder->where('role_name', $role['role_name'])->get()->getRowArray();
            if (!$exists) {
                $builder->insert($role);
                echo "✅ Role '{$role['role_name']}' created.\n";
            } else {
                echo "⚠️ Role '{$role['role_name']}' already exists.\n";
            }
        }
    }
}
