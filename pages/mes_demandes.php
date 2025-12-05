<?php
// Session is initialized centrally in includes/config.php
include('../includes/config.php');
include('../includes/auth.php');

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Filtres
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Récupération de toutes les demandes de l'utilisateur avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construction de la requête avec filtres
$queryParams = ['userId' => $userId];
$whereConditions = ["d.UtilisateurID = :userId"];

if (!empty($statusFilter)) {
    $whereConditions[] = "d.Statut = :status";
    $queryParams['status'] = $statusFilter;
}

if (!empty($typeFilter)) {
    $whereConditions[] = "d.TypeDemande = :type";
    $queryParams['type'] = $typeFilter;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(d.NumeroReference LIKE :search OR dc.Nom LIKE :search OR dn.Nom LIKE :search)";
    $queryParams['search'] = "%$searchQuery%";
}

$whereClause = implode(' AND ', $whereConditions);

$query = "SELECT d.*, 
          CASE 
            WHEN d.TypeDemande = 'CNI' THEN dc.Nom 
            WHEN d.TypeDemande = 'NATIONALITE' THEN dn.Nom 
          END as NomDemandeur,
          COUNT(*) OVER() as total_count
          FROM demandes d 
          LEFT JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID 
          LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID 
          WHERE $whereClause 
          ORDER BY d.DateSoumission DESC
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($queryParams as $param => $value) {
    $stmt->bindValue(":$param", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$demandes = $stmt->fetchAll();

$totalDemandes = !empty($demandes) ? $demandes[0]['total_count'] : 0;
$totalPages = ceil($totalDemandes / $limit);

// Récupération des statistiques
$stats = [
    'total' => 0,
    'en_cours' => 0,
    'approuvees' => 0,
    'a_retirer' => 0,
    'rejetees' => 0
];

$statsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN Statut = 'EnCours' THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN Statut = 'Approuvee' THEN 1 ELSE 0 END) as approuvees,
                SUM(CASE WHEN Statut = 'Terminee' THEN 1 ELSE 0 END) as a_retirer,
                SUM(CASE WHEN Statut = 'Rejetee' THEN 1 ELSE 0 END) as rejetees
              FROM demandes 
              WHERE UtilisateurID = :userId";
$stmt = $db->prepare($statsQuery);
$stmt->execute(['userId' => $userId]);
$statsResult = $stmt->fetch(PDO::FETCH_ASSOC);

if ($statsResult) {
    $stats = $statsResult;
}

// Récupération des notifications récentes
$notifQuery = "SELECT * FROM notifications 
               WHERE UtilisateurID = :userId 
               ORDER BY DateCreation DESC 
               LIMIT 5";
$stmt = $db->prepare($notifQuery);
$stmt->execute(['userId' => $userId]);
$notifications = $stmt->fetchAll();

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<style>
.dashboard-container {
    background-color: #f8f9fa;
    padding: 30px 0;
    min-height: calc(100vh - 180px);
}
.stats-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.table-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}
.status-badge {
    padding: 8px 12px;
    border-radius: 50px;
    font-weight: 500;
}
.btn-action {
    padding: 8px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.btn-action:hover {
    transform: translateY(-2px);
}
.pagination {
    margin-bottom: 0;
}
.page-link {
    padding: 10px 15px;
    border-radius: 8px;
    margin: 0 3px;
}
.demande-type-icon {
    font-size: 1.5rem;
    margin-right: 10px;
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
.stat-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease;
    background: linear-gradient(145deg, #ffffff, #f5f5f5);
    box-shadow: 5px 5px 15px #d1d9e6, -5px -5px 15px #ffffff;
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
.table-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
}
.notification-card {
    border: none;
    border-radius: 15px;
}
.filter-card {
    border: none;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-dot {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #1774df;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}
.timeline-content {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
}
.timeline-date {
    font-size: 12px;
    color: #6c757d;
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
    color: #6c757d;
}
.status-filter .dropdown-menu {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 15px;
}
.status-filter .dropdown-item {
    border-radius: 8px;
    padding: 10px 15px;
    margin-bottom: 5px;
}
.status-filter .dropdown-item:hover {
    background-color: #f8f9fa;
}
.status-filter .dropdown-item.active {
    background-color: #1774df;
}
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
}
.status-indicator.soumise { background-color: #6c757d; }
.status-indicator.encours { background-color: #1774df; }
.status-indicator.approuvee { background-color: #28a745; }
.status-indicator.rejetee { background-color: #dc3545; }
.status-indicator.terminee { background-color: #17a2b8; }
.status-indicator.annulee { background-color: #ffc107; }
.table-hover tbody tr:hover {
    background-color: rgba(23, 116, 223, 0.05);
}
.table th {
    font-weight: 600;
    color: #495057;
}
.table td {
    vertical-align: middle;
}
.card-header-tabs {
    margin-bottom: -0.75rem;
}
.nav-tabs .nav-link {
    border: none;
    border-radius: 0;
    padding: 1rem 1.5rem;
    font-weight: 500;
    color: #495057;
}
.nav-tabs .nav-link.active {
    color: #1774df;
    border-bottom: 2px solid #1774df;
    background-color: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    color: #1774df;
    border-bottom: 2px solid #e9ecef;
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
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar / Statistiques -->
            <div class="col-lg-3">
                <div class="card stat-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Mes statistiques</h5>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Total demandes</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['total']; ?></h3>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">En cours</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['en_cours']; ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Approuvées</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['approuvees']; ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">À retirer</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['a_retirer']; ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Rejetées</h6>
                                <h3 class="card-title mb-0"><?php echo $stats['rejetees']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications récentes -->
                <div class="card notification-card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Notifications récentes</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if(!empty($notifications)): ?>
                        <div class="timeline p-3">
                            <?php foreach($notifications as $notification): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                <i class="bi bi-bell-fill"></i>
                                </div>
                                <div class="timeline-content">
                                    <p class="mb-1"><?php echo htmlspecialchars($notification['Contenu']); ?></p>
                                    <p class="timeline-date mb-0">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($notification['DateCreation'])); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="p-4 text-center">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                            <p class="mt-3 mb-0">Aucune notification récente</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-footer bg-white text-center">
                            <a href="notifications.php" class="btn btn-sm btn-outline-primary">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>
                
                <!-- Liens rapides -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="demande_cni.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-badge me-2"></i>Nouvelle demande de CNI
                            </a>
                            <a href="demande_certificat.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Nouvelle demande de certificat
                            </a>
                            <a href="suivi_demande.php" class="btn btn-outline-primary">
                                <i class="bi bi-search me-2"></i>Suivre une demande
                            </a>
                            <a href="reclamations.php" class="btn btn-outline-primary">
                                <i class="bi bi-exclamation-circle me-2"></i>Faire une réclamation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Liste des demandes -->
            <div class="col-lg-9">
                <div class="card table-card mb-4">
                    <div class="card-header bg-white p-0">
                        <ul class="nav nav-tabs card-header-tabs" id="demandesTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="all-tab" data-bs-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">
                                    Toutes les demandes
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="cni-tab" data-bs-toggle="tab" href="#cni" role="tab" aria-controls="cni" aria-selected="false">
                                    CNI
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="certificat-tab" data-bs-toggle="tab" href="#certificat" role="tab" aria-controls="certificat" aria-selected="false">
                                    Certificats
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filtres et recherche -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Rechercher par référence ou nom..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <div class="status-filter me-2">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="statusFilterBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php 
                                        $statusLabels = [
                                            '' => 'Tous les statuts',
                                            'Soumise' => 'Soumise',
                                            'EnCours' => 'En cours',
                                            'Approuvee' => 'Approuvée',
                                            'Rejetee' => 'Rejetée',
                                            'Terminee' => 'Terminée',
                                            'Annulee' => 'Annulée'
                                        ];
                                        echo $statusLabels[$statusFilter] ?? 'Tous les statuts';
                                        ?>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="statusFilterBtn">
                                        <a class="dropdown-item <?php echo $statusFilter === '' ? 'active' : ''; ?>" href="?status=&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            Tous les statuts
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'Soumise' ? 'active' : ''; ?>" href="?status=Soumise&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator soumise"></span> Soumise
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'EnCours' ? 'active' : ''; ?>" href="?status=EnCours&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator encours"></span> En cours
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'Approuvee' ? 'active' : ''; ?>" href="?status=Approuvee&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator approuvee"></span> Approuvée
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'Rejetee' ? 'active' : ''; ?>" href="?status=Rejetee&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator rejetee"></span> Rejetée
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'Terminee' ? 'active' : ''; ?>" href="?status=Terminee&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator terminee"></span> Terminée
                                        </a>
                                        <a class="dropdown-item <?php echo $statusFilter === 'Annulee' ? 'active' : ''; ?>" href="?status=Annulee&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <span class="status-indicator annulee"></span> Annulée
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="type-filter">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="typeFilterBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php 
                                        $typeLabels = [
                                            '' => 'Tous les types',
                                            'CNI' => 'CNI',
                                            'NATIONALITE' => 'Certificat de nationalité'
                                        ];
                                        echo $typeLabels[$typeFilter] ?? 'Tous les types';
                                        ?>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="typeFilterBtn">
                                        <a class="dropdown-item <?php echo $typeFilter === '' ? 'active' : ''; ?>" href="?status=<?php echo $statusFilter; ?>&type=&search=<?php echo urlencode($searchQuery); ?>">
                                            Tous les types
                                        </a>
                                        <a class="dropdown-item <?php echo $typeFilter === 'CNI' ? 'active' : ''; ?>" href="?status=<?php echo $statusFilter; ?>&type=CNI&search=<?php echo urlencode($searchQuery); ?>">
                                            <i class="bi bi-person-badge me-2"></i> CNI
                                        </a>
                                        <a class="dropdown-item <?php echo $typeFilter === 'NATIONALITE' ? 'active' : ''; ?>" href="?status=<?php echo $statusFilter; ?>&type=NATIONALITE&search=<?php echo urlencode($searchQuery); ?>">
                                            <i class="bi bi-file-earmark-text me-2"></i> Certificat de nationalité
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contenu des onglets -->
                        <div class="tab-content" id="demandesTabContent">
                            <!-- Toutes les demandes -->
                            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                                <?php if(!empty($demandes)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Référence</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($demandes as $demande): ?>
                                            <tr class="animate-fade-in">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($demande['NumeroReference'] ?? 'N/A'); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if($demande['TypeDemande'] == 'CNI'): ?>
                                                    <span class="d-flex align-items-center">
                                                        <i class="bi bi-person-badge text-primary demande-type-icon"></i>
                                                        <span>Carte Nationale d'Identité</span>
                                                    </span>
                                                    <?php elseif($demande['TypeDemande'] == 'NATIONALITE'): ?>
                                                    <span class="d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-text text-success demande-type-icon"></i>
                                                        <span>Certificat de Nationalité</span>
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?></span>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($demande['DateSoumission'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
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
                                                        'EnCours' => 'En cours',
                                                        'Approuvee' => 'Approuvée',
                                                        'Rejetee' => 'Rejetée',
                                                        'Terminee' => 'À retirer',
                                                        'Annulee' => 'Annulée'
                                                    ];
                                                    $statusClass = $statusClasses[$demande['Statut']] ?? 'bg-secondary';
                                                    $statusLabel = $statusLabels[$demande['Statut']] ?? $demande['Statut'];
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?> status-badge">
                                                        <?php echo $statusLabel; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="details_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-sm btn-primary btn-action">
                                                            <i class="bi bi-eye me-1"></i> Détails
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <span class="visually-hidden">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <?php if($demande['Statut'] == 'Soumise'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="modifier_demande.php?id=<?php echo $demande['DemandeID']; ?>">
                                                                    <i class="bi bi-pencil me-2"></i> Modifier
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $demande['DemandeID']; ?>">
                                                                    <i class="bi bi-x-circle me-2"></i> Annuler
                                                                </a>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if($demande['Statut'] == 'Approuvee' || $demande['Statut'] == 'Terminee'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="telecharger_document.php?id=<?php echo $demande['DemandeID']; ?>">
                                                                    <i class="bi bi-download me-2"></i> Télécharger
                                                                </a>
                                                            </li>
                                                            <?php endif; ?>
                                                            <li>
                                                                <a class="dropdown-item" href="suivi_demande.php?reference=<?php echo urlencode($demande['NumeroReference']); ?>">
                                                                    <i class="bi bi-clock-history me-2"></i> Suivi
                                                                </a>
                                                            </li>
                                                            <?php if($demande['Statut'] == 'Rejetee'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="reclamation.php?demande=<?php echo $demande['DemandeID']; ?>">
                                                                    <i class="bi bi-exclamation-circle me-2"></i> Réclamation
                                                                </a>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                    
                                                    <!-- Modal d'annulation -->
                                                    <div class="modal fade" id="cancelModal<?php echo $demande['DemandeID']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Confirmer l'annulation</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Êtes-vous sûr de vouloir annuler cette demande ?</p>
                                                                    <p class="text-danger"><strong>Attention :</strong> Cette action est irréversible.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                                    <a href="annuler_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-danger">Confirmer l'annulation</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if($totalPages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <p class="text-muted mb-0">Affichage de <?php echo count($demandes); ?> demande(s) sur <?php echo $totalDemandes; ?></p>
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <?php if($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php for($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                            <?php endfor; ?>
                                            
                                            <?php if($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=<?php echo urlencode($searchQuery); ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                                <?php endif; ?>
                                
                                <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-folder"></i>
                                    <h4>Aucune demande trouvée</h4>
                                    <p class="text-muted">
                                        <?php if(!empty($searchQuery) || !empty($statusFilter) || !empty($typeFilter)): ?>
                                            Aucune demande ne correspond à vos critères de recherche.
                                            <a href="mes_demandes.php">Réinitialiser les filtres</a>
                                        <?php else: ?>
                                            Vous n'avez pas encore soumis de demande.
                                        <?php endif; ?>
                                    </p>
                                    <div class="mt-4">
                                        <a href="demande_cni.php" class="btn btn-primary me-2">
                                            <i class="bi bi-person-badge me-2"></i>Demander une CNI
                                        </a>
                                        <a href="demande_certificat.php" class="btn btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-2"></i>Demander un certificat
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Demandes de CNI -->
                            <div class="tab-pane fade" id="cni" role="tabpanel" aria-labelledby="cni-tab">
                                <div id="cni-content">
                                    <!-- Le contenu sera chargé dynamiquement -->
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <p class="mt-3">Chargement des demandes de CNI...</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Demandes de certificat -->
                            <div class="tab-pane fade" id="certificat" role="tabpanel" aria-labelledby="certificat-tab">
                                <div id="certificat-content">
                                    <!-- Le contenu sera chargé dynamiquement -->
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <p class="mt-3">Chargement des demandes de certificat...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Carte d'information -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-info-circle-fill text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="card-title">Besoin d'aide ?</h5>
                                <p class="card-text">Si vous avez des questions concernant vos demandes ou si vous rencontrez des difficultés, n'hésitez pas à contacter notre service d'assistance.</p>
                                <a href="contact.php" class="btn btn-sm btn-outline-primary">Contacter l'assistance</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour la recherche dynamique -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche dynamique
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            const searchValue = searchInput.value.trim();
            window.location.href = `?status=<?php echo $statusFilter; ?>&type=<?php echo $typeFilter; ?>&search=${encodeURIComponent(searchValue)}`;
        }, 500);
    });
    
    // Chargement dynamique des onglets
    const cniTab = document.getElementById('cni-tab');
    const certificatTab = document.getElementById('certificat-tab');
    const cniContent = document.getElementById('cni-content');
    const certificatContent = document.getElementById('certificat-content');
    
    cniTab.addEventListener('shown.bs.tab', function() {
        loadTabContent('CNI', cniContent);
    });
    
    certificatTab.addEventListener('shown.bs.tab', function() {
        loadTabContent('NATIONALITE', certificatContent);
    });
    
    function loadTabContent(type, container) {
        fetch(`get_demandes_by_type.php?type=${type}`)
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Une erreur est survenue lors du chargement des données.
                    </div>
                `;
                console.error('Erreur:', error);
            });
    }
    
    // Animation des cartes de statistiques
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-fade-in');
        }, index * 100);
    });
});
</script>

<?php
// Création du fichier get_demandes_by_type.php pour le chargement AJAX
$ajaxFile = '../pages/get_demandes_by_type.php';
if (!file_exists($ajaxFile)) {
    $ajaxContent = '<?php
    include(\'../includes/config.php\');
    include(\'../includes/auth.php\');
    // Session is initialized centrally in includes/config.php

    // Vérification de la connexion
    if (!isset($_SESSION[\'user_id\']) || $_SESSION[\'role\'] != 2) {
        echo "<div class=\'alert alert-danger\'>Accès non autorisé</div>";
        exit();
    }

    $userId = $_SESSION[\'user_id\'];
    $type = isset($_GET[\'type\']) ? $_GET[\'type\'] : \'\';

    if (empty($type)) {
        echo "<div class=\'alert alert-danger\'>Type de demande non spécifié</div>";
    ';
}

// Récupération des demandes par type
$query = "SELECT d.*, 
          CASE 
            WHEN d.TypeDemande = \'CNI\' THEN dc.Nom 
            WHEN d.TypeDemande = \'NATIONALITE\' THEN dn.Nom 
          END as NomDemandeur
          FROM demandes d 
          LEFT JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID 
          LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID 
          WHERE d.UtilisateurID = :userId AND d.TypeDemande = :type
          ORDER BY d.DateSoumission DESC";

$stmt = $db->prepare($query);
$stmt->execute([
    'userId' => $userId,
    'type' => $type
]);
$demandes = $stmt->fetchAll();

if (empty($demandes)) {
    $docTypeLabel = ($type == 'CNI') ? "Carte Nationale d'Identité" : "Certificat de Nationalité";
    $docLink = ($type == 'CNI') ? 'demande_cni.php' : 'demande_certificat.php';
    $docIcon = ($type == 'CNI') ? 'person-badge' : 'file-earmark-text';

    echo <<<HTML
<div class="empty-state">
    <i class="bi bi-folder"></i>
    <h4>Aucune demande trouvée</h4>
    <p class="text-muted">Vous n'avez pas encore soumis de demande de {$docTypeLabel}.</p>
    <div class="mt-4">
        <a href="{$docLink}" class="btn btn-primary">
            <i class="bi bi-{$docIcon} me-2"></i>
            Faire une demande
        </a>
    </div>
</div>
HTML;
    exit();
}

// Affichage des demandes
echo "<div class=\'table-responsive\'>
        <table class=\'table table-hover align-middle\'>
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";

foreach ($demandes as $demande) {
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
        'EnCours' => 'En cours',
        'Approuvee' => 'Approuvée',
        'Rejetee' => 'Rejetée',
        'Terminee' => 'À retirer',
        'Annulee' => 'Annulée'
    ];
    $statusClass = $statusClasses[$demande['Statut']] ?? 'bg-secondary';
    $statusLabel = $statusLabels[$demande['Statut']] ?? $demande['Statut'];

    $numeroRef = htmlspecialchars($demande['NumeroReference'] ?? 'N/A');
    $date = date('d/m/Y', strtotime($demande['DateSoumission']));
    $time = date('H:i', strtotime($demande['DateSoumission']));
    $demandeId = $demande['DemandeID'];
    $downloadAllowed = ($demande['Statut'] == 'Approuvee' || $demande['Statut'] == 'Terminee');
    $showModify = ($demande['Statut'] == 'Soumise');

    echo <<<HTML
<tr class="animate-fade-in">
    <td>
        <strong>{$numeroRef}</strong>
    </td>
    <td>
        <div class="d-flex flex-column">
            <span>{$date}</span>
            <small class="text-muted">{$time}</small>
        </div>
    </td>
    <td>
        <span class="badge {$statusClass} status-badge">{$statusLabel}</span>
    </td>
    <td>
        <div class="btn-group">
            <a href="details_demande.php?id={$demandeId}" class="btn btn-sm btn-primary btn-action">
                <i class="bi bi-eye me-1"></i> Détails
            </a>
            <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
HTML;

    if ($showModify) {
        echo <<<HTML
            <li>
                <a class="dropdown-item" href="modifier_demande.php?id={$demandeId}">
                    <i class="bi bi-pencil me-2"></i> Modifier
                </a>
            </li>
            <li>
                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelModal{$demandeId}">
                    <i class="bi bi-x-circle me-2"></i> Annuler
                </a>
            </li>
HTML;
    }

    if ($downloadAllowed) {
        echo <<<HTML
            <li>
                <a class="dropdown-item" href="telecharger_document.php?id={$demandeId}">
                    <i class="bi bi-download me-2"></i> Télécharger
                </a>
            </li>
HTML;
    }

    $numRefEscaped = urlencode($demande['NumeroReference']);
    echo <<<HTML
            <li>
                <a class="dropdown-item" href="suivi_demande.php?reference={$numRefEscaped}">
                    <i class="bi bi-clock-history me-2"></i> Suivi
                </a>
            </li>
HTML;

    if ($demande['Statut'] == 'Rejetee') {
        echo <<<HTML
            <li>
                <a class="dropdown-item" href="reclamation.php?demande={$demandeId}">
                    <i class="bi bi-exclamation-circle me-2"></i> Réclamation
                </a>
            </li>
HTML;
    }

    echo <<<HTML
            </ul>
        </div>

        <!-- Modal d'annulation -->
        <div class="modal fade" id="cancelModal{$demandeId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer l'annulation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir annuler cette demande ?</p>
                        <p class="text-danger"><strong>Attention :</strong> Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <a href="annuler_demande.php?id={$demandeId}" class="btn btn-danger">Confirmer l'annulation</a>
                    </div>
                </div>
            </div>
        </div>

    </td>
</tr>
HTML;
}

echo "</tbody>
    </table>
</div>";
?>';

    file_put_contents($ajaxFile, $ajaxContent);
}

include('../includes/footer.php');
?>
