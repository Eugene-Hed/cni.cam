<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Session is initialized centrally in includes/config.php
include('../includes/config.php');

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    $redirectMap = [
        1 => '../admin/admin_dashboard.php',
        2 => 'dashboard.php',
        3 => '../officier/dashboard.php',
        4 => '../president/dashboard.php',
    ];
    $redirectUrl = $redirectMap[$_SESSION['role']] ?? '../index.php';
    header("Location: $redirectUrl");
    exit();
}

// Fonction pour générer un code OTP aléatoire
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $loginMethod = $_POST['login_method'];
    $user = null;

    try {
        if ($loginMethod === 'phone') {
            // Nettoyer le numéro de téléphone
            $cleanPhone = preg_replace('/[^0-9+]/', '', $identifier);
            
            // Essayer différents formats de numéro
            $phoneFormats = [$cleanPhone];
            
            // Si le numéro commence par 6 et a 9 chiffres (format camerounais)
            if (strlen($cleanPhone) === 9 && substr($cleanPhone, 0, 1) === '6') {
                $phoneFormats[] = '+237' . $cleanPhone;
            }
            
            // Si le numéro commence par 237
            if (strlen($cleanPhone) >= 12 && substr($cleanPhone, 0, 3) === '237') {
                $phoneFormats[] = '+' . $cleanPhone;
                $phoneFormats[] = substr($cleanPhone, 3);
            }
            
            // Construire la requête pour chercher tous les formats possibles
            $placeholders = implode(',', array_fill(0, count($phoneFormats), '?'));
            $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE NumeroTelephone IN ($placeholders)");
            $stmt->execute($phoneFormats);
            
            // Log pour débogage
            error_log("Recherche téléphone: " . implode(', ', $phoneFormats));
        } else {
            // Pour l'email, convertir en minuscules
            $cleanEmail = strtolower($identifier);
            $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE LOWER(Email) = ?");
            $stmt->execute([$cleanEmail]);
            
            // Log pour débogage
            error_log("Recherche email: " . $cleanEmail);
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log pour débogage
        error_log("Résultat recherche: " . ($user ? "Utilisateur trouvé (ID: {$user['UtilisateurID']})" : "Aucun utilisateur trouvé"));
        
        if (!$user) {
            // Afficher tous les utilisateurs pour débogage (à supprimer en production)
            $debugStmt = $db->query("SELECT UtilisateurID, Email, NumeroTelephone FROM utilisateurs LIMIT 10");
            $debugUsers = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Premiers utilisateurs en base: " . json_encode($debugUsers));
            
            $error = '<div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ' . ($loginMethod === 'email' ? 'Adresse email' : 'Numéro de téléphone') . ' non trouvé
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
        } elseif (!$user['IsActive']) {
            $error = '<div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Ce compte est désactivé. Veuillez contacter l\'administrateur.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
        } else {
            // Générer un code OTP aléatoire
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Mettre à jour la base de données avec le nouveau code OTP
            $stmt = $db->prepare("UPDATE utilisateurs SET CodeOTP = ?, ExpirationOTP = ? WHERE UtilisateurID = ?");
            $stmt->execute([$otp, $expiry, $user['UtilisateurID']]);

            // Simuler l'envoi d'OTP (pour développement)
            $_SESSION['simulated_otp'] = $otp;
            $_SESSION['otp_simulation_time'] = time();
            $_SESSION['otp_simulation_method'] = $loginMethod;
            $_SESSION['otp_simulation_identifier'] = $identifier;
            
            // Log pour débogage
            error_log(($loginMethod === 'email' ? "Email" : "SMS") . " simulé envoyé à $identifier avec le code OTP: $otp");

            // Stocker les informations de session temporaires
            $_SESSION['login_identifier'] = $identifier;
            $_SESSION['login_method'] = $loginMethod;
            $_SESSION['temp_user_id'] = $user['UtilisateurID'];
            $_SESSION['otp_attempts'] = 0;

            // Afficher un message de chargement avant la redirection
            echo '<div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-3">Envoi du code de vérification...</p>
                  </div>';
            
            // Rediriger vers la vérification de l'OTP après un court délai
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'verify_otp.php';
                    }, 1500);
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        $error = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Erreur de connexion à la base de données
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        error_log("Erreur PDO: " . $e->getMessage());
    }
}

// Vérifier si nous avons un OTP simulé à afficher
$showDevInfo = false;
$simulatedOtp = '';
$simulationMethod = '';
$simulationIdentifier = '';
$simulationTime = '';

if (isset($_SESSION['simulated_otp'])) {
    $showDevInfo = true;
    $simulatedOtp = $_SESSION['simulated_otp'];
    $simulationMethod = $_SESSION['otp_simulation_method'] ?? 'inconnu';
    $simulationIdentifier = $_SESSION['otp_simulation_identifier'] ?? 'inconnu';
    $simulationTime = date('H:i:s', $_SESSION['otp_simulation_time']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CNI.CAM</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1774df;
            --primary-dark: #135bb2;
            --secondary-color: #00723F;
            --light-bg: #f8f9fa;
            --border-radius: 15px;
            --box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            margin: auto;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .login-header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .login-header img {
            width: 100px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        .login-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .progress-steps {
            display: flex;
            justify-content: center;
            margin: 25px 0;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 25%;
            right: 25%;
            height: 3px;
            background: #dee2e6;
            transform: translateY(-50%);
            z-index: 1;
            border-radius: 3px;
        }

        .progress-step {
            position: relative;
            z-index: 2;
            background: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 25px;
            transition: var(--transition);
            color: #6c757d;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }

        .progress-step.active {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .progress-step.completed {
            border-color: var(--secondary-color);
            background: var(--secondary-color);
            color: white;
        }

        .method-card {
            border: 2px solid #dee2e6;
            border-radius: var(--border-radius);
            padding: 25px 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 20px;
            background: white;
        }

        .method-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .method-card.selected {
            border-color: var(--primary-color);
            background: rgba(23, 116, 223, 0.05);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .method-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            transition: var(--transition);
        }

        .method-card:hover .method-icon {
            transform: scale(1.1);
        }

        .method-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .method-description {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: var(--transition);
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(23, 116, 223, 0.25);
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e9ecef;
            border-right: none;
            background-color: #f8f9fa;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(23, 116, 223, 0.3);
        }

        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
            transform: translateY(-3px);
        }

        .step-content {
            display: none;
            padding: 25px;
        }

        .step-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .iti {
            width: 100%;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #495057;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .create-account-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .create-account-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .alert {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        /* Animation pour le bouton de soumission */
        .btn-submit {
            position: relative;
        }

        .btn-submit .spinner-border {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }

        .btn-submit.loading .spinner-border {
            display: inline-block;
        }

        .btn-submit.loading {
            padding-left: 45px;
        }

        /* Mode développement - Affichage du code OTP simulé */
        .dev-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            max-width: 350px;
        }
        
        .dev-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .dev-info-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .dev-info-content {
            margin-bottom: 10px;
        }
        
        .dev-info-item {
            margin-bottom: 5px;
            display: flex;
        }
        
        .dev-info-label {
            font-weight: bold;
            width: 100px;
            color: #8ecdf7;
        }
        
        .dev-info-value {
            flex: 1;
            word-break: break-all;
        }
        
        .dev-info-otp {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            letter-spacing: 3px;
            color: #ffc107;
        }
        
        .dev-info-footer {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
            }
            
            .progress-step {
                width: 40px;
                height: 40px;
                margin: 0 15px;
            }
            
            .method-card {
                padding: 20px 15px;
            }
            
            .method-icon {
                font-size: 2rem;
            }
            
            .dev-info {
                left: 10px;
                right: 10px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/images/Cameroun.png" alt="CNI.CAM Logo" class="img-fluid">
                <h2 class="login-title">Connexion</h2>
                <p class="login-subtitle">Accédez à votre espace CNI.CAM</p>
            </div>

            <div class="progress-steps">
                <div class="progress-step active" data-step="1">1</div>
                <div class="progress-step" data-step="2">2</div>
            </div>

            <?php if (!empty($error)) echo $error; ?>
            <?php if (!empty($success)) echo $success; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="login_method" id="login_method" value="">

                <!-- Étape 1: Choix de la méthode -->
                <div class="step-content active" id="step1">
                    <h5 class="text-center mb-4">Choisissez votre méthode de connexion</h5>
                    
                    <div class="method-card" data-method="email">
                        <div class="method-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <h5 class="method-title">Email</h5>
                        <p class="method-description">Connexion avec votre adresse email</p>
                    </div>

                    <div class="method-card" data-method="phone">
                        <div class="method-icon">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <h5 class="method-title">Téléphone</h5>
                        <p class="method-description">Connexion avec votre numéro de téléphone</p>
                    </div>
                </div>

                <!-- Étape 2: Saisie de l'identifiant -->
                <div class="step-content" id="step2">
                    <div id="emailInput" style="display: none;">
                        <label class="form-label">Adresse email</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" class="form-control" name="identifier" id="email" placeholder="Entrez votre adresse email" required>
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>
                        <div class="form-text mb-3">
                            Nous vous enverrons un code de vérification à cette adresse.
                        </div>
                    </div>

                    <div id="phoneInput" style="display: none;">
                        <label class="form-label">Numéro de téléphone</label>
                        <div class="input-group mb-3">
                            <input type="tel" class="form-control" name="identifier" id="phone" placeholder="Entrez votre numéro de téléphone" required>
                            <div class="invalid-feedback">
                                Veuillez entrer un numéro de téléphone valide.
                            </div>
                        </div>
                        <div class="form-text mb-3">
                            Nous vous enverrons un code de vérification par SMS.
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i class="bi bi-arrow-right-circle me-2"></i>Continuer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="backButton">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </button>
                    </div>
                </div>
            </form>

            <div class="text-center p-4">
                <p class="mb-0">Vous n'avez pas de compte ? <a href="register.php" class="create-account-link">Créer un compte</a></p>
            </div>
        </div>
    </div>

    <!-- Info de développement pour voir le code OTP simulé -->
    <?php if ($showDevInfo): ?>
    <div class="dev-info" id="devInfo">
        <div class="dev-info-header">
            <span><i class="bi bi-code-slash"></i> <strong>Mode Développement</strong></span>
            <button type="button" class="dev-info-close" id="closeDevInfo">&times;</button>
        </div>
        <div class="dev-info-content">
            <div class="dev-info-item">
                <span class="dev-info-label">Méthode:</span>
                <span class="dev-info-value"><?php echo $simulationMethod === 'email' ? 'Email' : 'Téléphone'; ?></span>
            </div>
            <div class="dev-info-item">
                <span class="dev-info-label">Destinataire:</span>
                <span class="dev-info-value"><?php echo htmlspecialchars($simulationIdentifier); ?></span>
            </div>
            <div class="dev-info-item">
                <span class="dev-info-label">Heure:</span>
                <span class="dev-info-value"><?php echo $simulationTime; ?></span>
            </div>
        </div>
        <div class="dev-info-otp"><?php echo $simulatedOtp; ?></div>
        <div class="dev-info-footer">
            Ce code est simulé pour le développement uniquement.<br>
            Appuyez sur <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>D</kbd> pour afficher/masquer.
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation du plugin téléphone
            const phoneInput = document.getElementById('phone');
            const iti = window.intlTelInput(phoneInput, {
                initialCountry: "cm",
                preferredCountries: ["cm", "fr"],
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                separateDialCode: true,
                autoPlaceholder: "aggressive"
            });

            // Gestion des étapes
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const backButton = document.getElementById('backButton');
            const methodCards = document.querySelectorAll('.method-card');
            const progressSteps = document.querySelectorAll('.progress-step');
            const loginMethodInput = document.getElementById('login_method');
            const emailInput = document.getElementById('emailInput');
            const phoneInputDiv = document.getElementById('phoneInput');
            const submitBtn = document.getElementById('submitBtn');

            // Validation du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                let isValid = true;

                if (loginMethodInput.value === 'phone') {
                    if (iti.isValidNumber()) {
                        phoneInput.value = iti.getNumber();
                        phoneInput.setCustomValidity('');
                    } else {
                        phoneInput.setCustomValidity('Numéro de téléphone invalide');
                        isValid = false;
                    }
                } else if (loginMethodInput.value === 'email') {
                    const emailField = document.getElementById('email');
                    if (!emailField.validity.valid) {
                        isValid = false;
                    }
                }

                if (isValid) {
                    // Afficher l'animation de chargement
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    
                    // Soumettre le formulaire après un court délai pour montrer l'animation
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
                form.classList.add('was-validated');
            });

            // Gestion du clic sur les cartes de méthode
            methodCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.dataset.method;
                    loginMethodInput.value = method;

                    methodCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');

                    // Animation de transition
                    step1.classList.add('fade-out');
                    
                    setTimeout(() => {
                        step1.classList.remove('active', 'fade-out');
                        step2.classList.add('active');
                        progressSteps[0].classList.add('completed');
                        progressSteps[1].classList.add('active');

                        if (method === 'email') {
                            emailInput.style.display = 'block';
                            phoneInputDiv.style.display = 'none';
                            document.getElementById('email').focus();
                        } else {
                            emailInput.style.display = 'none';
                            phoneInputDiv.style.display = 'block';
                            document.getElementById('phone').focus();
                        }
                    }, 300);
                });
            });

            // Gestion du bouton retour
            backButton.addEventListener('click', function() {
                step2.classList.add('fade-out');
                
                setTimeout(() => {
                    step2.classList.remove('active', 'fade-out');
                    step1.classList.add('active');
                    progressSteps[0].classList.remove('completed');
                    progressSteps[1].classList.remove('active');
                    loginMethodInput.value = '';
                    form.classList.remove('was-validated');
                }, 300);
            });

            // Validation en temps réel du numéro de téléphone
            phoneInput.addEventListener('input', function() {
                if (iti.isValidNumber()) {
                    phoneInput.setCustomValidity('');
                    phoneInput.classList.remove('is-invalid');
                    phoneInput.classList.add('is-valid');
                } else {
                    phoneInput.setCustomValidity('Numéro de téléphone invalide');
                    phoneInput.classList.remove('is-valid');
                    phoneInput.classList.add('is-invalid');
                }
            });

            // Validation en temps réel de l'email
            const emailField = document.getElementById('email');
            emailField.addEventListener('input', function() {
                if (emailField.validity.valid) {
                    emailField.classList.remove('is-invalid');
                    emailField.classList.add('is-valid');
                    emailField.setCustomValidity('');
                } else {
                    emailField.classList.remove('is-valid');
                    emailField.classList.add('is-invalid');
                    emailField.setCustomValidity('Veuillez entrer une adresse email valide');
                }
            });

            // Fermeture automatique des alertes après 5 secondes
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Animation des boutons au clic
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mousedown', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Gestion de la fenêtre d'information OTP
            const devInfo = document.getElementById('devInfo');
            if (devInfo) {
                // Fermer la fenêtre d'info OTP
                document.getElementById('closeDevInfo').addEventListener('click', function() {
                    devInfo.style.display = 'none';
                });
                
                // Rendre la fenêtre d'info OTP déplaçable
                let isDragging = false;
                let offsetX, offsetY;
                
                const devInfoHeader = devInfo.querySelector('.dev-info-header');
                
                devInfoHeader.addEventListener('mousedown', function(e) {
                    isDragging = true;
                    offsetX = e.clientX - devInfo.getBoundingClientRect().left;
                    offsetY = e.clientY - devInfo.getBoundingClientRect().top;
                    devInfo.style.cursor = 'grabbing';
                });
                
                document.addEventListener('mousemove', function(e) {
                    if (isDragging) {
                        devInfo.style.left = (e.clientX - offsetX) + 'px';
                        devInfo.style.top = (e.clientY - offsetY) + 'px';
                        devInfo.style.right = 'auto';
                        devInfo.style.bottom = 'auto';
                    }
                });
                
                document.addEventListener('mouseup', function() {
                    isDragging = false;
                    devInfo.style.cursor = 'default';
                });
            }

            // Mode développement - Afficher/masquer le code OTP simulé avec Ctrl+Shift+D
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    const devInfo = document.getElementById('devInfo');
                    if (devInfo) {
                        devInfo.style.display = devInfo.style.display === 'none' ? 'block' : 'none';
                    }
                }
            });
        });

        // Fonction pour empêcher la soumission multiple du formulaire
        function preventMultipleSubmissions() {
            const form = document.querySelector('form');
            const submitButton = document.getElementById('submitBtn');
            
            form.addEventListener('submit', function() {
                // Désactiver le bouton de soumission
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
                
                // Réactiver le bouton après 10 secondes (au cas où la soumission échoue)
                setTimeout(function() {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="bi bi-arrow-right-circle me-2"></i>Continuer';
                }, 10000);
            });
        }
        
        // Appeler la fonction pour empêcher les soumissions multiples
        preventMultipleSubmissions();
    </script>
</body>
</html>
