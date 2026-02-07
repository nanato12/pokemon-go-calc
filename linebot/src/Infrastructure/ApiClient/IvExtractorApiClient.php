<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Infrastructure\ApiClient;

use PokemonGoCalc\IvExtractorClient\Api\DefaultApi;
use PokemonGoCalc\IvExtractorClient\Model\ExtractResponse;
use PokemonGoCalc\LineBot\Application\Ports\IvExtractorInterface;
use PokemonGoCalc\LineBot\Domain\Entities\ExtractResult;
use PokemonGoCalc\LineBot\Domain\ValueObjects\IV;

/**
 * IV抽出APIクライアント.
 */
final class IvExtractorApiClient implements IvExtractorInterface
{
    public function __construct(
        private readonly DefaultApi $api,
    ) {
    }

    public function extract(string $imageData): ExtractResult
    {
        // 一時ファイルを作成してSplFileObjectとして渡す
        $tempFile = tempnam(sys_get_temp_dir(), 'pokemon_');
        if ($tempFile === false) {
            throw new \RuntimeException('Failed to create temp file');
        }

        try {
            file_put_contents($tempFile, $imageData);
            $splFile = new \SplFileObject($tempFile, 'r');

            $response = $this->api->extractIv($splFile);

            if (!($response instanceof ExtractResponse)) {
                throw new \RuntimeException('API returned an error');
            }

            $iv = $response->getIv();

            return new ExtractResult(
                pokemonName: $response->getPokemon(),
                iv: new IV(
                    attack: $iv->getAttack(),
                    defense: $iv->getDefense(),
                    hp: $iv->getStamina(),
                ),
            );
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
