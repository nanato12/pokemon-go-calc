<?php

declare(strict_types=1);

namespace App\Domain\Bot\Contracts;

/**
 * Botクライアントインターフェース.
 *
 * LINE, Slack, Discord など各プラットフォームで実装
 */
interface BotClientInterface
{
    /**
     * テキストメッセージを返信.
     *
     * @param string      $replyToken 返信トークン
     * @param string      $text       メッセージ本文
     * @param null|string $quoteToken 引用トークン
     */
    public function replyText(string $replyToken, string $text, ?string $quoteToken = null): void;
}
