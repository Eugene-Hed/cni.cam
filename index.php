<?php
include('includes/config.php');
include('includes/header.php');
include('includes/navbar.php');
?>

<!-- Hero Section avec animation et appel à l'action optimisé -->
<section class="hero position-relative overflow-hidden">
    <div class="hero-bg-animation"></div>
    <div class="hero-overlay"></div>
    <div class="container position-relative py-5">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <h1 class="display-4 fw-bold text-white mb-3">Gestion Numérique des <span class="text-warning">CNI</span></h1>
                <p class="lead text-white-90 mb-4">Simplifiez vos démarches administratives avec la plateforme officielle de gestion des Cartes Nationales d'Identité du Cameroun.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="pages/register.php" class="btn btn-primary btn-lg px-4 shadow-sm">
                        <i class="bi bi-person-plus me-2"></i>Créer un compte
                    </a>
                    <a href="pages/login.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </a>
                </div>
                <div class="mt-4 d-flex align-items-center">
                    <div class="d-flex">
                        <img src="assets/images/user-1.jpg" class="rounded-circle border border-2 border-white shadow" width="40" height="40" alt="Utilisateur" style="margin-left: -10px; z-index: 3;">
                        <img src="assets/images/user-2.jpg" class="rounded-circle border border-2 border-white shadow" width="40" height="40" alt="Utilisateur" style="margin-left: -10px; z-index: 2;">
                        <img src="assets/images/user-3.jpg" class="rounded-circle border border-2 border-white shadow" width="40" height="40" alt="Utilisateur" style="margin-left: -10px; z-index: 1;">
                    </div>
                    <div class="ms-3 text-white-90">
                        <span class="fw-bold">+10,000</span> citoyens nous font confiance
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left" data-aos-delay="300">
                <div class="position-relative" style="animation: float 3s ease-in-out infinite;">
                    <img src="assets/images/Cameroun.png" alt="CNI Cameroun" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute top-0 end-0 translate-middle-y bg-white p-3 rounded-4 shadow-lg d-flex align-items-center" style="max-width: 200px;">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Processus simplifié</h6>
                            <p class="small text-muted mb-0">Délai réduit de 70%</p>
                        </div>
                    </div>
                    <div class="position-absolute bottom-0 start-0 translate-middle-y bg-white p-3 rounded-4 shadow-lg d-flex align-items-center" style="max-width: 200px;">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-shield-check text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">100% Sécurisé</h6>
                            <p class="small text-muted mb-0">Données protégées</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100">
            <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,42.7C1120,32,1280,32,1360,32L1440,32L1440,100L1360,100C1280,100,1120,100,960,100C800,100,640,100,480,100C320,100,160,100,80,100L0,100Z"></path>
        </svg>
    </div>
</section>

<!-- Statistiques Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <div class="p-4 rounded-4 bg-light">
                    <div class="display-5 fw-bold text-primary mb-2 counter">500K+</div>
                    <p class="mb-0 text-muted">Citoyens inscrits</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <div class="p-4 rounded-4 bg-light">
                    <div class="display-5 fw-bold text-success mb-2 counter">98%</div>
                    <p class="mb-0 text-muted">Taux de satisfaction</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <div class="p-4 rounded-4 bg-light">
                    <div class="display-5 fw-bold text-info mb-2 counter">24/7</div>
                    <p class="mb-0 text-muted">Service disponible</p>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                <div class="p-4 rounded-4 bg-light">
                    <div class="display-5 fw-bold text-warning mb-2 counter">-70%</div>
                    <p class="mb-0 text-muted">Temps de traitement</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Assistant IA -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="ai-avatar bg-primary bg-opacity-10 p-3 rounded-circle me-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-robot fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Assistant CNI.CAM</h4>
                                <p class="text-muted mb-0">Je réponds à vos questions 24h/24</p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-success px-3 py-2 rounded-pill">En ligne</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div id="chat-container" class="chat-container mb-3 p-3 bg-light rounded-3" style="height: 320px; overflow-y: auto;">
                            <div class="chat-message assistant">
                                <div class="message-content">
                                    <p class="mb-0">👋 Bonjour ! Je suis votre assistant virtuel CNI.CAM.</p>
                                    <p class="mb-0">Comment puis-je vous aider aujourd'hui ?</p>
                                </div>
                                <small class="text-muted d-block mt-1">À l'instant</small>
                            </div>
                            <div id="typing-indicator" class="chat-message assistant d-none">
                                <div class="message-content">
                                    <div class="typing-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form id="chat-form" class="d-flex gap-2">
                            <input type="text" id="user-input" class="form-control form-control-lg" 
                                   placeholder="Posez votre question..." autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                        <div class="mt-3">
                            <p class="small text-muted mb-1">Questions fréquentes :</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Comment demander une CNI ?</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Délai d'obtention ?</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Documents requis ?</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Coût d'une CNI ?</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Que faire en cas de perte ?</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-question">Validité de la CNI ?</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-2">Nos Services</span>
            <h2 class="display-5 fw-bold">Solutions Numériques</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Découvrez nos services numériques conçus pour simplifier vos démarches administratives</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4">
                        <div class="service-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-4 d-inline-flex">
                            <i class="bi bi-person-vcard fs-3"></i>
                        </div>
                        <h3 class="h4 mb-3">Carte Nationale d'Identité</h3>
                        <p class="text-muted mb-4">Demandez ou renouvelez votre CNI en quelques clics. Processus simplifié et suivi en temps réel.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="pages/login.php" class="btn btn-outline-primary rounded-pill px-4">
                                Commencer <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">Populaire</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4">
                        <div class="service-icon bg-success bg-opacity-10 text-success rounded-circle p-3 mb-4 d-inline-flex">
                            <i class="bi bi-flag fs-3"></i>
                        </div>
                        <h3 class="h4 mb-3">Certificat de Nationalité</h3>
                        <p class="text-muted mb-4">Obtenez votre certificat de nationalité en ligne. Procédure sécurisée et vérification rapide.</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="pages/login.php" class="btn btn-outline-success rounded-pill px-4">
                                Commencer <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">Nouveau</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4">
                        <div class="service-icon bg-info bg-opacity-10 text-info rounded-circle p-3 mb-4 d-inline-flex">
                            <i class="bi bi-search fs-3"></i>
                        </div>
                        <h3 class="h4 mb-3">Suivi de Demande</h3>
                        <p class="text-muted mb-4">Suivez l'état de vos demandes en temps réel. Notifications automatiques à chaque étape du processus.</p>
                        <div class="d-flex justify-content-between align-items-center">
                        <a href="pages/login.php" class="btn btn-outline-info rounded-pill px-4">
                                Commencer <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">Pratique</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Process Section amélioré -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-2">Processus</span>
            <h2 class="display-5 fw-bold">Comment ça marche ?</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Un processus simple en 4 étapes pour obtenir votre document</p>
        </div>
        
        <div class="position-relative">
            <!-- Ligne de connexion entre les étapes -->
            <div class="process-line d-none d-md-block"></div>
            
            <div class="row">
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="process-card text-center">
                        <div class="process-icon bg-primary text-white mx-auto mb-4">
                            <span class="h3">1</span>
                        </div>
                        <h4 class="mb-3">Créez votre compte</h4>
                        <p class="text-muted">Inscrivez-vous en quelques minutes avec vos informations personnelles</p>
                        <div class="mt-3">
                            <a href="pages/register.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                S'inscrire <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="process-card text-center">
                        <div class="process-icon bg-primary text-white mx-auto mb-4">
                            <span class="h3">2</span>
                        </div>
                        <h4 class="mb-3">Soumettez votre demande</h4>
                        <p class="text-muted">Remplissez le formulaire en ligne et téléchargez les documents requis</p>
                        <div class="mt-3">
                            <a href="pages/login.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                Commencer <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="process-card text-center">
                        <div class="process-icon bg-primary text-white mx-auto mb-4">
                            <span class="h3">3</span>
                        </div>
                        <h4 class="mb-3">Suivez votre dossier</h4>
                        <p class="text-muted">Consultez l'état d'avancement et recevez des notifications en temps réel</p>
                        <div class="mt-3">
                            <a href="pages/login.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                Suivre <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="process-card text-center">
                        <div class="process-icon bg-success text-white mx-auto mb-4">
                            <span class="h3">4</span>
                        </div>
                        <h4 class="mb-3">Récupérez votre document</h4>
                        <p class="text-muted">Retirez votre document final au centre indiqué ou recevez-le par courrier</p>
                        <div class="mt-3">
                            <a href="pages/login.php" class="btn btn-sm btn-outline-success rounded-pill">
                                En savoir plus <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Témoignages Section (Nouvelle) -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill mb-2">Témoignages</span>
            <h2 class="display-5 fw-bold">Ce que disent nos utilisateurs</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Découvrez les expériences de citoyens qui ont utilisé notre plateforme</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm rounded-4 testimonial-card">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-4 testimonial-text">"J'ai pu renouveler ma CNI en un temps record. Le processus était simple et j'ai reçu des notifications à chaque étape. Merci pour ce service qui m'a fait économiser beaucoup de temps !"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/testimonial-1.jpg" alt="Témoignage" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h5 class="mb-0">Marie Nguemo</h5>
                                <p class="small text-muted mb-0">Douala, Cameroun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm rounded-4 testimonial-card">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-4 testimonial-text">"En tant qu'entrepreneur, je n'avais pas le temps de faire la queue pendant des heures. Cette plateforme m'a permis d'obtenir mon certificat de nationalité rapidement. Service exceptionnel !"</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/testimonial-2.jpg" alt="Témoignage" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h5 class="mb-0">Jean Mbarga</h5>
                                <p class="small text-muted mb-0">Yaoundé, Cameroun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm rounded-4 testimonial-card">
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-half text-warning"></i>
                        </div>
                        <p class="mb-4 testimonial-text">"L'assistant virtuel m'a guidé tout au long du processus. J'ai apprécié la transparence et la facilité d'utilisation. Je recommande vivement ce service à tous les citoyens camerounais."</p>
                        <div class="d-flex align-items-center">
                            <img src="assets/images/testimonial-3.jpg" alt="Témoignage" class="rounded-circle me-3" width="50" height="50">
                            <div>
                                <h5 class="mb-0">Sophie Ekambi</h5>
                                <p class="small text-muted mb-0">Bafoussam, Cameroun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2">
                Voir plus de témoignages <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- FAQ Section (Nouvelle) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill mb-2">FAQ</span>
            <h2 class="display-5 fw-bold">Questions fréquentes</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Trouvez rapidement des réponses à vos questions</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion" data-aos="fade-up">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Quels documents sont nécessaires pour demander une CNI ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Pour demander une Carte Nationale d'Identité, vous devez fournir les documents suivants : un acte de naissance, un certificat de nationalité, une photo d'identité récente, et une preuve de résidence. Tous ces documents peuvent être téléchargés directement sur notre plateforme.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Quel est le délai d'obtention d'une CNI ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Grâce à notre plateforme numérique, le délai d'obtention d'une CNI a été considérablement réduit. En moyenne, vous pouvez obtenir votre CNI dans un délai de 7 à 14 jours ouvrables, contre 30 à 60 jours auparavant. Vous recevrez des notifications à chaque étape du processus.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Comment suivre l'état d'avancement de ma demande ?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Une fois votre demande soumise, vous pouvez suivre son état d'avancement en vous connectant à votre compte sur notre plateforme. Vous aurez accès à un tableau de bord qui affiche le statut actuel de votre demande. De plus, vous recevrez des notifications par email et SMS à chaque étape importante du processus.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Puis-je demander une CNI si je suis à l'étranger ?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Oui, notre plateforme permet aux Camerounais résidant à l'étranger de demander leur CNI. Vous devrez soumettre votre demande en ligne et prendre rendez-vous à l'ambassade ou au consulat du Cameroun dans votre pays de résidence pour la prise d'empreintes et la vérification de vos documents originaux.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Comment payer les frais de demande ?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous proposons plusieurs options de paiement sécurisées : par carte bancaire, par mobile money (Orange Money, MTN Mobile Money), ou par virement bancaire. Une fois le paiement effectué, vous recevrez immédiatement une confirmation et pourrez poursuivre votre demande.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="pages/faq.php" class="btn btn-outline-primary rounded-pill px-4 py-2">
                        Voir toutes les questions <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section (Nouvelle) -->
<section class="py-5 bg-primary text-white position-relative overflow-hidden">
    <div class="cta-shapes">
        <div class="shape-1"></div>
        <div class="shape-2"></div>
        <div class="shape-3"></div>
    </div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0" data-aos="fade-right">
                <h2 class="display-4 fw-bold mb-3">Prêt à commencer ?</h2>
                <p class="lead mb-4">Rejoignez des milliers de citoyens qui ont simplifié leurs démarches administratives grâce à notre plateforme numérique.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="pages/register.php" class="btn btn-light btn-lg px-4 shadow-sm">
                        <i class="bi bi-person-plus me-2"></i>Créer un compte
                    </a>
                    <a href="pages/login.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left">
                <img src="assets/images/cni.webp" alt="Illustration" class="img-fluid" style="max-height: 300px;">
            </div>
        </div>
    </div>
</section>

<!-- Partenaires Section (Nouvelle) -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill mb-2">Partenaires</span>
            <h2 class="h3 fw-bold">Ils nous font confiance</h2>
        </div>
        
        <div class="row align-items-center justify-content-center">
            <div class="col-md-2 col-4 text-center mb-4" data-aos="fade-up" data-aos-delay="100">
                <img src="assets/images/partners/partner-1.png" alt="Partenaire" class="img-fluid opacity-75 partner-logo">
            </div>
            <div class="col-md-2 col-4 text-center mb-4" data-aos="fade-up" data-aos-delay="200">
                <img src="assets/images/partners/partner-2.jpg" alt="Partenaire" class="img-fluid opacity-75 partner-logo">
            </div>
            <div class="col-md-2 col-4 text-center mb-4" data-aos="fade-up" data-aos-delay="300">
                <img src="assets/images/partners/partner-3.jpeg" alt="Partenaire" class="img-fluid opacity-75 partner-logo">
            </div>
            <div class="col-md-2 col-4 text-center mb-4" data-aos="fade-up" data-aos-delay="400">
                <img src="assets/images/partners/partner-4.png" alt="Partenaire" class="img-fluid opacity-75 partner-logo">
            </div>
            <div class="col-md-2 col-4 text-center mb-4" data-aos="fade-up" data-aos-delay="500">
                <img src="assets/images/partners/partner-5.png" alt="Partenaire" class="img-fluid opacity-75 partner-logo">
            </div>
        </div>
    </div>
</section>

<style>
/* Styles améliorés pour la page d'accueil */
:root {
    --primary-color: #1774df;
    --primary-dark: #135bb2;
    --primary-light: #e8f1fd;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --dark-color: #343a40;
    --light-color: #f8f9fa;
}

/* Hero Section */
.hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    min-height: 85vh;
    position: relative;
}

.hero-bg-animation {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('assets/images/pattern.png') repeat;
    opacity: 0.1;
    animation: slide 20s linear infinite;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at top right, rgba(23, 116, 223, 0.7) 0%, rgba(19, 91, 178, 0.9) 100%);
}

.hero-wave {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
}

.text-white-90 {
    color: rgba(255, 255, 255, 0.9);
}

/* Services Cards */
.hover-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.hover-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}

.service-icon {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Process Section */
.process-line {
    position: absolute;
    top: 80px;
    left: 50%;
    width: 75%;
    height: 3px;
    background-color: var(--primary-light);
    transform: translateX(-50%);
    z-index: 0;
}

.process-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.process-card {
    padding: 20px;
    transition: all 0.3s ease;
}

.process-card:hover {
    transform: translateY(-5px);
}

/* Chat Container */
.chat-container {
    border-radius: 15px;
    background-color: #f8f9fa !important;
}

.chat-message {
    margin-bottom: 15px;
    display: flex;
    flex-direction: column;
}

.chat-message.user {
    align-items: flex-end;
}

.chat-message.assistant {
    align-items: flex-start;
}

.message-content {
    padding: 12px 16px;
    border-radius: 18px;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    display: inline-block;
    max-width: 80%;
}

.chat-message.user .message-content {
    background: var(--primary-color);
    color: white;
    border-bottom-right-radius: 5px;
}

.chat-message.assistant .message-content {
    background: white;
    border-bottom-left-radius: 5px;
}

.typing-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    background-color: #adb5bd;
    border-radius: 50%;
    display: inline-block;
    animation: typing 1.4s infinite both;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* Testimonial Cards */
.testimonial-card {
    transition: all 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

.testimonial-text {
    position: relative;
    font-style: italic;
}

.testimonial-text::before {
    content: '"';
    font-size: 3rem;
    position: absolute;
    top: -20px;
    left: -10px;
    color: rgba(0, 0, 0, 0.05);
    font-family: serif;
}

/* FAQ Accordion */
.accordion-button:not(.collapsed) {
    background-color: var(--primary-light);
    color: var(--primary-color);
    font-weight: 600;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(23, 116, 223, 0.25);
}

/* CTA Section */
.cta-shapes .shape-1 {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
}

.cta-shapes .shape-2 {
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.05);
}

.cta-shapes .shape-3 {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.08);
    transform: translate(-50%, -50%);
}

/* Partner Logos */
.partner-logo {
    transition: all 0.3s ease;
    max-height: 60px;
}

.partner-logo:hover {
    opacity: 1 !important;
    transform: scale(1.1);
}

/* Animations */
@keyframes slide {
    0% { background-position: 0 0; }
    100% { background-position: 100% 100%; }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}


[data-aos] {
    opacity: 0;
    transition-duration: 1s;
    transition-property: opacity, transform;
}

[data-aos].aos-animate {
    opacity: 1;
}

/* Counter Animation */
.counter {
    display: inline-block;
    position: relative;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .hero {
        min-height: 70vh;
    }
    
    .process-line {
        display: none !important;
    }
    
    .process-card {
        margin-bottom: 30px;
    }
}

@media (max-width: 768px) {
    .hero {
        min-height: auto;
        padding: 80px 0;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
    
    .lead {
        font-size: 1rem;
    }
}
</style>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
// Initialiser AOS (Animate On Scroll)
AOS.init({
    duration: 800,
    once: true,
    offset: 100
});

// Chat functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const chatContainer = document.getElementById('chat-container');
    const quickQuestions = document.querySelectorAll('.quick-question');
    
    // Fonction pour ajouter un message au chat
    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;
        
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <p class="mb-0">${text}</p>
            </div>
            <small class="text-muted d-block mt-1">${time}</small>
        `;
        
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Fonction pour afficher l'indicateur de frappe
    function showTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator.classList.remove('d-none');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Fonction pour masquer l'indicateur de frappe
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        indicator.classList.add('d-none');
    }
    
    // Fonction pour générer une réponse de l'assistant
    function generateResponse(message) {
        // Réponses prédéfinies basées sur des mots-clés
        const responses = {
    'bonjour': 'Bonjour ! Comment puis-je vous aider aujourd\'hui ?',
    'salut': 'Salut ! Comment puis-je vous aider aujourd\'hui ?',
    'cni': 'Pour obtenir une CNI, vous devez créer un compte, soumettre une demande avec les documents requis, puis suivre votre dossier en ligne.',
    'document': 'Les documents nécessaires sont : un acte de naissance, un certificat de nationalité, une photo d\'identité et une preuve de résidence.',
    'délai': 'Le délai d\'obtention d\'une CNI est généralement de 24 H.',
    'coût': 'Le coût d\'une CNI est de 10 000 FCFA pour toutes demandes.',
    'paiement': 'Vous pouvez payer par carte bancaire, mobile money (Orange Money, MTN Mobile Money) ou virement bancaire.',
    'rendez-vous': 'Après avoir soumis votre demande en ligne, vous recevrez une notification pour prendre rendez-vous pour la prise d\'empreintes.',
    'problème': 'Si vous rencontrez un problème, veuillez contacter notre service client au 222 222 222 ou par email à support@cni.cam.',
    'merci': 'Je vous en prie ! N\'hésitez pas si vous avez d\'autres questions.',
    'horaires': 'Nos bureaux sont ouverts du lundi au vendredi de 8h à 16h et le samedi de 9h à 12h.',
    'renouvellement': 'Pour renouveler votre CNI, la procédure est similaire à une première demande. Vous devez toutefois fournir une copie de votre ancienne CNI.',
    'perte': 'En cas de perte de votre CNI, vous devez d\'abord faire une déclaration de perte auprès de la police, puis soumettre cette déclaration avec votre nouvelle demande.',
    'validité': 'La CNI camerounaise a une validité de 10 ans à compter de sa date d\'émission.',
    'étranger': 'Les Camerounais résidant à l\'étranger peuvent faire leur demande en ligne puis finaliser le processus à l\'ambassade ou au consulat du Cameroun dans leur pays de résidence.',
    'biométrie': 'La prise des données biométriques (empreintes digitales, photo, signature) se fait dans nos centres après validation de votre dossier en ligne.',
    'enfants': 'Les mineurs de moins de 18 ans doivent être accompagnés d\'un parent ou tuteur légal pour toute demande de CNI.',
    'modification': 'Pour toute modification d\'information sur votre CNI (changement d\'adresse, de nom après mariage, etc.), vous devez soumettre une demande de renouvellement avec les justificatifs appropriés.',
    'urgence': 'Nous proposons un service d\'urgence permettant d\'obtenir votre CNI en 12h moyennant des frais supplémentaires de 5 000 FCFA.',
    'centres': 'Nous disposons de centres de traitement dans toutes les préfectures et sous-préfectures du pays. Vous pouvez localiser le centre le plus proche via notre carte interactive.'
};
        
        // Recherche de correspondance dans les réponses prédéfinies
        let response = 'Je ne comprends pas votre demande. Pourriez-vous reformuler ou choisir une question parmi les suggestions ci-dessous ?';
        
        const messageLower = message.toLowerCase();
        for (const [keyword, reply] of Object.entries(responses)) {
            if (messageLower.includes(keyword)) {
                response = reply;
                break;
            }
        }
        
        // Réponses spécifiques pour les questions fréquentes
        if (messageLower.includes('comment demander une cni')) {
            response = 'Pour demander une CNI, suivez ces étapes : 1) Créez un compte sur notre plateforme, 2) Remplissez le formulaire en ligne, 3) Téléchargez les documents requis, 4) Payez les frais, 5) Prenez rendez-vous pour la prise d\'empreintes, 6) Suivez l\'état de votre demande en ligne.';
        } else if (messageLower.includes('délai d\'obtention')) {
            response = 'Grâce à notre plateforme numérique, le délai d\'obtention d\'une CNI a été considérablement réduit. En moyenne, vous pouvez obtenir votre CNI dans un délai de 24 H, contre 30 à 60 jours auparavant.';
        } else if (messageLower.includes('documents requis')) {
            response = 'Les documents requis pour une demande de CNI sont : 1) Un acte de naissance original, 2) Un certificat de nationalité, 3) Une photo d\'identité récente, 4) Une preuve de résidence. Tous ces documents peuvent être numérisés et téléchargés sur notre plateforme.';
        }
        
        return response;
    }
    
    // Gestion de la soumission du formulaire
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = userInput.value.trim();
        
        if (!message) return;
        
        // Ajouter le message de l'utilisateur
        addMessage(message, 'user');
        userInput.value = '';
        
        // Afficher l'indicateur de frappe
        showTypingIndicator();
        
        // Simuler un délai de réponse
        setTimeout(function() {
            // Masquer l'indicateur de frappe
            hideTypingIndicator();
            
            // Générer et ajouter la réponse de l'assistant
            const response = generateResponse(message);
            addMessage(response, 'assistant');
        }, 1500);
    });
    
    // Gestion des questions rapides
    quickQuestions.forEach(function(button) {
        button.addEventListener('click', function() {
            const question = this.textContent;
            
            // Ajouter la question au chat
            addMessage(question, 'user');
            
            // Afficher l'indicateur de frappe
            showTypingIndicator();
            
            // Simuler un délai de réponse
            setTimeout(function() {
                // Masquer l'indicateur de frappe
                hideTypingIndicator();
                
                // Générer et ajouter la réponse de l'assistant
                const response = generateResponse(question);
                addMessage(response, 'assistant');
            }, 1500);
        });
    });
    
    // Animation des compteurs
    const counters = document.querySelectorAll('.counter');
    const options = {
        threshold: 1,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                counter.classList.add('counting');
                observer.unobserve(counter);
            }
        });
    }, options);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
});
</script>

<?php include('includes/footer.php'); ?>
