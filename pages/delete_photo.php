<?php
include('../includes/config.php');
include('../includes/check_auth.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'];

    $query = "SELECT PhotoUtilisateur FROM utilisateurs WHERE UtilisateurID = :id";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if ($user && !empty($user['PhotoUtilisateur']) && file_exists($user['PhotoUtilisateur'])) {
        unlink($user['PhotoUtilisateur']);
    }

    $query = "UPDATE utilisateurs SET PhotoUtilisateur = NULL WHERE UtilisateurID = :id";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $userId]);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false]);