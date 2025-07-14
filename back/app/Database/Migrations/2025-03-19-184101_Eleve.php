<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Eleve extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'numEleve' => [ 'type' => 'VARCHAR', 'constraint' => 20],
            'nom' => ['type' => 'VARCHAR', 'constraint' => 200],
            'prenom' => ['type' => 'VARCHAR', 'constraint' => 200],
            'numEcole' => ['type' => 'VARCHAR', 'constraint' => 20],
        ]);

        $this->forge->addKey('numEleve', true);
        $this->forge->addForeignKey('numEcole', 'ECOLE', 'numEcole', 'CASCADE', 'SET NULL');
        $this->forge->createTable('ELEVE');
    }

    public function down()
    {
        $this->forge->dropTable('ELEVE');
    }
}
