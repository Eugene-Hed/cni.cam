<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../includes/config.php');
require '../vendor/autoload.php';
require_once('../vendor/setasign/fpdf/fpdf.php');

/**
 * Conversion de l'encodage du texte
 */
function convertEncoding($text) {
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    } else {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }
}

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Vérifier si l'utilisateur est connecté et autorisé
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    header('Location: /pages/login.php');
    exit();
}

$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$demandeId) {
    header('Location: demandes_cni.php');
    exit();
}

// Vérification de l'existence de la signature du citoyen
$query = "SELECT SignatureEnregistree, SignatureOfficierEnregistree, CheminSignature, CheminSignatureOfficier 
          FROM demandes 
          WHERE DemandeID = :id AND Statut = 'Approuvee'";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$signatures = $stmt->fetch();

if (!$signatures || !$signatures['SignatureEnregistree']) {
    $_SESSION['error_message'] = "La signature du citoyen n'a pas encore été enregistrée. La CNI ne peut pas être générée.";
    header('Location: traiter_demande.php?id=' . $demandeId);
    exit();
}

// Récupération des informations de la demande
$query = "SELECT d.*, dc.*, u.Email, u.NumeroTelephone, doc.CheminFichier as Photo
          FROM demandes d
          JOIN demande_cni_details dc ON d.DemandeID = dc.DemandeID
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          LEFT JOIN documents doc ON d.DemandeID = doc.DemandeID AND doc.TypeDocument = 'PhotoIdentite'
          WHERE d.DemandeID = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demande = $stmt->fetch();

// Récupération de la signature du citoyen
$signaturePath = $signatures['CheminSignature'];
if (!$signaturePath || !file_exists($signaturePath)) {
    $query = "SELECT CheminFichier FROM documents WHERE DemandeID = :id AND TypeDocument = 'Signature'";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $demandeId]);
    $signaturePath = $stmt->fetchColumn();
}

// Récupération de la signature de l'officier
$signatureOfficierPath = $signatures['CheminSignatureOfficier'];

// Génération d'un QR Code temporaire pour l'aperçu
$qrContent = json_encode([
    'numero' => 'PREVIEW-CNI',
    'nom' => $demande['Nom'],
    'prenom' => $demande['Prenom'],
    'dateNaissance' => $demande['DateNaissance'],
    'lieuNaissance' => $demande['LieuNaissance'],
    'nationalite' => 'Camerounaise',
    'sexe' => $demande['Sexe'],
    'taille' => $demande['Taille'],
    'profession' => $demande['Profession'],
    'adresse' => $demande['Adresse'],
    'dateEmission' => date('Y-m-d'),
    'dateExpiration' => date('Y-m-d', strtotime('+10 years'))
]);

$qrCode = new QrCode($qrContent);
$writer = new PngWriter();
$result = $writer->write($qrCode);

if (!file_exists('../uploads/qrcodes')) {
    mkdir('../uploads/qrcodes', 0777, true);
}

$previewQrPath = "../uploads/qrcodes/preview_qr_" . $demandeId . ".png";
$result->saveToFile($previewQrPath);

/**
 * Classe CNICAM améliorée pour la génération de la CNI
 */
class CNICAM extends FPDF {
    // Pas d'en-tête et de pied de page pour cette carte
    protected $angle = 0;
    function Header() {}
    function Footer() {}

    // Création de la face avant (recto)
    function createFrontPage($data, $photo, $signature) {
        $this->AddPage('L', array(85.6, 54));
        $this->SetAutoPageBreak(false);
        // Fond moderne avec une teinte neutre
        $this->SetFillColor(245, 245, 240);
        $this->Rect(0, 0, 85.6, 54, 'F');

        // Zone d'en-tête : Logo, intitulé officiel
        if (file_exists('../assets/images/Cameroun.gif')) {
            try {
                $this->Image('../assets/images/Cameroun.gif', 3, 3, 10);
            } catch (Exception $e) {
                error_log("Erreur chargement logo : " . $e->getMessage());
            }
        }
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(0, 70, 0);
        $this->SetXY(15, 3);
        $this->Cell(65, 4, convertEncoding('RÉPUBLIQUE DU CAMEROUN - MINISTÈRE DE L’INTÉRIEUR'), 0, 2, 'C');
        $this->SetFont('Arial', '', 5);
        $this->Cell(65, 3, convertEncoding('Paix – Travail – Patrie'), 0, 2, 'C');
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(150, 0, 0);
        $this->Cell(65, 4, convertEncoding('CARTE NATIONALE D’IDENTITÉ'), 0, 1, 'C');
        // Séparateur visuel
        $this->SetDrawColor(200, 200, 200);
        $this->Line(3, 12, 82.6, 12);

        // Affichage de la photo du titulaire
        if ($photo && file_exists($photo) && filesize($photo) > 0) {
            try {
                $this->Image($photo, 3, 14, 25, 30);
                $this->SetDrawColor(0, 0, 0);
                $this->Rect(3, 14, 25, 30);
            } catch (Exception $e) {
                error_log("Erreur chargement photo : " . $e->getMessage());
            }
        }
        
        // Informations personnelles à droite de la photo
        $this->SetXY(30, 14);
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(50, 3, convertEncoding('NOM / SURNAME:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(35, 3, convertEncoding(isset($data['nom']) ? mb_strtoupper($data['nom']) : ''), 0, 1);

        $this->SetXY(30, 18);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(50, 3, convertEncoding('PRÉNOMS / GIVEN NAMES:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(35, 3, convertEncoding(isset($data['prenom']) ? ucfirst($data['prenom']) : ''), 0, 1);

        $this->SetXY(30, 22);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(50, 3, convertEncoding('DATE DE NAISSANCE:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(35, 3, isset($data['dateNaissance']) ? date('d.m.Y', strtotime($data['dateNaissance'])) : '', 0, 1);

        $this->SetXY(30, 26);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(50, 3, convertEncoding('SEXE / SEX:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(35, 3, isset($data['sexe']) ? $data['sexe'] : '', 0, 1);

        $this->SetXY(30, 30);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(50, 3, convertEncoding('NUMÉRO CNI:'), 0, 0);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(35, 3, isset($data['numero']) ? $data['numero'] : '', 0, 1);

        $this->SetXY(30, 34);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(50, 3, convertEncoding('DATE D’EXPIRATION:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(35, 3, date('d.m.Y', strtotime('+10 years')), 0, 1);
        
        // Zone de signature du titulaire
        if ($signature && file_exists($signature) && filesize($signature) > 0) {
            try {
                $this->Image($signature, 30, 40, 35, 8);
                $this->SetDrawColor(0, 0, 0);
                $this->Rect(30, 40, 35, 8);
            } catch (Exception $e) {
                error_log("Erreur chargement signature : " . $e->getMessage());
            }
        }
        $this->SetXY(30, 48);
        $this->SetFont('Arial', 'I', 5);
        $this->Cell(35, 2, convertEncoding('Signature du titulaire / Holder’s Signature'), 0, 0);
    }

    // Création de la face arrière (verso)
    function createBackPage($data, $qr, $signatureOfficier) {
        $this->AddPage('L', array(85.6, 54));
        $this->SetAutoPageBreak(false);
        $this->SetFillColor(245, 245, 240);
        $this->Rect(0, 0, 85.6, 54, 'F');

        // Affichage du QR Code avec contour
        if ($qr && file_exists($qr) && filesize($qr) > 0) {
            try {
                $this->Image($qr, 5, 5, 20, 20);
                $this->SetDrawColor(150,150,150);
                $this->Rect(5, 5, 20, 20);
            } catch (Exception $e) {
                error_log("Erreur chargement QR Code : " . $e->getMessage());
            }
        }
        
        // Informations complémentaires
        $this->SetXY(30, 5);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(45, 3, convertEncoding('LIEU DE NAISSANCE / PLACE OF BIRTH:'), 0, 1);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(45, 3, convertEncoding(isset($data['lieuNaissance']) ? $data['lieuNaissance'] : ''), 0, 'L');

        $this->SetXY(30, 13);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(45, 3, convertEncoding('PROFESSION / OCCUPATION:'), 0, 1);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(45, 3, convertEncoding(isset($data['profession']) ? mb_strtoupper($data['profession']) : ''), 0, 'L');

        $this->SetXY(30, 21);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(45, 3, convertEncoding('ADRESSE / ADDRESS:'), 0, 1);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(45, 3, convertEncoding(isset($data['adresse']) ? mb_strtoupper($data['adresse']) : ''), 0, 'L');

        $this->SetXY(30, 29);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(45, 3, convertEncoding('TAILLE / HEIGHT:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(45, 3, (isset($data['taille']) ? $data['taille'] : '0') . ' cm', 0, 1);

        $this->SetXY(5, 30);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(25, 3, convertEncoding('DATE DÉLIVRANCE / ISSUE DATE:'), 0, 0);
        $this->SetFont('Arial', '', 6);
        $this->Cell(25, 3, date('d.m.Y'), 0, 1);

        // Zone de signature de l'officier
        $this->SetXY(45, 30);
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(35, 3, convertEncoding('LE DGNS / DGNS'), 0, 1, 'C');
        if ($signatureOfficier && file_exists($signatureOfficier) && filesize($signatureOfficier) > 0) {
            try {
                $this->Image($signatureOfficier, 45, 35, 25, 10);
            } catch (Exception $e) {
                error_log("Erreur chargement signature officier : " . $e->getMessage());
                if (file_exists('../assets/images/signature.png') && filesize('../assets/images/signature.png') > 0) {
                    try {
                        $this->Image('../assets/images/signature.png', 45, 35, 25, 10);
                    } catch (Exception $e) {
                        error_log("Erreur chargement signature par défaut : " . $e->getMessage());
                    }
                }
            }
        } else {
            if (file_exists('../assets/images/signature.png') && filesize('../assets/images/signature.png') > 0) {
                try {
                    $this->Image('../assets/images/signature.png', 45, 35, 25, 10);
                } catch (Exception $e) {
                    error_log("Erreur chargement signature par défaut : " . $e->getMessage());
                }
            }
        }
        
        // Zone MRZ modernisée avec police Courier
        $this->SetFont('Courier', 'B', 8);
        $this->SetXY(5, 45);
        $nom = isset($data['nom']) ? strtoupper($data['nom']) : 'XXXXXXXX';
        $prenom = isset($data['prenom']) ? strtoupper($data['prenom']) : 'XXXXXXXX';
        $this->Cell(75, 3, 'P<CMR' . $nom . '<<' . $prenom, 0, 1, 'L');
        
        $birthDate = isset($data['dateNaissance']) ? date('ymd', strtotime($data['dateNaissance'])) : '000000';
        $expiryDate = date('ymd', strtotime('+10 years'));
        $sexe = isset($data['sexe']) ? $data['sexe'] : 'X';
        $numero = isset($data['numero']) ? $data['numero'] : 'XXXXXXXX';
        $this->SetXY(5, 49);
        $this->Cell(75, 3, $numero . 'CMR' . $birthDate . $sexe . $expiryDate . '<<<<<<', 0, 1, 'L');
        
        // Ajout d'un filigrane pour la sécurité
        $this->SetFont('Arial', 'I', 4);
        $this->SetTextColor(220, 220, 220);
        $this->SetXY(5, 25);
        $this->RotatedText(30, 40, 'OFFICIEL', 45);
    }
    
    // Méthode utilitaire pour afficher un texte tourné
    function Rotate($angle, $x=-1, $y=-1) {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    
    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
}

// Traitement de la génération définitive de la CNI
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        $query = "SELECT MAX(CAST(SUBSTRING(NumeroCarteIdentite, 4) AS UNSIGNED)) as max_num FROM cartesidentite";
        $stmt = $db->query($query);
        $result = $stmt->fetch();
        $nextNum = str_pad(($result['max_num'] + 1), 8, '0', STR_PAD_LEFT);
        $numeroCNI = "CNI" . $nextNum;

        $qrContent = json_encode([
            'numero' => $numeroCNI,
            'nom' => $demande['Nom'],
            'prenom' => $demande['Prenom'],
            'dateNaissance' => $demande['DateNaissance'],
            'lieuNaissance' => $demande['LieuNaissance'],
            'nationalite' => 'Camerounaise',
            'sexe' => $demande['Sexe'],
            'taille' => $demande['Taille'],
            'profession' => $demande['Profession'],
            'adresse' => $demande['Adresse'],
            'dateEmission' => date('Y-m-d'),
            'dateExpiration' => date('Y-m-d', strtotime('+10 years'))
        ]);

        $qrCode = new QrCode($qrContent);
        $writer = new PngWriter();
        $resultQr = $writer->write($qrCode);
        
        if (!file_exists('../uploads/qrcodes')) {
            mkdir('../uploads/qrcodes', 0777, true);
        }
        if (!file_exists('../uploads/cni')) {
            mkdir('../uploads/cni', 0777, true);
        }
        
        $qrPath = "../uploads/qrcodes/{$numeroCNI}_qr.png";
        $resultQr->saveToFile($qrPath);

        $pdf = new CNICAM();
        $pdf->createFrontPage([
            'numero' => $numeroCNI,
            'nom' => $demande['Nom'],
            'prenom' => $demande['Prenom'],
            'dateNaissance' => $demande['DateNaissance'],
            'sexe' => $demande['Sexe'],
            'taille' => $demande['Taille'],
        ], $demande['Photo'], $signaturePath);

        $pdf->createBackPage([
            'nom' => $demande['Nom'],
            'prenom' => $demande['Prenom'],
            'dateNaissance' => $demande['DateNaissance'],
            'sexe' => $demande['Sexe'],
            'lieuNaissance' => $demande['LieuNaissance'],
            'profession' => $demande['Profession'],
            'taille' => $demande['Taille'],
            'adresse' => $demande['Adresse'],
            'numero' => $numeroCNI
        ], $qrPath, $signatureOfficierPath);

        $pdfPath = "../uploads/cni/{$numeroCNI}.pdf";
        $pdf->Output('F', $pdfPath);

        $stmt = $db->prepare("INSERT INTO cartesidentite 
            (UtilisateurID, DemandeID, NumeroCarteIdentite, DateEmission, DateExpiration, CodeQR, CheminFichier, Statut) 
            VALUES (:userId, :demandeId, :numero, :dateEmission, :dateExpiration, :codeQR, :cheminPDF, 'Active')");
        $stmt->execute([
            'userId' => $demande['UtilisateurID'],
            'demandeId' => $demandeId,
            'numero' => $numeroCNI,
            'dateEmission' => date('Y-m-d'),
            'dateExpiration' => date('Y-m-d', strtotime('+10 years')),
            'codeQR' => $qrPath,
            'cheminPDF' => $pdfPath
        ]);

        $stmt = $db->prepare("UPDATE demandes SET Statut = 'Terminee' WHERE DemandeID = :id");
        $stmt->execute(['id' => $demandeId]);

        $stmt = $db->prepare("INSERT INTO historique_demandes 
            (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
            VALUES (:demandeId, 'Approuvee', 'Terminee', 'CNI générée avec succès', :userId, NOW())");
        $stmt->execute([
            'demandeId' => $demandeId,
            'userId' => $_SESSION['user_id']
        ]);

        $stmt = $db->prepare("INSERT INTO journalactivites 
            (UtilisateurID, TypeActivite, Description, AdresseIP) 
            VALUES (:userId, 'Generation_CNI', :description, :ip)");
        $stmt->execute([
            'userId' => $_SESSION['user_id'],
            'description' => "Génération de la CNI numéro " . $numeroCNI,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        $stmt = $db->prepare("INSERT INTO notifications 
            (UtilisateurID, DemandeID, Contenu, TypeNotification) 
            VALUES (:userId, :demandeId, :content, 'Generation_CNI')");
        $stmt->execute([
            'userId' => $demande['UtilisateurID'],
            'demandeId' => $demandeId,
            'content' => 'Votre CNI a été générée avec succès.'
        ]);

        $db->commit();
        header("Location: visualiser_cni.php?id=" . $numeroCNI);
        exit();

    } catch(Exception $e) {
        $db->rollBack();
        $error = "Erreur lors de la génération : " . $e->getMessage();
    }
}

// Génération des aperçus pour affichage
$previewCniNumber = "PREVIEW-" . str_pad($demandeId, 8, '0', STR_PAD_LEFT);

$previewRecto = new CNICAM();
$previewRecto->createFrontPage([
    'numero' => $previewCniNumber,
    'nom' => $demande['Nom'],
    'prenom' => $demande['Prenom'],
    'dateNaissance' => $demande['DateNaissance'],
    'sexe' => $demande['Sexe'],
    'taille' => $demande['Taille'],
], $demande['Photo'], $signaturePath);

$previewVerso = new CNICAM();
$previewVerso->createBackPage([
    'nom' => $demande['Nom'],
    'prenom' => $demande['Prenom'],
    'dateNaissance' => $demande['DateNaissance'],
    'sexe' => $demande['Sexe'],
    'lieuNaissance' => $demande['LieuNaissance'],
    'profession' => $demande['Profession'],
    'taille' => $demande['Taille'],
    'adresse' => $demande['Adresse'],
    'numero' => $previewCniNumber
], $previewQrPath, $signatureOfficierPath);

$previewRectoPath = "../uploads/cni/preview_recto_" . $demandeId . ".pdf";
$previewVersoPath = "../uploads/cni/preview_verso_" . $demandeId . ".pdf";
$previewRecto->Output('F', $previewRectoPath);
$previewVerso->Output('F', $previewVersoPath);

$previewRectoImagePath = "../uploads/cni/preview_recto_" . $demandeId . ".png";
$previewVersoImagePath = "../uploads/cni/preview_verso_" . $demandeId . ".png";

if (extension_loaded('imagick')) {
    try {
        $imagick = new Imagick();
        $imagick->readImage($previewRectoPath . "[0]");
        $imagick->setImageFormat('png');
        $imagick->writeImage($previewRectoImagePath);
        
        $imagick->clear();
        $imagick->readImage($previewVersoPath . "[0]");
        $imagick->setImageFormat('png');
        $imagick->writeImage($previewVersoImagePath);
    } catch (Exception $e) {
        error_log("Erreur lors de la conversion des aperçus : " . $e->getMessage());
    }
}

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include('includes/sidebar.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Génération CNI</h1>
                <a href="traiter_demande.php?id=<?php echo $demandeId; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Aperçu de la CNI</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Recto (Face avant)</h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if(file_exists($previewRectoImagePath)): ?>
                                        <img src="<?php echo $previewRectoImagePath; ?>" alt="Aperçu recto CNI" class="img-fluid border">
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            L'aperçu du recto n'est pas disponible. La CNI sera générée avec les informations suivantes :
                                            <ul class="mt-2 text-start">
                                                <li><strong>Nom:</strong> <?php echo htmlspecialchars(mb_strtoupper($demande['Nom'])); ?></li>
                                                <li><strong>Prénom:</strong> <?php echo htmlspecialchars(ucfirst($demande['Prenom'])); ?></li>
                                                <li><strong>Date de naissance:</strong> <?php echo date('d.m.Y', strtotime($demande['DateNaissance'])); ?></li>
                                                <li><strong>Sexe:</strong> <?php echo $demande['Sexe']; ?></li>
                                                <li><strong>Date d'expiration:</strong> <?php echo date('d.m.Y', strtotime('+10 years')); ?></li>
                                                <li><strong>Numéro CNI:</strong> <?php echo $previewCniNumber; ?></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Verso (Face arrière)</h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if(file_exists($previewVersoImagePath)): ?>
                                        <img src="<?php echo $previewVersoImagePath; ?>" alt="Aperçu verso CNI" class="img-fluid border">
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            L'aperçu du verso n'est pas disponible. La CNI sera générée avec les informations suivantes :
                                            <ul class="mt-2 text-start">
                                                <li><strong>Lieu de naissance:</strong> <?php echo htmlspecialchars($demande['LieuNaissance']); ?></li>
                                                <li><strong>Profession:</strong> <?php echo htmlspecialchars(mb_strtoupper($demande['Profession'])); ?></li>
                                                <li><strong>Taille:</strong> <?php echo $demande['Taille']; ?> cm</li>
                                                <li><strong>Adresse:</strong> <?php echo htmlspecialchars(mb_strtoupper($demande['Adresse'])); ?></li>
                                                <li><strong>Date de délivrance:</strong> <?php echo date('d.m.Y'); ?></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Confirmation de génération</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations du demandeur</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Nom</th>
                                    <td><?php echo htmlspecialchars($demande['Nom']); ?></td>
                                </tr>
                                <tr>
                                    <th>Prénom</th>
                                    <td><?php echo htmlspecialchars($demande['Prenom']); ?></td>
                                </tr>
                                <tr>
                                    <th>Date de naissance</th>
                                    <td><?php echo date('d/m/Y', strtotime($demande['DateNaissance'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Lieu de naissance</th>
                                    <td><?php echo htmlspecialchars($demande['LieuNaissance']); ?></td>
                                </tr>
                                <tr>
                                    <th>Profession</th>
                                    <td><?php echo htmlspecialchars($demande['Profession']); ?></td>
                                </tr>
                                <tr>
                                    <th>Adresse</th>
                                    <td><?php echo htmlspecialchars($demande['Adresse']); ?></td>
                                </tr>
                                <tr>
                                    <th>Taille</th>
                                    <td><?php echo $demande['Taille']; ?> cm</td>
                                </tr>
                                <tr>
                                    <th>Sexe</th>
                                    <td><?php echo $demande['Sexe'] == 'M' ? 'Masculin' : 'Féminin'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <h6>Photo d'identité</h6>
                                    <?php if($demande['Photo'] && file_exists($demande['Photo'])): ?>
                                        <img src="<?php echo $demande['Photo']; ?>" alt="Photo d'identité" class="img-thumbnail" style="max-width: 100px;">
                                    <?php else: ?>
                                        <div class="alert alert-warning">Photo non disponible</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6>Signature citoyen</h6>
                                    <?php if($signaturePath && file_exists($signaturePath)): ?>
                                        <img src="<?php echo $signaturePath; ?>" alt="Signature du citoyen" class="img-thumbnail" style="max-width: 100px;">
                                    <?php else: ?>
                                        <div class="alert alert-warning">Signature non disponible</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6>Signature officier</h6>
                                    <?php if($signatureOfficierPath && file_exists($signatureOfficierPath)): ?>
                                        <img src="<?php echo $signatureOfficierPath; ?>" alt="Signature de l'officier" class="img-thumbnail" style="max-width: 100px;">
                                    <?php else: ?>
                                        <div class="alert alert-warning">Signature non disponible</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <h6>QR Code</h6>
                                    <?php if(file_exists($previewQrPath)): ?>
                                        <img src="<?php echo $previewQrPath; ?>" alt="QR Code" class="img-thumbnail" style="max-width: 100px;">
                                    <?php else: ?>
                                        <div class="alert alert-warning">QR Code non disponible</div>
                                    <?php endif; ?>
                                    <p class="small text-muted mt-2">Le QR Code contient toutes les informations de la CNI</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form method="POST" class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Générer la CNI
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
