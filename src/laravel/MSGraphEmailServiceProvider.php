<?php

namespace Abdobaiaich\Emailsender;

use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use LaravelMsGraphMail\Exceptions\CouldNotSendMail;
use \Illuminate\Support\Facades\Config;

class MsGraphMailServiceProvider extends ServiceProvider {

    /**
     * Boot any application services.
     * @return void
     */
    public function boot() {

        $configPath = __DIR__ . '/../../../config/msgraph.php'; /* path to the msgraph in the config laravel folder  */
        $this->publishes([
            __DIR__.'/../config/msgraph.php' => config_path('msgraph.php'),
        ]);
        Mail::extend('msgraph', function (array $config = []) {
            $configs = Config::get("msgraph"); 
            return new MSGraphEmailTransport($config);
        });
    }

}

?>