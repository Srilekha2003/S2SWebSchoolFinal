<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBranches extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each branch record',
            ],

            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing schools.id',
            ],

            'branch_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Unique code for the branch (optional)',
            ],

            'branch_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'comment'    => 'Name of the branch',
            ],

            'principal_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Branch principal name (optional)',
            ],

            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Branch address',
            ],

            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'comment'    => 'Branch email address',
            ],

            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'Branch contact phone number',
            ],

            'established_year' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Year branch was established',
            ],

            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
                'comment'    => 'Latitude coordinates (optional)',
            ],

            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
                'comment'    => 'Longitude coordinates (optional)',
            ],

            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Additional remarks (optional)',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => 'Status of the branch',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID who created/updated the record',
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
                'comment' => 'Soft delete timestamp',
            ],
        ]);

        // Primary Key
        $this->forge->addKey('id', true);

        // Foreign Keys
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create table
        $this->forge->createTable('branches', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('branches', true);
    }
}
