<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each user',
            ],
            'role_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing roles.id to assign role to user',
            ],
             'school_id' => [
                 'type'       => 'INT',
                 'constraint' => 11,
                 'unsigned'   => true,
                 'null'       => true,
                 'comment'    => 'Optional foreign key referencing villages.id',
             ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'comment'    => 'Full name of the user',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,   // important because phone may be used instead
                // 'unique'     => true,
                'comment'    => 'Unique email address of the user',
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Hashed password for authentication',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '15',
                'null'       => true,   // important because email may be used instead
                // 'unique'     => true,
                'comment'    => 'Unique contact phone number',
            ],
            'access_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Authentication code for ',
            ],
            'profile_pic' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Path to profile picture',
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null'       => true,
                'comment'    => 'Gender of the user',
            ],
            'dob' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date of birth',
            ],
            'address' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Full postal address of the user',
            ],
            'device_token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Device token for push notifications',
            ],
            'last_login' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Timestamp of last login',
            ],
            'last_ip' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Last IP address used to login',
            ],
            'login_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Number of failed login attempts',
            ],
            'is_verified' => [
                'type'       => 'ENUM',
                'constraint' => ['yes', 'no'],
                'default'    => 'no',
                'comment'    => 'Indicates whether the user is verified',
            ],
            'refresh_token' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Long validity 30-day token'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => 'Account status of the user',
            ],
            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID of who last modified this record (self foreign key)',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record creation timestamp',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record last update timestamp',
            ],
            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Soft delete timestamp; null if active',
            ],
        ]);

        $this->forge->addKey('id', true);

        // Foreign key constraints
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
      //  $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
