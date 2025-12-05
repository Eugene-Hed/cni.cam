<?php
include('../includes/config.php');

if(isset($_POST['departement_id'])) {
    $stmt = $db->prepare("SELECT * FROM villes WHERE DepartementID = ? ORDER BY NomVille");
    $stmt->execute([$_POST['departement_id']]);
    $villes = $stmt->fetchAll();
    
    echo '<option value="">Sélectionner une ville...</option>';
    foreach($villes as $ville) {
        echo '<option value="'.$ville['VilleID'].'">'.$ville['NomVille'].'</option>';
    }
}
