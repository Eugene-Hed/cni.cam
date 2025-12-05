<?php
include('../includes/config.php');
include('../includes/check_auth.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: /cni.cam/pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user information
$query = "SELECT * FROM utilisateurs WHERE UtilisateurID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telephone = preg_replace('/[^0-9]/', '', $_POST['telephone']);
        $adresse = htmlspecialchars($_POST['adresse']);

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['photo']['size'];

            if (!in_array($filetype, $allowed)) {
                throw new Exception('Format de fichier non autorisé. Utilisez JPG, JPEG ou PNG.');
            }

            if ($filesize > 5 * 1024 * 1024) {
                throw new Exception('La taille du fichier ne doit pas dépasser 5 MB.');
            }

            $uploadDir = '../uploads/profile_pictures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newname = uniqid('profile_') . '.' . $filetype;
            $destination = $uploadDir . $newname;

            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                throw new Exception('Erreur lors du téléchargement de l\'image.');
            }

            // Delete old photo if exists
            if (!empty($user['PhotoUtilisateur']) && file_exists($user['PhotoUtilisateur'])) {
                unlink($user['PhotoUtilisateur']);
            }

            // Update photo in database
            $queryPhoto = "UPDATE utilisateurs SET PhotoUtilisateur = :photo WHERE UtilisateurID = :id";
            $stmtPhoto = $db->prepare($queryPhoto);
            $stmtPhoto->execute([
                'photo' => $destination,
                'id' => $userId
            ]);
        }

        // Update user information
        $query = "UPDATE utilisateurs SET 
                  Nom = :nom, 
                  Prenom = :prenom, 
                  Email = :email, 
                  NumeroTelephone = :telephone, 
                  Adresse = :adresse
                  WHERE UtilisateurID = :id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'adresse' => $adresse,
            'id' => $userId
        ]);

        // Log the activity
        $logQuery = "INSERT INTO journalactivites (UtilisateurID, TypeActivite, Description, AdresseIP) 
                    VALUES (:userId, 'Modification_Profil', 'Mise à jour du profil', :ip)";
        $stmtLog = $db->prepare($logQuery);
        $stmtLog->execute([
            'userId' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        $db->commit();

        // Refresh user data
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE UtilisateurID = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        $success = "Profil mis à jour avec succès";

    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="<?php echo !empty($user['PhotoUtilisateur']) ? $user['PhotoUtilisateur'] : '../assets/images/default-avatar.png'; ?>" 
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h5><?php echo $user['Prenom'] . ' ' . $user['Nom']; ?></h5>
                    <p class="text-muted">Code: <?php echo $user['Codeutilisateur']; ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Modifier mon profil</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" name="nom" value="<?php echo $user['Nom']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" class="form-control" name="prenom" value="<?php echo $user['Prenom']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo $user['Email']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone" value="<?php echo $user['NumeroTelephone']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" class="form-control" name="adresse" value="<?php echo $user['Adresse']; ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png" onchange="previewImage(event)">
                            <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        const fileType = fileInput.files[0].type;
        
        if (fileSize > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('La taille du fichier ne doit pas dépasser 5 MB');
        }
        
        if (!['image/jpeg', 'image/png'].includes(fileType)) {
            e.preventDefault();
            alert('Format de fichier non autorisé. Utilisez JPG ou PNG');
        }
    }
});
</script>

<?php include('../includes/footer.php'); ?>
