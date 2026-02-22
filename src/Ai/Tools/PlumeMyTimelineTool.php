<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Data\Post;
use Plume\Facades\X;
use Throwable;

class PlumeMyTimelineTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:my-timeline';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Fetch the authenticated user\'s own tweet timeline.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        $userId = (string) $request->string('user_id');
        if ($userId === '') {
            $userId = (string) config('x.user_id', '');
        }
        if ($userId === '') {
            return 'No user ID configured. Set X_USER_ID in your .env or pass user_id parameter.';
        }

        /** @var int $maxResults */
        $maxResults = $request->integer('max_results', 10) ?: 10;

        try {
            $result = X::userTimeline($userId, $maxResults);

            if ($result->data === []) {
                return 'No tweets found in timeline.';
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
            return "Error fetching user timeline: {$e->getMessage()}";
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
            'user_id' => $schema
                ->string()
                ->description('The user ID (defaults to configured X_USER_ID).'),
            'max_results' => $schema
                ->integer()
                ->description('Maximum number of results to return (default: 10).'),
        ];
    }
}
