<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCulturalActivities extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each cultural activity',
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
                'null'       => true,
                'comment'    => 'Foreign key referencing classes.id (optional)',
            ],

            'event_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Name of the cultural event',
            ],

            'date_time' => [
                'type'    => 'DATETIME',
                'comment' => 'Event date and time',
            ],

            'venue' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Event venue/location',
            ],

            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Event category',
            ],

            'awards_recognitions' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Awards or recognitions received',
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Event description',
            ],

            'coordinator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Event coordinator name',
            ],

            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'comment'    => 'Attached file path (optional)',
            ],

            'attachment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image','video','document'],
                'null'       => true,
                'default'    => 'image',
                'comment'    => 'Type of attachment file',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['upcoming','ongoing','completed','cancelled'],
                'default'    => 'upcoming',
                'comment'    => 'Event status',
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
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('cultural_activities', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('cultural_activities', true);
    }
}
