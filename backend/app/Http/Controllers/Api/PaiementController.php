<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\HistoriqueDemande;
use App\Models\Paiement;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    /**
     * POST /api/citoyen/demandes/{id}/paiement
     * Simulate payment for a request.
     */
    public function payer(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'methode' => 'required|in:carte,mobile_money,orange_money,mtn_money',
            'numero' => 'nullable|string',
        ]);

        $user = $request->user();

        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $user->UtilisateurID)
            ->firstOrFail();

        if ($demande->StatutPaiement === 'Complete') {
            return response()->json([
                'success' => false,
                'message' => 'Le paiement a déjà été effectué pour cette demande.',
            ], 400);
        }

        // Simulate payment processing
        $reference = 'PAY-' . strtoupper(Str::random(10));

        $paiement = Paiement::create([
            'DemandeID' => $id,
            'Montant' => $demande->MontantPaiement,
            'StatutPaiement' => 'Complete',
            'ReferenceTransaction' => $reference,
        ]);

        // Update request payment status
        $ancienStatut = $demande->Statut;
        $demande->update([
            'StatutPaiement' => 'Complete',
            'Statut' => 'EnCours',
        ]);

        // History entry
        HistoriqueDemande::create([
            'DemandeID' => $id,
            'AncienStatut' => $ancienStatut,
            'NouveauStatut' => 'EnCours',
            'Commentaire' => 'Paiement effectué',
            'ModifiePar' => $user->UtilisateurID,
        ]);

        // Notification
        Notification::create([
            'UtilisateurID' => $user->UtilisateurID,
            'DemandeID' => $id,
            'Contenu' => "Paiement de {$demande->MontantPaiement} FCFA effectué. Référence: {$reference}",
            'TypeNotification' => 'paiement',
        ]);

        ActivityLogger::log('Paiement', "Paiement de {$demande->MontantPaiement} FCFA - Réf: {$reference}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'paiement' => $paiement,
            'reference' => $reference,
        ]);
    }
}
