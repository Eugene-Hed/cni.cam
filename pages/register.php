<?php
include('../includes/config.php');

// Récupération des régions
$stmt = $db->query("SELECT * FROM regions ORDER BY NomRegion");
$regions = $stmt->fetchAll();

// Récupération des ethnies
$stmt = $db->query("SELECT * FROM ethnies ORDER BY NomEthnie");
$ethnies = $stmt->fetchAll();

$success = false;
$error = null;
$similar_account = false;
$similar_email = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Validation des données
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
        $dateNaissance = $_POST['dateNaissance'];
        $genre = $_POST['genre'];
        $ethnieID = $_POST['ethnie'];
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
        $profession = filter_input(INPUT_POST, 'profession', FILTER_SANITIZE_STRING);
        $adresse = filter_input(INPUT_POST, 'adresse', FILTER_SANITIZE_STRING);
        
        // Données géographiques
        $regionNaissanceID = $_POST['region_naissance'];
        $departementNaissanceID = $_POST['departement_naissance'];
        $villeNaissanceID = $_POST['ville_naissance'];
        $regionResidenceID = $_POST['region_residence'];
        $departementResidenceID = $_POST['departement_residence'];
        $villeResidenceID = $_POST['ville_residence'];

        // Vérification email existant
        $stmt = $db->prepare("SELECT Email FROM utilisateurs WHERE Email = ?");
        $stmt->execute([$email]);
        if($stmt->fetchColumn()) {
            throw new Exception("Cette adresse email est déjà utilisée");
        }

        // Vérification numéro de téléphone existant
        $stmt = $db->prepare("SELECT NumeroTelephone FROM utilisateurs WHERE NumeroTelephone = ?");
        $stmt->execute([$telephone]);
        if($stmt->fetchColumn()) {
            throw new Exception("Ce numéro de téléphone est déjà utilisé");
        }

        // Vérification combinaison nom/prénom
        $stmt = $db->prepare("SELECT Email FROM utilisateurs WHERE LOWER(Nom) = LOWER(?) AND LOWER(Prenom) = LOWER(?)");
        $stmt->execute([strtolower($nom), strtolower($prenom)]);
        $similar_email = $stmt->fetchColumn();
        
        if($similar_email) {
            $similar_account = true;
            throw new Exception("Un compte avec ce nom et prénom existe déjà. S'agit-il de vous ?");
        }

        // Génération code utilisateur unique
        $codeUtilisateur = 'CNI' . date('Y') . rand(1000, 9999);

        // Insertion dans la base de données (sans mot de passe)
        $query = "INSERT INTO utilisateurs (
            Nom, Prenom, DateNaissance, Genre, Email, NumeroTelephone, 
            Adresse, CodeUtilisateur, RoleId, EthnieID,
            RegionNaissanceID, DepartementNaissanceID, VilleNaissanceID,
            RegionResidenceID, DepartementResidenceID, VilleResidenceID,
            Profession, DateCreation, IsActive
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 2, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1
        )";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $nom, $prenom, $dateNaissance, $genre, $email, $telephone,
            $adresse, $codeUtilisateur, $ethnieID,
            $regionNaissanceID, $departementNaissanceID, $villeNaissanceID,
            $regionResidenceID, $departementResidenceID, $villeResidenceID,
            $profession
        ]);

        $db->commit();
        $success = true;

    } catch(Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CNI.CAM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <style>
.register-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #1774df 0%, #135bb2 100%);
    padding: 2rem 0;
}

.register-card {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.register-header {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.register-header img {
    width: 80px;
    margin-bottom: 1rem;
}

/* Barre de progression */
.progress-bar-container {
    padding: 1rem 2rem;
    background: #f8f9fa;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 30px;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 2px;
    background: #dee2e6;
    transform: translateY(-50%);
    z-index: 1;
}

.progress-step {
    position: relative;
    z-index: 2;
    background: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    transition: all 0.3s ease;
}

.progress-step.active {
    border-color: #1774df;
    background: #1774df;
    color: white;
}

.progress-step.completed {
    border-color: #28a745;
    background: #28a745;
    color: white;
}

.progress-label {
    position: absolute;
    top: 45px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.8rem;
    white-space: nowrap;
}

/* Formulaire */
.form-section {
    padding: 2rem;
}

.form-control, .form-select {
    padding: 0.8rem 1rem;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1774df;
    box-shadow: 0 0 0 0.2rem rgba(23, 116, 223, 0.25);
}

.btn {
    padding: 0.8rem 1.5rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

/* Animations */
.step-content {
    animation: fadeIn 0.3s ease;
}
.iti {
    width: 100%;
}

.is-invalid ~ .invalid-feedback {
    display: block;
}

.success-animation {
    text-align: center;
    padding: 2rem;
}

.checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #28a745;
    stroke-miterlimit: 10;
    margin: 0 auto 1.5rem;
    box-shadow: inset 0px 0px 0px #28a745;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #28a745;
    fill: none;
    animation: stroke .6s cubic-bezier(0.650, 0.000, 0.450, 1.000) forwards;
}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke .3s cubic-bezier(0.650, 0.000, 0.450, 1.000) .8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }
    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px #28a745;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .register-card {
        margin: 1rem;
    }
    
    .progress-steps {
        margin: 0 1rem;
    }
}
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <?php if($success): ?>
                <!-- Message de succès et redirection -->
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                    <h2 class="mb-3">Inscription réussie !</h2>
                    <p class="text-muted mb-4">Votre compte a été créé avec succès.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/pages/login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </a>
                        <a href="/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-house-door me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
                <script>
                    // Redirection après 5 secondes
                    setTimeout(function() {
                        window.location.href = "/index.php";
                    }, 5000);
                </script>
            <?php elseif($similar_account): ?>
                <!-- Compte similaire trouvé -->
                <div class="text-center p-5">
                    <i class="bi bi-exclamation-circle text-warning display-1 mb-4"></i>
                    <h2 class="mb-3">Compte similaire détecté</h2>
                    <p class="text-muted mb-4">Un compte avec le nom "<?php echo htmlspecialchars($nom . ' ' . $prenom); ?>" existe déjà.</p>
                    <p class="mb-4">S'agit-il de vous ? Si oui, veuillez vous connecter avec votre email : <strong><?php echo htmlspecialchars($similar_email); ?></strong></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/pages/login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </a>
                        <button type="button" class="btn btn-outline-primary" id="continueRegistration">
                            <i class="bi bi-person-plus me-2"></i>Non, créer un nouveau compte
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="register-header">
                    <div class="progress-bar-container">
                        <div class="progress-steps">
                            <div class="progress-step active" data-step="1">
                                1
                                <span class="progress-label">Informations</span>
                            </div>
                            <div class="progress-step" data-step="2">
                                2
                                <span class="progress-label">Naissance</span>
                            </div>
                            <div class="progress-step" data-step="3">
                                3
                                <span class="progress-label">Résidence</span>
                            </div>
                            <div class="progress-step" data-step="4">
                                4
                                <span class="progress-label">Contact</span>
                            </div>
                        </div>
                    </div>

                    <img src="../assets/images/Cameroun.png" alt="CNI.CAM Logo" class="img-fluid">
                    <h2 class="mb-0">Créer un compte</h2>
                    <p class="text-muted">Rejoignez la plateforme CNI.CAM</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4">
                    <form method="POST" action="" class="needs-validation" novalidate id="registrationForm">
                        <!-- Étape 1: Informations personnelles -->
                        <div class="step-content" id="step1">
                            <h4 class="mb-4">Informations personnelles</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nom" required>
                                    <div class="invalid-feedback">Veuillez entrer votre nom</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="prenom" required>
                                    <div class="invalid-feedback">Veuillez entrer votre prénom</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="dateNaissance" required>
                                    <div class="invalid-feedback">Veuillez entrer une date de naissance valide (18 ans minimum)</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Genre <span class="text-danger">*</span></label>
                                    <select class="form-select" name="genre" required>
                                        <option value="">Sélectionner...</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre genre</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ethnie <span class="text-danger">*</span></label>
                                    <select class="form-select" name="ethnie" required>
                                        <option value="">Sélectionner une ethnie...</option>
                                        <?php foreach($ethnies as $ethnie): ?>
                                            <option value="<?php echo $ethnie['EthnieID']; ?>">
                                                <?php echo $ethnie['NomEthnie']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre ethnie</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Profession <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="profession" required>
                                    <div class="invalid-feedback">Veuillez entrer votre profession</div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 2: Lieu de naissance -->
                        <div class="step-content d-none" id="step2">
                            <h4 class="mb-4">Lieu de naissance</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Région <span class="text-danger">*</span></label>
                                    <select class="form-select" name="region_naissance" id="region_naissance" required>
                                        <option value="">Sélectionner une région...</option>
                                        <?php foreach($regions as $region): ?>
                                            <option value="<?php echo $region['RegionID']; ?>">
                                                <?php echo $region['NomRegion']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre région de naissance</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Département <span class="text-danger">*</span></label>
                                    <select class="form-select" name="departement_naissance" id="departement_naissance" required>
                                        <option value="">Sélectionner un département...</option>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre département de naissance</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ville <span class="text-danger">*</span></label>
                                    <select class="form-select" name="ville_naissance" id="ville_naissance" required>
                                        <option value="">Sélectionner une ville...</option>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre ville de naissance</div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 3: Lieu de résidence -->
                        <div class="step-content d-none" id="step3">
                            <h4 class="mb-4">Lieu de résidence</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Région <span class="text-danger">*</span></label>
                                    <select class="form-select" name="region_residence" id="region_residence" required>
                                        <option value="">Sélectionner une région...</option>
                                        <?php foreach($regions as $region): ?>
                                            <option value="<?php echo $region['RegionID']; ?>">
                                                <?php echo $region['NomRegion']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre région de résidence</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Département <span class="text-danger">*</span></label>
                                    <select class="form-select" name="departement_residence" id="departement_residence" required>
                                        <option value="">Sélectionner un département...</option>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre département de résidence</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ville <span class="text-danger">*</span></label>
                                    <select class="form-select" name="ville_residence" id="ville_residence" required>
                                        <option value="">Sélectionner une ville...</option>
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner votre ville de résidence</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Adresse complète <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="adresse" required>
                                    <div class="invalid-feedback">Veuillez entrer votre adresse complète</div>
                                </div>
                            </div>
                        </div>

                        <!-- Étape 4: Coordonnées de contact -->
                        <div class="step-content d-none" id="step4">
                            <h4 class="mb-4">Coordonnées de contact</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                    <div class="invalid-feedback">Veuillez entrer une adresse email valide</div>
                                    <div class="form-text">Cet email sera utilisé pour vous connecter et recevoir des notifications</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" required>
                                    <div class="invalid-feedback">Veuillez entrer un numéro de téléphone valide</div>
                                    <div class="form-text">Ce numéro sera utilisé pour les vérifications et notifications</div>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            J'accepte les <a href="/pages/terms.php" target="_blank">conditions d'utilisation</a> et la <a href="/pages/privacy.php" target="_blank">politique de confidentialité</a>
                                        </label>
                                        <div class="invalid-feedback">Vous devez accepter les conditions d'utilisation</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-primary" id="prevBtn" style="display:none">
                                <i class="bi bi-arrow-left me-2"></i>Précédent
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Suivant<i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display:none">
                                <i class="bi bi-check-circle me-2"></i>Créer mon compte
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Déjà inscrit ? -->
        <div class="text-center mt-4 text-white">
            <p>Déjà inscrit ? <a href="/pages/login.php" class="text-white fw-bold">Connectez-vous ici</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Gestion des étapes
let currentStep = 1;
const totalSteps = 4;

function showStep(step) {
    $('.step-content').addClass('d-none');
    $(`#step${step}`).removeClass('d-none');
    
    if(step === 1) {
        $('#prevBtn').hide();
        $('#nextBtn').show();
        $('#submitBtn').hide();
    } else if(step === totalSteps) {
        $('#prevBtn').show();
        $('#nextBtn').hide();
        $('#submitBtn').show();
    } else {
        $('#prevBtn').show();
        $('#nextBtn').show();
        $('#submitBtn').hide();
    }
}

// Gestion de la barre de progression
function updateProgressBar(step) {
    $('.progress-step').removeClass('active completed');
    $(`.progress-step[data-step="${step}"]`).addClass('active');
    $(`.progress-step[data-step="${step}"]`).prevAll('.progress-step').addClass('completed');
}

// Navigation entre les étapes
$('#nextBtn').click(() => {
    if(validateCurrentStep() && currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
        updateProgressBar(currentStep);
    }
});

$('#prevBtn').click(() => {
    if(currentStep > 1) {
        currentStep--;
        showStep(currentStep);
        updateProgressBar(currentStep);
    }
});

// Chargement dynamique des départements et villes pour le lieu de naissance
$('#region_naissance').change(function() {
    const regionId = $(this).val();
    if(regionId) {
        $.ajax({
            url: 'get_departements.php',
            type: 'POST',
            data: {region_id: regionId},
            success: function(response) {
                $('#departement_naissance').html(response);
                $('#ville_naissance').html('<option value="">Sélectionner une ville...</option>');
            }
        });
    }
});

$('#departement_naissance').change(function() {
    const departementId = $(this).val();
    if(departementId) {
        $.ajax({
            url: 'get_villes.php',
            type: 'POST',
            data: {departement_id: departementId},
            success: function(response) {
                $('#ville_naissance').html(response);
            }
        });
    }
});

// Chargement dynamique des départements et villes pour le lieu de résidence
$('#region_residence').change(function() {
    const regionId = $(this).val();
    if(regionId) {
        $.ajax({
            url: 'get_departements.php',
            type: 'POST',
            data: {region_id: regionId},
            success: function(response) {
                $('#departement_residence').html(response);
                $('#ville_residence').html('<option value="">Sélectionner une ville...</option>');
            }
        });
    }
});

$('#departement_residence').change(function() {
    const departementId = $(this).val();
    if(departementId) {
        $.ajax({
            url: 'get_villes.php',
            type: 'POST',
            data: {departement_id: departementId},
            success: function(response) {
                $('#ville_residence').html(response);
            }
        });
    }
});

// Validation des étapes
function validateCurrentStep() {
    const currentInputs = $(`#step${currentStep}`).find('input, select');
    let isValid = true;
    
    currentInputs.each(function() {
        if(this.checkValidity() === false) {
            isValid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        }
    });

        // Validation spécifique pour l'étape 1 (âge minimum)
        if(currentStep === 1) {
        const dateNaissance = $('input[name="dateNaissance"]').val();
        if(dateNaissance && !isAgeValid(dateNaissance)) {
            $('input[name="dateNaissance"]').addClass('is-invalid');
            isValid = false;
        }
    }

    // Validation spécifique pour l'étape 4 (email et téléphone)
    if(currentStep === 4) {
        // Validation de l'email (format)
        const email = $('#email').val();
        if(email && !isValidEmail(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        
        // Vérification des conditions d'utilisation
        if(!$('#terms').is(':checked')) {
            $('#terms').addClass('is-invalid');
            isValid = false;
        }
    }

    return isValid;
}

// Validation de l'âge (18 ans minimum)
function isAgeValid(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if(monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age >= 18;
}

// Validation du format email
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validation du formulaire complet
$('#registrationForm').on('submit', function(event) {
    // Valider toutes les étapes avant soumission
    for(let i = 1; i <= totalSteps; i++) {
        currentStep = i;
        if(!validateCurrentStep()) {
            event.preventDefault();
            showStep(i);
            updateProgressBar(i);
            return false;
        }
    }
    
    // Vérifier que les conditions sont acceptées
    if(!$('#terms').is(':checked')) {
        event.preventDefault();
        $('#terms').addClass('is-invalid');
        showStep(4);
        updateProgressBar(4);
        return false;
    }
    
    // Tout est valide, le formulaire peut être soumis
    return true;
});

// Validation en temps réel des champs
$('input, select').on('input change', function() {
    if(this.checkValidity()) {
        $(this).removeClass('is-invalid');
        $(this).addClass('is-valid');
    } else {
        $(this).removeClass('is-valid');
        $(this).addClass('is-invalid');
    }
    
    // Validation spécifique pour la date de naissance
    if($(this).attr('name') === 'dateNaissance') {
        if(this.value && !isAgeValid(this.value)) {
            $(this).removeClass('is-valid');
            $(this).addClass('is-invalid');
        }
    }
});

// Initialisation du téléphone
const phoneInput = document.querySelector("#telephone");
const iti = window.intlTelInput(phoneInput, {
    initialCountry: "cm",
    preferredCountries: ["cm", "fr"],
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
});

// Validation du numéro
phoneInput.addEventListener('blur', function() {
    if (phoneInput.value.trim()) {
        if (iti.isValidNumber()) {
            phoneInput.classList.remove('is-invalid');
            phoneInput.classList.add('is-valid');
        } else {
            phoneInput.classList.add('is-invalid');
            phoneInput.classList.remove('is-valid');
        }
    }
});

// Avant la soumission du formulaire
$('#registrationForm').on('submit', function(event) {
    const phoneNumber = iti.getNumber();
    $('#telephone').val(phoneNumber);
});

// Continuer l'inscription malgré un compte similaire
$('#continueRegistration').on('click', function() {
    window.location.href = 'register.php';
});

// Initialisation
$(document).ready(function() {
    showStep(1);
    updateProgressBar(1);
    
    // Vérifier si des données ont été soumises et qu'il y a eu une erreur
    <?php if(isset($error) && $_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        // Remplir les champs avec les données soumises
        $('input[name="nom"]').val('<?php echo htmlspecialchars($nom ?? ''); ?>');
        $('input[name="prenom"]').val('<?php echo htmlspecialchars($prenom ?? ''); ?>');
        $('input[name="dateNaissance"]').val('<?php echo htmlspecialchars($dateNaissance ?? ''); ?>');
        $('select[name="genre"]').val('<?php echo htmlspecialchars($genre ?? ''); ?>');
        $('select[name="ethnie"]').val('<?php echo htmlspecialchars($ethnieID ?? ''); ?>');
        $('input[name="profession"]').val('<?php echo htmlspecialchars($profession ?? ''); ?>');
        $('input[name="email"]').val('<?php echo htmlspecialchars($email ?? ''); ?>');
        $('input[name="telephone"]').val('<?php echo htmlspecialchars($telephone ?? ''); ?>');
        $('input[name="adresse"]').val('<?php echo htmlspecialchars($adresse ?? ''); ?>');
    <?php endif; ?>
});
</script>

</body>
</html>
