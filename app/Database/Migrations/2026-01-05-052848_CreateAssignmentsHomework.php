<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssignmentsHomework extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID',
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
                'comment'    => 'Foreign key referencing branches.id',
            ],

            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing classes.id',
            ],

            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing users.id (faculty)',
            ],

            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Subject name',
            ],

            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Assignment/Homework title',
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Detailed description',
            ],

            'assigned_date' => [
                'type'    => 'DATE',
                'comment' => 'Assigned date',
            ],

            'due_date' => [
                'type'    => 'DATE',
                'comment' => 'Submission due date',
            ],

            'submission_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending','submitted','evaluated'],
                'default'    => 'pending',
                'comment'    => 'Submission status',
            ],

            'marks_obtained' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Marks obtained',
            ],

            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'comment'    => 'Attachment file path',
            ],

            'attachment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image','video','document'],
                'null'       => true,
                'comment'    => 'Attachment type',
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
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('assignments_homework', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('assignments_homework', true);
    }
}
