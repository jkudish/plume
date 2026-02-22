<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumeLikeTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:like';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Like a tweet by its ID.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $tweetId */
        $tweetId = (string) $request->string('tweet_id');

        $userId = (string) $request->string('user_id');
        if ($userId === '') {
            $userId = (string) config('x.user_id', '');
        }
        if ($userId === '') {
            return 'No user ID configured. Set X_USER_ID in your .env or pass user_id parameter.';
        }

        try {
            X::like($userId, $tweetId);

            return "Tweet {$tweetId} liked successfully.";
        } catch (Throwable $e) {
            return "Error liking tweet {$tweetId}: {$e->getMessage()}";
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
                ->description('The ID of the tweet to like.')
                ->required(),
        ];
    }
}
