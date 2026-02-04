<?php

declare(strict_types=1);

namespace PokemonGoCalc\Services;

use PokemonGoCalc\Constants\IvConstant;
use PokemonGoCalc\Models\Iv;
use PokemonGoCalc\Support\HsvConverter;

/**
 * 個体値バーから攻撃/防御/HPの値を抽出するサービス.
 */
final class PokemonIvExtractor
{
    // バー領域クロップ比率
    private const BAR_Y1_RATIO = 0.65;
    private const BAR_Y2_RATIO = 0.87;
    private const BAR_X1_RATIO = 0.12;
    private const BAR_X2_RATIO = 0.48;

    // オレンジマスク HSV範囲
    /** @var array{int, int, int} */
    private const ORANGE_LOW = [10, 80, 150];
    /** @var array{int, int, int} */
    private const ORANGE_HIGH = [30, 255, 255];

    // ピンクマスク HSV範囲（Hueが0付近で折り返す）
    /** @var array{int, int, int} */
    private const PINK1_LOW = [0, 40, 150];
    /** @var array{int, int, int} */
    private const PINK1_HIGH = [10, 200, 255];
    /** @var array{int, int, int} */
    private const PINK2_LOW = [160, 40, 150];
    /** @var array{int, int, int} */
    private const PINK2_HIGH = [180, 200, 255];

    // グレー判定閾値
    private const GRAY_CHANNEL_DIFF_MAX = 20;
    private const GRAY_VALUE_MIN = 200;
    private const GRAY_VALUE_MAX = 245;

    // バー検出パラメータ
    private const PROFILE_THRESHOLD_RATIO = 0.3;
    private const BAR_ROW_GAP = 15;
    private const BAR_HEIGHT_MIN = 15;
    private const BAR_HEIGHT_MAX = 50;
    private const PINK_COUNT_THRESHOLD = 50;
    private const ORANGE_WIDTH_MIN = 50;
    private const ORANGE_COUNT_MIN = 30;

    /** @var list<string> */
    private const STAT_NAMES = ['attack', 'defense', 'stamina'];

    /**
     * 画像から個体値を抽出する.
     */
    public function extract(\GdImage $image): Iv
    {
        $w = imagesx($image);
        $h = imagesy($image);

        // バー領域をクロップ
        $y1 = (int) ($h * self::BAR_Y1_RATIO);
        $y2 = (int) ($h * self::BAR_Y2_RATIO);
        $x1 = (int) ($w * self::BAR_X1_RATIO);
        $x2 = (int) ($w * self::BAR_X2_RATIO);
        $barW = $x2 - $x1;
        $barH = $y2 - $y1;

        $cropped = imagecrop($image, [
            'x' => $x1,
            'y' => $y1,
            'width' => $barW,
            'height' => $barH,
        ]);
        if ($cropped === false) {
            return new Iv(attack: 0, defense: 0, stamina: 0);
        }

        try {
            $masks = $this->buildMasks($cropped, $barW, $barH);
            $barGroups = $this->detectBars(
                $masks['combined'],
                $barW,
                $barH,
            );

            return $this->calculateIvs(
                $masks['orange'],
                $masks['pink'],
                $masks['gray'],
                $barGroups,
                $barW,
            );
        } finally {
            unset($cropped);
        }
    }

    /**
     * 各色マスクを構築する.
     *
     * @return array{
     *     orange: list<list<bool>>,
     *     pink: list<list<bool>>,
     *     gray: list<list<bool>>,
     *     combined: list<list<bool>>,
     * }
     */
    private function buildMasks(
        \GdImage $image,
        int $w,
        int $h,
    ): array {
        /** @var list<list<bool>> $orange */
        $orange = [];
        /** @var list<list<bool>> $pink */
        $pink = [];
        /** @var list<list<bool>> $gray */
        $gray = [];
        /** @var list<list<bool>> $combined */
        $combined = [];

        for ($y = 0; $y < $h; $y++) {
            $orangeRow = [];
            $pinkRow = [];
            $grayRow = [];
            $combinedRow = [];

            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $hsv = HsvConverter::rgbToHsv($r, $g, $b);

                $isOrange = HsvConverter::inRange(
                    $hsv,
                    self::ORANGE_LOW,
                    self::ORANGE_HIGH,
                );
                $isPink = HsvConverter::inRange(
                    $hsv,
                    self::PINK1_LOW,
                    self::PINK1_HIGH,
                ) || HsvConverter::inRange(
                    $hsv,
                    self::PINK2_LOW,
                    self::PINK2_HIGH,
                );

                $channelDiff = max(abs($r - $g), abs($g - $b));
                $isGray = $channelDiff < self::GRAY_CHANNEL_DIFF_MAX
                    && $r > self::GRAY_VALUE_MIN
                    && $r < self::GRAY_VALUE_MAX;

                $orangeRow[] = $isOrange;
                $pinkRow[] = $isPink;
                $grayRow[] = $isGray;
                $combinedRow[] = $isOrange || $isPink || $isGray;
            }

            $orange[] = $orangeRow;
            $pink[] = $pinkRow;
            $gray[] = $grayRow;
            $combined[] = $combinedRow;
        }

        return [
            'orange' => $orange,
            'pink' => $pink,
            'gray' => $gray,
            'combined' => $combined,
        ];
    }

    /**
     * バー行を検出してグループ化する.
     *
     * @param list<list<bool>> $combinedMask
     * @return list<array{int, int}>
     */
    private function detectBars(
        array $combinedMask,
        int $w,
        int $h,
    ): array {
        // 行ごとのピクセル数を集計 (np.sum(combined_mask, axis=1) 相当)
        $barProfile = [];
        for ($y = 0; $y < $h; $y++) {
            $count = 0;
            for ($x = 0; $x < $w; $x++) {
                if ($combinedMask[$y][$x]) {
                    $count++;
                }
            }
            $barProfile[] = $count;
        }

        $maxProfile = max($barProfile);
        if ($maxProfile === 0) {
            return [];
        }

        $threshold = (int) ($maxProfile * self::PROFILE_THRESHOLD_RATIO);

        // 閾値を超える行を収集
        $barRows = [];
        for ($y = 0; $y < $h; $y++) {
            if ($barProfile[$y] > $threshold) {
                $barRows[] = $y;
            }
        }

        if ($barRows === []) {
            return [];
        }

        // 連続する行をグループ化
        /** @var list<array{int, int}> $groups */
        $groups = [];
        $start = $barRows[0];
        $count = count($barRows);
        for ($i = 1; $i < $count; $i++) {
            if ($barRows[$i] - $barRows[$i - 1] > self::BAR_ROW_GAP) {
                $groups[] = [$start, $barRows[$i - 1]];
                $start = $barRows[$i];
            }
        }
        $groups[] = [$start, $barRows[$count - 1]];

        // バー高さでフィルタ (15-50px)
        return array_values(array_filter(
            $groups,
            static fn (array $g): bool =>
                ($g[1] - $g[0]) >= self::BAR_HEIGHT_MIN
                && ($g[1] - $g[0]) <= self::BAR_HEIGHT_MAX,
        ));
    }

    /**
     * バー比率からIV値を計算する.
     *
     * @param list<list<bool>> $orangeMask
     * @param list<list<bool>> $pinkMask
     * @param list<list<bool>> $grayMask
     * @param list<array{int, int}> $barGroups
     */
    private function calculateIvs(
        array $orangeMask,
        array $pinkMask,
        array $grayMask,
        array $barGroups,
        int $barW,
    ): Iv {
        /** @var array<string, int> $results */
        $results = ['attack' => 0, 'defense' => 0, 'stamina' => 0];

        foreach (self::STAT_NAMES as $i => $stat) {
            if ($i >= count($barGroups)) {
                continue;
            }

            [$yStart, $yEnd] = $barGroups[$i];
            $barY = intdiv($yStart + $yEnd, 2);

            // ピンクチェック（ピンクならIV=15）
            $pinkCount = 0;
            for ($x = 0; $x < $barW; $x++) {
                if ($pinkMask[$barY][$x]) {
                    $pinkCount++;
                }
            }
            if ($pinkCount > self::PINK_COUNT_THRESHOLD) {
                $results[$stat] = IvConstant::IV_MAX;
                continue;
            }

            // オレンジ列・グレー列を収集
            $orangeCols = [];
            $grayCols = [];
            for ($x = 0; $x < $barW; $x++) {
                if ($orangeMask[$barY][$x]) {
                    $orangeCols[] = $x;
                }
                if ($grayMask[$barY][$x]) {
                    $grayCols[] = $x;
                }
            }

            // オレンジも灰色もない場合は0
            if ($orangeCols === [] && $grayCols === []) {
                $results[$stat] = 0;
                continue;
            }

            // 灰色のみの場合は0
            if ($orangeCols === []) {
                $results[$stat] = 0;
                continue;
            }

            $barLeft = $orangeCols[0];
            $coloredRight = $orangeCols[count($orangeCols) - 1];

            if ($grayCols !== []) {
                // オレンジの後ろに灰色があるか
                $grayAfter = array_values(array_filter(
                    $grayCols,
                    static fn (int $c): bool => $c > $coloredRight,
                ));
                if ($grayAfter !== []) {
                    $barRight = $grayAfter[count($grayAfter) - 1];
                } else {
                    $orangeWidth = $coloredRight - $barLeft;
                    if (
                        $orangeWidth < self::ORANGE_WIDTH_MIN
                        || count($orangeCols) < self::ORANGE_COUNT_MIN
                    ) {
                        $results[$stat] = 0;
                        continue;
                    }
                    $barRight = $coloredRight;
                }
            } else {
                $orangeWidth = $coloredRight - $barLeft;
                if (
                    $orangeWidth < self::ORANGE_WIDTH_MIN
                    || count($orangeCols) < self::ORANGE_COUNT_MIN
                ) {
                    $results[$stat] = 0;
                    continue;
                }
                $barRight = $coloredRight;
            }

            $totalLength = $barRight - $barLeft;
            $coloredLength = $coloredRight - $barLeft;

            if ($totalLength <= 0) {
                $results[$stat] = IvConstant::IV_MAX;
                continue;
            }

            $ratio = $coloredLength / $totalLength;
            $ivValue = min(
                IvConstant::IV_MAX,
                max(0, (int) round($ratio * IvConstant::IV_MAX)),
            );
            $results[$stat] = $ivValue;
        }

        return new Iv(
            attack: $results['attack'],
            defense: $results['defense'],
            stamina: $results['stamina'],
        );
    }
}
