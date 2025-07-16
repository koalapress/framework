<?php

/**
 * Configuration for the Plugins Manager.
 */
return [
    /* |--------------------------------------------------------------------------
     | Plugins Manager Configuration
     |--------------------------------------------------------------------------
     |
     | This configuration file is used to manage the plugins in the KoalaPress
     | ecosystem. It allows you to specify which plugins should be activated
     | and which ones should be blacklisted.
     |
     */
    'blacklist' => [
        !app()->isProduction() ? 'cachify' : null,
    ],
];
