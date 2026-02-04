<?php

declare(strict_types=1);

namespace PokemonGoCalc\Models;

use InvalidArgumentException;
use PokemonGoCalc\Constants\IvConstant;

/**
 * 個体値（Individual Values）.
 */
final readonly class Iv
{
    public function __construct(
        public int $attack,
        public int $defense,
        public int $stamina,
    ) {
        self::validate($attack, 'attack');
        self::validate($defense, 'defense');
        self::validate($stamina, 'stamina');
    }

    /**
     * 合計値を返す.
     */
    public function total(): int
    {
        return $this->attack + $this->defense + $this->stamina;
    }

    /**
     * パーセンテージを返す（0-100%）.
     */
    public function percentage(): float
    {
        return ($this->total() / IvConstant::MAX_TOTAL)
            * IvConstant::PERCENTAGE_MULTIPLIER;
    }

    public function __toString(): string
    {
        return "{$this->attack}/{$this->defense}/{$this->stamina}";
    }

    private static function validate(int $value, string $name): void
    {
        if ($value < IvConstant::IV_MIN || $value > IvConstant::IV_MAX) {
            throw new InvalidArgumentException(
                sprintf(
                    '%sは%d〜%dの範囲で指定してください（入力値: %d）',
                    $name,
                    IvConstant::IV_MIN,
                    IvConstant::IV_MAX,
                    $value,
                ),
            );
        }
    }
}
