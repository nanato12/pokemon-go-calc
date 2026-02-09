<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

const PRODUCTION_HOST = 'go-pilot.line-bot.jp';
const PRODUCTION_PATH = '/home/nanato12/github.com/line-bot-go-pilot';

const STAGING_HOST = 'go-pilot-stg.line-bot.jp';
const STAGING_PATH = '/home/nanato12/github.com/line-bot-go-pilot-stg';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
switch ($_SERVER['HTTP_HOST'] ?? '') {
    case PRODUCTION_HOST:
        require PRODUCTION_PATH . '/web/vendor/autoload.php';
        /** @var Application $app */
        $app = require_once PRODUCTION_PATH . '/web/bootstrap/app.php';
        break;

    case STAGING_HOST:
        require STAGING_PATH . '/web/vendor/autoload.php';
        /** @var Application $app */
        $app = require_once STAGING_PATH . '/web/bootstrap/app.php';
        break;

    default:
        require __DIR__ . '/../vendor/autoload.php';
        /** @var Application $app */
        $app = require_once __DIR__ . '/../bootstrap/app.php';
}

// Bootstrap Laravel and handle the request...
$app->handleRequest(Request::capture());
