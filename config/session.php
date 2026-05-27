<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | Database est un bon choix pour la production
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime - AUGMENTÉ pour éviter les 419
    |--------------------------------------------------------------------------
    |
    | 120 minutes = 2 heures (déjà bien configuré)
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery - RÉDUIT pour moins de nettoyage
    |--------------------------------------------------------------------------
    |
    | Réduire la fréquence de nettoyage des sessions expirées
    |
    */

    'lottery' => [1, 200], // Au lieu de [2, 100]

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'_session' // Underscore au lieu de tiret
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain - IMPORTANT pour les sous-domaines
    |--------------------------------------------------------------------------
    |
    | Décommentez et adaptez si vous utilisez des sous-domaines
    |
    */

    'domain' => env('SESSION_DOMAIN', null),
    // Pour les sous-domaines : .votredomaine.com

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies - IMPORTANT en production
    |--------------------------------------------------------------------------
    */

    'secure' => env('SESSION_SECURE_COOKIE', true), // true en production

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies - POUVANT ÊTRE AJUSTÉ
    |--------------------------------------------------------------------------
    |
    | "lax" est généralement le meilleur choix
    | Si problèmes de CORS, essayer "none" avec secure=true
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];