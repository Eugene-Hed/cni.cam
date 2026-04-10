<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * POST /api/chatbot
     * Send a message to the AI chatbot.
     */
    public function send(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $response = $chatbotService->getResponse($request->message);

        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
    }

    /**
     * Basic rule-based response matching for common CNI questions.
     * Placeholder until AI integration.
     */
    private function getResponse(string $message): string
    {
        $message = mb_strtolower($message);

        $responses = [
            'bonjour' => "Bonjour ! Je suis l'assistant virtuel CNI.CAM. Comment puis-je vous aider ?",
            'aide' => "Je peux vous aider avec :\n- Les démarches pour obtenir une CNI\n- Le suivi de votre demande\n- Les documents requis\n- Les frais de traitement\n- Les délais de traitement",
            'cni' => "Pour obtenir une CNI, vous devez :\n1. Créer un compte sur notre plateforme\n2. Remplir le formulaire de demande\n3. Télécharger les documents requis\n4. Effectuer le paiement (10 000 FCFA)\n5. Signer numériquement\n6. Attendre la validation par un officier",
            'document' => "Les documents requis pour une CNI sont :\n- Photo d'identité\n- Acte de naissance\n- Certificat de nationalité\n- Justificatif de profession",
            'prix' => "Les frais sont :\n- CNI : 10 000 FCFA\n- Certificat de nationalité : 5 000 FCFA",
            'delai' => "Le délai de traitement est généralement de 2 à 4 semaines après la soumission complète de votre dossier.",
            'suivi' => "Pour suivre votre demande, connectez-vous à votre espace citoyen et consultez la rubrique 'Mes demandes'.",
            'nationalite' => "Pour un certificat de nationalité, vous devez :\n1. Soumettre une demande dans la rubrique 'Certificat de Nationalité'\n2. Fournir les documents nécessaires\n3. Payer les frais (5 000 FCFA)\n4. Attendre la validation présidentielle",
        ];

        foreach ($responses as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return "Je suis l'assistant CNI.CAM. Je peux vous renseigner sur les démarches d'obtention de CNI et de certificat de nationalité. Posez-moi une question !";
    }
}
