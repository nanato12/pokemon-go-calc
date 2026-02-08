<?php

use App\Providers\AppServiceProvider;
use App\Providers\DatabaseQueryServiceProvider;
use App\Providers\LineBotServiceProvider;

return [
    AppServiceProvider::class,
    DatabaseQueryServiceProvider::class,
    LineBotServiceProvider::class,
];
