<?php

declare(strict_types=1);

namespace App\Domain\Bot\Entities;

/**
 * メッセージエンティティ（プラットフォーム非依存）.
 */
final readonly class Message
{
    public function __construct(
        public string $id,
        public string $text,
        public ?string $quoteToken = null,
    ) {}
}
