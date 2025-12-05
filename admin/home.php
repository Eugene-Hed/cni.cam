<?php
// Requêtes SQL pour récupérer les données
$paiementsData = $db->query('SELECT MONTH(DatePaiement) AS mois, SUM(Montant) AS total FROM paiements GROUP BY mois ORDER BY mois')->fetchAll(PDO::FETCH_ASSOC);
$utilisateursData = $db->query('SELECT MONTH(DateCreation) AS mois, COUNT(UtilisateurID) AS total FROM utilisateurs GROUP BY mois ORDER BY mois')->fetchAll(PDO::FETCH_ASSOC);
$demandesData = $db->query('SELECT MONTH(DateSoumission) AS mois, COUNT(DemandeID) AS total FROM demandes GROUP BY mois ORDER BY mois')->fetchAll(PDO::FETCH_ASSOC);
$rendezVousData = $db->query('SELECT MONTH(DateRendezVous) AS mois, COUNT(RendezVousID) AS total FROM rendezvous GROUP BY mois ORDER BY mois')->fetchAll(PDO::FETCH_ASSOC);

$activites = $db->query('SELECT DateHeure, UtilisateurID, TypeActivite FROM journalactivites ORDER BY DateHeure DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);

$nbRendezVous = $db->query('SELECT COUNT(RendezVousID) AS total FROM rendezvous')->fetchColumn();
$nbDemandes = $db->query('SELECT COUNT(DemandeID) AS total FROM demandes')->fetchColumn();
$nbUtilisateurs = $db->query('SELECT COUNT(UtilisateurID) AS total FROM utilisateurs')->fetchColumn();
$montant = $db->query('SELECT SUM(Montant) AS total FROM paiements')->fetchColumn();

// Fonction pour préparer les données pour Chart.js
function prepareChartData($data) {
    $months = range(1, 12);
    $prepared = array_fill_keys($months, 0);
    foreach ($data as $row) {
        $prepared[$row['mois']] = (float)$row['total'];
    }
    return array_values($prepared);
}

$paiementsChartData = prepareChartData($paiementsData);
$utilisateursChartData = prepareChartData($utilisateursData);
$demandesChartData = prepareChartData($demandesData);
$rendezVousChartData = prepareChartData($rendezVousData);
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-calendar-check text-primary fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Rendez-vous</h6>
                        <h3 class="mb-0"><?php echo number_format($nbRendezVous, 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-text text-success fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Demandes</h6>
                        <h3 class="mb-0"><?php echo number_format($nbDemandes, 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people text-info fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Utilisateurs</h6>
                        <h3 class="mb-0"><?php echo number_format($nbUtilisateurs, 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-exchange text-warning fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Paiements</h6>
                        <h3 class="mb-0"><?php echo number_format($montant, 0, ',', ' '); ?> FCFA</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Statistiques mensuelles</h5>
            </div>
            <div class="card-body">
                <canvas id="statsChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Activités récentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activites as $activite): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($activite['DateHeure'])); ?></td>
                                    <td><?php echo $activite['UtilisateurID']; ?></td>
                                    <td><?php echo $activite['TypeActivite']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [
            {
                label: 'Paiements (FCFA)',
                data: <?php echo json_encode($paiementsChartData); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
            },
            {
                label: 'Utilisateurs',
                data: <?php echo json_encode($utilisateursChartData); ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4
            },
            {
                label: 'Demandes',
                data: <?php echo json_encode($demandesChartData); ?>,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4
            },
            {
                label: 'Rendez-vous',
                data: <?php echo json_encode($rendezVousChartData); ?>,
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
