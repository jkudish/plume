<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class BookmarkCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:bookmark {id : Tweet ID to bookmark}';

    /** @var string */
    protected $description = 'Bookmark a tweet';

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
            $client->bookmark($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Tweet bookmarked successfully.');

        return self::SUCCESS;
    }
}
