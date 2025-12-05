<?php
// Session is initialized centrally in includes/config.php
// Vérifier si le fichier de configuration existe avant de l'inclure
if (file_exists('../includes/config.php')) {
    require_once '../includes/config.php';
} else {
    // Afficher un message d'erreur si le fichier n'existe pas
    die("Le fichier de configuration de la base de données est introuvable.");
}

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Mentions légales - CNI.CAM";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php 
    // Vérifier si le fichier navbar existe avant de l'inclure
    if (file_exists('../includes/navbar.php')) {
        include_once '../includes/navbar.php';
    } else {
        echo "<div class='alert alert-danger'>Le fichier de navigation est introuvable.</div>";
    }
    ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="text-center mb-4">Mentions légales</h1>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">1. Informations légales</h2>
                            <p>Le site CNI.CAM est édité par la Direction Générale de la Sûreté Nationale (DGSN) du Cameroun.</p>
                            <p><strong>Siège social :</strong> Yaoundé, Cameroun</p>
                            <p><strong>Téléphone :</strong> (+237) 222 222 222</p>
                            <p><strong>Email :</strong> contact@cni.cam</p>
                            <p><strong>Directeur de la publication :</strong> Le Délégué Général à la Sûreté Nationale</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">2. Hébergement</h2>
                            <p>Le site CNI.CAM est hébergé par le Centre National de Développement de l'Informatique (CENADI).</p>
                            <p><strong>Adresse :</strong> Yaoundé, Cameroun</p>
                            <p><strong>Téléphone :</strong> (+237) 222 222 222</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">3. Propriété intellectuelle</h2>
                            <p>L'ensemble du contenu du site CNI.CAM (textes, images, vidéos, logos, icônes, etc.) est la propriété exclusive de la Direction Générale de la Sûreté Nationale du Cameroun et est protégé par les lois camerounaises et internationales relatives à la propriété intellectuelle.</p>
                            <p>Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable de la Direction Générale de la Sûreté Nationale.</p>
                            <p>Toute exploitation non autorisée du site ou de l'un quelconque des éléments qu'il contient sera considérée comme constitutive d'une contrefaçon et poursuivie conformément aux dispositions des articles L.335-2 et suivants du Code de la Propriété Intellectuelle.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">4. Liens hypertextes</h2>
                            <p>Le site CNI.CAM peut contenir des liens hypertextes vers d'autres sites internet ou d'autres ressources disponibles sur Internet. La Direction Générale de la Sûreté Nationale ne dispose d'aucun moyen pour contrôler les sites en connexion avec son site internet et ne répond pas de la disponibilité de tels sites et sources externes, ni ne la garantit.</p>
                            <p>La Direction Générale de la Sûreté Nationale ne peut être tenue pour responsable de tout dommage, de quelque nature que ce soit, résultant du contenu de ces sites ou sources externes, et notamment des informations, produits ou services qu'ils proposent, ou de tout usage qui peut être fait de ces éléments.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">5. Responsabilité</h2>
                            <p>Les informations et services proposés sur le site CNI.CAM sont fournis à titre informatif. La Direction Générale de la Sûreté Nationale ne saurait être tenue pour responsable des erreurs ou omissions dans les informations diffusées ou des problèmes techniques rencontrés sur le site et sur tous les autres sites vers lesquels elle établit des liens, ou de toute interprétation des informations publiées sur ces sites, ainsi que des conséquences de leur utilisation.</p>
                            <p>La Direction Générale de la Sûreté Nationale se réserve le droit de modifier, à tout moment et sans préavis, le contenu de ce site.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">6. Droit applicable et juridiction compétente</h2>
                            <p>Les présentes mentions légales sont régies par la loi camerounaise. En cas de litige, les tribunaux camerounais seront seuls compétents.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">7. Contact</h2>
                            <p>Pour toute question relative aux présentes mentions légales ou pour toute demande concernant le site, vous pouvez nous contacter à l'adresse suivante : contact@cni.cam</p>
                        </div>
                        
                        <div class="text-center mt-5">
                            <p class="text-muted small">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
    // Vérifier si le fichier footer existe avant de l'inclure
    if (file_exists('../includes/footer.php')) {
        include_once '../includes/footer.php';
    } else {
        echo "<div class='alert alert-danger'>Le fichier de pied de page est introuvable.</div>";
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
