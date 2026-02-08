<?php

declare(strict_types=1);

namespace App\Domain\Bot\Entities;

/**
 * イベントエンティティ（プラットフォーム非依存）.
 */
final readonly class Event
{
    public function __construct(
        public EventType $type,
        public string $replyToken,
        public User $user,
        public ?Message $message = null,
        public mixed $rawEvent = null,
    ) {}
}
