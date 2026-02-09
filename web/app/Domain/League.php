<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Pokemon GOバトルリーグ.
 */
enum League: string
{
    case LITTLE = 'little';
    case GREAT = 'great';
    case ULTRA = 'ultra';
    case MASTER = 'master';

    /**
     * CP上限を取得.
     */
    public function cpCap(): ?int
    {
        return match ($this) {
            self::LITTLE => 500,
            self::GREAT => 1500,
            self::ULTRA => 2500,
            self::MASTER => null,
        };
    }

    /**
     * 表示名を取得.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::LITTLE => 'リトルカップ',
            self::GREAT => 'スーパーリーグ',
            self::ULTRA => 'ハイパーリーグ',
            self::MASTER => 'マスターリーグ',
        };
    }
}
