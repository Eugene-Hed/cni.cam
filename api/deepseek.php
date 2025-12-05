<?php
header('Content-Type: application/json');

// Configuration Dialogflow
$projectId = "VOTRE_PROJECT_ID";
$languageCode = "fr";

// Récupérer les données de la requête
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

// Charger les credentials Google
putenv('GOOGLE_APPLICATION_CREDENTIALS=path/to/your/credentials.json');

// Utiliser la bibliothèque Google Cloud
require_once 'vendor/autoload.php';

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;

try {
    // Créer une session Dialogflow
    $sessionsClient = new SessionsClient();
    $session = $sessionsClient->sessionName($projectId, uniqid());

    // Créer la requête
    $textInput = new TextInput();
    $textInput->setText($message);
    $textInput->setLanguageCode($languageCode);

    $queryInput = new QueryInput();
    $queryInput->setText($textInput);

    // Envoyer la requête
    $response = $sessionsClient->detectIntent($session, $queryInput);
    $queryResult = $response->getQueryResult();
    
    // Récupérer la réponse
    $fulfillmentText = $queryResult->getFulfillmentText();
    
    echo json_encode(['response' => $fulfillmentText]);

    $sessionsClient->close();
    
} catch (Exception $e) {
    echo json_encode(['response' => 'Une erreur est survenue: ' . $e->getMessage()]);
}
