<?php
// Session is initialized centrally in includes/config.php
require_once '../includes/config.php';
$pageTitle = "Foire Aux Questions - CNI.CAM";
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
                    <h1 class="display-4 fw-bold mb-4">Foire Aux Questions</h1>
                    <p class="lead mb-4">Trouvez rapidement des réponses à vos questions concernant les services de CNI.CAM.</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="/pages/services.php" class="text-white-50">Services</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">FAQ</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                    <img src="/assets/images/faq-hero.svg" alt="FAQ CNI.CAM" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Section de recherche -->
    <div class="bg-light py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="search-box p-2 bg-white rounded-pill shadow-sm" data-aos="fade-up">
                        <form id="faqSearchForm" class="d-flex align-items-center">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0">
                                    <i class="bi bi-search text-primary"></i>
                                </span>
                                <input type="text" id="faqSearch" class="form-control border-0 shadow-none" placeholder="Rechercher une question...">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Rechercher</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catégories de FAQ -->
    <div class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">Catégories</h2>
                <p class="lead text-muted">Explorez nos questions par thématique</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <!-- Catégorie 1 -->
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <a href="#cni" class="text-decoration-none category-card">
                        <div class="card h-100 border-0 shadow-sm hover-card rounded-4 text-center">
                            <div class="card-body p-4">
                                <div class="category-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                    <i class="bi bi-person-vcard"></i>
                                </div>
                                <h3 class="h5 mb-0">Carte Nationale d'Identité</h3>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Catégorie 2 -->
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <a href="#certificat" class="text-decoration-none category-card">
                        <div class="card h-100 border-0 shadow-sm hover-card rounded-4 text-center">
                            <div class="card-body p-4">
                                <div class="category-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                    <i class="bi bi-flag"></i>
                                </div>
                                <h3 class="h5 mb-0">Certificat de Nationalité</h3>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Catégorie 3 -->
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <a href="#paiement" class="text-decoration-none category-card">
                        <div class="card h-100 border-0 shadow-sm hover-card rounded-4 text-center">
                            <div class="card-body p-4">
                                <div class="category-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <h3 class="h5 mb-0">Paiement</h3>
                            </div>
                        </div>
                    </a>
                </div>
                
                <!-- Catégorie 4 -->
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <a href="#compte" class="text-decoration-none category-card">
                        <div class="card h-100 border-0 shadow-sm hover-card rounded-4 text-center">
                            <div class="card-body p-4">
                                <div class="category-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <h3 class="h5 mb-0">Compte et Profil</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions fréquentes par catégorie -->
    <div class="py-5 bg-light">
        <div class="container">
            <!-- CNI Section -->
            <div id="cni" class="mb-5 pt-3">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <div class="category-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <h2 class="h2 mb-0">Carte Nationale d'Identité</h2>
                </div>
                
                <div class="accordion faq-accordion" id="accordionCNI" data-aos="fade-up">
                    <!-- Question 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCNI1">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCNI1" aria-expanded="true" aria-controls="collapseCNI1">
                                Quels documents sont nécessaires pour une demande de CNI ?
                            </button>
                        </h2>
                        <div id="collapseCNI1" class="accordion-collapse collapse show" aria-labelledby="headingCNI1" data-bs-parent="#accordionCNI">
                            <div class="accordion-body">
                                <p>Pour une première demande de CNI, vous devez fournir :</p>
                                <ul>
                                    <li>Une copie d'acte de naissance</li>
                                    <li>Un certificat de nationalité camerounaise</li>
                                    <li>Une photo d'identité récente (format numérique)</li>
                                    <li>Une preuve de résidence (facture d'électricité, eau, etc.)</li>
                                    <li>Les empreintes digitales (à prendre lors du rendez-vous)</li>
                                </ul>
                                <p>Pour un renouvellement, vous devez ajouter une copie de l'ancienne CNI.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCNI2">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCNI2" aria-expanded="false" aria-controls="collapseCNI2">
                                Quel est le délai de traitement d'une demande de CNI ?
                            </button>
                        </h2>
                        <div id="collapseCNI2" class="accordion-collapse collapse" aria-labelledby="headingCNI2" data-bs-parent="#accordionCNI">
                            <div class="accordion-body">
                                <p>Le délai standard de traitement d'une demande de CNI est de 48 heurs à compter de la validation de votre dossier complet. Ce délai peut varier en fonction de :</p>
                                <ul>
                                    <li>La période de l'année (haute ou basse saison)</li>
                                    <li>La complétude de votre dossier</li>
                                    <li>La vérification des informations fournies</li>
                                </ul>
                                <p>Vous pouvez suivre l'avancement de votre demande en temps réel via votre espace personnel sur notre plateforme.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCNI3">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCNI3" aria-expanded="false" aria-controls="collapseCNI3">
                                Que faire en cas de perte de ma CNI ?
                            </button>
                        </h2>
                        <div id="collapseCNI3" class="accordion-collapse collapse" aria-labelledby="headingCNI3" data-bs-parent="#accordionCNI">
                            <div class="accordion-body">
                                <p>En cas de perte de votre CNI, vous devez :</p>
                                <ol>
                                    <li>Faire une déclaration de perte en ligne via notre service dédié</li>
                                    <li>Payer les frais de déclaration (1 000 FCFA)</li>
                                    <li>Télécharger l'attestation de perte générée</li>
                                    <li>Initier une demande de renouvellement de CNI</li>
                                </ol>
                                <p>L'attestation de perte est un document officiel qui peut vous servir temporairement en attendant votre nouvelle CNI.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCNI4">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCNI4" aria-expanded="false" aria-controls="collapseCNI4">
                                Quelle est la durée de validité d'une CNI ?
                            </button>
                        </h2>
                        <div id="collapseCNI4" class="accordion-collapse collapse" aria-labelledby="headingCNI4" data-bs-parent="#accordionCNI">
                            <div class="accordion-body">
                                <p>La Carte Nationale d'Identité camerounaise a une durée de validité de 10 ans à compter de sa date d'émission.</p>
                                <p>Il est recommandé d'entamer les démarches de renouvellement environ 3 mois avant la date d'expiration pour éviter tout désagrément.</p>
                                <p>Une CNI expirée n'est plus un document d'identité valide et peut vous exposer à des sanctions en cas de contrôle.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 5 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCNI5">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCNI5" aria-expanded="false" aria-controls="collapseCNI5">
                                Comment prendre rendez-vous pour la biométrie ?
                            </button>
                        </h2>
                        <div id="collapseCNI5" class="accordion-collapse collapse" aria-labelledby="headingCNI5" data-bs-parent="#accordionCNI">
                            <div class="accordion-body">
                                <p>Pour prendre rendez-vous pour la capture de vos données biométriques :</p>
                                <ol>
                                    <li>Connectez-vous à votre compte CNI.CAM</li>
                                    <li>Accédez au service "Rendez-vous en ligne"</li>
                                    <li>Sélectionnez le centre de capture le plus proche</li>
                                    <li>Choisissez une date et un créneau horaire disponible</li>
                                    <li>Confirmez votre rendez-vous</li>
                                </ol>
                                <p>Vous recevrez une confirmation par email et SMS. Présentez-vous au centre à l'heure indiquée avec vos documents originaux.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Certificat de Nationalité Section -->
            <div id="certificat" class="mb-5 pt-3">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <div class="category-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-flag"></i>
                    </div>
                    <h2 class="h2 mb-0">Certificat de Nationalité</h2>
                </div>
                
                <div class="accordion faq-accordion" id="accordionCertificat" data-aos="fade-up">
                    <!-- Question 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCert1">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCert1" aria-expanded="true" aria-controls="collapseCert1">
                                Quels documents faut-il pour demander un certificat de nationalité ?
                            </button>
                        </h2>
                        <div id="collapseCert1" class="accordion-collapse collapse show" aria-labelledby="headingCert1" data-bs-parent="#accordionCertificat">
                            <div class="accordion-body">
                                <p>Pour demander un certificat de nationalité camerounaise, vous devez fournir :</p>
                                <ul>
                                    <li>Une copie intégrale de votre acte de naissance</li>
                                    <li>Une copie de la CNI (si vous en possédez une)</li>
                                    <li>Une copie de l'acte de naissance de l'un de vos parents camerounais</li>
                                    <li>Une copie de la CNI de l'un de vos parents camerounais</li>
                                    <li>Un justificatif de domicile</li>
                                    <li>Une photo d'identité récente</li>
                                    <li>Le formulaire de demande dûment rempli</li>
                                </ul>
                                <p>Des documents supplémentaires peuvent être demandés selon votre situation particulière (naturalisation, adoption, etc.).</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCert2">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCert2" aria-expanded="false" aria-controls="collapseCert2">
                                Quelle est la durée de validité d'un certificat de nationalité ?
                            </button>
                        </h2>
                        <div id="collapseCert2" class="accordion-collapse collapse" aria-labelledby="headingCert2" data-bs-parent="#accordionCertificat">
                            <div class="accordion-body">
                                <p>Le certificat de nationalité camerounaise n'a pas de durée de validité limitée. Il reste valable tant que votre statut de nationalité ne change pas.</p>
                                <p>Cependant, pour certaines démarches administratives, un certificat récent (moins de 3 mois) peut être exigé. Il est donc parfois nécessaire de demander un nouveau certificat même si vous en possédez déjà un ancien.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCert3">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCert3" aria-expanded="false" aria-controls="collapseCert3">
                                Quel est le délai d'obtention d'un certificat de nationalité ?
                            </button>
                        </h2>
                        <div id="collapseCert3" class="accordion-collapse collapse" aria-labelledby="headingCert3" data-bs-parent="#accordionCertificat">
                            <div class="accordion-body">
                                <p>Le délai standard pour l'obtention d'un certificat de nationalité est de 7 à 15 jours ouvrables après la validation de votre dossier complet.</p>
                                <p>Ce délai peut varier en fonction de :</p>
                                <ul>
                                    <li>La complexité de votre situation</li>
                                    <li>La nécessité de vérifications supplémentaires</li>
                                    <li>La charge de travail des services concernés</li>
                                </ul>
                                <p>Vous pouvez suivre l'état d'avancement de votre demande dans votre espace personnel sur notre plateforme.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCert4">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCert4" aria-expanded="false" aria-controls="collapseCert4">
                                Puis-je demander un certificat de nationalité pour un mineur ?
                            </button>
                        </h2>
                        <div id="collapseCert4" class="accordion-collapse collapse" aria-labelledby="headingCert4" data-bs-parent="#accordionCertificat">
                            <div class="accordion-body">
                                <p>Oui, un parent ou tuteur légal peut demander un certificat de nationalité pour un mineur. Dans ce cas, vous devrez fournir :</p>
                                <ul>
                                    <li>L'acte de naissance du mineur</li>
                                    <li>Une preuve de votre autorité parentale ou tutelle (livret de famille, jugement de tutelle, etc.)</li>
                                    <li>Votre propre pièce d'identité</li>
                                    <li>Les documents prouvant la nationalité camerounaise du mineur (acte de naissance des parents, etc.)</li>
                                </ul>
                                <p>La demande doit être signée par le représentant légal du mineur.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Paiement Section -->
            <div id="paiement" class="mb-5 pt-3">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <div class="category-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h2 class="h2 mb-0">Paiement</h2>
                </div>
                
                <div class="accordion faq-accordion" id="accordionPaiement" data-aos="fade-up">
                    <!-- Question 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingPay1">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePay1" aria-expanded="true" aria-controls="collapsePay1">
                                Quels sont les moyens de paiement acceptés ?
                            </button>
                        </h2>
                        <div id="collapsePay1" class="accordion-collapse collapse show" aria-labelledby="headingPay1" data-bs-parent="#accordionPaiement">
                            <div class="accordion-body">
                                <p>Nous acceptons plusieurs moyens de paiement pour régler les frais administratifs :</p>
                                <ul>
                                    <li><strong>Mobile Money</strong> : Orange Money, MTN Mobile Money, etc.</li>
                                    <li><strong>Cartes bancaires</strong> : Visa, Mastercard, etc.</li>
                                    <li><strong>Virements bancaires</strong> : pour les paiements de montants importants</li>
                                    <li><strong>Paiement en espèces</strong> : uniquement dans nos centres agréés</li>
                                </ul>
                                <p>Les paiements en ligne sont sécurisés et vous recevez immédiatement une confirmation par email et SMS.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingPay2">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePay2" aria-expanded="false" aria-controls="collapsePay2">
                                Comment obtenir un reçu de paiement ?
                            </button>
                        </h2>
                        <div id="collapsePay2" class="accordion-collapse collapse" aria-labelledby="headingPay2" data-bs-parent="#accordionPaiement">
                            <div class="accordion-body">
                                <p>Après chaque paiement effectué sur notre plateforme, vous recevez automatiquement :</p>
                                <ul>
                                    <li>Un reçu électronique envoyé à votre adresse email</li>
                                    <li>Une confirmation par SMS avec votre numéro de référence</li>
                                </ul>
                                <p>Vous pouvez également :</p>
                                <ul>
                                    <li>Télécharger votre reçu depuis votre espace personnel dans la section "Mes paiements"</li>
                                    <li>Demander un duplicata en contactant notre service client</li>
                                </ul>
                                <p>Conservez précieusement vos reçus, ils peuvent être nécessaires en cas de réclamation.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingPay3">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePay3" aria-expanded="false" aria-controls="collapsePay3">
                                Que faire en cas d'échec de paiement ?
                            </button>
                        </h2>
                        <div id="collapsePay3" class="accordion-collapse collapse" aria-labelledby="headingPay3" data-bs-parent="#accordionPaiement">
                            <div class="accordion-body">
                                <p>Si votre paiement échoue, voici les étapes à suivre :</p>
                                <ol>
                                    <li>Vérifiez que votre compte bancaire ou mobile money dispose de fonds suffisants</li>
                                    <li>Assurez-vous que vos informations de paiement sont correctes</li>
                                    <li>Attendez quelques minutes et essayez à nouveau</li>
                                    <li>Essayez un autre moyen de paiement si possible</li>
                                </ol>
                                <p>Si le problème persiste :</p>
                                <ul>
                                    <li>Contactez notre service client au (+237) 222 222 222</li>
                                    <li>Envoyez un email à paiement@cni.cam en précisant votre numéro de demande</li>
                                    <li>Rendez-vous dans l'un de nos centres pour un paiement en personne</li>
                                </ul>
                                <p>Important : En cas de débit sans confirmation, le montant sera automatiquement remboursé sous 5 à 10 jours ouvrables.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Compte et Profil Section -->
            <div id="compte" class="mb-5 pt-3">
                <div class="d-flex align-items-center mb-4" data-aos="fade-up">
                    <div class="category-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h2 class="h2 mb-0">Compte et Profil</h2>
                </div>
                
                <div class="accordion faq-accordion" id="accordionCompte" data-aos="fade-up">
                    <!-- Question 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCompte1">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompte1" aria-expanded="true" aria-controls="collapseCompte1">
                                Comment créer un compte sur CNI.CAM ?
                            </button>
                        </h2>
                        <div id="collapseCompte1" class="accordion-collapse collapse show" aria-labelledby="headingCompte1" data-bs-parent="#accordionCompte">
                            <div class="accordion-body">
                                <p>Pour créer un compte sur CNI.CAM, suivez ces étapes :</p>
                                <ol>
                                    <li>Rendez-vous sur la page d'accueil de CNI.CAM</li>
                                    <li>Cliquez sur le bouton "Inscription" en haut à droite</li>
                                    <li>Remplissez le formulaire avec vos informations personnelles</li>
                                    <li>Créez un mot de passe sécurisé</li>
                                    <li>Acceptez les conditions d'utilisation et la politique de confidentialité</li>
                                    <li>Cliquez sur "Créer mon compte"</li>
                                    <li>Vérifiez votre adresse email en cliquant sur le lien reçu</li>
                                    <li>Complétez votre profil avec les informations supplémentaires demandées</li>
                                </ol>
                                <p>Une fois ces étapes terminées, vous pourrez accéder à tous nos services en ligne.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCompte2">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompte2" aria-expanded="false" aria-controls="collapseCompte2">
                                Comment modifier mes informations personnelles ?
                            </button>
                        </h2>
                        <div id="collapseCompte2" class="accordion-collapse collapse" aria-labelledby="headingCompte2" data-bs-parent="#accordionCompte">
                            <div class="accordion-body">
                                <p>Pour modifier vos informations personnelles :</p>
                                <ol>
                                    <li>Connectez-vous à votre compte CNI.CAM</li>
                                    <li>Cliquez sur votre nom d'utilisateur en haut à droite</li>
                                    <li>Sélectionnez "Mon profil" dans le menu déroulant</li>
                                    <li>Cliquez sur le bouton "Modifier" à côté de la section que vous souhaitez mettre à jour</li>
                                    <li>Effectuez vos modifications</li>
                                    <li>Cliquez sur "Enregistrer"</li>
                                </ol>
                                <p><strong>Important</strong> : Certaines informations critiques (nom, date de naissance, etc.) ne peuvent pas être modifiées directement. Pour ces changements, vous devrez contacter notre service client avec les justificatifs appropriés.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCompte3">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompte3" aria-expanded="false" aria-controls="collapseCompte3">
                                J'ai oublié mon mot de passe, que faire ?
                            </button>
                        </h2>
                        <div id="collapseCompte3" class="accordion-collapse collapse" aria-labelledby="headingCompte3" data-bs-parent="#accordionCompte">
                            <div class="accordion-body">
                                <p>Si vous avez oublié votre mot de passe, voici la procédure à suivre :</p>
                                <ol>
                                    <li>Sur la page de connexion, cliquez sur "Mot de passe oublié"</li>
                                    <li>Entrez l'adresse email associée à votre compte</li>
                                    <li>Cliquez sur "Réinitialiser mon mot de passe"</li>
                                    <li>Consultez votre boîte de réception (et éventuellement vos spams)</li>
                                    <li>Cliquez sur le lien de réinitialisation reçu par email</li>
                                    <li>Créez un nouveau mot de passe sécurisé</li>
                                    <li>Confirmez votre nouveau mot de passe</li>
                                </ol>
                                <p>Le lien de réinitialisation est valable pendant 24 heures. Si vous ne recevez pas l'email dans les 15 minutes, vérifiez vos spams ou contactez notre service client.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingCompte4">
                        <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompte4" aria-expanded="false" aria-controls="collapseCompte4">
                                Comment supprimer mon compte ?
                            </button>
                        </h2>
                        <div id="collapseCompte4" class="accordion-collapse collapse" aria-labelledby="headingCompte4" data-bs-parent="#accordionCompte">
                            <div class="accordion-body">
                                <p>La suppression d'un compte CNI.CAM nécessite une vérification, car certaines données doivent être conservées conformément à la législation en vigueur. Voici la procédure :</p>
                                <ol>
                                    <li>Connectez-vous à votre compte</li>
                                    <li>Accédez à la section "Paramètres"</li>
                                    <li>Faites défiler jusqu'à "Supprimer mon compte"</li>
                                    <li>Cliquez sur "Demander la suppression"</li>
                                    <li>Lisez attentivement les informations sur les conséquences de cette action</li>
                                    <li>Confirmez votre demande en saisissant votre mot de passe</li>
                                    <li>Indiquez la raison de votre demande de suppression</li>
                                </ol>
                                <p><strong>Important</strong> : La suppression n'est pas immédiate. Votre compte sera d'abord désactivé pendant 30 jours, période durant laquelle vous pourrez annuler votre demande. Certaines données liées à vos documents officiels seront conservées conformément aux obligations légales.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Autres questions -->
    <div class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold">Autres questions</h2>
                <p class="lead text-muted">Vous n'avez pas trouvé la réponse à votre question ?</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="other-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h3 class="h4 mb-3">Contactez-nous</h3>
                            <p class="text-muted mb-4">Notre équipe de support est disponible pour répondre à toutes vos questions.</p>
                            <a href="/pages/contact.php" class="btn btn-outline-primary rounded-pill px-4">
                                <i class="bi bi-envelope me-2"></i>Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="other-icon bg-success bg-opacity-10 text-success mx-auto mb-4">
                                <i class="bi bi-book"></i>
                            </div>
                            <h3 class="h4 mb-3">Guide d'utilisation</h3>
                            <p class="text-muted mb-4">Consultez notre guide détaillé pour mieux comprendre nos services.</p>
                            <a href="/pages/guide.php" class="btn btn-outline-success rounded-pill px-4">
                                <i class="bi bi-file-earmark-text me-2"></i>Voir le guide
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="other-icon bg-info bg-opacity-10 text-info mx-auto mb-4">
                                <i class="bi bi-headset"></i>
                            </div>
                            <h3 class="h4 mb-3">Assistance téléphonique</h3>
                            <p class="text-muted mb-4">Appelez notre service client pour une assistance immédiate.</p>
                            <a href="tel:+237222222222" class="btn btn-outline-info rounded-pill px-4">
                                <i class="bi bi-telephone me-2"></i>+237 222 222 222
                            </a>
                        </div>
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
                    <h2 class="display-5 fw-bold mb-3">Prêt à commencer ?</h2>
                    <p class="lead mb-0">Créez votre compte et accédez à tous nos services numériques dès maintenant.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="/pages/register.php" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-person-plus me-2"></i>Créer un compte
                    </a>
                    <a href="/pages/login.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Styles spécifiques à la page FAQ */
    .category-icon, .other-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto;
    }

    .hover-card {
        transition: all 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-10px);
    }

    .category-card:hover .card {
        border-color: var(--primary) !important;
    }

    .search-box {
        transition: all 0.3s ease;
    }

    .search-box:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
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
        .category-icon, .other-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .category-card {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .category-icon, .other-icon {
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
        
        // Gestion de la recherche dans la FAQ
        const faqSearch = document.getElementById('faqSearch');
        const faqSearchForm = document.getElementById('faqSearchForm');
        const accordionItems = document.querySelectorAll('.accordion-item');
        
        faqSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            searchFaq();
        });
        
        faqSearch.addEventListener('keyup', function() {
            searchFaq();
        });
        
        function searchFaq() {
            const searchTerm = faqSearch.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                // Réinitialiser l'affichage si le champ de recherche est vide
                accordionItems.forEach(item => {
                    item.style.display = 'block';
                    
                    // Fermer tous les accordéons sauf le premier de chaque section
                    const button = item.querySelector('.accordion-button');
                    const collapse = item.querySelector('.accordion-collapse');
                    
                    if (!button.classList.contains('first-item')) {
                        button.classList.add('collapsed');
                        collapse.classList.remove('show');
                    }
                });
                return;
            }
            
            let foundItems = 0;
            
            accordionItems.forEach(item => {
                const questionText = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answerText = item.querySelector('.accordion-body').textContent.toLowerCase();
                
                if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                    item.style.display = 'block';
                    
                    // Ouvrir l'accordéon qui correspond à la recherche
                    const button = item.querySelector('.accordion-button');
                    const collapse = item.querySelector('.accordion-collapse');
                    
                    button.classList.remove('collapsed');
                    collapse.classList.add('show');
                    
                    // Mettre en surbrillance les termes de recherche (optionnel)
                    // Cette fonctionnalité nécessiterait une implémentation plus complexe
                    
                    foundItems++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Afficher un message si aucun résultat n'est trouvé
            if (foundItems === 0) {
                // Vous pourriez ajouter un élément HTML pour afficher ce message
                console.log('Aucun résultat trouvé pour: ' + searchTerm);
            }
        }
        
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
        
        // Animation des cartes au survol
        const hoverCards = document.querySelectorAll('.hover-card');
        hoverCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.05)';
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
