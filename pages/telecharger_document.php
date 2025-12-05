<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Vérifier le type de document demandé
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$docId = isset($_GET['doc']) ? (int)$_GET['doc'] : 0;

// Si c'est un document spécifique (pièce jointe)
if ($docId > 0) {
    $query = "SELECT d.*, dem.UtilisateurID 
              FROM documents d 
              JOIN demandes dem ON d.DemandeID = dem.DemandeID 
              WHERE d.DocumentID = :docId AND dem.UtilisateurID = :userId";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'docId' => $docId,
        'userId' => $userId
    ]);
    $document = $stmt->fetch();
    
    if (!$document) {
        $_SESSION['error_message'] = "Le document demandé n'existe pas ou vous n'avez pas les droits pour y accéder.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Vérifier si le fichier existe
    if (!file_exists($document['CheminFichier'])) {
        $_SESSION['error_message'] = "Le fichier demandé n'existe pas sur le serveur.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Déterminer le type MIME
    $fileInfo = pathinfo($document['CheminFichier']);
    $extension = strtolower($fileInfo['extension']);
    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    $contentType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
    
    // Envoyer le fichier
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($document['CheminFichier']) . '"');
    header('Content-Length: ' . filesize($document['CheminFichier']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($document['CheminFichier']);
    exit();
}

// Sinon, c'est une CNI ou un certificat de nationalité
if ($type == 'cni') {
    // Récupérer les informations de la CNI
    $query = "SELECT c.*, d.UtilisateurID 
              FROM cartesidentite c 
              JOIN demandes d ON c.DemandeID = d.DemandeID 
              WHERE c.CarteID = :id AND d.UtilisateurID = :userId";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'id' => $id,
        'userId' => $userId
    ]);
    $cni = $stmt->fetch();
    
    if (!$cni) {
        $_SESSION['error_message'] = "La carte d'identité demandée n'existe pas ou vous n'avez pas les droits pour y accéder.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Vérifier si le fichier existe
    if (!file_exists($cni['CheminFichier'])) {
        $_SESSION['error_message'] = "Le fichier de la CNI n'existe pas sur le serveur.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Envoyer le fichier
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="CNI_' . $cni['NumeroCarteIdentite'] . '.pdf"');
    header('Content-Length: ' . filesize($cni['CheminFichier']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($cni['CheminFichier']);
    exit();
} elseif ($type == 'certificat') {
    // Récupérer les informations du certificat
    $query = "SELECT c.*, d.UtilisateurID 
              FROM certificatsnationalite c 
              JOIN demandes d ON c.DemandeID = d.DemandeID 
              WHERE c.CertificatID = :id AND d.UtilisateurID = :userId";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'id' => $id,
        'userId' => $userId
    ]);
    $certificat = $stmt->fetch();
    
    if (!$certificat) {
        $_SESSION['error_message'] = "Le certificat de nationalité demandé n'existe pas ou vous n'avez pas les droits pour y accéder.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Vérifier si le fichier existe
    if (!file_exists($certificat['CheminPDF'])) {
        $_SESSION['error_message'] = "Le fichier du certificat n'existe pas sur le serveur.";
        header('Location: mes_documents.php');
        exit();
    }
    
    // Envoyer le fichier
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Certificat_Nationalite_' . $certificat['NumeroCertificat'] . '.pdf"');
    header('Content-Length: ' . filesize($certificat['CheminPDF']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    readfile($certificat['CheminPDF']);
    exit();
} else {
    // Si aucun type valide n'est spécifié
    $_SESSION['error_message'] = "Type de document non spécifié ou invalide.";
    header('Location: mes_documents.php');
    exit();
}
