<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;
use Plume\Enums\SortOrder;

class SearchCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:search {query : The search query} {--max-results=10 : Max posts to retrieve} {--sort= : Sort order (recency or relevancy)} {--format=table}';

    /** @var string */
    protected $description = 'Search recent posts';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $query */
        $query = $this->argument('query');
        $maxResults = (int) $this->option('max-results');
        $sortOption = $this->option('sort');
        $sortOrder = null;

        if (is_string($sortOption) && $sortOption !== '') {
            $sortOrder = match ($sortOption) {
                'recency' => SortOrder::Recency,
                'relevancy' => SortOrder::Relevancy,
                default => null,
            };

            if ($sortOrder === null) {
                $this->error("Invalid sort order: {$sortOption}. Use 'recency' or 'relevancy'.");

                return self::FAILURE;
            }
        }

        try {
            $results = $client->searchRecent($query, maxResults: $maxResults, sortOrder: $sortOrder);
        } catch (\Throwable $e) {
            $this->error("Failed to search posts: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($results)) {
            return self::SUCCESS;
        }

        if (count($results->data) === 0) {
            $this->info('No posts found.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($results->data as $post) {
            $rows[] = [
                $post->id,
                Str::limit($post->text, 80),
                $post->createdAt ?? 'N/A',
            ];
        }

        $this->table(['ID', 'Text', 'Created At'], $rows);
        $this->info(count($results->data).' post(s) found.');

        return self::SUCCESS;
    }
}
