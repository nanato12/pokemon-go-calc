<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phine\Client;

class LineBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            /** @var string $secret */
            $secret = config('line-bot.channel_secret', '');

            /** @var string $token */
            $token = config('line-bot.channel_access_token', '');

            return new Client(
                channelAccessSecret: $secret,
                channelAccessToken: $token,
            );
        });
    }
}
