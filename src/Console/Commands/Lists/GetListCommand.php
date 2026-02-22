<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class GetListCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists:get {id : List ID} {--format=table}';

    /** @var string */
    protected $description = 'Get details about a specific list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        try {
            /** @var string $id */
            $id = $this->argument('id');
            $list = $client->getList($id);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($list)) {
            return self::SUCCESS;
        }

        $this->table(['Field', 'Value'], [
            ['ID', $list->id],
            ['Name', $list->name],
            ['Description', $list->description ?? 'N/A'],
            ['Owner ID', $list->ownerId ?? 'N/A'],
            ['Private', $list->private ? 'Yes' : 'No'],
            ['Created At', $list->createdAt ?? 'N/A'],
        ]);

        return self::SUCCESS;
    }
}
