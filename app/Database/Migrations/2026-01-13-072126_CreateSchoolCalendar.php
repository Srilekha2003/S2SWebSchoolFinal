<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchoolCalendar extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each school calendar record',
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

            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'comment'    => 'Event or Holiday title',
            ],

            'calendar_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Holiday','Event','Exam','Meeting','Festival'],
                'comment'    => 'Type of calendar entry',
            ],

            'start_date' => [
                'type'    => 'DATE',
                'comment' => 'Event or holiday start date',
            ],

            'end_date' => [
                'type'    => 'DATE',
                'comment' => 'Event or holiday end date',
            ],

            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Details for parents and staff',
            ],

            'is_working_day' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'comment'    => 'Whether attendance is allowed',
            ],

            'visibility' => [
                'type'       => 'ENUM',
                'constraint' => ['Students','Parents','Staff','All'],
                'default'    => 'All',
                'comment'    => 'Who can view this calendar entry',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active','inactive'],
                'default'    => 'Active',
                'comment'    => 'Status of the record',
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
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('school_calendar', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('school_calendar', true);
    }
}
