<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisciplinePerformance extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each record',
            ],

            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing schools.id',
            ],

            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing branches.id',
            ],

            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing students.id',
            ],

            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing faculty.id (faculty)',
            ],

            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Discipline or Performance',
            ],

            'remarks' => [
                'type'       => 'TEXT',
                'comment'    => 'Remarks or feedback details',
            ],

            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['admin','teacher','parent','all'],
                'default'    => 'all',
                'comment'    => 'Who can see this record',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active','inactive'],
                'default'    => 'active',
                'comment'    => 'Record status',
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

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('discipline_performance', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('discipline_performance', true);
    }
}
