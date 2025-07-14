<?php

namespace App\Models;

use CodeIgniter\Model;

class EleveModel extends Model
{
    protected $table            = 'ELEVE';
    protected $primaryKey       = 'numEleve';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['numEleve', 'nom', 'prenom', 'numEcole'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function get_eleve_with_ecole($numEleve) {
        $builder = $this->db->table('ELEVE'); // Commencez avec la table ELEVE
        $builder->select('ELEVE.*, ECOLE.Design');
        $builder->join('ECOLE', 'ELEVE.numEcole = ECOLE.numEcole');
        $builder->where('ELEVE.numEleve', $numEleve);
        return $builder->get()->getRowArray(); // Utilisez getRowArray() pour un seul résultat
    }


    public function get_notes_by_eleve($numEleve, $anneeScolaire) {
        $builder = $this->db->table('NOTE n');
        $builder->select('n.*, m.designMat, m.coef');
        $builder->join('MATIERE m', 'n.numMat = m.numMat', 'left'); // 'left' join pour garder les notes même si matière supprimée
        $builder->where('n.numEleve', $numEleve);
        $builder->where('n.anneeScolaire', $anneeScolaire);
        
        $query = $builder->get();
        
        if (!$query) {
            log_message('error', 'Erreur dans la requête get_notes_by_eleve');
            return [];
        }
        
        return $query->getResultArray();
    }

    public function getElevesPaginated($perPage, $currentPage)
    {
        return $this->paginate($perPage, 'eleves', $currentPage);
    }

    public function calculerMoyenne(string $numEleve, string $anneeScolaire): ?float
    {
        // Requête pour récupérer notes + coefficients
        $builder = $this->db->table('NOTE');
        $builder->select('NOTE.note, MATIERE.coef');
        $builder->join('MATIERE', 'NOTE.numMat = MATIERE.numMat');
        $builder->where('NOTE.numEleve', $numEleve);
        $builder->where('NOTE.anneeScolaire', $anneeScolaire);
        $notes = $builder->get()->getResult();

        if (empty($notes)) {
            return null;
        }

        // Calcul de la moyenne pondérée
        $totalPoints = 0;
        $totalCoef = 0;

        foreach ($notes as $note) {
            $totalPoints += $note->note * $note->coef;
            $totalCoef += $note->coef;
        }

        return ($totalCoef > 0) ? $totalPoints / $totalCoef : 0;
    }

    // Dans votre modèle (par exemple EleveModel.php)
    public function getResults($perPage = null, $offset = null)
    {
        $builder = $this->db->table('ECOLE');
        $builder->select('ELEVE.numEleve AS numero, ELEVE.nom, ELEVE.prenom, ECOLE.design AS ecole, NOTE.anneeScolaire, SUM(NOTE.note * MATIERE.coef)/SUM(MATIERE.coef) AS moyenne');
        $builder->join('ELEVE', 'ELEVE.numEcole = ECOLE.numEcole');
        $builder->join('NOTE', 'NOTE.numEleve = ELEVE.numEleve');
        $builder->join('MATIERE', 'MATIERE.numMat = NOTE.numMat');
        $builder->groupBy('numero, ELEVE.nom, ELEVE.prenom, ecole, NOTE.anneeScolaire');
        $builder->orderBy('moyenne', 'DESC');

        // Ajout de la pagination si les paramètres sont fournis
        if ($perPage !== null && $offset !== null) {
            $builder->limit($perPage, $offset);
        }

        return $builder->get();
    }

    public function countResults()
    {
        // Création d'une sous-requête pour compter correctement les résultats groupés
        $subQuery = $this->db->table('ECOLE')
            ->select('ELEVE.numEleve')
            ->join('ELEVE', 'ELEVE.numEcole = ECOLE.numEcole')
            ->join('NOTE', 'NOTE.numEleve = ELEVE.numEleve')
            ->join('MATIERE', 'MATIERE.numMat = NOTE.numMat')
            ->groupBy('ELEVE.numEleve, ELEVE.nom, ELEVE.prenom, ECOLE.design, NOTE.anneeScolaire')
            ->getCompiledSelect();

        // Comptage des résultats de la sous-requête
        return $this->db->query("SELECT COUNT(*) as total FROM ($subQuery) as subquery")->getRow()->total;
    }
}
