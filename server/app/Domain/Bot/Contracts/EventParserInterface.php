<?php

declare(strict_types=1);

namespace App\Domain\Bot\Contracts;

use App\Domain\Bot\Entities\Event;

/**
 * イベントパーサーインターフェース.
 */
interface EventParserInterface
{
    /**
     * リクエストからイベントをパース.
     *
     * @param string $body      リクエストボディ
     * @param string $signature 署名
     *
     * @return array<Event>
     */
    public function parse(string $body, string $signature): array;
}
