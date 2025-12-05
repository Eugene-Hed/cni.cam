<?php
// Statistiques générales
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
    'active_users' => $db->query("SELECT COUNT(*) FROM utilisateurs WHERE IsActive = 1")->fetchColumn(),
    'total_requests' => $db->query("SELECT COUNT(*) FROM demandes")->fetchColumn(),
    'pending_requests' => $db->query("SELECT COUNT(*) FROM demandes WHERE Statut = 'Soumise'")->fetchColumn()
];

// Dernières demandes
$recent_requests = $db->query("
    SELECT d.*, u.Nom, u.Prenom 
    FROM demandes d
    JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
    ORDER BY d.DateSoumission DESC 
    LIMIT 5
")->fetchAll();

// Historique des activités
$activities = $db->query("
    SELECT h.*, d.TypeDemande, u.Nom, u.Prenom
    FROM historique_demandes h
    JOIN demandes d ON h.DemandeID = d.DemandeID
    LEFT JOIN utilisateurs u ON h.ModifiePar = u.UtilisateurID
    ORDER BY h.DateModification DESC
    LIMIT 10
")->fetchAll();
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Utilisateurs Total</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['total_users'], 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-person-check fs-4 text-success"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Utilisateurs Actifs</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['active_users'], 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-text fs-4 text-info"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Demandes Total</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['total_requests'], 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-clock-history fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Demandes en Attente</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['pending_requests'], 0, ',', ' '); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Dernières Demandes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Demandeur</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_requests as $request): ?>
                                <tr>
                                    <td>#<?php echo str_pad($request['DemandeID'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($request['Nom'] . ' ' . $request['Prenom']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($request['TypeDemande']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($request['Statut']) {
                                                'Soumise' => 'secondary',
                                                'EnCours' => 'primary',
                                                'Approuvee' => 'success',
                                                'Rejetee' => 'danger',
                                                'Terminee' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($request['Statut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($request['DateSoumission'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Historique des Activités</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Demande</th>
                                <th>Modifié par</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($activity['DateModification'])); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($activity['AncienStatut']); ?> → 
                                            <?php echo htmlspecialchars($activity['NouveauStatut']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['TypeDemande']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['Nom'] . ' ' . $activity['Prenom']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
