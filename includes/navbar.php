<?php
// Récupérer le nombre de notifications non lues si l'utilisateur est connecté
$notifCount = 0;
$notifications = [];

if (isset($_SESSION['user_id'])) {
    try {
        $notifStmt = $db->prepare("SELECT * FROM notifications WHERE UtilisateurID = ? AND Lu = 0 ORDER BY DateCreation DESC LIMIT 5");
        $notifStmt->execute([$_SESSION['user_id']]);
        $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
        $notifCount = count($notifications);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
    }
}

// Déterminer la page active
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Spacer pour éviter le chevauchement avec la navbar fixe -->
<div class="navbar-spacer"></div>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
    <div class="container">
        <!-- Logo et Marque -->
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
            <div class="logo-container me-2">
                <img src="/assets/images/Cameroun.gif" alt="CNI.CAM" class="logo-img">
            </div>
            <span class="brand-text fw-bold">CNI<span class="text-warning">.CAM</span></span>
        </a>
        
        <!-- Boutons Mobile (Notifications + Menu) -->
        <div class="d-flex align-items-center d-lg-none">
            <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Notifications sur mobile -->
            <a href="/pages/notifications.php" class="btn-icon position-relative me-2">
                <i class="bi bi-bell-fill"></i>
                <?php if($notifCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                </span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            
            <!-- Bouton Menu Mobile -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        
        <!-- Menu Principal -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Menu de Navigation -->
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="/index.php">
                        <i class="bi bi-house-door me-1"></i><span>Accueil</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'services.php' ? 'active' : ''; ?>" href="/pages/services.php">
                        <i class="bi bi-grid me-1"></i><span>Services</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'about.php' ? 'active' : ''; ?>" href="/pages/about.php">
                        <i class="bi bi-info-circle me-1"></i><span>À propos</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'contact.php' ? 'active' : ''; ?>" href="/pages/contact.php">
                        <i class="bi bi-envelope me-1"></i><span>Contact</span>
                    </a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 2): // Citoyen ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage == 'mes_demandes.php' ? 'active' : ''; ?>" href="/pages/mes_demandes.php">
                                <i class="bi bi-list-check me-1"></i><span>Mes demandes</span>
                            </a>
                        </li>
                    <?php elseif($_SESSION['role'] == 3): // Officier ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="/pages/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i><span>Tableau de bord</span>
                            </a>
                        </li>
                    <?php elseif($_SESSION['role'] == 1): // Admin ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPage == 'admin.php' ? 'active' : ''; ?>" href="/pages/admin.php">
                                <i class="bi bi-shield-lock me-1"></i><span>Administration</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Menu Utilisateur -->
            <ul class="navbar-nav align-items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Notifications (Desktop) -->
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <?php if($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Notifications</h6>
                                <?php if($notifCount > 0): ?>
                                <a href="/pages/mark_all_read.php" class="text-decoration-none small">Tout marquer comme lu</a>
                                <?php endif; ?>
                            </div>
                            <div class="notifications-list">
                                <?php if(!empty($notifications)): ?>
                                    <?php foreach($notifications as $notif): ?>
                                    <a href="/pages/view_notification.php?id=<?php echo $notif['NotificationID']; ?>" class="dropdown-item notification-item">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon me-3">
                                                <i class="bi <?php echo getNotificationIcon($notif['Type']); ?>"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-1 notification-text"><?php echo htmlspecialchars($notif['Contenu']); ?></p>
                                                <small class="text-muted"><?php echo formatTimeAgo(strtotime($notif['DateCreation'])); ?></small>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="bi bi-bell-slash fs-4 mb-3 d-block"></i>
                                        <p class="mb-0">Aucune notification</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-2 border-top text-center">
                                <a href="/pages/notifications.php" class="btn btn-sm btn-primary rounded-pill px-3">Voir toutes</a>
                            </div>
                        </div>
                    </li>
                    
                    <!-- Menu Utilisateur -->
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <?php if(!empty($_SESSION['PhotoUtilisateur'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['PhotoUtilisateur']); ?>" alt="Photo de profil" class="avatar-img">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['prenom']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
                            <li class="dropdown-header text-center border-bottom pb-3">
                                <?php if(!empty($_SESSION['PhotoUtilisateur'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['PhotoUtilisateur']); ?>" alt="Photo de profil" class="avatar-img-lg mb-2">
                                <?php else: ?>
                                    <div class="avatar-placeholder-lg mb-2 mx-auto">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                                <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></h6>
                                <small class="text-muted"><?php echo getUserRoleName($_SESSION['role']); ?></small>
                            </li>
                            <li><a class="dropdown-item" href="/pages/profil.php"><i class="bi bi-person-circle me-2 text-primary"></i>Mon profil</a></li>
                            <li><a class="dropdown-item" href="/pages/parametres.php"><i class="bi bi-gear me-2 text-secondary"></i>Paramètres</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Boutons Connexion/Inscription -->
                    <li class="nav-item d-flex">
                        <a href="/pages/login.php" class="btn btn-light-primary me-2">
                            <i class="bi bi-box-arrow-in-right me-1 d-none d-sm-inline-block"></i>Connexion
                        </a>
                        <a href="/pages/register.php" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1 d-none d-sm-inline-block"></i>Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php
// Fonction pour obtenir l'icône en fonction du type de notification
function getNotificationIcon($type) {
    $icons = [
        'demande' => 'bi-file-earmark-text text-primary',
        'validation' => 'bi-check-circle-fill text-success',
        'rejet' => 'bi-x-circle-fill text-danger',
        'document' => 'bi-file-earmark-pdf text-info',
        'rendez_vous' => 'bi-calendar-event text-warning',
        'paiement' => 'bi-credit-card text-secondary',
        'systeme' => 'bi-gear text-dark',
    ];
    
    return $icons[$type] ?? 'bi-bell text-primary';
}

// Fonction pour formater le temps écoulé
function formatTimeAgo($timestamp) {
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "À l'instant";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "Il y a " . $minutes . " min";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "Il y a " . $hours . " h";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "Il y a " . $days . " j";
    } else {
        return date('d/m/Y', $timestamp);
    }
}

// Fonction pour obtenir le nom du rôle
function getUserRoleName($roleId) {
    $roles = [
        1 => 'Administrateur',
        2 => 'Citoyen',
        3 => 'Officier',
        4 => 'Président'
    ];
    
    return $roles[$roleId] ?? 'Utilisateur';
}
?>

<style>
:root {
    /* Variables de couleurs */
    --primary: #1774df;
    --primary-dark: #135bb2;
    --primary-light: rgba(23, 116, 223, 0.1);
    --warning: #ffc107;
    --danger: #dc3545;
    --success: #28a745;
    --info: #17a2b8;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #343a40;
    --white: #ffffff;
    
        /* Variables de mise en page */
        --navbar-height: 70px;
    --border-radius: 0.5rem;
    --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    --transition-speed: 0.3s;
}

/* Spacer pour éviter le chevauchement avec la navbar fixe */
.navbar-spacer {
    height: var(--navbar-height);
    width: 100%;
}

/* Styles de base pour la navbar */
.navbar {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 0.75rem 0;
    transition: all var(--transition-speed) ease;
    height: var(--navbar-height);
    z-index: 1030;
}

/* Logo et marque */
.logo-container {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all var(--transition-speed) ease;
}

.logo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all var(--transition-speed) ease;
}

.brand-text {
    font-size: 1.4rem;
    letter-spacing: 0.5px;
    transition: all var(--transition-speed) ease;
}

.navbar-brand:hover .logo-container {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.5);
}

/* Liens de navigation */
.nav-link {
    padding: 0.6rem 1rem;
    margin: 0 0.15rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: all var(--transition-speed) ease;
    position: relative;
    white-space: nowrap;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 3px;
    background-color: var(--warning);
    border-radius: 3px;
}

.nav-link i {
    transition: transform var(--transition-speed) ease;
}

.nav-link:hover i {
    transform: translateY(-1px);
}

/* Boutons */
.btn-light-primary {
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--white);
    border: none;
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    transition: all var(--transition-speed) ease;
}

.btn-light-primary:hover {
    background-color: rgba(255, 255, 255, 0.3);
    color: var(--white);
    transform: translateY(-2px);
}

.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
    box-shadow: 0 4px 10px rgba(23, 116, 223, 0.3);
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    transition: all var(--transition-speed) ease;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    color: var(--white);
    transition: all var(--transition-speed) ease;
}

.btn-icon:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Avatar utilisateur */
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background-color: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-speed) ease;
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
    color: var(--white);
    font-size: 1.2rem;
}

.avatar-img-lg {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--light);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.avatar-placeholder-lg {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--light);
    color: var(--secondary);
    font-size: 1.8rem;
}

/* Notifications */
.notification-dropdown {
    width: 320px;
    max-width: 90vw;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 0;
    overflow: hidden;
    margin-top: 0.5rem !important;
}

.notifications-list {
    max-height: 350px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.notifications-list::-webkit-scrollbar {
    width: 6px;
}

.notifications-list::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-light);
    font-size: 1.2rem;
}

.notification-item {
    transition: all var(--transition-speed) ease;
    border-left: 3px solid transparent;
    padding: 0.75rem 1rem;
}

.notification-item:hover {
    background-color: rgba(23, 116, 223, 0.05);
    border-left-color: var(--primary);
}

.notification-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Animation du badge de notification */
@keyframes pulse {
    0% { transform: scale(1) translate(-50%, -50%); }
    50% { transform: scale(1.2) translate(-50%, -50%); }
    100% { transform: scale(1) translate(-50%, -50%); }
}

.badge.bg-danger {
    animation: pulse 2s infinite;
    transform-origin: center;
}

/* Dropdown menu */
.dropdown-menu {
    border-radius: var(--border-radius);
    overflow: hidden;
    animation: dropdownFadeIn var(--transition-speed) ease;
    box-shadow: var(--box-shadow);
    border: none;
}

/* Correction spécifique pour le menu utilisateur */
.user-dropdown {
    width: 280px;
    max-width: 90vw;
}

.dropdown-header {
    background-color: var(--light);
    padding: 1rem;
}

.dropdown-item {
    padding: 0.7rem 1.2rem;
    transition: all var(--transition-speed) ease;
    border-radius: 0.3rem;
    margin: 0.1rem 0.5rem;
}

.dropdown-item:hover {
    background-color: var(--primary-light);
    transform: translateX(5px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-divider {
    margin: 0.5rem 0;
    opacity: 0.1;
}

/* Animations */
@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Navbar scrolled effect */
.navbar.scrolled {
    padding: 0.5rem 0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    background: var(--primary-dark);
}

.navbar.scrolled .logo-container {
    width: 34px;
    height: 34px;
}

.navbar.scrolled .brand-text {
    font-size: 1.2rem;
}

/* Navbar toggler */
.navbar-toggler {
    border: none;
    padding: 0.25rem 0.5rem;
    transition: transform var(--transition-speed) ease;
    color: var(--white);
    outline: none !important;
    box-shadow: none !important;
}

.navbar-toggler:hover {
    transform: rotate(90deg);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .navbar-collapse {
        position: fixed;
        top: var(--navbar-height);
        left: 0;
        width: 100%;
        height: calc(100vh - var(--navbar-height));
        background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 1rem;
        overflow-y: auto;
        z-index: 1030;
        transform: translateX(-100%);
        transition: transform var(--transition-speed) ease;
    }
    
    .navbar-collapse.show {
        transform: translateX(0);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .nav-link {
        padding: 0.8rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 10px;
    }
    
    .nav-link i {
        width: 24px;
        margin-right: 0.5rem;
        text-align: center;
    }
    
    .navbar-nav .dropdown-menu {
        background-color: rgba(255, 255, 255, 0.05);
        border: none;
        box-shadow: none;
        position: static !important;
        transform: none !important;
        width: 100%;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .dropdown-item {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: var(--white);
    }
    
    .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Amélioration pour le menu utilisateur sur mobile */
    .dropdown-item-text {
        color: var(--white);
    }
    
    .dropdown-item-text .text-muted {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    /* Animation pour le menu mobile */
    .navbar-collapse.collapsing {
        height: auto;
        overflow: hidden;
        transition: all var(--transition-speed) ease;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
}

/* Small screens optimization */
@media (max-width: 576px) {
    .navbar {
        padding: 0.5rem 0;
    }
    
    .logo-container {
        width: 32px;
        height: 32px;
    }
    
    .brand-text {
        font-size: 1.1rem;
    }
    
    .nav-link {
        font-size: 0.95rem;
    }
    
    .dropdown-item {
        font-size: 0.9rem;
    }
    
    /* Icônes plus grandes pour faciliter le toucher sur mobile */
    .nav-link i {
        font-size: 1.1rem;
    }
    
    /* Optimisation de l'espace sur très petits écrans */
    @media (max-width: 360px) {
        .navbar-brand img {
            width: 30px;
            height: 30px;
        }
        
        .navbar-toggler {
            padding: 0.2rem 0.4rem;
        }
        
        .nav-link {
            padding: 0.7rem 0.8rem;
        }
        
        .btn-light-primary, .btn-primary {
            padding: 0.4rem 0.7rem;
            font-size: 0.85rem;
        }
    }
}

/* Optimisations pour les écrans de taille moyenne */
@media (min-width: 768px) and (max-width: 991.98px) {
    .container {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }
}

/* Optimisations pour les écrans larges */
@media (min-width: 1200px) {
    .navbar .container {
        max-width: 1140px;
    }
    
    .nav-link {
        padding: 0.7rem 1.2rem;
    }
}

/* Optimisations pour les écrans extra-larges */
@media (min-width: 1400px) {
    .navbar .container {
        max-width: 1320px;
    }
}

/* Optimisations pour l'orientation paysage sur mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .navbar-collapse {
        max-height: 80vh;
    }
    
    .nav-link {
        padding: 0.5rem 0.8rem;
        margin-bottom: 0.2rem;
    }
    
    .notifications-list {
        max-height: 200px;
    }
}

/* Support pour les appareils à écran tactile */
@media (hover: none) {
    .nav-link:hover, .dropdown-item:hover {
        transform: none;
    }
}

/* Optimisations pour les utilisateurs qui préfèrent réduire les animations */
@media (prefers-reduced-motion: reduce) {
    .dropdown-menu,
    .navbar-collapse.show,
    .nav-link,
    .dropdown-item,
    .badge.bg-danger,
    .navbar-toggler,
    .btn-light-primary:hover,
    .btn-primary:hover {
        animation: none !important;
        transition: none !important;
        transform: none !important;
    }
}

/* Accessibilité améliorée */
.nav-link:focus, .dropdown-item:focus, .navbar-toggler:focus, .btn:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Styles pour le mode sombre */
@media (prefers-color-scheme: dark) {
    .dropdown-menu {
        background-color: #2b3035;
        border-color: #343a40;
    }
    
    .dropdown-item {
        color: #e9ecef;
    }
    
    .dropdown-item:hover {
        background-color: #343a40;
        color: #fff;
    }
    
    .dropdown-divider {
        border-color: #495057;
    }
    
    .text-muted {
        color: #adb5bd !important;
    }
    
    .avatar-placeholder-lg {
        background-color: #343a40;
        color: #e9ecef;
    }
    
    .dropdown-header {
        background-color: #212529;
    }
}
</style>

<!-- Assurez-vous d'inclure les bibliothèques Bootstrap complètes -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants Bootstrap
    initBootstrapComponents();
    
    // Effet de scroll pour la navbar
    setupScrollEffect();
    
    // Ajuster dynamiquement la hauteur du spacer
    updateNavbarSpacer();
    
    // Écouteurs d'événements pour le redimensionnement et l'orientation
    window.addEventListener('resize', updateNavbarSpacer);
    window.addEventListener('orientationchange', function() {
        setTimeout(updateNavbarSpacer, 300);
    });
    
    // Fermer le menu mobile lors du clic sur un lien
    setupMobileMenuClosing();
    
    // Optimisations pour iOS
    applyIOSFixes();
    
    // Optimisations pour les appareils tactiles
    setupTouchDeviceOptimizations();
});

// Fonction pour initialiser les composants Bootstrap
function initBootstrapComponents() {
    try {
        // Initialiser les dropdowns
        if (typeof bootstrap !== 'undefined') {
            var dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdownElementList.forEach(function(el) {
                new bootstrap.Dropdown(el, {
                    offset: [0, 10],
                    boundary: 'viewport',
                    reference: 'toggle',
                    display: 'dynamic'
                });
            });
        } else if (typeof jQuery !== 'undefined' && typeof jQuery().dropdown === 'function') {
            jQuery('[data-bs-toggle="dropdown"]').dropdown();
        } else {
            setupManualDropdowns();
        }
        
        // Initialiser les tooltips
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    } catch (e) {
        console.error("Erreur lors de l'initialisation des composants Bootstrap:", e);
        setupManualDropdowns();
    }
}

// Fonction pour configurer manuellement les dropdowns si Bootstrap n'est pas disponible
function setupManualDropdowns() {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.toggle('show');
                this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
            }
        });
    });
    
    // Fermer les dropdowns quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(openDropdown) {
                openDropdown.classList.remove('show');
                var toggle = openDropdown.previousElementSibling;
                if (toggle && toggle.hasAttribute('aria-expanded')) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
}

// Fonction pour configurer l'effet de scroll
function setupScrollEffect() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Fonction pour ajuster la hauteur du spacer
function updateNavbarSpacer() {
    const navbar = document.querySelector('.navbar');
    const navbarSpacer = document.querySelector('.navbar-spacer');
    
    if (navbar && navbarSpacer) {
        const navbarHeight = navbar.offsetHeight;
        navbarSpacer.style.height = navbarHeight + 'px';
    }
    
    // Ajuster la hauteur du menu mobile
    if (window.innerWidth < 992) {
        const navbarHeight = navbar ? navbar.offsetHeight : 70;
        const navbarCollapse = document.querySelector('.navbar-collapse');
        if (navbarCollapse) {
            navbarCollapse.style.top = `${navbarHeight}px`;
            navbarCollapse.style.height = `calc(100vh - ${navbarHeight}px)`;
        }
    }
}

// Fonction pour configurer la fermeture du menu mobile lors du clic sur un lien
function setupMobileMenuClosing() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const menuToggle = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992 && navbarCollapse && navbarCollapse.classList.contains('show')) {
                // Utiliser l'API Bootstrap si disponible
                if (typeof bootstrap !== 'undefined') {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        navbarCollapse.classList.remove('show');
                    }
                } else {
                    // Fallback si Bootstrap n'est pas disponible
                    navbarCollapse.classList.remove('show');
                    if (menuToggle) {
                        menuToggle.classList.add('collapsed');
                        menuToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });
    });
}

// Fonction pour appliquer des correctifs pour iOS
function applyIOSFixes() {
    if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
        document.documentElement.classList.add('ios-device');
        
        // Style spécifique pour iOS
        const style = document.createElement('style');
        style.textContent = `
            .ios-device .navbar-collapse {
                position: fixed;
                -webkit-overflow-scrolling: touch;
            }
            
            .ios-device .dropdown-menu {
                -webkit-overflow-scrolling: touch;
            }
            
            .ios-device .notifications-list {
                -webkit-overflow-scrolling: touch;
            }
        `;
        document.head.appendChild(style);
    }
}

// Fonction pour optimiser l'expérience sur les appareils tactiles
function setupTouchDeviceOptimizations() {
    if ('ontouchstart' in window) {
        document.documentElement.classList.add('touch-device');
        
        // Ajouter un délai pour éviter les clics accidentels sur mobile
        document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
            toggle.addEventListener('touchstart', function(e) {
                const self = this;
                
                // Vérifier si c'est un appareil mobile avec un petit écran
                if (window.innerWidth < 992) {
                    // Laisser le comportement par défaut pour les mobiles
                    return;
                }
                
                e.preventDefault();
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined') {
                        const dropdown = bootstrap.Dropdown.getInstance(self);
                        if (dropdown) {
                            dropdown.toggle();
                        }
                    } else {
                        self.click();
                    }
                }, 100);
            });
        });
        
        // Améliorer le défilement des menus déroulants sur les appareils tactiles
        document.querySelectorAll('.dropdown-menu, .notifications-list').forEach(function(menu) {
            menu.addEventListener('touchstart', function(e) {
                if (this.scrollHeight > this.clientHeight) {
                    e.stopPropagation();
                }
            });
        });
    }
}
</script>
