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

// Requête pour récupérer les documents
$query = "
    SELECT 
        c.CarteID as ID, 
        c.NumeroCarteIdentite as Numero, 
        c.DateEmission, 
        c.DateExpiration, 
        c.CodeQR, 
        c.CheminFichier, 
        c.Statut, 
        'CNI' as TypeDocument,
        d.DateSoumission,
        d.TypeDemande,
        d.DemandeID
    FROM cartesidentite c 
    JOIN demandes d ON c.DemandeID = d.DemandeID 
    WHERE d.UtilisateurID = :userId

    UNION ALL

    SELECT 
        n.CertificatID as ID, 
        n.NumeroCertificat as Numero, 
        n.DateEmission, 
        NULL as DateExpiration, 
        NULL as CodeQR, 
        n.CheminPDF as CheminFichier, 
        NULL as Statut, 
        'Certificat Nationalité' as TypeDocument,
        d.DateSoumission,
        d.TypeDemande,
        d.DemandeID
    FROM certificatsnationalite n 
    JOIN demandes d ON n.DemandeID = d.DemandeID 
    WHERE d.UtilisateurID = :userId

    ORDER BY DateEmission DESC
";

$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$documents = $stmt->fetchAll();

// Récupération des statistiques
$stats = [
    'cni_active' => 0,
    'cni_expiree' => 0,
    'cni_perdue' => 0,
    'certificats' => 0,
    'total' => 0
];

// Calcul des statistiques
foreach ($documents as $doc) {
    $stats['total']++;
    
    if ($doc['TypeDocument'] == 'CNI') {
        if ($doc['Statut'] == 'Active') {
            $stats['cni_active']++;
        } elseif ($doc['Statut'] == 'Expiree') {
            $stats['cni_expiree']++;
        } elseif ($doc['Statut'] == 'Perdue') {
            $stats['cni_perdue']++;
        }
    } else {
        $stats['certificats']++;
    }
}

// Récupération des demandes en cours
$query = "
    SELECT d.DemandeID, d.NumeroReference, d.TypeDemande, d.Statut, d.DateSoumission
    FROM demandes d
    WHERE d.UtilisateurID = :userId
    AND d.Statut IN ('Soumise', 'EnCours', 'Approuvee')
    ORDER BY d.DateSoumission DESC
    LIMIT 3
";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$demandesEnCours = $stmt->fetchAll();

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

.stat-card {
    border: none;
    border-radius: var(--border-radius);
    transition: var(--transition);
    background: linear-gradient(145deg, #ffffff, #f5f5f5);
    box-shadow: 5px 5px 15px #d1d9e6, -5px -5px 15px #ffffff;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
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

.document-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    height: 100%;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    border: none;
    background-color: var(--white);
}

.document-card:hover {
    transform: translateY(-5px);
}

.document-header {
    padding: 20px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
}

.document-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.document-body {
    padding: 20px;
}

.document-footer {
    padding: 15px 20px;
    background-color: var(--light);
    border-top: 1px solid rgba(0,0,0,0.05);
}

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.document-status {
    position: absolute;
    top: 15px;
    right: 15px;
}

.document-qr {
    position: absolute;
    bottom: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    background-color: var(--white);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: var(--transition);
}

.document-qr:hover {
    transform: scale(1.1);
}

.document-preview {
    position: relative;
    height: 200px;
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

.document-preview .placeholder {
    font-size: 3rem;
    color: var(--secondary);
}

.document-info {
    padding: 20px;
}

.document-actions {
    padding: 15px 20px;
    background-color: var(--light);
    border-top: 1px solid rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
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

.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-box {
    position: relative;
}

.search-box .form-control {
    padding-left: 40px;
    border-radius: 50px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--secondary);
}

.filter-dropdown .dropdown-menu {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    padding: 15px;
}

.filter-dropdown .dropdown-item {
    border-radius: 8px;
    padding: 8px 15px;
    margin-bottom: 5px;
}

.filter-dropdown .dropdown-item:last-child {
    margin-bottom: 0;
}

.filter-dropdown .dropdown-item:hover {
    background-color: var(--primary-light);
}

.filter-dropdown .dropdown-item.active {
    background-color: var(--primary);
}

.filter-badge {
    background-color: var(--primary-light);
    color: var(--primary);
    border-radius: 50px;
    padding: 5px 15px;
    margin-right: 5px;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.filter-badge .close {
    font-size: 1rem;
    line-height: 1;
    cursor: pointer;
}

.filter-badge .close:hover {
    color: var(--danger);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: var(--danger);
    color: var(--white);
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    border: none;
    border-radius: var(--border-radius);
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
}

.qr-modal .modal-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 30px;
}

.qr-modal .qr-code {
    margin-bottom: 20px;
}

.qr-modal .qr-info {
    text-align: center;
}

.expiration-warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-left: 4px solid var(--warning);
    padding: 10px 15px;
    margin-top: 10px;
    border-radius: 5px;
}

.expiration-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-left: 4px solid var(--danger);
    padding: 10px 15px;
    margin-top: 10px;
    border-radius: 5px;
}
</style>

<div class="dashboard-container fade-in">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Mes documents officiels</h2>
            <div class="d-flex gap-2">
                <a href="demande_cni.php" class="btn btn-primary btn-action">
                    <i class="bi bi-person-vcard me-2"></i>Nouvelle CNI
                </a>
                <a href="demande_certificat.php" class="btn btn-outline-primary btn-action">
                    <i class="bi bi-file-earmark-text me-2"></i>Certificat de nationalité
                </a>
            </div>
        </div>
        
        <!-- Cartes de statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-person-vcard"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">CNI Actives</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['cni_active']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Certificats</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['certificats']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">CNI Expirées</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['cni_expiree']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-files"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Total Documents</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="searchDocument" placeholder="Rechercher par numéro ou type...">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end gap-2">
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="typeFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-2"></i>Type
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="typeFilterDropdown">
                                <li><a class="dropdown-item active" href="#" data-filter="all">Tous les documents</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="CNI">Cartes d'identité</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="Certificat">Certificats de nationalité</a></li>
                            </ul>
                        </div>
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-filter me-2"></i>Statut
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                                <li><a class="dropdown-item active" href="#" data-filter="all">Tous les statuts</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="Active">Actifs</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="Expiree">Expirés</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="Perdue">Perdus/Volés</a></li>
                            </ul>
                        </div>
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-sort-down me-2"></i>Trier
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                <li><a class="dropdown-item active" href="#" data-sort="date-desc">Plus récents d'abord</a></li>
                                <li><a class="dropdown-item" href="#" data-sort="date-asc">Plus anciens d'abord</a></li>
                                <li><a class="dropdown-item" href="#" data-sort="type">Par type</a></li>
                                <li><a class="dropdown-item" href="#" data-sort="status">Par statut</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Filtres actifs -->
                <div id="activeFilters" class="mt-3 d-none">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Filtres actifs:</span>
                        <div id="filterBadges" class="d-flex flex-wrap gap-2">
                            <!-- Les badges de filtres seront ajoutés ici dynamiquement -->
                        </div>
                        <button id="clearFilters" class="btn btn-sm btn-link text-danger ms-2">Effacer tous les filtres</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets pour les différentes catégories -->
        <ul class="nav nav-pills mb-4" id="documentsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-docs" type="button" role="tab" aria-controls="all-docs" aria-selected="true">
                    <i class="bi bi-files me-2"></i>Tous les documents
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cni-tab" data-bs-toggle="tab" data-bs-target="#cni-docs" type="button" role="tab" aria-controls="cni-docs" aria-selected="false">
                    <i class="bi bi-person-vcard me-2"></i>Cartes d'identité
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="certificats-tab" data-bs-toggle="tab" data-bs-target="#certificats-docs" type="button" role="tab" aria-controls="certificats-docs" aria-selected="false">
                    <i class="bi bi-file-earmark-text me-2"></i>Certificats de nationalité
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link position-relative" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-docs" type="button" role="tab" aria-controls="pending-docs" aria-selected="false">
                    <i class="bi bi-hourglass-split me-2"></i>Demandes en cours
                    <?php if (count($demandesEnCours) > 0): ?>
                    <span class="notification-badge"><?php echo count($demandesEnCours); ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="documentsTabContent">
            <!-- Tous les documents -->
            <div class="tab-pane fade show active" id="all-docs" role="tabpanel" aria-labelledby="all-tab">
                <?php if (count($documents) > 0): ?>
                <div class="document-grid">
                    <?php foreach ($documents as $doc): ?>
                    <div class="document-card animate-fade-in" 
                         data-type="<?php echo $doc['TypeDocument']; ?>" 
                         data-status="<?php echo $doc['Statut']; ?>" 
                         data-date="<?php echo strtotime($doc['DateEmission']); ?>"
                         data-numero="<?php echo $doc['Numero']; ?>">
                        <div class="document-preview">
                            <?php if ($doc['TypeDocument'] == 'CNI'): ?>
                                <?php if (file_exists($doc['CheminFichier'])): ?>
                                    <img src="<?php echo $doc['CheminFichier']; ?>" alt="CNI">
                                <?php else: ?>
                                    <div class="placeholder">
                                        <i class="bi bi-person-vcard"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="document-status">
                                    <?php 
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    if ($doc['Statut'] == 'Active') {
                                        $statusClass = 'bg-success';
                                        $statusText = 'Active';
                                    } elseif ($doc['Statut'] == 'Expiree') {
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Expirée';
                                    } elseif ($doc['Statut'] == 'Perdue') {
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Perdue/Volée';
                                    } elseif ($doc['Statut'] == 'Annulee') {
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'Annulée';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> status-badge">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="placeholder">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($doc['CodeQR'])): ?>
                            <div class="document-qr" data-bs-toggle="modal" data-bs-target="#qrModal" data-qr="<?php echo $doc['CodeQR']; ?>" data-numero="<?php echo $doc['Numero']; ?>">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="document-info">
                            <h5><?php echo $doc['TypeDocument']; ?></h5>
                            <p class="text-muted mb-2">N° <?php echo $doc['Numero']; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>Émis le: <?php echo date('d/m/Y', strtotime($doc['DateEmission'])); ?>
                                </span>
                                <?php if ($doc['TypeDocument'] == 'CNI' && !empty($doc['DateExpiration'])): ?>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar-x me-1"></i>Expire le: <?php echo date('d/m/Y', strtotime($doc['DateExpiration'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($doc['TypeDocument'] == 'CNI' && !empty($doc['DateExpiration'])): ?>
                                <?php 
                                $today = new DateTime();
                                $expiration = new DateTime($doc['DateExpiration']);
                                $diff = $today->diff($expiration);
                                $daysRemaining = $expiration > $today ? $diff->days : -$diff->days;
                                
                                if ($daysRemaining < 0): ?>
                                    <div class="expiration-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Expirée depuis <?php echo abs($daysRemaining); ?> jours</strong>
                                    </div>
                                <?php elseif ($daysRemaining <= 180): ?>
                                    <div class="expiration-warning">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Expire dans <?php echo $daysRemaining; ?> jours</strong>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="document-actions">
                            <a href="details_document.php?type=<?php echo $doc['TypeDocument'] == 'CNI' ? 'cni' : 'certificat'; ?>&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i>Détails
                            </a>
                            <?php if (!empty($doc['CheminFichier']) && file_exists($doc['CheminFichier'])): ?>
                            <a href="telecharger_document.php?type=<?php echo $doc['TypeDocument'] == 'CNI' ? 'cni' : 'certificat'; ?>&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                            <?php endif; ?>
                            <?php if ($doc['TypeDocument'] == 'CNI' && $doc['Statut'] == 'Active'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declarationPerteModal" data-id="<?php echo $doc['ID']; ?>" data-numero="<?php echo $doc['Numero']; ?>">
                                <i class="bi bi-exclamation-triangle me-1"></i>Déclarer perte
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-folder-x"></i>
                    <h4>Aucun document trouvé</h4>
                    <p class="text-muted">
                        Vous n'avez pas encore de documents officiels.
                        Commencez par faire une demande de CNI ou de certificat de nationalité.
                    </p>
                    <div class="mt-4">
                        <a href="demande_cni.php" class="btn btn-primary me-2">
                            <i class="bi bi-person-vcard me-2"></i>Demander une CNI
                        </a>
                        <a href="demande_certificat.php" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-2"></i>Demander un certificat
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Cartes d'identité -->
            <div class="tab-pane fade" id="cni-docs" role="tabpanel" aria-labelledby="cni-tab">
                <?php 
                $cniDocs = array_filter($documents, function($doc) {
                    return $doc['TypeDocument'] == 'CNI';
                });
                
                if (count($cniDocs) > 0): 
                ?>
                <div class="document-grid">
                    <?php foreach ($cniDocs as $doc): ?>
                    <div class="document-card animate-fade-in" 
                         data-type="<?php echo $doc['TypeDocument']; ?>" 
                         data-status="<?php echo $doc['Statut']; ?>" 
                         data-date="<?php echo strtotime($doc['DateEmission']); ?>"
                         data-numero="<?php echo $doc['Numero']; ?>">
                        <div class="document-preview">
                            <?php if (file_exists($doc['CheminFichier'])): ?>
                                <img src="<?php echo $doc['CheminFichier']; ?>" alt="CNI">
                            <?php else: ?>
                                <div class="placeholder">
                                    <i class="bi bi-person-vcard"></i>
                                </div>
                            <?php endif; ?>
                            <div class="document-status">
                                <?php 
                                $statusClass = '';
                                $statusText = '';
                                
                                if ($doc['Statut'] == 'Active') {
                                    $statusClass = 'bg-success';
                                    $statusText = 'Active';
                                } elseif ($doc['Statut'] == 'Expiree') {
                                    $statusClass = 'bg-warning';
                                    $statusText = 'Expirée';
                                } elseif ($doc['Statut'] == 'Perdue') {
                                    $statusClass = 'bg-danger';
                                    $statusText = 'Perdue/Volée';
                                } elseif ($doc['Statut'] == 'Annulee') {
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Annulée';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?> status-badge">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($doc['CodeQR'])): ?>
                            <div class="document-qr" data-bs-toggle="modal" data-bs-target="#qrModal" data-qr="<?php echo $doc['CodeQR']; ?>" data-numero="<?php echo $doc['Numero']; ?>">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="document-info">
                            <h5>Carte Nationale d'Identité</h5>
                            <p class="text-muted mb-2">N° <?php echo $doc['Numero']; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>Émis le: <?php echo date('d/m/Y', strtotime($doc['DateEmission'])); ?>
                                </span>
                                <?php if (!empty($doc['DateExpiration'])): ?>
                                <span class="text-muted small">
                                    <i class="bi bi-calendar-x me-1"></i>Expire le: <?php echo date('d/m/Y', strtotime($doc['DateExpiration'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($doc['DateExpiration'])): ?>
                                <?php 
                                $today = new DateTime();
                                $expiration = new DateTime($doc['DateExpiration']);
                                $diff = $today->diff($expiration);
                                $daysRemaining = $expiration > $today ? $diff->days : -$diff->days;
                                
                                if ($daysRemaining < 0): ?>
                                    <div class="expiration-danger">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Expirée depuis <?php echo abs($daysRemaining); ?> jours</strong>
                                    </div>
                                <?php elseif ($daysRemaining <= 180): ?>
                                    <div class="expiration-warning">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Expire dans <?php echo $daysRemaining; ?> jours</strong>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="document-actions">
                            <a href="details_document.php?type=cni&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i>Détails
                            </a>
                            <?php if (!empty($doc['CheminFichier']) && file_exists($doc['CheminFichier'])): ?>
                            <a href="telecharger_document.php?type=cni&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                            <?php endif; ?>
                            <?php if ($doc['Statut'] == 'Active'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declarationPerteModal" data-id="<?php echo $doc['ID']; ?>" data-numero="<?php echo $doc['Numero']; ?>">
                                <i class="bi bi-exclamation-triangle me-1"></i>Déclarer perte
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-person-vcard-fill"></i>
                    <h4>Aucune carte d'identité trouvée</h4>
                    <p class="text-muted">Vous n'avez pas encore de carte nationale d'identité.</p>
                    <div class="mt-4">
                        <a href="demande_cni.php" class="btn btn-primary">
                            <i class="bi bi-person-vcard me-2"></i>Demander une CNI
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Certificats de nationalité -->
            <div class="tab-pane fade" id="certificats-docs" role="tabpanel" aria-labelledby="certificats-tab">
                <?php 
                $certDocs = array_filter($documents, function($doc) {
                    return $doc['TypeDocument'] == 'Certificat Nationalité';
                });
                
                if (count($certDocs) > 0): 
                ?>
                <div class="document-grid">
                    <?php foreach ($certDocs as $doc): ?>
                    <div class="document-card animate-fade-in" 
                         data-type="<?php echo $doc['TypeDocument']; ?>" 
                         data-date="<?php echo strtotime($doc['DateEmission']); ?>"
                         data-numero="<?php echo $doc['Numero']; ?>">
                        <div class="document-preview">
                            <div class="placeholder">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            
                            <?php if (!empty($doc['CodeQR'])): ?>
                            <div class="document-qr" data-bs-toggle="modal" data-bs-target="#qrModal" data-qr="<?php echo $doc['CodeQR']; ?>" data-numero="<?php echo $doc['Numero']; ?>">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="document-info">
                            <h5>Certificat de Nationalité</h5>
                            <p class="text-muted mb-2">N° <?php echo $doc['Numero']; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>Émis le: <?php echo date('d/m/Y', strtotime($doc['DateEmission'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="document-actions">
                            <a href="details_document.php?type=certificat&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i>Détails
                            </a>
                            <?php if (!empty($doc['CheminFichier']) && file_exists($doc['CheminFichier'])): ?>
                            <a href="telecharger_document.php?type=certificat&id=<?php echo $doc['ID']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-file-earmark-text"></i>
                    <h4>Aucun certificat de nationalité trouvé</h4>
                    <p class="text-muted">Vous n'avez pas encore de certificat de nationalité.</p>
                    <div class="mt-4">
                        <a href="demande_certificat.php" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text me-2"></i>Demander un certificat
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Demandes en cours -->
            <div class="tab-pane fade" id="pending-docs" role="tabpanel" aria-labelledby="pending-tab">
                <?php if (count($demandesEnCours) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Type</th>
                                <th>Date de soumission</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demandesEnCours as $demande): ?>
                            <tr>
                                <td><?php echo $demande['NumeroReference']; ?></td>
                                <td>
                                    <?php if ($demande['TypeDemande'] == 'CNI'): ?>
                                        <span class="badge bg-primary">Carte d'identité</span>
                                    <?php elseif ($demande['TypeDemande'] == 'NATIONALITE'): ?>
                                        <span class="badge bg-success">Certificat de nationalité</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo $demande['TypeDemande']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?></td>
                                <td>
                                    <?php 
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    if ($demande['Statut'] == 'Soumise') {
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'Soumise';
                                    } elseif ($demande['Statut'] == 'EnCours') {
                                        $statusClass = 'bg-primary';
                                        $statusText = 'En cours';
                                    } elseif ($demande['Statut'] == 'Approuvee') {
                                        $statusClass = 'bg-success';
                                        $statusText = 'Approuvée';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td>
                                    <a href="details_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i>Détails
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-hourglass"></i>
                    <h4>Aucune demande en cours</h4>
                    <p class="text-muted">Vous n'avez pas de demandes en cours de traitement.</p>
                    <div class="mt-4">
                        <a href="demande_cni.php" class="btn btn-primary me-2">
                            <i class="bi bi-person-vcard me-2"></i>Demander une CNI
                        </a>
                        <a href="demande_certificat.php" class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-text me-2"></i>Demander un certificat
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade qr-modal" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Code QR du document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="qr-code" id="qrCodeDisplay">
                    <!-- Le code QR sera affiché ici -->
                </div>
                <div class="qr-info">
                    <p class="mb-1">Document: <span id="qrDocumentNumber"></span></p>
                    <p class="text-muted small">
                        Ce code QR peut être scanné pour vérifier l'authenticité de votre document.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="downloadQrBtn">
                    <i class="bi bi-download me-2"></i>Télécharger
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Déclaration de perte -->
<div class="modal fade" id="declarationPerteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Déclarer une perte ou un vol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="declarationPerteForm" action="declarer_perte.php" method="POST">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <p>Vous êtes sur le point de déclarer la perte ou le vol de votre carte nationale d'identité.</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Important :</strong> Cette action est irréversible. Une fois déclarée perdue, votre CNI sera invalidée et vous devrez faire une nouvelle demande.
                    </div>
                    
                    <input type="hidden" id="carteId" name="carte_id">
                    
                    <div class="mb-3">
                        <label for="numeroCartePerdue" class="form-label">Numéro de la carte</label>
                        <input type="text" class="form-control" id="numeroCartePerdue" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="datePerteVol" class="form-label">Date de la perte/vol</label>
                        <input type="date" class="form-control" id="datePerteVol" name="date_perte" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="circonstancesPerteVol" class="form-label">Circonstances de la perte/vol</label>
                        <textarea class="form-control" id="circonstancesPerteVol" name="circonstances" rows="3" required placeholder="Décrivez les circonstances de la perte ou du vol..."></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmationPerte" required>
                        <label class="form-check-label" for="confirmationPerte">
                            Je confirme que les informations fournies sont exactes et je comprends que je devrai faire une nouvelle demande de CNI.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Déclarer la perte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables pour les filtres
    let activeTypeFilter = 'all';
    let activeStatusFilter = 'all';
    let activeSortMethod = 'date-desc';
    let searchTerm = '';
    
    // Éléments DOM
    const searchInput = document.getElementById('searchDocument');
    const typeFilterLinks = document.querySelectorAll('[data-filter]');
    const sortLinks = document.querySelectorAll('[data-sort]');
    const activeFiltersContainer = document.getElementById('activeFilters');
    const filterBadgesContainer = document.getElementById('filterBadges');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const documentCards = document.querySelectorAll('.document-card');
    
    // Fonction pour appliquer les filtres
    function applyFilters() {
        let hasVisibleCards = false;
        
        documentCards.forEach(card => {
            const cardType = card.dataset.type;
            const cardStatus = card.dataset.status || '';
            const cardDate = parseInt(card.dataset.date);
            const cardNumero = card.dataset.numero.toLowerCase();
            
            // Vérifier si la carte correspond aux filtres
            const matchesType = activeTypeFilter === 'all' || cardType.includes(activeTypeFilter);
            const matchesStatus = activeStatusFilter === 'all' || cardStatus === activeStatusFilter;
            const matchesSearch = searchTerm === '' || 
                                 cardNumero.includes(searchTerm) || 
                                 cardType.toLowerCase().includes(searchTerm);
            
            // Afficher ou masquer la carte
            if (matchesType && matchesStatus && matchesSearch) {
                card.style.display = 'block';
                hasVisibleCards = true;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Mettre à jour les badges de filtres
        updateFilterBadges();
        
        // Trier les cartes visibles
        sortCards();
        
        // Afficher ou masquer le message "Aucun résultat"
        const emptyStateContainer = document.querySelector('#all-docs .empty-state');
        if (emptyStateContainer) {
            if (hasVisibleCards) {
                emptyStateContainer.style.display = 'none';
            } else {
                emptyStateContainer.style.display = 'block';
                emptyStateContainer.querySelector('h4').textContent = 'Aucun document correspondant';
                emptyStateContainer.querySelector('p').textContent = 'Aucun document ne correspond à vos critères de recherche.';
            }
        }
    }
    
    // Fonction pour trier les cartes
    function sortCards() {
        const container = document.querySelector('.document-grid');
        if (!container) return;
        
        const cards = Array.from(container.querySelectorAll('.document-card[style="display: block"]'));
        
        cards.sort((a, b) => {
            if (activeSortMethod === 'date-desc') {
                return parseInt(b.dataset.date) - parseInt(a.dataset.date);
            } else if (activeSortMethod === 'date-asc') {
                return parseInt(a.dataset.date) - parseInt(b.dataset.date);
            } else if (activeSortMethod === 'type') {
                return a.dataset.type.localeCompare(b.dataset.type);
            } else if (activeSortMethod === 'status') {
                return (a.dataset.status || '').localeCompare(b.dataset.status || '');
            }
            return 0;
        });
        
        // Réorganiser les cartes dans le DOM
        cards.forEach(card => {
            container.appendChild(card);
        });
    }
    
    // Fonction pour mettre à jour les badges de filtres
    function updateFilterBadges() {
        filterBadgesContainer.innerHTML = '';
        let hasActiveFilters = false;
        
        // Ajouter un badge pour le filtre de type
        if (activeTypeFilter !== 'all') {
            const typeBadge = createFilterBadge('Type: ' + (activeTypeFilter === 'CNI' ? 'Carte d\'identité' : 'Certificat'), 'type');
            filterBadgesContainer.appendChild(typeBadge);
            hasActiveFilters = true;
        }
        
        // Ajouter un badge pour le filtre de statut
        if (activeStatusFilter !== 'all') {
            const statusBadge = createFilterBadge('Statut: ' + activeStatusFilter, 'status');
            filterBadgesContainer.appendChild(statusBadge);
            hasActiveFilters = true;
        }
        
        // Ajouter un badge pour la recherche
        if (searchTerm !== '') {
            const searchBadge = createFilterBadge('Recherche: ' + searchTerm, 'search');
            filterBadgesContainer.appendChild(searchBadge);
            hasActiveFilters = true;
        }
        
        // Afficher ou masquer le conteneur de filtres actifs
        if (hasActiveFilters) {
            activeFiltersContainer.classList.remove('d-none');
        } else {
            activeFiltersContainer.classList.add('d-none');
        }
    }
    
    // Fonction pour créer un badge de filtre
    function createFilterBadge(text, type) {
        const badge = document.createElement('div');
        badge.className = 'filter-badge';
        badge.innerHTML = `
            ${text}
            <span class="close" data-filter-type="${type}">&times;</span>
        `;
        
        // Ajouter un gestionnaire d'événements pour supprimer le filtre
        badge.querySelector('.close').addEventListener('click', function() {
            if (type === 'type') {
                activeTypeFilter = 'all';
                document.querySelector('[data-filter="all"]').classList.add('active');
                document.querySelectorAll('[data-filter]:not([data-filter="all"])').forEach(el => {
                    el.classList.remove('active');
                });
            } else if (type === 'status') {
                activeStatusFilter = 'all';
            } else if (type === 'search') {
                searchTerm = '';
                searchInput.value = '';
            }
            
            applyFilters();
        });
        
        return badge;
    }
    
    // Gestionnaire d'événements pour la recherche
    searchInput.addEventListener('input', function() {
        searchTerm = this.value.trim().toLowerCase();
        applyFilters();
    });
    
    // Gestionnaire d'événements pour les filtres de type
    typeFilterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Mettre à jour le filtre actif
            activeTypeFilter = this.dataset.filter;
            
            // Mettre à jour l'état actif des liens
            typeFilterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            applyFilters();
        });
    });
    
    // Gestionnaire d'événements pour les options de tri
    sortLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Mettre à jour la méthode de tri active
            activeSortMethod = this.dataset.sort;
            
            // Mettre à jour l'état actif des liens
            sortLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            applyFilters();
        });
    });
    
    // Gestionnaire d'événements pour effacer tous les filtres
    clearFiltersBtn.addEventListener('click', function() {
        activeTypeFilter = 'all';
        activeStatusFilter = 'all';
        searchTerm = '';
        searchInput.value = '';
        
        // Réinitialiser l'état actif des liens
        document.querySelector('[data-filter="all"]').classList.add('active');
        document.querySelectorAll('[data-filter]:not([data-filter="all"])').forEach(el => {
            el.classList.remove('active');
        });
        
        applyFilters();
    });
    
    // Gestionnaire d'événements pour le modal QR Code
    const qrModal = document.getElementById('qrModal');
    if (qrModal) {
        qrModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const qrData = button.getAttribute('data-qr');
            const numero = button.getAttribute('data-numero');
            
            // Mettre à jour le numéro du document
            document.getElementById('qrDocumentNumber').textContent = numero;
            
            // Générer le code QR
            const qrCodeDisplay = document.getElementById('qrCodeDisplay');
            qrCodeDisplay.innerHTML = '';
            
            const qr = new QRious({
                element: document.createElement('canvas'),
                value: qrData,
                size: 200
            });
            
            qrCodeDisplay.appendChild(qr.element);
            
            // Gestionnaire pour le bouton de téléchargement
            document.getElementById('downloadQrBtn').onclick = function() {
                const link = document.createElement('a');
                link.download = 'qr-code-' + numero + '.png';
                link.href = qr.element.toDataURL('image/png');
                link.click();
            };
        });
    }
    
    // Gestionnaire d'événements pour le modal de déclaration de perte
    const declarationPerteModal = document.getElementById('declarationPerteModal');
    if (declarationPerteModal) {
        declarationPerteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const numero = button.getAttribute('data-numero');
            
            document.getElementById('carteId').value = id;
            document.getElementById('numeroCartePerdue').value = numero;
            
            // Définir la date maximale à aujourd'hui
            document.getElementById('datePerteVol').max = new Date().toISOString().split('T')[0];
            document.getElementById('datePerteVol').value = new Date().toISOString().split('T')[0];
        });
    }
    
    // Validation du formulaire de déclaration de perte
    const declarationPerteForm = document.getElementById('declarationPerteForm');
    if (declarationPerteForm) {
        declarationPerteForm.addEventListener('submit', function(event) {
            const datePerteVol = document.getElementById('datePerteVol').value;
            const circonstancesPerteVol = document.getElementById('circonstancesPerteVol').value;
            const confirmationPerte = document.getElementById('confirmationPerte').checked;
            
            if (!datePerteVol || !circonstancesPerteVol || !confirmationPerte) {
                event.preventDefault();
                alert('Veuillez remplir tous les champs et confirmer la déclaration.');
            }
        });
    }
    
    // Appliquer les filtres au chargement de la page
    applyFilters();
    
    // Animation pour les onglets
    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    tabLinks.forEach(link => {
        link.addEventListener('shown.bs.tab', function(event) {
            const targetTab = document.querySelector(event.target.getAttribute('data-bs-target'));
            const cards = targetTab.querySelectorAll('.document-card, .empty-state');
            
            cards.forEach((card, index) => {
                card.classList.remove('animate-fade-in');
                void card.offsetWidth; // Force reflow
                card.classList.add('animate-fade-in');
                card.style.animationDelay = (index * 0.05) + 's';
            });
        });
    });
    
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

</script>

<?php include('../includes/footer.php'); ?>
