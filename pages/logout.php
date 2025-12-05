<?php
// Session is initialized centrally in includes/config.php
include('../includes/config.php');

// Destroy session and associated cookie
if (session_status() === PHP_SESSION_ACTIVE) {
    // Unset all session variables
    $_SESSION = array();

    // If a session cookie exists, delete it
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

// Remove ROLE_TYPE cookie if present
setcookie('ROLE_TYPE', '', time() - 3600, '/');

header('Location: ../index.php');
exit();
