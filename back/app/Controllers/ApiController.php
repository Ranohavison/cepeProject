<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\EcoleModel;
use App\Models\EleveModel;
use App\Models\MatiereModel;
use App\Models\NoteModel;
use Exception;
use Kint\Kint;
use TCPDF;

class ApiController extends ResourceController
{
    public function getEcoles()
    {
        // Récupération des données POST
        $json = $this->request->getJSON();

        $ecoleModel = new EcoleModel();

        $page = $json->page ?? 1;
        $perPage = $json->per_page ?? 10;
        $search = $json->search ?? '';

        try {
            // Appliquer le filtre de recherche si nécessaire
            if (!empty($search)) {
                $ecoleModel->groupStart()
                    ->like('numEcole', $search)
                    ->orLike('design', $search)
                    ->orLike('adresse', $search)
                    ->groupEnd();
            }

            $ecoles = $ecoleModel->getEcolePaginated($perPage, $page);

            foreach ($ecoles as $key => $ecole) {
                $ecoles[$key]['nbrEleves'] = $ecoleModel->countElevesInscrits($ecole['numEcole']);
            }

            return $this->respond([
                'success' => true,
                'columns' => ['numEcole', 'design', 'adresse'],
                'data' => $ecoles,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $ecoleModel->countAllResults(),
                    'last_page' => $ecoleModel->pager->getPageCount()
                ]
            ]);
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            log_message('error', 'Erreur dans getEcoles: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createEcole()
    {
        $ecoleModel = new EcoleModel();
        $json = $this->request->getJSON();

        if (!$json) {
            return $this->respond(['message' => 'Aucune donnée reçue'], 400);
        }

        // Valider les données reçues
        $data = [
            'numEcole'  => $json->numEcole ?? null,
            'design'    => $json->design ?? null,
            'adresse'   => $json->adresse ?? null,
        ];

        // Vérifier que toutes les données requises sont présentes
        if (empty($data['numEcole']) || empty($data['design']) || empty($data['adresse'])) {
            return $this->respond(['message' => 'Données manquantes'], 400);
        }

        // Log des données reçues
        // log_message('debug', 'Données reçues : ' . print_r($data, true));

        // Insérer les données dans la base de données
        try {
            if ($ecoleModel->insert($data)) {
                return $this->respond(['message' => 'École créée avec succès', 'data' => $data], 201);
            } else {
                // Log des erreurs d'insertion
                log_message('error', 'Erreur lors de l\'insertion des données : ' . print_r($ecoleModel->errors(), true));
                return $this->respond(['message' => 'Erreur lors de la création de l\'école', 'errors' => $ecoleModel->errors()], 500);
            }
        } catch (\Exception $e) {
            // Log des exceptions
            log_message('error', 'Exception : ' . $e->getMessage());
            return $this->respond(['message' => 'Erreur serveur : ' . $e->getMessage()], 500);
        }
    }

    public function deleteEcole($numEcole = null)
    {
        $ecoleModel = new EcoleModel();

        if (!$numEcole) {
            return $this->respond([
                'status' => false,
                'message' => 'numEcole manquant'
            ], 400);
        }

        $result = $ecoleModel->delete($numEcole);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'École supprimée avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la suppression'
            ], 500);
        }
    }

    public function updateEcole($numEcole = null)
    {
        $ecoleModel = new EcoleModel();
        $json = $this->request->getJSON();

        if (!$numEcole) {
            return $this->respond([
                'status' => false,
                'message' => 'numEcole manquant'
            ], 400);
        }

        if (!$json) {
            return $this->respond(['message' => 'Aucune donnée reçue'], 400);
        }

        // Valider les données reçues
        $data = [
            'numEcole'  => $numEcole,
            'design'    => $json->design ?? null,
            'adresse'   => $json->adresse ?? null,
        ];

        // Vérifier que toutes les données requises sont présentes
        if (empty($data['design']) || empty($data['adresse'])) {
            return $this->respond(['message' => 'Données manquantes', 'data' => $data], 400);
        }

        $result = $ecoleModel->update($numEcole, $data);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'École modifier avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la modification'
            ], 500);
        }
    }

    public function optionsTest()
    {
        return $this->response
            ->setStatusCode(200)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->setHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function getEleves()
    {
        $json = $this->request->getJSON();

        $eleveModel = new EleveModel();

        $page = $json->page ?? 1;
        $perPage = $json->per_page ?? 10;
        $search = $json->search ?? '';

        try {
            // Appliquer le filtre de recherche si nécessaire
            if (!empty($search)) {
                $eleveModel->groupStart()
                    ->like('numEleve', $search)
                    ->orLike('nom', $search)
                    ->orLike('prenom', $search)
                    ->orLike('numEcole', $search)
                    ->groupEnd();
            }

            $eleves = $eleveModel->getElevesPaginated($perPage, $page);

            foreach ($eleves as $key => $eleve) {
                $eleves[$key]['argNote'] = $eleveModel->calculerMoyenne($eleve['numEleve'], '2022-2023');
            }

            return $this->respond([
                'success' => true,
                'columns' => ['numEleve', 'nom', 'prenom', 'numEcole'],
                'data' => $eleves,
                'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $eleveModel->countAllResults(), 'last_page' => $eleveModel->pager->getPageCount(),]
            ]);
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            log_message('error', 'Erreur dans getEcoles: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createEleve()
    {
        // Récupérer les données JSON de la requête
        $json = $this->request->getJSON();

        // Valider les données
        if (empty($json->numEleve) || empty($json->nom) || empty($json->prenom) || empty($json->numEcole)) {
            return $this->failValidationErrors('Tous les champs sont obligatoires.');
        }

        // Enregistrer les données dans la base de données
        $eleveModel = new EleveModel();
        $data = [
            'numEleve' => $json->numEleve,
            'nom' => $json->nom,
            'prenom' => $json->prenom,
            'numEcole' => $json->numEcole,
        ];

        try {
            if ($eleveModel->insert($data)) {
                return $this->respondCreated(['message' => 'Élève enregistré avec succès.']);
            } else {
                return $this->failServerError('Erreur lors de l\'enregistrement.');
            }
        } catch (\Exception $e) {
            // Log de l'exception
            log_message('error', 'Erreur lors de l\'insertion : ' . $e->getMessage());
            return $this->failServerError('Une erreur s\'est produite lors de l\'enregistrement.');
        }
    }

    public function deleteEleve($numEleve = null)
    {
        $eleveModel = new EleveModel();

        if (!$numEleve) {
            return $this->respond([
                'status' => false,
                'message' => 'numEcole manquant'
            ], 400);
        }

        $result = $eleveModel->delete($numEleve);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'Élève supprimée avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la suppression'
            ], 500);
        }
    }

    public function updateEleve($numEleve = null)
    {
        $eleveModel = new EleveModel();
        $json = $this->request->getJSON();

        if (!$numEleve) {
            return $this->respond([
                'status' => false,
                'message' => 'num $numEleve manquant'
            ], 400);
        }

        if (!$json) {
            return $this->respond(['message' => 'Aucune donnée reçue'], 400);
        }

        // Valider les données reçues
        $data = [
            'numEleve'  => $numEleve,
            'nom'    => $json->nom ?? null,
            'prenom'   => $json->prenom ?? null,
            'numEcole' => $json->numEcole ?? null,
        ];

        // Vérifier que toutes les données requises sont présentes
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['numEcole'])) {
            return $this->respond(['message' => 'Données manquantes', 'data' => $data], 400);
        }

        $result = $eleveModel->update($numEleve, $data);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'École modifier avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la modification'
            ], 500);
        }
    }

    public function getMatieres()
    {
        $json = $this->request->getJSON();

        $matiereModel = new MatiereModel();

        $page = $json->page ?? 1;
        $perPage = $json->per_page ?? 10;
        $search = $json->search ?? '';

        try {
            // Appliquer le filtre de recherche si nécessaire
            if (!empty($search)) {
                $matiereModel->groupStart()
                    ->like('numMat', $search)
                    ->orLike('designMat', $search)
                    ->orLike('coef', $search)
                    ->groupEnd();
            }

            $matieres = $matiereModel->getMatierePaginated($perPage, $page);

            foreach ($matieres as $key => $matiere) {
                $matieres[$key]['nbrNote'] = $matiereModel->countNotes($matiere['numMat']);
            }

            return $this->respond([
                'success' => true,
                'columns' => ['numMat', 'designMat', 'coef'],
                'data' => $matieres,
                'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $matiereModel->countAllResults(), 'last_page' => $matiereModel->pager->getPageCount(),]
            ]);
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            log_message('error', 'Erreur dans getEcoles: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createMatiere()
    {
        $json = $this->request->getJSON();

        if (empty($json->numMat) || empty($json->design) || empty($json->coef)) {
            return $this->failValidationErrors('Tous les champs sont obligatoires.');
        }

        $matiereModel = new MatiereModel();
        $data = [
            'numMat' => $json->numMat,
            'designMat' => $json->design,
            'coef' => $json->coef,
        ];

        try {
            if ($matiereModel->insert($data)) {
                return $this->respondCreated(['message' => 'Élève enregistré avec succès.']);
            } else {
                return $this->failServerError('Erreur lors de l\'enregistrement.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de l\'insertion : ' . $e->getMessage());
            return $this->failServerError('Une erreur s\'est produite lors de l\'enregistrement.');
        }
    }

    public function deleteMatiere($numMat = null)
    {
        $matiereModel = new MatiereModel();

        if (!$numMat) {
            return $this->respond([
                'status' => false,
                'message' => 'numEcole manquant'
            ], 400);
        }

        $result = $matiereModel->delete($numMat);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'Élève supprimée avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la suppression'
            ], 500);
        }
    }

    public function updateMatiere($numMat = null)
    {
        $matiereModel = new MatiereModel();
        $json = $this->request->getJSON();

        if (!$numMat) {
            return $this->respond([
                'status' => false,
                'message' => 'num $numEleve manquant'
            ], 400);
        }

        if (!$json) {
            return $this->respond(['message' => 'Aucune donnée reçue'], 400);
        }

        if (empty($json->numMat) || empty($json->design) || empty($json->coef)) {
            return $this->failValidationErrors('Tous les champs sont obligatoires.');
        }

        $data = [
            'numMat' => $json->numMat,
            'designMat' => $json->design,
            'coef' => $json->coef,
        ];

        $result = $matiereModel->update($numMat, $data);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'École modifier avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la modification'
            ], 500);
        }
    }

    public function getNotes()
    {
        $json = $this->request->getJSON();

        $noteModel = new NoteModel();

        $page = $json->page ?? 1;
        $perPage = $json->per_page ?? 10;
        $search = $json->search ?? '';

        try {
            // Appliquer le filtre de recherche si nécessaire
            if (!empty($search)) {
                $noteModel->groupStart()
                    ->Like('numEleve', $search)
                    ->orLike('numMat', $search)
                    ->orLike('note', $search)
                    ->groupEnd();
            }

            $notes = $noteModel->getNotePaginated($perPage, $page);

            foreach ($notes as $key => $note) {
                $notes[$key]['nbrNote'] = $noteModel->countNotes($note['numEleve']);
            }

            return $this->respond([
                'success' => true,
                'columns' => ['anneeScolaire', 'numEleve', 'numMat', 'note'],
                'data' => $notes,
                'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $noteModel->countAllResults(), 'last_page' => $noteModel->pager->getPageCount(),]
            ]);
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            log_message('error', 'Erreur dans getEcoles: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createNote()
    {
        $json = $this->request->getJSON();

        if (empty($json->numEleve) || empty($json->numMat) || empty($json->note)) {
            return $this->failValidationErrors('Tous les champs sont obligatoires.');
        }

        $noteModel = new NoteModel();

        $data = [
            'anneeScolaire' => '2022-2023',
            'numEleve' => $json->numEleve,
            'numMat' => $json->numMat,
            'note' => $json->note,
        ];

        try {
            if ($noteModel->insert_note($data)) {
                return $this->respondCreated(['message' => 'Note enregistré avec succès.']);
            } else {
                return $this->failServerError('Erreur lors de l\'enregistrement.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de l\'insertion : ' . $e->getMessage());
            return $this->failServerError('Une erreur s\'est produite lors de l\'enregistrement. ');
        }
    }

    public function updateNote($numEleve = null)
    {
        $noteModel = new NoteModel();
        $json = $this->request->getJSON();

        if (!$numEleve) {
            return $this->respond([
                'status' => false,
                'message' => 'Cle secondaire manquant'
            ], 400);
        }


        if (!$json) {
            return $this->respond(['message' => 'Aucune donnée reçue'], 400);
        }

        if (empty($json->numEleve) || empty($json->numMat) || empty($json->note)) {
            return $this->failValidationErrors(['message' => 'Tous les champs sont obligatoires.', 'data' => $json]);
        }

        $data = [
            'anneeScolaire' => '2022-2023',
            'numEleve' => $json->numEleve,
            'numMat' => $json->numMat,
            'note' => $json->note,
        ];

        try {
            if ($noteModel->update_note($data)) {
                return $this->respondCreated(['message' => 'Note modifié avec succès.']);
            } else {
                return $this->failServerError('Erreur lors de la modificatioin.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur lors de la modification : ' . $e->getMessage());
            return $this->failServerError('Une erreur s\'est produite lors de la modification. ');
        }
    }

    public function deleteNote($numEleve = null, $numMat = null)
    {
        $noteModel = new NoteModel();

        if (!$numMat || !$numEleve) {
            return $this->respond([
                'status' => false,
                'message' => 'numEcole manquant'
            ], 400);
        }

        $result = $noteModel->delete_note($numEleve, $numMat);

        if ($result) {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => true,
                'message' => 'Élève supprimée avec succès'
            ], 200); // Changé de 400 à 200 (OK)
        } else {
            return $this->respond([ // Notez le 'return' ajouté ici
                'status' => false,
                'message' => 'Échec de la suppression'
            ], 500);
        }
    }

    public function generateMention($note = null)
    {
        if ($note > 12) {
            return "Admis en 6ème";
        } elseif ($note > 10) {
            return "Admis";
        } elseif ($note > 9.75) {
            return "Délibération";
        } else {
            return "Recalé";
        }
    }

    // Dans votre contrôleur
    public function getResults()
    {
        $json = $this->request->getJSON();

        $eleveModel = new EleveModel();

        $page = $json->page ?? 1;
        $perPage = $json->per_page ?? 10;
        $search = $json->search ?? '';
        $offset = ($page - 1) * $perPage;

        try {
            $query = $eleveModel->getResults($perPage, $offset);
            $eleves = $query->getResultArray();

            foreach ($eleves as $key => $eleve) {
                $eleves[$key]['mention'] = $this->generateMention($eleve['moyenne']);
            }

            $columns = ['numero', 'nom', 'prenom', 'ecole', 'moyenne', 'mention'];
            $numberPerMention = ['sixieme' => 0, 'admis' => 0, 'deliberation' => 0, 'recale' => 0];
            foreach ($eleves as $key => $eleve) {
                switch ($eleves[$key]['mention']){
                    case 'Admis en 6ème': $numberPerMention['sixieme'] += 1; break;
                    case 'Admis': $numberPerMention['admis'] += 1; break;
                    case 'Délibération': $numberPerMention['deliberation'] += 1; break;
                    case 'Recalé': $numberPerMention['recale'] += 1; break;
                }
            }

            if (!empty($search)) {
                $eleves = array_filter($eleves, function($row) use ($columns, $search) {
                foreach ($columns as $column) {
                    if (isset($row[$column]) && stripos($row[$column], $search) !== false) {
                        return true;
                    }
                }
                return false;
            });
            }

            foreach ($eleves as $key => $eleve) {
                $eleves[$key]['mention'] = $this->generateMention($eleve['moyenne']);
            }

            $eleves = array_values($eleves);

            $total = $eleveModel->countResults();
            $lastPage = ceil($total / $perPage);

            return $this->respond([
                'success' => true,
                'columns' => $columns,
                'data' => $eleves,
                'numberPerMention' => $numberPerMention,
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $lastPage,
                ]
            ]);
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            log_message('error', 'Erreur dans getEcoles: ' . $e->getMessage());

            return $this->respond([
                'success' => false,
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Controller
    public function generatePdf($numEleve) {
        // Récupérer les données de l'élève
        $eleveModel = new EleveModel();
        $eleve = $eleveModel->get_eleve_with_ecole($numEleve);
    
        // Vérifier si l'élève existe
        if (!$eleve) {
            return $this->failNotFound('Élève non trouvé');
        }
    
        // Convertir l'objet en tableau si nécessaire
        $eleve = is_object($eleve) ? (array)$eleve : $eleve;
    
        // Récupérer les notes
        $notes = $eleveModel->get_notes_by_eleve($numEleve, '2022-2023');
    
        // Vérifier si des notes existent
        if (empty($notes)) {
            return $this->failNotFound('Aucune note trouvée pour cet élève');
        }
    
        // Calculer les totaux et moyenne
        $totalPondere = 0;
        $totalCoefficients = 0;
        $notesTraitees = [];
    
        foreach ($notes as $note) {
            // Convertir la note en tableau si c'est un objet
            $note = is_object($note) ? (array)$note : $note;
            
            // Vérifier si la matière a déjà été traitée (éviter les redondances)
            $matiereId = $note['id_matiere'] ?? null;
            if ($matiereId && isset($notesTraitees[$matiereId])) {
                continue; // Passer à la note suivante si la matière a déjà été traitée
            }
            
            $notesTraitees[$matiereId] = true;
            $totalPondere += $note['note'] * $note['coef'];
            $totalCoefficients += $note['coef'];
        }
    
        $moyenne = $totalCoefficients > 0 ? $totalPondere / $totalCoefficients : 0;
    
        // Créer le PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('CEPE Application');
        $pdf->SetTitle('Relevé de notes - ' . $eleve['nom'] . ' ' . $eleve['prenom']);
        $pdf->SetSubject('Relevé de notes CEPE');
    
        $pdf->AddPage();
    
        // Contenu du PDF
        $html = '
            <style>
            h1 { font-size: 18pt; }
            table { width: 100%; border-collapse: collapse; }
            th { font-weight: bold; }
            .total-row { font-weight: bold; background-color: #f2f2f2; }
            </style>
            <h1 style="text-align:center;">Relevé de notes</h1>
            <p style="text-align:center;">Année scolaire : 2022-2023</p>
            <br>
            <p><strong>Nom :</strong> ' . htmlspecialchars($eleve['nom'] ?? '') . '</p>
            <p><strong>Prénoms :</strong> ' . htmlspecialchars($eleve['prenom'] ?? '') . '</p>
            <p><strong>Date de naissance :</strong> ' . htmlspecialchars($eleve['date_naissance'] ?? 'non enregistré') . '</p>
            <p><strong>Ecole :</strong> ' . htmlspecialchars($eleve['Design'] ?? '') . '</p>
            <br>
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th width="25%">Matière</th>
                        <th width="25%">Coefficient</th>
                        <th width="25%">Note</th>
                        <th width="25%">Note pondérée</th>
                    </tr>
                </thead>
                <tbody>';
    
        $notesTraitees = []; // Réinitialiser pour l'affichage
        foreach ($notes as $note) {
            $note = is_object($note) ? (array)$note : $note;
            $matiereId = $note['id_matiere'] ?? null;
            
            // Éviter les doublons dans l'affichage
            if ($matiereId && isset($notesTraitees[$matiereId])) {
                continue;
            }
            $notesTraitees[$matiereId] = true;
    
            $html .= '
            <tr>
                <td>' . htmlspecialchars($note['designMat'] ?? '') . '</td>
                <td>' . htmlspecialchars($note['coef'] ?? '') . '</td>
                <td>' . htmlspecialchars($note['note'] ?? '') . '</td>
                <td>' . htmlspecialchars(($note['note'] ?? 0) * ($note['coef'] ?? 0)) . '</td>
            </tr>';
        }
    
        $html .= '
            <tr class="total-row">
                <td colspan="3" align="right"><strong>Total</strong></td>
                <td>' . htmlspecialchars($totalPondere) . '</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" align="right"><strong>Moyenne</strong></td>
                <td>' . htmlspecialchars(number_format($moyenne, 2)) . '</td>
            </tr>
        </tbody>
        </table>
        ';
    
        $pdf->writeHTML($html, true, false, true, false, '');
    
        // Générer et envoyer le PDF
        // $pdf->Output('releve_notes_' . ($eleve['nom'] ?? '') . '_' . ($eleve['prenom'] ?? '') . '.pdf', 'I');
        $pdf->Output();
    }
}
