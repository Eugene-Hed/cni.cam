<?php
include('../includes/config.php');

// Vérification si l'utilisateur est connecté et est un officier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Location: /cni.cam/pages/login.php');
    exit();
}

// Récupération des réclamations liées aux CNI
$query = "SELECT r.*, d.TypeDemande, d.DateSoumission, u.Nom, u.Prenom
          FROM reclamations r
          JOIN demandes d ON r.DemandeID = d.DemandeID
          JOIN utilisateurs u ON r.UtilisateurID = u.UtilisateurID
          WHERE d.TypeDemande = 'CNI'
          ORDER BY r.DateCreation DESC";
$reclamations = $db->query($query)->fetchAll();

// Traitement du changement de statut
if(isset($_POST['update_status'])) {
    $reclamationId = $_POST['reclamation_id'];
    $newStatus = $_POST['new_status'];
    
    $query = "UPDATE reclamations SET Statut = :status WHERE ReclamationID = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'status' => $newStatus,
        'id' => $reclamationId
    ]);
    
    header('Location: reclamations.php');
    exit();
}

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar moderne -->
        <?php include('includes/sidebar.php'); ?>

        <!-- Contenu principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestion des réclamations CNI</h1>
            </div>

            <!-- Liste des réclamations -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Demandeur</th>
                                    <th>N° Demande</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reclamations as $reclamation): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reclamation['DateCreation'])); ?></td>
                                    <td><?php echo $reclamation['Nom'] . ' ' . $reclamation['Prenom']; ?></td>
                                    <td>#<?php echo str_pad($reclamation['DemandeID'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $reclamation['TypeReclamation']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $reclamation['Description']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($reclamation['Statut']) {
                                                'Ouverte' => 'warning',
                                                'EnCours' => 'primary',
                                                'Fermee' => 'success',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo $reclamation['Statut']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                Changer statut
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" class="dropdown-item">
                                                        <input type="hidden" name="reclamation_id" 
                                                               value="<?php echo $reclamation['ReclamationID']; ?>">
                                                        <input type="hidden" name="new_status" value="EnCours">
                                                        <button type="submit" name="update_status" class="btn btn-link text-primary">
                                                            <i class="bi bi-arrow-clockwise me-2"></i>Marquer en cours
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="dropdown-item">
                                                        <input type="hidden" name="reclamation_id" 
                                                               value="<?php echo $reclamation['ReclamationID']; ?>">
                                                        <input type="hidden" name="new_status" value="Fermee">
                                                        <button type="submit" name="update_status" class="btn btn-link text-success">
                                                            <i class="bi bi-check-circle me-2"></i>Marquer comme traitée
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(empty($reclamations)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots h1 text-muted"></i>
                        <p class="mt-3 text-muted">Aucune réclamation à traiter</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
