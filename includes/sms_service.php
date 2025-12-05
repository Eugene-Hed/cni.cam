<?php
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/config.php';

/**
 * Service d'envoi de SMS via Orange Cameroun API
 */
class SMSService {
    private $clientId;
    private $clientSecret;
    private $sender;
    private $accessToken;
    private $tokenExpiry;
    private $baseUrl;

    /**
     * Constructeur du service SMS
     */
    public function __construct() {
        $this->clientId = 'tleiYJ4XbPrCasn0bsNveReOsaWKpQWX'; // À remplacer par votre ID client Orange
        $this->clientSecret = 'lW4EUCOJ97lwOOcV61i8sM6LGT06uK2SuKjZnuaV6Jq9'; // À remplacer par votre secret client Orange
        $this->sender = 'CNI.CAM';
        $this->baseUrl = 'https://api.orange.com';
        $this->accessToken = null;
        $this->tokenExpiry = 0;
    }

    /**
     * Obtient un token d'accès à l'API Orange
     * 
     * @return string Token d'accès
     */
    private function getAccessToken() {
        // Vérifier si le token est encore valide
        if ($this->accessToken && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        // Préparer la requête pour obtenir un token
        $url = $this->baseUrl . '/oauth/v3/token';
        $auth = base64_encode($this->clientId . ':' . $this->clientSecret);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $auth,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error #:" . $err);
            return false;
        }

        $result = json_decode($response, true);
        if (isset($result['access_token'])) {
            $this->accessToken = $result['access_token'];
            $this->tokenExpiry = time() + $result['expires_in'] - 60; // Expiration moins 1 minute pour la sécurité
            return $this->accessToken;
        }

        error_log("Erreur d'authentification Orange API: " . $response);
        return false;
    }

    /**
     * Envoie un SMS via l'API Orange
     * 
     * @param string $to Numéro de téléphone du destinataire
     * @param string $message Contenu du message
     * @return bool Succès ou échec de l'envoi
     */
    public function sendSMS($to, $message) {
        // Extraire le code OTP du message pour la simulation
        preg_match('/(\d{6})/', $message, $matches);
        $otp = $matches[1] ?? '';

        // Formater le numéro de téléphone
        $to = $this->formatPhoneNumber($to);
        
        // Obtenir un token d'accès
        $token = $this->getAccessToken();
        if (!$token) {
            error_log("Impossible d'obtenir un token d'accès Orange API");
            
            // Pour la simulation, on stocke quand même le code OTP
            if (!empty($otp)) {
                $_SESSION['simulated_otp'] = $otp;
                $_SESSION['otp_simulation_time'] = time();
                error_log("SMS simulé envoyé à $to avec le code OTP: $otp");
            }
            
            return false;
        }

        // Préparer la requête d'envoi de SMS
        $url = $this->baseUrl . '/smsmessaging/v1/outbound/tel:' . urlencode($this->sender) . '/requests';
        
        $data = [
            'outboundSMSMessageRequest' => [
                'address' => 'tel:' . $to,
                'senderAddress' => 'tel:' . $this->sender,
                'outboundSMSTextMessage' => [
                    'message' => $message
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error #:" . $err);
            
            // Pour la simulation, on stocke quand même le code OTP
            if (!empty($otp)) {
                $_SESSION['simulated_otp'] = $otp;
                $_SESSION['otp_simulation_time'] = time();
                error_log("SMS simulé envoyé à $to avec le code OTP: $otp (après erreur API)");
            }
            
            return false;
        }

        $success = ($httpCode >= 200 && $httpCode < 300);
        
        if ($success) {
            error_log("SMS envoyé avec succès à $to via Orange API");
            
            // Pour la simulation et le développement, on stocke quand même le code OTP
            if (!empty($otp)) {
                $_SESSION['simulated_otp'] = $otp;
                $_SESSION['otp_simulation_time'] = time();
            }
            
            return true;
        } else {
            error_log("Erreur d'envoi SMS via Orange API: " . $response);
            
            // Pour la simulation, on stocke quand même le code OTP
            if (!empty($otp)) {
                $_SESSION['simulated_otp'] = $otp;
                $_SESSION['otp_simulation_time'] = time();
                error_log("SMS simulé envoyé à $to avec le code OTP: $otp (après échec API)");
            }
            
            return false;
        }
    }

    /**
     * Formate un numéro de téléphone au format international
     * 
     * @param string $phone Numéro de téléphone à formater
     * @return string Numéro formaté
     */
    private function formatPhoneNumber($phone) {
        // Supprimer tous les caractères non numériques sauf le +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si le numéro ne commence pas déjà par +237 (Cameroun)
        if (strpos($phone, '+237') !== 0) {
            // Si le numéro commence par 6 et a 9 chiffres (format camerounais)
            if (strlen($phone) === 9 && substr($phone, 0, 1) === '6') {
                $phone = '+237' . $phone;
            }
        }

        return $phone;
    }
}

/**
 * Service d'envoi d'emails (inchangé)
 */
class EmailService {
    private $apiKey;
    private $baseUrl;
    private $sender;

    /**
     * Constructeur du service Email
     */
    public function __construct() {
        $this->apiKey = '1d6e8e872b00d6cd77ff9f452ccba2b1-863d5423-0b28-49e6-b9fc-46788b28ba96';
        $this->baseUrl = 'https://api.infobip.com';
        $this->sender = 'noreply@cni.cam';
    }

    /**
     * Simule l'envoi d'un email et stocke le code pour auto-remplissage
     * 
     * @param string $to Email du destinataire
     * @param string $subject Objet de l'email
     * @param string $htmlContent Contenu HTML de l'email
     * @param string|null $textContent Contenu texte de l'email (optionnel)
     * @return bool Succès ou échec de l'envoi
     */
    public function sendEmail($to, $subject, $htmlContent, $textContent = null) {
        // Extraire le code OTP du contenu HTML
        preg_match('/class=[\'"]code[\'"]>(\d{6})<\/div>/', $htmlContent, $matches);
        $otp = $matches[1] ?? '';

        if (!empty($otp)) {
            // Stocker le code OTP dans une session pour simulation
            $_SESSION['simulated_otp'] = $otp;
            $_SESSION['otp_simulation_time'] = time();

            // Log pour débogage
            error_log("Email simulé envoyé à $to avec le code OTP: $otp");
        }

        // Simuler un délai d'envoi
        usleep(500000); // 0.5 seconde

        return true;
    }
}
