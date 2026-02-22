<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class UnretweetCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:unretweet {id : Tweet ID to unretweet}';

    /** @var string */
    protected $description = 'Undo a retweet';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        $userId = $this->resolveUserId($client);

        if (is_int($userId)) {
            return $userId;
        }

        /** @var string $id */
        $id = $this->argument('id');

        try {
            $client->undoRetweet($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Retweet undone successfully.');

        return self::SUCCESS;
    }
}
