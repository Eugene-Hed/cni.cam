<?php
include('../includes/config.php');
include('../includes/check_auth.php');


$step = isset($_GET['step']) ? $_GET['step'] : '1';
$message = '';
$error = '';

// Étape 1: Demande de réinitialisation
if($_SERVER['REQUEST_METHOD'] == 'POST' && $step == '1') {
    $email = $_POST['email'];
    
    $query = "SELECT * FROM utilisateurs WHERE Email = :email";
    $stmt = $db->prepare($query);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if($user) {
        // Génération token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Stockage temporaire du token (à implémenter dans la base de données)
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_expiry'] = $expiry;

        // Simuler l'envoi d'email (à implémenter avec un vrai service d'email)
        $resetLink = "/cni.cam/pages/reset_password.php?step=2&token=" . $token;
        $message = "Un lien de réinitialisation a été envoyé à votre adresse email.";
        
        header("Location: reset_password.php?step=2&token=" . $token);
        exit();
    } else {
        $error = "Aucun compte associé à cette adresse email.";
    }
}

// Étape 2: Réinitialisation du mot de passe
if($_SERVER['REQUEST_METHOD'] == 'POST' && $step == '2') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($token == $_SESSION['reset_token'] && time() < strtotime($_SESSION['reset_expiry'])) {
        if($password == $confirm_password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "UPDATE utilisateurs SET MotDePasse = :password WHERE Email = :email";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'password' => $hashedPassword,
                'email' => $_SESSION['reset_email']
            ]);

            unset($_SESSION['reset_token']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_expiry']);

            $message = "Votre mot de passe a été réinitialisé avec succès.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Le lien de réinitialisation est invalide ou expiré.";
    }
}

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Réinitialisation du mot de passe</h2>

                    <?php if($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if($step == '1'): ?>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Réinitialiser le mot de passe
                                </button>
                            </div>
                        </form>
                    <?php elseif($step == '2'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none">Retour à la connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
