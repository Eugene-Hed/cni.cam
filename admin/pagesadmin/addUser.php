<?php
global $db;

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codeUtilisateur = $_POST['codeUtilisateur'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $telephone = $_POST['telephone'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $dateNaissance = $_POST['dateNaissance'];
    $adresse = $_POST['adresse'];
    $role = $_POST['role'];
    $genre = $_POST['genre'];

    $errors = [];

    // Validation checks
    if (empty($codeUtilisateur)) {
        $errors[] = "Le code utilisateur est requis";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    // Check existing email and code
    $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cet email est déjà utilisé";
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE Codeutilisateur = ?");
    $stmt->execute([$codeUtilisateur]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Ce code utilisateur est déjà utilisé";
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO utilisateurs (Codeutilisateur, Email, MotDePasse, NumeroTelephone, 
                    Prenom, Nom, DateNaissance, Adresse, RoleId, Genre, IsActive) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $codeUtilisateur,
                $email,
                $hashedPassword,
                $telephone,
                $prenom,
                $nom,
                $dateNaissance,
                $adresse,
                $role,
                $genre
            ]);

            $db->commit();
            $success_message = "Utilisateur ajouté avec succès!";
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Erreur lors de l'ajout de l'utilisateur: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Récupérer la liste des rôles
$stmt = $db->query("SELECT * FROM role");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ajouter un nouvel utilisateur</h5>
            <a href="?page=<?php echo base64_encode('gestion_utilisateurs'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Code utilisateur</label>
                    <input type="text" name="codeUtilisateur" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="confirmPassword" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="dateNaissance" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>">
                                <?php echo htmlspecialchars($role['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Genre</label>
                    <select name="genre" class="form-select" required>
                        <option value="Homme">Homme</option>
                        <option value="Femme">Femme</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="3" required></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Ajouter l'utilisateur
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
