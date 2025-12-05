<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$demandeId) {
    header('Location: mes_demandes.php');
    exit();
}

// Vérifier que la demande appartient à l'utilisateur et est en statut "Approuvee"
$query = "SELECT * FROM demandes WHERE DemandeID = :id AND UtilisateurID = :userId AND Statut = 'Approuvee' AND SignatureRequise = 1 AND (SignatureEnregistree = 0 OR SignatureEnregistree IS NULL)";
$stmt = $db->prepare($query);
$stmt->execute([
    'id' => $demandeId,
    'userId' => $_SESSION['user_id']
]);
$demande = $stmt->fetch();

if (!$demande) {
    $_SESSION['error_message'] = "La demande n'existe pas, n'est pas approuvée ou la signature a déjà été enregistrée.";
    header('Location: mes_demandes.php');
    exit();
}

// Récupération des détails de la demande
$query = "SELECT d.*, dc.Nom, dc.Prenom
          FROM demandes d
          JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
          WHERE d.DemandeID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demandeDetails = $stmt->fetch();

// Traitement de l'enregistrement de la signature
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signature'])) {
    $signatureData = $_POST['signature'];
    
    try {
        $db->beginTransaction();

        // Créer le répertoire de signatures si nécessaire
        $uploadsDir = '../uploads/signatures';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Extraire les données de l'image base64
        $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
        $signatureData = str_replace(' ', '+', $signatureData);
        $signatureImage = base64_decode($signatureData);

        // Générer un nom de fichier unique
        $fileName = 'signature_' . $demandeId . '_' . time() . '.png';
        $filePath = $uploadsDir . '/' . $fileName;

        // Enregistrer l'image
        file_put_contents($filePath, $signatureImage);

        // Enregistrer le document dans la base de données
$query = "INSERT INTO documents (DemandeID, UtilisateurID, TypeDocument, CheminFichier, DateTelechargement, StatutValidation) 
          VALUES (:demandeId, :userId, 'Signature', :cheminFichier, NOW(), 'Approuve')";
$stmt = $db->prepare($query);
$stmt->execute([
    'demandeId' => $demandeId,
    'userId' => $_SESSION['user_id'],
    'cheminFichier' => $filePath
]);

if ($stmt->rowCount() == 0) {
    error_log("Échec de l'insertion de la signature: " . print_r($stmt->errorInfo(), true));
}


        // Mettre à jour le statut de la signature dans la demande
$query = "UPDATE demandes SET 
SignatureEnregistree = 1, 
CheminSignature = :cheminSignature, 
DateSignature = NOW() 
WHERE DemandeID = :id";
$stmt = $db->prepare($query);
$stmt->execute([
'id' => $demandeId,
'cheminSignature' => $filePath
]);


        // Ajouter dans l'historique
        $query = "INSERT INTO historique_demandes 
                  (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                  VALUES (:demandeId, 'Approuvee', 'Approuvee', 'Signature enregistrée par le citoyen', :userId, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'userId' => $_SESSION['user_id']
        ]);

        // Ajouter une notification pour l'officier
        $query = "INSERT INTO notifications 
                  (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                  VALUES (
                      (SELECT UtilisateurID FROM utilisateurs WHERE RoleId = 3 LIMIT 1), 
                      :demandeId, 
                      :contenu, 
                      'signature_enregistree', 
                      NOW()
                  )";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'contenu' => 'La signature pour la demande #' . $demandeId . ' a été enregistrée. Vous pouvez maintenant générer la CNI.'
        ]);

        $db->commit();
        
        $_SESSION['success_message'] = "Votre signature a été enregistrée avec succès.";
        header('Location: details_demande.php?id=' . $demandeId);
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de l'enregistrement de la signature: " . $e->getMessage();
    }
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">Enregistrer votre signature</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fs-3"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Information importante</h5>
                                <p class="mb-0">Votre signature sera imprimée sur votre Carte Nationale d'Identité. Veuillez signer clairement dans la zone ci-dessous.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Demande #<?php echo $demandeId; ?></h5>
                        <p>
                            <strong>Nom:</strong> <?php echo htmlspecialchars($demandeDetails['Nom']); ?><br>
                            <strong>Prénom:</strong> <?php echo htmlspecialchars($demandeDetails['Prenom']); ?><br>
                            <strong>Référence:</strong> <?php echo htmlspecialchars($demandeDetails['NumeroReference']); ?>
                        </p>
                    </div>

                    <form id="signature-form" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Votre signature</label>
                            <div class="signature-container">
                                <canvas id="signature-pad" class="signature-pad"></canvas>
                            </div>
                            <input type="hidden" name="signature" id="signature-data">
                        </div>

                        <div class="d-flex gap-2 mb-4">
                            <button type="button" id="clear-signature" class="btn btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i> Effacer
                            </button>
                            <button type="submit" id="save-signature" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Enregistrer ma signature
                            </button>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Attention:</strong> Une fois enregistrée, votre signature ne pourra plus être modifiée.
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light py-3">
                    <a href="details_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Retour aux détails de la demande
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.signature-container {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
    margin-bottom: 1rem;
}

#signature-pad {
    width: 100%;
    height: 200px;
    background-color: #fff;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
    });

    // Redimensionner le canvas pour qu'il remplisse son conteneur
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        signaturePad.clear(); // Effacer le contenu après redimensionnement
    }

    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    // Effacer la signature
    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
    });

    // Soumettre le formulaire
    document.getElementById('signature-form').addEventListener('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Veuillez signer avant de soumettre.');
            return false;
        }

        // Récupérer l'image de la signature en base64
        const signatureData = signaturePad.toDataURL();
        document.getElementById('signature-data').value = signatureData;
    });
});
</script>

<?php include('../includes/footer.php'); ?>
