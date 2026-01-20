<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacultySalary extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Unique salary record ID',
            ],

            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'School for which salary is paid',
            ],

            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Branch of the school',
            ],

            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Faculty who is being paid',
            ],

            'salary_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'Salary month (e.g., Jan-2026)',
            ],

            'salary_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Monthly','Hourly'],
                'comment'    => 'Type of salary calculation',
            ],

            'basic_salary' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Base salary amount',
            ],

            'allowances' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'null'    => true,
                'comment' => 'Extra allowances like HRA, incentives',
            ],

            'deductions' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'null'    => true,
                'comment' => 'PF, tax, leave deductions',
            ],

            'net_salary' => [
                'type'    => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Final salary after deductions',
            ],

            'payment_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date salary was paid',
            ],

            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash','Bank','UPI'],
                'null'       => true,
                'comment'    => 'Mode of salary payment',
            ],

            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Paid','Unpaid'],
                'default'    => 'Unpaid',
                'comment'    => 'Salary payment status',
            ],

            'processed_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Admin/Accountant who processed salary',
            ],

            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Any notes like bonus or advance adjusted',
            ],
            
             'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => 'Status of gallery record',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User who last modified this record',
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

        // Foreign Keys
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('faculty_salary', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('faculty_salary', true);
    }
}
