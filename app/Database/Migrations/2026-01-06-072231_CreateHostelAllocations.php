<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHostelAllocations extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each hostel allocation',
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

            'room_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing hostel_rooms.id',
            ],

            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key referencing students.id',
            ],

            'allocation_date' => [
                'type'    => 'DATE',
                'comment' => 'Date on which room was allocated',
            ],

            'vacate_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date on which room was vacated',
            ],

            'allocation_status' => [
                'type'       => 'ENUM',
                'constraint' => ['allocated', 'vacated'],
                'default'    => 'allocated',
                'comment'    => 'Current allocation status',
            ],

            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Additional remarks',
            ],

            // 'allocated_by' => [
            //     'type'       => 'INT',
            //     'constraint' => 11,
            //     'unsigned'   => true,
            //     'comment'    => 'User ID (warden/admin) who allocated the room',
            // ],

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
        $this->forge->addForeignKey('room_id', 'hostel_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('allocated_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('hostel_allocations', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('hostel_allocations', true);
    }
}
