<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'demande_id' => 'nullable|exists:demandes,DemandeID',
            'type' => 'required|string|max:50',
            'description' => 'required|string',
        ]);

        $reclamation = Reclamation::create([
            'UtilisateurID' => $request->user()->UtilisateurID,
            'DemandeID' => $request->demande_id,
            'TypeReclamation' => $request->type,
            'Description' => $request->description,
            'Statut' => 'Ouverte',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réclamation soumise avec succès.',
            'data' => $reclamation,
        ], 201);
    }

    public function mesClamations(Request $request): JsonResponse
    {
        $reclamations = Reclamation::where('UtilisateurID', $request->user()->UtilisateurID)
            ->with('demande')
            ->orderByDesc('DateCreation')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $reclamations]);
    }

    public function index(Request $request): JsonResponse
    {
        $reclamations = Reclamation::with(['utilisateur', 'demande'])
            ->orderByDesc('DateCreation')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $reclamations]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'statut' => 'required|in:Ouverte,EnCours,Fermee',
        ]);

        $reclamation = Reclamation::findOrFail($id);
        $reclamation->update(['Statut' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Réclamation mise à jour.',
            'data' => $reclamation,
        ]);
    }
}
