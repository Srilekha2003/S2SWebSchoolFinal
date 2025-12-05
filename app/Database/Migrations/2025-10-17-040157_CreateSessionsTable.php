<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => false,
                'comment'    => 'Unique session ID (primary key)',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'comment'    => 'IP address of the user for this session (IPv4 or IPv6)',
            ],
            'timestamp' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Timestamp of last activity for the session',
            ],
            'data' => [
                'type'       => 'BLOB',
                'null'       => true,
                'comment'    => 'Serialized session data',
            ],
        ]);

        $this->forge->addKey('id', true); // primary key
        $this->forge->createTable('ci_sessions', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('ci_sessions', true);
    }
}
