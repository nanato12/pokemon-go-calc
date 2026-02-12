<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Handlers;

use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use Phine\Client;
use Phine\Handlers\BaseCommandHandler;
use Phine\MessageBuilders\TextMessageBuilder;

/**
 * テストハンドラ.
 */
final class TestHandler extends BaseCommandHandler
{
    /**
     * @return string[]
     */
    public static function commands(): array
    {
        return ['test'];
    }

    public function handle(Client $client, Event $event): void
    {
        /** @var MessageEvent $event */
        /** @var TextMessageContent $message */
        $message = $event->getMessage();

        $client->reply([
            new TextMessageBuilder('ok', [], $message->getQuoteToken()),
        ]);
    }
}
