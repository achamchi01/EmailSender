<?php

namespace rednucleus\Emailsender;

use Exception;
use Illuminate\Support\ServiceProvider;
use LaravelMsGraphMail\Exceptions\CouldNotSendMail;

class MsGraphMailServiceProvider extends ServiceProvider {

    /**
     * Boot any application services.
     * @return void
     */
    public function boot() {
        $this->app->get('mail.manager')->extend('microsoft-graph', function (array $config) {
            
            $config = []; /* get the config from config file, if not throw an exception */

            return new MicrosoftGraphTransport($config);
        });
    }

}

?>