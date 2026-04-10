<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Demandes\StoreDemandeRequest;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\DemandeCniDetail;
use App\Models\DemandeNationaliteDetail;
use App\Models\HistoriqueDemande;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    /**
     * POST /api/citoyen/demandes
     * Create a new request (CNI or Nationalité).
     */
    public function store(StoreDemandeRequest $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->type_demande;
        $prefix = $type === 'CNI' ? 'CNI' : 'NAT';
        $montant = $type === 'CNI' ? 10000 : 5000;

        // Generate unique reference number
        $reference = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        $demande = Demande::create([
            'NumeroReference' => $reference,
            'UtilisateurID' => $user->UtilisateurID,
            'TypeDemande' => $type,
            'SousTypeDemande' => $request->sous_type,
            'Statut' => 'Soumise',
            'MontantPaiement' => $montant,
            'StatutPaiement' => 'En attente',
            'SignatureRequise' => true,
            'SignatureEnregistree' => false,
        ]);

        // Store type-specific details
        if ($type === 'CNI') {
            DemandeCniDetail::create([
                'DemandeID' => $demande->DemandeID,
                'TypeDemande' => $request->sous_type,
                'Nom' => $request->nom,
                'Prenom' => $request->prenom,
                'DateNaissance' => $request->date_naissance,
                'LieuNaissance' => $request->lieu_naissance,
                'Adresse' => $request->adresse,
                'Sexe' => $request->sexe,
                'Taille' => $request->taille,
                'Profession' => $request->profession,
                'StatutCivil' => $request->statut_civil,
                'NumeroCNIPrecedente' => $request->numero_cni_precedente,
                'DatePerteVol' => $request->date_perte_vol,
                'NumeroDecretNaturalisation' => $request->numero_decret_naturalisation,
                'NationalitePere' => $request->nationalite_pere,
                'NationaliteMere' => $request->nationalite_mere,
            ]);
        } else {
            DemandeNationaliteDetail::create([
                'DemandeID' => $demande->DemandeID,
                'Nom' => $request->nom,
                'Prenom' => $request->prenom,
                'DateNaissance' => $request->date_naissance,
                'LieuNaissance' => $request->lieu_naissance,
                'Sexe' => $request->sexe,
                'NomPere' => $request->nom_pere,
                'NomMere' => $request->nom_mere,
                'Adresse' => $request->adresse,
                'Ville' => $request->ville,
                'CodePostal' => $request->code_postal,
                'Telephone' => $request->telephone,
                'EtatCivil' => $request->etat_civil,
                'Profession' => $request->profession,
                'NationaliteActuelle' => $request->nationalite_actuelle,
                'Motif' => $request->motif,
            ]);
        }

        // Create history entry
        HistoriqueDemande::create([
            'DemandeID' => $demande->DemandeID,
            'AncienStatut' => null,
            'NouveauStatut' => 'Soumise',
            'Commentaire' => "Demande de {$type} soumise",
            'ModifiePar' => $user->UtilisateurID,
        ]);

        // Notification
        Notification::create([
            'UtilisateurID' => $user->UtilisateurID,
            'DemandeID' => $demande->DemandeID,
            'Contenu' => "Votre demande {$reference} a été soumise avec succès.",
            'TypeNotification' => 'demande',
        ]);

        ActivityLogger::log('Soumission_Demande', "Demande {$reference} soumise", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Demande soumise avec succès.',
            'demande' => $demande->load($type === 'CNI' ? 'detailsCni' : 'detailsNationalite'),
        ], 201);
    }

    /**
     * GET /api/citoyen/demandes
     * List current user's requests.
     */
    public function mesDemandes(Request $request): JsonResponse
    {
        $demandes = Demande::where('UtilisateurID', $request->user()->UtilisateurID)
            ->with(['detailsCni', 'detailsNationalite'])
            ->orderByDesc('DateSoumission')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $demandes,
        ]);
    }

    /**
     * GET /api/citoyen/demandes/{id}
     * Show request details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $request->user()->UtilisateurID)
            ->with([
                'detailsCni', 'detailsNationalite',
                'documents', 'paiements', 'historique.modifiePar',
                'carteIdentite', 'certificatNationalite', 'rendezVous',
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $demande,
        ]);
    }

    /**
     * GET /api/citoyen/demandes/{id}/suivi
     * Get request tracking timeline.
     */
    public function suivi(Request $request, int $id): JsonResponse
    {
        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $request->user()->UtilisateurID)
            ->firstOrFail();

        $historique = HistoriqueDemande::where('DemandeID', $id)
            ->with('modifiePar')
            ->orderBy('DateModification')
            ->get();

        return response()->json([
            'success' => true,
            'demande' => $demande,
            'historique' => $historique,
        ]);
    }

    /**
     * GET /api/citoyen/demandes/{id}/cni
     * Download generated CNI.
     */
    public function telechargerCni(Request $request, int $id)
    {
        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $request->user()->UtilisateurID)
            ->with('carteIdentite')
            ->firstOrFail();

        if (!$demande->carteIdentite || !$demande->carteIdentite->CheminFichier) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune CNI générée pour cette demande.',
            ], 404);
        }

        $path = storage_path('app/public/' . $demande->carteIdentite->CheminFichier);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier CNI introuvable.',
            ], 404);
        }

        return response()->download($path);
    }

    /**
     * GET /api/citoyen/demandes/{id}/certificat
     * Download generated nationality certificate.
     */
    public function telechargerCertificat(Request $request, int $id)
    {
        $demande = Demande::where('DemandeID', $id)
            ->where('UtilisateurID', $request->user()->UtilisateurID)
            ->with('certificatNationalite')
            ->firstOrFail();

        if (!$demande->certificatNationalite || !$demande->certificatNationalite->CheminPDF) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun certificat généré pour cette demande.',
            ], 404);
        }

        $path = storage_path('app/public/' . $demande->certificatNationalite->CheminPDF);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier certificat introuvable.',
            ], 404);
        }

        return response()->download($path);
    }
}
