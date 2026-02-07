<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Application\Ports;

/**
 * メッセージ送信のインターフェース.
 */
interface MessageSenderInterface
{
    /**
     * リプライメッセージを送信する.
     *
     * @param string $replyToken リプライトークン
     * @param string $message 送信するメッセージ
     */
    public function reply(string $replyToken, string $message): void;
}
