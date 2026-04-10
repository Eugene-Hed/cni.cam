<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Departement;
use App\Models\Ville;
use App\Models\Ethnie;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
{
    /**
     * GET /api/geo/regions
     */
    public function regions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Region::orderBy('NomRegion')->get(),
        ]);
    }

    /**
     * GET /api/geo/regions/{regionId}/departements
     */
    public function departements(int $regionId): JsonResponse
    {
        $departements = Departement::where('RegionID', $regionId)
            ->orderBy('NomDepartement')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departements,
        ]);
    }

    /**
     * GET /api/geo/departements/{departementId}/villes
     */
    public function villes(int $departementId): JsonResponse
    {
        $villes = Ville::where('DepartementID', $departementId)
            ->orderBy('NomVille')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $villes,
        ]);
    }

    /**
     * GET /api/geo/ethnies
     */
    public function ethnies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Ethnie::with('region')->orderBy('NomEthnie')->get(),
        ]);
    }
}
