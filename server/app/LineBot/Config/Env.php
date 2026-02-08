<?php

declare(strict_types=1);

namespace App\LineBot\Config;

use LogicException;

/**
 * 環境変数関係.
 */
final class Env
{
    /**
     * configから値を取得する.
     */
    public static function fromConfig(string $key): string
    {
        /** @var null|string $e */
        $e = config($key);

        if ($e === null) {
            throw new LogicException("config: {$key} is not Implemented.");
        }

        return $e;
    }

    /**
     * ローカル環境か判定する.
     */
    public static function isLocal(): bool
    {
        return self::fromConfig('app.env') === 'local';
    }
}
