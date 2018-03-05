<?php

namespace Timerlau\AliyunSms;

use Illuminate\Support\ServiceProvider;

class AliyunSmsServiceProvider extends ServiceProvider
{
    
    public static $abstract = 'sms';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {   
        $this->publishes([
            __DIR__.'/config/sms.php' => config_path('sms.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->_registerApp();
        $this->_mergeConfig();
    }

    private function _registerApp()
    {
        $this->app->singleton(static::$abstract, function ($app) {
            return new Sms(AliyunSmsAcsClient::getAcsClient());
        });
    }

    private function _mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/sms.php', static::$abstract
        );
    }
}
