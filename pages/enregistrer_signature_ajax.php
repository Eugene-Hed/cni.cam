<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour effectuer cette action.']);
    exit();
}

// Vérification des données reçues
if (!isset($_POST['demande_id']) || !isset($_POST['signature'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

$demandeId = (int)$_POST['demande_id'];
$signatureData = $_POST['signature'];

// Vérifier que la demande appartient à l'utilisateur et est en statut "Approuvee"
$query = "SELECT * FROM demandes WHERE DemandeID = :id AND UtilisateurID = :userId AND Statut = 'Approuvee' AND SignatureRequise = 1 AND (SignatureEnregistree = 0 OR SignatureEnregistree IS NULL)";
$stmt = $db->prepare($query);
$stmt->execute([
    'id' => $demandeId,
    'userId' => $_SESSION['user_id']
]);
$demande = $stmt->fetch();

if (!$demande) {
    echo json_encode(['success' => false, 'message' => 'Demande introuvable ou signature déjà enregistrée.']);
    exit();
}

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
    echo json_encode(['success' => true, 'message' => 'Signature enregistrée avec succès.']);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la signature: ' . $e->getMessage()]);
}
