<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

use function Laravel\Prompts\confirm;

class DeleteListCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:lists:delete {id : List ID} {--force : Skip confirmation}';

    /** @var string */
    protected $description = 'Delete a list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $id */
        $id = $this->argument('id');

        if (! $this->option('force') && ! confirm("Are you sure you want to delete list {$id}?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $client->deleteList($id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('List deleted successfully.');

        return self::SUCCESS;
    }
}
