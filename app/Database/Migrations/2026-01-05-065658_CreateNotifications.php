<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key, unique ID for each notification',
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
                'comment'    => 'Foreign key referencing classes.id',
            ],

            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'comment'    => 'Notification title',
            ],

            'message' => [
                'type'    => 'TEXT',
                'comment' => 'Notification message content',
            ],

            'date_time' => [
                'type'    => 'DATETIME',
                'comment' => 'Notification scheduled date and time',
            ],

            'recipient_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Students', 'Teachers', 'Parents', 'All'],
                'comment'    => 'Recipient category',
            ],

            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['general', 'event', 'exam', 'homework', 'announcement', 'holiday', 'fee'],
                'comment'    => 'Notification type',
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Read', 'Unread'],
                'default'    => 'Unread',
                'comment'    => 'Read status',
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'User ID who created this notification',
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
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('last_accessed_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('notifications', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('notifications', true);
    }
}
