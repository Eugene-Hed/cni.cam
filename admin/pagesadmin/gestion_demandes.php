<?php
// Pagination settings
$itemsPerPage = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$whereConditions = [];
$params = [];

if ($search) {
    $whereConditions[] = "(d.DemandeID LIKE :search OR u.Nom LIKE :search OR u.Prenom LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($typeFilter) {
    $whereConditions[] = "d.TypeDemande = :type";
    $params[':type'] = $typeFilter;
}
if ($statusFilter) {
    $whereConditions[] = "d.Statut = :status";
    $params[':status'] = $statusFilter;
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Count total demandes
$countQuery = "SELECT COUNT(*) FROM demandes d 
               LEFT JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID 
               $whereClause";
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$totalDemandes = $stmt->fetchColumn();
$totalPages = ceil($totalDemandes / $itemsPerPage);

// Main query
$query = "SELECT d.*, u.Nom, u.Prenom, u.Email 
          FROM demandes d 
          LEFT JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID 
          $whereClause 
          ORDER BY d.DateSoumission DESC 
          LIMIT :offset, :limit";

$stmt = $db->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$demandes = $stmt->fetchAll();
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Gestion des demandes</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="<?php echo base64_encode('gestion_demandes'); ?>">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="type" class="form-select" style="width: auto;">
                        <option value="">Type</option>
                        <option value="CNI" <?php echo $typeFilter === 'CNI' ? 'selected' : ''; ?>>CNI</option>
                        <option value="CertificatNationalite" <?php echo $typeFilter === 'CertificatNationalite' ? 'selected' : ''; ?>>Certificat</option>
                    </select>
                    <select name="status" class="form-select" style="width: auto;">
                        <option value="">Statut</option>
                        <option value="Soumise" <?php echo $statusFilter === 'Soumise' ? 'selected' : ''; ?>>Soumise</option>
                        <option value="EnCours" <?php echo $statusFilter === 'EnCours' ? 'selected' : ''; ?>>En cours</option>
                        <option value="Terminee" <?php echo $statusFilter === 'Terminee' ? 'selected' : ''; ?>>Terminée</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Demandeur</th>
                        <th>Type</th>
                        <th>Date soumission</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td>#<?php echo str_pad($demande['DemandeID'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div>
                                    <?php echo htmlspecialchars($demande['Nom'] . ' ' . $demande['Prenom']); ?>
                                    <div class="small text-muted"><?php echo htmlspecialchars($demande['Email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $demande['TypeDemande'] === 'CNI' ? 'info' : 'warning'; ?>">
                                    <?php echo $demande['TypeDemande']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($demande['DateSoumission'])); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($demande['Statut']) {
                                        'Soumise' => 'secondary',
                                        'EnCours' => 'primary',
                                        'Terminee' => 'success',
                                        default => 'info'
                                    };
                                ?>">
                                    <?php echo $demande['Statut']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="?page=<?php echo base64_encode('view_demande'); ?>&id=<?php echo $demande['DemandeID']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($demande['Statut'] === 'Soumise'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="approuverDemande(<?php echo $demande['DemandeID']; ?>)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="rejeterDemande(<?php echo $demande['DemandeID']; ?>)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo base64_encode('gestion_demandes'); ?>&p=<?php echo $i; ?><?php echo $search ? '&search='.$search : ''; ?><?php echo $typeFilter ? '&type='.$typeFilter : ''; ?><?php echo $statusFilter ? '&status='.$statusFilter : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function approuverDemande(id) {
    if (confirm('Voulez-vous approuver cette demande ?')) {
        window.location.href = `?page=<?php echo base64_encode('gestion_demandes'); ?>&action=approve&id=${id}`;
    }
}

function rejeterDemande(id) {
    if (confirm('Voulez-vous rejeter cette demande ?')) {
        window.location.href = `?page=<?php echo base64_encode('gestion_demandes'); ?>&action=reject&id=${id}`;
    }
}
</script>
