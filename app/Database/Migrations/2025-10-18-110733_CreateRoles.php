<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key: unique ID for each role',
            ],
            'role_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
                'comment'    => 'Name of the role (e.g., Admin, User, Villager)',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Optional detailed description of the role',
            ],
            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID who last modified this role record',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id', true); // primary key
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'CASCADE', 'SET NULL'); // very first migration created roles commented this line out
        $this->forge->createTable('roles', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('roles', true);
    }
}
