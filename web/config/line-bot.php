<?php

declare(strict_types=1);

return [
    'channel_id' => env('LINE_BOT_CHANNEL_ID', ''),
    'channel_secret' => env('LINE_BOT_CHANNEL_SECRET', ''),
    'channel_access_token' => env('LINE_BOT_CHANNEL_ACCESS_TOKEN', ''),
    'iv_extractor_url' => env('IV_EXTRACTOR_URL', ''),
];
