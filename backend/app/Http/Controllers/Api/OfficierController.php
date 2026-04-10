<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Document;
use App\Models\HistoriqueDemande;
use App\Models\Notification;
use App\Models\CarteIdentite;
use App\Services\ActivityLogger;
use App\Services\QrCodeService;
use App\Services\CniGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficierController extends Controller
{
    /**
     * GET /api/officier/demandes
     * List CNI requests for the officer.
     */
    public function listeDemandes(Request $request): JsonResponse
    {
        $query = Demande::where('TypeDemande', 'CNI')
            ->with(['utilisateur', 'detailsCni', 'documents']);

        // Filter by status
        if ($request->has('statut')) {
            $query->where('Statut', $request->statut);
        }

        $demandes = $query->orderByDesc('DateSoumission')->paginate(15);

        return response()->json(['success' => true, 'data' => $demandes]);
    }

    /**
     * GET /api/officier/demandes/{id}
     */
    public function detailDemande(int $id): JsonResponse
    {
        $demande = Demande::with([
            'utilisateur', 'detailsCni', 'documents',
            'paiements', 'historique.modifiePar', 'carteIdentite',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $demande]);
    }

    /**
     * PUT /api/officier/demandes/{id}/statut
     * Approve or reject a request.
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

        // Notify the citizen
        Notification::create([
            'UtilisateurID' => $demande->UtilisateurID,
            'DemandeID' => $id,
            'Contenu' => "Votre demande {$demande->NumeroReference} a été mise à jour: {$request->statut}",
            'TypeNotification' => 'statut',
        ]);

        ActivityLogger::log('Traitement_Demande', "Demande #{$id} → {$request->statut}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => "Demande mise à jour: {$request->statut}",
        ]);
    }

    /**
     * PUT /api/officier/documents/{id}/valider
     * Validate or reject a document.
     */
    public function validerDocument(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:Approuve,Rejete',
        ]);

        $user = $request->user();
        $document = Document::findOrFail($id);

        $document->update([
            'StatutValidation' => $request->statut,
            'DateValidation' => now(),
            'ValidePar' => $user->UtilisateurID,
        ]);

        ActivityLogger::log('Validation_Document', "Document #{$id} {$request->statut}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => "Document {$request->statut}.",
        ]);
    }

    /**
     * POST /api/officier/demandes/{id}/generer-cni
     * Generate the CNI (PDF + QR code).
     */
    public function genererCni(Request $request, int $id, QrCodeService $qrService, CniGeneratorService $cniService): JsonResponse
    {
        $user = $request->user();
        $demande = Demande::with(['detailsCni', 'utilisateur', 'documents'])->findOrFail($id);

        if ($demande->Statut !== 'Approuvee') {
            return response()->json([
                'success' => false,
                'message' => 'La demande doit être approuvée avant de générer la CNI.',
            ], 400);
        }

        // Check for signatures and photo
        if (!$demande->CheminSignature || !$demande->CheminSignatureOfficier) {
            return response()->json([
                'success' => false,
                'message' => 'La signature du citoyen ou de l\'officier est manquante.',
            ], 400);
        }

        // Generate unique CNI number
        $lastCard = CarteIdentite::orderByDesc('CarteID')->first();
        $nextNumber = $lastCard ? intval(substr($lastCard->NumeroCarteIdentite, 3)) + 1 : 1;
        $numeroCni = 'CNI' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // 1. Generate QR Code
        $details = $demande->detailsCni;
        $qrData = [
            'numero' => $numeroCni,
            'nom' => $details->Nom,
            'prenom' => $details->Prenom,
            'dateNaissance' => $details->DateNaissance,
            'lieuNaissance' => $details->LieuNaissance,
            ' nationalite' => 'Camerounaise',
            'sexe' => $details->Sexe,
            'taille' => $details->Taille,
            'profession' => $details->Profession,
            'adresse' => $details->Adresse,
            'dateEmission' => now()->toDateString(),
            'dateExpiration' => now()->addYears(10)->toDateString(),
        ];
        $qrPath = $qrService->generateForCni($qrData, "{$numeroCni}_qr");

        // 2. Generate PDF
        $pdfPath = $cniService->generate($demande, $numeroCni);

        // 3. Create card record
        $carte = CarteIdentite::create([
            'UtilisateurID' => $demande->UtilisateurID,
            'DemandeID' => $id,
            'NumeroCarteIdentite' => $numeroCni,
            'DateEmission' => now()->toDateString(),
            'DateExpiration' => now()->addYears(10)->toDateString(),
            'Statut' => 'Active',
            'CodeQR' => $qrPath,
            'CheminFichier' => $pdfPath,
        ]);

        // Update request status to completed
        $demande->update([
            'Statut' => 'Terminee',
            'DateAchevement' => now(),
        ]);

        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => 'Approuvee',
            'NouveauStatut' => 'Terminee',
            'Commentaire' => 'CNI générée avec succès',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        Notification::create([
            'UtilisateurID' => $demande->UtilisateurID,
            'DemandeID' => $id,
            'Contenu' => "Votre CNI {$numeroCni} a été générée. Vous pouvez la télécharger.",
            'TypeNotification' => 'cni',
        ]);

        ActivityLogger::log('Generation_CNI', "Génération de la CNI numéro {$numeroCni}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => "CNI {$numeroCni} générée avec succès.",
            'carte' => $carte,
        ]);
    }

    /**
     * GET /api/officier/demandes/{id}/visualiser-cni
     */
    public function visualiserCni(int $id): JsonResponse
    {
        $demande = Demande::with(['carteIdentite', 'detailsCni', 'utilisateur'])->findOrFail($id);

        if (!$demande->carteIdentite) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune CNI générée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'carte' => $demande->carteIdentite,
                'details' => $demande->detailsCni,
                'utilisateur' => $demande->utilisateur,
            ],
        ]);
    }
}
