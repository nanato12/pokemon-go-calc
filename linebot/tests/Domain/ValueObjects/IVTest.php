<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Tests\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PokemonGoCalc\LineBot\Domain\ValueObjects\IV;

final class IVTest extends TestCase
{
    #[Test]
    public function 有効なIVで生成できる(): void
    {
        $iv = new IV(15, 14, 13);

        $this->assertSame(15, $iv->attack);
        $this->assertSame(14, $iv->defense);
        $this->assertSame(13, $iv->hp);
    }

    #[Test]
    public function 最小値0で生成できる(): void
    {
        $iv = new IV(0, 0, 0);

        $this->assertSame(0, $iv->attack);
        $this->assertSame(0, $iv->defense);
        $this->assertSame(0, $iv->hp);
    }

    #[Test]
    public function 最大値15で生成できる(): void
    {
        $iv = new IV(15, 15, 15);

        $this->assertSame(15, $iv->attack);
        $this->assertSame(15, $iv->defense);
        $this->assertSame(15, $iv->hp);
    }

    #[Test]
    #[DataProvider('invalidIVProvider')]
    public function 範囲外のIVで例外が発生する(int $attack, int $defense, int $hp): void
    {
        $this->expectException(InvalidArgumentException::class);

        new IV($attack, $defense, $hp);
    }

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function invalidIVProvider(): array
    {
        return [
            '攻撃が負の値' => [-1, 0, 0],
            '防御が負の値' => [0, -1, 0],
            'HPが負の値' => [0, 0, -1],
            '攻撃が16以上' => [16, 0, 0],
            '防御が16以上' => [0, 16, 0],
            'HPが16以上' => [0, 0, 16],
        ];
    }

    #[Test]
    public function 文字列に変換できる(): void
    {
        $iv = new IV(15, 14, 13);

        $this->assertSame('15/14/13', (string) $iv);
    }

    #[Test]
    public function 合計値を取得できる(): void
    {
        $iv = new IV(15, 14, 13);

        $this->assertSame(42, $iv->total());
    }

    #[Test]
    #[DataProvider('percentageProvider')]
    public function パーセンテージを取得できる(int $attack, int $defense, int $hp, float $expected): void
    {
        $iv = new IV($attack, $defense, $hp);

        $this->assertEqualsWithDelta($expected, $iv->percentage(), 0.01);
    }

    /**
     * @return array<string, array{int, int, int, float}>
     */
    public static function percentageProvider(): array
    {
        return [
            '100%' => [15, 15, 15, 100.0],
            '0%' => [0, 0, 0, 0.0],
            '約93%' => [15, 14, 13, 93.33],
            '約67%' => [10, 10, 10, 66.67],
        ];
    }

    #[Test]
    public function 完璧な個体値を判定できる(): void
    {
        $perfect = new IV(15, 15, 15);
        $notPerfect = new IV(15, 15, 14);

        $this->assertTrue($perfect->isPerfect());
        $this->assertFalse($notPerfect->isPerfect());
    }
}
