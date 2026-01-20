<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGallery extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each gallery record',
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
                'null'       => true,
                'comment'    => 'Foreign key referencing branches.id',
            ],

            'event_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Name of the school event',
            ],

            'image_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Title or caption for the image',
            ],

            'attachment' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'comment'    => 'Gallery image file path',
            ],

            'attachment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image'],
                'default'    => 'image',
                'comment'    => 'Type of attachment',
            ],

            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'User ID who uploaded the image',
            ],

            'uploaded_date' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Date when image was uploaded',
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
                'comment'    => 'User ID who created or updated this record',
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
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('gallery', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('gallery', true);
    }
}
