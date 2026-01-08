<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendance extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key for attendance record',
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK → schools.id'
            ],
            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK → branches.id'
            ],
            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK → classes.id'
            ],
            'date' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Date of attendance',
            ],
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK → students.id',
            ],
            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'FK → faculty.id (nullable)',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Present', 'Absent', 'Late', 'Leave'],
                'default'    => 'Present',
                'comment'    => 'Attendance status for the student',
            ],
            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Optional remarks for the record',
            ],
            'percentage_report' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Cached monthly attendance percentage',
            ],
            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID of the person who created this record',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record creation timestamp',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record update timestamp',
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
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create Table
        $this->forge->createTable('attendance', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('attendance', true);
    }
}
