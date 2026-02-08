<?php

declare(strict_types=1);

namespace App\Domain\Handlers;

use App\Domain\Bot\Contracts\BotClientInterface;
use App\Domain\Bot\Entities\Event;

/**
 * メッセージハンドラインターフェース.
 */
interface MessageHandlerInterface
{
    /**
     * このハンドラが処理可能か判定.
     */
    public function canHandle(Event $event): bool;

    /**
     * イベントを処理.
     */
    public function handle(BotClientInterface $client, Event $event): void;
}
