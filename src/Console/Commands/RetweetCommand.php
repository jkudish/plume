<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class RetweetCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:retweet {id : Tweet ID to retweet}';

    /** @var string */
    protected $description = 'Retweet a tweet';

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
            $client->retweet($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Tweet retweeted successfully.');

        return self::SUCCESS;
    }
}
