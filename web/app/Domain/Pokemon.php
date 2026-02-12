<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * ポケモン種族値.
 */
final readonly class Pokemon
{
    public function __construct(
        public string $name,
        public int $dex,
        public int $baseAttack,
        public int $baseDefense,
        public int $baseStamina,
    ) {}
}
