<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;

use function Laravel\Prompts\confirm;

class DeletePostCommand extends Command
{
    use ResolvesXClient;

    /** @var string */
    protected $signature = 'plume:delete-post {id} {--force : Skip confirmation}';

    /** @var string */
    protected $description = 'Delete a post';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $id */
        $id = $this->argument('id');

        if (! $this->option('force') && ! confirm("Are you sure you want to delete post {$id}?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $client->deletePost($id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Post deleted successfully.');

        return self::SUCCESS;
    }
}
