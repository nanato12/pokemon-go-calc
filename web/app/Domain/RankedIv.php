<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * ランク付き個体値.
 */
final readonly class RankedIv
{
    public function __construct(
        public int $rank,
        public IV $iv,
        public float $level,
        public int $cp,
        public PokemonStats $stats,
        public float $statProduct,
        public float $statProductPercent,
    ) {}
}
