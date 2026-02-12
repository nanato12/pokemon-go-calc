<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\IvExtractor\IvExtractorClient;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use IvExtractorClient\Api\DefaultApi;
use IvExtractorClient\Configuration;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Clients\MessagingApi\Configuration as LineConfiguration;
use Phine\Client;

class LineBotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Phine Client
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

        // LINE Blob API (画像ダウンロード用)
        $this->app->singleton(MessagingApiBlobApi::class, function () {
            /** @var string $token */
            $token = config('line-bot.channel_access_token', '');

            $config = (new LineConfiguration())->setAccessToken($token);

            return new MessagingApiBlobApi(
                client: new GuzzleClient(),
                config: $config,
            );
        });

        // IV Extractor API Client
        $this->app->singleton(DefaultApi::class, function () {
            $config = new Configuration();
            /** @var string $host */
            $host = config('line-bot.iv_extractor_url', '');
            $config->setHost($host);

            return new DefaultApi(
                client: new GuzzleClient(),
                config: $config,
            );
        });

        $this->app->singleton(IvExtractorClient::class, function ($app) {
            return new IvExtractorClient(
                api: $app->make(DefaultApi::class),
            );
        });
    }
}
