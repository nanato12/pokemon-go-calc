<?php

declare(strict_types=1);

namespace PokemonGoCalc\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * テキストボックスからポケモン名を抽出するサービス.
 */
final class PokemonNameExtractor
{
    /** テキスト領域の開始位置（画像上部からの比率） */
    private const float TEXT_REGION_TOP_RATIO = 0.80;

    /** 二値化の閾値 */
    private const int THRESHOLD_VALUE = 200;

    /** 白ピクセル値 */
    private const int WHITE = 255;

    /** 黒ピクセル値 */
    private const int BLACK = 0;

    /** ポケモン名抽出パターン */
    private const string NAME_PATTERN = '/この(.+?)[をは]/u';

    /**
     * 画像からポケモン名を抽出する.
     */
    public function extract(\GdImage $image): ?string
    {
        $w = imagesx($image);
        $h = imagesy($image);

        // 下部20%をクロップ
        $y = (int) ($h * self::TEXT_REGION_TOP_RATIO);
        $cropH = $h - $y;
        $cropped = imagecrop($image, [
            'x' => 0,
            'y' => $y,
            'width' => $w,
            'height' => $cropH,
        ]);
        if ($cropped === false) {
            return null;
        }

        try {
            // グレースケール変換
            imagefilter($cropped, IMG_FILTER_GRAYSCALE);

            // 二値化
            $this->applyThreshold($cropped, $w, $cropH);

            // 一時ファイルに保存してOCR
            $tmpPath = tempnam(sys_get_temp_dir(), 'ocr_') . '.png';
            try {
                imagepng($cropped, $tmpPath);

                /** @var string $text */
                $text = (new TesseractOCR($tmpPath))
                    ->lang('jpn')
                    ->run();
            } finally {
                if (file_exists($tmpPath)) {
                    unlink($tmpPath);
                }
            }
        } finally {
            unset($cropped);
        }

        // 「この〇〇を」または「この〇〇は」パターンで名前を抽出
        if (preg_match(self::NAME_PATTERN, $text, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * 二値化（閾値200で白黒変換）.
     */
    private function applyThreshold(
        \GdImage $image,
        int $w,
        int $h,
    ): void {
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $gray = imagecolorat($image, $x, $y) & 0xFF;
                $val = ($gray > self::THRESHOLD_VALUE)
                    ? self::WHITE
                    : self::BLACK;
                $color = imagecolorallocate($image, $val, $val, $val);
                if ($color !== false) {
                    imagesetpixel($image, $x, $y, $color);
                }
            }
        }
    }
}
