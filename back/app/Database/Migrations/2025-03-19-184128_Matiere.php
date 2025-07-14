<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Matiere extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'numMat' => [ 'type' => 'VARCHAR', 'constraint' => 20],
            'designMat' => ['type' => 'VARCHAR', 'constraint' => 200],
            'coef' => ['type' => 'INT', 'constraint' => 20, 'unsigned' => true],
        ]);

        $this->forge->addKey('numMat', true);
        $this->forge->createTable('MATIERE');
    }

    public function down()
    {
        $this->forge->dropTable('MATIERE');
    }
}
