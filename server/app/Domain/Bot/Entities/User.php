<?php

declare(strict_types=1);

namespace App\Domain\Bot\Entities;

/**
 * ユーザーエンティティ（プラットフォーム非依存）.
 */
final readonly class User
{
    public function __construct(
        public string $id,
        public ?string $displayName = null,
    ) {}
}
