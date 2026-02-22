<?php

declare(strict_types=1);

namespace Plume\Console\Commands\Lists;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class UpdateListCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:lists:update {id : List ID} {--name=} {--description=} {--format=table}';

    /** @var string */
    protected $description = 'Update an existing list';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $id */
        $id = $this->argument('id');

        $data = [];
        $name = $this->option('name');
        $description = $this->option('description');

        if (is_string($name)) {
            $data['name'] = $name;
        }

        if (is_string($description)) {
            $data['description'] = $description;
        }

        if (count($data) === 0) {
            $this->error('Provide at least one option: --name or --description.');

            return self::FAILURE;
        }

        try {
            $list = $client->updateList($id, $data);
        } catch (\Throwable $e) {
            $this->error("Failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($list)) {
            return self::SUCCESS;
        }

        $this->info('List updated successfully.');
        $this->table(['Field', 'Value'], [
            ['ID', $list->id],
            ['Name', $list->name],
        ]);

        return self::SUCCESS;
    }
}
