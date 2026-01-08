<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchools extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each school',
            ],
            'school_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => false,
                'comment'    => 'Official name of the school',
            ],
            'school_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Unique school identification code',
            ],
            'address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Full address of the school',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['Primary', 'Secondary', 'High School'],
                'default'    => 'Primary',
                'comment'    => 'Type of school',
            ],
            'management' => [
                'type'       => 'ENUM',
                'constraint' => ['Government', 'Private', 'Aided', 'Unaided', 'Municipal', 'NGO'],
                'default'    => 'Private',
                'comment'    => 'School management type',
            ],
            'chairman_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Name of the school chairman or head',
            ],
            'contact_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '15',
                'null'       => true,
                'comment'    => 'Official contact phone number',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'comment'    => 'Official email address',
            ],
            
            'total_students' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 0,
                'comment'    => 'Total number of students in the school',
            ],
            'total_teachers' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => 0,
                'comment'    => 'Total number of teachers in the school',
            ],
            'established_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => true,
                'comment'    => 'Year the school was established',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => 'Indicates whether the school record is active or inactive',
            ],
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'School Logo Path'
            ],
            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID of admin (foreign key to users table)',
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

        // Primary Key
        $this->forge->addKey('id', true);
        
        // Foreign Key
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create Table
        $this->forge->createTable('schools', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('schools', true);
    }
}
