<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Domain\Entities;

use PokemonGoCalc\LineBot\Domain\ValueObjects\IV;

/**
 * IV抽出結果 Entity.
 */
final class ExtractResult
{
    public function __construct(
        public readonly ?string $pokemonName,
        public readonly IV $iv,
    ) {
    }

    public function toMessage(): string
    {
        $name = $this->pokemonName ?? '不明';
        $percentage = number_format($this->iv->percentage(), 1);

        return sprintf(
            "🎮 %s\n\n📊 個体値: %s (%s%%)\n⚔️ 攻撃: %d\n🛡️ 防御: %d\n❤️ HP: %d",
            $name,
            (string) $this->iv,
            $percentage,
            $this->iv->attack,
            $this->iv->defense,
            $this->iv->stamina
        );
    }
}
