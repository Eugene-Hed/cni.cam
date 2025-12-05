<?php
include('../includes/config.php');
include('../includes/header.php');
include('../includes/navbar.php');
?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4">Nos Services</h1>
                <p class="lead mb-4">Découvrez l'ensemble des services numériques proposés par CNI.CAM pour faciliter vos démarches administratives.</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Accueil</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Services</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="/assets/images/services-hero.svg" alt="Services CNI.CAM" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Services principaux -->
<div class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Services Principaux</h2>
            <p class="lead text-muted">Des solutions numériques pour tous vos besoins administratifs</p>
        </div>
        
        <div class="row g-4">
            <!-- Service 1 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <h3 class="h4 mb-3">Carte Nationale d'Identité</h3>
                        <p class="text-muted mb-4">Demandez ou renouvelez votre CNI en quelques clics. Suivez l'avancement de votre dossier en temps réel.</p>
                        <div class="d-grid">
                            <a href="/pages/demande_cni.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-right me-2"></i>Faire une demande
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Délai: 48 heurs</span>
                            <span><i class="bi bi-cash me-1"></i> Frais: 10 000 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 2 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-success bg-opacity-10 text-success mx-auto mb-4">
                            <i class="bi bi-flag"></i>
                        </div>
                        <h3 class="h4 mb-3">Certificat de Nationalité</h3>
                        <p class="text-muted mb-4">Obtenez votre certificat de nationalité camerounaise en ligne. Document officiel reconnu par l'administration.</p>
                        <div class="d-grid">
                            <a href="/pages/demande_certificat.php" class="btn btn-outline-success">
                                <i class="bi bi-arrow-right me-2"></i>Faire une demande
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Délai: 48 Heurs</span>
                            <span><i class="bi bi-cash me-1"></i> Frais: 1 500 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 3 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-info bg-opacity-10 text-info mx-auto mb-4">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="h4 mb-3">Suivi de Demande</h3>
                        <p class="text-muted mb-4">Suivez l'état d'avancement de vos demandes en temps réel. Recevez des notifications à chaque étape du processus.</p>
                        <div class="d-grid">
                            <a href="/pages/mes_demandes.php" class="btn btn-outline-info">
                                <i class="bi bi-arrow-right me-2"></i>Suivre mes demandes
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-wifi me-1"></i> Disponible 24/7</span>
                            <span><i class="bi bi-cash me-1"></i> Service gratuit</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 4 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-warning bg-opacity-10 text-warning mx-auto mb-4">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="h4 mb-3">Rendez-vous en ligne</h3>
                        <p class="text-muted mb-4">Prenez rendez-vous en ligne pour vos démarches nécessitant une présence physique. Évitez les files d'attente.</p>
                        <div class="d-grid">
                            <a href="/pages/rendez_vous.php" class="btn btn-outline-warning">
                                <i class="bi bi-arrow-right me-2"></i>Prendre rendez-vous
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Disponibilité: 48h</span>
                            <span><i class="bi bi-cash me-1"></i> Service gratuit</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 5 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-danger bg-opacity-10 text-danger mx-auto mb-4">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h3 class="h4 mb-3">Déclaration de perte</h3>
                        <p class="text-muted mb-4">Déclarez la perte de votre CNI en ligne et obtenez une attestation de perte officielle pour vos démarches.</p>
                        <div class="d-grid">
                            <a href="/pages/declaration_perte.php" class="btn btn-outline-danger">
                                <i class="bi bi-arrow-right me-2"></i>Déclarer une perte
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Délai: 24h</span>
                            <span><i class="bi bi-cash me-1"></i> Frais: 1 000 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Service 6 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-secondary bg-opacity-10 text-secondary mx-auto mb-4">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h3 class="h4 mb-3">Paiement en ligne</h3>
                        <p class="text-muted mb-4">Payez vos frais administratifs en ligne de manière sécurisée. Plusieurs méthodes de paiement disponibles.</p>
                        <div class="d-grid">
                            <a href="/pages/paiements.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-right me-2"></i>Effectuer un paiement
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 border-0">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-shield-check me-1"></i> 100% sécurisé</span>
                            <span><i class="bi bi-wifi me-1"></i> Disponible 24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment ça marche -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Comment ça marche ?</h2>
            <p class="lead text-muted">Un processus simple en 4 étapes</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="process-card text-center">
                    <div class="process-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <span class="h3">1</span>
                    </div>
                    <h4>Créez votre compte</h4>
                    <p class="text-muted">Inscrivez-vous en quelques minutes avec vos informations personnelles</p>
                </div>
            </div>
            
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="process-card text-center">
                    <div class="process-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <span class="h3">2</span>
                    </div>
                    <h4>Soumettez votre demande</h4>
                    <p class="text-muted">Remplissez le formulaire en ligne et téléchargez les documents requis</p>
                </div>
            </div>
            
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="process-card text-center">
                    <div class="process-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <span class="h3">3</span>
                    </div>
                    <h4>Suivez votre dossier</h4>
                    <p class="text-muted">Consultez l'état d'avancement de votre demande en temps réel</p>
                </div>
            </div>
            
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="process-card text-center">
                    <div class="process-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                        <span class="h3">4</span>
                    </div>
                    <h4>Récupérez votre document</h4>
                    <p class="text-muted">Retirez votre document final au centre indiqué ou recevez-le par voie postale</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Questions fréquentes</h2>
            <p class="lead text-muted">Trouvez rapidement des réponses à vos questions</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="accordion" id="faqAccordion">
                                        <!-- Question 1 -->
                                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Quels documents sont nécessaires pour une demande de CNI ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
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
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Quel est le délai de traitement d'une demande de CNI ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Le délai standard de traitement d'une demande de CNI est de 15 jours ouvrables à compter de la validation de votre dossier complet. Ce délai peut varier en fonction de :</p>
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
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Comment payer les frais administratifs en ligne ?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Notre plateforme propose plusieurs méthodes de paiement sécurisées :</p>
                                <ul>
                                    <li>Mobile Money (Orange Money, MTN Mobile Money, etc.)</li>
                                    <li>Carte bancaire (Visa, Mastercard)</li>
                                    <li>Virement bancaire</li>
                                </ul>
                                <p>Une fois votre paiement effectué, vous recevrez immédiatement une confirmation par email et SMS. Le reçu de paiement sera disponible dans votre espace personnel.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Que faire en cas de perte de ma CNI ?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
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
                    
                    <!-- Question 5 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Comment prendre rendez-vous pour la biométrie ?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
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
        </div>
        
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="/pages/faq.php" class="btn btn-outline-primary">
                <i class="bi bi-question-circle me-2"></i>Voir toutes les questions
            </a>
        </div>
    </div>
</div>

<!-- Témoignages -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Témoignages</h2>
            <p class="lead text-muted">Ce que nos utilisateurs disent de nos services</p>
        </div>
        
        <div class="row g-4">
            <!-- Témoignage 1 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="mb-4">"J'ai pu renouveler ma CNI en un temps record grâce à cette plateforme. Le processus était simple et j'ai pu suivre l'avancement de ma demande à chaque étape. Je recommande vivement ce service !"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Jean-Paul Mbarga</h6>
                                <small class="text-muted">Douala</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Témoignage 2 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <p class="mb-4">"Le service de prise de rendez-vous en ligne m'a fait gagner beaucoup de temps. Plus besoin de faire la queue pendant des heures ! Le personnel était professionnel et efficace lors de mon rendez-vous."</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Marie Nguemo</h6>
                                <small class="text-muted">Yaoundé</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Témoignage 3 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <i class="bi bi-star-half text-warning"></i>
                        </div>
                        <p class="mb-4">"J'ai obtenu mon certificat de nationalité en seulement 5 jours ! Les notifications par SMS m'ont permis de suivre chaque étape. Le paiement en ligne était simple et sécurisé. Merci CNI.CAM !"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Robert Foka</h6>
                                <small class="text-muted">Bafoussam</small>
                            </div>
                        </div>
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
/* Styles spécifiques à la page services */
.service-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto;
}

.process-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-10px);
}

.accordion-button:not(.collapsed) {
    background-color: rgba(23, 116, 223, 0.1);
    color: var(--primary);
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
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
    .service-icon {
        width: 70px;
        height: 70px;
        font-size: 1.75rem;
    }
    
    .process-icon {
        width: 50px;
        height: 50px;
    }
}

@media (max-width: 768px) {
    .process-card {
        margin-bottom: 2rem;
    }
}

@media (max-width: 576px) {
    .service-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
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
    
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
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
