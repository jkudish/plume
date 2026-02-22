<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

class AddMemberCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:lists:add-member {list-id : List ID} {user-id : User ID}';

    /** @var string */
    protected $description = 'Add a member to a list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $listId */
        $listId = $this->argument('list-id');
        /** @var string $userId */
        $userId = $this->argument('user-id');

        try {
            $client->addListMember($listId, $userId);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Member added successfully.');

        return self::SUCCESS;
    }
}
