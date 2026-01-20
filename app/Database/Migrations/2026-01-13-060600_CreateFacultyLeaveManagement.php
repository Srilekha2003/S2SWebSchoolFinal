<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacultyLeaveManagement extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each faculty leave record',
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

            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing faculty.id',
            ],

            'leave_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Sick','Casual','Earned','Medical','Maternity','Paternity','Compensatory','LossOfPay'],
                'comment'    => 'Type of leave applied by faculty',
            ],

            'from_date' => [
                'type'    => 'DATE',
                'comment' => 'Leave start date',
            ],

            'to_date' => [
                'type'    => 'DATE',
                'comment' => 'Leave end date',
            ],

            'reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Reason provided by faculty for leave',
            ],

            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Pending','Approved','Rejected'],
                'default'    => 'Pending',
                'comment'    => 'Approval status of the leave request',
            ],

            'approved_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Admin / HOD / Principal / Chairman who approved',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active','inactive'],
                'default'    => 'active',
                'comment'    => 'Status of the leave record',
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
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('faculty_leave_management', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('faculty_leave_management', true);
    }
}
