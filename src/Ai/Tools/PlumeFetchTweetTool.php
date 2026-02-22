<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumeFetchTweetTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:fetch-tweet';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Fetch a tweet by its ID, returning its text, author, and engagement metrics.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $tweetId */
        $tweetId = (string) $request->string('tweet_id');

        try {
            $post = X::getPost($tweetId);

            $data = [
                'id' => $post->id,
                'text' => $post->text,
                'author_id' => $post->authorId,
                'created_at' => $post->createdAt,
                'metrics' => $post->publicMetrics !== null ? [
                    'retweet_count' => $post->publicMetrics->retweetCount,
                    'reply_count' => $post->publicMetrics->replyCount,
                    'like_count' => $post->publicMetrics->likeCount,
                    'quote_count' => $post->publicMetrics->quoteCount,
                    'bookmark_count' => $post->publicMetrics->bookmarkCount,
                    'impression_count' => $post->publicMetrics->impressionCount,
                ] : null,
            ];

            return json_encode($data, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error fetching tweet {$tweetId}: {$e->getMessage()}";
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
            'tweet_id' => $schema
                ->string()
                ->description('The ID of the tweet to fetch.')
                ->required(),
        ];
    }
}
