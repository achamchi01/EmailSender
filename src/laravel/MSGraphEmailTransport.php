<?php 

namespace Abdobaiaich\Emailsender;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Http;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Str;
use Swift_Mime_SimpleMessage;
use Throwable;

class MicrosoftGraphTransport extends Transport
{


     private $http = null;
     private $apiEndpoint = 'https://graph.microsoft.com/v1.0/users/me/sendMail';

    public function __construct(public array $config)
    {
        $this->http = new Client();

    }


    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        
        $this->beforeSendPerformed($message);
        $payload = $this->getPayload($message);


        try {
            $this->http->post($this->apiEndpoint, [
                'headers' => $this->getHeaders(),
                'json' => [
                    'message' => $payload,
                ],
            ]);

            $this->sendPerformed($message);
            return $this->numberOfRecipients($message);
        } catch (BadResponseException $e) {
            // The API responded with 4XX or 5XX error    
        } catch (ConnectException $e) {
            // A connection error (DNS, timeout, ...) occurred
        } catch (Throwable $e) {
         
        }
    }

    /**
     * Transforms given SwiftMailer message instance into
     * Microsoft Graph message object
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    protected function getPayload(Swift_Mime_SimpleMessage $message): array {
        $from = $message->getFrom();
        $priority = $message->getPriority();
        $attachments = $message->getChildren();

        return array_filter([
            'subject' => $message->getSubject(),
            'sender' => $this->toRecipientCollection($from)[0],
            'from' => $this->toRecipientCollection($from)[0],
            'replyTo' => $this->toRecipientCollection($message->getReplyTo()),
            'toRecipients' => $this->toRecipientCollection($message->getTo()),
            'ccRecipients' => $this->toRecipientCollection($message->getCc()),
            'bccRecipients' => $this->toRecipientCollection($message->getBcc()),
            'importance' => $priority === 3 ? 'Normal' : ($priority < 3 ? 'Low' : 'High'),
            'body' => [
                'contentType' => Str::contains($message->getContentType(), ['text', 'plain']) ? 'text' : 'html',
                'content' => $message->getBody(),
            ],
            'attachments' => $this->toAttachmentCollection($attachments),
        ]);
    }

    /**
     * Transforms given SimpleMessage recipients into
     * Microsoft Graph recipients collection
     * @param array|string $recipients
     * @return array
     */
    protected function toRecipientCollection($recipients): array {
        $collection = [];

        // If the provided list is empty
        // return an empty collection
        if (!$recipients) {
            return $collection;
        }

        // Some fields yield single e-mail
        // addresses instead of arrays
        if (is_string($recipients)) {
            $collection[] = [
                'emailAddress' => [
                    'name' => null,
                    'address' => $recipients,
                ],
            ];

            return $collection;
        }

        foreach ($recipients as $address => $name) {
            $collection[] = [
                'emailAddress' => [
                    'name' => $name,
                    'address' => $address,
                ],
            ];
        }

        return $collection;
    }

    /**
     * Transforms given SwiftMailer children into
     * Microsoft Graph attachment collection
     * @param $attachments
     * @return array
     */
    protected function toAttachmentCollection($attachments): array {
        $collection = [];

        foreach ($attachments as $attachment) {
            if (!$attachment instanceof Swift_Mime_Attachment) {
                continue;
            }

            $collection[] = [
                'name' => $attachment->getFilename(),
                'contentId' => $attachment->getId(),
                'contentType' => $attachment->getContentType(),
                'contentBytes' => base64_encode($attachment->getBody()),
                'size' => strlen($attachment->getBody()),
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'isInline' => $attachment instanceof Swift_Mime_EmbeddedFile,
            ];

        }

        return $collection;
    }

    protected function getHeaders(): array {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }

    protected function getAccessToken(): string {

        /* try to get the token from the cache, if not exist then request a new one using the refresh token
        if we receive an expire time exception when we use the access token from cach we can requestion a new one
        */

        return '';
    }



    
}


?>