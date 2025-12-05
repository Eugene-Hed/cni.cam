<?php
include('../includes/config.php');
include('../includes/check_auth.php');

// Vérification du rôle président
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header('Location: /pages/login.php');
    exit();
}

// Vérification de l'ID de la demande
$demandeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$demandeId) {
    header('Location: demandes_nationalite.php');
    exit();
}

// Récupération des informations de la demande
$query = "SELECT d.*, 
          u.Nom as UserNom, u.Prenom as UserPrenom, u.Email, u.NumeroTelephone, u.PhotoUtilisateur,
          dnd.*, 
          cn.NumeroCertificat, cn.DateEmission, cn.CheminPDF, cn.SignaturePresidentielle, cn.CheminSignaturePresident
          FROM demandes d
          JOIN utilisateurs u ON d.UtilisateurID = u.UtilisateurID
          LEFT JOIN demande_nationalite_details dnd ON d.DemandeID = dnd.DemandeID
          LEFT JOIN certificatsnationalite cn ON d.DemandeID = cn.DemandeID
          WHERE d.DemandeID = :id AND d.Statut = 'Approuvee'";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $demandeId]);
$demande = $stmt->fetch();

if (!$demande) {
    $_SESSION['error_message'] = "La demande n'existe pas ou n'est pas approuvée.";
    header('Location: demandes_nationalite.php');
    exit();
}

// Vérifier que la signature présidentielle existe
if (empty($demande['SignaturePresidentielle']) || empty($demande['CheminSignaturePresident'])) {
    $_SESSION['error_message'] = "La signature présidentielle est requise avant de générer le certificat.";
    header('Location: traiter_demande.php?id=' . $demandeId);
    exit();
}

try {
    // Créer le répertoire pour les certificats si nécessaire
    $uploadsDir = '../uploads/certificats';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    // Nom du fichier PDF
    $fileName = 'certificat_nationalite_' . $demandeId . '_' . time() . '.pdf';
    $filePath = $uploadsDir . '/' . $fileName;
    
    // Génération du PDF avec FPDF ou une autre bibliothèque
    require_once('../vendor/fpdf/fpdf.php');
    
    class PDF extends FPDF {
        function Header() {
            // Logo
            $this->Image('../assets/images/Cameroun.gif', 10, 10, 30);
            // Police Arial gras 15
            $this->SetFont('Arial', 'B', 15);
            // Décalage à droite
            $this->Cell(80);
            // Titre
            $this->Cell(30, 10, 'REPUBLIQUE DU CAMEROUN', 0, 0, 'C');
            $this->Ln(7);
            $this->Cell(80);
            $this->Cell(30, 10, 'Paix - Travail - Patrie', 0, 0, 'C');
            $this->Ln(20);
        }
        
        function Footer() {
            // Positionnement à 1,5 cm du bas
            $this->SetY(-15);
            // Police Arial italique 8
            $this->SetFont('Arial', 'I', 8);
            // Numéro de page
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }
    
    // Création du PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Titre du document
    $pdf->Cell(0, 10, 'CERTIFICAT DE NATIONALITE CAMEROUNAISE', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Informations du certificat
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Certificat N°: ' . $demande['NumeroCertificat'], 0, 1);
    $pdf->Cell(0, 10, 'Date d\'émission: ' . date('d/m/Y', strtotime($demande['DateEmission'])), 0, 1);
    $pdf->Ln(5);
    
    // Informations du demandeur
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'INFORMATIONS PERSONNELLES', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Nom: ' . $demande['Nom'], 0, 1);
    $pdf->Cell(0, 10, 'Prénom: ' . $demande['Prenom'], 0, 1);
    $pdf->Cell(0, 10, 'Date de naissance: ' . date('d/m/Y', strtotime($demande['DateNaissance'])), 0, 1);
    $pdf->Cell(0, 10, 'Lieu de naissance: ' . $demande['LieuNaissance'], 0, 1);
    $pdf->Cell(0, 10, 'Sexe: ' . ($demande['Sexe'] == 'M' ? 'Masculin' : 'Féminin'), 0, 1);
    $pdf->Cell(0, 10, 'Adresse: ' . $demande['Adresse'], 0, 1);
    $pdf->Ln(5);
    
    // Motif de la nationalité
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'MOTIF DE LA NATIONALITÉ', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    $motifs = [
        'naissance' => 'Par naissance',
        'mariage' => 'Par mariage',
        'naturalisation' => 'Par naturalisation',
        'filiation' => 'Par filiation'
    ];
    
    $motif = isset($demande['Motif']) && isset($motifs[$demande['Motif']]) 
        ? $motifs[$demande['Motif']] 
        : 'Non spécifié';
    
    $pdf->Cell(0, 10, 'Nationalité camerounaise acquise: ' . $motif, 0, 1);
    $pdf->Ln(10);
    
    // Texte de certification
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->MultiCell(0, 10, 'Nous, Président de la République du Cameroun, certifions que la personne susmentionnée est de nationalité camerounaise conformément aux lois en vigueur.', 0, 'J');
    $pdf->Ln(15);
    
    // Signature
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Fait à Yaoundé, le ' . date('d/m/Y'), 0, 1, 'R');
    $pdf->Ln(5);
    
    // Ajouter la signature du président
    if (file_exists($demande['CheminSignaturePresident'])) {
        $pdf->Image($demande['CheminSignaturePresident'], 150, $pdf->GetY(), 40);
    }
    
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Le Président de la République', 0, 1, 'R');
    
    // Enregistrer le PDF
    $pdf->Output('F', $filePath);
    
    // Mettre à jour le chemin du PDF dans la base de données
    $stmt = $db->prepare("UPDATE certificatsnationalite SET CheminPDF = ? WHERE DemandeID = ?");
    $stmt->execute([$filePath, $demandeId]);
    
    // Ajouter une entrée dans l'historique
    $stmt = $db->prepare("INSERT INTO historique_demandes 
                        (DemandeID, AncienStatut, NouveauStatut, Commentaire, ModifiePar, DateModification) 
                        VALUES (?, 'Approuvee', 'Approuvee', 'Certificat PDF généré', ?, NOW())");
    $stmt->execute([$demandeId, $_SESSION['user_id']]);
    
    // Ajouter une notification
    $stmt = $db->prepare("INSERT INTO notifications 
                        (UtilisateurID, DemandeID, Contenu, TypeNotification, DateCreation) 
                        VALUES (?, ?, 'Votre certificat de nationalité est prêt et disponible au téléchargement.', 'document', NOW())");
    $stmt->execute([$demande['UtilisateurID'], $demandeId]);
    
    // Journal d'activité
    $stmt = $db->prepare("INSERT INTO journalactivites 
                        (UtilisateurID, TypeActivite, Description, AdresseIP) 
                        VALUES (?, 'Generation_Certificat', ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        "Génération du certificat de nationalité #$demandeId",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    $_SESSION['success_message'] = "Le certificat a été généré avec succès.";
    header('Location: traiter_demande.php?id=' . $demandeId);
    exit();
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur lors de la génération du certificat: " . $e->getMessage();
    header('Location: traiter_demande.php?id=' . $demandeId);
    exit();
}
