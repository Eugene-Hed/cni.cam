<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Demande;
use App\Models\JournalActivite;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * GET /api/admin/utilisateurs
     */
    public function listeUtilisateurs(Request $request): JsonResponse
    {
        $query = Utilisateur::with('role');

        if ($request->has('role_id')) {
            $query->where('RoleId', $request->role_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Nom', 'like', "%{$search}%")
                    ->orWhere('Prenom', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhere('Codeutilisateur', 'like', "%{$search}%");
            });
        }

        $utilisateurs = $query->orderByDesc('DateCreation')->paginate(15);

        return response()->json(['success' => true, 'data' => $utilisateurs]);
    }

    /**
     * GET /api/admin/utilisateurs/{id}
     */
    public function detailUtilisateur(int $id): JsonResponse
    {
        $utilisateur = Utilisateur::with([
            'role', 'regionNaissance', 'departementNaissance', 'villeNaissance',
            'regionResidence', 'departementResidence', 'villeResidence', 'ethnie',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $utilisateur]);
    }

    /**
     * PUT /api/admin/utilisateurs/{id}
     */
    public function updateUtilisateur(Request $request, int $id): JsonResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $request->validate([
            'role_id' => 'sometimes|integer|in:1,2,3,4',
            'is_active' => 'sometimes|boolean',
            'prenom' => 'sometimes|string|max:50',
            'nom' => 'sometimes|string|max:50',
        ]);

        $updates = array_filter([
            'RoleId' => $request->role_id,
            'IsActive' => $request->is_active,
            'Prenom' => $request->prenom,
            'Nom' => $request->nom,
        ], fn($v) => $v !== null);

        $utilisateur->update($updates);

        ActivityLogger::log(
            'Modification_Utilisateur',
            "Modification de l'utilisateur ID: {$id}",
            $request->user()->UtilisateurID
        );

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour.',
            'data' => $utilisateur->fresh('role'),
        ]);
    }

    /**
     * DELETE /api/admin/utilisateurs/{id}
     */
    public function deleteUtilisateur(Request $request, int $id): JsonResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->UtilisateurID === $request->user()->UtilisateurID) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 400);
        }

        // Soft-disable instead of hard delete
        $utilisateur->update(['IsActive' => false]);

        ActivityLogger::log('Desactivation_Utilisateur', "Utilisateur ID: {$id} désactivé", $request->user()->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur désactivé.',
        ]);
    }

    /**
     * GET /api/admin/demandes
     */
    public function listeDemandes(Request $request): JsonResponse
    {
        $query = Demande::with(['utilisateur', 'detailsCni', 'detailsNationalite']);

        if ($request->has('type')) {
            $query->where('TypeDemande', $request->type);
        }
        if ($request->has('statut')) {
            $query->where('Statut', $request->statut);
        }

        $demandes = $query->orderByDesc('DateSoumission')->paginate(15);

        return response()->json(['success' => true, 'data' => $demandes]);
    }

    /**
     * GET /api/admin/journal
     */
    public function journalActivites(Request $request): JsonResponse
    {
        $query = JournalActivite::with('utilisateur');

        if ($request->has('type')) {
            $query->where('TypeActivite', $request->type);
        }

        $journal = $query->orderByDesc('DateHeure')->paginate(20);

        return response()->json(['success' => true, 'data' => $journal]);
    }

    /**
     * GET /api/admin/demandes/{id}
     */
    public function detailDemande(int $id): JsonResponse
    {
        $demande = Demande::with([
            'utilisateur', 'detailsCni', 'detailsNationalite', 'documents',
            'paiements', 'historique.modifiePar', 'certificatNationalite', 'cni'
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $demande]);
    }
}
