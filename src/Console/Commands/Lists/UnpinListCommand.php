<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class UnpinListCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:lists:unpin {id : List ID}';

    /** @var string */
    protected $description = 'Unpin a list';

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
            $client->unpinList($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('List unpinned successfully.');

        return self::SUCCESS;
    }
}
