<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

use function Laravel\Prompts\confirm;

class UnfollowCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:unfollow {id : User ID to unfollow} {--force : Skip confirmation}';

    /** @var string */
    protected $description = 'Unfollow a user';

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

        if (! $this->option('force') && ! confirm("Are you sure you want to unfollow user {$id}?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $client->unfollow($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('User unfollowed successfully.');

        return self::SUCCESS;
    }
}
