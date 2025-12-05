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
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
$demande = null;
$historique = [];
$documents = [];
$paiements = [];
$rendezvous = [];
$error = '';

// Si un numéro de référence est fourni, rechercher la demande
if (!empty($reference)) {
    // Récupération des détails de la demande
    $query = "SELECT d.*, 
              CASE 
                  WHEN d.TypeDemande = 'CNI' THEN dc.Nom 
                  WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Nom 
              END as Nom,
              CASE 
                  WHEN d.TypeDemande = 'CNI' THEN dc.Prenom 
                  WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Prenom 
              END as Prenom
              FROM demandes d
              LEFT JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
              LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID
              WHERE d.NumeroReference = :reference AND d.UtilisateurID = :userId";

    $stmt = $db->prepare($query);
    $stmt->execute([
        'reference' => $reference,
        'userId' => $userId
    ]);
    $demande = $stmt->fetch();

    if ($demande) {
        // Récupération de l'historique
        $query = "SELECT h.*, u.Nom, u.Prenom, u.RoleId
                  FROM historique_demandes h
                  LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
                  WHERE h.DemandeID = :id 
                  ORDER BY h.DateModification DESC";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $demande['DemandeID']]);
        $historique = $stmt->fetchAll();

        // Récupération des documents
        $query = "SELECT * FROM documents WHERE DemandeID = :id ORDER BY DateTelechargement DESC";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $demande['DemandeID']]);
        $documents = $stmt->fetchAll();

        // Récupération des paiements
        $query = "SELECT * FROM paiements WHERE DemandeID = :id ORDER BY DatePaiement DESC";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $demande['DemandeID']]);
        $paiements = $stmt->fetchAll();

        // Récupération des rendez-vous
        $query = "SELECT * FROM rendezvous WHERE DemandeID = :id ORDER BY DateRendezVous";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $demande['DemandeID']]);
        $rendezvous = $stmt->fetchAll();
    } else {
        $error = "Aucune demande trouvée avec cette référence.";
    }
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<style>
:root {
    --primary: #1774df;
    --primary-light: rgba(23, 116, 223, 0.1);
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --white: #ffffff;
    --border-radius: 15px;
    --box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
}

.dashboard-container {
    background-color: var(--light);
    padding: 30px 0;
    min-height: calc(100vh - 180px);
}

.card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    overflow: hidden;
    margin-bottom: 20px;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    background-color: var(--white);
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 20px;
}

.card-title {
    margin-bottom: 0;
    font-weight: 600;
    color: var(--dark);
}

.card-body {
    padding: 20px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-action {
    padding: 8px 15px;
    border-radius: 8px;
    transition: var(--transition);
}

.btn-action:hover {
    transform: translateY(-2px);
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: var(--primary);
    border: 3px solid var(--white);
    box-shadow: 0 0 0 2px var(--primary-light);
    z-index: 2;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 9px;
    top: 20px;
    height: calc(100% + 5px);
    width: 2px;
    background-color: var(--primary-light);
    z-index: 1;
}

.timeline-content {
    background-color: var(--white);
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.timeline-date {
    font-size: 12px;
    color: var(--secondary);
}

.search-form {
    background-color: var(--white);
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    margin-bottom: 30px;
}

.search-form .form-control {
    border-radius: 8px;
    padding: 12px 15px;
    border: 1px solid rgba(0,0,0,0.1);
}

.search-form .btn {
    padding: 12px 20px;
    border-radius: 8px;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: var(--light);
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
}

.step-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--light);
    border: 2px solid var(--secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
}

.step.active .step-icon {
    background-color: var(--primary);
    border-color: var(--primary);
    color: var(--white);
}

.step.completed .step-icon {
    background-color: var(--success);
    border-color: var(--success);
    color: var(--white);
}

.step-label {
    font-size: 12px;
    color: var(--secondary);
}

.step.active .step-label {
    color: var(--primary);
    font-weight: 500;
}

.step.completed .step-label {
    color: var(--success);
    font-weight: 500;
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}

.status-indicator.soumise { background-color: var(--secondary); }
.status-indicator.encours { background-color: var(--primary); }
.status-indicator.approuvee { background-color: var(--success); }
.status-indicator.rejetee { background-color: var(--danger); }
.status-indicator.terminee { background-color: var(--info); }
.status-indicator.annulee { background-color: var(--warning); }

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    padding: 10px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
}

.info-list li:last-child {
    border-bottom: none;
}

.info-list .info-label {
    width: 40%;
    font-weight: 500;
    color: var(--dark);
}

.info-list .info-value {
    width: 60%;
    color: var(--secondary);
}

.document-card {
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
    transition: var(--transition);
}

.document-card:hover {
    transform: translateY(-5px);
}

.document-preview {
    height: 150px;
    background-color: var(--light);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.document-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.document-preview .document-icon {
    font-size: 3rem;
    color: var(--secondary);
}

.document-info {
    padding: 15px;
}

.document-actions {
    padding: 10px 15px;
    background-color: var(--light);
    border-top: 1px solid rgba(0,0,0,0.05);
}

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="dashboard-container">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Suivi de demande</h2>
            <a href="mes_demandes.php" class="btn btn-outline-secondary btn-action">
                <i class="bi bi-arrow-left me-2"></i>Retour à mes demandes
            </a>
        </div>
        
        <!-- Formulaire de recherche -->
        <div class="search-form">
            <form method="GET" action="suivi_demande.php">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="reference" class="form-label">Numéro de référence</label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="Ex: CNI-20250316-A4518E" value="<?php echo htmlspecialchars($reference); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($demande): ?>
        <!-- Résultat de la recherche -->
        <div class="card mb-4">
            <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Informations de la demande</h5>
                    <span class="badge <?php 
                        $statusClasses = [
                            'Soumise' => 'bg-secondary',
                            'EnCours' => 'bg-primary',
                            'Approuvee' => 'bg-success',
                            'Rejetee' => 'bg-danger',
                            'Terminee' => 'bg-info',
                            'Annulee' => 'bg-warning'
                        ];
                        echo $statusClasses[$demande['Statut']] ?? 'bg-secondary'; 
                    ?> status-badge">
                        <?php 
                        $statusLabels = [
                            'Soumise' => 'Soumise',
                            'EnCours' => 'En cours de traitement',
                            'Approuvee' => 'Approuvée',
                            'Rejetee' => 'Rejetée',
                            'Terminee' => 'Terminée (à retirer)',
                            'Annulee' => 'Annulée'
                        ];
                        echo $statusLabels[$demande['Statut']] ?? $demande['Statut']; 
                        ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="info-list">
                            <li>
                                <div class="info-label">Référence</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['NumeroReference']); ?></div>
                            </li>
                            <li>
                                <div class="info-label">Type de demande</div>
                                <div class="info-value">
                                    <?php if ($demande['TypeDemande'] == 'CNI'): ?>
                                        <i class="bi bi-person-badge me-2 text-primary"></i>Carte Nationale d'Identité
                                    <?php elseif ($demande['TypeDemande'] == 'NATIONALITE'): ?>
                                        <i class="bi bi-file-earmark-text me-2 text-success"></i>Certificat de Nationalité
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php if ($demande['TypeDemande'] == 'CNI' && !empty($demande['SousTypeDemande'])): ?>
                            <li>
                                <div class="info-label">Sous-type</div>
                                <div class="info-value">
                                    <?php 
                                    $sousTypes = [
                                        'premiere' => 'Première demande',
                                        'renouvellement' => 'Renouvellement',
                                        'perte' => 'Perte/Vol'
                                    ];
                                    echo $sousTypes[$demande['SousTypeDemande']] ?? $demande['SousTypeDemande'];
                                    ?>
                                </div>
                            </li>
                            <?php endif; ?>
                            <li>
                                <div class="info-label">Demandeur</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="info-list">
                            <li>
                                <div class="info-label">Date de soumission</div>
                                <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($demande['DateSoumission'])); ?></div>
                            </li>
                            <?php if (!empty($demande['DateAchevement'])): ?>
                            <li>
                                <div class="info-label">Date d'achèvement</div>
                                <div class="info-value"><?php echo date('d/m/Y', strtotime($demande['DateAchevement'])); ?></div>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($demande['MontantPaiement'])): ?>
                            <li>
                                <div class="info-label">Montant</div>
                                <div class="info-value"><?php echo number_format($demande['MontantPaiement'], 0, ',', ' '); ?> FCFA</div>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($demande['StatutPaiement'])): ?>
                            <li>
                                <div class="info-label">Statut du paiement</div>
                                <div class="info-value">
                                    <?php if ($demande['StatutPaiement'] == 'Complete'): ?>
                                        <span class="badge bg-success">Payé</span>
                                    <?php elseif ($demande['StatutPaiement'] == 'EnAttente'): ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php elseif ($demande['StatutPaiement'] == 'Echoue'): ?>
                                        <span class="badge bg-danger">Échoué</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo $demande['StatutPaiement']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Indicateur d'étape -->
                <div class="mt-4">
                    <h5 class="mb-3">Progression de la demande</h5>
                    <div class="step-indicator">
                        <div class="step <?php echo in_array($demande['Statut'], ['Soumise', 'EnCours', 'Approuvee', 'Terminee']) ? 'completed' : ''; ?>">
                            <div class="step-icon">
                                <i class="bi bi-check"></i>
                            </div>
                            <div class="step-label">Soumission</div>
                        </div>
                        <div class="step <?php echo in_array($demande['Statut'], ['EnCours', 'Approuvee', 'Terminee']) ? 'completed' : ($demande['Statut'] == 'Soumise' ? 'active' : ''); ?>">
                            <div class="step-icon">
                                <i class="bi bi-gear"></i>
                            </div>
                            <div class="step-label">Traitement</div>
                        </div>
                        <div class="step <?php echo in_array($demande['Statut'], ['Approuvee', 'Terminee']) ? 'completed' : ($demande['Statut'] == 'EnCours' ? 'active' : ''); ?>">
                            <div class="step-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="step-label">Approbation</div>
                        </div>
                        <div class="step <?php echo $demande['Statut'] == 'Terminee' ? 'completed' : ($demande['Statut'] == 'Approuvee' ? 'active' : ''); ?>">
                            <div class="step-icon">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="step-label">Disponible</div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions disponibles -->
                <div class="mt-4">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="details_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-primary btn-action">
                            <i class="bi bi-eye me-2"></i>Voir les détails complets
                        </a>
                        
                        <?php if ($demande['Statut'] == 'Soumise'): ?>
                        <a href="modifier_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-outline-primary btn-action">
                            <i class="bi bi-pencil me-2"></i>Modifier
                        </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($demande['Statut'], ['Approuvee', 'Terminee'])): ?>
                        <a href="telecharger_document.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-success btn-action">
                            <i class="bi bi-download me-2"></i>Télécharger
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($demande['Statut'] == 'Rejetee'): ?>
                        <a href="reclamation.php?demande=<?php echo $demande['DemandeID']; ?>" class="btn btn-warning btn-action">
                            <i class="bi bi-exclamation-circle me-2"></i>Faire une réclamation
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Historique de la demande -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Historique de la demande</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($historique)): ?>
                <div class="timeline">
                    <?php foreach($historique as $index => $event): ?>
                    <div class="timeline-item animate-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">
                                    <?php 
                                    $statusChanges = [
                                        'Soumise' => 'Demande soumise',
                                        'EnCours' => 'Traitement en cours',
                                        'Approuvee' => 'Demande approuvée',
                                        'Rejetee' => 'Demande rejetée',
                                        'Terminee' => 'Document prêt à retirer',
                                        'Annulee' => 'Demande annulée'
                                    ];
                                    echo $statusChanges[$event['NouveauStatut']] ?? 'Statut changé en ' . $event['NouveauStatut'];
                                    ?>
                                </h6>
                                <span class="badge <?php echo $statusClasses[$event['NouveauStatut']] ?? 'bg-secondary'; ?>">
                                    <?php echo $statusLabels[$event['NouveauStatut']] ?? $event['NouveauStatut']; ?>
                                </span>
                            </div>
                            <p class="mb-1">
                                <?php if (!empty($event['Commentaire'])): ?>
                                    <?php echo htmlspecialchars($event['Commentaire']); ?>
                                <?php else: ?>
                                    <?php if ($event['NouveauStatut'] == 'Soumise'): ?>
                                        Votre demande a été soumise avec succès.
                                    <?php elseif ($event['NouveauStatut'] == 'EnCours'): ?>
                                        Votre demande est maintenant en cours de traitement par nos services.
                                    <?php elseif ($event['NouveauStatut'] == 'Approuvee'): ?>
                                        Votre demande a été approuvée.
                                    <?php elseif ($event['NouveauStatut'] == 'Rejetee'): ?>
                                        Votre demande a été rejetée.
                                        <?php elseif ($event['NouveauStatut'] == 'Terminee'): ?>
                                        Votre document est prêt et disponible pour retrait.
                                    <?php elseif ($event['NouveauStatut'] == 'Annulee'): ?>
                                        La demande a été annulée.
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="timeline-date">
                                    <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y à H:i', strtotime($event['DateModification'])); ?>
                                </span>
                                <?php if (!empty($event['Nom']) && !empty($event['Prenom'])): ?>
                                <span class="text-muted small">
                                    <i class="bi bi-person me-1"></i>
                                    <?php 
                                    $roles = [1 => 'Admin', 2 => 'Citoyen', 3 => 'Officier', 4 => 'Président'];
                                    echo htmlspecialchars($event['Prenom'] . ' ' . $event['Nom']); 
                                    if (isset($event['RoleId']) && isset($roles[$event['RoleId']])) {
                                        echo ' (' . $roles[$event['RoleId']] . ')';
                                    }
                                    ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3">Aucun historique disponible pour cette demande.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Documents fournis -->
        <?php if (!empty($documents)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Documents fournis</h5>
            </div>
            <div class="card-body">
                <div class="document-grid">
                    <?php 
                    $docLabels = [
                        'Photo' => 'Photo d\'identité',
                        'PhotoIdentite' => 'Photo d\'identité',
                        'ActeNaissance' => 'Acte de naissance',
                        'CertificatNationalite' => 'Certificat de nationalité',
                        'AncienneCNI' => 'Ancienne CNI',
                        'ActeMariage' => 'Acte de mariage',
                        'JustificatifProfession' => 'Justificatif de profession',
                        'DecretNaturalisation' => 'Décret de naturalisation',
                        'CasierJudiciaire' => 'Casier judiciaire',
                        'DeclarationPerte' => 'Déclaration de perte',
                        'Signature' => 'Signature'
                    ];
                    
                    $docIcons = [
                        'Photo' => 'bi-person-square',
                        'PhotoIdentite' => 'bi-person-square',
                        'ActeNaissance' => 'bi-file-earmark-text',
                        'CertificatNationalite' => 'bi-file-earmark-check',
                        'AncienneCNI' => 'bi-person-badge',
                        'ActeMariage' => 'bi-file-earmark-text',
                        'JustificatifProfession' => 'bi-briefcase',
                        'DecretNaturalisation' => 'bi-file-earmark-text',
                        'CasierJudiciaire' => 'bi-file-earmark-text',
                        'DeclarationPerte' => 'bi-exclamation-triangle',
                        'Signature' => 'bi-pen'
                    ];
                    
                    foreach($documents as $document): 
                        $docType = $document['TypeDocument'];
                        $docLabel = $docLabels[$docType] ?? $docType;
                        $docIcon = $docIcons[$docType] ?? 'bi-file-earmark';
                        $isImage = in_array(pathinfo($document['CheminFichier'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
                        $isPdf = pathinfo($document['CheminFichier'], PATHINFO_EXTENSION) == 'pdf';
                    ?>
                    <div class="document-card">
                        <div class="document-preview">
                            <?php if ($isImage && file_exists($document['CheminFichier'])): ?>
                                <img src="<?php echo $document['CheminFichier']; ?>" alt="<?php echo $docLabel; ?>">
                            <?php elseif ($isPdf): ?>
                                <div class="document-icon text-danger">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                            <?php else: ?>
                                <div class="document-icon">
                                    <i class="bi <?php echo $docIcon; ?>"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="document-info">
                            <h6><?php echo $docLabel; ?></h6>
                            <p class="text-muted small mb-0">
                                Téléchargé le <?php echo date('d/m/Y', strtotime($document['DateTelechargement'])); ?>
                            </p>
                            <span class="badge <?php echo $document['StatutValidation'] == 'Approuve' ? 'bg-success' : ($document['StatutValidation'] == 'Rejete' ? 'bg-danger' : 'bg-secondary'); ?>">
                                <?php echo $document['StatutValidation'] == 'Approuve' ? 'Validé' : ($document['StatutValidation'] == 'Rejete' ? 'Rejeté' : 'En attente'); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Paiements -->
        <?php if (!empty($paiements)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Paiements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Référence</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($paiements as $paiement): ?>
                            <tr>
                                <td><?php echo date('d/m/Y à H:i', strtotime($paiement['DatePaiement'])); ?></td>
                                <td><?php echo number_format($paiement['Montant'], 0, ',', ' '); ?> FCFA</td>
                                <td><?php echo htmlspecialchars($paiement['ReferenceTransaction']); ?></td>
                                <td>
                                    <span class="badge <?php echo $paiement['StatutPaiement'] == 'Complete' ? 'bg-success' : ($paiement['StatutPaiement'] == 'EnAttente' ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?php echo $paiement['StatutPaiement'] == 'Complete' ? 'Payé' : ($paiement['StatutPaiement'] == 'EnAttente' ? 'En attente' : 'Échoué'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($demande['StatutPaiement'] == 'EnAttente'): ?>
                <div class="mt-3">
                    <a href="paiement.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>Effectuer le paiement
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Rendez-vous -->
        <?php if (!empty($rendezvous)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Rendez-vous</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rendezvous as $rdv): ?>
                            <tr>
                                <td><?php echo date('d/m/Y à H:i', strtotime($rdv['DateRendezVous'])); ?></td>
                                <td><?php echo htmlspecialchars($rdv['Lieu']); ?></td>
                                <td>
                                    <span class="badge <?php echo $rdv['Statut'] == 'Planifie' ? 'bg-primary' : ($rdv['Statut'] == 'Termine' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo $rdv['Statut'] == 'Planifie' ? 'Planifié' : ($rdv['Statut'] == 'Termine' ? 'Terminé' : 'Annulé'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($demande['Statut'] == 'Terminee'): ?>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    N'oubliez pas de vous munir d'une pièce d'identité pour le retrait.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Aucune demande trouvée ou pas encore recherchée -->
        <?php if (empty($reference)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-search text-primary" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Recherchez votre demande</h4>
                <p class="text-muted">
                    Entrez le numéro de référence de votre demande pour suivre son état d'avancement.
                    <br>Le numéro de référence se trouve sur votre récépissé de demande.
                </p>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Comment suivre votre demande ?</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="bg-light p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5>1. Trouvez votre référence</h5>
                        <p class="text-muted">
                            Le numéro de référence se trouve sur le récépissé qui vous a été remis lors de votre demande.
                        </p>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="bg-light p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-search text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5>2. Entrez la référence</h5>
                        <p class="text-muted">
                            Saisissez le numéro de référence dans le champ de recherche ci-dessus.
                        </p>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="bg-light p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-circle text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h5>3. Suivez votre demande</h5>
                        <p class="text-muted">
                            Consultez l'état d'avancement de votre demande et les prochaines étapes.
                        </p>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Besoin d'aide ?</strong> Si vous ne trouvez pas votre numéro de référence ou si vous rencontrez des difficultés, contactez notre service d'assistance au <strong>+237 XXX XXX XXX</strong> ou par email à <strong>assistance@cni.gov.cm</strong>.
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des éléments de la timeline
    const timelineItems = document.querySelectorAll('.timeline-item');
    
    // Observer pour animer les éléments quand ils deviennent visibles
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    timelineItems.forEach(item => {
        observer.observe(item);
    });
    
    // Validation du formulaire de recherche
    const searchForm = document.querySelector('form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(event) {
            const referenceInput = document.getElementById('reference');
            if (!referenceInput.value.trim()) {
                event.preventDefault();
                alert('Veuillez entrer un numéro de référence.');
                referenceInput.focus();
            }
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
