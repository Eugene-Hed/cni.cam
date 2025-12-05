<?php
global $db;

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = '';
$error_message = '';

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE UtilisateurID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ?page=" . base64_encode('gestion_utilisateurs'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $dateNaissance = $_POST['dateNaissance'];
    $adresse = $_POST['adresse'];
    $role = $_POST['role'];
    $genre = $_POST['genre'];
    $isActive = isset($_POST['isActive']) ? 1 : 0;

    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }

    // Vérifier si l'email existe déjà pour un autre utilisateur
    $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE Email = ? AND UtilisateurID != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Cet email est déjà utilisé";
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            $sql = "UPDATE utilisateurs SET 
                    Email = ?, 
                    NumeroTelephone = ?, 
                    Prenom = ?, 
                    Nom = ?, 
                    DateNaissance = ?, 
                    Adresse = ?, 
                    RoleId = ?, 
                    Genre = ?,
                    IsActive = ?,
                    DateMiseAJour = NOW()
                    WHERE UtilisateurID = ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $email,
                $telephone,
                $prenom,
                $nom,
                $dateNaissance,
                $adresse,
                $role,
                $genre,
                $isActive,
                $userId
            ]);

            // Journal d'activité
            $stmt = $db->prepare("INSERT INTO journalactivites (UtilisateurID, TypeActivite, Description, AdresseIP) 
                                VALUES (?, 'Modification_Utilisateur', ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                "Modification de l'utilisateur ID: $userId",
                $_SERVER['REMOTE_ADDR']
            ]);

            $db->commit();
            $success_message = "Utilisateur modifié avec succès!";
            
            // Rafraîchir les données
            $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE UtilisateurID = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error_message = "Erreur lors de la modification: " . $e->getMessage();
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
            <h5 class="mb-0">Modifier l'utilisateur</h5>
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
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['Codeutilisateur']); ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['Prenom']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['Nom']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?php echo htmlspecialchars($user['NumeroTelephone']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="dateNaissance" class="form-control" value="<?php echo $user['DateNaissance']; ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $user['RoleId'] == $role['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Genre</label>
                    <select name="genre" class="form-select" required>
                        <option value="Homme" <?php echo $user['Genre'] == 'Homme' ? 'selected' : ''; ?>>Homme</option>
                        <option value="Femme" <?php echo $user['Genre'] == 'Femme' ? 'selected' : ''; ?>>Femme</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="3" required><?php echo htmlspecialchars($user['Adresse']); ?></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="isActive" id="isActive" <?php echo $user['IsActive'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isActive">Compte actif</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
