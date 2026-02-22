<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class ListMembersCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists:members {id : List ID} {--max-results=100} {--format=table}';

    /** @var string */
    protected $description = 'List members of a list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        try {
            /** @var string $id */
            $id = $this->argument('id');
            $members = $client->listMembers($id, maxResults: (int) $this->option('max-results'));
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($members)) {
            return self::SUCCESS;
        }

        if (count($members->data) === 0) {
            $this->info('No members found.');

            return self::SUCCESS;
        }

        $this->info(count($members->data).' item(s) found.');

        $rows = [];
        foreach ($members->data as $user) {
            $rows[] = [
                $user->id,
                $user->name,
                $user->username,
            ];
        }

        $this->table(['ID', 'Name', 'Username'], $rows);

        return self::SUCCESS;
    }
}
