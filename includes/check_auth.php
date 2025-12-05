<?php

// Vérification du rôle selon le répertoire
$current_path = $_SERVER['PHP_SELF'];
if(strpos($current_path, '/admin/') !== false && $_SESSION['role'] != 1) {
    header('Location: /cni.cam/pages/login.php');
    exit();
} elseif(strpos($current_path, '/citoyen/') !== false && $_SESSION['role'] != 2) {
    header('Location: /cni.cam/pages/login.php');
    exit();
} elseif(strpos($current_path, '/officier/') !== false && $_SESSION['role'] != 3) {
    header('Location: /cni.cam/pages/login.php');
    exit();
} elseif(strpos($current_path, '/president/') !== false && $_SESSION['role'] != 4) {
    header('Location: /pages/login.php');
    exit();
}
?>
