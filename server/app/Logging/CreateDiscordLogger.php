<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Logger;

use function is_string;

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

        $url = $config['url'] ?? '';
        $username = $config['username'] ?? 'Laravel Log';
        $level = $config['level'] ?? 'error';

        $logger->pushHandler(new DiscordHandler(
            webhookUrl: is_string($url) ? $url : '',
            username: is_string($username) ? $username : 'Laravel Log',
            level: is_string($level) ? $level : 'error',
        ));

        return $logger;
    }
}
