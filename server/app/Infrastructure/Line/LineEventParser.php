<?php

declare(strict_types=1);

namespace App\Infrastructure\Line;

use App\Domain\Bot\Contracts\EventParserInterface;
use App\Domain\Bot\Entities\Event;
use App\Domain\Bot\Entities\EventType;
use App\Domain\Bot\Entities\Message;
use App\Domain\Bot\Entities\User;
use LINE\Parser\EventRequestParser;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\GroupSource;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\RoomSource;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Webhook\Model\UnfollowEvent;
use LINE\Webhook\Model\UserSource;

/**
 * LINE イベントパーサー実装.
 */
final readonly class LineEventParser implements EventParserInterface
{
    public function __construct(
        private string $channelSecret,
    ) {}

    /**
     * @return array<Event>
     *
     * @throws InvalidSignatureException
     * @throws InvalidEventRequestException
     */
    public function parse(string $body, string $signature): array
    {
        $parsedEvents = EventRequestParser::parseEventRequest($body, $this->channelSecret, $signature);

        $events = [];

        foreach ($parsedEvents->getEvents() as $lineEvent) {
            if (!$lineEvent instanceof MessageEvent && !$lineEvent instanceof FollowEvent && !$lineEvent instanceof UnfollowEvent) {
                continue;
            }

            $event = $this->convertToEvent($lineEvent);

            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $events;
    }

    private function convertToEvent(FollowEvent|MessageEvent|UnfollowEvent $lineEvent): ?Event
    {
        $source = $lineEvent->getSource();
        $userId = '';

        if ($source instanceof UserSource || $source instanceof GroupSource || $source instanceof RoomSource) {
            $userId = $source->getUserId() ?? '';
        }

        $user = new User(
            id: $userId,
        );

        if ($lineEvent instanceof MessageEvent) {
            $content = $lineEvent->getMessage();

            if ($content instanceof TextMessageContent) {
                return new Event(
                    type: EventType::MESSAGE,
                    replyToken: $lineEvent->getReplyToken() ?? '',
                    user: $user,
                    message: new Message(
                        id: $content->getId(),
                        text: $content->getText(),
                        quoteToken: $content->getQuoteToken(),
                    ),
                    rawEvent: $lineEvent,
                );
            }
        }

        if ($lineEvent instanceof FollowEvent) {
            return new Event(
                type: EventType::FOLLOW,
                replyToken: $lineEvent->getReplyToken(),
                user: $user,
                rawEvent: $lineEvent,
            );
        }

        if ($lineEvent instanceof UnfollowEvent) {
            return new Event(
                type: EventType::UNFOLLOW,
                replyToken: '',
                user: $user,
                rawEvent: $lineEvent,
            );
        }

        return null;
    }
}
