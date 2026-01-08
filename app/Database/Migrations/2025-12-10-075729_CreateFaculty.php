<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFaculty extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each faculty record',
            ],

            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing schools.id',
            ],

            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing branches.id (optional)',
            ],

            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Faculty first name',
            ],

            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Faculty last name (optional)',
            ],

            'date_of_birth' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date of birth',
            ],

            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['Male','Female','Other'],
                'null'       => true,
                'comment'    => 'Gender of faculty',
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Email address',
            ],

            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Contact phone number',
            ],

            'address' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Full address (optional)',
            ],

            'joining_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date of joining',
            ],

            'employment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Full-time','Part-time','Contract'],
                'null'       => true,
                'comment'    => 'Employment type',
            ],

            'retired' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'null'       => true,
            ],

            'resigned' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'null'       => true,
            ],

            'reason_for_leaving' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Reason of leaving (optional)',
            ],

            'rejoined' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'null'       => true,
            ],

            'rejoining_date' => [
                'type'    => 'DATE',
                'null'    => true,
            ],

            'designation' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Job title or post',
            ],

            'qualification' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Educational qualifications',
            ],

            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Profile photo path',
            ],

            'experience_years' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Total experience years',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active','inactive'],
                'default'    => 'active',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User who last modified the record',
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

        // Primary Key
        $this->forge->addKey('id', true);

        // Foreign Keys
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create table
        $this->forge->createTable('faculty', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('faculty', true);
    }
}
