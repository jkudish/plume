<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class UnbookmarkCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:unbookmark {id : Tweet ID to remove from bookmarks}';

    /** @var string */
    protected $description = 'Remove a tweet from bookmarks';

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
            $client->removeBookmark($userId, $id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Bookmark removed successfully.');

        return self::SUCCESS;
    }
}
