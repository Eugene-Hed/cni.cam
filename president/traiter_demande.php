<?php
include('../includes/config.php');
include('../includes/check_auth.php');

function getDocumentTypeName($type) {
    $types = [
        'acte_naissance' => 'Acte de naissance',
        'carte_identite' => 'Carte d\'identité',
        'passeport' => 'Passeport',
        'certificat_nationalite' => 'Certificat de nationalité',
        'acte_mariage' => 'Acte de mariage',
        'justificatif_domicile' => 'Justificatif de domicile',
        'photo_identite' => 'Photo d\'identité',
        'autre' => 'Autre document'
    ];
    
    return isset($types[$type]) ? $types[$type] : 'Document inconnu';
}

// Fonction pour obtenir le libellé du motif de demande
function getMotifLabel($motif) {
    $motifs = [
        'naissance' => 'Par naissance',
        'mariage' => 'Par mariage',
        'naturalisation' => 'Par naturalisation',
        'filiation' => 'Par filiation'
    ];
    
    return isset($motifs[$motif]) ? $motifs[$motif] : 'Motif inconnu';
}


// Vérification du rôle président
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: /cni.cam/pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$demandeId) {
    header('Location: demandes_nationalite.php');
    exit();
}

// Récupération des informations de la demande
$query = "SELECT d.*, 
          u.Nom as UserNom, u.Prenom as UserPrenom, u.Email, u.NumeroTelephone, u.PhotoUtilisateur,
          dnd.*, 
          cn.NumeroCertificat, cn.DateEmission, cn.CheminPDF, cn.SignaturePresidentielle, cn.CheminSignaturePresident
          FROM demandes d
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          LEFT JOIN demande_nationalite_details dnd ON d.DemandeID = dnd.DemandeID
          LEFT JOIN certificatsnationalite cn ON d.DemandeID = cn.DemandeID
          WHERE d.DemandeID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: demandes_nationalite.php');
    exit();
}

// Traitement des actions
if (isset($_POST['action'])) {
    try {
        $db->beginTransaction();
        
        $action = $_POST['action'];
        $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
        $ancienStatut = $demande['Statut'];
        $nouveauStatut = $ancienStatut;
        $notificationType = '';
        $notificationMessage = '';
        
        switch ($action) {
            case 'approuver':
                $nouveauStatut = 'Approuvee';
                $notificationType = 'validation';
                $notificationMessage = 'Votre demande de certificat de nationalité a été approuvée.';
                
                // Générer un numéro de certificat
                $numeroCertificat = 'NAT-' . date('Y') . '-' . str_pad($demandeId, 6, '0', STR_PAD_LEFT);
                
                // Vérifier si un certificat existe déjà
                $stmt = $db->prepare("SELECT COUNT(*) FROM certificatsnationalite WHERE DemandeID = ?");
                $stmt->execute([$demandeId]);
                $certificatExists = $stmt->fetchColumn() > 0;
                
                if (!$certificatExists) {
                    // Créer une entrée dans la table certificatsnationalite
                    $stmt = $db->prepare("INSERT INTO certificatsnationalite 
                                        (DemandeID, NumeroCertificat, DateEmission) 
                                        VALUES (?, ?, CURDATE())");
                    $stmt->execute([$demandeId, $numeroCertificat]);
                }
                
                // Mettre à jour la date d'approbation
                $stmt = $db->prepare("UPDATE demandes SET DateApprobation = NOW() WHERE DemandeID = ?");
                $stmt->execute([$demandeId]);
                break;
                
            case 'rejeter':
                $nouveauStatut = 'Rejetee';
                $notificationType = 'rejet';
                $notificationMessage = 'Votre demande de certificat de nationalité a été rejetée.';
                
                if (empty($commentaire)) {
                    throw new Exception("Un motif de rejet est requis.");
                }
                break;
                
            case 'demander_complement':
                $nouveauStatut = 'EnCours';
                $notificationType = 'demande';
                $notificationMessage = 'Des informations complémentaires sont requises pour votre demande de certificat de nationalité.';
                
                if (empty($commentaire)) {
                    throw new Exception("Veuillez préciser les informations complémentaires requises.");
                }
                break;
        }
        
        // Mettre à jour le statut de la demande
        $stmt = $db->prepare("UPDATE demandes SET Statut = ? WHERE DemandeID = ?");
        $stmt->execute([$nouveauStatut, $demandeId]);
        
        // Ajouter une entrée dans l'historique
        $stmt = $db->prepare("INSERT INTO historique_demandes 
                            (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$demandeId, $ancienStatut, $nouveauStatut, $commentaire, $_SESSION['user_id']]);
        
        // Ajouter une notification
        $stmt = $db->prepare("INSERT INTO notifications 
                            (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                            VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$demande['UtilisateurID'], $demandeId, $notificationMessage, $notificationType]);
        
        // Journal d'activité
        $stmt = $db->prepare("INSERT INTO journalactivites 
                            (UtilisateurID, TypeActivite, Description, AdresseIP) 
                            VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            'Traitement_Demande',
            "Demande #$demandeId: $ancienStatut -> $nouveauStatut",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $db->commit();
        
        // Redirection avec message de succès
        header('Location: traiter_demande.php?id=' . $demandeId . '&status=updated');
        exit();
        
    } catch(Exception $e) {
        $db->rollBack();
        $error = "Une erreur est survenue: " . $e->getMessage();
    }
}

// Traitement de la signature
if (isset($_GET['action']) && $_GET['action'] == 'signer') {
    try {
        $db->beginTransaction();
        
        // Vérifier si un certificat existe déjà pour cette demande
        $stmt = $db->prepare("SELECT COUNT(*) FROM certificatsnationalite WHERE DemandeID = ?");
        $stmt->execute([$demandeId]);
        $certificatExists = $stmt->fetchColumn() > 0;
        
        // Chemin de la signature du président
        $signaturePath = '../uploads/signatures_president/signature_president_' . $demandeId . '_' . time() . '.png';
        
        if (!$certificatExists) {
            // Créer une entrée dans la table certificatsnationalite
            $numeroCertificat = 'NAT-' . date('Y') . '-' . str_pad($demandeId, 6, '0', STR_PAD_LEFT);
            
            $stmt = $db->prepare("INSERT INTO certificatsnationalite 
                                (DemandeID, NumeroCertificat, DateEmission, SignaturePresidentielle, CheminSignaturePresident) 
                                VALUES (?, ?, CURDATE(), 1, ?)");
            $stmt->execute([$demandeId, $numeroCertificat, $signaturePath]);
        } else {
            // Mettre à jour l'entrée existante
            $stmt = $db->prepare("UPDATE certificatsnationalite 
                                SET SignaturePresidentielle = 1,
                                    CheminSignaturePresident = ?
                                WHERE DemandeID = ?");
            $stmt->execute([$signaturePath, $demandeId]);
        }
        
        // Mettre à jour le statut de la demande
        $stmt = $db->prepare("UPDATE demandes 
                            SET SignatureOfficierRequise = 1, 
                                SignatureOfficierEnregistree = 1, 
                                CheminSignatureOfficier = ?, 
                                DateSignatureOfficier = NOW() 
                            WHERE DemandeID = ?");
        $stmt->execute([$signaturePath, $demandeId]);
        
        // Ajouter une entrée dans l'historique
        $stmt = $db->prepare("INSERT INTO historique_demandes 
                            (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                            VALUES (?, 'Approuvee', 'Approuvee', 'Signature présidentielle enregistrée', ?, NOW())");
        $stmt->execute([$demandeId, $_SESSION['user_id']]);
        
        // Ajouter une notification
        $stmt = $db->prepare("INSERT INTO notifications 
                            (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                            VALUES (?, ?, 'Votre certificat de nationalité a été signé par le président et est en cours de finalisation.', 'signature_president', NOW())");
        $stmt->execute([$demande['UtilisateurID'], $demandeId]);
        
        // Journal d'activité
        $stmt = $db->prepare("INSERT INTO journalactivites 
                            (UtilisateurID, TypeActivite, Description, AdresseIP) 
                            VALUES (?, 'Signature_Certificat', ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            "Signature présidentielle pour le certificat de nationalité #$demandeId",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $db->commit();
        
        // Redirection avec message de succès
        header('Location: traiter_demande.php?id=' . $demandeId . '&status=signed');
        exit();
        
    } catch(Exception $e) {
        $db->rollBack();
        $error = "Une erreur est survenue lors de la signature: " . $e->getMessage();
    }
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
$query = "SELECT d.DemandeID, d.NumeroReference, d.DateSoumission, d.Statut, dnd.Motif
          FROM demandes d
          LEFT JOIN demande_nationalite_details dnd ON d.DemandeID = dnd.DemandeID
          WHERE d.UtilisateurID = :userId 
          AND d.TypeDemande = 'NATIONALITE'
          AND d.DemandeID != :currentId
          ORDER BY d.DateSoumission DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute([
    'userId' => $demande['UtilisateurID'],
    'currentId' => $demandeId
]);
$previousRequests = $stmt->fetchAll();

// Vérifier si la signature présidentielle a été enregistrée
$signaturePresidentielle = false;

// Vérifier d'abord dans la table certificatsnationalite
if (isset($demande['SignaturePresidentielle']) && $demande['SignaturePresidentielle'] == 1) {
    $signaturePresidentielle = true;
}

// Si pas trouvé, vérifier dans la table demandes
if (!$signaturePresidentielle && isset($demande['SignatureOfficierEnregistree']) && $demande['SignatureOfficierEnregistree'] == 1) {
    $signaturePresidentielle = true;
}

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

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include('../includes/president_sidebar.php'); ?>

        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Traitement de la demande #<?php echo str_pad($demandeId, 6, '0', STR_PAD_LEFT); ?></h1>
                <div>
                    <a href="demandes_nationalite.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    La demande a été mise à jour avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'signed'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Le certificat a été signé avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Colonne principale -->
                <div class="col-lg-8">
                    <!-- Carte d'information de la demande -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-flag text-primary me-2"></i>
                                Demande de certificat de nationalité
                            </h5>
                            <div>
                                <span class="badge bg-<?php 
                                    echo match($demande['Statut']) {
                                        'Soumise' => 'secondary',
                                        'EnCours' => 'primary',
                                        'Approuvee' => 'success',
                                        'Rejetee' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo $demande['Statut']; ?>
                                </span>
                                <?php if (!empty($priority_badge)) echo $priority_badge; ?>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h6 class="fw-bold">Informations de la demande</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <p class="mb-1 text-muted small">Référence</p>
                                            <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['NumeroReference']); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1 text-muted small">Date de soumission</p>
                                            <p class="mb-2 fw-medium"><?php echo date('d/m/Y H:i', strtotime($demande['DateSoumission'])); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1 text-muted small">Temps d'attente</p>
                                            <p class="mb-2 fw-medium"><?php echo $waiting_time; ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1 text-muted small">Priorité</p>
                                            <p class="mb-2 fw-medium"><?php echo $priority; ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <h6 class="fw-bold">Informations du demandeur</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($demande['PhotoUtilisateur'])): ?>
                                                <img src="<?php echo htmlspecialchars($demande['PhotoUtilisateur']); ?>" 
                                                     class="rounded-circle" width="50" height="50" 
                                                     style="object-fit: cover;" alt="Photo du demandeur">
                                            <?php else: ?>
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-person text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></h6>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-envelope-fill me-1"></i><?php echo htmlspecialchars($demande['Email']); ?>
                                            </p>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-telephone-fill me-1"></i><?php echo htmlspecialchars($demande['NumeroTelephone']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Détails personnels</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Date de naissance</p>
                                        <p class="mb-2 fw-medium"><?php echo date('d/m/Y', strtotime($demande['DateNaissance'])); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Lieu de naissance</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['LieuNaissance']); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Sexe</p>
                                        <p class="mb-2 fw-medium"><?php echo $demande['Sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Nationalité actuelle</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['NationaliteActuelle'] ?? 'Camerounaise'); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Profession</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['Profession'] ?? 'Non spécifiée'); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">État civil</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['EtatCivil'] ?? 'Non spécifié'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Adresse</h6>
                                <p class="mb-1"><?php echo htmlspecialchars($demande['Adresse'] ?? 'Non spécifiée'); ?></p>
                                <p class="mb-0">
                                    <?php echo htmlspecialchars($demande['Ville'] ?? ''); ?>
                                    <?php if(!empty($demande['CodePostal'])): ?>, <?php echo htmlspecialchars($demande['CodePostal']); ?><?php endif; ?>
                                </p>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Informations sur les parents</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Nom du père</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['NomPere'] ?? 'Non spécifié'); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Nom de la mère</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['NomMere'] ?? 'Non spécifié'); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">Détails de la demande</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Type de demande</p>
                                        <p class="mb-2 fw-medium">
                                            <?php 
                                                $typeLabels = [
                                                    'naissance' => 'Par naissance',
                                                    'mariage' => 'Par mariage',
                                                    'naturalisation' => 'Par naturalisation',
                                                    'filiation' => 'Par filiation'
                                                ];
                                                echo isset($demande['Motif']) && isset($typeLabels[$demande['Motif']]) 
                                                    ? $typeLabels[$demande['Motif']] 
                                                    : 'Certificat de nationalité'; 
                                            ?>
                                        </p>
                                    </div>
                                    <?php if ($demande['Statut'] == 'Approuvee' && !empty($demande['NumeroCertificat'])): ?>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Numéro de certificat</p>
                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($demande['NumeroCertificat']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($demande['Statut'] == 'Approuvee' && !empty($demande['DateEmission'])): ?>
                                    <div class="col-6">
                                        <p class="mb-1 text-muted small">Date d'émission</p>
                                        <p class="mb-2 fw-medium"><?php echo date('d/m/Y', strtotime($demande['DateEmission'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($demande['Statut'] == 'Approuvee' && $signaturePresidentielle): ?>
                            <div class="alert alert-success mt-3">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading">Certificat signé</h6>
                                        <p class="mb-0">Le certificat de nationalité a été signé par le président.</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($demande['Statut'] == 'Soumise' || $demande['Statut'] == 'EnCours'): ?>
                        <div class="card-footer bg-white p-4 border-top">
                            <h6 class="fw-bold mb-3">Actions</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approuverModal">
                                    <i class="bi bi-check-circle me-2"></i>Approuver
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejeterModal">
                                    <i class="bi bi-x-circle me-2"></i>Rejeter
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#complementModal">
                                    <i class="bi bi-info-circle me-2"></i>Demander un complément
                                </button>
                            </div>
                        </div>
                        <?php elseif ($demande['Statut'] == 'Approuvee' && !$signaturePresidentielle): ?>
<div class="card-footer bg-white p-4 border-top">
    <h6 class="fw-bold mb-3">Actions</h6>
    <div class="d-flex flex-wrap gap-2">
        <a href="enregistrer_signature_president.php?id=<?php echo $demandeId; ?>" 
           class="btn btn-warning">
            <i class="bi bi-pen me-2"></i>Signer le certificat
        </a>
    </div>
</div>
<?php endif; ?>

                    </div>
                    
                    <!-- Documents -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                Documents fournis
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if (empty($documents)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-file-earmark-x text-muted display-4"></i>
                                    <p class="mt-3 text-muted">Aucun document n'a été fourni pour cette demande.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($documents as $document): ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 border">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="document-icon me-3">
                                                            <?php
                                                            $extension = pathinfo($document['CheminFichier'], PATHINFO_EXTENSION);
                                                            $iconClass = 'bi-file-earmark';
                                                            
                                                            if (in_array($extension, ['pdf'])) {
                                                                $iconClass = 'bi-file-earmark-pdf text-danger';
                                                            } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                $iconClass = 'bi-file-earmark-image text-success';
                                                            } elseif (in_array($extension, ['doc', 'docx'])) {
                                                                $iconClass = 'bi-file-earmark-word text-primary';
                                                            }
                                                            ?>
                                                            <i class="bi <?php echo $iconClass; ?> fs-2"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?php echo getDocumentTypeName($document['TypeDocument']); ?></h6>
                                                            <p class="text-muted small mb-0">
                                                                <?php echo date('d/m/Y H:i', strtotime($document['DateAjout'])); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light p-2 border-top">
                                                    <a href="<?php echo htmlspecialchars($document['CheminFichier']); ?>" 
                                                       class="btn btn-sm btn-outline-primary w-100" 
                                                       target="_blank">
                                                        <i class="bi bi-eye me-1"></i>Visualiser
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Historique -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Historique de la demande
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="timeline p-4">
                                <?php if (empty($historique)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-clock text-muted display-4"></i>
                                        <p class="mt-3 text-muted">Aucun historique disponible pour cette demande.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($historique as $index => $event): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-badge bg-<?php 
                                                echo match($event['NouveauStatut']) {
                                                    'Soumise' => 'secondary',
                                                    'EnCours' => 'primary',
                                                    'Approuvee' => 'success',
                                                    'Rejetee' => 'danger',
                                                    default => 'info'
                                                };
                                            ?>">
                                                <i class="bi <?php 
                                                    echo match($event['NouveauStatut']) {
                                                        'Soumise' => 'bi-file-earmark-plus',
                                                        'EnCours' => 'bi-hourglass-split',
                                                        'Approuvee' => 'bi-check-circle',
                                                        'Rejetee' => 'bi-x-circle',
                                                        default => 'bi-arrow-right'
                                                    };
                                                ?>"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <h6 class="mb-1">
                                                    <?php 
                                                        echo match($event['NouveauStatut']) {
                                                            'Soumise' => 'Demande soumise',
                                                            'EnCours' => 'Demande en cours de traitement',
                                                            'Approuvee' => 'Demande approuvée',
                                                            'Rejetee' => 'Demande rejetée',
                                                            default => 'Statut mis à jour'
                                                        };
                                                    ?>
                                                </h6>
                                                <p class="text-muted small mb-2">
                                                    <?php echo date('d/m/Y H:i', strtotime($event['DateModification'])); ?>
                                                    <?php if (!empty($event['OfficierNom'])): ?>
                                                        par <?php echo htmlspecialchars($event['OfficierPrenom'] . ' ' . $event['OfficierNom']); ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if (!empty($event['Commentaire'])): ?>
                                                    <div class="card bg-light p-3 mt-2">
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($event['Commentaire'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne latérale -->
                <div class="col-lg-4">
                    <!-- Statut de la demande -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                Statut actuel
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <?php
                                $statusIcon = match($demande['Statut']) {
                                    'Soumise' => '<i class="bi bi-file-earmark-plus display-1 text-secondary"></i>',
                                    'EnCours' => '<i class="bi bi-hourglass-split display-1 text-primary"></i>',
                                    'Approuvee' => '<i class="bi bi-check-circle display-1 text-success"></i>',
                                    'Rejetee' => '<i class="bi bi-x-circle display-1 text-danger"></i>',
                                    default => '<i class="bi bi-question-circle display-1 text-secondary"></i>'
                                };
                                echo $statusIcon;
                                ?>
                                <h5 class="mt-3">
                                    <?php 
                                        echo match($demande['Statut']) {
                                            'Soumise' => 'Demande soumise',
                                            'EnCours' => 'En cours de traitement',
                                            'Approuvee' => 'Demande approuvée',
                                            'Rejetee' => 'Demande rejetée',
                                            default => 'Statut inconnu'
                                        };
                                    ?>
                                </h5>
                                <p class="text-muted">
                                    <?php 
                                        echo match($demande['Statut']) {
                                            'Soumise' => 'La demande a été soumise et est en attente de traitement.',
                                            'EnCours' => 'La demande est en cours d\'examen par nos services.',
                                            'Approuvee' => 'La demande a été approuvée.',
                                            'Rejetee' => 'La demande a été rejetée.',
                                            default => 'Le statut de cette demande est inconnu.'
                                        };
                                    ?>
                                </p>
                            </div>
                            
                            <div class="progress mb-3" style="height: 10px;">
                                <?php
                                $progressValue = match($demande['Statut']) {
                                    'Soumise' => 25,
                                    'EnCours' => 50,
                                    'Approuvee' => 100,
                                    'Rejetee' => 100,
                                    default => 0
                                };
                                
                                $progressClass = match($demande['Statut']) {
                                    'Soumise' => 'bg-secondary',
                                    'EnCours' => 'bg-primary',
                                    'Approuvee' => 'bg-success',
                                    'Rejetee' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <div class="progress-bar <?php echo $progressClass; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $progressValue; ?>%" 
                                     aria-valuenow="<?php echo $progressValue; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Soumise</span>
                                <span>En cours</span>
                                <span>Terminée</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Demandes précédentes -->
                    <?php if (!empty($previousRequests)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Demandes précédentes
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($previousRequests as $req): ?>
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($req['NumeroReference']); ?></h6>
                                            <p class="text-muted small mb-0">
                                                <?php echo date('d/m/Y', strtotime($req['DateSoumission'])); ?>
                                                <?php if (!empty($req['Motif'])): ?>
                                                    - <?php echo getMotifLabel($req['Motif']); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?php 
                                                echo match($req['Statut']) {
                                                    'Soumise' => 'secondary',
                                                    'EnCours' => 'primary',
                                                    'Approuvee' => 'success',
                                                    'Rejetee' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo $req['Statut']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="traiter_demande.php?id=<?php echo $req['DemandeID']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Voir
                                        </a>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Certificat (si approuvé) -->
                    <!-- Certificat (si approuvé) -->
<?php if ($demande['Statut'] == 'Approuvee' && !empty($demande['NumeroCertificat'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0">
            <i class="bi bi-file-earmark-check text-primary me-2"></i>
            Certificat de nationalité
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="text-center mb-3">
            <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
            <h5 class="mt-3">Certificat #<?php echo htmlspecialchars($demande['NumeroCertificat']); ?></h5>
            <p class="text-muted">
                Émis le <?php echo date('d/m/Y', strtotime($demande['DateEmission'] ?? $demande['DateSoumission'])); ?>
            </p>
        </div>
        
        <?php if (!empty($demande['CheminPDF'])): ?>
        <div class="d-grid">
            <a href="<?php echo htmlspecialchars($demande['CheminPDF']); ?>" 
               class="btn btn-primary" 
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-2"></i>Visualiser le certificat
            </a>
        </div>
        <?php elseif ($signaturePresidentielle): ?>
        <div class="alert alert-info mb-3">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div>
                    <p class="mb-0">Le certificat est signé mais le PDF n'a pas encore été généré.</p>
                </div>
            </div>
        </div>
        <div class="d-grid">
            <a href="generer_certificat.php?id=<?php echo $demandeId; ?>" 
               class="btn btn-success">
                <i class="bi bi-file-earmark-pdf me-2"></i>Générer le certificat PDF
            </a>
        </div>
        <?php else: ?>
        <div class="alert alert-info mb-0">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div>
                    <p class="mb-0">Le certificat PDF n'a pas encore été généré. Veuillez d'abord signer le certificat.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modals pour les actions -->
<!-- Modal Approuver -->
<div class="modal fade" id="approuverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="approuver">
                <div class="modal-header">
                    <h5 class="modal-title">Approuver la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Vous êtes sur le point d'approuver cette demande de certificat de nationalité.</p>
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" placeholder="Ajouter un commentaire..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Approuver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rejeter -->
<div class="modal fade" id="rejeterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="rejeter">
                <div class="modal-header">
                    <h5 class="modal-title">Rejeter la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Vous êtes sur le point de rejeter cette demande de certificat de nationalité.</p>
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" placeholder="Veuillez indiquer le motif du rejet..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-2"></i>Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Demander un complément -->
<div class="modal fade" id="complementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="demander_complement">
                <div class="modal-header">
                    <h5 class="modal-title">Demander des informations complémentaires</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Vous êtes sur le point de demander des informations complémentaires pour cette demande.</p>
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Précisez les informations requises <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" placeholder="Veuillez préciser les informations ou documents complémentaires requis..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-info-circle me-2"></i>Demander
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles pour la timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-badge {
    position: absolute;
    left: -43px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.timeline-badge i {
    font-size: 14px;
}

.timeline-content {
    padding-bottom: 20px;
    border-bottom: 1px dashed #dee2e6;
}

.timeline-item:last-child .timeline-content {
    border-bottom: none;
    padding-bottom: 0;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 13px;
    width: 2px;
    background-color: #dee2e6;
}

/* Styles pour les documents */
.document-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background-color: rgba(0, 0, 0, 0.03);
}

/* Styles pour les badges de statut */
.badge {
    padding: 0.5rem 0.8rem;
    font-weight: 500;
}

/* Styles pour les cartes */
.card {
    border-radius: 0.5rem;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* Styles pour les boutons */
.btn {
    border-radius: 0.4rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Styles pour les modals */
.modal-content {
    border-radius: 0.5rem;
    border: none;
}

.modal-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.modal-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}
</style>

<script>
// Fonction pour afficher une alerte de confirmation avant de soumettre un formulaire
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des alertes auto-fermantes
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000); // Fermer après 5 secondes
    });
    
    // Validation des formulaires
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    });
});

// Fonction pour obtenir le nom du type de document
function getDocumentTypeName(type) {
    const types = {
        'acte_naissance': 'Acte de naissance',
        'carte_identite': 'Carte d\'identité',
        'passeport': 'Passeport',
        'certificat_nationalite': 'Certificat de nationalité',
        'acte_mariage': 'Acte de mariage',
        'justificatif_domicile': 'Justificatif de domicile',
        'photo_identite': 'Photo d\'identité',
        'autre': 'Autre document'
    };
    
    return types[type] || 'Document inconnu';
}

// Fonction pour obtenir le libellé du motif de demande
function getMotifLabel(motif) {
    const motifs = {
        'naissance': 'Par naissance',
        'mariage': 'Par mariage',
        'naturalisation': 'Par naturalisation',
        'filiation': 'Par filiation'
    };
    
    return motifs[motif] || 'Motif inconnu';
}
</script>

<?php include('../includes/footer.php'); ?>
