<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GeoController;
use App\Http\Controllers\Api\DemandeController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\SignatureController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReclamationController;
use App\Http\Controllers\Api\StatistiqueController;
use App\Http\Controllers\Api\OfficierController;
use App\Http\Controllers\Api\PresidentController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ChatbotController;

/*
|--------------------------------------------------------------------------
| API Routes — CNI.CAM
|--------------------------------------------------------------------------
*/

// =====================================================================
// PUBLIC ROUTES (no authentication required)
// =====================================================================

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Geographic data (public, used by registration form)
Route::prefix('geo')->group(function () {
    Route::get('/regions', [GeoController::class, 'regions']);
    Route::get('/regions/{regionId}/departements', [GeoController::class, 'departements']);
    Route::get('/departements/{departementId}/villes', [GeoController::class, 'villes']);
    Route::get('/ethnies', [GeoController::class, 'ethnies']);
});

// Chatbot (public)
Route::post('/chatbot', [ChatbotController::class, 'send']);

// =====================================================================
// AUTHENTICATED ROUTES
// =====================================================================

Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/lire', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/lire-tout', [NotificationController::class, 'markAllAsRead']);

    // =====================================================================
    // CITOYEN ROUTES (role 2)
    // =====================================================================

    Route::middleware('role:2')->prefix('citoyen')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StatistiqueController::class, 'citoyenDashboard']);

        // Demandes
        Route::post('/demandes', [DemandeController::class, 'store']);
        Route::get('/demandes', [DemandeController::class, 'mesDemandes']);
        Route::get('/demandes/{id}', [DemandeController::class, 'show']);

        // Documents
        Route::post('/demandes/{id}/documents', [DocumentController::class, 'upload']);
        Route::get('/mes-documents', [DocumentController::class, 'mesDocuments']);

        // Paiement
        Route::post('/demandes/{id}/paiement', [PaiementController::class, 'payer']);

        // Signature citoyen
        Route::post('/demandes/{id}/signature', [SignatureController::class, 'signerCitoyen']);

        // Suivi
        Route::get('/demandes/{id}/suivi', [DemandeController::class, 'suivi']);

        // Téléchargement CNI / Certificat
        Route::get('/demandes/{id}/cni', [DemandeController::class, 'telechargerCni']);
        Route::get('/demandes/{id}/certificat', [DemandeController::class, 'telechargerCertificat']);

        // Réclamations
        Route::post('/reclamations', [ReclamationController::class, 'store']);
        Route::get('/reclamations', [ReclamationController::class, 'mesClamations']);
    });

    // =====================================================================
    // OFFICIER ROUTES (role 3)
    // =====================================================================

    Route::middleware('role:3')->prefix('officier')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StatistiqueController::class, 'officierDashboard']);

        // Demandes CNI
        Route::get('/demandes', [OfficierController::class, 'listeDemandes']);
        Route::get('/demandes/{id}', [OfficierController::class, 'detailDemande']);
        Route::put('/demandes/{id}/statut', [OfficierController::class, 'changerStatut']);

        // Validation documents
        Route::put('/documents/{id}/valider', [OfficierController::class, 'validerDocument']);

        // Signature officier
        Route::post('/demandes/{id}/signature', [SignatureController::class, 'signerOfficier']);

        // Génération CNI
        Route::post('/demandes/{id}/generer-cni', [OfficierController::class, 'genererCni']);
        Route::get('/demandes/{id}/visualiser-cni', [OfficierController::class, 'visualiserCni']);

        // Réclamations
        Route::get('/reclamations', [ReclamationController::class, 'index']);
        Route::put('/reclamations/{id}', [ReclamationController::class, 'update']);
    });

    // =====================================================================
    // PRESIDENT ROUTES (role 4)
    // =====================================================================

    Route::middleware('role:4')->prefix('president')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StatistiqueController::class, 'presidentDashboard']);

        // Demandes Nationalité
        Route::get('/demandes', [PresidentController::class, 'listeDemandes']);
        Route::get('/demandes/{id}', [PresidentController::class, 'detailDemande']);
        Route::put('/demandes/{id}/statut', [PresidentController::class, 'changerStatut']);

        // Signature président
        Route::post('/demandes/{id}/signature', [SignatureController::class, 'signerPresident']);

        // Génération certificat
        Route::post('/demandes/{id}/generer-certificat', [PresidentController::class, 'genererCertificat']);
    });

    // =====================================================================
    // ADMIN ROUTES (role 1)
    // =====================================================================

    Route::middleware('role:1')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StatistiqueController::class, 'adminDashboard']);

        // Utilisateurs
        Route::get('/utilisateurs', [AdminController::class, 'listeUtilisateurs']);
        Route::get('/utilisateurs/{id}', [AdminController::class, 'detailUtilisateur']);
        Route::put('/utilisateurs/{id}', [AdminController::class, 'updateUtilisateur']);
        Route::delete('/utilisateurs/{id}', [AdminController::class, 'deleteUtilisateur']);

        // Toutes les demandes
        Route::get('/demandes', [AdminController::class, 'listeDemandes']);

        // Journal d'activités
        Route::get('/journal', [AdminController::class, 'journalActivites']);

        // Statistiques globales
        Route::get('/statistiques', [StatistiqueController::class, 'statsGlobales']);
    });
});
