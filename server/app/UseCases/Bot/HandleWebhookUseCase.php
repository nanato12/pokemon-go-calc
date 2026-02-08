<?php

declare(strict_types=1);

namespace App\UseCases\Bot;

use App\Domain\Bot\Contracts\BotClientInterface;
use App\Domain\Bot\Contracts\EventParserInterface;
use App\Domain\Bot\Entities\Event;
use App\Domain\Handlers\MessageHandlerInterface;
use Illuminate\Support\Facades\Log;

/**
 * Webhookハンドリング UseCase.
 */
final readonly class HandleWebhookUseCase
{
    /**
     * @param array<MessageHandlerInterface> $handlers
     */
    public function __construct(
        private EventParserInterface $eventParser,
        private BotClientInterface $botClient,
        private array $handlers = [],
    ) {}

    /**
     * Webhookを処理.
     *
     * @return array<Event> 処理したイベント
     */
    public function execute(string $body, string $signature): array
    {
        $events = $this->eventParser->parse($body, $signature);

        foreach ($events as $event) {
            $this->handleEvent($event);
        }

        return $events;
    }

    private function handleEvent(Event $event): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($event)) {
                Log::debug('Handling event', [
                    'handler' => $handler::class,
                    'event_type' => $event->type->value,
                ]);

                $handler->handle($this->botClient, $event);

                return;
            }
        }

        Log::debug('No handler found for event', [
            'event_type' => $event->type->value,
        ]);
    }
}
