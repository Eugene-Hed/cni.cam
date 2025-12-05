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

// Récupération des informations complètes de l'utilisateur
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

// Vérification si l'utilisateur a déjà un certificat de nationalité
$query = "SELECT * FROM demandes 
          WHERE UtilisateurID = :userId 
          AND TypeDemande = 'NATIONALITE' 
          AND (Statut = 'Approuvee' OR Statut = 'Terminee')
          ORDER BY DateSoumission DESC 
          LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$existingCertificat = $stmt->fetch();

// Vérification si l'utilisateur a une demande de nationalité en cours
$query = "SELECT * FROM demandes 
          WHERE UtilisateurID = :userId 
          AND TypeDemande = 'NATIONALITE' 
          AND (Statut = 'Soumise' OR Statut = 'EnCours')";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$pendingRequest = $stmt->fetch();

// Vérification si l'utilisateur a déjà une CNI ou une demande de CNI en cours
$query = "SELECT * FROM demandes 
          WHERE UtilisateurID = :userId 
          AND TypeDemande = 'CNI'";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$cniRequests = $stmt->fetchAll();

// Variables pour déterminer l'état des demandes de CNI
$hasCNI = false;
$hasPendingCNI = false;
$certificatDocument = null;

// Parcourir toutes les demandes de CNI
foreach ($cniRequests as $cniRequest) {
    // Vérifier si l'utilisateur a une CNI approuvée ou terminée
    if ($cniRequest['Statut'] == 'Approuvee' || $cniRequest['Statut'] == 'Terminee') {
        $hasCNI = true;
        
        // Rechercher le certificat de nationalité dans les documents de cette demande
        $query = "SELECT d.* FROM documents d
                  WHERE d.DemandeID = :demandeId 
                  AND d.TypeDocument = 'CertificatNationalite'
                  ORDER BY d.DateTelechargement DESC 
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute(['demandeId' => $cniRequest['DemandeID']]);
        $certificatDocument = $stmt->fetch();
        
        if ($certificatDocument) {
            break; // On a trouvé un certificat, on arrête la recherche
        }
    }
    
    // Vérifier si l'utilisateur a une demande de CNI en cours
    if ($cniRequest['Statut'] == 'Soumise' || $cniRequest['Statut'] == 'EnCours') {
        $hasPendingCNI = true;
    }
}

// Si on n'a pas trouvé de certificat dans les demandes approuvées, chercher dans les demandes en cours
if (!$certificatDocument && $hasPendingCNI) {
    foreach ($cniRequests as $cniRequest) {
        if ($cniRequest['Statut'] == 'Soumise' || $cniRequest['Statut'] == 'EnCours') {
            $query = "SELECT d.* FROM documents d
                      WHERE d.DemandeID = :demandeId 
                      AND d.TypeDocument = 'CertificatNationalite'
                      ORDER BY d.DateTelechargement DESC 
                      LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute(['demandeId' => $cniRequest['DemandeID']]);
            $certificatDocument = $stmt->fetch();
            
            if ($certificatDocument) {
                break;
            }
        }
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Empêcher la demande si l'utilisateur a déjà une CNI ou une demande de CNI en cours
        if ($hasCNI) {
            throw new Exception("Vous possédez déjà une CNI, ce qui implique que vous avez déjà un certificat de nationalité. Veuillez contacter l'administration si vous avez besoin d'une copie.");
        }
        
        if ($hasPendingCNI) {
            throw new Exception("Vous avez déjà une demande de CNI en cours, qui inclut un certificat de nationalité. Veuillez attendre que cette demande soit traitée.");
        }

        // Validation des données
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
        $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
        $dateNaissance = $_POST['date_naissance'];
        $lieuNaissance = filter_var($_POST['lieu_naissance'], FILTER_SANITIZE_STRING);
        $sexe = $_POST['sexe']; // Nouveau champ
        $adresse = filter_var($_POST['adresse'], FILTER_SANITIZE_STRING);
        $ville = filter_var($_POST['ville'] ?? '', FILTER_SANITIZE_STRING); // Nouveau champ
        $codePostal = filter_var($_POST['code_postal'] ?? '', FILTER_SANITIZE_STRING); // Nouveau champ
        $telephone = filter_var($_POST['telephone'], FILTER_SANITIZE_STRING);
        $etatCivil = $_POST['etat_civil'] ?? 'Celibataire'; // Nouveau champ
        $profession = filter_var($_POST['profession'] ?? '', FILTER_SANITIZE_STRING); // Nouveau champ
        $nationaliteActuelle = filter_var($_POST['nationalite_actuelle'] ?? 'Camerounaise', FILTER_SANITIZE_STRING); // Nouveau champ
        $nomPere = filter_var($_POST['nom_pere'], FILTER_SANITIZE_STRING);
        $nomMere = filter_var($_POST['nom_mere'], FILTER_SANITIZE_STRING);
        $motif = filter_var($_POST['motif'], FILTER_SANITIZE_STRING);

        // Vérification de demande en cours
        if ($pendingRequest) {
            throw new Exception("Vous avez déjà une demande de certificat de nationalité en cours. Veuillez attendre que celle-ci soit traitée.");
        }

        // Génération d'un numéro de référence unique
        $reference = 'NAT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Insertion dans la table demandes
        $query = "INSERT INTO demandes (UtilisateurID, TypeDemande, Statut, NumeroReference, DateSoumission, MontantPaiement, StatutPaiement) 
                  VALUES (:userId, 'NATIONALITE', 'Soumise', :reference, NOW(), 5000, 'En attente')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'userId' => $userId,
            'reference' => $reference
        ]);
        
        $demandeId = $db->lastInsertId();

        // Insertion dans demande_nationalite_details avec les nouveaux champs
        $query = "INSERT INTO demande_nationalite_details 
                  (DemandeID, Nom, Prenom, DateNaissance, LieuNaissance, Sexe, 
                   NomPere, NomMere, Adresse, Ville, CodePostal, Telephone, 
                   EtatCivil, Profession, NationaliteActuelle, Motif) 
                  VALUES 
                  (:demandeId, :nom, :prenom, :dateNaissance, :lieuNaissance, :sexe, 
                   :nomPere, :nomMere, :adresse, :ville, :codePostal, :telephone, 
                   :etatCivil, :profession, :nationaliteActuelle, :motif)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'nom' => $nom,
            'prenom' => $prenom,
            'dateNaissance' => $dateNaissance,
            'lieuNaissance' => $lieuNaissance,
            'sexe' => $sexe,
            'nomPere' => $nomPere,
            'nomMere' => $nomMere,
            'adresse' => $adresse,
            'ville' => $ville,
            'codePostal' => $codePostal,
            'telephone' => $telephone,
            'etatCivil' => $etatCivil,
            'profession' => $profession,
            'nationaliteActuelle' => $nationaliteActuelle,
            'motif' => $motif
        ]);

        // Configuration des documents requis
        $documents = [
            'acte_naissance' => [
                'type' => 'ActeNaissance',
                'required' => true,
                'label' => 'Acte de naissance'
            ],
            'certificat_nationalite_parent' => [
                'type' => 'CertificatNationalite',
                'required' => true,
                'label' => 'Certificat de nationalité d\'un parent'
            ],
            'casier_judiciaire' => [
                'type' => 'CasierJudiciaire',
                'required' => true,
                'label' => 'Casier judiciaire'
            ]
        ];

        $uploadDir = '../uploads/documents_nationalite/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Traitement des documents
        foreach ($documents as $input_name => $doc_info) {
            if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
                $file = $_FILES[$input_name];
                $filename = $file['name'];
                $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                // Vérification du type de fichier
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array($filetype, $allowed)) {
                    throw new Exception("Le type de fichier pour {$doc_info['label']} n'est pas autorisé");
                }
                
                // Vérification de la taille (5MB max)
                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Le fichier {$doc_info['label']} est trop volumineux");
                }

                $newname = $uploadDir . uniqid() . '_' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $newname)) {
                    // Vérification si le document existe déjà pour cet utilisateur
                    $query = "SELECT d.* FROM documents d
                              JOIN demandes dm ON d.DemandeID = dm.DemandeID
                              WHERE d.Utilisateurid = :userId AND d.TypeDocument = :type
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
                        $query = "INSERT INTO documents 
                                 (DemandeID, TypeDocument, CheminFichier, Utilisateurid) 
                                 VALUES (:demandeId, :type, :chemin, :userId)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            'demandeId' => $demandeId,
                            'type' => $doc_info['type'],
                            'chemin' => $existingDoc['CheminFichier'],
                            'userId' => $userId
                        ]);
                    } else {
                        $query = "INSERT INTO documents 
                                 (DemandeID, TypeDocument, CheminFichier, Utilisateurid) 
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
                    throw new Exception("Erreur lors du téléchargement du fichier {$doc_info['label']}");
                }
            } elseif ($doc_info['required']) {
                // Si le document est requis mais n'a pas été fourni, vérifier s'il existe déjà
                $query = "SELECT d.* FROM documents d
                          JOIN demandes dm ON d.DemandeID = dm.DemandeID
                          WHERE d.Utilisateurid = :userId AND d.TypeDocument = :type
                          ORDER BY d.DateTelechargement DESC LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    'userId' => $userId,
                    'type' => $doc_info['type']
                ]);
                $existingDoc = $stmt->fetch();
                
                if ($existingDoc && file_exists($existingDoc['CheminFichier'])) {
                    // Réutiliser le document existant
                    $query = "INSERT INTO documents 
                             (DemandeID, TypeDocument, CheminFichier, Utilisateurid) 
                             VALUES (:demandeId, :type, :chemin, :userId)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        'demandeId' => $demandeId,
                        'type' => $doc_info['type'],
                        'chemin' => $existingDoc['CheminFichier'],
                        'userId' => $userId
                    ]);
                } else {
                    throw new Exception("Le document {$doc_info['label']} est requis");
                }
            }
        }

        // Ajouter une entrée dans l'historique
        $query = "INSERT INTO historique_demandes 
                 (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                 VALUES (:demandeId, NULL, 'Soumise', 'Demande de certificat de nationalité soumise', :userId, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'userId' => $userId
        ]);

        // Ajouter une notification pour l'utilisateur
        $query = "INSERT INTO notifications 
                 (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                 VALUES (:userId, :demandeId, 'Votre demande de certificat de nationalité a été soumise avec succès.', 'demande', NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'userId' => $userId,
            'demandeId' => $demandeId
        ]);

        // Ajouter une notification pour les officiers
        $query = "SELECT UtilisateurID FROM utilisateurs WHERE RoleID = 3";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $officiers = $stmt->fetchAll();

        foreach ($officiers as $officier) {
            $query = "INSERT INTO notifications 
                     (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                     VALUES (:officierID, :demandeId, 'Nouvelle demande de certificat de nationalité à traiter.', 'nouvelle_demande', NOW())";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'officierID' => $officier['UtilisateurID'],
                'demandeId' => $demandeId
            ]);
        }

        $db->commit();
        $success = "Votre demande de certificat de nationalité a été soumise avec succès. Numéro de référence: " . $reference;
        
        // Redirection vers la page de suivi
        header("Location: mes_demandes.php?success=" . urlencode($success));
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// Titre de la page
$pageTitle = "Demande de Certificat de Nationalité";
include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- En-tête de la page -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="icon-circle bg-primary text-white">
                                <i class="bi bi-flag"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h1 class="h3 mb-2">Demande de Certificat de Nationalité</h1>
                            <p class="text-muted mb-0">Complétez le formulaire ci-dessous pour obtenir votre certificat de nationalité camerounaise.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($existingCertificat): ?>
                <div class="alert alert-info" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-info-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Vous possédez déjà un certificat de nationalité</h5>
                            <p>Votre demande précédente a été approuvée le <?php echo date('d/m/Y', strtotime($existingCertificat['DateApprobation'])); ?>.</p>
                            <hr>
                            <p class="mb-0">Si vous souhaitez obtenir une copie, veuillez vous rendre à la section "Mes documents" ou contacter l'administration.</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($pendingRequest): ?>
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Demande en cours de traitement</h5>
                            <p>Vous avez déjà une demande de certificat de nationalité en cours de traitement soumise le <?php echo date('d/m/Y', strtotime($pendingRequest['DateSoumission'])); ?>.</p>
                            <hr>
                            <p class="mb-0">Vous pouvez suivre l'état de votre demande dans la section <a href="mes_demandes.php" class="alert-link">Mes demandes</a>.</p>
                        </div>
                    </div>
                </div>
            <?php elseif ($hasCNI): ?>
                <div class="alert alert-info" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-info-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Vous possédez déjà une CNI</h5>
                            <p>Votre Carte Nationale d'Identité implique que vous avez déjà un certificat de nationalité camerounaise.</p>
                            <?php if ($certificatDocument): ?>
                                <hr>
                                <p class="mb-0">Vous pouvez télécharger votre certificat de nationalité <a href="<?php echo $certificatDocument['CheminFichier']; ?>" class="alert-link" target="_blank">ici</a>.</p>
                            <?php else: ?>
                                <hr>
                                <p class="mb-0">Si vous avez besoin d'une copie de votre certificat, veuillez contacter l'administration.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($hasPendingCNI): ?>
                <div class="alert alert-warning" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Demande de CNI en cours</h5>
                            <p>Vous avez déjà une demande de Carte Nationale d'Identité en cours de traitement, qui inclut un certificat de nationalité.</p>
                            <hr>
                            <p class="mb-0">Veuillez attendre que votre demande de CNI soit traitée. Vous pouvez suivre son état dans la section <a href="mes_demandes.php" class="alert-link">Mes demandes</a>.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Formulaire de demande -->
                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Informations personnelles</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['Nom']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_naissance" class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($user['DateNaissance']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lieu_naissance" class="form-label">Lieu de naissance <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" 
                                           value="<?php echo htmlspecialchars($user['VilleNaissance'] ?? $user['LieuNaissance']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="sexe" class="form-label">Sexe <span class="text-danger">*</span></label>
                                    <select class="form-select" id="sexe" name="sexe" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="M" <?php echo $user['Sexe'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                        <option value="F" <?php echo $user['Sexe'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user['NumeroTelephone']); ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['Adresse']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ville" class="form-label">Ville <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ville" name="ville" value="<?php echo htmlspecialchars($user['VilleResidence'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="code_postal" class="form-label">Code postal</label>
                                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($user['CodePostal'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="etat_civil" class="form-label">État civil</label>
                                    <select class="form-select" id="etat_civil" name="etat_civil">
                                        <option value="Celibataire" <?php echo ($user['EtatCivil'] ?? '') == 'Celibataire' ? 'selected' : ''; ?>>Célibataire</option>
                                        <option value="Marie" <?php echo ($user['EtatCivil'] ?? '') == 'Marie' ? 'selected' : ''; ?>>Marié(e)</option>
                                        <option value="Divorce" <?php echo ($user['EtatCivil'] ?? '') == 'Divorce' ? 'selected' : ''; ?>>Divorcé(e)</option>
                                        <option value="Veuf" <?php echo ($user['EtatCivil'] ?? '') == 'Veuf' ? 'selected' : ''; ?>>Veuf/Veuve</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="profession" class="form-label">Profession</label>
                                    <input type="text" class="form-control" id="profession" name="profession" value="<?php echo htmlspecialchars($user['Profession'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="nationalite_actuelle" class="form-label">Nationalité actuelle</label>
                                    <input type="text" class="form-control" id="nationalite_actuelle" name="nationalite_actuelle" value="Camerounaise" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Informations sur les parents</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nom_pere" class="form-label">Nom complet du père <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom_pere" name="nom_pere" value="<?php echo htmlspecialchars($user['NomPere'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="nom_mere" class="form-label">Nom complet de la mère <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom_mere" name="nom_mere" value="<?php echo htmlspecialchars($user['NomMere'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Motif de la demande</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="motif" class="form-label">Motif de la demande <span class="text-danger">*</span></label>
                                <select class="form-select" id="motif" name="motif" required>
                                    <option value="">Sélectionnez un motif</option>
                                    <option value="naissance">Par naissance</option>
                                    <option value="mariage">Par mariage</option>
                                    <option value="naturalisation">Par naturalisation</option>
                                    <option value="filiation">Par filiation</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Documents requis</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-info-circle-fill"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">Veuillez télécharger les documents suivants au format PDF, JPG ou PNG. Taille maximale: 5 Mo par fichier.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="acte_naissance" class="form-label">Acte de naissance <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="acte_naissance" name="acte_naissance" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Copie intégrale de votre acte de naissance</div>
                                </div>
                                <div class="col-md-12">
                                    <label for="certificat_nationalite_parent" class="form-label">Certificat de nationalité d'un parent <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="certificat_nationalite_parent" name="certificat_nationalite_parent" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Certificat de nationalité camerounaise de l'un de vos parents</div>
                                </div>
                                <div class="col-md-12">
                                    <label for="casier_judiciaire" class="form-label">Casier judiciaire <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="casier_judiciaire" name="casier_judiciaire" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Extrait de casier judiciaire datant de moins de 3 mois</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Frais et paiement</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-primary mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-credit-card-fill"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Frais de traitement</h5>
                                        <p class="mb-0">Les frais de traitement pour une demande de certificat de nationalité s'élèvent à <strong>5 000 FCFA</strong>. Le paiement sera à effectuer après la soumission de votre demande.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Déclaration sur l'honneur</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="declaration" name="declaration" required>
                                <label class="form-check-label" for="declaration">
                                    Je déclare sur l'honneur que les informations fournies sont exactes et complètes. Je suis conscient(e) que toute fausse déclaration peut entraîner le rejet de ma demande et des poursuites judiciaires.
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                        <a href="mes_demandes.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Soumettre ma demande
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.form-label {
    font-weight: 500;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .icon-circle {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Afficher le nom du fichier sélectionné
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            const fileSize = this.files[0]?.size;
            
            // Vérifier la taille du fichier (max 5MB)
            if (fileSize > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. La taille maximale autorisée est de 5 Mo.');
                this.value = '';
                return;
            }
            
            // Afficher le nom du fichier
            const label = this.nextElementSibling;
            if (fileName) {
                label.textContent = fileName;
            } else {
                label.textContent = this.getAttribute('data-default-text') || '';
            }
        });
    });
    
    // Afficher/masquer les champs supplémentaires en fonction du motif
    const motifSelect = document.getElementById('motif');
    
    if (motifSelect) {
        motifSelect.addEventListener('change', function() {
            const motif = this.value;
            
        
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
