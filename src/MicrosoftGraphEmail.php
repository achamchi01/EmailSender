<?php

namespace rednucleus\Emailsender;

use GuzzleHttp\Client;

class MicrosoftGraphEmail
{

    // The Microsoft Graph API endpoint for sending email
    private $baseUrl = 'https://graph.microsoft.com/v1.0/';

    private $tenantId = null;
    private $clientID = null;
    private $clientSecret = null;

    private $refreshToken = null;
    private $accessToken = null;
    private $tokenManager = null;

    private $httpClient = null;

    
    

    public function __construct($clientID, $clientSecret, $tenantId, $refreshToken)
    {
         $this->clientID = $clientID;
         $this->clientSecret = $clientSecret;
         $this->refreshToken = $refreshToken;
         $this->tenantId = $tenantId;
         $this->tokenManager = new TokenManager($this->clientID, $this->clientSecret, $this->tenantId);
         $this->httpClient  = new Client();
        
    }



    public function send($to, $subject, $body)
    {  

        $this->accessToken = $this->tokenManager->isValidToken($this->accessToken) ?
                             $this->accessToken : 
                             $this->tokenManager->getAccessToken($this->refreshToken);

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];

        $this->httpClient = new Client(['headers' => $headers]);

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'Text',
                    'content' => $body
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $to
                        ]
                    ]
                ]
            ],
            'saveToSentItems' => 'true'
        ];

        $response = $this->httpClient->post($this->baseUrl . 'me/sendMail', [
            'json' => $payload
        ]);

        return $response->getStatusCode() === 202;
    }
}
