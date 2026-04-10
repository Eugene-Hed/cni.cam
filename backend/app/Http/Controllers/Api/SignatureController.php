<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\HistoriqueDemande;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    /**
     * POST /api/citoyen/demandes/{id}/signature
     * Save citizen's digital signature.
     */
    public function signerCitoyen(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'signature' => 'required|string', // Base64 PNG from canvas
        ]);

        $user = $request->user();
        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $user->UtilisateurID)
            ->firstOrFail();

        // Decode and save base64 signature
        $path = $this->saveSignature($request->signature, "signatures/signature_{$id}_" . time() . '.png');

        $ancienStatut = $demande->Statut;
        $demande->update([
            'SignatureEnregistree' => true,
            'CheminSignature' => $path,
            'DateSignature' => now(),
        ]);

        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => $ancienStatut,
            'NouveauStatut' => $ancienStatut,
            'Commentaire' => 'Signature enregistrée par le citoyen',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        ActivityLogger::log('Signature_Citoyen', "Signature citoyen enregistrée pour demande #{$id}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Signature enregistrée avec succès.',
        ]);
    }

    /**
     * POST /api/officier/demandes/{id}/signature
     * Save officer's digital signature.
     */
    public function signerOfficier(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $user = $request->user();
        $demande = Demande::findOrFail($id);

        $path = $this->saveSignature($request->signature, "signatures_officier/signature_officier_{$id}_" . time() . '.png');

        $ancienStatut = $demande->Statut;
        $demande->update([
            'SignatureOfficierEnregistree' => true,
            'CheminSignatureOfficier' => $path,
            'DateSignatureOfficier' => now(),
        ]);

        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => $ancienStatut,
            'NouveauStatut' => $ancienStatut,
            'Commentaire' => 'Signature de l\'officier enregistrée',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        ActivityLogger::log('Signature_Officier', "Signature officier enregistrée pour demande #{$id}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Signature de l\'officier enregistrée.',
        ]);
    }

    /**
     * POST /api/president/demandes/{id}/signature
     * Save president's digital signature.
     */
    public function signerPresident(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $user = $request->user();
        $demande = Demande::findOrFail($id);

        $path = $this->saveSignature($request->signature, "signatures_president/signature_president_{$id}_" . time() . '.png');

        // Store in certificatsnationalite if exists
        if ($demande->certificatNationalite) {
            $demande->certificatNationalite->update([
                'SignaturePresidentielle' => true,
                'CheminSignaturePresident' => $path,
            ]);
        }

        ActivityLogger::log('Signature_President', "Signature président enregistrée pour demande #{$id}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Signature présidentielle enregistrée.',
        ]);
    }

    /**
     * Save base64 signature image to storage.
     */
    private function saveSignature(string $base64, string $filename): string
    {
        // Remove data:image/png;base64, prefix if present
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $data = base64_decode($data);

        $path = "public/{$filename}";
        \Illuminate\Support\Facades\Storage::put($path, $data);

        return $filename;
    }
}
