<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class ListsCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists {--max-results=100} {--format=table}';

    /** @var string */
    protected $description = 'List your owned lists';

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
            $lists = $client->ownedLists($userId, maxResults: (int) $this->option('max-results'));
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($lists)) {
            return self::SUCCESS;
        }

        if (count($lists->data) === 0) {
            $this->info('No lists found.');

            return self::SUCCESS;
        }

        $this->info(count($lists->data).' item(s) found.');

        $rows = [];
        foreach ($lists->data as $list) {
            $rows[] = [
                $list->id,
                $list->name,
                Str::limit($list->description ?? '', 40),
                $list->private ? 'Yes' : 'No',
                $list->metrics !== null ? $list->metrics->memberCount : 'N/A',
            ];
        }

        $this->table(['ID', 'Name', 'Description', 'Private', 'Member Count'], $rows);

        return self::SUCCESS;
    }
}
