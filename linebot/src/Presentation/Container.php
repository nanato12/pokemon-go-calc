<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Presentation;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Clients\MessagingApi\Configuration as LineConfiguration;
use PokemonGoCalc\IvExtractorClient\Api\DefaultApi;
use PokemonGoCalc\IvExtractorClient\Configuration as IvExtractorConfiguration;
use PokemonGoCalc\LineBot\Application\Ports\ImageDownloaderInterface;
use PokemonGoCalc\LineBot\Application\Ports\IvExtractorInterface;
use PokemonGoCalc\LineBot\Application\Ports\MessageSenderInterface;
use PokemonGoCalc\LineBot\Application\UseCases\HandleImageMessageUseCase;
use PokemonGoCalc\LineBot\Infrastructure\ApiClient\IvExtractorApiClient;
use PokemonGoCalc\LineBot\Infrastructure\Http\WebhookHandler;
use PokemonGoCalc\LineBot\Infrastructure\LineBot\LineImageDownloader;
use PokemonGoCalc\LineBot\Infrastructure\LineBot\LineMessageSender;

/**
 * DIコンテナ.
 */
final class Container
{
    private readonly string $channelAccessToken;
    private readonly string $channelSecret;
    private readonly string $apiUrl;

    public function __construct()
    {
        $this->channelAccessToken = $this->getEnv('LINE_CHANNEL_ACCESS_TOKEN');
        $this->channelSecret = $this->getEnv('LINE_CHANNEL_SECRET');
        $this->apiUrl = $this->getEnv('IV_EXTRACTOR_API_URL');
    }

    private function getEnv(string $name): string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            throw new \RuntimeException("Environment variable {$name} is required");
        }
        return $value;
    }

    public function getWebhookHandler(): WebhookHandler
    {
        return new WebhookHandler(
            handleImageUseCase: $this->getHandleImageMessageUseCase(),
            messageSender: $this->getMessageSender(),
            channelSecret: $this->channelSecret,
        );
    }

    private function getHandleImageMessageUseCase(): HandleImageMessageUseCase
    {
        return new HandleImageMessageUseCase(
            imageDownloader: $this->getImageDownloader(),
            ivExtractor: $this->getIvExtractor(),
            messageSender: $this->getMessageSender(),
        );
    }

    private function getImageDownloader(): ImageDownloaderInterface
    {
        return new LineImageDownloader($this->getBlobApi());
    }

    private function getIvExtractor(): IvExtractorInterface
    {
        return new IvExtractorApiClient($this->getIvExtractorApi());
    }

    private function getMessageSender(): MessageSenderInterface
    {
        return new LineMessageSender($this->getMessagingApi());
    }

    private function getIvExtractorApi(): DefaultApi
    {
        $config = IvExtractorConfiguration::getDefaultConfiguration();
        $config->setHost($this->apiUrl);

        return new DefaultApi(
            client: $this->getHttpClient(),
            config: $config,
        );
    }

    private function getMessagingApi(): MessagingApiApi
    {
        $config = new LineConfiguration();
        $config->setAccessToken($this->channelAccessToken);

        return new MessagingApiApi(
            client: $this->getHttpClient(),
            config: $config,
        );
    }

    private function getBlobApi(): MessagingApiBlobApi
    {
        $config = new LineConfiguration();
        $config->setAccessToken($this->channelAccessToken);

        return new MessagingApiBlobApi(
            client: $this->getHttpClient(),
            config: $config,
        );
    }

    private function getHttpClient(): ClientInterface
    {
        return new Client(['timeout' => 30.0]);
    }
}
