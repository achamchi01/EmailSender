<?php

namespace rednucleus\Emailsender;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Http;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Str;
use Swift_Mime_SimpleMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Throwable;

class MSGraphEmailTransport extends AbstractTransport
{
    protected $microsoftGraphEmail = null;
    protected $sender = null;

    public function __construct($config)
    {
        parent::__construct();
        $this->sender = $config['sender'];
        $this->microsoftGraphEmail = new MicrosoftGraphEmail($config['clientid'], $config['clientsecret'], $config['tenantid'], $config['refreshtoken']);
    }

    protected function doSend(SentMessage $message): void
    {
        switch($this->sender) {
            case 'smtp': 
                dd("Send email using SMTP");
                break;
            case 'msgraph':
                dd("Send email using msgraph");
                $email = MessageConverter::toEmail($message->getOriginalMessage());
                $result = [
                    'from_email' => $email->getFrom(),
                    'to' => collect($email->getTo())->map(function ($email) {
                        return ['email' => $email->getAddress(), 'type' => 'to'];
                    })->all(),
                    'subject' => $email->getSubject(),
                    'text' => $email->getTextBody(),
                    'html' => $email->getHtmlBody(),
                ];

                $this->microsoftGraphEmail->send($result['to'], $result['subject'], $result['text'] == null ? $result['html'] : $result['text']);
                break;
            default: 
                exit();
        }
        
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }

}