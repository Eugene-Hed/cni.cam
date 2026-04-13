<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Services\OtpService;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * POST /api/auth/login
     * Send OTP to user email or phone.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifiant = $request->identifiant;
        $methode = $request->methode;

        // Find user by email or phone
        $user = $methode === 'email'
            ? Utilisateur::where('Email', $identifiant)->first()
            : Utilisateur::where('NumeroTelephone', $identifiant)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé avec cet identifiant.',
            ], 404);
        }

        if (!$user->IsActive) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été désactivé.',
            ], 403);
        }

        // Generate and send OTP
        $code = $this->otpService->generate($user);

        if ($methode === 'email') {
            $this->otpService->sendByEmail($user, $code);
        } else {
            $this->otpService->sendBySms($user, $code);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code OTP envoyé avec succès.',
            'methode' => $methode,
            'masque' => $this->masquerIdentifiant($identifiant, $methode),
            // DEV ONLY — remove in production
            'dev_otp' => config('app.debug') ? $code : null,
        ]);
    }

    /**
     * POST /api/auth/verify-otp
     * Verify OTP and return auth token.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::debug("Tentative de vérification OTP reçue", $request->all());

        $user = $request->methode === 'email'
            ? Utilisateur::where('Email', $request->identifiant)->first()
            : Utilisateur::where('NumeroTelephone', $request->identifiant)->first();

        if (!$user) {
            \Illuminate\Support\Facades\Log::warning("Vérification OTP : Utilisateur non trouvé pour {$request->identifiant}");
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        if (!$this->otpService->verify($user, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP invalide ou expiré.',
            ], 401);
        }

        // Create Sanctum token
        $token = $user->createToken('cni-cam-token', ['*'])->plainTextToken;

        // Load role relationship
        $user->load('role');

        ActivityLogger::log('Connexion', "Connexion réussie via {$request->methode}", $user->UtilisateurID);

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => [
                'id' => $user->UtilisateurID,
                'code' => $user->Codeutilisateur,
                'prenom' => $user->Prenom,
                'nom' => $user->Nom,
                'email' => $user->Email,
                'telephone' => $user->NumeroTelephone,
                'photo' => $user->PhotoUtilisateur,
                'role_id' => $user->RoleId,
                'role' => $user->role->role ?? null,
                'initiales' => $user->initiales,
            ],
        ]);
    }

    /**
     * POST /api/auth/register
     * Register a new citizen (role 2).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Vérification combinaison nom/prénom similaire (logique héritée)
        if (!$request->boolean('force_creation')) {
            $similarAccount = Utilisateur::whereRaw('LOWER(Nom) = ? AND LOWER(Prenom) = ?', [
                strtolower($request->nom),
                strtolower($request->prenom)
            ])->first();

            if ($similarAccount) {
                return response()->json([
                    'success' => false,
                    'is_similar' => true,
                    'similar_email' => $this->masquerIdentifiant($similarAccount->Email, 'email'),
                    'message' => 'Un compte avec ce nom et prénom existe déjà. S\'agit-il de vous ?',
                ], 409);
            }
        }

        // Generate unique user code
        $codeUtilisateur = 'CIT-' . strtoupper(Str::random(8));
        while (Utilisateur::where('Codeutilisateur', $codeUtilisateur)->exists()) {
            $codeUtilisateur = 'CIT-' . strtoupper(Str::random(8));
        }

        $user = Utilisateur::create([
            'Codeutilisateur' => $codeUtilisateur,
            'Email' => $request->email,
            'NumeroTelephone' => $request->telephone,
            'Prenom' => $request->prenom,
            'Nom' => $request->nom,
            'DateNaissance' => $request->date_naissance,
            'Genre' => $request->genre,
            'Adresse' => $request->adresse,
            'Profession' => $request->profession,
            'RoleId' => 2, // Citoyen
            'IsActive' => 1,
            'RegionNaissanceID' => $request->region_naissance_id,
            'DepartementNaissanceID' => $request->departement_naissance_id,
            'VilleNaissanceID' => $request->ville_naissance_id,
            'RegionResidenceID' => $request->region_residence_id,
            'DepartementResidenceID' => $request->departement_residence_id,
            'VilleResidenceID' => $request->ville_residence_id,
            'EthnieID' => $request->ethnie_id,
        ]);

        ActivityLogger::log('Inscription', "Nouveau citoyen inscrit: {$user->Prenom} {$user->Nom}", $user->UtilisateurID);

        // Auto-send OTP for immediate login
        $code = $this->otpService->generate($user);
        $this->otpService->sendByEmail($user, $code);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie ! Un code OTP a été envoyé à votre adresse email.',
            'user_code' => $codeUtilisateur,
            'dev_otp' => config('app.debug') ? $code : null,
        ], 201);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * GET /api/user/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load([
            'role',
            'regionNaissance', 'departementNaissance', 'villeNaissance',
            'regionResidence', 'departementResidence', 'villeResidence',
            'ethnie',
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * PUT /api/user/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'prenom' => 'sometimes|string|max:50',
            'nom' => 'sometimes|string|max:50',
            'telephone' => 'sometimes|string|max:20',
            'adresse' => 'sometimes|nullable|string',
            'profession' => 'sometimes|nullable|string|max:100',
        ]);

        $user->update(array_filter([
            'Prenom' => $request->prenom,
            'Nom' => $request->nom,
            'NumeroTelephone' => $request->telephone,
            'Adresse' => $request->adresse,
            'Profession' => $request->profession,
        ], fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Mask email/phone for privacy in response.
     */
    private function masquerIdentifiant(string $identifiant, string $methode): string
    {
        if ($methode === 'email') {
            $parts = explode('@', $identifiant);
            return substr($parts[0], 0, 2) . '***@' . $parts[1];
        }
        return substr($identifiant, 0, 4) . '****' . substr($identifiant, -2);
    }
}
