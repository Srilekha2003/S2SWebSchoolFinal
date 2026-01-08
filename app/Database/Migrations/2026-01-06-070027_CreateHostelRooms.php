<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHostelRooms extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each hostel room',
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

            'hostel_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Name of the hostel',
            ],

            'room_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Unique room number or identifier',
            ],

            'capacity' => [
                'type'       => 'INT',
                'constraint' => 5,
                'comment'    => 'Maximum number of students allowed in the room',
            ],

            'room_type' => [
                'type'       => 'ENUM',
                'constraint' => ['single', 'double', 'triple', 'dormitory'],
                'comment'    => 'Type of hostel room',
            ],

            'availability' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'occupied', 'maintenance'],
                'default'    => 'available',
                'comment'    => 'Current availability status of the room',
            ],

            'warden_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Name of the hostel warden',
            ],

            'warden_contact' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'Contact number of the hostel warden',
            ],

            'remarks' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Additional remarks about the room',
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
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Create Table
        $this->forge->createTable('hostel_rooms', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('hostel_rooms', true);
    }
}
