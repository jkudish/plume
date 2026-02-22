<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Facades\X;
use Throwable;

class PlumeProfileTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:profile';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Get a user profile by ID, username, or the authenticated user.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        /** @var string $userId */
        $userId = (string) $request->string('user_id');
        /** @var string $username */
        $username = (string) $request->string('username');

        try {
            if ($userId !== '') {
                $user = X::getUser($userId);
            } elseif ($username !== '') {
                $user = X::getUserByUsername($username);
            } else {
                $user = X::me();
            }

            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'description' => $user->description,
                'location' => $user->location,
                'url' => $user->url,
                'profile_image_url' => $user->profileImageUrl,
                'created_at' => $user->createdAt,
                'verified' => $user->verified,
                'metrics' => $user->publicMetrics !== null ? [
                    'followers_count' => $user->publicMetrics->followersCount,
                    'following_count' => $user->publicMetrics->followingCount,
                    'tweet_count' => $user->publicMetrics->tweetCount,
                    'listed_count' => $user->publicMetrics->listedCount,
                ] : null,
            ];

            return json_encode($data, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error fetching profile: {$e->getMessage()}";
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
                ->description('The user ID to look up (optional, defaults to authenticated user).'),
            'username' => $schema
                ->string()
                ->description('The username to look up (optional, used if user_id not provided).'),
        ];
    }
}
