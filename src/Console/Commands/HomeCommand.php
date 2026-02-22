<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class HomeCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:home {--max-results=10 : Max posts to retrieve} {--format=table}';

    /** @var string */
    protected $description = 'Show your home timeline';

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

        $maxResults = (int) $this->option('max-results');

        try {
            $home = $client->homeTimeline($userId, maxResults: $maxResults);
        } catch (\Throwable $e) {
            $this->error("Failed to fetch home timeline: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($home)) {
            return self::SUCCESS;
        }

        if (count($home->data) === 0) {
            $this->info('No posts found.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($home->data as $post) {
            $rows[] = [
                $post->id,
                Str::limit($post->text, 80),
                $post->createdAt ?? 'N/A',
            ];
        }

        $this->table(['ID', 'Text', 'Created At'], $rows);
        $this->info(count($home->data).' post(s) found.');

        return self::SUCCESS;
    }
}
