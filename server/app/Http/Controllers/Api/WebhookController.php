<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Line\LineEventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\Constants\HTTPHeader;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use Phine\Client;

/**
 * LINE Webhook Controller.
 */
class WebhookController extends Controller
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function __invoke(Request $request): Response
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if ($signature === null) {
            return response(sprintf('header: %s is not found.', HTTPHeader::LINE_SIGNATURE), 400);
        }

        try {
            $events = $this->client->parseEventRequest($request->getContent(), $signature);

            foreach ($events as $event) {
                LineEventDispatcher::dispatch($this->client, $event);
            }
        } catch (InvalidSignatureException) {
            return response('Invalid signature', 400);
        } catch (InvalidEventRequestException) {
            return response('Invalid event request', 400);
        }

        return response('OK', 200);
    }
}
