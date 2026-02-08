<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Handlers;

use App\Domain\Bot\Contracts\BotClientInterface;
use App\Domain\Bot\Entities\Event;
use App\Domain\Bot\Entities\EventType;
use App\Domain\Handlers\MessageHandlerInterface;

/**
 * エコーハンドラ（テスト用）.
 */
final class EchoHandler implements MessageHandlerInterface
{
    private const string COMMAND = '/echo';

    public function canHandle(Event $event): bool
    {
        if ($event->type !== EventType::MESSAGE) {
            return false;
        }

        if ($event->message === null) {
            return false;
        }

        return str_starts_with($event->message->text, self::COMMAND);
    }

    public function handle(BotClientInterface $client, Event $event): void
    {
        if ($event->message === null) {
            return;
        }

        $text = trim(mb_substr($event->message->text, mb_strlen(self::COMMAND)));

        if ($text === '') {
            $text = 'echo!';
        }

        $client->replyText(
            $event->replyToken,
            $text,
            $event->message->quoteToken,
        );
    }
}
