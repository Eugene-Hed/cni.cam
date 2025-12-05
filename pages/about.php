<?php include('../includes/config.php'); ?>
<?php include('../includes/header.php'); 
    include('../includes/check_auth.php');?>
<?php include('../includes/navbar.php'); ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4">À propos de CNI.CAM</h1>
                <p class="lead mb-4">La plateforme officielle de gestion des Cartes Nationales d'Identité et des Certificats de Nationalité du Cameroun.</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Accueil</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">À propos</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="/assets/images/Cameroun.gif" alt="CNI.CAM" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</div>

<!-- Notre Mission -->
<div class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="/assets/images/mission.svg" alt="Notre Mission" class="img-fluid rounded-4 shadow-sm">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="section-title mb-4">
                    <h6 class="text-primary fw-bold text-uppercase">Notre Mission</h6>
                    <h2 class="display-5 fw-bold">Simplifier vos démarches administratives</h2>
                </div>
                <p class="lead">CNI.CAM est la plateforme officielle de gestion des Cartes Nationales d'Identité et des Certificats de Nationalité du Cameroun.</p>
                <p>Notre mission est de moderniser et de simplifier les démarches administratives pour tous les citoyens camerounais, en offrant des services numériques accessibles, rapides et sécurisés.</p>
                <div class="mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Accessibilité pour tous</h5>
                            <p class="mb-0 text-muted">Partout au Cameroun et pour la diaspora</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Rapidité de traitement</h5>
                            <p class="mb-0 text-muted">Délai maximum de 48h pour tous nos services</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Sécurité garantie</h5>
                            <p class="mb-0 text-muted">Protection des données personnelles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nos Services -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary fw-bold text-uppercase">Nos Services</h6>
            <h2 class="display-5 fw-bold">Solutions numériques complètes</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Découvrez nos services numériques conçus pour faciliter vos démarches administratives avec un traitement en moins de 48h.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <h3 class="h4 mb-3">Carte Nationale d'Identité</h3>
                        <p class="text-muted mb-4">Demandez ou renouvelez votre CNI en quelques clics. Processus simplifié avec suivi en temps réel de votre demande.</p>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Délai: 48h max</span>
                            <span><i class="bi bi-shield-check me-1"></i> Sécurisé</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-success bg-opacity-10 text-success mx-auto mb-4">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3 class="h4 mb-3">Certificat de Nationalité</h3>
                        <p class="text-muted mb-4">Procédure dématérialisée pour l'obtention de votre certificat de nationalité camerounaise, reconnu par toutes les administrations.</p>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-clock me-1"></i> Délai: 48h max</span>
                            <span><i class="bi bi-shield-check me-1"></i> Officiel</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4">
                    <div class="card-body p-4 text-center">
                        <div class="service-icon bg-info bg-opacity-10 text-info mx-auto mb-4">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="h4 mb-3">Suivi de Demande</h3>
                        <p class="text-muted mb-4">Suivez l'état de vos demandes en temps réel. Recevez des notifications à chaque étape du processus de traitement.</p>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><i class="bi bi-wifi me-1"></i> 24h/24, 7j/7</span>
                            <span><i class="bi bi-phone me-1"></i> Mobile</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="/pages/services.php" class="btn btn-primary">
                <i class="bi bi-grid me-2"></i>Découvrir tous nos services
            </a>
        </div>
    </div>
</div>

<!-- Nos Avantages -->
<div class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0" data-aos="fade-left">
                <img src="/assets/images/advantages.svg" alt="Nos Avantages" class="img-fluid rounded-4 shadow-sm">
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <div class="section-title mb-4">
                    <h6 class="text-primary fw-bold text-uppercase">Nos Avantages</h6>
                    <h2 class="display-5 fw-bold">Pourquoi choisir CNI.CAM ?</h2>
                </div>
                <p class="lead mb-4">Notre plateforme offre de nombreux avantages qui révolutionnent les démarches administratives au Cameroun.</p>
                
                <div class="advantage-item d-flex align-items-start mb-4">
                    <div class="advantage-icon bg-primary bg-opacity-10 text-primary rounded-4 p-3 me-3">
                        <i class="bi bi-clock fs-4"></i>
                    </div>
                    <div>
                        <h4>Gain de temps considérable</h4>
                        <p class="text-muted mb-0">Fini les longues files d'attente ! Effectuez vos démarches en ligne et recevez vos documents en 48h maximum.</p>
                    </div>
                </div>
                
                <div class="advantage-item d-flex align-items-start mb-4">
                    <div class="advantage-icon bg-success bg-opacity-10 text-success rounded-4 p-3 me-3">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <h4>Sécurité des données garantie</h4>
                        <p class="text-muted mb-0">Vos informations personnelles sont protégées par des protocoles de sécurité avancés et conformes aux normes internationales.</p>
                    </div>
                </div>
                
                <div class="advantage-item d-flex align-items-start mb-4">
                    <div class="advantage-icon bg-info bg-opacity-10 text-info rounded-4 p-3 me-3">
                        <i class="bi bi-geo-alt fs-4"></i>
                    </div>
                    <div>
                        <h4>Accessibilité depuis tout le territoire</h4>
                        <p class="text-muted mb-0">Que vous soyez à Yaoundé, Douala, Bafoussam ou à l'étranger, accédez à nos services 24h/24 et 7j/7.</p>
                    </div>
                </div>
                
                <div class="advantage-item d-flex align-items-start">
                    <div class="advantage-icon bg-warning bg-opacity-10 text-warning rounded-4 p-3 me-3">
                        <i class="bi bi-phone fs-4"></i>
                    </div>
                    <div>
                        <h4>Suivi en temps réel</h4>
                        <p class="text-muted mb-0">Suivez l'avancement de vos demandes à tout moment et recevez des notifications à chaque étape du processus.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notre Équipe -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary fw-bold text-uppercase">Notre Équipe</h6>
            <h2 class="display-5 fw-bold">Une équipe dévouée à votre service</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Notre équipe de professionnels est composée d'experts en technologie et en administration publique, dédiés à simplifier vos démarches.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm hover-card rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="team-avatar mx-auto mb-3">
                            <img src="/assets/images/team/director.jpg" alt="Directeur Général" class="img-fluid rounded-circle">
                        </div>
                        <h4>Dr. Samuel Nkomo</h4>
                        <p class="text-primary mb-3">Directeur Général</p>
                        <p class="text-muted small">Expert en administration publique avec plus de 15 ans d'expérience dans la modernisation des services publics.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm hover-card rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="team-avatar mx-auto mb-3">
                            <img src="/assets/images/team/tech.jpg" alt="Directeur Technique" class="img-fluid rounded-circle">
                        </div>
                        <h4>Ing. Marie Fouda</h4>
                        <p class="text-primary mb-3">Directrice Technique</p>
                        <p class="text-muted small">Ingénieure en informatique spécialisée dans le développement de solutions numériques sécurisées.</p>
                    </div>
                    </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm hover-card rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="team-avatar mx-auto mb-3">
                            <img src="/assets/images/team/operations.jpg" alt="Responsable des Opérations" class="img-fluid rounded-circle">
                        </div>
                        <h4>M. Paul Biya</h4>
                        <p class="text-primary mb-3">Responsable des Opérations</p>
                        <p class="text-muted small">Expert en gestion des processus administratifs et en optimisation des délais de traitement.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card border-0 shadow-sm hover-card rounded-4 h-100">
                    <div class="card-body p-4 text-center">
                        <div class="team-avatar mx-auto mb-3">
                            <img src="/assets/images/team/support.jpg" alt="Responsable Support" class="img-fluid rounded-circle">
                        </div>
                        <h4>Mme. Jeanne Mbarga</h4>
                        <p class="text-primary mb-3">Responsable Support</p>
                        <p class="text-muted small">Spécialiste en relation client, dédiée à vous accompagner dans toutes vos démarches.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <div class="display-4 fw-bold mb-2 counter">500,000+</div>
                    <p class="mb-0">Utilisateurs inscrits</p>
                </div>
            </div>
            
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="display-4 fw-bold mb-2 counter">1,200,000+</div>
                    <p class="mb-0">Documents délivrés</p>
                </div>
            </div>
            
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <div class="display-4 fw-bold mb-2 counter">48h</div>
                    <p class="mb-0">Délai maximum</p>
                </div>
            </div>
            
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    <div class="display-4 fw-bold mb-2 counter">97%</div>
                    <p class="mb-0">Taux de satisfaction</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Partenaires -->
<div class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h6 class="text-primary fw-bold text-uppercase">Nos Partenaires</h6>
            <h2 class="display-5 fw-bold">Ils nous font confiance</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">CNI.CAM collabore avec des institutions nationales et internationales pour offrir des services de qualité.</p>
        </div>
        
        <div class="row g-4 align-items-center justify-content-center">
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <img src="/assets/images/partners/minfi.png" alt="MINFI" class="img-fluid partner-logo">
                </div>
            </div>
            
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <img src="/assets/images/partners/dgsn.png" alt="DGSN" class="img-fluid partner-logo">
                </div>
            </div>
            
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <img src="/assets/images/partners/minpostel.png" alt="MINPOSTEL" class="img-fluid partner-logo">
                </div>
            </div>
            
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    <img src="/assets/images/partners/campost.png" alt="CAMPOST" class="img-fluid partner-logo">
                </div>
            </div>
            
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="500">
                <div class="text-center">
                    <img src="/assets/images/partners/minjustice.png" alt="MINJUSTICE" class="img-fluid partner-logo">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="section-title mb-4">
                    <h6 class="text-primary fw-bold text-uppercase">Contact</h6>
                    <h2 class="display-5 fw-bold">Besoin d'aide ?</h2>
                </div>
                <p class="lead mb-4">Notre équipe de support est disponible pour répondre à toutes vos questions et vous accompagner dans vos démarches.</p>
                
                <div class="contact-info">
                    <div class="d-flex align-items-center mb-3">
                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Email</h5>
                            <p class="mb-0">contact@cni.cam</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Téléphone</h5>
                            <p class="mb-0">(+237) 222 222 222</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-geo"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Adresse</h5>
                            <p class="mb-0">Yaoundé, Cameroun</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Heures d'ouverture</h5>
                            <p class="mb-0">Lun-Ven: 8h-16h | Sam: 9h-12h</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="/pages/contact.php" class="btn btn-primary">
                        <i class="bi bi-chat-dots me-2"></i>Nous contacter
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Envoyez-nous un message</h4>
                        <form id="contactForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="name" placeholder="Votre nom">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="Votre email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Sujet</label>
                                <input type="text" class="form-control" id="subject" placeholder="Sujet de votre message">
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" rows="4" placeholder="Votre message"></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Envoyer le message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la page À propos */
.icon-box {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

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

.advantage-icon {
    min-width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.team-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid rgba(23, 116, 223, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.team-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contact-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.partner-logo {
    max-height: 80px;
    filter: grayscale(100%);
    opacity: 0.7;
    transition: all 0.3s ease;
}

.partner-logo:hover {
    filter: grayscale(0%);
    opacity: 1;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-10px);
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

/* Animation des compteurs */
.counter {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 1s ease, transform 1s ease;
}

.counter.animated {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .service-icon {
        width: 70px;
        height: 70px;
        font-size: 1.75rem;
    }
    
    .team-avatar {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 576px) {
    .service-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .advantage-icon {
        min-width: 50px;
        height: 50px;
    }
    
    .contact-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
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
    
    // Animation des compteurs
    const counters = document.querySelectorAll('.counter');
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5
    };
    
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, options);
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
    
    // Gestion du formulaire de contact
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simuler l'envoi du formulaire
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Envoi en cours...';
            
            setTimeout(() => {
                // Réinitialiser le formulaire
                this.reset();
                
                // Afficher un message de succès
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success mt-3';
                successAlert.innerHTML = '<i class="bi bi-check-circle me-2"></i>Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';
                
                this.appendChild(successAlert);
                
                // Restaurer le bouton
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Faire disparaître l'alerte après 5 secondes
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transition = 'opacity 0.5s ease';
                    
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }, 1500);
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
