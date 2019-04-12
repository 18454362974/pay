<?php
namespace OsPay\Pay;

use Illuminate\Support\Facades\Facade;
use OsPay\Pay\Factory;

/**
 * 
 */
class OsPay extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}