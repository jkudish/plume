<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class UserCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:user {id?} {--username= : Look up user by username} {--format=table}';

    /** @var string */
    protected $description = 'Show an X user profile by ID or username';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        $id = $this->argument('id');
        $username = $this->option('username');

        if (! is_string($username) && ! is_string($id)) {
            $this->error('Provide a user ID as argument or use --username to look up by username.');

            return self::FAILURE;
        }

        try {
            if (is_string($username) && $username !== '') {
                $user = $client->getUserByUsername($username);
            } else {
                /** @var string $id */
                $user = $client->getUser($id);
            }
        } catch (\Throwable $e) {
            $this->error("Failed to fetch user: {$e->getMessage()}");

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
