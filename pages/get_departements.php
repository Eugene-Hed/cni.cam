<?php
include('../includes/config.php');

if(isset($_POST['region_id'])) {
    $stmt = $db->prepare("SELECT * FROM departements WHERE RegionID = ? ORDER BY NomDepartement");
    $stmt->execute([$_POST['region_id']]);
    $departements = $stmt->fetchAll();
    
    echo '<option value="">Sélectionner un département...</option>';
    foreach($departements as $departement) {
        echo '<option value="'.$departement['DepartementID'].'">'.$departement['NomDepartement'].'</option>';
    }
}
