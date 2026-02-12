<?php

declare(strict_types=1);

namespace Tests\Unit\Constants;

use App\Constants\EvolutionDatabase;
use App\Constants\PokemonDatabase;
use App\Domain\Pokemon;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 *
 * @coversDefaultClass \App\Constants\EvolutionDatabase
 */
class EvolutionDatabaseTest extends TestCase
{
    /**
     * フシギダネ → [フシギソウ, フシギバナ] の2体が返る.
     */
    public function testLinearEvolutionChain(): void
    {
        $evolutions = EvolutionDatabase::getForwardEvolutions('フシギダネ');

        $names = array_map(
            static fn (Pokemon $p): string => $p->name,
            $evolutions
        );

        $this->assertCount(2, $evolutions);
        $this->assertSame(['フシギソウ', 'フシギバナ'], $names);
    }

    /**
     * イーブイ → 7体の分岐進化.
     */
    public function testBranchingEvolutionEevee(): void
    {
        $evolutions = EvolutionDatabase::getForwardEvolutions('イーブイ');

        $names = array_map(
            static fn (Pokemon $p): string => $p->name,
            $evolutions
        );

        $this->assertCount(7, $evolutions);
        $this->assertContains('シャワーズ', $names);
        $this->assertContains('サンダース', $names);
        $this->assertContains('ブースター', $names);
        $this->assertContains('エーフィ', $names);
        $this->assertContains('ブラッキー', $names);
        $this->assertContains('リーフィア', $names);
        $this->assertContains('グレイシア', $names);
    }

    /**
     * 最終進化形は空配列を返す.
     */
    public function testFinalFormReturnsEmpty(): void
    {
        $evolutions = EvolutionDatabase::getForwardEvolutions('ライチュウ');

        $this->assertSame([], $evolutions);
    }

    /**
     * リージョンフォームへの分岐進化.
     */
    public function testRegionalEvolution(): void
    {
        $evolutions = EvolutionDatabase::getForwardEvolutions('ピカチュウ');

        $names = array_map(
            static fn (Pokemon $p): string => $p->name,
            $evolutions
        );

        $this->assertCount(2, $evolutions);
        $this->assertContains('ライチュウ', $names);
        $this->assertContains('ライチュウ(アローラ)', $names);
    }

    /**
     * ベイビーポケモンからの全進化チェーン.
     */
    public function testBabyThroughFullChain(): void
    {
        $evolutions = EvolutionDatabase::getForwardEvolutions('ピチュー');

        $names = array_map(
            static fn (Pokemon $p): string => $p->name,
            $evolutions
        );

        $this->assertCount(3, $evolutions);
        $this->assertContains('ピカチュウ', $names);
        $this->assertContains('ライチュウ', $names);
        $this->assertContains('ライチュウ(アローラ)', $names);
    }

    /**
     * 全進化先がPokemonDatabaseに存在する.
     */
    public function testAllEvolutionTargetsExistInDatabase(): void
    {
        $reflection = new ReflectionClass(EvolutionDatabase::class);
        $constant = $reflection->getReflectionConstant('EVOLUTIONS');
        $this->assertNotFalse($constant);

        /** @var array<string, list<string>> $evolutions */
        $evolutions = $constant->getValue();

        foreach ($evolutions as $from => $targets) {
            foreach ($targets as $target) {
                $pokemon = PokemonDatabase::findByName($target);
                $this->assertNotNull(
                    $pokemon,
                    sprintf('進化先 "%s" (from "%s") がPokemonDatabaseに存在しません', $target, $from)
                );
            }
        }
    }
}
