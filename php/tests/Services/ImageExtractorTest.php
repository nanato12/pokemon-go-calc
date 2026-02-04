<?php

declare(strict_types=1);

namespace PokemonGoCalc\Tests\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PokemonGoCalc\Models\Iv;
use PokemonGoCalc\Services\ImageExtractor;
use PokemonGoCalc\Services\PokemonIvExtractor;
use PokemonGoCalc\Services\PokemonNameExtractor;

/**
 * 画像抽出機能のテスト.
 */
final class ImageExtractorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__
        . '/../../../tests/fixtures';

    /**
     * @return \Generator<string, array{string, string, Iv}>
     */
    public static function fixtureProvider(): \Generator
    {
        $dirs = glob(self::FIXTURES_DIR . '/case*');
        if ($dirs === false) {
            return;
        }
        sort($dirs);

        foreach ($dirs as $dir) {
            $jsonPath = $dir . '/expected.json';
            $contents = file_get_contents($jsonPath);
            if ($contents === false) {
                continue;
            }

            /** @var array{pokemon: string, iv: array{attack: int, defense: int, stamina: int}} $data */
            $data = json_decode($contents, true);
            $caseName = basename($dir);

            yield $caseName => [
                $dir . '/image.png',
                $data['pokemon'],
                new Iv(
                    attack: $data['iv']['attack'],
                    defense: $data['iv']['defense'],
                    stamina: $data['iv']['stamina'],
                ),
            ];
        }
    }

    #[DataProvider('fixtureProvider')]
    public function testExtractFromScreenshot(
        string $imagePath,
        string $expectedName,
        Iv $expectedIv,
    ): void {
        $extractor = new ImageExtractor(
            new PokemonNameExtractor(),
            new PokemonIvExtractor(),
        );

        $result = $extractor->extractFromScreenshot($imagePath);

        $this->assertSame(
            $expectedName,
            $result['name'],
            "名前不一致: {$result['name']} != {$expectedName}",
        );
        $this->assertEquals(
            $expectedIv,
            $result['iv'],
            "IV不一致: {$result['iv']} != {$expectedIv}",
        );
    }
}
