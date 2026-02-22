<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class FollowersCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:followers {--max-results=100} {--format=table}';

    /** @var string */
    protected $description = 'List your followers';

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
            $users = $client->followers($userId, maxResults: (int) $this->option('max-results'));
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($users)) {
            return self::SUCCESS;
        }

        if (count($users->data) === 0) {
            $this->info('No followers found.');

            return self::SUCCESS;
        }

        $rows = array_map(fn ($user) => [$user->id, $user->name, $user->username], $users->data);
        $this->table(['ID', 'Name', 'Username'], $rows);
        $this->info(count($users->data).' item(s) found.');

        return self::SUCCESS;
    }
}
