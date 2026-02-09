<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\CpmTable;
use App\Domain\IV;
use App\Domain\League;
use App\Domain\Pokemon;
use App\Domain\RankedIv;

/**
 * IVランキングサービス.
 */
final class RankingService
{
    private const IV_MIN = 0;
    private const IV_MAX = 15;
    private const PERCENTAGE_MULTIPLIER = 100;

    /**
     * 全4096通りの個体値をリーグ用にランク付けする.
     *
     * @return RankedIv[]
     */
    public static function rankAllIvs(
        Pokemon $pokemon,
        League $league,
        float $maxLevel = CpmTable::MAX_LEVEL,
    ): array {
        $cpCap = $league->cpCap();

        /** @var array<int, array{iv: IV, level: float, stats: \App\Domain\PokemonStats, product: float}> $results */
        $results = [];

        for ($atk = self::IV_MIN; $atk <= self::IV_MAX; $atk++) {
            for ($def = self::IV_MIN; $def <= self::IV_MAX; $def++) {
                for ($sta = self::IV_MIN; $sta <= self::IV_MAX; $sta++) {
                    $iv = new IV($atk, $def, $sta);

                    $level = $cpCap !== null
                        ? CpCalculator::findMaxLevelForCp($pokemon, $iv, $cpCap, $maxLevel)
                        : $maxLevel;

                    $stats = CpCalculator::calculateStats($pokemon, $iv, $level);
                    $statProduct = CpCalculator::calculateStatProduct($stats);

                    $results[] = [
                        'iv' => $iv,
                        'level' => $level,
                        'stats' => $stats,
                        'product' => $statProduct,
                    ];
                }
            }
        }

        // ステータス積の降順でソート
        usort($results, static fn (array $a, array $b): int => $b['product'] <=> $a['product']);

        $bestProduct = $results[0]['product'] ?? 1.0;

        $rankings = [];
        foreach ($results as $rank => $entry) {
            $percent = ($entry['product'] / $bestProduct) * self::PERCENTAGE_MULTIPLIER;

            $rankings[] = new RankedIv(
                rank: $rank + 1,
                iv: $entry['iv'],
                level: $entry['level'],
                cp: $entry['stats']->cp,
                stats: $entry['stats'],
                statProduct: $entry['product'],
                statProductPercent: $percent,
            );
        }

        return $rankings;
    }

    /**
     * 指定した個体値の順位を取得する.
     */
    public static function getIvRank(
        Pokemon $pokemon,
        IV $iv,
        League $league,
        float $maxLevel = CpmTable::MAX_LEVEL,
    ): ?RankedIv {
        $rankings = self::rankAllIvs($pokemon, $league, $maxLevel);

        foreach ($rankings as $ranked) {
            if (
                $ranked->iv->attack === $iv->attack
                && $ranked->iv->defense === $iv->defense
                && $ranked->iv->stamina === $iv->stamina
            ) {
                return $ranked;
            }
        }

        return null;
    }
}
