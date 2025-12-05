<?php
// Centralized session initialization
// Use a stable session name to avoid mismatches when some pages set custom names.
if (session_status() === PHP_SESSION_NONE) {
    $defaultSessionName = 'cni_session';

    // If a ROLE_TYPE cookie exists, keep backward compatibility by using a role-specific session name.
    // Otherwise use a single default session name.
    if (!empty($_COOKIE['ROLE_TYPE'])) {
        $role = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_COOKIE['ROLE_TYPE']);
        session_name('cni_' . $role);
    } else {
        session_name($defaultSessionName);
    }

    session_start();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=cni', 'hedric', 'Hedric&2002');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
