<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run()
    {
        $rolesBuilder = $this->db->table('roles');
        $usersBuilder = $this->db->table('users');

        // 1️⃣ Ensure superadmin role exists
        $superadminRole = $rolesBuilder->where('role_name', 'superadmin')->get()->getRowArray();
        if (!$superadminRole) {
            echo "⚠️ Superadmin role not found. Please run RolesSeeder first.\n";
            return;
        }

        // 2️⃣ Check if superadmin already exists
        $existing = $usersBuilder->where('email', 'superadmin@s2swebschools.com')->get()->getRowArray();
        if ($existing) {
            echo "⚠️ Superadmin user already exists.\n";
            return;
        }

        // 3️⃣ Insert default superadmin user
        $usersBuilder->insert([
            'role_id'          => $superadminRole['id'],
            // 'village_id'       => null,
            'name'             => 'S2S Web Solutions',
            'email'            => 'superadmin@s2swebschools.com',
            'password'         => password_hash('Super@1234', PASSWORD_BCRYPT),
            'phone'            => null,
            'status'           => 'active',
            'access_code'      => null,
            'profile_pic'      => null,
            'gender'           => null,
            'dob'              => null,
            'address'          => null,
            'device_token'     => null,
            'last_login'       => null,
            'last_ip'          => null,
            'login_attempts'   => 0,
            'is_verified'      => 'yes',
            'last_accessed_by' => null,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
            'deleted_at'       => null,
        ]);

        echo "✅ Superadmin user created successfully.\n";
    }
}
