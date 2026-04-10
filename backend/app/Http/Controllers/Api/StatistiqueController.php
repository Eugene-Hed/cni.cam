<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Utilisateur;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    /**
     * GET /api/citoyen/dashboard
     */
    public function citoyenDashboard(Request $request): JsonResponse
    {
        $userId = $request->user()->UtilisateurID;

        $stats = [
            'total_demandes' => Demande::where('UtilisateurID', $userId)->count(),
            'en_cours' => Demande::where('UtilisateurID', $userId)->where('Statut', 'EnCours')->count(),
            'approuvees' => Demande::where('UtilisateurID', $userId)->where('Statut', 'Approuvee')->count(),
            'terminees' => Demande::where('UtilisateurID', $userId)->where('Statut', 'Terminee')->count(),
            'rejetees' => Demande::where('UtilisateurID', $userId)->where('Statut', 'Rejetee')->count(),
            'notifications_non_lues' => Notification::where('UtilisateurID', $userId)->where('EstLue', false)->count(),
        ];

        $dernieresDemandes = Demande::where('UtilisateurID', $userId)
            ->with(['detailsCni', 'detailsNationalite'])
            ->orderByDesc('DateSoumission')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'dernieres_demandes' => $dernieresDemandes,
        ]);
    }

    /**
     * GET /api/officier/dashboard
     */
    public function officierDashboard(): JsonResponse
    {
        $stats = [
            'total' => Demande::where('TypeDemande', 'CNI')->count(),
            'nouvelles' => Demande::where('TypeDemande', 'CNI')->where('Statut', 'Soumise')->count(),
            'en_cours' => Demande::where('TypeDemande', 'CNI')->where('Statut', 'EnCours')->count(),
            'approuvees' => Demande::where('TypeDemande', 'CNI')->where('Statut', 'Approuvee')->count(),
            'terminees' => Demande::where('TypeDemande', 'CNI')->where('Statut', 'Terminee')->count(),
            'rejetees' => Demande::where('TypeDemande', 'CNI')->where('Statut', 'Rejetee')->count(),
        ];

        // Monthly evolution (last 6 months)
        $evolution = Demande::where('TypeDemande', 'CNI')
            ->where('DateSoumission', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(DateSoumission, '%Y-%m') as mois"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // By sous-type
        $parType = Demande::where('TypeDemande', 'CNI')
            ->select('SousTypeDemande', DB::raw('COUNT(*) as total'))
            ->groupBy('SousTypeDemande')
            ->get();

        $recentDemandes = Demande::where('TypeDemande', 'CNI')
            ->with('utilisateur')
            ->orderByDesc('DateSoumission')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'evolution' => $evolution,
            'par_type' => $parType,
            'recentes' => $recentDemandes,
        ]);
    }

    /**
     * GET /api/president/dashboard
     */
    public function presidentDashboard(): JsonResponse
    {
        $stats = [
            'total' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->count(),
            'soumises' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->where('Statut', 'Soumise')->count(),
            'en_cours' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->where('Statut', 'EnCours')->count(),
            'approuvees' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->where('Statut', 'Approuvee')->count(),
            'terminees' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->where('Statut', 'Terminee')->count(),
        ];

        $recentDemandes = Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])
            ->with(['utilisateur', 'detailsNationalite'])
            ->orderByDesc('DateSoumission')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recentes' => $recentDemandes,
        ]);
    }

    /**
     * GET /api/admin/dashboard
     */
    public function adminDashboard(): JsonResponse
    {
        $stats = [
            'total_utilisateurs' => Utilisateur::count(),
            'citoyens' => Utilisateur::where('RoleId', 2)->count(),
            'officiers' => Utilisateur::where('RoleId', 3)->count(),
            'total_demandes' => Demande::count(),
            'demandes_cni' => Demande::where('TypeDemande', 'CNI')->count(),
            'demandes_nationalite' => Demande::whereIn('TypeDemande', ['NATIONALITE', 'CertificatNationalite'])->count(),
            'en_attente' => Demande::where('Statut', 'Soumise')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * GET /api/admin/statistiques
     */
    public function statsGlobales(): JsonResponse
    {
        $parStatut = Demande::select('Statut', DB::raw('COUNT(*) as total'))
            ->groupBy('Statut')
            ->get();

        $parType = Demande::select('TypeDemande', DB::raw('COUNT(*) as total'))
            ->groupBy('TypeDemande')
            ->get();

        $parMois = Demande::where('DateSoumission', '>=', now()->subMonths(12))
            ->select(
                DB::raw("DATE_FORMAT(DateSoumission, '%Y-%m') as mois"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        return response()->json([
            'success' => true,
            'par_statut' => $parStatut,
            'par_type' => $parType,
            'par_mois' => $parMois,
        ]);
    }
}
