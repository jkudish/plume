<?php

declare(strict_types=1);

namespace Plume\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Plume\Data\User;
use Plume\Facades\X;
use Throwable;

class PlumeFollowersTool implements Tool
{
    public static function toolId(): string
    {
        return 'plume:followers';
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Fetch the followers list for a user.';
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
        $maxResults = $request->integer('max_results', 100) ?: 100;

        try {
            $result = X::followers($userId, $maxResults);

            if ($result->data === []) {
                return 'No followers found.';
            }

            $users = array_map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'description' => $user->description,
            ], $result->data);

            $data = [
                'result_count' => $result->resultCount,
                'users' => $users,
            ];

            return json_encode($data, JSON_PRETTY_PRINT) ?: 'No data.';
        } catch (Throwable $e) {
            return "Error fetching followers: {$e->getMessage()}";
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
                ->description('The user ID to get followers for (defaults to configured X_USER_ID).'),
            'max_results' => $schema
                ->integer()
                ->description('Maximum number of results to return (default: 100).'),
        ];
    }
}
