<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Tests\Domain\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PokemonGoCalc\LineBot\Domain\Entities\ExtractResult;
use PokemonGoCalc\LineBot\Domain\ValueObjects\IV;

final class ExtractResultTest extends TestCase
{
    #[Test]
    public function ポケモン名とIVで生成できる(): void
    {
        $iv = new IV(15, 14, 13);
        $result = new ExtractResult('ピカチュウ', $iv);

        $this->assertSame('ピカチュウ', $result->pokemonName);
        $this->assertSame($iv, $result->iv);
    }

    #[Test]
    public function ポケモン名がnullでも生成できる(): void
    {
        $iv = new IV(10, 10, 10);
        $result = new ExtractResult(null, $iv);

        $this->assertNull($result->pokemonName);
    }

    #[Test]
    public function メッセージに変換できる(): void
    {
        $iv = new IV(15, 14, 13);
        $result = new ExtractResult('ピカチュウ', $iv);

        $message = $result->toMessage();

        $this->assertStringContainsString('ピカチュウ', $message);
        $this->assertStringContainsString('15/14/13', $message);
        $this->assertStringContainsString('93.3%', $message);
        $this->assertStringContainsString('攻撃: 15', $message);
        $this->assertStringContainsString('防御: 14', $message);
        $this->assertStringContainsString('HP: 13', $message);
    }

    #[Test]
    public function ポケモン名がnullの場合は不明と表示される(): void
    {
        $iv = new IV(10, 10, 10);
        $result = new ExtractResult(null, $iv);

        $message = $result->toMessage();

        $this->assertStringContainsString('不明', $message);
    }

    #[Test]
    public function 完璧な個体値のメッセージ(): void
    {
        $iv = new IV(15, 15, 15);
        $result = new ExtractResult('ミュウツー', $iv);

        $message = $result->toMessage();

        $this->assertStringContainsString('ミュウツー', $message);
        $this->assertStringContainsString('15/15/15', $message);
        $this->assertStringContainsString('100.0%', $message);
    }
}
