<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UseCases\Bot\HandleWebhookUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\Constants\HTTPHeader;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;

/**
 * Webhook Controller.
 */
class WebhookController extends Controller
{
    public function __construct(
        private readonly HandleWebhookUseCase $handleWebhookUseCase,
    ) {}

    public function __invoke(Request $request): Response
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);

        if ($signature === null) {
            return response(sprintf('header: %s is not found.', HTTPHeader::LINE_SIGNATURE), 400);
        }

        try {
            $this->handleWebhookUseCase->execute($request->getContent(), $signature);
        } catch (InvalidSignatureException) {
            return response('Invalid signature', 400);
        } catch (InvalidEventRequestException) {
            return response('Invalid event request', 400);
        }

        return response('OK', 200);
    }
}
