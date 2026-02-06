<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PokemonGoCalc\LineBot\Presentation\Container;

// .env ファイルを読み込み (存在する場合のみ)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// ヘルスチェック
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

// Webhook
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');

if ($body === false || $signature === '') {
    http_response_code(400);
    exit;
}

try {
    $container = new Container();
    $handler = $container->getWebhookHandler();
    $handler->handle($body, $signature);

    http_response_code(200);
    echo 'OK';
} catch (\Throwable $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal Server Error';
}
