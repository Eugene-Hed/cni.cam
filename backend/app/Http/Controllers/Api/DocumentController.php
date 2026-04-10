<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * POST /api/citoyen/demandes/{id}/documents
     * Upload documents for a request.
     */
    public function upload(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*.type' => 'required|in:Photo,PhotoIdentite,ActeNaissance,CertificatNationalite,AncienneCNI,ActeMariage,JustificatifProfession,DecretNaturalisation,CasierJudiciaire,DeclarationPerte,Signature',
            'documents.*.fichier' => 'required|file|max:10240', // 10MB max
        ]);

        $user = $request->user();
        $uploaded = [];

        foreach ($request->documents as $doc) {
            $file = $doc['fichier'];
            $type = $doc['type'];

            // Store file
            $path = $file->store("documents/{$id}", 'public');

            $document = Document::create([
                'DemandeID' => $id,
                'TypeDocument' => $type,
                'CheminFichier' => $path,
                'StatutValidation' => 'EnAttente',
                'Utilisateurid' => $user->UtilisateurID,
            ]);

            $uploaded[] = $document;
        }

        ActivityLogger::log('Upload_Documents', count($uploaded) . " document(s) uploadé(s) pour la demande #{$id}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' document(s) uploadé(s) avec succès.',
            'documents' => $uploaded,
        ], 201);
    }

    /**
     * GET /api/citoyen/mes-documents
     * List all documents for current user.
     */
    public function mesDocuments(Request $request): JsonResponse
    {
        $documents = Document::where('Utilisateurid', $request->user()->UtilisateurID)
            ->with('demande')
            ->orderByDesc('DateTelechargement')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }
}
