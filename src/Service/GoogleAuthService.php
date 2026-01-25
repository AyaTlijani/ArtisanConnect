<?php
// src/Service/GoogleAuthService.php
namespace App\Service;

use Google\Client;

class GoogleAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private ?Client $client = null;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Obtient une instance du client Google
     */
    public function getClient(): Client
    {
        if (null === $this->client) {
            $this->client = new Client();
            $this->client->setClientId($this->clientId);
            $this->client->setClientSecret($this->clientSecret);
            $this->client->setRedirectUri($this->redirectUri);
            $this->client->addScope('email');
            $this->client->addScope('profile');
        }

        return $this->client;
    }

    /**
     * Génère l'URL d'authentification Google
     */
    public function getAuthUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    /**
     * Traite le code d'autorisation reçu de Google pour obtenir les informations utilisateur
     */
    public function processAuthCode(string $code): array
    {
        try {
            // Échanger le code contre un token
            $accessToken = $this->getClient()->fetchAccessTokenWithAuthCode($code);
            $this->getClient()->setAccessToken($accessToken);

            // Récupérer les informations de l'utilisateur
            $oauth2 = new \Google\Service\Oauth2($this->getClient());
            $userInfo = $oauth2->userinfo->get();

            return [
                'email' => $userInfo->getEmail(),
                'firstName' => $userInfo->getGivenName(),
                'lastName' => $userInfo->getFamilyName(),
                'picture' => $userInfo->getPicture(),
                'id' => $userInfo->getId(),
                'verified' => $userInfo->getVerifiedEmail(),
            ];
        } catch (\Exception $e) {
            // Logger l'erreur
            return ['error' => $e->getMessage()];
        }
    }
}