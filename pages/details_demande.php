<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$demandeId) {
    header('Location: mes_demandes.php');
    exit();
}

// Récupération des détails de la demande
$query = "SELECT d.*, 
          CASE 
              WHEN d.TypeDemande = 'CNI' THEN dc.Nom 
              WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Nom 
          END as Nom,
          CASE 
              WHEN d.TypeDemande = 'CNI' THEN dc.Prenom 
              WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Prenom 
          END as Prenom,
          CASE 
              WHEN d.TypeDemande = 'CNI' THEN dc.DateNaissance 
              WHEN d.TypeDemande = 'NATIONALITE' THEN dn.DateNaissance 
          END as DateNaissance,
          CASE 
              WHEN d.TypeDemande = 'CNI' THEN dc.LieuNaissance 
              WHEN d.TypeDemande = 'NATIONALITE' THEN dn.LieuNaissance 
          END as LieuNaissance,
          CASE 
              WHEN d.TypeDemande = 'CNI' THEN dc.Adresse 
              WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Adresse 
          END as Adresse,
          dc.Profession, dc.Taille, dc.Sexe, dc.StatutCivil, dc.NumeroCNIPrecedente,
          dn.NomPere, dn.NomMere, dn.Motif, dn.Telephone,
          u.Email, u.NumeroTelephone, u.PhotoUtilisateur
          FROM demandes d
          LEFT JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
          LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID
          LEFT JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.DemandeID = :id AND d.UtilisateurID = :userId";

$stmt = $db->prepare($query);
$stmt->execute([
    'id' => $demandeId,
    'userId' => $_SESSION['user_id']
]);
$demande = $stmt->fetch();

// Vérification que la demande existe et appartient à l'utilisateur
if (!$demande) {
    $_SESSION['error_message'] = "La demande demandée n'existe pas ou vous n'avez pas les droits pour y accéder.";
    header('Location: mes_demandes.php');
    exit();
}

// Récupération des documents
$query = "SELECT * FROM documents WHERE DemandeID = :id ORDER BY TypeDocument";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$documents = $stmt->fetchAll();

// Récupération de l'historique
$query = "SELECT h.*, u.Nom, u.Prenom, u.RoleId
          FROM historique_demandes h
          LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
          WHERE h.DemandeID = :id 
          ORDER BY h.DateModification DESC";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$historique = $stmt->fetchAll();

// Récupération des rendez-vous associés
$query = "SELECT * FROM rendezvous WHERE DemandeID = :id ORDER BY DateRendezVous";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$rendezvous = $stmt->fetchAll();

// Récupération des paiements
$query = "SELECT * FROM paiements WHERE DemandeID = :id ORDER BY DatePaiement DESC";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$paiements = $stmt->fetchAll();

// Récupération des réclamations
$query = "SELECT * FROM reclamations WHERE DemandeID = :id ORDER BY DateCreation DESC";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$reclamations = $stmt->fetchAll();

// Récupération de la carte d'identité si disponible
$cniQuery = "SELECT * FROM cartesidentite WHERE DemandeID = :id";
$stmt = $db->prepare($cniQuery);
$stmt->execute(['id' => $demandeId]);
$cni = $stmt->fetch();

// Récupération du certificat de nationalité si disponible
$certQuery = "SELECT * FROM certificatsnationalite WHERE DemandeID = :id";
$stmt = $db->prepare($certQuery);
$stmt->execute(['id' => $demandeId]);
$certificat = $stmt->fetch();

// Vérifier si la signature a été enregistrée
$signatureEnregistree = false;
if (isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1) {
    $signatureEnregistree = true;
}

// Vérifier si un document de signature existe
$signatureDocument = null;
foreach($documents as $doc) {
    if($doc['TypeDocument'] == 'Signature') {
        $signatureDocument = $doc;
        break;
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

.document-card {
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
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

.nav-pills .nav-link {
    border-radius: 8px;
    padding: 10px 20px;
    margin-right: 5px;
    color: var(--dark);
}

.nav-pills .nav-link.active {
    background-color: var(--primary);
    color: var(--white);
}

.profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 20px;
    border: 3px solid var(--white);
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h2 {
    margin-bottom: 5px;
}

.profile-info p {
    margin-bottom: 0;
    color: var(--secondary);
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

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
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

.qr-code {
    text-align: center;
    padding: 20px;
    background-color: var(--white);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.qr-code img {
    max-width: 100%;
    height: auto;
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Styles pour la zone de signature */
#signature-pad {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    background-color: #fff;
    width: 100%;
    height: 200px;
    margin-bottom: 15px;
}

.signature-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}
</style>

<div class="dashboard-container">
    <div class="container">
        <!-- En-tête avec informations de base -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Détails de la demande</h2>
                    <a href="mes_demandes.php" class="btn btn-outline-secondary btn-action">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                    <div class="profile-header">
                            <div class="profile-avatar">
                                <?php if (!empty($demande['PhotoUtilisateur']) && file_exists($demande['PhotoUtilisateur'])): ?>
                                    <img src="<?php echo $demande['PhotoUtilisateur']; ?>" alt="Photo de profil">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-primary text-white">
                                        <i class="bi bi-person-fill" style="font-size: 2rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="profile-info">
                                <h3><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></h3>
                                <p>
                                    <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($demande['Email']); ?><br>
                                    <i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($demande['NumeroTelephone']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h5 class="mb-3">Informations de la demande</h5>
                                    <ul class="info-list">
                                        <li>
                                            <div class="info-label">Référence</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['NumeroReference'] ?? 'N/A'); ?></div>
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
                                            <div class="info-label">Date de soumission</div>
                                            <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($demande['DateSoumission'])); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Statut</div>
                                            <div class="info-value">
                                                <?php
                                                $statusClasses = [
                                                    'Soumise' => 'bg-secondary',
                                                    'EnCours' => 'bg-primary',
                                                    'Approuvee' => 'bg-success',
                                                    'Rejetee' => 'bg-danger',
                                                    'Terminee' => 'bg-info',
                                                    'Annulee' => 'bg-warning'
                                                ];
                                                $statusLabels = [
                                                    'Soumise' => 'Soumise',
                                                    'EnCours' => 'En cours de traitement',
                                                    'Approuvee' => 'Approuvée',
                                                    'Rejetee' => 'Rejetée',
                                                    'Terminee' => 'Terminée (à retirer)',
                                                    'Annulee' => 'Annulée'
                                                ];
                                                $statusClass = $statusClasses[$demande['Statut']] ?? 'bg-secondary';
                                                $statusLabel = $statusLabels[$demande['Statut']] ?? $demande['Statut'];
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> status-badge">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </div>
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
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h5 class="mb-3">Informations personnelles</h5>
                                    <ul class="info-list">
                                        <li>
                                            <div class="info-label">Nom complet</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Date de naissance</div>
                                            <div class="info-value"><?php echo date('d/m/Y', strtotime($demande['DateNaissance'])); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Lieu de naissance</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['LieuNaissance']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Adresse</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['Adresse']); ?></div>
                                        </li>
                                        
                                        <?php if ($demande['TypeDemande'] == 'CNI'): ?>
                                        <li>
                                            <div class="info-label">Sexe</div>
                                            <div class="info-value"><?php echo $demande['Sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Taille</div>
                                            <div class="info-value"><?php echo $demande['Taille']; ?> cm</div>
                                        </li>
                                        <li>
                                            <div class="info-label">Profession</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['Profession']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Statut civil</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['StatutCivil']); ?></div>
                                        </li>
                                        <?php if (!empty($demande['NumeroCNIPrecedente'])): ?>
                                        <li>
                                            <div class="info-label">N° CNI précédente</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['NumeroCNIPrecedente']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($demande['TypeDemande'] == 'NATIONALITE'): ?>
                                        <li>
                                            <div class="info-label">Nom du père</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['NomPere']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Nom de la mère</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['NomMere']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Téléphone</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['Telephone']); ?></div>
                                        </li>
                                        <li>
                                            <div class="info-label">Motif</div>
                                            <div class="info-value"><?php echo htmlspecialchars($demande['Motif']); ?></div>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
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
                        </div>
                        
                        <!-- Actions disponibles -->
                        <div class="mt-4">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="suivi_demande.php?reference=<?php echo urlencode($demande['NumeroReference']); ?>" class="btn btn-primary btn-action">
                                    <i class="bi bi-clock-history me-2"></i>Suivi détaillé
                                </a>
                                
                                <?php if ($demande['Statut'] == 'Soumise'): ?>
                                <a href="modifier_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-primary btn-action">
                                    <i class="bi bi-pencil me-2"></i>Modifier
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-action" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-circle me-2"></i>Annuler
                                </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($demande['Statut'], ['Approuvee', 'Terminee'])): ?>
                                <a href="../telecharger_document.php?id=<?php echo $demandeId; ?>" class="btn btn-success btn-action">
                                    <i class="bi bi-download me-2"></i>Télécharger
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($demande['Statut'] == 'Rejetee'): ?>
                                    <a href="reclamation.php?demande=<?php echo $demandeId; ?>" class="btn btn-warning btn-action">
                                    <i class="bi bi-exclamation-circle me-2"></i>Faire une réclamation
                                </a>
                                <?php endif; ?>
                                
                                <a href="imprimer_recapitulatif.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-secondary btn-action" target="_blank">
                                    <i class="bi bi-printer me-2"></i>Imprimer
                                </a>
                            </div>
                        </div>
                        
<?php if ($demande['Statut'] == 'Approuvee' && isset($demande['SignatureRequise']) && $demande['SignatureRequise'] == 1 && (!isset($demande['SignatureEnregistree']) || $demande['SignatureEnregistree'] == 0)): ?>
    <div class="mt-4">
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Signature requise :</strong> Veuillez enregistrer votre signature pour finaliser votre demande de CNI.
        </div>
        
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Enregistrer votre signature</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Veuillez signer dans la zone ci-dessous. Votre signature sera utilisée sur votre Carte Nationale d'Identité.</p>
                
                <canvas id="signature-pad" class="signature-pad"></canvas>
                
                <div class="signature-actions">
                    <button id="clear-signature" class="btn btn-outline-secondary">
                        <i class="bi bi-eraser me-1"></i> Effacer
                    </button>
                    <button id="save-signature" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Enregistrer ma signature
                    </button>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Important :</strong> Assurez-vous que votre signature soit claire et lisible. Une fois enregistrée, elle ne pourra plus être modifiée.
                </div>
            </div>
        </div>
    </div>
<?php elseif ($demande['Statut'] == 'Approuvee' && isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1): ?>
    <div class="mt-4">
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Signature enregistrée :</strong> Votre signature a été enregistrée avec succès. Votre CNI est en cours de production.
        </div>
        
        <?php if($signatureDocument): ?>
        <div class="mt-3 text-center">
            <p class="mb-2">Aperçu de votre signature :</p>
            <img src="<?php echo htmlspecialchars($signatureDocument['CheminFichier']); ?>" alt="Votre signature" class="img-fluid border rounded" style="max-height: 100px;">
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

                    </div>
                </div>
            </div>
            
            <!-- Carte latérale avec statut et QR code si disponible -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Statut actuel</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $statusIcons = [
                            'Soumise' => '<i class="bi bi-hourglass-top text-secondary" style="font-size: 3rem;"></i>',
                            'EnCours' => '<i class="bi bi-gear-wide-connected text-primary" style="font-size: 3rem;"></i>',
                            'Approuvee' => '<i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>',
                            'Rejetee' => '<i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>',
                            'Terminee' => '<i class="bi bi-award-fill text-info" style="font-size: 3rem;"></i>',
                            'Annulee' => '<i class="bi bi-slash-circle-fill text-warning" style="font-size: 3rem;"></i>'
                        ];
                        $statusMessages = [
                            'Soumise' => 'Votre demande a été soumise et est en attente de traitement.',
                            'EnCours' => 'Votre demande est en cours de traitement par nos services.',
                            'Approuvee' => 'Votre demande a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.',
                            'Rejetee' => 'Votre demande a été rejetée. Veuillez consulter les détails pour plus d\'informations.',
                            'Terminee' => 'Votre document est prêt et disponible pour retrait.',
                            'Annulee' => 'Cette demande a été annulée.'
                        ];
                        
                        echo $statusIcons[$demande['Statut']] ?? '<i class="bi bi-question-circle text-secondary" style="font-size: 3rem;"></i>';
                        ?>
                        
                        <h4 class="mt-3"><?php echo $statusLabels[$demande['Statut']] ?? $demande['Statut']; ?></h4>
                        <p class="text-muted"><?php echo $statusMessages[$demande['Statut']] ?? ''; ?></p>
                        
                        <?php if (in_array($demande['Statut'], ['Approuvee', 'Terminee'])): ?>
                            <?php if ($demande['TypeDemande'] == 'CNI' && $cni): ?>
                                <div class="mt-4 qr-code">
                                    <h6>QR Code de vérification</h6>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($cni['NumeroCarteIdentite']); ?>" alt="QR Code">
                                    <p class="small text-muted mt-2">Scannez ce code pour vérifier l'authenticité</p>
                                </div>
                            <?php elseif ($demande['TypeDemande'] == 'NATIONALITE' && $certificat): ?>
                                <div class="mt-4 qr-code">
                                    <h6>QR Code de vérification</h6>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($certificat['NumeroCertificat']); ?>" alt="QR Code">
                                    <p class="small text-muted mt-2">Scannez ce code pour vérifier l'authenticité</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($rendezvous)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Rendez-vous</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($rendezvous as $rdv): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 me-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo date('d/m/Y à H:i', strtotime($rdv['DateRendezVous'])); ?></h6>
                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($rdv['Lieu']); ?></p>
                                <span class="badge <?php echo $rdv['Statut'] == 'Planifie' ? 'bg-primary' : ($rdv['Statut'] == 'Termine' ? 'bg-success' : 'bg-warning'); ?>">
                                    <?php echo $rdv['Statut'] == 'Planifie' ? 'Planifié' : ($rdv['Statut'] == 'Termine' ? 'Terminé' : 'Annulé'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($demande['Statut'] == 'Terminee'): ?>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            N'oubliez pas de vous munir d'une pièce d'identité pour le retrait.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($paiements)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Paiements</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($paiements as $paiement): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 me-3">
                                <div class="<?php echo $paiement['StatutPaiement'] == 'Complete' ? 'bg-success bg-opacity-10 text-success' : ($paiement['StatutPaiement'] == 'EnAttente' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger'); ?> rounded-circle p-3">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo number_format($paiement['Montant'], 0, ',', ' '); ?> FCFA</h6>
                                <p class="mb-0 text-muted"><?php echo date('d/m/Y à H:i', strtotime($paiement['DatePaiement'])); ?></p>
                                <span class="badge <?php echo $paiement['StatutPaiement'] == 'Complete' ? 'bg-success' : ($paiement['StatutPaiement'] == 'EnAttente' ? 'bg-warning' : 'bg-danger'); ?>">
                                    <?php echo $paiement['StatutPaiement'] == 'Complete' ? 'Payé' : ($paiement['StatutPaiement'] == 'EnAttente' ? 'En attente' : 'Échoué'); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($demande['StatutPaiement'] == 'EnAttente'): ?>
                        <div class="mt-3">
                            <a href="paiement.php?id=<?php echo $demandeId; ?>" class="btn btn-primary w-100">
                                <i class="bi bi-credit-card me-2"></i>Effectuer le paiement
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Onglets pour les documents et l'historique -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-pills mb-4" id="detailsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="true">
                            <i class="bi bi-file-earmark me-2"></i>Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#historique" type="button" role="tab" aria-controls="historique" aria-selected="false">
                            <i class="bi bi-clock-history me-2"></i>Historique
                        </button>
                    </li>
                    <?php if (!empty($reclamations)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reclamations-tab" data-bs-toggle="tab" data-bs-target="#reclamations" type="button" role="tab" aria-controls="reclamations" aria-selected="false">
                            <i class="bi bi-exclamation-circle me-2"></i>Réclamations
                        </button>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="tab-content" id="detailsTabContent">
                    <!-- Onglet Documents -->
                    <div class="tab-pane fade show active" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Documents fournis</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($documents)): ?>
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
                                        // Ne pas afficher le document de signature s'il n'est pas encore validé
                                        if($document['TypeDocument'] == 'Signature' && !$signatureEnregistree) {
                                            continue;
                                        }
                                        
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
                                        <div class="document-actions">
                                            <a href="<?php echo $document['CheminFichier']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-eye me-1"></i>Voir
                                            </a>
                                            <a href="telecharger_document.php?doc=<?php echo $document['DocumentID']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download me-1"></i>Télécharger
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Aucun document n'a été trouvé pour cette demande.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Historique -->
                    <div class="tab-pane fade" id="historique" role="tabpanel" aria-labelledby="historique-tab">
                        <div class="card">
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
                                                        Votre demande a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.
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
                    </div>
                    
                    <!-- Onglet Réclamations -->
                    <?php if (!empty($reclamations)): ?>
                    <div class="tab-pane fade" id="reclamations" role="tabpanel" aria-labelledby="reclamations-tab">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Réclamations</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach($reclamations as $reclamation): ?>
                                <div class="card mb-3 border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <?php echo htmlspecialchars($reclamation['TypeReclamation']); ?>
                                        </h6>
                                        <span class="badge <?php echo $reclamation['Statut'] == 'Ouverte' ? 'bg-danger' : ($reclamation['Statut'] == 'EnCours' ? 'bg-warning' : 'bg-success'); ?>">
                                            <?php echo $reclamation['Statut'] == 'Ouverte' ? 'Ouverte' : ($reclamation['Statut'] == 'EnCours' ? 'En cours' : 'Fermée'); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo nl2br(htmlspecialchars($reclamation['Description'])); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>Soumise le <?php echo date('d/m/Y', strtotime($reclamation['DateCreation'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>Dernière mise à jour: <?php echo date('d/m/Y', strtotime($reclamation['DateMiseAJour'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if ($demande['Statut'] == 'Rejetee'): ?>
                                <div class="mt-3">
                                    <a href="reclamation.php?demande=<?php echo $demandeId; ?>" class="btn btn-warning">
                                        <i class="bi bi-plus-circle me-2"></i>Nouvelle réclamation
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer l'annulation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                </div>
                <p>Êtes-vous sûr de vouloir annuler cette demande ?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Attention :</strong> Cette action est irréversible. Une fois annulée, vous devrez soumettre une nouvelle demande si nécessaire.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="annuler_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-danger">
                    <i class="bi bi-x-circle me-2"></i>Confirmer l'annulation
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
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
    
    // Gestion des onglets avec stockage dans l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab) {
        const tabElement = document.querySelector(`#${tab}-tab`);
        if (tabElement) {
            const tabInstance = new bootstrap.Tab(tabElement);
            tabInstance.show();
        }
    }
    
    // Mise à jour de l'URL lors du changement d'onglet
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            const id = event.target.id.replace('-tab', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', id);
            window.history.replaceState({}, '', url);
        });
    });

    // Initialisation du pad de signature si présent
    const signaturePadElement = document.getElementById('signature-pad');
    if (signaturePadElement) {
        const signaturePad = new SignaturePad(signaturePadElement, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Bouton pour effacer la signature
        document.getElementById('clear-signature').addEventListener('click', function() {
            signaturePad.clear();
        });

        // Bouton pour enregistrer la signature
        document.getElementById('save-signature').addEventListener('click', function() {
            if (signaturePad.isEmpty()) {
                alert('Veuillez signer avant de soumettre.');
                return;
            }

            // Récupérer l'image de la signature en base64
            const signatureData = signaturePad.toDataURL();
            
            // Envoyer la signature au serveur
            fetch('enregistrer_signature_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `demande_id=${<?php echo $demandeId; ?>}&signature=${encodeURIComponent(signatureData)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Votre signature a été enregistrée avec succès.');
                    // Recharger la page pour afficher les changements
                    window.location.reload();
                } else {
                    alert(data.message || 'Une erreur est survenue lors de l\'enregistrement de votre signature.');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la communication avec le serveur.');
            });
        });

        // Adapter la taille du canvas au redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            signaturePadElement.width = signaturePadElement.offsetWidth * ratio;
            signaturePadElement.height = signaturePadElement.offsetHeight * ratio;
            signaturePadElement.getContext("2d").scale(ratio, ratio);
            signaturePad.clear(); // Effacer le contenu après redimensionnement
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
