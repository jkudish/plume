<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class MentionsCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:mentions {--max-results=10 : Max posts to retrieve} {--format=table}';

    /** @var string */
    protected $description = 'Show recent mentions';

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
            $mentions = $client->mentionsTimeline($userId, maxResults: $maxResults);
        } catch (\Throwable $e) {
            $this->error("Failed to fetch mentions: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($mentions)) {
            return self::SUCCESS;
        }

        if (count($mentions->data) === 0) {
            $this->info('No mentions found.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($mentions->data as $post) {
            $rows[] = [
                $post->id,
                Str::limit($post->text, 80),
                $post->createdAt ?? 'N/A',
            ];
        }

        $this->table(['ID', 'Text', 'Created At'], $rows);
        $this->info(count($mentions->data).' mention(s) found.');

        return self::SUCCESS;
    }
}
