<?php

namespace App\Services;

use App\Models\JournalActivite;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an activity to the journal.
     */
    public static function log(string $type, string $description, ?int $userId = null): void
    {
        JournalActivite::create([
            'UtilisateurID' => $userId ?? Auth::id(),
            'TypeActivite' => $type,
            'Description' => $description,
            'AdresseIP' => request()->ip(),
        ]);
    }
}
