<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

use function Laravel\Prompts\confirm;

class RemoveMemberCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:lists:remove-member {list-id : List ID} {user-id : User ID} {--force : Skip confirmation}';

    /** @var string */
    protected $description = 'Remove a member from a list';

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

        if (! $this->option('force') && ! confirm("Are you sure you want to remove user {$userId} from list {$listId}?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $client->removeListMember($listId, $userId);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Member removed successfully.');

        return self::SUCCESS;
    }
}
