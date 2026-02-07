<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Application\UseCases;

use PokemonGoCalc\LineBot\Application\Ports\ImageDownloaderInterface;
use PokemonGoCalc\LineBot\Application\Ports\IvExtractorInterface;
use PokemonGoCalc\LineBot\Application\Ports\MessageSenderInterface;

/**
 * 画像メッセージ処理ユースケース.
 */
final class HandleImageMessageUseCase
{
    public function __construct(
        private readonly ImageDownloaderInterface $imageDownloader,
        private readonly IvExtractorInterface $ivExtractor,
        private readonly MessageSenderInterface $messageSender,
    ) {
    }

    /**
     * 画像メッセージを処理してIV抽出結果を返信する.
     *
     * @param string $replyToken リプライトークン
     * @param string $messageId 画像メッセージID
     */
    public function execute(string $replyToken, string $messageId): void
    {
        try {
            $imageData = $this->imageDownloader->download($messageId);
            $result = $this->ivExtractor->extract($imageData);
            $this->messageSender->reply($replyToken, $result->toMessage());
        } catch (\RuntimeException $e) {
            $this->messageSender->reply(
                $replyToken,
                "❌ 画像の解析に失敗しました\n\nPokemon GOのスクリーンショットを送信してください"
            );
        }
    }
}
