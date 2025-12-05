<?php
session_start();
include('../includes/config.php');

// Vérification de la session officier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Location: /pages/login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$userId = $_SESSION['user_id'];
$query = "SELECT Prenom, Nom, PhotoUtilisateur FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Récupération des statistiques CNI
$stats = [
    'nouvelles' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Soumise' AND TypeDemande = 'CNI'")->fetchColumn(),
    'encours' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'EnCours' AND TypeDemande = 'CNI'")->fetchColumn(),
    'approuvees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Approuvee' AND TypeDemande = 'CNI'")->fetchColumn(),
    'terminees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Terminee' AND TypeDemande = 'CNI'")->fetchColumn(),
    'rejetees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Rejetee' AND TypeDemande = 'CNI'")->fetchColumn(),
    'total' => $db->query("SELECT COUNT(*) FROM demandes WHERE TypeDemande = 'CNI'")->fetchColumn()
];

// Récupération des statistiques par type de demande
$typeStats = $db->query("
    SELECT SousTypeDemande, COUNT(*) as count 
    FROM demandes 
    WHERE TypeDemande = 'CNI' 
    GROUP BY SousTypeDemande
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Récupération des statistiques mensuelles pour le graphique
$monthlyStats = $db->query("
    SELECT 
        DATE_FORMAT(DateSoumission, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN Statut = 'Terminee' THEN 1 ELSE 0 END) as completed
    FROM demandes
    WHERE TypeDemande = 'CNI' AND DateSoumission >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(DateSoumission, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

// Récupération des dernières demandes CNI avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$query = "SELECT d.*, dc.Nom as NomDemandeur, dc.Prenom as PrenomDemandeur, 
                 u.PhotoUtilisateur, u.NumeroTelephone, u.Email
          FROM demandes d 
          LEFT JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID 
          LEFT JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.TypeDemande = 'CNI'
          ORDER BY d.DateSoumission DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$demandes = $stmt->fetchAll();

// Calcul du nombre total de pages
$total_demandes = $db->query("SELECT COUNT(*) FROM demandes WHERE TypeDemande = 'CNI'")->fetchColumn();
$total_pages = ceil($total_demandes / $limit);

// Récupération des activités récentes
$activities = $db->query("
    SELECT h.*, d.NumeroReference, d.TypeDemande, u.Prenom, u.Nom, u.PhotoUtilisateur
    FROM historique_demandes h
    JOIN demandes d ON h.DemandeID = d.DemandeID
    LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
    WHERE d.TypeDemande = 'CNI'
    ORDER BY h.DateModification DESC
    LIMIT 10
")->fetchAll();

// Récupération des réclamations récentes
$reclamations = $db->query("
    SELECT r.*, u.Prenom, u.Nom, u.PhotoUtilisateur
    FROM reclamations r
    JOIN utilisateurs u ON r.UtilisateurID = u.UtilisateurID
    WHERE r.Statut = 'Ouverte'
    ORDER BY r.DateCreation DESC
    LIMIT 5
")->fetchAll();

// Inclusion des templates
include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- En-tête de la page -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
                <div>
                    <h1 class="h2 mb-0">Tableau de bord</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshDashboard">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Imprimer
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="periodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar3 me-1"></i>Cette semaine
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="periodDropdown">
                            <li><a class="dropdown-item" href="#">Aujourd'hui</a></li>
                            <li><a class="dropdown-item active" href="#">Cette semaine</a></li>
                            <li><a class="dropdown-item" href="#">Ce mois</a></li>
                            <li><a class="dropdown-item" href="#">Cette année</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Personnaliser...</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Message de bienvenue -->
            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="welcome-avatar">
                                <?php if (!empty($user['PhotoUtilisateur']) && file_exists($user['PhotoUtilisateur'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['PhotoUtilisateur']); ?>" alt="Photo de profil" class="rounded-circle">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($user['Prenom'], 0, 1) . substr($user['Nom'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="welcome-title">Bienvenue, <?php echo htmlspecialchars($user['Prenom']); ?> !</h4>
                            <p class="welcome-message mb-0">
                                Vous avez <strong><?php echo $stats['nouvelles']; ?> nouvelles demandes</strong> à traiter et <strong><?php echo $stats['encours']; ?> demandes</strong> en cours de traitement.
                            </p>
                        </div>
                        <div class="col-md-4 col-lg-3 mt-3 mt-md-0">
                            <a href="demandes_cni.php" class="btn btn-primary w-100">
                                <i class="bi bi-list-check me-2"></i>Voir toutes les demandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row g-4 mb-4">
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-primary-light text-primary">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h6 class="stat-title">Nouvelles</h6>
                            <h3 class="stat-value"><?php echo $stats['nouvelles']; ?></h3>
                            <div class="stat-change increase">
                                <i class="bi bi-arrow-up-short"></i>
                                <?php 
                                    $percent = ($stats['total'] > 0) ? round(($stats['nouvelles'] / $stats['total']) * 100) : 0;
                                    echo $percent . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-info-light text-info">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <h6 class="stat-title">En cours</h6>
                            <h3 class="stat-value"><?php echo $stats['encours']; ?></h3>
                            <div class="stat-change increase">
                                <i class="bi bi-arrow-up-short"></i>
                                <?php 
                                    $percent = ($stats['total'] > 0) ? round(($stats['encours'] / $stats['total']) * 100) : 0;
                                    echo $percent . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-success-light text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <h6 class="stat-title">Approuvées</h6>
                            <h3 class="stat-value"><?php echo $stats['approuvees']; ?></h3>
                            <div class="stat-change increase">
                                <i class="bi bi-arrow-up-short"></i>
                                <?php 
                                    $percent = ($stats['total'] > 0) ? round(($stats['approuvees'] / $stats['total']) * 100) : 0;
                                    echo $percent . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-warning-light text-warning">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <h6 class="stat-title">Rejetées</h6>
                            <h3 class="stat-value"><?php echo $stats['rejetees']; ?></h3>
                            <div class="stat-change decrease">
                                <i class="bi bi-arrow-down-short"></i>
                                <?php 
                                    $percent = ($stats['total'] > 0) ? round(($stats['rejetees'] / $stats['total']) * 100) : 0;
                                    echo $percent . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-secondary-light text-secondary">
                                <i class="bi bi-flag"></i>
                            </div>
                            <h6 class="stat-title">Terminées</h6>
                            <h3 class="stat-value"><?php echo $stats['terminees']; ?></h3>
                            <div class="stat-change increase">
                                <i class="bi bi-arrow-up-short"></i>
                                <?php 
                                    $percent = ($stats['total'] > 0) ? round(($stats['terminees'] / $stats['total']) * 100) : 0;
                                    echo $percent . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-icon bg-dark-light text-dark">
                                <i class="bi bi-collection"></i>
                            </div>
                            <h6 class="stat-title">Total</h6>
                            <h3 class="stat-value"><?php echo $stats['total']; ?></h3>
                            <div class="stat-change neutral">
                                <i class="bi bi-dash"></i>
                                100%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques et statistiques détaillées -->
            <div class="row g-4 mb-4">
                                <!-- Graphique d'évolution -->
                                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Évolution des demandes</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    6 derniers mois
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartDropdown">
                                    <li><a class="dropdown-item" href="#">3 derniers mois</a></li>
                                    <li><a class="dropdown-item active" href="#">6 derniers mois</a></li>
                                    <li><a class="dropdown-item" href="#">12 derniers mois</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="demandesChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Répartition par type -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Répartition par type</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="typeChart" height="250"></canvas>
                            <div class="chart-legend mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: rgba(54, 162, 235, 0.8);"></span>
                                            <span class="legend-label">Première demande</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: rgba(255, 99, 132, 0.8);"></span>
                                            <span class="legend-label">Renouvellement</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: rgba(255, 205, 86, 0.8);"></span>
                                            <span class="legend-label">Perte/Vol</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: rgba(75, 192, 192, 0.8);"></span>
                                            <span class="legend-label">Autres</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières demandes et activités -->
            <div class="row g-4 mb-4">
                <!-- Dernières demandes -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Dernières demandes</h5>
                            <a href="demandes_cni.php" class="btn btn-sm btn-outline-primary">
                                Voir tout <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Référence</th>
                                        <th scope="col">Demandeur</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($demandes) > 0): ?>
                                        <?php foreach ($demandes as $demande): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-medium"><?php echo htmlspecialchars($demande['NumeroReference'] ?? 'N/A'); ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($demande['PhotoUtilisateur']) && file_exists($demande['PhotoUtilisateur'])): ?>
                                                            <img src="<?php echo htmlspecialchars($demande['PhotoUtilisateur']); ?>" class="avatar-sm rounded-circle me-2" alt="Photo">
                                                        <?php else: ?>
                                                            <div class="avatar-sm bg-primary-light text-primary rounded-circle me-2">
                                                                <?php 
                                                                    $initials = strtoupper(substr($demande['PrenomDemandeur'] ?? 'U', 0, 1) . substr($demande['NomDemandeur'] ?? 'N', 0, 1));
                                                                    echo $initials;
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div class="fw-medium"><?php echo htmlspecialchars(($demande['PrenomDemandeur'] ?? '') . ' ' . ($demande['NomDemandeur'] ?? '')); ?></div>
                                                            <div class="text-muted small"><?php echo htmlspecialchars($demande['Email'] ?? 'N/A'); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $sousType = $demande['SousTypeDemande'] ?? '';
                                                        $typeLabel = '';
                                                        $typeBadgeClass = '';
                                                        
                                                        switch ($sousType) {
                                                            case 'premiere':
                                                                $typeLabel = 'Première demande';
                                                                $typeBadgeClass = 'bg-info';
                                                                break;
                                                            case 'renouvellement':
                                                                $typeLabel = 'Renouvellement';
                                                                $typeBadgeClass = 'bg-primary';
                                                                break;
                                                            case 'perte':
                                                                $typeLabel = 'Perte/Vol';
                                                                $typeBadgeClass = 'bg-warning';
                                                                break;
                                                            default:
                                                                $typeLabel = 'Autre';
                                                                $typeBadgeClass = 'bg-secondary';
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $typeBadgeClass; ?>"><?php echo $typeLabel; ?></span>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $date = new DateTime($demande['DateSoumission']);
                                                        echo $date->format('d/m/Y H:i');
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $statut = $demande['Statut'] ?? '';
                                                        $statutLabel = '';
                                                        $statutBadgeClass = '';
                                                        
                                                        switch ($statut) {
                                                            case 'Soumise':
                                                                $statutLabel = 'Nouvelle';
                                                                $statutBadgeClass = 'bg-primary';
                                                                break;
                                                            case 'EnCours':
                                                                $statutLabel = 'En cours';
                                                                $statutBadgeClass = 'bg-info';
                                                                break;
                                                            case 'Approuvee':
                                                                $statutLabel = 'Approuvée';
                                                                $statutBadgeClass = 'bg-success';
                                                                break;
                                                            case 'Rejetee':
                                                                $statutLabel = 'Rejetée';
                                                                $statutBadgeClass = 'bg-danger';
                                                                break;
                                                            case 'Terminee':
                                                                $statutLabel = 'Terminée';
                                                                $statutBadgeClass = 'bg-secondary';
                                                                break;
                                                            default:
                                                                $statutLabel = $statut;
                                                                $statutBadgeClass = 'bg-secondary';
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $statutBadgeClass; ?>"><?php echo $statutLabel; ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <span class="visually-hidden">Plus</span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>">Voir les détails</a></li>
                                                            <?php if ($statut === 'Soumise'): ?>
                                                                <li><a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=start">Commencer le traitement</a></li>
                                                            <?php endif; ?>
                                                            <?php if ($statut === 'EnCours'): ?>
                                                                <li><a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=approve">Approuver</a></li>
                                                                <li><a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=reject">Rejeter</a></li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $demande['DemandeID']; ?>">Supprimer</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="empty-state">
                                                    <i class="bi bi-inbox text-muted"></i>
                                                    <p>Aucune demande trouvée</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Pagination des demandes">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Précédent">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Suivant">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Activités récentes et réclamations -->
                <div class="col-lg-4">
                    <!-- Activités récentes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Activités récentes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="timeline">
                                <?php if (count($activities) > 0): ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-icon">
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($activity['NouveauStatut']) {
                                                        case 'EnCours':
                                                            $statusClass = 'bg-info';
                                                            $icon = 'hourglass-split';
                                                            break;
                                                        case 'Approuvee':
                                                            $statusClass = 'bg-success';
                                                            $icon = 'check-circle';
                                                            break;
                                                        case 'Rejetee':
                                                            $statusClass = 'bg-danger';
                                                            $icon = 'x-circle';
                                                            break;
                                                        case 'Terminee':
                                                            $statusClass = 'bg-secondary';
                                                            $icon = 'flag';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-primary';
                                                            $icon = 'arrow-right-circle';
                                                    }
                                                ?>
                                                <div class="icon-wrapper <?php echo $statusClass; ?>">
                                                    <i class="bi bi-<?php echo $icon; ?>"></i>
                                                </div>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($activity['PhotoUtilisateur']) && file_exists($activity['PhotoUtilisateur'])): ?>
                                                            <img src="<?php echo htmlspecialchars($activity['PhotoUtilisateur']); ?>" class="avatar-xs rounded-circle me-2" alt="Photo">
                                                        <?php else: ?>
                                                            <div class="avatar-xs bg-primary-light text-primary rounded-circle me-2">
                                                                <?php 
                                                                    $initials = strtoupper(substr($activity['Prenom'] ?? 'U', 0, 1) . substr($activity['Nom'] ?? 'N', 0, 1));
                                                                    echo $initials;
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <span class="fw-medium"><?php echo htmlspecialchars(($activity['Prenom'] ?? '') . ' ' . ($activity['Nom'] ?? 'Système')); ?></span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php 
                                                            $date = new DateTime($activity['DateModification']);
                                                            echo $date->format('d/m/Y H:i');
                                                        ?>
                                                    </small>
                                                </div>
                                                <div class="timeline-body">
                                                    <?php 
                                                        $statusText = '';
                                                        switch ($activity['NouveauStatut']) {
                                                            case 'EnCours':
                                                                $statusText = 'a commencé le traitement de';
                                                                break;
                                                            case 'Approuvee':
                                                                $statusText = 'a approuvé';
                                                                break;
                                                            case 'Rejetee':
                                                                $statusText = 'a rejeté';
                                                                break;
                                                            case 'Terminee':
                                                                $statusText = 'a finalisé';
                                                                break;
                                                            default:
                                                                $statusText = 'a modifié';
                                                        }
                                                    ?>
                                                    <p class="mb-1">
                                                        <?php echo $statusText; ?> la demande 
                                                        <a href="traiter_demande.php?id=<?php echo $activity['DemandeID']; ?>" class="fw-medium">
                                                            <?php echo htmlspecialchars($activity['NumeroReference'] ?? 'N/A'); ?>
                                                        </a>
                                                    </p>
                                                    <?php if (!empty($activity['Commentaire'])): ?>
                                                        <p class="text-muted small mb-0">
                                                            <i class="bi bi-quote me-1"></i><?php echo htmlspecialchars($activity['Commentaire']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state p-4 text-center">
                                        <i class="bi bi-activity text-muted"></i>
                                        <p>Aucune activité récente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (count($activities) > 0): ?>
                            <div class="card-footer text-center">
                                <a href="activites.php" class="btn btn-sm btn-link">Voir toutes les activités</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Réclamations récentes -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Réclamations récentes</h5>
                            <a href="reclamations.php" class="btn btn-sm btn-outline-primary">
                                Voir tout <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if (count($reclamations) > 0): ?>
                                    <?php foreach ($reclamations as $reclamation): ?>
                                        <a href="reclamations.php?id=<?php echo $reclamation['ReclamationID']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($reclamation['PhotoUtilisateur']) && file_exists($reclamation['PhotoUtilisateur'])): ?>
                                                        <img src="<?php echo htmlspecialchars($reclamation['PhotoUtilisateur']); ?>" class="avatar-sm rounded-circle me-2" alt="Photo">
                                                    <?php else: ?>
                                                        <div class="avatar-sm bg-primary-light text-primary rounded-circle me-2">
                                                            <?php 
                                                                $initials = strtoupper(substr($reclamation['Prenom'] ?? 'U', 0, 1) . substr($reclamation['Nom'] ?? 'N', 0, 1));
                                                                echo $initials;
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars(($reclamation['Prenom'] ?? '') . ' ' . ($reclamation['Nom'] ?? '')); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($reclamation['TypeReclamation'] ?? 'Réclamation'); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php 
                                                        $date = new DateTime($reclamation['DateCreation']);
                                                        echo $date->format('d/m/Y');
                                                    ?>
                                                </small>
                                            </div>
                                            <p class="mb-1 mt-2 text-truncate">
                                                <?php echo htmlspecialchars(substr($reclamation['Description'] ?? '', 0, 100) . (strlen($reclamation['Description'] ?? '') > 100 ? '...' : '')); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="badge bg-warning">Non traitée</span>
                                                <small class="text-primary">Voir détails</small>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state p-4 text-center">
                                        <i class="bi bi-chat-dots text-muted"></i>
                                        <p>Aucune réclamation en attente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tâches à faire -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tâches à faire</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter une tâche
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="task-column">
                                <h6 class="task-column-header">
                                    <i class="bi bi-list-task me-2"></i>À faire
                                    <span class="badge bg-secondary ms-2">3</span>
                                </h6>
                                <div class="task-list">
                                    <div class="task-card">
                                        <div class="task-card-header">
                                            <span class="task-priority high">Haute</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers En cours</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Vérifier les demandes en attente</h6>
                                        <p class="task-description">Traiter les demandes de CNI soumises depuis plus de 48h</p>
                                        <div class="task-meta">
                                            <span class="task-due-date">
                                                <i class="bi bi-calendar3 me-1"></i>Aujourd'hui
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-card">
                                        <div class="task-card-header">
                                            <span class="task-priority medium">Moyenne</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers En cours</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Répondre aux réclamations</h6>
                                        <p class="task-description">Traiter les réclamations des citoyens concernant leurs demandes</p>
                                        <div class="task-meta">
                                            <span class="task-due-date">
                                                <i class="bi bi-calendar3 me-1"></i>Demain
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-card">
                                        <div class="task-card-header">
                                            <span class="task-priority low">Basse</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers En cours</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Mettre à jour la documentation</h6>
                                        <p class="task-description">Actualiser les procédures de traitement des demandes</p>
                                        <div class="task-meta">
                                            <span class="task-due-date">
                                                <i class="bi bi-calendar3 me-1"></i>Cette semaine
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="task-column">
                                <h6 class="task-column-header">
                                    <i class="bi bi-hourglass-split me-2"></i>En cours
                                    <span class="badge bg-info ms-2">2</span>
                                </h6>
                                <div class="task-list">
                                    <div class="task-card">
                                        <div class="task-card-header">
                                            <span class="task-priority high">Haute</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers Terminé</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Finaliser les demandes approuvées</h6>
                                        <p class="task-description">Générer les CNI pour les demandes approuvées</p>
                                        <div class="task-meta">
                                            <span class="task-due-date">
                                                <i class="bi bi-calendar3 me-1"></i>Aujourd'hui
                                            </span>
                                            <div class="task-progress">
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small">75%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-card">
                                        <div class="task-card-header">
                                            <span class="task-priority medium">Moyenne</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers Terminé</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Préparer le rapport hebdomadaire</h6>
                                        <p class="task-description">Compiler les statistiques des demandes traitées</p>
                                        <div class="task-meta">
                                            <span class="task-due-date">
                                                <i class="bi bi-calendar3 me-1"></i>Demain
                                            </span>
                                            <div class="task-progress">
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 40%;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small">40%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="task-column">
                                <h6 class="task-column-header">
                                    <i class="bi bi-check-circle me-2"></i>Terminé
                                    <span class="badge bg-success ms-2">2</span>
                                </h6>
                                <div class="task-list">
                                    <div class="task-card completed">
                                        <div class="task-card-header">
                                            <span class="task-priority high">Haute</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Archiver</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers À faire</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Vérifier les documents en attente</h6>
                                        <p class="task-description">Valider les documents soumis par les citoyens</p>
                                        <div class="task-meta">
                                            <span class="task-completion-date">
                                                <i class="bi bi-check-circle-fill me-1"></i>Terminé hier
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-card completed">
                                        <div class="task-card-header">
                                            <span class="task-priority medium">Moyenne</span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Archiver</a></li>
                                                    <li><a class="dropdown-item" href="#">Déplacer vers À faire</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <h6 class="task-title">Former les nouveaux agents</h6>
                                        <p class="task-description">Session de formation sur le traitement des demandes</p>
                                        <div class="task-meta">
                                            <span class="task-completion-date">
                                                <i class="bi bi-check-circle-fill me-1"></i>Terminé il y a 3 jours
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal d'ajout de tâche -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaskModalLabel">Ajouter une nouvelle tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Titre</label>
                        <input type="text" class="form-control" id="taskTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" rows="3"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="taskPriority" class="form-label">Priorité</label>
                            <select class="form-select" id="taskPriority">
                                <option value="low">Basse</option>
                                <option value="medium" selected>Moyenne</option>
                                <option value="high">Haute</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskDueDate" class="form-label">Date d'échéance</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="taskStatus" class="form-label">Statut</label>
                        <select class="form-select" id="taskStatus">
                            <option value="todo" selected>À faire</option>
                            <option value="inprogress">En cours</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveTaskBtn">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #1774df;
    --primary-light: rgba(23, 116, 223, 0.1);
    --success: #28a745;
    --success-light: rgba(40, 167, 69, 0.1);
    --info: #17a2b8;
    --info-light: rgba(23, 162, 184, 0.1);
    --warning: #ffc107;
    --warning-light: rgba(255, 193, 7, 0.1);
    --danger: #dc3545;
    --danger-light: rgba(220, 53, 69, 0.1);
    --secondary: #6c757d;
    --secondary-light: rgba(108, 117, 125, 0.1);
    --dark: #343a40;
    --dark-light: rgba(52, 58, 64, 0.1);
}

/* Styles généraux */
body {
    background-color: #f8f9fa;
}

main {
    padding-bottom: 2rem;
}

.breadcrumb {
    font-size: 0.875rem;
}

/* Carte de bienvenue */
.welcome-card {
    background: linear-gradient(135deg, #1774df 0%, #0d6efd 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
}

.welcome-avatar {
    width: 64px;
    height: 64px;
    overflow: hidden;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
}

.welcome-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.welcome-message {
    opacity: 0.9;
}

/* Cartes statistiques */
.stat-card {
    border: none;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.bg-primary-light {
    background-color: var(--primary-light);
}

.bg-success-light {
    background-color: var(--success-light);
}

.bg-info-light {
    background-color: var(--info-light);
}

.bg-warning-light {
    background-color: var(--warning-light);
}

.bg-danger-light {
    background-color: var(--danger-light);
}

.bg-secondary-light {
    background-color: var(--secondary-light);
}

.bg-dark-light {
    background-color: var(--dark-light);
}

.stat-title {
    color: var(--secondary);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-weight: 600;
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.stat-change {
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}

.stat-change.increase {
    color: var(--success);
}

.stat-change.decrease {
    color: var(--danger);
}

.stat-change.neutral {
    color: var(--secondary);
}

.stat-change i {
    font-size: 1.25rem;
    margin-right: 0.25rem;
}

/* Avatars */
.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.avatar-xs {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.625rem;
    font-weight: 600;
}

/* Timeline */
.timeline {
    position: relative;
    padding: 1rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 20px;
    height: 100%;
    width: 2px;
    background-color: #e9ecef;
    z-index: 1;
}

.timeline-item {
    position: relative;
    padding-left: 45px;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    z-index: 2;
}

.icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.timeline-content {
    background-color: #fff;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

/* Légende des graphiques */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    margin-right: 0.5rem;
}

.legend-label {
    font-size: 0.875rem;
}

/* État vide */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    color: var(--secondary);
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.empty-state p {
    margin-bottom: 0;
}

/* Tâches */
.task-column {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    height: 100%;
}

.task-column-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.task-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-height: 400px;
    overflow-y: auto;
}

.task-card {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.task-card.completed {
    opacity: 0.7;
    border-left-color: var(--success);
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.task-priority {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.task-priority.high {
    background-color: var(--danger-light);
    color: var(--danger);
}

.task-priority.medium {
    background-color: var(--warning-light);
    color: var(--warning);
}

.task-priority.low {
    background-color: var(--info-light);
    color: var(--info);
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.task-description {
    font-size: 0.875rem;
    color: var(--secondary);
    margin-bottom: 0.75rem;
}

.task-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.75rem;
}

.task-due-date, .task-completion-date {
    display: flex;
    align-items: center;
    color: var(--secondary);
}

.task-progress {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.task-progress .progress {
    flex: 1;
}

/* Responsive */
@media (max-width: 992px) {
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .task-column {
        margin-bottom: 1rem;
    }
}

@media (max-width: 768px) {
    .welcome-card .row {
        flex-direction: column;
    }
    
    .welcome-avatar {
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des graphiques
    initCharts();
    
    // Gestion du bouton d'actualisation
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        location.reload();
    });
    
    // Gestion du modal de suppression
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            
            confirmDeleteBtn.addEventListener('click', function() {
                window.location.href = 'supprimer_demande.php?id=' + id;
            });
        });
    }
    
    // Gestion de l'ajout de tâche
    const saveTaskBtn = document.getElementById('saveTaskBtn');
    if (saveTaskBtn) {
        saveTaskBtn.addEventListener('click', function() {
            const taskForm = document.getElementById('taskForm');
            if (taskForm.checkValidity()) {
                // Simuler l'ajout d'une tâche (à remplacer par un appel AJAX)
                const taskTitle = document.getElementById('taskTitle').value;
                const taskDescription = document.getElementById('taskDescription').value;
                const taskPriority = document.getElementById('taskPriority').value;
                const taskStatus = document.getElementById('taskStatus').value;
                
                // Afficher un message de succès
                alert('Tâche ajoutée avec succès !');
                
                // Fermer le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
                modal.hide();
                
                // Recharger la page (à remplacer par une mise à jour dynamique)
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                taskForm.classList.add('was-validated');
            }
        });
    }
});

function initCharts() {
    // Graphique d'évolution des demandes
    const ctxDemandes = document.getElementById('demandesChart');
    if (ctxDemandes) {
        new Chart(ctxDemandes, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                        foreach ($monthlyStats as $stat) {
                            $date = new DateTime($stat['month'] . '-01');
                            echo "'" . $date->format('M Y') . "', ";
                        }
                    ?>
                ],
                datasets: [{
                    label: 'Demandes soumises',
                    data: [
                        <?php 
                            foreach ($monthlyStats as $stat) {
                                echo $stat['total'] . ", ";
                            }
                        ?>
                    ],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Demandes traitées',
                    data: [
                        <?php 
                            foreach ($monthlyStats as $stat) {
                                echo $stat['completed'] . ", ";
                            }
                        ?>
                    ],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    // Graphique de répartition par type
    const ctxType = document.getElementById('typeChart');
    if (ctxType) {
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: ['Première demande', 'Renouvellement', 'Perte/Vol', 'Autres'],
                datasets: [{
                    data: [
                        <?php echo isset($typeStats['premiere']) ? $typeStats['premiere'] : 0; ?>,
                        <?php echo isset($typeStats['renouvellement']) ? $typeStats['renouvellement'] : 0; ?>,
                        <?php echo isset($typeStats['perte']) ? $typeStats['perte'] : 0; ?>,
                        <?php echo $stats['total'] - (
                            (isset($typeStats['premiere']) ? $typeStats['premiere'] : 0) + 
                            (isset($typeStats['renouvellement']) ? $typeStats['renouvellement'] : 0) + 
                            (isset($typeStats['perte']) ? $typeStats['perte'] : 0)
                        ); ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }
}
</script>

