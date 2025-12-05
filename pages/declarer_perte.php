<?php
// Session is initialized centrally in includes/config.php
include('../includes/config.php');
include('../includes/auth.php');

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/mes_documents.php');
    exit();
}

// Récupération et validation des données du formulaire
$carteId = isset($_POST['carte_id']) ? intval($_POST['carte_id']) : 0;
$datePerte = isset($_POST['date_perte']) ? $_POST['date_perte'] : '';
$circonstances = isset($_POST['circonstances']) ? trim($_POST['circonstances']) : '';

// Validation des données
if (empty($carteId) || empty($datePerte) || empty($circonstances)) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header('Location: /pages/mes_documents.php');
    exit();
}

// Vérification que la date n'est pas dans le futur
if (strtotime($datePerte) > time()) {
    $_SESSION['error_message'] = "La date de perte ne peut pas être dans le futur.";
    header('Location: /pages/mes_documents.php');
    exit();
}

try {
    // Début de la transaction
    $db->beginTransaction();
    
    // 1. Vérifier que la carte appartient bien à l'utilisateur et qu'elle est active
    $query = "
        SELECT c.CarteID, c.NumeroCarteIdentite, c.Statut, d.UtilisateurID 
        FROM cartesidentite c 
        JOIN demandes d ON c.DemandeID = d.DemandeID 
        WHERE c.CarteID = :carteId AND d.UtilisateurID = :userId
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'carteId' => $carteId,
        'userId' => $userId
    ]);
    
    $carte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$carte) {
        throw new Exception("Carte d'identité non trouvée ou vous n'êtes pas autorisé à effectuer cette action.");
    }
    
    if ($carte['Statut'] !== 'Active') {
        throw new Exception("Cette carte n'est pas active et ne peut pas être déclarée perdue.");
    }
    
    // 2. Mettre à jour le statut de la carte
    $updateQuery = "
        UPDATE cartesidentite 
        SET Statut = 'Perdue', DateMiseAJour = NOW() 
        WHERE CarteID = :carteId
    ";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute(['carteId' => $carteId]);
    
    // 3. Créer une entrée dans la table des déclarations de perte
    $insertQuery = "
        INSERT INTO declarationspertevol (
            CarteID, 
            DateDeclaration, 
            DatePerte, 
            Circonstances, 
            StatutDeclaration
        ) VALUES (
            :carteId, 
            NOW(), 
            :datePerte, 
            :circonstances, 
            'Soumise'
        )
    ";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->execute([
        'carteId' => $carteId,
        'datePerte' => $datePerte,
        'circonstances' => $circonstances
    ]);
    
    // 4. Créer une nouvelle demande de CNI pour remplacement
    $referenceNumber = 'CNI-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    $insertDemandeQuery = "
        INSERT INTO demandes (
            UtilisateurID, 
            TypeDemande, 
            NumeroReference, 
            Statut, 
            DateSoumission, 
            MotifDemande
        ) VALUES (
            :userId, 
            'CNI', 
            :referenceNumber, 
            'Soumise', 
            NOW(), 
            'Remplacement suite à perte/vol'
        )
    ";
    $insertDemandeStmt = $db->prepare($insertDemandeQuery);
    $insertDemandeStmt->execute([
        'userId' => $userId,
        'referenceNumber' => $referenceNumber
    ]);
    
    $nouvelleDemandeId = $db->lastInsertId();
    
    // 5. Copier les informations de la demande précédente pour la nouvelle demande
    $copyInfoQuery = "
        INSERT INTO informationspersonnelles (
            DemandeID, 
            Nom, 
            Prenom, 
            DateNaissance, 
            LieuNaissance, 
            Sexe, 
            Nationalite, 
            Adresse, 
            Telephone, 
            Email, 
            Profession, 
            SituationFamiliale, 
            NomPere, 
            NomMere
        )
        SELECT 
            :nouvelleDemandeId, 
            Nom, 
            Prenom, 
            DateNaissance, 
            LieuNaissance, 
            Sexe, 
            Nationalite, 
            Adresse, 
            Telephone, 
            Email, 
            Profession, 
            SituationFamiliale, 
            NomPere, 
            NomMere
        FROM informationspersonnelles ip
        JOIN demandes d ON ip.DemandeID = d.DemandeID
        JOIN cartesidentite c ON c.DemandeID = d.DemandeID
        WHERE c.CarteID = :carteId
    ";
    $copyInfoStmt = $db->prepare($copyInfoQuery);
    $copyInfoStmt->execute([
        'nouvelleDemandeId' => $nouvelleDemandeId,
        'carteId' => $carteId
    ]);
    
    // 6. Ajouter une note dans la table des notifications
    $notificationQuery = "
        INSERT INTO notifications (
            UtilisateurID, 
            Type, 
            Message, 
            DateCreation, 
            Statut, 
            Lien
        ) VALUES (
            :userId, 
            'Alerte', 
            :message, 
            NOW(), 
            'NonLue', 
            :lien
        )
    ";
    $notificationStmt = $db->prepare($notificationQuery);
    $notificationStmt->execute([
        'userId' => $userId,
        'message' => "Votre déclaration de perte/vol pour la CNI n°" . $carte['NumeroCarteIdentite'] . " a été enregistrée. Une nouvelle demande de CNI a été automatiquement créée.",
        'lien' => "/pages/details_demande.php?id=" . $nouvelleDemandeId
    ]);
    
    // Valider la transaction
    $db->commit();
    
    // Définir le message de succès
    $_SESSION['success_message'] = "Votre déclaration de perte/vol a été enregistrée avec succès. Une nouvelle demande de CNI a été automatiquement créée.";
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $db->rollBack();
    $_SESSION['error_message'] = "Erreur lors de la déclaration de perte : " . $e->getMessage();
}

// Redirection vers la page des documents
header('Location: /pages/mes_documents.php');
exit();
?>
