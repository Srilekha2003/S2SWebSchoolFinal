<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudents extends Migration
{
    public function up()
    {
        $this->forge->addField([
   
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary Key'
            ],

            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to school'
            ],

            'branch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to branch'
            ],

            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Student first name'
            ],

            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Student last name'
            ],

            'date_of_birth' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Student date of birth'
            ],

            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['M','F','Other'],
                'null'       => true,
                'comment'    => 'Gender of student'
            ],

            'aadhaar_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                'null'       => true,
                'comment'    => '12 digit Aadhaar number'
            ],

            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to class'
            ],

            'roll_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Student roll number'
            ],

            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
                'comment'    => 'Student phone number'
            ],

            'profile_photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path of student profile photo'
            ],

            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Residential address'
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'comment'    => 'Student email address'
            ],

            'admission_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date of admission'
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Active','Inactive'],
                'default'    => 'Active',
                'comment'    => 'Student status'
            ],

            'discontinuation_status' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'null'       => true,
                'comment'    => 'Whether student has discontinued'
            ],

            'discontinuation_reason' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Reason for discontinuation'
            ],

            'discontinuation_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Date student discontinued'
            ],

            'rejoined' => [
                'type'       => 'ENUM',
                'constraint' => ['Yes','No'],
                'default'    => 'No',
                'null'       => true,
                'comment'    => 'Whether student rejoined'
            ],

            'rejoining_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Rejoining date'
            ],

            'last_accessed_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'User who last accessed this record'
            ],

            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record creation time'
            ],

            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Record update time'
            ],

            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Soft delete timestamp'
            ],

        ]);

        $this->forge->addKey('id', true);

        // Foreign Keys
        $this->forge->addForeignKey('school_id','schools','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('branch_id','branches','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('class_id','classes','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('last_accessed_by','users','id','SET NULL','CASCADE');

        $this->forge->createTable('students', true);
    }

    public function down()
    {
        $this->forge->dropTable('students', true);
    }
}
