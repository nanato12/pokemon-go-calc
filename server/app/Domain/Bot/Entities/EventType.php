<?php

declare(strict_types=1);

namespace App\Domain\Bot\Entities;

/**
 * イベントタイプ.
 */
enum EventType: string
{
    case MESSAGE = 'message';
    case FOLLOW = 'follow';
    case UNFOLLOW = 'unfollow';
    case POSTBACK = 'postback';
    case UNKNOWN = 'unknown';
}
