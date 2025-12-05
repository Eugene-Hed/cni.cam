<?php
session_start();
include('../includes/config.php');

// Vérification de la session officier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
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
$query = "SELECT d.*, dc.Nom, dc.Prenom, dc.DateNaissance 
          FROM demandes d
          JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID 
          WHERE d.TypeDemande = 'CNI'";

$countQuery = "SELECT COUNT(*) FROM demandes d 
               JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID 
               WHERE d.TypeDemande = 'CNI'";

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
    $query .= " AND (dc.Nom LIKE :search OR dc.Prenom LIKE :search OR d.NumeroReference LIKE :search)";
    $countQuery .= " AND (dc.Nom LIKE :search OR dc.Prenom LIKE :search OR d.NumeroReference LIKE :search)";
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
        $query .= " ORDER BY dc.Nom ASC, dc.Prenom ASC";
        break;
    case 'nom_desc':
        $query .= " ORDER BY dc.Nom DESC, dc.Prenom DESC";
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
              WHERE TypeDemande = 'CNI'";
$stmt = $db->prepare($statsQuery);
$stmt->execute();
$stats = $stmt->fetch();

// Statistiques des signatures en attente
$signatureQuery = "SELECT COUNT(*) as signatures_en_attente
                  FROM demandes 
                  WHERE TypeDemande = 'CNI' 
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
        <?php include('includes/sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des demandes CNI</h1>
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
                        <p class="mb-0">Il y a <?php echo $signatures_en_attente; ?> demande(s) approuvée(s) en attente de signature du citoyen.</p>
                        <a href="demandes_cni.php?filter=signature" class="btn btn-sm btn-warning mt-2">
                            <i class="bi bi-filter me-1"></i> Filtrer les demandes en attente de signature
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($demande['Statut'] == 'Approuvee'): ?>
    <?php if (isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1 && (!isset($demande['SignatureOfficierEnregistree']) || $demande['SignatureOfficierEnregistree'] == 0)): ?>
        <li>
            <a class="dropdown-item" href="enregistrer_signature_officier.php?id=<?php echo $demande['DemandeID']; ?>">
                <i class="bi bi-pen text-warning"></i> Enregistrer ma signature
            </a>
        </li>
    <?php elseif (isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1 && isset($demande['SignatureOfficierEnregistree']) && $demande['SignatureOfficierEnregistree'] == 1): ?>
        <li>
            <a class="dropdown-item" href="generer_cni.php?id=<?php echo $demande['DemandeID']; ?>">
                <i class="bi bi-file-earmark-pdf text-primary"></i> Générer CNI
            </a>
        </li>
    <?php endif; ?>
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
                                <option value="premiere" <?php echo $type == 'premiere' ? 'selected' : ''; ?>>Première demande</option>
                                <option value="renouvellement" <?php echo $type == 'renouvellement' ? 'selected' : ''; ?>>Renouvellement</option>
                                <option value="perte" <?php echo $type == 'perte' ? 'selected' : ''; ?>>Perte</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tri</label>
                            <select name="sort" class="form-select">
                                <option value="date_asc" <?php echo $sort == 'date_asc' ? 'selected' : ''; ?>>Date (plus ancienne d'abord - FIFO)</option>
                                <option value="date_desc" <?php echo $sort == 'date_desc' ? 'selected' : ''; ?>>Date (plus récente d'abord)</option>
                                <option value="nom_asc" <?php echo $sort == 'nom_asc' ? 'selected' : ''; ?>>Nom (A-Z)</option>
                                <option value="nom_desc" <?php echo $sort == 'nom_desc' ? 'selected' : ''; ?>>Nom (Z-A)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rechercher</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou référence..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="demandes_cni.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle"></i> Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des demandes -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des demandes</h5>
                    <span class="badge bg-primary"><?php echo $total_demandes; ?> demande(s) trouvée(s)</span>
                </div>
                <div class="card-body">
                    <?php if(empty($demandes)): ?>
                        <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="mt-3">Aucune demande trouvée</h4>
                            <p class="text-muted">Modifiez vos critères de recherche ou consultez plus tard.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#Référence</th>
                                        <th scope="col">Demandeur</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date de soumission</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Temps d'attente</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($demandes as $demande): 
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
                                        
                                        // Déterminer la classe de priorité basée sur le temps d'attente
                                        $priority_class = '';
                                        if ($demande['Statut'] == 'Soumise') {
                                            if ($interval->days >= 14) {
                                                $priority_class = 'table-danger'; // Urgent (plus de 2 semaines)
                                            } elseif ($interval->days >= 7) {
                                                $priority_class = 'table-warning'; // Prioritaire (plus d'1 semaine)
                                            }
                                        }
                                    ?>
                                    <tr class="<?php echo $priority_class; ?>">
                                        <td>
                                            <span class="fw-medium"><?php echo htmlspecialchars($demande['NumeroReference']); ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initials me-2 bg-primary">
                                                    <?php 
                                                        $initials = mb_substr($demande['Prenom'], 0, 1) . mb_substr($demande['Nom'], 0, 1);
                                                        echo strtoupper($initials);
                                                    ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?></div>
                                                    <div class="small text-muted">
                                                        <?php 
                                                            $birthdate = new DateTime($demande['DateNaissance']);
                                                            $age = $birthdate->diff($now)->y;
                                                            echo $age . ' ans';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                                                                $type_labels = [
                                                                                                    'premiere' => '<span class="badge bg-primary">Première demande</span>',
                                                                                                    'renouvellement' => '<span class="badge bg-success">Renouvellement</span>',
                                                                                                    'perte' => '<span class="badge bg-warning text-dark">Perte</span>'
                                                                                                ];
                                                                                                echo $type_labels[$demande['SousTypeDemande']] ?? $demande['SousTypeDemande'];
                                                                                            ?>
                                                                                        </td>
                                                                                        <td>
                                                                                            <div><?php echo $date_soumission->format('d/m/Y'); ?></div>
                                                                                            <small class="text-muted"><?php echo $date_soumission->format('H:i'); ?></small>
                                                                                        </td>
                                                                                        <td>
                                                                                            <?php 
                                                                                                $status_badges = [
                                                                                                    'Soumise' => '<span class="badge bg-primary">Soumise</span>',
                                                                                                    'EnCours' => '<span class="badge bg-info">En cours</span>',
                                                                                                    'Approuvee' => '<span class="badge bg-success">Approuvée</span>',
                                                                                                    'Rejetee' => '<span class="badge bg-danger">Rejetée</span>',
                                                                                                    'Terminee' => '<span class="badge bg-secondary">Terminée</span>',
                                                                                                    'Annulee' => '<span class="badge bg-dark">Annulée</span>'
                                                                                                ];
                                                                                                echo $status_badges[$demande['Statut']] ?? $demande['Statut'];
                                                                                                
                                                                                                // Afficher un indicateur si la signature est requise mais pas encore enregistrée
                                                                                                if ($demande['Statut'] == 'Approuvee') {
                                                                                                    if (isset($demande['SignatureRequise']) && $demande['SignatureRequise'] == 1) {
                                                                                                        if (!isset($demande['SignatureEnregistree']) || $demande['SignatureEnregistree'] == 0) {
                                                                                                            echo ' <span class="badge bg-warning text-dark">Signature citoyen en attente</span>';
                                                                                                        } elseif (!isset($demande['SignatureOfficierEnregistree']) || $demande['SignatureOfficierEnregistree'] == 0) {
                                                                                                            echo ' <span class="badge bg-warning text-dark">Signature officier requise</span>';
                                                                                                        } else {
                                                                                                            echo ' <span class="badge bg-success">Signatures complètes</span>';
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            ?>
                                                                                        </td>
                                                                                        <td>
                                                                                            <?php if ($demande['Statut'] == 'Soumise' || $demande['Statut'] == 'EnCours'): ?>
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <?php if ($interval->days >= 14): ?>
                                                                                                        <span class="badge bg-danger me-2">Urgent</span>
                                                                                                    <?php elseif ($interval->days >= 7): ?>
                                                                                                        <span class="badge bg-warning text-dark me-2">Prioritaire</span>
                                                                                                    <?php endif; ?>
                                                                                                    <span><?php echo $waiting_time; ?></span>
                                                                                                </div>
                                                                                            <?php else: ?>
                                                                                                <span class="text-muted">Traité</span>
                                                                                            <?php endif; ?>
                                                                                        </td>
                                                                                        <td>
                                                                                            <div class="btn-group">
                                                                                                <a href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>" 
                                                                                                   class="btn btn-sm btn-primary">
                                                                                                    <i class="bi bi-eye"></i> Voir
                                                                                                </a>
                                                                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" 
                                                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                                                                </button>
                                                                                                <ul class="dropdown-menu">
                                                                                                    <?php if ($demande['Statut'] == 'Soumise'): ?>
                                                                                                    <li>
                                                                                                        <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=start">
                                                                                                            <i class="bi bi-play-fill text-info"></i> Commencer traitement
                                                                                                        </a>
                                                                                                    </li>
                                                                                                    <?php endif; ?>
                                                                                                    
                                                                                                    <?php if ($demande['Statut'] == 'EnCours'): ?>
                                                                                                    <li>
                                                                                                        <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=approve">
                                                                                                            <i class="bi bi-check-circle-fill text-success"></i> Approuver
                                                                                                        </a>
                                                                                                    </li>
                                                                                                    <li>
                                                                                                        <a class="dropdown-item" href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>&action=reject">
                                                                                                            <i class="bi bi-x-circle-fill text-danger"></i> Rejeter
                                                                                                        </a>
                                                                                                    </li>
                                                                                                    <?php endif; ?>
                                                                                                    
                                                                                                    <?php if ($demande['Statut'] == 'Approuvee' && isset($demande['SignatureEnregistree']) && $demande['SignatureEnregistree'] == 1): ?>
                                                                                                    <li>
                                                                                                        <a class="dropdown-item" href="generer_cni.php?id=<?php echo $demande['DemandeID']; ?>">
                                                                                                            <i class="bi bi-file-earmark-pdf text-primary"></i> Générer CNI
                                                                                                        </a>
                                                                                                    </li>
                                                                                                    <?php endif; ?>
                                                                                                    
                                                                                                    <li><hr class="dropdown-divider"></li>
                                                                                                    <li>
                                                                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" 
                                                                                                           data-bs-target="#historyModal" 
                                                                                                           data-demande-id="<?php echo $demande['DemandeID']; ?>">
                                                                                                            <i class="bi bi-clock-history text-secondary"></i> Historique
                                                                                                        </a>
                                                                                                    </li>
                                                                                                </ul>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        
                                                                        <!-- Pagination -->
                                                                        <?php if ($total_pages > 1): ?>
                                                                        <nav aria-label="Page navigation" class="mt-4">
                                                                            <ul class="pagination justify-content-center">
                                                                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&statut=<?php echo $statut; ?>&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                                                                                        <i class="bi bi-chevron-left"></i>
                                                                                    </a>
                                                                                </li>
                                                                                
                                                                                <?php
                                                                                $start_page = max(1, $page - 2);
                                                                                $end_page = min($total_pages, $page + 2);
                                                                                
                                                                                if ($start_page > 1) {
                                                                                    echo '<li class="page-item"><a class="page-link" href="?page=1&statut='.$statut.'&type='.$type.'&search='.urlencode($search).'&sort='.$sort.'">1</a></li>';
                                                                                    if ($start_page > 2) {
                                                                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                                                                    }
                                                                                }
                                                                                
                                                                                for ($i = $start_page; $i <= $end_page; $i++) {
                                                                                    echo '<li class="page-item '.($page == $i ? 'active' : '').'">
                                                                                            <a class="page-link" href="?page='.$i.'&statut='.$statut.'&type='.$type.'&search='.urlencode($search).'&sort='.$sort.'">'.$i.'</a>
                                                                                          </li>';
                                                                                }
                                                                                
                                                                                if ($end_page < $total_pages) {
                                                                                    if ($end_page < $total_pages - 1) {
                                                                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                                                                    }
                                                                                    echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&statut='.$statut.'&type='.$type.'&search='.urlencode($search).'&sort='.$sort.'">'.$total_pages.'</a></li>';
                                                                                }
                                                                                ?>
                                                                                
                                                                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&statut=<?php echo $statut; ?>&type=<?php echo $type; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                                                                                        <i class="bi bi-chevron-right"></i>
                                                                                    </a>
                                                                                </li>
                                                                            </ul>
                                                                        </nav>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </main>
                                                    </div>
                                                </div>
                                                
                                                <!-- Modal d'historique -->
                                                <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="historyModalLabel">Historique de la demande</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="text-center py-4" id="historyLoading">
                                                                    <div class="spinner-border text-primary" role="status">
                                                                        <span class="visually-hidden">Chargement...</span>
                                                                    </div>
                                                                    <p class="mt-2">Chargement de l'historique...</p>
                                                                </div>
                                                                <div id="historyContent" class="d-none">
                                                                    <ul class="timeline-steps" id="historyTimeline">
                                                                        <!-- L'historique sera inséré ici via AJAX -->
                                                                    </ul>
                                                                </div>
                                                                <div id="historyError" class="alert alert-danger d-none">
                                                                    Une erreur est survenue lors du chargement de l'historique.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <style>
                                                /* Styles pour les avatars avec initiales */
                                                .avatar-initials {
                                                    width: 40px;
                                                    height: 40px;
                                                    border-radius: 50%;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    color: white;
                                                    font-weight: bold;
                                                }
                                                
                                                /* Styles pour la timeline d'historique */
                                                .timeline-steps {
                                                    list-style: none;
                                                    padding-left: 0;
                                                    position: relative;
                                                }
                                                
                                                .timeline-steps:before {
                                                    content: '';
                                                    position: absolute;
                                                    left: 20px;
                                                    top: 0;
                                                    height: 100%;
                                                    width: 2px;
                                                    background-color: #e9ecef;
                                                }
                                                
                                                .timeline-step {
                                                    position: relative;
                                                    padding-left: 50px;
                                                    margin-bottom: 20px;
                                                }
                                                
                                                .timeline-step:last-child {
                                                    margin-bottom: 0;
                                                }
                                                
                                                .timeline-step-icon {
                                                    position: absolute;
                                                    left: 0;
                                                    top: 0;
                                                    width: 40px;
                                                    height: 40px;
                                                    border-radius: 50%;
                                                    background-color: #fff;
                                                    border: 2px solid #e9ecef;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    z-index: 1;
                                                }
                                                
                                                .timeline-step-content {
                                                    background-color: #f8f9fa;
                                                    border-radius: 0.5rem;
                                                    padding: 15px;
                                                }
                                                
                                                .timeline-step-title {
                                                    font-weight: 600;
                                                    margin-bottom: 5px;
                                                }
                                                
                                                .timeline-step-date {
                                                    font-size: 0.8rem;
                                                    color: #6c757d;
                                                }
                                                
                                                /* Couleurs pour les icônes de la timeline */
                                                .icon-submitted {
                                                    color: #1774df;
                                                }
                                                
                                                .icon-processing {
                                                    color: #17a2b8;
                                                }
                                                
                                                .icon-approved {
                                                    color: #28a745;
                                                }
                                                
                                                .icon-rejected {
                                                    color: #dc3545;
                                                }
                                                
                                                .icon-completed {
                                                    color: #6c757d;
                                                }
                                                
                                                /* Styles pour les lignes de priorité */
                                                tr.table-warning td, tr.table-danger td {
                                                    position: relative;
                                                }
                                                
                                                tr.table-warning::before, tr.table-danger::before {
                                                    content: '';
                                                    position: absolute;
                                                    left: 0;
                                                    top: 0;
                                                    height: 100%;
                                                    width: 4px;
                                                }
                                                
                                                tr.table-warning::before {
                                                    background-color: #ffc107;
                                                }
                                                
                                                tr.table-danger::before {
                                                    background-color: #dc3545;
                                                }
                                                </style>
                                                
                                                <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    // Gestion du modal d'historique
                                                    const historyModal = document.getElementById('historyModal');
                                                    if (historyModal) {
                                                        historyModal.addEventListener('show.bs.modal', function(event) {
                                                            const button = event.relatedTarget;
                                                            const demandeId = button.getAttribute('data-demande-id');
                                                            
                                                            // Réinitialiser le modal
                                                            document.getElementById('historyLoading').classList.remove('d-none');
                                                            document.getElementById('historyContent').classList.add('d-none');
                                                            document.getElementById('historyError').classList.add('d-none');
                                                            document.getElementById('historyTimeline').innerHTML = '';
                                                            
                                                            // Charger l'historique via AJAX
                                                            fetch(`get_history.php?demande_id=${demandeId}`)
                                                                .then(response => {
                                                                    if (!response.ok) {
                                                                        throw new Error('Erreur réseau');
                                                                    }
                                                                    return response.json();
                                                                })
                                                                .then(data => {
                                                                    if (data.success) {
                                                                        // Masquer le chargement
                                                                        document.getElementById('historyLoading').classList.add('d-none');
                                                                        document.getElementById('historyContent').classList.remove('d-none');
                                                                        
                                                                        // Construire la timeline
                                                                        const timeline = document.getElementById('historyTimeline');
                                                                        
                                                                        if (data.history.length === 0) {
                                                                            timeline.innerHTML = '<div class="text-center py-3">Aucun historique disponible</div>';
                                                                        } else {
                                                                            data.history.forEach(item => {
                                                                                // Déterminer l'icône en fonction du statut
                                                                                let iconClass = '';
                                                                                switch(item.NouveauStatut) {
                                                                                    case 'Soumise':
                                                                                        iconClass = 'bi-inbox-fill icon-submitted';
                                                                                        break;
                                                                                    case 'EnCours':
                                                                                        iconClass = 'bi-hourglass-split icon-processing';
                                                                                        break;
                                                                                    case 'Approuvee':
                                                                                        iconClass = 'bi-check-circle-fill icon-approved';
                                                                                        break;
                                                                                    case 'Rejetee':
                                                                                        iconClass = 'bi-x-circle-fill icon-rejected';
                                                                                        break;
                                                                                    case 'Terminee':
                                                                                        iconClass = 'bi-archive-fill icon-completed';
                                                                                        break;
                                                                                    default:
                                                                                        iconClass = 'bi-circle icon-default';
                                                                                }
                                                                                
                                                                                                                // Créer l'élément de timeline
                                const li = document.createElement('li');
                                li.className = 'timeline-step';
                                li.innerHTML = `
                                    <div class="timeline-step-icon">
                                        <i class="bi ${iconClass}"></i>
                                    </div>
                                    <div class="timeline-step-content">
                                        <div class="timeline-step-title">${getStatusLabel(item.NouveauStatut)}</div>
                                        <div class="timeline-step-date">${formatDate(item.DateModification)}</div>
                                        ${item.Commentaire ? `<div class="mt-2">${item.Commentaire}</div>` : ''}
                                        ${item.ModifiePar ? `<div class="small text-muted mt-1">Par: ${item.ModifiePar}</div>` : ''}
                                    </div>
                                `;
                                timeline.appendChild(li);
                            });
                        }
                    } else {
                        throw new Error(data.message || 'Erreur lors du chargement');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('historyLoading').classList.add('d-none');
                    document.getElementById('historyError').classList.remove('d-none');
                    document.getElementById('historyError').textContent = error.message || 'Une erreur est survenue lors du chargement de l\'historique.';
                });
        });
    }
    
    // Fonction pour formater les dates
    function formatDate(dateString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('fr-FR', options);
    }
    
    // Fonction pour obtenir le libellé du statut
    function getStatusLabel(status) {
        const labels = {
            'Soumise': 'Demande soumise',
            'EnCours': 'Traitement en cours',
            'Approuvee': 'Demande approuvée',
            'Rejetee': 'Demande rejetée',
            'Terminee': 'Traitement terminé',
            'Annulee': 'Demande annulée'
        };
        return labels[status] || status;
    }
    
    // Exportation Excel
    document.getElementById('exportExcel').addEventListener('click', function() {
        // Construire l'URL avec tous les filtres actuels
        const url = new URL(window.location.href);
        url.pathname = url.pathname.replace('demandes_cni.php', 'export_demandes.php');
        window.location.href = url.toString();
    });
});
</script>
