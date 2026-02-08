<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Bot\Contracts\BotClientInterface;
use App\Domain\Bot\Contracts\EventParserInterface;
use App\Domain\Handlers\MessageHandlerInterface;
use App\Infrastructure\Line\Handlers\EchoHandler;
use App\Infrastructure\Line\LineBotClient;
use App\Infrastructure\Line\LineEventParser;
use App\UseCases\Bot\HandleWebhookUseCase;
use Illuminate\Support\ServiceProvider;

class LineBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // EventParser
        $this->app->singleton(EventParserInterface::class, function () {
            /** @var string $secret */
            $secret = config('line-bot.channel_secret', '');

            return new LineEventParser(
                channelSecret: $secret,
            );
        });

        // BotClient
        $this->app->singleton(BotClientInterface::class, function () {
            /** @var string $token */
            $token = config('line-bot.channel_access_token', '');

            return new LineBotClient(
                channelAccessToken: $token,
            );
        });

        // Handlers
        $this->app->tag([
            EchoHandler::class,
        ], 'bot.handlers');

        // UseCase
        $this->app->singleton(HandleWebhookUseCase::class, function ($app) {
            /** @var array<MessageHandlerInterface> $handlers */
            $handlers = iterator_to_array($app->tagged('bot.handlers'));

            return new HandleWebhookUseCase(
                eventParser: $app->make(EventParserInterface::class),
                botClient: $app->make(BotClientInterface::class),
                handlers: $handlers,
            );
        });
    }
}
