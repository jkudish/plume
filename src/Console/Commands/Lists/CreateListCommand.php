<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class CreateListCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists:create {name} {--description=} {--private} {--format=table}';

    /** @var string */
    protected $description = 'Create a new list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $name */
        $name = $this->argument('name');

        $options = [];
        $description = $this->option('description');

        if (is_string($description)) {
            $options['description'] = $description;
        }

        if ($this->option('private')) {
            $options['private'] = true;
        }

        try {
            $list = $client->createList($name, $options);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($list)) {
            return self::SUCCESS;
        }

        $this->info('List created successfully.');
        $this->table(['Field', 'Value'], [
            ['ID', $list->id],
            ['Name', $list->name],
            ['Description', $list->description ?? 'N/A'],
            ['Private', $list->private ? 'Yes' : 'No'],
        ]);

        return self::SUCCESS;
    }
}
