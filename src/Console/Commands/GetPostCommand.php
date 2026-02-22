<?php

declare(strict_types=1);

namespace Plume\Console\Commands;

use Illuminate\Console\Command;
use Plume\Console\Concerns\ResolvesXClient;
use Plume\Console\Concerns\SupportsJsonOutput;

class GetPostCommand extends Command
{
    use ResolvesXClient;
    use SupportsJsonOutput;

    /** @var string */
    protected $signature = 'plume:get-post {id : The post ID} {--format=table}';

    /** @var string */
    protected $description = 'Show a single post by ID';

    public function handle(): int
    {
        $client = $this->resolveClient();

        if (is_int($client)) {
            return $client;
        }

        /** @var string $id */
        $id = $this->argument('id');

        try {
            $post = $client->getPost($id);
        } catch (\Throwable $e) {
            $this->error("Failed to fetch post: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($this->outputJson($post)) {
            return self::SUCCESS;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $post->id],
                ['Text', $post->text],
                ['Author ID', $post->authorId ?? 'N/A'],
                ['Conversation ID', $post->conversationId ?? 'N/A'],
                ['Language', $post->lang ?? 'N/A'],
                ['Source', $post->source ?? 'N/A'],
                ['Created At', $post->createdAt ?? 'N/A'],
            ],
        );

        return self::SUCCESS;
    }
}
