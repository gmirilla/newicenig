<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Legacy storage path
    |--------------------------------------------------------------------------
    |
    | Absolute path to the old app's `storage/app/public` directory, where
    | uploaded member documents physically live. Used only by
    | `php artisan legacy:migrate --only=files`.
    |
    */

    'storage_path' => env('LEGACY_STORAGE_PATH'),

];
