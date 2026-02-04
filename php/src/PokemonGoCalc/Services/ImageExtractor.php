<?php

declare(strict_types=1);

namespace PokemonGoCalc\Services;

use InvalidArgumentException;
use PokemonGoCalc\Models\Iv;

/**
 * スクリーンショットからポケモン名と個体値を抽出するファサード.
 */
final class ImageExtractor
{
    public function __construct(
        private readonly PokemonNameExtractor $nameExtractor,
        private readonly PokemonIvExtractor $ivExtractor,
    ) {
    }

    /**
     * スクリーンショットからポケモン名と個体値を抽出する.
     *
     * @return array{name: ?string, iv: Iv}
     *
     * @throws InvalidArgumentException 画像を読み込めない場合
     */
    public function extractFromScreenshot(string $imagePath): array
    {
        $image = @imagecreatefrompng($imagePath);
        if ($image === false) {
            throw new InvalidArgumentException(
                "画像を読み込めません: {$imagePath}",
            );
        }

        try {
            $name = $this->nameExtractor->extract($image);
            $iv = $this->ivExtractor->extract($image);
        } finally {
            unset($image);
        }

        return ['name' => $name, 'iv' => $iv];
    }
}
