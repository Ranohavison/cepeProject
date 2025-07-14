<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Ecole extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'numEcole' => [ 'type' => 'VARCHAR', 'constraint' => 20],
            'design' => ['type' => 'VARCHAR', 'constraint' => 20],
            'adresse' => ['type' => 'VARCHAR', 'constraint' => 20]
        ]);

        $this->forge->addKey('numEcole', true);
        $this->forge->createTable('ECOLE');
    }

    public function down()
    {
        $this->forge->dropTable('ECOLE');
    }
}
