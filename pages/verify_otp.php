<?php
// Session is initialized centrally in includes/config.php
include('../includes/config.php');

// Vérifier si les informations de session temporaires existent
if (!isset($_SESSION['temp_user_id'], $_SESSION['login_method'], $_SESSION['login_identifier'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['temp_user_id'];
$loginMethod = $_SESSION['login_method'];
$identifier = $_SESSION['login_identifier'];
$error = '';
$success = '';
$maxAttempts = 3;

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE UtilisateurID = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    header('Location: login.php');
    exit();
}

// Vérifier si le code OTP est expiré
$otpExpired = isset($user['ExpirationOTP']) && strtotime($user['ExpirationOTP']) < time();

// Traitement du renvoi de code OTP
if (isset($_POST['resend'])) {
    $otp = sprintf("%06d", random_int(0, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Mettre à jour la base de données
    $stmt = $db->prepare("UPDATE utilisateurs SET CodeOTP = ?, ExpirationOTP = ? WHERE UtilisateurID = ?");
    $stmt->execute([$otp, $expiry, $userId]);

    // Simuler l'envoi du nouveau code OTP
    $_SESSION['simulated_otp'] = $otp;
    $_SESSION['otp_simulation_time'] = time();
    
    // Log pour débogage
    error_log(($loginMethod === 'email' ? "Email" : "SMS") . " simulé envoyé à $identifier avec le nouveau code OTP: $otp");

    $success = '<div class="alert alert-success alert-dismissible fade show">
                  <i class="bi bi-check-circle-fill me-2"></i>
                  Un nouveau code a été envoyé. Veuillez vérifier votre ' . ($loginMethod === 'email' ? 'email' : 'téléphone') . '.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';

    $_SESSION['otp_attempts'] = 0;
    $otpExpired = false;
}

// Traitement de la vérification du code OTP
if (isset($_POST['verify'])) {
    $enteredOTP = trim($_POST['otp']);
    $_SESSION['otp_attempts'] = $_SESSION['otp_attempts'] ?? 0;

    if ($_SESSION['otp_attempts'] >= $maxAttempts) {
        $error = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Nombre maximum de tentatives atteint. Veuillez demander un nouveau code.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
    } elseif ($otpExpired) {
        $error = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Le code a expiré. Veuillez demander un nouveau code.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
    } elseif ($enteredOTP !== $user['CodeOTP']) {
        $_SESSION['otp_attempts']++;
        $remainingAttempts = $maxAttempts - $_SESSION['otp_attempts'];
        $error = '<div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Code incorrect. ' . ($remainingAttempts > 0 ? "Il vous reste $remainingAttempts tentative(s)." : "Veuillez demander un nouveau code.") . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
    } else {
        // Invalider le code OTP
        $stmt = $db->prepare("UPDATE utilisateurs SET CodeOTP = NULL, ExpirationOTP = NULL WHERE UtilisateurID = ?");
        $stmt->execute([$userId]);

        // Créer la session utilisateur
        $_SESSION['user_id'] = $user['UtilisateurID'];
        $_SESSION['nom'] = $user['Nom'];
        $_SESSION['prenom'] = $user['Prenom'];
        $_SESSION['email'] = $user['Email'];
        $_SESSION['role'] = $user['RoleId'];
        $_SESSION['codeutilisateur'] = $user['Codeutilisateur'];
        $_SESSION['photo'] = $user['PhotoUtilisateur'];

        // Supprimer les variables de session temporaires
        unset($_SESSION['temp_user_id'], $_SESSION['login_method'], $_SESSION['login_identifier'], $_SESSION['otp_attempts']);
        unset($_SESSION['simulated_otp'], $_SESSION['otp_simulation_time']);

        // Afficher un message de chargement avant la redirection
        echo '<div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255,255,255,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-3">Connexion réussie ! Redirection...</p>
              </div>';

        // Rediriger selon le rôle
        echo "<script>
                setTimeout(function() {
                    window.location.href = '" . getRedirectUrl($user['RoleId']) . "';
                }, 1500);
              </script>";
        exit();
    }
}

// Fonction pour déterminer l'URL de redirection selon le rôle
function getRedirectUrl($role) {
    $redirectMap = [
        1 => '../admin/admin_dashboard.php',
        2 => 'dashboard.php',
        3 => '../officier/dashboard.php',
        4 => '../president/dashboard.php',
    ];
    return $redirectMap[$role] ?? '../index.php';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification - CNI.CAM</title>
    <link rel="icon" href="../assets/images/favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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

        .otp-container {
            max-width: 450px;
            width: 100%;
            margin: auto;
            padding: 20px;
        }

        .otp-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .otp-header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            position: relative;
        }

        .otp-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .otp-header img {
            width: 100px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        .otp-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .otp-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .otp-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            transition: var(--transition);
        }

        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(23, 116, 223, 0.25);
            outline: none;
        }

        .otp-single-input {
            width: 100%;
            max-width: 240px;
            margin: 0 auto;
            text-align: center;
            font-size: 24px;
            letter-spacing: 0.5em;
            font-weight: bold;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .otp-single-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(23, 116, 223, 0.25);
            outline: none;
        }

        .timer {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 20px 0;
            text-align: center;
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

        .btn-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .btn-link:hover {
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

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Mode développement - Affichage du code OTP simulé */
        .dev-info {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.8rem;
            z-index: 1000;
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .otp-container {
                padding: 10px;
            }
            
            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="otp-card">
            <div class="otp-header">
                <img src="../assets/images/Cameroun.gif" alt="CNI.CAM Logo" class="img-fluid">
                <h2 class="otp-title">Vérification</h2>
                <p class="otp-subtitle">Entrez le code reçu par <?php echo $loginMethod === 'email' ? 'email' : 'SMS'; ?></p>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($error)) echo $error; ?>
                <?php if (!empty($success)) echo $success; ?>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-4 text-center">
                        <label for="otp" class="form-label">Code de vérification</label>
                        <input type="text" class="otp-single-input" name="otp" id="otp" maxlength="6" autocomplete="off" inputmode="numeric" pattern="[0-9]*" required>
                        <div class="invalid-feedback">
                            Veuillez entrer un code valide à 6 chiffres.
                        </div>
                    </div>

                    <div class="timer text-center" id="timer">
                        <span id="minutes">05</span>:<span id="seconds">00</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="verify" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Vérifier
                        </button>
                        
                        <button type="submit" name="resend" class="btn btn-outline-secondary" <?php echo $otpExpired ? '' : 'disabled'; ?> id="resendBtn">
                            <i class="bi bi-arrow-repeat me-2"></i>Renvoyer le code
                        </button>
                        
                        <a href="login.php" class="btn btn-link text-decoration-none">
                            <i class="bi bi-arrow-left me-2"></i>Retour à la connexion
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info de développement pour voir le code OTP simulé -->
    <div class="dev-info" id="devInfo">
        <div><strong>Mode développement</strong></div>
        <div>Code OTP: <span id="simulatedOtp">-</span></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus sur le champ OTP
            document.getElementById('otp').focus();
            
            // Gestion du minuteur
            let duration = 300; // 5 minutes en secondes
            const timerDisplay = document.getElementById('timer');
            const minutesDisplay = document.getElementById('minutes');
            const secondsDisplay = document.getElementById('seconds');
            const resendBtn = document.getElementById('resendBtn');
            
            <?php if ($otpExpired): ?>
                // Si le code est déjà expiré, désactiver le minuteur
                timerDisplay.innerHTML = '<span class="text-danger">Code expiré</span>';
                resendBtn.disabled = false;
            <?php else: ?>
                // Sinon, démarrer le minuteur
                const timer = setInterval(function() {
                    duration--;
                    
                    const minutes = Math.floor(duration / 60);
                    const seconds = duration % 60;
                    
                    minutesDisplay.textContent = minutes.toString().padStart(2, '0');
                    secondsDisplay.textContent = seconds.toString().padStart(2, '0');
                    
                    if (duration <= 0) {
                        clearInterval(timer);
                        timerDisplay.innerHTML = '<span class="text-danger">Code expiré</span>';
                        resendBtn.disabled = false;
                    }
                }, 1000);
            <?php endif; ?>
            
            // Validation du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const otpInput = document.getElementById('otp');
                if (otpInput.value.length !== 6 || !/^\d+$/.test(otpInput.value)) {
                    event.preventDefault();
                    otpInput.classList.add('is-invalid');
                } else {
                    otpInput.classList.remove('is-invalid');
                }
            });
            
            // Validation en temps réel du code OTP
            const otpInput = document.getElementById('otp');
            otpInput.addEventListener('input', function() {
                // Forcer uniquement des chiffres
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 6 && /^\d+$/.test(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    if (this.value.length > 0) {
                        this.classList.add('is-invalid');
                    }
                }
            });
            
            // Auto-remplissage du code OTP simulé
            <?php if (isset($_SESSION['simulated_otp'])): ?>
                // Afficher le code OTP simulé dans le panneau de développement
                const devInfo = document.getElementById('devInfo');
                const simulatedOtp = document.getElementById('simulatedOtp');
                
                devInfo.style.display = 'block';
                simulatedOtp.textContent = '<?php echo $_SESSION['simulated_otp']; ?>';
                
                // Double-clic sur le code pour l'auto-remplir
                simulatedOtp.addEventListener('dblclick', function() {
                    document.getElementById('otp').value = this.textContent;
                    document.getElementById('otp').classList.add('is-valid');
                });
            <?php endif; ?>
            
            // Mode développement - Afficher/masquer le panneau avec Ctrl+Shift+D
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    const devInfo = document.getElementById('devInfo');
                    devInfo.style.display = devInfo.style.display === 'none' ? 'block' : 'none';
                }
            });
            
            // Fermeture automatique des alertes après 5 secondes
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
    </script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
