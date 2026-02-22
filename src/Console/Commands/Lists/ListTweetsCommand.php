<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class ListTweetsCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists:tweets {id : List ID} {--max-results=10} {--format=table}';

    /** @var string */
    protected $description = 'List tweets from a list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        try {
            /** @var string $id */
            $id = $this->argument('id');
            $tweets = $client->listTweets($id, maxResults: (int) $this->option('max-results'));
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($tweets)) {
            return self::SUCCESS;
        }

        if (count($tweets->data) === 0) {
            $this->info('No tweets found.');

            return self::SUCCESS;
        }

        $this->info(count($tweets->data).' item(s) found.');

        $rows = [];
        foreach ($tweets->data as $post) {
            $rows[] = [
                $post->id,
                Str::limit($post->text, 80),
                $post->createdAt ?? 'N/A',
            ];
        }

        $this->table(['ID', 'Text', 'Created At'], $rows);

        return self::SUCCESS;
    }
}
