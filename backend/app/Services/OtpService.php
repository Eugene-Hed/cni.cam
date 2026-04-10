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
        $length = (int) config('app.otp_length', 6);
        $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        $user->update([
            'CodeOTP' => $code,
            'ExpirationOTP' => now()->addMinutes((int) config('app.otp_expiry', 5)),
        ]);

        return $code;
    }

    /**
     * Verify the OTP code for a user.
     */
    public function verify(Utilisateur $user, string $code): bool
    {
        if (!$user->CodeOTP || !$user->ExpirationOTP) {
            return false;
        }

        if (now()->greaterThan($user->ExpirationOTP)) {
            $this->invalidate($user);
            return false;
        }

        if ($user->CodeOTP !== $code) {
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
