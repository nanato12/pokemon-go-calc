<?php

declare(strict_types=1);

namespace PokemonGoCalc\Support;

/**
 * RGB→HSV変換ユーティリティ.
 *
 * OpenCV互換のスケール: H(0-180), S(0-255), V(0-255)
 */
final class HsvConverter
{
    private const SV_MAX = 255;
    private const H_SCALE = 360.0;

    /**
     * RGB→HSV変換（OpenCV互換スケール）.
     *
     * @return array{h: int, s: int, v: int}
     */
    public static function rgbToHsv(int $r, int $g, int $b): array
    {
        $rf = $r / self::SV_MAX;
        $gf = $g / self::SV_MAX;
        $bf = $b / self::SV_MAX;

        $max = max($rf, $gf, $bf);
        $min = min($rf, $gf, $bf);
        $diff = $max - $min;

        // Hue (0-360)
        $h = 0.0;
        if ($diff > 0.0) {
            if ($max === $rf) {
                $h = 60.0 * fmod(($gf - $bf) / $diff, 6.0);
            } elseif ($max === $gf) {
                $h = 60.0 * ((($bf - $rf) / $diff) + 2.0);
            } else {
                $h = 60.0 * ((($rf - $gf) / $diff) + 4.0);
            }
        }
        if ($h < 0.0) {
            $h += self::H_SCALE;
        }

        // Saturation (0-1)
        $s = ($max > 0.0) ? ($diff / $max) : 0.0;

        // OpenCVスケールに変換: H(0-180), S(0-255), V(0-255)
        return [
            'h' => (int) round($h / 2.0),
            's' => (int) round($s * self::SV_MAX),
            'v' => (int) round($max * self::SV_MAX),
        ];
    }

    /**
     * HSV値が指定範囲内かチェック（cv2.inRange相当）.
     *
     * @param array{h: int, s: int, v: int} $hsv
     * @param array{int, int, int} $low  [h_min, s_min, v_min]
     * @param array{int, int, int} $high [h_max, s_max, v_max]
     */
    public static function inRange(
        array $hsv,
        array $low,
        array $high,
    ): bool {
        return $hsv['h'] >= $low[0] && $hsv['h'] <= $high[0]
            && $hsv['s'] >= $low[1] && $hsv['s'] <= $high[1]
            && $hsv['v'] >= $low[2] && $hsv['v'] <= $high[2];
    }
}
