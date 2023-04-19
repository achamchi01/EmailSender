<?php 

namespace Abdobaiaich\Emailsender;

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

class MSGraphEmailTransport extends AbstractTransport{

 

    protected $microsoftGraphEmail = null;
    public function __construct($config)
    {
        parent::__construct();
        $this->microsoftGraphEmail = new MicrosoftGraphEmail($config['clientid'], $config['clientsecret'], $config['tenantid'], $config['refreshtoken']);
    }

    protected function doSend(SentMessage $message): void
    {
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
        //var_dump($result); // to be deleted and uncomment the line below , just for test 
        $this->microsoftGraphEmail->send($result['to'], $result['subject'], $result['text'] == null ? $result['html'] : $result['text']);
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
