<?php

namespace Abdobaiaich\Emailsender;

use DateTime;
use Exception;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use \Firebase\JWT\JWT;

class TokenManager
{
    protected $clientID = null;
    protected $clientSecret = null;
    protected $httpClient = null;
    protected $tenantId = null;

    public function __construct($clientID, $clientSecret, $tenantId)
    {
         $this->clientID     = $clientID;
         $this->clientSecret = $clientSecret;
         $this->tenantId  = $tenantId;
         $this->httpClient   = new Client();
    }

   public function getRefreshToken(): string{
    /* get it from the key vault */
     return '';
   }  


   public function getAccessToken(string $refreshToken): string{

    $tokenEndpoint = "https://login.microsoftonline.com/".$this->tenantId."/oauth2/v2.0/token";
    // Send a POST request to the token endpoint with the required parameters
    $response = $this->httpClient->request('POST', $tokenEndpoint, [
    'form_params' => [
        'grant_type' => 'refresh_token',
        'client_id' => $this->clientID,
        'client_secret' => $this->clientSecret,
        'refresh_token' => $refreshToken,
        'scope' => 'https://graph.microsoft.com/.default'
       ]
   ]);


    
    $data = json_decode($response->getBody(), true);
    
    if (!isset($data['access_token'])) {
        throw new Exception('Failed to obtain access token: ' . $response->getBody());
    }
    return $data['access_token'];

  }

   /** here we check only the expiraztion time and not the signature  */
   public function isValidToken($accessToken): bool {
     if(!$accessToken) return false;
     $jwtParts   = explode('.', $accessToken);
     $jwtPayload = $jwtParts[1];
     $jsonPayload = base64_decode($jwtPayload);
     $payload = json_decode($jsonPayload);
     $exp     = $payload->exp;
     return  time() < $exp;
   }

   }

