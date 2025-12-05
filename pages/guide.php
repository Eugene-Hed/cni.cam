<?php
// Session is initialized centrally in includes/config.php
require_once '../includes/config.php';
$pageTitle = "Guide d'utilisation - CNI.CAM";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php include_once '../includes/navbar.php'; ?>

    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">Guide d'utilisation</h1>
                    <p class="lead mb-4">Apprenez à utiliser efficacement les services de CNI.CAM avec notre guide détaillé.</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Accueil</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Guide d'utilisation</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                    <img src="/assets/images/guide-hero.svg" alt="Guide CNI.CAM" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Table des matières -->
    <div class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                        <div class="card-body p-4">
                            <h2 class="h3 mb-4 text-center">Table des matières</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#creation-compte" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                                Création de compte
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#demande-cni" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                                Demande de CNI
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#demande-certificat" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                                Demande de certificat de nationalité
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#paiement" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">4</span>
                                                Paiement en ligne
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#suivi-demande" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">5</span>
                                                Suivi de demande
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#rendez-vous" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">6</span>
                                                Prise de rendez-vous
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#declaration-perte" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">7</span>
                                                Déclaration de perte
                                            </a>
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <a href="#gestion-profil" class="text-decoration-none d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-2">8</span>
                                                Gestion du profil
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu du guide -->
    <div class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Section 1: Création de compte -->
                    <section id="creation-compte" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                    <h2 class="h2 mb-0">1. Création de compte</h2>
                                </div>
                                
                                <p class="lead">Pour accéder aux services de CNI.CAM, vous devez d'abord créer un compte utilisateur.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder à la page d'inscription</h3>
                                            <p>Rendez-vous sur la page d'accueil de CNI.CAM et cliquez sur le bouton "Inscription" situé en haut à droite de l'écran.</p>
                                            <div class="text-center mb-3">
                                                <img src="/assets/images/guide/inscription-button.jpg" alt="Bouton d'inscription" class="img-fluid rounded-3 shadow-sm guide-img">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Remplir le formulaire d'inscription</h3>
                                            <p>Complétez le formulaire avec vos informations personnelles :</p>
                                            <ul>
                                                <li>Nom et prénom</li>
                                                <li>Date de naissance</li>
                                                <li>Adresse email</li>
                                                <li>Numéro de téléphone</li>
                                            </ul>
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Assurez-vous d'utiliser une adresse email valide, car vous recevrez un lien de confirmation à cette adresse.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Vérifier votre adresse email</h3>
                                            <p>Après avoir soumis le formulaire, vous recevrez un email contenant un lien de confirmation. Cliquez sur ce lien pour activer votre compte.</p>
                                            <div class="alert alert-warning">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Si vous ne recevez pas l'email dans les 15 minutes, vérifiez votre dossier de spam ou cliquez sur "Renvoyer l'email de confirmation" sur la page de connexion.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Compléter votre profil</h3>
                                            <p>Une fois votre compte activé, connectez-vous et complétez votre profil avec les informations supplémentaires requises :</p>
                                            <ul>
                                                <li>Adresse complète</li>
                                                <li>Informations d'identité</li>
                                                <li>Photo de profil (facultatif)</li>
                                            </ul>
                                            <p>Ces informations faciliteront vos futures démarches administratives sur la plateforme.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/register.php" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-person-plus me-2"></i>Créer un compte maintenant
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 2: Demande de CNI -->
                    <section id="demande-cni" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-person-vcard"></i>
                                    </div>
                                    <h2 class="h2 mb-0">2. Demande de CNI</h2>
                                </div>
                                
                                <p class="lead">Apprenez à effectuer une demande de Carte Nationale d'Identité en ligne.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder au service de demande de CNI</h3>
                                            <p>Connectez-vous à votre compte et cliquez sur "Services" dans le menu principal, puis sélectionnez "Demande de CNI".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Choisir le type de demande</h3>
                                            <p>Sélectionnez le type de demande qui correspond à votre situation :</p>
                                            <ul>
                                                <li><strong>Première demande</strong> : Si vous n'avez jamais eu de CNI</li>
                                                <li><strong>Renouvellement</strong> : Si votre CNI est expirée ou va bientôt expirer</li>
                                                <li><strong>Remplacement</strong> : En cas de perte, vol ou détérioration</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Remplir le formulaire de demande</h3>
                                            <p>Complétez le formulaire avec les informations requises :</p>
                                            <ul>
                                                <li>Informations personnelles</li>
                                                <li>Informations sur les parents</li>
                                                <li>Adresse actuelle</li>
                                                <li>Profession</li>
                                            </ul>
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Assurez-vous que toutes les informations saisies correspondent exactement à celles figurant sur vos documents officiels.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Télécharger les documents requis</h3>
                                            <p>Numérisez et téléchargez les documents suivants :</p>
                                            <ul>
                                                <li>Copie d'acte de naissance</li>
                                                <li>Certificat de nationalité</li>
                                                <li>Photo d'identité numérique (format 35x45mm, fond blanc)</li>
                                                <li>Justificatif de domicile</li>
                                                <li>Ancienne CNI (en cas de renouvellement)</li>
                                                <li>Déclaration de perte (en cas de perte ou vol)</li>
                                            </ul>
                                            <div class="alert alert-warning">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Les fichiers doivent être au format JPG, PNG ou PDF et ne pas dépasser 2 Mo chacun.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 5</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Vérifier et soumettre la demande</h3>
                                            <p>Vérifiez attentivement toutes les informations saisies et les documents téléchargés. Une fois que tout est correct, cliquez sur "Soumettre ma demande".</p>
                                            <div class="alert alert-success">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Après soumission, vous recevrez un numéro de demande unique. Conservez-le précieusement pour suivre l'avancement de votre dossier.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 6</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Payer les frais administratifs</h3>
                                            <p>Procédez au paiement des frais administratifs (10 000 FCFA) via l'une des méthodes de paiement proposées.</p>
                                            <p>Une fois le paiement effectué, votre demande sera transmise aux services compétents pour traitement.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/demande_cni.php" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-person-vcard me-2"></i>Faire une demande de CNI
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 3: Demande de certificat de nationalité -->
                    <section id="demande-certificat" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-flag"></i>
                                    </div>
                                    <h2 class="h2 mb-0">3. Demande de certificat de nationalité</h2>
                                </div>
                                
                                <p class="lead">Suivez ces étapes pour demander votre certificat de nationalité camerounaise en ligne.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder au service de demande de certificat</h3>
                                            <p>Connectez-vous à votre compte et cliquez sur "Services" dans le menu principal, puis sélectionnez "Certificat de nationalité".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Remplir le formulaire de demande</h3>
                                            <p>Complétez le formulaire avec les informations requises :</p>
                                            <ul>
                                                <li>Informations personnelles</li>
                                                <li>Informations sur les parents</li>
                                                <li>Motif de la demande</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Télécharger les documents requis</h3>
                                            <p>Numérisez et téléchargez les documents suivants :</p>
                                            <ul>
                                                <li>Copie intégrale de l'acte de naissance</li>
                                                <li>Copie de la CNI (si vous en possédez une)</li>
                                                <li>Copie de l'acte de naissance d'un parent camerounais</li>
                                                <li>Copie de la CNI d'un parent camerounais</li>
                                                <li>Justificatif de domicile</li>
                                                <li>Photo d'identité numérique</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Vérifier et soumettre la demande</h3>
                                            <p>Vérifiez attentivement toutes les informations saisies et les documents téléchargés. Une fois que tout est correct, cliquez sur "Soumettre ma demande".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 5</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Payer les frais administratifs</h3>
                                            <p>Procédez au paiement des frais administratifs (1 500 FCFA) via l'une des méthodes de paiement proposées.</p>
                                            <p>Une fois le paiement effectué, votre demande sera transmise aux services compétents pour traitement.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/demande_certificat.php" class="btn btn-success rounded-pill px-4">
                                        <i class="bi bi-flag me-2"></i>Demander un certificat de nationalité
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 4: Paiement en ligne -->
                    <section id="paiement" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <h2 class="h2 mb-0">4. Paiement en ligne</h2>
                                </div>
                                
                                <p class="lead">Découvrez comment effectuer vos paiements en ligne de manière sécurisée.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder à la page de paiement</h3>
                                            <p>Après avoir soumis votre demande, vous serez automatiquement redirigé vers la page de paiement. Vous pouvez également y accéder depuis votre espace personnel en cliquant sur "Mes demandes" puis sur "Payer" à côté de la demande concernée.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Choisir le mode de paiement</h3>
                                            <p>Sélectionnez l'une des méthodes de paiement disponibles :</p>
                                            <div class="row g-3 mb-3">
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <i class="bi bi-phone fs-2 text-primary mb-2"></i>
                                                            <p class="mb-0 small">Mobile Money</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <i class="bi bi-credit-card fs-2 text-primary mb-2"></i>
                                                            <p class="mb-0 small">Carte bancaire</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <i class="bi bi-bank fs-2 text-primary mb-2"></i>
                                                            <p class="mb-0 small">Virement bancaire</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <i class="bi bi-cash fs-2 text-primary mb-2"></i>
                                                            <p class="mb-0 small">Paiement en agence</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Saisir les informations de paiement</h3>
                                            <p>Selon la méthode choisie, vous devrez fournir différentes informations :</p>
                                            <ul>
                                                <li><strong>Mobile Money</strong> : Numéro de téléphone et confirmation via votre téléphone</li>
                                                <li><strong>Carte bancaire</strong> : Numéro de carte, date d'expiration, code CVV</li>
                                                <li><strong>Virement bancaire</strong> : Informations du compte à créditer</li>
                                            </ul>
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-shield-lock-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Toutes les transactions sont sécurisées avec un cryptage SSL. Vos informations bancaires ne sont jamais stockées sur nos serveurs.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Confirmer et finaliser le paiement</h3>
                                            <p>Vérifiez le montant et les détails du paiement, puis confirmez la transaction. Une fois le paiement effectué :</p>
                                            <ul>
                                                <li>Vous recevrez une confirmation par email et SMS</li>
                                                <li>Un reçu électronique sera disponible dans votre espace personnel</li>
                                                <li>Votre demande passera automatiquement à l'étape suivante du traitement</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/paiements.php" class="btn btn-info rounded-pill px-4 text-white">
                                        <i class="bi bi-credit-card me-2"></i>Accéder aux paiements
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 5: Suivi de demande -->
                    <section id="suivi-demande" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-search"></i>
                                    </div>
                                    <h2 class="h2 mb-0">5. Suivi de demande</h2>
                                </div>
                                
                                <p class="lead">Suivez l'avancement de vos demandes en temps réel.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder à la page de suivi</h3>
                                            <p>Connectez-vous à votre compte et cliquez sur "Mes demandes" dans le menu principal.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Consulter l'état de vos demandes</h3>
                                            <p>Vous verrez la liste de toutes vos demandes avec leur statut actuel :</p>
                                            <div class="row g-3 mb-3">
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <div class="badge bg-warning text-dark mb-2">En attente</div>
                                                            <p class="mb-0 small">Demande soumise mais en attente de paiement</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <div class="badge bg-info text-white mb-2">En traitement</div>
                                                            <p class="mb-0 small">Demande en cours d'examen</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                        <div class="badge bg-success mb-2">Approuvée</div>
                                                            <p class="mb-0 small">Demande approuvée, document en production</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body text-center">
                                                            <div class="badge bg-danger mb-2">Rejetée</div>
                                                            <p class="mb-0 small">Demande rejetée avec motif</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Consulter les détails d'une demande</h3>
                                            <p>Cliquez sur "Voir détails" à côté de la demande qui vous intéresse pour accéder à :</p>
                                            <ul>
                                                <li>L'historique complet du traitement</li>
                                                <li>Les commentaires des agents traitants</li>
                                                <li>Les documents soumis</li>
                                                <li>Les éventuelles actions requises de votre part</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Recevoir des notifications</h3>
                                            <p>Vous serez automatiquement notifié par email et SMS à chaque changement de statut de votre demande. Vous pouvez également consulter vos notifications directement sur la plateforme en cliquant sur l'icône de cloche en haut à droite.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/mes_demandes.php" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-search me-2"></i>Suivre mes demandes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 6: Prise de rendez-vous -->
                    <section id="rendez-vous" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <h2 class="h2 mb-0">6. Prise de rendez-vous</h2>
                                </div>
                                
                                <p class="lead">Apprenez à prendre rendez-vous en ligne pour vos démarches nécessitant une présence physique.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder au service de rendez-vous</h3>
                                            <p>Connectez-vous à votre compte et cliquez sur "Services" dans le menu principal, puis sélectionnez "Rendez-vous en ligne".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Sélectionner le type de rendez-vous</h3>
                                            <p>Choisissez le motif de votre rendez-vous parmi les options disponibles :</p>
                                            <ul>
                                                <li>Capture des données biométriques</li>
                                                <li>Dépôt de documents originaux</li>
                                                <li>Retrait de document</li>
                                                <li>Entretien pour vérification d'identité</li>
                                                <li>Autre (à préciser)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Choisir un centre de service</h3>
                                            <p>Sélectionnez le centre de service le plus proche ou le plus pratique pour vous :</p>
                                            <ol>
                                                <li>Sélectionnez votre région</li>
                                                <li>Choisissez votre ville</li>
                                                <li>Sélectionnez un centre parmi ceux disponibles</li>
                                            </ol>
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">Vous pouvez consulter les informations détaillées sur chaque centre (adresse exacte, horaires d'ouverture, accessibilité) avant de faire votre choix.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Sélectionner une date et un créneau horaire</h3>
                                            <p>Consultez le calendrier des disponibilités et choisissez :</p>
                                            <ol>
                                                <li>Une date disponible (en vert sur le calendrier)</li>
                                                <li>Un créneau horaire parmi ceux proposés</li>
                                            </ol>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 5</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Confirmer le rendez-vous</h3>
                                            <p>Vérifiez les détails de votre rendez-vous et confirmez. Vous recevrez :</p>
                                            <ul>
                                                <li>Une confirmation par email avec un QR code</li>
                                                <li>Un SMS de rappel la veille du rendez-vous</li>
                                                <li>La possibilité d'ajouter le rendez-vous à votre calendrier (Google, Apple, Outlook)</li>
                                            </ul>
                                            <div class="alert alert-warning">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">En cas d'empêchement, pensez à annuler ou reporter votre rendez-vous au moins 24 heures à l'avance pour libérer le créneau.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/rendez_vous.php" class="btn btn-warning rounded-pill px-4">
                                        <i class="bi bi-calendar-check me-2"></i>Prendre rendez-vous
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 7: Déclaration de perte -->
                    <section id="declaration-perte" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-danger bg-opacity-10 text-danger me-3">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <h2 class="h2 mb-0">7. Déclaration de perte</h2>
                                </div>
                                
                                <p class="lead">Apprenez à déclarer la perte de votre CNI en ligne et à obtenir une attestation de perte.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder au service de déclaration de perte</h3>
                                            <p>Connectez-vous à votre compte et cliquez sur "Services" dans le menu principal, puis sélectionnez "Déclaration de perte".</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Remplir le formulaire de déclaration</h3>
                                            <p>Complétez le formulaire en indiquant :</p>
                                            <ul>
                                                <li>Les informations sur votre CNI perdue (numéro si vous le connaissez, date d'émission)</li>
                                                <li>Les circonstances de la perte (date, lieu, contexte)</li>
                                                <li>Si vous souhaitez demander une nouvelle CNI immédiatement</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Payer les frais de déclaration</h3>
                                            <p>Procédez au paiement des frais de déclaration de perte (1 000 FCFA) via l'une des méthodes de paiement proposées.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Recevoir l'attestation de perte</h3>
                                            <p>Une fois le paiement effectué, vous pourrez télécharger immédiatement votre attestation de perte au format PDF. Ce document vous sera également envoyé par email.</p>
                                            <div class="alert alert-info">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-info-circle-fill"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-0">L'attestation de perte est un document officiel qui peut vous servir temporairement en attendant votre nouvelle CNI.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 5</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Demander une nouvelle CNI (optionnel)</h3>
                                            <p>Si vous avez choisi de demander une nouvelle CNI, vous serez automatiquement redirigé vers le service de demande de CNI avec certains champs pré-remplis.</p>
                                            <p>Suivez ensuite la procédure standard de demande de CNI (voir section 2 de ce guide).</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/declaration_perte.php" class="btn btn-danger rounded-pill px-4">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Déclarer une perte
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Section 8: Gestion du profil -->
                    <section id="gestion-profil" class="mb-5 pt-3">
                        <div class="card border-0 shadow-sm rounded-4" data-aos="fade-up">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="guide-icon bg-secondary bg-opacity-10 text-secondary me-3">
                                        <i class="bi bi-person-gear"></i>
                                    </div>
                                    <h2 class="h2 mb-0">8. Gestion du profil</h2>
                                </div>
                                
                                <p class="lead">Apprenez à gérer votre profil et vos paramètres personnels.</p>
                                
                                <div class="steps-container">
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 1</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Accéder à votre profil</h3>
                                            <p>Cliquez sur votre nom d'utilisateur en haut à droite de l'écran, puis sélectionnez "Mon profil" dans le menu déroulant.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 2</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Modifier vos informations personnelles</h3>
                                            <p>Dans l'onglet "Informations personnelles", vous pouvez :</p>
                                            <ul>
                                                <li>Mettre à jour votre adresse</li>
                                                <li>Modifier votre numéro de téléphone</li>
                                                <li>Changer votre photo de profil</li>
                                                <li>Mettre à jour vos coordonnées professionnelles</li>
                                            </ul>
                                            <div class="alert alert-warning">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>
                                                    <div>
                                                    <p class="mb-0">Certaines informations critiques (nom, date de naissance) ne peuvent pas être modifiées directement. Pour ces changements, contactez le support avec les justificatifs appropriés.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 3</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Gérer la sécurité de votre compte</h3>
                                            <p>Dans l'onglet "Sécurité", vous pouvez :</p>
                                            <ul>
                                                <li>Changer votre mot de passe</li>
                                                <li>Activer l'authentification à deux facteurs</li>
                                                <li>Consulter l'historique des connexions</li>
                                                <li>Gérer les appareils connectés</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item mb-4">
                                        <div class="step-number">Étape 4</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Configurer vos préférences de notification</h3>
                                            <p>Dans l'onglet "Notifications", vous pouvez choisir comment et quand être notifié :</p>
                                            <ul>
                                                <li>Notifications par email</li>
                                                <li>Notifications par SMS</li>
                                                <li>Notifications dans l'application</li>
                                            </ul>
                                            <p>Vous pouvez personnaliser les types d'événements pour lesquels vous souhaitez être notifié (changement de statut de demande, rendez-vous, etc.).</p>
                                        </div>
                                    </div>
                                    
                                    <div class="step-item">
                                        <div class="step-number">Étape 5</div>
                                        <div class="step-content">
                                            <h3 class="h5 mb-3">Gérer vos documents personnels</h3>
                                            <p>Dans l'onglet "Mes documents", vous pouvez :</p>
                                            <ul>
                                                <li>Consulter les documents que vous avez téléchargés</li>
                                                <li>Ajouter de nouveaux documents à votre dossier numérique</li>
                                                <li>Télécharger vos documents officiels (CNI, certificat de nationalité, etc.)</li>
                                                <li>Partager certains documents de manière sécurisée</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="/pages/profil.php" class="btn btn-secondary rounded-pill px-4">
                                        <i class="bi bi-person-gear me-2"></i>Gérer mon profil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ rapide -->
    <div class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">Questions fréquentes</h2>
                <p class="lead text-muted">Des réponses rapides à vos questions les plus courantes</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="accordion" id="faqAccordion" data-aos="fade-up">
                        <!-- Question 1 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Combien de temps faut-il pour obtenir une CNI ?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Le délai standard pour l'obtention d'une CNI est de 48 heurs après la validation de votre dossier complet et la capture de vos données biométriques. Ce délai peut varier en fonction de la période de l'année et de la charge des services.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 2 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Puis-je suivre ma demande sans avoir de compte ?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Oui, vous pouvez suivre l'état d'avancement de votre demande sans avoir de compte en utilisant le numéro de référence de votre demande et votre date de naissance sur la page "Suivi public". Cependant, la création d'un compte vous donne accès à plus de fonctionnalités et à un suivi plus détaillé.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Question 3 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Que faire si ma demande est rejetée ?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <p>Si votre demande est rejetée, vous recevrez une notification expliquant les raisons du rejet. Vous pourrez alors :</p>
                                    <ol>
                                        <li>Corriger les informations erronées</li>
                                        <li>Fournir les documents manquants ou illisibles</li>
                                        <li>Soumettre à nouveau votre demande</li>
                                    </ol>
                                    <p>Si vous pensez que le rejet est injustifié, vous pouvez contacter notre service client pour obtenir des éclaircissements ou déposer une réclamation.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4" data-aos="fade-up">
                        <a href="/pages/faq.php" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-question-circle me-2"></i>Voir toutes les questions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-3">Besoin d'aide supplémentaire ?</h2>
                    <p class="lead mb-0">Notre équipe de support est disponible pour vous accompagner dans vos démarches.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="/pages/contact.php" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-chat-dots me-2"></i>Nous contacter
                    </a>
                    <a href="tel:+237222222222" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-telephone me-2"></i>Appeler
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Styles spécifiques à la page guide */
    .guide-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .steps-container {
        position: relative;
        padding-left: 40px;
    }

    .steps-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 19px;
        width: 2px;
        background-color: rgba(var(--primary-rgb, 23, 116, 223), 0.2);
    }

    .step-item {
        position: relative;
    }

    .step-number {
        position: absolute;
        left: -40px;
        width: 40px;
        height: 40px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        z-index: 1;
    }

    .step-content {
        padding-bottom: 1.5rem;
    }

    .guide-img {
        max-width: 100%;
        height: auto;
    }

    .category-card:hover .card {
        border-color: var(--primary) !important;
    }

    .accordion-button:not(.collapsed) {
        background-color: rgba(23, 116, 223, 0.1);
        color: var(--primary);
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, 0.125);
    }

    .accordion-button::after {
        background-size: 1rem;
        transition: all 0.3s ease;
    }

    /* Animation pour les éléments AOS */
    [data-aos] {
        opacity: 0;
        transition-duration: 1s;
        transition-property: opacity, transform;
    }

    [data-aos].aos-animate {
        opacity: 1;
    }

    [data-aos="fade-up"] {
        transform: translateY(30px);
    }

    [data-aos="fade-up"].aos-animate {
        transform: translateY(0);
    }

    [data-aos="fade-right"] {
        transform: translateX(-30px);
    }

    [data-aos="fade-right"].aos-animate {
        transform: translateX(0);
    }

    [data-aos="fade-left"] {
        transform: translateX(30px);
    }

    [data-aos="fade-left"].aos-animate {
        transform: translateX(0);
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .guide-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            left: -37px;
        }
    }

    @media (max-width: 768px) {
        .steps-container {
            padding-left: 30px;
        }
        
        .steps-container::before {
            left: 14px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            left: -30px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .guide-icon {
            width: 45px;
            height: 45px;
            font-size: 1.1rem;
        }
    }
    </style>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de la bibliothèque AOS pour les animations au scroll
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
        
        // Smooth scroll pour les liens d'ancrage
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    const headerOffset = 100;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Gestion des accordéons FAQ
        const accordionButtons = document.querySelectorAll('.accordion-button');
        accordionButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Ajouter un petit délai pour l'animation
                setTimeout(() => {
                    // Faire défiler jusqu'à l'accordéon ouvert si nécessaire
                    if (!this.classList.contains('collapsed')) {
                        const accordionItem = this.closest('.accordion-item');
                        const topOffset = accordionItem.getBoundingClientRect().top + window.pageYOffset - 100;
                        
                        if (topOffset < window.pageYOffset || topOffset > window.pageYOffset + window.innerHeight) {
                            window.scrollTo({
                                top: topOffset,
                                behavior: 'smooth'
                            });
                        }
                    }
                }, 300);
            });
        });
    });
    </script>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
