<?php
session_start();
include('../includes/config.php');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$demandeId) {
    header('Location: demandes_cni.php');
    exit();
}

// Traitement des actions rapides depuis l'URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action == 'start') {
        // Commencer le traitement
        try {
            $db->beginTransaction();
            
            // Récupération du statut actuel
            $stmt = $db->prepare("SELECT Statut FROM demandes WHERE DemandeID = ?");
            $stmt->execute([$demandeId]);
            $currentStatus = $stmt->fetchColumn();
            
            if ($currentStatus == 'Soumise') {
                // Mise à jour du statut
                $stmt = $db->prepare("UPDATE demandes SET Statut = 'EnCours' WHERE DemandeID = ?");
                $stmt->execute([$demandeId]);
                
                // Ajout dans l'historique
                $stmt = $db->prepare("INSERT INTO historique_demandes 
                                    (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                                    VALUES (?, ?, 'EnCours', 'Début du traitement de la demande', ?, NOW())");
                $stmt->execute([$demandeId, $currentStatus, $_SESSION['user_id']]);
                
                $db->commit();
                
                // Redirection avec message de succès
                header('Location: traiter_demande.php?id=' . $demandeId . '&status=started');
                exit();
            }
        } catch(Exception $e) {
            $db->rollBack();
            $error = "Une erreur est survenue: " . $e->getMessage();
        }
    }
}

// Traitement de l'approbation/rejet
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        $action = $_POST['action'];
        $commentaire = trim($_POST['commentaire']);
        $newStatus = ($action == 'approuver') ? 'Approuvee' : 'Rejetee';
        
        // Récupération du statut actuel et des informations utilisateur
        $stmt = $db->prepare("SELECT d.Statut, d.UtilisateurID FROM demandes d WHERE d.DemandeID = ?");
        $stmt->execute([$demandeId]);
        $demandeInfo = $stmt->fetch();
        $currentStatus = $demandeInfo['Statut'];
        $userId = $demandeInfo['UtilisateurID'];
        
        // Mise à jour du statut
        if ($action == 'approuver') {
            $stmt = $db->prepare("UPDATE demandes SET Statut = :status, SignatureRequise = 1, SignatureEnregistree = 0 WHERE DemandeID = :id");
        } else {
            $stmt = $db->prepare("UPDATE demandes SET Statut = :status WHERE DemandeID = :id");
        }
        
        $stmt->execute([
            'status' => $newStatus,
            'id' => $demandeId
        ]);
        
        // Ajout dans l'historique
        $stmt = $db->prepare("INSERT INTO historique_demandes 
                            (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                            VALUES (:demandeId, :oldStatus, :newStatus, :commentaire, :officier, NOW())");
        $stmt->execute([
            'demandeId' => $demandeId,
            'oldStatus' => $currentStatus,
            'newStatus' => $newStatus,
            'commentaire' => $commentaire,
            'officier' => $_SESSION['user_id']
        ]);
        
        // Ajout d'une notification
        $notifContent = ($action == 'approuver') 
            ? 'Votre demande de CNI a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.'
            : 'Votre demande de CNI a été rejetée. Motif: ' . $commentaire;
        
        $notifType = ($action == 'approuver') ? 'demande_approuvee' : 'demande_rejetee';
        
        $stmt = $db->prepare("INSERT INTO notifications 
                            (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                            VALUES (:userId, :demandeId, :content, :type, NOW())");
        $stmt->execute([
            'userId' => $userId,
            'demandeId' => $demandeId,
            'content' => $notifContent,
            'type' => $notifType
        ]);
        
        // Journal d'activité
        $stmt = $db->prepare("INSERT INTO journalactivites 
                            (UtilisateurID, TypeActivite, Description, AdresseIP) 
                            VALUES (:userId, :type, :description, :ip)");
        $stmt->execute([
            'userId' => $_SESSION['user_id'],
            'type' => 'Traitement_Demande',
            'description' => "Demande #$demandeId " . ($action == 'approuver' ? 'approuvée' : 'rejetée'),
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        $db->commit();
        
        // Redirection avec message de succès
        $statusParam = ($action == 'approuver') ? 'approved' : 'rejected';
        header('Location: demandes_cni.php?status=' . $statusParam);
        exit();
        
    } catch(Exception $e) {
        $db->rollBack();
        $error = "Une erreur est survenue: " . $e->getMessage();
    }
}

// Récupération des détails de la demande
$query = "SELECT d.*, dc.*, u.Email, u.NumeroTelephone, u.PhotoUtilisateur, u.Prenom as UserPrenom, u.Nom as UserNom
          FROM demandes d
          JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.DemandeID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: demandes_cni.php?error=not_found');
    exit();
}

// Récupération des documents
$query = "SELECT * FROM documents WHERE DemandeID = :id ORDER BY TypeDocument";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$documents = $stmt->fetchAll();

// Récupération de l'historique
$query = "SELECT h.*, u.Nom as OfficierNom, u.Prenom as OfficierPrenom 
          FROM historique_demandes h
          LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
          WHERE h.DemandeID = :id 
          ORDER BY h.DateModification DESC";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$historique = $stmt->fetchAll();

// Récupération des demandes précédentes du même utilisateur
$query = "SELECT d.DemandeID, d.NumeroReference, d.DateSoumission, d.Statut, d.SousTypeDemande
          FROM demandes d
          WHERE d.UtilisateurID = :userId 
          AND d.TypeDemande = 'CNI'
          AND d.DemandeID != :currentId
          ORDER BY d.DateSoumission DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([
    'userId' => $demande['UtilisateurID'],
    'currentId' => $demandeId
]);
$previousRequests = $stmt->fetchAll();

// Calcul du temps d'attente
$date_soumission = new DateTime($demande['DateSoumission']);
$now = new DateTime();
$interval = $date_soumission->diff($now);
$waiting_time = '';

if ($interval->y > 0) {
    $waiting_time = $interval->format('%y an(s), %m mois');
} elseif ($interval->m > 0) {
    $waiting_time = $interval->format('%m mois, %d jour(s)');
} elseif ($interval->d > 0) {
    $waiting_time = $interval->format('%d jour(s)');
} else {
    $waiting_time = $interval->format('%h heure(s)');
}

// Déterminer la priorité
$priority = '';
$priority_badge = '';
if ($demande['Statut'] == 'Soumise' || $demande['Statut'] == 'EnCours') {
    if ($interval->days >= 14) {
        $priority = 'Urgent';
        $priority_badge = '<span class="badge bg-danger">Urgent</span>';
    } elseif ($interval->days >= 7) {
        $priority = 'Prioritaire';
        $priority_badge = '<span class="badge bg-warning text-dark">Prioritaire</span>';
    } else {
        $priority = 'Normal';
        $priority_badge = '<span class="badge bg-info">Normal</span>';
    }
}

// Vérifier si la signature a été enregistrée
$signatureEnregistree = false;
if (isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1) {
    $signatureEnregistree = true;
}

// Récupérer le document de signature s'il existe
$signatureDocument = null;
foreach($documents as $doc) {
    if($doc['TypeDocument'] == 'Signature') {
        $signatureDocument = $doc;
        break;
    }
}

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Notification de statut -->
            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'started'): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Le traitement de la demande a été commencé avec succès.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- En-tête avec actions -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2 mb-0">
                        Demande #<?php echo str_pad($demandeId, 6, '0', STR_PAD_LEFT); ?>
                        <?php echo $priority_badge; ?>
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock me-1"></i> Soumise il y a <?php echo $waiting_time; ?> 
                        · Référence: <?php echo $demande['NumeroReference']; ?>
                    </p>
                </div>
                <div class="btn-toolbar">
                    <a href="demandes_cni.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                    
                    <?php if($demande['Statut'] == 'Soumise'): ?>
                        <a href="traiter_demande.php?id=<?php echo $demandeId; ?>&action=start" class="btn btn-primary me-2">
                            <i class="bi bi-play-fill"></i> Commencer le traitement
                        </a>
                    <?php endif; ?>
                    
                    <?php if($demande['Statut'] == 'EnCours'): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approuverModal">
                                <i class="bi bi-check-circle"></i> Approuver
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejeterModal">
                                <i class="bi bi-x-circle"></i> Rejeter
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($demande['Statut'] == 'Approuvee' && $signatureEnregistree && isset($demande['SignatureOfficierEnregistree']) && $demande['SignatureOfficierEnregistree'] == 1): ?>
    <a href="generer_cni.php?id=<?php echo $demandeId; ?>" class="btn btn-success me-2">
        <i class="bi bi-file-earmark-pdf"></i> Générer la CNI
    </a>
<?php endif; ?>

                    
                    <div class="dropdown ms-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i> Imprimer
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#documents-section">
                                    <i class="bi bi-file-earmark me-2"></i> Voir les documents
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#history-section">
                                    <i class="bi bi-clock-history me-2"></i> Voir l'historique
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statut actuel -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <?php 
                            $status_badges = [
                                'Soumise' => '<span class="status-badge status-submitted">Soumise</span>',
                                'EnCours' => '<span class="status-badge status-processing">En cours de traitement</span>',
                                'Approuvee' => '<span class="status-badge status-approved">Approuvée</span>',
                                'Rejetee' => '<span class="status-badge status-rejected">Rejetée</span>',
                                'Terminee' => '<span class="status-badge status-completed">Terminée</span>',
                                'Annulee' => '<span class="status-badge status-cancelled">Annulée</span>'
                            ];
                            echo $status_badges[$demande['Statut']] ?? $demande['Statut'];
                        ?>
                        
                        <div class="progress flex-grow-1 mx-3" style="height: 8px;">
                            <?php
                                $progress = 0;
                                switch($demande['Statut']) {
                                    case 'Soumise': $progress = 25; break;
                                    case 'EnCours': $progress = 50; break;
                                    case 'Approuvee': $progress = 75; break;
                                    case 'Terminee': $progress = 100; break;
                                    case 'Rejetee': $progress = 100; break;
                                    case 'Annulee': $progress = 100; break;
                                }
                            ?>
                            <div class="progress-bar bg-<?php echo ($demande['Statut'] == 'Rejetee' || $demande['Statut'] == 'Annulee') ? 'danger' : 'primary'; ?>" 
                                 role="progressbar" style="width: <?php echo $progress; ?>%" 
                                 aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="text-end">
                            <span class="text-muted small">Dernière mise à jour: 
                                <?php 
                                    echo !empty($historique) ? (new DateTime($historique[0]['DateModification']))->format('d/m/Y à H:i') : 'N/A'; 
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($demande['Statut'] == 'Approuvee'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-pen-fill fs-3 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1">Signature du citoyen</h5>
                                <?php if($signatureEnregistree): ?>
                                    <p class="mb-0 text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        La signature a été enregistrée par le citoyen. Vous pouvez maintenant générer la CNI.
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0 text-warning">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        En attente de la signature du citoyen. La CNI ne peut pas être générée tant que la signature n'est pas enregistrée.
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if($signatureEnregistree && $signatureDocument): ?>
                                <div>
                                    <a href="<?php echo htmlspecialchars($signatureDocument['CheminFichier']); ?>" 
                                       class="btn btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye me-1"></i> Voir la signature
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Colonne principale -->
                <div class="col-lg-8">
                    <!-- Informations de la demande -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Informations de la demande</h5>
                            <span class="badge bg-primary"><?php echo ucfirst($demande['SousTypeDemande']); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Nom</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['Nom']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Prénom</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['Prenom']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Date de naissance</label>
                                        <div class="form-control-plaintext">
                                            <?php 
                                                $birthdate = new DateTime($demande['DateNaissance']);
                                                echo $birthdate->format('d/m/Y') . ' (' . $birthdate->diff($now)->y . ' ans)';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Lieu de naissance</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['LieuNaissance']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Sexe</label>
                                        <div class="form-control-plaintext">
                                            <?php echo $demande['Sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Taille</label>
                                        <div class="form-control-plaintext"><?php echo $demande['Taille']; ?> cm</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Profession</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['Profession']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Statut civil</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['StatutCivil']); ?></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Adresse</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['Adresse']); ?></div>
                                    </div>
                                </div>
                                
                                <?php if($demande['SousTypeDemande'] == 'renouvellement' && !empty($demande['NumeroCNIPrecedente'])): ?>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label text-muted">Numéro CNI précédente</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($demande['NumeroCNIPrecedente']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if($demande['Statut'] == 'Approuvee' && $signatureEnregistree && (!isset($demande['SignatureOfficierEnregistree']) || $demande['SignatureOfficierEnregistree'] == 0)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-pen-fill fs-3 text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1">Signature de l'officier requise</h5>
                    <p class="mb-0 text-warning">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Vous devez enregistrer votre signature avant de pouvoir générer la CNI.
                    </p>
                </div>
                <div>
                    <a href="enregistrer_signature_officier.php?id=<?php echo $demandeId; ?>" class="btn btn-warning">
                        <i class="bi bi-pen me-1"></i> Enregistrer ma signature
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

                    <!-- Documents -->
                    <div class="card shadow-sm mb-4" id="documents-section">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Documents fournis</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($documents)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-file-earmark-x display-4 text-muted"></i>
                                    <p class="mt-3">Aucun document n'a été fourni pour cette demande.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php 
                                        $doc_icons = [
                                            'Photo' => 'bi-person-square',
                                            'CertificatNationalite' => 'bi-file-earmark-text',
                                            'ActeNaissance' => 'bi-file-earmark-text',
                                            'AncienneCNI' => 'bi-person-vcard',
                                            'ActeMariage' => 'bi-file-earmark-text',
                                            'JustificatifProfession' => 'bi-briefcase',
                                            'DeclarationPerte' => 'bi-exclamation-triangle',
                                            'Signature' => 'bi-pen'
                                        ];
                                        
                                        $doc_labels = [
                                            'Photo' => 'Photo d\'identité',
                                            'CertificatNationalite' => 'Certificat de nationalité',
                                            'ActeNaissance' => 'Acte de naissance',
                                            'AncienneCNI' => 'Ancienne CNI',
                                            'ActeMariage' => 'Acte de mariage',
                                            'JustificatifProfession' => 'Justificatif de profession',
                                            'DeclarationPerte' => 'Déclaration de perte',
                                            'Signature' => 'Signature'
                                        ];
                                    ?>
                                    
                                    <?php foreach($documents as $document): ?>
                                        <?php if($document['TypeDocument'] != 'Signature' || $signatureEnregistree): ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 document-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="document-icon me-3">
                                                            <i class="bi <?php echo $doc_icons[$document['TypeDocument']] ?? 'bi-file-earmark'; ?>"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?php echo $doc_labels[$document['TypeDocument']] ?? $document['TypeDocument']; ?></h6>
                                                            <p class="text-muted small mb-0">
                                                                Téléchargé le <?php echo (new DateTime($document['DateTelechargement']))->format('d/m/Y à H:i'); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white border-top-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <?php 
                                                                $status_colors = [
                                                                    'EnAttente' => 'warning',
                                                                    'Approuve' => 'success',
                                                                    'Rejete' => 'danger'
                                                                ];
                                                                $status_color = $status_colors[$document['StatutValidation']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $status_color; ?> bg-opacity-10 text-<?php echo $status_color; ?> border border-<?php echo $status_color; ?>">
                                                                <?php echo $document['StatutValidation']; ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <a href="<?php echo htmlspecialchars($document['CheminFichier']); ?>" 
                                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="bi bi-eye"></i> Voir
                                                            </a>
                                                            
                                                            <?php if($document['StatutValidation'] == 'EnAttente' && $demande['Statut'] == 'EnCours'): ?>
                                                                <div class="btn-group ms-1">
                                                                    <button type="button" class="btn btn-sm btn-outline-success document-action" 
                                                                            data-action="approve" data-document-id="<?php echo $document['DocumentID']; ?>">
                                                                        <i class="bi bi-check"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger document-action" 
                                                                            data-action="reject" data-document-id="<?php echo $document['DocumentID']; ?>">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Historique -->
                    <div class="card shadow-sm mb-4" id="history-section">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Historique de la demande</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($historique)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-clock-history display-4 text-muted"></i>
                                    <p class="mt-3">Aucun historique disponible pour cette demande.</p>
                                </div>
                            <?php else: ?>
                                <ul class="timeline">
                                    <?php foreach($historique as $index => $event): ?>
                                        <li class="timeline-item">
                                            <?php
                                                $status_icons = [
                                                    'Soumise' => 'bi-inbox-fill icon-submitted',
                                                    'EnCours' => 'bi-hourglass-split icon-processing',
                                                    'Approuvee' => 'bi-check-circle-fill icon-approved',
                                                    'Rejetee' => 'bi-x-circle-fill icon-rejected',
                                                    'Terminee' => 'bi-archive-fill icon-completed',
                                                    'Annulee' => 'bi-slash-circle-fill icon-cancelled'
                                                ];
                                                $icon_class = $status_icons[$event['NouveauStatut']] ?? 'bi-circle';
                                                
                                                $status_labels = [
                                                    'Soumise' => 'Demande soumise',
                                                    'EnCours' => 'Traitement en cours',
                                                    'Approuvee' => 'Demande approuvée',
                                                    'Rejetee' => 'Demande rejetée',
                                                    'Terminee' => 'Traitement terminé',
                                                    'Annulee' => 'Demande annulée'
                                                ];
                                                $status_label = $status_labels[$event['NouveauStatut']] ?? $event['NouveauStatut'];
                                            ?>
                                            <div class="timeline-icon">
                                                <i class="bi <?php echo $icon_class; ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <h6 class="mb-0"><?php echo $status_label; ?></h6>
                                                    <span class="text-muted small">
                                                    <?php echo (new DateTime($event['DateModification']))->format('d/m/Y à H:i'); ?>
                                                    </span>
                                                </div>
                                                <?php if(!empty($event['Commentaire'])): ?>
                                                    <div class="timeline-body mt-2">
                                                        <?php echo nl2br(htmlspecialchars($event['Commentaire'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if(!empty($event['OfficierNom'])): ?>
                                                    <div class="timeline-footer mt-2">
                                                        <span class="text-muted small">
                                                            Par: <?php echo htmlspecialchars($event['OfficierPrenom'] . ' ' . $event['OfficierNom']); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                                <!-- Colonne latérale -->
                                <div class="col-lg-4">
                    <!-- Informations du demandeur -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Informations du demandeur</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <?php if(!empty($demande['PhotoUtilisateur'])): ?>
                                    <img src="<?php echo htmlspecialchars($demande['PhotoUtilisateur']); ?>" 
                                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;" 
                                         alt="Photo de profil">
                                <?php else: ?>
                                    <div class="avatar-placeholder mb-3">
                                        <?php 
                                            $initials = mb_substr($demande['UserPrenom'], 0, 1) . mb_substr($demande['UserNom'], 0, 1);
                                            echo strtoupper($initials);
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <h5 class="mb-1"><?php echo htmlspecialchars($demande['UserPrenom'] . ' ' . $demande['UserNom']); ?></h5>
                                <p class="text-muted mb-0">Citoyen</p>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-envelope me-2 text-muted"></i>
                                    <span><?php echo htmlspecialchars($demande['Email']); ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-telephone me-2 text-muted"></i>
                                    <span><?php echo htmlspecialchars($demande['NumeroTelephone']); ?></span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="mailto:<?php echo $demande['Email']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-envelope"></i> Envoyer un email
                                </a>
                                <a href="tel:<?php echo $demande['NumeroTelephone']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-telephone"></i> Appeler
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Photo d'identité -->
                    <?php
                        $photoDocument = null;
                        foreach($documents as $doc) {
                            if($doc['TypeDocument'] == 'Photo') {
                                $photoDocument = $doc;
                                break;
                            }
                        }
                    ?>
                    <?php if($photoDocument): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Photo d'identité</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo htmlspecialchars($photoDocument['CheminFichier']); ?>" 
                                 class="img-fluid rounded" style="max-height: 300px;" alt="Photo d'identité">
                            
                            <?php if($photoDocument['StatutValidation'] == 'EnAttente' && $demande['Statut'] == 'EnCours'): ?>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-success document-action me-2" 
                                            data-action="approve" data-document-id="<?php echo $photoDocument['DocumentID']; ?>">
                                        <i class="bi bi-check-circle me-1"></i> Approuver
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger document-action" 
                                            data-action="reject" data-document-id="<?php echo $photoDocument['DocumentID']; ?>">
                                        <i class="bi bi-x-circle me-1"></i> Rejeter
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="mt-3">
                                    <?php 
                                        $status_colors = [
                                            'EnAttente' => 'warning',
                                            'Approuve' => 'success',
                                            'Rejete' => 'danger'
                                        ];
                                        $status_color = $status_colors[$photoDocument['StatutValidation']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $status_color; ?> bg-opacity-10 text-<?php echo $status_color; ?> border border-<?php echo $status_color; ?>">
                                        <?php echo $photoDocument['StatutValidation']; ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Signature du citoyen si disponible -->
                    <?php if($signatureEnregistree && $signatureDocument): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Signature</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo htmlspecialchars($signatureDocument['CheminFichier']); ?>" 
                                 class="img-fluid rounded" style="max-height: 150px;" alt="Signature">
                            
                            <div class="mt-3">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                    Signature validée
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Demandes précédentes -->
                    <?php if(!empty($previousRequests)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Demandes précédentes</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach($previousRequests as $req): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($req['NumeroReference']); ?></div>
                                                <div class="text-muted small">
                                                    <?php echo (new DateTime($req['DateSoumission']))->format('d/m/Y'); ?> - 
                                                    <?php echo ucfirst($req['SousTypeDemande']); ?>
                                                </div>
                                            </div>
                                            <div>
                                                <?php 
                                                    $status_badges = [
                                                        'Soumise' => '<span class="badge bg-primary">Soumise</span>',
                                                        'EnCours' => '<span class="badge bg-info">En cours</span>',
                                                        'Approuvee' => '<span class="badge bg-success">Approuvée</span>',
                                                        'Rejetee' => '<span class="badge bg-danger">Rejetée</span>',
                                                        'Terminee' => '<span class="badge bg-secondary">Terminée</span>',
                                                        'Annulee' => '<span class="badge bg-dark">Annulée</span>'
                                                    ];
                                                    echo $status_badges[$req['Statut']] ?? $req['Statut'];
                                                ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Notes de traitement -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Notes de traitement</h5>
                        </div>
                        <div class="card-body">
                            <form id="notesForm">
                                <div class="mb-3">
                                    <textarea class="form-control" id="treatment-notes" rows="4" placeholder="Ajoutez des notes privées concernant cette demande..."></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Enregistrer les notes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal d'approbation -->
<div class="modal fade" id="approuverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="approuver">
                
                <div class="modal-header">
                    <h5 class="modal-title">Approuver la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Vous êtes sur le point d'approuver cette demande de CNI. Une notification sera envoyée au demandeur pour l'inviter à enregistrer sa signature.
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" 
                                  placeholder="Ajoutez un commentaire qui sera visible par le demandeur..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Approuver la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="modal fade" id="rejeterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="rejeter">
                
                <div class="modal-header">
                    <h5 class="modal-title">Rejeter la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Vous êtes sur le point de rejeter cette demande de CNI. Cette action est irréversible.
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" 
                                  placeholder="Veuillez indiquer le motif du rejet..." required></textarea>
                        <div class="form-text">Ce motif sera communiqué au demandeur.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Rejeter la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles pour les badges de statut */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 500;
}

.status-submitted {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.status-processing {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.status-approved {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.status-rejected {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-completed {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.status-cancelled {
    background-color: rgba(33, 37, 41, 0.1);
    color: #212529;
}

/* Styles pour les icônes de documents */
.document-card {
    transition: all 0.2s ease;
}

.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
}

.document-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

/* Styles pour la timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
    list-style: none;
    margin: 0;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: -2rem;
    top: 0;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background-color: #fff;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    z-index: 1;
}

.timeline-content {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

/* Couleurs pour les icônes de la timeline */
.icon-submitted {
    color: #0d6efd;
}

.icon-processing {
    color: #0dcaf0;
}

.icon-approved {
    color: #198754;
}

.icon-rejected {
    color: #dc3545;
}

.icon-completed {
    color: #6c757d;
}

.icon-cancelled {
    color: #212529;
}

/* Styles pour l'avatar placeholder */
.avatar-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    margin: 0 auto;
}

/* Styles pour l'impression */
@media print {
    .sidebar, .navbar, .btn-toolbar, .dropdown, .btn-group, .document-action, #notesForm {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
    }
    
    .col-lg-8 {
        width: 100% !important;
    }
    
    .col-lg-4 {
        width: 100% !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des actions sur les documents
    const documentActions = document.querySelectorAll('.document-action');
    documentActions.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const documentId = this.getAttribute('data-document-id');
            
            // Appel AJAX pour mettre à jour le statut du document
            fetch('update_document_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `document_id=${documentId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mise à jour de l'interface
                    const card = this.closest('.document-card');
                    const statusBadge = card.querySelector('.badge');
                    
                    if (action === 'approve') {
                        statusBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success';
                        statusBadge.textContent = 'Approuve';
                    } else if (action === 'reject') {
                        statusBadge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger';
                        statusBadge.textContent = 'Rejete';
                    }
                    
                    // Masquer les boutons d'action
                    const actionButtons = card.querySelectorAll('.document-action');
                    actionButtons.forEach(btn => btn.style.display = 'none');
                    
                    // Notification
                    alert(data.message || 'Statut du document mis à jour avec succès');
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la communication avec le serveur');
            });
        });
    });
    
    // Gestion du formulaire de notes
    const notesForm = document.getElementById('notesForm');
    if (notesForm) {
        notesForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const notes = document.getElementById('treatment-notes').value;
            
            // Appel AJAX pour sauvegarder les notes
            fetch('save_treatment_notes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `demande_id=<?php echo $demandeId; ?>&notes=${encodeURIComponent(notes)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Notes enregistrées avec succès');
                } else {
                    alert(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la communication avec le serveur');
            });
        });
    }
    
    // Validation du formulaire de rejet
    const rejectForm = document.querySelector('#rejeterModal form');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const commentaire = this.querySelector('#commentaire').value.trim();
            if (!commentaire) {
                e.preventDefault();
                alert('Veuillez indiquer le motif du rejet');
            }
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
