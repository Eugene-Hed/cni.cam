<?php 
include('../includes/config.php');
include('../includes/header.php');
include('../includes/navbar.php');
include('../includes/check_auth.php');
?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4">Contactez-nous</h1>
                <p class="lead mb-4">Notre équipe est à votre disposition pour répondre à toutes vos questions et vous accompagner dans vos démarches.</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/index.php" class="text-white-50">Accueil</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Contact</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="/assets/images/contact-hero.svg" alt="Contactez CNI.CAM" class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</div>

<!-- Coordonnées et Formulaire -->
<div class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Coordonnées -->
            <div class="col-lg-5" data-aos="fade-right">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h2 class="card-title fw-bold mb-4">Nos coordonnées</h2>
                        
                        <div class="contact-info">
                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Téléphone</h5>
                                    <p class="mb-0 text-muted">
                                        <a href="tel:+237222222222" class="text-decoration-none text-muted">
                                            (+237) 222 222 222
                                        </a>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <a href="tel:+237233333333" class="text-decoration-none text-muted">
                                            (+237) 233 333 333
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon bg-success bg-opacity-10 text-success rounded-circle me-3">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Email</h5>
                                    <p class="mb-0 text-muted">
                                        <a href="mailto:contact@cni.cam" class="text-decoration-none text-muted">
                                            contact@cni.cam
                                        </a>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <a href="mailto:support@cni.cam" class="text-decoration-none text-muted">
                                            support@cni.cam
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon bg-info bg-opacity-10 text-info rounded-circle me-3">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Adresse</h5>
                                    <p class="mb-0 text-muted">
                                        Siège CNI.CAM<br>
                                        Avenue de l'Indépendance<br>
                                        Yaoundé, Cameroun
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-4">
                                <div class="contact-icon bg-warning bg-opacity-10 text-warning rounded-circle me-3">
                                    <i class="bi bi-clock-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Heures d'ouverture</h5>
                                    <p class="mb-0 text-muted">
                                        Lundi - Vendredi: 8h00 - 16h00<br>
                                        Samedi: 9h00 - 12h00<br>
                                        Dimanche: Fermé
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="fw-bold mb-3">Suivez-nous</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-light-primary rounded-circle social-btn">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="btn btn-light-info rounded-circle social-btn">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-light-danger rounded-circle social-btn">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-light-success rounded-circle social-btn">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="#" class="btn btn-light-secondary rounded-circle social-btn">
                                <i class="bi bi-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de contact -->
            <div class="col-lg-7" data-aos="fade-left">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="card-title fw-bold mb-4">Envoyez-nous un message</h2>
                        <p class="text-muted mb-4">Remplissez le formulaire ci-dessous et nous vous répondrons dans un délai de 48h maximum.</p>
                        
                        <form id="contactForm" action="process_contact.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bi bi-person text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-0 bg-light" id="nom" name="nom" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="bi bi-person text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-0 bg-light" id="prenom" name="prenom" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-0 bg-light" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="bi bi-phone text-muted"></i>
                                    </span>
                                    <input type="tel" class="form-control border-0 bg-light" id="telephone" name="telephone">
                                </div>
                                <div class="form-text">Format: (+237) XXX XXX XXX</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sujet" class="form-label">Sujet <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="bi bi-chat-left-text text-muted"></i>
                                    </span>
                                    <select class="form-select border-0 bg-light" id="sujet" name="sujet" required>
                                        <option value="">Choisir un sujet</option>
                                        <option value="demande">Demande d'informations</option>
                                        <option value="probleme">Signaler un problème</option>
                                        <option value="suggestion">Suggestion</option>
                                        <option value="reclamation">Réclamation</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="bi bi-pencil text-muted"></i>
                                    </span>
                                    <textarea class="form-control border-0 bg-light" id="message" name="message" rows="5" required></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    J'accepte que mes données soient traitées conformément à la <a href="/pages/privacy.php">politique de confidentialité</a> de CNI.CAM
                                </label>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-send me-2"></i>Envoyer
                                </button>
                            </div>
                        </form>
                        
                        <!-- Message de confirmation (initialement caché) -->
                        <div id="confirmationMessage" class="alert alert-success mt-4 d-none">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Message envoyé avec succès !</h5>
                                    <p class="mb-0">Merci de nous avoir contactés. Notre équipe vous répondra dans les plus brefs délais.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Carte et Bureaux -->
<div class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Nos bureaux</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Retrouvez-nous dans nos différents bureaux à travers le Cameroun</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-0">
                        <div class="map-container rounded-4">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127505.34620916416!2d11.447897658203123!3d3.8666673!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x108bcf7a309a7977%3A0x7f54bad35e693c51!2zWWFvdW5kw6k!5e0!3m2!1sfr!2scm!4v1651010000000!5m2!1sfr!2scm" 
                                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">Nos centres</h3>
                        
                        <div class="office-list">
                            <div class="office-item mb-4">
                                <h5 class="text-primary mb-2">Yaoundé (Siège)</h5>
                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Avenue de l'Indépendance, Yaoundé</p>
                                <p class="mb-1"><i class="bi bi-telephone me-2"></i>(+237) 222 222 222</p>
                                <p class="mb-0"><i class="bi bi-envelope me-2"></i>yaounde@cni.cam</p>
                            </div>
                            
                            <div class="office-item mb-4">
                                <h5 class="text-primary mb-2">Douala</h5>
                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Boulevard de la Liberté, Douala</p>
                                <p class="mb-1"><i class="bi bi-telephone me-2"></i>(+237) 233 333 333</p>
                                <p class="mb-0"><i class="bi bi-envelope me-2"></i>douala@cni.cam</p>
                            </div>
                            
                            <div class="office-item mb-4">
                                <h5 class="text-primary mb-2">Bafoussam</h5>
                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Rue des Palmiers, Bafoussam</p>
                                <p class="mb-1"><i class="bi bi-telephone me-2"></i>(+237) 244 444 444</p>
                                <p class="mb-0"><i class="bi bi-envelope me-2"></i>bafoussam@cni.cam</p>
                            </div>
                            
                            <div class="office-item">
                                <h5 class="text-primary mb-2">Garoua</h5>
                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>Avenue Ahmadou Ahidjo, Garoua</p>
                                <p class="mb-1"><i class="bi bi-telephone me-2"></i>(+237) 255 555 555</p>
                                <p class="mb-0"><i class="bi bi-envelope me-2"></i>garoua@cni.cam</p>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="/pages/centres.php" class="btn btn-outline-primary">
                                <i class="bi bi-geo-alt me-2"></i>Voir tous nos centres
                            </a>
                        </div>
                    </div>
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
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Trouvez rapidement des réponses aux questions les plus courantes</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10" data-aos="fade-up">
                <div class="accordion" id="faqAccordion">
                    <!-- Question 1 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Quel est le délai de réponse à mes questions ?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Notre équipe s'engage à répondre à toutes vos questions dans un délai maximum de <strong>48 heures</strong>. Pour les demandes urgentes, nous vous recommandons de nous contacter par téléphone pour un traitement prioritaire.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Comment puis-je suivre ma demande après avoir contacté le support ?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Lorsque vous soumettez une demande via notre formulaire de contact, vous recevez automatiquement un numéro de référence. Vous pouvez utiliser ce numéro pour suivre l'état de votre demande dans la section "Suivi de demande" de votre espace personnel ou en contactant notre service client.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Puis-je prendre rendez-vous pour un entretien en personne ?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Oui, vous pouvez prendre rendez-vous pour un entretien en personne dans l'un de nos centres. Pour cela, utilisez notre service de prise de rendez-vous en ligne disponible dans votre espace personnel ou contactez-nous par téléphone. Nous vous confirmerons la date et l'heure de votre rendez-vous par email et SMS.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/pages/faq.php" class="btn btn-outline-primary">
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
                <h2 class="display-5 fw-bold mb-3">Besoin d'aide rapide ?</h2>
                <p class="lead mb-0">Notre service d'assistance téléphonique est disponible du lundi au vendredi de 8h à 16h.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="tel:+237222222222" class="btn btn-light btn-lg">
                    <i class="bi bi-telephone-fill me-2"></i>Appelez-nous
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la page contact */
.contact-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.social-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.social-btn:hover {
    transform: translateY(-5px);
}

.btn-light-primary {
    background-color: rgba(23, 116, 223, 0.1);
    color: #1774df;
}

.btn-light-info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.btn-light-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.btn-light-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.btn-light-secondary {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.map-container {
    overflow: hidden;
    border-radius: 1rem;
}

.office-item {
    padding-left: 10px;
    border-left: 3px solid #1774df;
}

.input-group-text {
    border-radius: 0.5rem 0 0 0.5rem;
}

.form-control, .form-select {
    border-radius: 0 0.5rem 0.5rem 0;
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
    .contact-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .social-btn {
        width: 36px;
        height: 36px;
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
    
    // Gestion du formulaire de contact
    const contactForm = document.getElementById('contactForm');
    const confirmationMessage = document.getElementById('confirmationMessage');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            // Pour la démo, on empêche l'envoi réel du formulaire
            // Commentez cette ligne pour permettre l'envoi réel
            e.preventDefault();
            
            // Simuler l'envoi du formulaire
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Envoi en cours...';
            
            setTimeout(() => {
                // Masquer le formulaire
                contactForm.style.opacity = '0';
                contactForm.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    contactForm.style.display = 'none';
                    
                    // Afficher le message de confirmation
                    confirmationMessage.classList.remove('d-none');
                    confirmationMessage.style.opacity = '0';
                    
                    setTimeout(() => {
                        confirmationMessage.style.opacity = '1';
                        confirmationMessage.style.transition = 'opacity 0.5s ease';
                    }, 100);
                }, 500);
            }, 1500);
        });
    }
    
    // Validation du formulaire
    const inputs = document.querySelectorAll('.form-control, .form-select');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                if (this.value) {
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // Validation de l'email
    const emailInput = document.getElementById('email');
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            }
        });
    }
    
    // Validation du téléphone
    const phoneInput = document.getElementById('telephone');
    
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            if (this.value) {
                const phoneRegex = /^\+?\d{9,15}$/;
                if (!phoneRegex.test(this.value.replace(/\s/g, ''))) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            }
        });
    }
});
</script>

<?php include('../includes/footer.php'); ?>
