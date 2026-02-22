<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class BookmarksCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:bookmarks {--max-results=10} {--format=table}';

    /** @var string */
    protected $description = 'List your bookmarked tweets';

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

        try {
            $tweets = $client->bookmarks($userId, maxResults: (int) $this->option('max-results'));
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($tweets)) {
            return self::SUCCESS;
        }

        if (count($tweets->data) === 0) {
            $this->info('No bookmarked tweets found.');

            return self::SUCCESS;
        }

        $rows = array_map(fn ($post) => [
            $post->id,
            Str::limit($post->text, 80),
            $post->createdAt ?? 'N/A',
        ], $tweets->data);

        $this->table(['ID', 'Text', 'Created At'], $rows);
        $this->info(count($tweets->data).' item(s) found.');

        return self::SUCCESS;
    }
}
