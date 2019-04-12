<?php
namespace OsPay\Pay;

use OsPay\Pay\OsPay;
use Illuminate\Support\ServiceProvider;
use OsPay\Pay\Factory;
use SocialiteProviders\Manager\Contracts\Helpers\ConfigRetrieverInterface;
use SocialiteProviders\Manager\Helpers\ConfigRetriever;

/**
 * 
 */
class OsPayServiceProvider extends ServiceProvider
{
	
    protected $defer = true;
    
    public function register()
    {
        $this->app->singleton(Factory::class, function ($app) {
            // return new OsPay(config('os_pay'));
            return new OsPayManager(config('riak'));
        });
    }

    public function boot () 
    {}

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Factory::class];
    }
}