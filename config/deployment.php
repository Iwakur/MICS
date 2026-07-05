<?php

/**
 * MICS-specific infrastructure settings that are safe to cache in production.
 */

return [
    'trusted_proxies' => env('TRUSTED_PROXIES'),
];
