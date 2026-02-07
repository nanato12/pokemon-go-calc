<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Infrastructure\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use PokemonGoCalc\LineBot\Application\Ports\ImageDownloaderInterface;

/**
 * LINE Messaging APIを使用した画像ダウンローダー.
 */
final class LineImageDownloader implements ImageDownloaderInterface
{
    public function __construct(
        private readonly MessagingApiBlobApi $blobApi,
    ) {
    }

    public function download(string $messageId): string
    {
        $response = $this->blobApi->getMessageContent($messageId);
        $size = $response->getSize();
        $content = $response->fread($size !== false ? $size : 0);

        if ($content === false || $content === '') {
            throw new \RuntimeException('Failed to download image');
        }

        return $content;
    }
}
