<?php
// Démarrage de session avec vérification du nom de session
$session_names = ['admin_', 'citizen_', 'officer_', 'president_'];
foreach ($session_names as $name) {
    if (isset($_COOKIE[$name.'*'])) {
        session_name($name.'*');
        break;
    }
}
//session_start();

function checkRole($requiredRole) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != $requiredRole) {
        header('Location: /pages/login.php');
        exit();
    }
}
