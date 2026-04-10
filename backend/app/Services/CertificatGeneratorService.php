<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Demande;
use Carbon\Carbon;

class CertificatGeneratorService
{
    /**
     * Generate the Nationality Certificate (A4)
     */
    public function generate(Demande $demande, string $numeroCertificat): string
    {
        $demande->load(['detailsNationalite', 'utilisateur']);
        $details = $demande->detailsNationalite;
        $user = $demande->utilisateur;
        
        $data = [
            'numero' => $numeroCertificat,
            'nom' => mb_strtoupper($details->Nom),
            'prenom' => ucfirst($details->Prenom),
            'date_naissance' => Carbon::parse($details->DateNaissance)->format('d/m/Y'),
            'lieu_naissance' => $details->LieuNaissance,
            'nom_pere' => $details->NomPere,
            'nom_mere' => $details->NomMere,
            'motif' => $details->Motif,
            'date_emission' => now()->format('d/m/Y'),
            'signature_president' => $this->getBase64Image($demande->certificatNationalite?->CheminSignaturePresident),
        ];

        $pdf = Pdf::loadView('pdfs.certificat', $data)->setPaper('a4', 'portrait');
            
        $filename = "certificats/{$numeroCertificat}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());
        
        return $filename;
    }

    private function getBase64Image(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $content = Storage::disk('public')->get($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode($content);
    }
}
