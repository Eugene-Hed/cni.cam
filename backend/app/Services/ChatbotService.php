<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Send message to Google Gemini.
     */
    public function getResponse(string $message): string
    {
        if (!$this->apiKey) {
            return $this->getRuleBasedFallback($message);
        }

        try {
            $context = "Tu es l'assistant virtuel de CNI.CAM, la plateforme numérique de gestion des Cartes Nationales d'Identité et des Certificats de Nationalité au Cameroun.
            Tes objectifs :
            1. Aider les citoyens dans leurs démarches (Inscription, Demande CNI, Demande Nationalité).
            2. Expliquer les frais (CNI: 10 000 FCFA, Nationalité: 5 000 FCFA).
            3. Guider sur les documents requis (Photo, Acte de naissance, etc.).
            4. Répondre poliment et professionnellement en Français.
            Si la question ne concerne pas CNI.CAM ou les documents d'identité au Cameroun, redirige poliment l'utilisateur vers les services concernés.";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $context . "\n\nUtilisateur: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Désolé, je ne peux pas répondre pour le moment.";
            }

            Log::error('Gemini API Error: ' . $response->body());
            return $this->getRuleBasedFallback($message);

        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            return $this->getRuleBasedFallback($message);
        }
    }

    /**
     * Rule-based fallback if API fails or key is missing.
     */
    private function getRuleBasedFallback(string $message): string
    {
        $message = mb_strtolower($message);
        $responses = [
            'bonjour' => "Bonjour ! Je suis l'assistant virtuel CNI.CAM. Comment puis-je vous aider ?",
            'cni' => "Pour une CNI, le coût est de 10 000 FCFA. Vous devez fournir une photo, un acte de naissance et un certificat de nationalité.",
            'nationalité' => "Le certificat de nationalité coûte 5 000 FCFA. Il est signé par le Président du Tribunal.",
            'prix' => "Les frais : 10 000 FCFA pour la CNI et 5 000 FCFA pour la nationalité.",
            'délai' => "Le délai moyen est de 2 à 4 semaines.",
        ];

        foreach ($responses as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return "Je suis l'assistant CNI.CAM. Je peux vous aider pour vos démarches de CNI et de certificat de nationalité au Cameroun. Posez-moi une question sur les documents ou les frais !";
    }
}
