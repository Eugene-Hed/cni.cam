<?php

namespace App\Services;

use App\Models\Utilisateur;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate a random OTP code and store it on the user.
     */
    public function generate(Utilisateur $user): string
    {
        $length = (int) config('otp.length', 6);
        $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        $user->update([
            'CodeOTP' => $code,
            'ExpirationOTP' => now()->addMinutes((int) config('otp.expiry', 5)),
        ]);

        Log::info("Code OTP généré pour {$user->Email}: {$code} (Expire à: {$user->ExpirationOTP})");

        return $code;
    }

    /**
     * Verify the OTP code for a user.
     */
    public function verify(Utilisateur $user, string $code): bool
    {
        if (!$user->CodeOTP || !$user->ExpirationOTP) {
            Log::warning("Échec vérification OTP pour {$user->Email} : Aucun code en base.");
            return false;
        }

        if (now()->greaterThan($user->ExpirationOTP)) {
            Log::warning("Échec vérification OTP pour {$user->Email} : Code expiré (Expire à {$user->ExpirationOTP}, actuel: " . now() . ")");
            $this->invalidate($user);
            return false;
        }

        $inputCode = (string) trim($code);
        $storedCode = (string) $user->CodeOTP;

        if ($storedCode !== $inputCode) {
            Log::warning("Échec vérification OTP pour {$user->Email} : Code incorrect. Attendu: '{$storedCode}', Reçu: '{$inputCode}'");
            return false;
        }

        $this->invalidate($user);
        return true;
    }

    /**
     * Send OTP via email.
     */
    public function sendByEmail(Utilisateur $user, string $code): void
    {
        try {
            Mail::raw(
                "Votre code de vérification CNI.CAM est : {$code}\n\nCe code expire dans 5 minutes.\nNe partagez ce code avec personne.",
                function ($message) use ($user) {
                    $message->to($user->Email)
                        ->subject('Code de vérification CNI.CAM');
                }
            );
            Log::info("OTP envoyé par email à {$user->Email}");
        } catch (\Exception $e) {
            Log::error("Échec envoi OTP email: " . $e->getMessage());
            // In development, log the code so testing is possible
            Log::info("[DEV] Code OTP pour {$user->Email}: {$code}");
        }
    }

    /**
     * Send OTP via SMS (Orange API — simulated in dev).
     */
    public function sendBySms(Utilisateur $user, string $code): void
    {
        // TODO: Integrate Orange SMS API in production
        Log::info("[SMS] Code OTP pour {$user->NumeroTelephone}: {$code}");
    }

    /**
     * Clear the OTP after successful verification.
     */
    private function invalidate(Utilisateur $user): void
    {
        $user->update([
            'CodeOTP' => null,
            'ExpirationOTP' => null,
        ]);
    }
}
