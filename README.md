# README #

In this package, we support both native PHP applications and the Laravel framework application.

- To use the native PHP app, you can use the `MicrosoftGraphEmail` class.
- To use the Laravel framework, you can register the service provider `MSGraphEmailServiceProvider` and use the custom transport `MSGraphEmailTransport` to send email. You can set the custom transport as the default one in the config file `/config/mail.php` => (`'default' => env('MAIL_MAILER', 'microsoft-graph')`). Then use `Mail::to('email-receiver')->send(new MyMailableClass())`.

This is the process to generate and send email:

The `Mail` facade in Laravel provides a simple interface for sending email messages. When you call the `Mail::to()` method, it actually creates an instance of the `Illuminate\Mail\Mailer` class, which is responsible for sending email messages. The `Mailer` class is responsible for sending messages using different transports, such as SMTP, sendmail, and custom transports. It uses the `TransportManager` to resolve the appropriate transport based on the configuration options set in the `config/mail.php` file (in this case it's the `MSGraphEmailTransport`). Once it has resolved the transport, it calls the `send` method on the transport to actually send the email.

The `send` method in the email transport class then takes a `Swift_Mime_SimpleMessage` instance as an argument, which is constructed from the `Mailable` instance passed to the `send` method in the `Mailer` class.

We will use the caching system (memcached, ...) to cache the access token. If the cache functionality is not configured, the package will try to generate the access token from the refresh token each time we send an email in the job queue.

The caching system is managed by the shared class TokenMnager, we can use `Cache::store('memcached')->put('access_token_ms_actsupport', function(){ return 'value'; }, $new_minutes);` instead of `remember()` methode 


docker-compose.yml:

``` yaml

version: '3'

services:
  app:
    image: php:7-cli-alpine
    volumes:
      - ./:/app
    working_dir: /app
    command: php -S 0.0.0.0:8080
    ports:
      - 8080:8080
    networks:
      - my-network

networks:
  my-network:
