<?php

declare(strict_types=1);

namespace App\LineBot\Handlers\TextMessageHandlers;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use Phine\Client;
use Phine\Handlers\BaseCommandHandler;

/**
 * エコーハンドラ（テスト用）.
 */
final class EchoHandler extends BaseCommandHandler
{
    /**
     * @param MessageEvent $event
     */
    public function handle(Client $client, Event $event): void
    {
        /** @var TextMessageContent $content */
        $content = $event->getMessage();

        $client->reply([
            (new TextMessage())
                ->setText($content->getText())
                ->setQuoteToken($content->getQuoteToken())
                ->setType(MessageType::TEXT),
        ]);
    }

    /**
     * @return array<string>
     */
    public static function commands(): array
    {
        return [
            '/echo',
        ];
    }
}
