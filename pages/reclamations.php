<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /cni.cam/pages/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Traitement de la nouvelle réclamation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $demandeId = $_POST['demande_id'];
    $type = $_POST['type'];
    $description = $_POST['description'];

    $query = "INSERT INTO reclamations (UtilisateurID, DemandeID, TypeReclamation, Description, Statut) 
              VALUES (:userId, :demandeId, :type, :description, 'Ouverte')";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        'userId' => $userId,
        'demandeId' => $demandeId,
        'type' => $type,
        'description' => $description
    ]);

    $_SESSION['success'] = "Votre réclamation a été enregistrée avec succès";
    header('Location: reclamations.php');
    exit();
}

// Récupération des demandes de l'utilisateur pour le select
$query = "SELECT DemandeID, TypeDemande, DateSoumission FROM demandes WHERE UtilisateurID = :userId";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$demandes = $stmt->fetchAll();

// Récupération des réclamations
$query = "SELECT r.*, d.TypeDemande, d.DateSoumission 
          FROM reclamations r 
          LEFT JOIN demandes d ON r.DemandeID = d.DemandeID 
          WHERE r.UtilisateurID = :userId 
          ORDER BY r.DateCreation DESC";
$stmt = $db->prepare($query);
$stmt->execute(['userId' => $userId]);
$reclamations = $stmt->fetchAll();

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>
<style>
.dashboard-container {
    background-color: #f8f9fa;
    padding: 30px 0;
    min-height: calc(100vh - 180px);
}
.stats-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.table-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}
.status-badge {
    padding: 8px 12px;
    border-radius: 50px;
    font-weight: 500;
}
.btn-action {
    padding: 8px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.btn-action:hover {
    transform: translateY(-2px);
}
.pagination {
    margin-bottom: 0;
}
.page-link {
    padding: 10px 15px;
    border-radius: 8px;
    margin: 0 3px;
}
.demande-type-icon {
    font-size: 1.5rem;
    margin-right: 10px;
}
.empty-state {
    text-align: center;
    padding: 50px 20px;
}
.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}
</style>
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2">
                
            </div>

        <!-- Contenu principal -->
        <div class="col-md-9">
            <!-- Formulaire de nouvelle réclamation -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Nouvelle réclamation</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Demande concernée</label>
                                <select name="demande_id" class="form-select" required>
                                    <option value="">Sélectionner une demande</option>
                                    <?php foreach($demandes as $demande): ?>
                                        <option value="<?php echo $demande['DemandeID']; ?>">
                                            #<?php echo str_pad($demande['DemandeID'], 6, '0', STR_PAD_LEFT); ?> - 
                                            <?php echo $demande['TypeDemande']; ?> 
                                            (<?php echo date('d/m/Y', strtotime($demande['DateSoumission'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de réclamation</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="Delai">Délai de traitement</option>
                                    <option value="Document">Document manquant</option>
                                    <option value="Information">Demande d'information</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Envoyer la réclamation
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des réclamations -->
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Historique des réclamations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Demande</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reclamations as $reclamation): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reclamation['DateCreation'])); ?></td>
                                    <td>
                                        #<?php echo str_pad($reclamation['DemandeID'], 6, '0', STR_PAD_LEFT); ?><br>
                                        <small class="text-muted"><?php echo $reclamation['TypeDemande']; ?></small>
                                    </td>
                                    <td><?php echo $reclamation['TypeReclamation']; ?></td>
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
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(empty($reclamations)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots h1 text-muted"></i>
                        <p class="mt-3 text-muted">Aucune réclamation pour le moment</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
