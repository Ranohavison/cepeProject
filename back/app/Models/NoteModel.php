<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table            = 'NOTE';
    protected $primaryKey       = false;
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [ 'anneesScolaire', 'numEleve', 'numMat', 'note'];

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

    public function paire_exist($numEleve, $numMat) {
        return $this->where('numEleve', $numEleve)->where('numMat', $numMat)->countAllResults() > 0;
    }

    public function insert_note($data) {
        if (!$this->paire_exist($data['numEleve'], $data['numMat'])) {
            $sql = "INSERT INTO NOTE VALUES (?, ?, ?, ?)";
            return $this->db->query($sql, [$data['anneeScolaire'], $data['numEleve'], $data['numMat'], $data['note']]);
        } else {
            return false;
        }
    }

    public function update_note($data) {
        if ($this->paire_exist($data['numEleve'], $data['numMat'])) {
            $sql = "UPDATE NOTE SET note = ? WHERE numEleve = ? AND numMat = ?";
            return $this->db->query($sql, [ $data['note'], $data['numEleve'], $data['numMat']]);
            return ($this->db->affected_rows() > 0);
        } else {
            return false;
        }
    }

    public function delete_note($numEleve, $numMat) {
        if ($this->paire_exist($numEleve, $numMat)) {
            $sql = "DELETE FROM NOTE WHERE numEleve = ? AND numMat = ?";
            return $this->db->query($sql, [ $numEleve, $numMat]);
            return ($this->db->affected_rows() > 0);
        } else {
            return false;
        }
    }

    public function getNotePaginated($perPage, $currentPage) {
        return $this->paginate($perPage, 'notes', $currentPage);
    }

    public function countNotes($numEleve)
    {
        return $this->db->table('NOTE')
                        ->where('numEleve', $numEleve)
                        ->countAllResults();
    }
}
