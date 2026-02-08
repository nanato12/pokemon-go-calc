<?php

declare(strict_types=1);

namespace App\LineBot\Wrappers;

use App\LineBot\Handlers\TextMessageHandlers\EchoHandler;
use Phine\Handlers\EventDispatcher;

final class EventDispatcherWrapper extends EventDispatcher
{
    /**
     * @return array<class-string>
     */
    public function getHandlerClasses(): array
    {
        return [
            EchoHandler::class,
        ];
    }
}
