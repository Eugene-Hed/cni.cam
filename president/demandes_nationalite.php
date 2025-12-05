<?php
session_start();
include('../includes/config.php');

// Vérification de la session président
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: /pages/login.php');
    exit();
}

// Filtres et recherche
$statut = isset($_GET['statut']) ? $_GET['statut'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_asc'; // Tri par défaut: plus ancienne d'abord (FIFO)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Construction de la requête avec pagination
$query = "SELECT d.*, dn.Nom, dn.Prenom, dn.DateNaissance, u.Email, u.NumeroTelephone 
          FROM demandes d
          LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID 
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.TypeDemande = 'NATIONALITE'";

$countQuery = "SELECT COUNT(*) FROM demandes d 
               LEFT JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID 
               WHERE d.TypeDemande = 'NATIONALITE'";

$params = [];

if ($statut) {
    $query .= " AND d.Statut = :statut";
    $countQuery .= " AND d.Statut = :statut";
    $params[':statut'] = $statut;
}
if ($type) {
    $query .= " AND d.SousTypeDemande = :type";
    $countQuery .= " AND d.SousTypeDemande = :type";
    $params[':type'] = $type;
}
if ($search) {
    $query .= " AND (dn.Nom LIKE :search OR dn.Prenom LIKE :search OR d.NumeroReference LIKE :search)";
    $countQuery .= " AND (dn.Nom LIKE :search OR dn.Prenom LIKE :search OR d.NumeroReference LIKE :search)";
    $params[':search'] = "%$search%";
}

// Filtre pour les demandes nécessitant une signature
if (isset($_GET['filter']) && $_GET['filter'] == 'signature') {
    $query .= " AND d.Statut = 'Approuvee' AND d.SignatureRequise = 1 AND (d.SignatureEnregistree = 0 OR d.SignatureEnregistree IS NULL)";
    $countQuery .= " AND d.Statut = 'Approuvee' AND d.SignatureRequise = 1 AND (d.SignatureEnregistree = 0 OR d.SignatureEnregistree IS NULL)";
}

// Comptage total pour pagination
$stmt = $db->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_demandes = $stmt->fetchColumn();
$total_pages = ceil($total_demandes / $limit);

// Tri FIFO (First In, First Out)
switch ($sort) {
    case 'date_asc':
        $query .= " ORDER BY d.DateSoumission ASC"; // Plus ancienne d'abord (FIFO)
        break;
    case 'date_desc':
        $query .= " ORDER BY d.DateSoumission DESC"; // Plus récente d'abord
        break;
    case 'nom_asc':
        $query .= " ORDER BY dn.Nom ASC, dn.Prenom ASC";
        break;
    case 'nom_desc':
        $query .= " ORDER BY dn.Nom DESC, dn.Prenom DESC";
        break;
    default:
        $query .= " ORDER BY d.DateSoumission ASC"; // FIFO par défaut
}

// Requête finale avec LIMIT
$query .= " LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$demandes = $stmt->fetchAll();

// Statistiques des demandes
$statsQuery = "SELECT 
                SUM(CASE WHEN Statut = 'Soumise' THEN 1 ELSE 0 END) as nouvelles,
                SUM(CASE WHEN Statut = 'EnCours' THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN Statut = 'Approuvee' THEN 1 ELSE 0 END) as approuvees,
                SUM(CASE WHEN Statut = 'Rejetee' THEN 1 ELSE 0 END) as rejetees,
                SUM(CASE WHEN Statut = 'Terminee' THEN 1 ELSE 0 END) as terminees,
                COUNT(*) as total
              FROM demandes 
              WHERE TypeDemande = 'NATIONALITE'";
$stmt = $db->prepare($statsQuery);
$stmt->execute();
$stats = $stmt->fetch();

// Statistiques des signatures en attente
$signatureQuery = "SELECT COUNT(*) as signatures_en_attente
                  FROM demandes 
                  WHERE TypeDemande = 'NATIONALITE' 
                  AND Statut = 'Approuvee' 
                  AND SignatureRequise = 1 
                  AND (SignatureEnregistree = 0 OR SignatureEnregistree IS NULL)";
$stmt = $db->prepare($signatureQuery);
$stmt->execute();
$signatures_en_attente = $stmt->fetchColumn();

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('../includes/president_sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des demandes de nationalité</h1>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportExcel">
                            <i class="bi bi-file-excel"></i> Exporter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistiques des demandes -->
            <div class="row mb-4">
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-primary rounded-circle p-3">
                                    <i class="bi bi-inbox-fill fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['nouvelles'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">Nouvelles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-info rounded-circle p-3">
                                    <i class="bi bi-hourglass-split fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['en_cours'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">En cours</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-success rounded-circle p-3">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['approuvees'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">Approuvées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-danger rounded-circle p-3">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['rejetees'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">Rejetées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-secondary rounded-circle p-3">
                                    <i class="bi bi-archive-fill fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['terminees'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">Terminées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <span class="badge bg-dark rounded-circle p-3">
                                    <i class="bi bi-stack fs-5"></i>
                                </span>
                            </div>
                            <h3 class="mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                            <p class="text-muted small mb-0">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($signatures_en_attente > 0): ?>
            <div class="alert alert-warning mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
                    <div>
                        <h5 class="mb-1">Signatures en attente</h5>
                        <p class="mb-0">Il y a <?php echo $signatures_en_attente; ?> demande(s) approuvée(s) en attente de signature présidentielle.</p>
                        <a href="demandes_nationalite.php?filter=signature" class="btn btn-sm btn-warning mt-2">
                            <i class="bi bi-filter me-1"></i> Filtrer les demandes en attente de signature
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="Soumise" <?php echo $statut == 'Soumise' ? 'selected' : ''; ?>>Soumise</option>
                                <option value="EnCours" <?php echo $statut == 'EnCours' ? 'selected' : ''; ?>>En cours</option>
                                <option value="Approuvee" <?php echo $statut == 'Approuvee' ? 'selected' : ''; ?>>Approuvée</option>
                                <option value="Terminee" <?php echo $statut == 'Terminee' ? 'selected' : ''; ?>>Terminée</option>
                                <option value="Rejetee" <?php echo $statut == 'Rejetee' ? 'selected' : ''; ?>>Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type de demande</label>
                            <select name="type" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="naissance" <?php echo $type == 'naissance' ? 'selected' : ''; ?>>Par naissance</option>
                                <option value="mariage" <?php echo $type == 'mariage' ? 'selected' : ''; ?>>Par mariage</option>
                                <option value="naturalisation" <?php echo $type == 'naturalisation' ? 'selected' : ''; ?>>Par naturalisation</option>
                                <option value="filiation" <?php echo $type == 'filiation' ? 'selected' : ''; ?>>Par filiation</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou référence" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tri</label>
                            <select name="sort" class="form-select">
                                <option value="date_asc" <?php echo $sort == 'date_asc' ? 'selected' : ''; ?>>Date (plus ancienne d'abord)</option>
                                <option value="date_desc" <?php echo $sort == 'date_desc' ? 'selected' : ''; ?>>Date (plus récente d'abord)</option>
                                <option value="nom_asc" <?php echo $sort == 'nom_asc' ? 'selected' : ''; ?>>Nom (A-Z)</option>
                                <option value="nom_desc" <?php echo $sort == 'nom_desc' ? 'selected' : ''; ?>>Nom (Z-A)</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-grid gap-2 w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter me-1"></i> Filtrer
                                </button>
                                <a href="demandes_nationalite.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des demandes -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des demandes de nationalité</h5>
                    <span class="badge bg-primary"><?php echo $total_demandes; ?> demande(s) trouvée(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Demandeur</th>
                                    <th>Type</th>
                                    <th>Date de soumission</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($demandes)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bi bi-inbox text-muted mb-3" style="font-size: 2rem;"></i>
                                                <p class="text-muted">Aucune demande trouvée</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($demandes as $demande): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-medium"><?php echo htmlspecialchars($demande['NumeroReference']); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-placeholder avatar-sm me-2">
                                                        <?php 
                                                            $initials = '';
                                                            if (!empty($demande['Nom'])) {
                                                                $initials .= strtoupper(substr($demande['Nom'], 0, 1));
                                                            }
                                                            if (!empty($demande['Prenom'])) {
                                                                $initials .= strtoupper(substr($demande['Prenom'], 0, 1));
                                                            }
                                                            echo $initials ?: '?';
                                                        ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></div>
                                                        <div class="small text-muted">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            <?php echo date('d/m/Y', strtotime($demande['DateNaissance'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $typeLabels = [
                                                        'naissance' => 'Par naissance',
                                                        'mariage' => 'Par mariage',
                                                        'naturalisation' => 'Par naturalisation',
                                                        'filiation' => 'Par filiation'
                                                    ];
                                                    echo isset($demande['SousTypeDemande']) && isset($typeLabels[$demande['SousTypeDemande']]) 
            ? $typeLabels[$demande['SousTypeDemande']] 
            : 'Non spécifié'; 
                                                ?>
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
                                                    
                                                    // Ajouter un indicateur pour les demandes en attente de signature
                                                    $signatureEnAttente = $demande['Statut'] == 'Approuvee' && 
                                                                          $demande['SignatureRequise'] == 1 && 
                                                                          (!$demande['SignatureEnregistree'] || $demande['SignatureEnregistree'] == 0);
                                                ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($demande['Statut']); ?></span>
                                                    <?php if ($signatureEnAttente): ?>
                                                        <span class="badge bg-warning ms-1" data-bs-toggle="tooltip" title="En attente de signature présidentielle">
                                                            <i class="bi bi-pen"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye me-1"></i>Traiter
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <?php if ($demande['Statut'] == 'Soumise'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=start">
                                                                    <i class="bi bi-play-fill text-primary me-2"></i>Démarrer le traitement
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($demande['Statut'] == 'EnCours'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=approve">
                                                                    <i class="bi bi-check-circle text-success me-2"></i>Approuver
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=reject">
                                                                    <i class="bi bi-x-circle text-danger me-2"></i>Rejeter
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($signatureEnAttente): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="signer_demande.php?id=<?php echo $demande['DemandeID']; ?>">
                                                                    <i class="bi bi-pen text-warning me-2"></i>Signer le certificat
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <li><hr class="dropdown-divider"></li>
                                                        
                                                        <li>
                                                            <a class="dropdown-item" href="historique_demande.php?id=<?php echo $demande['DemandeID']; ?>">
                                                                <i class="bi bi-clock-history text-info me-2"></i>Historique
                                                            </a>
                                                        </li>
                                                        
                                                        <?php if ($demande['Statut'] == 'Approuvee' || $demande['Statut'] == 'Terminee'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="generer_certificat.php?id=<?php echo $demande['DemandeID']; ?>" target="_blank">
                                                                    <i class="bi bi-file-pdf text-danger me-2"></i>Certificat
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav aria-label="Pagination des demandes">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&statut=<?php echo urlencode($statut); ?>&type=<?php echo urlencode($type); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" aria-label="Précédent">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php
                                // Afficher un nombre limité de pages
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&statut=' . urlencode($statut) . '&type=' . urlencode($type) . '&search=' . urlencode($search) . '&sort=' . urlencode($sort) . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    echo '<li class="page-item ' . (($page == $i) ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&statut=' . urlencode($statut) . '&type=' . urlencode($type) . '&search=' . urlencode($search) . '&sort=' . urlencode($sort) . '">' . $i . '</a></li>';
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&statut=' . urlencode($statut) . '&type=' . urlencode($type) . '&search=' . urlencode($search) . '&sort=' . urlencode($sort) . '">' . $total_pages . '</a></li>';
                                }
                                ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&statut=<?php echo urlencode($statut); ?>&type=<?php echo urlencode($type); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" aria-label="Suivant">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Styles spécifiques à la page -->
<style>
/* Styles pour les avatars */
.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.avatar-placeholder {
    background-color: #1774df;
    color: white;
    font-weight: bold;
    font-size: 0.875rem;
}

/* Styles pour les badges */
.badge {
    font-weight: 500;
    padding: 0.4em 0.6em;
}

/* Styles pour les cartes de statistiques */
.card {
    border-radius: 0.75rem;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Styles pour les boutons d'action */
.btn-group .dropdown-menu {
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: none;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: rgba(23, 116, 223, 0.1);
    transform: translateX(5px);
}

/* Styles pour la pagination */
.pagination .page-link {
    border-radius: 0.25rem;
    margin: 0 0.1rem;
    color: #1774df;
}

.pagination .page-item.active .page-link {
    background-color: #1774df;
    border-color: #1774df;
}

/* Styles pour les alertes */
.alert {
    border-radius: 0.75rem;
    border: none;
}

/* Animation pour les badges de notification */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.badge.bg-warning {
    animation: pulse 2s infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .table th, .table td {
        padding: 0.75rem;
    }
}
</style>

<!-- Scripts spécifiques à la page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Fonction d'exportation Excel
    document.getElementById('exportExcel').addEventListener('click', function() {
        // Redirection vers un script d'export avec les mêmes paramètres de filtrage
        const params = new URLSearchParams(window.location.search);
        window.location.href = 'export_demandes.php?type=excel&' + params.toString();
    });
    
    // Confirmation pour les actions importantes
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Mise en évidence des lignes au survol
    document.querySelectorAll('tbody tr').forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.classList.add('bg-light');
        });
        
        row.addEventListener('mouseleave', function() {
            this.classList.remove('bg-light');
        });
    });
    
    // Gestion des filtres avancés
    const toggleFilters = document.getElementById('toggleFilters');
    const advancedFilters = document.getElementById('advancedFilters');
    
    if (toggleFilters && advancedFilters) {
        toggleFilters.addEventListener('click', function() {
            advancedFilters.classList.toggle('d-none');
            this.querySelector('i').classList.toggle('bi-chevron-down');
            this.querySelector('i').classList.toggle('bi-chevron-up');
        });
    }
    
    // Fonction pour réinitialiser les filtres
    document.querySelectorAll('.reset-filter').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filterForm = this.closest('form');
            const inputs = filterForm.querySelectorAll('input, select');
            
            inputs.forEach(function(input) {
                if (input.type === 'text' || input.type === 'search' || input.tagName === 'SELECT') {
                    input.value = '';
                } else if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                }
            });
            
            filterForm.submit();
        });
    });
    
    // Gestion des actions en masse (si implémentées)
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.demande-checkbox');
    
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = checkAll.checked;
            });
            
            // Activer/désactiver les boutons d'action en masse
            toggleBulkActions();
        });
        
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Vérifier si toutes les cases sont cochées
                const allChecked = Array.from(checkboxes).every(function(cb) {
                    return cb.checked;
                });
                
                // Mettre à jour l'état de la case "Tout cocher"
                checkAll.checked = allChecked;
                
                // Activer/désactiver les boutons d'action en masse
                toggleBulkActions();
            });
        });
        
        function toggleBulkActions() {
            const bulkActions = document.querySelectorAll('.bulk-action');
            const anyChecked = Array.from(checkboxes).some(function(cb) {
                return cb.checked;
            });
            
            bulkActions.forEach(function(action) {
                action.disabled = !anyChecked;
            });
        }
    }
});
</script>