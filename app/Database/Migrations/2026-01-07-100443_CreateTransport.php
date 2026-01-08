<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransport extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each transport record',
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
                'comment'    => 'Foreign key referencing students.id',
            ],

            'route_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Assigned transport route number',
            ],

            'bus_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Assigned bus number',
            ],

            'driver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Driver name',
            ],

            'driver_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
                'comment'    => 'Driver contact number',
            ],

            'pickup_point' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Pickup location',
            ],

            'drop_point' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Drop location',
            ],

            'pickup_time' => [
                'type'    => 'TIME',
                'null'    => true,
                'comment' => 'Pickup time',
            ],

            'drop_time' => [
                'type'    => 'TIME',
                'null'    => true,
                'comment' => 'Drop time',
            ],

            'transport_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'comment'    => 'Transport fee amount',
            ],

            'transport_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Active', 'Inactive'],
                'default'    => 'Active',
                'comment'    => 'Transport status',
            ],

            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Additional remarks',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID who created/updated this record',
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
        $this->forge->addForeignKey('school_id', 'schools', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('transport', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('transport', true);
    }
}
