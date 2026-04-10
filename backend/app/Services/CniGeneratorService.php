<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Demande;
use App\Models\CarteIdentite;
use Carbon\Carbon;

class CniGeneratorService
{
    /**
     * Generate the CNI PDF (Recto + Verso)
     */
    public function generate(Demande $demande, string $numeroCni): string
    {
        $demande->load(['detailsCni', 'utilisateur']);
        $details = $demande->detailsCni;
        $user = $demande->utilisateur;
        
        // Prepare data for the view
        $data = [
            'numero' => $numeroCni,
            'nom' => mb_strtoupper($details->Nom),
            'prenom' => ucfirst($details->Prenom),
            'date_naissance' => Carbon::parse($details->DateNaissance)->format('d.m.Y'),
            'lieu_naissance' => mb_strtoupper($details->LieuNaissance),
            'sexe' => $details->Sexe,
            'taille' => $details->Taille,
            'profession' => mb_strtoupper($details->Profession),
            'adresse' => mb_strtoupper($details->Adresse),
            'date_emission' => now()->format('d.m.Y'),
            'date_expiration' => now()->addYears(10)->format('d.m.Y'),
            'photo' => $this->getBase64Image($demande->documents()->where('TypeDocument', 'PhotoIdentite')->first()?->CheminFichier),
            'signature_citoyen' => $this->getBase64Image($demande->CheminSignature),
            'signature_officier' => $this->getBase64Image($demande->CheminSignatureOfficier),
            'qr_code' => $this->getBase64Image("qrcodes/{$numeroCni}_qr.png", 'public'),
            'mrz_line1' => $this->generateMrzLine1($details),
            'mrz_line2' => $this->generateMrzLine2($details, $numeroCni),
        ];

        // Generate PDF using custom paper size (85.6mm x 54mm)
        $pdf = Pdf::loadView('pdfs.cni', $data)
            ->setPaper([0, 0, 242.6, 153.1], 'landscape'); // 85.6mm x 54mm in pts
            
        $filename = "cni/{$numeroCni}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Generate First MRZ Line: P<CMR[NOM]<<[PRENOM]
     */
    private function generateMrzLine1($details): string
    {
        $nom = $this->sanitizeForMrz($details->Nom);
        $prenom = $this->sanitizeForMrz($details->Prenom);
        
        $line = "P<CMR" . $nom . "<<" . $prenom;
        return str_pad(substr($line, 0, 30), 30, '<');
    }

    /**
     * Generate Second MRZ Line: [NUMERO]CMR[BIRTH][SEX][EXPIRY]<<<<<<
     */
    private function generateMrzLine2($details, $numeroCni): string
    {
        $numero = str_pad(substr($numeroCni, 0, 9), 9, '<');
        $birth = Carbon::parse($details->DateNaissance)->format('ymd');
        $expiry = now()->addYears(10)->format('ymd');
        $sexe = $details->Sexe;
        
        $line = $numero . "CMR" . $birth . $sexe . $expiry . "<<<<<<<<<<<<<<";
        return substr($line, 0, 30);
    }

    /**
     * Sanitize text for MRZ (A-Z and <)
     */
    private function sanitizeForMrz(string $text): string
    {
        $text = strtoupper($text);
        $text = preg_replace('/[^A-Z]/', '<', $text);
        return $text;
    }

    /**
     * Get base64 encoded image for PDF embedding.
     */
    private function getBase64Image(?string $path, string $disk = 'public'): ?string
    {
        if (!$path || !Storage::disk($disk)->exists($path)) {
            return null;
        }

        $content = Storage::disk($disk)->get($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode($content);
    }
}
