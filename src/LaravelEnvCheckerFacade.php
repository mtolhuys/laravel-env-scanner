<?php

namespace Mtolhuys\LaravelEnvChecker;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mtolhuys\LaravelEnvChecker\Skeleton\SkeletonClass
 */
class LaravelEnvCheckerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-env-checker';
    }
}
