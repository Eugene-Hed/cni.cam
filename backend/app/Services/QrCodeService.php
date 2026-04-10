<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a JSON-encoded QR code for a CNI.
     */
    public function generateForCni(array $data, string $filename): string
    {
        $content = json_encode($data);
        
        $path = "qrcodes/{$filename}.png";
        
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($content);
            
        Storage::disk('public')->put($path, $qrCode);
        
        return $path;
    }
}
