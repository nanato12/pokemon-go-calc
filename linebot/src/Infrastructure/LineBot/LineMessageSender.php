<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Infrastructure\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use PokemonGoCalc\LineBot\Application\Ports\MessageSenderInterface;

/**
 * LINE Messaging APIを使用したメッセージ送信.
 */
final class LineMessageSender implements MessageSenderInterface
{
    public function __construct(
        private readonly MessagingApiApi $messagingApi,
    ) {
    }

    public function reply(string $replyToken, string $message): void
    {
        $textMessage = new TextMessage([
            'type' => 'text',
            'text' => $message,
        ]);

        $request = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$textMessage],
        ]);

        $this->messagingApi->replyMessage($request);
    }
}
