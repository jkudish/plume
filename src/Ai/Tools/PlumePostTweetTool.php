<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumePostTweetTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:post-tweet';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Post a new tweet, optionally as a reply to another tweet.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $text */
        $text = (string) $request->string('text');
        /** @var string $replyTo */
        $replyTo = (string) $request->string('reply_to');

        try {
            $options = [];
            if ($replyTo !== '') {
                $options['reply'] = ['in_reply_to_tweet_id' => $replyTo];
            }

            $post = X::createPost($text, $options);

            $data = [
                'id' => $post->id,
                'text' => $post->text,
            ];

            return json_encode($data, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error posting tweet: {$e->getMessage()}";
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
            'text' => $schema
                ->string()
                ->description('The text content of the tweet.')
                ->required(),
            'reply_to' => $schema
                ->string()
                ->description('Tweet ID to reply to (optional).'),
        ];
    }
}
