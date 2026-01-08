<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLibraryBooks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each library book',
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
                'constraint' => 150,
                'comment'    => 'Title of the book',
            ],

            'author' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Author name',
            ],

            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Book category',
            ],

            'isbn_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'ISBN number of the book',
            ],

            'copies_total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Total number of copies',
            ],

            'copies_available' => [
                'type'       => 'INT',
                'constraint' => 11,
                'comment'    => 'Available copies',
            ],

            'availability_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Available', 'Issued'],
                'default'    => 'Available',
                'comment'    => 'Current availability status',
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

        // Create table
        $this->forge->createTable('library_books', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('library_books', true);
    }
}
