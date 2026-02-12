<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

class DiscordHandler extends AbstractProcessingHandler
{
    private string $webhookUrl;

    private string $username;

    public function __construct(
        string $webhookUrl,
        string $username = 'Laravel Log',
        int|Level|string $level = Level::Error,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->webhookUrl = $webhookUrl;
        $this->username = $username;
    }

    protected function write(LogRecord $record): void
    {
        if (empty($this->webhookUrl)) {
            return;
        }

        $color = match ($record->level) {
            Level::Emergency, Level::Alert, Level::Critical => 0xFF0000,
            Level::Error => 0xE74C3C,
            Level::Warning => 0xF39C12,
            Level::Notice => 0x3498DB,
            Level::Info => 0x2ECC71,
            default => 0x95A5A6,
        };

        $embed = [
            'title' => $record->level->name,
            'description' => substr($record->message, 0, 2000),
            'color' => $color,
            'timestamp' => $record->datetime->format('c'),
            'footer' => [
                'text' => config('app.name', 'Laravel'),
            ],
        ];

        // 例外のスタックトレースを追加
        if (isset($record->context['exception']) && $record->context['exception'] instanceof Throwable) {
            $exception = $record->context['exception'];
            $trace = $exception->getTraceAsString();

            $embed['fields'] = [
                [
                    'name' => 'Exception',
                    'value' => substr(get_class($exception) . ': ' . $exception->getMessage(), 0, 1024),
                    'inline' => false,
                ],
                [
                    'name' => 'File',
                    'value' => substr($exception->getFile() . ':' . $exception->getLine(), 0, 1024),
                    'inline' => false,
                ],
                [
                    'name' => 'Stack Trace',
                    'value' => '```' . substr($trace, 0, 1000) . '```',
                    'inline' => false,
                ],
            ];
        }

        $payload = [
            'username' => $this->username,
            'embeds' => [$embed],
        ];

        $json = json_encode($payload);

        if ($json === false) {
            return;
        }

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
