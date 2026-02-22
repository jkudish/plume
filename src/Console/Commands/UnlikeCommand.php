<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class UnlikeCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:unlike {id : Tweet ID to unlike}';

    /** @var string */
    protected $description = 'Unlike a tweet';

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
            $client->unlike($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Tweet unliked successfully.');

        return self::SUCCESS;
    }
}
