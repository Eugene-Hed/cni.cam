<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Demande;
use App\Models\DemandeCniDetail;
use App\Services\CniGeneratorService;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Storage;

class CniGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_cni_pdf()
    {
        Storage::fake('public');

        // 1. Create User
        $user = Utilisateur::create([
            'Nom' => 'TCHOUPO',
            'Prenom' => 'Herve',
            'Email' => 'herve@example.com',
            'NumeroTelephone' => '677123456',
            'Codeutilisateur' => 'CIT-12345',
            'RoleId' => 2,
            'IsActive' => 1
        ]);

        // 2. Create Demande
        $demande = Demande::create([
            'UtilisateurID' => $user->UtilisateurID,
            'NumeroReference' => 'REF-CNI-001',
            'TypeDemande' => 'CNI',
            'Statut' => 'Approuvee',
            'CheminSignature' => 'signatures/mock_citizen.png',
            'CheminSignatureOfficier' => 'signatures/mock_officer.png',
        ]);

        // 3. Create Details
        DemandeCniDetail::create([
            'DemandeID' => $demande->DemandeID,
            'TypeDemande' => 'premiere',
            'Nom' => 'TCHOUPO',
            'Prenom' => 'Herve',
            'DateNaissance' => '1990-05-15',
            'LieuNaissance' => 'Bafoussam',
            'Sexe' => 'M',
            'Taille' => 175,
            'Profession' => 'Ingénieur',
            'Adresse' => 'Bastos, Yaoundé',
        ]);

        // Mock signatures in fake storage
        Storage::disk('public')->put('signatures/mock_citizen.png', 'fake image');
        Storage::disk('public')->put('signatures/mock_officer.png', 'fake image');

        // 4. Generate QR (needed for PDF)
        $qrService = new QrCodeService();
        $qrPath = $qrService->generateForCni(['test' => 'data'], 'CNI00000001_qr');
        
        $this->assertTrue(Storage::disk('public')->exists($qrPath));

        // 5. Generate PDF
        $cniService = new CniGeneratorService();
        $pdfPath = $cniService->generate($demande, 'CNI00000001');

        // 6. Assertions
        $this->assertNotNull($pdfPath);
        $this->assertTrue(Storage::disk('public')->exists($pdfPath));
        
        echo "PDF generated at: storage/app/public/" . $pdfPath . "\n";
    }
}
