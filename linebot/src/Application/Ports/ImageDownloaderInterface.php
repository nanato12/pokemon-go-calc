<?php

declare(strict_types=1);

namespace PokemonGoCalc\LineBot\Application\Ports;

/**
 * 画像ダウンローダーのインターフェース.
 */
interface ImageDownloaderInterface
{
    /**
     * メッセージIDから画像をダウンロードする.
     *
     * @param string $messageId LINEメッセージID
     * @return string 画像のバイナリデータ
     * @throws \RuntimeException ダウンロードに失敗した場合
     */
    public function download(string $messageId): string;
}
