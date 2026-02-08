<?php

declare(strict_types=1);

namespace App\Infrastructure\Line\Handlers;

use App\Infrastructure\IvExtractor\IvExtractorClient;
use Exception;
use LINE\Clients\MessagingApi\Api\MessagingApiBlobApi;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\MessageEvent;
use Phine\Client;
use Phine\Handlers\BaseEventHandler;
use Phine\Helpers\MessageBuilders\TextMessageBuilder;
use SplFileObject;

/**
 * 画像メッセージハンドラ.
 */
final class ImageHandler extends BaseEventHandler
{
    public const EVENT_CLASS = MessageEvent::class;
    public const MESSAGE_TYPE_CLASS = ImageMessageContent::class;

    public function handle(Client $client, Event $event): void
    {
        /** @var MessageEvent $event */
        /** @var ImageMessageContent $message */
        $message = $event->getMessage();

        try {
            /** @var MessagingApiBlobApi $blobApi */
            $blobApi = app(MessagingApiBlobApi::class);

            /** @var IvExtractorClient $ivExtractor */
            $ivExtractor = app(IvExtractorClient::class);

            // 画像をダウンロード
            /** @var SplFileObject $imageFile */
            $imageFile = $blobApi->getMessageContent($message->getId());
            $imageData = $imageFile->fread($imageFile->getSize() ?: 0) ?: '';

            // IV抽出APIを呼び出し
            $result = $ivExtractor->extract($imageData);

            $iv = $result->getIv();
            $text = sprintf(
                "🎮 %s\n\n攻撃: %d\n防御: %d\nHP: %d",
                $result->getPokemon(),
                $iv->getAttack(),
                $iv->getDefense(),
                $iv->getStamina(),
            );

            $client->reply([
                new TextMessageBuilder($text),
            ]);
        } catch (Exception $e) {
            $client->reply([
                new TextMessageBuilder("❌ 画像の解析に失敗しました\n\nPokemon GOのスクリーンショットを送信してください"),
            ]);
        }
    }
}
