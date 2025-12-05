<?php
session_start();
include('../includes/config.php');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: /pages/login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$query = "SELECT * FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();
$photo_path = $user['PhotoUtilisateur'] ?? '../assets/images/default-avatar.png';

// Gestion des pages dynamiques
$url = "admin_dashboard.php";
$pageTitle = 'Tableau de bord';

if (isset($_REQUEST['page'])) {
    $requestedPage = base64_decode($_REQUEST['page']);
    switch ($requestedPage) {
        case 'gestion_utilisateurs':
            $pageTitle = 'Gestion des Utilisateurs';
            break;
        case 'gestion_demandes':
            $pageTitle = 'Gestion des Demandes';
            break;
        case 'statistiques':
            $pageTitle = 'Statistiques';
            break;
        case 'parametres':
            $pageTitle = 'Paramètres';
            break;
        default:
            $pageTitle = 'Tableau de bord';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - <?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background:rgb(0, 59, 114);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            background: #f8f9fa;
        }
        .profile-section {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.2);
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .nav-link i {
            width: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-section">
                <img src="<?php echo htmlspecialchars($photo_path); ?>" 
                     alt="Photo de profil" 
                     class="profile-image mb-3">
                <h5 class="mb-1"><?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?></h5>
                <span class="badge bg-light text-dark">Administrateur</span>
            </div>

            <nav class="nav flex-column">
                <a href="<?php echo $url; ?>" 
                   class="nav-link <?php echo !isset($_REQUEST['page']) ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>Tableau de bord
                </a>
                <a href="<?php echo $url; ?>?page=<?php echo base64_encode('gestion_utilisateurs'); ?>" 
                   class="nav-link <?php echo (isset($_REQUEST['page']) && base64_decode($_REQUEST['page']) == 'gestion_utilisateurs') ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>Gestion des utilisateurs
                </a>
                <a href="<?php echo $url; ?>?page=<?php echo base64_encode('gestion_demandes'); ?>" 
                   class="nav-link <?php echo (isset($_REQUEST['page']) && base64_decode($_REQUEST['page']) == 'gestion_demandes') ? 'active' : ''; ?>">
                    <i class="bi bi-file-text"></i>Gestion des demandes
                </a>
                <a href="<?php echo $url; ?>?page=<?php echo base64_encode('statistiques'); ?>" 
                   class="nav-link <?php echo (isset($_REQUEST['page']) && base64_decode($_REQUEST['page']) == 'statistiques') ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up"></i>Statistiques
                </a>
                <!--<a href="<?php echo $url; ?>?page=<?php echo base64_encode('parametres'); ?>" 
                   class="nav-link <?php echo (isset($_REQUEST['page']) && base64_decode($_REQUEST['page']) == 'parametres') ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>Paramètres
                </a>-->
                <a href="../pages/logout.php" class="nav-link text-danger mt-auto">
                    <i class="bi bi-box-arrow-right"></i>Déconnexion
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
            <h2 class="mb-4">
            <a href="<?php echo $url; ?>" class="text-decoration-none text-dark">
                <?php echo $pageTitle; ?>
            </a>
        </h2>
                <?php
                if (isset($_REQUEST["page"])) {
                    $page = "pagesadmin/" . base64_decode($_REQUEST["page"]) . ".php";
                    if (file_exists($page)) {
                        include($page);
                    } else {
                        include('home.php');
                    }
                } else {
                    include('home.php');
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
