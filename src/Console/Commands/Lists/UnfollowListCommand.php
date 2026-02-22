<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class UnfollowListCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:lists:unfollow {id : List ID}';

    /** @var string */
    protected $description = 'Unfollow a list';

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
            $client->unfollowList($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('List unfollowed successfully.');

        return self::SUCCESS;
    }
}
