<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: ../pages/login.php');
    exit();
}
checkRole(4);
include('../includes/config.php');

$stats = [
    'nouvelles' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Soumise' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'encours' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'EnCours' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'approuvees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Approuvee' AND TypeDemande = 'NATIONALITE'")->fetchColumn(),
    'rejetees' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Rejetee' AND TypeDemande = 'NATIONALITE'")->fetchColumn()
];

// Dernières demandes
$query = "SELECT d.*, dn.Nom, dn.Prenom, dn.DateNaissance, dn.LieuNaissance
          FROM demandes d
          JOIN demande_nationalite_details dn ON d.DemandeID = dn.DemandeID
          WHERE d.TypeDemande = 'NATIONALITE'
          ORDER BY d.DateSoumission DESC 
          LIMIT 8";
$demandes = $db->query($query)->fetchAll();

include('../includes/header.php');
include('../includes/navbar.php');
?>
  <div class="dashboard-container">
      <div class="container-fluid">
          <div class="row">
              <!-- Sidebar -->
              <?php include('../includes/president_sidebar.php'); ?>

              <!-- Contenu principal avec le bon offset -->
              <main class="col-md-9 col-lg-10 ms-auto main-content">
                  <!-- En-tête -->
                  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                      <div>
                          <h1 class="h2 mb-0">Tableau de bord</h1>
                          <p class="text-muted">Bienvenue, M. le Président</p>
                      </div>
                      <div class="btn-toolbar mb-2 mb-md-0">
                          <div class="btn-group me-2">
                              <button type="button" class="btn btn-sm btn-outline-secondary">
                                  <i class="bi bi-download me-1"></i>Exporter
                              </button>
                              <button type="button" class="btn btn-sm btn-outline-secondary">
                                  <i class="bi bi-printer me-1"></i>Imprimer
                              </button>
                          </div>
                      </div>
                  </div>

                  <!-- Statistiques -->
                  <div class="row g-4 mb-4">
                      <div class="col-md-3">
                          <div class="card stat-card h-100 border-0 shadow-sm">
                              <div class="card-body">
                                  <div class="d-flex align-items-center">
                                      <div class="stat-icon bg-primary-subtle rounded-3 p-3 me-3">
                                          <i class="bi bi-file-earmark-plus h3 mb-0 text-primary"></i>
                                      </div>
                                      <div>
                                          <h6 class="card-subtitle mb-1">Nouvelles demandes</h6>
                                          <h2 class="card-title mb-0"><?php echo $stats['nouvelles']; ?></h2>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <div class="col-md-3">
                          <div class="card stat-card h-100 border-0 shadow-sm">
                              <div class="card-body">
                                  <div class="d-flex align-items-center">
                                      <div class="stat-icon bg-warning-subtle rounded-3 p-3 me-3">
                                          <i class="bi bi-clock-history h3 mb-0 text-warning"></i>
                                      </div>
                                      <div>
                                          <h6 class="card-subtitle mb-1">En cours</h6>
                                          <h2 class="card-title mb-0"><?php echo $stats['encours']; ?></h2>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <div class="col-md-3">
                          <div class="card stat-card h-100 border-0 shadow-sm">
                              <div class="card-body">
                                  <div class="d-flex align-items-center">
                                      <div class="stat-icon bg-success-subtle rounded-3 p-3 me-3">
                                          <i class="bi bi-check-circle h3 mb-0 text-success"></i>
                                      </div>
                                      <div>
                                          <h6 class="card-subtitle mb-1">Approuvées</h6>
                                          <h2 class="card-title mb-0"><?php echo $stats['approuvees']; ?></h2>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <div class="col-md-3">
                          <div class="card stat-card h-100 border-0 shadow-sm">
                              <div class="card-body">
                                  <div class="d-flex align-items-center">
                                      <div class="stat-icon bg-danger-subtle rounded-3 p-3 me-3">
                                          <i class="bi bi-x-circle h3 mb-0 text-danger"></i>
                                      </div>
                                      <div>
                                          <h6 class="card-subtitle mb-1">Rejetées</h6>
                                          <h2 class="card-title mb-0"><?php echo $stats['rejetees']; ?></h2>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <!-- Dernières demandes -->
                  <div class="card border-0 shadow-sm">
                      <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                          <h5 class="card-title mb-0">Dernières demandes de nationalité</h5>
                          <a href="demandes_nationalite.php" class="btn btn-primary btn-sm">
                              <i class="bi bi-list-ul me-1"></i>Voir toutes
                          </a>
                      </div>
                      <div class="card-body">
                          <div class="table-responsive">
                              <table class="table table-hover align-middle">
                                  <thead class="table-light">
                                      <tr>
                                          <th>ID</th>
                                          <th>Demandeur</th>
                                          <th>Date de naissance</th>
                                          <th>Date demande</th>
                                          <th>Statut</th>
                                          <th>Actions</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php foreach($demandes as $demande): ?>
                                      <tr>
                                          <td>
                                              <span class="badge bg-light text-dark">
                                                  #<?php echo str_pad($demande['DemandeID'], 6, '0', STR_PAD_LEFT); ?>
                                              </span>
                                          </td>
                                          <td>
                                              <div class="d-flex align-items-center">
                                                  <div class="avatar-sm me-2 bg-primary-subtle rounded-circle">
                                                      <?php echo strtoupper(substr($demande['Nom'], 0, 1) . substr($demande['Prenom'], 0, 1)); ?>
                                                  </div>
                                                  <div>
                                                      <div class="fw-medium"><?php echo $demande['Nom'] . ' ' . $demande['Prenom']; ?></div>
                                                      <small class="text-muted"><?php echo $demande['LieuNaissance']; ?></small>
                                                  </div>
                                              </div>
                                          </td>
                                          <td><?php echo date('d/m/Y', strtotime($demande['DateNaissance'])); ?></td>
                                          <td><?php echo date('d/m/Y H:i', strtotime($demande['DateSoumission'])); ?></td>
                                          <td>
                                              <span class="badge bg-<?php 
                                                  echo match($demande['Statut']) {
                                                      'Soumise' => 'secondary',
                                                      'EnCours' => 'primary',
                                                      'Approuvee' => 'success',
                                                      'Rejetee' => 'danger',
                                                      default => 'secondary'
                                                  };
                                              ?> rounded-pill">
                                                  <?php echo $demande['Statut']; ?>
                                              </span>
                                          </td>
                                          <td>
                                              <a href="traiter_demande.php?id=<?php echo $demande['DemandeID']; ?>" 
                                               class="btn btn-sm btn-outline-primary rounded-pill">
                                                  <i class="bi bi-eye me-1"></i>Traiter
                                              </a>
                                          </td>
                                      </tr>
                                      <?php endforeach; ?>
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>
              </main>
          </div>
      </div>
  </div>

  <style>
  .dashboard-container {
      background-color: #f8f9fa;
      min-height: 100vh;
      padding: 20px 0;
  }

  .main-content {
      padding-left: 30px;
      padding-right: 30px;
  }

  /* Ajustement responsive */
  @media (max-width: 768px) {
      .main-content {
          padding-left: 15px;
          padding-right: 15px;
          width: 100%;
      }
  }

  .stat-card {
      transition: transform 0.2s;
      border-radius: 15px;
  }

  .stat-card:hover {
      transform: translateY(-5px);
  }

  .stat-icon {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
  }

  .avatar-sm {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
  }

  .table {
      font-size: 0.9rem;
  }

  .table th {
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
  }

  .rounded-pill {
      padding: 0.5em 1em;
  }
  </style>
<?php include('../includes/footer.php'); ?>
