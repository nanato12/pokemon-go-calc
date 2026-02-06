<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Domain\ValueObjects;

/**
 * 個体値 (Individual Values) Value Object.
 */
final class IV
{
    private const MIN = 0;
    private const MAX = 15;

    public function __construct(
        public readonly int $attack,
        public readonly int $defense,
        public readonly int $stamina,
    ) {
        $this->validate($attack, 'attack');
        $this->validate($defense, 'defense');
        $this->validate($stamina, 'stamina');
    }

    private function validate(int $value, string $name): void
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new \InvalidArgumentException(
                sprintf('%s must be between %d and %d', $name, self::MIN, self::MAX)
            );
        }
    }

    public function __toString(): string
    {
        return sprintf('%d/%d/%d', $this->attack, $this->defense, $this->stamina);
    }

    public function total(): int
    {
        return $this->attack + $this->defense + $this->stamina;
    }

    public function percentage(): float
    {
        return ($this->total() / (self::MAX * 3)) * 100;
    }

    public function isPerfect(): bool
    {
        return $this->attack === self::MAX
            && $this->defense === self::MAX
            && $this->stamina === self::MAX;
    }
}
