<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * 計算済みステータス.
 */
final readonly class PokemonStats
{
    public function __construct(
        public float $attack,
        public float $defense,
        public int $stamina,
        public int $cp,
        public float $level,
    ) {}
}
