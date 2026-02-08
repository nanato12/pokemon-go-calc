<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class DiscordHandler extends AbstractProcessingHandler
{
    private string $webhookUrl;
    private string $username;

    public function __construct(
        string $webhookUrl,
        string $username = 'Laravel Log',
        int|string|Level $level = Level::Error,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->webhookUrl = $webhookUrl;
        $this->username = $username;
    }

    protected function write(LogRecord $record): void
    {
        $color = match ($record->level) {
            Level::Emergency, Level::Alert, Level::Critical => 0xFF0000, // Red
            Level::Error => 0xE74C3C, // Light Red
            Level::Warning => 0xF39C12, // Orange
            Level::Notice => 0x3498DB, // Blue
            Level::Info => 0x2ECC71, // Green
            default => 0x95A5A6, // Gray
        };

        $payload = [
            'username' => $this->username,
            'embeds' => [
                [
                    'title' => $record->level->name,
                    'description' => substr($record->message, 0, 2000),
                    'color' => $color,
                    'timestamp' => $record->datetime->format('c'),
                    'footer' => [
                        'text' => config('app.name', 'Laravel'),
                    ],
                ],
            ],
        ];

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
