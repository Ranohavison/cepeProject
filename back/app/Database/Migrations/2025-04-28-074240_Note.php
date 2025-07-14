<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Note extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'anneeScolaire' => [ 'type' => 'VARCHAR', 'constraint' => 20],
            'numEleve' => ['type' => 'VARCHAR', 'constraint' => 20],
            'numMat' => ['type' => 'VARCHAR', 'constraint' => 20],
            'note' => ['type' => 'FLOAT', 'constraint' => 20],
        ]);

        $this->forge->addForeignKey('numEleve', 'ELEVE', 'numEleve', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('numMat', 'MATIERE', 'numMat', 'CASCADE', 'CASCADE');
        $this->forge->createTable('NOTE');
    }

    public function down()
    {
        $this->forge->dropTable('NOTE');
    }
}
