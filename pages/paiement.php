<?php
include('../includes/config.php');
include('../includes/auth.php');
// Session is initialized centrally in includes/config.php

// Vérification de la connexion
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification des paramètres de session nécessaires
if (!isset($_SESSION['demande_id']) || !isset($_SESSION['montant']) || !isset($_SESSION['reference'])) {
    header('Location: /pages/dashboard.php');
    exit();
}

$userId = $_SESSION['user_id'];
$demandeId = $_SESSION['demande_id'];
$montant = $_SESSION['montant'];
$reference = $_SESSION['reference'];

// Récupération des informations de la demande
$query = "SELECT d.*, u.Nom, u.Prenom, u.Email, u.NumeroTelephone 
          FROM demandes d 
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID 
          WHERE d.DemandeID = :demandeId AND d.UtilisateurID = :userId";
$stmt = $db->prepare($query);
$stmt->execute([
    'demandeId' => $demandeId,
    'userId' => $userId
]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: /pages/dashboard.php');
    exit();
}

// Traitement du paiement
$paymentSuccess = false;
$paymentError = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // Validation des données de paiement
        $cardNumber = preg_replace('/\s+/', '', $_POST['card_number']);
        $cardExpiry = $_POST['card_expiry'];
        $cardCvv = $_POST['card_cvv'];
        $cardName = $_POST['card_name'];
        
        // Validation simple
        if (strlen($cardNumber) != 16 || !ctype_digit($cardNumber)) {
            throw new Exception("Le numéro de carte doit contenir 16 chiffres");
        }
        
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $cardExpiry)) {
            throw new Exception("La date d'expiration doit être au format MM/YY");
        }
        
        if (strlen($cardCvv) != 3 || !ctype_digit($cardCvv)) {
            throw new Exception("Le code CVV doit contenir 3 chiffres");
        }
        
        if (empty($cardName)) {
            throw new Exception("Le nom du titulaire de la carte est requis");
        }
        
        // Génération d'une référence de transaction
        $transactionRef = 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Simulation de paiement (en production, vous intégreriez un service de paiement réel)
        // Pour la démonstration, nous considérons que le paiement est toujours réussi
        
        // Mise à jour du statut de la demande
        $query = "UPDATE demandes SET StatutPaiement = 'Complete', MontantPaiement = :montant WHERE DemandeID = :demandeId";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'montant' => $montant,
            'demandeId' => $demandeId
        ]);
        
        // Enregistrement du paiement
        $query = "INSERT INTO paiements (DemandeID, Montant, StatutPaiement, ReferenceTransaction) 
                  VALUES (:demandeId, :montant, 'Complete', :reference)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'montant' => $montant,
            'reference' => $transactionRef
        ]);
        
        // Ajout d'une entrée dans l'historique des demandes
        $query = "INSERT INTO historique_demandes (DemandeID, AncienStatut, NouveauStatut, DateModification, Commentaire, ModifiePar) 
                  VALUES (:demandeId, 'Soumise', 'EnCours', NOW(), 'Paiement effectué', :userId)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'demandeId' => $demandeId,
            'userId' => $userId
        ]);
        
        // Mise à jour du statut de la demande
        $query = "UPDATE demandes SET Statut = 'EnCours' WHERE DemandeID = :demandeId";
        $stmt = $db->prepare($query);
        $stmt->execute(['demandeId' => $demandeId]);
        
        // Création d'une notification
        $query = "INSERT INTO notifications (UtilisateurID, DemandeID, Contenu, TypeNotification) 
                  VALUES (:userId, :demandeId, :contenu, 'paiement_recu')";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'userId' => $userId,
            'demandeId' => $demandeId,
            'contenu' => "Votre paiement de {$montant} FCFA pour la demande #{$demandeId} a été reçu. Votre demande est en cours de traitement."
        ]);
        
        $db->commit();
        $paymentSuccess = true;
        
        // Nettoyage des variables de session
        unset($_SESSION['demande_id']);
        unset($_SESSION['montant']);
        unset($_SESSION['reference']);
        
    } catch (Exception $e) {
        $db->rollBack();
        $paymentError = $e->getMessage();
    }
}

include('../includes/header.php');
include('../includes/citizen_navbar.php');
?>

<div class="dashboard-container">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($paymentSuccess): ?>
                <!-- Confirmation de paiement -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <span class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-inline-block">
                                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                            </span>
                        </div>
                        <h2 class="mb-3">Paiement effectué avec succès</h2>
                        <p class="text-muted mb-4">Votre demande a été enregistrée et est maintenant en cours de traitement.</p>
                        
                        <div class="alert alert-info mb-4">
                            <p class="mb-0"><strong>Numéro de référence :</strong> <?php echo htmlspecialchars($reference); ?></p>
                            <p class="mb-0"><strong>Montant payé :</strong> <?php echo number_format($montant, 0, ',', ' '); ?> FCFA</p>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="dashboard.php" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-house-door me-2"></i>Retour au tableau de bord
                            </a>
                            <a href="details_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-primary px-4 py-2">
                                <i class="bi bi-file-text me-2"></i>Voir les détails de ma demande
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Formulaire de paiement -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 p-4">
                        <h3 class="card-title mb-0">Paiement de votre demande</h3>
                        <p class="text-muted mb-0">Veuillez compléter les informations de paiement ci-dessous</p>
                    </div>
                    
                    <?php if($paymentError): ?>
                    <div class="alert alert-danger mx-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $paymentError; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Récapitulatif de la demande</h5>
                                    <p class="mb-0">Référence : <?php echo htmlspecialchars($reference); ?></p>
                                    <p class="mb-0">Type : <?php echo htmlspecialchars($demande['TypeDemande'] . ' - ' . $demande['SousTypeDemande']); ?></p>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-primary"><?php echo number_format($montant, 0, ',', ' '); ?> FCFA</h4>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" id="payment-form" class="needs-validation" novalidate>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="card_name" class="form-label">Nom du titulaire</label>
                                    <input type="text" class="form-control" id="card_name" name="card_name" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le nom du titulaire de la carte.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="card_number" class="form-label">Numéro de carte</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" required maxlength="19">
                                        <span class="input-group-text">
                                            <i class="bi bi-credit-card"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer un numéro de carte valide.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="card_expiry" class="form-label">Date d'expiration</label>
                                    <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/YY" required maxlength="5">
                                    <div class="invalid-feedback">
                                        Veuillez entrer une date d'expiration valide.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="card_cvv" class="form-label">Code de sécurité (CVV)</label>
                                    <input type="text" class="form-control" id="card_cvv" name="card_cvv" required maxlength="3">
                                    <div class="invalid-feedback">
                                        Veuillez entrer le code de sécurité.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-info-circle-fill text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Mode démonstration</h5>
                                        <p class="mb-0">Ceci est une démonstration. Aucun paiement réel ne sera effectué. Vous pouvez utiliser n'importe quelles données de carte pour tester.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les conditions générales et je confirme que les informations fournies sont correctes.
                                </label>
                                <div class="invalid-feedback">
                                    Vous devez accepter les conditions générales pour continuer.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary py-3">
                                    <i class="bi bi-lock-fill me-2"></i>Payer <?php echo number_format($montant, 0, ',', ' '); ?> FCFA
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations de sécurité -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">Paiement sécurisé</h5>
                        <p class="text-muted mb-3">Toutes vos informations de paiement sont cryptées et sécurisées.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-shield-lock-fill text-success me-2"></i>
                                <span class="text-muted">Connexion sécurisée</span>
                            </div>
                            <div>
                                <img src="../assets/img/payment-methods.png" alt="Méthodes de paiement" height="30" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #1774df;
    --primary-dark: #135bb2;
    --primary-light: rgba(23, 116, 223, 0.1);
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
}

.dashboard-container {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding-top: 2rem;
}

/* Styles pour les cartes de paiement */
.card {
    transition: all 0.3s ease;
}

.rounded-4 {
    border-radius: 0.75rem !important;
}

/* Styles pour les icônes */
.bi {
    vertical-align: -.125em;
}

/* Animation de succès */
@keyframes checkmark {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.bg-success.bg-opacity-10 .bi {
    animation: checkmark 0.5s ease-in-out forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.getElementById('payment-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
    
    // Formatage du numéro de carte
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            // Supprimer tous les espaces
            let value = e.target.value.replace(/\s+/g, '');
            
            // Ajouter un espace tous les 4 caractères
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
            }
            
            // Mettre à jour la valeur
            e.target.value = value;
        });
    }
    
    // Formatage de la date d'expiration
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            e.target.value = value;
        });
    }
    
    // Validation du CVV (uniquement des chiffres)
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
