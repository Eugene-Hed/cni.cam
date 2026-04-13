<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private $apiKey;
    private $apiUrl = 'http://ollama:11434/api/generate';

    public function __construct()
    {
        // API key no longer required for local Ollama
    }

    /**
     * Send message to Local Ollama (Phi-3).
     */
    public function getResponse(string $message, ?string $userContext = null): string
    {
        try {
            // Load base knowledge from text file
            $kbPath = base_path('ai_docs/knowledge_base.txt');
            $knowledgeBase = file_exists($kbPath) ? file_get_contents($kbPath) : "";

            $systemPrompt = "Tu es l'assistant virtuel expert de CNI.CAM au Cameroun.
            Tes instructions :
            1. RÉPONDS UNIQUEMENT EN TE BASANT SUR LA RÉGLEMENTATION CI-DESSOUS.
            2. Sois précis et cite les articles si nécessaire.
            3. Si une information n'est pas dans la base, dis poliment que tu ne sais pas.

            RÉGLEMENTATION OFFICIELLE :
            {$knowledgeBase}";

            if ($userContext) {
                $systemPrompt .= "\n\nCONTEXTE UTILISATEUR :\n" . $userContext;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'phi3',
                'prompt' => $systemPrompt . "\n\nUtilisateur: " . $message . "\nAssistant:",
                'stream' => false,
                'options' => [
                    'temperature' => 0.3, // Lower temp for more factual responses
                    'num_predict' => 500,
                ]
            ]);

            if ($response->successful()) {
                return $response->json()['response'] ?? "Désolé, je ne peux pas répondre pour le moment.";
            }

            return $this->getRuleBasedFallback($message, $userContext);

        } catch (\Exception $e) {
            Log::error('Local AI Error: ' . $e->getMessage());
            return $this->getRuleBasedFallback($message, $userContext);
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
