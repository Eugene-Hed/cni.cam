<?php
session_start();
include('../includes/config.php');

// Vérification de la session président
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: /pages/login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$userId = $_SESSION['user_id'];
$query = "SELECT Prenom, Nom, PhotoUtilisateur FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Récupération des statistiques Nationalité
$stats = [
    'nouvelles' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Soumise' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'encours' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'EnCours' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'approuvees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Approuvee' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'terminees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Terminee' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'rejetees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Rejetee' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'total' => $db->query("SELECT COUNT(*) FROM demandes WHERE TypeDemande = 'NATIONALITE'")->fetchColumn()
];

// Récupération des statistiques par type de demande
$typeStats = $db->query("
    SELECT SousTypeDemande, COUNT(*) as count 
    FROM demandes 
    WHERE TypeDemande = 'NATIONALITE' 
    GROUP BY SousTypeDemande
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Récupération des statistiques mensuelles pour le graphique
$monthlyStats = $db->query("
    SELECT 
        DATE_FORMAT(DateSoumission, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN Statut = 'Terminee' THEN 1 ELSE 0 END) as completed
    FROM demandes
    WHERE TypeDemande = 'NATIONALITE' AND DateSoumission >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(DateSoumission, '%Y-%m')
    ORDER BY month ASC
")->fetchAll();

// Récupération des dernières demandes Nationalité avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$query = "SELECT d.*, dn.Nom as NomDemandeur, dn.Prenom as PrenomDemandeur, 
                 u.PhotoUtilisateur, u.NumeroTelephone, u.Email
          FROM demandes d 
          LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID 
          LEFT JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.TypeDemande = 'NATIONALITE'
          ORDER BY d.DateSoumission DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$demandes = $stmt->fetchAll();

// Calcul du nombre total de pages
$total_demandes = $db->query("SELECT COUNT(*) FROM demandes WHERE TypeDemande = 'NATIONALITE'")->fetchColumn();
$total_pages = ceil($total_demandes / $limit);

// Récupération des activités récentes
$activities = $db->query("
    SELECT h.*, d.NumeroReference, d.TypeDemande, u.Prenom, u.Nom, u.PhotoUtilisateur
    FROM historique_demandes h
    JOIN demandes d ON h.DemandeID = d.DemandeID
    LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
    WHERE d.TypeDemande = 'NATIONALITE'
    ORDER BY h.DateModification DESC
    LIMIT 10
")->fetchAll();

// Récupération des réclamations récentes
$reclamations = $db->query("
    SELECT r.*, u.Prenom, u.Nom, u.PhotoUtilisateur
    FROM reclamations r
    JOIN utilisateurs u ON r.UtilisateurID = u.UtilisateurID
    WHERE r.Statut = 'Ouverte' AND r.TypeReclamation = 'Nationalité'
    ORDER BY r.DateCreation DESC
    LIMIT 5
")->fetchAll();

// Inclusion des templates
include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('../includes/president_sidebar.php'); ?>

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
                            <h4 class="welcome-title">Bienvenue, Monsieur le Président !</h4>
                            <p class="welcome-message mb-0">
                                Vous avez <strong><?php echo $stats['nouvelles']; ?> nouvelles demandes</strong> de nationalité à examiner et <strong><?php echo $stats['approuvees']; ?> demandes</strong> approuvées en attente de signature.
                            </p>
                        </div>
                        <div class="col-md-4 col-lg-3 mt-3 mt-md-0">
                            <a href="demandes_nationalite.php" class="btn btn-primary w-100">
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
                <!-- Graphique d'évolution des demandes -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Évolution des demandes de nationalité</h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active">6 mois</button>
                                <button type="button" class="btn btn-outline-secondary">1 an</button>
                                <button type="button" class="btn btn-outline-secondary">Tout</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="demandesChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Répartition par type de demande -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Répartition par type</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="typesChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dernières demandes et activités -->
            <div class="row g-4 mb-4">
                <!-- Dernières demandes -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Dernières demandes de nationalité</h5>
                            <a href="demandes_nationalite.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-list-ul me-1"></i>Voir toutes
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Référence</th>
                                            <th>Demandeur</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($demandes)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">Aucune demande trouvée</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($demandes as $demande): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fw-medium"><?php echo htmlspecialchars($demande['NumeroReference']); ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                                <?php if (!empty($demande['PhotoUtilisateur']) && file_exists($demande['PhotoUtilisateur'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($demande['PhotoUtilisateur']); ?>" alt="Photo" class="avatar-img rounded-circle">
                                                                <?php else: ?>
                                                                    <div class="avatar-placeholder">
                                                                        <?php 
                                                                            $initials = '';
                                                                            if (!empty($demande['NomDemandeur'])) {
                                                                                $initials .= strtoupper(substr($demande['NomDemandeur'], 0, 1));
                                                                            }
                                                                            if (!empty($demande['PrenomDemandeur'])) {
                                                                                $initials .= strtoupper(substr($demande['PrenomDemandeur'], 0, 1));
                                                                            }
                                                                            echo $initials ?: '?';
                                                                        ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($demande['NomDemandeur'] . ' ' . $demande['PrenomDemandeur']); ?></div>
                                                                <div class="small text-muted">
                                                                    <?php if (!empty($demande['Email'])): ?>
                                                                        <i class="bi bi-envelope-fill me-1"></i><?php echo htmlspecialchars($demande['Email']); ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div><?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?></div>
                                                        <div class="small text-muted"><?php echo date('H:i', strtotime($demande['DateSoumission'])); ?></div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            $statusClasses = [
                                                                'Soumise' => 'bg-secondary',
                                                                'EnCours' => 'bg-primary',
                                                                'Approuvee' => 'bg-success',
                                                                'Rejetee' => 'bg-danger',
                                                                'Terminee' => 'bg-info'
                                                            ];
                                                            $statusClass = $statusClasses[$demande['Statut']] ?? 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($demande['Statut']); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye me-1"></i>Traiter
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer bg-white">
                                    <nav aria-label="Pagination des demandes">
                                        <ul class="pagination pagination-sm justify-content-center mb-0">
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
                </div>
                
                <!-- Activités récentes -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Activités récentes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="timeline">
                                <?php if (empty($activities)): ?>
                                    <div class="text-center py-4">
                                        <div class="text-muted">Aucune activité récente</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-icon 
                                                <?php 
                                                    switch ($activity['Action']) {
                                                        case 'Création': echo 'bg-primary'; break;
                                                        case 'Modification': echo 'bg-info'; break;
                                                        case 'Approbation': echo 'bg-success'; break;
                                                        case 'Rejet': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                <i class="bi 
                                                    <?php 
                                                        switch ($activity['Action']) {
                                                            case 'Création': echo 'bi-plus-lg'; break;
                                                            case 'Modification': echo 'bi-pencil'; break;
                                                            case 'Approbation': echo 'bi-check-lg'; break;
                                                            case 'Rejet': echo 'bi-x-lg'; break;
                                                            default: echo 'bi-arrow-right';
                                                        }
                                                    ?>">
                                                </i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="avatar avatar-xs me-2">
                                                        <?php if (!empty($activity['PhotoUtilisateur']) && file_exists($activity['PhotoUtilisateur'])): ?>
                                                            <img src="<?php echo htmlspecialchars($activity['PhotoUtilisateur']); ?>" alt="Photo" class="avatar-img rounded-circle">
                                                        <?php else: ?>
                                                            <div class="avatar-placeholder avatar-xs">
                                                                <?php 
                                                                    $initials = '';
                                                                    if (!empty($activity['Prenom'])) {
                                                                        $initials .= strtoupper(substr($activity['Prenom'], 0, 1));
                                                                    }
                                                                    if (!empty($activity['Nom'])) {
                                                                        $initials .= strtoupper(substr($activity['Nom'], 0, 1));
                                                                    }
                                                                    echo $initials ?: '?';
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($activity['Prenom'] . ' ' . $activity['Nom']); ?></div>
                                                </div>
                                                <p class="mb-1">
                                                    <span class="fw-medium"><?php echo htmlspecialchars($activity['Action']); ?></span> de la demande 
                                                    <a href="traiter_demande.php?id=<?php echo $activity['DemandeID']; ?>" class="fw-medium">
                                                        <?php echo htmlspecialchars($activity['NumeroReference']); ?>
                                                    </a>
                                                </p>
                                                <div class="text-muted small">
                                                    <?php echo date('d/m/Y H:i', strtotime($activity['DateModification'])); ?>
                                                </div>
                                                <?php if (!empty($activity['Commentaire'])): ?>
                                                    <div class="mt-2 p-2 bg-light rounded small">
                                                        <?php echo htmlspecialchars($activity['Commentaire']); ?>
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
            </div>
            
            <!-- Réclamations récentes -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Réclamations récentes</h5>
                            <a href="reclamations.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-chat-dots me-1"></i>Voir toutes
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($reclamations)): ?>
                                <div class="text-center py-4">
                                    <div class="text-muted">Aucune réclamation en attente</div>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Utilisateur</th>
                                                <th>Sujet</th>
                                                <th>Date</th>
                                                <th>Priorité</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reclamations as $reclamation): ?>
                                                <tr>
                                                    <td>#<?php echo str_pad($reclamation['ReclamationID'], 5, '0', STR_PAD_LEFT); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2">
                                                            <?php if (!empty($reclamation['PhotoUtilisateur']) && file_exists($reclamation['PhotoUtilisateur'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($reclamation['PhotoUtilisateur']); ?>" alt="Photo" class="avatar-img rounded-circle">
                                                                <?php else: ?>
                                                                    <div class="avatar-placeholder">
                                                                        <?php 
                                                                            $initials = '';
                                                                            if (!empty($reclamation['Prenom'])) {
                                                                                $initials .= strtoupper(substr($reclamation['Prenom'], 0, 1));
                                                                            }
                                                                            if (!empty($reclamation['Nom'])) {
                                                                                $initials .= strtoupper(substr($reclamation['Nom'], 0, 1));
                                                                            }
                                                                            echo $initials ?: '?';
                                                                        ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($reclamation['Prenom'] . ' ' . $reclamation['Nom']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($reclamation['Sujet']); ?></td>
                                                    <td>
                                                        <div><?php echo date('d/m/Y', strtotime($reclamation['DateCreation'])); ?></div>
                                                        <div class="small text-muted"><?php echo date('H:i', strtotime($reclamation['DateCreation'])); ?></div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            $priorityClasses = [
                                                                'Basse' => 'bg-success',
                                                                'Moyenne' => 'bg-warning',
                                                                'Haute' => 'bg-danger',
                                                                'Urgente' => 'bg-danger'
                                                            ];
                                                            $priorityClass = $priorityClasses[$reclamation['Priorite']] ?? 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars($reclamation['Priorite']); ?></span>
                                                    </td>
                                                    <td>
                                                        <a href="traiter_reclamation.php?id=<?php echo $reclamation['ReclamationID']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-chat me-1"></i>Répondre
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Styles spécifiques au tableau de bord -->
<style>
/* Variables */
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

/* Carte de bienvenue */
.welcome-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    border-radius: 0.75rem;
}

.welcome-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
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
    background-color: var(--primary);
    color: white;
    font-weight: bold;
    font-size: 1.5rem;
}

.welcome-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.welcome-message {
    color: #495057;
}

/* Cartes de statistiques */
.stat-card {
    border: none;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.bg-primary-light { background-color: var(--primary-light); }
.bg-success-light { background-color: var(--success-light); }
.bg-info-light { background-color: var(--info-light); }
.bg-warning-light { background-color: var(--warning-light); }
.bg-danger-light { background-color: var(--danger-light); }
.bg-secondary-light { background-color: var(--secondary-light); }
.bg-dark-light { background-color: var(--dark-light); }

.stat-title {
    color: #6c757d;
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

/* Avatar */
.avatar {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background-color: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-xs {
    width: 24px;
    height: 24px;
}

.avatar-img {
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
    background-color: var(--primary);
    color: white;
    font-weight: bold;
    font-size: 1rem;
}

.avatar-placeholder.avatar-sm {
    font-size: 0.875rem;
}

.avatar-placeholder.avatar-xs {
    font-size: 0.75rem;
}

/* Timeline */
.timeline {
    position: relative;
    padding: 1rem;
}

.timeline-item {
    position: relative;
    padding-left: 2.5rem;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 1.5rem;
    bottom: 0;
    width: 1px;
    background-color: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.timeline-content {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
}

/* Responsive */
@media (max-width: 992px) {
    .stat-card {
        margin-bottom: 1rem;
    }
}

@media (max-width: 768px) {
    .welcome-avatar {
        width: 48px;
        height: 48px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .welcome-card .row {
        flex-direction: column;
    }
    
    .welcome-avatar {
        margin-bottom: 1rem;
    }
}
</style>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour le graphique d'évolution des demandes
    const monthlyData = <?php echo json_encode($monthlyStats); ?>;
    const months = monthlyData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
    });
    const totalDemandes = monthlyData.map(item => parseInt(item.total));
    const completedDemandes = monthlyData.map(item => parseInt(item.completed));
    
    // Graphique d'évolution des demandes
    const demandesCtx = document.getElementById('demandesChart').getContext('2d');
    const demandesChart = new Chart(demandesCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Demandes soumises',
                    data: totalDemandes,
                    backgroundColor: 'rgba(23, 116, 223, 0.1)',
                    borderColor: '#1774df',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Demandes terminées',
                    data: completedDemandes,
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderColor: '#28a745',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
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
    
    // Données pour le graphique de répartition par type
    const typeData = <?php echo json_encode($typeStats); ?>;
    const typeLabels = Object.keys(typeData);
    const typeCounts = Object.values(typeData).map(value => parseInt(value));
    
    // Graphique de répartition par type
    const typesCtx = document.getElementById('typesChart').getContext('2d');
    const typesChart = new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeCounts,
                backgroundColor: [
                    '#1774df',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });
    
    // Actualisation du tableau de bord
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        location.reload();
    });
});
</script>

<?php include('../includes/footer.php'); ?>
