<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimetable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each timetable record',
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

            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing classes.id',
            ],

            'day_of_week' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Monday','Tuesday','Wednesday',
                    'Thursday','Friday','Saturday','Sunday'
                ],
                'comment'    => 'Day of the week for the timetable',
            ],

            'period_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Period number in the day',
            ],

            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Subject taught in this period',
            ],

            'faculty_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key referencing users.id (faculty)',
            ],

            'start_time' => [
                'type'    => 'TIME',
                'null'    => true,
                'comment' => 'Period start time',
            ],

            'end_time' => [
                'type'    => 'TIME',
                'null'    => true,
                'comment' => 'Period end time',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'comment'    => 'Timetable record status',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User who last created/updated this record',
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

        // 🔑 FOREIGN KEYS
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('faculty_id', 'faculty', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create table
        $this->forge->createTable('timetable', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('timetable', true);
    }
}
