<?php

declare(strict_types=1);

namespace App\Infrastructure\Line;

use App\Infrastructure\Line\Handlers\ImageHandler;
use App\Infrastructure\Line\Handlers\TestHandler;
use Phine\Handlers\EventDispatcher;

/**
 * LINE イベントディスパッチャ.
 */
final class LineEventDispatcher extends EventDispatcher
{
    /**
     * @return string[]
     */
    public function getHandlerClasses(): array
    {
        return [
            ImageHandler::class,
            TestHandler::class,
        ];
    }
}
