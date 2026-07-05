<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

/**
 * Resolves trusted proxies at request time, after configuration is available.
 */
class TrustProxies extends Middleware
{
    public function __construct()
    {
        $this->proxies = config('deployment.trusted_proxies');
    }
}
