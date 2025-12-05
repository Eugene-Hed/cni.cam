<?php
// Récupérer le nombre de notifications non lues
$notifCount = 0;
$notifications = [];
global $db;
if (isset($_SESSION['user_id'])) {
    try {
        $notifStmt = $db->prepare("SELECT * FROM notifications WHERE UtilisateurID = ? AND EstLue = 0 ORDER BY DateCreation DESC LIMIT 5");
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

<!-- Barre de navigation pour les citoyens -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <!-- Logo et nom du site -->
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
            <img src="/assets/images/Cameroun.gif" alt="Logo" class="me-2 rounded-circle" width="40" height="40">
            <span class="fw-bold d-none d-sm-inline">CNI.CAM</span>
        </a>

        <!-- Boutons mobile (notifications et menu) -->
        <div class="d-flex align-items-center ms-auto me-2 me-lg-0 order-lg-last">
            <!-- Notifications sur mobile -->
            <div class="position-relative me-3 d-lg-none">
                <a href="/pages/notifications.php" class="btn btn-link text-white p-1 position-relative">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if($notifCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                    </span>
                    <?php endif; ?>
                </a>
            </div>
            
            <!-- Bouton menu mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarCitizen" aria-controls="navbarCitizen" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-4"></i>
            </button>
        </div>

        <!-- Menu principal -->
        <div class="collapse navbar-collapse" id="navbarCitizen">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="../pages/dashboard.php">
                        <i class="bi bi-house-door me-2"></i>Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'demande_cni.php' ? 'active' : ''; ?>" href="/pages/demande_cni.php">
                        <i class="bi bi-person-vcard me-2"></i>Nouvelle CNI
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'demande_certificat.php' ? 'active' : ''; ?>" href="/pages/demande_certificat.php">
                        <i class="bi bi-flag me-2"></i>Nationalité
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'mes_demandes.php' ? 'active' : ''; ?>" href="/pages/mes_demandes.php">
                        <i class="bi bi-list-check me-2"></i>Mes demandes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'mes_documents.php' ? 'active' : ''; ?>" href="/pages/mes_documents.php">
                        <i class="bi bi-file-earmark-text me-2"></i>Mes documents
                    </a>
                </li>
            </ul>

            <!-- Menu utilisateur et notifications -->
            <ul class="navbar-nav ms-auto">
                <!-- Notifications (desktop) -->
                <li class="nav-item dropdown d-none d-lg-block">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if($notifCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0 notif-dropdown" aria-labelledby="notifDropdown">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h6 class="mb-0 fw-bold">Notifications</h6>
                            <?php if($notifCount > 0): ?>
                            <a href="/pages/mark_all_read.php" class="text-decoration-none small">Tout marquer lu</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notif-container">
                            <?php if(!empty($notifications)): ?>
                                <?php foreach($notifications as $notif): ?>
                                <a href="/pages/view_notification.php?id=<?php echo $notif['NotificationID']; ?>" class="dropdown-item p-3 border-bottom">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="notification-icon">
                                                <i class="bi <?php echo getNotificationIcon($notif['TypeNotification']); ?>"></i>
                                            </div>
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
                                    <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                    <p class="mb-0">Aucune notification</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-2 text-center border-top">
                            <a href="/pages/notifications.php" class="btn btn-sm btn-primary rounded-pill px-3">Voir toutes</a>
                        </div>
                    </div>
                </li>
                
                <!-- Menu utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if(!empty($_SESSION['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['photo']); ?>" 
                                 class="rounded-circle me-2 border border-2 border-white" 
                                 width="32" height="32" alt="Photo de profil">
                        <?php else: ?>
                            <div class="user-avatar me-2">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Utilisateur'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-labelledby="userDropdown">
                        <li class="dropdown-item-text p-3 text-center border-bottom">
                            <?php if(!empty($_SESSION['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['photo']); ?>" 
                                     class="rounded-circle mb-2 border border-3 border-light" 
                                     width="64" height="64" alt="Photo de profil">
                            <?php else: ?>
                                <div class="user-avatar-lg mx-auto mb-2">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php endif; ?>
                            <div class="fw-bold"><?php echo htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
                            <div class="mt-2">
                                <span class="badge bg-primary rounded-pill px-3"><?php echo getUserRoleName($_SESSION['role'] ?? 0); ?></span>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="/pages/profil.php"><i class="bi bi-person me-2 text-primary"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="/pages/parametres.php"><i class="bi bi-gear me-2 text-secondary"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// Fonction pour obtenir l'icône en fonction du type de notification
function getNotificationIcon($type) {
    $icons = [
        'demande' => 'bi-file-earmark-text',
        'validation' => 'bi-check-circle',
        'rejet' => 'bi-x-circle',
        'document' => 'bi-file-earmark-pdf',
        'rendez_vous' => 'bi-calendar-event',
        'paiement' => 'bi-credit-card',
        'systeme' => 'bi-gear',
        'demande_approuvee' => 'bi-check-circle',
        'paiement_recu' => 'bi-credit-card',
        'signature_enregistree' => 'bi-pen'
    ];
    
    return $icons[$type] ?? 'bi-bell';
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
/* Variables CSS pour faciliter la personnalisation */
:root {
    --primary-color: #1774df;
    --primary-dark: #135bb2;
    --primary-light: #3a8ae6;
    --text-light: #ffffff;
    --text-muted: rgba(255, 255, 255, 0.7);
    --hover-bg: rgba(255, 255, 255, 0.1);
    --active-bg: rgba(255, 255, 255, 0.2);
    --dropdown-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    --border-radius: 0.5rem;
    --transition-speed: 0.3s;
}

/* Styles de base pour la navbar */
.navbar {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 1rem;
}

.navbar-brand {
    font-weight: 700;
    display: flex;
    align-items: center;
}

.navbar-brand img {
    border: 2px solid rgba(255, 255, 255, 0.6);
    transition: transform var(--transition-speed);
}

.navbar-brand:hover img {
    transform: scale(1.05);
}

/* Styles pour les liens de navigation */
.nav-link {
    padding: 0.6rem 1rem;
    margin: 0 0.15rem;
    border-radius: var(--border-radius);
    transition: all var(--transition-speed);
    font-weight: 500;
    color: var(--text-light) !important;
    white-space: nowrap;
}

.nav-link:hover {
    background-color: var(--hover-bg);
    transform: translateY(-2px);
}

.nav-link.active {
    background-color: var(--active-bg);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.nav-link i {
    transition: transform var(--transition-speed);
}

.nav-link:hover i {
    transform: translateX(-2px);
}

/* Styles pour les dropdowns */
.dropdown-menu {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--dropdown-shadow);
    overflow: hidden;
    animation: fadeIn var(--transition-speed);
    padding: 0.5rem 0;
}

.notif-dropdown {
    width: 320px;
    max-width: 90vw;
}

.notif-container {
    max-height: 350px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.notif-container::-webkit-scrollbar {
    width: 6px;
}

.notif-container::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.dropdown-item {
    padding: 0.7rem 1rem;
    transition: all var(--transition-speed);
    border-radius: 0.3rem;
    margin: 0.1rem 0.5rem;
    font-weight: 500;
}

.dropdown-item:hover {
    background-color: rgba(var(--primary-color-rgb, 23, 116, 223), 0.1);
    transform: translateX(5px);
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.user-dropdown {
    width: 280px;
    max-width: 90vw;
}

/* Styles pour les avatars utilisateur */
.user-avatar {
    width: 32px;
    height: 32px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.user-avatar-lg {
    width: 64px;
    height: 64px;
    background-color: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 2rem;
}

/* Styles pour les notifications */
.notification-icon {
    width: 40px;
    height: 40px;
    background-color: rgba(var(--primary-color-rgb, 23, 116, 223), 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.2rem;
}

.notification-text {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Animation pour le badge de notification */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.badge.bg-danger {
    animation: pulse 2s infinite;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Styles pour le bouton toggler */
.navbar-toggler {
    border: none;
    padding: 0.25rem 0.5rem;
    transition: transform var(--transition-speed);
    color: var(--text-light);
    outline: none !important;
    box-shadow: none !important;
}

.navbar-toggler:hover {
    transform: rotate(90deg);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}

/* Styles pour les badges */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.badge.bg-danger {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(25%, -25%);
}

/* Styles responsifs */
@media (max-width: 991.98px) {
    /* Styles pour le menu mobile */
    .navbar-collapse {
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        padding: 1rem;
        max-height: calc(100vh - 56px);
        overflow-y: auto;
        z-index: 1030;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        border-radius: 0 0 1rem 1rem;
        transition: all var(--transition-speed) ease;
    }
    
    .navbar-collapse.collapsing {
        height: auto;
        transition: all var(--transition-speed) ease;
        overflow: hidden;
    }
    
    .navbar-collapse.show {
        animation: slideDown var(--transition-speed) ease forwards;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Styles pour les liens dans le menu mobile */
    .nav-link {
        padding: 0.8rem 1rem;
        margin: 0.3rem 0;
        border-radius: 0.5rem;
    }
    
    .nav-link i {
        width: 24px;
        margin-right: 0.5rem;
        text-align: center;
    }
    
    /* Styles pour les dropdowns dans le menu mobile */
    .navbar-nav .dropdown-menu {
        position: static;
        float: none;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.05);
        border: none;
        box-shadow: none;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .dropdown-item {
        color: var(--text-light);
    }
    
    .dropdown-item:hover {
        background-color: var(--hover-bg);
        color: var(--text-light);
    }
    
    .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Styles pour le profil utilisateur dans le menu mobile */
    .dropdown-item-text {
        color: var(--text-light);
    }
    
    .dropdown-item-text .text-muted {
        color: var(--text-muted) !important;
    }
    
    /* Ajustements pour les notifications sur mobile */
    .notif-dropdown {
        width: 100%;
        max-width: 100%;
    }
}

/* Optimisations pour les petits écrans */
@media (max-width: 575.98px) {
    .navbar-brand span {
        font-size: 0.9rem;
    }
    
    .navbar-brand img {
        width: 36px;
        height: 36px;
    }
    
    .nav-link {
        font-size: 0.95rem;
    }
    
    .dropdown-item {
        font-size: 0.9rem;
    }
    
    /* Augmenter la taille des icônes pour faciliter le toucher */
    .nav-link i {
        font-size: 1.1rem;
    }
    
    /* Ajustements pour les très petits écrans */
    @media (max-width: 359.98px) {
        .navbar-brand img {
            width: 32px;
            height: 32px;
        }
        
        .navbar-toggler {
            padding: 0.2rem 0.4rem;
        }
        
        .nav-link {
            padding: 0.7rem 0.8rem;
        }
    }
}

/* Optimisations pour les écrans moyens */
@media (min-width: 768px) and (max-width: 991.98px) {
    .container {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }
}

/* Optimisations pour les grands écrans */
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
    
    .notif-container {
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
    .navbar-toggler {
        animation: none !important;
        transition: none !important;
        transform: none !important;
    }
}

/* Accessibilité améliorée */
.nav-link:focus, .dropdown-item:focus {
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
    
    .user-avatar-lg {
        background-color: #343a40;
        color: #e9ecef;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants Bootstrap
    initBootstrapComponents();
    
    // Gestion de la hauteur du menu mobile
    adjustMobileMenuHeight();
    
    // Écouteurs d'événements pour le redimensionnement et l'orientation
    window.addEventListener('resize', adjustMobileMenuHeight);
    window.addEventListener('orientationchange', function() {
        setTimeout(adjustMobileMenuHeight, 300);
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
    // Initialiser les dropdowns
    try {
        if (typeof bootstrap !== 'undefined') {
            // Utiliser l'API Bootstrap 5
            var dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdownElementList.forEach(function(el) {
                new bootstrap.Dropdown(el);
            });
        } else if (typeof jQuery !== 'undefined' && typeof jQuery().dropdown === 'function') {
            // Fallback pour Bootstrap 4 avec jQuery
            jQuery('[data-bs-toggle="dropdown"]').dropdown();
        } else {
            // Fallback manuel si Bootstrap n'est pas disponible
            setupManualDropdowns();
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

// Fonction pour ajuster la hauteur du menu mobile
function adjustMobileMenuHeight() {
    if (window.innerWidth < 992) {
        const navbarHeight = document.querySelector('.navbar').offsetHeight;
        const navbarCollapse = document.querySelector('.navbar-collapse');
        if (navbarCollapse) {
            navbarCollapse.style.top = `${navbarHeight}px`;
            navbarCollapse.style.maxHeight = `calc(100vh - ${navbarHeight}px)`;
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
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.addEventListener('touchstart', function(e) {
                if (this.scrollHeight > this.clientHeight) {
                    e.stopPropagation();
                }
            });
        });
    }
}
</script>
