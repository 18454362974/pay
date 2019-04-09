<?php
namespace OsPay\Pay;

use OsPay\Pay\OsPay;
use Illuminate\Support\ServiceProvider;

/**
 * 
 */
class OsPayServiceProvider extends ServiceProvider
{
	
    // protected $defer = true;
    
    public function register()
    {
        $this->app->singleton(OsPay::class, function ($app) {
            return new OsPay(config('os_pay'));
        });
    }
	
    public function boot()
    {}

}