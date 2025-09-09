<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Throttle Configurations
    |--------------------------------------------------------------------------
    |
    | Define custom throttle limits for different features in your application.
    | Format: 'name' => 'attempts,decay_minutes'
    |
    */

    'comments' => '20,1',        // 20 comments per minute
    'likes' => '120,1',          // 120 likes per minute (more frequent)
    'flags' => '10,60',          // 10 flags per hour
    'notifications' => '120,1',   // 120 notification requests per minute
    'search' => '60,1',          // 60 searches per minute
    'uploads' => '10,60',        // 10 file uploads per hour
];