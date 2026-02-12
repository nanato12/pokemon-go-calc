<?php

declare(strict_types=1);

namespace App\Domain;

use InvalidArgumentException;

/**
 * 個体値.
 */
final readonly class IV
{
    private const MIN = 0;

    private const MAX = 15;

    public function __construct(
        public int $attack,
        public int $defense,
        public int $stamina,
    ) {
        $this->validate($attack, 'attack');
        $this->validate($defense, 'defense');
        $this->validate($stamina, 'stamina');
    }

    /**
     * 個体値合計を取得.
     */
    public function total(): int
    {
        return $this->attack + $this->defense + $this->stamina;
    }

    private function validate(int $value, string $name): void
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidArgumentException(
                sprintf('%s must be between %d and %d, got %d', $name, self::MIN, self::MAX, $value)
            );
        }
    }
}
