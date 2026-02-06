<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Infrastructure\Http;

use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use PokemonGoCalc\LineBot\Application\Ports\MessageSenderInterface;
use PokemonGoCalc\LineBot\Application\UseCases\HandleImageMessageUseCase;

/**
 * LINE Webhook Handler.
 */
final class WebhookHandler
{
    private const HELP_MESSAGE = <<<MSG
🎮 Pokemon GO IV チェッカー

Pokemon GOのスクリーンショットを送信すると、個体値を解析します。

📸 使い方:
1. Pokemon GOで「ポケモンを調べてもらう」を開く
2. スクリーンショットを撮影
3. このトークに画像を送信

🔍 対応している画面:
・リーダーの評価画面
MSG;

    public function __construct(
        private readonly HandleImageMessageUseCase $handleImageUseCase,
        private readonly MessageSenderInterface $messageSender,
        private readonly string $channelSecret,
    ) {
    }

    /**
     * Webhookリクエストを処理する.
     *
     * @param string $body リクエストボディ
     * @param string $signature X-Line-Signature ヘッダー
     */
    public function handle(string $body, string $signature): void
    {
        $parsedEvents = EventRequestParser::parseEventRequest(
            $body,
            $this->channelSecret,
            $signature
        );

        foreach ($parsedEvents->getEvents() as $event) {
            $this->handleEvent($event);
        }
    }

    private function handleEvent(Event $event): void
    {
        if (!($event instanceof MessageEvent)) {
            return;
        }

        $message = $event->getMessage();
        $replyToken = $event->getReplyToken();

        if ($replyToken === null) {
            return;
        }

        if ($message instanceof ImageMessageContent) {
            $this->handleImageMessage($replyToken, $message);
        } elseif ($message instanceof TextMessageContent) {
            $this->handleTextMessage($replyToken, $message);
        }
    }

    private function handleImageMessage(
        string $replyToken,
        ImageMessageContent $message
    ): void {
        $messageId = $message->getId();
        $this->handleImageUseCase->execute($replyToken, $messageId);
    }

    private function handleTextMessage(
        string $replyToken,
        TextMessageContent $message
    ): void {
        $text = strtolower(trim($message->getText()));

        // ヘルプ以外は無視
        if (in_array($text, ['help', 'ヘルプ', '使い方'], true)) {
            $this->messageSender->reply($replyToken, self::HELP_MESSAGE);
        }
    }
}
