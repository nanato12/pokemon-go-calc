<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LineBot\Config\Env;
use App\LineBot\Wrappers\EventDispatcherWrapper;
use App\LineBot\Wrappers\PhineWrapper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Constants\HTTPHeader;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;

class LineBotController extends Controller
{
    /**
     * LINE Bot Webhook callback.
     */
    public function callback(Request $request): Response
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if ($signature === null) {
            return response(sprintf('header: %s is not found.', HTTPHeader::LINE_SIGNATURE), 400);
        }

        $bot = new PhineWrapper(
            channelAccessSecret: Env::fromConfig('line-bot.channel_secret'),
            channelAccessToken: Env::fromConfig('line-bot.channel_access_token'),
        );

        try {
            $events = $bot->parseEventRequest($request->getContent(), $signature);
        } catch (InvalidSignatureException) {
            return response('Invalid signature', 400);
        } catch (InvalidEventRequestException) {
            return response('Invalid event request', 400);
        }

        foreach ($events as $event) {
            Log::debug(__METHOD__, [
                'event_type' => $event::class,
            ]);

            try {
                EventDispatcherWrapper::dispatch($bot, $event);
            } catch (ApiException $e) {
                Log::error('LINE API error', ['exception' => $e->getMessage()]);
            }
        }

        return response('OK', 200);
    }
}
