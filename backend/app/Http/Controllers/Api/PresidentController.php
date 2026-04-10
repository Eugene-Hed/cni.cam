<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\CertificatNationalite;
use App\Models\HistoriqueDemande;
use App\Models\Notification;
use App\Services\ActivityLogger;
use App\Services\CertificatGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresidentController extends Controller
{
    /**
     * GET /api/president/demandes
     */
    public function listeDemandes(Request $request): JsonResponse
    {
        $query = Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])
            ->with(['utilisateur', 'detailsNationalite', 'documents']);

        if ($request->has('statut')) {
            $query->where('Statut', $request->statut);
        }

        $demandes = $query->orderByDesc('DateSoumission')->paginate(15);

        return response()->json(['success' => true, 'data' => $demandes]);
    }

    /**
     * GET /api/president/demandes/{id}
     */
    public function detailDemande(int $id): JsonResponse
    {
        $demande = Demande::with([
            'utilisateur', 'detailsNationalite', 'documents',
            'paiements', 'historique.modifiePar', 'certificatNationalite',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $demande]);
    }

    /**
     * PUT /api/president/demandes/{id}/statut
     */
    public function changerStatut(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:EnCours,Approuvee,Rejetee',
            'commentaire' => 'nullable|string',
        ]);

        $user = $request->user();
        $demande = Demande::findOrFail($id);
        $ancienStatut = $demande->Statut;

        $demande->update(['Statut' => $request->statut]);

        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => $ancienStatut,
            'NouveauStatut' => $request->statut,
            'Commentaire' => $request->commentaire ?? '',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        Notification::create([
            'UtilisateurID' => $demande->UtilisateurID,
            'DemandeID' => $id,
            'Contenu' => "Votre demande de nationalité {$demande->NumeroReference} : {$request->statut}",
            'TypeNotification' => 'statut',
        ]);

        ActivityLogger::log('Traitement_Nationalite', "Demande nationalité #{$id} → {$request->statut}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => "Demande mise à jour: {$request->statut}",
        ]);
    }

    /**
     * POST /api/president/demandes/{id}/generer-certificat
     */
    public function genererCertificat(Request $request, int $id, CertificatGeneratorService $certService): JsonResponse
    {
        $user = $request->user();
        $demande = Demande::with(['detailsNationalite', 'utilisateur', 'certificatNationalite'])->findOrFail($id);

        if ($demande->Statut !== 'Approuvee') {
            return response()->json([
                'success' => false,
                'message' => 'La demande doit être approuvée.',
            ], 400);
        }

        // Generate unique certificate number
        $lastCert = CertificatNationalite::orderByDesc('CertificatID')->first();
        $nextNumber = $lastCert ? intval(substr($lastCert->NumeroCertificat, 4)) + 1 : 1;
        $numeroCertificat = 'CERT' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // Generate PDF
        $pdfPath = $certService->generate($demande, $numeroCertificat);

        $certificat = CertificatNationalite::create([
            'DemandeID' => $id,
            'UtilisateurID' => $demande->UtilisateurID,
            'NumeroCertificat' => $numeroCertificat,
            'DateEmission' => now()->toDateString(),
            'CheminFichier' => $pdfPath,
            'Statut' => 'Valide',
        ]);

        $demande->update([
            'Statut' => 'Terminee',
            'DateAchevement' => now(),
        ]);

        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => 'Approuvee',
            'NouveauStatut' => 'Terminee',
            'Commentaire' => 'Certificat de nationalité généré',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        Notification::create([
            'UtilisateurID' => $demande->UtilisateurID,
            'DemandeID' => $id,
            'Contenu' => "Votre certificat de nationalité {$numeroCertificat} a été généré.",
            'TypeNotification' => 'certificat',
        ]);

        ActivityLogger::log('Generation_Certificat', "Certificat {$numeroCertificat} généré", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => "Certificat {$numeroCertificat} généré avec succès.",
            'certificat' => $certificat,
        ]);
    }
}
