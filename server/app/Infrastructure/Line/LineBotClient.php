<?php

declare(strict_types=1);

namespace App\Infrastructure\Line;

use App\Domain\Bot\Contracts\BotClientInterface;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage as LineTextMessage;
use LINE\Constants\MessageType;

/**
 * LINE Bot クライアント実装.
 */
final class LineBotClient implements BotClientInterface
{
    private MessagingApiApi $api;

    public function __construct(string $channelAccessToken)
    {
        $config = (new Configuration())->setAccessToken($channelAccessToken);
        $this->api = new MessagingApiApi(config: $config);
    }

    public function replyText(string $replyToken, string $text, ?string $quoteToken = null): void
    {
        $message = new LineTextMessage([
            'type' => MessageType::TEXT,
            'text' => $text,
        ]);

        if ($quoteToken !== null) {
            $message->setQuoteToken($quoteToken);
        }

        $request = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$message],
        ]);

        $this->api->replyMessage($request);
    }
}
