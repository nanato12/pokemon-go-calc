<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Constants\CpmTable;
use App\Constants\PokemonDatabase;
use App\Domain\IV;
use App\Domain\League;
use App\Domain\RankedIv;
use App\Services\RankingService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \App\Services\RankingService
 */
class RankingServiceTest extends TestCase
{
    private const TOTAL_IV_COMBINATIONS = 4096;

    /**
     * マリルリのスーパーリーグで4096件返る.
     */
    public function testRankAllIvsReturns4096Entries(): void
    {
        $pokemon = PokemonDatabase::findByName('マリルリ');
        $this->assertNotNull($pokemon);

        $rankings = RankingService::rankAllIvs($pokemon, League::GREAT);

        $this->assertCount(self::TOTAL_IV_COMBINATIONS, $rankings);
    }

    /**
     * ランク1位のステータス積が最大.
     */
    public function testBestIvIsRank1(): void
    {
        $pokemon = PokemonDatabase::findByName('マリルリ');
        $this->assertNotNull($pokemon);

        $rankings = RankingService::rankAllIvs($pokemon, League::GREAT);

        $rank1 = $rankings[0];
        $this->assertSame(1, $rank1->rank);

        // ランク1のステータス積が全エントリーの中で最大であることを確認
        $products = array_map(
            static fn (RankedIv $r): float => $r->statProduct,
            $rankings
        );
        /** @var non-empty-array<float> $products */
        $maxProduct = max($products);
        $this->assertSame($maxProduct, $rank1->statProduct);

        // ランク1のパーセンテージは100%
        $this->assertEqualsWithDelta(100.0, $rank1->statProductPercent, 0.001);
    }

    /**
     * マリルリ 0/15/15 がスーパーリーグ1位.
     */
    public function testGetIvRankReturnsCorrectRank(): void
    {
        $pokemon = PokemonDatabase::findByName('マリルリ');
        $this->assertNotNull($pokemon);

        $iv = new IV(0, 15, 15);
        $ranked = RankingService::getIvRank($pokemon, $iv, League::GREAT);

        $this->assertNotNull($ranked);
        $this->assertSame(1, $ranked->rank);
        $this->assertSame(0, $ranked->iv->attack);
        $this->assertSame(15, $ranked->iv->defense);
        $this->assertSame(15, $ranked->iv->stamina);
    }

    /**
     * マスターリーグで15/15/15がトップ付近であることを確認.
     *
     * HP flooring により 15/15/14 と 15/15/15 が同率になる場合がある
     */
    public function testMasterLeaguePerfectIvIsTopRank(): void
    {
        $pokemon = PokemonDatabase::findByName('マリルリ');
        $this->assertNotNull($pokemon);

        $iv = new IV(15, 15, 15);
        $ranked = RankingService::getIvRank($pokemon, $iv, League::MASTER);

        $this->assertNotNull($ranked);
        $this->assertLessThanOrEqual(3, $ranked->rank);
        $this->assertEqualsWithDelta(100.0, $ranked->statProductPercent, 0.5);
    }

    /**
     * マスターリーグは全IVが同じレベル（51.0）.
     */
    public function testMasterLeagueAllSameLevel(): void
    {
        $pokemon = PokemonDatabase::findByName('マリルリ');
        $this->assertNotNull($pokemon);

        $rankings = RankingService::rankAllIvs($pokemon, League::MASTER);

        foreach ($rankings as $ranked) {
            $this->assertSame(
                CpmTable::MAX_LEVEL,
                $ranked->level,
                sprintf(
                    'IV %d/%d/%d のレベルが %.1f (期待値: %.1f)',
                    $ranked->iv->attack,
                    $ranked->iv->defense,
                    $ranked->iv->stamina,
                    $ranked->level,
                    CpmTable::MAX_LEVEL,
                )
            );
        }
    }
}
