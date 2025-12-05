<?php
global $db;

// Pagination settings
$itemsPerPage = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = '';
$params = [];

if ($search) {
    $searchCondition = " WHERE (u.Nom LIKE :search 
                        OR u.Prenom LIKE :search 
                        OR u.Email LIKE :search 
                        OR u.Codeutilisateur LIKE :search
                        OR u.NumeroTelephone LIKE :search
                        OR r.role LIKE :search)";
    $params[':search'] = "%$search%";
}

// Count total users for pagination
$countQuery = "SELECT COUNT(*) FROM utilisateurs u 
               LEFT JOIN role r ON u.RoleId = r.id 
               $searchCondition";
$stmt = $db->prepare($countQuery);
if ($search) {
    $stmt->bindParam(':search', $params[':search']);
}
$stmt->execute();
$totalUsers = $stmt->fetchColumn();
$totalPages = ceil($totalUsers / $itemsPerPage);

// Main query with sorting
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'DateCreation';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$allowedColumns = ['Nom', 'Prenom', 'Email', 'DateCreation', 'IsActive', 'Codeutilisateur'];
$sortColumn = in_array($sortColumn, $allowedColumns) ? $sortColumn : 'DateCreation';
$sortOrder = $sortOrder === 'ASC' ? 'ASC' : 'DESC';

$query = "SELECT u.*, r.role as role_name 
          FROM utilisateurs u 
          LEFT JOIN role r ON u.RoleId = r.id 
          $searchCondition
          ORDER BY u.$sortColumn $sortOrder
          LIMIT :offset, :limit";

$stmt = $db->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
if ($search) {
    $stmt->bindParam(':search', $params[':search']);
}
$stmt->execute();
$utilisateurs = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Gestion des Utilisateurs</h5>
        <div class="d-flex gap-2">
            <a href="?page=<?php echo base64_encode('addUser'); ?>" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Ajouter un utilisateur
            </a>
            <form class="d-flex" method="GET">
                <input type="hidden" name="page" value="<?php echo base64_encode('gestion_utilisateurs'); ?>">
                <div class="input-group">
                    <input type="search" name="search" class="form-control" placeholder="Rechercher..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['Codeutilisateur']) ?></td>
                            <td><?= htmlspecialchars($user['Nom']) ?></td>
                            <td><?= htmlspecialchars($user['Prenom']) ?></td>
                            <td><?= htmlspecialchars($user['Email']) ?></td>
                            <td><?= htmlspecialchars($user['NumeroTelephone']) ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($user['role_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['IsActive'] ? 'success' : 'danger' ?>">
                                    <?= $user['IsActive'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="?page=<?= base64_encode('edit_user') ?>&id=<?= $user['UtilisateurID'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= base64_encode('gestion_utilisateurs') ?>&p=<?= $i ?><?= $search ? '&search='.$search : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
