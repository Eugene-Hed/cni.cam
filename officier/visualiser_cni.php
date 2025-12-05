<?php
session_start();
include('../includes/config.php');

// Vérification de l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Location: /pages/login.php');
    exit();
}

$numeroCNI = isset($_GET['id']) ? $_GET['id'] : null;

if (!$numeroCNI) {
    header('Location: demandes_cni.php');
    exit();
}

// Récupération des informations de la CNI
$query = "SELECT c.*, d.*, dc.*, u.Email, u.NumeroTelephone
          FROM cartesidentite c
          JOIN demandes d ON c.DemandeID = d.DemandeID
          JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          WHERE c.NumeroCarteIdentite = :numero";
$stmt = $db->prepare($query);
$stmt->execute(['numero' => $numeroCNI]);
$cni = $stmt->fetch();

include('../includes/header.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/sidebar.php'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Visualisation CNI</h1>
                <div class="btn-toolbar">
                    <a href="demandes_cni.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                    <a href="<?php echo $cni['CheminFichier']; ?>" class="btn btn-primary" target="_blank">
                        <i class="bi bi-file-pdf"></i> Ouvrir le PDF
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Informations de la CNI</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Numéro CNI</th>
                                    <td><?php echo $cni['NumeroCarteIdentite']; ?></td>
                                </tr>
                                <tr>
                                    <th>Date d'émission</th>
                                    <td><?php echo date('d/m/Y', strtotime($cni['DateEmission'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Date d'expiration</th>
                                    <td><?php echo date('d/m/Y', strtotime($cni['DateExpiration'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td>
                                        <span class="badge bg-success"><?php echo $cni['Statut']; ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Titulaire</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Nom complet</th>
                                    <td><?php echo $cni['Nom'] . ' ' . $cni['Prenom']; ?></td>
                                </tr>
                                <tr>
                                    <th>Date de naissance</th>
                                    <td><?php echo date('d/m/Y', strtotime($cni['DateNaissance'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Lieu de naissance</th>
                                    <td><?php echo $cni['LieuNaissance']; ?></td>
                                </tr>
                                <tr>
                                    <th>Contact</th>
                                    <td>
                                        <i class="bi bi-envelope"></i> <?php echo $cni['Email']; ?><br>
                                        <i class="bi bi-phone"></i> <?php echo $cni['NumeroTelephone']; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Aperçu de la CNI</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 text-center mb-3">
                                    <h6>Recto</h6>
                                    <iframe src="<?php echo $cni['CheminFichier']; ?>#page=1" 
                                            width="100%" height="300px" frameborder="0"></iframe>
                                </div>
                                <div class="col-md-6 text-center">
                                    <h6>Verso</h6>
                                    <iframe src="<?php echo $cni['CheminFichier']; ?>#page=2" 
                                            width="100%" height="300px" frameborder="0"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">QR Code</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo $cni['CodeQR']; ?>" 
                                 alt="QR Code" class="img-fluid" 
                                 style="max-width: 200px;">
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
