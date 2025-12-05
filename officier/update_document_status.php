<?php
session_start();
include('../includes/config.php');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Vérification des paramètres
if (!isset($_POST['document_id']) || !isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

$documentId = (int)$_POST['document_id'];
$action = $_POST['action'];

// Validation de l'action
if ($action !== 'approve' && $action !== 'reject') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Action non valide']);
    exit();
}

try {
    $db->beginTransaction();
    
    // Récupération des informations du document et de la demande associée
    $query = "SELECT d.DocumentID, d.DemandeID, d.TypeDocument, dm.Statut as DemandeStatut 
              FROM documents d
              JOIN demandes dm ON d.DemandeID = dm.DemandeID
              WHERE d.DocumentID = :documentId";
    $stmt = $db->prepare($query);
    $stmt->execute(['documentId' => $documentId]);
    $document = $stmt->fetch();
    
    if (!$document) {
        throw new Exception('Document non trouvé');
    }
    
    // Vérification que la demande est en cours de traitement
    if ($document['DemandeStatut'] !== 'EnCours') {
        throw new Exception('La demande n\'est pas en cours de traitement');
    }
    
    // Mise à jour du statut du document
    $newStatus = ($action === 'approve') ? 'Approuve' : 'Rejete';
    
    $query = "UPDATE documents 
              SET StatutValidation = :statut, DateValidation = NOW(), ValidePar = :officier 
              WHERE DocumentID = :documentId";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'statut' => $newStatus,
        'officier' => $_SESSION['user_id'],
        'documentId' => $documentId
    ]);
    
    // Ajout dans le journal d'activité
    $query = "INSERT INTO journalactivites 
              (UtilisateurID, TypeActivite, Description, AdresseIP) 
              VALUES (:userId, :type, :description, :ip)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'userId' => $_SESSION['user_id'],
        'type' => 'Validation_Document',
        'description' => "Document #$documentId " . ($action === 'approve' ? 'approuvé' : 'rejeté'),
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    // Vérification si tous les documents sont validés ou si au moins un est rejeté
    $query = "SELECT COUNT(*) as total, 
              SUM(CASE WHEN StatutValidation = 'EnAttente' THEN 1 ELSE 0 END) as pending,
              SUM(CASE WHEN StatutValidation = 'Rejete' THEN 1 ELSE 0 END) as rejected
              FROM documents 
              WHERE DemandeID = :demandeId";
    $stmt = $db->prepare($query);
    $stmt->execute(['demandeId' => $document['DemandeID']]);
    $docStats = $stmt->fetch();
    
    // Si au moins un document est rejeté, on peut suggérer de rejeter la demande
    $message = '';
    if ($docStats['rejected'] > 0) {
        $message = 'Document ' . ($action === 'approve' ? 'approuvé' : 'rejeté') . ' avec succès. Certains documents sont rejetés, vous pouvez rejeter la demande.';
    } 
    // Si tous les documents sont validés, on peut suggérer d'approuver la demande
    elseif ($docStats['pending'] == 0) {
        $message = 'Document ' . ($action === 'approve' ? 'approuvé' : 'rejeté') . ' avec succès. Tous les documents sont validés, vous pouvez approuver la demande.';
    }
    else {
        $message = 'Document ' . ($action === 'approve' ? 'approuvé' : 'rejeté') . ' avec succès.';
    }
    
    $db->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'status' => $newStatus,
        'allValidated' => ($docStats['pending'] == 0 && $docStats['rejected'] == 0),
        'hasRejected' => ($docStats['rejected'] > 0)
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
