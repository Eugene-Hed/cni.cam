<?php
session_start();
include('../../includes/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$documentId = (int)$data['document_id'];

try {
    $db->beginTransaction();
    
    // Mise à jour du statut du document
    $stmt = $db->prepare("UPDATE documents SET StatutValidation = 'Approuve', Utilisateurid = :officier, DateTelechargement = NOW() WHERE DocumentID = :id");
    $stmt->execute([
        'officier' => $_SESSION['user_id'],
        'id' => $documentId
    ]);
    
    // Ajout dans le journal d'activités
    $stmt = $db->prepare("INSERT INTO journalactivites (UtilisateurID, TypeActivite, Description, AdresseIP) VALUES (:user, 'Validation_Document', :desc, :ip)");
    $stmt->execute([
        'user' => $_SESSION['user_id'],
        'desc' => "Validation du document #$documentId",
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $db->commit();
    echo json_encode(['success' => true]);
    
} catch(Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
