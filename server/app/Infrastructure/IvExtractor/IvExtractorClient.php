<?php

declare(strict_types=1);

namespace App\Infrastructure\IvExtractor;

use IvExtractorClient\Api\DefaultApi;
use IvExtractorClient\Model\ExtractResponse;
use RuntimeException;
use SplFileObject;

/**
 * IV抽出APIクライアント.
 */
final class IvExtractorClient
{
    public function __construct(
        private readonly DefaultApi $api,
    ) {}

    /**
     * 画像からIVを抽出.
     *
     * @param string $imageData 画像バイナリデータ
     */
    public function extract(string $imageData): ExtractResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pokemon_');

        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temp file');
        }

        try {
            file_put_contents($tempFile, $imageData);
            $splFile = new SplFileObject($tempFile, 'r');

            $response = $this->api->extractIv($splFile);

            if (!$response instanceof ExtractResponse) {
                throw new RuntimeException('API returned an error');
            }

            return $response;
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
