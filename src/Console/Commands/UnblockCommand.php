<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

use function Laravel\Prompts\confirm;

class UnblockCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:unblock {id : User ID to unblock} {--force : Skip confirmation}';

    /** @var string */
    protected $description = 'Unblock a user';

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

        if (! $this->option('force') && ! confirm("Are you sure you want to unblock user {$id}?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $client->unblock($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('User unblocked successfully.');

        return self::SUCCESS;
    }
}
