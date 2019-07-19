<?php

namespace Mtolhuys\LaravelEnvScanner;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mtolhuys\LaravelEnvScanner\Skeleton\SkeletonClass
 */
class LaravelEnvScannerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-env-scanner';
    }
}
