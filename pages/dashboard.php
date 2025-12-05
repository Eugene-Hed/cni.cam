<?php
include('../includes/config.php');

// Vérification si l'utilisateur est connecté et est un citoyen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$userId = $_SESSION['user_id'];

// Récupération de la photo de profil
$query = "SELECT * FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
$profilePhoto = $user['PhotoUtilisateur'] ?? '/assets/images/default-avatar.png';

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

// Calcul des statistiques
$stats = [
    'total' => count($demandes),
    'en_cours' => 0,
    'approuvees' => 0,
    'a_retirer' => 0,
    'rejetees' => 0
];

foreach ($demandes as $demande) {
    if ($demande['Statut'] == 'EnCours') $stats['en_cours']++;
    elseif ($demande['Statut'] == 'Approuvee') $stats['approuvees']++;
    elseif ($demande['Statut'] == 'Terminee') $stats['a_retirer']++;
    elseif ($demande['Statut'] == 'Rejetee') $stats['rejetees']++;
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="dashboard-container py-5">
    <div class="container">
        <!-- Bienvenue et résumé -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card welcome-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <?php if(!empty($user['PhotoUtilisateur'])): ?>
                                <img src="<?php echo htmlspecialchars($user['PhotoUtilisateur']); ?>" alt="Photo de profil" class="welcome-avatar me-3">
                            <?php else: ?>
                                <div class="welcome-avatar-placeholder me-3">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h2 class="welcome-title mb-0">Bienvenue, <?php echo htmlspecialchars($user['Prenom']); ?></h2>
                                <p class="text-muted mb-0">Voici un aperçu de vos activités</p>
                            </div>
                        </div>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="info-card bg-light p-3 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="bi bi-person-vcard"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Code utilisateur</h6>
                                            <p class="mb-0 fw-bold"><?php echo htmlspecialchars($user['CodeUtilisateur'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card bg-light p-3 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="info-icon bg-success bg-opacity-10 text-success me-3">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Compte créé le</h6>
                                            <p class="mb-0"><?php echo isset($user['DateCreation']) ? date('d/m/Y', strtotime($user['DateCreation'])) : 'N/A'; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Actions rapides</h5>
                            </div>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <a href="/pages/demande_cni.php" class="action-card d-flex flex-column align-items-center p-3 rounded-3 text-center">
                                        <div class="action-icon bg-primary bg-opacity-10 text-primary mb-2">
                                            <i class="bi bi-person-vcard"></i>
                                        </div>
                                        <span>Nouvelle CNI</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/pages/demande_certificat.php" class="action-card d-flex flex-column align-items-center p-3 rounded-3 text-center">
                                        <div class="action-icon bg-success bg-opacity-10 text-success mb-2">
                                            <i class="bi bi-flag"></i>
                                        </div>
                                        <span>Certificat</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/pages/mes_demandes.php" class="action-card d-flex flex-column align-items-center p-3 rounded-3 text-center">
                                        <div class="action-icon bg-info bg-opacity-10 text-info mb-2">
                                            <i class="bi bi-list-check"></i>
                                        </div>
                                        <span>Mes demandes</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3">
                                    <a href="/pages/mes_documents.php" class="action-card d-flex flex-column align-items-center p-3 rounded-3 text-center">
                                        <div class="action-icon bg-warning bg-opacity-10 text-warning mb-2">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <span>Documents</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">Statistiques</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="stats-circle mx-auto">
                                <div class="stats-circle-inner">
                                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    <p class="mb-0 small">Total</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-item d-flex align-items-center mb-3">
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">En cours</h6>
                                    <span class="fw-bold"><?php echo $stats['en_cours']; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo $stats['total'] > 0 ? ($stats['en_cours'] / $stats['total'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-item d-flex align-items-center mb-3">
                            <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">Approuvées</h6>
                                    <span class="fw-bold"><?php echo $stats['approuvees']; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $stats['total'] > 0 ? ($stats['approuvees'] / $stats['total'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-item d-flex align-items-center mb-3">
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">À retirer</h6>
                                    <span class="fw-bold"><?php echo $stats['a_retirer']; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $stats['total'] > 0 ? ($stats['a_retirer'] / $stats['total'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-item d-flex align-items-center">
                            <div class="stats-icon bg-danger bg-opacity-10 text-danger me-3">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">Rejetées</h6>
                                    <span class="fw-bold"><?php echo $stats['rejetees']; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-danger" style="width: <?php echo $stats['total'] > 0 ? ($stats['rejetees'] / $stats['total'] * 100) : 0; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Demandes récentes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                        <h5 class="mb-0">Demandes récentes</h5>
                        <a href="/pages/mes_demandes.php" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-eye me-1"></i>Voir tout
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Type</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($demandes) > 0): ?>
                                        <?php foreach(array_slice($demandes, 0, 5) as $demande): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="demande-icon me-2 <?php echo $demande['TypeDemande'] == 'CNI' ? 'bg-primary' : 'bg-success'; ?> bg-opacity-10">
                                                    <i class="bi <?php echo $demande['TypeDemande'] == 'CNI' ? 'bi-person-vcard' : 'bi-flag'; ?>"></i>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($demande['TypeDemande']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo match($demande['Statut']) {
                                                        'Soumise' => 'bg-secondary',
                                                        'EnCours' => 'bg-primary',
                                                        'Approuvee' => 'bg-success',
                                                        'Rejetee' => 'bg-danger',
                                                        'Terminee' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                ?> rounded-pill">
                                                    <?php 
                                                    echo match($demande['Statut']) {
                                                        'Soumise' => 'Soumise',
                                                        'EnCours' => 'En cours',
                                                        'Approuvee' => 'Approuvée',
                                                        'Rejetee' => 'Rejetée',
                                                        'Terminee' => 'À retirer',
                                                        default => $demande['Statut']
                                                    };
                                                    ?>
                                                </span>
                                                <?php if($demande['Statut'] == 'Approuvee' && isset($demande['SignatureRequise']) && $demande['SignatureRequise'] == 1 && (!isset($demande['SignatureEnregistree']) || $demande['SignatureEnregistree'] == 0)): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill ms-1">Signature requise</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="/pages/details_demande.php?id=<?php echo $demande['DemandeID']; ?>" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="bi bi-eye me-1"></i>Détails
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="empty-state">
                                                    <i class="bi bi-file-earmark-text display-6 text-muted mb-3"></i>
                                                    <p class="mb-0">Vous n'avez pas encore de demandes</p>
                                                    <a href="/pages/demande_cni.php" class="btn btn-sm btn-primary mt-3">
                                                        <i class="bi bi-plus-circle me-1"></i>Nouvelle demande
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                        <h5 class="mb-0">Notifications récentes</h5>
                        <a href="/pages/notifications.php" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-bell me-1"></i>Voir tout
                        </a>
                    </div>
                    <?php
// Vérifier s'il y a des demandes approuvées nécessitant une signature
$query = "SELECT COUNT(*) as count FROM demandes 
          WHERE UtilisateurID = :userId 
          AND Statut = 'Approuvee' 
          AND SignatureRequise = 1 
          AND (SignatureEnregistree = 0 OR SignatureEnregistree IS NULL)";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$signatureRequise = $stmt->fetchColumn();
?>

<?php if($signatureRequise > 0): ?>
<div class="alert alert-warning mt-3 mx-3">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
        <div>
            <h5 class="mb-1">Signature requise</h5>
            <p class="mb-0">Vous avez <?php echo $signatureRequise; ?> demande(s) de CNI approuvée(s) qui nécessite(nt) votre signature. Veuillez consulter vos demandes pour finaliser le processus.</p>
            <a href="mes_demandes.php?filter=signature" class="btn btn-sm btn-warning mt-2">
                <i class="bi bi-pen me-1"></i> Enregistrer ma signature
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
                    <div class="card-body p-0">
                        <?php if(count($notifications) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach($notifications as $notif): ?>
                                <a href="/pages/view_notification.php?id=<?php echo $notif['NotificationID']; ?>" class="list-group-item list-group-item-action border-0 py-3">
                                    <div class="d-flex">
                                        <div class="notif-icon me-3 rounded-circle text-center" style="width: 40px; height: 40px; line-height: 40px; background-color: rgba(23, 116, 223, 0.1);">
                                            <i class="bi bi-bell text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1"><?php echo htmlspecialchars($notif['Contenu']); ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($notif['DateCreation'])); ?>
                                            </small>
                                        </div>
                                        <?php if($notif['EstLue'] == 0): ?>
                                            <span class="badge bg-primary rounded-pill align-self-center">Nouveau</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="empty-state">
                                    <i class="bi bi-bell display-6 text-muted mb-3"></i>
                                    <p class="mb-0">Aucune notification récente</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #1774df;
    --primary-dark: #135bb2;
    --success: #28a745;
    --warning: #ffc107;
    --danger: #dc3545;
    --info: #17a2b8;
    --light: #f8f9fa;
    --dark: #343a40;
}

.dashboard-container {
    background-color: #f8f9fa;
    min-height: calc(100vh - 70px);
    padding-top: 20px;
}

/* Carte de bienvenue */
.welcome-card {
    background: linear-gradient(to right, #ffffff, #f8f9fa);
    transition: all 0.3s ease;
}

.welcome-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.welcome-avatar-placeholder {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #6c757d;
    border: 3px solid #fff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.welcome-title {
    font-size: 1.75rem;
    font-weight: 600;
}

/* Cartes d'information */
.info-card {
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* Cartes d'action */
.action-card {
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--dark);
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    color: var(--primary);
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

/* Statistiques */
.stats-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(
        var(--primary) 0deg, 
        var(--success) 90deg, 
        var(--warning) 180deg, 
        var(--danger) 270deg
    );
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-circle-inner {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}

.stats-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.stats-item {
    transition: all 0.3s ease;
}

.stats-item:hover {
    transform: translateX(5px);
}

/* Icônes pour les demandes */
.demande-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Icônes pour les notifications */
.notif-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

/* États vides */
.empty-state {
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease;
}

/* Responsive */
@media (max-width: 992px) {
    .welcome-title {
        font-size: 1.5rem;
    }
    
    .stats-circle {
        width: 100px;
        height: 100px;
    }
    
    .stats-circle-inner {
        width: 75px;
        height: 75px;
    }
}

@media (max-width: 768px) {
    .action-card {
        padding: 0.75rem;
    }
    
    .action-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .welcome-avatar, .welcome-avatar-placeholder {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .welcome-title {
        font-size: 1.25rem;
    }
    
    .info-icon, .stats-icon, .demande-icon, .notif-icon {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des statistiques
    const statsItems = document.querySelectorAll('.stats-item');
    statsItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(20px)';
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, 100 * index);
    });
    
    // Effet de survol pour les cartes
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.05)';
        });
    });
    
    // Effet de survol pour les éléments de liste
    const listItems = document.querySelectorAll('.list-group-item');
    listItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if(!this.classList.contains('list-group-item-action')) return;
            this.style.backgroundColor = 'rgba(23, 116, 223, 0.05)';
        });
        
        item.addEventListener('mouseleave', function() {
            if(!this.classList.contains('list-group-item-action')) return;
            this.style.backgroundColor = '';
        });
    });
});
</script>

<?php include('../includes/footer.php'); ?>
