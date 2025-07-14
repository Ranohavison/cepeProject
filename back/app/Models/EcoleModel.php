<?php
    namespace App\Models;

    use CodeIgniter\Model;

    class EcoleModel extends Model {
        protected $table = 'ECOLE';
        protected $useAutoIncrement = false;
        protected $primaryKey = 'numEcole';
        protected $useSoftDeletes   = false;
        protected $protectFields    = true;
        protected $allowedFields = [ 'numEcole', 'design', 'adresse'];

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

        public function getEcolePaginated($perPage, $currentPage) {
            return $this->paginate($perPage, 'ecoles', $currentPage);
        }
        
        public function create($data) {
            return $this->insert($data);
        }

        public function countElevesInscrits($numEcole)
        {
            // Utilisation du Query Builder pour compter les élèves
            return $this->db->table('ELEVE')
                            ->where('numEcole', $numEcole)
                            ->countAllResults();
        }
    }
?>