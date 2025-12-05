<?php
// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF']);

// Récupérer les statistiques pour les badges
$query = "SELECT 
            SUM(CASE WHEN Statut = 'Soumise' THEN 1 ELSE 0 END) as nouvelles
          FROM demandes 
          WHERE TypeDemande = 'CNI'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch();
?>

<!-- Sidebar minimaliste -->
<div class="slim-sidebar">
    <!-- Logo de l'application -->
    <div class="slim-logo">
        <i class="bi bi-person-vcard-fill"></i>
    </div>
    
    <!-- Menu de navigation -->
    <div class="slim-nav">
        <a href="dashboard.php" class="slim-nav-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>" title="Tableau de bord">
            <i class="bi bi-speedometer2"></i>
            <span class="slim-tooltip">Tableau de bord</span>
        </a>
        
        <a href="demandes_cni.php" class="slim-nav-item <?php echo ($current_page === 'demandes_cni.php') ? 'active' : ''; ?>" title="Demandes CNI">
            <i class="bi bi-person-vcard"></i>
            <?php if(isset($stats['nouvelles']) && $stats['nouvelles'] > 0): ?>
                <span class="slim-badge"><?php echo $stats['nouvelles']; ?></span>
            <?php endif; ?>
            <span class="slim-tooltip">Demandes CNI</span>
        </a>
        
        <a href="reclamations.php" class="slim-nav-item <?php echo ($current_page === 'reclamations.php') ? 'active' : ''; ?>" title="Réclamations">
            <i class="bi bi-chat-dots"></i>
            <span class="slim-tooltip">Réclamations</span>
        </a>
    </div>
    
    <!-- Menu secondaire -->
    <div class="slim-nav slim-nav-bottom">
        <a href="../pages/logout.php" class="slim-nav-item" title="Déconnexion">
            <i class="bi bi-box-arrow-right"></i>
            <span class="slim-tooltip">Déconnexion</span>
        </a>
    </div>
</div>

<style>
/* Variables */
:root {
    --slim-sidebar-width: 50px;
    --slim-primary: #1774df;
    --slim-bg: #f8f9fa;
    --slim-hover: #e9ecef;
    --slim-active: #1774df;
    --slim-text: #495057;
    --slim-border: #dee2e6;
    --slim-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
}

/* Reset pour éviter les conflits */
.slim-sidebar, .slim-sidebar * {
    box-sizing: border-box;
}

/* Structure de base */
.slim-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--slim-sidebar-width);
    height: 100vh;
    background-color: var(--slim-bg);
    border-right: 1px solid var(--slim-border);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px 0;
    z-index: 999;
    box-shadow: var(--slim-shadow);
}

/* Logo */
.slim-logo {
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 0;
}

.slim-logo i {
    font-size: 24px;
    color: var(--slim-primary);
}

/* Navigation */
.slim-nav {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.slim-nav-bottom {
    margin-top: auto;
}

.slim-nav-item {
    position: relative;
    width: 100%;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--slim-text);
    text-decoration: none;
    transition: all 0.2s ease;
}

.slim-nav-item i {
    font-size: 1.25rem;
    transition: transform 0.2s ease;
}

.slim-nav-item:hover {
    background-color: var(--slim-hover);
}

.slim-nav-item:hover i {
    transform: scale(1.1);
    color: var(--slim-primary);
}

.slim-nav-item.active {
    color: var(--slim-active);
    background-color: rgba(23, 116, 223, 0.1);
}

.slim-nav-item.active i {
    color: var(--slim-active);
}

/* Badge de notification */
.slim-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    min-width: 16px;
    height: 16px;
    border-radius: 8px;
    background-color: #dc3545;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 4px;
}

/* Tooltip */
.slim-tooltip {
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background-color: #343a40;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    pointer-events: none;
    margin-left: 8px;
    z-index: 1000;
}

.slim-tooltip::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -4px;
    transform: translateY(-50%);
    border-width: 4px 4px 4px 0;
    border-style: solid;
    border-color: transparent #343a40 transparent transparent;
}

.slim-nav-item:hover .slim-tooltip {
    opacity: 1;
    visibility: visible;
}

/* Responsive */
@media (max-width: 767.98px) {
    .slim-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .slim-sidebar.show {
        transform: translateX(0);
    }
    
    .slim-toggle-btn {
        display: block;
    }
}

/* Bouton toggle pour mobile */
.slim-toggle-btn {
    position: fixed;
    top: 10px;
    left: 10px;
    z-index: 1000;
    background-color: var(--slim-bg);
    color: var(--slim-text);
    border: 1px solid var(--slim-border);
    border-radius: 4px;
    width: 36px;
    height: 36px;
    display: none;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: var(--slim-shadow);
}
</style>

<!-- Bouton toggle pour mobile -->
<button class="slim-toggle-btn d-md-none">
    <i class="bi bi-list"></i>
</button>

<!-- Script pour la sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajuster le contenu principal pour laisser de la place à la sidebar
    const sidebarWidth = 50; // Doit correspondre à --slim-sidebar-width
    
    // Fonction pour ajuster le contenu
    function adjustContent() {
        // Ne pas modifier le contenu sur mobile
        if (window.innerWidth >= 768) {
            document.body.style.paddingLeft = sidebarWidth + 'px';
        } else {
            document.body.style.paddingLeft = '0';
        }
    }
    
    // Ajuster au chargement
    adjustContent();
    
    // Ajuster lors du redimensionnement
    window.addEventListener('resize', adjustContent);
    
    // Toggle sidebar sur mobile
    const toggleBtn = document.querySelector('.slim-toggle-btn');
    const sidebar = document.querySelector('.slim-sidebar');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Fermer la sidebar en cliquant sur un lien (mobile)
    const navItems = document.querySelectorAll('.slim-nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('show');
            }
        });
    });
    
    // Fermer la sidebar en cliquant en dehors (mobile)
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 768 && 
            sidebar.classList.contains('show') && 
            !sidebar.contains(event.target) && 
            !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });
});
</script>
