<?php
session_start();
include('../includes/config.php');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$demandeId) {
    header('Location: demandes_nationalite.php');
    exit();
}

// Vérifier que la demande est approuvée
$query = "SELECT d.*, u.Nom, u.Prenom 
          FROM demandes d
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE d.DemandeID = :id 
          AND d.Statut = 'Approuvee'";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demande = $stmt->fetch();

if (!$demande) {
    $_SESSION['error_message'] = "La demande n'existe pas ou n'est pas approuvée.";
    header('Location: demandes_nationalite.php');
    exit();
}

// Traitement de l'enregistrement de la signature
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signature'])) {
    $signatureData = $_POST['signature'];
    
    try {
        $db->beginTransaction();

        // Créer le répertoire de signatures si nécessaire
        $uploadsDir = '../uploads/signatures_president';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Extraire les données de l'image base64
        $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
        $signatureData = str_replace(' ', '+', $signatureData);
        $signatureImage = base64_decode($signatureData);

        // Générer un nom de fichier unique
        $fileName = 'signature_president_' . $demandeId . '_' . time() . '.png';
        $filePath = $uploadsDir . '/' . $fileName;

        // Enregistrer l'image
        file_put_contents($filePath, $signatureImage);

        // Vérifier si un certificat existe déjà pour cette demande
        $stmt = $db->prepare("SELECT COUNT(*) FROM certificatsnationalite WHERE DemandeID = ?");
        $stmt->execute([$demandeId]);
        $certificatExists = $stmt->fetchColumn() > 0;
        
        if (!$certificatExists) {
            // Créer une entrée dans la table certificatsnationalite
            $numeroCertificat = 'NAT-' . date('Y') . '-' . str_pad($demandeId, 6, '0', STR_PAD_LEFT);
            
            $stmt = $db->prepare("INSERT INTO certificatsnationalite 
                                (DemandeID, NumeroCertificat, DateEmission, SignaturePresidentielle, CheminSignaturePresident) 
                                VALUES (?, ?, CURDATE(), 1, ?)");
            $stmt->execute([$demandeId, $numeroCertificat, $filePath]);
        } else {
            // Mettre à jour l'entrée existante
            $stmt = $db->prepare("UPDATE certificatsnationalite 
                                SET SignaturePresidentielle = 1,
                                    CheminSignaturePresident = ?
                                WHERE DemandeID = ?");
            $stmt->execute([$filePath, $demandeId]);
        }
        
        // Mettre à jour le statut de la demande
        $stmt = $db->prepare("UPDATE demandes 
                            SET SignatureOfficierRequise = 1, 
                                SignatureOfficierEnregistree = 1, 
                                CheminSignatureOfficier = ?, 
                                DateSignatureOfficier = NOW() 
                            WHERE DemandeID = ?");
        $stmt->execute([$filePath, $demandeId]);
        
        // Ajouter une entrée dans l'historique
        $stmt = $db->prepare("INSERT INTO historique_demandes 
                            (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                            VALUES (?, 'Approuvee', 'Approuvee', 'Signature présidentielle enregistrée', ?, NOW())");
        $stmt->execute([$demandeId, $_SESSION['user_id']]);
        
        // Ajouter une notification
        $stmt = $db->prepare("INSERT INTO notifications 
                            (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                            VALUES (?, ?, 'Votre certificat de nationalité a été signé par le président et est en cours de finalisation.', 'signature_president', NOW())");
        $stmt->execute([$demande['UtilisateurID'], $demandeId]);
        
        // Journal d'activité
        $stmt = $db->prepare("INSERT INTO journalactivites 
                            (UtilisateurID, TypeActivite, Description, AdresseIP) 
                            VALUES (?, 'Signature_Certificat', ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            "Signature présidentielle pour le certificat de nationalité #$demandeId",
            $_SERVER['REMOTE_ADDR']
        ]);

        $db->commit();
        
        $_SESSION['success_message'] = "Votre signature a été enregistrée avec succès.";
        header('Location: traiter_demande.php?id=' . $demandeId);
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de l'enregistrement de la signature: " . $e->getMessage();
    }
}

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('../includes/president_sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Signer le certificat de nationalité</h1>
                <a href="traiter_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Signature du Président</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fs-3"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Information importante</h5>
                                <p class="mb-0">Votre signature sera imprimée sur le Certificat de Nationalité en tant que Président. Veuillez signer clairement dans la zone ci-dessous.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Demande #<?php echo $demandeId; ?></h5>
                        <p>
                            <strong>Nom:</strong> <?php echo htmlspecialchars($demande['Nom']); ?><br>
                            <strong>Prénom:</strong> <?php echo htmlspecialchars($demande['Prenom']); ?><br>
                            <strong>Référence:</strong> <?php echo htmlspecialchars($demande['NumeroReference']); ?>
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
            </div>
        </main>
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
