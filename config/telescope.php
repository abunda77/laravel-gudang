<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telescope Enabled
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable Telescope. This is useful when you
    | want to disable Telescope in production environments.
    |
    */

    'enabled' => env('TELESCOPE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Telescope Route Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Telescope will be accessible from. Feel free
    | to change this path to anything you like.
    |
    */

    'path' => env('TELESCOPE_PATH', 'telescope'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Telescope's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | Telescope. The watchers gather application information when a request
    | or task is executed. Feel free to customize this list as needed.
    |
    */

    'watchers' => [
        'batch' => env('TELESCOPE_BATCH_WATCHER', true),
        'cache' => env('TELESCOPE_CACHE_WATCHER', true),
        'command' => env('TELESCOPE_COMMAND_WATCHER', true),
        'dump' => env('TELESCOPE_DUMP_WATCHER', true),
        'event' => env('TELESCOPE_EVENT_WATCHER', true),
        'exception' => env('TELESCOPE_EXCEPTION_WATCHER', true),
        'gate' => env('TELESCOPE_GATE_WATCHER', true),
        'job' => env('TELESCOPE_JOB_WATCHER', true),
        'log' => env('TELESCOPE_LOG_WATCHER', true),
        'mail' => env('TELESCOPE_MAIL_WATCHER', true),
        'model' => env('TELESCOPE_MODEL_WATCHER', true),
        'notification' => env('TELESCOPE_NOTIFICATION_WATCHER', true),
        'query' => env('TELESCOPE_QUERY_WATCHER', true),
        'redis' => env('TELESCOPE_REDIS_WATCHER', true),
        'request' => env('TELESCOPE_REQUEST_WATCHER', true),
        'schedule' => env('TELESCOPE_SCHEDULE_WATCHER', true),
        'view' => env('TELESCOPE_VIEW_WATCHER', true),
    ],

];
