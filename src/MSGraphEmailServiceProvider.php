<?php

namespace rednucleus\Emailsender;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use LaravelMsGraphMail\Exceptions\CouldNotSendMail;
use \Illuminate\Support\Facades\Config;

class MSGraphEmailServiceProvider extends ServiceProvider
{

    /**
     * Boot any application services.
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/rnemailsender.php' => config_path('rnemailsender.php'),
        ]);
        Mail::extend('rnemailsender', function (array $config = []) {
            $keys = Config::get("rnemailsender");
            return new MSGraphEmailTransport(array_merge($keys, $config));
        });
    }

    public function register()
    {
        $this->setEnv('MS_CLIENT_ID');
        $this->setEnv('MS_CLIENT_SECRET');
        $this->setEnv('MS_TENANT_ID');
        $this->setEnv('MS_REFRESH_TOKEN');
    }

    private function setEnv($key, $value = '')
    {
        $path = base_path('.env');

        try {
            if (file_exists($path)) {

                // Get all the lines from that file
                $lines = explode("\n", file_get_contents($path));

                $settings = collect($lines)
                    ->filter() // remove empty lines
                    ->transform(function ($item) {
                        return explode("=", $item, 2);
                    }) // separate key and values
                    ->pluck(1, 0); // keys to keys, values to values

                if (!isset($settings[$key]))
                    $settings[$key] = $value; // set the new value whether it exists or not
                $previousKey = null;
                $rebuilt = $settings->map(function ($value, $key) use (&$previousKey) {
                    $line = "";
                    if ($previousKey == null) {
                        $previousKey = $key;
                    }

                    $first_word_1 = substr($previousKey, 0, strpos($previousKey, '_'));
                    $first_word_2 = substr($key, 0, strpos($key, '_'));

                    // Compare the two words
                    if ($first_word_1 != $first_word_2) {
                        $line = "\n";
                    }

                    $line .= "$key=$value";
                    $previousKey = $key;
                    return $line;
                })->implode("\n"); // rebuild the env file

                file_put_contents($path, $rebuilt); // put the new contents
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}

?>