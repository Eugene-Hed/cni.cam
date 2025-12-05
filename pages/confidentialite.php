<?php
// Session is initialized centrally in includes/config.php
require_once '../includes/config.php';
$pageTitle = "Politique de confidentialité - CNI.CAM";
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
    <?php include_once '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="text-center mb-4">Politique de confidentialité</h1>
                        
                        <div class="alert alert-info mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-info-circle-fill fs-4"></i>
                                </div>
                                <div>
                                    <p class="mb-0">La protection de vos données personnelles est une priorité pour la Direction Générale de la Sûreté Nationale du Cameroun. Cette politique de confidentialité vous informe sur la manière dont nous collectons, utilisons et protégeons vos informations personnelles.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">1. Collecte des données personnelles</h2>
                            <p>Dans le cadre de la délivrance des Cartes Nationales d'Identité et des Certificats de Nationalité, nous collectons les données personnelles suivantes :</p>
                            <ul>
                                <li>Nom et prénom</li>
                                <li>Date et lieu de naissance</li>
                                <li>Sexe</li>
                                <li>Adresse postale et électronique</li>
                                <li>Numéro de téléphone</li>
                                <li>Photographie d'identité</li>
                                <li>Empreintes digitales</li>
                                <li>Signature</li>
                                <li>Informations sur les parents (nom, prénom, nationalité)</li>
                                <li>Documents justificatifs d'identité</li>
                            </ul>
                            <p>Ces informations sont collectées directement auprès de vous lors de votre demande de CNI ou de Certificat de Nationalité, via notre plateforme en ligne ou dans nos centres de délivrance.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">2. Utilisation des données personnelles</h2>
                            <p>Les données personnelles que nous collectons sont utilisées pour :</p>
                            <ul>
                                <li>Traiter votre demande de Carte Nationale d'Identité ou de Certificat de Nationalité</li>
                                <li>Vérifier votre identité et votre éligibilité</li>
                                <li>Produire les documents officiels demandés</li>
                                <li>Vous contacter concernant votre demande</li>
                                <li>Gérer les rendez-vous et les procédures administratives</li>
                                <li>Assurer la sécurité et l'intégrité du processus de délivrance</li>
                                <li>Respecter nos obligations légales et réglementaires</li>
                                <li>Établir des statistiques anonymisées</li>
                            </ul>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">3. Base légale du traitement</h2>
                            <p>Le traitement de vos données personnelles est fondé sur :</p>
                            <ul>
                                <li>L'exécution d'une mission d'intérêt public ou relevant de l'exercice de l'autorité publique</li>
                                <li>Le respect d'une obligation légale à laquelle la Direction Générale de la Sûreté Nationale est soumise</li>
                                <li>Votre consentement, lorsque celui-ci est requis</li>
                            </ul>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">4. Conservation des données</h2>
                            <p>Vos données personnelles sont conservées pendant la durée nécessaire à la réalisation des finalités pour lesquelles elles ont été collectées, augmentée des délais légaux de conservation et de prescription.</p>
                            <p>Les données relatives à l'identité sont conservées pendant toute la durée de validité de la Carte Nationale d'Identité ou du Certificat de Nationalité, et au-delà conformément aux obligations légales d'archivage des documents administratifs.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">5. Partage des données</h2>
                            <p>Vos données personnelles peuvent être partagées avec :</p>
                            <ul>
                                <li>Les services administratifs impliqués dans le processus de délivrance des documents d'identité</li>
                                <li>Les sous-traitants techniques intervenant dans la production des documents</li>
                                <li>Les autorités judiciaires ou administratives, lorsque la loi l'exige</li>
                            </ul>
                            <p>Nous ne vendons ni ne louons vos données personnelles à des tiers à des fins commerciales.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">6. Sécurité des données</h2>
                            <p>La Direction Générale de la Sûreté Nationale met en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre la perte, l'accès non autorisé, la divulgation, l'altération ou la destruction.</p>
                            <p>Ces mesures comprennent notamment :</p>
                            <ul>
                                <li>Le chiffrement des données sensibles</li>
                                <li>L'accès restreint aux données sur la base du besoin d'en connaître</li>
                                <li>Des procédures de sauvegarde et de récupération</li>
                                <li>Des audits de sécurité réguliers</li>
                                <li>La formation du personnel aux bonnes pratiques de sécurité</li>
                            </ul>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">7. Vos droits</h2>
                            <p>Conformément à la législation en vigueur, vous disposez des droits suivants concernant vos données personnelles :</p>
                            <ul>
                                <li>Droit d'accès à vos données</li>
                                <li>Droit de rectification des données inexactes</li>
                                <li>Droit à l'effacement (dans les limites des obligations légales)</li>
                                <li>Droit à la limitation du traitement</li>
                                <li>Droit d'opposition au traitement (dans certains cas)</li>
                                <li>Droit à la portabilité de vos données</li>
                            </ul>
                            <p>Pour exercer ces droits, vous pouvez nous contacter à l'adresse suivante : <a href="mailto:privacy@cni.cam">privacy@cni.cam</a> ou par courrier à l'adresse de la Direction Générale de la Sûreté Nationale.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">8. Cookies et technologies similaires</h2>
                            <p>Notre site web utilise des cookies et technologies similaires pour améliorer votre expérience de navigation et assurer le bon fonctionnement du site.</p>
                            <p>Les cookies utilisés sont principalement :</p>
                            <ul>
                                <li>Des cookies techniques nécessaires au fonctionnement du site</li>
                                <li>Des cookies de session pour gérer votre connexion</li>
                                <li>Des cookies analytiques pour comprendre l'utilisation du site (anonymisés)</li>
                            </ul>
                            <p>Vous pouvez configurer votre navigateur pour refuser les cookies, mais cela pourrait affecter certaines fonctionnalités du site.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">9. Modifications de la politique de confidentialité</h2>
                            <p>La Direction Générale de la Sûreté Nationale se réserve le droit de modifier cette politique de confidentialité à tout moment. Les modifications entreront en vigueur dès leur publication sur le site.</p>
                            <p>Nous vous encourageons à consulter régulièrement cette page pour prendre connaissance des éventuelles modifications.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h2 class="h4 text-primary">10. Contact</h2>
                            <p>Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits, vous pouvez nous contacter :</p>
                            <ul>
                                <li>Par email : <a href="mailto:privacy@cni.cam">privacy@cni.cam</a></li>
                                <li>Par téléphone : (+237) 222 222 222</li>
                                <li>Par courrier : Direction Générale de la Sûreté Nationale, Yaoundé, Cameroun</li>
                            </ul>
                        </div>
                        
                        <div class="text-center mt-5">
                            <p class="text-muted small">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>
