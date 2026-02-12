<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\CpmTable;
use App\Domain\IV;
use App\Domain\Pokemon;
use App\Domain\PokemonStats;

/**
 * CP・ステータス計算サービス.
 *
 * 計算ロジック: pokemongo-get.com/cpcal/
 */
final class CpCalculator
{
    private const MIN_CP = 10;

    private const CP_DIVISOR = 10;

    private const STAT_PRODUCT_DIVISOR = 1000;

    /**
     * CPを計算する.
     *
     * 計算式: CP = floor((Atk × √Def × √HP × CPM²) / 10)
     */
    public static function calculateCp(Pokemon $pokemon, IV $iv, float $level): int
    {
        $cpm = CpmTable::get($level);

        $attack = $pokemon->baseAttack + $iv->attack;
        $defense = $pokemon->baseDefense + $iv->defense;
        $stamina = $pokemon->baseStamina + $iv->stamina;

        $cp = (int) ($attack * sqrt($defense) * sqrt($stamina) * ($cpm * $cpm) / self::CP_DIVISOR);

        return max($cp, self::MIN_CP);
    }

    /**
     * ステータスを計算する.
     */
    public static function calculateStats(Pokemon $pokemon, IV $iv, float $level): PokemonStats
    {
        $cpm = CpmTable::get($level);

        $attack = ($pokemon->baseAttack + $iv->attack) * $cpm;
        $defense = ($pokemon->baseDefense + $iv->defense) * $cpm;
        $stamina = (int) (($pokemon->baseStamina + $iv->stamina) * $cpm);

        $cp = self::calculateCp($pokemon, $iv, $level);

        return new PokemonStats(
            attack: $attack,
            defense: $defense,
            stamina: $stamina,
            cp: $cp,
            level: $level,
        );
    }

    /**
     * ステータス積を計算する.
     *
     * 計算式: Stat Product = Attack × Defense × HP / 1000
     */
    public static function calculateStatProduct(PokemonStats $stats): float
    {
        return $stats->attack * $stats->defense * $stats->stamina / self::STAT_PRODUCT_DIVISOR;
    }

    /**
     * CP上限以下で最大のレベルを探す.
     */
    public static function findMaxLevelForCp(
        Pokemon $pokemon,
        IV $iv,
        int $maxCp,
        float $maxLevel = CpmTable::MAX_LEVEL,
    ): float {
        $bestLevel = CpmTable::MIN_LEVEL;

        foreach (CpmTable::allLevels() as $level) {
            if ($level > $maxLevel) {
                break;
            }

            $cp = self::calculateCp($pokemon, $iv, $level);

            if ($cp <= $maxCp) {
                $bestLevel = $level;
            } else {
                break;
            }
        }

        return $bestLevel;
    }
}
