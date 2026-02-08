<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

final class DatabaseQueryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (config('logging.sql.enable') !== true) {
            return;
        }

        DB::listen(static function (QueryExecuted $event) {
            $sql = $event->connection
                ->getQueryGrammar()
                ->substituteBindingsIntoRawSql(
                    sql: $event->sql,
                    bindings: $event->connection->prepareBindings($event->bindings),
                );

            if ($event->time > config('logging.sql.slow_query_time')) {
                Log::warning(sprintf('%.2f ms, SQL: %s;', $event->time, $sql));
            }
            Log::channel('sql')->debug(sprintf('%.2f ms, SQL: %s;', $event->time, $sql));
        });

        Event::listen(static fn (TransactionBeginning $event) => Log::channel('sql')->debug('START TRANSACTION'));
        Event::listen(static fn (TransactionCommitted $event) => Log::channel('sql')->debug('COMMIT'));
        Event::listen(static fn (TransactionRolledBack $event) => Log::channel('sql')->debug('ROLLBACK'));
    }
}
