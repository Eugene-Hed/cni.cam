<?php
// Récupération des informations de l'utilisateur
$userId = $_SESSION['user_id'];

// Récupération de la photo de profil
$query = "SELECT PhotoUtilisateur FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
$profilePhoto = $user['PhotoUtilisateur'] ?? '/cni.cam/assets/images/default-avatar.png';

// Récupération des demandes de l'utilisateur
$query = "SELECT * FROM demandes WHERE UtilisateurID = :userId ORDER BY DateSoumission DESC";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$demandes = $stmt->fetchAll();

// Récupération des notifications
$query = "SELECT * FROM notifications WHERE UtilisateurID = :userId AND EstLue = 0 ORDER BY DateCreation DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$notifications = $stmt->fetchAll();
?>

<style>
.dashboard-container {
    background-color: #f8f9fa;
    padding: 30px 0;
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
.notification-item {
    transition: background-color 0.3s ease;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
</style>

<div class="dashboard-container">
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">En cours</h6>
                                <h3 class="card-title mb-0">
                                    <?php
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM demandes WHERE UtilisateurID = :userId AND Statut = 'EnCours'");
                                    $stmt->execute(['userId' => $userId]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Approuvées</h6>
                                <h3 class="card-title mb-0">
                                    <?php
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM demandes WHERE UtilisateurID = :userId AND Statut = 'Approuvee'");
                                    $stmt->execute(['userId' => $userId]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">À retirer</h6>
                                <h3 class="card-title mb-0">
                                    <?php
                                    $stmt = $db->prepare("SELECT COUNT(*) FROM demandes WHERE UtilisateurID = :userId AND Statut = 'Terminee'");
                                    $stmt->execute(['userId' => $userId]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests Table -->
        <div class="card table-card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Dernières demandes</h5>
                <a href="mes_demandes.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye me-1"></i>Voir tout
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($demandes, 0, 5) as $demande): ?>
                            <tr>
                                <td>
                                    <i class="bi <?php echo $demande['TypeDemande'] == 'CNI' ? 'bi-person-vcard' : 'bi-flag'; ?> me-2"></i>
                                    <?php echo $demande['TypeDemande']; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($demande['Statut']) {
                                            'Soumise' => 'secondary',
                                            'EnCours' => 'primary',
                                            'Approuvee' => 'success',
                                            'Rejetee' => 'danger',
                                            'Terminee' => 'info',
                                            default => 'secondary'
                                        };
                                    ?> rounded-pill">
                                        <?php echo $demande['Statut']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="details_demande.php?id=<?php echo $demande['DemandeID']; ?>" 
                                       class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye me-1"></i>Détails
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="card notification-card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Notifications récentes</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if(count($notifications) > 0): ?>
                    <?php foreach($notifications as $notif): ?>
                    <div class="list-group-item notification-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-bell text-primary me-2"></i>
                                <span><?php echo $notif['Contenu']; ?></span>
                            </div>
                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($notif['DateCreation'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center py-4">
                        <i class="bi bi-bell-slash h4 mb-2 d-block"></i>
                        <p class="text-muted mb-0">Aucune notification récente</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
