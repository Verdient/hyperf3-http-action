<?php

use function Hyperf\Support\env;

return [
    'async' => [
        'default_queue' => env('HTTP_ACTION_ASYNC_DEFAULT_QUEUE')
    ]
];
