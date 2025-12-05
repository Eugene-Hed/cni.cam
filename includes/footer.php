<footer class="footer pt-5">
    <div class="container">
        <!-- Section principale -->
        <div class="row g-4">
            <!-- Colonne À propos -->
            <div class="col-lg-4">
                <div class="footer-brand mb-4">
                    <img src="/assets/images/Cameroun.gif" alt="CNI.CAM" height="50" class="mb-3">
                    <h5 class="text-white">CNI.CAM</h5>
                </div>
                <p class="text-light mb-4">La plateforme officielle de gestion des Cartes Nationales d'Identité et des Certificats de Nationalité du Cameroun.</p>
                <div class="social-links">
                    <a href="#" class="btn btn-light btn-sm rounded-circle me-2" data-bs-toggle="tooltip" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm rounded-circle me-2" data-bs-toggle="tooltip" title="Twitter">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm rounded-circle me-2" data-bs-toggle="tooltip" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-light btn-sm rounded-circle" data-bs-toggle="tooltip" title="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                </div>
            </div>

            <!-- Colonne Liens Rapides -->
            <div class="col-lg-2">
                <h5 class="text-white mb-4">Liens Rapides</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="/pages/about.php">À propos</a></li>
                    <li><a href="/pages/services.php">Nos services</a></li>
                    <li><a href="/pages/faq.php">FAQ</a></li>
                    <li><a href="/pages/contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Colonne Services -->
            <div class="col-lg-2">
                <h5 class="text-white mb-4">Services</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="/pages/demande_cni.php">Demande de CNI</a></li>
                    <li><a href="/pages/demande_certificat.php">Certificat de nationalité</a></li>
                    <li><a href="/pages/mes_demandes.php">Suivi des demandes</a></li>
                    <li><a href="/pages/reclamations.php">Réclamations</a></li>
                </ul>
            </div>

            <!-- Colonne Contact -->
            <div class="col-lg-4">
                <h5 class="text-white mb-4">Contact</h5>
                <ul class="list-unstyled contact-info">
                    <li class="d-flex mb-3">
                        <i class="bi bi-geo-alt text-primary me-3"></i>
                        <span>Yaoundé, Cameroun</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-envelope text-primary me-3"></i>
                        <span>contact@cni.cam</span>
                    </li>
                    <li class="d-flex mb-3">
                        <i class="bi bi-telephone text-primary me-3"></i>
                        <span>(+237) 222 222 222</span>
                    </li>
                    <li class="d-flex">
                        <i class="bi bi-clock text-primary me-3"></i>
                        <span>Lun - Ven: 8h00 - 16h00</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Barre de séparation -->
        <hr class="my-4 border-light">

        <!-- Section du bas -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-light mb-md-0">
                    &copy; <?php echo date('Y'); ?> CNI.CAM - Tous droits réservés
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-bottom-links">
                    <a href="/pages/mentions-legales.php">Mentions légales</a>
                    <span class="mx-2">|</span>
                    <a href="/pages/confidentialite.php">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background: linear-gradient(135deg, #1774df 0%, #135bb2 100%);
    color: #fff;
    padding-bottom: 2rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    display: block;
    padding: 0.5rem 0;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #fff;
    transform: translateX(5px);
}

.social-links a {
    width: 35px;
    height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.social-links a:hover {
    background: #fff;
    color: #1774df;
    transform: translateY(-3px);
}

.contact-info li {
    color: rgba(255, 255, 255, 0.8);
}

.contact-info i {
    font-size: 1.2rem;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #fff;
}

@media (max-width: 768px) {
    .footer-bottom-links {
        text-align: center;
        margin-top: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
