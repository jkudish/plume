<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class MeCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:me {--format=table}';

    /** @var string */
    protected $description = 'Show your X profile information';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        try {
            $user = $client->me();
        } catch (\Throwable $e) {
            $this->error("Failed to fetch profile: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($user)) {
            return self::SUCCESS;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Username', "@{$user->username}"],
                ['ID', $user->id],
                ['Description', $user->description ?? 'N/A'],
                ['Location', $user->location ?? 'N/A'],
                ['URL', $user->url ?? 'N/A'],
                ['Verified', $user->verified ? 'Yes' : 'No'],
                ['Created At', $user->createdAt ?? 'N/A'],
            ],
        );

        return self::SUCCESS;
    }
}
