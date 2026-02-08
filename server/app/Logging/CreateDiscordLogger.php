<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Logger;

class CreateDiscordLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array<string, mixed> $config
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('discord');

        $logger->pushHandler(new DiscordHandler(
            webhookUrl: $config['webhook_url'],
            username: $config['username'] ?? 'Laravel Log',
            level: $config['level'] ?? 'error',
        ));

        return $logger;
    }
}
