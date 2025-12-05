<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Récupération des informations de l'utilisateur
$query = "SELECT u.*, r1.NomRegion as RegionNaissance, d1.NomDepartement as DepartementNaissance, 
          v1.NomVille as VilleNaissance, r2.NomRegion as RegionResidence, d2.NomDepartement as DepartementResidence, 
          v2.NomVille as VilleResidence, e.NomEthnie
          FROM utilisateurs u
          LEFT JOIN regions r1 ON u.RegionNaissanceID = r1.RegionID
          LEFT JOIN departements d1 ON u.DepartementNaissanceID = d1.DepartementID
          LEFT JOIN villes v1 ON u.VilleNaissanceID = v1.VilleID
          LEFT JOIN regions r2 ON u.RegionResidenceID = r2.RegionID
          LEFT JOIN departements d2 ON u.DepartementResidenceID = d2.DepartementID
          LEFT JOIN villes v2 ON u.VilleResidenceID = v2.VilleID
          LEFT JOIN ethnies e ON u.EthnieID = e.EthnieID
          WHERE u.UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Vérification si l'utilisateur a déjà une CNI
$query = "SELECT * FROM demandes 
          WHERE UtilisateurID = :userId 
          AND TypeDemande = 'CNI' 
          AND (Statut = 'Approuvee' OR Statut = 'Terminee')
          ORDER BY DateSoumission DESC 
          LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$existingCNI = $stmt->fetch();

// Vérification si l'utilisateur a une demande en cours
$query = "SELECT * FROM demandes 
          WHERE UtilisateurID = :userId 
          AND TypeDemande = 'CNI' 
          AND (Statut = 'Soumise' OR Statut = 'EnCours')";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$pendingRequest = $stmt->fetch();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Validation des données
        $typeDemande = $_POST['type_demande'];
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
        $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
        $dateNaissance = $_POST['date_naissance'];
        $lieuNaissance = $_POST['lieu_naissance'];
        $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
        $sexe = $_POST['sexe'];
        $taille = filter_var($_POST['taille'], FILTER_VALIDATE_INT);
        $profession = filter_var($_POST['profession'], FILTER_SANITIZE_STRING);
        $statutCivil = $_POST['statut_civil'];
        $numeroCNI = isset($_POST['numero_cni']) ? $_POST['numero_cni'] : null;
        
        // Validation de la taille
        if ($taille === false || $taille < 100 || $taille > 250) {
            throw new Exception("La taille doit être un nombre entier entre 100 et 250 cm");
        }

        // Vérification pour nouvelle demande
        if ($typeDemande == 'premiere' && $existingCNI) {
            throw new Exception("Vous possédez déjà une CNI. Veuillez choisir l'option de renouvellement ou de perte.");
        }

        // Vérification pour renouvellement/perte
        if (($typeDemande == 'renouvellement' || $typeDemande == 'perte') && !$existingCNI) {
            throw new Exception("Aucune CNI existante trouvée. Veuillez choisir l'option de première demande.");
        }

        // Vérification de l'expiration pour renouvellement
        if ($typeDemande == 'renouvellement' && $existingCNI) {
            $dateEmission = new DateTime($existingCNI['DateSoumission']);
            $dateExpiration = clone $dateEmission;
            $dateExpiration->add(new DateInterval('P15Y')); // 15 ans de validité
            $today = new DateTime();
            
            if ($dateExpiration > $today) {
                throw new Exception("Votre CNI actuelle est encore valide jusqu'au " . $dateExpiration->format('d/m/Y') . ". Le renouvellement n'est pas nécessaire.");
            }
        }

        // Vérification de demande en cours
        if ($pendingRequest) {
            throw new Exception("Vous avez déjà une demande de CNI en cours. Veuillez attendre que celle-ci soit traitée.");
        }

        // Génération d'un numéro de référence unique
        $reference = 'CNI-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Insertion dans la table demandes
        $query = "INSERT INTO demandes (UtilisateurID, TypeDemande, SousTypeDemande, Statut, NumeroReference, DateSoumission, MontantPaiement, StatutPaiement) 
                  VALUES (:userId, 'CNI', :sousType, 'Soumise', :reference, NOW(), 10000, 'En attente')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'userId' => $userId,
            'sousType' => $typeDemande,
            'reference' => $reference
        ]);
        
        $demandeId = $db->lastInsertId();

        // Insertion dans demande_cni_details
        $query = "INSERT INTO demande_cni_details (DemandeID, TypeDemande, Nom, Prenom, DateNaissance, 
                  LieuNaissance, Adresse, Sexe, Taille, Profession, StatutCivil, NumeroCNIPrecedente) 
                  VALUES (:demandeId, :typeDemande, :nom, :prenom, :dateNaissance, :lieuNaissance, 
                  :adresse, :sexe, :taille, :profession, :statutCivil, :numeroCNI)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'typeDemande' => $typeDemande,
            'nom' => $nom,
            'prenom' => $prenom,
            'dateNaissance' => $dateNaissance,
            'lieuNaissance' => $lieuNaissance,
            'adresse' => $adresse,
            'sexe' => $sexe,
            'taille' => $taille,
            'profession' => $profession,
            'statutCivil' => $statutCivil,
            'numeroCNI' => $numeroCNI
        ]);

        // Configuration des documents requis selon le type de demande
        $documents = [
            'premiere' => [
                'photo_identite' => ['type' => 'PhotoIdentite', 'required' => true],
                'acte_naissance' => ['type' => 'ActeNaissance', 'required' => true],
                'certificat_nationalite' => ['type' => 'CertificatNationalite', 'required' => true],
                'justificatif_profession' => ['type' => 'JustificatifProfession', 'required' => true]
            ],
            'renouvellement' => [
                'photo_identite' => ['type' => 'PhotoIdentite', 'required' => true],
                'ancienne_cni' => ['type' => 'AncienneCNI', 'required' => true],
                'justificatif_profession' => ['type' => 'JustificatifProfession', 'required' => true]
            ],
            'perte' => [
                'photo_identite' => ['type' => 'PhotoIdentite', 'required' => true],
                'declaration_perte' => ['type' => 'DeclarationPerte', 'required' => true],
                'certificat_nationalite' => ['type' => 'CertificatNationalite', 'required' => true]
            ]
        ];

        // Ajout du document d'acte de mariage pour les femmes mariées
        if ($sexe == 'F' && $statutCivil == 'Marie') {
            $documents[$typeDemande]['acte_mariage'] = ['type' => 'ActeMariage', 'required' => true];
        }

        $currentDocs = $documents[$typeDemande];
        $uploadDir = '../uploads/documents/';
        
        // Création du répertoire si inexistant
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Traitement des documents
        foreach ($currentDocs as $input_name => $doc_info) {
            // Traitement spécial pour la photo d'identité (base64)
            if ($input_name == 'photo_identite' && isset($_POST['photo_data'])) {
                $photoData = $_POST['photo_data'];
                $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
                $photoData = str_replace(' ', '+', $photoData);
                $photoData = base64_decode($photoData);
                
                if ($photoData) {
                    $photoFilename = $uploadDir . uniqid() . '_photo_' . $userId . '.jpg';
                    file_put_contents($photoFilename, $photoData);
                    
                    $query = "INSERT INTO documents (DemandeID, TypeDocument, CheminFichier, UtilisateurID) 
                             VALUES (:demandeId, :type, :chemin, :userId)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        'demandeId' => $demandeId,
                        'type' => $doc_info['type'],
                        'chemin' => $photoFilename,
                        'userId' => $userId
                    ]);
                } elseif ($doc_info['required']) {
                    throw new Exception("La photo d'identité est requise");
                }
            } 
            // Traitement des autres documents
            elseif ($input_name != 'photo_identite' && isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
                $filename = $_FILES[$input_name]['name'];
                $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $newname = $uploadDir . uniqid() . '_' . $filename;
                
                // Vérification du type de fichier
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array($filetype, $allowed)) {
                    throw new Exception("Le type de fichier n'est pas autorisé pour " . $input_name);
                }

                // Vérification de la taille (5MB max)
                if ($_FILES[$input_name]['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Le fichier " . $input_name . " est trop volumineux");
                }

                if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $newname)) {
                    // Vérification si le document existe déjà pour cet utilisateur
                    $query = "SELECT d.* FROM documents d
                              JOIN demandes dm ON d.DemandeID = dm.DemandeID
                              WHERE d.UtilisateurID = :userId AND d.TypeDocument = :type
                              AND dm.Statut IN ('Approuvee', 'Terminee')
                              ORDER BY d.DateTelechargement DESC LIMIT 1";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        'userId' => $userId,
                        'type' => $doc_info['type']
                    ]);
                    $existingDoc = $stmt->fetch();
                    
                    // Si le document existe et est valide, on l'utilise
                    if ($existingDoc && file_exists($existingDoc['CheminFichier'])) {
                        $query = "INSERT INTO documents (DemandeID, TypeDocument, CheminFichier, UtilisateurID) 
                                 VALUES (:demandeId, :type, :chemin, :userId)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            'demandeId' => $demandeId,
                            'type' => $doc_info['type'],
                            'chemin' => $existingDoc['CheminFichier'],
                            'userId' => $userId
                        ]);
                    } else {
                        $query = "INSERT INTO documents (DemandeID, TypeDocument, CheminFichier, UtilisateurID) 
                                 VALUES (:demandeId, :type, :chemin, :userId)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            'demandeId' => $demandeId,
                            'type' => $doc_info['type'],
                            'chemin' => $newname,
                            'userId' => $userId
                        ]);
                    }
                } else {
                    throw new Exception("Erreur lors du téléchargement du fichier " . $input_name);
                }
            } elseif ($doc_info['required']) {
                throw new Exception("Le document " . $input_name . " est requis");
            }
        }

        $db->commit();
        
        // Redirection vers la page de paiement
        $_SESSION['demande_id'] = $demandeId;
        $_SESSION['montant'] = 10000;
        $_SESSION['reference'] = $reference;
        header('Location: paiement.php');
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h3 class="card-title mb-0">Demande de Carte Nationale d'Identité</h3>
                        <p class="text-muted mb-0">Remplissez le formulaire ci-dessous pour soumettre votre demande</p>
                    </div>
                    
                    <?php if(isset($error)): ?>
                    <div class="alert alert-danger mx-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($pendingRequest): ?>
                    <div class="alert alert-warning mx-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Vous avez déjà une demande de CNI en cours (Référence: <?php echo $pendingRequest['NumeroReference']; ?>).
                        <a href="details_demande.php?id=<?php echo $pendingRequest['DemandeID']; ?>" class="alert-link">
                            Consulter ma demande
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <form id="cniForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <!-- Barre de progression -->
                            <div class="mb-4">
                                <div class="progress-container">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%;" id="formProgress"></div>
                                    </div>
                                    <div class="progress-steps">
                                        <div class="progress-step active" data-step="1">
                                            <div class="step-number">1</div>
                                            <div class="step-label">Type de demande</div>
                                        </div>
                                        <div class="progress-step" data-step="2">
                                            <div class="step-number">2</div>
                                            <div class="step-label">Informations personnelles</div>
                                        </div>
                                        <div class="progress-step" data-step="3">
                                            <div class="step-number">3</div>
                                            <div class="step-label">Documents</div>
                                        </div>
                                        <div class="progress-step" data-step="4">
                                            <div class="step-number">4</div>
                                            <div class="step-label">Vérification</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Étape 1: Type de demande -->
                            <div class="form-step" id="step1">
                                <h4 class="mb-4">Type de demande</h4>
                                
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="card h-100 type-card" data-type="premiere">
                                            <div class="card-body text-center p-4">
                                                <div class="type-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                                    <i class="bi bi-person-plus"></i>
                                                </div>
                                                <h5>Première demande</h5>
                                                <p class="text-muted small mb-0">Pour une première obtention de CNI</p>
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type_demande" id="type_premiere" value="premiere" required <?php echo (!$existingCNI) ? 'checked' : ''; ?> <?php echo ($existingCNI) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="type_premiere">
                                                        Sélectionner
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 type-card" data-type="renouvellement">
                                            <div class="card-body text-center p-4">
                                                <div class="type-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </div>
                                                <h5>Renouvellement</h5>
                                                <p class="text-muted small mb-0">Pour renouveler une CNI expirée</p>
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type_demande" id="type_renouvellement" value="renouvellement" required <?php echo ($existingCNI) ? 'checked' : ''; ?> <?php echo (!$existingCNI) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="type_renouvellement">
                                                        Sélectionner
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 type-card" data-type="perte">
                                            <div class="card-body text-center p-4">
                                                <div class="type-icon bg-danger bg-opacity-10 text-danger mx-auto mb-3">
                                                    <i class="bi bi-exclamation-triangle"></i>
                                                </div>
                                                <h5>Perte/Vol</h5>
                                                <p class="text-muted small mb-0">En cas de perte ou de vol de CNI</p>
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pb-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="type_demande" id="type_perte" value="perte" required <?php echo (!$existingCNI) ? 'disabled' : ''; ?>>
                                                    <label class="form-check-label" for="type_perte">
                                                        Sélectionner
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if($existingCNI): ?>
                                <div class="alert alert-info mt-4">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Vous possédez déjà une CNI (N° <?php echo $existingCNI['NumeroReference']; ?>).
                                    <?php
                                    $dateEmission = new DateTime($existingCNI['DateSoumission']);
                                    $dateExpiration = clone $dateEmission;
                                    $dateExpiration->add(new DateInterval('P15Y')); // 15 ans de validité
                                    $today = new DateTime();
                                    
                                    if($dateExpiration > $today): ?>
                                        <strong>Votre CNI est valide jusqu'au <?php echo $dateExpiration->format('d/m/Y'); ?>.</strong>
                                    <?php else: ?>
                                        <strong>Votre CNI est expirée depuis le <?php echo $dateExpiration->format('d/m/Y'); ?>.</strong>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Étape 2: Informations personnelles -->
                            <div class="form-step d-none" id="step2">
                                <h4 class="mb-4">Informations personnelles</h4>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['Nom']); ?>" required readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required readonly>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="date_naissance" class="form-label">Date de naissance</label>
                                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo $user['DateNaissance']; ?>" required readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                                        <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" value="<?php echo htmlspecialchars($user['VilleNaissance'] . ', ' . $user['DepartementNaissance'] . ', ' . $user['RegionNaissance']); ?>" required readonly>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="sexe" class="form-label">Sexe</label>
                                        <select class="form-select" id="sexe" name="sexe" required>
                                            <option value="M" <?php echo $user['Genre'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                            <option value="F" <?php echo $user['Genre'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="statut_civil" class="form-label">Statut civil</label>
                                        <select class="form-select" id="statut_civil" name="statut_civil" required>
                                            <option value="Celibataire">Célibataire</option>
                                            <option value="Marie">Marié(e)</option>
                                            <option value="Divorce">Divorcé(e)</option>
                                            <option value="Veuf">Veuf/Veuve</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="profession" class="form-label">Profession</label>
                                        <input type="text" class="form-control" id="profession" name="profession" value="<?php echo htmlspecialchars($user['Profession']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="taille" class="form-label">Taille (cm)</label>
                                        <input type="number" class="form-control" id="taille" name="taille" min="100" max="250" required>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="adresse" class="form-label">Adresse actuelle</label>
                                        <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['Adresse']); ?>" required>
                                    </div>
                                    
                                    <div class="col-12 numero-cni-container d-none">
                                        <label for="numero_cni" class="form-label">Numéro de l'ancienne CNI</label>
                                        <input type="text" class="form-control" id="numero_cni" name="numero_cni">
                                        <div class="form-text">Renseignez le numéro de votre ancienne CNI si vous le connaissez.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Étape 3: Documents -->
                            <div class="form-step d-none" id="step3">
                                <h4 class="mb-4">Documents requis</h4>
                                
                                <div class="row g-4">
                                    <!-- Photo d'identité -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Photo d'identité</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <div id="camera-container" class="mx-auto mb-3" style="width: 320px; height: 240px; border-radius: 8px; overflow: hidden; background-color: #f8f9fa;">
                                                        <video id="camera" width="320" height="240" class="d-none"></video>
                                                        <canvas id="photo-preview" width="320" height="240" class="d-none"></canvas>
                                                        <div id="camera-placeholder" class="d-flex flex-column align-items-center justify-content-center h-100">
                                                            <i class="bi bi-camera text-muted" style="font-size: 3rem;"></i>
                                                            <p class="text-muted mt-2">Activez la caméra pour prendre une photo</p>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="photo_data" id="photo_data">
                                                    <div class="btn-group">
                                                        <button type="button" id="start-camera" class="btn btn-outline-primary">
                                                            <i class="bi bi-camera-fill me-2"></i>Activer la caméra
                                                        </button>
                                                        <button type="button" id="take-photo" class="btn btn-primary d-none">
                                                            <i class="bi bi-camera-fill me-2"></i>Prendre la photo
                                                        </button>
                                                        <button type="button" id="retake-photo" class="btn btn-outline-secondary d-none">
                                                            <i class="bi bi-arrow-repeat me-2"></i>Reprendre
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Prenez une photo de face, sur fond blanc, sans lunettes ni couvre-chef.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Documents spécifiques pour première demande -->
                                    <div class="col-md-6 doc-premiere">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Acte de naissance</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="acte_naissance" name="acte_naissance" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez une copie de votre acte de naissance (format PDF, JPG ou PNG).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 doc-premiere">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Certificat de nationalité</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="certificat_nationalite" name="certificat_nationalite" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez une copie de votre certificat de nationalité (format PDF, JPG ou PNG).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Documents spécifiques pour renouvellement -->
                                    <div class="col-md-6 doc-renouvellement d-none">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Ancienne CNI</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="ancienne_cni" name="ancienne_cni" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez une copie recto-verso de votre ancienne CNI (format PDF, JPG ou PNG).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Documents spécifiques pour perte -->
                                    <div class="col-md-6 doc-perte d-none">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Déclaration de perte</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="declaration_perte" name="declaration_perte" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez une copie de votre déclaration de perte délivrée par les autorités compétentes (format PDF, JPG ou PNG).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Document pour femme mariée -->
                                    <div class="col-md-6 doc-mariage d-none">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Acte de mariage</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="acte_mariage" name="acte_mariage" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez une copie de votre acte de mariage (format PDF, JPG ou PNG).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Justificatif de profession -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Justificatif de profession</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input class="form-control" type="file" id="justificatif_profession" name="justificatif_profession" accept=".pdf,.jpg,.jpeg,.png">
                                                </div>
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <small>Téléchargez un document justifiant votre profession actuelle (carte professionnelle, attestation d'emploi, etc.).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Étape 4: Vérification et soumission -->
                            <div class="form-step d-none" id="step4">
                                <h4 class="mb-4">Vérification et soumission</h4>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Récapitulatif de la demande</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Type de demande:</strong> <span id="recap-type"></span></p>
                                                <p><strong>Nom:</strong> <span id="recap-nom"></span></p>
                                                <p><strong>Prénom:</strong> <span id="recap-prenom"></span></p>
                                                <p><strong>Date de naissance:</strong> <span id="recap-date-naissance"></span></p>
                                                <p><strong>Lieu de naissance:</strong> <span id="recap-lieu-naissance"></span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Sexe:</strong> <span id="recap-sexe"></span></p>
                                                <p><strong>Statut civil:</strong> <span id="recap-statut-civil"></span></p>
                                                <p><strong>Profession:</strong> <span id="recap-profession"></span></p>
                                                <p><strong>Taille:</strong> <span id="recap-taille"></span> cm</p>
                                                <p><strong>Adresse:</strong> <span id="recap-adresse"></span></p>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-4 text-center">
                                                <p><strong>Photo d'identité</strong></p>
                                                <img id="recap-photo" src="" alt="Photo d'identité" class="img-thumbnail" style="max-height: 150px;">
                                            </div>
                                            <div class="col-md-8">
                                                <p><strong>Documents fournis:</strong></p>
                                                <ul id="recap-documents" class="list-group">
                                                    <!-- Liste des documents sera générée dynamiquement -->
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Frais de demande</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0"><strong>Montant à payer:</strong></p>
                                                <p class="text-muted small">Le paiement sera effectué à l'étape suivante</p>
                                            </div>
                                            <div>
                                                <h3 class="text-primary mb-0">10 000 FCFA</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Je certifie sur l'honneur l'exactitude des informations fournies et j'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">conditions générales</a> du service.
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Boutons de navigation -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display:none;">
                                    <i class="bi bi-arrow-left me-2"></i>Précédent
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn">
                                    Suivant<i class="bi bi-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">
                                    <i class="bi bi-check-circle me-2"></i>Soumettre ma demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal des conditions générales -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Conditions générales d'utilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>1. Informations générales</h6>
                <p>La demande de Carte Nationale d'Identité (CNI) est soumise aux lois et règlements en vigueur au Cameroun.</p>
                
                <h6>2. Exactitude des informations</h6>
                <p>Le demandeur certifie l'exactitude des informations fournies. Toute fausse déclaration est passible de poursuites judiciaires.</p>
                
                <h6>3. Protection des données personnelles</h6>
                <p>Les données collectées sont utilisées exclusivement dans le cadre de la demande de CNI et sont protégées conformément à la législation en vigueur.</p>
                
                <h6>4. Frais de demande</h6>
                <p>Les frais de demande s'élèvent à 10 000 FCFA et ne sont pas remboursables, quelle que soit l'issue de la demande.</p>
                
                <h6>5. Validité de la CNI</h6>
                <p>La CNI a une durée de validité de 15 ans à compter de sa date d'émission.</p>
                
                <h6>6. Retrait de la CNI</h6>
                <p>Le demandeur sera notifié par SMS et email lorsque sa CNI sera disponible pour retrait. Le retrait se fait en personne au centre indiqué.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #1774df;
    --primary-dark: #135bb2;
    --primary-light: rgba(23, 116, 223, 0.1);
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
}

.dashboard-container {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding-top: 2rem;
}

/* Barre de progression */
.progress-container {
    margin-bottom: 2rem;
    position: relative;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
    position: relative;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-align: center;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background-color: var(--primary);
    color: white;
}

.progress-step.active .step-label {
    color: var(--primary);
    font-weight: 600;
}

.progress-step.completed .step-number {
    background-color: var(--success);
    color: white;
}

/* Cartes de type de demande */
.type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.type-card.selected {
    border-color: var(--primary);
    background-color: var(--primary-light);
}

.type-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Animation */
.form-step {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .step-label {
        font-size: 0.7rem;
    }
    
    .type-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .progress-step {
        width: 25%;
    }
    
    .step-label {
        font-size: 0.6rem;
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentStep = 1;
    const totalSteps = 4;
    let photoTaken = false;
    let stream = null;
    
    // Éléments DOM
    const form = document.getElementById('cniForm');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('formProgress');
    const typeCards = document.querySelectorAll('.type-card');
    const sexeSelect = document.getElementById('sexe');
    const statutCivilSelect = document.getElementById('statut_civil');
    
    // Éléments caméra
    const cameraContainer = document.getElementById('camera-container');
    const cameraElement = document.getElementById('camera');
    const photoPreview = document.getElementById('photo-preview');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const startCameraBtn = document.getElementById('start-camera');
    const takePhotoBtn = document.getElementById('take-photo');
    const retakePhotoBtn = document.getElementById('retake-photo');
    const photoDataInput = document.getElementById('photo_data');
    
    // Initialisation
    updateProgressBar(currentStep);
    
    // Gestion des cartes de type de demande
    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            const radioInput = this.querySelector('input[type="radio"]');
            if (!radioInput.disabled) {
                radioInput.checked = true;
                updateTypeCardSelection();
                updateDocumentSections();
            }
        });
    });
    
    // Mise à jour de la sélection des cartes
    function updateTypeCardSelection() {
        typeCards.forEach(card => {
            const radioInput = card.querySelector('input[type="radio"]');
            if (radioInput.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
    
    // Mise à jour des sections de documents selon le type de demande
    function updateDocumentSections() {
        const selectedType = document.querySelector('input[name="type_demande"]:checked').value;
        const numeroCniContainer = document.querySelector('.numero-cni-container');
        
        // Cacher tous les documents spécifiques
        document.querySelectorAll('.doc-premiere, .doc-renouvellement, .doc-perte').forEach(el => {
            el.classList.add('d-none');
        });
        
        // Afficher les documents selon le type
        if (selectedType === 'premiere') {
            document.querySelectorAll('.doc-premiere').forEach(el => {
                el.classList.remove('d-none');
            });
            numeroCniContainer.classList.add('d-none');
        } else if (selectedType === 'renouvellement') {
            document.querySelectorAll('.doc-renouvellement').forEach(el => {
                el.classList.remove('d-none');
            });
            numeroCniContainer.classList.remove('d-none');
        } else if (selectedType === 'perte') {
            document.querySelectorAll('.doc-perte').forEach(el => {
                el.classList.remove('d-none');
            });
            numeroCniContainer.classList.remove('d-none');
        }
        
        // Gestion du document d'acte de mariage pour femme mariée
        updateMarriageDocumentVisibility();
    }
    
    // Mise à jour de la visibilité du document d'acte de mariage
    function updateMarriageDocumentVisibility() {
        const sexe = sexeSelect.value;
        const statutCivil = statutCivilSelect.value;
        const docMariage = document.querySelector('.doc-mariage');
        
        if (sexe === 'F' && statutCivil === 'Marie') {
            docMariage.classList.remove('d-none');
        } else {
            docMariage.classList.add('d-none');
        }
    }
    
    // Écouteurs pour le sexe et le statut civil
    sexeSelect.addEventListener('change', updateMarriageDocumentVisibility);
    statutCivilSelect.addEventListener('change', updateMarriageDocumentVisibility);
    
    // Navigation entre les étapes
    nextBtn.addEventListener('click', function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
                updateProgressBar(currentStep);
                
                if (currentStep === totalSteps) {
                    updateRecapitulatif();
                }
            }
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            updateProgressBar(currentStep);
        }
    });
    
    // Affichage de l'étape courante
    function showStep(step) {
        document.querySelectorAll('.form-step').forEach((el, index) => {
            if (index + 1 === step) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
        
        // Gestion des boutons
        if (step === 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        } else if (step === totalSteps) {
            prevBtn.style.display = 'block';
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            prevBtn.style.display = 'block';
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }
    }
    
    // Mise à jour de la barre de progression
    function updateProgressBar(step) {
        const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
        progressBar.style.width = progressPercentage + '%';
        
        document.querySelectorAll('.progress-step').forEach((el, index) => {
            if (index + 1 < step) {
                el.classList.add('completed');
                el.classList.remove('active');
            } else if (index + 1 === step) {
                el.classList.add('active');
                el.classList.remove('completed');
            } else {
                el.classList.remove('active', 'completed');
            }
        });
    }
    
    // Validation de l'étape courante
    function validateCurrentStep() {
        if (currentStep === 1) {
            const typeSelected = document.querySelector('input[name="type_demande"]:checked');
            if (!typeSelected) {
                alert('Veuillez sélectionner un type de demande');
                return false;
            }
            return true;
        } else if (currentStep === 2) {
            const requiredFields = document.querySelectorAll('#step2 [required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                alert('Veuillez remplir tous les champs obligatoires');
                return false;
            }
            
            const taille = document.getElementById('taille').value;
            if (taille < 100 || taille > 250) {
                alert('La taille doit être comprise entre 100 et 250 cm');
                document.getElementById('taille').classList.add('is-invalid');
                return false;
            }
            
            return true;
        } else if (currentStep === 3) {
            // Vérification de la photo
            if (!photoTaken) {
                alert('Veuillez prendre une photo d\'identité');
                return false;
            }
            
            // Vérification des documents requis selon le type de demande
            const selectedType = document.querySelector('input[name="type_demande"]:checked').value;
            const sexe = sexeSelect.value;
            const statutCivil = statutCivilSelect.value;
            
            let requiredDocs = [];
            
            if (selectedType === 'premiere') {
                requiredDocs.push('acte_naissance', 'certificat_nationalite');
            } else if (selectedType === 'renouvellement') {
                requiredDocs.push('ancienne_cni');
            } else if (selectedType === 'perte') {
                requiredDocs.push('declaration_perte');
            }
            
            // Ajout de l'acte de mariage pour femme mariée
            if (sexe === 'F' && statutCivil === 'Marie') {
                requiredDocs.push('acte_mariage');
            }
            
            // Vérification des documents
            let missingDocs = [];
            requiredDocs.forEach(doc => {
                const input = document.getElementById(doc);
                if (!input.files || input.files.length === 0) {
                    input.classList.add('is-invalid');
                    missingDocs.push(doc.replace('_', ' '));
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (missingDocs.length > 0) {
                alert('Veuillez fournir les documents suivants : ' + missingDocs.join(', '));
                return false;
            }
            
            return true;
        }
        
        return true;
    }
    
    // Mise à jour du récapitulatif
    function updateRecapitulatif() {
        const selectedType = document.querySelector('input[name="type_demande"]:checked').value;
        const typeLabels = {
            'premiere': 'Première demande',
            'renouvellement': 'Renouvellement',
            'perte': 'Perte/Vol'
        };
        
        document.getElementById('recap-type').textContent = typeLabels[selectedType];
        document.getElementById('recap-nom').textContent = document.getElementById('nom').value;
        document.getElementById('recap-prenom').textContent = document.getElementById('prenom').value;
        document.getElementById('recap-date-naissance').textContent = formatDate(document.getElementById('date_naissance').value);
        document.getElementById('recap-lieu-naissance').textContent = document.getElementById('lieu_naissance').value;
        document.getElementById('recap-sexe').textContent = document.getElementById('sexe').value === 'M' ? 'Masculin' : 'Féminin';
        document.getElementById('recap-statut-civil').textContent = document.getElementById('statut_civil').options[document.getElementById('statut_civil').selectedIndex].text;
        document.getElementById('recap-profession').textContent = document.getElementById('profession').value;
        document.getElementById('recap-taille').textContent = document.getElementById('taille').value;
        document.getElementById('recap-adresse').textContent = document.getElementById('adresse').value;
        
        // Photo
        document.getElementById('recap-photo').src = photoDataInput.value;
        
        // Documents
        const recapDocuments = document.getElementById('recap-documents');
        recapDocuments.innerHTML = '';
        
        const docLabels = {
            'acte_naissance': 'Acte de naissance',
            'certificat_nationalite': 'Certificat de nationalité',
            'ancienne_cni': 'Ancienne CNI',
            'declaration_perte': 'Déclaration de perte',
            'acte_mariage': 'Acte de mariage',
            'justificatif_profession': 'Justificatif de profession'
        };
        
        // Ajouter les documents fournis
        Object.keys(docLabels).forEach(docId => {
            const input = document.getElementById(docId);
            if (input && input.files && input.files.length > 0) {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <span>${docLabels[docId]}</span>
                    <span class="badge bg-success rounded-pill">Fourni</span>
                `;
                recapDocuments.appendChild(li);
            }
        });
    }
    
        // Formatage de date
        function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Gestion de la caméra
    startCameraBtn.addEventListener('click', function() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    cameraElement.srcObject = mediaStream;
                    cameraElement.play();
                    
                    // Afficher la vidéo et le bouton pour prendre la photo
                    cameraPlaceholder.classList.add('d-none');
                    cameraElement.classList.remove('d-none');
                    startCameraBtn.classList.add('d-none');
                    takePhotoBtn.classList.remove('d-none');
                })
                .catch(function(error) {
                    console.error("Erreur d'accès à la caméra: ", error);
                    alert("Impossible d'accéder à la caméra. Veuillez vérifier les permissions.");
                });
        } else {
            alert("Votre navigateur ne supporte pas l'accès à la caméra.");
        }
    });
    
    takePhotoBtn.addEventListener('click', function() {
        // Prendre la photo
        const context = photoPreview.getContext('2d');
        photoPreview.width = cameraElement.videoWidth;
        photoPreview.height = cameraElement.videoHeight;
        context.drawImage(cameraElement, 0, 0, photoPreview.width, photoPreview.height);
        
        // Convertir en base64
        const photoData = photoPreview.toDataURL('image/jpeg');
        photoDataInput.value = photoData;
        
        // Afficher l'aperçu et le bouton pour reprendre
        cameraElement.classList.add('d-none');
        photoPreview.classList.remove('d-none');
        takePhotoBtn.classList.add('d-none');
        retakePhotoBtn.classList.remove('d-none');
        
        // Arrêter la caméra
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        
        photoTaken = true;
    });
    
    retakePhotoBtn.addEventListener('click', function() {
        // Réinitialiser
        photoPreview.classList.add('d-none');
        retakePhotoBtn.classList.add('d-none');
        startCameraBtn.classList.remove('d-none');
        photoTaken = false;
        photoDataInput.value = '';
    });
    
    // Soumission du formulaire
    form.addEventListener('submit', function(event) {
        if (!validateCurrentStep()) {
            event.preventDefault();
            return;
        }
        
        // Vérification de l'acceptation des conditions
        const termsCheckbox = document.getElementById('terms');
        if (!termsCheckbox.checked) {
            alert('Veuillez accepter les conditions générales pour continuer');
            event.preventDefault();
            return;
        }
    });
    
    // Initialisation
    updateTypeCardSelection();
    updateDocumentSections();
});
</script>

<?php include('../includes/footer.php'); ?>
