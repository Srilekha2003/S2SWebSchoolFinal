<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentLeaveManagement extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
                'comment' => 'Primary key, unique ID for each leave request'
            ],

            'school_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Foreign key referencing schools.id'
            ],

            'branch_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Foreign key referencing branches.id'
            ],

            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Foreign key referencing students.id'
            ],

            'class_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Foreign key referencing classes.id'
            ],

            'leave_type' => [
                'type' => 'ENUM',
                'constraint' => ['Sick','Casual','Festival','Emergency'],
                'comment' => 'Type of leave'
            ],

            'from_date' => [
                'type' => 'DATE',
                'comment' => 'Leave start date'
            ],

            'to_date' => [
                'type' => 'DATE',
                'comment' => 'Leave end date'
            ],

            'reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Reason for leave'
            ],

            'guardian_note' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Parent note'
            ],

            'approval_status' => [
                'type' => 'ENUM',
                'constraint' => ['Pending','Approved','Rejected'],
                'default' => 'Pending',
                'comment' => 'Approval status'
            ],

            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Faculty who approved'
            ],

            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active','inactive'],
                'default' => 'active',
                'comment' => 'Record status'
            ],

            'last_accessed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'User who created/updated'
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Created timestamp'
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Updated timestamp'
            ],

            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Soft delete timestamp'
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('school_id','schools','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('branch_id','branches','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('student_id','students','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('class_id','classes','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('approved_by','faculty','id','SET NULL','CASCADE');
        $this->forge->addForeignKey('last_accessed_by','users','id','SET NULL','CASCADE');

        $this->forge->createTable('student_leave_management', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('student_leave_management', true);
    }
}
