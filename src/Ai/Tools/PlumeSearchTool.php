<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Data\Post;
use Plume\Facades\X;
use Throwable;

class PlumeSearchTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:search';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Search recent tweets matching a query.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $query */
        $query = (string) $request->string('query');

        /** @var int $maxResults */
        $maxResults = $request->integer('max_results', 10) ?: 10;

        try {
            $result = X::searchRecent($query, $maxResults);

            if ($result->data === []) {
                return 'No tweets found for the given query.';
            }

            $tweets = array_map(fn (Post $post) => [
                'id' => $post->id,
                'text' => $post->text,
                'author_id' => $post->authorId,
                'created_at' => $post->createdAt,
            ], $result->data);

            $data = [
                'result_count' => $result->resultCount,
                'tweets' => $tweets,
            ];

            return json_encode($data, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error searching tweets: {$e->getMessage()}";
        }
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
                ->string()
                ->description('The search query for finding tweets.')
                ->required(),
            'max_results' => $schema
                ->integer()
                ->description('Maximum number of results to return (default: 10).'),
        ];
    }
}
